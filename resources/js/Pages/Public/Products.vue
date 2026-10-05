<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch, nextTick } from 'vue';

const props = defineProps({
    products: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    settings: {
        type: Object,
        default: () => ({ contact_phone: '+255758774695' }),
    },
    auth: { type: Object, default: () => ({ user: null }) },
});

// ─── Default Products (Fallback when DB empty) ────────────────────────────────
const defaultProducts = [
    { id: 1, sku: 'KFV-MAYAI', name: 'Farm Fresh Free-Range Eggs', category: 'Eggs', selling_price: 8000, unit: 'Tray (30 Eggs)', badge: 'Best Seller', description: 'Farm-fresh organic eggs with vibrant golden yolks, gathered every morning from free-range pasture hens roaming the Kitonga highlands.', image: '/images/farm_egg_trays.webp' },
    { id: 2, sku: 'KFV-MTINDI-5L', name: 'Kitonga Cultured Sour Milk / Mtindi (5L)', category: 'Dairy', selling_price: 17000, unit: '5 Liters', badge: 'Fresh Today', description: 'Traditional thick and creamy cultured sour milk from 100% pure Kitonga pasture milk. Rich in natural probiotics and authentic heritage taste.', image: '/images/kitonga_mtindi_5l.webp' },
    { id: 3, sku: 'KFV-MTINDI-3L', name: 'Kitonga Cultured Sour Milk / Mtindi (3L)', category: 'Dairy', selling_price: 13000, unit: '3 Liters', badge: '', description: 'Traditional thick and creamy cultured sour milk from pure farm pasture milk, bottled fresh in a convenient 3L container.', image: '/images/kitonga_mtindi_3l.webp' },
    { id: 4, sku: 'KFV-FRESH-5L', name: 'Fresh Pasture Whole Milk (5 Liters)', category: 'Dairy', selling_price: 13000, unit: '5 Liters', badge: 'Fresh Today', description: '100% pure fresh unpasteurized whole farm milk rich in natural golden cream, harvested daily from our pasture-fed dairy cattle.', image: '/images/IMG_0404.webp' },
    { id: 5, sku: 'KFV-FRESH-3L', name: 'Fresh Pasture Whole Milk (3 Liters)', category: 'Dairy', selling_price: 9000, unit: '3 Liters', badge: '', description: 'Pure wholesome unhomogenized fresh farm milk rich in nutrients and natural cream, bottled daily in a 3L container.', image: '/images/fresh_milk_3l.webp' },
    { id: 6, sku: 'KFV-YOGURT-1L', name: 'Probiotic Artisanal Yogurt (1 Liter)', category: 'Dairy', selling_price: 6000, unit: '1 Liter', badge: '', description: 'Smooth, velvety artisanal drinking yogurt cultured from fresh morning milk, rich in natural probiotics and authentic farm sweetness.', image: '/images/yogurt_1l.webp' },
    { id: 7, sku: 'KFV-YOGURT-05L', name: 'Probiotic Artisanal Yogurt (500ml)', category: 'Dairy', selling_price: 3000, unit: '0.5 L', badge: '', description: 'Delicious probiotic artisanal drinking yogurt crafted from pure whole milk, bottled in a convenient 500ml on-the-go size.', image: '/images/IMG_0389.webp' },
    { id: 8, sku: 'KFV-ASALI-1KG', name: 'Raw Wild Forest Honey (1kg)', category: 'Honey', selling_price: 10000, unit: '1 kg', badge: 'Best Seller', description: '100% pure, unfiltered and unheated golden raw honey harvested from traditional highland top-bar apiaries deep in the Komkonga forest.', image: '/images/raw_forest_honey.webp' },
    { id: 9, sku: 'KFV-MANGO-1KG', name: 'Sweet Kitonga Highland Mangoes (1kg)', category: 'Fruits', selling_price: 3000, unit: '1 kg', badge: 'Fresh Today', description: 'Sun-ripened, fragrant organic mangoes picked directly from mature estate orchard trees, bursting with natural highland sweetness.', image: '/images/mango_wallpaper.webp' },
    { id: 10, sku: 'KFV-PAPAW-1PC', name: 'Tree-Ripened Sweet Papaws (Papayas)', category: 'Fruits', selling_price: 4000, unit: 'per Piece', badge: '', description: 'Luscious, vibrant orange sweet papaws cultivated in fertile highland soil and harvested at peak natural sweetness.', image: '/images/pawpaw_fresh.webp' },
    { id: 11, sku: 'KFV-PINEAPPLE-1PC', name: 'Sun-Drenched Estate Pineapples', category: 'Fruits', selling_price: 4500, unit: 'per Piece', badge: '', description: 'Naturally sweet and juicy tropical pineapples grown with pure highland mountain rainfall and sunshine on the Kitonga estate.', image: '/images/pineapple_fresh.webp' },
    { id: 12, sku: 'KFV-VEG-BUNDLE', name: 'Spring-Fed Fresh Vegetables & Greens', category: 'Vegetables', selling_price: 6000, unit: 'Bundle', badge: 'Fresh Today', description: 'Crisp pesticide-free garden greens, tender spinach, lettuce, sweet basil, and fresh mint gathered at dawn from spring-fed beds.', image: '/images/fresh_vegetables_garden.webp' },
];

// ─── Category helpers ────────────────────────────────────────────────────────
const getCategoryName = (prod) => {
    const rawCat = (prod?.category && typeof prod.category === 'object') ? prod.category.name : (prod?.category || '');
    const name = (prod?.name || '').toLowerCase();
    const cat = String(rawCat).toLowerCase();
    if (name.includes('egg') || name.includes('mayai')) return 'Eggs';
    if (name.includes('milk') || name.includes('mtindi') || name.includes('yogurt') || name.includes('yoghurt') || name.includes('maziwa')) return 'Dairy';
    if (name.includes('honey') || name.includes('asali') || cat.includes('honey')) return 'Honey';
    if (name.includes('mango') || name.includes('papaw') || name.includes('pineapple') || name.includes('fruit') || cat.includes('fruit')) return 'Fruits';
    if (name.includes('veg') || name.includes('green') || name.includes('mboga') || cat.includes('veg')) return 'Vegetables';
    return rawCat || 'Produce';
};

