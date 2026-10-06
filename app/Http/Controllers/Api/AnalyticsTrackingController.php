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
        // Simple rate limiting: 120 pageviews/minute per IP
        $key = 'analytics_pv:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 120)) {
            return response()->json(['tracked' => false, 'rate_limited' => true], 429);
        }
        RateLimiter::hit($key, 60);

        $payload = $request->validate([
            'session_id' => 'nullable|string|max:64',
            'visitor_id' => 'nullable|string|max:64',
            'url' => 'nullable|string|max:2000',
            'route_name' => 'nullable|string|max:100',
            'page_title' => 'nullable|string|max:255',
            'referrer' => 'nullable|string|max:2000',
            'utm_source' => 'nullable|string|max:100',
            'utm_medium' => 'nullable|string|max:100',
            'utm_campaign' => 'nullable|string|max:100',
            'utm_content' => 'nullable|string|max:100',
            'utm_term' => 'nullable|string|max:100',
        ]);

        try {
            $result = $this->analyticsService->trackPageView($request, $payload);
            return response()->json($result);
        } catch (\Exception $e) {
            // Never break client execution
            return response()->json(['tracked' => false, 'error' => $e->getMessage()], 200);
        }
    }

    /**
     * Record interaction or conversion event.
     */
    public function trackEvent(Request $request)
    {
        $payload = $request->validate([
            'session_id' => 'required|string|max:64',
            'event_name' => 'required|string|max:100',
            'event_data' => 'nullable',
            'page_url' => 'nullable|string|max:2000',
        ]);

        try {
            $result = $this->analyticsService->trackEvent($request, $payload);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 200);
        }
    }
}
