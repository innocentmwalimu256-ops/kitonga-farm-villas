<script setup>
import { ref } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="min-h-screen bg-[#F4F6F4] font-sans antialiased text-gray-900">
        <!-- Top Staff Navigation Bar -->
        <nav class="sticky top-0 z-40 border-b border-gray-200/80 bg-white/95 backdrop-blur-md shadow-xs">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between items-center">
                    
                    <!-- Left: Brand Logo & Desktop Nav -->
                    <div class="flex items-center gap-6">
                        <Link :href="route('dashboard')" class="flex items-center gap-2.5 group">
                            <div class="w-8 h-8 rounded-lg bg-[#14231C] text-[#C98A3E] flex items-center justify-center font-serif font-bold text-base shadow-xs">
                                K
                            </div>
                            <div class="flex flex-col">
                                <span class="font-bold text-[#14231C] text-xs tracking-wider uppercase leading-none">KITONGA</span>
                                <span class="text-[9px] font-medium text-emerald-700 tracking-widest uppercase mt-0.5">Admin Desk</span>
                            </div>
                        </Link>

                        <!-- Desktop Main Nav -->
                        <div class="hidden lg:flex items-center space-x-2 xl:space-x-4 ml-4">
                            <NavLink :href="route('dashboard')" :active="route().current('dashboard')" class="text-xs font-semibold uppercase tracking-wider">
                                Dashboard
                            </NavLink>
                            <NavLink :href="route('admin.pos.terminal')" :active="route().current('admin.pos.terminal')" class="text-xs font-semibold uppercase tracking-wider text-emerald-700 font-bold">
                                POS Terminal
                            </NavLink>
                            
                            <!-- Bookings Dropdown -->
                            <div class="relative">
                                <Dropdown align="left" width="56">
                                    <template #trigger>
                                        <button type="button" class="inline-flex items-center px-3 py-2 text-xs font-semibold uppercase tracking-wider text-gray-600 hover:text-gray-900 focus:outline-none transition cursor-pointer">
                                            <span>Bookings</span>
                                            <svg class="ms-1 h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                        </button>
                                    </template>
                                    <template #content>
                                        <div class="py-1">
                                            <DropdownLink :href="route('admin.bookings.calendar')">Calendar Grid</DropdownLink>
                                            <DropdownLink :href="route('admin.bookings.index')">Reservations List</DropdownLink>
                                            <DropdownLink :href="route('admin.bookings.create')">New Booking</DropdownLink>
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>

                            <!-- Operations Dropdown -->
                            <div class="relative">
                                <Dropdown align="left" width="56">
                                    <template #trigger>
                                        <button type="button" class="inline-flex items-center px-3 py-2 text-xs font-semibold uppercase tracking-wider text-gray-600 hover:text-gray-900 focus:outline-none transition cursor-pointer">
                                            <span>Operations</span>
                                            <svg class="ms-1 h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                        </button>
                                    </template>
                                    <template #content>
                                        <div class="py-1">
                                            <DropdownLink :href="route('admin.accommodation.index')">Accommodation Setup</DropdownLink>
                                            <DropdownLink :href="route('admin.experiences.index')">Experiences</DropdownLink>
                                            <DropdownLink :href="route('admin.housekeeping.index')">Housekeeping</DropdownLink>
                                            <DropdownLink :href="route('admin.products.index')">Inventory & Stock</DropdownLink>
                                            <DropdownLink :href="route('admin.pos.index')">POS Sales Log</DropdownLink>
                                            <DropdownLink :href="route('admin.expenses.index')">Expenses Log</DropdownLink>
                                            <DropdownLink :href="route('admin.payments.index')">Payments Registry</DropdownLink>
                                            <DropdownLink :href="route('admin.financials.report')">Financial Statement</DropdownLink>
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>

                            <!-- Administration Dropdown -->
                            <div class="relative">
                                <Dropdown align="left" width="56">
                                    <template #trigger>
                                        <button type="button" class="inline-flex items-center px-3 py-2 text-xs font-semibold uppercase tracking-wider text-gray-600 hover:text-gray-900 focus:outline-none transition cursor-pointer">
                                            <span>Admin</span>
                                            <svg class="ms-1 h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                        </button>
                                    </template>
                                    <template #content>
                                        <div class="py-1">
                                            <DropdownLink :href="route('admin.reports.index')">Reports Center</DropdownLink>
                                            <DropdownLink :href="route('admin.cms.index')">CMS Manager</DropdownLink>
                                            <DropdownLink :href="route('admin.media.index')">Media Library</DropdownLink>
                                            <DropdownLink :href="route('admin.guests.index')">Guests Registry</DropdownLink>
                                            <DropdownLink :href="route('admin.users.index')">Staff Accounts</DropdownLink>
                                            <DropdownLink :href="route('admin.audit_logs.index')">Audit Logs</DropdownLink>
                                            <DropdownLink :href="route('admin.settings.index')">Global Settings</DropdownLink>
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Quick Live Site Link & User Profile -->
                    <div class="hidden lg:flex items-center gap-3">
                        <a 
                            href="/" 
                            target="_blank" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 text-xs font-semibold text-gray-600 hover:text-[#14231C] hover:bg-gray-50 transition"
                        >
                            <span>Live Website</span>
                            <span class="text-[10px]">↗</span>
                        </a>

                        <div class="relative">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition cursor-pointer">
                                        <span>{{ $page.props.auth.user.name }}</span>
                                        <svg class="h-3.5 w-3.5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </template>
                                <template #content>
                                    <DropdownLink :href="route('profile.edit')">My Profile</DropdownLink>
                                    <DropdownLink :href="route('logout')" method="post" as="button">Log Out</DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>

                    <!-- Mobile Hamburger Button -->
                    <div class="flex items-center gap-2 lg:hidden">
                        <Link 
                            :href="route('admin.pos.terminal')" 
                            class="px-2.5 py-1.5 rounded-md bg-emerald-700 text-white text-[11px] font-bold"
                        >
                            POS
                        </Link>
                        
                        <button 
                            @click="showingNavigationDropdown = !showingNavigationDropdown" 
                            class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 focus:outline-none transition cursor-pointer"
                            aria-label="Toggle Staff Menu"
                        >
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path v-if="!showingNavigationDropdown" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                </div>
            </div>

            <!-- Mobile Navigation Drawer -->
            <div 
                v-if="showingNavigationDropdown" 
                class="lg:hidden border-t border-gray-200 bg-white px-4 pt-3 pb-6 max-h-[85vh] overflow-y-auto space-y-4 shadow-xl text-xs"
            >
                <div class="space-y-1">
                    <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')" @click="showingNavigationDropdown = false">
                        Dashboard
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('admin.pos.terminal')" :active="route().current('admin.pos.terminal')" @click="showingNavigationDropdown = false" class="text-emerald-800 font-bold">
                        POS Sales Terminal
                    </ResponsiveNavLink>
                </div>

                <!-- Bookings Group -->
                <div class="pt-2 border-t border-gray-100">
                    <div class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-gray-400">Bookings & Reservations</div>
                    <div class="space-y-1 mt-1">
                        <ResponsiveNavLink :href="route('admin.bookings.calendar')" @click="showingNavigationDropdown = false">Calendar Grid</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('admin.bookings.index')" @click="showingNavigationDropdown = false">Reservations List</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('admin.bookings.create')" @click="showingNavigationDropdown = false">New Booking</ResponsiveNavLink>
                    </div>
                </div>

                <!-- Operations Group -->
                <div class="pt-2 border-t border-gray-100">
                    <div class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-gray-400">Estate Operations</div>
                    <div class="space-y-1 mt-1">
                        <ResponsiveNavLink :href="route('admin.accommodation.index')" @click="showingNavigationDropdown = false">Accommodation Setup</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('admin.experiences.index')" @click="showingNavigationDropdown = false">Experiences</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('admin.housekeeping.index')" @click="showingNavigationDropdown = false">Housekeeping</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('admin.products.index')" @click="showingNavigationDropdown = false">Inventory & Stock</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('admin.pos.index')" @click="showingNavigationDropdown = false">POS Sales Log</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('admin.expenses.index')" @click="showingNavigationDropdown = false">Expenses Log</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('admin.payments.index')" @click="showingNavigationDropdown = false">Payments</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('admin.financials.report')" @click="showingNavigationDropdown = false">Financials</ResponsiveNavLink>
                    </div>
                </div>

                <!-- Admin Group -->
                <div class="pt-2 border-t border-gray-100">
                    <div class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-gray-400">System Admin</div>
                    <div class="space-y-1 mt-1">
                        <ResponsiveNavLink :href="route('admin.reports.index')" @click="showingNavigationDropdown = false">Reports Center</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('admin.cms.index')" @click="showingNavigationDropdown = false">CMS Manager</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('admin.media.index')" @click="showingNavigationDropdown = false">Media Library</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('admin.guests.index')" @click="showingNavigationDropdown = false">Guests Registry</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('admin.users.index')" @click="showingNavigationDropdown = false">Staff Accounts</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('admin.audit_logs.index')" @click="showingNavigationDropdown = false">Audit Logs</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('admin.settings.index')" @click="showingNavigationDropdown = false">Global Settings</ResponsiveNavLink>
                    </div>
                </div>

                <!-- Profile Options -->
                <div class="pt-3 border-t border-gray-200">
                    <div class="px-3 py-1">
                        <div class="font-bold text-gray-900">{{ $page.props.auth.user.name }}</div>
                        <div class="text-[11px] text-gray-500">{{ $page.props.auth.user.email }}</div>
                    </div>
                    <div class="mt-2 space-y-1">
                        <ResponsiveNavLink :href="route('profile.edit')" @click="showingNavigationDropdown = false">Edit Profile</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('logout')" method="post" as="button">Log Out</ResponsiveNavLink>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Header -->
        <header class="bg-white border-b border-gray-200/60 shadow-2xs" v-if="$slots.header">
            <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="pb-16">
            <slot />
        </main>
    </div>
</template>
