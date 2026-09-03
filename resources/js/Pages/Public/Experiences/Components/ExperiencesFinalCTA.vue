<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <section class="relative w-full h-[60vh] min-h-[460px] max-h-[620px] overflow-hidden bg-black flex items-center justify-center">
        
        <!-- Authentic Background Visual -->
        <img 
            src="/images/experiences_hero_bg.webp" 
            alt="Kitonga Farm Sunset Landscape" 
            class="absolute inset-0 w-full h-full object-cover opacity-50 filter brightness-90 saturate-110"
        />

        <!-- Dark Dramatic Vignette Overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/95 via-black/60 to-black/80 pointer-events-none"></div>

        <!-- Center CTA Content -->
        <div class="relative z-10 text-center px-6 space-y-6 max-w-3xl mx-auto text-white font-sans">
            
            <span class="text-[10px] md:text-[11px] uppercase tracking-[5px] font-bold text-[#E6C387] block drop-shadow-sm">
                YOUR COUNTRYSIDE RETREAT AWAITS
            </span>

            <h2 class="text-3xl sm:text-5xl lg:text-6xl font-serif font-light text-[#F7F3EA] tracking-wide leading-tight drop-shadow-md">
                Come Experience Kitonga
            </h2>

            <p class="text-xs sm:text-sm md:text-base text-gray-200 tracking-wider font-light drop-shadow-sm max-w-xl mx-auto">
                Where unhurried farm living, pure nature, and authentic countryside moments come together.
            </p>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center justify-center gap-4 pt-3">
                <Link 
                    :href="route('booking.form')" 
                    prefetch
                    class="px-8 py-3.5 bg-[#1B2E22] hover:bg-[#C98A3E] text-[#F7F3EA] border border-[#C98A3E]/50 font-bold uppercase tracking-wider text-xs rounded-xl transition duration-300 shadow-2xl cursor-pointer"
                >
                    BOOK YOUR STAY
                </Link>
                <Link 
                    :href="route('villas')" 
                    prefetch
                    class="px-8 py-3.5 bg-black/40 hover:bg-black/70 text-white border border-white/30 font-bold uppercase tracking-wider text-xs rounded-xl transition duration-300 backdrop-blur-xs cursor-pointer"
                >
                    EXPLORE VILLAS
                </Link>
            </div>

        </div>

    </section>
</template>

