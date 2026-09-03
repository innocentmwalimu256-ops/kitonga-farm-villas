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

const filterForm = useForm({
    category: props.filters.category || '',
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
});

const showCreateForm = ref(false);

const submitExpense = () => {
    form.post(route('admin.expenses.store'), {
        onSuccess: () => {
            form.reset();
            showCreateForm.value = false;
            alert('Expense recorded successfully.');
        }
    });
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
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Hotel & Farm Expenses
                </h2>
                <button @click="showCreateForm = !showCreateForm" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded shadow-xs transition">
                    {{ showCreateForm ? 'Close Form' : 'Record New Expense' }}
                </button>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <!-- CREATE FORM -->
                <div v-if="showCreateForm" class="bg-white p-6 rounded-lg border border-gray-100 shadow-sm space-y-4">
                    <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider">Log Expense Details</h3>
                    <form @submit.prevent="submitExpense" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Category</label>
                            <select v-model="form.category" required class="w-full text-xs rounded border-gray-300 mt-1">
                                <option value="" disabled>Select category</option>
                                <option v-for="cat in categories" :key="cat" :value="cat.toLowerCase()">{{ cat }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Amount (TZS)</label>
                            <input v-model="form.amount" type="number" step="0.01" required class="w-full text-xs rounded border-gray-300 mt-1" />
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Date</label>
                            <input v-model="form.date" type="date" required class="w-full text-xs rounded border-gray-300 mt-1" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Description / Vendor / Item Details</label>
                            <input v-model="form.description" type="text" required class="w-full text-xs rounded border-gray-300 mt-1" />
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Payment Method</label>
                            <select v-model="form.payment_method" class="w-full text-xs rounded border-gray-300 mt-1">
                                <option v-for="method in payment_methods" :key="method" :value="method">{{ method }}</option>
                            </select>
                        </div>
                        <div class="md:col-span-3 pt-2">
                            <button type="submit" class="w-full py-2 bg-emerald-600 text-white font-bold rounded text-xs hover:bg-emerald-700 transition">Save Expense Record</button>
                        </div>
                    </form>
                </div>

                <!-- FILTERS -->
                <div class="bg-white p-6 rounded-lg border border-gray-100 shadow-sm space-y-4">
                    <h3 class="font-bold text-gray-800 text-xs uppercase tracking-wider">Filter Operations</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Category</label>
                            <select v-model="filterForm.category" class="w-full text-xs rounded border-gray-300 mt-1">
                                <option value="">All Categories</option>
                                <option v-for="cat in categories" :key="cat" :value="cat.toLowerCase()">{{ cat }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Start Date</label>
                            <input v-model="filterForm.start_date" type="date" class="w-full text-xs rounded border-gray-300 mt-1" />
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">End Date</label>
                            <input v-model="filterForm.end_date" type="date" class="w-full text-xs rounded border-gray-300 mt-1" />
                        </div>
                        <div class="flex items-end space-x-2">
                            <button @click="applyFilters" class="flex-1 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded transition">Apply</button>
                            <button @click="clearFilters" class="flex-1 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded transition">Clear</button>
                        </div>
                    </div>
                </div>

                <!-- LIST TABLE -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead>
                                <tr class="bg-gray-100/50 border-b text-xs text-gray-400 uppercase font-bold tracking-wider">
                                    <th class="p-4">Date</th>
                                    <th class="p-4">Category</th>
                                    <th class="p-4">Description</th>
                                    <th class="p-4">Payment Method</th>
                                    <th class="p-4">Recorded By</th>
                                    <th class="p-4">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="exp in expenses.data" :key="exp.id" class="border-b hover:bg-gray-50/50">
                                    <td class="p-4 font-mono text-xs text-gray-900">{{ exp.date }}</td>
                                    <td class="p-4">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-gray-100 text-gray-700">
                                            {{ exp.category }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-xs font-semibold text-gray-900">{{ exp.description }}</td>
                                    <td class="p-4 uppercase text-xs">{{ exp.payment_method }}</td>
                                    <td class="p-4 text-xs">{{ exp.creator ? exp.creator.name : 'System' }}</td>
                                    <td class="p-4 font-bold text-gray-900 text-right font-mono">TZS {{ Number(exp.amount).toLocaleString() }}</td>
                                </tr>
                                <tr v-if="expenses.data.length === 0">
                                    <td colspan="6" class="p-8 text-center text-gray-400 italic">No expenses recorded matching the query.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- PAGINATION -->
                    <div v-if="expenses.links.length > 3" class="p-4 border-t flex justify-center space-x-1">
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
    </AuthenticatedLayout>
</template>
