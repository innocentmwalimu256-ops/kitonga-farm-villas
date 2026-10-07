<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VisitorAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AnalyticsTrackingController extends Controller
{
    protected VisitorAnalyticsService $analyticsService;

    public function __construct(VisitorAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Record pageview and session heartbeat asynchronously.
     */
    public function trackPageView(Request $request)
    {
        // Rate limiting: 240 pageviews/minute per IP
        $key = 'analytics_pv:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 240)) {
            return response()->json(['tracked' => false, 'rate_limited' => true], 429);
        }
        RateLimiter::hit($key, 60);

        // Safe robust input extraction
        $raw = $request->json()->all();
        if (empty($raw)) {
            $raw = $request->all();
        }
        if (empty($raw)) {
            $rawContent = file_get_contents('php://input');
            if (!empty($rawContent)) {
                $raw = json_decode($rawContent, true) ?: [];
            }
        }

        $payload = [
            'session_id' => substr((string) ($raw['session_id'] ?? ''), 0, 64),
            'visitor_id' => substr((string) ($raw['visitor_id'] ?? ''), 0, 64),
            'url' => substr((string) ($raw['url'] ?? $raw['path'] ?? $request->header('Referer') ?? '/'), 0, 2000),
            'path' => substr((string) ($raw['path'] ?? $raw['url'] ?? '/'), 0, 2000),
            'route_name' => substr((string) ($raw['route_name'] ?? ''), 0, 100),
            'title' => substr((string) ($raw['title'] ?? $raw['page_title'] ?? ''), 0, 255),
            'page_title' => substr((string) ($raw['page_title'] ?? $raw['title'] ?? ''), 0, 255),
            'referrer' => substr((string) ($raw['referrer'] ?? $request->header('Referer') ?? ''), 0, 2000),
            'screen_width' => isset($raw['screen_width']) ? (int) $raw['screen_width'] : null,
            'screen_height' => isset($raw['screen_height']) ? (int) $raw['screen_height'] : null,
            'utm_source' => substr((string) ($raw['utm_source'] ?? ''), 0, 100),
            'utm_medium' => substr((string) ($raw['utm_medium'] ?? ''), 0, 100),
            'utm_campaign' => substr((string) ($raw['utm_campaign'] ?? ''), 0, 100),
            'utm_content' => substr((string) ($raw['utm_content'] ?? ''), 0, 100),
            'utm_term' => substr((string) ($raw['utm_term'] ?? ''), 0, 100),
        ];

        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('visitor_sessions')) {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            }

            $result = $this->analyticsService->trackPageView($request, $payload);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['tracked' => false, 'error' => $e->getMessage()], 200);
        }
    }

    /**
     * Record active session heartbeat.
     */
    public function heartbeat(Request $request)
    {
        $raw = $request->json()->all() ?: $request->all();
        if (empty($raw)) {
            $rawContent = file_get_contents('php://input');
            if (!empty($rawContent)) {
                $raw = json_decode($rawContent, true) ?: [];
            }
        }

        $payload = [
            'session_id' => substr((string) ($raw['session_id'] ?? ''), 0, 64),
            'path' => substr((string) ($raw['path'] ?? $raw['url'] ?? '/'), 0, 2000),
            'url' => substr((string) ($raw['url'] ?? $raw['path'] ?? '/'), 0, 2000),
            'time_spent' => isset($raw['time_spent']) ? (int) $raw['time_spent'] : 0,
        ];

        if (empty($payload['session_id'])) {
            return response()->json(['success' => false, 'message' => 'Missing session_id'], 200);
        }

        try {
            $result = $this->analyticsService->recordHeartbeat($request, $payload);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 200);
        }
    }

    /**
     * Record interaction or conversion event.
     */
    public function trackEvent(Request $request)
    {
        $raw = $request->json()->all() ?: $request->all();
        if (empty($raw)) {
            $rawContent = file_get_contents('php://input');
            if (!empty($rawContent)) {
                $raw = json_decode($rawContent, true) ?: [];
            }
        }

        $payload = [
            'session_id' => substr((string) ($raw['session_id'] ?? ''), 0, 64),
            'visitor_id' => substr((string) ($raw['visitor_id'] ?? ''), 0, 64),
            'event_name' => substr((string) ($raw['event_name'] ?? 'interaction'), 0, 100),
            'event_category' => substr((string) ($raw['event_category'] ?? 'engagement'), 0, 100),
            'event_label' => substr((string) ($raw['event_label'] ?? ''), 0, 255),
            'metadata' => $raw['metadata'] ?? $raw['event_data'] ?? null,
            'event_data' => $raw['event_data'] ?? $raw['metadata'] ?? null,
            'path' => substr((string) ($raw['path'] ?? $raw['page_url'] ?? '/'), 0, 2000),
            'page_url' => substr((string) ($raw['page_url'] ?? $raw['path'] ?? '/'), 0, 2000),
        ];

        if (empty($payload['session_id'])) {
            return response()->json(['success' => false, 'message' => 'Missing session_id'], 200);
        }

        try {
            $result = $this->analyticsService->trackEvent($request, $payload);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 200);
        }
    }
}
