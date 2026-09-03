<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    experiences: {
        type: Array,
        default: () => [],
    },
});

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-TZ', { style: 'currency', currency: 'TZS', maximumFractionDigits: 0 }).format(val);
};

const getImageUrl = (path, slug) => {
    if (slug === 'normal-farm-tour') return '/images/normal_farm_tour.webp';
    if (slug === 'general-farm-tour') return '/images/general_farm_hero.webp';
    if (!path) return '/images/general_farm_hero.webp';
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    return `/images/${path}`;
};
</script>

<template>
    <section id="featured-tours" class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-12 pb-24 md:pb-32">
        
        <!-- Section Header -->
        <div class="text-center space-y-3 mb-14 max-w-2xl mx-auto">
            <span class="text-[10px] md:text-[11px] uppercase tracking-[4px] font-sans font-bold text-[#C98A3E] block">
                CURATED IMMERSIONS
            </span>
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-serif font-light text-[#1F2420]">
                Featured Farm Experiences
            </h2>
            <p class="text-xs sm:text-sm text-[#6B6D66] font-sans">
                Handcrafted agritourism journeys connecting you directly with the vibrant soil, livestock, and produce of Kilimanjaro.
            </p>
        </div>

        <!-- Two Large Spacious Magazine-Grade Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-14 items-stretch">
            
            <div 
                v-for="exp in experiences" 
                :key="exp.id"
                class="bg-white rounded-3xl border border-[#E5E0D8] overflow-hidden shadow-sm hover:shadow-2xl transition duration-500 flex flex-col justify-between group"
            >
                <!-- Large Image Container -->
                <div class="relative h-72 sm:h-80 w-full overflow-hidden bg-[#14231C]">
                    <img loading="lazy" decoding="async" 
                        :src="getImageUrl(exp.featured_image, exp.slug)" 
                        :alt="exp.name" 
                        class="w-full h-full object-cover object-center group-hover:scale-105 transition duration-700 ease-out" 
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent pointer-events-none"></div>
                    
                    <!-- Top Category Badge -->
                    <div class="absolute top-5 left-5">
                        <span class="px-3.5 py-1 bg-white/95 backdrop-blur-xs text-[#1B2E22] text-[9px] uppercase font-extrabold tracking-widest rounded-md font-sans shadow-xs">
                            FARM TOUR
                        </span>
                    </div>

                    <!-- Bottom Category Info -->
                    <div class="absolute bottom-4 left-5 right-5 flex justify-between items-baseline text-white font-sans">
                        <span v-if="exp.category" class="text-xs uppercase tracking-wider font-semibold text-[#E6C387]">
                            {{ exp.category }}
                        </span>
                    </div>
                </div>

                <!-- Card Body -->
                <div class="p-8 sm:p-10 flex-1 flex flex-col justify-between space-y-6">
                    
                    <div class="space-y-3">
                        <h3 class="text-2xl sm:text-3xl font-serif font-light text-[#1F2420] group-hover:text-[#C98A3E] transition duration-300">
                            {{ exp.name }}
                        </h3>
                        
                        <p class="text-xs sm:text-sm text-[#5A5C55] font-sans leading-relaxed">
                            {{ exp.description }}
                        </p>
                    </div>

                    <!-- Inclusions & Price -->
                    <div class="space-y-5 pt-4 border-t border-gray-100 font-sans">
                        
                        <!-- Confirmed Inclusions Chips -->
                        <div class="flex flex-wrap gap-2 text-[11px] font-medium text-[#2C3E2B]">
                            <span v-if="exp.slug === 'normal-farm-tour'" class="bg-[#FAF8F5] px-3 py-1.5 rounded-lg border border-gray-200 flex items-center gap-1.5">
                                ðŸŒ¿ Farm Walk
                            </span>
                            <span v-if="exp.slug === 'normal-farm-tour'" class="bg-[#FAF8F5] px-3 py-1.5 rounded-lg border border-gray-200 flex items-center gap-1.5">
                                ðŸŠ Swimming Pool
                            </span>
                            <span v-if="exp.slug === 'general-farm-tour'" class="bg-[#FAF8F5] px-3 py-1.5 rounded-lg border border-gray-200 flex items-center gap-1.5">
                                ðŸ„ Dairy Zone
                            </span>
                            <span v-if="exp.slug === 'general-farm-tour'" class="bg-[#FAF8F5] px-3 py-1.5 rounded-lg border border-gray-200 flex items-center gap-1.5">
                                ðŸ” Poultry Zone
                            </span>
                            <span v-if="exp.slug === 'general-farm-tour'" class="bg-[#FAF8F5] px-3 py-1.5 rounded-lg border border-gray-200 flex items-center gap-1.5">
                                ðŸŒ± Greenhouses
                            </span>
                            <span v-if="exp.duration" class="bg-[#FAF8F5] px-3 py-1.5 rounded-lg border border-gray-200 flex items-center gap-1.5">
                                â±ï¸ {{ exp.duration }}
                            </span>
                            <span class="bg-[#FAF8F5] px-3 py-1.5 rounded-lg border border-gray-200 flex items-center gap-1.5">
                                ðŸ‘¥ All Ages
                            </span>
                        </div>

                        <!-- Price Row -->
                        <div class="flex items-baseline justify-between pt-1">
                            <div class="flex items-baseline gap-1.5">
                                <span class="text-2xl sm:text-3xl font-extrabold text-[#1B2E22]">
                                    {{ formatCurrency(exp.price) }}
                                </span>
                                <span class="text-xs text-gray-500">/ person</span>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="pt-2">
                            <Link 
                                :href="route('experiences.show', exp.slug)" 
                                prefetch
                                class="w-full py-4 bg-[#1B2E22] hover:bg-[#C98A3E] text-[#F7F3EA] text-xs font-bold uppercase tracking-wider rounded-xl transition duration-300 flex items-center justify-center gap-2 shadow-md cursor-pointer"
                            >
                                <span>DISCOVER THIS EXPERIENCE</span>
                                <span>â†’</span>
                            </Link>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>
</template>


