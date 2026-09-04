<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    villa: Object,
    other_villas: {
        type: Array,
        default: () => [],
    },
    settings: {
        type: Object,
        default: () => ({}),
    },
});

// Dynamic Gallery Images per Villa (4 Distinct Photos per Villa)
const getGallery = (slug) => {
    if (slug === 'luxury-villa') {
        return [
            { category: 'Architecture & Grounds', src: '/images/villas_gallery/IMG_0063.webp', title: 'Private Residence Setting' },
            { category: 'Villa Exterior', src: '/images/villas_gallery/IMG_0064.webp', title: 'Estate Pathway & Architecture' },
            { category: 'Master Bedroom', src: '/images/villas_gallery/IMG_0092.webp', title: 'Interior Bedroom Suite' },
            { category: 'Living Space', src: '/images/villas_gallery/IMG_0094.webp', title: 'Lounge & Natural Lighting' },
        ];
    }
    if (slug === 'semi-luxury-villa') {
        return [
            { category: 'Private Veranda', src: '/images/villas_gallery/IMG_0119.webp', title: 'Outdoor Living Area' },
            { category: 'Garden Walkway', src: '/images/villas_gallery/IMG_0120.webp', title: 'Lush Garden Pathway' },
            { category: 'Veranda & Grounds', src: '/images/villas_gallery/IMG_0132.webp', title: 'Private Outdoor Retreat' },
            { category: 'Sunset & Farm View', src: '/images/villas_gallery/IMG_0133.webp', title: 'Panoramic Countryside View' },
        ];
    }
    // family-villa default
    return [
        { category: 'Family Residence', src: '/images/villas_gallery/IMG_0018.webp', title: 'Spacious Family Setting' },
        { category: 'Estate Grounds', src: '/images/villas_gallery/IMG_0034.webp', title: 'Family Villa Environment' },
        { category: 'Courtyard & Terrace', src: '/images/villas_gallery/IMG_0130.webp', title: 'Private Outdoor Terrace' },
        { category: 'Estate Gardens', src: '/images/villas_gallery/IMG_0131.webp', title: 'Lush Tropical Landscapes' },
    ];
};

const galleryImages = computed(() => getGallery(props.villa.slug));

// Lightbox state
const activeLightboxIndex = ref(null);

const openLightbox = (idx) => {
    activeLightboxIndex.value = idx;
};

const closeLightbox = () => {
    activeLightboxIndex.value = null;
};

const nextLightbox = () => {
    if (activeLightboxIndex.value !== null) {
        activeLightboxIndex.value = (activeLightboxIndex.value + 1) % galleryImages.value.length;
    }
};

const prevLightbox = () => {
    if (activeLightboxIndex.value !== null) {
        activeLightboxIndex.value = (activeLightboxIndex.value - 1 + galleryImages.value.length) % galleryImages.value.length;
    }
};

// Booking State & Date Calculation
const today = new Date().toISOString().split('T')[0];
const tomorrowDate = new Date(Date.now() + 86400000).toISOString().split('T')[0];

const checkIn = ref(today);
const checkOut = ref(tomorrowDate);
const guestsCount = ref(Math.min(2, props.villa.capacity || 2));

const numberOfNights = computed(() => {
    if (!checkIn.value || !checkOut.value) return 1;
    const start = new Date(checkIn.value);
    const end = new Date(checkOut.value);
    const diffTime = end - start;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays > 0 ? diffDays : 1;
});

const estimatedTotal = computed(() => {
    return (props.villa.base_price || 0) * numberOfNights.value;
});

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-TZ', { style: 'currency', currency: 'TZS', maximumFractionDigits: 0 }).format(val);
};

const getImageUrl = (path, slug) => {
    if (slug === 'luxury-villa') return '/images/luxury_villa_img.webp';
    if (slug === 'semi-luxury-villa') return '/images/semi_luxury_villa_img.webp';
    if (slug === 'family-villa') return '/images/family_villa_img.webp';
    if (!path) return '/images/luxury_villa_img.webp';
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    return `/images/${path}`;
};
</script>

