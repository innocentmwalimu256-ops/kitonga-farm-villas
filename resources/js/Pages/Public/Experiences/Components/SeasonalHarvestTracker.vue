<script setup>
import { ref } from 'vue';

const activeHarvestTab = ref('crops');

const harvestData = {
    crops: {
        title: 'Tropical Orchards & Crops',
        subtitle: 'SUN-GROWN ACRES',
        description: 'Cultivated in mineral-rich volcanic soil, our fruit trees and plantation plots yield vibrant harvests.',
        items: [
            { name: 'Mangoes', desc: 'Lush tropical trees along the sunny valley slopes.' },
            { name: 'Papayas', desc: 'Fast-growing sweet fruit trees near our irrigation paths.' },
            { name: 'Pineapples', desc: 'Sweet ground crops thriving in warm sunshine.' },
            { name: 'Avocados & Coconuts', desc: 'Tall boundary trees providing rich shade.' },
            { name: 'Vanilla & Chillies', desc: 'Carefully tended greenhouse and garden rows.' },
            { name: 'Magimbi & Vegetables', desc: 'Traditional countryside root crops and seasonal greens.' },
        ],
        image: '/images/gallery_img_0223.webp',
    },
    livestock: {
        title: 'Pastoral Livestock',
        subtitle: 'HEALTHY ANIMAL HUSBANDRY',
        description: 'Our animals are cared for in open, clean rural environments with dedicated feeding and shelter areas.',
        items: [
            { name: 'Dairy Cattle', desc: 'Healthy dairy herd managed in modern open pastures.' },
            { name: 'Local Goats', desc: 'Naturally browsing across the outer vegetation boundary.' },
            { name: 'Chickens & Ducks', desc: 'Free-range poultry active throughout the daytime.' },
            { name: 'Turkeys', desc: 'Traditional countryside farm birds in dedicated runs.' },
        ],
        image: '/images/download_41.webp',
    },
    produce: {
        title: 'Farm Fresh Harvest',
        subtitle: 'PURE COUNTRYSIDE BOUNTY',
        description: 'Every product comes straight from our active farm operations to provide genuine local freshness.',
        items: [
            { name: 'Pure Honey', desc: 'Natural wild honey harvested from on-farm apiary zones.' },
            { name: 'Fresh Milk & Yogurt', desc: 'Pure daily dairy from our healthy cattle herd.' },
            { name: 'Farm Eggs', desc: 'Collected daily from free-roaming poultry.' },
            { name: 'Coffee & Tea', desc: 'Traditional Tanzanian hot beverages served at the rural lounge.' },
        ],
        image: '/images/farm_gallery_1.webp',
    },
};
</script>

<template>
    <section class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-12 py-16 md:py-24 space-y-12">
        
        <!-- Section Header -->
        <div class="text-center space-y-3 max-w-2xl mx-auto">
            <span class="text-[10px] md:text-[11px] uppercase tracking-[4px] font-sans font-bold text-[#C98A3E] block">
                FLORA & FAUNA DISCOVERY
            </span>
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-serif font-light text-[#1F2420]">
                Living Harvest & Ecosystem
            </h2>
            <p class="text-xs sm:text-sm text-[#6B6D66] font-sans">
                Explore the verified crops, livestock, and fresh produce cultivated across Kitonga Farm.
            </p>
        </div>

        <!-- Interactive Category Switcher -->
        <div class="flex justify-center font-sans text-xs">
            <div class="bg-white p-1.5 rounded-2xl border border-[#E5E0D8] inline-flex gap-2 shadow-xs">
                <button 
                    type="button"
                    @click="activeHarvestTab = 'crops'"
                    :class="[
                        'py-2.5 px-5 rounded-xl font-bold uppercase tracking-wider transition duration-300 cursor-pointer',
                        activeHarvestTab === 'crops' 
                            ? 'bg-[#1B2E22] text-[#F7F3EA] shadow-xs' 
                            : 'text-gray-600 hover:text-black'
                    ]"
                >
                    ðŸŒ± Crops & Orchards
                </button>
                <button 
                    type="button"
                    @click="activeHarvestTab = 'livestock'"
                    :class="[
                        'py-2.5 px-5 rounded-xl font-bold uppercase tracking-wider transition duration-300 cursor-pointer',
                        activeHarvestTab === 'livestock' 
                            ? 'bg-[#1B2E22] text-[#F7F3EA] shadow-xs' 
                            : 'text-gray-600 hover:text-black'
                    ]"
                >
                    ðŸ„ Pastoral Livestock
                </button>
                <button 
                    type="button"
                    @click="activeHarvestTab = 'produce'"
                    :class="[
                        'py-2.5 px-5 rounded-xl font-bold uppercase tracking-wider transition duration-300 cursor-pointer',
                        activeHarvestTab === 'produce' 
                            ? 'bg-[#1B2E22] text-[#F7F3EA] shadow-xs' 
                            : 'text-gray-600 hover:text-black'
                    ]"
                >
                    ðŸ¯ Farm Fresh Harvest
                </button>
            </div>
        </div>

        <!-- Dynamic Category Display -->
        <div class="bg-white rounded-3xl border border-[#E5E0D8] p-8 sm:p-12 shadow-lg grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-center">
            
            <!-- Left Narrative & Item Grid (7 Cols) -->
            <div class="lg:col-span-7 space-y-6 font-sans">
                <div>
                    <span class="text-[10px] uppercase font-extrabold tracking-widest text-[#C98A3E] block">
                        {{ harvestData[activeHarvestTab].subtitle }}
                    </span>
                    <h3 class="text-2xl sm:text-3xl font-serif font-light text-[#1F2420] mt-1">
                        {{ harvestData[activeHarvestTab].title }}
                    </h3>
                    <p class="text-xs sm:text-sm text-[#5A5C55] mt-2 leading-relaxed">
                        {{ harvestData[activeHarvestTab].description }}
                    </p>
                </div>

                <!-- Items Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div 
                        v-for="(item, idx) in harvestData[activeHarvestTab].items" 
                        :key="idx"
                        class="p-4 rounded-2xl bg-[#FAF8F5] border border-gray-150 space-y-1 hover:border-[#C98A3E]/40 transition"
                    >
                        <h4 class="text-sm font-serif font-medium text-[#1B2E22]">
                            {{ item.name }}
                        </h4>
                        <p class="text-xs text-gray-500 leading-relaxed">
                            {{ item.desc }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Visual (5 Cols) -->
            <div class="lg:col-span-5 relative">
                <div class="aspect-[4/3] rounded-2xl overflow-hidden shadow-md border border-[#E5E0D8] bg-[#14231C]">
                    <img loading="lazy" decoding="async" 
                        :src="harvestData[activeHarvestTab].image" 
                        :alt="harvestData[activeHarvestTab].title" 
                        class="w-full h-full object-cover object-center transform hover:scale-105 transition duration-700" 
                    />
                </div>
            </div>

        </div>

    </section>
</template>


