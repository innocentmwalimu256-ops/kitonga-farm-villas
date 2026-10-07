import { router } from '@inertiajs/vue3';

/**
 * Kitonga Farm & Villas — Global Lightweight Client-Side Analytics Tracker
 * Privacy-first, non-blocking anonymous analytics.
 */

const STORAGE_KEYS = {
    VISITOR_ID: 'kfv_vid',
    SESSION_ID: 'kfv_sid',
    SESSION_LAST_ACTIVE: 'kfv_sid_time',
};

const SESSION_TIMEOUT_MS = 30 * 60 * 1000; // 30 minutes
const HEARTBEAT_INTERVAL_MS = 45 * 1000; // 45 seconds

function generateId(prefix = 'v') {
    const random = Math.random().toString(36).substring(2, 10);
    const timestamp = Date.now().toString(36);
    return `${prefix}_${timestamp}${random}`;
}

function getVisitorId() {
    try {
        let vid = localStorage.getItem(STORAGE_KEYS.VISITOR_ID);
        if (!vid) {
            vid = generateId('kfv');
            localStorage.setItem(STORAGE_KEYS.VISITOR_ID, vid);
        }
        return vid;
    } catch (e) {
        return generateId('kfv_mem');
    }
}

function getSessionId() {
    try {
        const now = Date.now();
        let sid = localStorage.getItem(STORAGE_KEYS.SESSION_ID);
        const lastActive = parseInt(localStorage.getItem(STORAGE_KEYS.SESSION_LAST_ACTIVE) || '0', 10);

        if (!sid || (now - lastActive > SESSION_TIMEOUT_MS)) {
            sid = generateId('s');
            localStorage.setItem(STORAGE_KEYS.SESSION_ID, sid);
        }
        localStorage.setItem(STORAGE_KEYS.SESSION_LAST_ACTIVE, now.toString());
        return sid;
    } catch (e) {
        return generateId('s_mem');
    }
}

function updateSessionActivity() {
    try {
        localStorage.setItem(STORAGE_KEYS.SESSION_LAST_ACTIVE, Date.now().toString());
    } catch (e) {}
}

function getUtmParams() {
    const params = new URLSearchParams(window.location.search);
    return {
        utm_source: params.get('utm_source'),
        utm_medium: params.get('utm_medium'),
        utm_campaign: params.get('utm_campaign'),
        utm_content: params.get('utm_content'),
        utm_term: params.get('utm_term'),
    };
}

function getCsrfToken() {
    try {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta && meta.content) return meta.content;
        const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
        if (match) return decodeURIComponent(match[1]);
    } catch (e) {}
    return '';
}

function sendPayload(url, data) {
    updateSessionActivity();
    const payload = JSON.stringify(data);
    const csrfToken = getCsrfToken();
    
    // Always use standard fetch first as it includes JSON Content-Type and headers properly!
    try {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        };
        if (csrfToken) {
            headers['X-CSRF-TOKEN'] = csrfToken;
            headers['X-XSRF-TOKEN'] = csrfToken;
        }

        fetch(url, {
            method: 'POST',
            headers: headers,
            body: payload,
            keepalive: true,
            credentials: 'same-origin',
        }).catch(() => {
            // Fallback to sendBeacon if fetch fails or on page leave
            if (navigator.sendBeacon) {
                const blob = new Blob([payload], { type: 'application/json' });
                navigator.sendBeacon(url, blob);
            }
        });
    } catch (e) {
        if (navigator.sendBeacon) {
            try {
                const blob = new Blob([payload], { type: 'application/json' });
                navigator.sendBeacon(url, blob);
            } catch (err) {}
        }
    }
}

let currentPageStartTime = Date.now();
let lastTrackedPath = '';

export function trackPageView(customPath = null, customTitle = null) {
    const path = customPath || window.location.pathname + window.location.search;
    const title = customTitle || document.title;
    
    // Don't track admin pages or internal dashboard routes
    if (path.startsWith('/admin') || path.startsWith('/login') || path.startsWith('/register')) {
        return;
    }

    currentPageStartTime = Date.now();
    lastTrackedPath = path;

    const utm = getUtmParams();
    const data = {
        visitor_id: getVisitorId(),
        session_id: getSessionId(),
        path: path,
        title: title,
        referrer: document.referrer || '',
        screen_width: window.screen ? window.screen.width : null,
        screen_height: window.screen ? window.screen.height : null,
        ...utm,
    };

    sendPayload('/api/analytics/pageview', data);
}

export function trackEvent(eventName, eventCategory = 'interaction', eventLabel = '', metadata = {}) {
    // Don't track on admin routes
    if (window.location.pathname.startsWith('/admin')) {
        return;
    }

    const data = {
        visitor_id: getVisitorId(),
        session_id: getSessionId(),
        event_name: eventName,
        event_category: eventCategory,
        event_label: eventLabel,
        path: window.location.pathname,
        metadata: metadata,
    };

    sendPayload('/api/analytics/event', data);
}

export function sendHeartbeat() {
    if (document.visibilityState !== 'visible') {
        return;
    }
    if (window.location.pathname.startsWith('/admin')) {
        return;
    }

    const timeSpent = Math.round((Date.now() - currentPageStartTime) / 1000);
    const data = {
        session_id: getSessionId(),
        path: window.location.pathname,
        time_spent: timeSpent,
    };

    sendPayload('/api/analytics/heartbeat', data);
}

let isInitialized = false;

export function initAnalyticsTracker() {
    if (isInitialized || typeof window === 'undefined') {
        return;
    }
    isInitialized = true;

    // Track initial page view after a short tick
    setTimeout(() => {
        trackPageView();
    }, 300);

    // Track Inertia Page Navigations
    if (router && typeof router.on === 'function') {
        router.on('navigate', (event) => {
            setTimeout(() => {
                trackPageView();
            }, 100);
        });
    }

    // Active Heartbeat Interval (Every 45 seconds)
    setInterval(() => {
        sendHeartbeat();
    }, HEARTBEAT_INTERVAL_MS);

    // Global Click Listener for Conversion Events
    document.addEventListener('click', (e) => {
        try {
            const target = e.target.closest('a, button, [data-track]');
            if (!target) return;

            const href = target.getAttribute('href') || '';
            const trackAttr = target.getAttribute('data-track');

            // 1. WhatsApp Click Tracking
            if (trackAttr === 'whatsapp' || href.includes('wa.me') || href.includes('whatsapp.com')) {
                trackEvent('whatsapp_click', 'conversion', href, {
                    button_text: target.innerText?.trim()?.substring(0, 50),
                    target_url: href,
                });
            }
            // 2. Phone Call Click Tracking
            else if (href.startsWith('tel:') || trackAttr === 'phone') {
                trackEvent('phone_call_click', 'conversion', href, {
                    phone_number: href.replace('tel:', ''),
                });
            }
            // 3. Meet Mr Kitonga Consultation CTA
            else if (href.includes('/meet-mr-kitonga') || trackAttr === 'meet-kitonga') {
                trackEvent('consultation_cta_click', 'engagement', 'Meet Mr. Kitonga', {
                    source_page: window.location.pathname,
                });
            }
            // 4. Booking CTA
            else if (href.includes('/book') || href.includes('/experiences/') || trackAttr === 'booking_start') {
                trackEvent('booking_intent', 'conversion', href, {
                    target_url: href,
                });
            }
        } catch (err) {}
    }, true);

    // Visibility state changes
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            sendHeartbeat();
        }
    });
}
