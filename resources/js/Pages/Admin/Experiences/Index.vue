<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    experiences: Array,
});

const createForm = useForm({
    name: '',
    slug: '',
    price: 0,
    capacity_per_slot: 20,
    status: 'draft',
});

const showCreateForm = ref(false);

const autoGenerateSlug = () => {
    createForm.slug = createForm.name
        .toLowerCase()
        .replace(/[^a-z0-9 -]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
};

const submitCreateExperience = () => {
    createForm.post(route('admin.experiences.store'), {
        onSuccess: () => {
            createForm.reset();
            showCreateForm.value = false;
            alert('Experience created successfully. You can now edit its full details.');
        }
    });
};

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-TZ', { style: 'currency', currency: 'TZS', maximumFractionDigits: 0 }).format(val);
};

const deleteForm = useForm({});
const deleteExperience = (id) => {
    if (confirm('Are you sure you want to permanently delete this experience?')) {
        deleteForm.delete(route('admin.experiences.destroy', id), {
            onSuccess: () => alert('Experience deleted.')
        });
    }
};
</script>

<template>
    <Head title="Agritourism Experiences" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Agritourism Experiences
                </h2>
                <button @click="showCreateForm = !showCreateForm" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded shadow-xs transition">
                    {{ showCreateForm ? 'Close Form' : 'Add New Experience' }}
                </button>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <!-- CREATE FORM -->
                <div v-if="showCreateForm" class="bg-white p-6 rounded-lg border border-gray-100 shadow-xs space-y-4">
                    <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider">New Experience Details</h3>
                    <form @submit.prevent="submitCreateExperience" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div>
                            <label class="font-bold text-gray-400 uppercase text-[9px] block">Name</label>
                            <input v-model="createForm.name" @input="autoGenerateSlug" type="text" class="w-full text-xs rounded border-gray-300 mt-1" required />
                        </div>
                        <div>
                            <label class="font-bold text-gray-400 uppercase text-[9px] block">Slug</label>
                            <input v-model="createForm.slug" type="text" class="w-full text-xs rounded border-gray-300 mt-1" required />
                        </div>
                        <div>
                            <label class="font-bold text-gray-400 uppercase text-[9px] block">Price (TZS)</label>
                            <input v-model="createForm.price" type="number" class="w-full text-xs rounded border-gray-300 mt-1" required min="0" />
                        </div>
                        <div>
                            <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded transition">
                                Create & Edit Details
                            </button>
                        </div>
                    </form>
                </div>

                <!-- LISTING TABLE -->
                <div class="bg-white overflow-hidden shadow-xs sm:rounded-lg border border-gray-100">
                    <div class="p-6 text-gray-900">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="border-b border-gray-100 text-gray-400 uppercase text-[9px] tracking-wider">
                                        <th class="py-3 px-4">Name</th>
                                        <th class="py-3 px-4">Category</th>
                                        <th class="py-3 px-4">Duration</th>
                                        <th class="py-3 px-4">Price</th>
                                        <th class="py-3 px-4">Max Guests</th>
                                        <th class="py-3 px-4">Status</th>
                                        <th class="py-3 px-4 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="exp in experiences" :key="exp.id" class="border-b border-gray-50 hover:bg-gray-50/50">
                                        <td class="py-4 px-4 font-bold text-gray-800">
                                            {{ exp.name }}
                                            <span v-if="exp.featured" class="ml-1.5 px-1.5 py-0.5 bg-amber-50 text-amber-600 rounded text-[9px] font-bold border border-amber-200">Featured</span>
                                        </td>
                                        <td class="py-4 px-4 text-gray-500">{{ exp.category || 'N/A' }}</td>
                                        <td class="py-4 px-4 text-gray-500">{{ exp.duration || 'N/A' }}</td>
                                        <td class="py-4 px-4 font-semibold text-gray-800">{{ formatCurrency(exp.price) }}</td>
                                        <td class="py-4 px-4 text-gray-500">{{ exp.capacity_per_slot }} Guests</td>
                                        <td class="py-4 px-4">
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider" :class="{
                                                'bg-amber-100 text-amber-700': exp.status === 'draft',
                                                'bg-blue-100 text-blue-700': exp.status === 'preview',
                                                'bg-emerald-100 text-emerald-700': exp.status === 'published',
                                                'bg-gray-100 text-gray-600': exp.status === 'unpublished',
                                                'bg-rose-100 text-rose-700': exp.status === 'archived',
                                            }">
                                                {{ exp.status }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 text-right space-x-2">
                                            <Link :href="route('admin.experiences.preview', exp.slug)" class="px-2 py-1 border border-blue-200 text-blue-600 rounded hover:bg-blue-50">Preview</Link>
                                            <Link :href="route('admin.experiences.edit', exp.id)" class="px-2 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200">Edit Details</Link>
                                            <button @click="deleteExperience(exp.id)" class="px-2 py-1 bg-rose-50 text-rose-600 rounded hover:bg-rose-100 border border-rose-100">Delete</button>
                                        </td>
                                    </tr>
                                    <tr v-if="experiences.length === 0">
                                        <td colspan="7" class="py-8 text-center text-gray-400">No experiences registered in database.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
