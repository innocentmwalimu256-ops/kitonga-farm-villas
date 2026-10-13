<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    bookings: Object,
    filters: Object,
    counts: {
        type: Object,
        default: () => ({ all: 0, villa: 0, tour: 0, consultation: 0 }),
    },
    activeType: {
        type: String,
        default: 'all',
    },
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const startDate = ref(props.filters?.start_date || '');
const endDate = ref(props.filters?.end_date || '');
const currentType = ref(props.activeType || 'all');

// Modals state
const isStatusModalOpen = ref(false);
const isFinancialsModalOpen = ref(false);
const selectedBooking = ref(null);

const statusForm = useForm({
    status: 'pending',
    notes: '',
    record_payment: false,
    payment_amount: 0,
    payment_method: 'mobile_money',
    payment_reference: '',
});

const financialsForm = useForm({
    total: 0,
    amount_paid: 0,
    payment_method: 'mobile_money',
    payment_reference: '',
    notes: '',
});

const openStatusModal = (booking) => {
    selectedBooking.value = booking;
    statusForm.status = booking.status;
    statusForm.notes = '';
    statusForm.record_payment = false;
    statusForm.payment_amount = Math.max(0, Number(booking.balance) || 0);
    statusForm.payment_method = 'mobile_money';
    statusForm.payment_reference = '';
    isStatusModalOpen.value = true;
};

const closeStatusModal = () => {
    isStatusModalOpen.value = false;
    selectedBooking.value = null;
};

const submitStatusChange = () => {
    if (!selectedBooking.value) return;
    statusForm.post(route('admin.bookings.status', selectedBooking.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeStatusModal();
        },
    });
};

const openFinancialsModal = (booking) => {
    selectedBooking.value = booking;
    financialsForm.total = Number(booking.total) || 0;
    financialsForm.amount_paid = Number(booking.amount_paid) || 0;
    financialsForm.payment_method = 'mobile_money';
    financialsForm.payment_reference = '';
    financialsForm.notes = '';
    isFinancialsModalOpen.value = true;
};

const closeFinancialsModal = () => {
    isFinancialsModalOpen.value = false;
    selectedBooking.value = null;
};

const calculatedBalance = computed(() => {
    const total = Number(financialsForm.total) || 0;
    const paid = Number(financialsForm.amount_paid) || 0;
    return Math.max(0, total - paid);
});

const calculatedOverpaid = computed(() => {
    const total = Number(financialsForm.total) || 0;
    const paid = Number(financialsForm.amount_paid) || 0;
    return Math.max(0, paid - total);
});

const submitFinancialsChange = () => {
    if (!selectedBooking.value) return;
    financialsForm.post(route('admin.bookings.financials', selectedBooking.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeFinancialsModal();
        },
    });
};

const switchType = (typeKey) => {
    currentType.value = typeKey;
    router.get(route('admin.bookings.index'), {
        type: typeKey !== 'all' ? typeKey : undefined,
        search: search.value || undefined,
        status: status.value || undefined,
        start_date: startDate.value || undefined,
        end_date: endDate.value || undefined,
    }, { preserveState: true, replace: true });
};

const handleFilter = () => {
    router.get(route('admin.bookings.index'), {
        type: currentType.value !== 'all' ? currentType.value : undefined,
        search: search.value || undefined,
        status: status.value || undefined,
        start_date: startDate.value || undefined,
        end_date: endDate.value || undefined,
    }, { preserveState: true, replace: true });
};

const clearFilters = () => {
    search.value = '';
    status.value = '';
    startDate.value = '';
    endDate.value = '';
    handleFilter();
};

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-TZ', { style: 'currency', currency: 'TZS', maximumFractionDigits: 0 }).format(val || 0);
};