<template>
    <Head :title="`${villa.name} â€” Private Residence | Kitonga Farm Villas`" />

    <div class="bg-[#FAF8F5] text-[#1F2420] font-sans min-h-screen selection:bg-[#C98A3E] selection:text-white">
        
        <!-- STICKY TOP NAVBAR -->
        <header class="sticky top-0 z-50 bg-[#14231C]/95 backdrop-blur-md px-6 py-4 md:px-12 flex justify-between items-center text-white border-b border-white/10 shadow-md transition duration-300">
            <Link :href="route('home')" class="flex flex-col items-start group">
                <span class="font-serif text-lg md:text-2xl font-light text-[#F5F1E8] tracking-[4px] uppercase leading-none transition group-hover:text-[#C98A3E] duration-300">
                    KITONGA
                </span>
                <span class="font-sans text-[8px] md:text-[9px] font-medium text-[#C98A3E] tracking-[6px] uppercase leading-none mt-1 pl-[2px] transition group-hover:text-[#F5F1E8] duration-300">
                    FARMS VILLAS
                </span>
            </Link>
            <nav class="hidden md:flex space-x-7 lg:space-x-8 text-xs font-semibold uppercase tracking-widest text-gray-200 items-center font-sans">
                <Link :href="route('home')" prefetch class="hover:text-[#C98A3E] transition">Home</Link>
                <Link :href="route('villas')" prefetch class="text-[#E6C387] font-bold border-b-2 border-[#E6C387] pb-0.5">Villas</Link>
                <Link :href="route('experiences')" prefetch class="hover:text-[#C98A3E] transition">Experiences</Link>
                <Link :href="route('farm')" prefetch class="hover:text-[#C98A3E] transition">Our Farm</Link>
                <Link :href="route('products')" prefetch class="hover:text-[#C98A3E] transition">Produce</Link>
                <Link :href="route('contact')" prefetch class="hover:text-[#C98A3E] transition">Contact</Link>
                <Link :href="route('login')" prefetch class="hover:text-[#C98A3E] transition">Sign In</Link>
            </nav>
            <Link :href="route('booking.form')" prefetch class="px-5 py-2.5 bg-white text-gray-900 hover:bg-[#FAF8F5] text-xs font-extrabold uppercase tracking-wider rounded-lg transition font-sans shadow-xs">
                BOOK STAY
            </Link>
        </header>

        <!-- 1. FULL-WIDTH CINEMATIC HERO -->
        <section class="w-full h-[65vh] md:h-[75vh] min-h-[500px] bg-[#14231C] overflow-hidden relative select-none">
            <img loading="lazy" decoding="async" 
                :src="getImageUrl(villa.featured_image, villa.slug)" 
                class="w-full h-full object-cover object-center transform scale-102" 
                :alt="villa.name" 
            />
            <!-- Subtle Bottom Gradient Only for Title Legibility (No dark curtain over photo) -->
            <div class="absolute bottom-0 left-0 w-full h-36 bg-gradient-to-t from-black/60 to-transparent pointer-events-none"></div>
            
            <div class="absolute bottom-8 sm:bottom-12 left-0 w-full px-6 sm:px-12 max-w-7xl mx-auto flex flex-col items-start space-y-3 font-sans">
                <Link 
                    :href="route('villas')" 
                    prefetch 
                    class="text-xs uppercase tracking-[3px] font-bold text-[#E6C387] hover:text-white transition flex items-center gap-1.5"
                >
                    <span>â†</span> <span>All Residences</span>
                </Link>
                <h1 class="text-3xl sm:text-5xl md:text-6xl font-serif font-light text-[#F7F3EA] tracking-tight">
                    {{ villa.name }}
                </h1>
                <div class="text-xs sm:text-sm text-gray-300 font-medium tracking-wide">
                    <span>{{ villa.capacity }} Guests</span>
                    <span class="mx-2 text-[#C98A3E]">Â·</span>
                    <span>{{ villa.bedrooms }} {{ villa.bedrooms === 1 ? 'Bedroom' : 'Bedrooms' }}</span>
                    <span class="mx-2 text-[#C98A3E]">Â·</span>
                    <span>{{ villa.bathrooms }} {{ villa.bathrooms === 1 ? 'Bathroom' : 'Bathrooms' }}</span>
                </div>
            </div>
        </section>

        <!-- 2. MAIN RESIDENCE CONTENT & STICKY BOOKING CARD -->
        <main class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-12 py-16 sm:py-20 grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-start">
            
            <!-- LEFT COLUMN (7 COLS): STORY, SPECS, THE EXPERIENCE, GALLERY, AMENITIES -->
            <div class="lg:col-span-7 space-y-16">
                
                <!-- Intro Statement -->
                <section class="space-y-4 font-sans">
                    <span class="text-[10px] uppercase tracking-[4px] font-bold text-[#C98A3E] block">
                        â€” RESIDENCE ESSENCE â€”
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-serif font-light text-[#1F2420]">
                        Quiet mornings. Private space. The beauty of Kitonga.
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-600 leading-relaxed whitespace-pre-line">
                        {{ villa.description || 'Designed as an autonomous private retreat, each residence offers uninterrupted privacy, generous natural light, and panoramic views of our surrounding organic farm grounds.' }}
                    </p>
                </section>

                <!-- Villa Specifications Inline Cards -->
                <section class="space-y-4">
                    <h3 class="font-serif text-xl font-light text-gray-900 border-b border-[#E5E0D8] pb-2">
                        Specifications & Layout
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 font-sans text-xs">
                        <div class="bg-white p-4 rounded-xl border border-[#E5E0D8] space-y-1">
                            <span class="text-[10px] uppercase font-bold text-gray-400 block">Capacity</span>
                            <span class="text-sm font-bold text-[#1B2E22]">{{ villa.capacity }} Guests</span>
                        </div>
                        <div class="bg-white p-4 rounded-xl border border-[#E5E0D8] space-y-1">
                            <span class="text-[10px] uppercase font-bold text-gray-400 block">Bedrooms</span>
                            <span class="text-sm font-bold text-[#1B2E22]">{{ villa.bedrooms }} {{ villa.bedrooms === 1 ? 'Room' : 'Rooms' }}</span>
                        </div>
                        <div class="bg-white p-4 rounded-xl border border-[#E5E0D8] space-y-1">
                            <span class="text-[10px] uppercase font-bold text-gray-400 block">Bathrooms</span>
                            <span class="text-sm font-bold text-[#1B2E22]">{{ villa.bathrooms }} Private</span>
                        </div>
                        <div class="bg-white p-4 rounded-xl border border-[#E5E0D8] space-y-1">
                            <span class="text-[10px] uppercase font-bold text-gray-400 block">Kitchen</span>
                            <span class="text-sm font-bold text-[#1B2E22]">{{ villa.has_interior_kitchen ? 'Yes' : 'No' }}</span>
                        </div>
                    </div>
                </section>

                <!-- "THE EXPERIENCE" SECTION -->
                <section class="space-y-4 bg-white p-8 rounded-2xl border border-[#E5E0D8] shadow-xs font-sans">
                    <span class="text-[10px] uppercase tracking-[4px] font-bold text-[#C98A3E] block">
                        â€” THE EXPERIENCE â€”
                    </span>
                    <h3 class="text-2xl font-serif font-light text-[#1B2E22]">
                        Slow Living & Countryside Serenity
                    </h3>
                    <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                        Wake up to the soft morning chorus of countryside birds, sip freshly harvested Kitonga coffee on your private terrace, and enjoy total seclusion away from urban noise. From sunrise farm walks to evening relaxation under starlit skies, your stay is centered entirely around peaceful rejuvenation.
                    </p>
                </section>

                <!-- CATEGORIZED EDITORIAL GALLERY -->
                <section class="space-y-6">
                    <div class="flex justify-between items-baseline border-b border-[#E5E0D8] pb-2">
                        <h3 class="font-serif text-xl font-light text-gray-900">
                            Residence Gallery
                        </h3>
                        <span class="text-[10px] uppercase tracking-widest text-gray-400 font-sans">
                            Click to view full photo
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div 
                            v-for="(img, idx) in galleryImages" 
                            :key="idx"
                            @click="openLightbox(idx)"
                            class="relative aspect-[4/3] rounded-2xl overflow-hidden shadow-sm border border-[#E5E0D8] cursor-pointer group bg-gray-100"
                        >
                            <img loading="lazy" decoding="async" 
                                :src="img.src" 
                                :alt="img.title" 
                                class="w-full h-full object-cover object-center transform group-hover:scale-105 transition duration-500" 
                            />
                            <div class="absolute inset-0 bg-black/20 group-hover:bg-black/10 transition duration-300"></div>
                            <div class="absolute bottom-3 left-3 text-white font-sans text-xs">
                                <span class="text-[9px] uppercase tracking-wider text-[#E6C387] font-bold block">{{ img.category }}</span>
                                <span class="font-medium text-xs">{{ img.title }}</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- INCLUDED AMENITIES -->
                <section v-if="villa.amenities && villa.amenities.length > 0" class="space-y-4">
                    <h3 class="font-serif text-xl font-light text-gray-900 border-b border-[#E5E0D8] pb-2">
                        Included Amenities
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 font-sans text-xs text-gray-700">
                        <div 
                            v-for="amenity in villa.amenities" 
                            :key="amenity.id" 
                            class="p-3 bg-white border border-[#E5E0D8] rounded-xl flex items-center gap-2"
                        >
                            <span class="text-emerald-700 font-bold">âœ“</span>
                            <span>{{ amenity.name }}</span>
                        </div>
                    </div>
                </section>

            </div>

            <!-- RIGHT COLUMN (5 COLS): STICKY EDITORIAL BOOKING CARD -->
            <div class="lg:col-span-5 lg:sticky lg:top-8 space-y-6">
                
                <div class="bg-white border border-[#E5E0D8] rounded-3xl p-6 sm:p-8 shadow-xl space-y-6 font-sans">
                    
                    <!-- Rate Summary -->
                    <div class="border-b border-[#E5E0D8] pb-4">
                        <span class="text-[10px] uppercase tracking-[3px] font-bold text-[#C98A3E] block">YOUR STAY</span>
                        <div class="flex items-baseline gap-1.5 mt-1">
                            <span class="text-3xl font-extrabold text-[#1B2E22]">{{ formatCurrency(villa.base_price) }}</span>
                            <span class="text-xs text-gray-400 font-normal">/ night</span>
                        </div>
                    </div>

                    <!-- Stay Rules -->
                    <div class="grid grid-cols-2 gap-2 text-xs text-gray-600 bg-[#FAF8F5] p-3.5 rounded-xl border border-[#E5E0D8]">
                        <div>
                            <span class="text-[9px] uppercase font-bold text-gray-400 block">Check-in</span>
                            <span class="font-semibold text-gray-900">{{ settings.check_in_time || '14:00' }}</span>
                        </div>
                        <div>
                            <span class="text-[9px] uppercase font-bold text-gray-400 block">Check-out</span>
                            <span class="font-semibold text-gray-900">{{ settings.check_out_time || '11:00' }}</span>
                        </div>
                        <div class="col-span-2 pt-1 border-t border-gray-200 flex justify-between">
                            <span class="text-gray-400">Min. Stay:</span>
                            <span class="font-semibold text-gray-900">{{ villa.minimum_stay || 1 }} Night(s)</span>
                        </div>
                    </div>

                    <!-- Date & Guest Selector -->
                    <div class="space-y-3 text-xs">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="font-bold text-gray-600 uppercase text-[10px] block mb-1">Check-in</label>
                                <input 
                                    type="date" 
                                    v-model="checkIn" 
                                    :min="today"
                                    class="w-full text-xs rounded-lg border-gray-300 focus:border-[#1B2E22] focus:ring-1 focus:ring-[#1B2E22] py-2 px-2.5 bg-white" 
                                />
                            </div>
                            <div>
                                <label class="font-bold text-gray-600 uppercase text-[10px] block mb-1">Check-out</label>
                                <input 
                                    type="date" 
                                    v-model="checkOut" 
                                    :min="checkIn"
                                    class="w-full text-xs rounded-lg border-gray-300 focus:border-[#1B2E22] focus:ring-1 focus:ring-[#1B2E22] py-2 px-2.5 bg-white" 
                                />
                            </div>
                        </div>

                        <div>
                            <label class="font-bold text-gray-600 uppercase text-[10px] block mb-1">Guests</label>
                            <select 
                                v-model="guestsCount" 
                                class="w-full text-xs rounded-lg border-gray-300 focus:border-[#1B2E22] focus:ring-1 focus:ring-[#1B2E22] py-2 px-3 bg-white"
                            >
                                <option v-for="n in (villa.capacity || 2)" :key="n" :value="n">
                                    {{ n }} {{ n === 1 ? 'Guest' : 'Guests' }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Dynamic Subtotal Calculation -->
                    <div class="border-t border-[#E5E0D8] pt-4 space-y-1.5 text-xs text-gray-600">
                        <div class="flex justify-between">
                            <span>{{ formatCurrency(villa.base_price) }} Ã— {{ numberOfNights }} {{ numberOfNights === 1 ? 'night' : 'nights' }}</span>
                            <span class="font-bold text-gray-900">{{ formatCurrency(estimatedTotal) }}</span>
                        </div>
                        <div class="flex justify-between items-baseline pt-2 border-t border-gray-150">
                            <span class="font-bold uppercase tracking-wider text-[#1B2E22]">Total Stay Estimate:</span>
                            <span class="text-xl font-bold text-[#1B2E22]">{{ formatCurrency(estimatedTotal) }}</span>
                        </div>
                    </div>

                    <!-- Primary Booking CTA -->
                    <Link 
                        :href="route('booking.form', { villa_id: villa.id, check_in: checkIn, check_out: checkOut, guests_count: guestsCount })" 
                        prefetch
                        class="w-full py-4 bg-[#1B2E22] hover:bg-[#C98A3E] text-[#F7F3EA] font-bold uppercase tracking-wider text-xs rounded-xl transition font-sans flex items-center justify-center gap-2 cursor-pointer shadow-lg group"
                    >
                        <span>Reserve this Residence</span>
                        <span class="text-[#C98A3E] group-hover:text-white group-hover:translate-x-1 transition">â†’</span>
                    </Link>

                    <p class="text-[10px] text-gray-400 text-center leading-tight">
                        No instant card charges. Your reservation request will be finalized via WhatsApp.
                    </p>

                </div>

            </div>

        </main>

        <!-- 3. "EXPLORE ANOTHER RESIDENCE" (2 ALTERNATIVES) -->
        <section v-if="other_villas && other_villas.length > 0" class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-12 py-16 border-t border-[#E5E0D8]">
            <div class="text-center space-y-2 mb-10">
                <span class="text-[10px] uppercase tracking-[4px] font-sans font-bold text-[#C98A3E] block">
                    â€” ALTERNATIVE RETREATS â€”
                </span>
                <h3 class="text-2xl sm:text-3xl font-serif font-light text-[#1F2420]">
                    Explore Another Residence
                </h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                <div 
                    v-for="alt in other_villas" 
                    :key="alt.id" 
                    class="bg-white border border-[#E5E0D8] rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition p-5 space-y-4 group"
                >
                    <div class="aspect-[16/10] rounded-xl overflow-hidden bg-gray-100 relative">
                        <img loading="lazy" decoding="async" 
                            :src="getImageUrl(alt.featured_image, alt.slug)" 
                            :alt="alt.name" 
                            class="w-full h-full object-cover group-hover:scale-105 transition duration-500" 
                        />
                    </div>
                    <div class="space-y-2 font-sans">
                        <div class="flex justify-between items-baseline">
                            <h4 class="font-serif font-light text-xl text-gray-900">{{ alt.name }}</h4>
                            <span class="text-xs font-bold text-[#1B2E22]">{{ formatCurrency(alt.base_price) }}/night</span>
                        </div>
                        <p class="text-xs text-gray-500 line-clamp-2">{{ alt.description }}</p>
                        <div class="pt-2">
                            <Link 
                                :href="route('villas.show', alt.slug)" 
                                prefetch 
                                class="text-xs font-bold uppercase tracking-wider text-[#C98A3E] hover:text-[#1B2E22] transition inline-flex items-center gap-1"
                            >
                                <span>View Residence</span>
                                <span>â†’</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- LIGHTBOX MODAL -->
        <teleport to="body">
            <div 
                v-if="activeLightboxIndex !== null" 
                class="fixed inset-0 z-100 bg-black/90 backdrop-blur-md flex items-center justify-center p-4 select-none"
                @click.self="closeLightbox"
                @keydown.esc="closeLightbox"
            >
                <!-- Close Button -->
                <button 
                    @click="closeLightbox" 
                    class="absolute top-6 right-6 text-white text-3xl font-light hover:text-[#C98A3E] transition cursor-pointer z-110 w-10 h-10 flex items-center justify-center"
                    aria-label="Close Lightbox"
                >
                    âœ•
                </button>

                <!-- Prev Button -->
                <button 
                    @click="prevLightbox" 
                    class="absolute left-4 top-1/2 -translate-y-1/2 text-white text-3xl p-3 hover:text-[#C98A3E] transition cursor-pointer z-110 bg-black/40 rounded-full"
                    aria-label="Previous Photo"
                >
                    â€¹
                </button>

                <!-- Next Button -->
                <button 
                    @click="nextLightbox" 
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-white text-3xl p-3 hover:text-[#C98A3E] transition cursor-pointer z-110 bg-black/40 rounded-full"
                    aria-label="Next Photo"
                >
                    â€º
                </button>

                <!-- Current Image & Caption -->
                <div class="max-w-5xl max-h-[85vh] flex flex-col items-center justify-center space-y-3">
                    <img loading="lazy" decoding="async" 
                        :src="galleryImages[activeLightboxIndex].src" 
                        :alt="galleryImages[activeLightboxIndex].title" 
                        class="max-h-[75vh] max-w-full object-contain rounded-xl shadow-2xl" 
                    />
                    <div class="text-center font-sans text-xs text-white">
                        <span class="text-[#E6C387] font-bold uppercase tracking-wider block text-[10px]">
                            {{ galleryImages[activeLightboxIndex].category }} ({{ activeLightboxIndex + 1 }}/{{ galleryImages.length }})
                        </span>
                        <span class="text-sm font-medium">{{ galleryImages[activeLightboxIndex].title }}</span>
                    </div>
                </div>
            </div>
        </teleport>

        <!-- FOOTER -->
        <footer class="bg-[#14231C] text-[#F7F3EA]/80 py-12 px-6 md:px-12 border-t border-white/10 text-center font-sans text-xs">
            <p class="font-serif font-semibold text-white text-sm mb-2 uppercase tracking-widest">KITONGA FARM VILLAS</p>
            <p class="text-[#F7F3EA]/60 mb-4">Komkonga Village, Tanga Region, Tanzania</p>
            <p class="text-[10px] text-[#F7F3EA]/40">© 2026 Kitonga Farm Villas. All rights reserved.</p>
        </footer>

    </div>
</template>


