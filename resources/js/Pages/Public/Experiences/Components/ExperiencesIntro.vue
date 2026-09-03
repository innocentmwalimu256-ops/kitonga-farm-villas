<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <section class="max-w-4xl mx-auto px-6 sm:px-8 lg:px-12 pt-16 pb-10 md:pt-20 md:pb-12 text-center space-y-6">
        
        <span class="text-[10px] md:text-[11px] uppercase tracking-[4px] font-sans font-bold text-[#C98A3E] block">
            — THE PHILOSOPHY —
        </span>
        
        <h2 class="text-3xl sm:text-4xl md:text-5xl font-serif font-light text-[#1F2420] tracking-tight leading-tight">
            More Than a Farm Tour
        </h2>
        
        <p class="text-base sm:text-lg text-[#3B4239] font-serif italic leading-relaxed max-w-2xl mx-auto">
            Kitonga is not simply a place to visit. It is a place to slow down, explore, and reconnect with the natural rhythm of countryside life.
        </p>

        <p class="text-xs sm:text-sm text-[#6B6D66] font-sans leading-relaxed max-w-2xl mx-auto">
            From walking beneath shaded palm paths to observing free-range livestock and relaxing by the countryside swimming pool, every moment at Kitonga is crafted for peaceful countryside immersion.
        </p>

        <div class="pt-2">
            <Link 
                :href="route('about')" 
                prefetch 
                class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[2px] text-[#1B2E22] hover:text-[#C98A3E] font-sans transition duration-200 group"
            >
                <span>LEARN OUR STORY</span>
                <span class="text-[#C98A3E] group-hover:translate-x-1 transition duration-300">→</span>
            </Link>
        </div>

    </section>
</template>
