<script setup>
import { ref } from 'vue';

const props = defineProps({
    gallery: Array,
});

const activeImageIndex = ref(null);

const openLightbox = (index) => {
    activeImageIndex.value = index;
};

const closeLightbox = () => {
    activeImageIndex.value = null;
};

const nextImage = () => {
    if (activeImageIndex.value !== null) {
        activeImageIndex.value = (activeImageIndex.value + 1) % props.gallery.length;
    }
};

const prevImage = () => {
    if (activeImageIndex.value !== null) {
        activeImageIndex.value = (activeImageIndex.value - 1 + props.gallery.length) % props.gallery.length;
    }
};

const getImageUrl = (path, fallback = 'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=1200&q=80') => {
    if (!path) return fallback;
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    return `/images/${path}`;
};
</script>

<template>
    <div v-if="gallery && gallery.length > 0" class="space-y-4 pt-4">
        <div class="flex justify-between items-baseline">
            <h3 class="font-serif text-2xl text-gray-900 font-light">Gallery & Scenery</h3>
            <span class="text-xs font-sans text-gray-400 font-normal">{{ gallery.length }} photos</span>
        </div>

        <!-- Responsive Grid for 5-6 Photos (3 columns desktop, 2 columns mobile) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 md:gap-4">
            <div 
                v-for="(img, index) in gallery" 
                :key="index"
                @click="openLightbox(index)"
                class="h-36 sm:h-44 md:h-48 bg-gray-100 rounded-xl overflow-hidden border border-gray-200/80 relative cursor-pointer group shadow-2xs hover:shadow-md transition duration-300"
            >
                <img loading="lazy" decoding="async" 
                    :src="getImageUrl(img)" 
                    class="absolute inset-0 w-full h-full object-cover transition duration-500 transform group-hover:scale-105" 
                    alt="Gallery photo" 
                />
                <div class="absolute inset-0 bg-black/10 group-hover:bg-black/0 transition duration-300"></div>

                <!-- Hover zoom hint badge -->
                <div class="absolute bottom-2 right-2 p-1.5 bg-black/40 group-hover:bg-black/60 rounded-md text-white opacity-0 group-hover:opacity-100 transition duration-200">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Lightbox Teleport Modal -->
        <teleport to="body">
            <div 
                v-if="activeImageIndex !== null" 
                class="fixed inset-0 z-100 bg-black/95 backdrop-blur-xs flex flex-col justify-center items-center p-4 select-none"
                @click.self="closeLightbox"
                @keydown.esc="closeLightbox"
                tabindex="0"
            >
                <!-- Close Button -->
                <button 
                    @click="closeLightbox" 
                    class="absolute top-5 right-6 text-white/80 hover:text-white text-3xl font-light font-sans z-20 cursor-pointer transition"
                >
                    âœ•
                </button>

                <!-- Navigation Buttons -->
                <button 
                    @click="prevImage" 
                    class="absolute left-4 sm:left-8 top-1/2 -translate-y-1/2 text-white/80 hover:text-white text-4xl sm:text-5xl font-light font-sans z-20 p-3 cursor-pointer transition"
                >
                    â€¹
                </button>
                <button 
                    @click="nextImage" 
                    class="absolute right-4 sm:right-8 top-1/2 -translate-y-1/2 text-white/80 hover:text-white text-4xl sm:text-5xl font-light font-sans z-20 p-3 cursor-pointer transition"
                >
                    â€º
                </button>

                <!-- Main Image Container -->
                <div class="max-w-5xl max-h-[82vh] flex items-center justify-center relative p-2">
                    <img loading="lazy" decoding="async" 
                        :src="getImageUrl(gallery[activeImageIndex])" 
                        class="max-w-full max-h-[82vh] object-contain rounded-lg shadow-2xl" 
                        alt="Lightbox Image" 
                    />
                </div>

                <!-- Index indicator -->
                <div class="text-white/70 font-sans text-xs pt-3 uppercase tracking-widest font-light">
                    {{ activeImageIndex + 1 }} / {{ gallery.length }}
                </div>
            </div>
        </teleport>
    </div>
</template>

