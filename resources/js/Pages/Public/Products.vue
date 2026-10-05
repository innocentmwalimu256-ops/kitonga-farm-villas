<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    products: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    settings: {
        type: Object,
        default: () => ({ contact_phone: '+255758774695' }),
    },
    auth: { type: Object, default: () => ({ user: null }) },
});

// ─── Default High-Quality Authentic Products ─────────────────────────────────
const defaultProducts = [
    {
        id: 1,
        sku: 'KFV-MAYAI-30',
        name: 'Farm Fresh Free-Range Eggs',
        category: 'Eggs',
        selling_price: 8000,
        unit: 'Tray (30 Eggs)',
        badge: 'Best Seller',
        origin: 'Komkonga Poultry Pastures',
        description: 'Organic free-range eggs with rich golden yolks, gathered at dawn every morning from hens roaming freely on green highland clover.',
        image: '/images/farm_egg_trays.webp',
    },
    {
        id: 2,
        sku: 'KFV-MTINDI-5L',
        name: 'Kitonga Cultured Sour Milk / Mtindi (5L)',
        category: 'Dairy',
        selling_price: 17000,
        unit: '5 Liters Container',
        badge: 'Fresh Daily',
        origin: 'Highland Dairy Barns',
        description: 'Traditional thick and velvety cultured sour milk made from 100% pure whole pasture milk, naturally fermented with wholesome probiotics.',
        image: '/images/kitonga_mtindi_5l.webp',
    },
    {
        id: 3,
        sku: 'KFV-MTINDI-3L',
        name: 'Kitonga Cultured Sour Milk / Mtindi (3L)',
        category: 'Dairy',
        selling_price: 13000,
        unit: '3 Liters Container',
        badge: 'Artisanal',
        origin: 'Highland Dairy Barns',
        description: 'Authentic fermented pasture sour milk rich in natural nutrients and probiotic cultures, sealed fresh in an artisanal 3-liter container.',
        image: '/images/kitonga_mtindi_3l.webp',
    },
    {
        id: 4,
        sku: 'KFV-FRESH-5L',
        name: 'Fresh Pasture Whole Milk (5 Liters)',
        category: 'Dairy',
        selling_price: 13000,
        unit: '5 Liters Can',
        badge: 'Dawn Milking',
        origin: 'Pasture Dairy Herd',
        description: 'Pure, unhomogenized whole milk with natural golden cream layer, drawn fresh from pedigree dairy cows grazing on pesticide-free grasses.',
        image: '/images/IMG_0404.webp',
    },
    {
        id: 5,
        sku: 'KFV-FRESH-3L',
        name: 'Fresh Pasture Whole Milk (3 Liters)',
        category: 'Dairy',
        selling_price: 9000,
        unit: '3 Liters Container',
        badge: 'Raw Purity',
        origin: 'Pasture Dairy Herd',
        description: 'Wholesome fresh milk delivered straight from our morning milking, retaining all natural bioactive enzymes, vitamins, and natural sweetness.',
        image: '/images/fresh_milk_3l.webp',
    },
    {
        id: 6,
        sku: 'KFV-YOGURT-1L',
        name: 'Probiotic Artisanal Drinking Yogurt (1L)',
        category: 'Dairy',
        selling_price: 6000,
        unit: '1 Liter Bottle',
        badge: 'Best Seller',
        origin: 'Artisanal Dairy Kitchen',
        description: 'Silk-smooth probiotic drinking yogurt handcrafted with pure milk and live active cultures for gentle digestion and wholesome vitality.',
        image: '/images/yogurt_1l.webp',
    },
    {
        id: 7,
        sku: 'KFV-YOGURT-05L',
        name: 'Probiotic Artisanal Yogurt (500ml)',
        category: 'Dairy',
        selling_price: 3000,
        unit: '500ml Bottle',
        badge: 'Daily Favorite',
        origin: 'Artisanal Dairy Kitchen',
        description: 'Convenient single-serve artisanal drinking yogurt packed with probiotic goodness, crafted fresh daily without artificial additives.',
        image: '/images/IMG_0389.webp',
    },
    {
        id: 9,
        sku: 'KFV-MANGO-1KG',
        name: 'Sweet Highland Orchard Mangoes',
        category: 'Fruits',
        selling_price: 3000,
        unit: 'per Kilogram',
        badge: 'Sun-Ripened',
        origin: 'Estate Fruit Orchard',
        description: 'Fragrant sun-ripened organic mangoes handpicked directly from our mature estate trees at peak sweetness and succulence.',
        image: '/images/mango_wallpaper.webp',
    },
    {
        id: 10,
        sku: 'KFV-PAPAW-1PC',
        name: 'Tree-Ripened Sweet Papaws',
        category: 'Fruits',
        selling_price: 4000,
        unit: 'per Piece',
        badge: 'Harvested Today',
        origin: 'Estate Fruit Orchard',
        description: 'Deep orange, honey-sweet papayas grown in rich composted soil and harvested fully tree-ripened for maximum flavor and nutrition.',
        image: '/images/pawpaw_fresh.webp',
    },
    {
        id: 11,
        sku: 'KFV-PINEAPPLE-1PC',
        name: 'Sun-Drenched Estate Pineapples',
        category: 'Fruits',
        selling_price: 4500,
        unit: 'per Piece',
        badge: 'Organic',
        origin: 'Komkonga Valley Fields',
        description: 'Luscious, low-acidity tropical pineapples naturally hydrated by mountain rainfall and bright highland sun.',
        image: '/images/pineapple_fresh.webp',
    },
    {
        id: 12,
        sku: 'KFV-VEG-BUNDLE',
        name: 'Spring-Fed Garden Greens Bundle',
        category: 'Vegetables',
        selling_price: 6000,
        unit: 'per Bundle',
        badge: 'Picked at Dawn',
        origin: 'Permaculture Gardens',
        description: 'Crisp pesticide-free garden spinach, seasonal greens, tender lettuce and fresh culinary herbs picked fresh upon morning request.',
        image: '/images/fresh_vegetables_garden.webp',
    },
];

