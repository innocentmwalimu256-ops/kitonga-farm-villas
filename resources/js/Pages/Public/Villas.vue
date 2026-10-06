<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PublicNavbar from '@/Components/PublicNavbar.vue';

const props = defineProps({
    villas: {
        type: Array,
        default: () => [],
    },
});

const isMobileMenuOpen = ref(false);
const activeFilter = ref('all'); // all, couples, family, retreat

const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-TZ', { style: 'currency', currency: 'TZS', maximumFractionDigits: 0 }).format(val);
};

const getImageUrl = (path, slug) => {
    if (slug === 'luxury-villa') return '/images/luxury_villa_img.webp';
    if (slug === 'semi-luxury-villa') return '/images/semi_luxury_villa_img.webp';
    if (slug === 'family-villa') return '/images/family_villa_img.webp';
    if (path) {
        if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('/')) return path;
        return `/${path}`;
    }
    return '/images/luxury_villa_img.webp';
};

const filteredVillas = computed(() => {
    if (activeFilter.value === 'couples') {
        return props.villas.filter(v => v.slug !== 'family-villa');
    }
    if (activeFilter.value === 'family') {
        return props.villas.filter(v => v.slug === 'family-villa' || v.capacity >= 4);
    }
    return props.villas;
});

import SEOHead from '@/Components/SEOHead.vue';

const villasSchema = {
    '@context': 'https://schema.org',
    '@type': 'ItemList',
    'itemListElement': [
        {
            '@type': 'ListItem',
            'position': 1,
            'name': 'Luxury Villa',
            'url': 'https://kitongafarm.com/villas/luxury-villa',
            'description': 'Private luxury villa with bespoke plunge pool, king suite, and mountain views.'
        },
        {
            '@type': 'ListItem',
            'position': 2,
            'name': 'Family Luxury Villa',
            'url': 'https://kitongafarm.com/villas/family-villa',
            'description': 'Expansive multi-bedroom family residence with private pool, interior kitchen, and countryside lawns.'
        }
    ]
};

const scrollToResidences = () => {
    const el = document.getElementById('residences-list');
    if (el) {
        el.scrollIntoView({ behavior: 'smooth' });
    }
};
</script>

