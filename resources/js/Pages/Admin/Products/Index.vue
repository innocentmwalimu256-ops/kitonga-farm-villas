<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    products: Object,
    categories: Array,
    filters: Object,
});

// CREATE FORM
const showCreateModal = ref(false);
const createForm = useForm({
    product_category_id: '',
    sku: '',
    name: '',
    description: '',
    image: '',
    unit: 'pcs',
    selling_price: '',
    cost_price: '',
    stock: 0,
    low_stock_threshold: 5,
});

// EDIT FORM
const showEditModal = ref(false);
const editForm = useForm({
    id: null,
    product_category_id: '',
    sku: '',
    name: '',
    description: '',
    image: '',
    unit: '',
    selling_price: '',
    cost_price: '',
    low_stock_threshold: 5,
    active: true,
});

// DELETE
const showDeleteModal = ref(false);
const productToDelete = ref(null);
const deleteForm = useForm({});

// ADJUST STOCK
const activeAdjustProduct = ref(null);
const adjustStockForm = useForm({
    quantity: '',
    type: 'add',
    reason: '',
});

// FILTERS
const filterForm = useForm({
    search: props.filters.search || '',
    category_id: props.filters.category_id || '',
});

// CREATE HANDLERS
const openCreateModal = () => {
    createForm.reset();
    createForm.unit = 'pcs';
    createForm.stock = 0;
    createForm.low_stock_threshold = 5;
    showCreateModal.value = true;
};

const submitCreateProduct = () => {
    createForm.post(route('admin.products.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        }
    });
};

// EDIT HANDLERS
const openEditModal = (product) => {
    editForm.id = product.id;
    editForm.product_category_id = product.product_category_id;
    editForm.sku = product.sku;
    editForm.name = product.name;
    editForm.description = product.description || '';
    editForm.image = product.image || '';
    editForm.unit = product.unit;
    editForm.selling_price = product.selling_price;
    editForm.cost_price = product.cost_price;
    editForm.low_stock_threshold = product.low_stock_threshold;
    editForm.active = Boolean(product.active);
    showEditModal.value = true;
};

const submitEditProduct = () => {
    editForm.post(route('admin.products.update', editForm.id), {
        preserveScroll: true,
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
        }
    });
};

// DELETE HANDLERS
const openDeleteModal = (product) => {
    productToDelete.value = product;
    showDeleteModal.value = true;
};

const confirmDeleteProduct = () => {
    if (!productToDelete.value) return;
    deleteForm.delete(route('admin.products.destroy', productToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            productToDelete.value = null;
        }
    });
};

// ADJUST STOCK HANDLERS
const openAdjustModal = (product) => {
    activeAdjustProduct.value = product;
    adjustStockForm.quantity = '';
    adjustStockForm.type = 'add';
    adjustStockForm.reason = '';
};

const closeAdjustModal = () => {
    activeAdjustProduct.value = null;
    adjustStockForm.reset();
};

const submitStockAdjustment = () => {
    if (!activeAdjustProduct.value) return;
    adjustStockForm.post(route('admin.products.adjust', activeAdjustProduct.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeAdjustModal();
        }
    });
};

// FILTER HANDLERS
const applyFilters = () => {
    filterForm.get(route('admin.products.index'), { preserveState: true });
};

const clearFilters = () => {
    filterForm.search = '';
    filterForm.category_id = '';
    filterForm.get(route('admin.products.index'), { preserveState: true });
};
</script>

