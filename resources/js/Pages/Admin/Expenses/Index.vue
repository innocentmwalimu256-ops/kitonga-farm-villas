<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    expenses: Object,
    categories: Array,
    payment_methods: Array,
    filters: Object,
});

const form = useForm({
    category: '',
    amount: '',
    date: new Date().toISOString().split('T')[0],
    description: '',
    payment_method: 'cash',
    status: 'approved',
});

const editForm = useForm({
    category: '',
    amount: '',
    date: '',
    description: '',
    payment_method: 'cash',
});

const filterForm = useForm({
    category: props.filters.category || '',
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
});

const showCreateForm = ref(false);
const isEditModalOpen = ref(false);
const editingExpense = ref(null);

const submitExpense = () => {
    form.post(route('admin.expenses.store'), {
        onSuccess: () => {
            form.reset();
            showCreateForm.value = false;
        }
    });
};

const openEditModal = (exp) => {
    editingExpense.value = exp;
    editForm.category = exp.category ? exp.category.toLowerCase() : 'other';
    editForm.amount = exp.amount;
    
    // Parse date into YYYY-MM-DD
    if (exp.date) {
        try {
            editForm.date = new Date(exp.date).toISOString().split('T')[0];
        } catch (e) {
            editForm.date = exp.date;
        }
    } else {
        editForm.date = new Date().toISOString().split('T')[0];
    }
    
    editForm.description = exp.description || '';
    editForm.payment_method = exp.payment_method || 'cash';
    isEditModalOpen.value = true;
};

const closeEditModal = () => {
    isEditModalOpen.value = false;
    editingExpense.value = null;
    editForm.reset();
};

const submitEditExpense = () => {
    if (!editingExpense.value) return;
    editForm.post(route('admin.expenses.update', editingExpense.value.id), {
        onSuccess: () => {
            closeEditModal();
        }
    });
};

const deleteExpense = (exp) => {
    if (confirm(`Are you sure you want to delete this expense of TZS ${Number(exp.amount).toLocaleString()} (${exp.description || exp.category})?`)) {
        useForm({}).delete(route('admin.expenses.destroy', exp.id));
    }
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    } catch (e) {
        return dateStr;
    }
};

const applyFilters = () => {
    filterForm.get(route('admin.expenses.index'));
};

const clearFilters = () => {
    filterForm.category = '';
    filterForm.start_date = '';
    filterForm.end_date = '';
    filterForm.get(route('admin.expenses.index'));
};
</script>

