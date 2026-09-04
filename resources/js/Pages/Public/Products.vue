<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    products: {
        type: Array,
        default: () => [],
    },
    categories: {
        type: Array,
        default: () => [],
    },
    settings: {
        type: Object,
        default: () => ({
            contact_phone: '+255784123456',
        }),
    },
    auth: {
        type: Object,
        default: () => ({ user: null }),
    },
});

// Authentic Kitonga Farm Harvest Data (100% English Descriptions)
const defaultProducts = [
    {
        id: 1,
        sku: 'KFV-MTINDI-5L',
        name: 'Kitonga Cultured Sour Milk / Mtindi (5 Liters)',
        category: 'Dairy & Eggs',
        selling_price: 17000,
        unit: '5 Liters',
        description: 'Traditional thick and creamy cultured sour milk made from 100% pure Kitonga pasture milk. Rich in natural probiotics and authentic heritage taste, bottled in a 5L container.',
        image: '/images/download_44.webp',
    },
    {
        id: 2,
        sku: 'KFV-MTINDI-3L',
        name: 'Kitonga Cultured Sour Milk / Mtindi (3 Liters)',
        category: 'Dairy & Eggs',
        selling_price: 13000,
        unit: '3 Liters',
        description: 'Traditional thick and creamy cultured sour milk made from pure farm pasture milk, bottled fresh in a convenient 3L container.',
        image: '/images/download_46.webp',
    },
    {
        id: 3,
        sku: 'KFV-FRESH-5L',
        name: 'Fresh Whole Farm Milk (5 Liters)',
        category: 'Dairy & Eggs',
        selling_price: 13000,
        unit: '5 Liters',
        description: '100% pure fresh unpasteurized whole farm milk rich in natural golden cream, harvested daily from our pasture-fed dairy cattle in a 5L container.',
        image: '/images/IMG_0404.webp',
    },
    {
        id: 4,
        sku: 'KFV-FRESH-3L',
        name: 'Fresh Whole Farm Milk (3 Liters)',
        category: 'Dairy & Eggs',
        selling_price: 9000,
        unit: '3 Liters',
        description: 'Pure wholesome unhomogenized fresh farm milk rich in nutrients and natural cream, bottled daily in a 3L container.',
        image: '/images/download_45.webp',
    },
    {
        id: 5,
        sku: 'KFV-YOGURT-1L',
        name: 'Kitonga Farm Artisanal Yogurt (1 Liter)',
        category: 'Dairy & Eggs',
        selling_price: 6000,
        unit: '1 Liter',
        description: 'Smooth, velvety artisanal drinking yogurt cultured from fresh morning milk, available in luscious Vanilla and ripe Strawberry infusions in a 1L bottle.',
        image: '/images/yogurt_1l.webp',
    },
    {
        id: 6,
        sku: 'KFV-YOGURT-05L',
        name: 'Kitonga Farm Artisanal Yogurt (0.5 L / 500ml)',
        category: 'Dairy & Eggs',
        selling_price: 3000,
        unit: '0.5 L',
        description: 'Delicious probiotic artisanal drinking yogurt crafted from pure whole milk, bottled in a convenient 500ml on-the-go size.',
        image: '/images/IMG_0389.webp',
    },
    {
        id: 7,
        sku: 'KFV-MAYAI-KISASA',
        name: 'Farm Fresh Organic Eggs (Tray)',
        category: 'Dairy & Eggs',
        selling_price: 8000,
        unit: 'Tray (30 Eggs)',
        description: 'Farm-fresh organic eggs with vibrant golden yolks, gathered every morning from free-range pasture hens.',
        image: '/images/farm_egg_trays.webp',
    },
    {
        id: 8,
        sku: 'KFV-ASALI-1KG',
        name: 'Raw Wild Forest Honey (1kg Net Weight)',
        category: 'Honey',
        selling_price: 10000,
        unit: '1 kg',
        description: '100% pure, unfiltered and unheated golden raw honey harvested from traditional highland top-bar apiaries deep in the Komkonga forest (1kg net weight).',
        image: '/images/raw_forest_honey.webp',
    },
    {
        id: 9,
        sku: 'KFV-MANGO-1KG',
        name: 'Sweet Kitonga Highland Mangoes (1kg)',
        category: 'Fruits',
        selling_price: 3000,
        unit: '1 kg',
        description: 'Sun-ripened, fragrant organic mangoes picked directly from mature estate orchard trees (1kg).',
        image: '/images/mango_wallpaper.webp',
    },
    {
        id: 10,
        sku: 'KFV-PAPAW-1PC',
        name: 'Tree-Ripened Sweet Papaws (Papayas)',
        category: 'Fruits',
        selling_price: 4000,
        unit: 'Piece / kg',
        description: 'Luscious, vibrant orange sweet papaws cultivated in fertile highland soil and harvested at peak natural sweetness.',
        image: '/images/pawpaw_fresh.webp',
    },
    {
        id: 11,
        sku: 'KFV-PINEAPPLE-1PC',
        name: 'Sun-Drenched Estate Pineapples',
        category: 'Fruits',
        selling_price: 4500,
        unit: 'Piece',
        description: 'Naturally sweet and juicy tropical pineapples grown with pure highland mountain rainfall and sunshine.',
        image: '/images/pineapple_fresh.webp',
    },
    {
        id: 12,
        sku: 'KFV-VEG-BUNDLE',
        name: 'Spring-Fed Fresh Vegetables & Greens',
        category: 'Fresh Vegetables',
        selling_price: 6000,
        unit: 'Bundle',
        description: 'Crisp pesticide-free garden greens, tender spinach, lettuce, sweet basil, and fresh mint gathered at dawn.',
        image: '/images/fresh_vegetables_garden.webp',
    },
];

