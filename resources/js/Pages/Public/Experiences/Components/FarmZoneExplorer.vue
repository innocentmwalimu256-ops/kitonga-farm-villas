<script setup>
import { ref } from 'vue';

const activeZoneIndex = ref(0);

const farmZones = [
    {
        id: 'zone-01',
        number: '01',
        title: 'Central Palm Walkways',
        subtitle: 'Shaded Botanical Paths',
        summary: 'Avenue of mature palm trees creating natural shade across the central walking trails.',
        description: 'Take a relaxed, gentle walk along our iconic palm avenues. The paths connect the main farm zones and provide a cool, scenic route through the center of the estate.',
        highlights: ['Shaded walking trails', 'Gentle natural terrain', 'Direct access to fruit orchards'],
        image: '/images/normal_farm_tour.webp',
    },
    {
        id: 'zone-02',
        number: '02',
        title: 'Dairy Cattle & Pastures',
        subtitle: 'Pastoral Dairy Farming',
        summary: 'Active dairy cattle pastures and clean shelter stables.',
        description: 'Observe our dairy cows grazing quietly in open paddocks. Visitors on the General Farm Tour get an up-close look at modern dairy cattle management.',
        highlights: ['Healthy dairy herd', 'Open paddock grazing', 'Clean feeding & shelter bays'],
        image: '/images/three_cows.webp',
    },
    {
        id: 'zone-03',
        number: '03',
        title: 'Poultry & Small Animals',
        subtitle: 'Free-Range Husbandry',
        summary: 'Natural enclosures for chickens, ducks, turkeys, and goats.',
        description: 'Explore the lively animal enclosures where local poultry and goats roam freely, showing the harmonious multi-species balance of our rural farm.',
        highlights: ['Free-range chickens & ducks', 'Turkeys and goats', 'Natural rural setting'],
        image: '/images/download_41.webp',
    },
    {
        id: 'zone-04',
        number: '04',
        title: 'Crops & Greenhouses',
        subtitle: 'Tropical Orchards & Plants',
        summary: 'Fields of papayas, pineapples, avocados, chillies, vanilla, and vegetables.',
        description: 'Walk through rows of tropical fruit trees and greenhouse zones where rich volcanic soils yield seasonal harvest crops in the mountain air.',
        highlights: ['Papayas, pineapples & avocados', 'Greenhouse vanilla & chillies', 'Fresh organic greens'],
        image: '/images/gallery_img_0223.webp',
    },
    {
        id: 'zone-05',
        number: '05',
        title: 'Swimming Pool & Rural Lounge',
        subtitle: 'Countryside Leisure',
        summary: 'Outdoor swimming pool and open-air bar lounge.',
        description: 'The perfect spot to cool off after a farm walk. Swim in the clean outdoor pool surrounded by palm paths, and unwind with cold drinks.',
        highlights: ['Clean swimming pool', 'Open-air shaded lounge', 'Cold refreshments'],
        image: '/images/gallery_img_0220.webp',
    },
];
</script>

<template>
    <section id="farm-zone-explorer" class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-12 py-16 md:py-24">
        
        <!-- Section Header -->
        <div class="text-center space-y-3 mb-12 max-w-2xl mx-auto">
            <span class="text-[10px] md:text-[11px] uppercase tracking-[4px] font-sans font-bold text-[#C98A3E] block">
                INTERACTIVE WAYFINDER
            </span>
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-serif font-light text-[#1F2420]">
                Explore Kitonga Farm Zones
            </h2>
            <p class="text-xs sm:text-sm text-[#6B6D66] font-sans">
                Select a zone below to explore the distinct areas and living spaces of our countryside estate.
            </p>
        </div>

        <!-- Zone Selector Navigation Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pb-4 mb-8 no-scrollbar font-sans text-xs">
            <button 
                v-for="(zone, idx) in farmZones" 
                :key="zone.id"
                type="button"
                @click="activeZoneIndex = idx"
                :class="[
                    'px-5 py-3 rounded-2xl border font-bold uppercase tracking-wider transition duration-300 whitespace-nowrap cursor-pointer flex items-center gap-2.5 shrink-0 shadow-xs',
                    activeZoneIndex === idx 
                        ? 'bg-[#1B2E22] text-[#F7F3EA] border-[#C98A3E]' 
                        : 'bg-white text-gray-700 border-[#E5E0D8] hover:bg-gray-50'
                ]"
            >
                <span class="text-[10px] font-serif text-[#C98A3E]">{{ zone.number }}</span>
                <span>{{ zone.title }}</span>
            </button>
        </div>

        <!-- Interactive Zone Spotlight Showcase (Bespoke 2-Column Luxury Layout) -->
        <div class="bg-white rounded-3xl border border-[#E5E0D8] overflow-hidden shadow-xl grid grid-cols-1 lg:grid-cols-12 items-stretch">
            
            <!-- Left High-Res Visual Display (7 Cols) -->
            <div class="lg:col-span-7 relative h-72 sm:h-96 lg:h-auto min-h-[340px] bg-[#14231C] overflow-hidden">
                <img loading="lazy" decoding="async" 
                    :src="farmZones[activeZoneIndex].image" 
                    :alt="farmZones[activeZoneIndex].title" 
                    class="w-full h-full object-cover object-center transform transition duration-700 scale-100 hover:scale-105"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-black/20 pointer-events-none"></div>

                <!-- Floating Zone Badge -->
                <div class="absolute bottom-6 left-6 text-white font-sans">
                    <span class="text-[10px] uppercase font-bold tracking-[3px] text-[#E6C387] block">
                        ZONE {{ farmZones[activeZoneIndex].number }}
                    </span>
                    <h4 class="text-2xl font-serif font-light text-[#F7F3EA]">
                        {{ farmZones[activeZoneIndex].title }}
                    </h4>
                </div>
            </div>

            <!-- Right Editorial Zone Story & Inclusions (5 Cols) -->
            <div class="lg:col-span-5 p-8 sm:p-10 flex flex-col justify-between space-y-6 font-sans">
                
                <div class="space-y-4">
                    <span class="text-[10px] uppercase font-extrabold tracking-widest text-[#C98A3E] block">
                        {{ farmZones[activeZoneIndex].subtitle }}
                    </span>

                    <h3 class="text-2xl sm:text-3xl font-serif font-light text-[#1F2420]">
                        {{ farmZones[activeZoneIndex].title }}
                    </h3>

                    <p class="text-sm font-serif italic text-[#3B4239] leading-relaxed">
                        {{ farmZones[activeZoneIndex].summary }}
                    </p>

                    <p class="text-xs sm:text-sm text-[#6B6D66] leading-relaxed">
                        {{ farmZones[activeZoneIndex].description }}
                    </p>

                    <!-- Highlights List -->
                    <div class="pt-2 space-y-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-800 block">
                            Key Highlights:
                        </span>
                        <ul class="space-y-2">
                            <li 
                                v-for="(hl, hIdx) in farmZones[activeZoneIndex].highlights" 
                                :key="hIdx"
                                class="text-xs text-gray-600 flex items-center gap-2"
                            >
                                <span class="text-[#C98A3E] font-bold">âœ“</span>
                                <span>{{ hl }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Footer Action -->
                <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-[11px] text-gray-500 font-medium">Included in our guided tours</span>
                    <a 
                        href="#experience-studio"
                        class="text-xs font-bold uppercase tracking-wider text-[#1B2E22] hover:text-[#C98A3E] flex items-center gap-1.5 transition"
                    >
                        <span>View Tour Options</span>
                        <span>â†’</span>
                    </a>
                </div>

            </div>

        </div>

    </section>
</template>


