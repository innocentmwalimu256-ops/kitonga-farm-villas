<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

const isMobileMenuOpen = ref(false);

const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

// 20 Authentic Kitonga Estate Photographs
const galleryImages = [
    { id: 1, src: '/images/dji_0298.jpg', aspect: 'col-span-1 sm:col-span-2 aspect-[16/10]' },
    { id: 2, src: '/images/luxury_villa_img.jpg', aspect: 'col-span-1 aspect-[4/3]' },
    { id: 3, src: '/images/IMG_0064.jpg', aspect: 'col-span-1 aspect-[4/3]' },
    { id: 4, src: '/images/IMG_0362.jpg', aspect: 'col-span-1 aspect-[4/3]' },
    { id: 5, src: '/images/three_cows.jpg', aspect: 'col-span-1 aspect-[4/3]' },
    { id: 6, src: '/images/IMG_0389.jpg', aspect: 'col-span-1 aspect-[4/3]' },
    { id: 7, src: '/images/IMG_0119.jpg', aspect: 'col-span-1 sm:col-span-2 aspect-[16/10]' },
    { id: 8, src: '/images/IMG_0419.jpg', aspect: 'col-span-1 aspect-[4/3]' },
    { id: 9, src: '/images/IMG_0321.jpg', aspect: 'col-span-1 aspect-[4/3]' },
    { id: 10, src: '/images/IMG_0326.jpg', aspect: 'col-span-1 aspect-[4/3]' },
    { id: 11, src: '/images/farm_egg_trays.jpg', aspect: 'col-span-1 aspect-[4/3]' },
    { id: 12, src: '/images/IMG_0120.jpg', aspect: 'col-span-1 sm:col-span-2 aspect-[16/10]' },
    { id: 13, src: '/images/IMG_0094.jpg', aspect: 'col-span-1 aspect-[4/3]' },
    { id: 14, src: '/images/IMG_0328.jpg', aspect: 'col-span-1 aspect-[4/3]' },
    { id: 15, src: '/images/IMG_0404.jpg', aspect: 'col-span-1 aspect-[4/3]' },
    { id: 16, src: '/images/IMG_0334.jpg', aspect: 'col-span-1 aspect-[4/3]' },
    { id: 17, src: '/images/IMG_0061.jpg', aspect: 'col-span-1 sm:col-span-2 aspect-[16/10]' },
    { id: 18, src: '/images/IMG_0394.jpg', aspect: 'col-span-1 aspect-[4/3]' },
    { id: 19, src: '/images/IMG_0353.jpg', aspect: 'col-span-1 aspect-[4/3]' },
    { id: 20, src: '/images/IMG_0252.jpg', aspect: 'col-span-1 sm:col-span-3 aspect-[21/9]' },
];

// Lightbox State
const activeIndex = ref(null);

const openLightbox = (index) => {
    activeIndex.value = index;
    document.body.style.overflow = 'hidden';
};

const closeLightbox = () => {
    activeIndex.value = null;
    document.body.style.overflow = '';
};

const nextImage = () => {
    if (activeIndex.value !== null) {
        activeIndex.value = (activeIndex.value + 1) % galleryImages.length;
    }
};

const prevImage = () => {
    if (activeIndex.value !== null) {
        activeIndex.value = (activeIndex.value - 1 + galleryImages.length) % galleryImages.length;
    }
};

const handleKeyDown = (e) => {
    if (activeIndex.value === null) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowRight') nextImage();
    if (e.key === 'ArrowLeft') prevImage();
};

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
    document.body.style.overflow = '';
});
</script>

