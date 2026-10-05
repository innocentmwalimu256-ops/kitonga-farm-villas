<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    active_tab: {
        type: String,
        default: 'guests',
    },
    filters: Object,
    guest_report: Object,
    tour_report: Object,
    farming_report: Object,
    financial_report: Object,
});

const currentTab = ref(props.active_tab || 'guests');
const guestSubFilter = ref('all'); // 'all', 'arrived_today', 'in_house', 'departed_today', 'unpaid'
const farmingSubTab = ref('movements'); // 'movements', 'sales', 'inventory'
const searchQuery = ref('');

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-TZ', { style: 'currency', currency: 'TZS', maximumFractionDigits: 0 }).format(val || 0);
};

const formatDate = (isoString) => {
    if (!isoString) return '-';
    return isoString;
};

const customDateForm = useForm({
    filter: 'custom',
    tab: currentTab.value,
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
});

const showCustomRange = ref(props.filters?.active === 'custom');

const switchTab = (tabName) => {
    currentTab.value = tabName;
    searchQuery.value = '';
};

const applyDateFilter = (filterKey) => {
    router.get(route('admin.reports.index'), {
        filter: filterKey,
        tab: currentTab.value,
    }, { preserveState: true });
};

const applyCustomDates = () => {
    customDateForm.tab = currentTab.value;
    customDateForm.get(route('admin.reports.index'), { preserveState: true });
};

// ─── FILTERED DATA COMPUTEDS ─────────────────────────────────────────────────
const filteredGuests = computed(() => {
    let list = props.guest_report?.list || [];
    
    // Sub-filter
    if (guestSubFilter.value === 'arrived_today') {
        list = list.filter(b => b.arrival_status === 'arrived_today');
    } else if (guestSubFilter.value === 'in_house') {
        list = list.filter(b => b.arrival_status === 'in_house');
    } else if (guestSubFilter.value === 'departed_today') {
        list = list.filter(b => b.arrival_status === 'departed_today');
    } else if (guestSubFilter.value === 'unpaid') {
        list = list.filter(b => b.balance > 0);
    }

    // Text search
    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase();
        list = list.filter(b => {
            return (b.customer_name || '').toLowerCase().includes(q) ||
                   (b.customer_phone || '').toLowerCase().includes(q) ||
                   (b.reference || '').toLowerCase().includes(q) ||
                   (b.unit_name || '').toLowerCase().includes(q) ||
                   (b.id_number || '').toLowerCase().includes(q);
        });
    }

    return list;
});

const filteredTours = computed(() => {
    let list = props.tour_report?.list || [];
    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase();
        list = list.filter(t => {
            return (t.customer_name || '').toLowerCase().includes(q) ||
                   (t.customer_phone || '').toLowerCase().includes(q) ||
                   (t.reference || '').toLowerCase().includes(q) ||
                   (t.tour_name || '').toLowerCase().includes(q);
        });
    }
    return list;
});

const filteredFarmingMovements = computed(() => {
    let list = props.farming_report?.movements || [];
    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase();
        list = list.filter(m => {
            return (m.product_name || '').toLowerCase().includes(q) ||
                   (m.category || '').toLowerCase().includes(q) ||
                   (m.reason || '').toLowerCase().includes(q) ||
                   (m.recorded_by || '').toLowerCase().includes(q);
        });
    }
    return list;
});

const filteredFarmSales = computed(() => {
    let list = props.farming_report?.sales || [];
    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase();
        list = list.filter(s => {
            return (s.product_name || '').toLowerCase().includes(q) ||
                   (s.category || '').toLowerCase().includes(q);
        });
    }
    return list;
});

const filteredFarmInventory = computed(() => {
    let list = props.farming_report?.inventory || [];
    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase();
        list = list.filter(p => {
            return (p.name || '').toLowerCase().includes(q) ||
                   (p.sku || '').toLowerCase().includes(q) ||
                   (p.category || '').toLowerCase().includes(q);
        });
    }
    return list;
});

const triggerPrint = () => {
    window.print();
};
</script>