// ─── Category & Image Resolvers ──────────────────────────────────────────────
const getCategoryName = (prod) => {
    const rawCat = (prod?.category && typeof prod.category === 'object') ? prod.category.name : (prod?.category || '');
    const name = (prod?.name || '').toLowerCase();
    const cat = String(rawCat).toLowerCase();
    if (name.includes('egg') || name.includes('mayai') || cat.includes('egg')) return 'Eggs';
    if (name.includes('milk') || name.includes('mtindi') || name.includes('yogurt') || name.includes('yoghurt') || name.includes('maziwa') || cat.includes('dairy')) return 'Dairy';
    if (name.includes('mango') || name.includes('papaw') || name.includes('pineapple') || name.includes('fruit') || cat.includes('fruit')) return 'Fruits';
    if (name.includes('veg') || name.includes('green') || name.includes('mboga') || cat.includes('veg')) return 'Vegetables';
    return rawCat || 'Farm Produce';
};

const resolveProductImage = (prod) => {
    const raw = prod?.image || prod?.featured_image;
    const name = (prod?.name || '').toLowerCase();
    const sku = (prod?.sku || '').toUpperCase();

    if (sku.includes('MTINDI-5L') || (name.includes('mtindi') && name.includes('5'))) return '/images/kitonga_mtindi_5l.webp';
    if (sku.includes('MTINDI-3L') || (name.includes('mtindi') && name.includes('3'))) return '/images/kitonga_mtindi_3l.webp';
    if (sku.includes('FRESH-5L') || (name.includes('milk') && name.includes('5'))) return '/images/IMG_0404.webp';
    if (sku.includes('FRESH-3L') || (name.includes('milk') && name.includes('3'))) return '/images/fresh_milk_3l.webp';
    if (sku.includes('YOGURT-1L') || (name.includes('yogurt') && name.includes('1'))) return '/images/yogurt_1l.webp';
    if (sku.includes('YOGURT-05L') || (name.includes('yogurt') && name.includes('500'))) return '/images/IMG_0389.webp';

    if (raw && raw.startsWith('http')) return raw;
    if (raw && raw.startsWith('/')) return raw;

    if (name.includes('egg') || name.includes('mayai')) return '/images/farm_egg_trays.webp';
    if (name.includes('yoghurt') || name.includes('yogurt')) return '/images/yogurt_1l.webp';
    if (name.includes('mtindi') || name.includes('sour milk')) return '/images/kitonga_mtindi_5l.webp';
    if (name.includes('milk') || name.includes('maziwa')) return '/images/IMG_0404.webp';
    if (name.includes('mango') || name.includes('embe')) return '/images/mango_wallpaper.webp';
    if (name.includes('papaw') || name.includes('papaya')) return '/images/pawpaw_fresh.webp';
    if (name.includes('pine') || name.includes('nanasi')) return '/images/pineapple_fresh.webp';
    if (name.includes('veg') || name.includes('mboga') || name.includes('green')) return '/images/fresh_vegetables_garden.webp';
    return '/images/fresh_vegetables_garden.webp';
};

// ─── Products & Filter Reactive State ─────────────────────────────────────────
const allProducts = computed(() => {
    if (props.products && props.products.length > 0) return props.products;
    return defaultProducts;
});

const activeCategory = ref('All');
const categoriesList = ['All', 'Eggs', 'Dairy', 'Fruits', 'Vegetables'];