const getCategoryName = (prod) => {
    const rawCat = (prod?.category && typeof prod.category === 'object') ? prod.category.name : (prod?.category || '');
    const name = (prod?.name || '').toLowerCase();
    const cat = String(rawCat).toLowerCase();

    if (name.includes('egg') || name.includes('mayai')) return 'Eggs';
    if (name.includes('milk') || name.includes('mtindi') || name.includes('yogurt') || name.includes('yoghurt') || name.includes('maziwa')) return 'Dairy';
    if (name.includes('honey') || name.includes('asali') || cat.includes('honey')) return 'Honey';
    if (name.includes('mango') || name.includes('papaw') || name.includes('pineapple') || name.includes('fruit') || cat.includes('fruit') || cat.includes('orchard')) return 'Fruits';
    if (name.includes('veg') || name.includes('green') || name.includes('mboga') || cat.includes('veg')) return 'Fresh Vegetables';
    return rawCat || 'Produce';
};

const resolveProductImage = (prod) => {
    const raw = prod?.image || prod?.featured_image;
    if (raw && (raw.includes('yogurt_1l') || raw.includes('Date Milk') || raw.includes('date_milk'))) {
        return '/images/yogurt_1l.webp';
    }
    if (raw && (raw.includes('download_46') || raw.includes('download (46)'))) {
        return '/images/download_46.webp';
    }
    if (raw && (raw.includes('download_45') || raw.includes('download (45)'))) {
        return '/images/download_45.webp';
    }
    if (raw && (raw.includes('download_44') || raw.includes('download (44)'))) {
        return '/images/download_44.webp';
    }
    const name = (prod?.name || '').toLowerCase();
    const sku = (prod?.sku || '').toUpperCase();
    if (sku.includes('YOGURT-1L') || ((name.includes('yogurt') || name.includes('yoghurt')) && (name.includes('1l') || name.includes('1 liter') || name.includes('1-liter') || name.includes('(1')))) {
        return '/images/yogurt_1l.webp';
    }
    if (sku.includes('MTINDI-3L') || ((name.includes('mtindi') || name.includes('sour milk')) && name.includes('3'))) {
        return '/images/download_46.webp';
    }
    if (sku.includes('FRESH-3L') || ((name.includes('fresh') || name.includes('milk') || name.includes('maziwa')) && name.includes('3'))) {
        return '/images/download_45.webp';
    }
    if (sku.includes('MTINDI-5L') || (name.includes('mtindi') && name.includes('5'))) {
        return '/images/download_44.webp';
    }

    if (raw && (raw.includes('IMG_0404') || raw.includes('IMG_0401') || raw.includes('IMG_0394') || raw.includes('IMG_0389') || raw.includes('IMG_0341') || raw.includes('farm_egg_trays') || raw.includes('raw_forest_honey') || raw.includes('mango_wallpaper') || raw.includes('pawpaw_fresh') || raw.includes('pineapple_fresh') || raw.includes('fresh_vegetables_garden'))) {
        return raw.startsWith('/') ? raw : `/images/${raw}`;
    }
    if (raw && (raw.startsWith('http') || raw.startsWith('/'))) return raw;

    if (name.includes('egg') || name.includes('mayai') || name.includes('poultry')) return '/images/farm_egg_trays.webp';
    if (name.includes('yoghurt') || name.includes('yogurt')) return '/images/IMG_0389.webp';
    if (name.includes('mtindi') || name.includes('sour milk')) return '/images/IMG_0394.webp';
    if (name.includes('fresh whole milk') || name.includes('maziwa halisi') || name.includes('milk') || name.includes('maziwa')) return '/images/IMG_0404.webp';
    if (name.includes('honey') || name.includes('asali')) return '/images/raw_forest_honey.webp';
    if (name.includes('mango') || name.includes('embe')) return '/images/mango_wallpaper.webp';
    if (name.includes('papaw') || name.includes('papai') || name.includes('papaya')) return '/images/pawpaw_fresh.webp';
    if (name.includes('pine') || name.includes('nanasi')) return '/images/pineapple_fresh.webp';
    if (name.includes('veg') || name.includes('mboga') || name.includes('green')) return '/images/fresh_vegetables_garden.webp';

    return '/images/fresh_vegetables_garden.webp';
};

