<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    policy: String,
    cms: Object,
});
</script>

<template>
    <Head title="Terms & Booking Policies" />

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
                <Link :href="route('contact')" prefetch class="hover:text-[#E6C387] transition">Contact</Link>
                <Link :href="route('login')" prefetch class="hover:text-[#E6C387] transition">Sign In</Link>
            </nav>
            <Link :href="route('booking.form')" prefetch class="px-5 py-2 bg-[#E6C387] hover:bg-[#D5B276] text-gray-900 text-xs font-bold uppercase tracking-wider rounded transition">
                Book Stay
            </Link>
        </header>

        <!-- INTRO -->
        <section class="max-w-4xl mx-auto px-6 py-12 md:py-20 text-center space-y-4">
            <span class="text-xs uppercase tracking-widest font-sans text-amber-600 font-bold">Standard Policies</span>
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-wide uppercase">
                {{ policy }} Policy
            </h1>
            <p class="text-xs max-w-xl mx-auto text-gray-500 font-sans leading-relaxed">
                Please review our standard reservation rules, privacy protections, and refund guidelines.
            </p>
        </section>

        <!-- POLICY CONTENTS -->
        <section class="max-w-3xl mx-auto px-6 pb-20 space-y-8 text-xs font-sans text-gray-600 leading-relaxed text-justify bg-white p-8 rounded border border-gray-150 shadow-xs">
            <div v-if="policy === 'terms'" class="space-y-4">
                <h3 class="font-extrabold text-sm text-gray-900 font-serif">1. Reservation Agreements</h3>
                <p>All bookings must satisfy our minimum stay guidelines. Guest names must match verified national IDs presented during physical check-in.</p>
                <h3 class="font-extrabold text-sm text-gray-900 font-serif">2. Stay Conduct</h3>
                <p>We maintain a peaceful countryside atmosphere. Excessive noise, illegal activities, and unapproved commercial filming are strictly prohibited.</p>
            </div>
            
            <div v-else-if="policy === 'privacy'" class="space-y-4">
                <h3 class="font-extrabold text-sm text-gray-900 font-serif">Data Protection Guidelines</h3>
                <p>We respect your privacy. Personal contact details, credentials, and transaction paths are stored securely and never sold to third-party aggregators.</p>
            </div>

            <div v-else-if="policy === 'refunds'" class="space-y-4">
                <h3 class="font-extrabold text-sm text-gray-900 font-serif">Refund Policy</h3>
                <p>Cancellations made 14 days or more prior to arrival are eligible for a full refund minus a 5% processing fee. Cancellations inside 14 days are subject to one-night villa charge.</p>
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
            <p>&copy; 2026 Kitonga Farm Villas. All rights reserved.</p>
        </footer>

    </div>
</template>

