<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

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
                                <th class="py-3.5 px-4">Imelipwa</th>
                                <th class="py-3.5 px-4">Salio</th>
                                <th class="py-3.5 px-4 text-center">Status</th>
                                <th class="py-3.5 px-4 text-right">Kitendo</th>
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
                                <td class="py-3.5 px-4 font-semibold text-xs" :class="['confirmed', 'checked_in', 'checked_out'].includes(booking.status) ? 'text-emerald-700' : 'text-gray-400 italic'">
                                    {{ ['confirmed', 'checked_in', 'checked_out'].includes(booking.status) ? formatCurrency(booking.amount_paid) : '—' }}
                                </td>

                                <!-- Balance -->
                                <td class="py-3.5 px-4 font-semibold text-xs" :class="['confirmed', 'checked_in', 'checked_out'].includes(booking.status) && parseFloat(booking.balance) > 0 ? 'text-red-500' : 'text-gray-400'">
                                    {{ ['confirmed', 'checked_in', 'checked_out'].includes(booking.status) ? formatCurrency(booking.balance) : '—' }}
                                </td>

                                <!-- Status Badge -->
                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold capitalize border inline-block"
                                        :class="{
                                            'bg-amber-50 text-amber-800 border-amber-200': booking.status === 'pending',
                                            'bg-emerald-50 text-emerald-800 border-emerald-200': booking.status === 'confirmed',
                                            'bg-blue-50 text-blue-800 border-blue-200': booking.status === 'checked_in',
                                            'bg-gray-100 text-gray-800 border-gray-200': booking.status === 'checked_out',
                                            'bg-red-50 text-red-800 border-red-200': booking.status === 'cancelled',
                                            'bg-purple-50 text-purple-800 border-purple-200': booking.status === 'no_show',
                                        }">
                                        {{ booking.status }}
                                    </span>
                                </td>

                                <!-- Action -->
                                <td class="py-3.5 px-4 text-right">
                                    <Link 
                                        :href="route('admin.bookings.show', booking.id)" 
                                        class="px-2.5 py-1 bg-gray-100 hover:bg-emerald-50 hover:text-emerald-800 text-gray-700 rounded-lg text-xs font-bold transition inline-flex items-center gap-1"
                                    >
                                        <span>Tazama</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </Link>
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

        </div>
    </AuthenticatedLayout>
</template>