const filteredProducts = computed(() => {
    if (activeCategory.value === 'All') return allProducts.value;
    return allProducts.value.filter(p => {
        const cat = getCategoryName(p).toLowerCase();
        const name = (p.name || '').toLowerCase();
        if (activeCategory.value === 'Eggs') return cat.includes('egg') || name.includes('egg') || name.includes('mayai');
        if (activeCategory.value === 'Dairy') return cat.includes('dairy') || name.includes('milk') || name.includes('mtindi') || name.includes('yogurt') || name.includes('yoghurt') || name.includes('maziwa');
        if (activeCategory.value === 'Fruits') return cat.includes('fruit') || name.includes('mango') || name.includes('papaw') || name.includes('pineapple');
        if (activeCategory.value === 'Vegetables') return cat.includes('veg') || name.includes('veg') || name.includes('mboga') || name.includes('green');
        return true;
    });
});

const formatCurrency = (val) => {
    const num = Number(val) || 0;
    return new Intl.NumberFormat('en-TZ', { style: 'currency', currency: 'TZS', maximumFractionDigits: 0 }).format(num);
};

const getPrice = (prod) => {
    return Number(prod?.selling_price || prod?.price || 0);
};

// ─── Cenizaro-Style Fullscreen Gallery Lightbox ──────────────────────────────
const lightboxIndex = ref(null);
const isLightboxOpen = computed(() => lightboxIndex.value !== null);
const currentLightboxProduct = computed(() => {
    if (lightboxIndex.value === null) return null;
    return filteredProducts.value[lightboxIndex.value] || null;
});

const openLightbox = (index) => {
    lightboxIndex.value = index;
    if (typeof document !== 'undefined') {
        document.body.style.overflow = 'hidden';
    }
};

const closeLightbox = () => {
    lightboxIndex.value = null;
    if (typeof document !== 'undefined') {
        document.body.style.overflow = '';
    }
};

const prevLightbox = () => {
    if (lightboxIndex.value === null) return;
    const total = filteredProducts.value.length;
    lightboxIndex.value = (lightboxIndex.value - 1 + total) % total;
};

const nextLightbox = () => {
    if (lightboxIndex.value === null) return;
    const total = filteredProducts.value.length;
    lightboxIndex.value = (lightboxIndex.value + 1) % total;
};

const handleKeydown = (e) => {
    if (!isLightboxOpen.value) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') prevLightbox();
    if (e.key === 'ArrowRight') nextLightbox();
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
    if (typeof document !== 'undefined') {
        document.body.style.overflow = '';
    }
});

// ─── WhatsApp Inquiry URL ───────────────────────────────────────────────────
const getProductWhatsappUrl = (prod) => {
    const phone = (props.settings?.contact_phone || '+255758774695').replace(/[^0-9]/g, '');
    const name = prod?.name || 'Farm Produce';
    const price = formatCurrency(getPrice(prod));
    const unit = prod?.unit ? ` (${prod.unit})` : '';
    const text = `Habari Kitonga Farm Villas,\n\nNingependa kupata taarifa zaidi au kuagiza bidhaa hii ya shambani:\n• *${name}*\n• Bei: *${price}${unit}*\n\nNaomba msaada wa upatikanaji na utaratibu wa kuletewa. Asante!`;
    return `https://wa.me/${phone}?text=${encodeURIComponent(text)}`;
};

const generalWhatsappUrl = computed(() => {
    const phone = (props.settings?.contact_phone || '+255758774695').replace(/[^0-9]/g, '');
    const text = `Habari Kitonga Farm Villas, ningependa kuwasiliana nanyi kuhusu bidhaa zenu za shambani (Farm Produce & Dairy).`;
    return `https://wa.me/${phone}?text=${encodeURIComponent(text)}`;
});

const scrollToGallery = () => {
    const el = document.getElementById('produce-gallery-grid');
    if (el) el.scrollIntoView({ behavior: 'smooth' });
};
</script>

