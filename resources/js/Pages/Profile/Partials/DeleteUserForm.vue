<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-4">
        <header class="border-b border-red-100 pb-3 flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span>
                    <h2 class="text-base font-bold text-red-950 uppercase tracking-wider">
                        Deactivate / Delete Account
                    </h2>
                </div>
                <p class="mt-1 text-xs text-gray-500">
                    Permanently terminate this administrator profile and remove access privileges.
                </p>
            </div>
            <button 
                type="button" 
                @click="confirmUserDeletion"
                class="px-4 py-2 bg-red-50 hover:bg-red-600 text-red-700 hover:text-white border border-red-200 text-xs font-bold uppercase tracking-wider rounded-xl transition cursor-pointer"
            >
                Delete Account
            </button>
        </header>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6 sm:p-8 space-y-4">
                <div class="w-12 h-12 rounded-2xl bg-red-100 text-red-600 flex items-center justify-center font-bold text-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>

                <div class="space-y-1">
                    <h2 class="text-lg font-bold text-gray-900">
                        Confirm Account Deletion
                    </h2>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        Are you sure you want to permanently delete your administrator account? This will revoke all your staff access keys and system permissions. Please enter your password to proceed.
                    </p>
                </div>

                <div class="pt-2">
                    <InputLabel
                        for="password"
                        value="Confirm Your Password"
                        class="text-xs font-bold text-gray-700 uppercase"
                    />

                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1.5 block w-full rounded-xl border-gray-300 focus:border-red-500 focus:ring-red-500 text-sm shadow-xs"
                        placeholder="Enter password to confirm"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2 text-xs" />
                </div>

                <div class="pt-4 flex justify-end gap-3 border-t border-gray-100">
                    <SecondaryButton @click="closeModal" class="rounded-xl text-xs font-bold uppercase">
                        Cancel
                    </SecondaryButton>

                    <button
                        type="button"
                        class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-bold uppercase tracking-wider transition disabled:opacity-50"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Confirm Delete
                    </button>
                </div>
            </div>
        </Modal>
    </section>
</template>
