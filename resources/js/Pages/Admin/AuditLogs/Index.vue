<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    logs: Object,
});
</script>

<template>
    <Head title="System Audit Logs" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                System Activity & Audit Logs
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <!-- TABLE GRID -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead>
                                <tr class="bg-gray-100/50 border-b text-xs text-gray-400 uppercase font-bold tracking-wider">
                                    <th class="p-4">Timestamp</th>
                                    <th class="p-4">Event / Action</th>
                                    <th class="p-4">Performed On</th>
                                    <th class="p-4">Operator</th>
                                    <th class="p-4">Changes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="log in logs.data" :key="log.id" class="border-b hover:bg-gray-50/50">
                                    <td class="p-4 font-mono text-xs text-gray-900">{{ new Date(log.created_at).toLocaleString() }}</td>
                                    <td class="p-4">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">
                                            {{ log.description }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-xs font-mono text-gray-400">
                                        {{ log.subject_type ? log.subject_type.split('\\').pop() : 'System' }} (ID: {{ log.subject_id || 'N/A' }})
                                    </td>
                                    <td class="p-4 text-xs font-bold text-gray-900">
                                        {{ log.causer ? log.causer.name : 'System / POS' }}
                                    </td>
                                    <td class="p-4 text-xs">
                                        <div v-if="log.properties" class="font-mono text-[9px] bg-gray-50 p-2 rounded border max-h-24 overflow-y-auto whitespace-pre-wrap max-w-xs">
                                            {{ JSON.stringify(log.properties, null, 2) }}
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="logs.data.length === 0">
                                    <td colspan="5" class="p-8 text-center text-gray-400 italic">No audit log records recorded yet.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- PAGINATION -->
                    <div v-if="logs.links.length > 3" class="p-4 border-t flex justify-center space-x-1">
                        <Link v-for="(link, k) in logs.links" :key="k" :href="link.url || '#'" 
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