const allProducts = computed(() => {
    if (props.products && props.products.length > 0) {
        return props.products;
    }
    return defaultProducts;
});

// Category filtering & Search
const activeCategory = ref('All');
const searchQuery = ref('');
const cart = ref({});
const cartDrawerOpen = ref(false);
const deliveryNotes = ref('');
const mobileMenuOpen = ref(false);

const categoriesList = computed(() => {
    return ['All', 'Dairy & Eggs', 'Honey', 'Fruits', 'Fresh Vegetables'];
});

const filteredProducts = computed(() => {
    return allProducts.value.filter(p => {
        const catName = getCategoryName(p).toLowerCase();
        const name = (p.name || '').toLowerCase();
        const matchesCat = activeCategory.value === 'All' || 
            (activeCategory.value === 'Dairy & Eggs' && (catName.includes('dairy') || catName.includes('egg') || name.includes('milk') || name.includes('mtindi') || name.includes('yoghurt') || name.includes('yogurt') || name.includes('egg') || name.includes('mayai'))) ||
            (activeCategory.value === 'Honey' && (catName.includes('honey') || name.includes('honey') || name.includes('asali'))) ||
            (activeCategory.value === 'Fruits' && (catName.includes('fruit') || name.includes('mango') || name.includes('papaw') || name.includes('pine') || name.includes('fruit'))) ||
            (activeCategory.value === 'Fresh Vegetables' && (catName.includes('veg') || name.includes('veg') || name.includes('green') || name.includes('mboga')));

        const q = searchQuery.value.toLowerCase().trim();
        const matchesSearch = !q || (p.name || '').toLowerCase().includes(q) || (p.description || '').toLowerCase().includes(q);

        return matchesCat && matchesSearch;
    });
});

// Safe Currency Formatter
const formatCurrency = (val) => {
    const num = Number(val) || 0;
    return 'TSh ' + num.toLocaleString('en-US');
};

const getProductPrice = (prod) => {
    return Number(prod?.selling_price || prod?.price || 0);
};

// Cart Logic
onMounted(() => {
    try {
        const saved = localStorage.getItem('kitonga_cart_items');
        if (saved) cart.value = JSON.parse(saved);
    } catch (e) {}
});

const saveCart = () => {
    try {
        localStorage.setItem('kitonga_cart_items', JSON.stringify(cart.value));
    } catch (e) {}
};

