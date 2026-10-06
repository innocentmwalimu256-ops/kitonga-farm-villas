<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import PublicNavbar from '@/Components/PublicNavbar.vue';

const props = defineProps({
    villas: Array,
    experiences: Array,
    products: Array,
    cms: Object,
    media: Array,
    hero_video_url: String,
    hero_video_mime: String,
    settings: Object,
});

const isMobileMenuOpen = ref(false);
const isMuted = ref(true);
const isPaused = ref(false);
const videoPlayer = ref(null);

onMounted(() => {
    if (videoPlayer.value) {
        const vid = videoPlayer.value;
        vid.muted = true;
        vid.defaultMuted = true;
        vid.playsInline = true;
        isMuted.value = true;

        const startPlayback = () => {
            if (vid) {
                vid.muted = true;
                const p = vid.play();
                if (p !== undefined) {
                    p.then(() => {
                        isPaused.value = false;
                    }).catch(() => {
                        // Handled on first user interaction
                    });
                }
            }
        };

        startPlayback();

        // Fallback on first user interaction if browser autoplay policy was restricted
        const triggerPlay = () => {
            if (vid && vid.paused) {
                startPlayback();
            }
            window.removeEventListener('click', triggerPlay);
            window.removeEventListener('touchstart', triggerPlay);
            window.removeEventListener('scroll', triggerPlay);
        };
        window.addEventListener('click', triggerPlay, { passive: true, once: true });
        window.addEventListener('touchstart', triggerPlay, { passive: true, once: true });
        window.addEventListener('scroll', triggerPlay, { passive: true, once: true });
    }
});

const toggleMute = () => {
    if (videoPlayer.value) {
        videoPlayer.value.muted = !videoPlayer.value.muted;
        isMuted.value = videoPlayer.value.muted;
    }
};

const togglePlay = () => {
    if (videoPlayer.value) {
        if (videoPlayer.value.paused) {
            videoPlayer.value.play().then(() => {
                isPaused.value = false;
            });
        } else {
            videoPlayer.value.pause();
            isPaused.value = true;
        }
    }
};

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-TZ', { style: 'currency', currency: 'TZS', maximumFractionDigits: 0 }).format(val);
};

const getImageUrl = (path, fallback = 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=800&q=80') => {
    if (!path) return fallback;
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    return `/images/${path}`;
};

import SEOHead from '@/Components/SEOHead.vue';

const homeSchema = {
    '@context': 'https://schema.org',
    '@graph': [
        {
            '@type': 'Resort',
            '@id': 'https://kitongafarm.com/#resort',
            'name': 'Kitonga Farm Villas Sanctuary',
            'url': 'https://kitongafarm.com',
            'logo': 'https://kitongafarm.com/images/logo_gold.webp',
            'image': 'https://kitongafarm.com/images/hero_villa_render.webp',
            'description': 'Luxury private villas nestled within a 150-acre organic agricultural sanctuary in Komkonga, Handeni, Tanga, Tanzania.',
            'telephone': '+255758774695',
            'priceRange': 'TZS 295,000 - 590,000',
            'address': {
                '@type': 'PostalAddress',
                'streetAddress': 'Komkonga Village, Handeni District',
                'addressLocality': 'Handeni',
                'addressRegion': 'Tanga',
                'addressCountry': 'TZ'
            },
            'geo': {
                '@type': 'GeoCoordinates',
                'latitude': -5.0889,
                'longitude': 39.0988
            },
            'checkinTime': '14:00',
            'checkoutTime': '11:00',
            'amenityFeature': [
                { '@type': 'LocationFeatureSpecification', 'name': 'Private Plunge Pool', 'value': true },
                { '@type': 'LocationFeatureSpecification', 'name': 'Organic Farm-to-Table Dining', 'value': true },
                { '@type': 'LocationFeatureSpecification', 'name': 'High-Speed Wi-Fi', 'value': true },
                { '@type': 'LocationFeatureSpecification', 'name': 'Agro-Tourism & Farm Tours', 'value': true },
                { '@type': 'LocationFeatureSpecification', 'name': 'Mountain Scenic Verandas', 'value': true }
            ]
        },
        {
            '@type': 'WebSite',
            '@id': 'https://kitongafarm.com/#website',
            'url': 'https://kitongafarm.com',
            'name': 'Kitonga Farm Villas Sanctuary',
            'publisher': { '@id': 'https://kitongafarm.com/#resort' }
        }
    ]
};