<template>
    <Head title="Organic Farm Produce Gallery | Kitonga Farm Villas" />

    <div class="min-h-screen bg-[#FAF8F5] text-[#2C3530] font-sans antialiased selection:bg-[#C98A3E] selection:text-white">

        <!-- ══════════════════════════════════════════════════════════════════════
             1. REFINED LUXURY TOP NAVIGATION (Cenizaro Style)
        ══════════════════════════════════════════════════════════════════════ -->
        <header class="sticky top-0 z-50 bg-[#14231C]/95 backdrop-blur-md border-b border-white/10 text-white transition-all">
            <div class="max-w-7xl mx-auto px-4 sm:px-8 py-4 flex items-center justify-between">
                
                <!-- Left: Logo & Crest -->
                <Link :href="route('home')" class="flex items-center gap-3.5 group">
                    <div class="w-10 h-10 rounded-full border border-[#C98A3E]/60 flex items-center justify-center p-1.5 bg-[#1B2E24] shadow-sm group-hover:border-[#E6C387] transition">
                        <img src="/images/logo_gold.webp" alt="Kitonga Logo" class="w-full h-full object-contain" />
                    </div>
                    <div>
                        <span class="block text-sm sm:text-base font-serif font-bold tracking-[0.25em] text-[#E6C387] uppercase leading-none">
                            KITONGA
                        </span>
                        <span class="block text-[9px] tracking-[0.3em] text-white/60 uppercase font-light mt-0.5">
                            FARM VILLAS SANCTUARY
                        </span>
                    </div>
                </Link>

                <!-- Center: Luxury Navigation Links -->
                <nav class="hidden lg:flex items-center gap-8 text-[11px] font-medium tracking-[0.2em] uppercase text-white/80">
                    <Link :href="route('home')" class="hover:text-[#E6C387] transition-colors">Home</Link>
                    <Link :href="route('villas')" class="hover:text-[#E6C387] transition-colors">Villas</Link>
                    <Link :href="route('experiences')" class="hover:text-[#E6C387] transition-colors">Experiences</Link>
                    <Link :href="route('farm')" class="hover:text-[#E6C387] transition-colors">Farm</Link>
                    <Link :href="route('products')" class="text-[#E6C387] font-bold border-b border-[#E6C387] pb-1">Produce</Link>
                    <Link :href="route('gallery')" class="hover:text-[#E6C387] transition-colors">Gallery</Link>
                    <Link :href="route('about')" class="hover:text-[#E6C387] transition-colors">About</Link>
                    <Link :href="route('contact')" class="hover:text-[#E6C387] transition-colors">Contact</Link>
                </nav>

                <!-- Right: Direct Concierge Action -->
                <div class="flex items-center gap-3">
                    <a
                        :href="generalWhatsappUrl"
                        target="_blank"
                        class="hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-full text-[11px] font-semibold tracking-widest uppercase border border-[#C98A3E]/70 text-[#E6C387] hover:bg-[#C98A3E] hover:text-white transition duration-300"
                    >
                        <span>Farm Concierge</span>
                    </a>
                    <Link
                        :href="route('booking.form')"
                        class="px-5 py-2 rounded-full text-[11px] font-bold tracking-widest uppercase bg-[#C98A3E] hover:bg-[#b57a34] text-white shadow-md transition duration-300"
                    >
                        Book Stay
                    </Link>
                </div>

            </div>
        </header>


        <!-- ══════════════════════════════════════════════════════════════════════
             2. MASTHEAD HERO SECTION (Cenizaro Editorial Inspiration)
        ══════════════════════════════════════════════════════════════════════ -->
        <section class="relative bg-[#14231C] text-white pt-20 pb-24 sm:pt-24 sm:pb-28 px-4 sm:px-8 overflow-hidden">
            <!-- Ambient Background Glow & Vignette -->
            <div class="absolute inset-0 bg-radial from-transparent via-[#14231C]/60 to-[#0C1712] pointer-events-none"></div>
            <div class="absolute top-0 right-0 w-96 h-96 bg-[#C98A3E]/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative max-w-4xl mx-auto text-center space-y-5">
                <!-- Breadcrumbs -->
                <div class="flex items-center justify-center gap-2 text-[10px] tracking-[0.25em] uppercase text-white/50 font-light">
                    <Link :href="route('home')" class="hover:text-[#E6C387] transition">Home</Link>
                    <span>/</span>
                    <Link :href="route('farm')" class="hover:text-[#E6C387] transition">Farm</Link>
                    <span>/</span>
                    <span class="text-[#E6C387]">Produce Gallery</span>
                </div>

                <!-- Subtitle Pill -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-[#1F352A] border border-[#C98A3E]/40 text-[#E6C387] text-[10px] tracking-[0.25em] uppercase font-semibold">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#E6C387] animate-pulse"></span>
                    <span>Agro-Ecological Sanctuary</span>
                </div>

                <!-- Main Editorial Title -->
                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-serif font-normal text-white tracking-wide leading-tight">
                    Organic Farm Produce
                </h1>

                <!-- Elegant Divider -->
                <div class="flex items-center justify-center gap-3 pt-2">
                    <span class="w-12 h-px bg-gradient-to-r from-transparent to-[#C98A3E]"></span>
                    <span class="text-xs text-[#C98A3E]">✦</span>
                    <span class="w-12 h-px bg-gradient-to-l from-transparent to-[#C98A3E]"></span>
                </div>

                <!-- Narrative Description -->
                <p class="text-xs sm:text-sm text-white/80 font-light max-w-2xl mx-auto leading-relaxed pt-1">
                    Nurtured by highland spring water and rich organic compost in Komkonga, Handeni.
                    Explore our daily dawn-milked pasture dairy, farm-fresh eggs, and sun-ripened orchard fruits cultivated with unhurried care.
                </p>

                <!-- Quick Scroll Prompt -->
                <div class="pt-4">
                    <button 
                        @click="scrollToGallery"
                        class="text-[10px] tracking-[0.25em] uppercase text-white/60 hover:text-[#E6C387] transition inline-flex items-center gap-2 cursor-pointer"
                    >
                        <span>View Harvest Showcase</span>
                        <svg class="w-3.5 h-3.5 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                    </button>
                </div>
            </div>
        </section>


        <!-- ══════════════════════════════════════════════════════════════════════
             3. CENIZARO-STYLE MINIMAL CATEGORY FILTER TABS
        ══════════════════════════════════════════════════════════════════════ -->
        <section class="sticky top-[69px] z-40 bg-[#FAF8F5]/95 backdrop-blur-md border-b border-[#E8E2D6] py-4 shadow-2xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-8">
                <div class="flex items-center justify-center gap-2 sm:gap-6 overflow-x-auto scrollbar-none py-1">
                    <button
                        v-for="cat in categoriesList"
                        :key="cat"
                        @click="activeCategory = cat"
                        class="relative px-4 sm:px-6 py-2 text-[11px] sm:text-xs tracking-[0.2em] uppercase font-semibold transition-all cursor-pointer whitespace-nowrap rounded-full"
                        :class="activeCategory === cat 
                            ? 'bg-[#14231C] text-[#E6C387] shadow-sm' 
                            : 'text-[#5F6B63] hover:text-[#14231C] hover:bg-white/80'"
                    >
                        <span>{{ cat === 'All' ? 'All Harvests' : cat }}</span>
                        <span 
                            v-if="activeCategory === cat" 
                            class="absolute -bottom-1.5 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-[#C98A3E]"
                        ></span>
                    </button>
                </div>
            </div>
        </section>


        <!-- ══════════════════════════════════════════════════════════════════════
             4. GALLERY SHOWCASE GRID (Clean, Editorial, High-Resolution Cards)
        ══════════════════════════════════════════════════════════════════════ -->
        <main id="produce-gallery-grid" class="max-w-7xl mx-auto px-4 sm:px-8 py-14 sm:py-20 space-y-16">

            <!-- Category Summary Heading -->
            <div class="flex flex-col sm:flex-row items-start sm:items-end justify-between border-b border-[#E8E2D6] pb-4 gap-2">
                <div>
                    <span class="text-[10px] tracking-[0.25em] uppercase text-[#C98A3E] font-bold block">
                        Kitonga Estate Harvest
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-serif text-[#14231C] font-normal tracking-wide">
                        {{ activeCategory === 'All' ? 'Complete Produce Catalog' : `${activeCategory} Collection` }}
                    </h2>
                </div>
                <div class="text-xs text-[#5F6B63] font-serif italic">
                    Showing {{ filteredProducts.length }} {{ filteredProducts.length === 1 ? 'Produce item' : 'Produce items' }}
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="filteredProducts.length === 0" class="py-20 text-center space-y-4 bg-white rounded-3xl border border-[#E8E2D6]">
                <p class="font-serif text-lg text-[#14231C]">Hakuna bidhaa kwenye kategoria hii kwa sasa.</p>
                <button @click="activeCategory = 'All'" class="text-xs text-[#C98A3E] font-bold uppercase tracking-widest hover:underline cursor-pointer">
                    Tazama Bidhaa Zote
                </button>
            </div>

            <!-- Luxury Masonry / Structured Grid -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 sm:gap-10">
                
                <article
                    v-for="(prod, index) in filteredProducts"
                    :key="prod.id || index"
                    class="group bg-white rounded-2xl overflow-hidden border border-[#EAE4D8] shadow-xs hover:shadow-xl hover:border-[#C98A3E]/50 transition-all duration-500 flex flex-col"
                >
                    <!-- Photography Container (Clickable for Lightbox) -->
                    <div 
                        @click="openLightbox(index)"
                        class="relative aspect-[4/3] bg-[#EAE4D8] overflow-hidden cursor-pointer"
                        title="Bonyeza kutazama picha na maelezo"
                    >
                        <img 
                            loading="lazy"
                            decoding="async"
                            :src="resolveProductImage(prod)" 
                            :alt="prod.name"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out"
                        />
                        
                        <!-- Ambient Dark Vignette on Hover -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                        <!-- Top Badges -->
                        <div class="absolute top-4 left-4 right-4 flex items-center justify-between pointer-events-none">
                            <span 
                                v-if="prod.badge"
                                class="px-3 py-1 rounded-full text-[10px] font-bold tracking-wider uppercase bg-[#14231C]/85 backdrop-blur-md text-[#E6C387] border border-[#C98A3E]/30 shadow-xs"
                            >
                                {{ prod.badge }}
                            </span>
                            <span v-else></span>

                            <!-- Zoom Indicator Button -->
                            <div class="w-8 h-8 rounded-full bg-white/90 backdrop-blur-md text-[#14231C] flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 shadow-md transform group-hover:scale-110">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/></svg>
                            </div>
                        </div>

                        <!-- Bottom Category Tag in image -->
                        <div class="absolute bottom-3 left-4 text-[10px] tracking-[0.2em] font-semibold uppercase text-white/90 drop-shadow-md opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            {{ getCategoryName(prod) }}
                        </div>
                    </div>

                    <!-- Editorial Content Body -->
                    <div class="p-6 sm:p-7 flex-1 flex flex-col justify-between space-y-5 bg-white">
                        
                        <div class="space-y-3">
                            <!-- Category & SKU -->
                            <div class="flex items-center justify-between text-[10px] tracking-[0.2em] uppercase text-[#C98A3E] font-bold">
                                <span>{{ getCategoryName(prod) }}</span>
                                <span v-if="prod.origin" class="text-[#5F6B63] font-normal tracking-normal font-mono text-[9px]">{{ prod.origin }}</span>
                            </div>

                            <!-- Product Name in Editorial Serif -->
                            <h3 
                                @click="openLightbox(index)"
                                class="text-xl sm:text-2xl font-serif text-[#14231C] font-semibold leading-snug group-hover:text-[#C98A3E] transition-colors cursor-pointer"
                            >
                                {{ prod.name }}
                            </h3>

                            <!-- Price Tag Display -->
                            <div class="pt-1 flex items-baseline gap-2">
                                <span class="text-2xl font-serif font-bold text-[#14231C] tracking-tight">
                                    {{ formatCurrency(getPrice(prod)) }}
                                </span>
                                <span v-if="prod.unit" class="text-xs text-[#5F6B63] font-medium">
                                    / {{ prod.unit }}
                                </span>
                            </div>

                            <!-- Description / Farm Story -->
                            <p class="text-xs text-[#5F6B63] leading-relaxed line-clamp-3 pt-1">
                                {{ prod.description || 'Organic harvest produced with pure natural care at Kitonga Farm Sanctuary.' }}
                            </p>
                        </div>

                        <!-- Action Bar: View Detail & WhatsApp Inquiry -->
                        <div class="pt-4 border-t border-[#F0EBE0] flex items-center justify-between gap-3 text-xs">
                            <button
                                @click="openLightbox(index)"
                                class="text-[11px] font-bold tracking-wider uppercase text-[#14231C] hover:text-[#C98A3E] inline-flex items-center gap-1.5 transition cursor-pointer"
                            >
                                <span>Maelezo Kamili</span>
                                <span>➔</span>
                            </button>

                            <a
                                :href="getProductWhatsappUrl(prod)"
                                target="_blank"
                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-[#EAF7EE] hover:bg-[#25D366] text-[#1E7E34] hover:text-white font-semibold text-[11px] transition duration-300 shadow-2xs"
                                title="Ulizia kupitia WhatsApp"
                            >
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                <span>Ulizia</span>
                            </a>
                        </div>

                    </div>
                </article>

            </div>

        </main>


        <!-- ══════════════════════════════════════════════════════════════════════
             5. FARM PROVENANCE & ORGANIC STANDARDS (Quality Strip)
        ══════════════════════════════════════════════════════════════════════ -->
        <section class="bg-[#14231C] text-white py-16 sm:py-20 px-4 sm:px-8 border-t border-[#C98A3E]/20 relative overflow-hidden">
            <div class="max-w-7xl mx-auto space-y-12 relative z-10">
                
                <div class="text-center max-w-2xl mx-auto space-y-3">
                    <span class="text-[10px] tracking-[0.25em] uppercase text-[#C98A3E] font-bold">Uncompromising Quality</span>
                    <h2 class="text-2xl sm:text-4xl font-serif font-normal text-white">Our Agricultural Principles</h2>
                    <p class="text-xs sm:text-sm text-white/70 font-light leading-relaxed">
                        Every drop of milk, egg, and fresh harvest is raised in strict harmony with nature on our 150-acre highland sanctuary.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-center">
                    
                    <div class="bg-[#1B2E24] p-6 rounded-2xl border border-white/10 space-y-3">
                        <div class="w-12 h-12 rounded-full bg-[#14231C] border border-[#C98A3E]/40 text-[#E6C387] mx-auto flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h4 class="font-serif text-base text-[#E6C387]">Pasture-Fed Cattle</h4>
                        <p class="text-xs text-white/60 leading-relaxed">Pedigree dairy cows graze on nutrient-rich highland grasses with zero chemical growth stimulants.</p>
                    </div>

                    <div class="bg-[#1B2E24] p-6 rounded-2xl border border-white/10 space-y-3">
                        <div class="w-12 h-12 rounded-full bg-[#14231C] border border-[#C98A3E]/40 text-[#E6C387] mx-auto flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <h4 class="font-serif text-base text-[#E6C387]">Dawn Harvest & Milking</h4>
                        <p class="text-xs text-white/60 leading-relaxed">Gathered fresh at 6:00 AM daily to guarantee maximum natural taste, texture, and living nutrients.</p>
                    </div>

                    <div class="bg-[#1B2E24] p-6 rounded-2xl border border-white/10 space-y-3">
                        <div class="w-12 h-12 rounded-full bg-[#14231C] border border-[#C98A3E]/40 text-[#E6C387] mx-auto flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                        </div>
                        <h4 class="font-serif text-base text-[#E6C387]">100% Free-Range Poultry</h4>
                        <p class="text-xs text-white/60 leading-relaxed">Hens enjoy open sunlight, natural scratching soil, and pure organic grain for golden yolks.</p>
                    </div>

                    <div class="bg-[#1B2E24] p-6 rounded-2xl border border-white/10 space-y-3">
                        <div class="w-12 h-12 rounded-full bg-[#14231C] border border-[#C98A3E]/40 text-[#E6C387] mx-auto flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/></svg>
                        </div>
                        <h4 class="font-serif text-base text-[#E6C387]">Highland Spring Water</h4>
                        <p class="text-xs text-white/60 leading-relaxed">Irrigated exclusively by virgin mountain spring aquifers preserving native Handeni soil ecosystems.</p>
                    </div>

                </div>

                <!-- Bespoke Inquiries Banner -->
                <div class="bg-[#1B2E24] p-8 sm:p-10 rounded-3xl border border-[#C98A3E]/30 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="space-y-2 text-center md:text-left">
                        <h3 class="text-xl sm:text-2xl font-serif text-[#E6C387]">Direct Farm Inquiries & Lodge Supply</h3>
                        <p class="text-xs sm:text-sm text-white/70 max-w-xl">
                            Looking to arrange regular weekly supply for premier lodges, restaurants, or private villa breakfast deliveries? Speak directly with our farm management.
                        </p>
                    </div>
                    <a
                        :href="generalWhatsappUrl"
                        target="_blank"
                        class="px-8 py-3.5 rounded-full bg-[#C98A3E] hover:bg-[#b57a34] text-white text-xs font-bold tracking-widest uppercase shadow-lg transition duration-300 whitespace-nowrap"
                    >
                        WhatsApp Farm Desk
                    </a>
                </div>

            </div>
        </section>


        <!-- ══════════════════════════════════════════════════════════════════════
             6. CENIZARO-STYLE FULLSCREEN LIGHTBOX & MODAL
        ══════════════════════════════════════════════════════════════════════ -->
        <Teleport to="body">
            <Transition name="fade">
                <div 
                    v-if="isLightboxOpen && currentLightboxProduct"
                    class="fixed inset-0 z-[100] bg-black/90 backdrop-blur-md flex items-center justify-center p-4 sm:p-8 select-none"
                    @click.self="closeLightbox"
                >
                    <!-- Close button -->
                    <button
                        @click="closeLightbox"
                        class="absolute top-6 right-6 z-20 w-11 h-11 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer text-lg"
                        title="Close (Esc)"
                    >
                        ✕
                    </button>

                    <!-- Prev Arrow -->
                    <button
                        @click="prevLightbox"
                        class="absolute left-4 sm:left-8 top-1/2 -translate-y-1/2 z-20 w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer text-xl"
                        title="Previous (Left Arrow)"
                    >
                        ‹
                    </button>

                    <!-- Next Arrow -->
                    <button
                        @click="nextLightbox"
                        class="absolute right-4 sm:right-8 top-1/2 -translate-y-1/2 z-20 w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer text-xl"
                        title="Next (Right Arrow)"
                    >
                        ›
                    </button>

                    <!-- Lightbox Modal Container -->
                    <div class="relative bg-[#14231C] text-white rounded-3xl overflow-hidden max-w-4xl w-full max-h-[90vh] flex flex-col md:flex-row border border-white/20 shadow-2xl">
                        
                        <!-- Left: Large Photography -->
                        <div class="md:w-1/2 aspect-square md:aspect-auto bg-black flex items-center justify-center overflow-hidden">
                            <img 
                                :src="resolveProductImage(currentLightboxProduct)" 
                                :alt="currentLightboxProduct.name"
                                class="w-full h-full object-cover"
                            />
                        </div>

                        <!-- Right: Editorial Details -->
                        <div class="p-6 sm:p-10 md:w-1/2 flex flex-col justify-between space-y-6 overflow-y-auto">
                            
                            <div class="space-y-4">
                                <div class="flex items-center justify-between text-[10px] tracking-[0.25em] uppercase text-[#C98A3E] font-bold">
                                    <span>{{ getCategoryName(currentLightboxProduct) }}</span>
                                    <span>{{ (lightboxIndex + 1) }} / {{ filteredProducts.length }}</span>
                                </div>

                                <h2 class="text-2xl sm:text-3xl font-serif text-white font-semibold leading-tight">
                                    {{ currentLightboxProduct.name }}
                                </h2>

                                <div class="flex items-baseline gap-2 pt-1 border-b border-white/10 pb-4">
                                    <span class="text-3xl font-serif font-bold text-[#E6C387]">
                                        {{ formatCurrency(getPrice(currentLightboxProduct)) }}
                                    </span>
                                    <span v-if="currentLightboxProduct.unit" class="text-xs text-white/60">
                                        / {{ currentLightboxProduct.unit }}
                                    </span>
                                </div>

                                <div class="space-y-2 text-xs text-white/80 leading-relaxed font-light">
                                    <p class="text-white/40 text-[10px] uppercase tracking-widest font-bold">Provenance & Harvest Story</p>
                                    <p>{{ currentLightboxProduct.description }}</p>
                                </div>

                                <div v-if="currentLightboxProduct.origin" class="bg-[#1B2E24] p-3.5 rounded-xl border border-white/10 text-xs text-white/80 flex items-center gap-2.5">
                                    <svg class="w-4 h-4 text-[#E6C387] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>Harvested at <strong>{{ currentLightboxProduct.origin }}</strong></span>
                                </div>
                            </div>

                            <!-- Direct WhatsApp Inquiry -->
                            <div class="pt-4 border-t border-white/10 flex items-center justify-between gap-3">
                                <a
                                    :href="getProductWhatsappUrl(currentLightboxProduct)"
                                    target="_blank"
                                    class="w-full py-3.5 rounded-full bg-[#25D366] hover:bg-[#20ba59] text-white font-bold text-xs tracking-wider uppercase flex items-center justify-center gap-2 shadow-md transition duration-300 cursor-pointer"
                                >
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                    <span>Ulizia Bidhaa Hii WhatsApp</span>
                                </a>
                            </div>

                        </div>

                    </div>
                </div>
            </Transition>
        </Teleport>


        <!-- ══════════════════════════════════════════════════════════════════════
             7. LUXURY FOOTER
        ══════════════════════════════════════════════════════════════════════ -->
        <footer class="bg-[#0F1D16] text-white py-14 px-4 sm:px-8 border-t border-white/10">
            <div class="max-w-7xl mx-auto space-y-10">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    
                    <!-- Col 1: Brand -->
                    <div class="space-y-4 md:col-span-2">
                        <span class="font-serif text-lg font-bold tracking-[0.25em] text-[#E6C387] uppercase block">
                            KITONGA FARM VILLAS
                        </span>
                        <p class="text-xs text-white/60 max-w-sm leading-relaxed font-light">
                            150-acre integrated organic agricultural sanctuary and luxury private villas located in the fertile highlands of Komkonga Village, Handeni, Tanga, Tanzania.
                        </p>
                    </div>

                    <!-- Col 2: Navigation -->
                    <div>
                        <h4 class="text-[10px] font-bold uppercase tracking-[0.25em] text-[#C98A3E] mb-3">Sanctuary</h4>
                        <ul class="space-y-2 text-xs text-white/70">
                            <li><Link :href="route('home')" class="hover:text-white transition">Home</Link></li>
                            <li><Link :href="route('villas')" class="hover:text-white transition">Private Villas</Link></li>
                            <li><Link :href="route('experiences')" class="hover:text-white transition">Farm Experiences</Link></li>
                            <li><Link :href="route('farm')" class="hover:text-white transition">Our Farm</Link></li>
                            <li><Link :href="route('gallery')" class="hover:text-white transition">Photo Gallery</Link></li>
                        </ul>
                    </div>

                    <!-- Col 3: Contact & Location -->
                    <div>
                        <h4 class="text-[10px] font-bold uppercase tracking-[0.25em] text-[#C98A3E] mb-3">Direct Contact</h4>
                        <p class="text-xs text-white/70 leading-relaxed">
                            Komkonga Village, Handeni, Tanga, Tanzania<br />
                            Phone: +255 758 774 695 / +255 675 315 279
                        </p>
                        <div class="pt-3">
                            <a
                                :href="generalWhatsappUrl"
                                target="_blank"
                                class="inline-flex items-center gap-1.5 text-xs text-[#E6C387] hover:underline"
                            >
                                <span>✦ Chat with us on WhatsApp</span>
                            </a>
                        </div>
                    </div>

                </div>

                <!-- Copyright -->
                <div class="pt-6 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between text-[11px] text-white/50 gap-4">
                    <p>© 2026 Kitonga Farm Villas &amp; Sanctuary. All rights reserved.</p>
                    <div class="flex items-center gap-2 text-[10px]">
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

            </div>
        </footer>

    </div>
</template>

<style scoped>
.scrollbar-none::-webkit-scrollbar { display: none; }
.scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>
