<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    villas: {
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

const form = useForm({
    customer_mode: 'new', // 'new' or 'existing'
    customer_id: '',
    customer_name: '',
    customer_phone: '',
    customer_email: '',
    accommodation_type_id: props.villas[0]?.id || '',
    guests_count: 1,
    check_in: today,
    check_out: tomorrow,
    status: 'confirmed',
    source: 'walk_in',
    rate_override: '',
    discount: '',
    amount_paid: '',
    payment_method: 'cash',
    payment_reference: '',
    notes: '',
});

const selectedVilla = computed(() => {
    return props.villas.find(v => v.id == form.accommodation_type_id) || null;
});

const onVillaChange = () => {
    if (selectedVilla.value) {
        if (form.guests_count > selectedVilla.value.capacity) {
            form.guests_count = selectedVilla.value.capacity;
        }
        if (!form.rate_override) {
            form.rate_override = '';
        }
    }
};

const numberOfNights = computed(() => {
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
    return selectedVilla.value ? Number(selectedVilla.value.base_price) : 0;
});

const subtotal = computed(() => {
    return baseRate.value * numberOfNights.value;
});

const discountAmount = computed(() => {
    const d = Number(form.discount);
    return !isNaN(d) && d > 0 ? d : 0;
});

const taxAmount = computed(() => {
    const taxable = Math.max(0, subtotal.value - discountAmount.value);
    return taxable * 0.18; // 18% VAT
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
    <Head title="Create New Booking" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Create New Reservation
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Record a manual villa reservation, walk-in guest, or phone booking.</p>
                </div>
                <Link :href="route('admin.bookings.index')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded shadow-xs transition">
                    ← Back to List
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <form @submit.prevent="submitBooking" class="space-y-6">
                    
                    <!-- 1. VILLA & STAY SELECTION -->
                    <div class="bg-white p-6 rounded-2xl shadow-xs border border-gray-150 space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                                <span>🏡</span> Accommodation & Stay Details
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

                            <!-- Number of Guests (1 to Villa Capacity or Custom) -->
                            <div class="sm:col-span-2">
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">
                                    Number of Guests (Allowed: 1 to {{ selectedVilla?.capacity || 2 }}) *
                                </label>
                                <div class="flex items-center gap-2">
                                    <select v-model.number="form.guests_count" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 py-2.5" required>
                                        <option v-for="n in (selectedVilla?.capacity || 6)" :key="n" :value="n">
                                            {{ n }} {{ n === 1 ? 'Guest (1 Person / Single)' : n + ' Guests' }}
                                        </option>
                                    </select>
                                    <input 
                                        type="number" 
                                        v-model.number="form.guests_count" 
                                        min="1" 
                                        :max="selectedVilla?.capacity || 10" 
                                        placeholder="Qty" 
                                        class="w-20 text-xs text-center rounded-xl border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 py-2.5 font-bold"
                                        title="Directly type guest count"
                                    />
                                </div>
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

                    <!-- 2. GUEST PROFILE DETAILS -->
                    <div class="bg-white p-6 rounded-2xl shadow-xs border border-gray-150 space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                                <span>👤</span> Lead Guest Information
                            </h3>
                            <div class="inline-flex rounded-lg bg-gray-100 p-0.5 text-xs font-semibold">
                                <button 
                                    type="button" 
                                    @click="form.customer_mode = 'new'" 
                                    :class="form.customer_mode === 'new' ? 'bg-white shadow-xs text-gray-900' : 'text-gray-500'" 
                                    class="px-3 py-1 rounded-md transition"
                                >
                                    New Guest
                                </button>
                                <button 
                                    type="button" 
                                    @click="form.customer_mode = 'existing'" 
                                    :class="form.customer_mode === 'existing' ? 'bg-white shadow-xs text-gray-900' : 'text-gray-500'" 
                                    class="px-3 py-1 rounded-md transition"
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

                    <!-- 3. RATES, FINANCIALS & PAYMENT -->
                    <div class="bg-white p-6 rounded-2xl shadow-xs border border-gray-150 space-y-4">
                        <h3 class="font-bold text-gray-900 text-sm border-b border-gray-100 pb-3 flex items-center gap-2">
                            <span>💳</span> Pricing, Status & Payment
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Initial Status</label>
                                <select v-model="form.status" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 py-2.5">
                                    <option value="confirmed">Confirmed</option>
                                    <option value="pending">Pending</option>
                                    <option value="checked_in">Checked In</option>
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
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Custom Rate / Night (TZS)</label>
                                <input type="number" v-model="form.rate_override" :placeholder="selectedVilla ? String(selectedVilla.base_price) : 'Default'" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-600 focus:ring-emerald-600 py-2.5">
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
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Internal Notes & Requests</label>
                            <textarea v-model="form.notes" rows="2" placeholder="Arrival details, specific requests, extra beds..." class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-600 focus:ring-emerald-600"></textarea>
                        </div>

                        <!-- Financial Calculation Card -->
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 text-xs font-mono space-y-1.5 mt-4">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal ({{ numberOfNights }} nights @ {{ formatCurrency(baseRate) }}):</span>
                                <span class="font-bold">{{ formatCurrency(subtotal) }}</span>
                            </div>
                            <div v-if="discountAmount > 0" class="flex justify-between text-red-600">
                                <span>Discount Applied:</span>
                                <span>-{{ formatCurrency(discountAmount) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
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

                    <!-- SUBMIT BUTTONS -->
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <Link :href="route('admin.bookings.index')" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold uppercase tracking-wider rounded-xl transition">
                            Cancel
                        </Link>
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="px-8 py-3 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-md transition cursor-pointer disabled:opacity-50"
                        >
                            {{ form.processing ? 'Creating Booking...' : 'Save & Confirm Booking →' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
