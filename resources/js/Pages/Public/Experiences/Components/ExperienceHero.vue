<script setup>
import PublicNavbar from '@/Components/PublicNavbar.vue';
</script>

<template>
    <div>
        <!-- 1. UNIVERSAL LUXURY NAVBAR & MOBILE DRAWER -->
        <PublicNavbar current-page="experiences" />

        <!-- 2. MAJESTIC 4K HERO VISUAL -->
        <div class="relative w-full h-[76vh] sm:h-[80vh] md:h-[84vh] min-h-[500px] max-h-[860px] bg-[#14231C] overflow-hidden flex flex-col justify-between select-none">
            <img loading="lazy" decoding="async" 
                src="/images/experiences_hero_bg.webp" 
                alt="Kitonga Farm Aerial Drone Landscape" 
                class="absolute inset-0 w-full h-full object-cover object-center pointer-events-none scale-100 transition duration-1000"
            />
            <!-- Bottom Zero-Gap Connector -->
            <div class="relative z-20 w-full h-12 bg-gradient-to-t from-[#FAF8F5] to-transparent pointer-events-none mt-auto"></div>
        </div>
    </div>
</template>


