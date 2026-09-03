<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    users: Array,
    roles: Array,
});

const showCreateForm = ref(false);
const activeEditUser = ref(null);

const userForm = useForm({
    name: '',
    email: '',
    password: '',
    role: '',
});

const editUserForm = useForm({
    name: '',
    email: '',
    role: '',
});

const submitUser = () => {
    userForm.post(route('admin.users.store'), {
        onSuccess: () => {
            userForm.reset();
            showCreateForm.value = false;
            alert('Staff account created.');
        }
    });
};

const openEditModal = (user) => {
    activeEditUser.value = user;
    editUserForm.name = user.name;
    editUserForm.email = user.email;
    editUserForm.role = user.roles.length > 0 ? user.roles[0].name : '';
};

const closeEditModal = () => {
    activeEditUser.value = null;
};

const submitUpdateUser = () => {
    editUserForm.post(route('admin.users.update', activeEditUser.value.id), {
        onSuccess: () => {
            closeEditModal();
            alert('Staff account updated successfully.');
        }
    });
};
</script>

<template>
    <Head title="Staff & Roles Panel" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Staff Accounts & Permissions (RBAC)
                </h2>
                <button @click="showCreateForm = !showCreateForm" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded shadow-xs transition">
                    {{ showCreateForm ? 'Close Form' : 'Create Staff Account' }}
                </button>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <!-- CREATE FORM -->
                <div v-if="showCreateForm" class="bg-white p-6 rounded-lg border border-gray-100 shadow-sm space-y-4">
                    <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider">New Staff Credentials</h3>
                    <form @submit.prevent="submitUser" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Staff Name</label>
                            <input v-model="userForm.name" type="text" required class="w-full text-xs rounded border-gray-300 mt-1" />
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Email Address</label>
                            <input v-model="userForm.email" type="email" required class="w-full text-xs rounded border-gray-300 mt-1" />
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Password</label>
                            <input v-model="userForm.password" type="password" required class="w-full text-xs rounded border-gray-300 mt-1" />
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Assigned Role</label>
                            <select v-model="userForm.role" required class="w-full text-xs rounded border-gray-300 mt-1">
                                <option value="" disabled>Select role</option>
                                <option v-for="r in roles" :key="r.id" :value="r.name">{{ r.name }}</option>
                            </select>
                        </div>
                        <div class="md:col-span-4">
                            <button type="submit" class="w-full py-2 bg-emerald-600 text-white font-bold rounded text-xs hover:bg-emerald-700 transition">Save Staff User</button>
                        </div>
                    </form>
                </div>

                <!-- TABLE GRID -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead>
                                <tr class="bg-gray-100/50 border-b text-xs text-gray-400 uppercase font-bold tracking-wider">
                                    <th class="p-4">Name</th>
                                    <th class="p-4">Email</th>
                                    <th class="p-4">Role Assigned</th>
                                    <th class="p-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in users" :key="user.id" class="border-b hover:bg-gray-50/50">
                                    <td class="p-4 font-bold text-gray-900 text-xs">{{ user.name }}</td>
                                    <td class="p-4 text-xs font-mono">{{ user.email }}</td>
                                    <td class="p-4">
                                        <span v-for="role in user.roles" :key="role.id" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 mr-1">
                                            {{ role.name }}
                                        </span>
                                        <span v-if="user.roles.length === 0" class="text-[10px] text-gray-400 italic">No role</span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <button @click="openEditModal(user)" class="px-2 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold rounded shadow-xs transition">
                                            Edit Staff
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- EDIT MODAL -->
                <div v-if="activeEditUser" class="fixed inset-0 bg-gray-900/50 flex items-center justify-center z-50 p-4">
                    <div class="bg-white p-6 rounded-lg shadow-lg border w-full max-w-sm space-y-4">
                        <h3 class="font-bold text-gray-800 border-b pb-2">Edit Staff Account: {{ activeEditUser.name }}</h3>
                        
                        <form @submit.prevent="submitUpdateUser" class="space-y-4">
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Staff Name</label>
                                <input v-model="editUserForm.name" type="text" required class="w-full text-xs rounded border-gray-300 mt-1" />
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Email Address</label>
                                <input v-model="editUserForm.email" type="email" required class="w-full text-xs rounded border-gray-300 mt-1" />
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Role</label>
                                <select v-model="editUserForm.role" required class="w-full text-xs rounded border-gray-300 mt-1">
                                    <option v-for="r in roles" :key="r.id" :value="r.name">{{ r.name }}</option>
                                </select>
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
