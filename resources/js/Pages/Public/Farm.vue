<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    cms: {
        type: Object,
        default: () => ({}),
    },
    products: {
        type: Array,
        default: () => [],
    },
    experiences: {
        type: Array,
        default: () => [],
    },
});

const isMobileMenuOpen = ref(false);
const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-TZ', { style: 'currency', currency: 'TZS', maximumFractionDigits: 0 }).format(val);
};

const getImageUrl = (path, fallback = '/images/general_farm_hero.webp') => {
    if (!path) return fallback;
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    return `/images/${path}`;
};

// Authentic Farm Sanctuary Zones Data (Accurately Matching Every Real Photo)
const farmZones = [
    {
        number: '01',
        tag: 'Dairy Cattle & Calf Nursery',
        title: 'Pedigree Dairy Calves & Barn Feeding Stall',
        subtitle: 'Healthy Friesian and cross-breed calves nourished in clean wooden stalls.',
        description: 'Our dairy calf barn provides a clean, well-ventilated shelter where young calves feed on specialized nutrient-rich rations and forage. Handled with meticulous hygiene and veterinary care, they form the foundation of Kitonga Farm\'s future high-yielding dairy herd.',
        specs: ['Nutrient-Rich Feed Rations', 'Sheltered Wooden Stalls', 'Daily Health & Growth Checks', 'Pedigree Dairy Bloodline'],
        image: '/images/IMG_0321.webp',
        reverse: false,
    },
    {
        number: '02',
        tag: 'Layer Poultry & Fresh Eggs',
        title: 'Modern Tiered Layer Poultry House & Fresh Eggs',
        subtitle: 'Automated tiered layer cage system producing fresh, golden-yolk farm eggs daily.',
        description: 'Our modern layer poultry house features hygienic tiered cage infrastructure with automated water nipple lines and clean egg-roll collection trays. Hens receive balanced, mineral-rich grain rations, ensuring clean, fresh organic eggs harvested every morning.',
        specs: ['Tiered Layer Cage Systems', 'Automated Nipple Drinkers', 'Daily Morning Egg Gathering', 'Strict Poultry Biosecurity'],
        image: '/images/IMG_0341.webp',
        reverse: true,
    },
    {
        number: '03',
        tag: 'Dairy Nutrition & Feeding',
        title: 'Zero-Grazing Troughs & Fresh Fodder Nutrition',
        subtitle: 'High-grade dairy cows feeding on nutrient-dense chopped green pasture.',
        description: 'Our mature dairy cows are individually tagged and managed in clean zero-grazing feedlot pens. Every day, they are served fresh succulent pasture grass, silage, and balanced mineral supplements, producing rich, creamy whole milk that is processed fresh right on the farm.',
        specs: ['Fresh Napier & Pasture Fodder', 'Individual Ear-Tag Monitoring', 'Continuous Fresh Water', 'High-Cream Morning Milk'],
        image: '/images/IMG_0353.webp',
        reverse: false,
    },
    {
        number: '04',
        tag: 'Farm Operations & Fodder Harvest',
        title: 'Daily Pasture Harvesting & Herd Nutrition',
        subtitle: 'Dedicated farm team transporting freshly cut green fodder from the fields.',
        description: 'Every morning across the lush green fields of Kitonga Farm, our dedicated team harvests bundles of protein-rich green pasture and Napier grass. Carried directly to the livestock sheds, this freshly cut fodder guarantees optimal nutrition, vitality, and milk production for the entire herd.',
        specs: ['Daily Dawn Fodder Cutting', '100% Organic Field Grasses', 'Dedicated Farm Hands', 'Sustainable Forage Cycles'],
        image: '/images/IMG_0362.webp',
        reverse: true,
    },
];