<template>
    <Head title="Operations & Business Reports — Kitonga Farm Villas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-xl font-bold leading-tight text-gray-900 font-serif">
                                Kituo cha Ripoti za Uendeshaji &amp; Hesabu (Operations &amp; Reporting Hub)
                            </h2>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Ripoti za kina za wageni wa vyumbani, watalii wa farm tour, uzalishaji wa mazao/ufugaji, na hesabu za fedha.
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap items-center gap-2">
                        <button 
                            type="button" 
                            @click="triggerPrint" 
                            class="px-3 py-1.5 bg-gray-800 hover:bg-gray-900 text-white rounded-lg text-xs font-bold transition flex items-center gap-1.5 shadow-2xs cursor-pointer"
                        >
                            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            <span>Print / PDF</span>
                        </button>

                        <a 
                            :href="route('admin.reports.pdf', { start_date: filters.start_date, end_date: filters.end_date })" 
                            class="px-3 py-1.5 bg-red-700 hover:bg-red-800 text-white rounded-lg text-xs font-bold transition flex items-center gap-1.5 shadow-2xs cursor-pointer"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Executive PDF</span>
                        </a>
                    </div>
                </div>

                <!-- DATE RANGE FILTER BAR -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1.5 pt-1 -mx-3 px-3 sm:mx-0 sm:px-0 text-xs no-scrollbar border-t border-gray-100">
                    <button 
                        @click="applyDateFilter('today')" 
                        class="px-3 py-1.5 rounded-lg transition shrink-0 font-medium cursor-pointer" 
                        :class="filters.active === 'today' ? 'bg-[#14231C] text-white font-bold shadow-2xs' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'"
                    >
                        Leo (Today)
                    </button>
                    <button 
                        @click="applyDateFilter('yesterday')" 
                        class="px-3 py-1.5 rounded-lg transition shrink-0 font-medium cursor-pointer" 
                        :class="filters.active === 'yesterday' ? 'bg-[#14231C] text-white font-bold shadow-2xs' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'"
                    >
                        Jana (Yesterday)
                    </button>
                    <button 
                        @click="applyDateFilter('last_7')" 
                        class="px-3 py-1.5 rounded-lg transition shrink-0 font-medium cursor-pointer" 
                        :class="filters.active === 'last_7' ? 'bg-[#14231C] text-white font-bold shadow-2xs' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'"
                    >
                        Siku 7 Zilizopita
                    </button>
                    <button 
                        @click="applyDateFilter('this_month')" 
                        class="px-3 py-1.5 rounded-lg transition shrink-0 font-medium cursor-pointer" 
                        :class="filters.active === 'this_month' ? 'bg-[#14231C] text-white font-bold shadow-2xs' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'"
                    >
                        Mwezi Huu
                    </button>
                    <button 
                        @click="applyDateFilter('last_month')" 
                        class="px-3 py-1.5 rounded-lg transition shrink-0 font-medium cursor-pointer" 
                        :class="filters.active === 'last_month' ? 'bg-[#14231C] text-white font-bold shadow-2xs' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'"
                    >
                        Mwezi Uliopita
                    </button>
                    <button 
                        @click="applyDateFilter('this_year')" 
                        class="px-3 py-1.5 rounded-lg transition shrink-0 font-medium cursor-pointer" 
                        :class="filters.active === 'this_year' ? 'bg-[#14231C] text-white font-bold shadow-2xs' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'"
                    >
                        Mwaka Huu
                    </button>
                    <button 
                        @click="showCustomRange = !showCustomRange" 
                        class="px-3 py-1.5 rounded-lg transition shrink-0 font-bold border border-gray-300 cursor-pointer" 
                        :class="showCustomRange ? 'bg-gray-800 text-white' : 'bg-white hover:bg-gray-100 text-gray-700'"
                    >
                        {{ showCustomRange ? 'Ficha Tarehe' : 'Chagua Tarehe Maalum...' }}
                    </button>
                    
                    <span class="text-[11px] text-gray-500 font-mono pl-2 shrink-0">
                        Kipindi: <strong>{{ filters.label }}</strong>
                    </span>
                </div>
            </div>

            <!-- CUSTOM RANGE PICKER PANEL -->
            <div v-if="showCustomRange" class="mt-3 p-4 bg-white border border-gray-200 rounded-xl shadow-xs flex flex-col sm:flex-row items-stretch sm:items-end gap-3 max-w-xl">
                <div class="flex-1">
                    <label class="text-[10px] font-bold uppercase text-gray-500 block mb-1">Kuanzia Tarehe</label>
                    <input v-model="customDateForm.start_date" type="date" class="text-xs rounded-lg border-gray-300 w-full focus:border-emerald-600 focus:ring-emerald-600" />
                </div>
                <div class="flex-1">
                    <label class="text-[10px] font-bold uppercase text-gray-500 block mb-1">Hadi Tarehe</label>
                    <input v-model="customDateForm.end_date" type="date" class="text-xs rounded-lg border-gray-300 w-full focus:border-emerald-600 focus:ring-emerald-600" />
                </div>
                <button @click="applyCustomDates" class="px-5 py-2.5 bg-[#14231C] hover:bg-emerald-800 text-white font-bold rounded-lg text-xs shadow-xs transition cursor-pointer">
                    Chuja Ripoti
                </button>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-3 sm:px-6 lg:px-8 space-y-6">

                <!-- ════════════════════════════════════════════════════════════ -->
                <!-- 4 MAIN REPORT CATEGORY TABS                                  -->
                <!-- ════════════════════════════════════════════════════════════ -->
                <div class="bg-white p-2 rounded-2xl shadow-xs border border-gray-200 flex flex-wrap gap-2">
                    <!-- Tab 1: Guest Stays & Bookings -->
                    <button
                        type="button"
                        @click="switchTab('guests')"
                        class="flex-1 min-w-[160px] py-3 px-4 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 cursor-pointer"
                        :class="currentTab === 'guests' 
                            ? 'bg-[#14231C] text-white shadow-xs' 
                            : 'bg-gray-50 hover:bg-gray-100 text-gray-700'"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span>🏡 Wageni &amp; Vyumba ({{ guest_report.stats.total_bookings }})</span>
                    </button>

                    <!-- Tab 2: Farm Tours & Experiences -->
                    <button
                        type="button"
                        @click="switchTab('tours')"
                        class="flex-1 min-w-[160px] py-3 px-4 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 cursor-pointer"
                        :class="currentTab === 'tours' 
                            ? 'bg-[#14231C] text-white shadow-xs' 
                            : 'bg-gray-50 hover:bg-gray-100 text-gray-700'"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>🚜 Farm Tours &amp; Ziara ({{ tour_report.stats.total_tours }})</span>
                    </button>

                    <!-- Tab 3: Farming & Agriculture -->
                    <button
                        type="button"
                        @click="switchTab('farming')"
                        class="flex-1 min-w-[160px] py-3 px-4 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 cursor-pointer"
                        :class="currentTab === 'farming' 
                            ? 'bg-[#14231C] text-white shadow-xs' 
                            : 'bg-gray-50 hover:bg-gray-100 text-gray-700'"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <span>🥚 Kilimo &amp; Mavuno (Farming)</span>
                    </button>

                    <!-- Tab 4: Financial Statements -->
                    <button
                        type="button"
                        @click="switchTab('financials')"
                        class="flex-1 min-w-[160px] py-3 px-4 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 cursor-pointer"
                        :class="currentTab === 'financials' 
                            ? 'bg-[#14231C] text-white shadow-xs' 
                            : 'bg-gray-50 hover:bg-gray-100 text-gray-700'"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span>📊 Mapato &amp; Matumizi (P&amp;L)</span>
                    </button>
                </div>

                <!-- ════════════════════════════════════════════════════════════ -->
                <!-- 1. GUEST STAYS & ARRIVALS REPORT TAB                         -->
                <!-- ════════════════════════════════════════════════════════════ -->
                <div v-if="currentTab === 'guests'" class="space-y-6">
                    
                    <!-- KPI Summary Cards -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3">
                        <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-2xs">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Jumla ya Bookings</span>
                            <span class="text-xl sm:text-2xl font-black text-gray-900 block mt-1">{{ guest_report.stats.total_bookings }}</span>
                            <span class="text-[10px] text-gray-500 font-semibold">{{ guest_report.stats.total_guests }} Wageni Jumla</span>
                        </div>

                        <div class="bg-emerald-50 p-4 rounded-2xl border border-emerald-200/80 shadow-2xs">
                            <span class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider block">Waliowasili Leo</span>
                            <span class="text-xl sm:text-2xl font-black text-emerald-950 block mt-1">{{ guest_report.stats.arrivals_count }}</span>
                            <span class="text-[10px] text-emerald-700 font-semibold">Today's Check-ins</span>
                        </div>

                        <div class="bg-indigo-50 p-4 rounded-2xl border border-indigo-200/80 shadow-2xs">
                            <span class="text-[10px] font-bold text-indigo-800 uppercase tracking-wider block">Waliopo Ndani</span>
                            <span class="text-xl sm:text-2xl font-black text-indigo-950 block mt-1">{{ guest_report.stats.in_house_count }}</span>
                            <span class="text-[10px] text-indigo-700 font-semibold">Currently In-House</span>
                        </div>

                        <div class="bg-amber-50 p-4 rounded-2xl border border-amber-200/80 shadow-2xs">
                            <span class="text-[10px] font-bold text-amber-800 uppercase tracking-wider block">Wanaondoka Leo</span>
                            <span class="text-xl sm:text-2xl font-black text-amber-950 block mt-1">{{ guest_report.stats.departures_count }}</span>
                            <span class="text-[10px] text-amber-700 font-semibold">Departures</span>
                        </div>

                        <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-2xs">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Mapato ya Vyumba</span>
                            <span class="text-sm sm:text-base font-black font-mono text-emerald-700 block mt-1">{{ formatCurrency(guest_report.stats.total_revenue) }}</span>
                            <span class="text-[10px] text-emerald-600 font-semibold">Imelipwa: {{ formatCurrency(guest_report.stats.amount_collected) }}</span>
                        </div>

                        <div class="bg-rose-50 p-4 rounded-2xl border border-rose-200/80 shadow-2xs">
                            <span class="text-[10px] font-bold text-rose-800 uppercase tracking-wider block">Baki ya Madeni</span>
                            <span class="text-sm sm:text-base font-black font-mono text-rose-700 block mt-1">{{ formatCurrency(guest_report.stats.outstanding_balance) }}</span>
                            <span class="text-[10px] text-rose-600 font-semibold">{{ guest_report.stats.id_verified_count }} IDs Verified</span>
                        </div>
                    </div>

                    <!-- Filter Bar & Search -->
                    <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                        <div class="flex flex-wrap items-center gap-1.5 text-xs">
                            <button 
                                @click="guestSubFilter = 'all'" 
                                class="px-2.5 py-1 rounded-lg border font-bold transition cursor-pointer"
                                :class="guestSubFilter === 'all' ? 'bg-[#14231C] text-white border-[#14231C]' : 'bg-gray-50 text-gray-700 border-gray-200'"
                            >
                                Bookings Zote ({{ guest_report.list.length }})
                            </button>
                            <button 
                                @click="guestSubFilter = 'arrived_today'" 
                                class="px-2.5 py-1 rounded-lg border font-bold transition cursor-pointer"
                                :class="guestSubFilter === 'arrived_today' ? 'bg-emerald-800 text-white border-emerald-800' : 'bg-emerald-50 text-emerald-800 border-emerald-200'"
                            >
                                Waliowasili Leo ({{ guest_report.stats.arrivals_count }})
                            </button>
                            <button 
                                @click="guestSubFilter = 'in_house'" 
                                class="px-2.5 py-1 rounded-lg border font-bold transition cursor-pointer"
                                :class="guestSubFilter === 'in_house' ? 'bg-indigo-800 text-white border-indigo-800' : 'bg-indigo-50 text-indigo-800 border-indigo-200'"
                            >
                                Waliopo Ndani ({{ guest_report.stats.in_house_count }})
                            </button>
                            <button 
                                @click="guestSubFilter = 'unpaid'" 
                                class="px-2.5 py-1 rounded-lg border font-bold transition cursor-pointer"
                                :class="guestSubFilter === 'unpaid' ? 'bg-rose-800 text-white border-rose-800' : 'bg-rose-50 text-rose-800 border-rose-200'"
                            >
                                Wenye Madeni
                            </button>
                        </div>

                        <div class="flex items-center gap-2">
                            <div class="relative flex-1 sm:w-64">
                                <input 
                                    v-model="searchQuery" 
                                    type="text" 
                                    placeholder="Tafuta mgeni, simu, ID, ref..." 
                                    class="w-full text-xs pl-3 pr-8 py-1.5 rounded-lg border border-gray-300 focus:outline-none focus:border-emerald-600"
                                />
                                <span v-if="searchQuery" @click="searchQuery = ''" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 cursor-pointer text-xs">✕</span>
                            </div>

                            <a 
                                :href="route('admin.reports.excel.bookings', { start_date: filters.start_date, end_date: filters.end_date })" 
                                class="px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg text-xs font-bold transition shrink-0"
                            >
                                Export Excel
                            </a>
                        </div>
                    </div>

                    <!-- Bookings Table -->
                    <div class="bg-white rounded-2xl shadow-xs border border-gray-200 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="bg-gray-50/80 border-b border-gray-200 text-[10px] text-gray-400 uppercase font-bold tracking-wider">
                                        <th class="p-3">Ref &amp; Tarehe</th>
                                        <th class="p-3">Jina la Mgeni &amp; Mawasiliano</th>
                                        <th class="p-3">Kitambulisho (Digital ID)</th>
                                        <th class="p-3">Chumba / Villa</th>
                                        <th class="p-3">Tarehe za Stay</th>
                                        <th class="p-3 text-center">Wageni</th>
                                        <th class="p-3 text-right">Jumla (TZS)</th>
                                        <th class="p-3 text-right">Baki</th>
                                        <th class="p-3 text-center">Hali</th>
                                        <th class="p-3 text-right">Kitendo</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="b in filteredGuests" :key="b.id" class="hover:bg-gray-50/50 transition">
                                        <td class="p-3">
                                            <span class="font-mono font-bold text-gray-900 block">{{ b.reference }}</span>
                                            <span class="text-[10px] text-gray-400">{{ b.created_at }}</span>
                                        </td>
                                        <td class="p-3">
                                            <span class="font-bold text-gray-900 block">{{ b.customer_name }}</span>
                                            <span class="text-[11px] text-gray-500 font-mono">{{ b.customer_phone }}</span>
                                        </td>
                                        <td class="p-3">
                                            <div class="space-y-0.5">
                                                <span class="text-[11px] font-semibold text-gray-800 block capitalize">{{ b.id_type }}</span>
                                                <span class="font-mono text-[10px] text-gray-500 block">{{ b.id_number }}</span>
                                                <span v-if="b.has_id_document" class="inline-block px-1.5 py-0.2 bg-emerald-100 text-emerald-800 text-[9px] font-bold rounded">
                                                    ID Attached
                                                </span>
                                                <span v-else class="text-[9px] text-gray-400 italic">No Photo</span>
                                            </div>
                                        </td>
                                        <td class="p-3">
                                            <span class="font-bold text-gray-900 block">{{ b.unit_name }}</span>
                                            <span class="text-[10px] text-gray-500">{{ b.villa_type }}</span>
                                        </td>
                                        <td class="p-3">
                                            <div class="text-[11px]">
                                                <span class="text-gray-800 block font-medium">In: {{ b.check_in }}</span>
                                                <span class="text-gray-500 block">Out: {{ b.check_out }} ({{ b.nights }} usiku)</span>
                                            </div>
                                        </td>
                                        <td class="p-3 text-center font-bold text-gray-800">
                                            {{ b.guests_count }}
                                        </td>
                                        <td class="p-3 text-right font-mono font-bold text-emerald-800">
                                            {{ formatCurrency(b.total) }}
                                        </td>
                                        <td class="p-3 text-right font-mono font-bold" :class="b.balance > 0 ? 'text-red-600' : 'text-emerald-700'">
                                            {{ b.balance > 0 ? formatCurrency(b.balance) : 'Paid' }}
                                        </td>
                                        <td class="p-3 text-center">
                                            <span 
                                                class="px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase tracking-wider inline-block"
                                                :class="{
                                                    'bg-emerald-100 text-emerald-800': b.status === 'checked_in' || b.status === 'confirmed',
                                                    'bg-amber-100 text-amber-800': b.status === 'pending',
                                                    'bg-gray-100 text-gray-700': b.status === 'checked_out',
                                                    'bg-red-100 text-red-700': b.status === 'cancelled',
                                                }"
                                            >
                                                {{ b.status }}
                                            </span>
                                        </td>
                                        <td class="p-3 text-right">
                                            <Link :href="route('admin.bookings.show', b.id)" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-800 text-[10px] font-bold rounded-md transition">
                                                Fungua ➔
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="filteredGuests.length === 0">
                                        <td colspan="10" class="p-8 text-center text-gray-400 italic">
                                            Hakuna rekodi za wageni zinazopatikana kwenye kipindi hiki.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ════════════════════════════════════════════════════════════ -->
                <!-- 2. FARM TOURS & EXPERIENCES REPORT TAB                       -->
                <!-- ════════════════════════════════════════════════════════════ -->
                <div v-if="currentTab === 'tours'" class="space-y-6">
                    
                    <!-- KPI Summary Cards -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-2xs">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Ziara Zilizofanyika</span>
                            <span class="text-2xl font-black text-gray-900 block mt-1">{{ tour_report.stats.total_tours }} Tours</span>
                        </div>
                        <div class="bg-indigo-50 p-5 rounded-2xl border border-indigo-200/80 shadow-2xs">
                            <span class="text-[10px] font-bold text-indigo-800 uppercase tracking-wider block">Jumla ya Watalii / Visitors</span>
                            <span class="text-2xl font-black text-indigo-950 block mt-1">{{ tour_report.stats.total_visitors }} Watu</span>
                        </div>
                        <div class="bg-emerald-50 p-5 rounded-2xl border border-emerald-200/80 shadow-2xs">
                            <span class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider block">Mapato ya Farm Tours</span>
                            <span class="text-xl sm:text-2xl font-black font-mono text-emerald-900 block mt-1">{{ formatCurrency(tour_report.stats.total_revenue) }}</span>
                        </div>
                        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-2xs flex items-center justify-between">
                            <div>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Excel Download</span>
                                <span class="text-xs text-gray-600 block mt-0.5">Pakua ripoti kamili ya ziara</span>
                            </div>
                            <a 
                                :href="route('admin.reports.excel.tours', { start_date: filters.start_date, end_date: filters.end_date })" 
                                class="px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold rounded-lg shadow-xs"
                            >
                                Export
                            </a>
                        </div>
                    </div>

                    <!-- Tours Table -->
                    <div class="bg-white rounded-2xl shadow-xs border border-gray-200 overflow-hidden">
                        <div class="p-4 border-b border-gray-100 flex justify-between items-center">
                            <h3 class="font-bold text-gray-900 text-sm">Orodha ya Watalii &amp; Ziara za Shamba</h3>
                            <div class="w-64">
                                <input 
                                    v-model="searchQuery" 
                                    type="text" 
                                    placeholder="Tafuta mtalii, namba, tour..." 
                                    class="w-full text-xs px-3 py-1.5 rounded-lg border border-gray-300 focus:outline-none focus:border-emerald-600"
                                />
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="bg-gray-50/80 border-b border-gray-200 text-[10px] text-gray-400 uppercase font-bold tracking-wider">
                                        <th class="p-3">Ref &amp; Tarehe</th>
                                        <th class="p-3">Aina ya Ziara (Experience)</th>
                                        <th class="p-3">Mtalii Kiongozi &amp; Simu</th>
                                        <th class="p-3 text-center">Idadi ya Wageni</th>
                                        <th class="p-3 text-right">Gharama (TZS)</th>
                                        <th class="p-3 text-right">Imelipwa</th>
                                        <th class="p-3 text-center">Hali</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="t in filteredTours" :key="t.id" class="hover:bg-gray-50/50">
                                        <td class="p-3">
                                            <span class="font-mono font-bold text-gray-900 block">{{ t.reference }}</span>
                                            <span class="text-[10px] text-gray-400">{{ t.date }}</span>
                                        </td>
                                        <td class="p-3 font-semibold text-gray-900">
                                            {{ t.tour_name }}
                                        </td>
                                        <td class="p-3">
                                            <span class="font-bold text-gray-900 block">{{ t.customer_name }}</span>
                                            <span class="text-[11px] text-gray-500 font-mono">{{ t.customer_phone }}</span>
                                        </td>
                                        <td class="p-3 text-center font-bold text-gray-800">
                                            {{ t.visitors_count }} Watu
                                        </td>
                                        <td class="p-3 text-right font-mono font-bold text-emerald-800">
                                            {{ formatCurrency(t.total) }}
                                        </td>
                                        <td class="p-3 text-right font-mono font-bold" :class="t.balance > 0 ? 'text-amber-700' : 'text-emerald-700'">
                                            {{ formatCurrency(t.amount_paid) }}
                                        </td>
                                        <td class="p-3 text-center">
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase bg-emerald-100 text-emerald-800">
                                                {{ t.status }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="filteredTours.length === 0">
                                        <td colspan="7" class="p-8 text-center text-gray-400 italic">
                                            Hakuna ziara zilizosajiliwa kwenye kipindi hiki.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ════════════════════════════════════════════════════════════ -->
                <!-- 3. FARM AGRICULTURE, HARVEST & INVENTORY TAB                 -->
                <!-- ════════════════════════════════════════════════════════════ -->
                <div v-if="currentTab === 'farming'" class="space-y-6">
                    
                    <!-- KPI Summary Cards -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-4">
                        <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-2xs">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Matukio ya Mavuno</span>
                            <span class="text-2xl font-black text-gray-900 block mt-1">{{ farming_report.stats.harvest_actions_count }}</span>
                            <span class="text-[10px] text-gray-500 font-semibold">{{ farming_report.stats.total_units_harvested }} Units Harvested</span>
                        </div>

                        <div class="bg-emerald-50 p-4 rounded-2xl border border-emerald-200 shadow-2xs">
                            <span class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider block">Mauzo ya Mazao</span>
                            <span class="text-xl sm:text-2xl font-black font-mono text-emerald-950 block mt-1">{{ formatCurrency(farming_report.stats.produce_sales_revenue) }}</span>
                            <span class="text-[10px] text-emerald-700 font-semibold">Direct Produce Sales</span>
                        </div>

                        <div class="bg-rose-50 p-4 rounded-2xl border border-rose-200 shadow-2xs">
                            <span class="text-[10px] font-bold text-rose-800 uppercase tracking-wider block">Uharibifu / Spoilage</span>
                            <span class="text-2xl font-black text-rose-700 block mt-1">{{ farming_report.stats.spoilage_losses_count }} Units</span>
                            <span class="text-[10px] text-rose-600 font-semibold">Losses / Damaged</span>
                        </div>

                        <div class="bg-indigo-50 p-4 rounded-2xl border border-indigo-200 shadow-2xs">
                            <span class="text-[10px] font-bold text-indigo-800 uppercase tracking-wider block">Thamani ya Mazao Stoo</span>
                            <span class="text-xl sm:text-2xl font-black font-mono text-indigo-950 block mt-1">{{ formatCurrency(farming_report.stats.total_farm_stock_valuation) }}</span>
                            <span class="text-[10px] text-indigo-700 font-semibold">Current Stock Value</span>
                        </div>

                        <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-2xs flex items-center justify-between">
                            <div>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Excel Ripoti</span>
                                <span class="text-xs text-gray-600 block mt-0.5">Mavuno &amp; Stoo</span>
                            </div>
                            <a 
                                :href="route('admin.reports.excel.farming', { start_date: filters.start_date, end_date: filters.end_date })" 
                                class="px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold rounded-lg shadow-xs"
                            >
                                Export
                            </a>
                        </div>
                    </div>

                    <!-- Sub-tabs Switcher -->
                    <div class="flex items-center gap-2 border-b border-gray-200 pb-2">
                        <button 
                            @click="farmingSubTab = 'movements'" 
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer"
                            :class="farmingSubTab === 'movements' ? 'bg-[#14231C] text-white' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'"
                        >
                            Mavuno &amp; Mabadiliko ya Stoo (Harvest Logs)
                        </button>
                        <button 
                            @click="farmingSubTab = 'sales'" 
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer"
                            :class="farmingSubTab === 'sales' ? 'bg-[#14231C] text-white' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'"
                        >
                            Mauzo ya Mazao (Produce Sales)
                        </button>
                        <button 
                            @click="farmingSubTab = 'inventory'" 
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer"
                            :class="farmingSubTab === 'inventory' ? 'bg-[#14231C] text-white' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'"
                        >
                            Hali Halisi ya Stoo ya Shamba (Current Live Stock)
                        </button>
                    </div>

                    <!-- Sub-view 1: Harvest & Inventory Movements -->
                    <div v-if="farmingSubTab === 'movements'" class="bg-white rounded-2xl shadow-xs border border-gray-200 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="bg-gray-50/80 border-b border-gray-200 text-[10px] text-gray-400 uppercase font-bold tracking-wider">
                                        <th class="p-3">Tarehe &amp; Muda</th>
                                        <th class="p-3">Zao / Bidhaa</th>
                                        <th class="p-3">Kundi</th>
                                        <th class="p-3 text-center">Aina ya Tukio</th>
                                        <th class="p-3 text-right">Kiasi (Quantity)</th>
                                        <th class="p-3">Maelezo / Shamba / Sababu</th>
                                        <th class="p-3">Mhusika</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="m in filteredFarmingMovements" :key="m.id" class="hover:bg-gray-50/50">
                                        <td class="p-3 text-gray-500 font-mono text-[11px]">{{ m.date }}</td>
                                        <td class="p-3 font-bold text-gray-900">{{ m.product_name }}</td>
                                        <td class="p-3 text-gray-600">{{ m.category }}</td>
                                        <td class="p-3 text-center">
                                            <span 
                                                class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase"
                                                :class="{
                                                    'bg-emerald-100 text-emerald-800': m.type === 'purchase' || m.type === 'harvest' || m.type === 'addition',
                                                    'bg-red-100 text-red-800': m.type === 'loss',
                                                    'bg-blue-100 text-blue-800': m.type === 'sale',
                                                    'bg-gray-100 text-gray-800': m.type === 'adjustment',
                                                }"
                                            >
                                                {{ m.type }}
                                            </span>
                                        </td>
                                        <td class="p-3 text-right font-mono font-bold text-gray-900">
                                            {{ m.quantity }} {{ m.unit }}
                                        </td>
                                        <td class="p-3 text-gray-700 italic text-[11px]">{{ m.reason }}</td>
                                        <td class="p-3 text-gray-500 font-semibold">{{ m.recorded_by }}</td>
                                    </tr>
                                    <tr v-if="filteredFarmingMovements.length === 0">
                                        <td colspan="7" class="p-8 text-center text-gray-400 italic">
                                            Hakuna rekodi za mavuno au mabadiliko ya stoo kwenye kipindi hiki.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Sub-view 2: Produce Sales -->
                    <div v-if="farmingSubTab === 'sales'" class="bg-white rounded-2xl shadow-xs border border-gray-200 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="bg-gray-50/80 border-b border-gray-200 text-[10px] text-gray-400 uppercase font-bold tracking-wider">
                                        <th class="p-3">Tarehe ya Mauzo</th>
                                        <th class="p-3">Zao / Bidhaa</th>
                                        <th class="p-3 text-center">Idadi (Qty)</th>
                                        <th class="p-3 text-right">Bei ya Kipande</th>
                                        <th class="p-3 text-right">Jumla ya Mauzo</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="s in filteredFarmSales" :key="s.id" class="hover:bg-gray-50/50">
                                        <td class="p-3 text-gray-500 font-mono text-[11px]">{{ s.date }}</td>
                                        <td class="p-3 font-bold text-gray-900">{{ s.product_name }}</td>
                                        <td class="p-3 text-center font-mono font-bold text-gray-800">{{ s.quantity }}</td>
                                        <td class="p-3 text-right font-mono text-gray-600">{{ formatCurrency(s.unit_price) }}</td>
                                        <td class="p-3 text-right font-mono font-bold text-emerald-800">{{ formatCurrency(s.total) }}</td>
                                    </tr>
                                    <tr v-if="filteredFarmSales.length === 0">
                                        <td colspan="5" class="p-8 text-center text-gray-400 italic">
                                            Hakuna mauzo ya mazao ya shamba yaliyorekodiwa kwenye kipindi hiki.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Sub-view 3: Live Farm Inventory -->
                    <div v-if="farmingSubTab === 'inventory'" class="bg-white rounded-2xl shadow-xs border border-gray-200 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="bg-gray-50/80 border-b border-gray-200 text-[10px] text-gray-400 uppercase font-bold tracking-wider">
                                        <th class="p-3">Jina la Bidhaa / Zao</th>
                                        <th class="p-3">Kundi</th>
                                        <th class="p-3 text-center">Kiasi Kilichopo (Stock)</th>
                                        <th class="p-3 text-right">Bei ya Kuuza</th>
                                        <th class="p-3 text-right">Thamani ya Stoo</th>
                                        <th class="p-3 text-center">Hali ya Stoo</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="p in filteredFarmInventory" :key="p.id" class="hover:bg-gray-50/50">
                                        <td class="p-3">
                                            <span class="font-bold text-gray-900 block">{{ p.name }}</span>
                                            <span class="text-[10px] text-gray-400 font-mono">{{ p.sku }}</span>
                                        </td>
                                        <td class="p-3 text-gray-600">{{ p.category }}</td>
                                        <td class="p-3 text-center font-mono font-bold text-sm" :class="p.is_low_stock ? 'text-red-600' : 'text-gray-900'">
                                            {{ p.stock }} {{ p.unit }}
                                        </td>
                                        <td class="p-3 text-right font-mono text-gray-600">{{ formatCurrency(p.selling_price) }}</td>
                                        <td class="p-3 text-right font-mono font-bold text-indigo-800">{{ formatCurrency(p.stock_value) }}</td>
                                        <td class="p-3 text-center">
                                            <span 
                                                class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase"
                                                :class="p.is_low_stock ? 'bg-red-100 text-red-800' : 'bg-emerald-100 text-emerald-800'"
                                            >
                                                {{ p.is_low_stock ? 'Inakaribia Kuisha' : 'Inatosha' }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ════════════════════════════════════════════════════════════ -->
                <!-- 4. FINANCIAL STATEMENTS & EXPENSES TAB                       -->
                <!-- ════════════════════════════════════════════════════════════ -->
                <div v-if="currentTab === 'financials'" class="space-y-6">
                    
                    <!-- P&L Headline Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-2xs">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Jumla ya Mapato (Total Revenue)</span>
                            <h3 class="text-3xl font-black font-mono text-emerald-800 mt-2">{{ formatCurrency(financial_report.total_revenue) }}</h3>
                            <div class="mt-3 text-[11px] text-gray-500 space-y-1 border-t border-gray-100 pt-2 font-mono">
                                <div class="flex justify-between">
                                    <span>Vyumba (Villas):</span>
                                    <span>{{ formatCurrency(financial_report.accommodation_revenue) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Farm Tours:</span>
                                    <span>{{ formatCurrency(financial_report.tour_revenue) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Mazao ya Shamba:</span>
                                    <span>{{ formatCurrency(financial_report.produce_revenue) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Vinywaji &amp; Bar:</span>
                                    <span>{{ formatCurrency(financial_report.bar_revenue) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-2xs">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Jumla ya Matumizi (Expenses)</span>
                                    <h3 class="text-3xl font-black font-mono text-red-600 mt-2">{{ formatCurrency(financial_report.total_expenses) }}</h3>
                                </div>
                                <a 
                                    :href="route('admin.reports.excel.expenses', { start_date: filters.start_date, end_date: filters.end_date })" 
                                    class="px-2.5 py-1 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 rounded-lg text-xs font-bold"
                                >
                                    Export Excel
                                </a>
                            </div>
                            <p class="text-[11px] text-gray-400 mt-4">
                                Gharama zote zilizoidhinishwa za chakula cha mifugo, mbegu, matengenezo, mishahara, na uendeshaji.
                            </p>
                        </div>

                        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-2xs">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Faida Halisi (Net Profit)</span>
                            <h3 class="text-3xl font-black font-mono mt-2" :class="financial_report.net_profit >= 0 ? 'text-emerald-700' : 'text-red-600'">
                                {{ formatCurrency(financial_report.net_profit) }}
                            </h3>
                            <div class="mt-4 p-3 rounded-xl bg-gray-50 border border-gray-100 text-xs text-gray-600">
                                <span>Margin: </span>
                                <strong class="text-gray-900">
                                    {{ financial_report.total_revenue > 0 ? Math.round((financial_report.net_profit / financial_report.total_revenue) * 100) : 0 }}%
                                </strong>
                            </div>
                        </div>
                    </div>

                    <!-- Expenses Ledger List -->
                    <div class="bg-white rounded-2xl shadow-xs border border-gray-200 overflow-hidden">
                        <div class="p-4 border-b border-gray-100 flex justify-between items-center">
                            <h3 class="font-bold text-gray-900 text-sm">Mchanganuo wa Matumizi (Approved Expense Ledger)</h3>
                            <span class="text-xs font-bold text-gray-500 font-mono">{{ financial_report.expenses_list.length }} Payouts</span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="bg-gray-50/80 border-b border-gray-200 text-[10px] text-gray-400 uppercase font-bold tracking-wider">
                                        <th class="p-3">Tarehe</th>
                                        <th class="p-3">Kundi (Category)</th>
                                        <th class="p-3">Maelezo</th>
                                        <th class="p-3">Mpokeaji</th>
                                        <th class="p-3 text-right">Kiasi (TZS)</th>
                                        <th class="p-3">Msimamizi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="e in financial_report.expenses_list" :key="e.id" class="hover:bg-gray-50/50">
                                        <td class="p-3 text-gray-500 font-mono text-[11px]">{{ e.date }}</td>
                                        <td class="p-3 font-semibold text-gray-800">{{ e.category }}</td>
                                        <td class="p-3 text-gray-700">{{ e.description }}</td>
                                        <td class="p-3 text-gray-900 font-medium">{{ e.recipient }}</td>
                                        <td class="p-3 text-right font-mono font-bold text-red-600">{{ formatCurrency(e.amount) }}</td>
                                        <td class="p-3 text-gray-500">{{ e.recorded_by }}</td>
                                    </tr>
                                    <tr v-if="financial_report.expenses_list.length === 0">
                                        <td colspan="6" class="p-8 text-center text-gray-400 italic">
                                            Hakuna matumizi yaliyorekodiwa kwenye kipindi hiki.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
