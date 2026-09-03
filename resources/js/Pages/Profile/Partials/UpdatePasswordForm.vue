<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section class="space-y-6">
        <header class="border-b border-gray-100 pb-4">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-700"></span>
                <h2 class="text-base font-bold text-gray-900 uppercase tracking-wider">
                    Security & Password
                </h2>
            </div>

            <p class="mt-1 text-xs text-gray-500">
                Ensure your account maintains a strong, complex password to safeguard operational records.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="space-y-4">
            <div>
                <InputLabel for="current_password" value="Current Password" class="text-xs font-bold text-gray-700 uppercase tracking-wider" />

                <TextInput
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    class="mt-1.5 block w-full rounded-xl border-gray-300 focus:border-[#C98A3E] focus:ring-[#C98A3E] text-sm shadow-xs"
                    autocomplete="current-password"
                />

                <InputError
                    :message="form.errors.current_password"
                    class="mt-2 text-xs"
                />
            </div>

            <div>
                <InputLabel for="password" value="New Password" class="text-xs font-bold text-gray-700 uppercase tracking-wider" />

                <TextInput
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    class="mt-1.5 block w-full rounded-xl border-gray-300 focus:border-[#C98A3E] focus:ring-[#C98A3E] text-sm shadow-xs"
                    autocomplete="new-password"
                />

                <InputError :message="form.errors.password" class="mt-2 text-xs" />
            </div>

            <div>
                <InputLabel
                    for="password_confirmation"
                    value="Confirm New Password"
                    class="text-xs font-bold text-gray-700 uppercase tracking-wider"
                />

                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1.5 block w-full rounded-xl border-gray-300 focus:border-[#C98A3E] focus:ring-[#C98A3E] text-sm shadow-xs"
                    autocomplete="new-password"
                />

                <InputError
                    :message="form.errors.password_confirmation"
                    class="mt-2 text-xs"
                />
            </div>

            <div class="flex items-center gap-4 pt-2">
                <button 
                    type="submit" 
                    :disabled="form.processing"
                    class="px-6 py-2.5 bg-[#C98A3E] hover:bg-[#A76D28] text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-xs transition cursor-pointer disabled:opacity-50"
                >
                    Update Password
                </button>

                <Transition
                    enter-active-class="transition ease-in-out duration-300"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out duration-300"
                    leave-to-class="opacity-0"
                >
                    <span
                        v-if="form.recentlySuccessful"
                        class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-800 text-xs font-bold rounded-lg border border-emerald-200"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Password updated
                    </span>
                </Transition>
            </div>
        </form>
    </section>
</template>
