<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    types: Array,
    blocks: Array,
});

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-TZ', { style: 'currency', currency: 'TZS', maximumFractionDigits: 0 }).format(val);
};

const showCreateTypeForm = ref(false);
const showCreateUnitForm = ref(false);
const showCreateBlockForm = ref(false);
const activeEditType = ref(null);

const typeForm = useForm({
    name: '',
    base_price: '',
    capacity: 2,
    bedrooms: 1,
    beds: 1,
    bathrooms: 1,
    has_interior_kitchen: false,
    minimum_stay: 1,
    description: '',
});

const editTypeForm = useForm({
    name: '',
    base_price: '',
    capacity: 2,
    bedrooms: 1,
    beds: 1,
    bathrooms: 1,
    has_interior_kitchen: false,
    minimum_stay: 1,
    description: '',
    active: true,
});

const unitForm = useForm({
    accommodation_type_id: '',
    name: '',
    status: 'active',
});

const blockForm = useForm({
    accommodation_unit_id: '',
    start_date: '',
    end_date: '',
    reason: '',
});

const submitType = () => {
    typeForm.post(route('admin.accommodation.types.store'), {
        onSuccess: () => {
            typeForm.reset();
            showCreateTypeForm.value = false;
            alert('Accommodation Type registered.');
        }
    });
};

const openEditTypeModal = (type) => {
    activeEditType.value = type;
    editTypeForm.name = type.name;
    editTypeForm.base_price = type.base_price;
    editTypeForm.capacity = type.capacity;
    editTypeForm.bedrooms = type.bedrooms;
    editTypeForm.beds = type.beds;
    editTypeForm.bathrooms = type.bathrooms;
    editTypeForm.has_interior_kitchen = !!type.has_interior_kitchen;
    editTypeForm.minimum_stay = type.minimum_stay;
    editTypeForm.description = type.description || '';
    editTypeForm.active = !!type.active;
};

const closeEditTypeModal = () => {
    activeEditType.value = null;
};

const submitUpdateType = () => {
    editTypeForm.post(route('admin.accommodation.types.update', activeEditType.value.id), {
        onSuccess: () => {
            closeEditTypeModal();
            alert('Accommodation Type updated.');
        }
    });
};

const submitUnit = () => {
    unitForm.post(route('admin.accommodation.units.store'), {
        onSuccess: () => {
            unitForm.reset();
            showCreateUnitForm.value = false;
            alert('Physical unit registered.');
        }
    });
};

const submitBlock = () => {
    blockForm.post(route('admin.accommodation.blocks.store'), {
        onSuccess: () => {
            blockForm.reset();
            showCreateBlockForm.value = false;
            alert('Blackout date range registered.');
        }
    });
};

const removeBlock = (id) => {
    if (confirm('Are you sure you want to remove this blackout block and unblock the dates?')) {
        router.delete(route('admin.accommodation.blocks.destroy', id), {
            onSuccess: () => {
                alert('Blackout date block removed successfully.');
            }
        });
    }
};

const toggleUnitStatus = (unit, newStatus) => {
    useForm({
        status: newStatus,
        notes: unit.notes
    }).post(route('admin.accommodation.units.update', unit.id), {
        onSuccess: () => {
            alert(`Physical unit status changed to ${newStatus}`);
        }
    });
};
</script>

