<script setup>
const props = defineProps({
    activeCategory: {
        type: String,
        default: 'All',
    },
});

const emit = defineEmits(['selectCategory']);

const categories = [
    {
        name: 'All Experiences',
        filterKey: 'All',
        icon: 'âœ¨',
        image: '/images/normal_farm_tour.webp',
    },
    {
        name: 'Farm Life',
        filterKey: 'Nature & Trails',
        icon: 'ðŸŒ±',
        image: '/images/gallery_img_0216.webp',
    },
    {
        name: 'Animals',
        filterKey: 'Complete Ecosystem',
        icon: 'ðŸ„',
        image: '/images/three_cows.webp',
    },
    {
        name: 'Farm & Food',
        filterKey: 'Farm & Food',
        icon: 'ðŸ¯',
        image: '/images/farm_gallery_1.webp',
    },
    {
        name: 'Nature',
        filterKey: 'Nature',
        icon: 'ðŸŒ¿',
        image: '/images/gallery_img_0223.webp',
    },
    {
        name: 'Relaxation',
        filterKey: 'Relaxation',
        icon: 'ðŸŠ',
        image: '/images/gallery_img_0220.webp',
    },
];

const handleSelect = (key) => {
    emit('selectCategory', key);
};
</script>

<template>
    <section id="discover-categories" class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-12 py-10">
        
        <div class="text-center space-y-2 mb-8">
            <span class="text-[10px] md:text-[11px] uppercase tracking-[4px] font-sans font-bold text-[#C98A3E] block">
                DISCOVER KITONGA
            </span>
            <h3 class="text-2xl sm:text-3xl font-serif font-light text-[#1F2420]">
                Explore By Atmosphere & Interest
            </h3>
        </div>

        <!-- 6 Visual Category Cards in Horizontal Responsive Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4 font-sans">
            <button 
                v-for="cat in categories" 
                :key="cat.filterKey"
                type="button"
                @click="handleSelect(cat.filterKey)"
                :class="[
                    'relative h-28 sm:h-32 rounded-xl overflow-hidden group cursor-pointer border transition duration-300 flex flex-col items-center justify-center p-3 text-center text-white',
                    activeCategory === cat.filterKey 
                        ? 'border-[#C98A3E] ring-2 ring-[#C98A3E] shadow-lg scale-[1.02]' 
                        : 'border-transparent hover:border-white/40 shadow-sm'
                ]"
            >
                <!-- Background Image with Overlay -->
                <img 
                    :src="cat.image" 
                    :alt="cat.name" 
                    class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition duration-500" 
                />
                <div 
                    :class="[
                        'absolute inset-0 transition duration-300',
                        activeCategory === cat.filterKey 
                            ? 'bg-[#14231C]/75' 
                            : 'bg-black/55 group-hover:bg-black/45'
                    ]"
                ></div>

                <!-- Category Content -->
                <div class="relative z-10 space-y-1">
                    <span class="text-xl sm:text-2xl block drop-shadow-sm">{{ cat.icon }}</span>
                    <span class="text-xs font-bold tracking-wider uppercase block drop-shadow-sm">
                        {{ cat.name }}
                    </span>
                </div>
            </button>
        </div>

    </section>
</template>

