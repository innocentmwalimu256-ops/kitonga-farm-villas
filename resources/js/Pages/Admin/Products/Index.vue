<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    products: Object,
    categories: Array,
    filters: Object,
});

const createForm = useForm({
    product_category_id: '',
    sku: '',
    name: '',
    description: '',
    unit: 'pcs',
    selling_price: '',
    cost_price: '',
    stock: 0,
    low_stock_threshold: 5,
});

const filterForm = useForm({
    search: props.filters.search || '',
    category_id: props.filters.category_id || '',
});

const adjustStockForm = useForm({
    quantity: '',
    type: 'add', // add, subtract, opening
    reason: '',
});

const showCreateForm = ref(false);
const activeAdjustProduct = ref(null);

const submitCreateProduct = () => {
    createForm.post(route('admin.products.store'), {
        onSuccess: () => {
            createForm.reset();
            showCreateForm.value = false;
            alert('Product created successfully.');
        }
    });
};

const openAdjustModal = (product) => {
    activeAdjustProduct.value = product;
    adjustStockForm.quantity = '';
    adjustStockForm.type = 'add';
    adjustStockForm.reason = '';
};

const closeAdjustModal = () => {
    activeAdjustProduct.value = null;
};

const submitStockAdjustment = () => {
    adjustStockForm.post(route('admin.products.adjust', activeAdjustProduct.value.id), {
        onSuccess: () => {
            closeAdjustModal();
            alert('Stock adjusted successfully.');
        }
    });
};

const applyFilters = () => {
    filterForm.get(route('admin.products.index'));
};

const clearFilters = () => {
    filterForm.search = '';
    filterForm.category_id = '';
    filterForm.get(route('admin.products.index'));
};
</script>

