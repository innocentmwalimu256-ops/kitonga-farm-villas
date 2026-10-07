<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';

const props = defineProps({
    filters: Object,
    overview: Object,
    chartData: Object,
    topPages: Array,
    locations: Array,
    devices: Array,
    browsers: Array,
    operatingSystems: Array,
    referrers: Array,
    events: Array,
    recentSessions: Array,
    liveVisitors: Array,
});

// Period and Date filter state
const selectedPeriod = ref(props.filters?.period || '7d');
const customFrom = ref(props.filters?.from || '');
const customTo = ref(props.filters?.to || '');
const showCustomPicker = ref(props.filters?.period === 'custom');

// Chart Metric selection
const activeMetric = ref('all'); // 'all', 'visitors', 'pageviews', 'sessions'

// Live visitors state with auto-refresh
const liveList = ref(props.liveVisitors || []);
const liveCount = ref(props.overview?.live_visitors || liveList.value.length);
const isPolling = ref(true);
let pollInterval = null;

const periods = [
    { key: 'today', label: 'Today' },
    { key: 'yesterday', label: 'Yesterday' },
    { key: '7d', label: 'Last 7 Days' },
    { key: '30d', label: 'Last 30 Days' },
    { key: 'this_month', label: 'This Month' },
    { key: 'last_month', label: 'Last Month' },
    { key: 'custom', label: 'Custom' },
];

function setPeriod(periodKey) {
    selectedPeriod.value = periodKey;
    if (periodKey === 'custom') {
        showCustomPicker.value = true;
        return;
    }
    showCustomPicker.value = false;
    applyFilter();
}

function applyFilter() {
    const params = { period: selectedPeriod.value };
    if (selectedPeriod.value === 'custom') {
        if (!customFrom.value || !customTo.value) return;
        params.from = customFrom.value;
        params.to = customTo.value;
    }
    router.get(route('admin.analytics.index'), params, {
        preserveState: true,
        preserveScroll: true,
    });
}

function exportCsv() {
    let url = route('admin.analytics.export') + `?period=${selectedPeriod.value}`;
    if (selectedPeriod.value === 'custom' && customFrom.value && customTo.value) {
        url += `&from=${customFrom.value}&to=${customTo.value}`;
    }
    window.open(url, '_blank');
}

// Live polling
async function fetchLiveVisitors() {
    if (!isPolling.value) return;
    try {
        const response = await axios.get(route('admin.analytics.live'));
        if (response.data) {
            liveCount.value = response.data.count || 0;
            liveList.value = response.data.visitors || [];
        }
    } catch (e) {
        // silent fallback
    }
}

onMounted(() => {
    fetchLiveVisitors();
    pollInterval = setInterval(fetchLiveVisitors, 4000); // Poll every 4 seconds for fast live updates
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});

// SVG Chart Calculations
const chartWidth = 900;
const chartHeight = 240;
const padding = { top: 20, right: 20, bottom: 35, left: 45 };

const chartLabels = computed(() => props.chartData?.labels || []);
const chartVisitors = computed(() => props.chartData?.visitors || []);
const chartPageViews = computed(() => props.chartData?.pageviews || []);
const chartSessions = computed(() => props.chartData?.sessions || []);

const maxVal = computed(() => {
    const all = [
        ...chartVisitors.value,
        ...chartPageViews.value,
        ...chartSessions.value,
    ];
    const m = Math.max(...all, 5);
    return Math.ceil(m * 1.15); // Add headroom
});

function getCoordinates(dataArray) {
    if (!dataArray || !dataArray.length) return [];
    const usableW = chartWidth - padding.left - padding.right;
    const usableH = chartHeight - padding.top - padding.bottom;
    const stepX = dataArray.length > 1 ? usableW / (dataArray.length - 1) : usableW / 2;

    return dataArray.map((val, idx) => {
        const x = padding.left + idx * stepX;
        const y = padding.top + usableH - (val / (maxVal.value || 1)) * usableH;
        return { x, y, val };
    });
}

function buildSvgPath(points) {
    if (!points.length) return '';
    return points.reduce((acc, pt, i) => {
        if (i === 0) return `M ${pt.x} ${pt.y}`;
        const prev = points[i - 1];
        const cx1 = prev.x + (pt.x - prev.x) / 2;
        const cy1 = prev.y;
        const cx2 = prev.x + (pt.x - prev.x) / 2;
        const cy2 = pt.y;
        return `${acc} C ${cx1} ${cy1}, ${cx2} ${cy2}, ${pt.x} ${pt.y}`;
    }, '');
}

