<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    booking: Object,
    statuses: Array,
    payment_methods: Array,
});

const statusForm = useForm({
    status: props.booking.status,
    notes: '',
});

const paymentForm = useForm({
    method: 'cash',
    amount: '',
    reference: '',
});

const isPaymentModalOpen = ref(false);
const isIdModalOpen = ref(false);

const idTypeLabels = {
    nida: 'National ID (NIDA) / Kitambulisho cha Taifa',
    passport: 'International Passport / Pasi ya Kusafiria',
    driving_license: 'Driving License / Leseni ya Udereva',
    voter_id: 'Voter ID / Kitambulisho cha Mpiga Kura',
    other: 'Other Official ID / Kitambulisho Kingine',
};

const idDocumentUrl = () => {
    const path = props.booking.id_document_path || props.booking.customer?.id_document_path;
    if (!path) return null;
    return path.startsWith('http') || path.startsWith('/') ? path : '/' + path;
};

const isIdPdf = () => {
    const url = idDocumentUrl();
    return url && url.toLowerCase().endsWith('.pdf');
};

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-TZ', { style: 'currency', currency: 'TZS', maximumFractionDigits: 0 }).format(val);
};

const updateStatus = () => {
    statusForm.post(route('admin.bookings.status', props.booking.id), {
        onSuccess: () => {
            statusForm.notes = '';
            alert("Booking status updated successfully.");
        }
    });
};

const submitPayment = () => {
    paymentForm.post(route('admin.bookings.payment', props.booking.id), {
        onSuccess: () => {
            paymentForm.amount = '';
            paymentForm.reference = '';
            isPaymentModalOpen.value = false;
            alert("Payment recorded successfully.");
        }
    });
};
</script>

