<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

defineProps({
    status: {
        type: String,
        default: '',
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Reset Password — Kitonga Farm Villas" />

    <div class="min-h-screen flex bg-[#FAF8F5] text-[#14231C] font-sans selection:bg-[#C98A3E] selection:text-white">

        <!-- 1. LEFT CINEMATIC BRANDING PANE (Desktop lg+) -->
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-[#14231C] text-white flex-col justify-between p-12 select-none">
            <img 
                src="/images/IMG_0289.jpg" 
                alt="Kitonga Farm Sanctuary" 
                class="absolute inset-0 w-full h-full object-cover object-center filter brightness-[0.45] contrast-[1.05] scale-105 transition duration-1000 ease-out"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-[#0E1712] via-[#14231C]/60 to-[#14231C]/70"></div>

            <!-- Top Logo -->
            <div class="relative z-10">
                <Link :href="route('home')" class="inline-flex flex-col items-start group">
                    <span class="font-serif text-3xl font-light text-[#FAF8F5] tracking-[5px] uppercase leading-none group-hover:text-[#E6C387] transition duration-300">
                        KITONGA
                    </span>
                    <span class="font-sans text-[10px] font-semibold text-[#C98A3E] tracking-[7px] uppercase leading-none mt-1.5 pl-[2px]">
                        FARMS VILLAS
                    </span>
                </Link>
            </div>

            <!-- Middle Narrative -->
            <div class="relative z-10 max-w-lg space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20">
                    <span class="w-2 h-2 rounded-full bg-[#E6C387] animate-pulse"></span>
                    <span class="text-[11px] uppercase tracking-[3px] font-bold text-[#E6C387]">
                        Account Security & Recovery
                    </span>
                </div>

                <h2 class="font-serif text-3xl xl:text-4xl font-light text-[#FAF8F5] leading-snug tracking-tight">
                    Secure access to your private sanctuary profile and reservations.
                </h2>

                <p class="text-sm text-gray-300 font-sans leading-relaxed">
                    Provide your registered email address and we will immediately send you a secure password reset link.
                </p>
            </div>

            <!-- Bottom Location Tag -->
            <div class="relative z-10 flex justify-between items-center text-xs text-gray-400 border-t border-white/10 pt-6">
                <span>Komkonga Highlands · Tanga, Tanzania</span>
                <span>&copy; 2026 Kitonga Farm Villas</span>
            </div>
        </div>

        <!-- 2. RIGHT INTERACTIVE FORM CONTAINER -->
        <div class="w-full lg:w-1/2 flex flex-col justify-between p-6 sm:p-12 md:p-16 overflow-y-auto">
            
            <!-- Top Nav Back Link -->
            <div class="flex items-center justify-between">
                <Link 
                    :href="route('login')" 
                    class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-gray-500 hover:text-[#C98A3E] transition group"
                >
                    <span class="transition group-hover:-translate-x-1">←</span>
                    <span>Back to Sign In</span>
                </Link>

                <div class="lg:hidden">
                    <Link :href="route('home')" class="flex flex-col items-end">
                        <span class="font-serif text-lg font-bold tracking-[3px] text-[#14231C]">KITONGA</span>
                        <span class="text-[8px] font-bold tracking-[4px] text-[#C98A3E]">VILLAS</span>
                    </Link>
                </div>
            </div>

            <!-- Form Content Box -->
            <div class="max-w-md w-full mx-auto my-auto py-10 space-y-8">
                
                <!-- Header -->
                <div class="space-y-2">
                    <div class="text-[11px] uppercase font-bold tracking-[3px] text-[#C98A3E]">
                        PASSWORD RECOVERY
                    </div>
                    <h1 class="font-serif text-3xl sm:text-4xl font-bold text-[#14231C] tracking-tight">
                        Reset Your Password
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 font-sans leading-relaxed">
                        Enter your email address and we'll dispatch a link allowing you to choose a new password.
                    </p>
                </div>

                <!-- Session Flash Status -->
                <div 
                    v-if="status" 
                    class="p-4 rounded-2xl bg-green-50 border border-green-200 text-xs font-bold text-green-800 flex items-center gap-2"
                >
                    <span>✓</span>
                    <span>{{ status }}</span>
                </div>

                <!-- Main Form -->
                <form @submit.prevent="submit" class="space-y-5">
                    
                    <!-- Email Address -->
                    <div class="space-y-1.5">
                        <label for="email" class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                            Registered Email Address
                        </label>
                        <div class="relative">
                            <svg class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                            <input
                                id="email"
                                type="email"
                                v-model="form.email"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="name@example.com"
                                class="w-full pl-12 pr-4 py-3.5 bg-white border border-gray-200 rounded-2xl text-sm font-sans focus:outline-none focus:ring-2 focus:ring-[#C98A3E]/40 focus:border-[#C98A3E] transition shadow-xs"
                            />
                        </div>
                        <InputError :message="form.errors.email" />
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full py-4 px-6 bg-[#14231C] hover:bg-[#C98A3E] text-white text-xs font-bold uppercase tracking-widest rounded-2xl transition duration-300 flex items-center justify-center gap-2 shadow-lg hover:shadow-xl cursor-pointer disabled:opacity-50"
                        >
                            <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>{{ form.processing ? 'Dispatching Link...' : 'Email Reset Link' }}</span>
                            <span v-if="!form.processing">→</span>
                        </button>
                    </div>

                </form>

                <!-- Footer Back to Login -->
                <div class="pt-6 border-t border-gray-200 text-center">
                    <p class="text-xs text-gray-500">
                        Remember your password?
                        <Link 
                            :href="route('login')" 
                            class="font-bold text-[#14231C] hover:text-[#C98A3E] underline ml-1 transition"
                        >
                            Sign In Here
                        </Link>
                    </p>
                </div>

            </div>

            <!-- Footer -->
            <div class="text-center text-xs text-gray-400 pt-8">
                &copy; 2026 Kitonga Farm Villas Sanctuary. All rights reserved.
            </div>

        </div>

    </div>
</template>

