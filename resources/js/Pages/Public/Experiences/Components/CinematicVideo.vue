<template>
    <section class="relative h-[55vh] md:h-[65vh] w-full overflow-hidden bg-black flex items-center justify-center my-16">
        <!-- Loop Video -->
        <video 
            autoplay 
            muted 
            loop 
            playsinline 
            poster="https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=1600&q=80"
            class="absolute inset-0 w-full h-full object-cover opacity-50"
        >
            <source src="/videos/hero_cinematic.mp4" type="video/mp4" />
        </video>

        <!-- Overlay -->
        <div class="absolute inset-0 bg-black/40"></div>

        <!-- Content Overlay -->
        <div class="relative z-10 text-center px-6 space-y-6 max-w-4xl">
            <span class="text-xs uppercase tracking-[6px] font-sans text-amber-500 font-bold block">EXPERIENCE</span>
            <h2 class="text-3xl md:text-5xl font-light text-[#F5F1E8] font-serif leading-tight">
                See Kitonga Through a Different Lens
            </h2>
            <div class="flex flex-wrap justify-center items-center gap-6 md:gap-12 text-[#F5F1E8]/90 font-sans text-xs tracking-wider uppercase pt-4 font-light">
                <span>Walk the farm</span>
                <span class="hidden md:inline text-amber-500">•</span>
                <span>Meet the animals</span>
                <span class="hidden md:inline text-amber-500">•</span>
                <span>Discover the landscape</span>
                <span class="hidden md:inline text-amber-500">•</span>
                <span>Slow down</span>
            </div>
        </div>
    </section>
</template>