<template>
    <Head title="Booking details" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Reservation Details: {{ booking.reference }}
                </h2>
                <div class="flex space-x-2">
                    <button @click="isPaymentModalOpen = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded shadow transition">
                        Record Payment
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- MAIN RESERVATION CARD -->
                    <div class="lg:col-span-2 space-y-6">
                        
                        <!-- Guest Details & Digital ID Verification -->
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 space-y-5">
                            <div class="flex items-center justify-between border-b pb-3">
                                <h3 class="font-bold text-gray-800 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span>Guest Profile &amp; Contact</span>
                                </h3>
                                <span class="capitalize bg-gray-100 text-gray-700 px-2.5 py-0.5 rounded text-xs font-semibold">Source: {{ booking.source }}</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="text-xs text-gray-400 block uppercase font-semibold">Full Name</span>
                                    <span class="font-bold text-gray-800">{{ booking.customer?.name }}</span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-400 block uppercase font-semibold">Phone Number</span>
                                    <span class="font-mono text-gray-800 font-semibold">{{ booking.customer?.phone || 'N/A' }}</span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-400 block uppercase font-semibold">Email Address</span>
                                    <span class="font-mono text-gray-800 text-xs">{{ booking.customer?.email || 'N/A' }}</span>
                                </div>
                            </div>

                            <!-- DIGITAL GUEST ID & KYC VERIFICATION CARD -->
                            <div class="bg-emerald-50/60 p-4 rounded-xl border border-emerald-100 space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded bg-emerald-800 text-white flex items-center justify-center text-xs font-bold">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                            </svg>
                                        </div>
                                        <span class="text-xs font-bold text-emerald-950 uppercase tracking-wider">Digital ID Verification (Kitambulisho)</span>
                                    </div>

                                    <div v-if="idDocumentUrl()" class="flex items-center gap-1.5 text-xs font-bold text-emerald-800 bg-white px-2.5 py-1 rounded-lg border border-emerald-200 shadow-xs">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        <span>Digital Copy Verified</span>
                                    </div>
                                    <div v-else class="text-xs font-semibold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200">
                                        No ID Copy Uploaded
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                                    <div class="bg-white p-3 rounded-lg border border-gray-200">
                                        <span class="text-[10px] text-gray-400 block uppercase font-bold">Document Type</span>
                                        <span class="font-bold text-gray-800">
                                            {{ idTypeLabels[booking.id_type || booking.customer?.id_type] || (booking.id_type || booking.customer?.id_type || 'Not specified') }}
                                        </span>
                                    </div>
                                    <div class="bg-white p-3 rounded-lg border border-gray-200">
                                        <span class="text-[10px] text-gray-400 block uppercase font-bold">ID / Document Number</span>
                                        <span class="font-mono font-bold text-gray-900">
                                            {{ booking.id_number || booking.customer?.id_number || 'N/A' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Attached ID Preview Thumbnail & Action buttons -->
                                <div v-if="idDocumentUrl()" class="bg-white p-3 rounded-lg border border-gray-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div 
                                            v-if="!isIdPdf()"
                                            @click="isIdModalOpen = true"
                                            class="w-20 h-14 rounded-lg overflow-hidden border border-gray-300 bg-gray-100 cursor-pointer group relative shrink-0 shadow-xs"
                                            title="Click to view full photo"
                                        >
                                            <img :src="idDocumentUrl()" alt="Guest ID Thumbnail" class="w-full h-full object-cover group-hover:scale-105 transition duration-200" />
                                            <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-[10px] font-bold transition">
                                                Zoom
                                            </div>
                                        </div>
                                        <div v-else class="w-20 h-14 rounded-lg border border-red-200 bg-red-50 text-red-700 flex flex-col items-center justify-center font-bold text-xs shrink-0">
                                            <span>PDF</span>
                                            <span class="text-[9px] font-normal text-red-500">Document</span>
                                        </div>

                                        <div class="space-y-0.5">
                                            <p class="text-xs font-bold text-gray-900">Guest Identification File</p>
                                            <p class="text-[10px] text-gray-500 font-mono break-all">{{ booking.id_document_path || booking.customer?.id_document_path }}</p>
                                            <p class="text-[10px] text-emerald-700 font-semibold">Available in system — No paper copy needed at desk.</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 w-full sm:w-auto">
                                        <button 
                                            v-if="!isIdPdf()"
                                            type="button" 
                                            @click="isIdModalOpen = true"
                                            class="flex-1 sm:flex-none px-3 py-1.5 bg-emerald-800 hover:bg-emerald-900 text-white text-xs font-bold rounded-lg transition flex items-center justify-center gap-1 cursor-pointer shadow-xs"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <span>Inspect ID</span>
                                        </button>
                                        <a 
                                            :href="idDocumentUrl()" 
                                            target="_blank" 
                                            download
                                            class="flex-1 sm:flex-none px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-bold rounded-lg transition flex items-center justify-center gap-1 cursor-pointer border border-gray-300"
                                        >
                                            <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                            <span>Download</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Stay details / Charges -->
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 space-y-4">
                            <h3 class="font-bold text-gray-800 border-b pb-2">Stay Charges & Items</h3>
                            
                            <table class="w-full text-left text-xs text-gray-600 font-mono">
                                <thead>
                                    <tr class="text-gray-400 uppercase font-semibold border-b">
                                        <th class="py-2">Item Description</th>
                                        <th class="py-2 text-center">Qty / Nights</th>
                                        <th class="py-2 text-right">Rate Snapshot</th>
                                        <th class="py-2 text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in booking.items" :key="item.id" class="border-b">
                                        <td class="py-3 font-semibold text-gray-800">{{ item.description_snapshot }}</td>
                                        <td class="py-3 text-center font-bold">{{ item.quantity }}</td>
                                        <td class="py-3 text-right">{{ formatCurrency(item.unit_price_snapshot) }}</td>
                                        <td class="py-3 text-right font-bold">{{ formatCurrency(item.total) }}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="flex justify-end pt-4">
                                <div class="w-64 space-y-2 text-xs font-mono text-gray-700">
                                    <div class="flex justify-between">
                                        <span>Subtotal:</span>
                                        <span>{{ formatCurrency(booking.subtotal) }}</span>
                                    </div>
                                    <div class="flex justify-between text-red-600" v-if="booking.discount > 0">
                                        <span>Discount:</span>
                                        <span>-{{ formatCurrency(booking.discount) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>VAT (18%):</span>
                                        <span>{{ formatCurrency(booking.tax) }}</span>
                                    </div>
                                    <div class="flex justify-between font-bold text-sm text-gray-900 border-t pt-2">
                                        <span>Grand Total:</span>
                                        <span>{{ formatCurrency(booking.total) }}</span>
                                    </div>
                                    <div class="flex justify-between text-emerald-700 font-bold border-b pb-2">
                                        <span>Amount Paid:</span>
                                        <span>{{ formatCurrency(booking.amount_paid) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm font-extrabold text-red-600 pt-1">
                                        <span>Balance Due:</span>
                                        <span>{{ formatCurrency(booking.balance) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- CONTROL SIDEBAR -->
                    <div class="space-y-6">
                        
                        <!-- Reservation Status controller -->
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 space-y-4">
                            <h3 class="font-bold text-gray-800 border-b pb-2">Lifecycle Management</h3>
                            
                            <div class="space-y-1">
                                <span class="text-[10px] text-gray-400 uppercase font-bold">Current Status</span>
                                <div class="capitalize text-lg font-extrabold text-gray-800">
                                    {{ booking.status }}
                                </div>
                            </div>

                            <form @submit.prevent="updateStatus" class="space-y-3">
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase">Change Status To</label>
                                    <select v-model="statusForm.status" class="w-full text-xs rounded border-gray-300 mt-1">
                                        <option v-for="s in statuses" :key="s" :value="s" class="capitalize">{{ s }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase">Internal Comment / Notes</label>
                                    <textarea v-model="statusForm.notes" rows="3" placeholder="Reason for change..." class="w-full text-xs rounded border-gray-300 mt-1"></textarea>
                                </div>
                                <button type="submit" class="w-full py-2 bg-emerald-600 text-white font-bold text-xs rounded hover:bg-emerald-700 shadow-xs transition">
                                    Update Status
                                </button>
                            </form>
                        </div>

                        <!-- Status History / Timeline -->
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 space-y-4">
                            <h3 class="font-bold text-gray-800 border-b pb-2">Activity timeline</h3>
                            
                            <div class="space-y-4 max-h-60 overflow-y-auto pr-1">
                                <div v-for="h in booking.status_history" :key="h.id" class="border-l-2 border-emerald-500 pl-4 relative text-xs space-y-1">
                                    <div class="absolute -left-1.5 top-1 w-2.5 h-2.5 bg-emerald-600 rounded-full border border-white"></div>
                                    <div class="flex justify-between text-gray-400 font-mono text-[10px]">
                                        <span>{{ new Date(h.created_at).toLocaleDateString() }}</span>
                                        <span>{{ h.user?.name || 'System' }}</span>
                                    </div>
                                    <p class="font-semibold text-gray-800">
                                        Status changed: <span class="capitalize text-gray-500">{{ h.from_status }}</span> ➔ <span class="capitalize text-emerald-800 font-bold">{{ h.to_status }}</span>
                                    </p>
                                    <p class="text-gray-500 italic text-[11px]" v-if="h.notes">{{ h.notes }}</p>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <!-- ADD PAYMENT MODAL (SIMPLE INLINE POPUP) -->
                <div v-if="isPaymentModalOpen" class="fixed inset-0 bg-gray-900/50 flex items-center justify-center z-50 p-4">
                    <div class="bg-white p-6 rounded-lg shadow-lg border w-full max-w-sm space-y-4">
                        <h3 class="font-bold text-gray-800 border-b pb-2">Record Transaction Payment</h3>
                        
                        <form @submit.prevent="submitPayment" class="space-y-4">
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Payment Method</label>
                                <select v-model="paymentForm.method" class="w-full text-xs rounded border-gray-300 mt-1">
                                    <option v-for="m in payment_methods" :key="m" :value="m" class="capitalize">{{ m }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Amount (TZS)</label>
                                <input type="number" v-model.number="paymentForm.amount" class="w-full text-xs rounded border-gray-300 mt-1" min="0.01" step="any" placeholder="e.g. 100000" required>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Transaction Reference / Code</label>
                                <input type="text" v-model="paymentForm.reference" class="w-full text-xs rounded border-gray-300 mt-1" placeholder="e.g. MPESA transaction ID">
                            </div>
                            <div class="flex space-x-2 pt-2">
                                <button type="submit" class="flex-1 py-2 bg-emerald-600 text-white text-xs font-bold rounded hover:bg-emerald-700 transition">Save Payment</button>
                                <button type="button" @click="isPaymentModalOpen = false" class="flex-1 py-2 bg-gray-100 text-gray-700 text-xs font-bold rounded hover:bg-gray-200 transition">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- DIGITAL ID FULLSCREEN LIGHTBOX MODAL -->
                <div v-if="isIdModalOpen" class="fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="isIdModalOpen = false">
                    <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-3xl overflow-hidden flex flex-col max-h-[90vh]">
                        <div class="p-4 bg-gray-900 text-white flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                </svg>
                                <div>
                                    <h3 class="text-xs font-bold uppercase tracking-wider">{{ booking.customer?.name }} — Guest ID Verification</h3>
                                    <p class="text-[10px] text-gray-400">{{ idTypeLabels[booking.id_type || booking.customer?.id_type] || 'Official Document' }} | No: {{ booking.id_number || booking.customer?.id_number || 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a :href="idDocumentUrl()" target="_blank" download class="px-3 py-1.5 bg-emerald-700 hover:bg-emerald-600 text-white text-xs font-bold rounded-lg transition">
                                    Download File
                                </a>
                                <button type="button" @click="isIdModalOpen = false" class="p-1.5 text-gray-400 hover:text-white rounded-lg transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="p-4 bg-gray-100 flex-1 overflow-auto flex items-center justify-center min-h-[300px]">
                            <img :src="idDocumentUrl()" alt="Full ID Document" class="max-w-full max-h-[70vh] object-contain rounded-lg shadow-md" />
                        </div>

                        <div class="p-3 bg-white border-t border-gray-200 flex items-center justify-between text-xs text-gray-500">
                            <span>Kitonga Farm Villas Secure Guest ID Record</span>
                            <button type="button" @click="isIdModalOpen = false" class="px-4 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg transition">
                                Close Preview
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
