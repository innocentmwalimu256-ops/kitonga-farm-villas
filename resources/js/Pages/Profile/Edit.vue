<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = computed(() => usePage().props.auth.user || {});

const userInitials = computed(() => {
    if (!user.value.name) return 'KV';
    return user.value.name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
});

const memberSince = computed(() => {
    if (!user.value.created_at) return 'Active Member';
    const date = new Date(user.value.created_at);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
});
</script>

<template>
    <Head title="Staff & Admin Profile — Kitonga Farm Villas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 leading-tight">
                        My Account & Profile
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Manage your staff credentials, security credentials, and access settings.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Active Session
                    </span>
                </div>
            </div>
        </template>

        <div class="py-8 bg-gray-50/50 min-h-[calc(100vh-140px)]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                
                <!-- 1. EXECUTIVE ADMIN IDENTITY HERO CARD -->
                <div class="bg-gradient-to-r from-[#14231C] to-[#1E3329] rounded-2xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden border border-white/10">
                    <div class="absolute -right-10 -bottom-10 w-48 h-48 rounded-full bg-white/5 pointer-events-none blur-2xl"></div>
                    
                    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div class="flex items-center gap-5">
                            <!-- Avatar with Initials -->
                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-[#C98A3E] to-[#A76D28] text-white flex items-center justify-center font-serif text-2xl sm:text-3xl font-bold shadow-md border-2 border-white/20 select-none">
                                {{ userInitials }}
                            </div>

                            <div class="space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#FAF8F5]">
                                        {{ user.name }}
                                    </h1>
                                    <span class="px-2.5 py-0.5 rounded-md text-[10px] uppercase font-bold tracking-wider bg-[#C98A3E] text-white shadow-xs">
                                        {{ user.role || 'Staff / Administrator' }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-300 font-mono">
                                    {{ user.email }}
                                </p>
                                <p class="text-[11px] text-gray-400">
                                    Member on system since: <span class="text-gray-200 font-semibold">{{ memberSince }}</span>
                                </p>
                            </div>
                        </div>

                        <!-- System Security Clearances -->
                        <div class="flex flex-wrap md:flex-col items-start md:items-end gap-2 text-xs">
                            <span class="text-gray-400 text-[11px] uppercase tracking-wider font-semibold">Security Clearance</span>
                            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/10 backdrop-blur-sm border border-white/10 text-[#E6C387] font-mono text-[11px] font-bold">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                Verified Access & Activity Logging
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. TWO-COLUMN EDIT PANELS (PROFILE INFO & PASSWORD) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    
                    <!-- Left: Profile Information (7 cols) -->
                    <div class="lg:col-span-7 bg-white rounded-2xl shadow-xs border border-gray-200/80 p-6 sm:p-8">
                        <UpdateProfileInformationForm
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                        />
                    </div>

                    <!-- Right: Password Update (5 cols) -->
                    <div class="lg:col-span-5 bg-white rounded-2xl shadow-xs border border-gray-200/80 p-6 sm:p-8">
                        <UpdatePasswordForm />
                    </div>

                </div>

                <!-- 3. SYSTEM SECURITY & DANGER ZONE -->
                <div class="bg-white rounded-2xl shadow-xs border border-red-100 p-6 sm:p-8">
                    <DeleteUserForm />
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
