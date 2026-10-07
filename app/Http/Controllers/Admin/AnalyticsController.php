<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\VisitorAnalyticsService;
use App\Models\VisitorSession;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsController extends Controller
{
    protected VisitorAnalyticsService $analyticsService;

    public function __construct(VisitorAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Display the main Admin Analytics Dashboard.
     */
    public function index(Request $request)
    {
        abort_if(!auth()->user()->hasAnyPermission(['view_bookings', 'manage_settings', 'view_revenue']), 403, 'Unauthorized access to analytics desk.');

        $period = $request->input('period', '7d');
        $startDate = $request->input('start_date') ?: $request->input('from');
        $endDate = $request->input('end_date') ?: $request->input('to');

        $analytics = $this->analyticsService->getDashboardMetrics($period, $startDate, $endDate);

        return Inertia::render('Admin/Analytics/Index', [
            'overview' => $analytics['overview'] ?? [],
            'chartData' => $analytics['chart_data'] ?? [],
            'topPages' => $analytics['top_pages'] ?? [],
            'referrers' => $analytics['traffic_sources'] ?? [],
            'locations' => $analytics['locations'] ?? [],
            'devices' => $analytics['devices'] ?? [],
            'browsers' => $analytics['browsers'] ?? [],
            'operatingSystems' => $analytics['operating_systems'] ?? [],
            'events' => $analytics['events'] ?? [],
            'recentSessions' => $analytics['recent_sessions'] ?? $analytics['recent_visitors'] ?? [],
            'liveVisitors' => $analytics['live_visitors'] ?? [],
            'analytics' => $analytics,
            'filters' => [
                'period' => $period,
                'from' => $startDate ?? ($analytics['range']['start'] ?? ''),
                'to' => $endDate ?? ($analytics['range']['end'] ?? ''),
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    /**
     * Live active visitors endpoint for real-time polling.
     */
     public function live()
     {
         abort_if(!auth()->user()->hasAnyPermission(['view_bookings', 'manage_settings', 'view_revenue']), 403, 'Unauthorized.');

         if (!\Illuminate\Support\Facades\Schema::hasTable('visitor_sessions')) {
             return response()->json([
                 'count' => 0,
                 'live_count' => 0,
                 'visitors' => [],
                 'live_visitors' => [],
             ]);
         }

         $activeWindowMinutes = 10;
         $liveCount = VisitorSession::active($activeWindowMinutes)->count();
         $liveList = VisitorSession::active($activeWindowMinutes)
             ->latest('last_seen_at')
             ->limit(25)
             ->get()
             ->map(function ($s) {
                 return [
                     'id' => $s->id,
                     'session_id' => $s->session_id,
                     'short_id' => '#' . substr(hash('crc32', $s->visitor_id), 0, 4),
                     'visitor_label' => '#' . substr(hash('crc32', $s->visitor_id), 0, 4),
                     'country' => $s->country ?: 'Tanzania',
                     'country_name' => $s->country ?: 'Tanzania',
                     'country_code' => $s->country_code ?: 'TZ',
                     'device' => ucfirst($s->device_type ?: 'mobile'),
                     'device_type' => ucfirst($s->device_type ?: 'mobile'),
                     'browser' => $s->browser ?: 'Chrome',
                     'current_page' => $s->exit_page ?: $s->landing_page ?: '/',
                     'last_activity' => $s->last_seen_at ? $s->last_seen_at->format('H:i:s') : 'Just now',
                     'last_seen_ago' => $s->last_seen_at ? $s->last_seen_at->diffForHumans(null, true) . ' ago' : 'active',
                 ];
             });

         return response()->json([
             'count' => $liveCount,
             'live_count' => $liveCount,
             'visitors' => $liveList,
             'live_visitors' => $liveList,
         ]);
     }

    /**
     * Export analytics data to CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        abort_if(!auth()->user()->hasAnyPermission(['view_bookings', 'manage_settings', 'view_revenue']), 403, 'Unauthorized.');

        $period = $request->input('period', '7d');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $range = $this->analyticsService->resolveDateRange($period, $startDate, $endDate);
        $sessions = VisitorSession::whereBetween('created_at', [$range['start'], $range['end']])
            ->latest('created_at')
            ->get();

        $fileName = 'kitonga-website-analytics-' . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($sessions) {
            $handle = fopen('php://output', 'w');
            
            // CSV Header
            fputcsv($handle, [
                'Session ID',
                'Visitor Short ID',
                'First Seen',
                'Last Seen',
                'Duration',
                'Pages Viewed',
                'Landing Page',
                'Exit Page',
                'Referrer Domain',
                'Device Type',
                'Operating System',
                'Browser',
                'Country',
                'UTM Source',
                'UTM Campaign'
            ]);

            foreach ($sessions as $s) {
                fputcsv($handle, [
                    $s->session_id,
                    '#' . substr(hash('crc32', $s->visitor_id), 0, 4),
                    $s->first_seen_at ? $s->first_seen_at->toDateTimeString() : '',
                    $s->last_seen_at ? $s->last_seen_at->toDateTimeString() : '',
                    $this->analyticsService->formatDuration($s->duration_seconds),
                    $s->page_views_count,
                    $s->landing_page,
                    $s->exit_page,
                    $s->referrer_domain ?: 'Direct',
                    $s->device_type,
                    $s->operating_system,
                    $s->browser,
                    $s->country,
                    $s->utm_source ?: '-',
                    $s->utm_campaign ?: '-',
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}
