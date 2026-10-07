<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    villas: {
        type: Array,
        default: () => [],
    },
    experiences: {
        type: Array,
        default: () => [],
    },
    customers: {
        type: Array,
        default: () => [],
    },
});

const today = new Date().toISOString().split('T')[0];
const tomorrow = new Date(Date.now() + 86400000).toISOString().split('T')[0];

const bookingType = ref('villa'); // 'villa', 'tour', or 'consultation'

const form = useForm({
    booking_type: 'villa',
    customer_mode: 'new', // 'new' or 'existing'
    customer_id: '',
    customer_name: '',
    customer_phone: '',
    customer_email: '',
    id_type: 'nida',
    id_number: '',
    id_document: null,
    
    // Villa fields
    accommodation_type_id: props.villas[0]?.id || '',
    check_in: today,
    check_out: tomorrow,
    
    // Tour fields
    farm_tour_id: props.experiences[0]?.id || '',
    tour_date: today,
    time_slot: '09:00 AM - 11:00 AM',

    // Consultation fields
    consultation_format: 'physical', // 'physical' or 'online'
    consultation_topic: 'Agritourism & Farm Resort Setup',
    consultation_date: today,
    consultation_time: 'Morning Session (09:00 AM - 11:00 AM)',
    
    // Shared fields
    guests_count: 1,
    status: 'confirmed',
    source: 'walk_in',
    rate_override: '',
    discount: '',
    amount_paid: '',
    payment_method: 'cash',
    payment_reference: '',
    notes: '',
});

const fileInputRef = ref(null);
const idPreview = ref(null);
const isPdfFile = ref(false);
const fileName = ref('');

const handleIdFileUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
        if (file.size > 10 * 1024 * 1024) {
            alert('File size exceeds 10MB limit.');
            return;
        }
        form.id_document = file;
        fileName.value = file.name;
        if (file.type === 'application/pdf') {
            isPdfFile.value = true;
            idPreview.value = null;
        } else if (file.type.startsWith('image/')) {
            isPdfFile.value = false;
            const reader = new FileReader();
            reader.onload = (ev) => {
                idPreview.value = ev.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
};

const removeIdDocument = () => {
    form.id_document = null;
    idPreview.value = null;
    isPdfFile.value = false;
    fileName.value = '';
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
};

// Watch bookingType and update form.booking_type
watch(bookingType, (newType) => {
    form.booking_type = newType;
    guestError.value = '';
    form.rate_override = '';
    form.discount = '';
    form.amount_paid = '';
});

// ─── VILLA LOGIC ─────────────────────────────────────────────────────────────
const selectedVilla = computed(() => {
    return props.villas.find(v => v.id == form.accommodation_type_id) || null;
});

// ─── TOUR LOGIC ──────────────────────────────────────────────────────────────
const selectedTour = computed(() => {
    return props.experiences.find(t => t.id == form.farm_tour_id) || null;
});

const defaultTimeSlots = [
    '09:00 AM - 11:00 AM (Morning Tour)',
    '11:30 AM - 01:30 PM (Midday Harvest Walk)',
    '02:00 PM - 04:00 PM (Afternoon Explorer)',
    '04:30 PM - 06:30 PM (Sunset Pasture Tour)',
];

// ─── CONSULTATION LOGIC ──────────────────────────────────────────────────────
const consultationTopics = [
    'Agritourism & Farm Resort Setup',
    'Commercial Poultry & Free-Range Layers',
    'Pasture Dairy, Milking & Value Addition',
    'Organic Horticulture & Fruit Orchards',
    'Master Planning & Estate Infrastructure',
    '1-on-1 Direct Strategic Roadmap',
    'General Agricultural Consultation & Advisory',
];

const consultationTimeSlots = [
    'Morning Session (09:00 AM - 11:00 AM)',
    'Midday Session (11:30 AM - 01:30 PM)',
    'Afternoon Session (02:30 PM - 04:30 PM)',
    'Evening Session (05:00 PM - 07:00 PM)',
];

// ─── GUESTS CAPACITY & VALIDATION ────────────────────────────────────────────
const guestError = ref('');

const maxCapacity = computed(() => {
    if (bookingType.value === 'villa') {
        return selectedVilla.value ? Number(selectedVilla.value.capacity) || 2 : 10;
    } else if (bookingType.value === 'tour') {
        return selectedTour.value ? Number(selectedTour.value.capacity_per_slot) || 20 : 30;
    } else {
        return 10;
    }
});

const validateGuests = () => {
    if (form.guests_count === '' || form.guests_count === null) return;
    const val = parseInt(form.guests_count, 10);
    const max = maxCapacity.value;
    if (isNaN(val) || val < 1) {
        guestError.value = 'Minimum 1 guest required';
        form.guests_count = 1;
    } else if (val > max) {
        guestError.value = `Maximum ${max} guests allowed`;
        form.guests_count = max;
    } else {
        guestError.value = '';
        form.guests_count = val;
    }
};

const incrementGuests = () => {
    const val = parseInt(form.guests_count || 1, 10);
    const max = maxCapacity.value;
    if (val < max) {
        form.guests_count = val + 1;
        guestError.value = '';
    } else {
        guestError.value = `Maximum ${max} guests allowed`;
    }
};

const decrementGuests = () => {
    const val = parseInt(form.guests_count || 1, 10);
    if (val > 1) {
        form.guests_count = val - 1;
        guestError.value = '';
    } else {
        guestError.value = 'Minimum 1 guest required';
    }
};

const onVillaChange = () => {
    if (selectedVilla.value) {
        if (form.guests_count > selectedVilla.value.capacity) {
            form.guests_count = selectedVilla.value.capacity;
        }
        guestError.value = '';
        form.rate_override = '';
    }
};

const onTourChange = () => {
    if (selectedTour.value) {
        if (form.guests_count > selectedTour.value.capacity_per_slot) {
            form.guests_count = selectedTour.value.capacity_per_slot;
        }
        guestError.value = '';
        form.rate_override = '';
    }
};

// ─── FINANCIAL CALCULATIONS ──────────────────────────────────────────────────
const numberOfNights = computed(() => {
    if (bookingType.value !== 'villa') return 1;
    if (!form.check_in || !form.check_out) return 1;
    const start = new Date(form.check_in);
    const end = new Date(form.check_out);
    const diff = end - start;
    const nights = Math.round(diff / (1000 * 60 * 60 * 24));
    return nights > 0 ? nights : 1;
});

const baseRate = computed(() => {
    if (form.rate_override !== '' && !isNaN(form.rate_override) && Number(form.rate_override) >= 0) {
        return Number(form.rate_override);
    }
    if (bookingType.value === 'villa') {
        return selectedVilla.value ? Number(selectedVilla.value.base_price) : 0;
    } else if (bookingType.value === 'tour') {
        return selectedTour.value ? Number(selectedTour.value.price) : 0;
    } else {
        return 100000; // Standard Mr. Kitonga Consultation Fee: TZS 100,000
    }
});

const subtotal = computed(() => {
    if (bookingType.value === 'villa') {
        return baseRate.value * numberOfNights.value;
    } else if (bookingType.value === 'tour') {
        return baseRate.value * (Number(form.guests_count) || 1);
    } else {
        return baseRate.value; // Consultation fee
    }
});

const discountAmount = computed(() => {
    const d = Number(form.discount);
    return !isNaN(d) && d > 0 ? d : 0;
});

const taxAmount = computed(() => {
    if (bookingType.value === 'villa') {
        const taxable = Math.max(0, subtotal.value - discountAmount.value);
        return taxable * 0.18; // 18% VAT for accommodation
    }
    return 0; // Day tours & Consultations are direct/VAT inclusive
});

const grandTotal = computed(() => {
    return Math.max(0, subtotal.value - discountAmount.value) + taxAmount.value;
});

const paidAmount = computed(() => {
    const p = Number(form.amount_paid);
    return !isNaN(p) && p > 0 ? p : 0;
});

const balanceDue = computed(() => {
    return Math.max(0, grandTotal.value - paidAmount.value);
});

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-TZ', { style: 'currency', currency: 'TZS', maximumFractionDigits: 0 }).format(val || 0);
};

const submitBooking = () => {
    form.post(route('admin.bookings.store'), {
        onError: (errors) => {
            alert(Object.values(errors).join('\n'));
        }
    });
};
</script>

