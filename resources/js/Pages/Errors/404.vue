<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    status: {
        type: Number,
        default: 404,
    },
});
</script>

<template>
    <Head title="Page Not Found | Kitonga Farm Villas" />

    <div class="min-h-screen bg-[#0F1D16] text-[#F5F1E8] font-serif flex flex-col justify-between selection:bg-[#C98A3E] selection:text-white relative overflow-hidden">
        
        <!-- Ambient background glows -->
        <div class="absolute inset-0 bg-radial from-transparent via-[#0F1D16]/80 to-[#070F0B] pointer-events-none"></div>
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-[#C98A3E]/10 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Header -->
        <header class="relative z-10 max-w-7xl mx-auto w-full px-6 py-6 flex items-center justify-between border-b border-white/10">
            <Link :href="route('home')" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full border border-[#C98A3E]/60 flex items-center justify-center p-1.5 bg-[#14231C]">
                    <img src="/images/logo_gold.webp" alt="Kitonga Logo" class="w-full h-full object-contain" />
                </div>
                <div>
                    <span class="block text-sm font-bold tracking-[0.25em] text-[#E6C387] uppercase leading-none">KITONGA</span>
                    <span class="block text-[8px] tracking-[0.3em] text-white/50 uppercase font-light mt-0.5">FARM VILLAS SANCTUARY</span>
                </div>
            </Link>

            <Link :href="route('home')" class="text-xs tracking-[0.2em] uppercase font-sans font-semibold text-[#E6C387] hover:underline">
                Return Home ➔
            </Link>
        </header>

        <!-- Main 404 Message -->
        <main class="relative z-10 max-w-3xl mx-auto px-6 py-16 text-center space-y-6">
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-[#1B2E24] border border-[#C98A3E]/40 text-[#E6C387] text-[10px] tracking-[0.25em] uppercase font-sans font-semibold">
                <span>Error 404 • Page Not Found</span>
            </div>

            <h1 class="text-6xl sm:text-8xl font-normal text-[#E6C387] tracking-tight font-serif">
                404
            </h1>

            <h2 class="text-2xl sm:text-4xl font-normal text-white leading-snug">
                The Sanctuary Path You Are Seeking Has Wandered
            </h2>

            <p class="text-xs sm:text-sm text-white/70 font-sans font-light max-w-lg mx-auto leading-relaxed">
                The page or resource you requested may have been moved, renamed, or is temporarily resting. We invite you to explore our luxury villas, farm experiences, or return to the homepage.
            </p>

            <div class="pt-6 flex flex-wrap items-center justify-center gap-4 font-sans text-xs">
                <Link
                    :href="route('home')"
                    class="px-7 py-3 rounded-full bg-[#C98A3E] hover:bg-[#b57a34] text-white font-bold tracking-widest uppercase shadow-lg transition duration-300"
                >
                    Visit Homepage
                </Link>
                <Link
                    :href="route('villas')"
                    class="px-7 py-3 rounded-full bg-white/10 hover:bg-white/20 text-white font-semibold tracking-widest uppercase border border-white/20 transition duration-300"
                >
                    Explore Villas
                </Link>
                <Link
                    :href="route('experiences')"
                    class="px-7 py-3 rounded-full bg-white/10 hover:bg-white/20 text-white font-semibold tracking-widest uppercase border border-white/20 transition duration-300"
                >
                    Farm Experiences
                </Link>
                <Link
                    :href="route('contact')"
                    class="px-7 py-3 rounded-full bg-white/10 hover:bg-white/20 text-white font-semibold tracking-widest uppercase border border-white/20 transition duration-300"
                >
                    Contact Concierge
                </Link>
            </div>
        </main>

        <!-- Footer -->
        <footer class="relative z-10 max-w-7xl mx-auto w-full px-6 py-6 text-center text-[11px] text-white/40 font-sans border-t border-white/10">
            © 2026 Kitonga Farm Villas &amp; Sanctuary. Handeni, Tanga, Tanzania.
        </footer>

    </div>
</template>