const addToCart = (prod) => {
    if (!prod) return;
    if (cart.value[prod.id]) {
        cart.value[prod.id].qty += 1;
    } else {
        cart.value[prod.id] = {
            id: prod.id,
            name: prod.name,
            price: getProductPrice(prod),
            unit: prod.unit || 'unit',
            image: resolveProductImage(prod),
            qty: 1,
        };
    }
    saveCart();
};

const decrementCart = (prodId) => {
    if (cart.value[prodId]) {
        if (cart.value[prodId].qty > 1) {
            cart.value[prodId].qty -= 1;
        } else {
            delete cart.value[prodId];
        }
        saveCart();
    }
};

const removeFromCart = (prodId) => {
    if (cart.value[prodId]) {
        delete cart.value[prodId];
        saveCart();
    }
};

const cartList = computed(() => Object.values(cart.value));
const totalCartItems = computed(() => cartList.value.reduce((acc, item) => acc + item.qty, 0));
const totalCartPrice = computed(() => cartList.value.reduce((acc, item) => acc + (item.price * item.qty), 0));

// WhatsApp Checkout URL
const whatsappUrl = computed(() => {
    const phone = (props.settings?.contact_phone || '+255758774695').replace(/[^0-9]/g, '');
    if (cartList.value.length === 0) {
        return `https://wa.me/${phone}?text=${encodeURIComponent('Hello Kitonga Farm Villas, I would like to inquire about fresh harvest produce.')}`;
    }

    let msg = "ðŸŒ¿ *KITONGA FARM VILLAS â€” HARVEST ORDER*\n\n";
    msg += "Greetings, I would like to order the following fresh produce from the farm:\n\n";
    cartList.value.forEach((item, i) => {
        msg += `${i + 1}. *${item.name}*\n   - Quantity: ${item.qty} ${item.unit}\n   - Subtotal: ${formatCurrency(item.price * item.qty)}\n\n`;
    });
    msg += `ðŸ’° *TOTAL ESTIMATE: ${formatCurrency(totalCartPrice.value)}*\n\n`;
    if (deliveryNotes.value.trim()) {
        msg += `ðŸ“ *Delivery / Special Notes:* ${deliveryNotes.value.trim()}\n\n`;
    }
    msg += "Please confirm availability and dispatch details. Thank you!";
    return `https://wa.me/${phone}?text=${encodeURIComponent(msg)}`;
});
</script>