const resolveProductImage = (prod) => {
    const raw = prod?.image || prod?.featured_image;
    const name = (prod?.name || '').toLowerCase();
    const sku = (prod?.sku || '').toUpperCase();

    // Specific SKU mappings
    if (sku.includes('MTINDI-5L') || (name.includes('mtindi') && name.includes('5'))) return '/images/kitonga_mtindi_5l.webp';
    if (sku.includes('MTINDI-3L') || (name.includes('mtindi') && name.includes('3'))) return '/images/kitonga_mtindi_3l.webp';
    if (sku.includes('FRESH-5L') || (name.includes('milk') && name.includes('5'))) return '/images/IMG_0404.webp';
    if (sku.includes('FRESH-3L') || (name.includes('milk') && name.includes('3'))) return '/images/fresh_milk_3l.webp';
    if (sku.includes('YOGURT-1L') || (name.includes('yogurt') && name.includes('1'))) return '/images/yogurt_1l.webp';
    if (sku.includes('YOGURT-05L') || (name.includes('yogurt') && name.includes('500'))) return '/images/IMG_0389.webp';

    if (raw && raw.startsWith('http')) return raw;
    if (raw && raw.startsWith('/')) return raw;

    // Category fallbacks
    if (name.includes('egg') || name.includes('mayai')) return '/images/farm_egg_trays.webp';
    if (name.includes('yoghurt') || name.includes('yogurt')) return '/images/yogurt_1l.webp';
    if (name.includes('mtindi') || name.includes('sour milk')) return '/images/kitonga_mtindi_5l.webp';
    if (name.includes('milk') || name.includes('maziwa')) return '/images/IMG_0404.webp';
    if (name.includes('honey') || name.includes('asali')) return '/images/raw_forest_honey.webp';
    if (name.includes('mango') || name.includes('embe')) return '/images/mango_wallpaper.webp';
    if (name.includes('papaw') || name.includes('papaya')) return '/images/pawpaw_fresh.webp';
    if (name.includes('pine') || name.includes('nanasi')) return '/images/pineapple_fresh.webp';
    if (name.includes('veg') || name.includes('mboga') || name.includes('green')) return '/images/fresh_vegetables_garden.webp';
    return '/images/fresh_vegetables_garden.webp';
};

// ─── All Products ─────────────────────────────────────────────────────────────
const allProducts = computed(() => {
    if (props.products && props.products.length > 0) return props.products;
    return defaultProducts;
});

// ─── Filter / Search / Sort ────────────────────────────────────────────────
const activeCategory = ref('All');
const searchQuery = ref('');
const sortOrder = ref('popular');
const visibleCount = ref(12);
const categoriesList = ['All', 'Eggs', 'Dairy', 'Honey', 'Fruits', 'Vegetables'];

const filteredProducts = computed(() => {
    let list = allProducts.value.filter(p => {
        const catName = getCategoryName(p).toLowerCase();
        const name = (p.name || '').toLowerCase();
        const matchesCat = activeCategory.value === 'All'
            || (activeCategory.value === 'Eggs' && (catName.includes('egg') || name.includes('egg') || name.includes('mayai')))
            || (activeCategory.value === 'Dairy' && (catName.includes('dairy') || name.includes('milk') || name.includes('mtindi') || name.includes('yogurt') || name.includes('yoghurt') || name.includes('maziwa')))
            || (activeCategory.value === 'Honey' && (catName.includes('honey') || name.includes('honey') || name.includes('asali')))
            || (activeCategory.value === 'Fruits' && (catName.includes('fruit') || name.includes('mango') || name.includes('papaw') || name.includes('pineapple')))
            || (activeCategory.value === 'Vegetables' && (catName.includes('veg') || name.includes('veg') || name.includes('mboga') || name.includes('green')));
        const q = searchQuery.value.toLowerCase().trim();
        const matchesSearch = !q || name.includes(q) || (p.description || '').toLowerCase().includes(q);
        return matchesCat && matchesSearch;
    });

    if (sortOrder.value === 'price_asc') list = [...list].sort((a, b) => (a.selling_price || a.price || 0) - (b.selling_price || b.price || 0));
    else if (sortOrder.value === 'price_desc') list = [...list].sort((a, b) => (b.selling_price || b.price || 0) - (a.selling_price || a.price || 0));

    return list;
});

const visibleProducts = computed(() => filteredProducts.value.slice(0, visibleCount.value));
const hasMore = computed(() => visibleCount.value < filteredProducts.value.length);

watch([activeCategory, searchQuery], () => { visibleCount.value = 12; });

const loadMore = () => { visibleCount.value += 8; };

// ─── Currency ─────────────────────────────────────────────────────────────────
const formatCurrency = (val) => 'TSh ' + (Number(val) || 0).toLocaleString('en-US');
const getPrice = (p) => Number(p?.selling_price || p?.price || 0);

// ─── Cart ─────────────────────────────────────────────────────────────────────
const cart = ref({});
const cartDrawerOpen = ref(false);
const deliveryNotes = ref('');
const mobileMenuOpen = ref(false);
const toastMessage = ref('');
const toastVisible = ref(false);
let toastTimer = null;

onMounted(() => {
    try {
        const saved = localStorage.getItem('kitonga_cart_v2');
        if (saved) cart.value = JSON.parse(saved);
    } catch (e) {}
});

const saveCart = () => {
    try { localStorage.setItem('kitonga_cart_v2', JSON.stringify(cart.value)); } catch (e) {}
};

const showToast = (msg) => {
    toastMessage.value = msg;
    toastVisible.value = true;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => { toastVisible.value = false; }, 2500);
};

const addToCart = (prod) => {
    if (!prod) return;
    if (cart.value[prod.id]) {
        cart.value[prod.id].qty += 1;
    } else {
        cart.value[prod.id] = {
            id: prod.id, name: prod.name,
            price: getPrice(prod), unit: prod.unit || 'unit',
            image: resolveProductImage(prod), qty: 1,
        };
    }
    saveCart();
    showToast('Added to basket 🛒');
};

const decrementCart = (prodId) => {
    if (cart.value[prodId]) {
        if (cart.value[prodId].qty > 1) cart.value[prodId].qty -= 1;
        else delete cart.value[prodId];
        saveCart();
    }
};

const removeFromCart = (prodId) => {
    delete cart.value[prodId];
    saveCart();
};

const cartList = computed(() => Object.values(cart.value));
const totalCartItems = computed(() => cartList.value.reduce((a, i) => a + i.qty, 0));
const totalCartPrice = computed(() => cartList.value.reduce((a, i) => a + i.price * i.qty, 0));

// ─── WhatsApp URL ─────────────────────────────────────────────────────────────
const whatsappPhone = computed(() => (props.settings?.contact_phone || '+255758774695').replace(/[^0-9]/g, ''));