// Complete Estate Visual Archive (Accurate photo titles for Milk, Eggs, Dairy, & Pastures)
const farmGallery = [
    { src: '/images/IMG_0341.webp', title: 'Modern Tiered Layer Poultry House & Fresh Farm Eggs', category: 'Poultry & Eggs' },
    { src: '/images/IMG_0321.webp', title: 'Young Dairy Calves at the Barn Feeding Rail', category: 'Calf Nursery' },
    { src: '/images/IMG_0328.webp', title: 'Automated Cattle Shower & Spray Race Biosecurity Tunnel', category: 'Herd Biosecurity' },
    { src: '/images/IMG_0353.webp', title: 'Pedigree Dairy Cows Feeding on Fresh Fodder', category: 'Dairy Herd' },
    { src: '/images/IMG_0362.webp', title: 'Farm Team Harvesting & Transporting Green Pasture Fodder', category: 'Pasture Harvest' },
    { src: '/images/IMG_0404.webp', title: 'Fresh Whole Pasture Milk Direct from the Farm (3L & 5L)', category: 'Fresh Milk' },
    { src: '/images/IMG_0394.webp', title: 'Kitonga Cultured Sour Milk / Mtindi Safi (3L & 5L)', category: 'Mtindi Safi' },
    { src: '/images/IMG_0389.webp', title: 'Kitonga Artisanal Drinking Yogurt (0.5L & 1L)', category: 'Artisanal Yogurt' },
];

const activeLightbox = ref(null);
const openLightbox = (idx) => { activeLightbox.value = idx; };
const closeLightbox = () => { activeLightbox.value = null; };
const nextLightbox = () => {
    if (activeLightbox.value !== null) {
        activeLightbox.value = (activeLightbox.value + 1) % farmGallery.length;
    }
};
const prevLightbox = () => {
    if (activeLightbox.value !== null) {
        activeLightbox.value = (activeLightbox.value - 1 + farmGallery.length) % farmGallery.length;
    }
};



const orderProduct = (product) => {
    const text = encodeURIComponent(`Habari Kitonga Farm! Ningependa kuagiza bidhaa hii kutoka shambani: ${product.name} (${formatCurrency(product.price)}/${product.unit || 'kitu'}). Tafadhali nisaidie upatikanaji na utaratibu wa kuletewa.`);
    window.open(`https://wa.me/255758774695?text=${text}`, '_blank');
};
</script>