const formatDate = (dateStr) => {
    if (!dateStr) return 'N/A';
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

const isTour = (booking) => {
    return !booking.accommodation_unit_id || 
           (booking.reference && booking.reference.startsWith('KTG-EXP')) || 
           booking.source === 'online_experience';
};

const getExperienceTitle = (booking) => {
    if (booking.items && booking.items.length > 0 && booking.items[0].description_snapshot) {
        return booking.items[0].description_snapshot;
    }
    if (booking.notes && booking.notes.includes('Experience:')) {
        const match = booking.notes.match(/Experience:\s*([^|]+)/);
        if (match) return match[1].trim();
    }
    return 'Farm Tour & Agro-Experience';
};

const calculateNights = (booking) => {
    if (!booking.check_in || !booking.check_out) return 1;
    const diff = Math.round((new Date(booking.check_out) - new Date(booking.check_in)) / (1000 * 60 * 60 * 24));
    return Math.max(1, diff);
};
</script>

<template>
    <Head title="Bookings & Reservations Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                <div>
                    <h2 class="text-xl sm:text-2xl font-serif font-bold text-[#14231C]">
                        Bookings &amp; Reservations
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Dhibiti wageni waliobook Villa, Farm Tours, na Mashauriano ya Mr. Kitonga.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('admin.bookings.calendar')" class="px-3.5 py-2 bg-white hover:bg-gray-50 text-gray-700 text-xs font-bold rounded-lg border border-gray-300 shadow-xs transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Calendar Grid</span>
                    </Link>
                    <Link :href="route('admin.bookings.create')" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold rounded-lg shadow transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>New Booking</span>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6 sm:py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- ══════════════════════════════════════════════════════════════════
                 1. CATEGORY TABS SWITCHER (Villas vs Farm Tours vs Mr Kitonga)
            ══════════════════════════════════════════════════════════════════ -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <!-- Tab: All Bookings -->
                <button
                    type="button"
                    @click="switchType('all')"
                    class="p-4 rounded-2xl border text-left transition relative cursor-pointer"
                    :class="currentType === 'all' 
                        ? 'bg-white border-emerald-600 ring-2 ring-emerald-600/20 shadow-sm' 
                        : 'bg-white border-gray-200/90 hover:border-gray-300 text-gray-600 hover:bg-gray-50/50'"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider" :class="currentType === 'all' ? 'text-emerald-800' : 'text-gray-500'">
                            📋 Zote (All)
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-extrabold"
                            :class="currentType === 'all' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600'">
                            {{ counts?.all || 0 }}
                        </span>
                    </div>
                    <div class="text-lg font-bold mt-2" :class="currentType === 'all' ? 'text-gray-900' : 'text-gray-700'">
                        All Bookings
                    </div>
                    <div class="text-[11px] text-gray-400 mt-0.5">
                        Villa &amp; Farm Tours kwa pamoja
                    </div>
                </button>

                <!-- Tab: Villa Reservations -->
                <button
                    type="button"
                    @click="switchType('villa')"
                    class="p-4 rounded-2xl border text-left transition relative cursor-pointer"
                    :class="currentType === 'villa' 
                        ? 'bg-white border-emerald-600 ring-2 ring-emerald-600/20 shadow-sm' 
                        : 'bg-white border-gray-200/90 hover:border-gray-300 text-gray-600 hover:bg-gray-50/50'"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider" :class="currentType === 'villa' ? 'text-emerald-800' : 'text-gray-500'">
                            🏡 Villa Stays
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-extrabold"
                            :class="currentType === 'villa' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600'">
                            {{ counts?.villa || 0 }}
                        </span>
                    </div>
                    <div class="text-lg font-bold mt-2" :class="currentType === 'villa' ? 'text-gray-900' : 'text-gray-700'">
                        Villa Reservations
                    </div>
                    <div class="text-[11px] text-gray-400 mt-0.5">
                        Wageni waliobook malazi &amp; vyumba
                    </div>
                </button>

                <!-- Tab: Farm Tour Bookings -->
                <button
                    type="button"
                    @click="switchType('tour')"
                    class="p-4 rounded-2xl border text-left transition relative cursor-pointer"
                    :class="currentType === 'tour' 
                        ? 'bg-white border-emerald-600 ring-2 ring-emerald-600/20 shadow-sm' 
                        : 'bg-white border-gray-200/90 hover:border-gray-300 text-gray-600 hover:bg-gray-50/50'"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider" :class="currentType === 'tour' ? 'text-emerald-800' : 'text-gray-500'">
                            🌿 Farm Tours
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-extrabold"
                            :class="currentType === 'tour' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600'">
                            {{ counts?.tour || 0 }}
                        </span>
                    </div>
                    <div class="text-lg font-bold mt-2" :class="currentType === 'tour' ? 'text-gray-900' : 'text-gray-700'">
                        Farm Day Tours
                    </div>
                    <div class="text-[11px] text-gray-400 mt-0.5">
                        Wageni waliobook utalii wa shamba
                    </div>
                </button>

                <!-- Tab: Mr. Kitonga Consultations (Quick Jump) -->
                <Link
                    :href="route('admin.consultations.index')"
                    class="p-4 rounded-2xl border text-left transition relative group bg-gradient-to-br from-[#14231C] to-[#1c3227] text-white border-[#14231C] hover:shadow-md"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-[#E6C387] flex items-center gap-1">
                            <span>👔 Mr. Kitonga</span>
                            <span class="text-[9px] bg-[#E6C387]/20 text-[#E6C387] px-1.5 py-0.2 rounded font-mono">DESK</span>
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-extrabold bg-[#E6C387] text-[#14231C]">
                            {{ counts?.consultation || 0 }}
                        </span>
                    </div>
                    <div class="text-lg font-bold mt-2 text-white flex items-center justify-between">
                        <span>Consultations</span>
                        <svg class="w-4 h-4 text-[#E6C387] group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </div>
                    <div class="text-[11px] text-gray-300 mt-0.5">
                        Tazama mashauriano &amp; mentorship &rarr;
                    </div>
                </Link>
            </div>

            <!-- ══════════════════════════════════════════════════════════════════
                 2. FILTERS BAR
            ══════════════════════════════════════════════════════════════════ -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-xs border border-gray-200/80 grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Tafuta Mteja / Ref / Tour</label>
                    <input 
                        type="text" 
                        v-model="search" 
                        @input="handleFilter" 
                        placeholder="e.g. KTG-EXP, Juma, Suite..." 
                        class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 mt-1"
                    />
                </div>
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Hali ya Booking (Status)</label>
                    <select 
                        v-model="status" 
                        @change="handleFilter" 
                        class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 mt-1"
                    >
                        <option value="">Hali Zote (All Statuses)</option>
                        <option value="pending">Pending (Inasubiri)</option>
                        <option value="confirmed">Confirmed (Imethibitishwa)</option>
                        <option value="checked_in">Checked In (Amewasili)</option>
                        <option value="checked_out">Checked Out (Ameondoka)</option>
                        <option value="cancelled">Cancelled (Imeghairiwa)</option>
                        <option value="no_show">No Show</option>
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Tarehe ya Kuanzia</label>
                    <input 
                        type="date" 
                        v-model="startDate" 
                        @change="handleFilter" 
                        class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 mt-1"
                    />
                </div>
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Tarehe ya Mwisho</label>
                    <input 
                        type="date" 
                        v-model="endDate" 
                        @change="handleFilter" 
                        class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 mt-1"
                    />
                </div>
                <div class="flex items-center gap-2">
                    <button 
                        @click="clearFilters" 
                        type="button" 
                        class="w-full py-2.5 text-xs border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 font-bold transition flex items-center justify-center gap-1 cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Futa Vichungi</span>
                    </button>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════════════════════
                 3. BOOKINGS TABLE
            ══════════════════════════════════════════════════════════════════ -->
            <div class="bg-white rounded-2xl shadow-xs border border-gray-200/80 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead>
                            <tr class="bg-gray-50/80 border-b border-gray-200/80 text-[11px] text-gray-400 uppercase font-bold tracking-wider">
                                <th class="py-3.5 px-4">Reference</th>
                                <th class="py-3.5 px-4">Mteja (Guest)</th>
                                <th class="py-3.5 px-4">Aina &amp; Chumba / Tour</th>
                                <th class="py-3.5 px-4">Tarehe (Dates)</th>
                                <th class="py-3.5 px-4 text-center">Muda</th>
                                <th class="py-3.5 px-4">Jumla (Total)</th>
                                <th class="py-3.5 px-4">Imelipwa (Paid)</th>
                                <th class="py-3.5 px-4">Salio (Balance)</th>
                                <th class="py-3.5 px-4 text-center">Status</th>
                                <th class="py-3.5 px-4 text-right">Vitendo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="booking in bookings.data" :key="booking.id" class="hover:bg-gray-50/60 transition">
                                <!-- Reference -->
                                <td class="py-3.5 px-4 font-bold text-gray-900">
                                    <Link :href="route('admin.bookings.show', booking.id)" class="hover:underline text-emerald-800 font-mono text-xs flex items-center gap-1.5">
                                        <span>{{ booking.reference }}</span>
                                    </Link>
                                    <span class="text-[10px] text-gray-400 font-mono block uppercase">
                                        {{ booking.source || 'direct' }}
                                    </span>
                                </td>

                                <!-- Guest -->
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-gray-800 text-xs">{{ booking.customer?.name || 'Mteja' }}</div>
                                    <span class="text-[11px] text-gray-400 font-mono block">{{ booking.customer?.phone || 'N/A' }}</span>
                                </td>

                                <!-- Type & Unit / Tour -->
                                <td class="py-3.5 px-4">
                                    <!-- Farm Tour Booking -->
                                    <div v-if="isTour(booking)" class="space-y-1">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                            🌿 Farm Tour
                                        </span>
                                        <div class="font-bold text-gray-800 text-xs leading-snug">
                                            {{ getExperienceTitle(booking) }}
                                        </div>
                                        <span class="text-[10px] text-gray-400 block">
                                            Wageni: {{ booking.guests_count || 1 }}
                                        </span>
                                    </div>

                                    <!-- Villa Stay Booking -->
                                    <div v-else class="space-y-1">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            🏡 Villa Stay
                                        </span>
                                        <div class="font-bold text-gray-800 text-xs">
                                            {{ booking.unit?.name || 'Villa Room' }}
                                        </div>
                                        <span class="text-[10px] text-gray-400 block">
                                            {{ booking.unit?.type?.name || 'Accommodation' }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Dates -->
                                <td class="py-3.5 px-4 text-xs whitespace-nowrap">
                                    <div v-if="isTour(booking)" class="font-semibold text-gray-800">
                                        {{ formatDate(booking.check_in) }}
                                        <span class="text-[10px] text-gray-400 block font-normal">Siku 1 (Day Tour)</span>
                                    </div>
                                    <div v-else class="font-semibold text-gray-800">
                                        <div>{{ formatDate(booking.check_in) }}</div>
                                        <div class="text-[10px] text-gray-400 font-normal">hadi {{ formatDate(booking.check_out) }}</div>
                                    </div>
                                </td>

                                <!-- Duration / Nights -->
                                <td class="py-3.5 px-4 text-center">
                                    <span v-if="isTour(booking)" class="text-xs font-bold text-gray-600 bg-gray-100 px-2 py-0.5 rounded-md">
                                        1 Siku
                                    </span>
                                    <span v-else class="text-xs font-bold text-gray-700 bg-gray-100 px-2 py-0.5 rounded-md">
                                        {{ calculateNights(booking) }} Nights
                                    </span>
                                </td>

                                <!-- Total -->
                                <td class="py-3.5 px-4 font-bold text-xs" :class="['confirmed', 'checked_in', 'checked_out'].includes(booking.status) ? 'text-gray-900' : 'text-gray-400 italic'">
                                    {{ ['confirmed', 'checked_in', 'checked_out'].includes(booking.status) ? formatCurrency(booking.total) : '—' }}
                                </td>

                                <!-- Paid -->
                                <td class="py-3.5 px-4 font-semibold text-xs whitespace-nowrap">
                                    <div v-if="['confirmed', 'checked_in', 'checked_out'].includes(booking.status)">
                                        <span class="text-emerald-700 font-bold">{{ formatCurrency(booking.amount_paid) }}</span>
                                        <span v-if="Number(booking.amount_paid) > Number(booking.total)" class="ml-1 px-1.5 py-0.2 rounded text-[9px] bg-purple-100 text-purple-800 font-bold" title="Kuna ziada ya malipo">+Ziada</span>
                                    </div>
                                    <span v-else class="text-gray-400 italic">—</span>
                                </td>

                                <!-- Balance -->
                                <td class="py-3.5 px-4 font-semibold text-xs whitespace-nowrap">
                                    <div v-if="['confirmed', 'checked_in', 'checked_out'].includes(booking.status)">
                                        <span v-if="parseFloat(booking.balance) > 0" class="text-red-600 font-bold">
                                            {{ formatCurrency(booking.balance) }}
                                        </span>
                                        <span v-else class="text-emerald-700 font-bold flex items-center gap-1">
                                            <span>✓ TSh 0</span>
                                        </span>
                                    </div>
                                    <span v-else class="text-gray-400 italic">—</span>
                                </td>

                                <!-- Status Badge (Interactive Click to Change) -->
                                <td class="py-3.5 px-4 text-center">
                                    <button
                                        type="button"
                                        @click="openStatusModal(booking)"
                                        class="px-2.5 py-1 rounded-full text-[11px] font-bold capitalize border inline-flex items-center gap-1 transition hover:opacity-80 cursor-pointer shadow-2xs"
                                        :class="{
                                            'bg-amber-50 text-amber-800 border-amber-300': booking.status === 'pending',
                                            'bg-emerald-50 text-emerald-800 border-emerald-300': booking.status === 'confirmed',
                                            'bg-blue-50 text-blue-800 border-blue-300': booking.status === 'checked_in',
                                            'bg-gray-100 text-gray-800 border-gray-300': booking.status === 'checked_out',
                                            'bg-red-50 text-red-800 border-red-300': booking.status === 'cancelled',
                                            'bg-purple-50 text-purple-800 border-purple-300': booking.status === 'no_show',
                                        }"
                                        title="Bofya hapa kubadilisha status ya booking hii moja kwa moja"
                                    >
                                        <span>{{ booking.status }}</span>
                                        <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                </td>

                                <!-- Action Buttons -->
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Edit Financials Button -->
                                        <button
                                            type="button"
                                            @click="openFinancialsModal(booking)"
                                            class="px-2 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200/70 rounded-lg text-xs font-bold transition inline-flex items-center gap-1 cursor-pointer"
                                            title="Hariri kiasi alicholipa mteja na salio (Total & Paid Amount)"
                                        >
                                            <span>💰 Pesa</span>
                                        </button>

                                        <!-- View Details Button -->
                                        <Link 
                                            :href="route('admin.bookings.show', booking.id)" 
                                            class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-bold transition inline-flex items-center gap-0.5"
                                            title="Tazama maelezo yote ya booking"
                                        >
                                            <span>Tazama</span>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </Link>
                                    </div>
                                </td>
                            </tr>

                            <!-- Empty State -->
                            <tr v-if="!bookings.data || bookings.data.length === 0">
                                <td colspan="10" class="py-12 text-center">
                                    <div class="max-w-xs mx-auto space-y-2">
                                        <div class="text-3xl">📭</div>
                                        <div class="font-bold text-gray-700 text-sm">
                                            <span v-if="currentType === 'villa'">Hakuna booking za Villa zilizopatikana.</span>
                                            <span v-else-if="currentType === 'tour'">Hakuna booking za Farm Tours zilizopatikana.</span>
                                            <span v-else>Hakuna booking zilizopatikana.</span>
                                        </div>
                                        <p class="text-xs text-gray-400">
                                            Jaribu kubadilisha vichungi vya tarehe, jina, au status ili kuona matokeo zaidi.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="bookings.links && bookings.links.length > 3" class="p-4 bg-gray-50/60 border-t border-gray-100 flex items-center justify-between">
                    <div class="text-xs text-gray-500">
                        Inaonyesha <span class="font-bold">{{ bookings.from || 0 }}</span> hadi <span class="font-bold">{{ bookings.to || 0 }}</span> kati ya <span class="font-bold">{{ bookings.total || 0 }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <template v-for="(link, i) in bookings.links" :key="i">
                            <Link 
                                v-if="link.url"
                                :href="link.url" 
                                class="px-3 py-1 text-xs rounded-lg font-semibold transition"
                                :class="link.active ? 'bg-emerald-700 text-white font-bold' : 'bg-white border text-gray-700 hover:bg-gray-100'"
                                v-html="link.label"
                            />
                            <span 
                                v-else 
                                class="px-2 py-1 text-xs text-gray-300"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════════════════════
                 4. QUICK STATUS CHANGER MODAL
            ══════════════════════════════════════════════════════════════════ -->
            <div v-if="isStatusModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
                <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-gray-100 space-y-5 animate-in fade-in zoom-in-95 duration-150">
                    <div class="flex items-center justify-between border-b pb-3">
                        <div>
                            <h3 class="font-bold text-gray-900 text-base">Badilisha Hali ya Booking</h3>
                            <span class="text-xs font-mono text-emerald-800 font-bold">{{ selectedBooking?.reference }} — {{ selectedBooking?.customer?.name }}</span>
                        </div>
                        <button @click="closeStatusModal" type="button" class="text-gray-400 hover:text-gray-600 text-lg cursor-pointer">&times;</button>
                    </div>

                    <form @submit.prevent="submitStatusChange" class="space-y-4">
                        <div>
                            <label class="text-xs font-bold text-gray-700 block mb-1">Chagua Status Mpya</label>
                            <select v-model="statusForm.status" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="pending">🟡 Pending (Inasubiri uthibitisho - Haitasoma kwenye mapato)</option>
                                <option value="confirmed">🟢 Confirmed (Imethibitishwa - Inasoma kwenye mapato ya Dashboard)</option>
                                <option value="checked_in">🔵 Checked In (Amewasili hotelini)</option>
                                <option value="checked_out">⚪ Checked Out (Ameondoka hotelini)</option>
                                <option value="cancelled">🔴 Cancelled (Imeghairiwa - Haitasoma kwenye mapato)</option>
                                <option value="no_show">🟣 No Show (Hakutokea)</option>
                            </select>
                            <p class="text-[11px] text-gray-500 mt-1">
                                <span v-if="['pending', 'cancelled'].includes(statusForm.status)" class="text-amber-700 font-semibold">
                                    ⚠️ Ukichagua <strong>{{ statusForm.status }}</strong>, booking hii haitahesabiwa kwenye mapato ya Dashboard mpaka iwe confirmed.
                                </span>
                                <span v-else-if="statusForm.status === 'confirmed'" class="text-emerald-700 font-semibold">
                                    ✅ Ukichagua <strong>Confirmed</strong>, booking hii itasoma rasmi kwenye mapato halisi ya Dashboard.
                                </span>
                            </p>
                        </div>

                        <div>
                            <label class="text-xs font-bold text-gray-700 block mb-1">Maelezo ya Ziada / Sababu (Hiari)</label>
                            <textarea 
                                v-model="statusForm.notes" 
                                rows="2" 
                                placeholder="Mfano: Mteja amelipia kwa M-Pesa / Amepiga simu kughairi..." 
                                class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"
                            ></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2 border-t">
                            <button 
                                @click="closeStatusModal" 
                                type="button" 
                                class="px-4 py-2 border rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-50 transition cursor-pointer"
                            >
                                Ghairi
                            </button>
                            <button 
                                type="submit" 
                                :disabled="statusForm.processing"
                                class="px-5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-bold shadow transition cursor-pointer disabled:opacity-50"
                            >
                                <span v-if="statusForm.processing">Inasasisha...</span>
                                <span v-else>Sasisha Status Sasa</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════════════════════
                 5. FINANCIAL ADJUSTMENT MODAL (Total, Amount Paid, Balance)
            ══════════════════════════════════════════════════════════════════ -->
            <div v-if="isFinancialsModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
                <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 space-y-5 animate-in fade-in zoom-in-95 duration-150">
                    <div class="flex items-center justify-between border-b pb-3">
                        <div>
                            <h3 class="font-bold text-gray-900 text-base">💰 Hariri Malipo &amp; Salio (Financials Desk)</h3>
                            <span class="text-xs font-mono text-emerald-800 font-bold">
                                {{ selectedBooking?.reference }} — {{ selectedBooking?.customer?.name }}
                            </span>
                        </div>
                        <button @click="closeFinancialsModal" type="button" class="text-gray-400 hover:text-gray-600 text-lg cursor-pointer">&times;</button>
                    </div>

                    <form @submit.prevent="submitFinancialsChange" class="space-y-4">
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Total Price -->
                            <div>
                                <label class="text-xs font-bold text-gray-700 block mb-1">Jumla Kuu (Total Amount)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-xs text-gray-400 font-bold">TSh</span>
                                    <input 
                                        type="number" 
                                        step="1" 
                                        min="0"
                                        v-model="financialsForm.total" 
                                        class="w-full pl-11 text-xs rounded-xl border-gray-300 font-bold text-gray-900 focus:border-emerald-500 focus:ring-emerald-500"
                                        placeholder="0"
                                    />
                                </div>
                                <span class="text-[10px] text-gray-400 mt-0.5 block">Jumla ya gharama za chumba/huduma.</span>
                            </div>

                            <!-- Amount Paid -->
                            <div>
                                <label class="text-xs font-bold text-emerald-800 block mb-1">Kiasi Alicholipa Mteja (Paid)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-xs text-emerald-600 font-bold">TSh</span>
                                    <input 
                                        type="number" 
                                        step="1" 
                                        min="0"
                                        v-model="financialsForm.amount_paid" 
                                        class="w-full pl-11 text-xs rounded-xl border-emerald-300 bg-emerald-50/20 font-bold text-emerald-800 focus:border-emerald-500 focus:ring-emerald-500"
                                        placeholder="0"
                                    />
                                </div>
                                <span class="text-[10px] text-gray-400 mt-0.5 block">Pesa halisi iliyoingia (hata ikiwa zaidi au kidogo).</span>
                            </div>
                        </div>

                        <!-- Live Calculation Preview Card -->
                        <div class="p-3.5 rounded-2xl border" 
                            :class="{
                                'bg-red-50/70 border-red-200 text-red-900': calculatedBalance > 0,
                                'bg-purple-50/70 border-purple-200 text-purple-900': calculatedOverpaid > 0,
                                'bg-emerald-50/70 border-emerald-200 text-emerald-900': calculatedBalance === 0 && calculatedOverpaid === 0
                            }">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold">Hali ya Salio / Hesabu:</span>
                                <span v-if="calculatedBalance > 0" class="font-extrabold text-red-700">
                                    Salio Linalodaiwa (Outstanding): {{ formatCurrency(calculatedBalance) }}
                                </span>
                                <span v-else-if="calculatedOverpaid > 0" class="font-extrabold text-purple-700">
                                    Amelipa Zaidi (Overpaid): {{ formatCurrency(calculatedOverpaid) }} (Salio: TSh 0)
                                </span>
                                <span v-else class="font-extrabold text-emerald-700">
                                    ✓ Limelipwa Kamili: Salio TSh 0
                                </span>
                            </div>
                            <p class="text-[11px] opacity-80 mt-1">
                                Hesabu hii itarekodiwa moja kwa moja kwenye ripoti za fedha, risiti za wateja, na Dashboard ya mapato.
                            </p>
                        </div>

                        <!-- Payment Method & Reference -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-gray-700 block mb-1">Njia ya Malipo</label>
                                <select v-model="financialsForm.payment_method" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                    <option value="mobile_money">📱 Mobile Money (M-Pesa, Tigopesa, Airtel)</option>
                                    <option value="cash">💵 Cash (Taslimu)</option>
                                    <option value="bank_transfer">🏦 Bank Transfer</option>
                                    <option value="card">💳 Card (Visa / Mastercard)</option>
                                    <option value="other">Kwingineko</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-xs font-bold text-gray-700 block mb-1">Kumbukumbu ya Muamala</label>
                                <input 
                                    type="text" 
                                    v-model="financialsForm.payment_reference" 
                                    placeholder="e.g. QX98127391 / Risiti No" 
                                    class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-bold text-gray-700 block mb-1">Sababu / Maelezo ya Mabadiliko (Hiari)</label>
                            <textarea 
                                v-model="financialsForm.notes" 
                                rows="2" 
                                placeholder="Mfano: Mteja ameongeza tip / Tulikubaliana punguzo maalum la TSh..." 
                                class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"
                            ></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2 border-t">
                            <button 
                                @click="closeFinancialsModal" 
                                type="button" 
                                class="px-4 py-2 border rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-50 transition cursor-pointer"
                            >
                                Ghairi
                            </button>
                            <button 
                                type="submit" 
                                :disabled="financialsForm.processing"
                                class="px-5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-bold shadow transition cursor-pointer disabled:opacity-50"
                            >
                                <span v-if="financialsForm.processing">Inahifadhi...</span>
                                <span v-else>Hifadhi Taarifa za Malipo</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
