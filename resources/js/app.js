import '../css/app.css';
import './bootstrap';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Kitonga Farm Villas';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#C98A3E',
        includeCSS: true,
        showSpinner: false,
        delay: 50,
    },
});

// ULTRA-FAST INTELLIGENT INERTIA ROUTE & ASSET PREFETCH ENGINE
if (typeof window !== 'undefined') {
    const prefetchedUrls = new Set();
    const coreRoutes = ['/', '/villas', '/experiences', '/farm', '/products', '/gallery', '/contact', '/book', '/location'];

    const prefetchUrl = (href) => {
        if (!href || prefetchedUrls.has(href)) return;
        try {
            const url = new URL(href, window.location.origin);
            if (url.origin !== window.location.origin) return;
            if (url.pathname.startsWith('/storage') || url.pathname.startsWith('/images') || url.pathname.startsWith('/stream')) return;
            
            prefetchedUrls.add(href);
            
            // 1. Inertia 2.0 In-Memory Data & Component Prefetch (0ms instant page transitions)
            if (typeof router.prefetch === 'function') {
                router.prefetch(url.pathname + url.search, { method: 'get' }, { cacheFor: '5m' });
            }

            // 2. Native browser link prefetch hint as fallback
            const linkEl = document.createElement('link');
            linkEl.rel = 'prefetch';
            linkEl.href = href;
            document.head.appendChild(linkEl);
        } catch (err) {
            // Ignore invalid URLs
        }
    };

    // Preload core routes automatically when browser is idle
    const warmUpCoreRoutes = () => {
        coreRoutes.forEach((path, idx) => {
            setTimeout(() => {
                prefetchUrl(window.location.origin + path);
            }, idx * 150);
        });
    };

    if ('requestIdleCallback' in window) {
        window.requestIdleCallback(warmUpCoreRoutes, { timeout: 1500 });
    } else {
        setTimeout(warmUpCoreRoutes, 500);
    }

    // Preload instantly on hover or touchstart before click completes
    document.addEventListener('mouseover', (e) => {
        const target = e.target.closest('a');
        if (target && target.href) {
            prefetchUrl(target.href);
        }
    }, { passive: true });

    document.addEventListener('touchstart', (e) => {
        const target = e.target.closest('a');
        if (target && target.href) {
            prefetchUrl(target.href);
        }
    }, { passive: true });
}


