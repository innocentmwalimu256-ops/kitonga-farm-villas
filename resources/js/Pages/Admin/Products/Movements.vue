<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    product: Object,
    movements: Object,
});
</script>

<template>
    <Head :title="'Inventory Movements: ' + product.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Stock Movements Log: {{ product.name }}
                </h2>
                <Link :href="route('admin.products.index')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded shadow-xs transition">
                    Back to Products
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                        <div>
                            <span class="text-xs text-gray-400 font-bold uppercase">SKU:</span>
                            <span class="ml-1 text-xs font-mono font-bold text-gray-900">{{ product.sku }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-400 font-bold uppercase">Current Stock:</span>
                            <span class="ml-1 text-xs font-bold text-gray-900">{{ product.stock }} {{ product.unit }}</span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead>
                                <tr class="bg-gray-100/50 border-b text-xs text-gray-400 uppercase font-bold tracking-wider">
                                    <th class="p-4">Timestamp</th>
                                    <th class="p-4">Type</th>
                                    <th class="p-4">Quantity Changed</th>
                                    <th class="p-4">Reference Source</th>
                                    <th class="p-4">Adjustment Reason</th>
                                    <th class="p-4">Processed By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="m in movements.data" :key="m.id" class="border-b hover:bg-gray-50/50">
                                    <td class="p-4 font-mono text-xs text-gray-900">{{ new Date(m.created_at).toLocaleString() }}</td>
                                    <td class="p-4">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase"
                                            :class="{
                                                'bg-green-100 text-green-800': ['opening', 'stock_in', 'return'].includes(m.type),
                                                'bg-red-100 text-red-800': ['sale', 'wastage', 'adjustment'].includes(m.type) && m.quantity < 0,
                                                'bg-gray-100 text-gray-700': m.type === 'adjustment' && m.quantity >= 0,
                                            }">
                                            {{ m.type }}
                                        </span>
                                    </td>
                                    <td class="p-4 font-mono text-xs font-bold"
                                        :class="{
                                            'text-green-600': m.quantity > 0,
                                            'text-red-600': m.quantity < 0,
                                        }">
                                        {{ m.quantity > 0 ? '+' : '' }}{{ m.quantity }}
                                    </td>
                                    <td class="p-4 uppercase text-xs text-gray-400">{{ m.reference_type }}</td>
                                    <td class="p-4 text-xs text-gray-900 font-semibold">{{ m.reason }}</td>
                                    <td class="p-4 text-xs">{{ m.creator ? m.creator.name : 'System / POS' }}</td>
                                </tr>
                                <tr v-if="movements.data.length === 0">
                                    <td colspan="6" class="p-8 text-center text-gray-400 italic">No inventory movements recorded.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- PAGINATION -->
                    <div v-if="movements.links.length > 3" class="p-4 border-t flex justify-center space-x-1">
                        <Link v-for="(link, k) in movements.links" :key="k" :href="link.url || '#'" 
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
