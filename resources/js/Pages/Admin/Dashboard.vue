<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    kpis: Object,
    charts: Object,
    recent_bookings: Array,
    filters: Object,
});

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-TZ', { style: 'currency', currency: 'TZS', maximumFractionDigits: 0 }).format(val);
};

const customDateForm = useForm({
    filter: 'custom',
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
});

const showCustomRange = ref(props.filters.active === 'custom');

const applyCustomDates = () => {
    customDateForm.get(route('admin.dashboard'));
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
                <div>
                    <h2 class="text-lg sm:text-xl font-bold leading-tight text-gray-900">
                        Kitonga Farm Villas Command Center
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Real-time hospitality metrics, occupancy, and estate financials</p>
                </div>
                
                <!-- DATE RANGE FILTERS -->
                <div class="flex items-center overflow-x-auto pb-1 sm:pb-0 gap-1 bg-white p-1 rounded-lg border border-gray-200 shadow-2xs max-w-full">
                    <Link :href="route('admin.dashboard', { filter: 'today' })" class="px-2.5 py-1 text-xs rounded-md transition shrink-0" :class="filters.active === 'today' ? 'bg-emerald-700 text-white font-bold' : 'text-gray-600 hover:bg-gray-50'">Today</Link>
                    <Link :href="route('admin.dashboard', { filter: 'yesterday' })" class="px-2.5 py-1 text-xs rounded-md transition shrink-0" :class="filters.active === 'yesterday' ? 'bg-emerald-700 text-white font-bold' : 'text-gray-600 hover:bg-gray-50'">Yesterday</Link>
                    <Link :href="route('admin.dashboard', { filter: 'last_7' })" class="px-2.5 py-1 text-xs rounded-md transition shrink-0" :class="filters.active === 'last_7' ? 'bg-emerald-700 text-white font-bold' : 'text-gray-600 hover:bg-gray-50'">7 Days</Link>
                    <Link :href="route('admin.dashboard', { filter: 'last_30' })" class="px-2.5 py-1 text-xs rounded-md transition shrink-0" :class="filters.active === 'last_30' ? 'bg-emerald-700 text-white font-bold' : 'text-gray-600 hover:bg-gray-50'">30 Days</Link>
                    <Link :href="route('admin.dashboard', { filter: 'this_month' })" class="px-2.5 py-1 text-xs rounded-md transition shrink-0" :class="filters.active === 'this_month' ? 'bg-emerald-700 text-white font-bold' : 'text-gray-600 hover:bg-gray-50'">This Month</Link>
                    <Link :href="route('admin.dashboard', { filter: 'last_month' })" class="px-2.5 py-1 text-xs rounded-md transition shrink-0" :class="filters.active === 'last_month' ? 'bg-emerald-700 text-white font-bold' : 'text-gray-600 hover:bg-gray-50'">Last Month</Link>
                    <Link :href="route('admin.dashboard', { filter: 'this_year' })" class="px-2.5 py-1 text-xs rounded-md transition shrink-0" :class="filters.active === 'this_year' ? 'bg-emerald-700 text-white font-bold' : 'text-gray-600 hover:bg-gray-50'">This Year</Link>
                    <Link :href="route('admin.dashboard', { filter: 'last_year' })" class="px-2.5 py-1 text-xs rounded-md transition shrink-0" :class="filters.active === 'last_year' ? 'bg-emerald-700 text-white font-bold' : 'text-gray-600 hover:bg-gray-50'">Last Year</Link>
                    <button @click="showCustomRange = !showCustomRange" class="px-2.5 py-1 text-xs rounded-md transition text-gray-600 hover:bg-gray-50 font-medium shrink-0">Custom...</button>
                </div>
            </div>
            
            <!-- CUSTOM RANGE PANEL -->
            <div v-if="showCustomRange" class="mt-4 p-4 bg-white border border-gray-200 rounded-xl shadow-xs flex flex-col sm:flex-row items-stretch sm:items-center gap-3 max-w-lg">
                <div class="flex-1">
                    <label class="text-[10px] font-bold uppercase text-gray-400 block mb-1">Start Date</label>
                    <input v-model="customDateForm.start_date" type="date" class="text-xs rounded-lg border-gray-300 w-full" />
                </div>
                <div class="flex-1">
                    <label class="text-[10px] font-bold uppercase text-gray-400 block mb-1">End Date</label>
                    <input v-model="customDateForm.end_date" type="date" class="text-xs rounded-lg border-gray-300 w-full" />
                </div>
                <button @click="applyCustomDates" class="self-end sm:self-auto px-5 py-2.5 bg-emerald-700 text-white font-bold rounded-lg text-xs hover:bg-emerald-800 transition">Apply</button>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <!-- WHAT IS HAPPENING TODAY (OVERVIEW PANEL) -->
                <div class="bg-gradient-to-r from-emerald-800 to-emerald-950 p-6 rounded-lg text-white shadow-md flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div class="space-y-1">
                        <span class="text-[10px] font-bold tracking-widest text-emerald-300 uppercase">Live Operations: Today</span>
                        <h3 class="text-xl font-bold">Today's Operating Revenue</h3>
                        <p class="text-3xl font-extrabold font-mono text-emerald-200 mt-1">{{ formatCurrency(kpis.today_revenue) }}</p>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 w-full md:w-auto">
                        <div class="bg-white/10 p-3 rounded text-center">
                            <span class="text-[10px] text-emerald-200 font-bold block uppercase">7-Day Rev</span>
                            <span class="font-mono text-sm font-bold">{{ formatCurrency(kpis.seven_days_revenue) }}</span>
                        </div>
                        <div class="bg-white/10 p-3 rounded text-center">
                            <span class="text-[10px] text-emerald-200 font-bold block uppercase">30-Day Rev</span>
                            <span class="font-mono text-sm font-bold">{{ formatCurrency(kpis.thirty_days_revenue) }}</span>
                        </div>
                        <div class="bg-white/10 p-3 rounded text-center">
                            <span class="text-[10px] text-emerald-200 font-bold block uppercase">Monthly Rev</span>
                            <span class="font-mono text-sm font-bold">{{ formatCurrency(kpis.monthly_revenue) }}</span>
                        </div>
                        <Link :href="route('admin.products.index')" class="bg-white/10 hover:bg-white/20 p-3 rounded text-center block transition">
                            <span class="text-[10px] text-emerald-200 font-bold block uppercase">Low Stock</span>
                            <span class="font-mono text-sm font-bold text-red-300">{{ kpis.low_stock_count }} items</span>
                        </Link>
                    </div>
                </div>

                <!-- DRILL-DOWN KPI CARDS -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- TOTAL BOOKINGS -->
                    <Link :href="route('admin.bookings.index')" class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 flex flex-col justify-between hover:border-emerald-500 transition group">
                        <div>
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Bookings count</span>
                                <span class="text-xs text-emerald-600 font-bold group-hover:underline">Drill Down ➔</span>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-800 mt-2">{{ kpis.total_bookings }} reservations</h3>
                        </div>
                        <div class="mt-4 text-xs text-gray-400 border-t pt-2 flex justify-between">
                            <span>Pending approvals: {{ kpis.pending_bookings }}</span>
                        </div>
                    </Link>

                    <!-- TOTAL REVENUE -->
                    <Link :href="route('admin.pos.index')" class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 flex flex-col justify-between hover:border-emerald-500 transition group">
                        <div>
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Filtered Revenue</span>
                                <span class="text-xs text-emerald-600 font-bold group-hover:underline">Sales list ➔</span>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-800 mt-2">{{ formatCurrency(kpis.total_revenue) }}</h3>
                        </div>
                        <div class="mt-4 text-xs text-gray-400 border-t pt-2 flex justify-between">
                            <span>Villas: {{ formatCurrency(kpis.accommodation_revenue) }}</span>
                            <span>POS: {{ formatCurrency(kpis.tour_revenue + kpis.bar_revenue + kpis.product_revenue) }}</span>
                        </div>
                    </Link>

                    <!-- TOTAL EXPENSES -->
                    <Link :href="route('admin.expenses.index')" class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 flex flex-col justify-between hover:border-emerald-500 transition group">
                        <div>
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Expenses</span>
                                <span class="text-xs text-emerald-600 font-bold group-hover:underline">Expenses log ➔</span>
                            </div>
                            <h3 class="text-2xl font-bold text-red-600 mt-2">{{ formatCurrency(kpis.expenses) }}</h3>
                        </div>
                        <div class="mt-4 text-xs text-gray-400 border-t pt-2">
                            <span>Approved operating payouts</span>
                        </div>
                    </Link>

                    <!-- NET PROFIT -->
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 flex flex-col justify-between">
                        <div>
                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Net Profit Estimate</span>
                            <h3 class="text-2xl font-bold mt-2" :class="kpis.net_profit >= 0 ? 'text-emerald-600' : 'text-red-600'">
                                {{ formatCurrency(kpis.net_profit) }}
                            </h3>
                        </div>
                        <div class="mt-4 text-xs text-gray-400 border-t pt-2 flex justify-between">
                            <span>Occupancy: {{ kpis.occupancy_percentage }}%</span>
                            <span class="text-red-500 font-bold" v-if="kpis.outstanding_balance > 0">Unpaid: {{ formatCurrency(kpis.outstanding_balance) }}</span>
                        </div>
                    </div>
                </div>

                <!-- CHARTS ROW (REVENUE TREND & REVENUE BY CATEGORY) -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- REVENUE VS EXPENSES BAR GRAPH -->
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 lg:col-span-2 space-y-4">
                        <div class="flex justify-between items-center">
                            <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider">Daily Financial Performance Trend</h3>
                            <div class="flex items-center space-x-2 text-[10px] font-bold">
                                <span class="flex items-center"><span class="w-2.5 h-2.5 bg-emerald-500 rounded-sm mr-1"></span> Revenue</span>
                                <span class="flex items-center"><span class="w-2.5 h-2.5 bg-red-400 rounded-sm mr-1"></span> Expenses</span>
                            </div>
                        </div>
                        <div class="h-64 flex items-end space-x-2 pt-6 border-b pb-2">
                            <div v-for="(rev, idx) in charts.revenue" :key="idx" class="flex-1 flex flex-col items-center group relative h-full justify-end">
                                <span class="absolute bottom-full mb-1 text-[9px] bg-gray-900 text-white rounded p-2 hidden group-hover:block whitespace-nowrap z-10 font-mono">
                                    Rev: {{ formatCurrency(rev) }}<br>Exp: {{ formatCurrency(charts.expenses[idx]) }}
                                </span>
                                <div class="w-full flex space-x-0.5 items-end justify-center h-full">
                                    <div class="bg-emerald-500 w-full max-w-[8px] rounded-t" :style="{ height: `${Math.min(100, (rev / Math.max(1, ...charts.revenue)) * 100)}%` }"></div>
                                    <div class="bg-red-400 w-full max-w-[8px] rounded-t" :style="{ height: `${Math.min(100, (charts.expenses[idx] / Math.max(1, ...charts.revenue)) * 100)}%` }"></div>
                                </div>
                                <span class="text-[8px] text-gray-400 mt-2 truncate w-full text-center hidden md:block">{{ charts.labels[idx] }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- REVENUE BY CATEGORY & PAYMENT METHODS -->
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 space-y-6">
                        <div>
                            <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider mb-4">Revenue by Source</h3>
                            <div class="space-y-3">
                                <div>
                                    <div class="flex justify-between text-xs font-semibold text-gray-700">
                                        <span>Villa Accommodation</span>
                                        <span>{{ formatCurrency(kpis.accommodation_revenue) }}</span>
                                    </div>
                                    <div class="w-full bg-gray-100 h-1.5 rounded-full mt-1 overflow-hidden">
                                        <div class="bg-emerald-600 h-full" :style="{ width: `${kpis.total_revenue > 0 ? (kpis.accommodation_revenue / kpis.total_revenue) * 100 : 0}%` }"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-xs font-semibold text-gray-700">
                                        <span>Farm Tours</span>
                                        <span>{{ formatCurrency(kpis.tour_revenue) }}</span>
                                    </div>
                                    <div class="w-full bg-gray-100 h-1.5 rounded-full mt-1 overflow-hidden">
                                        <div class="bg-indigo-600 h-full" :style="{ width: `${kpis.total_revenue > 0 ? (kpis.tour_revenue / kpis.total_revenue) * 100 : 0}%` }"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-xs font-semibold text-gray-700">
                                        <span>Bar & Beverage</span>
                                        <span>{{ formatCurrency(kpis.bar_revenue) }}</span>
                                    </div>
                                    <div class="w-full bg-gray-100 h-1.5 rounded-full mt-1 overflow-hidden">
                                        <div class="bg-amber-500 h-full" :style="{ width: `${kpis.total_revenue > 0 ? (kpis.bar_revenue / kpis.total_revenue) * 100 : 0}%` }"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-xs font-semibold text-gray-700">
                                        <span>Products Store</span>
                                        <span>{{ formatCurrency(kpis.product_revenue) }}</span>
                                    </div>
                                    <div class="w-full bg-gray-100 h-1.5 rounded-full mt-1 overflow-hidden">
                                        <div class="bg-sky-500 h-full" :style="{ width: `${kpis.total_revenue > 0 ? (kpis.product_revenue / kpis.total_revenue) * 100 : 0}%` }"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-t pt-4">
                            <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider mb-3">Billing Methods</h3>
                            <div class="flex flex-wrap gap-2">
                                <div v-for="pay in charts.payment_methods" :key="pay.method" class="bg-gray-50 border rounded px-2.5 py-1 text-center flex-1">
                                    <span class="text-[9px] font-bold text-gray-400 uppercase block">{{ pay.method }}</span>
                                    <span class="text-xs font-mono font-bold text-gray-800">{{ formatCurrency(pay.total_amount) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- VILLA PERFORMANCE & TOP PRODUCTS -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- VILLA PERFORMANCE -->
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 space-y-4">
                        <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider">Accommodation Types Efficacy</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="bg-gray-50 border-b text-gray-400 uppercase font-bold tracking-wider">
                                        <th class="p-3">Villa Model</th>
                                        <th class="p-3 text-right">Revenue Generated</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="perf in charts.villa_performance" :key="perf.name" class="border-b hover:bg-gray-50/50">
                                        <td class="p-3 font-semibold text-gray-900">{{ perf.name }}</td>
                                        <td class="p-3 text-right font-mono font-bold text-emerald-600">{{ formatCurrency(perf.total_revenue) }}</td>
                                    </tr>
                                    <tr v-if="charts.villa_performance.length === 0">
                                        <td colspan="2" class="p-6 text-center text-gray-400 italic">No bookings performance logs in this date range.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TOP PRODUCTS -->
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 space-y-4">
                        <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider">Top-Selling Farm/Bar Products</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="bg-gray-50 border-b text-gray-400 uppercase font-bold tracking-wider">
                                        <th class="p-3">Product Name</th>
                                        <th class="p-3 text-right">Quantity Sold</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="prod in charts.top_products" :key="prod.name" class="border-b hover:bg-gray-50/50">
                                        <td class="p-3 font-semibold text-gray-900">{{ prod.name }}</td>
                                        <td class="p-3 text-right font-mono font-bold">{{ prod.total_qty }} units</td>
                                    </tr>
                                    <tr v-if="charts.top_products.length === 0">
                                        <td colspan="2" class="p-6 text-center text-gray-400 italic">No products sales logged.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- QUICK ACTION LINKS -->
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 space-y-4">
                    <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider">Quick Actions & Managers</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                        <Link :href="route('admin.pos.terminal')" class="p-4 border rounded-xl hover:border-emerald-500 hover:bg-emerald-50/30 transition flex items-center space-x-3 bg-white">
                            <div class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-xs">POS</div>
                            <div>
                                <h4 class="text-xs font-bold text-gray-900">Cashier Register</h4>
                                <p class="text-[10px] text-gray-400">POS checkout cart</p>
                            </div>
                        </Link>
                        <Link :href="route('admin.bookings.calendar')" class="p-4 border rounded-xl hover:border-emerald-500 hover:bg-emerald-50/30 transition flex items-center space-x-3 bg-white">
                            <div class="w-9 h-9 rounded-lg bg-indigo-100 text-indigo-800 flex items-center justify-center font-bold text-xs">CAL</div>
                            <div>
                                <h4 class="text-xs font-bold text-gray-900">Bookings Calendar</h4>
                                <p class="text-[10px] text-gray-400">Monthly schedule grid</p>
                            </div>
                        </Link>
                        <Link :href="route('admin.accommodation.index')" class="p-4 border rounded-xl hover:border-emerald-500 hover:bg-emerald-50/30 transition flex items-center space-x-3 bg-white">
                            <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-xs">VIL</div>
                            <div>
                                <h4 class="text-xs font-bold text-gray-900">Accommodation</h4>
                                <p class="text-[10px] text-gray-400">Villas & Room setup</p>
                            </div>
                        </Link>
                        <Link :href="route('admin.housekeeping.index')" class="p-4 border rounded-xl hover:border-emerald-500 hover:bg-emerald-50/30 transition flex items-center space-x-3 bg-white">
                            <div class="w-9 h-9 rounded-lg bg-teal-100 text-teal-800 flex items-center justify-center font-bold text-xs">HKP</div>
                            <div>
                                <h4 class="text-xs font-bold text-gray-900">Housekeeping</h4>
                                <p class="text-[10px] text-gray-400">Cleanliness statuses</p>
                            </div>
                        </Link>
                        <Link :href="route('admin.settings.index')" class="p-4 border rounded-xl hover:border-emerald-500 hover:bg-emerald-50/30 transition flex items-center space-x-3 bg-white">
                            <div class="w-9 h-9 rounded-lg bg-gray-100 text-gray-800 flex items-center justify-center font-bold text-xs">SET</div>
                            <div>
                                <h4 class="text-xs font-bold text-gray-900">Global Settings</h4>
                                <p class="text-[10px] text-gray-400">System pricing terms</p>
                            </div>
                        </Link>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