<template>
    <Head title="Accommodation & Rooms Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap justify-between items-center gap-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Villas & Accommodation Management
                </h2>
                <div class="flex space-x-2">
                    <button @click="showCreateTypeForm = !showCreateTypeForm" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded shadow-xs transition">
                        New Villa Model
                    </button>
                    <button @click="showCreateUnitForm = !showCreateUnitForm" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded shadow-xs transition">
                        Register Physical Unit
                    </button>
                    <button @click="showCreateBlockForm = !showCreateBlockForm" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded shadow-xs transition">
                        Add Blackout Block
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <!-- 1. CREATE VILLA TYPE FORM -->
                <div v-if="showCreateTypeForm" class="bg-white p-6 rounded-lg border border-gray-100 shadow-sm space-y-4">
                    <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider">New Villa Model Details</h3>
                    <form @submit.prevent="submitType" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="md:col-span-2">
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Model Name</label>
                            <input v-model="typeForm.name" type="text" required class="w-full text-xs rounded border-gray-300 mt-1" />
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Base Price (TZS)</label>
                            <input v-model="typeForm.base_price" type="number" required class="w-full text-xs rounded border-gray-300 mt-1" />
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Minimum Stay (Nights)</label>
                            <input v-model="typeForm.minimum_stay" type="number" required min="1" class="w-full text-xs rounded border-gray-300 mt-1" />
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Max Occupancy</label>
                            <input v-model="typeForm.capacity" type="number" required min="1" class="w-full text-xs rounded border-gray-300 mt-1" />
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Bedrooms</label>
                            <input v-model="typeForm.bedrooms" type="number" required min="0" class="w-full text-xs rounded border-gray-300 mt-1" />
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Beds</label>
                            <input v-model="typeForm.beds" type="number" required min="1" class="w-full text-xs rounded border-gray-300 mt-1" />
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Bathrooms</label>
                            <input v-model="typeForm.bathrooms" type="number" required min="1" class="w-full text-xs rounded border-gray-300 mt-1" />
                        </div>
                        <div class="md:col-span-4 flex items-center space-x-2">
                            <input v-model="typeForm.has_interior_kitchen" type="checkbox" id="kitchen_check" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" />
                            <label for="kitchen_check" class="text-xs font-bold text-gray-700">Has Interior Kitchen Setup</label>
                        </div>
                        <div class="md:col-span-4">
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Description</label>
                            <textarea v-model="typeForm.description" rows="2" class="w-full text-xs rounded border-gray-300 mt-1"></textarea>
                        </div>
                        <div class="md:col-span-4">
                            <button type="submit" class="w-full py-2 bg-emerald-600 text-white font-bold rounded text-xs hover:bg-emerald-700 transition">Save Model</button>
                        </div>
                    </form>
                </div>

                <!-- 2. CREATE UNIT FORM -->
                <div v-if="showCreateUnitForm" class="bg-white p-6 rounded-lg border border-gray-100 shadow-sm space-y-4">
                    <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider">Register Physical Unit</h3>
                    <form @submit.prevent="submitUnit" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Villa Model Type</label>
                            <select v-model="unitForm.accommodation_type_id" required class="w-full text-xs rounded border-gray-300 mt-1">
                                <option value="" disabled>Select model</option>
                                <option v-for="t in types" :key="t.id" :value="t.id">{{ t.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Unit Name / ID</label>
                            <input v-model="unitForm.name" type="text" required placeholder="e.g. V3 - Luxury Villa 3" class="w-full text-xs rounded border-gray-300 mt-1" />
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Initial Status</label>
                            <select v-model="unitForm.status" class="w-full text-xs rounded border-gray-300 mt-1">
                                <option value="active">Active (Sellable)</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="blocked">Owner Blocked</option>
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <button type="submit" class="w-full py-2 bg-indigo-600 text-white font-bold rounded text-xs hover:bg-indigo-700 transition">Register Unit</button>
                        </div>
                    </form>
                </div>

                <!-- 3. CREATE BLACKOUT BLOCK FORM -->
                <div v-if="showCreateBlockForm" class="bg-white p-6 rounded-lg border border-gray-100 shadow-sm space-y-4">
                    <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider">Create Blackout Date Block</h3>
                    <form @submit.prevent="submitBlock" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Physical Unit</label>
                            <select v-model="blockForm.accommodation_unit_id" required class="w-full text-xs rounded border-gray-300 mt-1">
                                <option value="" disabled>Select unit</option>
                                <optgroup v-for="t in types" :key="t.id" :label="t.name">
                                    <option v-for="u in t.units" :key="u.id" :value="u.id">{{ u.name }}</option>
                                </optgroup>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Start Date</label>
                            <input v-model="blockForm.start_date" type="date" required class="w-full text-xs rounded border-gray-300 mt-1" />
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">End Date</label>
                            <input v-model="blockForm.end_date" type="date" required class="w-full text-xs rounded border-gray-300 mt-1" />
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Block Reason</label>
                            <input v-model="blockForm.reason" type="text" required placeholder="e.g. Deep cleaning, painting" class="w-full text-xs rounded border-gray-300 mt-1" />
                        </div>
                        <div class="md:col-span-4">
                            <button type="submit" class="w-full py-2 bg-red-600 text-white font-bold rounded text-xs hover:bg-red-700 transition">Save Blackout Range</button>
                        </div>
                    </form>
                </div>

                <!-- 4. ACCOMMODATION MODELS GRID -->
                <div v-for="type in types" :key="type.id" class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                        <div>
                            <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider">{{ type.name }}</h3>
                            <p class="text-xs text-gray-400 mt-1">Base Price: <span class="font-mono font-bold text-emerald-600">{{ formatCurrency(type.base_price) }}/night</span> | Min Stay: {{ type.minimum_stay }} night(s)</p>
                        </div>
                        <div class="flex space-x-2">
                            <button @click="openEditTypeModal(type)" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded transition">
                                Edit Details
                            </button>
                        </div>
                    </div>

                    <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- DETAILS -->
                        <div class="space-y-2">
                            <h4 class="text-xs font-bold text-gray-400 uppercase">Villa Specifications</h4>
                            <ul class="text-xs text-gray-600 space-y-1">
                                <li>🚪 Capacity: {{ type.capacity }} guests max</li>
                                <li>🛌 Bedrooms: {{ type.bedrooms }} | Beds: {{ type.beds }}</li>
                                <li>🚿 Bathrooms: {{ type.bathrooms }}</li>
                                <li>🍳 Kitchen: {{ type.has_interior_kitchen ? 'Yes' : 'No' }}</li>
                                <li>🟢 Status: {{ type.active ? 'Active (Listed)' : 'Inactive (Hidden)' }}</li>
                            </ul>
                            <p class="text-xs text-gray-500 italic mt-2">{{ type.description || 'No description provided.' }}</p>
                        </div>

                        <!-- UNITS LIST -->
                        <div class="md:col-span-2 space-y-2">
                            <h4 class="text-xs font-bold text-gray-400 uppercase">Physical Room Units</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div v-for="unit in type.units" :key="unit.id" class="p-3 border rounded flex justify-between items-center bg-gray-50/50">
                                    <div>
                                        <div class="text-xs font-bold text-gray-900">{{ unit.name }}</div>
                                        <div class="text-[10px] uppercase font-bold mt-1"
                                            :class="{
                                                'text-green-600': unit.status === 'active',
                                                'text-red-500': unit.status === 'maintenance',
                                                'text-gray-500': unit.status === 'blocked',
                                            }">
                                            {{ unit.status }}
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <button v-if="unit.status !== 'active'" @click="toggleUnitStatus(unit, 'active')" class="px-2 py-0.5 bg-green-600 hover:bg-green-700 text-white text-[9px] font-bold rounded">Set Active</button>
                                        <button v-if="unit.status !== 'maintenance'" @click="toggleUnitStatus(unit, 'maintenance')" class="px-2 py-0.5 bg-red-600 hover:bg-red-700 text-white text-[9px] font-bold rounded">Set Maint</button>
                                        <button v-if="unit.status !== 'blocked'" @click="toggleUnitStatus(unit, 'blocked')" class="px-2 py-0.5 bg-gray-500 hover:bg-gray-600 text-white text-[9px] font-bold rounded">Set Block</button>
                                    </div>
                                </div>
                                <div v-if="type.units.length === 0" class="col-span-full py-4 text-center text-gray-400 italic text-xs">
                                    No physical units registered under this model.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. ACTIVE BLACKOUT DATE BLOCKS LIST -->
                <div class="bg-white rounded-xl shadow-xs border border-gray-200 overflow-hidden space-y-3 p-6">
                    <div class="flex justify-between items-center border-b border-gray-100 pb-3">
                        <div>
                            <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider">Active Blackout & Maintenance Blocks</h3>
                            <p class="text-xs text-gray-400 mt-0.5">Rooms and dates currently blocked from online guest reservations.</p>
                        </div>
                        <span class="text-xs font-bold font-mono px-2.5 py-1 rounded bg-red-50 text-red-700 border border-red-200">
                            {{ blocks.length }} Active Block(s)
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="bg-gray-50 border-b text-gray-400 uppercase font-bold tracking-wider">
                                    <th class="p-3">Physical Room Unit</th>
                                    <th class="p-3">Start Date</th>
                                    <th class="p-3">End Date</th>
                                    <th class="p-3">Reason</th>
                                    <th class="p-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="block in blocks" :key="block.id" class="border-b hover:bg-gray-50/60 transition">
                                    <td class="p-3 font-bold text-gray-900">{{ block.unit?.name || 'Unit #' + block.accommodation_unit_id }}</td>
                                    <td class="p-3 font-mono font-semibold text-gray-700">{{ block.start_date }}</td>
                                    <td class="p-3 font-mono font-semibold text-gray-700">{{ block.end_date }}</td>
                                    <td class="p-3 text-gray-600 italic">{{ block.reason }}</td>
                                    <td class="p-3 text-right">
                                        <button 
                                            type="button" 
                                            @click="removeBlock(block.id)" 
                                            class="px-3 py-1 bg-red-50 hover:bg-red-600 text-red-700 hover:text-white border border-red-200 rounded font-bold text-[11px] uppercase transition cursor-pointer"
                                        >
                                            Remove Block
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="blocks.length === 0">
                                    <td colspan="5" class="p-8 text-center text-gray-400 italic">
                                        No active date blackout blocks found. All rooms are open according to their unit status.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- EDIT TYPE MODAL -->
                <div v-if="activeEditType" class="fixed inset-0 bg-gray-900/50 flex items-center justify-center z-50 p-4">
                    <div class="bg-white p-6 rounded-lg shadow-lg border w-full max-w-xl space-y-4">
                        <h3 class="font-bold text-gray-800 border-b pb-2">Edit Villa Model: {{ activeEditType.name }}</h3>
                        
                        <form @submit.prevent="submitUpdateType" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Model Name</label>
                                <input v-model="editTypeForm.name" type="text" required class="w-full text-xs rounded border-gray-300 mt-1" />
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Base Price (TZS)</label>
                                <input v-model="editTypeForm.base_price" type="number" required class="w-full text-xs rounded border-gray-300 mt-1" />
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Minimum Stay (Nights)</label>
                                <input v-model="editTypeForm.minimum_stay" type="number" required min="1" class="w-full text-xs rounded border-gray-300 mt-1" />
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Max Occupancy</label>
                                <input v-model="editTypeForm.capacity" type="number" required min="1" class="w-full text-xs rounded border-gray-300 mt-1" />
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Bedrooms</label>
                                <input v-model="editTypeForm.bedrooms" type="number" required min="0" class="w-full text-xs rounded border-gray-300 mt-1" />
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Beds</label>
                                <input v-model="editTypeForm.beds" type="number" required min="1" class="w-full text-xs rounded border-gray-300 mt-1" />
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Bathrooms</label>
                                <input v-model="editTypeForm.bathrooms" type="number" required min="1" class="w-full text-xs rounded border-gray-300 mt-1" />
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Status List</label>
                                <select v-model="editTypeForm.active" class="w-full text-xs rounded border-gray-300 mt-1">
                                    <option :value="true">Listed (Active)</option>
                                    <option :value="false">Hidden (Inactive)</option>
                                </select>
                            </div>
                            <div class="md:col-span-2 flex items-center space-x-2">
                                <input v-model="editTypeForm.has_interior_kitchen" type="checkbox" id="edit_kitchen_check" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" />
                                <label for="edit_kitchen_check" class="text-xs font-bold text-gray-700">Has Interior Kitchen Setup</label>
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Description</label>
                                <textarea v-model="editTypeForm.description" rows="2" class="w-full text-xs rounded border-gray-300 mt-1"></textarea>
                            </div>
                            <div class="md:col-span-2 flex space-x-2 pt-2">
                                <button type="submit" class="flex-1 py-2 bg-emerald-600 text-white text-xs font-bold rounded hover:bg-emerald-700 transition">Save Changes</button>
                                <button type="button" @click="closeEditTypeModal" class="flex-1 py-2 bg-gray-100 text-gray-700 text-xs font-bold rounded hover:bg-gray-200 transition">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
