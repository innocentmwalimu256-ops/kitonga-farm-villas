<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    cms: Object,
    uploaded_media: Array,
});

const isMobileMenuOpen = ref(false);
const activeCategory = ref('all');

const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

// 20 Authentic Kitonga Estate Photographs
const baseGalleryImages = [
    { id: 'b1', src: '/images/dji_0298.webp', title: 'Estate Panoramic View', category: 'villas', media_type: 'image', aspect: 'col-span-1 sm:col-span-2 aspect-[16/10]' },
    { id: 'b2', src: '/images/luxury_villa_img.webp', title: 'Luxury Villa Sanctuary', category: 'villas', media_type: 'image', aspect: 'col-span-1 aspect-[4/3]' },
    { id: 'b3', src: '/images/IMG_0064.webp', title: 'Highland Sunset & Architecture', category: 'villas', media_type: 'image', aspect: 'col-span-1 aspect-[4/3]' },
    { id: 'b4', src: '/images/IMG_0362.webp', title: 'Organic Avocado Tree Orchards', category: 'farm', media_type: 'image', aspect: 'col-span-1 aspect-[4/3]' },
    { id: 'b5', src: '/images/three_cows.webp', title: 'Purebred Dairy Pastoral Zone', category: 'farm', media_type: 'image', aspect: 'col-span-1 aspect-[4/3]' },
    { id: 'b6', src: '/images/IMG_0389.webp', title: 'Horticulture & Greenhouse Zones', category: 'farm', media_type: 'image', aspect: 'col-span-1 aspect-[4/3]' },
    { id: 'b7', src: '/images/IMG_0119.webp', title: 'Verdant Farm Greenhouses', category: 'farm', media_type: 'image', aspect: 'col-span-1 sm:col-span-2 aspect-[16/10]' },
    { id: 'b8', src: '/images/IMG_0419.webp', title: 'Poultry & Layer Coops', category: 'farm', media_type: 'image', aspect: 'col-span-1 aspect-[4/3]' },
    { id: 'b9', src: '/images/IMG_0321.webp', title: 'Tasting Pure Kitonga Honey', category: 'food', media_type: 'image', aspect: 'col-span-1 aspect-[4/3]' },
    { id: 'b10', src: '/images/IMG_0326.webp', title: 'Fresh Dairy Yogurt & Milk', category: 'food', media_type: 'image', aspect: 'col-span-1 aspect-[4/3]' },
    { id: 'b11', src: '/images/farm_egg_trays.webp', title: 'Daily Harvest Farm Eggs', category: 'food', media_type: 'image', aspect: 'col-span-1 aspect-[4/3]' },
    { id: 'b12', src: '/images/IMG_0120.webp', title: 'Guided Agritourism Walking Tour', category: 'experiences', media_type: 'image', aspect: 'col-span-1 sm:col-span-2 aspect-[16/10]' },
    { id: 'b13', src: '/images/IMG_0094.webp', title: 'Estate Gardens & Flora', category: 'nature', media_type: 'image', aspect: 'col-span-1 aspect-[4/3]' },
    { id: 'b14', src: '/images/IMG_0328.webp', title: 'Bespoke Private Villa Suite', category: 'villas', media_type: 'image', aspect: 'col-span-1 aspect-[4/3]' },
    { id: 'b15', src: '/images/IMG_0404.webp', title: 'Modern Irrigation & Drip System', category: 'farm', media_type: 'image', aspect: 'col-span-1 aspect-[4/3]' },
    { id: 'b16', src: '/images/IMG_0334.webp', title: 'Pasture Cattle Grazing', category: 'farm', media_type: 'image', aspect: 'col-span-1 aspect-[4/3]' },
    { id: 'b17', src: '/images/IMG_0061.webp', title: 'Evening Glow over Villa Lounge', category: 'villas', media_type: 'image', aspect: 'col-span-1 sm:col-span-2 aspect-[16/10]' },
    { id: 'b18', src: '/images/IMG_0394.webp', title: 'Organic Vegetable Harvesting', category: 'food', media_type: 'image', aspect: 'col-span-1 aspect-[4/3]' },
    { id: 'b19', src: '/images/IMG_0353.webp', title: 'Apiary Bee Keeping Sanctuary', category: 'experiences', media_type: 'image', aspect: 'col-span-1 aspect-[4/3]' },
    { id: 'b20', src: '/images/IMG_0252.webp', title: 'Highland Countryside Horizon', category: 'nature', media_type: 'image', aspect: 'col-span-1 sm:col-span-3 aspect-[21/9]' },
];

// Combined Dynamic Gallery Items
const allGalleryItems = computed(() => {
    const dynamicItems = (props.uploaded_media || []).map((m, idx) => ({
        id: `u_${m.id}`,
        src: m.path,
        title: m.title || m.name,
        caption: m.caption || '',
        category: m.category || 'general',
        media_type: m.media_type || 'image',
        aspect: m.media_type === 'video' ? 'col-span-1 sm:col-span-2 aspect-[16/9]' : 'col-span-1 aspect-[4/3]',
        is_dynamic: true,
    }));

    return [...dynamicItems, ...baseGalleryImages];
});

