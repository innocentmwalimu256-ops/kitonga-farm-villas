<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import SEOHead from '@/Components/SEOHead.vue';
import PublicNavbar from '@/Components/PublicNavbar.vue';
import ExperienceBookingPanel from './Experiences/Components/ExperienceBookingPanel.vue';
import VillaCrossSell from './Experiences/Components/VillaCrossSell.vue';

const props = defineProps({
    experience: Object,
    villas: Array,
    isPreview: Boolean,
});

const getImageUrl = (path, slug) => {
    if (slug === 'normal-farm-tour') return '/images/normal_farm_tour.webp';
    if (slug === 'general-farm-tour') return '/images/general_farm_hero.webp';
    if (!path) return '/images/general_farm_hero.webp';
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    return `/images/${path}`;
};

const experienceSchema = computed(() => ({
    '@context': 'https://schema.org',
    '@type': 'TouristAttraction',
    'name': props.experience?.name || 'Farm Experience',
    'description': props.experience?.seo_description || props.experience?.description || 'Authentic agritourism farm tour and experience at Kitonga Farm Villas.',
    'url': `https://kitongafarm.com/experiences/${props.experience?.slug || ''}`,
    'image': getImageUrl(props.experience?.featured_image, props.experience?.slug),
    'isAccessibleForFree': false,
    'offers': {
        '@type': 'Offer',
        'price': props.experience?.price || 0,
        'priceCurrency': 'TZS',
        'availability': 'https://schema.org/InStock'
    },
    'location': {
        '@type': 'Place',
        'name': 'Kitonga Farm Villas',
        'address': {
            '@type': 'PostalAddress',
            'streetAddress': 'Kitonga Farm Estate',
            'addressLocality': 'Iringa',
            'addressRegion': 'Iringa',
            'addressCountry': 'TZ'
        }
    }
}));
</script>

