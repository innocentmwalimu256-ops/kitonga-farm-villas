<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    guests: Object,
    filters: Object,
});

const filterForm = useForm({
    search: props.filters.search || '',
});

const applyFilters = () => {
    filterForm.get(route('admin.guests.index'));
};

const clearFilters = () => {
    filterForm.search = '';
    filterForm.get(route('admin.guests.index'));
};
</script>

<template>
    <Head title="Guests Registry" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Guests Registry
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <!-- FILTERS -->
                <div class="bg-white p-6 rounded-lg border border-gray-100 shadow-sm space-y-4">
                    <h3 class="font-bold text-gray-800 text-xs uppercase tracking-wider">Search Guests</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <input v-model="filterForm.search" type="text" placeholder="Search by name, phone or email..." class="w-full text-xs rounded border-gray-300 mt-1" />
                        </div>
                        <div class="flex items-end space-x-2">
                            <button @click="applyFilters" class="flex-1 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded transition">Search</button>
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
                                    <th class="p-4">Guest Name</th>
                                    <th class="p-4">Phone Number</th>
                                    <th class="p-4">Email Address</th>
                                    <th class="p-4">Registration Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="guest in guests.data" :key="guest.id" class="border-b hover:bg-gray-50/50">
                                    <td class="p-4 font-bold text-gray-900 text-xs">{{ guest.name }}</td>
                                    <td class="p-4 font-mono text-xs">{{ guest.phone || 'N/A' }}</td>
                                    <td class="p-4 text-xs">{{ guest.email || 'N/A' }}</td>
                                    <td class="p-4 font-mono text-xs text-gray-400">{{ new Date(guest.created_at).toLocaleDateString() }}</td>
                                </tr>
                                <tr v-if="guests.data.length === 0">
                                    <td colspan="4" class="p-8 text-center text-gray-400 italic">No guest records found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- PAGINATION -->
                    <div v-if="guests.links.length > 3" class="p-4 border-t flex justify-center space-x-1">
                        <Link v-for="(link, k) in guests.links" :key="k" :href="link.url || '#'" 
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

            </div>
        </div>
    </AuthenticatedLayout>
</template>
