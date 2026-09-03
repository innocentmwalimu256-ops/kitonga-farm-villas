<script setup>
import { ref } from 'vue';

const openIndex = ref(0);

const toggleAccordion = (idx) => {
    openIndex.value = openIndex.value === idx ? -1 : idx;
};

const guideItems = [
    {
        title: 'Footwear & Attire for Walking the Farm',
        icon: '👟',
        content: 'We recommend comfortable closed walking shoes or sneakers, lightweight clothing, and a sun hat for outdoor paths. The trails are natural and gentle.',
    },
    {
        title: 'Swimming Pool & Countryside Lounge',
        icon: '🏊',
        content: 'Access to our outdoor swimming pool is included with both farm tours. Feel free to bring your swimwear and towel to refresh after the walking tour.',
    },
    {
        title: 'Visiting with Children & Families',
        icon: '👨‍👩‍👧‍👦',
        content: 'Both the Normal Farm Tour and General Farm Tour are suitable for guests of all ages. Children particularly enjoy seeing our dairy cows, goats, and free-range poultry.',
    },
    {
        title: 'Combining Day Tours with a Villa Stay',
        icon: '🏡',
        content: 'Day visitors can extend their countryside experience by booking overnight stays in our Luxury, Semi Luxury, or Family Villas with full privacy.',
    },
];
</script>

<template>
    <section class="max-w-4xl mx-auto px-6 sm:px-8 lg:px-12 py-16 md:py-24 space-y-10">
        
        <!-- Section Header -->
        <div class="text-center space-y-3">
            <span class="text-[10px] md:text-[11px] uppercase tracking-[4px] font-sans font-bold text-[#C98A3E] block">
                VISITOR ESSENTIALS
            </span>
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-serif font-light text-[#1F2420]">
                Planning Your Visit
            </h2>
            <p class="text-xs sm:text-sm text-[#6B6D66] font-sans">
                Helpful guidelines to ensure a comfortable and memorable day on the farm.
            </p>
        </div>

        <!-- Accordion Container -->
        <div class="space-y-4 font-sans text-xs">
            <div 
                v-for="(item, idx) in guideItems" 
                :key="idx"
                class="bg-white rounded-2xl border border-[#E5E0D8] overflow-hidden transition shadow-xs"
            >
                <button 
                    type="button"
                    @click="toggleAccordion(idx)"
                    class="w-full p-5 sm:p-6 text-left flex items-center justify-between gap-4 font-medium text-gray-900 hover:text-[#C98A3E] transition cursor-pointer"
                >
                    <div class="flex items-center gap-3">
                        <span class="text-lg">{{ item.icon }}</span>
                        <span class="text-sm font-serif font-normal text-[#1B2E22]">{{ item.title }}</span>
                    </div>
                    <span class="text-lg text-[#C98A3E] transform transition duration-300 font-sans" :class="{ 'rotate-180': openIndex === idx }">
                        {{ openIndex === idx ? '−' : '+' }}
                    </span>
                </button>

                <div 
                    v-if="openIndex === idx" 
                    class="px-6 pb-6 pt-1 text-gray-600 leading-relaxed border-t border-gray-100 bg-[#FAF8F5]/50"
                >
                    <p>{{ item.content }}</p>
                </div>
            </div>
        </div>

    </section>
</template>
