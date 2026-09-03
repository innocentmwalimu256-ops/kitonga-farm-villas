<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    sale: Object,
});

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-TZ', { style: 'currency', currency: 'TZS', maximumFractionDigits: 0 }).format(val);
};

const printReceipt = () => {
    window.print();
};
</script>

<template>
    <Head title="Sale Receipt" />

    <div class="bg-gray-100 min-h-screen py-8 print:bg-white print:py-0">
        <div class="max-w-md mx-auto bg-white p-6 shadow rounded border print:border-none print:shadow-none">
            
            <!-- Receipt Header -->
            <div class="text-center border-b pb-4 mb-4">
                <h1 class="font-extrabold text-lg text-emerald-800 uppercase tracking-widest">Kitonga Farm Villas</h1>
                <p class="text-[11px] text-gray-500">Komkonga Village, Tanga Region</p>
                <p class="text-[11px] text-gray-500">kitongafarmvillas@gmail.com</p>
                <p class="text-xs font-semibold text-gray-700 mt-2">OFFICIAL POS RECEIPT</p>
            </div>

            <!-- Receipt Info -->
            <div class="space-y-1 text-xs text-gray-600 mb-4 font-mono">
                <div class="flex justify-between">
                    <span>Reference:</span>
                    <span class="font-semibold text-gray-900">{{ sale.reference }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Date:</span>
                    <span>{{ new Date(sale.created_at).toLocaleString() }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Customer:</span>
                    <span>{{ sale.customer?.name || 'Walk-in Guest' }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Cashier:</span>
                    <span>{{ sale.creator?.name || 'Admin' }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Status:</span>
                    <span class="uppercase font-semibold" :class="sale.status === 'completed' ? 'text-emerald-700' : 'text-red-600'">{{ sale.status }}</span>
                </div>
            </div>

            <!-- Items list -->
            <table class="w-full text-left text-xs border-t border-b py-2 mb-4 font-mono">
                <thead>
                    <tr class="text-gray-400 font-semibold uppercase">
                        <th class="py-1">Item</th>
                        <th class="py-1 text-center">Qty</th>
                        <th class="py-1 text-right">Price</th>
                        <th class="py-1 text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in sale.items" :key="item.id" class="border-b border-dashed">
                        <td class="py-2">{{ item.product?.name || item.description_snapshot }}</td>
                        <td class="py-2 text-center">{{ item.quantity }}</td>
                        <td class="py-2 text-right">{{ formatCurrency(item.unit_price) }}</td>
                        <td class="py-2 text-right">{{ formatCurrency(item.total) }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Financial summary -->
            <div class="space-y-1.5 text-xs text-gray-700 font-mono pr-1">
                <div class="flex justify-between">
                    <span>Subtotal:</span>
                    <span>{{ formatCurrency(sale.subtotal) }}</span>
                </div>
                <div v-if="sale.discount > 0" class="flex justify-between text-red-600">
                    <span>Discount:</span>
                    <span>-{{ formatCurrency(sale.discount) }}</span>
                </div>
                <div class="flex justify-between font-bold text-sm text-gray-900 border-t pt-2">
                    <span>Grand Total:</span>
                    <span>{{ formatCurrency(sale.total) }}</span>
                </div>
                <div v-for="payment in sale.payments" :key="payment.id" class="flex justify-between text-gray-500 text-[11px] pt-1">
                    <span>Paid via {{ payment.method }} (Ref: {{ payment.reference || 'N/A' }}):</span>
                    <span>{{ formatCurrency(payment.amount) }}</span>
                </div>
            </div>

            <!-- Footer Message -->
            <div class="text-center text-[10px] text-gray-400 font-mono border-t pt-4 mt-6">
                <p>Thank you for visiting Kitonga Farm Villas!</p>
                <p>Where luxury meets farm life.</p>
            </div>

            <!-- Print Actions -->
            <div class="mt-8 flex justify-center space-x-3 print:hidden">
                <button @click="printReceipt" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded shadow transition">
                    Print Receipt
                </button>
                <Link :href="route('admin.pos.terminal')" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-bold rounded shadow transition">
                    Back to POS
                </Link>
            </div>

        </div>
    </div>
</template>
