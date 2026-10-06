<?php

namespace App\Services;

use App\Models\VisitorSession;
use App\Models\VisitorPageView;
use App\Models\VisitorEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VisitorAnalyticsService
{
    /**
     * Record or update an anonymous visitor session and pageview.
     */
    public function trackPageView(Request $request, array $data): array
    {
        $now = Carbon::now();
        $sessionId = trim($data['session_id'] ?? '');
        $visitorId = trim($data['visitor_id'] ?? '');

        if (empty($sessionId)) {
            $sessionId = 'kfv_s_' . Str::random(24);
        }
        if (empty($visitorId)) {
            $visitorId = 'kfv_v_' . Str::random(24);
        }

        $url = trim($data['url'] ?? $request->header('Referer') ?? '/');
        $pageTitle = trim($data['page_title'] ?? '');
        $routeName = trim($data['route_name'] ?? '');
        $rawReferrer = trim($data['referrer'] ?? $request->header('Referer') ?? '');
        
        $userAgent = (string) $request->userAgent();
        $ip = $request->ip();
        $ipHash = hash('sha256', $ip . config('app.key', 'kitonga_salt'));

        // Geolocation and Device detection
        $deviceInfo = $this->parseUserAgent($userAgent);
        $location = $this->detectLocation($request);
        $referrerInfo = $this->parseReferrer($rawReferrer);

        // Find or create session
        $session = VisitorSession::where('session_id', $sessionId)->first();

        if (!$session) {
            // Check if visitor has visited before (for is_new_visitor)
            $hasPastSessions = VisitorSession::where('visitor_id', $visitorId)->exists();

            $session = VisitorSession::create([
                'session_id' => $sessionId,
                'visitor_id' => $visitorId,
                'first_seen_at' => $now,
                'last_seen_at' => $now,
                'landing_page' => $url,
                'exit_page' => $url,
                'referrer' => $rawReferrer ?: 'Direct',
                'referrer_domain' => $referrerInfo['domain'],
                'utm_source' => $data['utm_source'] ?? $request->query('utm_source'),
                'utm_medium' => $data['utm_medium'] ?? $request->query('utm_medium'),
                'utm_campaign' => $data['utm_campaign'] ?? $request->query('utm_campaign'),
                'utm_content' => $data['utm_content'] ?? $request->query('utm_content'),
                'utm_term' => $data['utm_term'] ?? $request->query('utm_term'),
                'device_type' => $deviceInfo['device_type'],
                'browser' => $deviceInfo['browser'],
                'operating_system' => $deviceInfo['os'],
                'country' => $location['country'],
                'country_code' => $location['country_code'],
                'city' => $location['city'],
                'is_new_visitor' => !$hasPastSessions,
                'page_views_count' => 1,
                'duration_seconds' => 0,
                'ip_hash' => $ipHash,
            ]);
        } else {
            // Update existing session
            $firstSeen = $session->first_seen_at ?? $now;
            $duration = max(0, $now->diffInSeconds($firstSeen));

            $session->update([
                'last_seen_at' => $now,
                'exit_page' => $url,
                'page_views_count' => ($session->page_views_count ?? 1) + 1,
                'duration_seconds' => $duration,
            ]);
        }

        // Record individual pageview
        VisitorPageView::create([
            'visitor_session_id' => $session->id,
            'url' => $url,
            'route_name' => $routeName ?: null,
            'page_title' => $pageTitle ?: $url,
            'referrer' => $rawReferrer ?: null,
            'visited_at' => $now,
            'time_on_page' => 0,
        ]);

        return [
            'session_id' => $session->session_id,
            'visitor_id' => $session->visitor_id,
            'tracked' => true,
        ];
    }

    /**
     * Record a business conversion or interaction event.
     */
    public function trackEvent(Request $request, array $data): array
    {
        $sessionId = trim($data['session_id'] ?? '');
        $eventName = trim($data['event_name'] ?? 'custom_event');
        $eventData = $data['event_data'] ?? [];
        $pageUrl = trim($data['page_url'] ?? $request->header('Referer') ?? '');

        if (empty($sessionId)) {
            return ['success' => false, 'message' => 'Missing session ID'];
        }

        $session = VisitorSession::where('session_id', $sessionId)->first();
        if ($session) {
            $session->update(['last_seen_at' => Carbon::now()]);

            VisitorEvent::create([
                'visitor_session_id' => $session->id,
                'event_name' => $eventName,
                'event_data' => is_array($eventData) ? $eventData : ['raw' => $eventData],
                'page_url' => $pageUrl,
                'created_at' => Carbon::now(),
            ]);

            return ['success' => true, 'event' => $eventName];
        }

        return ['success' => false, 'message' => 'Session not found'];
    }

    /**
     * Parse User-Agent for Device, Browser, and OS.
     */
    public function parseUserAgent(?string $ua): array
    {
        if (empty($ua)) {
            return ['device_type' => 'desktop', 'browser' => 'Other', 'os' => 'Other'];
        }

        $uaLower = strtolower($ua);

        // 1. Device Type
        $deviceType = 'desktop';
        if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i', $ua)) {
            $deviceType = 'tablet';
        } elseif (preg_match('/(mobi|iphone|ipod|blackberry|opera mini|iemobile|mobile)/i', $ua)) {
            $deviceType = 'mobile';
        }

        // 2. Operating System
        $os = 'Other';
        if (str_contains($uaLower, 'windows')) $os = 'Windows';
        elseif (str_contains($uaLower, 'android')) $os = 'Android';
        elseif (str_contains($uaLower, 'iphone') || str_contains($uaLower, 'ipad') || str_contains($uaLower, 'ios')) $os = 'iOS';
        elseif (str_contains($uaLower, 'macintosh') || str_contains($uaLower, 'mac os')) $os = 'macOS';
        elseif (str_contains($uaLower, 'linux')) $os = 'Linux';
        elseif (str_contains($uaLower, 'cros')) $os = 'ChromeOS';

        // 3. Browser
        $browser = 'Other';
        if (str_contains($uaLower, 'edg') || str_contains($uaLower, 'edge')) $browser = 'Edge';
        elseif (str_contains($uaLower, 'opr') || str_contains($uaLower, 'opera')) $browser = 'Opera';
        elseif (str_contains($uaLower, 'chrome') || str_contains($uaLower, 'crios')) $browser = 'Chrome';
        elseif (str_contains($uaLower, 'firefox') || str_contains($uaLower, 'fxios')) $browser = 'Firefox';
        elseif (str_contains($uaLower, 'safari') && !str_contains($uaLower, 'chrome')) $browser = 'Safari';
        elseif (str_contains($uaLower, 'msie') || str_contains($uaLower, 'trident/7')) $browser = 'IE';

        return [
            'device_type' => $deviceType,
            'browser' => $browser,
            'os' => $os,
        ];
    }

    /**
     * Parse Referrer source.
     */
    public function parseReferrer(?string $ref): array
    {
        if (empty($ref) || strtolower($ref) === 'direct') {
            return ['domain' => 'Direct', 'source' => 'Direct'];
        }

        $host = parse_url($ref, PHP_URL_HOST);
        if (!$host) {
            return ['domain' => 'Direct', 'source' => 'Direct'];
        }

        $host = strtolower(preg_replace('/^www\./', '', $host));

        if (str_contains($host, 'google.')) return ['domain' => 'Google', 'source' => 'Search'];
        if (str_contains($host, 'instagram.')) return ['domain' => 'Instagram', 'source' => 'Social'];
        if (str_contains($host, 'facebook.') || str_contains($host, 'fb.com')) return ['domain' => 'Facebook', 'source' => 'Social'];
        if (str_contains($host, 'whatsapp.') || str_contains($host, 'wa.me')) return ['domain' => 'WhatsApp', 'source' => 'Social'];
        if (str_contains($host, 'tiktok.')) return ['domain' => 'TikTok', 'source' => 'Social'];
        if (str_contains($host, 'twitter.') || str_contains($host, 'x.com') || str_contains($host, 't.co')) return ['domain' => 'X (Twitter)', 'source' => 'Social'];
        if (str_contains($host, 'youtube.') || str_contains($host, 'youtu.be')) return ['domain' => 'YouTube', 'source' => 'Social'];
        if (str_contains($host, 'bing.')) return ['domain' => 'Bing', 'source' => 'Search'];
        if (str_contains($host, 'yahoo.')) return ['domain' => 'Yahoo', 'source' => 'Search'];
        if (str_contains($host, 'kitongafarm.com') || str_contains($host, 'localhost') || str_contains($host, '127.0.0.1')) {
            return ['domain' => 'Direct', 'source' => 'Direct'];
        }

        return ['domain' => $host, 'source' => 'Referral'];
    }

    /**
     * Approximate location detection via headers or fallback.
     */
    public function detectLocation(Request $request): array
    {
        // 1. Cloudflare header
        $countryCode = $request->header('CF-IPCountry') ?: $request->header('X-Country-Code') ?: 'TZ';
        $countryCode = strtoupper(substr($countryCode, 0, 2));

        $countryMap = [
            'TZ' => 'Tanzania',
            'KE' => 'Kenya',
            'UG' => 'Uganda',
            'RW' => 'Rwanda',
            'BI' => 'Burundi',
            'US' => 'United States',
            'GB' => 'United Kingdom',
            'DE' => 'Germany',
            'ZA' => 'South Africa',
            'FR' => 'France',
            'IT' => 'Italy',
            'AE' => 'United Arab Emirates',
            'IN' => 'India',
            'CA' => 'Canada',
            'AU' => 'Australia',
            'NL' => 'Netherlands',
            'CH' => 'Switzerland',
            'SE' => 'Sweden',
            'NO' => 'Norway',
            'CN' => 'China',
            'XX' => 'Unknown',
            'T1' => 'Tor / Proxy',
        ];

        $country = $countryMap[$countryCode] ?? ($countryCode ?: 'Tanzania');
        $city = $request->header('CF-IPCity') ?: ($countryCode === 'TZ' ? 'Iringa / Dar es Salaam' : null);

        return [
            'country' => $country,
            'country_code' => $countryCode,
            'city' => $city,
        ];
    }

    /**
     * Resolve Date Range boundaries.
     */
    public function resolveDateRange(string $period = '7d', ?string $customStart = null, ?string $customEnd = null): array
    {
        $now = Carbon::now();

        switch ($period) {
            case 'today':
                $start = $now->copy()->startOfDay();
                $end = $now->copy()->endOfDay();
                $label = 'Today';
                break;
            case 'yesterday':
                $start = $now->copy()->subDay()->startOfDay();
                $end = $now->copy()->subDay()->endOfDay();
                $label = 'Yesterday';
                break;
            case '30d':
                $start = $now->copy()->subDays(29)->startOfDay();
                $end = $now->copy()->endOfDay();
                $label = 'Last 30 Days';
                break;
            case 'this_month':
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfDay();
                $label = 'This Month';
                break;
            case 'last_month':
                $start = $now->copy()->subMonth()->startOfMonth();
                $end = $now->copy()->subMonth()->endOfMonth();
                $label = 'Last Month';
                break;
            case 'custom':
                $start = $customStart ? Carbon::parse($customStart)->startOfDay() : $now->copy()->subDays(6)->startOfDay();
                $end = $customEnd ? Carbon::parse($customEnd)->endOfDay() : $now->copy()->endOfDay();
                $label = $start->format('d M') . ' - ' . $end->format('d M Y');
                break;
            case '7d':
            default:
                $start = $now->copy()->subDays(6)->startOfDay();
                $end = $now->copy()->endOfDay();
                $label = 'Last 7 Days';
                break;
        }

        return ['start' => $start, 'end' => $end, 'label' => $label, 'period' => $period];
    }

    /**
     * Get Complete Analytics Dashboard Data.
     */
    public function getDashboardMetrics(string $period = '7d', ?string $customStart = null, ?string $customEnd = null): array
    {
        $range = $this->resolveDateRange($period, $customStart, $customEnd);
        $start = $range['start'];
        $end = $range['end'];

        // 1. Overview KPIs
        $sessionsQuery = VisitorSession::whereBetween('created_at', [$start, $end]);
        $pageViewsQuery = VisitorPageView::whereBetween('visited_at', [$start, $end]);
        $eventsQuery = VisitorEvent::whereBetween('created_at', [$start, $end]);

        $totalSessions = (clone $sessionsQuery)->count();
        $totalVisitors = (clone $sessionsQuery)->distinct('visitor_id')->count('visitor_id');
        $totalPageViews = (clone $pageViewsQuery)->count();
        $avgDurationSec = (clone $sessionsQuery)->avg('duration_seconds') ?: 0;
        
        // Single page visits for bounce rate calculation
        $singlePageSessions = (clone $sessionsQuery)->where('page_views_count', '<=', 1)->count();
        $bounceRate = $totalSessions > 0 ? round(($singlePageSessions / $totalSessions) * 100, 1) : 0;

        // Live visitors online right now (last 5 minutes)
        $liveVisitorsCount = VisitorSession::active(5)->count();
        $liveVisitorsList = VisitorSession::active(5)
            ->latest('last_seen_at')
            ->limit(10)
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'short_id' => '#' . substr(hash('crc32', $s->visitor_id), 0, 4),
                    'country' => $s->country,
                    'country_code' => $s->country_code,
                    'device_type' => $s->device_type,
                    'browser' => $s->browser,
                    'current_page' => $s->exit_page ?: $s->landing_page ?: '/',
                    'last_activity' => $s->last_seen_at ? $s->last_seen_at->format('H:i:s') : 'Just now',
                    'last_seen_ago' => $s->last_seen_at ? $s->last_seen_at->diffForHumans(null, true) . ' ago' : 'active',
                ];
            });

        // 2. Chart Trend Timeline
        $chartData = $this->buildChartTimeline($start, $end, $period);

        // 3. Top Pages
        $topPages = (clone $pageViewsQuery)
            ->select('url', 'page_title', DB::raw('count(*) as views_count'), DB::raw('count(distinct visitor_session_id) as unique_visitors'))
            ->groupBy('url', 'page_title')
            ->orderByDesc('views_count')
            ->limit(10)
            ->get()
            ->map(function ($p) use ($totalPageViews) {
                $pct = $totalPageViews > 0 ? round(($p->views_count / $totalPageViews) * 100, 1) : 0;
                return [
                    'url' => $p->url,
                    'title' => $this->cleanPageTitle($p->page_title, $p->url),
                    'views' => (int) $p->views_count,
                    'unique_visitors' => (int) $p->unique_visitors,
                    'percentage' => $pct,
                ];
            });

        // 4. Traffic Sources / Referrers
        $trafficSources = (clone $sessionsQuery)
            ->select('referrer_domain', DB::raw('count(*) as count'))
            ->groupBy('referrer_domain')
            ->orderByDesc('count')
            ->limit(8)
            ->get()
            ->map(function ($r) use ($totalSessions) {
                $domain = $r->referrer_domain ?: 'Direct';
                $pct = $totalSessions > 0 ? round(($r->count / $totalSessions) * 100, 1) : 0;
                return [
                    'source' => $domain,
                    'count' => (int) $r->count,
                    'percentage' => $pct,
                ];
            });

        // 5. Locations / Countries
        $locations = (clone $sessionsQuery)
            ->select('country', 'country_code', DB::raw('count(*) as count'))
            ->groupBy('country', 'country_code')
            ->orderByDesc('count')
            ->limit(8)
            ->get()
            ->map(function ($loc) use ($totalSessions) {
                $pct = $totalSessions > 0 ? round(($loc->count / $totalSessions) * 100, 1) : 0;
                return [
                    'country' => $loc->country ?: 'Tanzania',
                    'country_code' => $loc->country_code ?: 'TZ',
                    'count' => (int) $loc->count,
                    'percentage' => $pct,
                ];
            });

        // 6. Devices & OS & Browsers
        $devices = (clone $sessionsQuery)
            ->select('device_type', DB::raw('count(*) as count'))
            ->groupBy('device_type')
            ->orderByDesc('count')
            ->get()
            ->map(function ($d) use ($totalSessions) {
                $pct = $totalSessions > 0 ? round(($d->count / $totalSessions) * 100, 1) : 0;
                return [
                    'name' => ucfirst($d->device_type),
                    'count' => (int) $d->count,
                    'percentage' => $pct,
                ];
            });

        $browsers = (clone $sessionsQuery)
            ->select('browser', DB::raw('count(*) as count'))
            ->groupBy('browser')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(function ($b) use ($totalSessions) {
                $pct = $totalSessions > 0 ? round(($b->count / $totalSessions) * 100, 1) : 0;
                return [
                    'name' => $b->browser,
                    'count' => (int) $b->count,
                    'percentage' => $pct,
                ];
            });

        $operatingSystems = (clone $sessionsQuery)
            ->select('operating_system', DB::raw('count(*) as count'))
            ->groupBy('operating_system')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(function ($os) use ($totalSessions) {
                $pct = $totalSessions > 0 ? round(($os->count / $totalSessions) * 100, 1) : 0;
                return [
                    'name' => $os->operating_system,
                    'count' => (int) $os->count,
                    'percentage' => $pct,
                ];
            });

        // 7. Business Conversion Events
        $conversions = [
            'whatsapp_clicks' => (clone $eventsQuery)->where('event_name', 'whatsapp_clicked')->count(),
            'phone_clicks' => (clone $eventsQuery)->where('event_name', 'phone_clicked')->count(),
            'booking_starts' => (clone $eventsQuery)->where('event_name', 'booking_started')->count(),
            'consultation_clicks' => (clone $eventsQuery)->where('event_name', 'consultation_clicked')->count(),
            'total_conversions' => (clone $eventsQuery)->count(),
        ];

        // 8. Recent Visitor Sessions Stream
        $recentVisitors = VisitorSession::latest('last_seen_at')
            ->limit(15)
            ->get()
            ->map(function ($v) {
                return [
                    'id' => $v->id,
                    'short_id' => '#' . substr(hash('crc32', $v->visitor_id), 0, 4),
                    'country' => $v->country,
                    'country_code' => $v->country_code,
                    'device_type' => $v->device_type,
                    'browser' => $v->browser,
                    'operating_system' => $v->operating_system,
                    'referrer' => $v->referrer_domain ?: 'Direct',
                    'landing_page' => $v->landing_page ?: '/',
                    'exit_page' => $v->exit_page ?: '/',
                    'page_views' => $v->page_views_count,
                    'duration' => $this->formatDuration($v->duration_seconds),
                    'is_online' => $v->last_seen_at && $v->last_seen_at->gte(Carbon::now()->subMinutes(5)),
                    'time_ago' => $v->last_seen_at ? $v->last_seen_at->diffForHumans(null, true) . ' ago' : 'just now',
                ];
            });

        return [
            'overview' => [
                'total_visitors' => $totalVisitors,
                'total_page_views' => $totalPageViews,
                'total_sessions' => $totalSessions,
                'live_visitors' => $liveVisitorsCount,
                'avg_duration_formatted' => $this->formatDuration((int) $avgDurationSec),
                'avg_duration_seconds' => (int) $avgDurationSec,
                'bounce_rate' => $bounceRate,
            ],
            'chart' => $chartData,
            'live_visitors' => $liveVisitorsList,
            'top_pages' => $topPages,
            'traffic_sources' => $trafficSources,
            'locations' => $locations,
            'devices' => $devices,
            'browsers' => $browsers,
            'operating_systems' => $operatingSystems,
            'conversions' => $conversions,
            'recent_visitors' => $recentVisitors,
            'range' => $range,
        ];
    }

    /**
     * Build Chart Points for Line/Area Chart.
     */
    protected function buildChartTimeline(Carbon $start, Carbon $end, string $period): array
    {
        $points = [];
        $diffDays = $start->diffInDays($end);

        if ($period === 'today' || $period === 'yesterday' || $diffDays === 0) {
            // Hourly breakdown (00:00 to 23:00)
            for ($h = 0; $h < 24; $h++) {
                $hStart = $start->copy()->hour($h)->minute(0)->second(0);
                $hEnd = $start->copy()->hour($h)->minute(59)->second(59);

                $sessions = VisitorSession::whereBetween('created_at', [$hStart, $hEnd])->count();
                $pageViews = VisitorPageView::whereBetween('visited_at', [$hStart, $hEnd])->count();
                $visitors = VisitorSession::whereBetween('created_at', [$hStart, $hEnd])->distinct('visitor_id')->count('visitor_id');

                $points[] = [
                    'label' => sprintf('%02d:00', $h),
                    'sessions' => $sessions,
                    'page_views' => $pageViews,
                    'visitors' => $visitors,
                ];
            }
        } else {
            // Daily breakdown
            $current = $start->copy();
            while ($current->lte($end)) {
                $dStart = $current->copy()->startOfDay();
                $dEnd = $current->copy()->endOfDay();

                $sessions = VisitorSession::whereBetween('created_at', [$dStart, $dEnd])->count();
                $pageViews = VisitorPageView::whereBetween('visited_at', [$dStart, $dEnd])->count();
                $visitors = VisitorSession::whereBetween('created_at', [$dStart, $dEnd])->distinct('visitor_id')->count('visitor_id');

                $points[] = [
                    'label' => $current->format('D, d M'),
                    'short_label' => $current->format('d/m'),
                    'sessions' => $sessions,
                    'page_views' => $pageViews,
                    'visitors' => $visitors,
                ];

                $current->addDay();
            }
        }

        return $points;
    }

    /**
     * Format seconds to human mm:ss or hh:mm:ss.
     */
    public function formatDuration(int $seconds): string
    {
        if ($seconds < 60) return "{$seconds}s";
        $minutes = floor($seconds / 60);
        $remSeconds = $seconds % 60;
        if ($minutes < 60) return "{$minutes}m {$remSeconds}s";
        $hours = floor($minutes / 60);
        $remMinutes = $minutes % 60;
        return "{$hours}h {$remMinutes}m";
    }

    /**
     * Clean page titles for readable UI.
     */
    protected function cleanPageTitle(?string $title, string $url): string
    {
        if (!empty($title) && !str_starts_with($title, 'http')) {
            return Str::limit(str_replace([' | Kitonga Farm Villas', ' - Kitonga Farm Villas', 'Kitonga Farm Villas'], '', $title), 60);
        }

        $path = trim(parse_url($url, PHP_URL_PATH) ?? '/', '/');
        if (empty($path)) return 'Home Page';
        return Str::title(str_replace(['-', '_'], ' ', $path));
    }
}