function buildAreaPath(points) {
    if (!points.length) return '';
    const line = buildSvgPath(points);
    const lastX = points[points.length - 1].x;
    const firstX = points[0].x;
    const baselineY = chartHeight - padding.bottom;
    return `${line} L ${lastX} ${baselineY} L ${firstX} ${baselineY} Z`;
}

const visitorPoints = computed(() => getCoordinates(chartVisitors.value));
const pageviewPoints = computed(() => getCoordinates(chartPageViews.value));
const sessionPoints = computed(() => getCoordinates(chartSessions.value));

// Active Hover Tooltip on Chart
const hoveredIndex = ref(null);
function handleChartMouseMove(e) {
    const svgRect = e.currentTarget.getBoundingClientRect();
    const mouseX = e.clientX - svgRect.left;
    const usableW = chartWidth - padding.left - padding.right;
    const count = chartLabels.value.length;
    if (count <= 1) return;

    const relX = mouseX - (padding.left * (svgRect.width / chartWidth));
    const step = (usableW * (svgRect.width / chartWidth)) / (count - 1);
    let idx = Math.round(relX / step);
    if (idx < 0) idx = 0;
    if (idx >= count) idx = count - 1;
    hoveredIndex.value = idx;
}
function handleChartMouseLeave() {
    hoveredIndex.value = null;
}

// Helpers
function formatSeconds(sec) {
    if (!sec) return '0s';
    const m = Math.floor(sec / 60);
    const s = sec % 60;
    return m > 0 ? `${m}m ${s}s` : `${s}s`;
}

function getFlag(countryCode) {
    if (!countryCode || countryCode === 'Unknown' || countryCode === 'XX') return '🌍';
    const code = countryCode.toUpperCase();
    if (code === 'TZ') return '🇹🇿';
    if (code === 'KE') return '🇰🇪';
    if (code === 'UG') return '🇺🇬';
    if (code === 'RW') return '🇷🇼';
    if (code === 'US') return '🇺🇸';
    if (code === 'GB') return '🇬🇧';
    if (code === 'DE') return '🇩🇪';
    if (code === 'FR') return '🇫🇷';
    if (code === 'IT') return '🇮🇹';
    if (code === 'ZA') return '🇿🇦';
    if (code === 'AE') return '🇦🇪';
    if (code === 'IN') return '🇮🇳';
    if (code === 'CN') return '🇨🇳';
    return '🌍';
}
</script>