const handleLogoClick = (e) => {
    if (window.location.pathname === '/') {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};
</script>

<template>
    <SEOHead
        :title="cms?.seo_title || 'Where Luxury Meets Farm Life | Luxury Private Villas in Tanga'"
        :description="cms?.seo_description || 'Escape to Kitonga Farm Villas Sanctuary. 150 acres of organic countryside, luxury private pool villas, pasture dairy, and immersive agro-tourism in Handeni, Tanga, Tanzania.'"
        canonical-url="/"
        og-image="/images/hero_villa_render.webp"
        og-image-alt="Kitonga Farm Villas Sanctuary Estate"
        :schema="homeSchema"
    />

    <div class="bg-[#FAF8F5] text-[#2C3E2B] font-serif min-h-screen">
        
        <!-- 1. UNIVERSAL LUXURY NAVBAR & MOBILE DRAWER -->
        <PublicNavbar current-page="home" />

        <!-- 1. CINEMATIC HERO VIDEO SECTION (CLEAN FULL-VIEW VIDEO) -->
        <section 
            class="relative min-h-[75vh] md:min-h-[85vh] lg:min-h-[90vh] w-full overflow-hidden bg-[#0A120E] flex items-center justify-center select-none group"
        >
            <!-- Hero Video (Direct Zero-Latency High-Quality Static Video) -->
            <video 
                ref="videoPlayer"
                :key="hero_video_url || 'default-hero-video'"
                poster="/images/hero_poster.webp"
                class="absolute inset-0 w-full h-full object-cover object-center pointer-events-none"
                autoplay 
                loop 
                muted
                :muted="true"
                playsinline
                webkit-playsinline="true"
                disablePictureInPicture
                disableRemotePlayback
                preload="auto"
            >
                <!-- Priority 1: Hero video published from the Admin Media Studio -->
                <source v-if="hero_video_url" :src="hero_video_url" :type="hero_video_mime || 'video/mp4'">
                <!-- Priority 2: Bundled Kitonga Farm video (fallback only) -->
                <source src="/videos/IMG_2249.mp4" type="video/mp4">
                <source src="/videos/hero_cinematic.webm" type="video/webm">
            </video>

            <!-- Subtle Gradient for Top Navbar Contrast (Maintains full video brightness and crisp clarity) -->
            <div class="absolute inset-0 bg-gradient-to-b from-black/35 via-transparent to-black/20 pointer-events-none"></div>

            <!-- Floating Minimal Glass Video Controls (Icons Only) -->
            <div class="absolute bottom-6 right-6 z-20 flex items-center gap-2.5 font-sans" @click.stop>
                <!-- Play / Pause Button -->
                <button 
                    type="button"
                    @click="togglePlay" 
                    class="w-11 h-11 rounded-full bg-black/50 hover:bg-[#14231C] active:scale-95 border border-white/20 text-white backdrop-blur-md transition-all flex items-center justify-center shadow-lg hover:shadow-xl hover:border-[#C98A3E] cursor-pointer group/btn"
                    :title="isPaused ? 'Play Video' : 'Pause Video'"
                    aria-label="Play or Pause Video"
                >
                    <!-- Play Icon (when paused) -->
                    <svg v-if="isPaused" class="w-5 h-5 text-white group-hover/btn:text-[#E6C387] transition-colors translate-x-0.5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M8 5v14l11-7z"/>
                    </svg>
                    <!-- Pause Icon (when playing) -->
                    <svg v-else class="w-5 h-5 text-white group-hover/btn:text-[#E6C387] transition-colors" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                    </svg>
                </button>

                <!-- Sound Mute / Unmute Button -->
                <button 
                    type="button"
                    @click="toggleMute" 
                    class="w-11 h-11 rounded-full bg-black/50 hover:bg-[#14231C] active:scale-95 border border-white/20 text-white backdrop-blur-md transition-all flex items-center justify-center shadow-lg hover:shadow-xl hover:border-[#C98A3E] cursor-pointer group/btn"
                    :title="isMuted ? 'Unmute Audio' : 'Mute Audio'"
                    aria-label="Mute or Unmute Audio"
                >
                    <!-- Muted Icon -->
                    <svg v-if="isMuted" class="w-5 h-5 text-white group-hover/btn:text-[#E6C387] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
                    </svg>
                    <!-- Sound High / Unmuted Icon -->
                    <svg v-else class="w-5 h-5 text-white group-hover/btn:text-[#E6C387] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                    </svg>
                </button>
            </div>
        </section>

        <!-- 2. SHORT BRAND STORY -->
        <section class="content-auto py-20 px-6 md:px-12 max-w-5xl mx-auto text-center space-y-6">
            <span class="text-xs text-emerald-800 uppercase tracking-widest font-sans font-bold">The Philosophy</span>
            <h2 class="text-3xl md:text-4xl font-extrabold leading-tight">Serenity, Privacy, and Local Heritage</h2>
            <p class="text-base text-[#4A5D49] leading-relaxed font-sans max-w-3xl mx-auto">
                Kitonga Farm Villas is not just an accommodation; it is a premium countryside destination in Komkonga, Tanga. We connect luxury villa hospitality with organic farming — offering fresh fruits, swimming pool, forest trails, and cattle farm experiences. Relish farm-to-table cuisine prepared with care by our kitchen.
            </p>
        </section>

        <!-- 2.1. THE 4 PILLARS OF KITONGA (WITH ELEGANT MICRO-ANIMATIONS) -->
        <section class="content-auto py-16 px-6 md:px-12 max-w-6xl mx-auto border-t border-b border-gray-200/70">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8">
                
                <!-- 01 / Sanctuary -->
                <div class="group relative space-y-3 p-7 bg-white rounded-2xl shadow-xs border border-gray-200/80 hover:border-[#C98A3E]/70 hover:shadow-2xl hover:-translate-y-2.5 transition-all duration-500 ease-out overflow-hidden cursor-default">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#C98A3E] via-[#E6C387] to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="absolute -bottom-10 -right-10 w-28 h-28 bg-[#C98A3E]/10 rounded-full blur-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                    
                    <span class="font-mono text-xs font-bold text-[#C98A3E] tracking-widest uppercase block transition-all duration-300 group-hover:translate-x-1">01 / Sanctuary</span>
                    <h3 class="font-serif text-xl font-bold text-[#14231C] group-hover:text-[#C98A3E] transition-colors duration-300">Secluded Luxury</h3>
                    <p class="text-xs text-[#4A5D49] leading-relaxed font-sans transition-colors duration-300 group-hover:text-[#2C3E2B]">
                        Privately situated villas with panoramic country views, spacious bedrooms, and peaceful private verandas.
                    </p>
                </div>

                <!-- 02 / Agriculture -->
                <div class="group relative space-y-3 p-7 bg-white rounded-2xl shadow-xs border border-gray-200/80 hover:border-[#C98A3E]/70 hover:shadow-2xl hover:-translate-y-2.5 transition-all duration-500 ease-out overflow-hidden cursor-default">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#C98A3E] via-[#E6C387] to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="absolute -bottom-10 -right-10 w-28 h-28 bg-[#C98A3E]/10 rounded-full blur-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                    
                    <span class="font-mono text-xs font-bold text-[#C98A3E] tracking-widest uppercase block transition-all duration-300 group-hover:translate-x-1">02 / Agriculture</span>
                    <h3 class="font-serif text-xl font-bold text-[#14231C] group-hover:text-[#C98A3E] transition-colors duration-300">Organic Farmland</h3>
                    <p class="text-xs text-[#4A5D49] leading-relaxed font-sans transition-colors duration-300 group-hover:text-[#2C3E2B]">
                        Flourishing dairy cattle, free-range poultry, wild forest apiary, and sweet fruit canopies grown naturally.
                    </p>
                </div>

                <!-- 03 / Dining -->
                <div class="group relative space-y-3 p-7 bg-white rounded-2xl shadow-xs border border-gray-200/80 hover:border-[#C98A3E]/70 hover:shadow-2xl hover:-translate-y-2.5 transition-all duration-500 ease-out overflow-hidden cursor-default">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#C98A3E] via-[#E6C387] to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="absolute -bottom-10 -right-10 w-28 h-28 bg-[#C98A3E]/10 rounded-full blur-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                    
                    <span class="font-mono text-xs font-bold text-[#C98A3E] tracking-widest uppercase block transition-all duration-300 group-hover:translate-x-1">03 / Dining</span>
                    <h3 class="font-serif text-xl font-bold text-[#14231C] group-hover:text-[#C98A3E] transition-colors duration-300">Farm-to-Table</h3>
                    <p class="text-xs text-[#4A5D49] leading-relaxed font-sans transition-colors duration-300 group-hover:text-[#2C3E2B]">
                        Fresh morning eggs, warm pasture milk, wild honey, and sweet orchard fruits served daily with unhurried care.
                    </p>
                </div>

                <!-- 04 / Nature -->
                <div class="group relative space-y-3 p-7 bg-white rounded-2xl shadow-xs border border-gray-200/80 hover:border-[#C98A3E]/70 hover:shadow-2xl hover:-translate-y-2.5 transition-all duration-500 ease-out overflow-hidden cursor-default">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#C98A3E] via-[#E6C387] to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="absolute -bottom-10 -right-10 w-28 h-28 bg-[#C98A3E]/10 rounded-full blur-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                    
                    <span class="font-mono text-xs font-bold text-[#C98A3E] tracking-widest uppercase block transition-all duration-300 group-hover:translate-x-1">04 / Nature</span>
                    <h3 class="font-serif text-xl font-bold text-[#14231C] group-hover:text-[#C98A3E] transition-colors duration-300">Unhurried Peace</h3>
                    <p class="text-xs text-[#4A5D49] leading-relaxed font-sans transition-colors duration-300 group-hover:text-[#2C3E2B]">
                        Crisp mountain air, swimming pool, birdsong, guided walking trails, and crackling evening firepits under starlit skies.
                    </p>
                </div>

            </div>
        </section>

        <!-- 2.2. THE DAILY RHYTHM AT KITONGA (PURE TEXT NARRATIVE — NO NEW IMAGES) -->
        <section class="content-auto py-20 px-6 md:px-12 max-w-5xl mx-auto space-y-12">
            <div class="text-center space-y-3">
                <span class="text-xs text-emerald-800 uppercase tracking-widest font-sans font-bold">The Experience</span>
                <h2 class="text-3xl font-extrabold">A Day in the Life at Kitonga</h2>
                <p class="text-sm text-[#4A5D49] font-sans max-w-xl mx-auto">
                    Experience time slowing down as you reconnect with nature, comfort, and heritage.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
                
                <!-- 01. Dawn & Morning -->
                <div class="group relative p-6 bg-white/60 hover:bg-white rounded-r-2xl border-l-4 border-[#C98A3E]/30 hover:border-[#C98A3E] shadow-xs hover:shadow-xl hover:translate-x-1.5 transition-all duration-500 ease-out cursor-default">
                    <div class="space-y-3">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 group-hover:bg-[#C98A3E] text-[#C98A3E] group-hover:text-white transition-colors duration-300">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#C98A3E] group-hover:bg-white transition-colors duration-300 animate-ping"></span>
                            <span class="text-[10px] uppercase tracking-widest font-extrabold font-sans">01. Dawn & Morning</span>
                        </div>
                        <h4 class="font-serif font-bold text-lg text-[#14231C] group-hover:text-[#C98A3E] transition-colors duration-300">
                            Awakening to Country Air
                        </h4>
                        <p class="text-xs text-[#4A5D49] leading-relaxed font-sans transition-colors duration-300 group-hover:text-[#2C3E2B]">
                            Wake to birdsong and soft mountain breezes. Enjoy breakfast on your veranda featuring fresh estate eggs, warm pasture milk, and wild forest honey.
                        </p>
                    </div>
                </div>

                <!-- 02. Afternoon in Nature -->
                <div class="group relative p-6 bg-white/60 hover:bg-white rounded-r-2xl border-l-4 border-[#C98A3E]/30 hover:border-[#C98A3E] shadow-xs hover:shadow-xl hover:translate-x-1.5 transition-all duration-500 ease-out cursor-default">
                    <div class="space-y-3">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 group-hover:bg-[#C98A3E] text-[#C98A3E] group-hover:text-white transition-colors duration-300">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#C98A3E] group-hover:bg-white transition-colors duration-300 animate-ping"></span>
                            <span class="text-[10px] uppercase tracking-widest font-extrabold font-sans">02. Afternoon in Nature</span>
                        </div>
                        <h4 class="font-serif font-bold text-lg text-[#14231C] group-hover:text-[#C98A3E] transition-colors duration-300">
                            Orchards & Swimming Pool
                        </h4>
                        <p class="text-xs text-[#4A5D49] leading-relaxed font-sans transition-colors duration-300 group-hover:text-[#2C3E2B]">
                            Take a leisurely walk through shady mango and fruit groves, visit the friendly dairy cows, or unwind by the swimming pool under the warm sun.
                        </p>
                    </div>
                </div>

                <!-- 03. Evening & Night -->
                <div class="group relative p-6 bg-white/60 hover:bg-white rounded-r-2xl border-l-4 border-[#C98A3E]/30 hover:border-[#C98A3E] shadow-xs hover:shadow-xl hover:translate-x-1.5 transition-all duration-500 ease-out cursor-default">
                    <div class="space-y-3">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 group-hover:bg-[#C98A3E] text-[#C98A3E] group-hover:text-white transition-colors duration-300">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#C98A3E] group-hover:bg-white transition-colors duration-300 animate-ping"></span>
                            <span class="text-[10px] uppercase tracking-widest font-extrabold font-sans">03. Evening & Night</span>
                        </div>
                        <h4 class="font-serif font-bold text-lg text-[#14231C] group-hover:text-[#C98A3E] transition-colors duration-300">
                            Sundowners & Firepit
                        </h4>
                        <p class="text-xs text-[#4A5D49] leading-relaxed font-sans transition-colors duration-300 group-hover:text-[#2C3E2B]">
                            Watch golden sunsets over the highland ridges, savor a farm-fresh dinner, and gather around the outdoor wood firepit under clear star-filled skies.
                        </p>
                    </div>
                </div>

            </div>
        </section>

        <!-- 3. VILLA SHOWCASE -->
        <section class="content-auto py-16 bg-white border-t border-b border-gray-100">
            <div class="max-w-6xl mx-auto px-6 md:px-12 space-y-12">
                <div class="text-center space-y-2">
                    <span class="text-xs text-emerald-800 uppercase tracking-widest font-sans font-bold">The Sanctuary</span>
                    <h2 class="text-3xl font-extrabold">Our Luxury Accommodations</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div v-for="villa in villas" :key="villa.id" class="space-y-4 group">
                        <div class="aspect-[4/3] overflow-hidden bg-gray-100 rounded">
                            <img :src="getImageUrl(villa.featured_image)" :alt="villa.name" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        </div>
                        <div class="flex justify-between items-start pt-2">
                            <div>
                                <h3 class="font-extrabold text-lg">{{ villa.name }}</h3>
                                <p class="text-xs text-gray-500 font-sans mt-1">{{ villa.short_description }}</p>
                            </div>
                        </div>
                        <p class="text-xs text-gray-600 line-clamp-3 font-sans leading-relaxed">{{ villa.description }}</p>
                        <Link :href="route('villas.show', villa.slug)" prefetch class="inline-block text-xs font-bold text-emerald-700 hover:text-emerald-950 font-sans tracking-wider uppercase border-b-2 border-emerald-700 pb-0.5 mt-2">Explore Villa Details →</Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- LUXURY FOOTER -->
        <footer class="content-auto bg-[#1C261A] text-[#B5C2B4] py-16 px-6 md:px-12 border-t border-[#293627]">
            <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-12 text-xs font-sans">
                <div class="space-y-4">
                    <h4 class="font-bold text-white text-sm tracking-widest uppercase">Kitonga Farm Villas</h4>
                    <p class="leading-relaxed">A luxury country accommodation stay and authentic farm-stay destination in Komkonga, Tanga. Where luxury meets farm life.</p>
                </div>
                <div class="space-y-4">
                    <h4 class="font-bold text-white text-sm tracking-widest uppercase">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><Link :href="route('villas')" prefetch class="hover:underline">Villa Options</Link></li>
                        <li><Link :href="route('experiences')" prefetch class="hover:underline">Farm Tours</Link></li>
                        <li><Link :href="route('products')" prefetch class="hover:underline">Farm Produce</Link></li>
                        <li><Link :href="route('booking.form')" prefetch class="hover:underline">Check Availability</Link></li>
                    </ul>
                </div>
                <div class="space-y-4">
                    <h4 class="font-bold text-white text-sm tracking-widest uppercase">Contact Details</h4>
                    <p><a :href="'mailto:' + (settings.contact_email || 'kitongafarmvillas@gmail.com')" class="hover:underline hover:text-white transition">{{ settings.contact_email || 'kitongafarmvillas@gmail.com' }}</a></p>
                    <p><a :href="'tel:' + (settings.contact_phone || '+255784123456').replace(/\s+/g, '')" class="hover:underline hover:text-white transition">{{ settings.contact_phone || '+255 784 123 456' }}</a></p>
                    <p>Komkonga Village, Tanga Region, Tanzania</p>
                </div>
            </div>
            <div class="max-w-6xl mx-auto border-t border-[#293627] mt-12 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-gray-400 font-sans">
                <p>© {{ new Date().getFullYear() }} Kitonga Farm Villas. All rights reserved.</p>
                <div class="flex items-center gap-2">
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

