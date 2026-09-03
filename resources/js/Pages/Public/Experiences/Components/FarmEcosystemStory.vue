<script setup>
const ecosystemStories = [
    {
        title: 'Crops & Orchards',
        category: 'THE LIVING SOIL',
        description: 'Mangoes, papayas, pineapples, avocados, coconuts, chillies, and vegetables.',
        narrative: 'Across fertile countryside fields, our plantations cultivate seasonal tropical fruits, rich vanilla, and vibrant vegetables that thrive in the fresh mountain air.',
        image: '/images/gallery_img_0223.webp',
    },
    {
        title: 'Livestock & Farm Life',
        category: 'RURAL HARMONY',
        description: 'Dairy cattle, goats, chickens, ducks, and turkeys.',
        narrative: 'Our animals are cared for in natural, open environments. Walking through our dairy stables and poultry runs offers an authentic glimpse into active pastoral life.',
        image: '/images/download_41.webp',
    },
    {
        title: 'Farm Fresh Harvest',
        category: 'EARTH TO TABLE',
        description: 'Pure honey, eggs, fresh milk, yogurt, coffee, and tea.',
        narrative: 'Every harvest reflects the purity of our soil. From fresh dairy and local honey to rich coffee, Kitonga products embody true countryside freshness.',
        image: '/images/farm_gallery_1.webp',
    },
];
</script>

<template>
    <section class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-12 py-16 md:py-24 space-y-12">
        
        <div class="text-center space-y-3 max-w-2xl mx-auto">
            <span class="text-[10px] md:text-[11px] uppercase tracking-[4px] font-sans font-bold text-[#C98A3E] block">
                OUR LIVING ECOSYSTEM
            </span>
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-serif font-light text-[#1F2420]">
                The Farm & Nature in Harmony
            </h2>
            <p class="text-xs sm:text-sm text-[#6B6D66] font-sans">
                Every corner of Kitonga connects in a continuous cycle of fertile soil, seasonal crops, healthy animals, and fresh harvest.
            </p>
        </div>

        <!-- 3 Editorial Story Rows -->
        <div class="space-y-8">
            <div 
                v-for="(story, idx) in ecosystemStories" 
                :key="idx"
                class="grid grid-cols-1 md:grid-cols-12 gap-8 items-center bg-white p-7 sm:p-9 rounded-3xl border border-[#E5E0D8] shadow-sm hover:shadow-lg transition duration-300"
            >
                <!-- Left Image (5 Cols) -->
                <div class="md:col-span-5 aspect-[4/3] rounded-2xl overflow-hidden bg-[#14231C] border border-[#E5E0D8]">
                    <img 
                        :src="story.image" 
                        :alt="story.title" 
                        class="w-full h-full object-cover object-center transform hover:scale-105 transition duration-700" 
                    />
                </div>

                <!-- Right Narrative (7 Cols) -->
                <div class="md:col-span-7 space-y-3">
                    <span class="text-[10px] uppercase font-extrabold tracking-widest text-[#C98A3E] font-sans block">
                        {{ story.category }}
                    </span>
                    <h3 class="text-2xl sm:text-3xl font-serif font-light text-[#1F2420]">
                        {{ story.title }}
                    </h3>
                    <p class="text-sm sm:text-base font-serif italic text-[#3B4239]">
                        {{ story.description }}
                    </p>
                    <p class="text-xs sm:text-sm text-[#6B6D66] font-sans leading-relaxed pt-1">
                        {{ story.narrative }}
                    </p>
                </div>
            </div>
        </div>

    </section>
</template>