<template>
    <Head title="Visual Gallery — Kitonga Farm Villas" />

    <div class="bg-[#FAF8F5] text-[#1F2420] font-sans min-h-screen selection:bg-[#C98A3E] selection:text-white">
        
        <!-- 1. STICKY TOP NAVBAR -->
        <header class="sticky top-0 z-50 w-full px-6 py-4 md:px-12 flex justify-between items-center text-white bg-[#14231C]/95 backdrop-blur-md border-b border-white/10 shadow-md transition duration-300">
            
            <!-- Logo -->
            <Link :href="route('home')" class="flex flex-col items-start group cursor-pointer">
                <span class="font-serif text-lg md:text-2xl font-light text-[#F5F1E8] tracking-[4px] uppercase leading-none transition group-hover:text-[#C98A3E] duration-300">
                    KITONGA
                </span>
                <span class="font-sans text-[8px] md:text-[9px] font-medium text-[#C98A3E] tracking-[6px] uppercase leading-none mt-1 pl-[2px] transition group-hover:text-[#F5F1E8] duration-300">
                    FARMS VILLAS
                </span>
            </Link>

            <!-- Desktop Navigation Links -->
            <nav class="hidden md:flex space-x-6 lg:space-x-8 text-xs font-semibold uppercase tracking-widest text-gray-200 font-sans items-center">
                <Link :href="route('home')" prefetch class="hover:text-[#C98A3E] transition duration-200">Home</Link>
                <Link :href="route('villas')" prefetch class="hover:text-[#C98A3E] transition duration-200">Villas</Link>
                <Link :href="route('experiences')" prefetch class="hover:text-[#C98A3E] transition duration-200">Experiences</Link>
                <Link :href="route('farm')" prefetch class="hover:text-[#C98A3E] transition duration-200">Our Farm</Link>
                <Link :href="route('products')" prefetch class="hover:text-[#C98A3E] transition duration-200">Produce</Link>
                <Link :href="route('gallery')" prefetch class="text-[#E6C387] font-bold border-b-2 border-[#E6C387] pb-0.5">Gallery</Link>
                <Link :href="route('contact')" prefetch class="hover:text-[#C98A3E] transition duration-200">Contact</Link>
                <Link :href="route('login')" prefetch class="hover:text-[#C98A3E] transition duration-200">Sign In</Link>
            </nav>

            <!-- Desktop Primary CTA Button -->
            <Link 
                :href="route('booking.form')" 
                prefetch 
                class="hidden md:inline-flex px-5 py-2.5 bg-white text-gray-900 hover:bg-[#FAF8F5] text-xs font-extrabold uppercase tracking-wider rounded-lg transition font-sans shadow-md hover:shadow-lg cursor-pointer"
            >
                BOOK STAY
            </Link>

            <!-- Mobile Action & Hamburger Drawer Button -->
            <div class="flex items-center gap-3 md:hidden">
                <Link 
                    :href="route('booking.form')" 
                    prefetch 
                    class="px-3.5 py-1.5 bg-white text-gray-900 text-[11px] font-extrabold uppercase tracking-wider rounded transition font-sans shadow-xs cursor-pointer"
                >
                    BOOK STAY
                </Link>
                <button 
                    type="button" 
                    @click="toggleMobileMenu" 
                    class="p-1.5 text-white hover:text-[#C98A3E] focus:outline-none transition cursor-pointer"
                    aria-label="Toggle navigation menu"
                >
                    <svg v-if="!isMobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </header>

        <!-- Mobile Navigation Menu Drawer -->
        <div 
            v-if="isMobileMenuOpen" 
            class="md:hidden sticky top-[68px] left-0 w-full bg-[#14231C]/98 backdrop-blur-md border-b border-white/15 z-40 px-6 py-6 space-y-4 font-sans text-xs uppercase tracking-widest text-white shadow-2xl"
        >
            <div class="flex flex-col space-y-4">
                <Link :href="route('home')" @click="isMobileMenuOpen = false" class="hover:text-[#C98A3E] transition py-1">Home</Link>
                <Link :href="route('villas')" @click="isMobileMenuOpen = false" class="hover:text-[#C98A3E] transition py-1">Villas</Link>
                <Link :href="route('experiences')" @click="isMobileMenuOpen = false" class="hover:text-[#C98A3E] transition py-1">Experiences</Link>
                <Link :href="route('farm')" @click="isMobileMenuOpen = false" class="hover:text-[#C98A3E] transition py-1">Our Farm</Link>
                <Link :href="route('products')" @click="isMobileMenuOpen = false" class="hover:text-[#C98A3E] transition py-1">Produce</Link>
                <Link :href="route('gallery')" @click="isMobileMenuOpen = false" class="text-[#E6C387] font-bold py-1">Gallery</Link>
                <Link :href="route('contact')" @click="isMobileMenuOpen = false" class="hover:text-[#C98A3E] transition py-1">Contact</Link>
                <Link :href="route('login')" @click="isMobileMenuOpen = false" class="hover:text-[#C98A3E] transition py-1">Sign In</Link>
            </div>
            <div class="pt-2 border-t border-white/10">
                <Link 
                    :href="route('booking.form')" 
                    @click="isMobileMenuOpen = false"
                    class="block w-full py-3 bg-[#C98A3E] text-white text-center font-bold uppercase tracking-wider rounded-lg shadow-sm"
                >
                    BOOK STAY
                </Link>
            </div>
        </div>

        <!-- 2. HERO SECTION YA MANENO KUELEZEA KITONGA -->
        <section class="bg-[#14231C] text-white pt-20 pb-24 md:pt-28 md:pb-32 px-6 md:px-12 relative overflow-hidden text-center">
            <!-- Subtle background lighting -->
            <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#C98A3E_1px,transparent_1px)] [background-size:24px_24px] pointer-events-none"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[300px] bg-[#C98A3E]/15 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative max-w-4xl mx-auto space-y-6">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/5 border border-white/10 text-[#C98A3E] text-xs uppercase tracking-[3px] font-semibold">
                    <span>The Visual Sanctuary</span>
                </div>

                <h1 class="font-serif text-4xl sm:text-5xl md:text-6xl font-light tracking-wide leading-tight text-[#FAF8F5]">
                    A Glimpse Into <span class="text-[#E6C387] italic font-normal">Kitonga</span>
                </h1>

                <p class="font-sans text-sm sm:text-base md:text-lg text-[#FAF8F5]/80 font-normal leading-relaxed max-w-2xl mx-auto">
                    Immerse yourself in the tranquility of Kitonga Farm Villas. Set in the lush highlands of Komkonga, Tanga, explore where bespoke private architecture meets authentic organic agriculture, open pastures, and serene countryside horizons.
                </p>

                <div class="pt-4 flex items-center justify-center gap-3 text-xs uppercase tracking-widest text-[#E6C387]/90 font-medium">
                    <span>20 Authentic Perspectives</span>
                    <span>•</span>
                    <span>Highland Heritage</span>
                </div>
            </div>
        </section>

        <!-- 3. PURE LUXURY PHOTO GALLERY (20 IMAGES — RESIDENCE STYLE) -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
                <div 
                    v-for="(img, index) in galleryImages" 
                    :key="img.id"
                    @click="openLightbox(index)"
                    :class="[img.aspect, 'group relative overflow-hidden rounded-2xl bg-[#EBE5DB] shadow-sm hover:shadow-2xl transition-all duration-500 cursor-pointer']"
                >
                    <img 
                        :src="img.src" 
                        alt="Kitonga Farm Villas Perspective"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out"
                    />
                    
                    <!-- Clean Subtle Hover Overlay with Expand Icon -->
                    <div class="absolute inset-0 bg-black/25 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                        <div class="w-12 h-12 rounded-full bg-white/90 text-[#14231C] flex items-center justify-center shadow-lg transform scale-75 group-hover:scale-100 transition-transform duration-300">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

        </section>

        <!-- 4. FULLSCREEN LIGHTBOX -->
        <div 
            v-if="activeIndex !== null"
            class="fixed inset-0 z-50 bg-black/95 flex flex-col justify-between p-4 md:p-8 select-none"
            @click.self="closeLightbox"
        >
            <!-- Lightbox Top Controls -->
            <div class="flex items-center justify-between text-white/80 z-10">
                <div class="font-mono text-xs tracking-widest text-[#E6C387]">
                    {{ String(activeIndex + 1).padStart(2, '0') }} / {{ String(galleryImages.length).padStart(2, '0') }}
                </div>

                <button 
                    @click="closeLightbox"
                    class="p-2 text-white/70 hover:text-white transition rounded-full hover:bg-white/10 cursor-pointer"
                    aria-label="Close Lightbox"
                >
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Main Display Image & Navigation Arrows -->
            <div class="relative flex-1 flex items-center justify-center my-4 overflow-hidden">
                
                <!-- Prev Button -->
                <button 
                    @click.stop="prevImage"
                    class="absolute left-2 md:left-6 p-3 rounded-full bg-black/40 hover:bg-black/80 text-white border border-white/20 transition-all cursor-pointer z-10"
                    aria-label="Previous image"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <!-- Current Photograph -->
                <img 
                    :src="galleryImages[activeIndex].src" 
                    alt="Kitonga High Resolution Preview"
                    class="max-h-[82vh] max-w-[94vw] object-contain rounded-lg shadow-2xl transition-all duration-300"
                />

                <!-- Next Button -->
                <button 
                    @click.stop="nextImage"
                    class="absolute right-2 md:right-6 p-3 rounded-full bg-black/40 hover:bg-black/80 text-white border border-white/20 transition-all cursor-pointer z-10"
                    aria-label="Next image"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

            </div>

            <!-- Lightbox Bottom Navigation Indicator -->
            <div class="text-center text-xs text-white/50 font-sans">
                Use <span class="text-white/80 font-semibold">←</span> and <span class="text-white/80 font-semibold">→</span> keys to navigate · <span class="text-white/80 font-semibold">ESC</span> to close
            </div>
        </div>

        <!-- 5. FOOTER -->
        <footer class="bg-[#14231C] text-gray-400 text-xs py-14 border-t border-white/10 font-sans">
            <div class="max-w-6xl mx-auto px-6 grid grid-cols-1 md:grid-cols-3 gap-10">
                <div class="space-y-3">
                    <p class="font-bold text-white text-sm font-serif uppercase tracking-widest">KITONGA FARM VILLAS</p>
                    <p class="text-gray-400 text-xs leading-relaxed">A luxury countryside sanctuary and organic farm-stay destination in Komkonga, Tanga, Tanzania.</p>
                </div>
                <div class="space-y-2">
                    <p class="font-bold text-white uppercase tracking-wider text-xs">Quick Links</p>
                    <div class="flex flex-col space-y-1.5">
                        <Link :href="route('villas')" class="hover:text-white transition">Private Residences & Villas</Link>
                        <Link :href="route('experiences')" class="hover:text-white transition">Highland Experiences</Link>
                        <Link :href="route('farm')" class="hover:text-white transition">Our Organic Farm</Link>
                        <Link :href="route('products')" class="hover:text-white transition">Farm Fresh Harvest</Link>
                        <Link :href="route('gallery')" class="hover:text-white transition">Visual Gallery</Link>
                    </div>
                </div>
                <div class="space-y-2">
                    <p class="font-bold text-white uppercase tracking-wider text-xs">Contact Concierge</p>
                    <p class="text-gray-400">Komkonga Village, Tanga, Tanzania</p>
                    <p class="text-gray-400">Phone: +255 758 774 695</p>
                    <p class="text-gray-400">Email: info@kitongafarmvillas.com</p>
                </div>
            </div>
            <div class="max-w-6xl mx-auto px-6 mt-10 pt-6 border-t border-white/10 text-center text-gray-500">
                © 2026 Kitonga Farm Villas. All rights reserved.
            </div>
        </footer>

    </div>
</template>