<template>
    <Head title="Create New Reservation" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Create New Reservation
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Record a manual booking for a Villa Stay or a Farm Tour / Day Experience.</p>
                </div>
                <Link :href="route('admin.bookings.index')" class="self-start sm:self-auto px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded shadow-xs transition">
                    Back to List
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8 space-y-6">

                <!-- ── TYPE TOGGLE TABS ────────────────────────────────────── -->
                <div class="bg-white p-2 rounded-2xl shadow-xs border border-gray-200 grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <button
                        type="button"
                        @click="bookingType = 'villa'"
                        class="py-3 px-4 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 cursor-pointer"
                        :class="bookingType === 'villa' 
                            ? 'bg-[#14301F] text-white shadow-sm' 
                            : 'bg-gray-50 hover:bg-gray-100 text-gray-700'"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span>Villa Stay Reservation</span>
                    </button>
                    
                    <button
                        type="button"
                        @click="bookingType = 'tour'"
                        class="py-3 px-4 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 cursor-pointer"
                        :class="bookingType === 'tour' 
                            ? 'bg-[#14301F] text-white shadow-sm' 
                            : 'bg-gray-50 hover:bg-gray-100 text-gray-700'"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Farm Tour / Experience</span>
                    </button>

                    <button
                        type="button"
                        @click="bookingType = 'consultation'"
                        class="py-3 px-4 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 cursor-pointer"
                        :class="bookingType === 'consultation' 
                            ? 'bg-[#14301F] text-white shadow-sm ring-2 ring-amber-400/50' 
                            : 'bg-amber-50/60 hover:bg-amber-100/70 text-amber-950 border border-amber-200/60'"
                    >
                        <span class="text-amber-400">✦</span>
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Meet Mr. Kitonga</span>
                    </button>
                </div>

                <form @submit.prevent="submitBooking" class="space-y-6">
                    
                    <!-- ── OPTION A: VILLA DETAILS ────────────────────────────── -->
                    <div v-if="bookingType === 'villa'" class="bg-white p-6 rounded-2xl shadow-xs border border-gray-200 space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                <span>Villa &amp; Stay Details</span>
                            </h3>
                            <span v-if="selectedVilla" class="text-xs font-bold text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">
                                Max Capacity: {{ selectedVilla.capacity }} Guests
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                            <!-- Villa Selection -->
                            <div class="sm:col-span-2">
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Villa / Room Category *</label>
                                <select v-model="form.accommodation_type_id" @change="onVillaChange" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 py-2.5" required>
                                    <option v-for="villa in villas" :key="villa.id" :value="villa.id">
                                        {{ villa.name }} — {{ formatCurrency(villa.base_price) }}/night (Max {{ villa.capacity }} guests)
                                    </option>
                                </select>
                            </div>

                            <!-- Number of Guests -->
                            <div class="sm:col-span-2 space-y-1">
                                <div class="flex justify-between items-center">
                                    <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block">
                                        Number of Guests (1 to {{ maxCapacity }}) *
                                    </label>
                                    <span v-if="guestError" class="text-[10px] text-red-600 font-bold">
                                        {{ guestError }}
                                    </span>
                                </div>
                                
                                <div class="flex items-center rounded-xl border border-gray-300 overflow-hidden bg-white shadow-xs focus-within:border-emerald-600 focus-within:ring-1 focus-within:ring-emerald-600">
                                    <button 
                                        type="button" 
                                        @click="decrementGuests" 
                                        :disabled="parseInt(form.guests_count || 1) <= 1"
                                        class="px-4 py-2.5 bg-gray-50 hover:bg-gray-100 text-gray-700 font-bold text-sm border-r border-gray-200 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer"
                                    >
                                        −
                                    </button>
                                    
                                    <input 
                                        type="number" 
                                        v-model.number="form.guests_count" 
                                        @input="validateGuests"
                                        @blur="validateGuests"
                                        min="1" 
                                        :max="maxCapacity" 
                                        placeholder="1" 
                                        class="w-full text-xs text-center font-bold text-gray-900 border-0 focus:ring-0 py-2.5 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                        required
                                    />
                                    
                                    <button 
                                        type="button" 
                                        @click="incrementGuests" 
                                        :disabled="parseInt(form.guests_count || 1) >= maxCapacity"
                                        class="px-4 py-2.5 bg-gray-50 hover:bg-gray-100 text-gray-700 font-bold text-sm border-l border-gray-200 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer"
                                    >
                                        +
                                    </button>
                                </div>
                                
                                <p class="text-[10px] text-gray-400">
                                    {{ form.guests_count == 1 ? '1 Guest (Single Occupancy)' : form.guests_count + ' Guests (Max ' + maxCapacity + ' for ' + (selectedVilla?.name || 'this room') + ')' }}
                                </p>
                            </div>

                            <!-- Check-in Date -->
                            <div class="sm:col-span-2">
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Check-in Date *</label>
                                <input type="date" v-model="form.check_in" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 py-2.5" required>
                            </div>

                            <!-- Check-out Date -->
                            <div class="sm:col-span-2">
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Check-out Date ({{ numberOfNights }} Nights) *</label>
                                <input type="date" v-model="form.check_out" :min="form.check_in" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 py-2.5" required>
                            </div>
                        </div>
                    </div>

                    <!-- ── OPTION B: FARM TOUR DETAILS ────────────────────────── -->
                    <div v-else-if="bookingType === 'tour'" class="bg-white p-6 rounded-2xl shadow-xs border border-gray-200 space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Farm Tour &amp; Experience Details</span>
                            </h3>
                            <span v-if="selectedTour" class="text-xs font-bold text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">
                                Max Capacity: {{ selectedTour.capacity_per_slot || 20 }} Visitors / Slot
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                            <!-- Tour Experience Selection -->
                            <div class="sm:col-span-2">
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Select Farm Tour *</label>
                                <select v-model="form.farm_tour_id" @change="onTourChange" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 py-2.5" required>
                                    <option v-for="tour in experiences" :key="tour.id" :value="tour.id">
                                        {{ tour.name }} — {{ formatCurrency(tour.price) }}/person ({{ tour.duration || '2 hrs' }})
                                    </option>
                                </select>
                            </div>

                            <!-- Number of Visitors -->
                            <div class="sm:col-span-2 space-y-1">
                                <div class="flex justify-between items-center">
                                    <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block">
                                        Number of Visitors (1 to {{ maxCapacity }}) *
                                    </label>
                                    <span v-if="guestError" class="text-[10px] text-red-600 font-bold">
                                        {{ guestError }}
                                    </span>
                                </div>
                                
                                <div class="flex items-center rounded-xl border border-gray-300 overflow-hidden bg-white shadow-xs focus-within:border-emerald-600 focus-within:ring-1 focus-within:ring-emerald-600">
                                    <button 
                                        type="button" 
                                        @click="decrementGuests" 
                                        :disabled="parseInt(form.guests_count || 1) <= 1"
                                        class="px-4 py-2.5 bg-gray-50 hover:bg-gray-100 text-gray-700 font-bold text-sm border-r border-gray-200 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer"
                                    >
                                        −
                                    </button>
                                    
                                    <input 
                                        type="number" 
                                        v-model.number="form.guests_count" 
                                        @input="validateGuests"
                                        @blur="validateGuests"
                                        min="1" 
                                        :max="maxCapacity" 
                                        placeholder="1" 
                                        class="w-full text-xs text-center font-bold text-gray-900 border-0 focus:ring-0 py-2.5 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                        required
                                    />
                                    
                                    <button 
                                        type="button" 
                                        @click="incrementGuests" 
                                        :disabled="parseInt(form.guests_count || 1) >= maxCapacity"
                                        class="px-4 py-2.5 bg-gray-50 hover:bg-gray-100 text-gray-700 font-bold text-sm border-l border-gray-200 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer"
                                    >
                                        +
                                    </button>
                                </div>
                                
                                <p class="text-[10px] text-gray-400">
                                    {{ form.guests_count }} {{ form.guests_count == 1 ? 'Visitor' : 'Visitors' }} @ {{ formatCurrency(baseRate) }} each
                                </p>
                            </div>

                            <!-- Tour Date -->
                            <div class="sm:col-span-2">
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Tour Date *</label>
                                <input type="date" v-model="form.tour_date" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 py-2.5" required>
                            </div>

                            <!-- Time Slot -->
                            <div class="sm:col-span-2">
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Time Slot *</label>
                                <select v-model="form.time_slot" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 py-2.5" required>
                                    <option v-for="slot in defaultTimeSlots" :key="slot" :value="slot">
                                        {{ slot }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- ── OPTION C: MEET MR. KITONGA CONSULTATION DETAILS ────── -->
                    <div v-else-if="bookingType === 'consultation'" class="bg-white p-6 rounded-2xl shadow-xs border border-amber-200/80 bg-linear-to-b from-amber-50/20 to-white space-y-5">
                        <div class="flex items-center justify-between border-b border-amber-100 pb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-600 flex items-center justify-center font-bold text-sm">
                                    ✦
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900 text-sm">
                                        Meet Mr. Kitonga Consultation Session
                                    </h3>
                                    <p class="text-[11px] text-gray-500">1-on-1 Agri-Business Advisory, Estate Master Planning &amp; Poultry/Dairy Strategy.</p>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-amber-900 bg-amber-100 px-3 py-1 rounded-lg border border-amber-300">
                                Fee: {{ formatCurrency(baseRate) }} / Session
                            </span>
                        </div>

                        <!-- Consultation Format Selector -->
                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-2">1. Consultation Format *</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label 
                                    @click="form.consultation_format = 'physical'"
                                    class="flex items-start gap-3 p-3.5 rounded-xl border cursor-pointer transition"
                                    :class="form.consultation_format === 'physical' ? 'border-emerald-600 bg-emerald-50/50 shadow-xs ring-1 ring-emerald-600' : 'border-gray-200 hover:border-gray-300 bg-white'"
                                >
                                    <input type="radio" v-model="form.consultation_format" value="physical" class="mt-0.5 text-emerald-700 focus:ring-emerald-600" />
                                    <div>
                                        <div class="text-xs font-bold text-gray-900">Physical at Kitonga Farm</div>
                                        <div class="text-[11px] text-gray-500 mt-0.5">Kitonga Farm Walkthrough + In-Person Meeting &amp; Refreshments</div>
                                    </div>
                                </label>

                                <label 
                                    @click="form.consultation_format = 'online'"
                                    class="flex items-start gap-3 p-3.5 rounded-xl border cursor-pointer transition"
                                    :class="form.consultation_format === 'online' ? 'border-emerald-600 bg-emerald-50/50 shadow-xs ring-1 ring-emerald-600' : 'border-gray-200 hover:border-gray-300 bg-white'"
                                >
                                    <input type="radio" v-model="form.consultation_format" value="online" class="mt-0.5 text-emerald-700 focus:ring-emerald-600" />
                                    <div>
                                        <div class="text-xs font-bold text-gray-900">Online Live Video Call</div>
                                        <div class="text-[11px] text-gray-500 mt-0.5">Google Meet / Zoom / WhatsApp Live Video Consultation</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 pt-1">
                            <!-- Reason / Focus Topic -->
                            <div class="sm:col-span-2 md:col-span-1">
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Reason / Focus Topic *</label>
                                <select v-model="form.consultation_topic" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 py-2.5" required>
                                    <option v-for="t in consultationTopics" :key="t" :value="t">
                                        {{ t }}
                                    </option>
                                </select>
                            </div>

                            <!-- Preferred Appointment Date -->
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Appointment Date *</label>
                                <input type="date" v-model="form.consultation_date" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 py-2.5" required>
                            </div>

                            <!-- Preferred Time Slot -->
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Time Slot *</label>
                                <select v-model="form.consultation_time" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 py-2.5" required>
                                    <option v-for="s in consultationTimeSlots" :key="s" :value="s">
                                        {{ s }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- ── 2. GUEST / CLIENT DETAILS ───────────────────────────── -->
                    <div class="bg-white p-6 rounded-2xl shadow-xs border border-gray-200 space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span>{{ bookingType === 'consultation' ? 'Client / Attendee Profile' : 'Lead Guest / Visitor Details' }}</span>
                            </h3>
                            <div class="inline-flex rounded-lg bg-gray-100 p-0.5 text-xs font-semibold">
                                <button 
                                    type="button" 
                                    @click="form.customer_mode = 'new'" 
                                    :class="form.customer_mode === 'new' ? 'bg-white shadow-xs text-gray-900' : 'text-gray-500'" 
                                    class="px-3 py-1 rounded-md transition cursor-pointer"
                                >
                                    New Guest
                                </button>
                                <button 
                                    type="button" 
                                    @click="form.customer_mode = 'existing'" 
                                    :class="form.customer_mode === 'existing' ? 'bg-white shadow-xs text-gray-900' : 'text-gray-500'" 
                                    class="px-3 py-1 rounded-md transition cursor-pointer"
                                >
                                    Existing Client
                                </button>
                            </div>
                        </div>

                        <!-- Existing Customer Selection -->
                        <div v-if="form.customer_mode === 'existing'">
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Select Registered Customer *</label>
                            <select v-model="form.customer_id" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 py-2.5" required>
                                <option value="">-- Choose Customer --</option>
                                <option v-for="c in customers" :key="c.id" :value="c.id">
                                    {{ c.name }} ({{ c.phone || 'No phone' }} - {{ c.email || 'No email' }})
                                </option>
                            </select>
                        </div>

                        <!-- New Customer Fields -->
                        <div v-else class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Full Name (Jina Kamili) *</label>
                                <input type="text" v-model="form.customer_name" placeholder="e.g. John Doe / Amani Mwamba" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 py-2.5" required>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Phone / WhatsApp *</label>
                                <input type="text" v-model="form.customer_phone" placeholder="+255 7..." class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 py-2.5">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Email Address (Optional)</label>
                                <input type="email" v-model="form.customer_email" placeholder="client@example.com" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 py-2.5">
                            </div>
                        </div>

                        <!-- Digital ID & Verification Attachment -->
                        <div class="pt-3 border-t border-gray-100 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold text-gray-800 uppercase tracking-wider flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                    </svg>
                                    <span>Client Identification Document (Optional)</span>
                                </span>
                                <span class="text-[10px] px-2 py-0.5 bg-gray-100 text-gray-600 rounded font-semibold">Optional</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">ID Type / Aina ya Kitambulisho</label>
                                    <select v-model="form.id_type" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 py-2.5">
                                        <option value="nida">NIDA / Kitambulisho cha Taifa</option>
                                        <option value="passport">Passport / Pasi ya Kusafiria</option>
                                        <option value="driving_license">Driving License / Leseni</option>
                                        <option value="voter_id">Voter ID / Mpiga Kura</option>
                                        <option value="other">Other Official Document</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">ID Number / Namba ya Kitambulisho</label>
                                    <input type="text" v-model="form.id_number" placeholder="e.g. 19900101-XXXXX-XXXXX" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 py-2.5">
                                </div>
                            </div>

                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Attach ID Photo / Document (Scan or Photo)</label>
                                <div v-if="!form.id_document" class="border-2 border-dashed border-gray-200 hover:border-emerald-600 bg-gray-50 rounded-xl p-3.5 text-center cursor-pointer transition" @click="fileInputRef.click()">
                                    <input ref="fileInputRef" type="file" accept="image/*,application/pdf" @change="handleIdFileUpload" class="hidden" />
                                    <div class="flex items-center justify-center gap-2 text-xs text-gray-600 font-semibold">
                                        <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span>Click to attach client ID snapshot / scan file</span>
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-0.5">Supports JPG, PNG, WEBP, or PDF (Max 10MB)</p>
                                </div>
                                <div v-else class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div v-if="idPreview" class="w-12 h-10 rounded border overflow-hidden bg-white shrink-0">
                                            <img :src="idPreview" class="w-full h-full object-cover" alt="ID preview" />
                                        </div>
                                        <div v-else class="w-12 h-10 rounded border bg-red-100 text-red-800 flex items-center justify-center font-bold text-xs shrink-0">
                                            PDF
                                        </div>
                                        <span class="text-xs font-bold text-emerald-950">{{ fileName || 'Document attached' }}</span>
                                    </div>
                                    <button type="button" @click="removeIdDocument" class="text-xs font-bold text-red-600 hover:text-red-800 transition">Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── 3. PRICING, STATUS & PAYMENT ───────────────────────── -->
                    <div class="bg-white p-6 rounded-2xl shadow-xs border border-gray-200 space-y-4">
                        <h3 class="font-bold text-gray-900 text-sm border-b border-gray-100 pb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            <span>Pricing, Status &amp; Payment</span>
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Initial Status</label>
                                <select v-model="form.status" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 py-2.5">
                                    <option value="confirmed">Confirmed</option>
                                    <option value="pending">Pending / Awaiting Payment</option>
                                    <option value="checked_in">{{ bookingType === 'villa' ? 'Checked In' : (bookingType === 'consultation' ? 'In Session' : 'Tour Started') }}</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Booking Source</label>
                                <select v-model="form.source" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 py-2.5">
                                    <option value="walk_in">Walk-in Guest</option>
                                    <option value="phone">Phone Call</option>
                                    <option value="whatsapp">WhatsApp</option>
                                    <option value="direct">Direct Reception</option>
                                    <option value="online">Online / Web</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">
                                    {{ bookingType === 'villa' ? 'Custom Rate / Night (TZS)' : (bookingType === 'consultation' ? 'Custom Session Fee (TZS)' : 'Custom Rate / Person (TZS)') }}
                                </label>
                                <input type="number" v-model="form.rate_override" :placeholder="String(baseRate || 'Default')" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 py-2.5">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Discount (TZS)</label>
                                <input type="number" v-model="form.discount" placeholder="0" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 py-2.5">
                            </div>
                        </div>

                        <!-- Instant Payment fields -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 border-t border-gray-100">
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Amount Paid Now (TZS)</label>
                                <input type="number" v-model="form.amount_paid" placeholder="0" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 py-2.5">
                            </div>
                            <div v-if="Number(form.amount_paid) > 0">
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Payment Method *</label>
                                <select v-model="form.payment_method" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 py-2.5" required>
                                    <option value="cash">Cash (Counter)</option>
                                    <option value="mobile_money">M-Pesa / Airtel Money / Tigo Pesa</option>
                                    <option value="bank_transfer">Bank Transfer / CRDB / NMB</option>
                                    <option value="card">Credit / Debit Card</option>
                                </select>
                            </div>
                            <div v-if="Number(form.amount_paid) > 0">
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Payment Reference / TxID</label>
                                <input type="text" v-model="form.payment_reference" placeholder="e.g. MPESA-QK87654" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 py-2.5">
                            </div>
                        </div>

                        <div class="pt-2">
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Internal Notes &amp; Special Requests</label>
                            <textarea v-model="form.notes" rows="2" placeholder="Special requirements, project details, arrival notes..." class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-600 focus:ring-emerald-600"></textarea>
                        </div>

                        <!-- Financial Calculation Card -->
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 text-xs font-mono space-y-1.5 mt-4">
                            <div v-if="bookingType === 'villa'" class="flex justify-between text-gray-600">
                                <span>Subtotal ({{ numberOfNights }} nights @ {{ formatCurrency(baseRate) }}):</span>
                                <span class="font-bold">{{ formatCurrency(subtotal) }}</span>
                            </div>
                            <div v-else-if="bookingType === 'tour'" class="flex justify-between text-gray-600">
                                <span>Subtotal ({{ form.guests_count }} visitors @ {{ formatCurrency(baseRate) }}/person):</span>
                                <span class="font-bold">{{ formatCurrency(subtotal) }}</span>
                            </div>
                            <div v-else class="flex justify-between text-gray-600">
                                <span>Subtotal (Consultation Session Fee):</span>
                                <span class="font-bold">{{ formatCurrency(subtotal) }}</span>
                            </div>
                            <div v-if="discountAmount > 0" class="flex justify-between text-red-600">
                                <span>Discount Applied:</span>
                                <span>-{{ formatCurrency(discountAmount) }}</span>
                            </div>
                            <div v-if="bookingType === 'villa'" class="flex justify-between text-gray-600">
                                <span>VAT (18%):</span>
                                <span>{{ formatCurrency(taxAmount) }}</span>
                            </div>
                            <div class="flex justify-between font-bold text-sm text-gray-900 border-t border-gray-200 pt-1.5">
                                <span>Grand Total:</span>
                                <span>{{ formatCurrency(grandTotal) }}</span>
                            </div>
                            <div class="flex justify-between text-emerald-800 font-bold">
                                <span>Amount Paid:</span>
                                <span>{{ formatCurrency(paidAmount) }}</span>
                            </div>
                            <div class="flex justify-between font-bold" :class="balanceDue > 0 ? 'text-red-600' : 'text-gray-400'">
                                <span>Balance Remaining:</span>
                                <span>{{ formatCurrency(balanceDue) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- ── SUBMIT BUTTONS ─────────────────────────────────────── -->
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <Link :href="route('admin.bookings.index')" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold uppercase tracking-wider rounded-xl transition">
                            Cancel
                        </Link>
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="px-8 py-3 bg-emerald-800 hover:bg-emerald-900 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-md transition cursor-pointer disabled:opacity-50 flex items-center gap-2"
                        >
                            <svg v-if="form.processing" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                            <span>{{ form.processing ? 'Creating Booking...' : (bookingType === 'consultation' ? 'Save & Schedule Consultation' : (bookingType === 'tour' ? 'Save & Confirm Farm Tour' : 'Save & Confirm Villa Stay')) }}</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
