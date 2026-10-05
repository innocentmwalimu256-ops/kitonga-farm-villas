<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\InventoryMovement;
use App\Exports\BookingsExport;
use App\Exports\ExpensesExport;
use App\Exports\FarmingExport;
use App\Exports\ToursExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Show comprehensive reports dashboard.
     */
    public function index(Request $request)
    {
        abort_if(!auth()->user()->hasAnyPermission(['view_bookings', 'view_revenue', 'view_profit']), 403, 'Unauthorized access to reports center.');

        // 1. Date Range Filter
        $filter = $request->input('filter', 'today');
        $activeTab = $request->input('tab', 'guests'); // 'guests', 'tours', 'farming', 'financials'

        $startDate = Carbon::today()->startOfDay();
        $endDate = Carbon::today()->endOfDay();

        switch ($filter) {
            case 'today':
                $startDate = Carbon::today()->startOfDay();
                $endDate = Carbon::today()->endOfDay();
                break;
            case 'yesterday':
                $startDate = Carbon::yesterday()->startOfDay();
                $endDate = Carbon::yesterday()->endOfDay();
                break;
            case 'last_7':
                $startDate = Carbon::now()->subDays(7)->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                break;
            case 'this_month':
                $startDate = Carbon::now()->startOfMonth()->startOfDay();
                $endDate = Carbon::now()->endOfMonth()->endOfDay();
                break;
            case 'last_month':
                $startDate = Carbon::now()->subMonth()->startOfMonth()->startOfDay();
                $endDate = Carbon::now()->subMonth()->endOfMonth()->endOfDay();
                break;
            case 'this_year':
                $startDate = Carbon::now()->startOfYear()->startOfDay();
                $endDate = Carbon::now()->endOfYear()->endOfDay();
                break;
            case 'custom':
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
                    $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
                }
                break;
        }

        $startDateStr = $startDate->format('Y-m-d');
        $endDateStr = $endDate->format('Y-m-d');
        $todayStr = Carbon::today()->format('Y-m-d');

        // ─── 1. STAY RESERVATIONS & GUEST ARRIVALS DATA ──────────────────────────────
        $bookingsQuery = Booking::with(['customer', 'unit.type', 'payments'])
            ->where(function ($q) use ($startDateStr, $endDateStr) {
                // Bookings that check in within range OR overlap with range
                $q->whereBetween('check_in', [$startDateStr, $endDateStr])
                  ->orWhere(function ($sub) use ($startDateStr, $endDateStr) {
                      $sub->where('check_in', '<=', $endDateStr)
                          ->where('check_out', '>=', $startDateStr);
                  });
            })
            ->whereNotNull('accommodation_unit_id')
            ->orderBy('check_in', 'desc');

        $bookings = $bookingsQuery->get()->map(function ($b) use ($todayStr) {
            $rawCheckIn = Carbon::parse($b->getRawOriginal('check_in'))->format('Y-m-d');
            $rawCheckOut = Carbon::parse($b->getRawOriginal('check_out'))->format('Y-m-d');

            $arrivalStatus = 'scheduled';
            if ($rawCheckIn === $todayStr) {
                $arrivalStatus = 'arrived_today';
            } elseif ($rawCheckIn < $todayStr && $rawCheckOut > $todayStr) {
                $arrivalStatus = 'in_house';
            } elseif ($rawCheckOut === $todayStr) {
                $arrivalStatus = 'departed_today';
            }

            return [
                'id' => $b->id,
                'reference' => $b->reference,
                'customer_name' => $b->customer?->name ?? 'Guest',
                'customer_phone' => $b->customer?->phone ?? 'N/A',
                'customer_email' => $b->customer?->email ?? 'N/A',
                'id_type' => $b->id_type ?? $b->customer?->id_type ?? 'Not Provided',
                'id_number' => $b->id_number ?? $b->customer?->id_number ?? 'N/A',
                'has_id_document' => !empty($b->id_document_path || $b->customer?->id_document_path),
                'id_document_path' => $b->id_document_path || $b->customer?->id_document_path,
                'unit_name' => $b->unit?->name ?? 'Villa',
                'villa_type' => $b->unit?->type?->name ?? 'Accommodation',
                'check_in' => Carbon::parse($b->getRawOriginal('check_in'))->format('d M Y'),
                'check_out' => Carbon::parse($b->getRawOriginal('check_out'))->format('d M Y'),
                'raw_check_in' => $rawCheckIn,
                'raw_check_out' => $rawCheckOut,
                'nights' => $b->duration_in_nights,
                'guests_count' => $b->guests_count ?? 1,
                'subtotal' => (float) $b->subtotal,
                'discount' => (float) $b->discount,
                'tax' => (float) $b->tax,
                'total' => (float) $b->total,
                'amount_paid' => (float) $b->amount_paid,
                'balance' => (float) $b->balance,
                'status' => $b->status,
                'source' => $b->source,
                'arrival_status' => $arrivalStatus,
                'notes' => $b->notes,
                'created_at' => $b->created_at->format('d M Y H:i'),
            ];
        });

        // Guest Summary stats for selected range
        $guestStats = [
            'total_bookings' => $bookings->count(),
            'total_guests' => $bookings->sum('guests_count'),
            'arrivals_count' => $bookings->where('arrival_status', 'arrived_today')->count(),
            'in_house_count' => $bookings->where('arrival_status', 'in_house')->count(),
            'departures_count' => $bookings->where('arrival_status', 'departed_today')->count(),
            'total_revenue' => $bookings->sum('total'),
            'amount_collected' => $bookings->sum('amount_paid'),
            'outstanding_balance' => $bookings->sum('balance'),
            'id_verified_count' => $bookings->where('has_id_document', true)->count(),
        ];

        // ─── 2. FARM TOURS & DAY EXPERIENCES REPORT ──────────────────────────────────
        $tourBookings = Booking::with(['customer', 'items'])
            ->where(function ($q) use ($startDateStr, $endDateStr) {
                $q->whereBetween('check_in', [$startDateStr, $endDateStr]);
            })
            ->whereNull('accommodation_unit_id')
            ->orderBy('check_in', 'desc')
            ->get()->map(function ($t) {
                $item = $t->items->where('item_type', 'tour')->first();
                return [
                    'id' => $t->id,
                    'reference' => $t->reference,
                    'tour_name' => $item?->description_snapshot ?? 'Farm Tour Experience',
                    'date' => Carbon::parse($t->getRawOriginal('check_in'))->format('d M Y'),
                    'visitors_count' => $t->guests_count ?? 1,
                    'customer_name' => $t->customer?->name ?? 'Visitor',
                    'customer_phone' => $t->customer?->phone ?? 'N/A',
                    'id_type' => $t->id_type ?? $t->customer?->id_type,
                    'id_number' => $t->id_number ?? $t->customer?->id_number,
                    'has_id_document' => !empty($t->id_document_path || $t->customer?->id_document_path),
                    'total' => (float) $t->total,
                    'amount_paid' => (float) $t->amount_paid,
                    'balance' => (float) $t->balance,
                    'status' => $t->status,
                    'notes' => $t->notes,
                    'created_at' => $t->created_at->format('d M Y H:i'),
                ];
            });

        $tourStats = [
            'total_tours' => $tourBookings->count(),
            'total_visitors' => $tourBookings->sum('visitors_count'),
            'total_revenue' => $tourBookings->sum('total'),
            'amount_collected' => $tourBookings->sum('amount_paid'),
        ];

        // ─── 3. FARM AGRICULTURE, HARVEST & INVENTORY MOVEMENTS ─────────────────────
        // Inventory additions & harvest logs
        $farmingMovements = InventoryMovement::with(['product.category', 'creator'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get()->map(function ($m) {
                return [
                    'id' => $m->id,
                    'date' => $m->created_at->format('d M Y H:i'),
                    'product_name' => $m->product?->name ?? 'Farm Item',
                    'category' => $m->product?->category?->name ?? 'Agriculture',
                    'type' => $m->type, // purchase, harvest/addition, adjustment, loss/spoilage, sale
                    'quantity' => (float) $m->quantity,
                    'unit' => $m->product?->unit ?? 'unit',
                    'reason' => $m->reason ?? 'Harvest & Stock Operation',
                    'recorded_by' => $m->creator?->name ?? 'Farm Manager',
                ];
            });

        // Farm Produce Sales (Eggs, Honey, Seedlings, Milk, Vegetables)
        $farmProduceSales = SaleItem::with(['product.category', 'sale'])
            ->whereHas('sale', function ($sq) use ($startDate, $endDate) {
                $sq->where('status', 'completed')
                   ->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->whereHas('product.category', function ($cq) {
                $cq->where('name', 'like', '%produce%')
                   ->orWhere('name', 'like', '%farm%')
                   ->orWhere('name', 'like', '%harvest%')
                   ->orWhere('name', 'like', '%seedling%');
            })
            ->get()->map(function ($si) {
                return [
                    'id' => $si->id,
                    'date' => $si->created_at->format('d M Y H:i'),
                    'product_name' => $si->product?->name ?? $si->description,
                    'category' => $si->product?->category?->name ?? 'Farm Produce',
                    'quantity' => (float) $si->quantity,
                    'unit_price' => (float) $si->unit_price,
                    'total' => (float) $si->total,
                ];
            });

        // Current Live Farm Inventory Snapshot
        $farmInventory = Product::with('category')
            ->where('active', true)
            ->orderBy('name')
            ->get()->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'category' => $p->category?->name ?? 'General',
                    'stock' => (float) $p->stock,
                    'unit' => $p->unit ?? 'unit',
                    'cost_price' => (float) $p->cost_price,
                    'selling_price' => (float) $p->selling_price,
                    'stock_value' => (float) ($p->stock * $p->selling_price),
                    'is_low_stock' => $p->stock <= $p->low_stock_threshold,
                ];
            });

        $farmingStats = [
            'harvest_actions_count' => $farmingMovements->whereIn('type', ['purchase', 'harvest', 'addition'])->count(),
            'total_units_harvested' => $farmingMovements->whereIn('type', ['purchase', 'harvest', 'addition'])->sum('quantity'),
            'spoilage_losses_count' => $farmingMovements->where('type', 'loss')->sum('quantity'),
            'produce_sales_revenue' => $farmProduceSales->sum('total'),
            'total_farm_stock_valuation' => $farmInventory->sum('stock_value'),
        ];

        // ─── 4. FINANCIAL REVENUES VS EXPENSES & NET PROFIT ─────────────────────────
        $accRevenue = Booking::whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
            ->whereBetween('check_in', [$startDateStr, $endDateStr])
            ->whereNotNull('accommodation_unit_id')
            ->sum('total');

        $tourRev = Booking::whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
            ->whereBetween('check_in', [$startDateStr, $endDateStr])
            ->whereNull('accommodation_unit_id')
            ->sum('total');

        $posSales = Sale::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $barRevenue = $posSales->where('category', 'bar')->sum('total');
        $produceRevenue = $posSales->where('category', 'product')->sum('total');
        $otherRevenue = $posSales->where('category', 'other')->sum('total');
        $totalRevenue = $accRevenue + $tourRev + $posSales->sum('total');

        $expenses = Expense::with(['category', 'creator'])
            ->where('status', 'approved')
            ->whereBetween('date', [$startDateStr, $endDateStr])
            ->get();

        $totalExpenses = $expenses->sum('amount');
        $netProfit = $totalRevenue - $totalExpenses;

        $financialSummary = [
            'accommodation_revenue' => (float) $accRevenue,
            'tour_revenue' => (float) $tourRev,
            'bar_revenue' => (float) $barRevenue,
            'produce_revenue' => (float) $produceRevenue,
            'other_revenue' => (float) $otherRevenue,
            'total_revenue' => (float) $totalRevenue,
            'total_expenses' => (float) $totalExpenses,
            'net_profit' => (float) $netProfit,
            'expenses_list' => $expenses->map(function ($e) {
                return [
                    'id' => $e->id,
                    'date' => Carbon::parse($e->date)->format('d M Y'),
                    'category' => $e->category?->name ?? 'General',
                    'description' => $e->description,
                    'amount' => (float) $e->amount,
                    'recipient' => $e->recipient,
                    'recorded_by' => $e->creator?->name ?? 'Staff',
                ];
            }),
        ];

        return Inertia::render('Admin/Reports/Index', [
            'active_tab' => $activeTab,
            'filters' => [
                'active' => $filter,
                'start_date' => $startDateStr,
                'end_date' => $endDateStr,
                'label' => $startDate->format('d M Y') . ' — ' . $endDate->format('d M Y'),
            ],
            'guest_report' => [
                'stats' => $guestStats,
                'list' => $bookings,
            ],
            'tour_report' => [
                'stats' => $tourStats,
                'list' => $tourBookings,
            ],
            'farming_report' => [
                'stats' => $farmingStats,
                'movements' => $farmingMovements,
                'sales' => $farmProduceSales,
                'inventory' => $farmInventory,
            ],
            'financial_report' => $financialSummary,
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

        return Excel::download(new BookingsExport($startDate, $endDate), 'kitonga-guest-bookings-report-' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export Tours Excel.
     */
    public function exportTours(Request $request)
    {
        abort_if(!auth()->user()->hasAnyPermission(['view_bookings', 'view_revenue']), 403, 'Unauthorized to export tours.');

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        return Excel::download(new ToursExport($startDate, $endDate), 'kitonga-farm-tours-report-' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export Farming Production & Harvest Excel.
     */
    public function exportFarming(Request $request)
    {
        abort_if(!auth()->user()->hasAnyPermission(['view_revenue', 'manage_inventory']), 403, 'Unauthorized to export farming logs.');

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        return Excel::download(new FarmingExport($startDate, $endDate), 'kitonga-farming-harvest-report-' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export Expenses Excel.
     */
    public function exportExpenses(Request $request)
    {
        abort_if(!auth()->user()->hasAnyPermission(['view_expenses', 'view_profit']), 403, 'Unauthorized to export expenses.');

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        return Excel::download(new ExpensesExport($startDate, $endDate), 'kitonga-expenses-report-' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Download PDF summary report.
     */
    public function downloadPdfReport(Request $request)
    {
        abort_if(!auth()->user()->hasAnyPermission(['view_revenue', 'view_profit']), 403, 'Unauthorized to download PDF reports.');

        $startDate = $request->input('start_date', Carbon::today()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::today()->format('Y-m-d'));

        $bookings = Booking::with(['customer', 'unit.type'])
            ->whereBetween('check_in', [$startDate, $endDate])
            ->get();

        $bookingsCount = $bookings->count();
        $guestsCount = $bookings->sum('guests_count');
        $accommodationRevenue = $bookings->whereNotNull('accommodation_unit_id')->sum('total');
        $tourRevenue = $bookings->whereNull('accommodation_unit_id')->sum('total');

        $posRevenue = Sale::where('status', 'completed')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->sum('total');

        $totalRevenue = $accommodationRevenue + $tourRevenue + $posRevenue;
        
        $expenses = Expense::where('status', 'approved')
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $expensesCount = $expenses->count();
        $totalExpenses = $expenses->sum('amount');
        $netProfit = $totalRevenue - $totalExpenses;

        $pdf = Pdf::loadView('admin.reports.pdf', compact(
            'startDate',
            'endDate',
            'bookingsCount', 
            'guestsCount',
            'expensesCount', 
            'accommodationRevenue',
            'tourRevenue',
            'posRevenue',
            'totalRevenue', 
            'totalExpenses', 
            'netProfit'
        ));

        return $pdf->download('kitonga-executive-report-' . date('Y-m-d') . '.pdf');
    }
}
