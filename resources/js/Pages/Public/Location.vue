<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    settings: Object,
});
</script>

<template>
    <Head title="Journey to Tanga" />

    <div class="bg-[#FAF8F5] text-[#2C3E2B] font-serif min-h-screen">
        
        <!-- HEADER -->
        <header class="bg-[#2C3E2B] px-6 py-6 md:px-12 flex justify-between items-center text-white">
            <Link :href="route('home')" class="flex flex-col items-center md:items-start text-center md:text-left group">
                <span class="font-['Cormorant_Garamond'] text-lg md:text-2xl font-light text-[#F5F1E8] tracking-[4px] uppercase leading-none transition group-hover:text-[#C8A96B] duration-300">
                    KITONGA
                </span>
                <span class="font-['Inter'] text-[8px] md:text-[9px] font-medium text-[#C8A96B] tracking-[6px] uppercase leading-none mt-1 pl-[6px] transition group-hover:text-[#F5F1E8] duration-300">
                    FARMS VILLAS
                </span>
            </Link>
            <nav class="hidden md:flex space-x-7 lg:space-x-8 text-xs font-semibold uppercase tracking-widest text-gray-300 items-center font-sans">
                <Link :href="route('villas')" prefetch class="hover:text-[#E6C387] transition">Villas</Link>
                <Link :href="route('experiences')" prefetch class="hover:text-[#E6C387] transition">Experiences</Link>
                <Link :href="route('farm')" prefetch class="hover:text-[#E6C387] transition">Our Farm</Link>
                <Link :href="route('products')" prefetch class="hover:text-[#E6C387] transition">Produce</Link>
                <Link :href="route('gallery')" prefetch class="hover:text-[#E6C387] transition">Gallery</Link>
                <Link :href="route('contact')" prefetch class="hover:text-[#E6C387] transition">Contact</Link>
                <Link :href="route('login')" prefetch class="hover:text-[#E6C387] transition">Sign In</Link>
            </nav>
            <Link :href="route('booking.form')" prefetch class="px-5 py-2 bg-[#E6C387] hover:bg-[#D5B276] text-gray-900 text-xs font-bold uppercase tracking-wider rounded transition">
                Book Stay
            </Link>
        </header>

        <!-- INTRODUCTION -->
        <section class="max-w-4xl mx-auto px-6 py-12 md:py-20 text-center space-y-6">
            <span class="text-xs uppercase tracking-widest font-sans text-amber-600 font-bold">Directions & Location</span>
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-wide">Journey to Kitonga</h1>
            <p class="text-xs text-gray-600 font-sans leading-relaxed max-w-xl mx-auto">
                Kitonga Farm Villas is located in Komkonga Village, in the beautiful Tanga Region of Tanzania. 
            </p>
        </section>

        <!-- COORDINATES & DETAILS -->
        <section class="max-w-5xl mx-auto px-6 pb-20 grid grid-cols-1 md:grid-cols-2 gap-12 font-sans text-xs">
            <div class="space-y-6">
                <div class="bg-white p-6 rounded border border-gray-150 shadow-xs space-y-2">
                    <h3 class="font-extrabold text-sm text-[#2C3E2B] font-serif uppercase tracking-wider">Coordinates</h3>
                    <p class="text-gray-600">{{ settings.location_coordinates || '5°04\'48.2"S 39°05\'22.1"E' }}</p>
                </div>
                <div class="bg-white p-6 rounded border border-gray-150 shadow-xs space-y-2">
                    <h3 class="font-extrabold text-sm text-[#2C3E2B] font-serif uppercase tracking-wider">Contact Phone</h3>
                    <p class="text-gray-600">{{ settings.contact_phone || '+255 712 345 678' }}</p>
                </div>
            </div>
            
            <div class="bg-[#2C3E2B] text-white p-8 rounded shadow space-y-4">
                <h3 class="font-bold text-lg font-serif">Travel Assistance</h3>
                <p class="leading-relaxed">
                    We organize private vehicle pickup from Tanga Airport or the local bus terminal. Please coordinate with our concierge desk ahead of your arrival date.
                </p>
            </div>
        </section>

        <!-- FOOTER -->
        <footer class="bg-[#2C3E2B] text-gray-400 text-xs py-12 border-t border-emerald-900 text-center font-sans">
            <p class="font-extrabold text-white text-sm mb-2 font-serif uppercase tracking-widest">KITONGA FARM VILLAS</p>
            <p class="mb-4">Komkonga Village, Tanga Region, Tanzania</p>
            <div class="flex justify-center space-x-6 mb-6">
                <Link :href="route('about')" prefetch class="hover:text-white transition">About Us</Link>
                <Link :href="route('policies', 'terms')" prefetch class="hover:text-white transition">Terms & Policies</Link>
                <Link :href="route('contact')" prefetch class="hover:text-white transition">Contact</Link>
            </div>
            <div class="max-w-4xl mx-auto border-t border-emerald-900/50 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs px-6">
                <p>&copy; 2026 Kitonga Farm Villas. All rights reserved.</p>
                <div class="flex items-center gap-2 text-[11px]">
                    <span class="text-gray-400">Created by</span>
                    <a 
                        href="https://wa.me/255675315279" 
                        target="_blank" 
                        rel="noopener noreferrer" 
                        class="inline-flex items-center gap-1.5 px-3 py-1 bg-[#14231C] hover:bg-[#C98A3E] text-[#E6C387] hover:text-white rounded-full border border-[#C98A3E]/30 transition duration-300 font-medium shadow-xs"
                        title="Chat on WhatsApp"
                    >
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>0675 315 279</span>
                    </a>
                </div>
            </div>
        </footer>

    </div>
</template>
