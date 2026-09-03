<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    payments: Object,
    filters: Object,
});

const filterForm = useForm({
    method: props.filters.method || '',
});

const refundForm = useForm({
    amount: '',
    reason: '',
});

const activeRefundPayment = ref(null);

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-TZ', { style: 'currency', currency: 'TZS', maximumFractionDigits: 0 }).format(val);
};

const openRefundModal = (payment) => {
    activeRefundPayment.value = payment;
    refundForm.amount = payment.amount;
    refundForm.reason = '';
};

const closeRefundModal = () => {
    activeRefundPayment.value = null;
};

const submitRefund = () => {
    refundForm.post(route('admin.payments.refund', activeRefundPayment.value.id), {
        onSuccess: () => {
            closeRefundModal();
            alert('Refund processed successfully.');
        },
        onError: (err) => {
            alert(Object.values(err).join('\n'));
        }
    });
};

const applyFilters = () => {
    filterForm.get(route('admin.payments.index'));
};

const clearFilters = () => {
    filterForm.method = '';
    filterForm.get(route('admin.payments.index'));
};
</script>

<template>
    <Head title="Payments Registry" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Transactional Payments Registry
                </h2>
                <Link :href="route('admin.financials.report')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded shadow-xs transition">
                    View Financial Statement
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <!-- FILTERS -->
                <div class="bg-white p-6 rounded-lg border border-gray-100 shadow-sm space-y-4">
                    <h3 class="font-bold text-gray-800 text-xs uppercase tracking-wider">Search & Filter</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <select v-model="filterForm.method" class="w-full text-xs rounded border-gray-300 mt-1">
                                <option value="">All Methods</option>
                                <option value="cash">Cash</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="card">Credit/Debit Card</option>
                            </select>
                        </div>
                        <div class="flex items-end space-x-2">
                            <button @click="applyFilters" class="flex-1 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded transition">Apply</button>
                            <button @click="clearFilters" class="flex-1 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded transition">Clear</button>
                        </div>
                    </div>
                </div>

                <!-- TABLE GRID -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead>
                                <tr class="bg-gray-100/50 border-b text-xs text-gray-400 uppercase font-bold tracking-wider">
                                    <th class="p-4">Paid At</th>
                                    <th class="p-4">Reference</th>
                                    <th class="p-4">Customer Name</th>
                                    <th class="p-4">Method</th>
                                    <th class="p-4">Amount Paid</th>
                                    <th class="p-4">Status</th>
                                    <th class="p-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="pay in payments.data" :key="pay.id" class="border-b hover:bg-gray-50/50">
                                    <td class="p-4 font-mono text-xs text-gray-900">{{ new Date(pay.paid_at).toLocaleString() }}</td>
                                    <td class="p-4">
                                        <span v-if="pay.booking" class="text-xs font-semibold text-gray-900 font-mono">BOOKING: {{ pay.booking.reference }}</span>
                                        <span v-else-if="pay.sale" class="text-xs font-semibold text-gray-900 font-mono">SALE: {{ pay.sale.reference }}</span>
                                        <span v-else class="text-xs text-gray-400 font-mono">N/A</span>
                                    </td>
                                    <td class="p-4 text-xs font-medium text-gray-700">
                                        {{ pay.booking ? pay.booking.customer.name : (pay.sale ? (pay.sale.customer ? pay.sale.customer.name : 'Walk-in') : 'N/A') }}
                                    </td>
                                    <td class="p-4 text-xs font-semibold uppercase text-gray-500">{{ pay.method }}</td>
                                    <td class="p-4 font-mono font-bold text-gray-900 text-xs">{{ formatCurrency(pay.amount) }}</td>
                                    <td class="p-4 text-xs">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase"
                                            :class="{
                                                'bg-green-100 text-green-800': pay.status === 'completed',
                                                'bg-red-100 text-red-800': pay.status === 'refunded',
                                                'bg-yellow-100 text-yellow-800': pay.status === 'partially_refunded',
                                            }">
                                            {{ pay.status }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <button v-if="pay.status === 'completed'" @click="openRefundModal(pay)" class="px-2 py-1 bg-red-650 hover:bg-red-700 text-white text-[10px] font-bold rounded shadow-xs transition">
                                            Refund / Void
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="payments.data.length === 0">
                                    <td colspan="7" class="p-8 text-center text-gray-400 italic">No payments logged.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- PAGINATION -->
                    <div v-if="payments.links.length > 3" class="p-4 border-t flex justify-center space-x-1">
                        <Link v-for="(link, k) in payments.links" :key="k" :href="link.url || '#'" 
                            class="px-3 py-1 rounded text-xs transition" 
                            :class="{
                                'bg-emerald-600 text-white font-bold': link.active,
                                'bg-gray-100 hover:bg-gray-200 text-gray-600': !link.active && link.url,
                                'text-gray-300 cursor-not-allowed': !link.url
                            }"
                            v-html="link.label">
                        </Link>
                    </div>
                </div>

                <!-- REFUND MODAL -->
                <div v-if="activeRefundPayment" class="fixed inset-0 bg-gray-900/50 flex items-center justify-center z-50 p-4">
                    <div class="bg-white p-6 rounded-lg shadow-lg border w-full max-w-sm space-y-4">
                        <h3 class="font-bold text-gray-800 border-b pb-2">Process Refund</h3>
                        <p class="text-xs text-gray-500">Refund limit: {{ formatCurrency(activeRefundPayment.amount) }}</p>
                        
                        <form @submit.prevent="submitRefund" class="space-y-4">
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Refund Amount (TZS)</label>
                                <input v-model="refundForm.amount" type="number" required min="0.01" :max="activeRefundPayment.amount" class="w-full text-xs rounded border-gray-300 mt-1" />
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Reason for Refund</label>
                                <input v-model="refundForm.reason" type="text" required placeholder="e.g. Booking cancellation, customer return" class="w-full text-xs rounded border-gray-300 mt-1" />
                            </div>
                            <div class="flex space-x-2 pt-2">
                                <button type="submit" class="flex-1 py-2 bg-red-650 text-white text-xs font-bold rounded hover:bg-red-700 transition">Confirm Refund</button>
                                <button type="button" @click="closeRefundModal" class="flex-1 py-2 bg-gray-100 text-gray-700 text-xs font-bold rounded hover:bg-gray-200 transition">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
