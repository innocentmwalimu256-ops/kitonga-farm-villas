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
                        
                        <!-- Guest Details -->
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 space-y-4">
                            <h3 class="font-bold text-gray-800 border-b pb-2">Guest Profile</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-xs text-gray-400 block uppercase font-semibold">Full Name</span>
                                    <span class="font-bold text-gray-800">{{ booking.customer.name }}</span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-400 block uppercase font-semibold">Phone Number</span>
                                    <span class="font-mono text-gray-800 font-semibold">{{ booking.customer.phone || 'N/A' }}</span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-400 block uppercase font-semibold">Email Address</span>
                                    <span class="font-mono text-gray-800">{{ booking.customer.email || 'N/A' }}</span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-400 block uppercase font-semibold">Booking Source</span>
                                    <span class="capitalize bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-xs font-semibold">{{ booking.source }}</span>
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

            </div>
        </div>
    </AuthenticatedLayout>
</template>
