<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    villas: Array,
});

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-TZ', { style: 'currency', currency: 'TZS', maximumFractionDigits: 0 }).format(val);
};

const getImageUrl = (path, fallback = 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=800&q=80') => {
    if (!path) return fallback;
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    return `/images/${path}`;
};
</script>

<template>
    <section class="max-w-7xl mx-auto px-6 py-16 md:py-24 border-t border-gray-150 space-y-12">
        <div class="text-center space-y-3">
            <span class="text-xs uppercase tracking-[4px] font-sans text-amber-600 font-bold block">EXTEND THE JOURNEY</span>
            <h2 class="text-3xl md:text-4xl font-serif text-gray-950 font-light leading-tight">
                Make Your Visit a Stay
            </h2>
            <p class="text-xs max-w-xl mx-auto text-gray-500 font-sans leading-relaxed">
                Slow down and extend your farm day. Stay in one of our private, luxury countryside villas nestled within the organic orchards of Kitonga.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div v-for="villa in villas.slice(0, 3)" :key="villa.id" class="bg-white border border-gray-150 rounded-lg overflow-hidden flex flex-col justify-between shadow-xs group">
                <div>
                    <div class="h-56 bg-gray-50 relative overflow-hidden">
                        <img loading="lazy" decoding="async" 
                            :src="getImageUrl(villa.featured_image)" 
                            class="absolute inset-0 w-full h-full object-cover transition duration-700 transform group-hover:scale-105" 
                            alt=""
                        />
                    </div>
                    <div class="p-6 space-y-3">
                        <h3 class="font-serif text-lg text-gray-900">{{ villa.name }}</h3>
                        <p class="text-xs text-gray-500 font-sans leading-relaxed line-clamp-3">{{ villa.description }}</p>
                    </div>
                </div>
                <div class="p-6 pt-0 border-t border-gray-50 mt-4 flex items-center justify-between">
                    <Link 
                        :href="route('villas.show', villa.slug)" 
                        prefetch
                        class="text-xs font-bold font-sans text-[#2C3E2B] hover:underline"
                    >
                        View Villa details →
                    </Link>
                    <Link 
                        :href="route('booking.form', { villa_id: villa.id })" 
                        prefetch
                        class="px-4 py-2 bg-[#2C3E2B] hover:bg-emerald-950 text-white text-[10px] uppercase font-bold tracking-wider rounded transition font-sans"
                    >
                        Book Stay
                    </Link>
                </div>
            </div>
        </div>
    </section>
</template>

