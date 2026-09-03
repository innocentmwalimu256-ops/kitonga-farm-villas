<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    metrics: Object,
    filters: Object,
});

const filterForm = useForm({
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
});

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-TZ', { style: 'currency', currency: 'TZS', maximumFractionDigits: 0 }).format(val);
};

const applyFilters = () => {
    filterForm.get(route('admin.financials.report'));
};
</script>

<template>
    <Head title="Financial Report" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Detailed Financial Statement
                </h2>
                
                <!-- DATE FILTERS -->
                <form @submit.prevent="applyFilters" class="flex items-center space-x-2 bg-white p-1.5 rounded border shadow-xs text-xs">
                    <input v-model="filterForm.start_date" type="date" class="rounded border-gray-300 p-1 text-xs" required />
                    <span class="text-gray-400">to</span>
                    <input v-model="filterForm.end_date" type="date" class="rounded border-gray-300 p-1 text-xs" required />
                    <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded text-xs transition">Apply</button>
                </form>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8 space-y-6">

                <!-- FINANCIAL AUDIT SHEETS -->
                <div class="bg-white p-8 rounded-lg shadow-sm border border-gray-100 space-y-6">
                    <div class="text-center border-b pb-4 mb-4">
                        <h3 class="font-extrabold text-lg text-emerald-800 uppercase tracking-widest">Kitonga Farm Villas</h3>
                        <p class="text-xs text-gray-400 mt-1">Operational Financial Statement</p>
                        <p class="text-[10px] text-gray-400 font-mono">Date Interval: {{ filters.start_date }} to {{ filters.end_date }}</p>
                    </div>

                    <!-- METRICS SHEET -->
                    <div class="space-y-4">
                        <div class="flex justify-between border-b pb-2 text-xs">
                            <span class="font-bold text-gray-500 uppercase tracking-wider">Financial Line Item</span>
                            <span class="font-bold text-gray-500 uppercase tracking-wider">Amount (TZS)</span>
                        </div>

                        <div class="flex justify-between text-sm py-1">
                            <span class="text-gray-700">Gross Operational Revenue</span>
                            <span class="font-mono font-bold text-gray-900">{{ formatCurrency(metrics.gross_revenue) }}</span>
                        </div>

                        <div class="flex justify-between text-sm py-1 text-red-650 border-b pb-2.5">
                            <span class="text-gray-700">Less: Refunds Processed</span>
                            <span class="font-mono font-bold">-{{ formatCurrency(metrics.refunds) }}</span>
                        </div>

                        <div class="flex justify-between text-sm font-bold py-2 border-b">
                            <span class="text-gray-950 font-bold">Net Operational Revenue</span>
                            <span class="font-mono text-emerald-700">{{ formatCurrency(metrics.net_revenue) }}</span>
                        </div>

                        <div class="flex justify-between text-sm py-1 text-red-600 border-b pb-2.5">
                            <span class="text-gray-700">Less: Operating Expenses (Approved)</span>
                            <span class="font-mono font-bold">-{{ formatCurrency(metrics.operating_expenses) }}</span>
                        </div>

                        <div class="flex justify-between text-base font-extrabold py-3 border-b-2 border-emerald-800">
                            <span class="text-gray-950 uppercase tracking-wide">Operational Net Profit Estimate</span>
                            <span class="font-mono text-emerald-800">{{ formatCurrency(metrics.operational_net_profit) }}</span>
                        </div>
                    </div>

                    <!-- BALANCE SHEET DETAILS -->
                    <div class="bg-gray-50 p-4 rounded border space-y-3">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Balances Ledger Summary</h4>
                        <div class="grid grid-cols-2 gap-4 text-xs font-mono">
                            <div class="p-3 bg-white border rounded">
                                <span class="text-[9px] text-gray-400 uppercase font-bold block">Cash Collected</span>
                                <span class="text-sm font-bold text-gray-800 mt-1 block">{{ formatCurrency(metrics.cash_collected) }}</span>
                            </div>
                            <div class="p-3 bg-white border rounded">
                                <span class="text-[9px] text-gray-400 uppercase font-bold block">Outstanding Balances</span>
                                <span class="text-sm font-bold text-red-600 mt-1 block">{{ formatCurrency(metrics.outstanding_balances) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Accounting warning block -->
                    <div class="text-[10px] text-gray-400 leading-relaxed italic bg-amber-50 border border-amber-200 p-3 rounded">
                        <strong>Accounting Policy Notice:</strong> This statement presents an <em>Operational Net Profit Estimate</em> based on raw bookings and POS checkout transactions. This figure does not represent formal audited accounting profit.
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
