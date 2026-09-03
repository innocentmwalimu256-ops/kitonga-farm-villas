<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Sale;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Expense;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class FinancialController extends Controller
{
    /**
     * Display listing of all payments with details.
     */
    public function paymentsIndex(Request $request)
    {
        abort_if(!auth()->user()->hasAnyPermission(['view_revenue', 'view_profit']), 403, 'Unauthorized access to payments registry.');

        $query = Payment::with(['booking.customer', 'sale.customer', 'recorder']);

        if ($request->filled('method')) {
            $query->where('method', $request->input('method'));
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return Inertia::render('Admin/Financials/Payments', [
            'payments' => $payments,
            'filters' => $request->only(['method']),
        ]);
    }

    /**
     * Process refund against a payment.
     */
    public function refundPayment(Request $request, $id)
    {
        abort_if(!auth()->user()->hasPermissionTo('refund_sales'), 403, 'Unauthorized to process payment refunds.');

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
        ]);

        $payment = Payment::findOrFail($id);
        $refundAmount = $request->input('amount');

        if ($refundAmount > $payment->amount) {
            return back()->withErrors(['refund' => 'Refund amount cannot exceed the original payment amount.']);
        }

        try {
            DB::transaction(function () use ($payment, $refundAmount, $request) {
                // 1. Create refund record
                $refund = Refund::create([
                    'payment_id' => $payment->id,
                    'amount' => $refundAmount,
                    'reason' => $request->input('reason'),
                    'status' => 'completed',
                    'recorded_by' => auth()->id(),
                ]);

                // 2. Adjust parent transaction balance if booking
                if ($payment->booking_id) {
                    $booking = Booking::lockForUpdate()->find($payment->booking_id);
                    $newPaid = max(0.00, $booking->amount_paid - $refundAmount);
                    $newBalance = $booking->total - $newPaid;

                    $booking->update([
                        'amount_paid' => $newPaid,
                        'balance' => $newBalance,
                    ]);

                    ActivityLog::create([
                        'user_id' => auth()->id(),
                        'action' => 'booking_payment_refunded',
                        'entity_type' => 'Booking',
                        'entity_id' => $booking->id,
                        'new_values' => $booking->toArray(),
                        'created_at' => Carbon::now(),
                    ]);
                }

                // 3. Adjust parent transaction if POS sale
                if ($payment->sale_id) {
                    $sale = Sale::lockForUpdate()->find($payment->sale_id);
                    // Mark sale refunded/cancelled if fully refunded
                    if ($refundAmount >= $payment->amount) {
                        $sale->update(['status' => 'cancelled']);
                    }

                    ActivityLog::create([
                        'user_id' => auth()->id(),
                        'action' => 'sale_payment_refunded',
                        'entity_type' => 'Sale',
                        'entity_id' => $sale->id,
                        'new_values' => $sale->toArray(),
                        'created_at' => Carbon::now(),
                    ]);
                }

                // Update payment status to partially or fully refunded
                $payment->update([
                    'status' => ($refundAmount >= $payment->amount) ? 'refunded' : 'partially_refunded',
                ]);

                // Log audit log
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'payment_refund_processed',
                    'entity_type' => 'Payment',
                    'entity_id' => $payment->id,
                    'new_values' => $refund->toArray(),
                    'created_at' => Carbon::now(),
                ]);
            });

            return back()->with('success', 'Refund processed successfully and parent accounts reconciled.');
        } catch (Exception $e) {
            return back()->withErrors(['refund' => $e->getMessage()]);
        }
    }

    /**
     * Detailed financial statement and dashboard.
     */
    public function financialReport(Request $request)
    {
        abort_if(!auth()->user()->hasAnyPermission(['view_revenue', 'view_profit']), 403, 'Unauthorized access to financial report.');

        $startDate = Carbon::now()->startOfMonth()->startOfDay();
        $endDate = Carbon::now()->endOfMonth()->endOfDay();

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
        }

        // 1. Gross Revenue
        $accommodationRevenue = Booking::whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
            ->whereBetween('check_in', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->sum('total');

        $posRevenue = Sale::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total');

        $grossRevenue = $accommodationRevenue + $posRevenue;

        // 2. Refunds processed
        $refunds = Refund::whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        // 3. Net Revenue
        $netRevenue = $grossRevenue - $refunds;

        // 4. Cash Collected (completed payments amount)
        $cashCollected = Payment::where('status', 'completed')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->sum('amount');

        // 5. Outstanding Balances
        $outstandingBalances = Booking::whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->sum('balance');

        // 6. Operating Expenses (approved expenses amount)
        $operatingExpenses = Expense::where('status', 'approved')
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->sum('amount');

        // 7. Net Operational Estimate (Net Revenue minus Operating Expenses)
        $operationalNetProfit = $netRevenue - $operatingExpenses;

        return Inertia::render('Admin/Financials/Report', [
            'metrics' => [
                'gross_revenue' => $grossRevenue,
                'refunds' => $refunds,
                'net_revenue' => $netRevenue,
                'cash_collected' => $cashCollected,
                'outstanding_balances' => $outstandingBalances,
                'operating_expenses' => $operatingExpenses,
                'operational_net_profit' => $operationalNetProfit,
            ],
            'filters' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
        ]);
    }
}
