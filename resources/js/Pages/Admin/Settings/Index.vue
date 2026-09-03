<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const props = defineProps({
    settings: Array,
});

const form = useForm({
    settings: [],
});

onMounted(() => {
    // Populate form settings from props
    form.settings = props.settings.map(s => {
        return {
            id: s.id,
            key: s.key,
            value: s.value,
            description: s.description || '',
        };
    });
});

const submitSettings = () => {
    form.post(route('admin.settings.update'), {
        onSuccess: () => {
            alert('Global settings updated and audit logs recorded.');
        }
    });
};
</script>

<template>
    <Head title="System Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Global Settings & Policy Panel
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8 space-y-6">

                <div class="bg-white p-6 rounded-lg border border-gray-100 shadow-sm space-y-6">
                    <div>
                        <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider">Business Variables</h3>
                        <p class="text-xs text-gray-400 mt-1">Changes to these variables will immediately impact tax calculations and bookings logic system-wide.</p>
                    </div>

                    <form @submit.prevent="submitSettings" class="space-y-4">
                        <div v-for="(setting, index) in form.settings" :key="setting.key" class="border-b pb-4 last:border-0 last:pb-0">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">{{ setting.key.replace('_', ' ') }}</label>
                            
                            <!-- Display input type depending on key name -->
                            <div class="flex space-x-2 mt-1">
                                <input v-model="form.settings[index].value" type="text" required class="flex-1 text-xs rounded border-gray-300" />
                            </div>
                            <span class="text-[10px] text-gray-400 italic block mt-1">{{ setting.description }}</span>
                        </div>

                        <div class="pt-4">
                            <button type="submit" :disabled="form.processing" class="w-full py-2 bg-emerald-600 text-white font-bold rounded text-xs hover:bg-emerald-700 transition disabled:bg-gray-300">
                                {{ form.processing ? 'Saving changes...' : 'Save Settings Override' }}
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
