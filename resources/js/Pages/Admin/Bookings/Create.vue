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

const bookingType = ref('villa'); // 'villa' or 'tour'

const form = useForm({
    booking_type: 'villa',
    customer_mode: 'new', // 'new' or 'existing'
    customer_id: '',
    customer_name: '',
    customer_phone: '',
    customer_email: '',
    
    // Villa fields
    accommodation_type_id: props.villas[0]?.id || '',
    check_in: today,
    check_out: tomorrow,
    
    // Tour fields
    farm_tour_id: props.experiences[0]?.id || '',
    tour_date: today,
    time_slot: '09:00 AM - 11:00 AM',
    
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

// ─── GUESTS CAPACITY & VALIDATION ────────────────────────────────────────────
const guestError = ref('');

const maxCapacity = computed(() => {
    if (bookingType.value === 'villa') {
        return selectedVilla.value ? Number(selectedVilla.value.capacity) || 2 : 10;
    } else {
        return selectedTour.value ? Number(selectedTour.value.capacity_per_slot) || 20 : 30;
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
    } else {
        return selectedTour.value ? Number(selectedTour.value.price) : 0;
    }
});

const subtotal = computed(() => {
    if (bookingType.value === 'villa') {
        return baseRate.value * numberOfNights.value;
    } else {
        return baseRate.value * (Number(form.guests_count) || 1);
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
    return 0; // Day tours prices are VAT inclusive/exempt
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
                <div class="bg-white p-2 rounded-2xl shadow-xs border border-gray-200 flex flex-col sm:flex-row gap-2">
                    <button
                        type="button"
                        @click="bookingType = 'villa'"
                        class="flex-1 py-3 px-4 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 cursor-pointer"
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
                        class="flex-1 py-3 px-4 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 cursor-pointer"
                        :class="bookingType === 'tour' 
                            ? 'bg-[#14301F] text-white shadow-sm' 
                            : 'bg-gray-50 hover:bg-gray-100 text-gray-700'"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Farm Tour / Day Experience</span>
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
                    <div v-else class="bg-white p-6 rounded-2xl shadow-xs border border-gray-200 space-y-4">
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

                    <!-- ── 2. GUEST PROFILE DETAILS ────────────────────────────── -->
                    <div class="bg-white p-6 rounded-2xl shadow-xs border border-gray-200 space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span>Lead Guest / Visitor Details</span>
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
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Full Name *</label>
                                <input type="text" v-model="form.customer_name" placeholder="e.g. John Doe" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 py-2.5" required>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Phone / WhatsApp</label>
                                <input type="text" v-model="form.customer_phone" placeholder="+255 7..." class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 py-2.5">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Email Address</label>
                                <input type="email" v-model="form.customer_email" placeholder="client@example.com" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 py-2.5">
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
                                    <option value="pending">Pending</option>
                                    <option value="checked_in">{{ bookingType === 'villa' ? 'Checked In' : 'Tour Started' }}</option>
                                    <option value="completed">{{ bookingType === 'villa' ? 'Checked Out' : 'Completed' }}</option>
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
                                    {{ bookingType === 'villa' ? 'Custom Rate / Night (TZS)' : 'Custom Rate / Person (TZS)' }}
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
                            <textarea v-model="form.notes" rows="2" placeholder="Special requirements, dietary preferences, arrival notes..." class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-600 focus:ring-emerald-600"></textarea>
                        </div>

                        <!-- Financial Calculation Card -->
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 text-xs font-mono space-y-1.5 mt-4">
                            <div v-if="bookingType === 'villa'" class="flex justify-between text-gray-600">
                                <span>Subtotal ({{ numberOfNights }} nights @ {{ formatCurrency(baseRate) }}):</span>
                                <span class="font-bold">{{ formatCurrency(subtotal) }}</span>
                            </div>
                            <div v-else class="flex justify-between text-gray-600">
                                <span>Subtotal ({{ form.guests_count }} visitors @ {{ formatCurrency(baseRate) }}/person):</span>
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
                            <span>{{ form.processing ? 'Creating Booking...' : (bookingType === 'tour' ? 'Save & Confirm Farm Tour' : 'Save & Confirm Villa Stay') }}</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
