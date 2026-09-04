<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ExperienceHero from './Experiences/Components/ExperienceHero.vue';
import ExperiencesIntro from './Experiences/Components/ExperiencesIntro.vue';
import FeaturedExperiences from './Experiences/Components/FeaturedExperiences.vue';

const props = defineProps({
    experiences: {
        type: Array,
        default: () => [],
    },
    villas: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head title="Experiences & Farm Tours — Kitonga Farm Villas" />

    <div class="bg-[#FAF8F5] text-[#1F2420] font-sans min-h-screen selection:bg-[#C98A3E] selection:text-white">
        
        <!-- 1. CINEMATIC HERO -->
        <ExperienceHero />

        <!-- 2. SERENE EDITORIAL PHILOSOPHY -->
        <ExperiencesIntro />

        <!-- 3. LARGE SPACIOUS TOUR SHOWCASE CARDS -->
        <FeaturedExperiences :experiences="experiences" />

        <!-- 4. LUXURY FOOTER -->
        <footer class="bg-[#14231C] text-[#F7F3EA]/80 py-16 px-6 md:px-12 border-t border-white/10">
            <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-12 text-xs font-sans">
                <div class="space-y-4">
                    <h4 class="font-serif font-semibold text-white text-sm tracking-widest uppercase">Kitonga Farm Villas</h4>
                    <p class="leading-relaxed text-[#F7F3EA]/70">A luxury countryside accommodation and authentic farm-stay destination in Komkonga, Tanga. Where luxury meets farm life.</p>
                </div>
                <div class="space-y-4">
                    <h4 class="font-sans font-bold text-white text-xs tracking-widest uppercase">Quick Links</h4>
                    <ul class="space-y-2 text-[#F7F3EA]/70">
                        <li><Link :href="route('villas')" prefetch class="hover:text-[#C98A3E] transition">Villa Options</Link></li>
                        <li><Link :href="route('experiences')" prefetch class="hover:text-[#C98A3E] transition">Farm Tours</Link></li>
                        <li><Link :href="route('products')" prefetch class="hover:text-[#C98A3E] transition">Farm Produce</Link></li>
                        <li><Link :href="route('booking.form')" prefetch class="hover:text-[#C98A3E] transition">Book Stay</Link></li>
                    </ul>
                </div>
                <div class="space-y-4">
                    <h4 class="font-sans font-bold text-white text-xs tracking-widest uppercase">Contact Details</h4>
                    <div class="space-y-2 text-[#F7F3EA]/70">
                        <p>✉️ <a href="mailto:info@kitongafarmvillas.com" class="hover:text-[#C98A3E] transition">info@kitongafarmvillas.com</a></p>
                        <p>📞 <a href="tel:+255758774695" class="hover:text-[#C98A3E] transition">+255 758 774 695</a></p>
                        <p>📍 Kitonga Farm, Komkonga, Tanga, Tanzania</p>
                    </div>
                </div>
            </div>
            <div class="max-w-6xl mx-auto mt-12 pt-6 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-[#F7F3EA]/60 font-sans">
                <p>© 2026 Kitonga Farm Villas. All rights reserved.</p>
                <div class="flex items-center gap-2">
                    <span class="text-gray-400">Created by</span>
                    <a 
                        href="https://wa.me/255675315279" 
                        target="_blank" 
                        rel="noopener noreferrer" 
                        class="inline-flex items-center gap-1.5 px-3 py-1 bg-[#1E3326] hover:bg-[#C98A3E] text-[#E6C387] hover:text-white rounded-full border border-[#C98A3E]/30 transition duration-300 font-medium shadow-xs"
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
