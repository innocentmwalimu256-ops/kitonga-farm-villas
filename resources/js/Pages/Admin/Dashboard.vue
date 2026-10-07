<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    kpis: Object,
    charts: Object,
    recent_bookings: Array,
    live_rooms: {
        type: Array,
        default: () => [],
    },
    room_summary: {
        type: Object,
        default: () => ({ total: 0, occupied: 0, vacant: 0, arriving_today: 0, maintenance: 0 }),
    },
    filters: Object,
});

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-TZ', { style: 'currency', currency: 'TZS', maximumFractionDigits: 0 }).format(val || 0);
};

const formatDate = (isoString) => {
    if (!isoString) return '-';
    const d = new Date(isoString);
    if (isNaN(d.getTime())) return isoString;
    return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

const customDateForm = useForm({
    filter: 'custom',
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
});

const showCustomRange = ref(props.filters.active === 'custom');

const applyCustomDates = () => {
    customDateForm.get(route('admin.dashboard'));
};

// ─── LIVE ROOM OCCUPANCY FILTERING ──────────────────────────────────────────
const roomStatusFilter = ref('all'); // 'all', 'occupied', 'vacant', 'arriving_today', 'maintenance'
const roomSearchQuery = ref('');

const filteredRooms = computed(() => {
    return (props.live_rooms || []).filter(room => {
        // Status filter
        if (roomStatusFilter.value !== 'all' && room.occupancy_status !== roomStatusFilter.value) {
            return false;
        }
        // Text search
        if (roomSearchQuery.value.trim()) {
            const query = roomSearchQuery.value.toLowerCase();
            const matchName = (room.name || '').toLowerCase().includes(query);
            const matchType = (room.villa_type || '').toLowerCase().includes(query);
            const matchGuest = (room.guest_info?.guest_name || '').toLowerCase().includes(query);
            const matchRef = (room.guest_info?.reference || '').toLowerCase().includes(query);
            return matchName || matchType || matchGuest || matchRef;
        }
        return true;
    });
});
</script>

<template>
    <Head title="Admin Command Center" />

    <AuthenticatedLayout>
        <template #header>
            <div class="space-y-3">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <h2 class="text-lg sm:text-xl font-bold leading-tight text-gray-900 font-serif">
                                Command Center & Live Operations
                            </h2>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">Real-time hospitality KPIs, room occupancy, POS sales, and farm financials</p>
                    </div>

                    <!-- Quick POS action on mobile header -->
                    <div class="flex items-center gap-2 self-start sm:self-auto">
                        <Link 
                            :href="route('admin.pos.terminal')" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white rounded-lg text-xs font-bold shadow-2xs transition"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            Open POS
                        </Link>
                        <Link 
                            :href="route('admin.bookings.create')" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 rounded-lg text-xs font-bold shadow-2xs transition"
                        >
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            New Booking
                        </Link>
                    </div>
                </div>
                
                <!-- DATE RANGE FILTER BAR (Smooth Mobile Horizontal Scroll) -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1.5 pt-1 -mx-3 px-3 sm:mx-0 sm:px-0 text-xs no-scrollbar">
                    <Link :href="route('admin.dashboard', { filter: 'today' })" class="px-3 py-1.5 rounded-lg transition shrink-0 font-medium" :class="filters.active === 'today' ? 'bg-emerald-800 text-white font-bold shadow-2xs' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'">Today</Link>
                    <Link :href="route('admin.dashboard', { filter: 'yesterday' })" class="px-3 py-1.5 rounded-lg transition shrink-0 font-medium" :class="filters.active === 'yesterday' ? 'bg-emerald-800 text-white font-bold shadow-2xs' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'">Yesterday</Link>
                    <Link :href="route('admin.dashboard', { filter: 'last_7' })" class="px-3 py-1.5 rounded-lg transition shrink-0 font-medium" :class="filters.active === 'last_7' ? 'bg-emerald-800 text-white font-bold shadow-2xs' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'">7 Days</Link>
                    <Link :href="route('admin.dashboard', { filter: 'last_30' })" class="px-3 py-1.5 rounded-lg transition shrink-0 font-medium" :class="filters.active === 'last_30' ? 'bg-emerald-800 text-white font-bold shadow-2xs' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'">30 Days</Link>
                    <Link :href="route('admin.dashboard', { filter: 'this_month' })" class="px-3 py-1.5 rounded-lg transition shrink-0 font-medium" :class="filters.active === 'this_month' ? 'bg-emerald-800 text-white font-bold shadow-2xs' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'">This Month</Link>
                    <Link :href="route('admin.dashboard', { filter: 'last_month' })" class="px-3 py-1.5 rounded-lg transition shrink-0 font-medium" :class="filters.active === 'last_month' ? 'bg-emerald-800 text-white font-bold shadow-2xs' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'">Last Month</Link>
                    <Link :href="route('admin.dashboard', { filter: 'this_year' })" class="px-3 py-1.5 rounded-lg transition shrink-0 font-medium" :class="filters.active === 'this_year' ? 'bg-emerald-800 text-white font-bold shadow-2xs' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'">This Year</Link>
                    <Link :href="route('admin.dashboard', { filter: 'last_year' })" class="px-3 py-1.5 rounded-lg transition shrink-0 font-medium" :class="filters.active === 'last_year' ? 'bg-emerald-800 text-white font-bold shadow-2xs' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'">Last Year</Link>
                    <button @click="showCustomRange = !showCustomRange" class="px-3 py-1.5 rounded-lg transition shrink-0 font-bold border border-gray-300" :class="showCustomRange ? 'bg-gray-800 text-white' : 'bg-white hover:bg-gray-100 text-gray-700'">
                        {{ showCustomRange ? 'Hide Dates' : 'Custom Dates...' }}
                    </button>
                </div>
            </div>
            
            <!-- CUSTOM RANGE PANEL -->
            <div v-if="showCustomRange" class="mt-3 p-4 bg-white border border-gray-200 rounded-xl shadow-xs flex flex-col sm:flex-row items-stretch sm:items-end gap-3 max-w-xl animate-in fade-in duration-150">
                <div class="flex-1">
                    <label class="text-[10px] font-bold uppercase text-gray-500 block mb-1">Start Date</label>
                    <input v-model="customDateForm.start_date" type="date" class="text-xs rounded-lg border-gray-300 w-full focus:border-emerald-600 focus:ring-emerald-600" />
                </div>
                <div class="flex-1">
                    <label class="text-[10px] font-bold uppercase text-gray-500 block mb-1">End Date</label>
                    <input v-model="customDateForm.end_date" type="date" class="text-xs rounded-lg border-gray-300 w-full focus:border-emerald-600 focus:ring-emerald-600" />
                </div>
                <button @click="applyCustomDates" class="px-5 py-2.5 bg-emerald-700 active:bg-emerald-800 text-white font-bold rounded-lg text-xs shadow-sm transition">Apply Filter</button>
            </div>
        </template>

        <div class="py-4 sm:py-6">
            <div class="mx-auto max-w-7xl px-3 sm:px-6 lg:px-8 space-y-5 sm:space-y-6">

                <!-- LIVE OPERATIONS HERO BANNER -->
                <div class="bg-gradient-to-br from-[#14231C] via-[#1b2e25] to-[#0f1a14] p-4 sm:p-6 lg:p-7 rounded-2xl text-white shadow-md border border-emerald-950/50 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-5">
                    <div class="space-y-1 w-full lg:w-auto">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-[10px] font-bold uppercase tracking-wider border border-emerald-500/30">
                                Live Overview
                            </span>
                        </div>
                        <h3 class="text-sm sm:text-base font-semibold text-gray-200">Today's Operating Revenue</h3>
                        <p class="text-2xl sm:text-3xl lg:text-4xl font-black font-mono text-[#E4A853] tracking-tight">
                            {{ formatCurrency(kpis.today_revenue) }}
                        </p>
                    </div>

                    <!-- 4-Box Stats on Mobile & Desktop -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3 w-full lg:w-auto">
                        <div class="bg-white/10 backdrop-blur-xs p-3 rounded-xl border border-white/10 text-center">
                            <span class="text-[10px] text-emerald-200/80 font-bold block uppercase tracking-wider">7-Day Rev</span>
                            <span class="font-mono text-xs sm:text-sm font-bold text-white block mt-0.5">{{ formatCurrency(kpis.seven_days_revenue) }}</span>
                        </div>
                        <div class="bg-white/10 backdrop-blur-xs p-3 rounded-xl border border-white/10 text-center">
                            <span class="text-[10px] text-emerald-200/80 font-bold block uppercase tracking-wider">30-Day Rev</span>
                            <span class="font-mono text-xs sm:text-sm font-bold text-white block mt-0.5">{{ formatCurrency(kpis.thirty_days_revenue) }}</span>
                        </div>
                        <div class="bg-white/10 backdrop-blur-xs p-3 rounded-xl border border-white/10 text-center">
                            <span class="text-[10px] text-emerald-200/80 font-bold block uppercase tracking-wider">Month Rev</span>
                            <span class="font-mono text-xs sm:text-sm font-bold text-white block mt-0.5">{{ formatCurrency(kpis.monthly_revenue) }}</span>
                        </div>
                        <Link :href="route('admin.products.index')" class="bg-white/10 hover:bg-white/15 active:bg-white/20 backdrop-blur-xs p-3 rounded-xl border border-white/10 text-center block transition">
                            <span class="text-[10px] text-emerald-200/80 font-bold block uppercase tracking-wider">Low Stock</span>
                            <span class="font-mono text-xs sm:text-sm font-bold text-red-300 block mt-0.5">{{ kpis.low_stock_count }} items</span>
                        </Link>
                    </div>
                </div>

                <!-- DRILL-DOWN KPI CARDS -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
                    <!-- TOTAL BOOKINGS -->
                    <Link :href="route('admin.bookings.index')" class="bg-white p-4 sm:p-5 rounded-2xl shadow-xs border border-gray-200/80 flex flex-col justify-between hover:border-emerald-500 active:bg-gray-50/50 transition group">
                        <div>
                            <div class="flex justify-between items-center">
                                <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Reservations</span>
                                <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            </div>
                            <h3 class="text-xl sm:text-2xl font-black text-gray-900 mt-2">{{ kpis.total_bookings }} <span class="text-xs font-medium text-gray-500 font-sans">bookings</span></h3>
                        </div>
                        <div class="mt-3 text-xs text-gray-500 border-t border-gray-100 pt-2.5 flex justify-between items-center">
                            <span>Pending: <strong class="text-amber-600 font-bold">{{ kpis.pending_bookings }}</strong></span>
                            <span class="text-emerald-700 font-bold text-[11px] group-hover:translate-x-0.5 transition-transform">List ➔</span>
                        </div>
                    </Link>

                    <!-- TOTAL REVENUE -->
                    <Link :href="route('admin.pos.index')" class="bg-white p-4 sm:p-5 rounded-2xl shadow-xs border border-gray-200/80 flex flex-col justify-between hover:border-emerald-500 active:bg-gray-50/50 transition group">
                        <div>
                            <div class="flex justify-between items-center">
                                <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Filtered Revenue</span>
                                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                            </div>
                            <h3 class="text-xl sm:text-2xl font-black font-mono text-emerald-800 mt-2">{{ formatCurrency(kpis.total_revenue) }}</h3>
                        </div>
                        <div class="mt-3 text-[11px] text-gray-500 border-t border-gray-100 pt-2.5 flex justify-between items-center">
                            <span class="truncate">Villas: {{ formatCurrency(kpis.accommodation_revenue) }}</span>
                            <span class="text-emerald-700 font-bold group-hover:translate-x-0.5 transition-transform">Sales ➔</span>
                        </div>
                    </Link>

                    <!-- TOTAL EXPENSES -->
                    <Link :href="route('admin.expenses.index')" class="bg-white p-4 sm:p-5 rounded-2xl shadow-xs border border-gray-200/80 flex flex-col justify-between hover:border-red-400 active:bg-gray-50/50 transition group">
                        <div>
                            <div class="flex justify-between items-center">
                                <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Total Expenses</span>
                                <div class="w-7 h-7 rounded-lg bg-red-50 text-red-600 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                            </div>
                            <h3 class="text-xl sm:text-2xl font-black font-mono text-red-600 mt-2">{{ formatCurrency(kpis.expenses) }}</h3>
                        </div>
                        <div class="mt-3 text-[11px] text-gray-500 border-t border-gray-100 pt-2.5 flex justify-between items-center">
                            <span>Operating payouts</span>
                            <span class="text-red-600 font-bold group-hover:translate-x-0.5 transition-transform">Expenses ➔</span>
                        </div>
                    </Link>

                    <!-- NET PROFIT -->
                    <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-xs border border-gray-200/80 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center">
                                <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Net Profit</span>
                                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-800 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                </div>
                            </div>
                            <h3 class="text-xl sm:text-2xl font-black font-mono mt-2" :class="kpis.net_profit >= 0 ? 'text-emerald-700' : 'text-red-600'">
                                {{ formatCurrency(kpis.net_profit) }}
                            </h3>
                        </div>
                        <div class="mt-3 text-[11px] text-gray-500 border-t border-gray-100 pt-2.5 flex justify-between items-center">
                            <span>Occupancy: <strong class="text-gray-900">{{ kpis.occupancy_percentage }}%</strong></span>
                            <span class="text-amber-700 font-bold" v-if="kpis.outstanding_balance > 0">Unpaid: {{ formatCurrency(kpis.outstanding_balance) }}</span>
                        </div>
                    </div>
                </div>

                <!-- ════════════════════════════════════════════════════════════════ -->
                <!-- WEBSITE VISITOR ANALYTICS QUICK LAUNCH BANNER                   -->
                <!-- ════════════════════════════════════════════════════════════════ -->
                <div class="bg-gradient-to-r from-indigo-900 via-slate-900 to-[#14231C] p-4 sm:p-5 rounded-2xl text-white shadow-xs border border-indigo-950 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-500/20 text-indigo-300 border border-indigo-400/30 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h4 class="font-bold text-sm sm:text-base text-white">Website Visitor Analytics &amp; Live Traffic</h4>
                                <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-[10px] font-bold uppercase tracking-wider border border-emerald-500/30 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    <span>{{ kpis.live_visitors_count ?? 0 }} Online Now</span>
                                </span>
                            </div>
                            <p class="text-xs text-slate-300 mt-0.5">
                                Real-time online visitors, traffic sources (Google, WhatsApp, Instagram), top viewed villas, and conversion leads.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <div class="hidden md:block text-right pr-2">
                            <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold block">Total Visitors</span>
                            <span class="font-mono font-bold text-sm text-white">{{ kpis.total_website_visitors ?? 0 }}</span>
                        </div>
                        <Link
                            :href="route('admin.analytics.index')"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#C98A3E] hover:bg-[#b07833] text-white text-xs font-bold shadow-xs transition shrink-0 self-stretch sm:self-auto justify-center"
                        >
                            <span>Open Analytics Desk</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </Link>
                    </div>
                </div>

                <!-- ════════════════════════════════════════════════════════════════ -->
                <!-- REAL-TIME ROOM & VILLA OCCUPANCY STATUS GRID (HALI YA VYUMBA)    -->
                <!-- ════════════════════════════════════════════════════════════════ -->
                <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-xs border border-gray-200/90 space-y-5">
                    
                    <!-- Header with Title & Quick Counts -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-gray-100 pb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                                <h3 class="text-sm sm:text-base font-bold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                                    <svg class="w-5 h-5 text-emerald-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <span>Hali ya Vyumba &amp; Villas (Live Room Occupancy)</span>
                                </h3>
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Ufuatiliaji wa vyumba vilivyo wazi, vilivyojaa, wageni waliopo ndani, na wanaowasili leo kwa muda halisi.
                            </p>
                        </div>

                        <!-- Room Summary Badges -->
                        <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 text-xs">
                            <button 
                                type="button" 
                                @click="roomStatusFilter = 'all'"
                                class="px-2.5 py-1 rounded-lg border font-bold transition flex items-center gap-1.5 cursor-pointer"
                                :class="roomStatusFilter === 'all' ? 'bg-[#14231C] text-white border-[#14231C]' : 'bg-gray-50 hover:bg-gray-100 text-gray-700 border-gray-200'"
                            >
                                <span>Vyumba Vyote</span>
                                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="roomStatusFilter === 'all' ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-800'">{{ room_summary.total }}</span>
                            </button>

                            <button 
                                type="button" 
                                @click="roomStatusFilter = 'occupied'"
                                class="px-2.5 py-1 rounded-lg border font-bold transition flex items-center gap-1.5 cursor-pointer"
                                :class="roomStatusFilter === 'occupied' ? 'bg-rose-700 text-white border-rose-700' : 'bg-rose-50 hover:bg-rose-100 text-rose-800 border-rose-200'"
                            >
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500" :class="{ 'animate-pulse': room_summary.occupied > 0 }"></span>
                                <span>Vimejaa (Occupied)</span>
                                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="roomStatusFilter === 'occupied' ? 'bg-white/20 text-white' : 'bg-rose-200 text-rose-900'">{{ room_summary.occupied }}</span>
                            </button>

                            <button 
                                type="button" 
                                @click="roomStatusFilter = 'vacant'"
                                class="px-2.5 py-1 rounded-lg border font-bold transition flex items-center gap-1.5 cursor-cursor"
                                :class="roomStatusFilter === 'vacant' ? 'bg-emerald-700 text-white border-emerald-700' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border-emerald-200'"
                            >
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span>Viko Wazi (Vacant)</span>
                                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="roomStatusFilter === 'vacant' ? 'bg-white/20 text-white' : 'bg-emerald-200 text-emerald-900'">{{ room_summary.vacant }}</span>
                            </button>

                            <button 
                                type="button" 
                                @click="roomStatusFilter = 'arriving_today'"
                                class="px-2.5 py-1 rounded-lg border font-bold transition flex items-center gap-1.5 cursor-pointer"
                                :class="roomStatusFilter === 'arriving_today' ? 'bg-amber-600 text-white border-amber-600' : 'bg-amber-50 hover:bg-amber-100 text-amber-800 border-amber-200'"
                            >
                                <span>Wanawasili Leo</span>
                                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="roomStatusFilter === 'arriving_today' ? 'bg-white/20 text-white' : 'bg-amber-200 text-amber-900'">{{ room_summary.arriving_today }}</span>
                            </button>

                            <button 
                                v-if="room_summary.maintenance > 0"
                                type="button" 
                                @click="roomStatusFilter = 'maintenance'"
                                class="px-2.5 py-1 rounded-lg border font-bold transition flex items-center gap-1.5 cursor-pointer"
                                :class="roomStatusFilter === 'maintenance' ? 'bg-gray-700 text-white border-gray-700' : 'bg-gray-100 hover:bg-gray-200 text-gray-700 border-gray-200'"
                            >
                                <span>Marekebisho</span>
                                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-gray-200 text-gray-800">{{ room_summary.maintenance }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Search Input Bar -->
                    <div class="flex items-center justify-between gap-3">
                        <div class="relative flex-1 max-w-md">
                            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input 
                                v-model="roomSearchQuery" 
                                type="text" 
                                placeholder="Tafuta chumba, jina la villa, au jina la mgeni..." 
                                class="w-full text-xs pl-9 pr-8 py-2 rounded-xl border border-gray-300 focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600"
                            />
                            <button 
                                v-if="roomSearchQuery" 
                                @click="roomSearchQuery = ''" 
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs font-bold"
                            >
                                ✕
                            </button>
                        </div>

                        <div class="flex items-center gap-2">
                            <Link 
                                :href="route('admin.bookings.calendar')" 
                                class="text-xs font-bold text-emerald-800 hover:text-emerald-950 flex items-center gap-1"
                            >
                                <span>Calendar Grid ➔</span>
                            </Link>
                        </div>
                    </div>

                    <!-- ROOM CARDS GRID -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div 
                            v-for="room in filteredRooms" 
                            :key="room.id" 
                            class="rounded-2xl border p-4.5 flex flex-col justify-between transition-all duration-200 shadow-2xs hover:shadow-xs"
                            :class="{
                                'border-rose-300 bg-rose-50/20': room.occupancy_status === 'occupied',
                                'border-emerald-300 bg-emerald-50/20': room.occupancy_status === 'vacant',
                                'border-amber-300 bg-amber-50/30': room.occupancy_status === 'arriving_today',
                                'border-gray-300 bg-gray-50/60': room.occupancy_status === 'maintenance',
                            }"
                        >
                            <!-- Top: Room Name & Status Badge -->
                            <div class="space-y-3">
                                <div class="flex justify-between items-start gap-2">
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full"
                                                :class="{
                                                    'bg-rose-500 animate-pulse': room.occupancy_status === 'occupied',
                                                    'bg-emerald-500': room.occupancy_status === 'vacant',
                                                    'bg-amber-500 animate-pulse': room.occupancy_status === 'arriving_today',
                                                    'bg-gray-400': room.occupancy_status === 'maintenance',
                                                }"
                                            ></span>
                                            <h4 class="font-serif font-bold text-base text-gray-900">{{ room.name }}</h4>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ room.villa_type }}</p>
                                    </div>

                                    <!-- Status Pill -->
                                    <span 
                                        class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider"
                                        :class="{
                                            'bg-rose-100 text-rose-800 border border-rose-200': room.occupancy_status === 'occupied',
                                            'bg-emerald-100 text-emerald-800 border border-emerald-200': room.occupancy_status === 'vacant',
                                            'bg-amber-100 text-amber-800 border border-amber-200': room.occupancy_status === 'arriving_today',
                                            'bg-gray-200 text-gray-700 border border-gray-300': room.occupancy_status === 'maintenance',
                                        }"
                                    >
                                        {{ room.occupancy_status === 'occupied' ? 'IMEJAA (Occupied)' : (room.occupancy_status === 'vacant' ? 'IPO WAZI (Vacant)' : (room.occupancy_status === 'arriving_today' ? 'ANAWASILI LEO' : 'MAREKEBISHO')) }}
                                    </span>
                                </div>

                                <!-- Middle Content Depends on Status -->
                                <!-- 1. OCCUPIED DETAILS -->
                                <div v-if="room.occupancy_status === 'occupied' && room.guest_info" class="bg-white/90 p-3 rounded-xl border border-rose-100 space-y-2 text-xs">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <span class="text-[10px] uppercase font-bold text-gray-400 block">Mgeni Aliyepo Ndani</span>
                                            <span class="font-bold text-gray-900 text-sm">{{ room.guest_info.guest_name }}</span>
                                            <span v-if="room.guest_info.guest_phone" class="text-gray-500 font-mono text-[11px] block">{{ room.guest_info.guest_phone }}</span>
                                        </div>
                                        <span class="font-mono text-[10px] text-gray-500 font-bold bg-gray-100 px-1.5 py-0.5 rounded">
                                            {{ room.guest_info.reference }}
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2 pt-1 border-t border-gray-100 text-[11px]">
                                        <div>
                                            <span class="text-gray-400 block text-[10px]">Check-In:</span>
                                            <span class="font-semibold text-gray-800">{{ room.guest_info.check_in }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-400 block text-[10px]">Check-Out:</span>
                                            <span class="font-semibold text-rose-700 font-bold">{{ room.guest_info.check_out }}</span>
                                        </div>
                                    </div>

                                    <div class="flex justify-between items-center pt-1 border-t border-gray-100 text-[11px]">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-gray-500">Wageni: <strong>{{ room.guest_info.guests_count }}</strong></span>
                                            <span v-if="room.guest_info.has_id_document" class="text-[9px] px-1.5 py-0.2 bg-emerald-100 text-emerald-800 font-bold rounded">
                                                ID Verified
                                            </span>
                                        </div>
                                        <div v-if="room.guest_info.balance > 0" class="text-red-600 font-bold text-[10px] font-mono">
                                            Baki: {{ formatCurrency(room.guest_info.balance) }}
                                        </div>
                                        <div v-else class="text-emerald-700 font-bold text-[10px]">
                                            Paid in Full
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. ARRIVING TODAY DETAILS -->
                                <div v-else-if="room.occupancy_status === 'arriving_today' && room.guest_info" class="bg-white/90 p-3 rounded-xl border border-amber-100 space-y-2 text-xs">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <span class="text-[10px] uppercase font-bold text-amber-700 block">Mgeni Anawasili Leo</span>
                                            <span class="font-bold text-gray-900 text-sm">{{ room.guest_info.guest_name }}</span>
                                            <span v-if="room.guest_info.guest_phone" class="text-gray-500 font-mono text-[11px] block">{{ room.guest_info.guest_phone }}</span>
                                        </div>
                                        <span class="font-mono text-[10px] text-gray-500 font-bold bg-gray-100 px-1.5 py-0.5 rounded">
                                            {{ room.guest_info.reference }}
                                        </span>
                                    </div>

                                    <div class="flex justify-between items-center pt-1 border-t border-gray-100 text-[11px]">
                                        <span class="text-gray-600">Kuondoka: <strong>{{ room.guest_info.check_out }}</strong></span>
                                        <span v-if="room.guest_info.has_id_document" class="text-[9px] px-1.5 py-0.2 bg-emerald-100 text-emerald-800 font-bold rounded">
                                            Digital ID Ready
                                        </span>
                                    </div>
                                </div>

                                <!-- 3. VACANT & READY DETAILS -->
                                <div v-else-if="room.occupancy_status === 'vacant'" class="bg-white/80 p-3 rounded-xl border border-emerald-100 space-y-2 text-xs">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-1.5 text-emerald-800 font-bold text-xs">
                                            <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                            <span>Chumba Kiko Wazi &amp; Safi</span>
                                        </div>
                                        <span class="text-[10px] font-mono font-bold text-gray-700">{{ formatCurrency(room.base_price) }}/usiku</span>
                                    </div>
                                    <p class="text-[11px] text-gray-500 leading-relaxed">
                                        Tayari kupokea mgeni mpya au kuthibitisha reservation ya leo.
                                    </p>
                                </div>

                                <!-- 4. MAINTENANCE DETAILS -->
                                <div v-else class="bg-white/80 p-3 rounded-xl border border-gray-200 space-y-1.5 text-xs text-gray-600">
                                    <span class="text-[10px] uppercase font-bold text-gray-400 block">Sababu ya Kufungwa</span>
                                    <p class="text-xs font-semibold text-gray-800">{{ room.block_reason || 'Matengenezo na Usafi Maalum' }}</p>
                                </div>
                            </div>

                            <!-- Bottom Action Button -->
                            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                                <div class="text-[10px] text-gray-400">
                                    Max {{ room.capacity }} Guests
                                </div>

                                <div v-if="room.occupancy_status === 'occupied' && room.guest_info">
                                    <Link 
                                        :href="route('admin.bookings.show', room.guest_info.booking_id)" 
                                        class="px-3 py-1.5 bg-rose-700 hover:bg-rose-800 text-white rounded-lg font-bold text-[11px] transition inline-flex items-center gap-1 shadow-2xs"
                                    >
                                        <span>Tazama Booking</span>
                                        <span>➔</span>
                                    </Link>
                                </div>

                                <div v-else-if="room.occupancy_status === 'arriving_today' && room.guest_info">
                                    <Link 
                                        :href="route('admin.bookings.show', room.guest_info.booking_id)" 
                                        class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-bold text-[11px] transition inline-flex items-center gap-1 shadow-2xs"
                                    >
                                        <span>Check-In Desk</span>
                                        <span>➔</span>
                                    </Link>
                                </div>

                                <div v-else-if="room.occupancy_status === 'vacant'">
                                    <Link 
                                        :href="route('admin.bookings.create')" 
                                        class="px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg font-bold text-[11px] transition inline-flex items-center gap-1 shadow-2xs"
                                    >
                                        <span>Sajili Mgeni</span>
                                        <span>+</span>
                                    </Link>
                                </div>

                                <div v-else>
                                    <Link 
                                        :href="route('admin.accommodation.index')" 
                                        class="px-3 py-1.5 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-bold text-[11px] transition"
                                    >
                                        Fungua Chumba
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="filteredRooms.length === 0" class="py-10 text-center text-gray-400 space-y-2">
                        <p class="font-bold text-sm text-gray-600">Hakuna vyumba vinavyolingana na kigezo ulichochagua.</p>
                        <button @click="roomStatusFilter = 'all'; roomSearchQuery = ''" class="text-xs text-emerald-700 underline font-bold">Onyesha vyumba vyote</button>
                    </div>

                </div>

                <!-- CHARTS ROW (REVENUE TREND & REVENUE BY CATEGORY) -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 sm:gap-6">
                    <!-- REVENUE VS EXPENSES BAR GRAPH -->
                    <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-xs border border-gray-200/80 lg:col-span-2 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
                            <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                                Daily Revenue vs Expenses Trend
                            </h3>
                            <div class="flex items-center gap-3 text-[11px] font-bold">
                                <span class="flex items-center"><span class="w-2.5 h-2.5 bg-emerald-600 rounded-sm mr-1"></span> Revenue</span>
                                <span class="flex items-center"><span class="w-2.5 h-2.5 bg-red-400 rounded-sm mr-1"></span> Expenses</span>
                            </div>
                        </div>

                        <!-- Bar chart container with smooth horizontal scroll on mobile -->
                        <div class="overflow-x-auto pb-2 -mx-2 px-2">
                            <div class="h-56 sm:h-64 min-w-[320px] flex items-end space-x-1.5 pt-6 border-b border-gray-100 pb-2">
                                <div v-for="(rev, idx) in charts.revenue" :key="idx" class="flex-1 flex flex-col items-center group relative h-full justify-end min-w-[14px]">
                                    <!-- Tooltip -->
                                    <div class="absolute bottom-full mb-1 text-[10px] bg-gray-900 text-white rounded-lg p-2 hidden group-hover:block whitespace-nowrap z-20 font-mono shadow-lg pointer-events-none">
                                        <div class="text-gray-300 font-sans text-[9px]">{{ charts.labels[idx] }}</div>
                                        <div class="text-emerald-400">Rev: {{ formatCurrency(rev) }}</div>
                                        <div class="text-red-400">Exp: {{ formatCurrency(charts.expenses[idx]) }}</div>
                                    </div>
                                    <div class="w-full flex space-x-0.5 items-end justify-center h-full">
                                        <div class="bg-emerald-600 w-full max-w-[10px] rounded-t-sm" :style="{ height: `${Math.min(100, (rev / Math.max(1, ...charts.revenue)) * 100)}%` }"></div>
                                        <div class="bg-red-400 w-full max-w-[10px] rounded-t-sm" :style="{ height: `${Math.min(100, (charts.expenses[idx] / Math.max(1, ...charts.revenue)) * 100)}%` }"></div>
                                    </div>
                                    <span class="text-[8px] text-gray-400 mt-1.5 truncate w-full text-center block">{{ charts.labels[idx] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- REVENUE BY CATEGORY & PAYMENT METHODS -->
                    <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-xs border border-gray-200/80 space-y-5">
                        <div>
                            <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider mb-3">Revenue by Source</h3>
                            <div class="space-y-3">
                                <div>
                                    <div class="flex justify-between text-xs font-bold text-gray-700">
                                        <span>Villa Accommodation</span>
                                        <span class="font-mono text-emerald-800">{{ formatCurrency(kpis.accommodation_revenue) }}</span>
                                    </div>
                                    <div class="w-full bg-gray-100 h-2 rounded-full mt-1.5 overflow-hidden">
                                        <div class="bg-emerald-700 h-full rounded-full transition-all duration-500" :style="{ width: `${kpis.total_revenue > 0 ? (kpis.accommodation_revenue / kpis.total_revenue) * 100 : 0}%` }"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-xs font-bold text-gray-700">
                                        <span>Farm Tours & Experiences</span>
                                        <span class="font-mono text-indigo-700">{{ formatCurrency(kpis.tour_revenue) }}</span>
                                    </div>
                                    <div class="w-full bg-gray-100 h-2 rounded-full mt-1.5 overflow-hidden">
                                        <div class="bg-indigo-600 h-full rounded-full transition-all duration-500" :style="{ width: `${kpis.total_revenue > 0 ? (kpis.tour_revenue / kpis.total_revenue) * 100 : 0}%` }"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-xs font-bold text-gray-700">
                                        <span>Bar & Beverage</span>
                                        <span class="font-mono text-amber-700">{{ formatCurrency(kpis.bar_revenue) }}</span>
                                    </div>
                                    <div class="w-full bg-gray-100 h-2 rounded-full mt-1.5 overflow-hidden">
                                        <div class="bg-amber-500 h-full rounded-full transition-all duration-500" :style="{ width: `${kpis.total_revenue > 0 ? (kpis.bar_revenue / kpis.total_revenue) * 100 : 0}%` }"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-xs font-bold text-gray-700">
                                        <span>Farm Produce & Shop</span>
                                        <span class="font-mono text-sky-700">{{ formatCurrency(kpis.product_revenue) }}</span>
                                    </div>
                                    <div class="w-full bg-gray-100 h-2 rounded-full mt-1.5 overflow-hidden">
                                        <div class="bg-sky-500 h-full rounded-full transition-all duration-500" :style="{ width: `${kpis.total_revenue > 0 ? (kpis.product_revenue / kpis.total_revenue) * 100 : 0}%` }"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-4">
                            <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider mb-2.5">Billing Methods</h3>
                            <div class="grid grid-cols-2 gap-2">
                                <div v-for="pay in charts.payment_methods" :key="pay.method" class="bg-gray-50 border border-gray-200/80 rounded-xl p-2 text-center">
                                    <span class="text-[9px] font-bold text-gray-400 uppercase block tracking-wider">{{ pay.method }}</span>
                                    <span class="text-xs font-mono font-bold text-gray-900 block mt-0.5">{{ formatCurrency(pay.total_amount) }}</span>
                                </div>
                                <div v-if="charts.payment_methods.length === 0" class="col-span-2 text-center text-xs text-gray-400 italic py-2">
                                    No payments registered yet.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RECENT RESERVATIONS -->
                <div v-if="recent_bookings && recent_bookings.length > 0" class="bg-white p-4 sm:p-6 rounded-2xl shadow-xs border border-gray-200/80 space-y-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Recent Bookings & Arrivals
                            </h3>
                            <p class="text-[11px] text-gray-500">Latest guest reservations and check-in schedules</p>
                        </div>
                        <Link :href="route('admin.bookings.index')" class="text-xs font-bold text-emerald-700 hover:text-emerald-800">
                            View All ➔
                        </Link>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 md:hidden">
                        <div v-for="booking in recent_bookings" :key="booking.id" class="p-3.5 rounded-xl border border-gray-200/80 bg-gray-50/50 space-y-2">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="text-xs font-bold text-gray-900">{{ booking.customer ? booking.customer.name : booking.guest_name || 'Guest' }}</div>
                                    <div class="text-[10px] text-gray-500 font-mono">{{ booking.reference || `#${booking.id}` }}</div>
                                </div>
                                <span 
                                    class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase"
                                    :class="{
                                        'bg-emerald-100 text-emerald-800': booking.status === 'confirmed' || booking.status === 'checked_in',
                                        'bg-amber-100 text-amber-800': booking.status === 'pending',
                                        'bg-gray-100 text-gray-700': booking.status === 'checked_out',
                                        'bg-red-100 text-red-700': booking.status === 'cancelled',
                                    }"
                                >
                                    {{ booking.status }}
                                </span>
                            </div>
                            <div class="text-[11px] text-gray-600 flex justify-between">
                                <span>{{ booking.unit && booking.unit.type ? booking.unit.type.name : 'Villa' }}</span>
                                <span class="font-mono font-bold text-emerald-800">{{ formatCurrency(booking.total) }}</span>
                            </div>
                            <div class="text-[10px] text-gray-400 border-t border-gray-200/60 pt-1.5 flex justify-between">
                                <span>In: {{ formatDate(booking.check_in) }}</span>
                                <span>Out: {{ formatDate(booking.check_out) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop Table View -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100 text-[11px] text-gray-400 uppercase font-bold tracking-wider">
                                    <th class="p-3">Reference</th>
                                    <th class="p-3">Guest Name</th>
                                    <th class="p-3">Villa</th>
                                    <th class="p-3">Check In</th>
                                    <th class="p-3">Check Out</th>
                                    <th class="p-3">Amount</th>
                                    <th class="p-3 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="b in recent_bookings" :key="b.id" class="hover:bg-gray-50/50">
                                    <td class="p-3 font-mono font-bold text-gray-900">{{ b.reference || `#${b.id}` }}</td>
                                    <td class="p-3 font-semibold text-gray-900">{{ b.customer ? b.customer.name : b.guest_name || 'Guest' }}</td>
                                    <td class="p-3 text-gray-600">{{ b.unit && b.unit.type ? b.unit.type.name : 'Villa' }}</td>
                                    <td class="p-3 text-gray-500">{{ formatDate(b.check_in) }}</td>
                                    <td class="p-3 text-gray-500">{{ formatDate(b.check_out) }}</td>
                                    <td class="p-3 font-mono font-bold text-emerald-800">{{ formatCurrency(b.total) }}</td>
                                    <td class="p-3 text-right">
                                        <span 
                                            class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase inline-block"
                                            :class="{
                                                'bg-emerald-100 text-emerald-800': b.status === 'confirmed' || b.status === 'checked_in',
                                                'bg-amber-100 text-amber-800': b.status === 'pending',
                                                'bg-gray-100 text-gray-700': b.status === 'checked_out',
                                                'bg-red-100 text-red-700': b.status === 'cancelled',
                                            }"
                                        >
                                            {{ b.status }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- VILLA PERFORMANCE & TOP PRODUCTS -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 sm:gap-6">
                    <!-- VILLA PERFORMANCE -->
                    <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-xs border border-gray-200/80 space-y-3">
                        <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Accommodation Performance</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="bg-gray-50/80 border-b border-gray-100 text-gray-400 uppercase font-bold text-[10px] tracking-wider">
                                        <th class="p-3">Villa Model</th>
                                        <th class="p-3 text-right">Revenue Generated</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="perf in charts.villa_performance" :key="perf.name" class="border-b border-gray-100 hover:bg-gray-50/50">
                                        <td class="p-3 font-bold text-gray-900">{{ perf.name }}</td>
                                        <td class="p-3 text-right font-mono font-bold text-emerald-700">{{ formatCurrency(perf.total_revenue) }}</td>
                                    </tr>
                                    <tr v-if="charts.villa_performance.length === 0">
                                        <td colspan="2" class="p-6 text-center text-gray-400 italic">No bookings performance logs in this date range.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TOP PRODUCTS -->
                    <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-xs border border-gray-200/80 space-y-3">
                        <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Top-Selling Farm / Bar Produce</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="bg-gray-50/80 border-b border-gray-100 text-gray-400 uppercase font-bold text-[10px] tracking-wider">
                                        <th class="p-3">Product</th>
                                        <th class="p-3 text-right">Quantity Sold</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="prod in charts.top_products" :key="prod.name" class="border-b border-gray-100 hover:bg-gray-50/50">
                                        <td class="p-3 font-bold text-gray-900">{{ prod.name }}</td>
                                        <td class="p-3 text-right font-mono font-bold text-indigo-700">{{ prod.total_qty }} units</td>
                                    </tr>
                                    <tr v-if="charts.top_products.length === 0">
                                        <td colspan="2" class="p-6 text-center text-gray-400 italic">No product sales logged in this date range.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- QUICK ACTION SHORTCUTS -->
                <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-xs border border-gray-200/80 space-y-4">
                    <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Quick Action Shortcuts</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                        <Link :href="route('admin.pos.terminal')" class="p-3.5 border border-gray-200/80 rounded-xl hover:border-emerald-500 active:bg-emerald-50/50 hover:bg-emerald-50/30 transition flex items-center gap-3 bg-white shadow-2xs">
                            <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-xs flex-shrink-0">POS</div>
                            <div class="min-w-0">
                                <h4 class="text-xs font-bold text-gray-900 truncate">POS Terminal</h4>
                                <p class="text-[10px] text-gray-500 truncate">Sales checkout</p>
                            </div>
                        </Link>
                        <Link :href="route('admin.bookings.calendar')" class="p-3.5 border border-gray-200/80 rounded-xl hover:border-emerald-500 active:bg-emerald-50/50 hover:bg-emerald-50/30 transition flex items-center gap-3 bg-white shadow-2xs">
                            <div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-800 flex items-center justify-center font-bold text-xs flex-shrink-0">CAL</div>
                            <div class="min-w-0">
                                <h4 class="text-xs font-bold text-gray-900 truncate">Calendar</h4>
                                <p class="text-[10px] text-gray-500 truncate">Guest grid</p>
                            </div>
                        </Link>
                        <Link :href="route('admin.products.index')" class="p-3.5 border border-gray-200/80 rounded-xl hover:border-emerald-500 active:bg-emerald-50/50 hover:bg-emerald-50/30 transition flex items-center gap-3 bg-white shadow-2xs">
                            <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-xs flex-shrink-0">PRD</div>
                            <div class="min-w-0">
                                <h4 class="text-xs font-bold text-gray-900 truncate">Products</h4>
                                <p class="text-[10px] text-gray-500 truncate">Catalog & Prices</p>
                            </div>
                        </Link>
                        <Link :href="route('admin.expenses.index')" class="p-3.5 border border-gray-200/80 rounded-xl hover:border-emerald-500 active:bg-emerald-50/50 hover:bg-emerald-50/30 transition flex items-center gap-3 bg-white shadow-2xs">
                            <div class="w-9 h-9 rounded-xl bg-red-100 text-red-800 flex items-center justify-center font-bold text-xs flex-shrink-0">EXP</div>
                            <div class="min-w-0">
                                <h4 class="text-xs font-bold text-gray-900 truncate">Expenses</h4>
                                <p class="text-[10px] text-gray-500 truncate">Costs log</p>
                            </div>
                        </Link>
                        <Link :href="route('admin.settings.index')" class="p-3.5 border border-gray-200/80 rounded-xl hover:border-emerald-500 active:bg-emerald-50/50 hover:bg-emerald-50/30 transition flex items-center gap-3 bg-white shadow-2xs">
                            <div class="w-9 h-9 rounded-xl bg-gray-100 text-gray-800 flex items-center justify-center font-bold text-xs flex-shrink-0">SET</div>
                            <div class="min-w-0">
                                <h4 class="text-xs font-bold text-gray-900 truncate">Settings</h4>
                                <p class="text-[10px] text-gray-500 truncate">Configuration</p>
                            </div>
                        </Link>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