const whatsappUrl = computed(() => {
    if (cartList.value.length === 0) {
        return `https://wa.me/${whatsappPhone.value}?text=${encodeURIComponent('Hello Kitonga Farm Villas, I would like to inquire about fresh harvest produce.')}`;
    }
    let msg = '*KITONGA FARM VILLAS — HARVEST ORDER*\n\n';
    msg += 'Greetings, I would like to order the following fresh produce:\n\n';
    cartList.value.forEach((item, i) => {
        msg += `${i + 1}. *${item.name}*\n   Qty: ${item.qty} ${item.unit}\n   Subtotal: ${formatCurrency(item.price * item.qty)}\n\n`;
    });
    msg += `*TOTAL ESTIMATE: ${formatCurrency(totalCartPrice.value)}*\n\n`;
    if (deliveryNotes.value.trim()) msg += `*Notes:* ${deliveryNotes.value.trim()}\n\n`;
    msg += 'Please confirm availability and dispatch. Thank you!';
    return `https://wa.me/${whatsappPhone.value}?text=${encodeURIComponent(msg)}`;
});

const directWhatsapp = computed(() =>
    `https://wa.me/${whatsappPhone.value}?text=${encodeURIComponent('Hello Kitonga Farm Villas, I would like to order fresh produce from the farm.')}`
);

// ─── Quick View Modal ─────────────────────────────────────────────────────────
const quickViewProduct = ref(null);
const quickViewQty = ref(1);

const openQuickView = (prod) => {
    quickViewProduct.value = prod;
    quickViewQty.value = cart.value[prod.id]?.qty || 1;
};

const closeQuickView = () => { quickViewProduct.value = null; };

const addQuickViewToCart = () => {
    if (!quickViewProduct.value) return;
    const prod = quickViewProduct.value;
    cart.value[prod.id] = {
        id: prod.id, name: prod.name,
        price: getPrice(prod), unit: prod.unit || 'unit',
        image: resolveProductImage(prod), qty: quickViewQty.value,
    };
    saveCart();
    showToast(`Added ${quickViewQty.value}x ${prod.name} to basket 🛒`);
    closeQuickView();
};

// Scroll hero to products
const scrollToProducts = () => {
    document.getElementById('products-grid')?.scrollIntoView({ behavior: 'smooth' });
};
</script>