<template>
    <Head>
        <title>Produce | Kitonga Farm Villas</title>
        <meta name="description" content="Fresh organic eggs, pasture fresh milk, mtindi safi, natural yoghurt, wild forest honey, sweet mangoes, papaws, pineapples, and farm vegetables from Kitonga Farm." />
    </Head>

    <div class="min-h-screen bg-[#FDFBF7] text-[#1E2922] font-sans antialiased">
        
        <!-- TOP HEADER / NAVBAR (STICKY SO CONTENT NEVER GETS COVERED) -->
        <header class="sticky top-0 z-50 w-full bg-[#14231C]/95 backdrop-blur-md border-b border-white/10 text-white shadow-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-8 h-20 flex items-center justify-between">
                
                <!-- Logo -->
                <Link href="/" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#C98A3E] flex items-center justify-center font-serif text-xl font-bold text-white shadow-md">
                        K
                    </div>
                    <div class="flex flex-col">
                        <span class="font-serif text-lg font-bold tracking-[3px] text-white leading-none">KITONGA</span>
                        <span class="text-[9px] tracking-[4px] text-[#C98A3E] font-medium mt-1">FARMS VILLAS</span>
                    </div>
                </Link>

                <!-- Navigation Links -->
                <nav class="hidden lg:flex items-center gap-7 text-xs uppercase tracking-widest text-white/80 font-sans">
                    <Link :href="route('home')" prefetch class="hover:text-[#C98A3E] transition-colors py-2">Home</Link>
                    <Link :href="route('villas')" prefetch class="hover:text-[#C98A3E] transition-colors py-2">Villas</Link>
                    <Link :href="route('experiences')" prefetch class="hover:text-[#C98A3E] transition-colors py-2">Experiences</Link>
                    <Link :href="route('farm')" prefetch class="hover:text-[#C98A3E] transition-colors py-2">Our Farm</Link>
                    <Link :href="route('products')" prefetch class="text-[#E6C387] font-bold border-b-2 border-[#E6C387] pb-0.5">Produce</Link>
                    <Link :href="route('gallery')" prefetch class="hover:text-[#C98A3E] transition-colors py-2">Gallery</Link>
                    <Link :href="route('contact')" prefetch class="hover:text-[#C98A3E] transition-colors py-2">Contact</Link>
                    <Link :href="route('login')" prefetch class="hover:text-[#C98A3E] transition-colors py-2 text-white/70">Sign In</Link>
                </nav>

                <!-- Basket & CTA -->
                <div class="flex items-center gap-4">
                    <button 
                        @click="cartDrawerOpen = true"
                        class="flex items-center gap-2 px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 border border-white/15 text-xs font-semibold tracking-wider transition-all cursor-pointer font-sans"
                    >
                        <span>ðŸ›’ Basket</span>
                        <span v-if="totalCartItems > 0" class="px-2 py-0.5 rounded-full bg-[#C98A3E] text-white text-[10px] font-bold">
                            {{ totalCartItems }}
                        </span>
                    </button>

                    <Link 
                        :href="route('booking.form')" 
                        prefetch
                        class="hidden sm:inline-block px-5 py-2.5 bg-white text-gray-900 hover:bg-[#FAF8F5] text-xs font-extrabold uppercase tracking-wider rounded-lg transition font-sans shadow-md"
                    >
                        BOOK STAY
                    </Link>

                    <!-- Mobile Toggle -->
                    <button 
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="lg:hidden p-2 text-white focus:outline-none cursor-pointer"
                    >
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

            </div>

            <!-- Mobile Dropdown -->
            <div v-if="mobileMenuOpen" class="lg:hidden bg-[#14231C] px-6 py-6 border-t border-white/10 space-y-4 font-sans text-xs uppercase tracking-widest text-white">
                <Link :href="route('home')" prefetch class="block py-1 hover:text-[#C98A3E]">Home</Link>
                <Link :href="route('villas')" prefetch class="block py-1 hover:text-[#C98A3E]">Villas</Link>
                <Link :href="route('experiences')" prefetch class="block py-1 hover:text-[#C98A3E]">Experiences</Link>
                <Link :href="route('farm')" prefetch class="block py-1 hover:text-[#C98A3E]">Our Farm</Link>
                <Link :href="route('products')" prefetch class="block py-1 text-[#E6C387] font-bold">Produce</Link>
                <Link :href="route('gallery')" prefetch class="block py-1 hover:text-[#C98A3E]">Gallery</Link>
                <Link :href="route('contact')" prefetch class="block py-1 hover:text-[#C98A3E]">Contact</Link>
                <Link :href="route('login')" prefetch class="block py-1 text-white/60">Sign In</Link>
                <div class="pt-2 border-t border-white/10">
                    <Link :href="route('booking.form')" prefetch class="block w-full py-3 text-center rounded-lg bg-[#C98A3E] text-white text-xs font-bold uppercase tracking-wider">
                        BOOK STAY
                    </Link>
                </div>
            </div>
        </header>

        <!-- 2. MAELEZO YA SHAMBA & VITU TUNAZALISHA (GENEROUS PADDING & ELEGANT TOP BADGE) -->
        <section class="max-w-5xl mx-auto px-4 sm:px-8 pt-14 sm:pt-20 pb-12 text-center">

            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#14231C]/5 border border-[#14231C]/10 text-[#C98A3E] text-[11px] font-bold uppercase tracking-[0.2em] mb-4">
                <span>ðŸŒ¿</span> Estate Harvest & Fresh Produce
            </div>

            <h1 class="font-serif text-3xl sm:text-4xl md:text-5xl font-bold text-[#14231C] tracking-tight leading-tight mb-6">
                Fresh & Authentic Harvest From Kitonga Farm
            </h1>

            <p class="text-base sm:text-lg text-[#1E2922]/80 leading-relaxed max-w-3xl mx-auto font-normal mb-8">
                Nestled in the fertile countryside of Komkonga, Handeni, <strong>Kitonga Farm Villas</strong> cultivates pure, organic harvest for our estate guests and discerning community. We produce <strong>fresh free-range farm eggs</strong>, <strong>rich pasture whole milk</strong>, <strong>traditional mtindi safi</strong>, <strong>probiotic artisanal yogurt</strong>, <strong>raw wild forest honey</strong>, sweet tree-ripened seasonal fruits such as <strong>mangoes, papaws, and pineapples</strong>, alongside <strong>crisp spring-fed farm vegetables</strong> harvested fresh daily with uncompromised quality.
            </p>

            <!-- Highlights Pill Strip -->
            <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-4 text-xs font-semibold text-[#14231C]/80">
                <span class="px-4 py-2 rounded-xl bg-white shadow-xs border border-[#14231C]/10 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#C98A3E]"></span>
                    Farm Fresh Free-Range Eggs
                </span>
                <span class="px-4 py-2 rounded-xl bg-white shadow-xs border border-[#14231C]/10 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-700"></span>
                    Fresh Milk, Mtindi & Yogurt
                </span>
                <span class="px-4 py-2 rounded-xl bg-white shadow-xs border border-[#14231C]/10 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-600"></span>
                    Raw Wild Forest Honey
                </span>
                <span class="px-4 py-2 rounded-xl bg-white shadow-xs border border-[#14231C]/10 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-orange-600"></span>
                    Mangoes, Papaws & Pineapples
                </span>
                <span class="px-4 py-2 rounded-xl bg-white shadow-xs border border-[#14231C]/10 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                    Spring-Fed Fresh Vegetables
                </span>
            </div>

        </section>

        <!-- 3. FILTER TABS & SEARCH BAR -->
        <section class="max-w-7xl mx-auto px-4 sm:px-8 mb-10">
            <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-sm border border-[#14231C]/10 flex flex-col md:flex-row items-center justify-between gap-4">
                
                <!-- Category Buttons -->
                <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                    <button 
                        v-for="cat in categoriesList" 
                        :key="cat"
                        @click="activeCategory = cat"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer"
                        :class="activeCategory === cat 
                            ? 'bg-[#14231C] text-white shadow-sm' 
                            : 'bg-[#F4EFE6] hover:bg-[#EBE2D5] text-[#14231C]'"
                    >
                        {{ cat }}
                    </button>
                </div>

                <!-- Search Input -->
                <div class="relative w-full md:w-72">
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Search any harvest..."
                        class="w-full pl-9 pr-4 py-2 bg-[#FDFBF7] border border-[#14231C]/15 rounded-xl text-xs text-[#14231C] focus:outline-none focus:border-[#C98A3E]"
                    />
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-[#14231C]/40">ðŸ”</span>
                </div>

            </div>
        </section>

        <!-- 4. SIMPLE SMART PRODUCT CARDS GRID -->
        <section class="max-w-7xl mx-auto px-4 sm:px-8 pb-24">
            
            <div v-if="filteredProducts.length === 0" class="py-20 text-center space-y-3">
                <span class="text-4xl">ðŸŒ¾</span>
                <h3 class="font-serif text-xl font-bold text-[#14231C]">No harvest items found</h3>
                <p class="text-xs text-[#14231C]/60">Try selecting another category or resetting your search term.</p>
                <button 
                    @click="activeCategory = 'All'; searchQuery = '';"
                    class="px-5 py-2 rounded-full bg-[#14231C] text-white text-xs font-bold mt-2 cursor-pointer"
                >
                    Show All Produce
                </button>
            </div>

            <!-- Product Cards -->
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <div 
                    v-for="prod in filteredProducts" 
                    :key="prod.id"
                    class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl border border-[#14231C]/10 flex flex-col justify-between transition-all duration-300 group"
                >
                    <!-- Product Image Container with Crisp Aesthetic Fit -->
                    <div class="relative h-60 sm:h-64 md:h-72 w-full overflow-hidden bg-[#F4EFE6] flex items-center justify-center">
                        <img loading="lazy" decoding="async" 
                            :src="resolveProductImage(prod)" 
                            :alt="prod.name" 
                            class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500"
                        />
                        <div class="absolute top-3 left-3">
                            <span class="px-2.5 py-1 rounded-md bg-[#14231C]/85 backdrop-blur-sm text-white text-[10px] font-bold uppercase tracking-wider shadow-xs">
                                {{ getCategoryName(prod) }}
                            </span>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                        
                        <div class="space-y-2">
                            <h3 class="font-serif text-lg font-bold text-[#14231C] leading-snug group-hover:text-[#C98A3E] transition-colors">
                                {{ prod.name }}
                            </h3>
                            <p class="text-xs text-[#1E2922]/70 line-clamp-2 leading-relaxed">
                                {{ prod.description }}
                            </p>
                        </div>

                        <!-- Price & Actions -->
                        <div class="pt-3 border-t border-[#14231C]/10 flex items-center justify-between gap-2">
                            <div>
                                <div class="font-bold text-base text-[#14231C]">
                                    {{ formatCurrency(getProductPrice(prod)) }}
                                </div>
                                <div v-if="prod.unit" class="text-[10px] text-[#14231C]/60 font-medium">
                                    per {{ prod.unit }}
                                </div>
                            </div>

                            <!-- Stepper or Buy Button -->
                            <div>
                                <div v-if="cart[prod.id]" class="flex items-center bg-[#F4EFE6] rounded-full p-1 border border-[#14231C]/10">
                                    <button @click="decrementCart(prod.id)" class="w-6 h-6 rounded-full bg-white text-[#14231C] font-bold text-xs flex items-center justify-center shadow-sm cursor-pointer">âˆ’</button>
                                    <span class="w-6 text-center font-bold text-xs">{{ cart[prod.id].qty }}</span>
                                    <button @click="addToCart(prod)" class="w-6 h-6 rounded-full bg-white text-[#14231C] font-bold text-xs flex items-center justify-center shadow-sm cursor-pointer">+</button>
                                </div>

                                <button 
                                    v-else
                                    @click="addToCart(prod)"
                                    class="px-4 py-2 rounded-xl bg-[#14231C] hover:bg-[#C98A3E] text-white text-xs font-bold flex items-center gap-1.5 transition-all shadow-sm cursor-pointer"
                                >
                                    <span>Buy</span>
                                    <span>+</span>
                                </button>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </section>

        <!-- 5. BASKET DRAWER -->
        <div 
            v-if="cartDrawerOpen" 
            class="fixed inset-0 z-50 overflow-hidden"
            role="dialog"
            aria-modal="true"
        >
            <div @click="cartDrawerOpen = false" class="absolute inset-0 bg-black/60 backdrop-blur-xs transition-opacity"></div>

            <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
                <div class="w-screen max-w-md bg-white shadow-2xl flex flex-col justify-between">
                    
                    <!-- Header -->
                    <div class="p-6 bg-[#14231C] text-white flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">ðŸ§º</span>
                            <div>
                                <h3 class="font-serif text-lg font-bold text-white">Your Harvest Basket</h3>
                                <span class="text-[11px] text-[#C98A3E] font-medium">Kitonga Farm Direct Order</span>
                            </div>
                        </div>
                        <button @click="cartDrawerOpen = false" class="text-white/70 hover:text-white p-2 text-lg cursor-pointer">âœ•</button>
                    </div>

                    <!-- Items -->
                    <div class="p-6 overflow-y-auto flex-1 space-y-4">
                        
                        <div v-if="cartList.length === 0" class="py-16 text-center space-y-3">
                            <span class="text-4xl block">ðŸ§º</span>
                            <h4 class="font-serif text-base font-bold text-[#14231C]">Your basket is empty</h4>
                            <p class="text-xs text-[#14231C]/60">Select eggs, dairy, honey, or fresh fruits above to add to your basket.</p>
                        </div>

                        <div v-else class="space-y-3">
                            <div 
                                v-for="item in cartList" 
                                :key="item.id"
                                class="flex items-center gap-3 p-3 bg-[#FDFBF7] rounded-xl border border-[#14231C]/10"
                            >
                                <img loading="lazy" decoding="async" :src="item.image" :alt="item.name" class="w-14 h-14 rounded-lg object-cover" />
                                
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-bold text-xs text-[#14231C] truncate">{{ item.name }}</h4>
                                    <div class="text-[11px] text-[#C98A3E] font-semibold">
                                        {{ formatCurrency(item.price) }} / {{ item.unit }}
                                    </div>
                                    <div class="text-xs font-bold text-[#14231C] mt-0.5">
                                        Subtotal: {{ formatCurrency(item.price * item.qty) }}
                                    </div>
                                </div>

                                <div class="flex items-center bg-white border border-[#14231C]/15 rounded-lg p-0.5 shadow-2xs">
                                    <button @click="decrementCart(item.id)" class="w-7 h-7 rounded text-xs font-bold hover:bg-gray-100 flex items-center justify-center cursor-pointer transition">">âˆ’</button>
                                    <span class="w-6 text-center text-xs font-bold font-mono">{{ item.qty }}</span>
                                    <button @click="addToCart(item)" class="w-7 h-7 rounded text-xs font-bold hover:bg-gray-100 flex items-center justify-center cursor-pointer transition">+</button>
                                </div>

                                <button @click="removeFromCart(item.id)" class="text-red-500 hover:text-red-700 p-1.5 text-xs cursor-pointer" aria-label="Remove item">âœ•</button>
                            </div>

                            <!-- Delivery notes -->
                            <div class="pt-3">
                                <label class="text-xs font-bold text-[#14231C] block mb-1">Special Delivery Instructions:</label>
                                <textarea 
                                    v-model="deliveryNotes"
                                    rows="2"
                                    placeholder="e.g. Deliver to Luxury Villa 2 at 8:00 AM, or pickup at main gate..."
                                    class="w-full p-2.5 bg-[#FDFBF7] border border-[#14231C]/15 rounded-xl text-xs focus:outline-none focus:border-[#C98A3E]"
                                ></textarea>
                            </div>
                        </div>

                    </div>

                    <!-- Footer Checkout -->
                    <div class="p-5 sm:p-6 bg-[#FDFBF7] border-t border-[#14231C]/10 space-y-3.5">
                        <div class="flex justify-between items-baseline">
                            <span class="text-xs font-bold text-[#14231C]/70 uppercase tracking-wider">Estimated Total</span>
                            <span class="font-serif text-2xl font-bold text-[#14231C] font-mono">{{ formatCurrency(totalCartPrice) }}</span>
                        </div>

                        <a 
                            :href="whatsappUrl"
                            target="_blank"
                            class="w-full py-3.5 rounded-xl bg-[#25D366] hover:bg-[#20ba59] text-white text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-2 shadow-md transition-all cursor-pointer"
                        >
                            <span>ðŸ’¬ Send Order via WhatsApp</span>
                        </a>
                    </div>

                </div>
            </div>
        </div>

        <!-- 6. FLOATING BASKET BUTTON -->
        <button 
            v-if="totalCartItems > 0"
            @click="cartDrawerOpen = true"
            class="fixed bottom-6 right-6 z-40 flex items-center gap-3 px-5 py-3 rounded-full bg-[#14231C] hover:bg-[#C98A3E] text-white shadow-2xl border border-white/20 transition-all cursor-pointer"
        >
            <span class="text-base">ðŸ›’</span>
            <span class="text-xs font-bold tracking-wider">
                {{ totalCartItems }} {{ totalCartItems === 1 ? 'Item' : 'Items' }} Â· {{ formatCurrency(totalCartPrice) }}
            </span>
        </button>

        <!-- 7. FOOTER -->
        <footer class="bg-[#14231C] text-white py-12 px-4 sm:px-8 border-t border-white/10 text-center">
            <div class="max-w-7xl mx-auto space-y-4">
                <div class="font-serif text-xl font-bold tracking-[3px] text-[#C98A3E]">KITONGA FARM VILLAS</div>
                <p class="text-xs text-white/60 max-w-md mx-auto">
                    Organic farm and luxury private villas in the Komkonga highlands, Tanga, Tanzania.
                </p>
                <div class="text-xs text-white/50 pt-4 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p>© 2026 Kitonga Farm Villas Sanctuary. All rights reserved.</p>
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
            </div>
        </footer>

    </div>
</template>