<template>
    <SEOHead
        title="Private Luxury Villas & Residences | Kitonga Farm Villas"
        description="Discover private luxury villas and family residences in the lush Komkonga highlands, Tanga. Private plunge pools, farm-to-table breakfast, and peaceful seclusion."
        canonical-url="/villas"
        og-image="/images/luxury_villa_img.webp"
        og-image-alt="Private Luxury Villas at Kitonga Farm Villas"
        :schema="villasSchema"
    />

    <div class="bg-[#FAF8F5] text-[#1F2420] font-sans min-h-screen selection:bg-[#C98A3E] selection:text-white">
        
        <!-- 1. UNIVERSAL LUXURY NAVBAR & MOBILE DRAWER -->
        <PublicNavbar current-page="villas" />

        <!-- 2. CINEMATIC HERO SECTION -->
        <div class="relative w-full h-[76vh] sm:h-[80vh] min-h-[500px] max-h-[820px] bg-[#14231C] overflow-hidden flex flex-col justify-between select-none">
            
            <!-- 4K Architectural Visual -->
            <img loading="lazy" decoding="async" 
                src="/images/luxury_villa_img.webp" 
                alt="Kitonga Luxury Private Residence" 
                class="absolute inset-0 w-full h-full object-cover object-center pointer-events-none transform scale-105 transition duration-1000"
            />

            <!-- BOTTOM ZERO-GAP CONNECTOR -->
            <div class="relative z-20 w-full h-16 bg-gradient-to-t from-[#FAF8F5] to-transparent pointer-events-none"></div>

        </div>

        <!-- 2. EDITORIAL INTRO STATEMENT -->
        <section class="max-w-4xl mx-auto px-6 sm:px-8 lg:px-12 pt-16 pb-10 text-center space-y-6">
            <span class="text-[10px] md:text-[11px] uppercase tracking-[4px] font-sans font-bold text-[#C98A3E] block">
                — ARCHITECTURAL PHILOSOPHY —
            </span>
            
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-serif font-light text-[#1F2420] tracking-tight leading-tight">
                Quiet Sanctuary & Complete Privacy
            </h2>
            
            <p class="text-base sm:text-lg text-[#3B4239] font-serif italic leading-relaxed max-w-2xl mx-auto">
                Each residence at Kitonga is an autonomous private retreat, designed for unhurried rest, generous natural light, and seamless connection to our organic farm grounds.
            </p>
        </section>

        <!-- 3. MINIMAL CATEGORY FILTER BAR (TEXT-BASED, NO DROPDOWNS) -->
        <section class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-12 pb-12">
            <div class="flex justify-center items-center gap-6 sm:gap-10 font-sans text-xs uppercase tracking-widest border-b border-[#E5E0D8] pb-4">
                <button 
                    type="button" 
                    @click="activeFilter = 'all'"
                    :class="[
                        'transition duration-300 cursor-pointer pb-1',
                        activeFilter === 'all' 
                            ? 'text-[#1B2E22] font-bold border-b-2 border-[#C98A3E]' 
                            : 'text-gray-400 hover:text-gray-800'
                    ]"
                >
                    All Residences
                </button>
                <button 
                    type="button" 
                    @click="activeFilter = 'couples'"
                    :class="[
                        'transition duration-300 cursor-pointer pb-1',
                        activeFilter === 'couples' 
                            ? 'text-[#1B2E22] font-bold border-b-2 border-[#C98A3E]' 
                            : 'text-gray-400 hover:text-gray-800'
                    ]"
                >
                    Couples
                </button>
                <button 
                    type="button" 
                    @click="activeFilter = 'family'"
                    :class="[
                        'transition duration-300 cursor-pointer pb-1',
                        activeFilter === 'family' 
                            ? 'text-[#1B2E22] font-bold border-b-2 border-[#C98A3E]' 
                            : 'text-gray-400 hover:text-gray-800'
                    ]"
                >
                    Family
                </button>
                <button 
                    type="button" 
                    @click="activeFilter = 'all'"
                    :class="[
                        'transition duration-300 cursor-pointer pb-1',
                        activeFilter === 'retreat' 
                            ? 'text-[#1B2E22] font-bold border-b-2 border-[#C98A3E]' 
                            : 'text-gray-400 hover:text-gray-800'
                    ]"
                >
                    Private Retreat
                </button>
            </div>
        </section>

        <!-- 4. EDITORIAL RESIDENCE SECTIONS (ALTERNATING ASYMMETRICAL LAYOUT) -->
        <main id="residences-list" class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-12 space-y-20 sm:space-y-28 pb-24">
            
            <article 
                v-for="(villa, idx) in filteredVillas" 
                :key="villa.id"
                class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-16 items-center"
            >
                <!-- Image Column (Alternates Left/Right based on Index) -->
                <div 
                    :class="[
                        'lg:col-span-7 relative aspect-[16/10] rounded-3xl overflow-hidden shadow-xl border border-[#E5E0D8] bg-[#14231C] group',
                        idx % 2 === 1 ? 'lg:order-2' : 'lg:order-1'
                    ]"
                >
                    <img loading="lazy" decoding="async" 
                        :src="getImageUrl(villa.featured_image, villa.slug)" 
                        :alt="villa.name" 
                        class="w-full h-full object-cover object-center transform group-hover:scale-103 transition duration-700 ease-out" 
                    />
                    <div class="absolute inset-0 bg-black/10 group-hover:bg-transparent transition duration-300"></div>
                </div>

                <!-- Editorial Content Column -->
                <div 
                    :class="[
                        'lg:col-span-5 space-y-6 font-sans',
                        idx % 2 === 1 ? 'lg:order-1' : 'lg:order-2'
                    ]"
                >
                    <!-- Editorial Number & Title -->
                    <div class="space-y-1.5 border-b border-[#E5E0D8] pb-4">
                        <span class="text-xs font-serif italic text-[#C98A3E] block">
                            0{{ idx + 1 }} — RESIDENCE
                        </span>
                        <h2 class="text-3xl sm:text-4xl font-serif font-light text-[#1F2420] tracking-tight">
                            {{ villa.name }}
                        </h2>
                    </div>

                    <!-- Description -->
                    <p class="text-xs sm:text-sm text-[#5A5C55] leading-relaxed">
                        {{ villa.description || 'Experience ultimate peace inside our spacious organic countryside farm suites, designed for true privacy.' }}
                    </p>

                    <!-- Inline Specifications with Middle Dots (·) -->
                    <div class="text-xs font-medium text-[#2D312E] tracking-wide py-2 border-y border-[#E5E0D8]/60 leading-relaxed">
                        <span>{{ villa.capacity }} Guests</span>
                        <span class="mx-2 text-[#C98A3E]">·</span>
                        <span>{{ villa.bedrooms }} {{ villa.bedrooms === 1 ? 'Bedroom' : 'Bedrooms' }}</span>
                        <span class="mx-2 text-[#C98A3E]">·</span>
                        <span>{{ villa.bathrooms }} {{ villa.bathrooms === 1 ? 'Bathroom' : 'Bathrooms' }}</span>
                        <template v-if="villa.has_interior_kitchen">
                            <span class="mx-2 text-[#C98A3E]">·</span>
                            <span>Interior Kitchen</span>
                        </template>
                        <span class="mx-2 text-[#C98A3E]">·</span>
                        <span>Private Terrace</span>
                    </div>

                    <!-- Understated Pricing Typography (No Colored Badges) -->
                    <div class="space-y-0.5 pt-1">
                        <span class="text-[10px] uppercase font-bold tracking-[3px] text-gray-400 block">
                            FROM
                        </span>
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-2xl sm:text-3xl font-extrabold text-[#1B2E22]">
                                {{ formatCurrency(villa.base_price) }}
                            </span>
                            <span class="text-xs text-gray-500 font-normal">/ night</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-4 pt-2">
                        <Link 
                            :href="route('villas.show', villa.slug)" 
                            prefetch 
                            class="px-7 py-3.5 bg-[#1B2E22] hover:bg-[#C98A3E] text-[#F7F3EA] text-xs font-bold uppercase tracking-wider rounded-xl transition duration-300 shadow-md cursor-pointer flex items-center gap-2 group"
                        >
                            <span>Explore Residence</span>
                            <span class="text-[#C98A3E] group-hover:text-white group-hover:translate-x-1 transition duration-300">→</span>
                        </Link>
                        <Link 
                            :href="route('booking.form', { villa_id: villa.id })" 
                            prefetch 
                            class="px-7 py-3.5 bg-[#FAF8F5] hover:bg-gray-100 text-[#1B2E22] border border-gray-200 text-xs font-bold uppercase tracking-wider rounded-xl transition cursor-pointer"
                        >
                            Reserve
                        </Link>
                    </div>

                </div>

            </article>

        </main>

        <!-- 5. LUXURY FOOTER -->
        <footer class="bg-[#0E1A14] text-[#F7F3EA]/80 py-16 px-6 md:px-12 border-t border-white/10">
            <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-12 text-xs font-sans">
                <div class="space-y-4">
                    <h4 class="font-serif font-semibold text-white text-sm tracking-widest uppercase">Kitonga Farm Villas</h4>
                    <p class="leading-relaxed text-[#F7F3EA]/70">A luxury countryside accommodation and authentic farm-stay destination in Komkonga, Tanga. Where luxury meets farm life.</p>
                </div>
                <div class="space-y-4">
                    <h4 class="font-sans font-bold text-white text-xs tracking-widest uppercase">Navigation</h4>
                    <ul class="space-y-2 text-[#F7F3EA]/70">
                        <li><Link :href="route('villas')" prefetch class="hover:text-[#C98A3E] transition">Villas</Link></li>
                        <li><Link :href="route('experiences')" prefetch class="hover:text-[#C98A3E] transition">Experiences</Link></li>
                        <li><Link :href="route('farm')" prefetch class="hover:text-[#C98A3E] transition">Our Farm</Link></li>
                        <li><Link :href="route('products')" prefetch class="hover:text-[#C98A3E] transition">Produce</Link></li>
                        <li><Link :href="route('booking.form')" prefetch class="hover:text-[#C98A3E] transition">Book Stay</Link></li>
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