// Filtered Gallery by Category Tab
const filteredGalleryItems = computed(() => {
    if (activeCategory.value === 'all') {
        return allGalleryItems.value;
    }
    if (activeCategory.value === 'videos') {
        return allGalleryItems.value.filter(item => item.media_type === 'video');
    }
    return allGalleryItems.value.filter(item => item.category === activeCategory.value);
});

// Lightbox State
const activeIndex = ref(null);

const currentItem = computed(() => {
    if (activeIndex.value === null) return null;
    return filteredGalleryItems.value[activeIndex.value] || null;
});

const openLightbox = (index) => {
    activeIndex.value = index;
    document.body.style.overflow = 'hidden';
};

const closeLightbox = () => {
    activeIndex.value = null;
    document.body.style.overflow = '';
};

const nextImage = () => {
    if (activeIndex.value !== null && filteredGalleryItems.value.length > 0) {
        activeIndex.value = (activeIndex.value + 1) % filteredGalleryItems.value.length;
    }
};

const prevImage = () => {
    if (activeIndex.value !== null && filteredGalleryItems.value.length > 0) {
        activeIndex.value = (activeIndex.value - 1 + filteredGalleryItems.value.length) % filteredGalleryItems.value.length;
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

const categories = [
    { id: 'all', label: 'All Perspectives' },
    { id: 'villas', label: 'Luxury Villas' },
    { id: 'farm', label: 'Shamba & Dairy' },
    { id: 'experiences', label: 'Tours & Experiences' },
    { id: 'food', label: 'Produce & Dining' },
    { id: 'videos', label: '🎥 Videos' },
];
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
                <Link :href="route('gallery')" prefetch class="text-[#E6C387] font-bold border-b-2 border-[#C98A3E] pb-1">Gallery</Link>
                <Link :href="route('contact')" prefetch class="hover:text-[#C98A3E] transition duration-200">Contact</Link>
            </nav>

            <!-- Right CTA Button -->
            <div class="flex items-center space-x-4">
                <Link 
                    :href="route('booking.form')" 
                    prefetch 
                    class="px-3.5 py-1.5 bg-white text-gray-900 text-[11px] font-extrabold uppercase tracking-wider rounded transition font-sans shadow-xs cursor-pointer hover:bg-[#E6C387]"
                >
                    BOOK STAY
                </Link>
                <button 
                    type="button" 
                    @click="toggleMobileMenu" 
                    class="p-1.5 text-white hover:text-[#C98A3E] focus:outline-none transition cursor-pointer md:hidden"
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

        <!-- 2. HERO SECTION -->
        <section class="bg-[#14231C] text-white pt-20 pb-20 md:pt-28 md:pb-24 px-6 md:px-12 relative overflow-hidden text-center">
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

                <!-- Category Filter Pills -->
                <div class="pt-6 flex flex-wrap items-center justify-center gap-2 font-sans">
                    <button 
                        v-for="cat in categories" 
                        :key="cat.id"
                        @click="activeCategory = cat.id"
                        class="px-4 py-2 rounded-full text-xs uppercase tracking-wider font-bold transition duration-200 cursor-pointer"
                        :class="activeCategory === cat.id 
                            ? 'bg-[#C98A3E] text-white shadow-lg' 
                            : 'bg-white/10 hover:bg-white/20 text-gray-300 border border-white/10'"
                    >
                        {{ cat.label }}
                    </button>
                </div>
            </div>
        </section>

        <!-- 3. PURE LUXURY PHOTO & VIDEO GALLERY -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-20">
            
            <div v-if="filteredGalleryItems.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
                <div 
                    v-for="(item, index) in filteredGalleryItems" 
                    :key="item.id"
                    @click="openLightbox(index)"
                    :class="[item.aspect, 'group relative overflow-hidden rounded-2xl bg-[#14231C] shadow-sm hover:shadow-2xl transition-all duration-500 cursor-pointer']"
                >
                    <!-- Image Card -->
                    <img 
                        v-if="item.media_type === 'image'"
                        loading="lazy" 
                        decoding="async" 
                        :src="item.src" 
                        :alt="item.title || 'Kitonga Farm Perspective'"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out"
                    />

                    <!-- Video Card -->
                    <div v-else class="relative w-full h-full bg-black overflow-hidden flex items-center justify-center">
                        <video 
                            :src="item.src" 
                            muted 
                            loop 
                            class="w-full h-full object-cover opacity-90 group-hover:opacity-100 group-hover:scale-105 transition duration-700"
                            onmouseover="this.play()" 
                            onmouseout="this.pause()"
                        ></video>

                        <!-- Video Badge -->
                        <div class="absolute top-3 right-3 px-2.5 py-1 rounded-md bg-[#C98A3E] text-white text-[10px] font-extrabold uppercase tracking-wider shadow-lg flex items-center gap-1">
                            <span>▶ Video Clip</span>
                        </div>

                        <!-- Center Play Button -->
                        <div class="absolute inset-0 flex items-center justify-center bg-black/20 group-hover:bg-transparent transition pointer-events-none">
                            <div class="w-14 h-14 rounded-full bg-white/90 text-[#14231C] flex items-center justify-center shadow-2xl group-hover:scale-110 transition">
                                <svg class="w-6 h-6 ml-1" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Clean Hover Caption Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-5 text-white">
                        <span v-if="item.category" class="text-[10px] uppercase font-bold tracking-widest text-[#E6C387] mb-1">
                            {{ item.category }}
                        </span>
                        <h3 class="font-serif text-base md:text-lg font-normal leading-tight text-white">
                            {{ item.title }}
                        </h3>
                        <p v-if="item.caption" class="text-xs text-gray-300 font-sans mt-1 line-clamp-2">
                            {{ item.caption }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Empty category state -->
            <div v-else class="py-20 text-center space-y-3">
                <p class="text-gray-400 font-sans text-sm">No media found in this category.</p>
                <button @click="activeCategory = 'all'" class="text-[#C98A3E] underline font-bold text-xs">
                    View All Perspectives
                </button>
            </div>

        </section>

        <!-- 4. FULLSCREEN LIGHTBOX (IMAGES & VIDEOS) -->
        <div 
            v-if="activeIndex !== null && currentItem"
            class="fixed inset-0 z-50 bg-black/95 flex flex-col justify-between p-4 md:p-8 select-none"
            @click.self="closeLightbox"
        >
            <!-- Lightbox Top Controls -->
            <div class="flex items-center justify-between text-white/80 z-10">
                <div class="font-mono text-xs tracking-widest text-[#E6C387]">
                    {{ String(activeIndex + 1).padStart(2, '0') }} / {{ String(filteredGalleryItems.length).padStart(2, '0') }}
                </div>

                <div class="text-center font-serif text-sm text-white hidden sm:block">
                    {{ currentItem.title }}
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

            <!-- Main Display Image or Video & Navigation Arrows -->
            <div class="relative flex-1 flex items-center justify-center my-4 overflow-hidden">
                
                <!-- Prev Button -->
                <button 
                    @click.stop="prevImage"
                    class="absolute left-2 md:left-6 p-3 rounded-full bg-black/40 hover:bg-black/80 text-white border border-white/20 transition-all cursor-pointer z-20"
                    aria-label="Previous image"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <!-- Current Photo Display -->
                <img 
                    v-if="currentItem.media_type === 'image'"
                    loading="lazy" 
                    decoding="async" 
                    :src="currentItem.src" 
                    :alt="currentItem.title || 'Kitonga High Resolution Preview'"
                    class="max-h-[80vh] max-w-[92vw] object-contain rounded-lg shadow-2xl transition-all duration-300"
                />

                <!-- Current Video Display -->
                <video 
                    v-else 
                    :src="currentItem.src" 
                    controls 
                    autoplay 
                    class="max-h-[80vh] max-w-[92vw] w-full max-w-4xl rounded-xl shadow-2xl"
                ></video>

                <!-- Next Button -->
                <button 
                    @click.stop="nextImage"
                    class="absolute right-2 md:right-6 p-3 rounded-full bg-black/40 hover:bg-black/80 text-white border border-white/20 transition-all cursor-pointer z-20"
                    aria-label="Next image"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

            </div>

            <!-- Lightbox Bottom Navigation Indicator & Details -->
            <div class="text-center space-y-1 text-xs text-white/70 font-sans">
                <p v-if="currentItem.caption" class="text-sm text-[#E6C387] max-w-xl mx-auto">{{ currentItem.caption }}</p>
                <p class="text-[11px] text-white/40">
                    Use <span class="text-white/80 font-semibold">←</span> and <span class="text-white/80 font-semibold">→</span> keys to navigate · <span class="text-white/80 font-semibold">ESC</span> to close
                </p>
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
            <div class="max-w-6xl mx-auto px-6 mt-10 pt-6 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-4 text-gray-400 text-xs">
                <p>© 2026 Kitonga Farm Villas. All rights reserved.</p>
                <div class="flex items-center gap-2 text-[11px]">
                    <span class="text-gray-400">Created by</span>
                    <a 
                        href="https://wa.me/255675315279" 
                        target="_blank" 
                        rel="noopener noreferrer" 
                        class="inline-flex items-center gap-1.5 px-3 py-1 bg-[#1E3326] hover:bg-[#C98A3E] text-[#E6C387] hover:text-white rounded-full border border-[#C98A3E]/30 transition duration-300 font-medium shadow-xs"
                        title="Chat on WhatsApp"
                    >
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>0675 315 279</span>
                    </a>
                </div>
            </div>
        </footer>

    </div>
</template>
