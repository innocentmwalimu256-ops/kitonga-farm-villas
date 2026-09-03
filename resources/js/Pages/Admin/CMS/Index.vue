<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    pages: Array,
});

const activeSection = ref(null);

const editForm = useForm({
    value: '',
});

const openEditModal = (section) => {
    activeSection.value = section;
    editForm.value = section.value || '';
};

const closeEditModal = () => {
    activeSection.value = null;
};

const submitSectionUpdate = () => {
    editForm.post(route('admin.cms.update_section', activeSection.value.id), {
        onSuccess: () => {
            closeEditModal();
            alert('CMS section updated successfully.');
        }
    });
};
</script>

<template>
    <Head title="Website CMS Manager" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Website Content Management System (CMS)
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <div v-for="page in pages" :key="page.id" class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                        <div>
                            <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider">{{ page.title }} Page</h3>
                            <p class="text-xs text-gray-400 mt-1">SEO Title: {{ page.seo_title || 'Not set' }}</p>
                        </div>
                        <span class="text-xs font-mono font-bold bg-green-100 text-green-800 px-2 py-0.5 rounded-full uppercase">Active</span>
                    </div>

                    <div class="p-6 space-y-4">
                        <div v-for="sec in page.sections" :key="sec.id" class="border p-4 rounded-lg flex justify-between items-start hover:border-emerald-500 transition">
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">{{ sec.key.replace('_', ' ') }}</span>
                                <p class="text-xs text-gray-800 leading-relaxed font-medium whitespace-pre-line">{{ sec.value || 'Empty content' }}</p>
                            </div>
                            <button @click="openEditModal(sec)" class="ml-4 px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded shadow-xs transition">
                                Edit Copy
                            </button>
                        </div>
                    </div>
                </div>

                <!-- UPDATE MODAL -->
                <div v-if="activeSection" class="fixed inset-0 bg-gray-900/50 flex items-center justify-center z-50 p-4">
                    <div class="bg-white p-6 rounded-lg shadow-lg border w-full max-w-lg space-y-4">
                        <h3 class="font-bold text-gray-800 border-b pb-2">Update Section: {{ activeSection.key.replace('_', ' ') }}</h3>
                        
                        <form @submit.prevent="submitSectionUpdate" class="space-y-4">
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Headline / Text Content</label>
                                <textarea v-model="editForm.value" rows="6" class="w-full text-xs rounded border-gray-300 mt-1" required></textarea>
                            </div>
                            <div class="flex space-x-2 pt-2">
                                <button type="submit" :disabled="editForm.processing" class="flex-1 py-2 bg-emerald-600 text-white text-xs font-bold rounded hover:bg-emerald-700 transition">
                                    {{ editForm.processing ? 'Saving copy...' : 'Save Changes' }}
                                </button>
                                <button type="button" @click="closeEditModal" class="flex-1 py-2 bg-gray-100 text-gray-700 text-xs font-bold rounded hover:bg-gray-200 transition">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
