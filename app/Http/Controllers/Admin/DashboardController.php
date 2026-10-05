<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\Product;
use App\Models\AccommodationUnit;
use App\Models\AvailabilityBlock;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        abort_if(!auth()->user()->hasAnyPermission(['view_bookings', 'view_revenue', 'view_profit']), 403, 'Unauthorized access to dashboard.');

        // 1. Resolve Date Range Filter
        $filter = $request->input('filter', 'last_30');
        $startDate = Carbon::now()->subDays(30)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

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
            case 'last_year':
                $startDate = Carbon::now()->subYear()->startOfYear()->startOfDay();
                $endDate = Carbon::now()->subYear()->endOfYear()->endOfDay();
                break;
            case 'custom':
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
                    $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
                }
                break;
        }

        // Days count in selected range
        $daysInRange = max(1, $startDate->diffInDays($endDate) + 1);

        // 2. Global Static Intervals (Cards Headlines)
        $todayStr = Carbon::today()->format('Y-m-d');
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();

        // Today's Revenue
        $todayAcc = Booking::whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
            ->where('check_in', $todayStr)
            ->sum('total');
        $todaySales = Sale::where('status', 'completed')
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->sum('total');
        $todayRevenue = $todayAcc + $todaySales;

        // 7-Day Revenue
        $sevenDaysAgo = Carbon::now()->subDays(7)->startOfDay();
        $sevenDaysAcc = Booking::whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
            ->whereBetween('check_in', [$sevenDaysAgo->format('Y-m-d'), $todayStr])
            ->sum('total');
        $sevenDaysSales = Sale::where('status', 'completed')
            ->whereBetween('created_at', [$sevenDaysAgo, Carbon::now()->endOfDay()])
            ->sum('total');
        $sevenDaysRevenue = $sevenDaysAcc + $sevenDaysSales;

        // 30-Day Revenue
        $thirtyDaysAgo = Carbon::now()->subDays(30)->startOfDay();
        $thirtyDaysAcc = Booking::whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
            ->whereBetween('check_in', [$thirtyDaysAgo->format('Y-m-d'), $todayStr])
            ->sum('total');
        $thirtyDaysSales = Sale::where('status', 'completed')
            ->whereBetween('created_at', [$thirtyDaysAgo, Carbon::now()->endOfDay()])
            ->sum('total');
        $thirtyDaysRevenue = $thirtyDaysAcc + $thirtyDaysSales;

        // Monthly Revenue (This Calendar Month)
        $monthStart = Carbon::now()->startOfMonth()->startOfDay();
        $monthEnd = Carbon::now()->endOfMonth()->endOfDay();
        $monthAcc = Booking::whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
            ->whereBetween('check_in', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])
            ->sum('total');
        $monthSales = Sale::where('status', 'completed')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->sum('total');
        $monthlyRevenue = $monthAcc + $monthSales;

        // 3. Filter-specific aggregates (for charts & profit summaries)
        $accommodationRevenue = Booking::whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
            ->whereBetween('check_in', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->sum('total');

        $salesRevenue = Sale::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $tourRevenue = $salesRevenue->where('category', 'tour')->sum('total');
        $barRevenue = $salesRevenue->where('category', 'bar')->sum('total');
        $productRevenue = $salesRevenue->where('category', 'product')->sum('total');
        $otherSalesRevenue = $salesRevenue->where('category', 'other')->sum('total');

        $totalRevenue = $accommodationRevenue + $salesRevenue->sum('total');

        $expenses = Expense::where('status', 'approved')
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->sum('amount');

        $netProfit = $totalRevenue - $expenses;

        // 4. Booking Statistics
        $totalBookings = Booking::whereBetween('created_at', [$startDate, $endDate])->count();
        $pendingBookings = Booking::where('status', 'pending')->count();

        // 5. Occupancy Calculations
        $totalUnits = AccommodationUnit::count();
        $totalPotentialNights = $totalUnits * $daysInRange;

        $blockedNights = AvailabilityBlock::where(function ($query) use ($startDate, $endDate) {
            $query->where('start_date', '<=', $endDate->format('Y-m-d'))
                  ->where('end_date', '>=', $startDate->format('Y-m-d'));
        })->get()->sum(function ($block) use ($startDate, $endDate) {
            $blockStart = Carbon::parse($block->start_date);
            $blockEnd = Carbon::parse($block->end_date);
            $start = $blockStart->greaterThan($startDate) ? $blockStart : $startDate->copy();
            $end = $blockEnd->lessThan($endDate) ? $blockEnd : $endDate->copy();
            return max(0, $start->diffInDays($end) + 1);
        });

        $availableSellableNights = max(0, $totalPotentialNights - $blockedNights);

        $occupiedNights = Booking::whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where('check_in', '<=', $endDate->format('Y-m-d'))
                      ->where('check_out', '>=', $startDate->format('Y-m-d'));
            })->get()->sum(function ($booking) use ($startDate, $endDate) {
                $bookingCheckIn = Carbon::parse($booking->check_in);
                $bookingCheckOut = Carbon::parse($booking->check_out);
                $start = $bookingCheckIn->greaterThan($startDate) ? $bookingCheckIn : $startDate->copy();
                $end = $bookingCheckOut->lessThan($endDate) ? $bookingCheckOut : $endDate->copy();
                return max(0, $start->diffInDays($end));
            });

        $occupancyPercentage = $availableSellableNights > 0 
            ? round(($occupiedNights / $availableSellableNights) * 100, 1) 
            : 0.00;

        $adr = $occupiedNights > 0 ? round($accommodationRevenue / $occupiedNights, 2) : 0.00;

        // 6. Outstanding balances
        $outstandingBalance = Booking::whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->get()
            ->sum(function ($b) {
                return max(0, $b->total - $b->payments->sum('amount'));
            });

        // 7. Stock alerts
        $lowStockCount = Product::where('active', true)
            ->whereColumn('stock', '<=', 'low_stock_threshold')
            ->count();

        // 8. Daily breakdowns (Revenue vs Expenses vs Profit trend)
        $dailyRevenue = [];
        $dailyExpenses = [];
        $dailyProfit = [];
        $dailyBookings = [];
        $chartLabels = [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $formattedDate = $date->format('Y-m-d');
            $chartLabels[] = $date->format('M d');

            // Daily Accommodation
            $dayAcc = Booking::whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
                ->where('check_in', $formattedDate)
                ->sum('total');

            // Daily Sales
            $daySales = Sale::where('status', 'completed')
                ->whereBetween('created_at', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
                ->sum('total');

            // Daily Expenses
            $dayExp = Expense::where('status', 'approved')
                ->where('date', $formattedDate)
                ->sum('amount');

            $rev = $dayAcc + $daySales;
            $dailyRevenue[] = $rev;
            $dailyExpenses[] = $dayExp;
            $dailyProfit[] = $rev - $dayExp;

            // Daily Bookings created
            $dailyBookings[] = Booking::whereBetween('created_at', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])->count();
        }

        // 9. Villa performance
        $villaPerformance = DB::table('bookings')
            ->join('accommodation_units', 'bookings.accommodation_unit_id', '=', 'accommodation_units.id')
            ->join('accommodation_types', 'accommodation_units.accommodation_type_id', '=', 'accommodation_types.id')
            ->whereIn('bookings.status', ['confirmed', 'checked_in', 'checked_out'])
            ->whereBetween('bookings.check_in', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->select('accommodation_types.name', DB::raw('SUM(bookings.total) as total_revenue'))
            ->groupBy('accommodation_types.id', 'accommodation_types.name')
            ->get();

        // 10. Top Products
        $topProducts = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->select('products.name', DB::raw('SUM(sale_items.quantity) as total_qty'))
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_qty', 'desc')
            ->take(5)
            ->get();

        // 11. Payment Methods
        $paymentMethods = DB::table('payments')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('method', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('method')
            ->get();

        // 12. Recent Bookings
        $recentBookings = Booking::with('customer', 'unit.type')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // 13. Real-Time Room & Villa Occupancy Grid (Live status: Occupied, Vacant, Arriving Today, Maintenance)
        $todayStr = Carbon::today()->format('Y-m-d');
        $liveRooms = AccommodationUnit::with([
            'type',
            'bookings' => function ($q) use ($todayStr) {
                $q->whereIn('status', ['confirmed', 'checked_in', 'pending'])
                  ->where('check_in', '<=', $todayStr)
                  ->where('check_out', '>=', $todayStr)
                  ->with('customer');
            },
            'blocks' => function ($q) use ($todayStr) {
                $q->where('start_date', '<=', $todayStr)
                  ->where('end_date', '>=', $todayStr);
            }
        ])->orderBy('name')->get()->map(function ($unit) use ($todayStr) {
            // Check if active in-house stay
            $activeStay = $unit->bookings->first(function ($b) use ($todayStr) {
                $checkIn = Carbon::parse($b->getRawOriginal('check_in'))->format('Y-m-d');
                $checkOut = Carbon::parse($b->getRawOriginal('check_out'))->format('Y-m-d');
                return $checkIn <= $todayStr && $checkOut > $todayStr;
            });

            // Check if arriving today
            $arrivingToday = null;
            if (!$activeStay) {
                $arrivingToday = $unit->bookings->first(function ($b) use ($todayStr) {
                    $checkIn = Carbon::parse($b->getRawOriginal('check_in'))->format('Y-m-d');
                    return $checkIn === $todayStr;
                });
            }

            // Check if blocked
            $activeBlock = $unit->blocks->first();

            $occupancyStatus = 'vacant';
            $statusLabel = 'Ipo Wazi (Vacant & Ready)';
            $guestInfo = null;

            if ($unit->status === 'maintenance' || $activeBlock) {
                $occupancyStatus = 'maintenance';
                $statusLabel = 'Marekebisho (Maintenance / Blocked)';
            } elseif ($activeStay) {
                $occupancyStatus = 'occupied';
                $statusLabel = $activeStay->status === 'checked_in' ? 'Ina Mgeni (Checked In)' : 'Imejaa (Occupied / Confirmed)';
                $guestInfo = [
                    'booking_id' => $activeStay->id,
                    'reference' => $activeStay->reference,
                    'guest_name' => $activeStay->customer?->name ?? 'Guest',
                    'guest_phone' => $activeStay->customer?->phone,
                    'check_in' => Carbon::parse($activeStay->getRawOriginal('check_in'))->format('d M Y'),
                    'check_out' => Carbon::parse($activeStay->getRawOriginal('check_out'))->format('d M Y'),
                    'nights_total' => $activeStay->duration_in_nights,
                    'guests_count' => $activeStay->guests_count,
                    'status' => $activeStay->status,
                    'balance' => (float) $activeStay->balance,
                    'id_type' => $activeStay->id_type ?? $activeStay->customer?->id_type,
                    'id_number' => $activeStay->id_number ?? $activeStay->customer?->id_number,
                    'has_id_document' => !empty($activeStay->id_document_path || $activeStay->customer?->id_document_path),
                ];
            } elseif ($arrivingToday) {
                $occupancyStatus = 'arriving_today';
                $statusLabel = 'Mgeni Anawasili Leo (Arriving Today)';
                $guestInfo = [
                    'booking_id' => $arrivingToday->id,
                    'reference' => $arrivingToday->reference,
                    'guest_name' => $arrivingToday->customer?->name ?? 'Guest',
                    'guest_phone' => $arrivingToday->customer?->phone,
                    'check_in' => Carbon::parse($arrivingToday->getRawOriginal('check_in'))->format('d M Y'),
                    'check_out' => Carbon::parse($arrivingToday->getRawOriginal('check_out'))->format('d M Y'),
                    'guests_count' => $arrivingToday->guests_count,
                    'status' => $arrivingToday->status,
                    'balance' => (float) $arrivingToday->balance,
                    'id_type' => $arrivingToday->id_type ?? $arrivingToday->customer?->id_type,
                    'id_number' => $arrivingToday->id_number ?? $arrivingToday->customer?->id_number,
                    'has_id_document' => !empty($arrivingToday->id_document_path || $arrivingToday->customer?->id_document_path),
                ];
            }

            return [
                'id' => $unit->id,
                'name' => $unit->name,
                'villa_type' => $unit->type?->name ?? 'Villa',
                'villa_type_id' => $unit->accommodation_type_id,
                'capacity' => $unit->type?->capacity ?? 2,
                'base_price' => (float) ($unit->type?->base_price ?? 0),
                'housekeeping_status' => $unit->housekeeping_status ?? 'clean',
                'occupancy_status' => $occupancyStatus,
                'status_label' => $statusLabel,
                'guest_info' => $guestInfo,
                'block_reason' => $activeBlock?->reason,
            ];
        });

        $roomSummary = [
            'total' => $liveRooms->count(),
            'occupied' => $liveRooms->where('occupancy_status', 'occupied')->count(),
            'vacant' => $liveRooms->where('occupancy_status', 'vacant')->count(),
            'arriving_today' => $liveRooms->where('occupancy_status', 'arriving_today')->count(),
            'maintenance' => $liveRooms->where('occupancy_status', 'maintenance')->count(),
        ];

        return Inertia::render('Admin/Dashboard', [
            'kpis' => [
                'today_revenue' => $todayRevenue,
                'seven_days_revenue' => $sevenDaysRevenue,
                'thirty_days_revenue' => $thirtyDaysRevenue,
                'monthly_revenue' => $monthlyRevenue,
                'total_revenue' => $totalRevenue,
                'accommodation_revenue' => $accommodationRevenue,
                'tour_revenue' => $tourRevenue,
                'bar_revenue' => $barRevenue,
                'product_revenue' => $productRevenue,
                'other_revenue' => $otherSalesRevenue,
                'expenses' => $expenses,
                'net_profit' => $netProfit,
                'total_bookings' => $totalBookings,
                'pending_bookings' => $pendingBookings,
                'occupancy_percentage' => $occupancyPercentage,
                'adr' => $adr,
                'outstanding_balance' => $outstandingBalance,
                'low_stock_count' => $lowStockCount,
            ],
            'charts' => [
                'labels' => $chartLabels,
                'revenue' => $dailyRevenue,
                'expenses' => $dailyExpenses,
                'profit' => $dailyProfit,
                'bookings' => $dailyBookings,
                'villa_performance' => $villaPerformance,
                'top_products' => $topProducts,
                'payment_methods' => $paymentMethods,
            ],
            'recent_bookings' => $recentBookings,
            'live_rooms' => $liveRooms,
            'room_summary' => $roomSummary,
            'filters' => [
                'active' => $filter,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ]
        ]);
    }
}