<template>
    <Head title="Website Visitor Analytics - Kitonga Farm & Villas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-indigo-900 text-white flex items-center justify-center shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-serif font-bold text-xl sm:text-2xl text-[#14231C] leading-none">
                                Website Visitor Analytics
                            </h2>
                            <p class="text-xs text-gray-500 mt-1">
                                Real-time privacy-compliant website traffic, live visitors, sources & business conversions.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Right Toolbar: Export & Refresh -->
                <div class="flex items-center gap-2">
                    <button
                        @click="exportCsv"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white border border-gray-200 text-xs font-bold text-gray-700 hover:bg-gray-50 shadow-2xs transition cursor-pointer"
                        title="Download CSV report"
                    >
                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Export CSV
                    </button>
                    
                    <button
                        @click="applyFilter"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-[#14231C] text-[#E6C387] text-xs font-bold hover:bg-[#1c3228] shadow-2xs transition cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- 1. DATE FILTER TOOLBAR -->
            <div class="bg-white rounded-2xl p-3 sm:p-4 border border-gray-200 shadow-2xs flex flex-wrap items-center justify-between gap-3">
                <!-- Period Pills -->
                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                    <button
                        v-for="p in periods"
                        :key="p.key"
                        @click="setPeriod(p.key)"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer"
                        :class="selectedPeriod === p.key
                            ? 'bg-[#14231C] text-white shadow-2xs'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                    >
                        {{ p.label }}
                    </button>
                </div>

                <!-- Custom Date Range Form (when active) -->
                <div v-if="showCustomPicker" class="flex flex-wrap items-center gap-2">
                    <input
                        type="date"
                        v-model="customFrom"
                        class="text-xs rounded-lg border-gray-300 py-1.5 px-2.5 focus:ring-emerald-500 focus:border-emerald-500"
                    />
                    <span class="text-xs text-gray-400">to</span>
                    <input
                        type="date"
                        v-model="customTo"
                        class="text-xs rounded-lg border-gray-300 py-1.5 px-2.5 focus:ring-emerald-500 focus:border-emerald-500"
                    />
                    <button
                        @click="applyFilter"
                        class="px-3 py-1.5 rounded-lg bg-emerald-700 text-white text-xs font-bold hover:bg-emerald-800 transition"
                    >
                        Apply
                    </button>
                </div>

                <!-- Active Date Badge -->
                <div class="text-[11px] text-gray-500 font-medium ml-auto">
                    Period: <span class="font-bold text-gray-800">{{ filters?.from }}</span> to <span class="font-bold text-gray-800">{{ filters?.to }}</span>
                </div>
            </div>

            <!-- 2. OVERVIEW KPI CARDS -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
                
                <!-- Card 1: Total Visitors -->
                <div class="bg-white rounded-2xl p-4 border border-gray-200 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Visitors</span>
                        <div class="w-6 h-6 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-gray-900">{{ overview?.total_visitors?.toLocaleString() || 0 }}</span>
                    </div>
                    <div class="mt-1 text-[10px] text-gray-400">Unique individuals</div>
                </div>

                <!-- Card 2: Page Views -->
                <div class="bg-white rounded-2xl p-4 border border-gray-200 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Page Views</span>
                        <div class="w-6 h-6 rounded-md bg-purple-50 text-purple-600 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </div>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-gray-900">{{ overview?.total_page_views?.toLocaleString() || 0 }}</span>
                    </div>
                    <div class="mt-1 text-[10px] text-gray-400">Total screens loaded</div>
                </div>

                <!-- Card 3: Live Online Visitors -->
                <div class="bg-gradient-to-br from-emerald-900 to-[#14231C] rounded-2xl p-4 text-white shadow-xs ring-1 ring-emerald-500/30">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-300">Live Active</span>
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-400"></span>
                        </span>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-[#E6C387]">{{ liveCount }}</span>
                        <span class="text-[11px] text-emerald-200">online</span>
                    </div>
                    <div class="mt-1 text-[10px] text-emerald-300/70">Past 5 minutes</div>
                </div>

                <!-- Card 4: Sessions -->
                <div class="bg-white rounded-2xl p-4 border border-gray-200 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Sessions</span>
                        <div class="w-6 h-6 rounded-md bg-amber-50 text-amber-600 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-gray-900">{{ overview?.total_sessions?.toLocaleString() || 0 }}</span>
                    </div>
                    <div class="mt-1 text-[10px] text-gray-400">Browsing visits</div>
                </div>

                <!-- Card 5: Avg Duration -->
                <div class="bg-white rounded-2xl p-4 border border-gray-200 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Avg Duration</span>
                        <div class="w-6 h-6 rounded-md bg-teal-50 text-teal-600 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-gray-900">{{ overview?.avg_duration_formatted || '0s' }}</span>
                    </div>
                    <div class="mt-1 text-[10px] text-gray-400">Per session avg</div>
                </div>

                <!-- Card 6: Bounce Rate -->
                <div class="bg-white rounded-2xl p-4 border border-gray-200 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Bounce Rate</span>
                        <div class="w-6 h-6 rounded-md bg-rose-50 text-rose-600 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        </div>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-gray-900">{{ overview?.bounce_rate || 0 }}%</span>
                    </div>
                    <div class="mt-1 text-[10px] text-gray-400">Single page visits</div>
                </div>

            </div>

            <!-- 3. MAIN INTERACTIVE VISITOR GRAPH -->
            <div class="bg-white rounded-2xl p-4 sm:p-6 border border-gray-200 shadow-2xs">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-gray-100">
                    <div>
                        <h3 class="font-serif font-bold text-lg text-gray-900">Visitors & Page Views Trend</h3>
                        <p class="text-xs text-gray-500">Daily trajectory for the selected date range</p>
                    </div>

                    <!-- Metric Toggle Controls -->
                    <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-xl text-xs font-bold">
                        <button
                            @click="activeMetric = 'all'"
                            class="px-3 py-1 rounded-lg transition"
                            :class="activeMetric === 'all' ? 'bg-white text-gray-900 shadow-2xs' : 'text-gray-600 hover:text-gray-900'"
                        >
                            All Metrics
                        </button>
                        <button
                            @click="activeMetric = 'visitors'"
                            class="px-3 py-1 rounded-lg transition flex items-center gap-1.5"
                            :class="activeMetric === 'visitors' ? 'bg-blue-600 text-white shadow-2xs' : 'text-gray-600 hover:text-gray-900'"
                        >
                            <span class="w-2 h-2 rounded-full bg-blue-400"></span> Visitors
                        </button>
                        <button
                            @click="activeMetric = 'pageviews'"
                            class="px-3 py-1 rounded-lg transition flex items-center gap-1.5"
                            :class="activeMetric === 'pageviews' ? 'bg-purple-600 text-white shadow-2xs' : 'text-gray-600 hover:text-gray-900'"
                        >
                            <span class="w-2 h-2 rounded-full bg-purple-400"></span> Page Views
                        </button>
                        <button
                            @click="activeMetric = 'sessions'"
                            class="px-3 py-1 rounded-lg transition flex items-center gap-1.5"
                            :class="activeMetric === 'sessions' ? 'bg-amber-600 text-white shadow-2xs' : 'text-gray-600 hover:text-gray-900'"
                        >
                            <span class="w-2 h-2 rounded-full bg-amber-400"></span> Sessions
                        </button>
                    </div>
                </div>

                <!-- SVG Area/Line Chart -->
                <div class="mt-4 relative">
                    <svg
                        :viewBox="`0 0 ${chartWidth} ${chartHeight}`"
                        class="w-full h-48 sm:h-64 overflow-visible select-none"
                        @mousemove="handleChartMouseMove"
                        @mouseleave="handleChartMouseLeave"
                    >
                        <defs>
                            <!-- Gradient Visitors (Blue) -->
                            <linearGradient id="gradVisitors" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#2563eb" stop-opacity="0.28" />
                                <stop offset="100%" stop-color="#2563eb" stop-opacity="0.0" />
                            </linearGradient>
                            <!-- Gradient Page Views (Purple) -->
                            <linearGradient id="gradPageViews" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#9333ea" stop-opacity="0.22" />
                                <stop offset="100%" stop-color="#9333ea" stop-opacity="0.0" />
                            </linearGradient>
                            <!-- Gradient Sessions (Amber) -->
                            <linearGradient id="gradSessions" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#d97706" stop-opacity="0.2" />
                                <stop offset="100%" stop-color="#d97706" stop-opacity="0.0" />
                            </linearGradient>
                        </defs>

                        <!-- Background Grid Lines -->
                        <g class="stroke-gray-100" stroke-width="1" stroke-dasharray="4 4">
                            <line :x1="padding.left" :y1="padding.top" :x2="chartWidth - padding.right" :y2="padding.top" />
                            <line :x1="padding.left" :y1="padding.top + (chartHeight - padding.top - padding.bottom) / 2" :x2="chartWidth - padding.right" :y2="padding.top + (chartHeight - padding.top - padding.bottom) / 2" />
                            <line :x1="padding.left" :y1="chartHeight - padding.bottom" :x2="chartWidth - padding.right" :y2="chartHeight - padding.bottom" />
                        </g>

                        <!-- Y-Axis Ticks -->
                        <g class="fill-gray-400 text-[10px] font-medium" text-anchor="end">
                            <text :x="padding.left - 8" :y="padding.top + 4">{{ maxVal }}</text>
                            <text :x="padding.left - 8" :y="padding.top + (chartHeight - padding.top - padding.bottom) / 2 + 4">{{ Math.round(maxVal / 2) }}</text>
                            <text :x="padding.left - 8" :y="chartHeight - padding.bottom + 4">0</text>
                        </g>

                        <!-- X-Axis Labels -->
                        <g class="fill-gray-400 text-[10px] font-semibold" text-anchor="middle">
                            <template v-for="(lbl, idx) in chartLabels" :key="idx">
                                <text
                                    v-if="chartLabels.length <= 10 || idx % Math.ceil(chartLabels.length / 8) === 0"
                                    :x="padding.left + (idx * ((chartWidth - padding.left - padding.right) / (chartLabels.length > 1 ? chartLabels.length - 1 : 1)))"
                                    :y="chartHeight - 8"
                                >
                                    {{ lbl }}
                                </text>
                            </template>
                        </g>

                        <!-- Page Views Area & Line -->
                        <g v-if="activeMetric === 'all' || activeMetric === 'pageviews'">
                            <path :d="buildAreaPath(pageviewPoints)" fill="url(#gradPageViews)" />
                            <path :d="buildSvgPath(pageviewPoints)" fill="none" stroke="#9333ea" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                        </g>

                        <!-- Sessions Area & Line -->
                        <g v-if="activeMetric === 'all' || activeMetric === 'sessions'">
                            <path :d="buildAreaPath(sessionPoints)" fill="url(#gradSessions)" />
                            <path :d="buildSvgPath(sessionPoints)" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="3 3" />
                        </g>

                        <!-- Visitors Area & Line (Top Primary) -->
                        <g v-if="activeMetric === 'all' || activeMetric === 'visitors'">
                            <path :d="buildAreaPath(visitorPoints)" fill="url(#gradVisitors)" />
                            <path :d="buildSvgPath(visitorPoints)" fill="none" stroke="#2563eb" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                        </g>

                        <!-- Hover Vertical Guideline & Indicator Dots -->
                        <g v-if="hoveredIndex !== null && chartLabels[hoveredIndex]">
                            <line
                                :x1="visitorPoints[hoveredIndex]?.x || 0"
                                :y1="padding.top"
                                :x2="visitorPoints[hoveredIndex]?.x || 0"
                                :y2="chartHeight - padding.bottom"
                                stroke="#6b7280"
                                stroke-width="1.5"
                                stroke-dasharray="2 2"
                            />
                            <!-- Dot Visitors -->
                            <circle
                                v-if="visitorPoints[hoveredIndex]"
                                :cx="visitorPoints[hoveredIndex].x"
                                :cy="visitorPoints[hoveredIndex].y"
                                r="5"
                                fill="#2563eb"
                                stroke="#ffffff"
                                stroke-width="2"
                            />
                            <!-- Dot PageViews -->
                            <circle
                                v-if="pageviewPoints[hoveredIndex]"
                                :cx="pageviewPoints[hoveredIndex].x"
                                :cy="pageviewPoints[hoveredIndex].y"
                                r="4"
                                fill="#9333ea"
                                stroke="#ffffff"
                                stroke-width="2"
                            />
                        </g>
                    </svg>

                    <!-- Interactive Tooltip Overlay -->
                    <div
                        v-if="hoveredIndex !== null && chartLabels[hoveredIndex]"
                        class="absolute pointer-events-none bg-gray-900/95 text-white text-xs rounded-xl p-3 shadow-xl backdrop-blur-sm border border-gray-700 min-w-[150px] transition-all duration-75"
                        :style="{
                            left: `${Math.min(Math.max((visitorPoints[hoveredIndex]?.x || 0) - 75, 10), chartWidth - 170)}px`,
                            top: '10px'
                        }"
                    >
                        <div class="font-bold text-gray-300 border-b border-gray-700/80 pb-1 mb-1.5 flex items-center justify-between">
                            <span>{{ chartLabels[hoveredIndex] }}</span>
                        </div>
                        <div class="space-y-1 text-[11px]">
                            <div class="flex items-center justify-between text-blue-400">
                                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-blue-500"></span> Visitors:</span>
                                <span class="font-bold text-white">{{ chartVisitors[hoveredIndex] || 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between text-purple-400">
                                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-purple-500"></span> Page Views:</span>
                                <span class="font-bold text-white">{{ chartPageViews[hoveredIndex] || 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between text-amber-400">
                                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Sessions:</span>
                                <span class="font-bold text-white">{{ chartSessions[hoveredIndex] || 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. LIVE VISITORS ACTIVE RADAR & BUSINESS CONVERSIONS -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Live Visitors Stream (2 Cols) -->
                <div class="lg:col-span-2 bg-white rounded-2xl p-5 border border-gray-200 shadow-2xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                            <div class="flex items-center gap-2">
                                <span class="relative flex h-3 w-3">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                                </span>
                                <h3 class="font-serif font-bold text-base text-gray-900">
                                    Live Active Visitors ({{ liveCount }} Online)
                                </h3>
                            </div>
                            <span class="text-[11px] text-gray-400 font-mono">Updates every 10s</span>
                        </div>

                        <!-- Live Visitors List -->
                        <div class="mt-3 divide-y divide-gray-100 max-h-72 overflow-y-auto pr-1">
                            <div
                                v-for="visitor in liveList"
                                :key="visitor.session_id"
                                class="py-2.5 flex items-center justify-between text-xs hover:bg-gray-50/80 rounded-lg px-2 transition"
                            >
                                <div class="flex items-center gap-2.5">
                                    <span class="text-base">{{ getFlag(visitor.country) }}</span>
                                    <div>
                                        <div class="flex items-center gap-1.5 font-mono font-bold text-gray-800">
                                            <span>{{ visitor.visitor_label }}</span>
                                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 font-sans font-medium">
                                                {{ visitor.device }} · {{ visitor.browser }}
                                            </span>
                                        </div>
                                        <div class="text-[11px] text-emerald-800 font-medium flex items-center gap-1 mt-0.5">
                                            <span class="text-gray-400">Viewing:</span>
                                            <code class="bg-emerald-50 px-1 py-0.5 rounded text-emerald-900">{{ visitor.current_page || '/' }}</code>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <div class="text-[11px] font-semibold text-gray-700">{{ visitor.country_name || visitor.country }}</div>
                                    <div class="text-[10px] text-gray-400 font-mono">{{ visitor.last_activity }}</div>
                                </div>
                            </div>

                            <div v-if="!liveList.length" class="py-8 text-center text-gray-400 text-xs">
                                No active visitors currently detected on public pages.
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-gray-100 flex items-center justify-between text-[11px] text-gray-500">
                        <span>Active heartbeat threshold: 5 minutes</span>
                        <span class="text-emerald-700 font-semibold font-mono">● LIVE STREAM ACTIVE</span>
                    </div>
                </div>

                <!-- Business Conversion Events (1 Col) -->
                <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-2xs">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <h3 class="font-serif font-bold text-base text-gray-900">Conversion Actions</h3>
                        <span class="text-[10px] bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full font-bold">Key Goals</span>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div
                            v-for="evt in events"
                            :key="evt.event_name"
                            class="p-3 rounded-xl bg-gray-50 border border-gray-100 flex items-center justify-between"
                        >
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-xs"
                                    :class="{
                                        'bg-emerald-100 text-emerald-800': evt.event_name.includes('whatsapp'),
                                        'bg-blue-100 text-blue-800': evt.event_name.includes('phone'),
                                        'bg-purple-100 text-purple-800': evt.event_name.includes('booking'),
                                        'bg-amber-100 text-amber-800': evt.event_name.includes('consultation'),
                                    }"
                                >
                                    <span v-if="evt.event_name.includes('whatsapp')">WA</span>
                                    <span v-else-if="evt.event_name.includes('phone')">📞</span>
                                    <span v-else-if="evt.event_name.includes('consultation')">MK</span>
                                    <span v-else>✦</span>
                                </div>
                                <div>
                                    <div class="font-bold text-xs text-gray-800 capitalize">
                                        {{ evt.event_name.replace(/_/g, ' ') }}
                                    </div>
                                    <div class="text-[10px] text-gray-400 capitalize">
                                        {{ evt.event_category || 'Goal' }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-base font-black text-gray-900">{{ evt.count?.toLocaleString() || 0 }}</span>
                                <span class="block text-[10px] text-gray-400">actions</span>
                            </div>
                        </div>

                        <div v-if="!events || !events.length" class="py-6 text-center text-xs text-gray-400">
                            No conversion clicks recorded in this period yet.
                        </div>
                    </div>
                </div>

            </div>

            <!-- 5. TOP PAGES & TRAFFIC REFERRERS -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- Top Pages -->
                <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-2xs">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <h3 class="font-serif font-bold text-base text-gray-900">Top Viewed Pages</h3>
                        <span class="text-xs text-gray-400 font-semibold">{{ topPages?.length || 0 }} pages</span>
                    </div>

                    <div class="mt-3 overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="text-[10px] uppercase font-bold text-gray-400 border-b border-gray-100">
                                    <th class="pb-2">Page URL / Title</th>
                                    <th class="pb-2 text-right">Views</th>
                                    <th class="pb-2 text-right">Visitors</th>
                                    <th class="pb-2 text-right">Share</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="page in topPages" :key="page.path" class="hover:bg-gray-50/80">
                                    <td class="py-2.5 pr-2 max-w-[200px] truncate">
                                        <div class="font-bold text-gray-800 truncate" :title="page.title">{{ page.title || page.path }}</div>
                                        <div class="text-[10px] text-gray-400 font-mono truncate">{{ page.path }}</div>
                                    </td>
                                    <td class="py-2.5 text-right font-black text-gray-900">{{ page.views?.toLocaleString() }}</td>
                                    <td class="py-2.5 text-right text-gray-600">{{ page.unique_visitors?.toLocaleString() }}</td>
                                    <td class="py-2.5 text-right w-24">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <div class="w-12 bg-gray-100 h-1.5 rounded-full overflow-hidden">
                                                <div
                                                    class="bg-indigo-600 h-full rounded-full"
                                                    :style="{ width: `${page.percentage || 0}%` }"
                                                ></div>
                                            </div>
                                            <span class="text-[10px] font-bold text-gray-600 w-7">{{ page.percentage || 0 }}%</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!topPages?.length">
                                    <td colspan="4" class="py-6 text-center text-gray-400 text-xs">No pageview data found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Traffic Sources & Referrers -->
                <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-2xs">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <h3 class="font-serif font-bold text-base text-gray-900">Traffic Sources / Referrers</h3>
                        <span class="text-xs text-gray-400 font-semibold">{{ referrers?.length || 0 }} sources</span>
                    </div>

                    <div class="mt-3 overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="text-[10px] uppercase font-bold text-gray-400 border-b border-gray-100">
                                    <th class="pb-2">Source / Platform</th>
                                    <th class="pb-2 text-right">Sessions</th>
                                    <th class="pb-2 text-right">Share</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="ref in referrers" :key="ref.source" class="hover:bg-gray-50/80">
                                    <td class="py-2.5 flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full"
                                            :class="{
                                                'bg-blue-500': ref.source.toLowerCase().includes('google'),
                                                'bg-emerald-500': ref.source.toLowerCase().includes('whatsapp'),
                                                'bg-pink-500': ref.source.toLowerCase().includes('instagram'),
                                                'bg-blue-700': ref.source.toLowerCase().includes('facebook'),
                                                'bg-gray-700': ref.source.toLowerCase().includes('direct'),
                                                'bg-amber-500': !['google','whatsapp','instagram','facebook','direct'].some(s => ref.source.toLowerCase().includes(s))
                                            }"
                                        ></span>
                                        <span class="font-bold text-gray-800">{{ ref.source }}</span>
                                    </td>
                                    <td class="py-2.5 text-right font-black text-gray-900">{{ ref.count?.toLocaleString() }}</td>
                                    <td class="py-2.5 text-right w-28">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <div class="w-14 bg-gray-100 h-1.5 rounded-full overflow-hidden">
                                                <div
                                                    class="bg-emerald-600 h-full rounded-full"
                                                    :style="{ width: `${ref.percentage || 0}%` }"
                                                ></div>
                                            </div>
                                            <span class="text-[10px] font-bold text-gray-600 w-8">{{ ref.percentage || 0 }}%</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!referrers?.length">
                                    <td colspan="3" class="py-6 text-center text-gray-400 text-xs">No referrer data found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- 6. GEOLOCATION, DEVICES, OS & BROWSERS -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                <!-- Locations & Countries -->
                <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-2xs">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <h3 class="font-serif font-bold text-sm text-gray-900">Visitor Locations</h3>
                        <span class="text-[10px] text-gray-400">GeoIP</span>
                    </div>
                    <div class="mt-3 space-y-2.5">
                        <div
                            v-for="loc in locations"
                            :key="loc.country"
                            class="flex items-center justify-between text-xs"
                        >
                            <div class="flex items-center gap-2">
                                <span>{{ getFlag(loc.country) }}</span>
                                <span class="font-semibold text-gray-700 truncate max-w-[100px]">{{ loc.country_name || loc.country }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-gray-900">{{ loc.count }}</span>
                                <span class="text-[10px] text-gray-400 w-8 text-right">{{ loc.percentage }}%</span>
                            </div>
                        </div>
                        <div v-if="!locations?.length" class="py-4 text-center text-xs text-gray-400">
                            No location data yet.
                        </div>
                    </div>
                </div>

                <!-- Devices Breakdown -->
                <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-2xs">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <h3 class="font-serif font-bold text-sm text-gray-900">Devices</h3>
                        <span class="text-[10px] text-gray-400">Hardware</span>
                    </div>
                    <div class="mt-3 space-y-3">
                        <div
                            v-for="dev in devices"
                            :key="dev.device"
                            class="space-y-1 text-xs"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-gray-700 capitalize">{{ dev.device }}</span>
                                <span class="font-bold text-gray-900">{{ dev.percentage }}% ({{ dev.count }})</span>
                            </div>
                            <div class="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden">
                                <div
                                    class="h-full rounded-full"
                                    :class="dev.device === 'Mobile' ? 'bg-emerald-600' : (dev.device === 'Desktop' ? 'bg-blue-600' : 'bg-purple-600')"
                                    :style="{ width: `${dev.percentage}%` }"
                                ></div>
                            </div>
                        </div>
                        <div v-if="!devices?.length" class="py-4 text-center text-xs text-gray-400">
                            No device data yet.
                        </div>
                    </div>
                </div>

                <!-- Operating Systems -->
                <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-2xs">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <h3 class="font-serif font-bold text-sm text-gray-900">Operating Systems</h3>
                        <span class="text-[10px] text-gray-400">OS</span>
                    </div>
                    <div class="mt-3 space-y-2.5">
                        <div
                            v-for="os in operatingSystems"
                            :key="os.os"
                            class="flex items-center justify-between text-xs"
                        >
                            <span class="font-semibold text-gray-700">{{ os.os }}</span>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-gray-900">{{ os.count }}</span>
                                <span class="text-[10px] text-gray-400 w-8 text-right">{{ os.percentage }}%</span>
                            </div>
                        </div>
                        <div v-if="!operatingSystems?.length" class="py-4 text-center text-xs text-gray-400">
                            No OS data yet.
                        </div>
                    </div>
                </div>

                <!-- Browsers -->
                <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-2xs">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <h3 class="font-serif font-bold text-sm text-gray-900">Web Browsers</h3>
                        <span class="text-[10px] text-gray-400">Clients</span>
                    </div>
                    <div class="mt-3 space-y-2.5">
                        <div
                            v-for="b in browsers"
                            :key="b.browser"
                            class="flex items-center justify-between text-xs"
                        >
                            <span class="font-semibold text-gray-700">{{ b.browser }}</span>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-gray-900">{{ b.count }}</span>
                                <span class="text-[10px] text-gray-400 w-8 text-right">{{ b.percentage }}%</span>
                            </div>
                        </div>
                        <div v-if="!browsers?.length" class="py-4 text-center text-xs text-gray-400">
                            No browser data yet.
                        </div>
                    </div>
                </div>

            </div>

            <!-- 7. RECENT VISITORS ACTIVITY LOG STREAM -->
            <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-2xs">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <div>
                        <h3 class="font-serif font-bold text-base text-gray-900">Recent Visitor Sessions</h3>
                        <p class="text-xs text-gray-500">Anonymous session logs showing entry, device, location and engagement.</p>
                    </div>
                    <span class="text-xs font-semibold text-gray-500">{{ recentSessions?.length || 0 }} logged</span>
                </div>

                <div class="mt-3 overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-[10px] uppercase font-bold text-gray-400 border-b border-gray-100">
                                <th class="pb-2.5">Visitor / Session</th>
                                <th class="pb-2.5">Location</th>
                                <th class="pb-2.5">Device & Browser</th>
                                <th class="pb-2.5">Entry Page / Source</th>
                                <th class="pb-2.5 text-center">Views</th>
                                <th class="pb-2.5 text-center">Duration</th>
                                <th class="pb-2.5 text-right">Last Seen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="s in recentSessions" :key="s.id" class="hover:bg-gray-50/80">
                                <td class="py-3 font-mono">
                                    <div class="font-bold text-gray-900 flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full" :class="s.is_active ? 'bg-emerald-500 animate-pulse' : 'bg-gray-300'"></span>
                                        {{ s.visitor_id_short }}
                                    </div>
                                    <div class="text-[10px] text-gray-400">{{ s.ip_hash_short || 'hashed' }}</div>
                                </td>
                                <td class="py-3">
                                    <div class="flex items-center gap-1.5">
                                        <span>{{ getFlag(s.country) }}</span>
                                        <span class="font-medium text-gray-800">{{ s.country_name || s.country || 'Unknown' }}</span>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <div class="font-medium text-gray-800">{{ s.device || 'Desktop' }} · {{ s.os || 'OS' }}</div>
                                    <div class="text-[10px] text-gray-400">{{ s.browser || 'Browser' }}</div>
                                </td>
                                <td class="py-3 max-w-[200px] truncate">
                                    <div class="font-bold text-emerald-800 truncate">{{ s.landing_page || '/' }}</div>
                                    <div class="text-[10px] text-gray-400 truncate">via {{ s.referrer_source || 'Direct' }}</div>
                                </td>
                                <td class="py-3 text-center font-bold text-gray-900">
                                    {{ s.page_views_count || 1 }}
                                </td>
                                <td class="py-3 text-center text-gray-700 font-mono">
                                    {{ formatSeconds(s.duration_seconds) }}
                                </td>
                                <td class="py-3 text-right text-gray-500 font-mono text-[11px]">
                                    {{ s.last_activity_time }}
                                </td>
                            </tr>
                            <tr v-if="!recentSessions?.length">
                                <td colspan="7" class="py-8 text-center text-gray-400 text-xs">No visitor sessions recorded yet.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