<template>
    <Head>
        <title>Fresh Farm Produce | Kitonga Farm Villas</title>
        <meta name="description" content="Order fresh free-range eggs, pasture milk, artisanal yogurt, wild forest honey, sweet mangoes, papaws, pineapples and farm vegetables directly from Kitonga Farm in Komkonga, Handeni." />
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet" />
    </Head>

    <div class="min-h-screen antialiased" style="font-family: 'Figtree', sans-serif; background-color: #FFFBF5; color: #1A1A1A;">

        <!-- ── TOAST NOTIFICATION ─────────────────────────────────────── -->
        <Transition name="toast">
            <div v-if="toastVisible"
                class="fixed top-6 right-6 z-[200] flex items-center gap-3 px-5 py-3 rounded-full text-white text-sm font-semibold shadow-2xl"
                style="background-color: #14301F;">
                <span>{{ toastMessage }}</span>
            </div>
        </Transition>

        <!-- ── SECTION 0: TOP INFO BAR ───────────────────────────────── -->
        <div class="hidden sm:block text-white text-xs py-2 px-4" style="background-color: #0d2015;">
            <div class="max-w-[1200px] mx-auto flex items-center justify-between">
                <div class="flex items-center gap-5">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Komkonga, Handeni, Tanga
                    </span>
                    <span class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Mon–Sun: 6:00 AM – 6:00 PM
                    </span>
                </div>
                <div class="flex items-center gap-4">
                    <a :href="`tel:${whatsappPhone}`" class="flex items-center gap-1.5 hover:opacity-80 transition-opacity" style="color: #D98A3D;">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        +255 758 774 695
                    </a>
                    <a href="https://wa.me/255758774695" target="_blank" class="opacity-70 hover:opacity-100 transition-opacity">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    </a>
                    <a href="https://instagram.com" target="_blank" class="opacity-70 hover:opacity-100 transition-opacity">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                    </a>
                    <a href="https://facebook.com" target="_blank" class="opacity-70 hover:opacity-100 transition-opacity">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- ── SECTION 1: STICKY HEADER ──────────────────────────────── -->
        <header class="sticky top-0 z-50 w-full backdrop-blur-md border-b shadow-md" style="background-color: rgba(20,48,31,0.97); border-color: rgba(255,255,255,0.08);">
            <div class="max-w-[1200px] mx-auto px-6 lg:px-8 h-[70px] flex items-center justify-between">

                <!-- Logo -->
                <Link href="/" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-white shadow-md text-xl" style="background-color: #D98A3D; font-family: 'Playfair Display', serif;">K</div>
                    <div class="flex flex-col">
                        <span class="font-bold tracking-[3px] text-white leading-none text-base" style="font-family: 'Playfair Display', serif;">KITONGA</span>
                        <span class="text-[9px] tracking-[4px] font-medium mt-0.5" style="color: #D98A3D;">FARMS VILLAS</span>
                    </div>
                </Link>

                <!-- Nav Links (desktop) -->
                <nav class="hidden lg:flex items-center gap-7 text-xs uppercase tracking-widest text-white/80">
                    <Link :href="route('home')" prefetch class="hover:opacity-100 transition-opacity py-2" style="color: rgba(255,255,255,0.75);">Home</Link>
                    <Link :href="route('villas')" prefetch class="hover:opacity-100 transition-opacity py-2" style="color: rgba(255,255,255,0.75);">Villas</Link>
                    <Link :href="route('experiences')" prefetch class="hover:opacity-100 transition-opacity py-2" style="color: rgba(255,255,255,0.75);">Experiences</Link>
                    <Link :href="route('farm')" prefetch class="hover:opacity-100 transition-opacity py-2" style="color: rgba(255,255,255,0.75);">Our Farm</Link>
                    <Link :href="route('products')" prefetch class="font-bold border-b-2 pb-0.5" style="color: #D98A3D; border-color: #D98A3D;">Produce</Link>
                    <Link :href="route('gallery')" prefetch class="hover:opacity-100 transition-opacity py-2" style="color: rgba(255,255,255,0.75);">Gallery</Link>
                    <Link :href="route('contact')" prefetch class="hover:opacity-100 transition-opacity py-2" style="color: rgba(255,255,255,0.75);">Contact</Link>
                </nav>

                <!-- Basket + CTA -->
                <div class="flex items-center gap-3">
                    <!-- Basket Button -->
                    <button @click="cartDrawerOpen = true" class="flex items-center gap-2 px-3.5 py-2 rounded-full border text-xs font-semibold tracking-wide transition-all cursor-pointer relative" style="background-color: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.15); color: white;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #D98A3D;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span class="hidden sm:inline">Basket</span>
                        <span v-if="totalCartItems > 0" class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold text-white" style="background-color: #D98A3D;">{{ totalCartItems }}</span>
                    </button>

                    <Link :href="route('booking.form')" prefetch class="hidden sm:inline-block px-5 py-2 rounded-full text-xs font-bold uppercase tracking-wider transition-all shadow-md" style="background-color: #D98A3D; color: white;">
                        Book Stay
                    </Link>

                    <!-- Hamburger -->
                    <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" class="p-1.5 lg:hidden cursor-pointer text-white">
                        <svg v-if="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div v-if="mobileMenuOpen" class="lg:hidden px-6 py-5 border-t space-y-3 text-xs uppercase tracking-widest text-white" style="background-color: #14301F; border-color: rgba(255,255,255,0.08);">
                <Link :href="route('home')" prefetch class="block py-1.5 hover:opacity-80">Home</Link>
                <Link :href="route('villas')" prefetch class="block py-1.5 hover:opacity-80">Villas</Link>
                <Link :href="route('experiences')" prefetch class="block py-1.5 hover:opacity-80">Experiences</Link>
                <Link :href="route('farm')" prefetch class="block py-1.5 hover:opacity-80">Our Farm</Link>
                <Link :href="route('products')" prefetch class="block py-1.5 font-bold" style="color: #D98A3D;">Produce</Link>
                <Link :href="route('gallery')" prefetch class="block py-1.5 hover:opacity-80">Gallery</Link>
                <Link :href="route('contact')" prefetch class="block py-1.5 hover:opacity-80">Contact</Link>
                <div class="pt-2 border-t" style="border-color: rgba(255,255,255,0.1);">
                    <Link :href="route('booking.form')" prefetch class="block w-full py-3 text-center rounded-full font-bold text-white text-xs" style="background-color: #D98A3D;">Book Stay</Link>
                </div>
            </div>
        </header>

        <!-- ── SECTION 2: HERO ────────────────────────────────────────── -->
        <section class="relative overflow-hidden" style="background-color: #FFFBF5; padding: 72px 0 56px;">
            <div class="max-w-[1200px] mx-auto px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-10 lg:gap-16 items-center">

                    <!-- Left: Text -->
                    <div class="order-2 lg:order-1">
                        <!-- Badge -->
                        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-[11px] font-bold uppercase tracking-[0.18em] mb-5 border" style="background-color: rgba(20,48,31,0.05); border-color: rgba(20,48,31,0.1); color: #D98A3D;">
                            <span style="color: #D98A3D;">•</span> Estate Harvest &amp; Fresh Produce
                        </div>

                        <!-- H1 -->
                        <h1 class="font-bold leading-tight mb-5" style="font-family: 'Playfair Display', serif; font-size: clamp(34px, 5vw, 56px); line-height: 1.15;">
                            <span style="color: #14301F;">Fresh &amp; Authentic</span><br>
                            <span style="color: #1A1A1A;">Harvest From Kitonga Farm</span>
                        </h1>

                        <!-- Subtext -->
                        <p class="leading-relaxed mb-8 max-w-md" style="font-size: 16px; line-height: 1.65; color: #5F6B63;">
                            Free-range eggs, pasture milk, wild forest honey and tree-ripened fruits, harvested daily in Komkonga, Handeni.
                        </p>

                        <!-- CTAs -->
                        <div class="flex flex-wrap gap-4">
                            <button @click="scrollToProducts" class="flex items-center gap-2 px-7 py-3 rounded-full font-semibold text-white text-sm transition-all shadow-md hover:shadow-lg hover:-translate-y-0.5 cursor-pointer" style="background-color: #14301F; height: 48px;">
                                Shop Now
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <a :href="directWhatsapp" target="_blank" class="flex items-center gap-2 px-7 py-3 rounded-full font-semibold text-white text-sm transition-all shadow-md hover:shadow-lg hover:-translate-y-0.5" style="background-color: #25D366; height: 48px;">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                Order on WhatsApp
                            </a>
                        </div>
                    </div>

                    <!-- Right: Hero Image -->
                    <div class="order-1 lg:order-2">
                        <div class="relative rounded-3xl overflow-hidden shadow-2xl" style="aspect-ratio: 4/3;">
                            <img
                                src="/images/produce_hero_fruit_bg.webp"
                                alt="Fresh organic harvest from Kitonga Farm — eggs, honey, fruits and dairy produce"
                                class="w-full h-full object-cover"
                                loading="eager"
                            />
                            <div class="absolute inset-0 rounded-3xl" style="background: linear-gradient(135deg, rgba(20,48,31,0.12) 0%, transparent 60%);"></div>
                            <!-- Floating badge -->
                            <div class="absolute bottom-5 left-5 flex items-center gap-3 px-4 py-3 rounded-2xl shadow-xl" style="background-color: rgba(255,251,245,0.97);">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm" style="background-color: #14301F;">✓</div>
                                <div>
                                    <div class="text-xs font-bold" style="color: #14301F;">100% Farm Fresh</div>
                                    <div class="text-[11px]" style="color: #5F6B63;">Harvested Daily</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── SECTION 3: TRUST STRIP ─────────────────────────────────── -->
        <section class="border-y" style="background-color: #fff; border-color: #E8E2D6; padding: 32px 0;">
            <div class="max-w-[1200px] mx-auto px-6 lg:px-8">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-0 divide-x" style="border-color: #E8E2D6;">
                    <div v-for="(trust, i) in [
                        { icon: '🌿', title: 'Free-Range', desc: 'Animals roam freely on open pasture' },
                        { icon: '🌾', title: 'Pasture-Fed', desc: 'Rich natural highland feed' },
                        { icon: '🍯', title: 'Raw & Natural', desc: 'Minimally processed, never adulterated' },
                        { icon: '🌅', title: 'Harvested Daily', desc: 'Fresh to your table every morning' },
                    ]" :key="i" class="flex flex-col sm:flex-row items-start sm:items-center gap-3 px-6 py-4" style="border-color: #E8E2D6;">
                        <span class="text-2xl">{{ trust.icon }}</span>
                        <div>
                            <div class="font-semibold text-sm" style="color: #14301F;">{{ trust.title }}</div>
                            <div class="text-xs mt-0.5" style="color: #5F6B63;">{{ trust.desc }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── SECTION 4: FILTER + SEARCH + SORT (sticky) ────────────── -->
        <div class="sticky z-40 border-b shadow-sm" style="top: 70px; background-color: #FFFBF5; border-color: #E8E2D6; padding: 12px 0;">
            <div class="max-w-[1200px] mx-auto px-6 lg:px-8 flex flex-col sm:flex-row items-start sm:items-center gap-3 justify-between">

                <!-- Category Tabs (scrollable on mobile) -->
                <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0 scrollbar-none flex-1">
                    <button
                        v-for="cat in categoriesList"
                        :key="cat"
                        @click="activeCategory = cat"
                        class="whitespace-nowrap px-4 py-2 rounded-full text-xs font-semibold transition-all cursor-pointer border"
                        :style="activeCategory === cat
                            ? 'background-color: #14301F; color: white; border-color: #14301F;'
                            : 'background-color: white; color: #14301F; border-color: #E8E2D6;'"
                    >{{ cat }}</button>
                </div>

                <!-- Search + Sort -->
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <div class="relative flex-1 sm:w-52">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search produce..."
                            class="w-full pl-9 pr-4 py-2 rounded-full text-xs border focus:outline-none transition-colors"
                            style="background-color: white; border-color: #E8E2D6; color: #1A1A1A;"
                            onfocus="this.style.borderColor='#14301F'" onblur="this.style.borderColor='#E8E2D6'"
                        />
                        <svg class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #5F6B63;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <select v-model="sortOrder" class="px-3 py-2 rounded-full text-xs border cursor-pointer focus:outline-none" style="background-color: white; border-color: #E8E2D6; color: #1A1A1A;">
                        <option value="popular">Popular</option>
                        <option value="price_asc">Price: Low to High</option>
                        <option value="price_desc">Price: High to Low</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- ── SECTION 5: PRODUCTS GRID ───────────────────────────────── -->
        <section id="products-grid" style="padding: 48px 0 80px;">
            <div class="max-w-[1200px] mx-auto px-6 lg:px-8">

                <!-- Empty state -->
                <div v-if="filteredProducts.length === 0" class="text-center py-24 space-y-4">
                    <div class="text-5xl">🌿</div>
                    <h3 class="font-bold text-xl" style="font-family: 'Playfair Display', serif; color: #14301F;">Hakuna bidhaa hapa bado</h3>
                    <p class="text-sm" style="color: #5F6B63;">Try another category or clear your search.</p>
                    <button @click="activeCategory = 'All'; searchQuery = '';" class="px-6 py-2.5 rounded-full text-white text-sm font-semibold cursor-pointer" style="background-color: #14301F;">Show All Produce</button>
                </div>

                <!-- Product Cards -->
                <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5 lg:gap-6">
                    <div
                        v-for="prod in visibleProducts"
                        :key="prod.id"
                        class="group flex flex-col rounded-2xl overflow-hidden cursor-pointer transition-all duration-300 hover:-translate-y-1"
                        style="background-color: white; box-shadow: 0 4px 20px rgba(20,48,31,0.08);"
                        @mouseover="$event.currentTarget.style.boxShadow = '0 12px 40px rgba(20,48,31,0.15)'"
                        @mouseleave="$event.currentTarget.style.boxShadow = '0 4px 20px rgba(20,48,31,0.08)'"
                        @click="openQuickView(prod)"
                    >
                        <!-- Image -->
                        <div class="relative overflow-hidden" style="aspect-ratio: 4/3;">
                            <img
                                loading="lazy"
                                decoding="async"
                                :src="resolveProductImage(prod)"
                                :alt="`${prod.name} — fresh from Kitonga Farm`"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                            />
                            <!-- Category badge top-left -->
                            <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider text-white shadow-sm" style="background-color: #14301F;">
                                {{ getCategoryName(prod) }}
                            </span>
                            <!-- Optional badge top-right -->
                            <span v-if="prod.badge && prod.badge !== 'Out of Stock'" class="absolute top-3 right-3 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider text-white shadow-sm" style="background-color: #D98A3D;">
                                {{ prod.badge }}
                            </span>
                            <span v-else-if="prod.badge === 'Out of Stock'" class="absolute top-3 right-3 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider text-white shadow-sm" style="background-color: #9CA3AF;">
                                Out of Stock
                            </span>
                        </div>

                        <!-- Card Body -->
                        <div class="p-4 flex flex-col flex-1 gap-3">
                            <div class="flex-1">
                                <h3 class="font-bold leading-snug line-clamp-2" style="font-family: 'Playfair Display', serif; font-size: 15px; color: #1A1A1A;">{{ prod.name }}</h3>
                                <p class="mt-1.5 line-clamp-2 text-[13px] leading-relaxed" style="color: #5F6B63;">{{ prod.description }}</p>
                            </div>

                            <!-- Divider -->
                            <div style="height: 1px; background-color: #E8E2D6;"></div>

                            <!-- Price + Add button -->
                            <div class="flex items-end justify-between gap-2" @click.stop>
                                <div>
                                    <div class="font-bold text-base" style="color: #14301F;">{{ formatCurrency(getPrice(prod)) }}</div>
                                    <div v-if="prod.unit" class="text-[11px]" style="color: #5F6B63;">per {{ prod.unit }}</div>
                                </div>

                                <!-- Stepper or Add button -->
                                <div v-if="cart[prod.id]" class="flex items-center gap-1 rounded-full border" style="background-color: #F0F7F3; border-color: rgba(20,48,31,0.15);">
                                    <button @click="decrementCart(prod.id)" class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm cursor-pointer hover:bg-white transition-colors" style="color: #14301F;">−</button>
                                    <span class="w-6 text-center text-sm font-bold" style="color: #14301F;">{{ cart[prod.id].qty }}</span>
                                    <button @click="addToCart(prod)" class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm cursor-pointer hover:bg-white transition-colors" style="color: #14301F;">+</button>
                                </div>
                                <button
                                    v-else
                                    @click="addToCart(prod)"
                                    class="flex items-center gap-1.5 px-4 py-2 rounded-full text-white text-xs font-bold transition-all cursor-pointer hover:opacity-90 hover:-translate-y-0.5"
                                    style="background-color: #14301F; height: 36px;"
                                >
                                    Add <span class="text-base leading-none">+</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Load More -->
                <div v-if="hasMore" class="text-center mt-10">
                    <button @click="loadMore" class="px-8 py-3 rounded-full border text-sm font-semibold transition-all cursor-pointer hover:-translate-y-0.5" style="border-color: #14301F; color: #14301F; background-color: white;">
                        Load More Produce
                    </button>
                </div>
            </div>
        </section>

        <!-- ── SECTION 6: WHY CHOOSE KITONGA ─────────────────────────── -->
        <section style="background-color: #14301F; padding: 72px 0;">
            <div class="max-w-[1200px] mx-auto px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="font-bold text-white mb-3" style="font-family: 'Playfair Display', serif; font-size: clamp(28px, 4vw, 40px);">Why Choose Kitonga Farm?</h2>
                    <p class="text-sm max-w-lg mx-auto" style="color: rgba(255,255,255,0.65);">Every product leaves our farm with care, quality, and authenticity at its heart.</p>
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
                    <div v-for="(item, i) in [
                        { icon: '📦', title: 'Hygienic Packaging', desc: 'Sealed for freshness and safety on every order.' },
                        { icon: '🚚', title: 'Reliable Delivery', desc: 'Direct farm-to-door delivery within the area.' },
                        { icon: '💚', title: 'Fair Prices', desc: 'No middlemen. Direct farm pricing always.' },
                        { icon: '📞', title: 'Customer Support', desc: 'WhatsApp support 7 days a week, morning to evening.' },
                    ]" :key="i" class="flex flex-col items-center text-center gap-3 p-6 rounded-2xl" style="background-color: rgba(255,255,255,0.06);">
                        <span class="text-3xl">{{ item.icon }}</span>
                        <div class="font-bold text-white text-sm" style="font-family: 'Playfair Display', serif;">{{ item.title }}</div>
                        <div class="text-xs leading-relaxed" style="color: rgba(255,255,255,0.6);">{{ item.desc }}</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── SECTION 7: HOW TO ORDER ────────────────────────────────── -->
        <section style="background-color: #FFFBF5; padding: 72px 0;">
            <div class="max-w-[1200px] mx-auto px-6 lg:px-8 text-center">
                <h2 class="font-bold mb-3" style="font-family: 'Playfair Display', serif; font-size: clamp(28px, 4vw, 40px); color: #14301F;">How to Order</h2>
                <p class="text-sm mb-12 max-w-lg mx-auto" style="color: #5F6B63;">It's simple, fast and direct from the farm to your hands.</p>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 mb-12">
                    <div v-for="(step, i) in [
                        { num: '01', title: 'Browse & Choose', desc: 'Browse our fresh produce, filter by category, and add what you want to your basket.' },
                        { num: '02', title: 'Add to Basket', desc: 'Review your basket, adjust quantities, and add delivery notes or special instructions.' },
                        { num: '03', title: 'Pay & Receive', desc: 'Send your order via WhatsApp and choose delivery or pickup. Pay via M-Pesa, Airtel Money, Tigo Pesa or Cash.' },
                    ]" :key="i" class="flex flex-col items-center gap-4">
                        <div class="w-16 h-16 rounded-full flex items-center justify-center text-white font-bold text-xl border-4" style="background-color: #14301F; border-color: #D98A3D; font-family: 'Playfair Display', serif;">{{ step.num }}</div>
                        <h3 class="font-bold text-base" style="color: #14301F; font-family: 'Playfair Display', serif;">{{ step.title }}</h3>
                        <p class="text-sm leading-relaxed max-w-xs" style="color: #5F6B63;">{{ step.desc }}</p>
                    </div>
                </div>

                <!-- Payment methods -->
                <div class="flex flex-wrap items-center justify-center gap-3">
                    <span class="text-xs font-semibold" style="color: #5F6B63;">Accepted payments:</span>
                    <span v-for="pay in ['M-Pesa', 'Airtel Money', 'Tigo Pesa', 'Cash on Delivery']" :key="pay" class="px-3 py-1.5 rounded-full text-xs font-semibold border" style="background-color: white; border-color: #E8E2D6; color: #14301F;">{{ pay }}</span>
                </div>
            </div>
        </section>

        <!-- ── SECTION 8: TESTIMONIALS (hidden — use real reviews only) → skipped per spec -->

        <!-- ── SECTION 9: CTA BANNER ──────────────────────────────────── -->
        <section class="relative overflow-hidden" style="background-color: #14301F; padding: 72px 0;">
            <!-- Background image overlay -->
            <div class="absolute inset-0">
                <img src="/images/general_farm_hero.webp" alt="Kitonga Farm" class="w-full h-full object-cover opacity-20" />
                <div class="absolute inset-0" style="background: linear-gradient(135deg, rgba(20,48,31,0.85) 0%, rgba(20,48,31,0.6) 100%);"></div>
            </div>

            <div class="relative z-10 max-w-[1200px] mx-auto px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-10 items-center">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest mb-3" style="color: #D98A3D;">Bulk Orders Welcome</p>
                        <h2 class="font-bold text-white mb-4" style="font-family: 'Playfair Display', serif; font-size: clamp(28px, 4vw, 44px); line-height: 1.2;">Fresh from our farm<br>to your table.</h2>
                        <p class="text-sm leading-relaxed" style="color: rgba(255,255,255,0.7);">Serving hotels, restaurants, schools and families across Handeni. Contact us for weekly or monthly bulk produce arrangements.</p>
                    </div>

                    <div class="rounded-2xl p-7 shadow-2xl" style="background-color: white;">
                        <h3 class="font-bold text-base mb-1" style="font-family: 'Playfair Display', serif; color: #14301F;">Ready to place a bulk order?</h3>
                        <p class="text-sm mb-4" style="color: #5F6B63;">Call or WhatsApp us directly:</p>
                        <div class="text-xl font-bold mb-5" style="color: #14301F;">+255 758 774 695</div>
                        <a :href="directWhatsapp" target="_blank" class="flex items-center justify-center gap-2 w-full py-3 rounded-full text-white text-sm font-bold transition-all hover:opacity-90" style="background-color: #25D366;">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            Chat on WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── SECTION 10: FOOTER ─────────────────────────────────────── -->
        <footer style="background-color: #14301F; color: white; padding: 64px 0 0;">
            <div class="max-w-[1200px] mx-auto px-6 lg:px-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10 pb-12">

                    <!-- Col 1: Brand -->
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-white text-xl" style="background-color: #D98A3D; font-family: 'Playfair Display', serif;">K</div>
                            <div>
                                <div class="font-bold tracking-widest text-sm" style="font-family: 'Playfair Display', serif;">KITONGA</div>
                                <div class="text-[9px] tracking-[3px] mt-0.5" style="color: #D98A3D;">FARMS VILLAS</div>
                            </div>
                        </div>
                        <p class="text-xs leading-relaxed mb-5" style="color: rgba(255,255,255,0.55);">Organic farm and luxury private villas nestled in the Komkonga highlands, Handeni, Tanzania.</p>
                        <div class="flex items-center gap-3">
                            <a href="https://instagram.com" target="_blank" class="w-8 h-8 rounded-full flex items-center justify-center transition-colors" style="background-color: rgba(255,255,255,0.1);" onmouseover="this.style.backgroundColor='#D98A3D'" onmouseout="this.style.backgroundColor='rgba(255,255,255,0.1)'">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                            </a>
                            <a href="https://facebook.com" target="_blank" class="w-8 h-8 rounded-full flex items-center justify-center transition-colors" style="background-color: rgba(255,255,255,0.1);" onmouseover="this.style.backgroundColor='#D98A3D'" onmouseout="this.style.backgroundColor='rgba(255,255,255,0.1)'">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </a>
                            <a :href="directWhatsapp" target="_blank" class="w-8 h-8 rounded-full flex items-center justify-center transition-colors" style="background-color: rgba(255,255,255,0.1);" onmouseover="this.style.backgroundColor='#25D366'" onmouseout="this.style.backgroundColor='rgba(255,255,255,0.1)'">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            </a>
                        </div>
                    </div>

                    <!-- Col 2: Quick Links -->
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-widest mb-4" style="color: #D98A3D;">Quick Links</h4>
                        <ul class="space-y-2.5 text-xs" style="color: rgba(255,255,255,0.6);">
                            <li><Link :href="route('home')" prefetch class="hover:text-white transition-colors">Home</Link></li>
                            <li><Link :href="route('villas')" prefetch class="hover:text-white transition-colors">Villas</Link></li>
                            <li><Link :href="route('experiences')" prefetch class="hover:text-white transition-colors">Experiences</Link></li>
                            <li><Link :href="route('farm')" prefetch class="hover:text-white transition-colors">Our Farm</Link></li>
                            <li><Link :href="route('gallery')" prefetch class="hover:text-white transition-colors">Gallery</Link></li>
                            <li><Link :href="route('contact')" prefetch class="hover:text-white transition-colors">Contact</Link></li>
                        </ul>
                    </div>

                    <!-- Col 3: Produce -->
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-widest mb-4" style="color: #D98A3D;">Produce</h4>
                        <ul class="space-y-2.5 text-xs" style="color: rgba(255,255,255,0.6);">
                            <li><button @click="activeCategory = 'Eggs'; scrollToProducts()" class="hover:text-white transition-colors cursor-pointer text-left">Eggs</button></li>
                            <li><button @click="activeCategory = 'Dairy'; scrollToProducts()" class="hover:text-white transition-colors cursor-pointer text-left">Dairy Products</button></li>
                            <li><button @click="activeCategory = 'Honey'; scrollToProducts()" class="hover:text-white transition-colors cursor-pointer text-left">Forest Honey</button></li>
                            <li><button @click="activeCategory = 'Fruits'; scrollToProducts()" class="hover:text-white transition-colors cursor-pointer text-left">Fresh Fruits</button></li>
                            <li><button @click="activeCategory = 'Vegetables'; scrollToProducts()" class="hover:text-white transition-colors cursor-pointer text-left">Vegetables</button></li>
                        </ul>
                    </div>

                    <!-- Col 4: Contact -->
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-widest mb-4" style="color: #D98A3D;">Contact Us</h4>
                        <ul class="space-y-3 text-xs" style="color: rgba(255,255,255,0.6);">
                            <li class="flex items-start gap-2">
                                <svg class="w-3.5 h-3.5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #D98A3D;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Komkonga, Handeni, Tanga, Tanzania
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #D98A3D;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                +255 758 774 695
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #D98A3D;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                kitongafarmvillas@gmail.com
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #D98A3D;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Mon–Sun: 6:00 AM – 6:00 PM
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Footer bottom -->
                <div class="border-t py-5 flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px]" style="border-color: rgba(255,255,255,0.1); color: rgba(255,255,255,0.4);">
                    <p>© 2026 Kitonga Farm Villas Sanctuary. All rights reserved.</p>
                    <div class="flex items-center gap-4">
                        <a href="#" class="hover:text-white transition-colors">Privacy Policy</a>
                        <a href="#" class="hover:text-white transition-colors">Terms of Service</a>
                        <a href="https://wa.me/255675315279" target="_blank" class="flex items-center gap-1.5 px-3 py-1 rounded-full border hover:border-[#D98A3D] hover:text-[#D98A3D] transition-all" style="border-color: rgba(255,255,255,0.15);">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse inline-block"></span>
                            Dev: 0675 315 279
                        </a>
                    </div>
                </div>
            </div>
        </footer>

        <!-- ── BASKET DRAWER ───────────────────────────────────────────── -->
        <Teleport to="body">
            <div v-if="cartDrawerOpen" class="fixed inset-0 z-[100] overflow-hidden" role="dialog" aria-modal="true">
                <div @click="cartDrawerOpen = false" class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity"></div>
                <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
                    <div class="w-screen max-w-sm sm:max-w-md flex flex-col shadow-2xl" style="background-color: white;">

                        <!-- Drawer header -->
                        <div class="flex items-center justify-between p-5 sm:p-6" style="background-color: #14301F; color: white;">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #D98A3D;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                <div>
                                    <h3 class="font-bold text-base" style="font-family: 'Playfair Display', serif;">Your Harvest Basket</h3>
                                    <span class="text-[11px]" style="color: #D98A3D;">Kitonga Farm Direct</span>
                                </div>
                            </div>
                            <button @click="cartDrawerOpen = false" class="w-8 h-8 flex items-center justify-center rounded-full cursor-pointer transition-colors" style="background-color: rgba(255,255,255,0.1);">✕</button>
                        </div>

                        <!-- Items -->
                        <div class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-3">
                            <div v-if="cartList.length === 0" class="py-20 text-center space-y-3">
                                <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #E8E2D6;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                <h4 class="font-bold text-sm" style="color: #14301F;">Your basket is empty</h4>
                                <p class="text-xs" style="color: #5F6B63;">Browse fresh produce above and add items to your basket.</p>
                                <button @click="cartDrawerOpen = false" class="px-5 py-2 rounded-full text-white text-xs font-semibold cursor-pointer" style="background-color: #14301F;">Browse Produce</button>
                            </div>

                            <div v-for="item in cartList" :key="item.id" class="flex items-center gap-3 p-3 rounded-xl border" style="background-color: #FFFBF5; border-color: #E8E2D6;">
                                <img loading="lazy" :src="item.image" :alt="item.name" class="w-14 h-14 rounded-lg object-cover shrink-0" />
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-xs font-bold truncate" style="color: #1A1A1A;">{{ item.name }}</h4>
                                    <div class="text-[11px] font-semibold mt-0.5" style="color: #D98A3D;">{{ formatCurrency(item.price) }} / {{ item.unit }}</div>
                                    <div class="text-xs font-bold mt-0.5" style="color: #14301F;">{{ formatCurrency(item.price * item.qty) }}</div>
                                </div>
                                <div class="flex items-center gap-1 rounded-full border" style="background-color: #F0F7F3; border-color: rgba(20,48,31,0.12);">
                                    <button @click="decrementCart(item.id)" class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold cursor-pointer hover:bg-white transition-colors" style="color: #14301F;">−</button>
                                    <span class="w-5 text-center text-xs font-bold" style="color: #14301F;">{{ item.qty }}</span>
                                    <button @click="addToCart(item)" class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold cursor-pointer hover:bg-white transition-colors" style="color: #14301F;">+</button>
                                </div>
                                <button @click="removeFromCart(item.id)" class="text-red-400 hover:text-red-600 w-6 h-6 flex items-center justify-center cursor-pointer text-sm">✕</button>
                            </div>

                            <!-- Delivery notes -->
                            <div v-if="cartList.length > 0" class="pt-2">
                                <label class="text-xs font-bold block mb-1.5" style="color: #14301F;">Delivery / Special Instructions:</label>
                                <textarea
                                    v-model="deliveryNotes"
                                    rows="2"
                                    placeholder="e.g. Deliver to Luxury Villa at 8:00 AM, or pickup at main gate..."
                                    class="w-full p-3 rounded-xl text-xs border focus:outline-none resize-none"
                                    style="background-color: #FFFBF5; border-color: #E8E2D6; color: #1A1A1A;"
                                ></textarea>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div v-if="cartList.length > 0" class="p-5 sm:p-6 border-t space-y-3" style="background-color: #FFFBF5; border-color: #E8E2D6;">
                            <div class="flex items-baseline justify-between">
                                <span class="text-xs font-semibold uppercase tracking-wider" style="color: #5F6B63;">Estimated Total</span>
                                <span class="font-bold text-2xl" style="font-family: 'Playfair Display', serif; color: #14301F;">{{ formatCurrency(totalCartPrice) }}</span>
                            </div>
                            <a :href="whatsappUrl" target="_blank" class="flex items-center justify-center gap-2 w-full py-3.5 rounded-full text-white text-sm font-bold transition-all hover:opacity-90 shadow-md" style="background-color: #25D366;">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                Send Order via WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- ── QUICK VIEW MODAL ────────────────────────────────────────── -->
        <Teleport to="body">
            <div v-if="quickViewProduct" class="fixed inset-0 z-[110] flex items-center justify-center p-4" @click.self="closeQuickView">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeQuickView"></div>
                <div class="relative w-full max-w-xl rounded-3xl overflow-hidden shadow-2xl" style="background-color: white; max-height: 90vh; overflow-y: auto;">
                    <!-- Image -->
                    <div class="relative" style="aspect-ratio: 16/9;">
                        <img loading="lazy" :src="resolveProductImage(quickViewProduct)" :alt="quickViewProduct.name" class="w-full h-full object-cover" />
                        <button @click="closeQuickView" class="absolute top-4 right-4 w-9 h-9 rounded-full flex items-center justify-center text-white shadow-md cursor-pointer" style="background-color: rgba(0,0,0,0.5);">✕</button>
                        <span class="absolute top-4 left-4 px-3 py-1.5 rounded-full text-[11px] font-bold uppercase tracking-wider text-white" style="background-color: #14301F;">{{ getCategoryName(quickViewProduct) }}</span>
                    </div>
                    <!-- Body -->
                    <div class="p-6 sm:p-8">
                        <h2 class="font-bold text-xl mb-2" style="font-family: 'Playfair Display', serif; color: #14301F;">{{ quickViewProduct.name }}</h2>
                        <p class="text-sm leading-relaxed mb-6" style="color: #5F6B63;">{{ quickViewProduct.description }}</p>

                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <div class="font-bold text-2xl" style="color: #14301F; font-family: 'Playfair Display', serif;">{{ formatCurrency(getPrice(quickViewProduct)) }}</div>
                                <div v-if="quickViewProduct.unit" class="text-xs mt-0.5" style="color: #5F6B63;">per {{ quickViewProduct.unit }}</div>
                            </div>
                            <div class="flex items-center gap-3">
                                <!-- Qty stepper -->
                                <div class="flex items-center gap-2 rounded-full border px-1" style="border-color: #E8E2D6;">
                                    <button @click="quickViewQty = Math.max(1, quickViewQty - 1)" class="w-8 h-8 rounded-full flex items-center justify-center font-bold cursor-pointer hover:bg-gray-100 transition-colors" style="color: #14301F;">−</button>
                                    <span class="w-6 text-center font-bold text-sm" style="color: #14301F;">{{ quickViewQty }}</span>
                                    <button @click="quickViewQty++" class="w-8 h-8 rounded-full flex items-center justify-center font-bold cursor-pointer hover:bg-gray-100 transition-colors" style="color: #14301F;">+</button>
                                </div>
                                <button @click="addQuickViewToCart" class="flex items-center gap-2 px-6 py-2.5 rounded-full text-white font-semibold text-sm transition-all hover:opacity-90 cursor-pointer" style="background-color: #14301F;">
                                    Add to Basket
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- ── FLOATING BASKET (mobile) ───────────────────────────────── -->
        <Transition name="slide-up">
            <div v-if="totalCartItems > 0" class="fixed bottom-6 left-4 right-4 sm:left-auto sm:right-6 sm:w-auto z-[90]">
                <button
                    @click="cartDrawerOpen = true"
                    class="w-full sm:w-auto flex items-center justify-between sm:justify-start gap-3 px-5 py-3.5 rounded-full text-white text-sm font-bold shadow-2xl cursor-pointer transition-all hover:-translate-y-0.5"
                    style="background-color: #14301F; border: 2px solid rgba(217,138,61,0.4);"
                >
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #D98A3D;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span>{{ totalCartItems }} {{ totalCartItems === 1 ? 'Item' : 'Items' }}</span>
                    </div>
                    <span>•</span>
                    <span>{{ formatCurrency(totalCartPrice) }}</span>
                </button>
            </div>
        </Transition>

        <!-- ── FLOATING WHATSAPP BUTTON ───────────────────────────────── -->
        <a
            :href="directWhatsapp"
            target="_blank"
            class="fixed z-[89] flex items-center justify-center rounded-full text-white shadow-2xl transition-all hover:scale-110"
            style="background-color: #25D366; width: 52px; height: 52px; bottom: 88px; right: 20px;"
            title="Chat on WhatsApp"
        >
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        </a>

    </div>
</template>

<style scoped>
.scrollbar-none::-webkit-scrollbar { display: none; }
.scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

/* Toast */
.toast-enter-active, .toast-leave-active { transition: all 0.3s ease; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateY(-12px); }

/* Floating basket */
.slide-up-enter-active, .slide-up-leave-active { transition: all 0.3s ease; }
.slide-up-enter-from, .slide-up-leave-to { opacity: 0; transform: translateY(20px); }
</style>
