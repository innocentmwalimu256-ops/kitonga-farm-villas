<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section class="space-y-6">
        <header class="border-b border-gray-100 pb-4">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#C98A3E]"></span>
                <h2 class="text-base font-bold text-gray-900 uppercase tracking-wider">
                    Staff Identity & Contact Information
                </h2>
            </div>

            <p class="mt-1 text-xs text-gray-500">
                Update your administrative display name and official Kitonga estate email address.
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="space-y-5"
        >
            <div>
                <InputLabel for="name" value="Full Name / Staff Name" class="text-xs font-bold text-gray-700 uppercase tracking-wider" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1.5 block w-full rounded-xl border-gray-300 focus:border-[#C98A3E] focus:ring-[#C98A3E] text-sm shadow-xs"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2 text-xs" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Official Email Address" class="text-xs font-bold text-gray-700 uppercase tracking-wider" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1.5 block w-full rounded-xl border-gray-300 focus:border-[#C98A3E] focus:ring-[#C98A3E] text-sm shadow-xs"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2 text-xs" :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="p-4 rounded-xl bg-amber-50 border border-amber-200">
                <p class="text-xs text-amber-800">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="ml-1 font-bold underline hover:text-amber-900 focus:outline-none"
                    >
                        Click here to re-send the verification link.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-xs font-bold text-emerald-700"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4 pt-2">
                <button 
                    type="submit" 
                    :disabled="form.processing"
                    class="px-6 py-2.5 bg-[#14231C] hover:bg-[#1E3329] text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-xs transition cursor-pointer disabled:opacity-50"
                >
                    Save Changes
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
                        Saved successfully
                    </span>
                </Transition>
            </div>
        </form>
    </section>
</template>
