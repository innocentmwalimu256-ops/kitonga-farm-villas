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
    <div class="min-h-screen bg-[#F4F6F4] font-sans antialiased text-gray-900 flex flex-col justify-between">
        <div>
            <!-- Top Staff Navigation Bar -->
            <nav class="sticky top-0 z-40 border-b border-gray-200/80 bg-white/95 backdrop-blur-md shadow-xs">
                <div class="mx-auto max-w-7xl px-3 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between items-center">
                        
                        <!-- Left: Brand Logo & Desktop Nav -->
                        <div class="flex items-center gap-4 sm:gap-6">
                            <Link :href="route('dashboard')" class="flex items-center gap-2.5 group">
                                <div class="w-9 h-9 rounded-xl bg-[#14231C] text-[#C98A3E] flex items-center justify-center font-serif font-bold text-base shadow-sm ring-1 ring-[#C98A3E]/30">
                                    K
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-bold text-[#14231C] text-xs sm:text-sm tracking-wider uppercase leading-none font-serif">KITONGA</span>
                                    <span class="text-[9px] font-bold text-emerald-700 tracking-widest uppercase mt-0.5">Admin Desk</span>
                                </div>
                            </Link>

                            <!-- Desktop Main Nav -->
                            <div class="hidden lg:flex items-center space-x-2 xl:space-x-3 ml-2">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')" class="text-xs font-bold uppercase tracking-wider">
                                    Dashboard
                                </NavLink>
                                <NavLink :href="route('admin.pos.terminal')" :active="route().current('admin.pos.terminal')" class="text-xs font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50/80 px-2.5 py-1 rounded-md border border-emerald-200">
                                    POS Terminal
                                </NavLink>
                                
                                <!-- Bookings Dropdown -->
                                <div class="relative">
                                    <Dropdown align="left" width="56">
                                        <template #trigger>
                                            <button type="button" class="inline-flex items-center px-2.5 py-2 text-xs font-bold uppercase tracking-wider text-gray-600 hover:text-gray-900 focus:outline-none transition cursor-pointer">
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
                                            <button type="button" class="inline-flex items-center px-2.5 py-2 text-xs font-bold uppercase tracking-wider text-gray-600 hover:text-gray-900 focus:outline-none transition cursor-pointer">
                                                <span>Operations</span>
                                                <svg class="ms-1 h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                        </template>
                                        <template #content>
                                            <div class="py-1">
                                                <DropdownLink :href="route('admin.accommodation.index')">Accommodation Setup</DropdownLink>
                                                <DropdownLink :href="route('admin.experiences.index')">Experiences</DropdownLink>
                                                <DropdownLink :href="route('admin.housekeeping.index')">Housekeeping</DropdownLink>
                                                <DropdownLink :href="route('admin.products.index')">Products & Stock</DropdownLink>
                                                <DropdownLink :href="route('admin.pos.index')">POS Sales Log</DropdownLink>
                                                <DropdownLink :href="route('admin.expenses.index')">Hotel & Farm Expenses</DropdownLink>
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
                                            <button type="button" class="inline-flex items-center px-2.5 py-2 text-xs font-bold uppercase tracking-wider text-gray-600 hover:text-gray-900 focus:outline-none transition cursor-pointer">
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

                        <!-- Right: Quick Live Site Link & User Profile (Desktop) -->
                        <div class="hidden lg:flex items-center gap-3">
                            <a 
                                href="/" 
                                target="_blank" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 text-xs font-semibold text-gray-600 hover:text-[#14231C] hover:bg-gray-50 transition shadow-2xs"
                            >
                                <span>Live Website</span>
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>

                            <div class="relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition cursor-pointer shadow-2xs">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
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

                        <!-- Mobile Top Header Actions -->
                        <div class="flex items-center gap-2 lg:hidden">
                            <Link 
                                :href="route('admin.pos.terminal')" 
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold shadow-2xs"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                POS
                            </Link>

                            <a 
                                href="/" 
                                target="_blank" 
                                class="p-2 rounded-lg border border-gray-200 text-gray-600 active:bg-gray-100"
                                title="View Live Website"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                            
                            <button 
                                @click="showingNavigationDropdown = !showingNavigationDropdown" 
                                class="p-2 rounded-lg text-gray-700 hover:bg-gray-100 active:bg-gray-200 focus:outline-none transition cursor-pointer"
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
                    class="lg:hidden border-t border-gray-200 bg-white px-4 pt-3 pb-8 max-h-[80vh] overflow-y-auto space-y-4 shadow-2xl text-xs divide-y divide-gray-100 animate-in fade-in duration-150"
                >
                    <!-- Quick Core Links -->
                    <div class="space-y-1.5 pt-1">
                        <Link 
                            :href="route('dashboard')" 
                            @click="showingNavigationDropdown = false"
                            class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg font-bold text-sm transition"
                            :class="route().current('dashboard') ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'text-gray-800 hover:bg-gray-50'"
                        >
                            <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            Dashboard Command Center
                        </Link>

                        <Link 
                            :href="route('admin.pos.terminal')" 
                            @click="showingNavigationDropdown = false"
                            class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg font-bold text-sm bg-emerald-700 text-white shadow-sm"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            Open POS Terminal
                        </Link>
                    </div>

                    <!-- Bookings Group -->
                    <div class="pt-3">
                        <div class="px-3 py-1 text-[10px] font-extrabold uppercase tracking-wider text-emerald-800 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Bookings & Reservations
                        </div>
                        <div class="grid grid-cols-2 gap-1.5 mt-2">
                            <Link :href="route('admin.bookings.calendar')" @click="showingNavigationDropdown = false" class="px-3 py-2 rounded-lg bg-gray-50 hover:bg-gray-100 font-semibold text-gray-700">Calendar Grid</Link>
                            <Link :href="route('admin.bookings.index')" @click="showingNavigationDropdown = false" class="px-3 py-2 rounded-lg bg-gray-50 hover:bg-gray-100 font-semibold text-gray-700">Reservations</Link>
                            <Link :href="route('admin.bookings.create')" @click="showingNavigationDropdown = false" class="col-span-2 px-3 py-2 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold text-center">+ New Reservation</Link>
                        </div>
                    </div>

                    <!-- Operations Group -->
                    <div class="pt-3">
                        <div class="px-3 py-1 text-[10px] font-extrabold uppercase tracking-wider text-emerald-800 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            Estate & Farm Operations
                        </div>
                        <div class="grid grid-cols-2 gap-1.5 mt-2">
                            <Link :href="route('admin.products.index')" @click="showingNavigationDropdown = false" class="px-3 py-2 rounded-lg bg-gray-50 hover:bg-gray-100 font-semibold text-gray-700">Products & Stock</Link>
                            <Link :href="route('admin.expenses.index')" @click="showingNavigationDropdown = false" class="px-3 py-2 rounded-lg bg-gray-50 hover:bg-gray-100 font-semibold text-gray-700">Farm Expenses</Link>
                            <Link :href="route('admin.pos.index')" @click="showingNavigationDropdown = false" class="px-3 py-2 rounded-lg bg-gray-50 hover:bg-gray-100 font-semibold text-gray-700">POS Sales Log</Link>
                            <Link :href="route('admin.payments.index')" @click="showingNavigationDropdown = false" class="px-3 py-2 rounded-lg bg-gray-50 hover:bg-gray-100 font-semibold text-gray-700">Payments Log</Link>
                            <Link :href="route('admin.accommodation.index')" @click="showingNavigationDropdown = false" class="px-3 py-2 rounded-lg bg-gray-50 hover:bg-gray-100 font-semibold text-gray-700">Villas & Rooms</Link>
                            <Link :href="route('admin.experiences.index')" @click="showingNavigationDropdown = false" class="px-3 py-2 rounded-lg bg-gray-50 hover:bg-gray-100 font-semibold text-gray-700">Experiences</Link>
                            <Link :href="route('admin.housekeeping.index')" @click="showingNavigationDropdown = false" class="px-3 py-2 rounded-lg bg-gray-50 hover:bg-gray-100 font-semibold text-gray-700">Housekeeping</Link>
                            <Link :href="route('admin.financials.report')" @click="showingNavigationDropdown = false" class="px-3 py-2 rounded-lg bg-gray-50 hover:bg-gray-100 font-semibold text-gray-700">Financials</Link>
                        </div>
                    </div>

                    <!-- Admin Group -->
                    <div class="pt-3">
                        <div class="px-3 py-1 text-[10px] font-extrabold uppercase tracking-wider text-emerald-800 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            System Admin & Settings
                        </div>
                        <div class="grid grid-cols-2 gap-1.5 mt-2">
                            <Link :href="route('admin.reports.index')" @click="showingNavigationDropdown = false" class="px-3 py-2 rounded-lg bg-gray-50 hover:bg-gray-100 font-semibold text-gray-700">Reports Center</Link>
                            <Link :href="route('admin.cms.index')" @click="showingNavigationDropdown = false" class="px-3 py-2 rounded-lg bg-gray-50 hover:bg-gray-100 font-semibold text-gray-700">CMS Manager</Link>
                            <Link :href="route('admin.media.index')" @click="showingNavigationDropdown = false" class="px-3 py-2 rounded-lg bg-gray-50 hover:bg-gray-100 font-semibold text-gray-700">Media Library</Link>
                            <Link :href="route('admin.guests.index')" @click="showingNavigationDropdown = false" class="px-3 py-2 rounded-lg bg-gray-50 hover:bg-gray-100 font-semibold text-gray-700">Guests</Link>
                            <Link :href="route('admin.users.index')" @click="showingNavigationDropdown = false" class="px-3 py-2 rounded-lg bg-gray-50 hover:bg-gray-100 font-semibold text-gray-700">Staff Accounts</Link>
                            <Link :href="route('admin.settings.index')" @click="showingNavigationDropdown = false" class="px-3 py-2 rounded-lg bg-gray-50 hover:bg-gray-100 font-semibold text-gray-700">Settings</Link>
                        </div>
                    </div>

                    <!-- Profile Options -->
                    <div class="pt-3">
                        <div class="px-3 py-2 bg-gray-50 rounded-xl flex items-center justify-between">
                            <div>
                                <div class="font-bold text-gray-900 text-xs">{{ $page.props.auth.user.name }}</div>
                                <div class="text-[10px] text-gray-500">{{ $page.props.auth.user.email }}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <Link :href="route('profile.edit')" @click="showingNavigationDropdown = false" class="px-2.5 py-1.5 rounded-lg bg-white border border-gray-200 text-[11px] font-bold text-gray-700">Profile</Link>
                                <Link :href="route('logout')" method="post" as="button" class="px-2.5 py-1.5 rounded-lg bg-red-50 text-red-600 text-[11px] font-bold">Logout</Link>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Header -->
            <header class="bg-white border-b border-gray-200/70 shadow-2xs" v-if="$slots.header">
                <div class="mx-auto max-w-7xl px-3 py-4 sm:px-6 sm:py-5 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="pb-20 lg:pb-12">
                <slot />
            </main>
        </div>

        <!-- Sticky Mobile Bottom App Bar -->
        <div class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-gray-200/80 shadow-lg px-2 py-1.5 flex items-center justify-around">
            <!-- Dashboard -->
            <Link 
                :href="route('dashboard')" 
                class="flex flex-col items-center justify-center p-1.5 rounded-xl transition min-w-[56px]"
                :class="route().current('dashboard') ? 'text-emerald-800 font-bold bg-emerald-50/80' : 'text-gray-500 hover:text-gray-900'"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span class="text-[9px] mt-0.5">Home</span>
            </Link>

            <!-- POS Register -->
            <Link 
                :href="route('admin.pos.terminal')" 
                class="flex flex-col items-center justify-center p-1.5 rounded-xl transition min-w-[56px]"
                :class="route().current('admin.pos.terminal') ? 'text-emerald-800 font-bold bg-emerald-50/80' : 'text-gray-500 hover:text-gray-900'"
            >
                <div class="relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <span class="text-[9px] mt-0.5">POS</span>
            </Link>

            <!-- Bookings -->
            <Link 
                :href="route('admin.bookings.index')" 
                class="flex flex-col items-center justify-center p-1.5 rounded-xl transition min-w-[56px]"
                :class="route().current('admin.bookings.*') ? 'text-emerald-800 font-bold bg-emerald-50/80' : 'text-gray-500 hover:text-gray-900'"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="text-[9px] mt-0.5">Bookings</span>
            </Link>

            <!-- Products & Stock -->
            <Link 
                :href="route('admin.products.index')" 
                class="flex flex-col items-center justify-center p-1.5 rounded-xl transition min-w-[56px]"
                :class="route().current('admin.products.*') ? 'text-emerald-800 font-bold bg-emerald-50/80' : 'text-gray-500 hover:text-gray-900'"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span class="text-[9px] mt-0.5">Stock</span>
            </Link>

            <!-- Menu Drawer Button -->
            <button 
                @click="showingNavigationDropdown = !showingNavigationDropdown" 
                class="flex flex-col items-center justify-center p-1.5 rounded-xl transition min-w-[56px] text-gray-500 hover:text-gray-900"
                :class="showingNavigationDropdown ? 'text-emerald-800 font-bold bg-emerald-50/80' : ''"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <span class="text-[9px] mt-0.5">Menu</span>
            </button>
        </div>
    </div>
</template>
