<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    experiences: {
        type: Array,
        default: () => [],
    },
});

const selectedTourIndex = ref(0);

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-TZ', { style: 'currency', currency: 'TZS', maximumFractionDigits: 0 }).format(val);
};

const currentTour = computed(() => {
    if (!props.experiences || props.experiences.length === 0) {
        return {
            name: 'Normal Farm Tour',
            slug: 'normal-farm-tour',
            price: 20000,
            description: 'A relaxed entry into the beauty of Kitonga. Wander through palm pathways and enjoy refreshing pool access.',
        };
    }
    return props.experiences[selectedTourIndex.value] || props.experiences[0];
});

const getTourImage = (slug) => {
    if (slug === 'general-farm-tour') return '/images/three_cows.jpg';
    return '/images/normal_farm_tour.jpg';
};

const inclusionMatrix = [
    {
        feature: 'Farm Entrance & Guided Walk',
        normal: 'Included',
        general: 'Included',
    },
    {
        feature: 'Central Palm Tree Pathways',
        normal: 'Included',
        general: 'Included',
    },
    {
        feature: 'Outdoor Swimming Pool Access',
        normal: 'Included',
        general: 'Included',
    },
    {
        feature: 'Dairy Cattle Stables & Pastures',
        normal: '—',
        general: 'Full Access',
    },
    {
        feature: 'Poultry & Small Animal Enclosures',
        normal: '—',
        general: 'Full Access',
    },
    {
        feature: 'Tropical Orchards & Greenhouses',
        normal: 'Scenic Overview',
        general: 'Full Exploration',
    },
];
</script>

<template>
    <section id="experience-studio" class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-12 py-16 md:py-24 space-y-12">
        
        <!-- Section Header -->
        <div class="text-center space-y-3 max-w-2xl mx-auto">
            <span class="text-[10px] md:text-[11px] uppercase tracking-[4px] font-sans font-bold text-[#C98A3E] block">
                TOUR COMPARISON & SELECTION
            </span>
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-serif font-light text-[#1F2420]">
                Choose Your Farm Journey
            </h2>
            <p class="text-xs sm:text-sm text-[#6B6D66] font-sans">
                Select between our relaxed introduction or the comprehensive pastoral ecosystem tour.
            </p>
        </div>

        <!-- Interactive Tour Selector Switcher -->
        <div class="flex justify-center">
            <div class="bg-[#FAF8F5] p-1.5 rounded-2xl border border-[#E5E0D8] inline-flex items-center gap-2 font-sans text-xs shadow-inner">
                <button 
                    v-for="(exp, idx) in experiences"
                    :key="exp.id"
                    type="button"
                    @click="selectedTourIndex = idx"
                    :class="[
                        'py-3 px-6 rounded-xl font-bold uppercase tracking-wider transition duration-300 cursor-pointer flex items-center gap-2',
                        selectedTourIndex === idx 
                            ? 'bg-[#1B2E22] text-[#F7F3EA] shadow-md' 
                            : 'text-gray-700 hover:text-black'
                    ]"
                >
                    <span>{{ exp.name }}</span>
                    <span class="text-[10px] font-normal opacity-80">({{ formatCurrency(exp.price) }})</span>
                </button>
            </div>
        </div>

        <!-- Dual Column Interactive Studio Showcase -->
        <div class="bg-white rounded-3xl border border-[#E5E0D8] overflow-hidden shadow-xl grid grid-cols-1 lg:grid-cols-12 items-stretch">
            
            <!-- Left Editorial Visual & Highlights (6 Cols) -->
            <div class="lg:col-span-6 relative h-80 lg:h-auto min-h-[380px] bg-[#14231C] overflow-hidden">
                <img 
                    :src="getTourImage(currentTour.slug)" 
                    :alt="currentTour.name" 
                    class="w-full h-full object-cover object-center transform transition duration-700 hover:scale-105"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>

                <!-- Bottom Floating Info Overlay -->
                <div class="absolute bottom-8 left-8 right-8 text-white space-y-2 font-sans">
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 bg-[#C98A3E] text-white text-[9px] uppercase font-bold tracking-widest rounded">
                            GUIDED EXPERIENCE
                        </span>
                        <span v-if="currentTour.duration" class="px-3 py-1 bg-black/40 backdrop-blur-xs text-white text-xs font-semibold rounded border border-white/20">
                            ⏱️ {{ currentTour.duration }}
                        </span>
                    </div>
                    <h3 class="text-3xl font-serif font-light text-[#F7F3EA]">
                        {{ currentTour.name }}
                    </h3>
                    <p class="text-xs text-gray-200 line-clamp-2">
                        {{ currentTour.description }}
                    </p>
                </div>
            </div>

            <!-- Right Inclusions Matrix & Direct Action (6 Cols) -->
            <div class="lg:col-span-6 p-8 sm:p-10 flex flex-col justify-between space-y-6 font-sans">
                
                <div class="space-y-4">
                    <div class="flex items-baseline justify-between border-b border-gray-100 pb-4">
                        <div>
                            <span class="text-[10px] uppercase font-extrabold tracking-widest text-[#C98A3E] block">
                                LIVE DATABASE RATE
                            </span>
                            <div class="flex items-baseline gap-1 pt-1">
                                <span class="text-3xl font-extrabold text-[#1B2E22]">
                                    {{ formatCurrency(currentTour.price) }}
                                </span>
                                <span class="text-xs text-gray-500">/ person</span>
                            </div>
                        </div>
                        <span class="text-xs font-semibold px-3 py-1 bg-[#FAF8F5] rounded-lg border border-gray-200 text-gray-700">
                            👥 All Ages Welcome
                        </span>
                    </div>

                    <!-- Comparison Table -->
                    <div class="space-y-3">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-700 block">
                            Confirmed Tour Features:
                        </span>
                        <div class="divide-y divide-gray-100 border border-gray-150 rounded-2xl overflow-hidden text-xs">
                            <div 
                                v-for="(item, idx) in inclusionMatrix" 
                                :key="idx"
                                class="px-4 py-3 flex items-center justify-between bg-white hover:bg-[#FAF8F5] transition"
                            >
                                <span class="text-gray-700 font-medium">{{ item.feature }}</span>
                                <span 
                                    :class="[
                                        'font-bold text-xs',
                                        (currentTour.slug === 'normal-farm-tour' ? item.normal : item.general) === '—' 
                                            ? 'text-gray-300' 
                                            : 'text-emerald-700'
                                    ]"
                                >
                                    {{ currentTour.slug === 'normal-farm-tour' ? item.normal : item.general }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="pt-4 flex flex-col sm:flex-row gap-3">
                    <Link 
                        :href="route('experiences.show', currentTour.slug)" 
                        prefetch
                        class="flex-1 py-3.5 bg-[#1B2E22] hover:bg-[#C98A3E] text-[#F7F3EA] text-xs font-bold uppercase tracking-wider rounded-xl transition duration-300 text-center shadow-md cursor-pointer"
                    >
                        Explore Full Itinerary & Details →
                    </Link>
                    <Link 
                        :href="route('booking.form')" 
                        prefetch
                        class="py-3.5 px-6 bg-[#FAF8F5] hover:bg-gray-100 text-[#1B2E22] border border-gray-200 text-xs font-bold uppercase tracking-wider rounded-xl transition text-center cursor-pointer"
                    >
                        Book Stay
                    </Link>
                </div>

            </div>

        </div>

    </section>
</template>
