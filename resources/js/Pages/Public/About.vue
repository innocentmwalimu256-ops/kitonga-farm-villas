<script setup>
import { Head, Link } from '@inertiajs/vue3';
import SEOHead from '@/Components/SEOHead.vue';

import PublicNavbar from '@/Components/PublicNavbar.vue';

defineProps({
    cms: {
        type: Object,
        default: () => ({}),
    },
});

const aboutSchema = {
    '@context': 'https://schema.org',
    '@type': 'AboutPage',
    'name': 'About Kitonga Farm Villas',
    'description': 'The story and heritage of Kitonga Farm Villas — an eco-luxury agritourism resort and sustainable organic farm in Tanzania.',
    'url': 'https://kitongafarm.com/about',
    'mainEntity': {
        '@type': 'Resort',
        'name': 'Kitonga Farm Villas',
        'description': 'A sanctuary of ecological farming, luxury private villa retreats, and tranquil countryside living.',
        'address': {
            '@type': 'PostalAddress',
            'streetAddress': 'Kitonga Farm Estate',
            'addressLocality': 'Iringa',
            'addressRegion': 'Iringa',
            'addressCountry': 'TZ'
        }
    }
};
</script>

<template>
    <SEOHead 
        title="Our Story & Sustainable Farm Heritage"
        description="Learn the story of Kitonga Farm Villas: where organic agriculture, bespoke hospitality, and serene nature combine to create an unforgettable country escape."
        canonical="/about"
        og-image="/images/luxury_villa_img.webp"
        :schema="aboutSchema"
    />

    <div class="bg-[#FAF8F5] text-[#2C3E2B] font-serif min-h-screen">
        
        <!-- HEADER -->
        <PublicNavbar current-page="about" />

        <!-- MAIN STORY -->
        <section class="max-w-3xl mx-auto px-6 py-12 md:py-20 space-y-8 text-center">
            <span class="text-xs uppercase tracking-widest font-sans text-amber-600 font-bold">Our Heritage</span>
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-wide">The Kitonga Story</h1>
            
            <p class="text-xs text-gray-600 font-sans leading-relaxed text-justify">
                {{ cms.brand_story || 'Kitonga Farm Villas was founded on the philosophy that true luxury is found in nature, silence, and clean, organic living. Tucked away in Komkonga Village within the Tanga Region, our farm-resort offers a pure, unhurried escape from modern stress. We operate on ecological farming guidelines, powering our operations with sustainability and supporting local village families.' }}
            </p>
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