<template>
    <Head title="Products & Inventory" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Products & Farm Inventory
                </h2>
                <button @click="showCreateForm = !showCreateForm" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded shadow-xs transition">
                    {{ showCreateForm ? 'Close Form' : 'Add New Product' }}
                </button>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <!-- CREATE FORM -->
                <div v-if="showCreateForm" class="bg-white p-6 rounded-lg border border-gray-100 shadow-sm space-y-4">
                    <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider">New Product Details</h3>
                    <form @submit.prevent="submitCreateProduct" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Category</label>
                            <select v-model="createForm.product_category_id" required class="w-full text-xs rounded border-gray-300 mt-1">
                                <option value="" disabled>Select category</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">SKU / Code</label>
                            <input v-model="createForm.sku" type="text" required placeholder="e.g. KFV-P-MANGO" class="w-full text-xs rounded border-gray-300 mt-1" />
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Product Name</label>
                            <input v-model="createForm.name" type="text" required class="w-full text-xs rounded border-gray-300 mt-1" />
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Selling Price (TZS)</label>
                            <input v-model="createForm.selling_price" type="number" required class="w-full text-xs rounded border-gray-300 mt-1" />
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Cost Price (TZS)</label>
                            <input v-model="createForm.cost_price" type="number" required class="w-full text-xs rounded border-gray-300 mt-1" />
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Unit of Measure</label>
                            <input v-model="createForm.unit" type="text" required placeholder="e.g. kg, bottle, tray" class="w-full text-xs rounded border-gray-300 mt-1" />
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Opening Stock</label>
                            <input v-model="createForm.stock" type="number" required class="w-full text-xs rounded border-gray-300 mt-1" />
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Low Stock Alert Threshold</label>
                            <input v-model="createForm.low_stock_threshold" type="number" required class="w-full text-xs rounded border-gray-300 mt-1" />
                        </div>
                        <div class="md:col-span-3">
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Description</label>
                            <textarea v-model="createForm.description" rows="2" class="w-full text-xs rounded border-gray-300 mt-1"></textarea>
                        </div>
                        <div class="md:col-span-3">
                            <button type="submit" class="w-full py-2 bg-emerald-600 text-white font-bold rounded text-xs hover:bg-emerald-700 transition">Save Product</button>
                        </div>
                    </form>
                </div>

                <!-- FILTERS -->
                <div class="bg-white p-6 rounded-lg border border-gray-100 shadow-sm space-y-4">
                    <h3 class="font-bold text-gray-800 text-xs uppercase tracking-wider">Search & Filter</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <input v-model="filterForm.search" type="text" placeholder="Search by name or SKU..." class="w-full text-xs rounded border-gray-300 mt-1" />
                        </div>
                        <div>
                            <select v-model="filterForm.category_id" class="w-full text-xs rounded border-gray-300 mt-1">
                                <option value="">All Categories</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
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
                                    <th class="p-4">SKU</th>
                                    <th class="p-4">Product Name</th>
                                    <th class="p-4">Category</th>
                                    <th class="p-4">Selling Price</th>
                                    <th class="p-4">Cost Price</th>
                                    <th class="p-4">Available Stock</th>
                                    <th class="p-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="prod in products.data" :key="prod.id" class="border-b hover:bg-gray-50/50">
                                    <td class="p-4 font-mono text-xs text-gray-900 font-bold">{{ prod.sku }}</td>
                                    <td class="p-4">
                                        <div class="text-xs font-bold text-gray-900">{{ prod.name }}</div>
                                        <div class="text-[10px] text-gray-400">{{ prod.unit }}</div>
                                    </td>
                                    <td class="p-4 text-xs font-semibold">{{ prod.category ? prod.category.name : 'Unassigned' }}</td>
                                    <td class="p-4 font-mono text-xs">TZS {{ Number(prod.selling_price).toLocaleString() }}</td>
                                    <td class="p-4 font-mono text-xs text-gray-400">TZS {{ Number(prod.cost_price).toLocaleString() }}</td>
                                    <td class="p-4">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold"
                                            :class="{
                                                'bg-red-100 text-red-800': prod.stock <= prod.low_stock_threshold,
                                                'bg-green-100 text-green-800': prod.stock > prod.low_stock_threshold,
                                            }">
                                            {{ prod.stock }} {{ prod.unit }}
                                        </span>
                                        <span v-if="prod.stock <= prod.low_stock_threshold" class="ml-1 text-[9px] text-red-500 font-bold uppercase">Low Stock</span>
                                    </td>
                                    <td class="p-4 text-right space-x-1">
                                        <button @click="openAdjustModal(prod)" class="px-2 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold rounded shadow-xs transition">
                                            Adjust Stock
                                        </button>
                                        <Link :href="route('admin.products.movements', { id: prod.id })" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-[10px] font-bold rounded shadow-xs transition inline-block">
                                            Movements Log
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="products.data.length === 0">
                                    <td colspan="7" class="p-8 text-center text-gray-400 italic">No products matching filters.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- PAGINATION -->
                    <div v-if="products.links.length > 3" class="p-4 border-t flex justify-center space-x-1">
                        <Link v-for="(link, k) in products.links" :key="k" :href="link.url || '#'" 
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

                <!-- ADJUST STOCK MODAL -->
                <div v-if="activeAdjustProduct" class="fixed inset-0 bg-gray-900/50 flex items-center justify-center z-50 p-4">
                    <div class="bg-white p-6 rounded-lg shadow-lg border w-full max-w-sm space-y-4">
                        <h3 class="font-bold text-gray-800 border-b pb-2">Adjust Stock for: {{ activeAdjustProduct.name }}</h3>
                        <p class="text-xs text-gray-400">Current Stock: {{ activeAdjustProduct.stock }} {{ activeAdjustProduct.unit }}</p>
                        
                        <form @submit.prevent="submitStockAdjustment" class="space-y-4">
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Adjustment Type</label>
                                <select v-model="adjustStockForm.type" class="w-full text-xs rounded border-gray-300 mt-1">
                                    <option value="add">Add Stock (Purchase / Harvest)</option>
                                    <option value="subtract">Subtract Stock (Wastage / Return)</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Quantity ({{ activeAdjustProduct.unit }})</label>
                                <input v-model="adjustStockForm.quantity" type="number" required min="1" class="w-full text-xs rounded border-gray-300 mt-1" />
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Reason / Reference</label>
                                <input v-model="adjustStockForm.reason" type="text" required placeholder="e.g. Harvest from Field B, spoilage" class="w-full text-xs rounded border-gray-300 mt-1" />
                            </div>
                            <div class="flex space-x-2 pt-2">
                                <button type="submit" class="flex-1 py-2 bg-emerald-600 text-white text-xs font-bold rounded hover:bg-emerald-700 transition">Apply Adjustment</button>
                                <button type="button" @click="closeAdjustModal" class="flex-1 py-2 bg-gray-100 text-gray-700 text-xs font-bold rounded hover:bg-gray-200 transition">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
