<script setup>
const journeyMoments = [
    {
        title: 'Arrive & Awaken',
        icon: '🌅',
        summary: 'Crisp morning air and countryside tranquility.',
        narrative: 'Arrive into the serene quiet of Kitonga. Walk through gentle mountain breezes and listen to the dawn birds over the trees.',
    },
    {
        title: 'Walk & Discover',
        icon: '🌿',
        summary: 'Explore crops, greenhouses, and livestock.',
        narrative: 'Wander along palm pathways, observe our dairy cattle, goats, and poultry, and see seasonal plantations thriving in fertile soil.',
    },
    {
        title: 'Unwind & Swim',
        icon: '🏊',
        summary: 'Cool off in our clean countryside pool.',
        narrative: 'Take a refreshing swim in the outdoor pool surrounded by palm paths, and relax at the rural lounge with chilled refreshments.',
    },
    {
        title: 'Sunset Calm',
        icon: '🌄',
        summary: 'Golden hour serenity over the landscape.',
        narrative: 'Watch the sun sink behind the horizon as the countryside winds cool down, bringing a peaceful conclusion to your day on the farm.',
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
                    <div class="w-12 h-12 rounded-2xl bg-[#FAF8F5] border border-gray-200 flex items-center justify-center text-2xl shadow-xs">
                        {{ moment.icon }}
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
