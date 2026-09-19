<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    bookingsCount: Number,
    expensesCount: Number,
    totalRevenue: Number,
    totalExpenses: Number,
    netProfit: Number,
});

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-TZ', { style: 'currency', currency: 'TZS', maximumFractionDigits: 0 }).format(val);
};
</script>

<template>
    <Head title="Export Financial Reports" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Business Analytics & Financial Exports
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <!-- SUMMARY CARD METRICS -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Total Lifetime Revenue</span>
                        <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ formatCurrency(totalRevenue) }}</h3>
                        <p class="text-[10px] text-gray-400 mt-2">Includes bookings and cashier terminals</p>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Total Approved Expenses</span>
                        <h3 class="text-2xl font-bold text-red-600 mt-1">{{ formatCurrency(totalExpenses) }}</h3>
                        <p class="text-[10px] text-gray-400 mt-2">Approved and verified payouts</p>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Estimated Net Profit</span>
                        <h3 class="text-2xl font-bold mt-1" :class="netProfit >= 0 ? 'text-emerald-600' : 'text-red-600'">
                            {{ formatCurrency(netProfit) }}
                        </h3>
                        <p class="text-[10px] text-gray-400 mt-2">Gross revenues minus expenses</p>
                    </div>
                </div>

                <!-- EXPORTS SECTION -->
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 space-y-6">
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider">Reports Center</h3>
                        <p class="text-xs text-gray-400 mt-1">Download production spreadsheets and PDF files for auditing and bookkeeping.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Excel Bookings -->
                        <div class="p-6 border rounded-lg space-y-4 hover:border-emerald-500 transition text-center flex flex-col justify-between">
                            <div>
                                <div class="w-12 h-12 bg-emerald-50 text-emerald-700 rounded-full flex items-center justify-center mx-auto">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <h4 class="font-bold text-gray-900 text-xs uppercase tracking-wider mt-3">Bookings Registry (Excel)</h4>
                                <p class="text-[10px] text-gray-400 mt-1">Full reservation list containing customers details, room unit tags, dates, balances, and status flags.</p>
                            </div>
                            <a :href="route('admin.reports.excel.bookings')" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded transition block text-center mt-4">
                                Download Excel Sheet
                            </a>
                        </div>

                        <!-- Excel Expenses -->
                        <div class="p-6 border rounded-lg space-y-4 hover:border-emerald-500 transition text-center flex flex-col justify-between">
                            <div>
                                <div class="w-12 h-12 bg-emerald-50 text-emerald-700 rounded-full flex items-center justify-center mx-auto">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h4 class="font-bold text-gray-900 text-xs uppercase tracking-wider mt-3">Expenses Logs (Excel)</h4>
                                <p class="text-[10px] text-gray-400 mt-1">Ledger list of all payouts, recipients, categories, slip amounts, and approval states.</p>
                            </div>
                            <a :href="route('admin.reports.excel.expenses')" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded transition block text-center mt-4">
                                Download Excel Sheet
                            </a>
                        </div>

                        <!-- PDF Audit Report -->
                        <div class="p-6 border rounded-lg space-y-4 hover:border-emerald-500 transition text-center flex flex-col justify-between">
                            <div>
                                <div class="w-12 h-12 bg-red-50 text-red-650 rounded-full flex items-center justify-center mx-auto">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h4 class="font-bold text-gray-900 text-xs uppercase tracking-wider mt-3">Executive Summary (PDF)</h4>
                                <p class="text-[10px] text-gray-400 mt-1">Streamlined executive PDF summary highlighting accommodation vs POS split revenue, profit margins, and audit counters.</p>
                            </div>
                            <a :href="route('admin.reports.pdf')" class="w-full py-2 bg-red-650 hover:bg-red-700 text-white font-bold text-xs rounded transition block text-center mt-4">
                                Download PDF Document
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
