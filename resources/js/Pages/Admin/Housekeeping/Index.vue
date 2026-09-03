<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    units: Array,
    statuses: Array,
});

const activeUnit = ref(null);

const housekeepingForm = useForm({
    housekeeping_status: '',
    status: '',
    notes: '',
});

const openEditModal = (unit) => {
    activeUnit.value = unit;
    housekeepingForm.housekeeping_status = unit.housekeeping_status;
    housekeepingForm.status = unit.status;
    housekeepingForm.notes = unit.notes || '';
};

const closeEditModal = () => {
    activeUnit.value = null;
};

const submitStatusUpdate = () => {
    housekeepingForm.post(route('admin.housekeeping.update', activeUnit.value.id), {
        onSuccess: () => {
            closeEditModal();
            alert("Housekeeping status updated successfully.");
        }
    });
};
</script>

<template>
    <Head title="Housekeeping Manager" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Housekeeping & Maintenance
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider">Villa Cleanliness & Readiness Grid</h3>
                        <span class="text-xs text-gray-400 font-semibold font-mono">{{ units.length }} total physical villa units</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead>
                                <tr class="bg-gray-100/50 border-b text-xs text-gray-400 uppercase font-bold tracking-wider">
                                    <th class="p-4">Unit Name</th>
                                    <th class="p-4">Villa Type</th>
                                    <th class="p-4">Availability Status</th>
                                    <th class="p-4">Housekeeping Status</th>
                                    <th class="p-4">Active Alerts</th>
                                    <th class="p-4">Last Notes</th>
                                    <th class="p-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="unit in units" :key="unit.id" class="border-b hover:bg-gray-50/50">
                                    <td class="p-4 font-bold text-gray-900">{{ unit.name }}</td>
                                    <td class="p-4">{{ unit.type_name }}</td>
                                    <td class="p-4">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold uppercase"
                                            :class="{
                                                'bg-green-100 text-green-800': unit.status === 'active',
                                                'bg-red-100 text-red-800': unit.status === 'maintenance',
                                                'bg-gray-100 text-gray-800': unit.status === 'blocked',
                                            }">
                                            {{ unit.status }}
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold uppercase"
                                            :class="{
                                                'bg-emerald-100 text-emerald-800': unit.housekeeping_status === 'clean',
                                                'bg-yellow-100 text-yellow-800': unit.housekeeping_status === 'dirty',
                                                'bg-indigo-100 text-indigo-800': unit.housekeeping_status === 'inspect',
                                            }">
                                            {{ unit.housekeeping_status }}
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex flex-col gap-1">
                                            <span v-if="unit.needs_cleaning" class="px-2 py-0.5 bg-red-100 text-red-800 text-[9px] font-bold rounded-full w-max">🧹 GUEST CHECK-OUT TODAY</span>
                                            <span v-if="unit.incoming_guest" class="px-2 py-0.5 bg-indigo-100 text-indigo-800 text-[9px] font-bold rounded-full w-max">🔑 ARRIVAL TODAY</span>
                                            <span v-if="!unit.needs_cleaning && !unit.incoming_guest" class="text-xs text-gray-400 italic">No alerts</span>
                                        </div>
                                    </td>
                                    <td class="p-4 text-xs text-gray-500 italic max-w-xs truncate">{{ unit.notes || 'None' }}</td>
                                    <td class="p-4 text-right">
                                        <button @click="openEditModal(unit)" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded shadow-xs transition">
                                            Update Status
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- UPDATE MODAL -->
                <div v-if="activeUnit" class="fixed inset-0 bg-gray-900/50 flex items-center justify-center z-50 p-4">
                    <div class="bg-white p-6 rounded-lg shadow-lg border w-full max-w-sm space-y-4">
                        <h3 class="font-bold text-gray-800 border-b pb-2">Update {{ activeUnit.name }} status</h3>
                        
                        <form @submit.prevent="submitStatusUpdate" class="space-y-4">
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Housekeeping Cleanliness</label>
                                <select v-model="housekeepingForm.housekeeping_status" class="w-full text-xs rounded border-gray-300 mt-1">
                                    <option value="clean">Clean & Ready</option>
                                    <option value="dirty">Dirty (Requires clean)</option>
                                    <option value="inspect">Needs Inspection</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Availability Status</label>
                                <select v-model="housekeepingForm.status" class="w-full text-xs rounded border-gray-300 mt-1">
                                    <option value="active">Active (Sellable)</option>
                                    <option value="maintenance">Maintenance Blocked</option>
                                    <option value="blocked">Staff / Owner Occupied</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Housekeeping notes</label>
                                <textarea v-model="housekeepingForm.notes" rows="3" class="w-full text-xs rounded border-gray-300 mt-1" placeholder="Add details..."></textarea>
                            </div>
                            <div class="flex space-x-2 pt-2">
                                <button type="submit" class="flex-1 py-2 bg-emerald-600 text-white text-xs font-bold rounded hover:bg-emerald-700 transition">Save Changes</button>
                                <button type="button" @click="closeEditModal" class="flex-1 py-2 bg-gray-100 text-gray-700 text-xs font-bold rounded hover:bg-gray-200 transition">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
