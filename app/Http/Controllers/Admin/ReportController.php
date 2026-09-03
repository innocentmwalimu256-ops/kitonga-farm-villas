<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Expense;
use App\Models\Sale;
use App\Exports\BookingsExport;
use App\Exports\ExpensesExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    /**
     * Show report dashboard.
     */
    public function index()
    {
        abort_if(!auth()->user()->hasAnyPermission(['view_bookings', 'view_revenue', 'view_profit']), 403, 'Unauthorized access to reports center.');
        $bookingsCount = Booking::count();
        $expensesCount = Expense::count();
        $totalRevenue = Booking::whereIn('status', ['confirmed', 'checked_in', 'checked_out'])->sum('total') + Sale::where('status', 'completed')->sum('total');
        $totalExpenses = Expense::where('status', 'approved')->sum('amount');
        $netProfit = $totalRevenue - $totalExpenses;

        return Inertia::render('Admin/Reports/Index', [
            'bookingsCount' => $bookingsCount,
            'expensesCount' => $expensesCount,
            'totalRevenue' => $totalRevenue,
            'totalExpenses' => $totalExpenses,
            'netProfit' => $netProfit,
        ]);
    }

    /**
     * Export Bookings Excel.
     */
    public function exportBookings(Request $request)
    {
        abort_if(!auth()->user()->hasAnyPermission(['view_bookings', 'view_revenue']), 403, 'Unauthorized to export bookings.');

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = Booking::query();
        if ($startDate && $endDate) {
            $query->whereBetween('check_in', [$startDate, $endDate]);
        }
        $count = $query->count();

        if ($count > 100) {
            $fileName = "exports/bookings_" . time() . ".xlsx";
            Excel::store(new BookingsExport($startDate, $endDate), $fileName, 'public');
            return back()->with('success', 'The bookings list is large. Generation queued in background. Check storage/exports/ folder.');
        }

        return Excel::download(new BookingsExport($startDate, $endDate), 'kitonga-bookings-registry.xlsx');
    }

    /**
     * Export Expenses Excel.
     */
    public function exportExpenses(Request $request)
    {
        abort_if(!auth()->user()->hasAnyPermission(['view_expenses', 'view_profit']), 403, 'Unauthorized to export expenses.');

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = Expense::query();
        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }
        $count = $query->count();

        if ($count > 100) {
            $fileName = "exports/expenses_" . time() . ".xlsx";
            Excel::store(new ExpensesExport($startDate, $endDate), $fileName, 'public');
            return back()->with('success', 'The expenses list is large. Generation queued in background. Check storage/exports/ folder.');
        }

        return Excel::download(new ExpensesExport($startDate, $endDate), 'kitonga-expenses-logs.xlsx');
    }

    /**
     * Download PDF summary report.
     */
    public function downloadPdfReport()
    {
        abort_if(!auth()->user()->hasAnyPermission(['view_revenue', 'view_profit']), 403, 'Unauthorized to download PDF reports.');

        $bookingsCount = Booking::count();
        $expensesCount = Expense::count();
        
        $accommodationRevenue = Booking::whereIn('status', ['confirmed', 'checked_in', 'checked_out'])->sum('total');
        $posRevenue = Sale::where('status', 'completed')->sum('total');
        $totalRevenue = $accommodationRevenue + $posRevenue;
        
        $totalExpenses = Expense::where('status', 'approved')->sum('amount');
        $netProfit = $totalRevenue - $totalExpenses;

        $pdf = Pdf::loadView('admin.reports.pdf', compact(
            'bookingsCount', 
            'expensesCount', 
            'accommodationRevenue',
            'posRevenue',
            'totalRevenue', 
            'totalExpenses', 
            'netProfit'
        ));

        return $pdf->download('kitonga-business-report.pdf');
    }
}
