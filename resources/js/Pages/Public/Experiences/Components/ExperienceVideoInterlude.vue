<script setup>
import { ref } from 'vue';

const videoRef = ref(null);
</script>

<template>
    <section class="relative w-full h-[45vh] min-h-[360px] max-h-[500px] overflow-hidden bg-black flex items-center justify-center my-12 md:my-16">
        
        <!-- Authentic Kitonga Cinematic Video -->
        <video 
            ref="videoRef"
            class="absolute inset-0 w-full h-full object-cover opacity-75"
            autoplay 
            loop 
            muted 
            playsinline
            poster="/images/experiences_hero_bg.webp"
        >
            <source src="/videos/hero_cinematic.mp4" type="video/mp4">
        </video>

        <!-- Cinematic Vignette Overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-black/80 pointer-events-none"></div>

        <!-- Center Content -->
        <div class="relative z-10 text-center px-6 space-y-4 max-w-3xl mx-auto text-white">
            
            <span class="text-[10px] md:text-[11px] uppercase tracking-[5px] font-sans font-bold text-[#E6C387] drop-shadow-sm block">
                CINEMATIC INTERLUDE
            </span>

            <h3 class="text-2xl sm:text-4xl md:text-5xl font-serif font-light text-[#F7F3EA] tracking-wide leading-tight drop-shadow-md">
                See Kitonga Through a Different Lens
            </h3>

            <p class="text-xs sm:text-sm text-gray-200 font-sans tracking-widest uppercase font-light drop-shadow-sm">
                Nature â€¢ Living Farm â€¢ Countryside Peace
            </p>

        </div>

    </section>
</template>

