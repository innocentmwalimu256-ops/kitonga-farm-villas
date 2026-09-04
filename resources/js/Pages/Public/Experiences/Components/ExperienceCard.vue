<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    experience: Object,
});

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-TZ', { style: 'currency', currency: 'TZS', maximumFractionDigits: 0 }).format(val);
};

const getImageUrl = (path, fallback = 'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=800&q=80') => {
    if (!path) return fallback;
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    return `/images/${path}`;
};
</script>

<template>
    <div class="bg-white border border-gray-150 rounded-lg overflow-hidden flex flex-col md:flex-row justify-between shadow-xs hover:shadow-md transition duration-500 group">
        <!-- Image Area -->
        <div class="h-64 md:h-auto md:w-1/2 relative overflow-hidden bg-gray-50">
            <img loading="lazy" decoding="async" 
                :src="getImageUrl(experience.featured_image)" 
                class="absolute inset-0 w-full h-full object-cover transition duration-700 ease-out transform group-hover:scale-105" 
                alt=""
            />
            <div v-if="experience.category" class="absolute top-4 left-4 px-3 py-1 bg-[#1C261A] text-white text-[10px] uppercase font-bold tracking-wider font-sans rounded">
                {{ experience.category }}
            </div>
        </div>

        <!-- Content Area -->
        <div class="p-8 md:w-1/2 flex flex-col justify-between space-y-6">
            <div class="space-y-4">
                <div class="flex justify-between items-start">
                    <h3 class="font-serif text-xl md:text-2xl text-gray-900 leading-tight">
                        {{ experience.name }}
                    </h3>
                </div>
                <p class="text-xs text-gray-550 font-sans leading-relaxed line-clamp-4">
                    {{ experience.description }}
                </p>
                
                <!-- Highlights indicator -->
                <div v-if="experience.highlights && experience.highlights.length > 0" class="space-y-1.5 pt-2">
                    <span class="text-[9px] uppercase tracking-wider text-amber-600 font-bold block">Tour Highlights</span>
                    <ul class="text-[11px] text-gray-600 space-y-1 font-sans">
                        <li v-for="(hl, idx) in experience.highlights.slice(0, 2)" :key="idx" class="flex items-center gap-1.5">
                            <span class="text-amber-500 font-bold">•</span> {{ hl }}
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-6 space-y-4">
                <div class="flex justify-between items-center text-xs font-sans text-gray-400">
                    <span v-if="experience.duration">{{ experience.duration }}</span>
                    <span v-if="experience.capacity_per_slot">Up to {{ experience.capacity_per_slot }} Guests</span>
                </div>

                <div class="flex justify-between items-center">
                    <div class="flex flex-col">
                        <span class="text-[9px] uppercase tracking-widest text-gray-400 font-sans">Price Per Person</span>
                        <span class="text-base font-extrabold text-gray-900 font-sans">
                            {{ formatCurrency(experience.price) }}
                        </span>
                    </div>
                    <Link 
                        :href="route('experiences.show', experience.slug)" 
                        prefetch
                        class="px-5 py-2.5 bg-[#2C3E2B] text-white hover:bg-emerald-950 text-xs font-bold uppercase tracking-wider rounded transition font-sans inline-flex items-center gap-1.5"
                    >
                        <span>Explore Experience</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

