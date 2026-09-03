<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    villas: {
        type: Array,
        default: () => [],
    },
});

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-TZ', { style: 'currency', currency: 'TZS', maximumFractionDigits: 0 }).format(val);
};

const getImageUrl = (path) => {
    if (!path) return '/images/gallery_img_0220.webp';
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    return `/images/${path}`;
};
</script>

<template>
    <section class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-12 py-16 md:py-24 space-y-12">
        
        <div class="text-center space-y-3 max-w-2xl mx-auto">
            <span class="text-[10px] md:text-[11px] uppercase tracking-[4px] font-sans font-bold text-[#C98A3E] block">
                EXTEND YOUR EXPERIENCE
            </span>
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-serif font-light text-[#1F2420]">
                Make It a Stay
            </h2>
            <p class="text-xs sm:text-sm text-[#6B6D66] font-sans">
                Turn your day farm tour into an overnight countryside retreat inside our private luxury villas.
            </p>
        </div>

        <!-- 3 Dynamic Database-Driven Villa Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div 
                v-for="villa in villas" 
                :key="villa.id"
                class="bg-white rounded-3xl border border-[#E5E0D8] overflow-hidden shadow-sm hover:shadow-xl transition duration-500 flex flex-col justify-between group"
            >
                <!-- Villa Image -->
                <div class="aspect-[16/10] overflow-hidden bg-[#14231C] relative">
                    <img loading="lazy" decoding="async" 
                        :src="getImageUrl(villa.featured_image || villa.image_url)" 
                        :alt="villa.name" 
                        class="w-full h-full object-cover group-hover:scale-105 transition duration-700 ease-out" 
                    />
                </div>

                <!-- Villa Details -->
                <div class="p-7 space-y-5 flex-1 flex flex-col justify-between font-sans">
                    <div class="space-y-2 text-center">
                        <h3 class="text-2xl font-serif font-light text-[#1F2420] group-hover:text-[#C98A3E] transition">
                            {{ villa.name }}
                        </h3>
                        <p v-if="villa.short_description" class="text-xs text-gray-500 line-clamp-2">
                            {{ villa.short_description }}
                        </p>
                        <div class="flex items-baseline justify-center gap-1 pt-1">
                            <span class="text-xl font-bold text-[#1B2E22]">
                                {{ formatCurrency(villa.base_price) }}
                            </span>
                            <span class="text-xs text-gray-500">/ night</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="grid grid-cols-2 gap-2.5 pt-3 border-t border-gray-100 text-xs font-bold uppercase tracking-wider">
                        <Link 
                            :href="route('villas.show', villa.slug)" 
                            prefetch
                            class="py-3 px-3 bg-[#FAF8F5] hover:bg-gray-100 text-[#1B2E22] border border-gray-200 rounded-xl text-center transition cursor-pointer"
                        >
                            View Villa
                        </Link>
                        <Link 
                            :href="route('booking.form')" 
                            prefetch
                            class="py-3 px-3 bg-[#1B2E22] hover:bg-[#C98A3E] text-[#F7F3EA] rounded-xl text-center transition shadow-xs cursor-pointer"
                        >
                            Book Stay
                        </Link>
                    </div>
                </div>

            </div>
        </div>

    </section>
</template>