<template>
    <Head title="Products & Farm Inventory" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-900 tracking-tight font-serif">
                        Products & Farm Inventory
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Manage farm produce, catalog prices, stock levels, and item configurations</p>
                </div>
                <button 
                    @click="openCreateModal" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white text-xs font-bold rounded-lg shadow-sm hover:shadow transition-all duration-200"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add New Product
                </button>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <!-- FILTERS -->
                <div class="bg-white p-5 rounded-xl border border-gray-200/80 shadow-xs space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-gray-700 text-xs uppercase tracking-wider flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            Search & Filters
                        </h3>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="relative">
                            <input 
                                v-model="filterForm.search" 
                                type="text" 
                                placeholder="Search by name or SKU..." 
                                @keyup.enter="applyFilters"
                                class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-2xs py-2 pl-3" 
                            />
                        </div>
                        <div>
                            <select 
                                v-model="filterForm.category_id" 
                                @change="applyFilters"
                                class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-2xs py-2"
                            >
                                <option value="">All Categories</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-2">
                            <button 
                                @click="applyFilters" 
                                class="flex-1 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition shadow-2xs"
                            >
                                Apply
                            </button>
                            <button 
                                @click="clearFilters" 
                                class="flex-1 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-lg transition shadow-2xs"
                            >
                                Clear
                            </button>
                        </div>
                    </div>
                </div>

                <!-- TABLE GRID -->
                <div class="bg-white rounded-xl shadow-xs border border-gray-200/80 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead>
                                <tr class="bg-gray-50/80 border-b border-gray-200/80 text-[11px] text-gray-500 uppercase font-bold tracking-wider">
                                    <th class="py-3.5 px-4">Product</th>
                                    <th class="py-3.5 px-4">SKU / Code</th>
                                    <th class="py-3.5 px-4">Category</th>
                                    <th class="py-3.5 px-4">Selling Price</th>
                                    <th class="py-3.5 px-4">Cost Price</th>
                                    <th class="py-3.5 px-4">Stock Status</th>
                                    <th class="py-3.5 px-4 text-center">Status</th>
                                    <th class="py-3.5 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="prod in products.data" :key="prod.id" class="hover:bg-gray-50/70 transition-colors">
                                    <!-- Product Info & Image -->
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-gray-100 border border-gray-200 flex-shrink-0 overflow-hidden flex items-center justify-center">
                                                <img 
                                                    v-if="prod.image" 
                                                    :src="prod.image" 
                                                    :alt="prod.name" 
                                                    class="w-full h-full object-cover" 
                                                    @error="(e) => e.target.style.display = 'none'" 
                                                />
                                                <svg v-else class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="text-xs font-bold text-gray-900">{{ prod.name }}</div>
                                                <div class="text-[10px] text-gray-500 font-medium">Unit: {{ prod.unit }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- SKU -->
                                    <td class="py-3.5 px-4 font-mono text-xs text-gray-700 font-semibold">{{ prod.sku }}</td>

                                    <!-- Category -->
                                    <td class="py-3.5 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                            {{ prod.category ? prod.category.name : 'Unassigned' }}
                                        </span>
                                    </td>

                                    <!-- Selling Price -->
                                    <td class="py-3.5 px-4 font-mono text-xs font-bold text-emerald-800">
                                        TZS {{ Number(prod.selling_price).toLocaleString() }}
                                    </td>

                                    <!-- Cost Price -->
                                    <td class="py-3.5 px-4 font-mono text-xs text-gray-500">
                                        TZS {{ Number(prod.cost_price).toLocaleString() }}
                                    </td>

                                    <!-- Available Stock -->
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-1.5">
                                            <span 
                                                class="px-2.5 py-0.5 rounded-full text-xs font-bold"
                                                :class="{
                                                    'bg-red-100 text-red-700 border border-red-200': prod.stock <= prod.low_stock_threshold,
                                                    'bg-emerald-100 text-emerald-800 border border-emerald-200': prod.stock > prod.low_stock_threshold,
                                                }"
                                            >
                                                {{ prod.stock }} {{ prod.unit }}
                                            </span>
                                            <span v-if="prod.stock <= prod.low_stock_threshold" class="text-[9px] font-extrabold text-red-600 uppercase tracking-tight">
                                                Low
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Active Status -->
                                    <td class="py-3.5 px-4 text-center">
                                        <span 
                                            class="inline-block w-2.5 h-2.5 rounded-full" 
                                            :class="prod.active ? 'bg-emerald-500 ring-4 ring-emerald-100' : 'bg-gray-300 ring-4 ring-gray-100'"
                                            :title="prod.active ? 'Active' : 'Inactive'"
                                        ></span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="py-3.5 px-4 text-right space-x-1.5 whitespace-nowrap">
                                        <!-- Edit Button -->
                                        <button 
                                            @click="openEditModal(prod)" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-lg border border-blue-200/80 transition"
                                            title="Edit Product & Prices"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                            Edit
                                        </button>

                                        <!-- Adjust Stock Button -->
                                        <button 
                                            @click="openAdjustModal(prod)" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold rounded-lg border border-emerald-200/80 transition"
                                            title="Adjust Stock Level"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                            </svg>
                                            Stock
                                        </button>

                                        <!-- Movements Log -->
                                        <Link 
                                            :href="route('admin.products.movements', { id: prod.id })" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-lg border border-gray-200/80 transition"
                                            title="View Inventory Movements Log"
                                        >
                                            Log
                                        </Link>

                                        <!-- Delete Button -->
                                        <button 
                                            @click="openDeleteModal(prod)" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold rounded-lg border border-red-200/80 transition"
                                            title="Delete Product"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="products.data.length === 0">
                                    <td colspan="8" class="p-12 text-center text-gray-400 italic">
                                        No products matching the current search filters.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- PAGINATION -->
                    <div v-if="products.links && products.links.length > 3" class="p-4 border-t border-gray-100 flex justify-center space-x-1">
                        <Link 
                            v-for="(link, k) in products.links" 
                            :key="k" 
                            :href="link.url || '#'" 
                            class="px-3 py-1.5 rounded-lg text-xs transition" 
                            :class="{
                                'bg-emerald-600 text-white font-bold shadow-2xs': link.active,
                                'bg-gray-100 hover:bg-gray-200 text-gray-700': !link.active && link.url,
                                'text-gray-300 cursor-not-allowed': !link.url
                            }"
                            v-html="link.label"
                        >
                        </Link>
                    </div>
                </div>

            </div>
        </div>

        <!-- CREATE PRODUCT MODAL -->
        <div v-if="showCreateModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4 overflow-y-auto">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 w-full max-w-2xl overflow-hidden my-8 transform transition-all">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Add New Farm Product</h3>
                        <p class="text-xs text-gray-500">Create a new product item in your inventory catalog</p>
                    </div>
                    <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-200/50 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form @submit.prevent="submitCreateProduct" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Category <span class="text-red-500">*</span></label>
                            <select v-model="createForm.product_category_id" required class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-2xs">
                                <option value="" disabled>Select category</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">SKU / Code <span class="text-red-500">*</span></label>
                            <input v-model="createForm.sku" type="text" required placeholder="e.g. KFV-MILK-1L" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-2xs" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Product Name <span class="text-red-500">*</span></label>
                            <input v-model="createForm.name" type="text" required placeholder="e.g. Fresh Whole Farm Milk (1L)" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-2xs" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Unit of Measure <span class="text-red-500">*</span></label>
                            <input v-model="createForm.unit" type="text" required placeholder="e.g. 1L, Tray, Bottle, Kg" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-2xs" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Selling Price (TZS) <span class="text-red-500">*</span></label>
                            <input v-model="createForm.selling_price" type="number" step="any" min="0" required placeholder="e.g. 3500" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-2xs" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Cost Price (TZS) <span class="text-red-500">*</span></label>
                            <input v-model="createForm.cost_price" type="number" step="any" min="0" required placeholder="e.g. 2000" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-2xs" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Opening Stock Quantity <span class="text-red-500">*</span></label>
                            <input v-model="createForm.stock" type="number" min="0" required class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-2xs" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Low Stock Alert Level <span class="text-red-500">*</span></label>
                            <input v-model="createForm.low_stock_threshold" type="number" min="0" required class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-2xs" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Product Image URL / Path</label>
                        <input v-model="createForm.image" type="text" placeholder="/storage/products/item.webp or image URL" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-2xs" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Description / Notes</label>
                        <textarea v-model="createForm.description" rows="3" placeholder="Optional description for web catalog or customer receipts..." class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-2xs"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg text-xs transition">
                            Cancel
                        </button>
                        <button type="submit" :disabled="createForm.processing" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-bold rounded-lg text-xs shadow-sm transition">
                            {{ createForm.processing ? 'Saving...' : 'Create Product' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- EDIT PRODUCT MODAL -->
        <div v-if="showEditModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4 overflow-y-auto">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 w-full max-w-2xl overflow-hidden my-8 transform transition-all">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Edit Product: {{ editForm.name }}</h3>
                        <p class="text-xs text-gray-500">Update pricing, codes, units, and details</p>
                    </div>
                    <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-200/50 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form @submit.prevent="submitEditProduct" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Category <span class="text-red-500">*</span></label>
                            <select v-model="editForm.product_category_id" required class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-2xs">
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">SKU / Code <span class="text-red-500">*</span></label>
                            <input v-model="editForm.sku" type="text" required class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-2xs font-mono" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Product Name <span class="text-red-500">*</span></label>
                            <input v-model="editForm.name" type="text" required class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-2xs" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Unit of Measure <span class="text-red-500">*</span></label>
                            <input v-model="editForm.unit" type="text" required class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-2xs" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-emerald-800 uppercase tracking-wide mb-1">Selling Price (TZS) <span class="text-red-500">*</span></label>
                            <input v-model="editForm.selling_price" type="number" step="any" min="0" required class="w-full text-xs font-bold rounded-lg border-emerald-300 focus:border-emerald-500 focus:ring-emerald-500 bg-emerald-50/20 shadow-2xs" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Cost Price (TZS) <span class="text-red-500">*</span></label>
                            <input v-model="editForm.cost_price" type="number" step="any" min="0" required class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-2xs" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Low Stock Alert Level <span class="text-red-500">*</span></label>
                            <input v-model="editForm.low_stock_threshold" type="number" min="0" required class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-2xs" />
                        </div>
                        <div class="flex items-center pt-5">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="editForm.active" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                                <span class="ml-3 text-xs font-bold text-gray-800">Active (Visible in Catalog & POS)</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Product Image URL / Path</label>
                        <div class="flex items-center gap-3">
                            <input v-model="editForm.image" type="text" placeholder="/storage/products/item.webp or image URL" class="flex-1 text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-2xs" />
                            <div v-if="editForm.image" class="w-10 h-10 rounded-lg bg-gray-100 border overflow-hidden flex-shrink-0">
                                <img :src="editForm.image" class="w-full h-full object-cover" />
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Description / Notes</label>
                        <textarea v-model="editForm.description" rows="3" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-2xs"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg text-xs transition">
                            Cancel
                        </button>
                        <button type="submit" :disabled="editForm.processing" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-bold rounded-lg text-xs shadow-sm transition">
                            {{ editForm.processing ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- DELETE CONFIRMATION MODAL -->
        <div v-if="showDeleteModal && productToDelete" class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 w-full max-w-md overflow-hidden transform transition-all p-6 space-y-4">
                <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>

                <div class="text-center space-y-2">
                    <h3 class="text-base font-bold text-gray-900">Delete Product?</h3>
                    <p class="text-xs text-gray-600">
                        Are you sure you want to delete <strong class="text-gray-900">{{ productToDelete.name }}</strong> ({{ productToDelete.sku }})?
                    </p>
                    <p class="text-[11px] text-amber-600 bg-amber-50 rounded-lg p-2.5 border border-amber-200/80">
                        Note: If this product has historical sales records, it will be deactivated to preserve sales reporting integrity.
                    </p>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="button" @click="showDeleteModal = false" class="flex-1 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-lg transition">
                        Cancel
                    </button>
                    <button type="button" @click="confirmDeleteProduct" :disabled="deleteForm.processing" class="flex-1 py-2.5 bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white text-xs font-bold rounded-lg shadow-sm transition">
                        {{ deleteForm.processing ? 'Deleting...' : 'Yes, Delete' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ADJUST STOCK MODAL -->
        <div v-if="activeAdjustProduct" class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 w-full max-w-sm overflow-hidden p-6 space-y-4">
                <div class="border-b border-gray-100 pb-3">
                    <h3 class="font-bold text-gray-900 text-sm">Adjust Stock: {{ activeAdjustProduct.name }}</h3>
                    <p class="text-xs text-emerald-700 font-semibold mt-0.5">Current Stock: {{ activeAdjustProduct.stock }} {{ activeAdjustProduct.unit }}</p>
                </div>
                
                <form @submit.prevent="submitStockAdjustment" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Adjustment Type</label>
                        <select v-model="adjustStockForm.type" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-2xs">
                            <option value="add">Add Stock (Purchase / Harvest)</option>
                            <option value="subtract">Subtract Stock (Wastage / Return)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Quantity ({{ activeAdjustProduct.unit }}) <span class="text-red-500">*</span></label>
                        <input v-model="adjustStockForm.quantity" type="number" required min="1" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-2xs" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Reason / Reference <span class="text-red-500">*</span></label>
                        <input v-model="adjustStockForm.reason" type="text" required placeholder="e.g. Harvest from Field B, spoilage" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-2xs" />
                    </div>
                    <div class="flex space-x-2 pt-2">
                        <button type="button" @click="closeAdjustModal" class="flex-1 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-lg transition">Cancel</button>
                        <button type="submit" :disabled="adjustStockForm.processing" class="flex-1 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-xs font-bold rounded-lg shadow-sm transition">
                            {{ adjustStockForm.processing ? 'Applying...' : 'Apply' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
