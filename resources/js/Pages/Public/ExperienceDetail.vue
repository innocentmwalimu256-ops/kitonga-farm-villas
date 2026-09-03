<script setup>
import { Head, Link } from '@inertiajs/vue3';
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
</script>

<template>
    <Head :title="experience.seo_title || experience.name" />

    <div class="bg-[#FAF8F5] text-[#2C3E2B] font-serif min-h-screen">
        
        <!-- STICKY TOP NAVBAR -->
        <header class="sticky top-0 z-50 bg-[#14231C]/95 backdrop-blur-md px-6 py-4 md:px-12 flex justify-between items-center text-white border-b border-white/10 shadow-md transition duration-300">
            <Link :href="route('home')" class="flex flex-col items-center md:items-start text-center md:text-left group">
                <span class="font-serif text-lg md:text-2xl font-light text-[#F5F1E8] tracking-[4px] uppercase leading-none transition group-hover:text-[#C98A3E] duration-300">
                    KITONGA
                </span>
                <span class="font-sans text-[8px] md:text-[9px] font-medium text-[#C98A3E] tracking-[6px] uppercase leading-none mt-1 pl-[2px] transition group-hover:text-[#F5F1E8] duration-300">
                    FARMS VILLAS
                </span>
            </Link>
            <nav class="hidden md:flex space-x-7 lg:space-x-8 text-xs font-semibold uppercase tracking-widest text-gray-200 items-center font-sans">
                <Link :href="route('home')" prefetch class="hover:text-[#C98A3E] transition">Home</Link>
                <Link :href="route('villas')" prefetch class="hover:text-[#C98A3E] transition">Villas</Link>
                <Link :href="route('experiences')" prefetch class="text-[#E6C387] hover:text-[#C98A3E] transition font-bold border-b-2 border-[#E6C387] pb-0.5">Experiences</Link>
                <Link :href="route('farm')" prefetch class="hover:text-[#C98A3E] transition">Our Farm</Link>
                <Link :href="route('products')" prefetch class="hover:text-[#C98A3E] transition">Produce</Link>
                <Link :href="route('contact')" prefetch class="hover:text-[#C98A3E] transition">Contact</Link>
                <Link :href="route('login')" prefetch class="hover:text-[#C98A3E] transition">Sign In</Link>
            </nav>
            <Link :href="route('booking.form')" prefetch class="px-5 py-2.5 bg-white text-gray-900 hover:bg-[#FAF8F5] text-xs font-extrabold uppercase tracking-wider rounded transition font-sans shadow-xs">
                BOOK STAY
            </Link>
        </header>

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
                        â±ï¸ {{ experience.duration }}
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
                    â† Back to Experiences
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
                                <span class="text-[#C98A3E] font-bold">â˜…</span>
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
                                <span class="text-emerald-700 font-extrabold text-sm">âœ“</span>
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
                    <p class="leading-relaxed text-[#F7F3EA]/70">A luxury country accommodation stay and authentic farm-stay destination in Sanya Juu, Kilimanjaro. Where luxury meets farm life.</p>
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
                        <p>âœ‰ï¸ <a href="mailto:info@kitongafarmvillas.com" class="hover:text-[#C98A3E] transition">info@kitongafarmvillas.com</a></p>
                        <p>ðŸ“ž <a href="tel:+255758774695" class="hover:text-[#C98A3E] transition">+255 758 774 695</a></p>
                        <p>ðŸ“ Kitonga Farm, Sanya Juu, Kilimanjaro</p>
                    </div>
                </div>
            </div>
            <div class="max-w-6xl mx-auto mt-12 pt-6 border-t border-white/10 text-center text-[10px] text-[#F7F3EA]/50 font-sans">
                Â© 2026 Kitonga Farm Villas. All rights reserved.
            </div>
        </footer>

    </div>
</template>


