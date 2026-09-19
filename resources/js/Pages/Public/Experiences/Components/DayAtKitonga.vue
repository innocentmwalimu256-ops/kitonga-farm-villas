<script setup>
const journeyMoments = [
    {
        title: 'Arrive & Awaken',
        summary: 'Crisp morning air and countryside tranquility.',
        narrative: 'Arrive into the serene quiet of Kitonga. Walk through gentle mountain breezes and listen to the dawn birds over the trees.',
        svgPath: 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z',
    },
    {
        title: 'Walk & Discover',
        summary: 'Explore crops, greenhouses, and livestock.',
        narrative: 'Wander along palm pathways, observe our dairy cattle, goats, and poultry, and see seasonal plantations thriving in fertile soil.',
        svgPath: 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    },
    {
        title: 'Unwind & Swim',
        summary: 'Cool off in our clean countryside pool.',
        narrative: 'Take a refreshing swim in the outdoor pool surrounded by palm paths, and relax at the rural lounge with chilled refreshments.',
        svgPath: 'M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21a9 9 0 100-18 9 9 0 000 18z',
    },
    {
        title: 'Sunset Calm',
        summary: 'Golden hour serenity over the landscape.',
        narrative: 'Watch the sun sink behind the horizon as the countryside winds cool down, bringing a peaceful conclusion to your day on the farm.',
        svgPath: 'M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z',
    },
];
</script>

<template>
    <section class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-12 py-16 md:py-20">
        
        <div class="text-center space-y-3 mb-12 max-w-2xl mx-auto">
            <span class="text-[10px] md:text-[11px] uppercase tracking-[4px] font-sans font-bold text-[#C98A3E] block">
                NARRATIVE PROGRESSION
            </span>
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-serif font-light text-[#1F2420]">
                A Day at Kitonga
            </h2>
            <p class="text-xs sm:text-sm text-[#6B6D66] font-sans">
                How an unhurried day unfolds naturally from morning quiet to evening calm.
            </p>
        </div>

        <!-- 4 Narrative Story Cards (No Invented Times) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 font-sans">
            <div 
                v-for="(moment, i) in journeyMoments" 
                :key="i"
                class="bg-white border border-[#E5E0D8] rounded-3xl p-7 space-y-4 shadow-sm hover:shadow-md hover:border-[#C98A3E]/40 transition duration-300 flex flex-col justify-between"
            >
                <div class="space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-[#FAF8F5] border border-gray-200 flex items-center justify-center text-[#C98A3E] shadow-xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="moment.svgPath" />
                        </svg>
                    </div>

                    <div>
                        <h4 class="text-xl font-serif font-medium text-[#1B2E22]">
                            {{ moment.title }}
                        </h4>
                    </div>

                    <p class="text-xs font-semibold text-[#1F2420] pt-1">
                        {{ moment.summary }}
                    </p>

                    <p class="text-xs text-[#6B6D66] leading-relaxed">
                        {{ moment.narrative }}
                    </p>
                </div>
            </div>
        </div>

    </section>
</template>