<template>
    <Head title="Our Organic Farm & Countryside Heritage â€” Kitonga Farm Villas" />

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
                <Link :href="route('farm')" prefetch class="text-[#E6C387] font-bold border-b-2 border-[#E6C387] pb-0.5 hover:text-[#C98A3E] transition duration-200">Our Farm</Link>
                <Link :href="route('products')" prefetch class="hover:text-[#C98A3E] transition duration-200">Produce</Link>
                <Link :href="route('gallery')" prefetch class="hover:text-[#C98A3E] transition duration-200">Gallery</Link>
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
                <Link :href="route('home')" prefetch @click="isMobileMenuOpen = false" class="hover:text-[#C98A3E] transition py-1">Home</Link>
                <Link :href="route('villas')" prefetch @click="isMobileMenuOpen = false" class="hover:text-[#C98A3E] transition py-1">Villas</Link>
                <Link :href="route('experiences')" prefetch @click="isMobileMenuOpen = false" class="hover:text-[#C98A3E] transition py-1">Experiences</Link>
                <Link :href="route('farm')" prefetch @click="isMobileMenuOpen = false" class="text-[#E6C387] font-bold py-1">Our Farm</Link>
                <Link :href="route('products')" prefetch @click="isMobileMenuOpen = false" class="hover:text-[#C98A3E] transition py-1">Produce</Link>
                <Link :href="route('gallery')" prefetch @click="isMobileMenuOpen = false" class="hover:text-[#C98A3E] transition py-1">Gallery</Link>
                <Link :href="route('contact')" prefetch @click="isMobileMenuOpen = false" class="hover:text-[#C98A3E] transition py-1">Contact</Link>
                <Link :href="route('login')" prefetch @click="isMobileMenuOpen = false" class="hover:text-[#C98A3E] transition py-1">Sign In</Link>
            </div>
            <div class="pt-2 border-t border-white/10">
                <Link 
                    :href="route('booking.form')" 
                    prefetch
                    @click="isMobileMenuOpen = false"
                    class="block w-full py-3 bg-[#C98A3E] text-white text-center font-bold uppercase tracking-wider rounded-lg shadow-sm"
                >
                    Book Stay
                </Link>
            </div>
        </div>

        <!-- 2. CINEMATIC CLEAR HERO SECTION -->
        <div class="relative w-full h-[70vh] sm:h-[76vh] md:h-[82vh] min-h-[480px] max-h-[820px] bg-[#14231C] overflow-hidden flex flex-col justify-between select-none">
            
            <!-- 4K Farm Visual (Crystal Clear) -->
            <img 
                src="/images/farm_hero_img_0289.webp" 
                alt="Kitonga Organic Farm & Sanctuary" 
                fetchpriority="high"
                decoding="async"
                class="absolute inset-0 w-full h-full object-cover object-center pointer-events-none transform scale-100 transition duration-1000"
            />

            <!-- BOTTOM ZERO-GAP CONNECTOR -->
            <div class="relative z-20 w-full h-16 bg-gradient-to-t from-[#FAF8F5] to-transparent pointer-events-none mt-auto"></div>
        </div>

        <!-- 3. EDITORIAL STATEMENT & VALUES -->
        <section class="content-auto max-w-5xl mx-auto px-6 pt-6 pb-20 text-center space-y-8">
            <div class="inline-flex items-center gap-2">
                <span class="w-8 h-px bg-[#C98A3E]"></span>
                <span class="text-xs uppercase tracking-[4px] font-bold text-[#C98A3E] font-sans">01 â€” The Estate Sanctuary</span>
                <span class="w-8 h-px bg-[#C98A3E]"></span>
            </div>

            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-serif font-light text-[#14231C] leading-[1.15] tracking-tight">
                {{ cms.farm_headline || 'A Symphony of Living Earth, Pedigree Livestock & Country Peace.' }}
            </h1>

            <p class="text-base sm:text-lg text-gray-700 font-sans font-light leading-relaxed max-w-3xl mx-auto">
                {{ cms.farm_story || 'Spread across the fertile countryside of Komkonga Village in Handeni, Tanga, Kitonga Farm is an integrated modern agricultural sanctuary. Home to high-grade pedigree dairy cattle, advanced layer poultry houses, and lush forage pastures, our enterprise produces fresh whole milk, authentic mtindi, artisanal yogurt, raw honey, and farm-fresh eggs with uncompromising standards of care and biosecurity.' }}
            </p>

            <!-- 4 Core Pillars Strip (With Luxury Interactive Micro-Animations) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 pt-8 text-left">
                
                <!-- Pedigree Dairy Herd -->
                <div class="group relative p-7 bg-white rounded-2xl border border-[#E8E2D5] hover:border-[#C98A3E]/80 shadow-xs hover:shadow-2xl hover:-translate-y-2 transition-all duration-500 ease-out overflow-hidden cursor-default space-y-4">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#C98A3E] via-[#E6C387] to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="absolute -bottom-8 -right-8 w-24 h-24 bg-[#C98A3E]/10 rounded-full blur-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                    
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-800 group-hover:bg-[#14231C] group-hover:text-[#E6C387] group-hover:scale-110 flex items-center justify-center font-bold text-sm shadow-xs transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <h3 class="font-serif text-lg font-bold text-[#14231C] group-hover:text-[#C98A3E] transition-colors duration-300">Pedigree Dairy Herd</h3>
                        <p class="text-xs text-gray-500 group-hover:text-[#2C3E2B] font-sans leading-relaxed mt-1.5 transition-colors duration-300">High-yielding Friesian dairy cows with dedicated zero-grazing feeding troughs.</p>
                    </div>
                </div>

                <!-- Layer Poultry House -->
                <div class="group relative p-7 bg-white rounded-2xl border border-[#E8E2D5] hover:border-[#C98A3E]/80 shadow-xs hover:shadow-2xl hover:-translate-y-2 transition-all duration-500 ease-out overflow-hidden cursor-default space-y-4">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#C98A3E] via-[#E6C387] to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="absolute -bottom-8 -right-8 w-24 h-24 bg-[#C98A3E]/10 rounded-full blur-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                    
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-[#C98A3E] group-hover:bg-[#14231C] group-hover:text-[#E6C387] group-hover:scale-110 flex items-center justify-center font-bold text-sm shadow-xs transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <div>
                        <h3 class="font-serif text-lg font-bold text-[#14231C] group-hover:text-[#C98A3E] transition-colors duration-300">Layer Poultry House</h3>
                        <p class="text-xs text-gray-500 group-hover:text-[#2C3E2B] font-sans leading-relaxed mt-1.5 transition-colors duration-300">Modern tiered layer cage system producing fresh farm eggs every morning.</p>
                    </div>
                </div>

                <!-- Spray Race Biosecurity -->
                <div class="group relative p-7 bg-white rounded-2xl border border-[#E8E2D5] hover:border-[#C98A3E]/80 shadow-xs hover:shadow-2xl hover:-translate-y-2 transition-all duration-500 ease-out overflow-hidden cursor-default space-y-4">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#C98A3E] via-[#E6C387] to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="absolute -bottom-8 -right-8 w-24 h-24 bg-[#C98A3E]/10 rounded-full blur-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                    
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-800 group-hover:bg-[#14231C] group-hover:text-[#E6C387] group-hover:scale-110 flex items-center justify-center font-bold text-sm shadow-xs transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-serif text-lg font-bold text-[#14231C] group-hover:text-[#C98A3E] transition-colors duration-300">Spray Race Biosecurity</h3>
                        <p class="text-xs text-gray-500 group-hover:text-[#2C3E2B] font-sans leading-relaxed mt-1.5 transition-colors duration-300">Automated livestock shower tunnels ensuring herd health and tick prevention.</p>
                    </div>
                </div>

                <!-- Fresh Pasture Forage -->
                <div class="group relative p-7 bg-white rounded-2xl border border-[#E8E2D5] hover:border-[#C98A3E]/80 shadow-xs hover:shadow-2xl hover:-translate-y-2 transition-all duration-500 ease-out overflow-hidden cursor-default space-y-4">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#C98A3E] via-[#E6C387] to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="absolute -bottom-8 -right-8 w-24 h-24 bg-[#C98A3E]/10 rounded-full blur-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                    
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-[#C98A3E] group-hover:bg-[#14231C] group-hover:text-[#E6C387] group-hover:scale-110 flex items-center justify-center font-bold text-sm shadow-xs transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-serif text-lg font-bold text-[#14231C] group-hover:text-[#C98A3E] transition-colors duration-300">Fresh Pasture Forage</h3>
                        <p class="text-xs text-gray-500 group-hover:text-[#2C3E2B] font-sans leading-relaxed mt-1.5 transition-colors duration-300">Daily harvested succulent Napier grass and mineral-rich livestock feed.</p>
                    </div>
                </div>

            </div>
        </section>

        <!-- 4. SANCTUARY ZONES (EDITORIAL ALTERNATING SHOWCASE) -->
        <section class="content-auto max-w-7xl mx-auto px-6 py-12 space-y-24">
            <div class="text-center space-y-3">
                <div class="inline-flex items-center gap-2">
                    <span class="w-8 h-px bg-[#C98A3E]"></span>
                    <span class="text-xs uppercase tracking-[4px] font-bold text-[#C98A3E] font-sans">02 â€” Estate Zones</span>
                    <span class="w-8 h-px bg-[#C98A3E]"></span>
                </div>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-serif font-light text-[#14231C]">
                    Four Distinct Agricultural Realms
                </h2>
                <p class="text-sm text-gray-600 font-sans max-w-xl mx-auto">
                    Explore the interconnected micro-environments that provide fresh produce daily to our kitchens and villas.
                </p>
            </div>

            <!-- Alternating Realm Cards -->
            <div 
                v-for="zone in farmZones" 
                :key="zone.number"
                class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-16 items-center"
            >
                <!-- Image Side -->
                <div 
                    class="lg:col-span-7 relative overflow-hidden rounded-2xl shadow-xl group"
                    :class="zone.reverse ? 'lg:order-2' : 'lg:order-1'"
                >
                    <div class="aspect-4/3 w-full overflow-hidden bg-[#14231C]">
                        <img 
                            :src="zone.image" 
                            :alt="zone.title" 
                            loading="lazy"
                            decoding="async"
                            class="w-full h-full object-cover object-center group-hover:scale-105 transition duration-700 ease-out"
                        />
                    </div>
                    <div class="absolute top-4 left-4 px-3 py-1.5 bg-[#14231C]/85 backdrop-blur-md text-[#E6C387] text-[11px] uppercase tracking-widest font-bold rounded-lg border border-white/10">
                        {{ zone.tag }}
                    </div>
                </div>

                <!-- Narrative Side -->
                <div 
                    class="lg:col-span-5 space-y-6"
                    :class="zone.reverse ? 'lg:order-1' : 'lg:order-2'"
                >
                    <div class="flex items-center gap-3">
                        <span class="font-serif text-3xl font-light text-[#C98A3E]">{{ zone.number }}</span>
                        <span class="w-12 h-px bg-[#C98A3E]/40"></span>
                        <span class="text-xs uppercase tracking-[3px] font-bold text-gray-400 font-sans">Zone Discovery</span>
                    </div>

                    <h3 class="text-2xl sm:text-3xl lg:text-4xl font-serif font-light text-[#14231C] leading-snug">
                        {{ zone.title }}
                    </h3>

                    <p class="text-xs sm:text-sm font-semibold uppercase tracking-wider text-[#C98A3E] font-sans">
                        {{ zone.subtitle }}
                    </p>

                    <p class="text-sm text-gray-600 font-sans leading-relaxed">
                        {{ zone.description }}
                    </p>

                    <!-- Feature Tags -->
                    <div class="grid grid-cols-2 gap-2.5 pt-2">
                        <div 
                            v-for="(spec, sIdx) in zone.specs" 
                            :key="sIdx"
                            class="flex items-center gap-2 text-xs font-medium text-gray-700 font-sans"
                        >
                            <span class="w-1.5 h-1.5 rounded-full bg-[#C98A3E]"></span>
                            <span>{{ spec }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 5. ESTATE VISUAL ARCHIVE (5 AUTHENTIC PHOTOS) -->
        <section class="max-w-7xl mx-auto px-6 py-16 space-y-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-[#E8E2D5] pb-6">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2">
                        <span class="w-6 h-px bg-[#C98A3E]"></span>
                        <span class="text-xs uppercase tracking-[3px] font-bold text-[#C98A3E] font-sans">03 â€” Visual Archive</span>
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-serif font-light text-[#14231C]">
                        Moments Captured on the Estate
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-600 font-sans">
                        Glimpses into our coffee hills, greenhouses, dairy pastures, and native canopy.
                    </p>
                </div>
            </div>

            <!-- Asymmetric Editorial Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-6">
                <!-- Large Feature Image -->
                <div 
                    @click="openLightbox(0)"
                    class="sm:col-span-2 lg:col-span-7 aspect-16/10 rounded-2xl overflow-hidden shadow-lg relative group cursor-pointer bg-[#14231C]"
                >
                    <img loading="lazy" decoding="async" 
                        :src="farmGallery[0].src" 
                        :alt="farmGallery[0].title" 
                        class="w-full h-full object-cover group-hover:scale-105 transition duration-700"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition duration-300 flex items-end p-6">
                        <div class="text-white space-y-1">
                            <span class="text-[10px] uppercase font-bold tracking-widest text-[#E6C387]">{{ farmGallery[0].category }}</span>
                            <p class="font-serif text-lg">{{ farmGallery[0].title }}</p>
                        </div>
                    </div>
                </div>

                <!-- Secondary Feature Image -->
                <div 
                    @click="openLightbox(1)"
                    class="sm:col-span-2 lg:col-span-5 aspect-16/10 rounded-2xl overflow-hidden shadow-lg relative group cursor-pointer bg-[#14231C]"
                >
                    <img loading="lazy" decoding="async" 
                        :src="farmGallery[1].src" 
                        :alt="farmGallery[1].title" 
                        class="w-full h-full object-cover group-hover:scale-105 transition duration-700"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition duration-300 flex items-end p-6">
                        <div class="text-white space-y-1">
                            <span class="text-[10px] uppercase font-bold tracking-widest text-[#E6C387]">{{ farmGallery[1].category }}</span>
                            <p class="font-serif text-lg">{{ farmGallery[1].title }}</p>
                        </div>
                    </div>
                </div>

                <!-- 3 Balanced Columns -->
                <div 
                    v-for="(item, idx) in farmGallery.slice(2)" 
                    :key="idx + 2"
                    @click="openLightbox(idx + 2)"
                    class="lg:col-span-4 aspect-4/3 rounded-2xl overflow-hidden shadow-md relative group cursor-pointer bg-[#14231C]"
                >
                    <img loading="lazy" decoding="async" 
                        :src="item.src" 
                        :alt="item.title" 
                        class="w-full h-full object-cover group-hover:scale-105 transition duration-700"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition duration-300 flex items-end p-5">
                        <div class="text-white space-y-1">
                            <span class="text-[10px] uppercase font-bold tracking-widest text-[#E6C387]">{{ item.category }}</span>
                            <p class="font-serif text-sm">{{ item.title }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>



        <!-- LIGHTBOX MODAL -->
        <div 
            v-if="activeLightbox !== null" 
            class="fixed inset-0 z-50 bg-black/95 flex items-center justify-center p-4 select-none backdrop-blur-md"
            @click="closeLightbox"
        >
            <button 
                @click.stop="closeLightbox" 
                class="absolute top-6 right-6 text-white/80 hover:text-white p-2 z-50 text-2xl"
                aria-label="Close image preview"
            >
                âœ•
            </button>

            <!-- Navigation Buttons -->
            <button 
                @click.stop="prevLightbox" 
                class="absolute left-4 sm:left-8 top-1/2 -translate-y-1/2 text-white/80 hover:text-white p-3 rounded-full bg-white/10 hover:bg-white/20 z-50 text-xl font-bold"
                aria-label="Previous image"
            >
                â€¹
            </button>
            <button 
                @click.stop="nextLightbox" 
                class="absolute right-4 sm:right-8 top-1/2 -translate-y-1/2 text-white/80 hover:text-white p-3 rounded-full bg-white/10 hover:bg-white/20 z-50 text-xl font-bold"
                aria-label="Next image"
            >
                â€º
            </button>

            <div class="max-w-5xl max-h-[85vh] flex flex-col items-center" @click.stop>
                <img loading="lazy" decoding="async" 
                    :src="farmGallery[activeLightbox].src" 
                    :alt="farmGallery[activeLightbox].title" 
                    class="max-w-full max-h-[75vh] object-contain rounded-lg shadow-2xl"
                />
                <div class="mt-4 text-center text-white">
                    <span class="text-xs uppercase font-bold tracking-widest text-[#E6C387]">
                        {{ farmGallery[activeLightbox].category }}
                    </span>
                    <p class="font-serif text-lg text-gray-200 mt-1">
                        {{ farmGallery[activeLightbox].title }}
                    </p>
                </div>
            </div>
        </div>

        <!-- 8. LUXURY FOOTER -->
        <footer class="bg-[#0E1712] text-gray-400 font-sans pt-16 pb-12 border-t border-white/10">
            <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-4 gap-10 pb-12 border-b border-white/10">
                <div class="space-y-4 md:col-span-2">
                    <Link :href="route('home')" class="flex flex-col items-start group cursor-pointer">
                        <span class="font-serif text-2xl font-light text-[#F5F1E8] tracking-[4px] uppercase leading-none">KITONGA</span>
                        <span class="font-sans text-[9px] font-medium text-[#C98A3E] tracking-[6px] uppercase leading-none mt-1 pl-[2px]">FARMS VILLAS</span>
                    </Link>
                    <p class="text-xs text-gray-400 max-w-md leading-relaxed">
                        An unhurried organic farm and private residence sanctuary situated in the scenic highlands of Komkonga Village, Tanga Region, Tanzania.
                    </p>
                    <div class="pt-2 text-xs text-gray-400 space-y-1">
                        <p><strong class="text-gray-200">WhatsApp Concierge:</strong> +255 758 774 695</p>
                        <p><strong class="text-gray-200">Location:</strong> Komkonga Village, Tanga, Tanzania</p>
                    </div>
                </div>

                <div class="space-y-3 text-xs">
                    <h3 class="text-white font-bold uppercase tracking-widest font-serif text-sm">Resort Navigation</h3>
                    <ul class="space-y-2">
                        <li><Link :href="route('home')" prefetch class="hover:text-[#C98A3E] transition">Home</Link></li>
                        <li><Link :href="route('villas')" prefetch class="hover:text-[#C98A3E] transition">Private Villas</Link></li>
                        <li><Link :href="route('experiences')" prefetch class="hover:text-[#C98A3E] transition">Farm Experiences</Link></li>
                        <li><Link :href="route('farm')" prefetch class="hover:text-[#C98A3E] transition">Our Organic Farm</Link></li>
                        <li><Link :href="route('products')" prefetch class="hover:text-[#C98A3E] transition">Farm Produce</Link></li>
                    </ul>
                </div>

                <div class="space-y-3 text-xs">
                    <h3 class="text-white font-bold uppercase tracking-widest font-serif text-sm">Guest Services</h3>
                    <ul class="space-y-2">
                        <li><Link :href="route('booking.form')" prefetch class="hover:text-[#C98A3E] transition">Reserve a Stay</Link></li>
                        <li><Link :href="route('contact')" prefetch class="hover:text-[#C98A3E] transition">Contact & Inquiries</Link></li>
                        <li><Link :href="route('about')" prefetch class="hover:text-[#C98A3E] transition">About The Estate</Link></li>
                        <li><Link :href="route('policies', 'terms')" prefetch class="hover:text-[#C98A3E] transition">Terms & Policies</Link></li>
                    </ul>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-6 pt-8 flex flex-col sm:flex-row justify-between items-center text-xs text-gray-400 gap-4">
                <p>&copy; 2026 Kitonga Farm Villas. All rights reserved.</p>
                <div class="flex items-center gap-2 text-[11px]">
                    <span class="text-gray-400">Created by</span>
                    <a 
                        href="https://wa.me/255675315279" 
                        target="_blank" 
                        rel="noopener noreferrer" 
                        class="inline-flex items-center gap-1.5 px-3 py-1 bg-[#14231C] hover:bg-[#C98A3E] text-[#E6C387] hover:text-white rounded-full border border-[#C98A3E]/30 transition duration-300 font-medium shadow-xs"
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



