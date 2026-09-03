<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    sales: Object,
    filters: Object,
});

const filterForm = useForm({
    category: props.filters.category || '',
    status: props.filters.status || '',
});

const refundForm = useForm({
    reason: '',
});

const activeRefundSale = ref(null);

const openRefundModal = (sale) => {
    activeRefundSale.value = sale;
    refundForm.reason = '';
};

const closeRefundModal = () => {
    activeRefundSale.value = null;
};

const submitRefund = () => {
    refundForm.post(route('admin.pos.cancel', activeRefundSale.value.id), {
        onSuccess: () => {
            closeRefundModal();
            alert('Sale cancelled and inventory restored.');
        }
    });
};

const applyFilters = () => {
    filterForm.get(route('admin.pos.index'));
};

const clearFilters = () => {
    filterForm.category = '';
    filterForm.status = '';
    filterForm.get(route('admin.pos.index'));
};
</script>

<template>
    <Head title="POS Sales History" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    POS Sales Registry
                </h2>
                <Link :href="route('admin.pos.terminal')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded shadow-xs transition">
                    Open Cashier Terminal
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <!-- FILTERS -->
                <div class="bg-white p-6 rounded-lg border border-gray-100 shadow-sm space-y-4">
                    <h3 class="font-bold text-gray-800 text-xs uppercase tracking-wider">Filter Sales</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Category</label>
                            <select v-model="filterForm.category" class="w-full text-xs rounded border-gray-300 mt-1">
                                <option value="">All Categories</option>
                                <option value="bar">Bar & Cafe</option>
                                <option value="product">Farm Produce</option>
                                <option value="tour">Tours & Experiences</option>
                                <option value="other">Other Services</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Status</label>
                            <select v-model="filterForm.status" class="w-full text-xs rounded border-gray-300 mt-1">
                                <option value="">All Statuses</option>
                                <option value="paid">Paid & Completed</option>
                                <option value="cancelled">Refunded / Cancelled</option>
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
                                    <th class="p-4">Reference ID</th>
                                    <th class="p-4">Customer</th>
                                    <th class="p-4">Category</th>
                                    <th class="p-4">Total Amount</th>
                                    <th class="p-4">Date</th>
                                    <th class="p-4">Status</th>
                                    <th class="p-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="sale in sales.data" :key="sale.id" class="border-b hover:bg-gray-50/50">
                                    <td class="p-4 font-mono text-xs text-gray-900 font-bold">SALE-{{ String(sale.id).padStart(5, '0') }}</td>
                                    <td class="p-4">
                                        <div class="text-xs font-bold text-gray-900">{{ sale.customer ? sale.customer.name : sale.customer_name }}</div>
                                        <div class="text-[9px] text-gray-400 font-mono">{{ sale.customer ? sale.customer.phone : (sale.customer_phone || 'Walk-in') }}</div>
                                    </td>
                                    <td class="p-4 uppercase text-xs font-semibold">
                                        <span class="px-2 py-0.5 rounded-full text-[9px]"
                                            :class="{
                                                'bg-indigo-100 text-indigo-800': sale.category === 'bar',
                                                'bg-yellow-100 text-yellow-800': sale.category === 'product',
                                                'bg-emerald-100 text-emerald-800': sale.category === 'tour',
                                            }">
                                            {{ sale.category }}
                                        </span>
                                    </td>
                                    <td class="p-4 font-mono font-bold text-gray-900 text-xs">TZS {{ Number(sale.total).toLocaleString() }}</td>
                                    <td class="p-4 text-xs font-mono">{{ new Date(sale.created_at).toLocaleString() }}</td>
                                    <td class="p-4 text-xs">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase"
                                            :class="{
                                                'bg-green-100 text-green-800': sale.status === 'paid',
                                                'bg-red-100 text-red-800': sale.status === 'cancelled',
                                            }">
                                            {{ sale.status }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-right space-x-1">
                                        <Link :href="route('admin.pos.receipt', { id: sale.id })" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-[10px] font-bold rounded shadow-xs transition inline-block">
                                            View Receipt
                                        </Link>
                                        <button v-if="sale.status === 'paid'" @click="openRefundModal(sale)" class="px-2 py-1 bg-red-600 hover:bg-red-700 text-white text-[10px] font-bold rounded shadow-xs transition">
                                            Refund / Cancel
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="sales.data.length === 0">
                                    <td colspan="7" class="p-8 text-center text-gray-400 italic">No sales transactions found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- PAGINATION -->
                    <div v-if="sales.links.length > 3" class="p-4 border-t flex justify-center space-x-1">
                        <Link v-for="(link, k) in sales.links" :key="k" :href="link.url || '#'" 
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

                <!-- REFUND DIALOG -->
                <div v-if="activeRefundSale" class="fixed inset-0 bg-gray-900/50 flex items-center justify-center z-50 p-4">
                    <div class="bg-white p-6 rounded-lg shadow-lg border w-full max-w-sm space-y-4">
                        <h3 class="font-bold text-gray-800 border-b pb-2">Cancel Transaction SALE-{{ String(activeRefundSale.id).padStart(5, '0') }}</h3>
                        <p class="text-xs text-red-600 font-semibold">Warning: This will void the payment and restore all items back to stock inventory.</p>
                        
                        <form @submit.prevent="submitRefund" class="space-y-4">
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Reason for Cancellation</label>
                                <input v-model="refundForm.reason" type="text" required placeholder="e.g. Spoilage, Customer changed mind" class="w-full text-xs rounded border-gray-300 mt-1" />
                            </div>
                            <div class="flex space-x-2 pt-2">
                                <button type="submit" class="flex-1 py-2 bg-red-600 text-white text-xs font-bold rounded hover:bg-red-700 transition">Confirm Void</button>
                                <button type="button" @click="closeRefundModal" class="flex-1 py-2 bg-gray-100 text-gray-700 text-xs font-bold rounded hover:bg-gray-200 transition">Close</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
