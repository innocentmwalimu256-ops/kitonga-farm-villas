<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    bookings: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');

const handleFilter = () => {
    router.get(route('admin.bookings.index'), {
        search: search.value,
        status: status.value,
        start_date: startDate.value,
        end_date: endDate.value,
    }, { preserveState: true });
};

const clearFilters = () => {
    search.value = '';
    status.value = '';
    startDate.value = '';
    endDate.value = '';
    handleFilter();
};

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-TZ', { style: 'currency', currency: 'TZS', maximumFractionDigits: 0 }).format(val);
};
</script>

<template>
    <Head title="Bookings list" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Bookings Management
                </h2>
                <div class="flex space-x-2">
                    <Link :href="route('admin.bookings.calendar')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded shadow-xs transition">
                        Calendar View
                    </Link>
                    <Link :href="route('admin.bookings.create')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded shadow transition">
                        Create Booking
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-4">
                
                <!-- Filters Grid -->
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Search Guest / Ref</label>
                        <input type="text" v-model="search" @input="handleFilter" placeholder="e.g. Juma / KFV..." class="w-full text-xs rounded border-gray-300 mt-1">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Status</label>
                        <select v-model="status" @change="handleFilter" class="w-full text-xs rounded border-gray-300 mt-1">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="checked_in">Checked In</option>
                            <option value="checked_out">Checked Out</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="no_show">No Show</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Start Date</label>
                        <input type="date" v-model="startDate" @change="handleFilter" class="w-full text-xs rounded border-gray-300 mt-1">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase">End Date</label>
                        <input type="date" v-model="endDate" @change="handleFilter" class="w-full text-xs rounded border-gray-300 mt-1">
                    </div>
                    <div class="flex space-x-2">
                        <button @click="clearFilters" class="flex-1 py-2 text-xs border rounded text-gray-600 hover:bg-gray-50 font-bold transition">Clear</button>
                    </div>
                </div>

                <!-- Bookings Table -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead>
                                <tr class="bg-gray-50 border-b text-xs text-gray-400 uppercase font-bold tracking-wider">
                                    <th class="p-4">Reference</th>
                                    <th class="p-4">Guest</th>
                                    <th class="p-4">Villa Room</th>
                                    <th class="p-4">Stay Dates</th>
                                    <th class="p-4">Nights</th>
                                    <th class="p-4">Total</th>
                                    <th class="p-4">Paid</th>
                                    <th class="p-4">Balance</th>
                                    <th class="p-4 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="booking in bookings.data" :key="booking.id" class="border-b hover:bg-gray-50">
                                    <td class="p-4 font-bold text-gray-900">
                                        <Link :href="route('admin.bookings.show', booking.id)" class="hover:underline text-emerald-700 font-mono">{{ booking.reference }}</Link>
                                    </td>
                                    <td class="p-4">
                                        <div>{{ booking.customer.name }}</div>
                                        <span class="text-[10px] text-gray-400 font-mono">{{ booking.customer.phone }}</span>
                                    </td>
                                    <td class="p-4">
                                        <span class="font-semibold text-gray-800">{{ booking.unit?.name || 'TBD' }}</span>
                                        <div class="text-[10px] text-gray-400">{{ booking.unit?.type?.name }}</div>
                                    </td>
                                    <td class="p-4 text-xs font-semibold">
                                        {{ booking.check_in }} to {{ booking.check_out }}
                                    </td>
                                    <td class="p-4 text-center font-bold text-xs text-gray-500">
                                        {{ Math.round((new Date(booking.check_out) - new Date(booking.check_in)) / (1000 * 60 * 60 * 24)) }}
                                    </td>
                                    <td class="p-4 font-bold text-gray-800">{{ formatCurrency(booking.total) }}</td>
                                    <td class="p-4 text-emerald-700 font-semibold">{{ formatCurrency(booking.amount_paid) }}</td>
                                    <td class="p-4 font-semibold" :class="parseFloat(booking.balance) > 0 ? 'text-red-500' : 'text-gray-400'">{{ formatCurrency(booking.balance) }}</td>
                                    <td class="p-4 text-right">
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold"
                                            :class="{
                                                'bg-yellow-100 text-yellow-800': booking.status === 'pending',
                                                'bg-green-100 text-green-800': booking.status === 'confirmed',
                                                'bg-blue-100 text-blue-800': booking.status === 'checked_in',
                                                'bg-gray-100 text-gray-800': booking.status === 'checked_out',
                                                'bg-red-100 text-red-800': booking.status === 'cancelled',
                                            }">
                                            {{ booking.status }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="bookings.data.length === 0">
                                    <td colspan="9" class="p-8 text-center text-gray-400">No booking reservations found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
