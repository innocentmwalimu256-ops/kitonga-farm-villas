<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

const showPassword = ref(false);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Create Account â€” Kitonga Farm Villas" />

    <div class="min-h-screen flex bg-[#FAF8F5] text-[#14231C] font-sans selection:bg-[#C98A3E] selection:text-white">

        <!-- 1. LEFT CINEMATIC BRANDING PANE (Desktop lg+) -->
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-[#14231C] text-white flex-col justify-between p-12 select-none">
            <!-- Background HD Photo with Dark Green Luxury Vignette -->
            <img 
                src="/images/IMG_0265.webp" 
                alt="Kitonga Farm Sanctuary Canopy" 
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
                        Join The Estate Sanctuary
                    </span>
                </div>

                <h2 class="font-serif text-3xl xl:text-4xl font-light text-[#FAF8F5] leading-snug tracking-tight">
                    Experience luxury residence privileges in harmony with the earth.
                </h2>

                <p class="text-sm text-gray-300 font-sans leading-relaxed">
                    Create an account to unlock tailored booking experiences, agritourism tour schedules, and priority organic harvest shipments.
                </p>

                <!-- 3 Feature Points -->
                <div class="space-y-3 pt-4 border-t border-white/10 text-xs text-gray-300">
                    <div class="flex items-center gap-3">
                        <span class="text-[#E6C387] text-base">âœ¦</span>
                        <span>Personalized guest profiles & stay preferences</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-[#E6C387] text-base">âœ¦</span>
                        <span>Instant digital booking confirmations</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-[#E6C387] text-base">âœ¦</span>
                        <span>Exclusive member invites to seasonal harvests</span>
                    </div>
                </div>
            </div>

            <!-- Bottom Location Tag -->
            <div class="relative z-10 flex justify-between items-center text-xs text-gray-400 border-t border-white/10 pt-6">
                <span>Komkonga Highlands Â· Tanga, Tanzania</span>
                <span>&copy; 2026 Kitonga Farm Villas</span>
            </div>
        </div>

        <!-- 2. RIGHT INTERACTIVE FORM CONTAINER -->
        <div class="w-full lg:w-1/2 flex flex-col justify-between p-6 sm:p-12 md:p-16 overflow-y-auto">
            
            <!-- Top Nav Back Link -->
            <div class="flex items-center justify-between">
                <Link 
                    :href="route('home')" 
                    class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-gray-500 hover:text-[#C98A3E] transition group"
                >
                    <span class="transition group-hover:-translate-x-1">â†</span>
                    <span>Return to Home</span>
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
                        MEMBERSHIP & RESERVATIONS
                    </div>
                    <h1 class="font-serif text-3xl sm:text-4xl font-bold text-[#14231C] tracking-tight">
                        Create Your Account
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 font-sans leading-relaxed">
                        Join the Kitonga community to manage your visits and farm fresh deliveries.
                    </p>
                </div>

                <!-- Main Form -->
                <form @submit.prevent="submit" class="space-y-4">
                    
                    <!-- Full Name -->
                    <div class="space-y-1.5">
                        <label for="name" class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                            Full Name
                        </label>
                        <div class="relative">
                            <svg class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <input
                                id="name"
                                type="text"
                                v-model="form.name"
                                required
                                autofocus
                                autocomplete="name"
                                placeholder="Juma Said"
                                class="w-full pl-12 pr-4 py-3.5 bg-white border border-gray-200 rounded-2xl text-sm font-sans focus:outline-none focus:ring-2 focus:ring-[#C98A3E]/40 focus:border-[#C98A3E] transition shadow-xs"
                            />
                        </div>
                        <InputError :message="form.errors.name" />
                    </div>

                    <!-- Email Address -->
                    <div class="space-y-1.5">
                        <label for="email" class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                            Email Address
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
                                autocomplete="username"
                                placeholder="name@example.com"
                                class="w-full pl-12 pr-4 py-3.5 bg-white border border-gray-200 rounded-2xl text-sm font-sans focus:outline-none focus:ring-2 focus:ring-[#C98A3E]/40 focus:border-[#C98A3E] transition shadow-xs"
                            />
                        </div>
                        <InputError :message="form.errors.email" />
                    </div>

                    <!-- Password -->
                    <div class="space-y-1.5">
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                            Password
                        </label>
                        <div class="relative">
                            <svg class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                v-model="form.password"
                                required
                                autocomplete="new-password"
                                placeholder="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢"
                                class="w-full pl-12 pr-12 py-3.5 bg-white border border-gray-200 rounded-2xl text-sm font-sans focus:outline-none focus:ring-2 focus:ring-[#C98A3E]/40 focus:border-[#C98A3E] transition shadow-xs"
                            />
                            <button 
                                type="button" 
                                @click="showPassword = !showPassword"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none text-xs font-bold"
                            >
                                {{ showPassword ? 'HIDE' : 'SHOW' }}
                            </button>
                        </div>
                        <InputError :message="form.errors.password" />
                    </div>

                    <!-- Confirm Password -->
                    <div class="space-y-1.5">
                        <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                            Confirm Password
                        </label>
                        <div class="relative">
                            <svg class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <input
                                id="password_confirmation"
                                :type="showPassword ? 'text' : 'password'"
                                v-model="form.password_confirmation"
                                required
                                autocomplete="new-password"
                                placeholder="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢"
                                class="w-full pl-12 pr-4 py-3.5 bg-white border border-gray-200 rounded-2xl text-sm font-sans focus:outline-none focus:ring-2 focus:ring-[#C98A3E]/40 focus:border-[#C98A3E] transition shadow-xs"
                            />
                        </div>
                        <InputError :message="form.errors.password_confirmation" />
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-3">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full py-4 px-6 bg-[#14231C] hover:bg-[#C98A3E] text-white text-xs font-bold uppercase tracking-widest rounded-2xl transition duration-300 flex items-center justify-center gap-2 shadow-lg hover:shadow-xl cursor-pointer disabled:opacity-50"
                        >
                            <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>{{ form.processing ? 'Creating Account...' : 'Register Account' }}</span>
                            <span v-if="!form.processing">â†’</span>
                        </button>
                    </div>

                </form>

                <!-- Divider & Sign In Link -->
                <div class="pt-6 border-t border-gray-200 text-center space-y-4">
                    <p class="text-xs text-gray-500">
                        Already have an account?
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


