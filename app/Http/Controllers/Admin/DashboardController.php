<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\Product;
use App\Models\AccommodationUnit;
use App\Models\AvailabilityBlock;
use App\Models\ConsultationRequest;
use App\Models\Payment;
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

        // 2. Revenue calculation helpers across all streams:
        //    - Villa Accommodation: Bookings with accommodation units
        //    - Farm Tours & Experiences: Bookings with tours or POS tour sales
        //    - Mr. Kitonga Mentorship: Verified/Confirmed consultation sessions
        //    - POS Sales: Bar, Farm Produce, etc.

        $calcVillaRevenue = function ($from, $to, $fromStr, $toStr) {
            return (float) Booking::whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
                ->whereNotNull('accommodation_unit_id')
                ->where('reference', 'not like', 'KTG-EXP%')
                ->where(function ($q) use ($from, $to, $fromStr, $toStr) {
                    $q->whereBetween('check_in', [$fromStr, $toStr])
                      ->orWhereBetween('created_at', [$from, $to])
                      ->orWhereBetween('updated_at', [$from, $to])
                      ->orWhereHas('payments', function ($pq) use ($from, $to) {
                          $pq->where('status', 'completed')
                             ->whereBetween('created_at', [$from, $to]);
                      });
                })->sum('total');
        };

        $calcTourRevenue = function ($from, $to, $fromStr, $toStr) {
            $tourBookings = (float) Booking::whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
                ->where(function ($q) {
                    $q->whereNull('accommodation_unit_id')
                      ->orWhere('source', 'online_experience')
                      ->orWhere('reference', 'like', 'KTG-EXP%');
                })
                ->where(function ($q) use ($from, $to, $fromStr, $toStr) {
                    $q->whereBetween('check_in', [$fromStr, $toStr])
                      ->orWhereBetween('created_at', [$from, $to])
                      ->orWhereBetween('updated_at', [$from, $to])
                      ->orWhereHas('payments', function ($pq) use ($from, $to) {
                          $pq->where('status', 'completed')
                             ->whereBetween('created_at', [$from, $to]);
                      });
                })->sum('total');

            $tourSales = (float) Sale::where('status', 'completed')
                ->where('category', 'tour')
                ->whereBetween('created_at', [$from, $to])
                ->sum('total');

            return $tourBookings + $tourSales;
        };

        $calcConsultationRevenue = function ($from, $to, $fromStr, $toStr) {
            return (float) ConsultationRequest::whereIn('status', ['payment_verified', 'confirmed', 'completed'])
                ->where(function ($q) use ($from, $to, $fromStr, $toStr) {
                    $q->whereBetween('created_at', [$from, $to])
                      ->orWhereBetween('preferred_date', [$fromStr, $toStr])
                      ->orWhereBetween('confirmed_date_time', [$from, $to]);
                })->sum('fee');
        };

        $calcPosSales = function ($from, $to) {
            return (float) Sale::where('status', 'completed')
                ->whereBetween('created_at', [$from, $to])
                ->where('category', '!=', 'tour')
                ->sum('total');
        };

        // 3. Global Static Intervals (Cards Headlines)
        $todayStr = Carbon::today()->format('Y-m-d');
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();
        $upcomingLimitStr = Carbon::now()->addDays(60)->format('Y-m-d');

        // Today's Revenue
        $todayVilla = $calcVillaRevenue($todayStart, $todayEnd, $todayStr, $upcomingLimitStr);
        $todayTour = $calcTourRevenue($todayStart, $todayEnd, $todayStr, $upcomingLimitStr);
        $todayConsult = $calcConsultationRevenue($todayStart, $todayEnd, $todayStr, $upcomingLimitStr);
        $todaySales = $calcPosSales($todayStart, $todayEnd);
        $todayRevenue = $todayVilla + $todayTour + $todayConsult + $todaySales;

        // 7-Day Revenue
        $sevenDaysAgo = Carbon::now()->subDays(7)->startOfDay();
        $sevenDaysVilla = $calcVillaRevenue($sevenDaysAgo, Carbon::now()->endOfDay(), $sevenDaysAgo->format('Y-m-d'), $upcomingLimitStr);
        $sevenDaysTour = $calcTourRevenue($sevenDaysAgo, Carbon::now()->endOfDay(), $sevenDaysAgo->format('Y-m-d'), $upcomingLimitStr);
        $sevenDaysConsult = $calcConsultationRevenue($sevenDaysAgo, Carbon::now()->endOfDay(), $sevenDaysAgo->format('Y-m-d'), $upcomingLimitStr);
        $sevenDaysSales = $calcPosSales($sevenDaysAgo, Carbon::now()->endOfDay());
        $sevenDaysRevenue = $sevenDaysVilla + $sevenDaysTour + $sevenDaysConsult + $sevenDaysSales;

        // 30-Day Revenue
        $thirtyDaysAgo = Carbon::now()->subDays(30)->startOfDay();
        $thirtyDaysVilla = $calcVillaRevenue($thirtyDaysAgo, Carbon::now()->endOfDay(), $thirtyDaysAgo->format('Y-m-d'), $upcomingLimitStr);
        $thirtyDaysTour = $calcTourRevenue($thirtyDaysAgo, Carbon::now()->endOfDay(), $thirtyDaysAgo->format('Y-m-d'), $upcomingLimitStr);
        $thirtyDaysConsult = $calcConsultationRevenue($thirtyDaysAgo, Carbon::now()->endOfDay(), $thirtyDaysAgo->format('Y-m-d'), $upcomingLimitStr);
        $thirtyDaysSales = $calcPosSales($thirtyDaysAgo, Carbon::now()->endOfDay());
        $thirtyDaysRevenue = $thirtyDaysVilla + $thirtyDaysTour + $thirtyDaysConsult + $thirtyDaysSales;

        // Monthly Revenue (This Calendar Month)
        $monthStart = Carbon::now()->startOfMonth()->startOfDay();
        $monthEnd = Carbon::now()->endOfMonth()->endOfDay();
        $monthVilla = $calcVillaRevenue($monthStart, $monthEnd, $monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d'));
        $monthTour = $calcTourRevenue($monthStart, $monthEnd, $monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d'));
        $monthConsult = $calcConsultationRevenue($monthStart, $monthEnd, $monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d'));
        $monthSales = $calcPosSales($monthStart, $monthEnd);
        $monthlyRevenue = $monthVilla + $monthTour + $monthConsult + $monthSales;

        // 4. Filter-specific aggregates (for charts & profit summaries)
        $startDateStr = $startDate->format('Y-m-d');
        $endDateStr = $endDate->format('Y-m-d');

        $accommodationRevenue = $calcVillaRevenue($startDate, $endDate, $startDateStr, $upcomingLimitStr);
        $tourRevenue = $calcTourRevenue($startDate, $endDate, $startDateStr, $upcomingLimitStr);
        $consultationRevenue = $calcConsultationRevenue($startDate, $endDate, $startDateStr, $upcomingLimitStr);

        $salesRevenue = Sale::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $barRevenue = (float) $salesRevenue->where('category', 'bar')->sum('total');
        $productRevenue = (float) $salesRevenue->where('category', 'product')->sum('total');
        $otherSalesRevenue = (float) $salesRevenue->where('category', 'other')->sum('total');

        $totalRevenue = $accommodationRevenue + $tourRevenue + $consultationRevenue + $barRevenue + $productRevenue + $otherSalesRevenue;

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
            $dayStart = $date->copy()->startOfDay();
            $dayEnd = $date->copy()->endOfDay();
            $chartLabels[] = $date->format('M d');

            // Daily Villa
            $dayAcc = (float) Booking::whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
                ->whereNotNull('accommodation_unit_id')
                ->where('reference', 'not like', 'KTG-EXP%')
                ->where(function ($q) use ($dayStart, $dayEnd, $formattedDate) {
                    $q->where('check_in', $formattedDate)
                      ->orWhereBetween('created_at', [$dayStart, $dayEnd])
                      ->orWhereHas('payments', function ($pq) use ($dayStart, $dayEnd) {
                          $pq->where('status', 'completed')->whereBetween('created_at', [$dayStart, $dayEnd]);
                      });
                })->sum('total');

            // Daily Tour
            $dayTour = (float) Booking::whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
                ->where(function ($q) {
                    $q->whereNull('accommodation_unit_id')
                      ->orWhere('source', 'online_experience')
                      ->orWhere('reference', 'like', 'KTG-EXP%');
                })
                ->where(function ($q) use ($dayStart, $dayEnd, $formattedDate) {
                    $q->where('check_in', $formattedDate)
                      ->orWhereBetween('created_at', [$dayStart, $dayEnd])
                      ->orWhereHas('payments', function ($pq) use ($dayStart, $dayEnd) {
                          $pq->where('status', 'completed')->whereBetween('created_at', [$dayStart, $dayEnd]);
                      });
                })->sum('total')
                + (float) Sale::where('status', 'completed')->where('category', 'tour')->whereBetween('created_at', [$dayStart, $dayEnd])->sum('total');

            // Daily Consultation
            $dayConsult = (float) ConsultationRequest::whereIn('status', ['payment_verified', 'confirmed', 'completed'])
                ->where(function ($q) use ($dayStart, $dayEnd, $formattedDate) {
                    $q->where('preferred_date', $formattedDate)
                      ->orWhereBetween('created_at', [$dayStart, $dayEnd]);
                })->sum('fee');

            // Daily POS Sales (excluding tours)
            $daySales = (float) Sale::where('status', 'completed')
                ->where('category', '!=', 'tour')
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->sum('total');

            // Daily Expenses
            $dayExp = (float) Expense::where('status', 'approved')
                ->where('date', $formattedDate)
                ->sum('amount');

            $rev = $dayAcc + $dayTour + $dayConsult + $daySales;
            $dailyRevenue[] = $rev;
            $dailyExpenses[] = $dayExp;
            $dailyProfit[] = $rev - $dayExp;

            // Daily Bookings created
            $dailyBookings[] = Booking::whereBetween('created_at', [$dayStart, $dayEnd])->count();
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
                'consultation_revenue' => $consultationRevenue,
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
                'live_visitors_count' => \Illuminate\Support\Facades\Schema::hasTable('visitor_sessions') ? \App\Models\VisitorSession::active(10)->count() : 0,
                'total_website_visitors' => \Illuminate\Support\Facades\Schema::hasTable('visitor_sessions') ? \App\Models\VisitorSession::distinct('visitor_id')->count('visitor_id') : 0,
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