<template>
    <SEOHead 
        :title="experience.seo_title || `${experience.name} — Agritourism Tour`"
        :description="experience.seo_description || `${experience.name} at Kitonga Farm Villas. Discover organic farming, hands-on agriculture, and peaceful countryside exploration.`"
        :canonical="`/experiences/${experience.slug}`"
        :og-image="getImageUrl(experience.featured_image, experience.slug)"
        og-type="article"
        :schema="experienceSchema"
    />

    <div class="bg-[#FAF8F5] text-[#2C3E2B] font-serif min-h-screen">
        
        <!-- UNIVERSAL LUXURY NAVBAR & TELEPORTED MOBILE DRAWER -->
        <PublicNavbar current-page="experiences" />

        <!-- TOP DETAILED BANNER IMAGE -->
        <section class="relative h-[55vh] md:h-[65vh] bg-[#14231C] overflow-hidden">
            <img loading="lazy" decoding="async" 
                :src="getImageUrl(experience.featured_image, experience.slug)" 
                class="w-full h-full object-cover" 
                :alt="experience.name"
            />
            <!-- Dual gradient for crystal clear navbar and text contrast -->
            <div class="absolute inset-0 bg-gradient-to-b from-black/65 via-transparent to-black/85 pointer-events-none"></div>
            
            <div class="absolute bottom-8 left-6 md:left-12 max-w-4xl space-y-3 text-white z-10">
                <div class="flex items-center gap-3">
                    <span v-if="experience.category" class="px-3 py-1 bg-[#C98A3E] text-white text-[10px] uppercase font-bold tracking-wider rounded font-sans shadow-sm">
                        {{ experience.category }}
                    </span>
                    <span v-if="experience.duration" class="px-3 py-1 bg-black/40 backdrop-blur-xs text-white text-xs font-sans font-semibold rounded border border-white/20">
                        {{ experience.duration }}
                    </span>
                </div>
                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-light tracking-wide text-[#F7F3EA] font-serif leading-tight drop-shadow-md">
                    {{ experience.name }}
                </h1>
                
                <div v-if="isPreview" class="inline-block px-3 py-1 bg-blue-500 text-white text-[10px] font-bold uppercase tracking-wider rounded font-sans shadow-sm animate-pulse">
                    Preview Mode (Draft/Unpublished Version)
                </div>
            </div>
        </section>

        <!-- BACK LINK & GRID WRAPPER -->
        <section class="max-w-7xl mx-auto px-6 py-8">
            <div class="mb-8">
                <Link 
                    :href="route('experiences')" 
                    prefetch
                    class="text-xs font-bold font-sans text-gray-500 hover:text-[#2C3E2B] transition inline-flex items-center gap-1.5"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Back to Experiences</span>
                </Link>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 items-start">
                
                <!-- LEFT COLUMN: DETAIL CONTENT -->
                <div class="lg:col-span-2 space-y-12">
                    
                    <!-- Narrative -->
                    <div class="space-y-6 text-xs sm:text-sm text-gray-700 font-sans leading-relaxed">
                        <h2 class="font-serif text-2xl sm:text-3xl text-gray-950 font-light leading-tight">
                            Experience Details
                        </h2>
                        <p class="leading-relaxed whitespace-pre-line">{{ experience.description }}</p>
                    </div>

                    <!-- Highlights -->
                    <div v-if="experience.highlights && experience.highlights.length > 0" class="bg-white p-8 rounded-2xl border border-gray-200 space-y-4 shadow-xs">
                        <h3 class="font-serif text-xl text-gray-950 font-light">Experience Highlights</h3>
                        <ul class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs font-sans text-gray-600">
                            <li v-for="(hl, idx) in experience.highlights" :key="idx" class="flex gap-2">
                                <span class="text-[#C98A3E] font-bold">•</span>
                                <span class="leading-relaxed">{{ hl }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Inclusions -->
                    <div v-if="experience.inclusions && experience.inclusions.length > 0" class="space-y-4">
                        <h3 class="font-serif text-xl text-gray-950 font-light">What's Included</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 font-sans text-xs">
                            <div 
                                v-for="(inc, idx) in experience.inclusions" 
                                :key="idx" 
                                class="p-4 bg-white border border-gray-200 rounded-xl flex items-center gap-2 text-gray-700 shadow-xs"
                            >
                                <svg class="w-4 h-4 text-emerald-700 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>{{ inc }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Good to Know -->
                    <div v-if="experience.good_to_know" class="space-y-3 border-t border-gray-200 pt-8 text-xs font-sans text-gray-500">
                        <h4 class="font-bold text-gray-900 uppercase text-[9px] tracking-widest">Good to Know & Helpful Tips</h4>
                        <p class="leading-relaxed whitespace-pre-line">{{ experience.good_to_know }}</p>
                    </div>

                </div>

                <!-- RIGHT COLUMN: STICKY BOOKING PANEL -->
                <div class="lg:sticky lg:top-8 space-y-6">
                    <ExperienceBookingPanel :experience="experience" />
                </div>

            </div>
        </section>

        <!-- VILLA CROSS-SELL -->
        <VillaCrossSell :villas="villas" />

        <!-- LUXURY FOOTER -->
        <footer class="bg-[#14231C] text-[#F7F3EA]/80 py-16 px-6 md:px-12 border-t border-white/10 mt-12">
            <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-12 text-xs font-sans">
                <div class="space-y-4">
                    <h4 class="font-serif font-semibold text-white text-sm tracking-widest uppercase">Kitonga Farm Villas</h4>
                    <p class="leading-relaxed text-[#F7F3EA]/70">A luxury country accommodation stay and authentic farm-stay destination in Komkonga, Tanga. Where luxury meets farm life.</p>
                </div>
                <div class="space-y-4">
                    <h4 class="font-sans font-bold text-white text-xs tracking-widest uppercase">Quick Links</h4>
                    <ul class="space-y-2 text-[#F7F3EA]/70">
                        <li><Link :href="route('villas')" prefetch class="hover:text-[#C98A3E] transition">Villa Options</Link></li>
                        <li><Link :href="route('experiences')" prefetch class="hover:text-[#C98A3E] transition">Farm Tours</Link></li>
                        <li><Link :href="route('products')" prefetch class="hover:text-[#C98A3E] transition">Farm Produce</Link></li>
                        <li><Link :href="route('booking.form')" prefetch class="hover:text-[#C98A3E] transition">Check Availability</Link></li>
                    </ul>
                </div>
                <div class="space-y-4">
                    <h4 class="font-sans font-bold text-white text-xs tracking-widest uppercase">Contact Details</h4>
                    <div class="space-y-2 text-[#F7F3EA]/70">
                        <p><a href="mailto:kitongafarmvillas@gmail.com" class="hover:text-[#C98A3E] transition">kitongafarmvillas@gmail.com</a></p>
                        <p><a href="tel:+255758774695" class="hover:text-[#C98A3E] transition">+255 758 774 695</a></p>
                        <p>Kitonga Farm, Komkonga, Tanga, Tanzania</p>
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