<template>
    <Head title="Expense Manager" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-bold leading-tight text-gray-800">
                        Hotel & Farm Expenses
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Manage operational costs, farm supplies, and resort upkeep.</p>
                </div>
                <button 
                    @click="showCreateForm = !showCreateForm" 
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path v-if="!showCreateForm" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span>{{ showCreateForm ? 'Close Form' : 'Record New Expense' }}</span>
                </button>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <!-- CREATE FORM -->
                <div v-if="showCreateForm" class="bg-white p-6 rounded-xl border border-emerald-100 shadow-md space-y-4 animate-fade-in">
                    <div class="flex justify-between items-center border-b border-gray-100 pb-3">
                        <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Log New Expense Details</span>
                        </h3>
                        <span class="text-xs text-gray-400">All fields marked * are required</span>
                    </div>
                    <form @submit.prevent="submitExpense" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-[11px] font-bold text-gray-600 uppercase">Category *</label>
                            <select v-model="form.category" required class="w-full text-xs rounded-lg border-gray-300 mt-1 focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="" disabled>Select category</option>
                                <option v-for="cat in categories" :key="cat" :value="cat.toLowerCase()">{{ cat }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[11px] font-bold text-gray-600 uppercase">Amount (TZS) *</label>
                            <input v-model="form.amount" type="number" step="0.01" min="1" placeholder="e.g. 50000" required class="w-full text-xs rounded-lg border-gray-300 mt-1 focus:ring-emerald-500 focus:border-emerald-500" />
                        </div>
                        <div>
                            <label class="text-[11px] font-bold text-gray-600 uppercase">Date *</label>
                            <input v-model="form.date" type="date" required class="w-full text-xs rounded-lg border-gray-300 mt-1 focus:ring-emerald-500 focus:border-emerald-500" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-[11px] font-bold text-gray-600 uppercase">Description / Vendor / Item Details *</label>
                            <input v-model="form.description" type="text" placeholder="e.g. Purchase of organic fertilizers for greenhouse" required class="w-full text-xs rounded-lg border-gray-300 mt-1 focus:ring-emerald-500 focus:border-emerald-500" />
                        </div>
                        <div>
                            <label class="text-[11px] font-bold text-gray-600 uppercase">Payment Method</label>
                            <select v-model="form.payment_method" class="w-full text-xs rounded-lg border-gray-300 mt-1 focus:ring-emerald-500 focus:border-emerald-500">
                                <option v-for="method in payment_methods" :key="method" :value="method">{{ method.replace('_', ' ').toUpperCase() }}</option>
                            </select>
                        </div>
                        <div class="md:col-span-3 pt-2 flex justify-end gap-3">
                            <button type="button" @click="showCreateForm = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg text-xs transition">Cancel</button>
                            <button type="submit" :disabled="form.processing" class="px-6 py-2 bg-emerald-600 text-white font-bold rounded-lg text-xs hover:bg-emerald-700 transition disabled:opacity-50">Save Expense Record</button>
                        </div>
                    </form>
                </div>

                <!-- FILTERS -->
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm space-y-3">
                    <h3 class="font-bold text-gray-700 text-xs uppercase tracking-wider">Filter Operations</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Category</label>
                            <select v-model="filterForm.category" class="w-full text-xs rounded-lg border-gray-300 mt-1">
                                <option value="">All Categories</option>
                                <option v-for="cat in categories" :key="cat" :value="cat.toLowerCase()">{{ cat }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Start Date</label>
                            <input v-model="filterForm.start_date" type="date" class="w-full text-xs rounded-lg border-gray-300 mt-1" />
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">End Date</label>
                            <input v-model="filterForm.end_date" type="date" class="w-full text-xs rounded-lg border-gray-300 mt-1" />
                        </div>
                        <div class="flex items-end space-x-2">
                            <button @click="applyFilters" class="flex-1 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition cursor-pointer">Apply</button>
                            <button @click="clearFilters" class="flex-1 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-lg transition cursor-pointer">Clear</button>
                        </div>
                    </div>
                </div>

                <!-- LIST TABLE -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead>
                                <tr class="bg-gray-50/80 border-b text-[11px] text-gray-400 uppercase font-bold tracking-wider">
                                    <th class="p-4">Date</th>
                                    <th class="p-4">Category</th>
                                    <th class="p-4">Description</th>
                                    <th class="p-4">Payment Method</th>
                                    <th class="p-4">Recorded By</th>
                                    <th class="p-4 text-right">Amount</th>
                                    <th class="p-4 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="exp in expenses.data" :key="exp.id" class="hover:bg-gray-50/70 transition duration-150">
                                    <!-- Clean Date -->
                                    <td class="p-4 font-mono text-xs text-gray-800 whitespace-nowrap">
                                        {{ formatDate(exp.date) }}
                                    </td>

                                    <!-- Category Badge -->
                                    <td class="p-4 whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-emerald-50 text-emerald-800 border border-emerald-200/60">
                                            {{ exp.category }}
                                        </span>
                                    </td>

                                    <!-- Description -->
                                    <td class="p-4 text-xs font-semibold text-gray-900 min-w-[200px]">
                                        {{ exp.description || '-' }}
                                    </td>

                                    <!-- Payment Method -->
                                    <td class="p-4 text-xs font-mono uppercase text-gray-500 whitespace-nowrap">
                                        {{ exp.payment_method ? exp.payment_method.replace('_', ' ') : 'CASH' }}
                                    </td>

                                    <!-- Recorded By -->
                                    <td class="p-4 text-xs text-gray-600 whitespace-nowrap">
                                        {{ exp.creator ? exp.creator.name : 'Kitonga Owner' }}
                                    </td>

                                    <!-- Amount -->
                                    <td class="p-4 font-bold text-gray-900 text-right font-mono whitespace-nowrap">
                                        TZS {{ Number(exp.amount).toLocaleString() }}
                                    </td>

                                    <!-- Action Buttons -->
                                    <td class="p-4 text-center whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5">
                                            <!-- Edit Button -->
                                            <button 
                                                @click="openEditModal(exp)" 
                                                class="px-2.5 py-1.5 bg-blue-50 hover:bg-blue-600 text-blue-700 hover:text-white rounded-lg text-xs font-bold transition duration-200 flex items-center gap-1 cursor-pointer"
                                                title="Edit Expense"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                                <span>Edit</span>
                                            </button>

                                            <!-- Delete Button -->
                                            <button 
                                                @click="deleteExpense(exp)" 
                                                class="p-1.5 bg-red-50 hover:bg-red-600 text-red-600 hover:text-white rounded-lg text-xs transition duration-200 cursor-pointer"
                                                title="Delete Expense"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="expenses.data.length === 0">
                                    <td colspan="7" class="p-10 text-center text-gray-400 italic">No expenses recorded matching the query.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- PAGINATION -->
                    <div v-if="expenses.links && expenses.links.length > 3" class="p-4 border-t flex justify-center space-x-1">
                        <Link v-for="(link, k) in expenses.links" :key="k" :href="link.url || '#'" 
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

        <!-- EDIT EXPENSE MODAL -->
        <div v-if="isEditModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 space-y-5 animate-scale-in">
                
                <!-- Modal Header -->
                <div class="flex justify-between items-center border-b border-gray-100 pb-3">
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-base">Edit Expense Record</h3>
                            <p class="text-xs text-gray-400">Modify amount, category, or expense details.</p>
                        </div>
                    </div>
                    <button @click="closeEditModal" class="text-gray-400 hover:text-gray-600 text-xl font-bold p-1 cursor-pointer">✕</button>
                </div>

                <!-- Modal Form -->
                <form @submit.prevent="submitEditExpense" class="space-y-4">
                    
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Category -->
                        <div>
                            <label class="text-[11px] font-bold text-gray-600 uppercase block mb-1">Category *</label>
                            <select v-model="editForm.category" required class="w-full text-xs rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                                <option v-for="cat in categories" :key="cat" :value="cat.toLowerCase()">{{ cat }}</option>
                            </select>
                        </div>

                        <!-- Amount -->
                        <div>
                            <label class="text-[11px] font-bold text-gray-600 uppercase block mb-1">Amount (TZS) *</label>
                            <input v-model="editForm.amount" type="number" step="0.01" min="1" required class="w-full text-xs rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 font-mono font-bold" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <!-- Date -->
                        <div>
                            <label class="text-[11px] font-bold text-gray-600 uppercase block mb-1">Date *</label>
                            <input v-model="editForm.date" type="date" required class="w-full text-xs rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" />
                        </div>

                        <!-- Payment Method -->
                        <div>
                            <label class="text-[11px] font-bold text-gray-600 uppercase block mb-1">Payment Method</label>
                            <select v-model="editForm.payment_method" class="w-full text-xs rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                                <option v-for="method in payment_methods" :key="method" :value="method">{{ method.replace('_', ' ').toUpperCase() }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="text-[11px] font-bold text-gray-600 uppercase block mb-1">Description / Vendor / Item Details *</label>
                        <textarea v-model="editForm.description" rows="2" required class="w-full text-xs rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" placeholder="e.g. Monthly subscription for 5 villa units"></textarea>
                    </div>

                    <!-- Modal Actions -->
                    <div class="pt-3 border-t border-gray-100 flex justify-end gap-3">
                        <button 
                            type="button" 
                            @click="closeEditModal" 
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg text-xs transition cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            :disabled="editForm.processing" 
                            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg text-xs transition shadow-md cursor-pointer disabled:opacity-50"
                        >
                            {{ editForm.processing ? 'Saving...' : 'Update Expense' }}
                        </button>
                    </div>

                </form>

            </div>
        </div>

    </AuthenticatedLayout>
</template>
