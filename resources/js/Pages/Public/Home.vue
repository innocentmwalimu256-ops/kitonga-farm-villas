<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps({
    villas: Array,
    experiences: Array,
    products: Array,
    cms: Object,
    settings: Object,
});

const isMobileMenuOpen = ref(false);
const isMuted = ref(true);
const isPaused = ref(false);
const videoPlayer = ref(null);

onMounted(() => {
    if (videoPlayer.value) {
        videoPlayer.value.muted = true;
        videoPlayer.value.defaultMuted = true;
        isMuted.value = true;

        const startPlayback = () => {
            if (videoPlayer.value) {
                videoPlayer.value.muted = true;
                const p = videoPlayer.value.play();
                if (p !== undefined) {
                    p.then(() => {
                        isPaused.value = false;
                    }).catch(err => {
                        console.log("Autoplay waiting for touch:", err);
                    });
                }
            }
        };

        startPlayback();

        // Fallback on first user touch/click/scroll
        const triggerPlay = () => {
            if (videoPlayer.value && videoPlayer.value.paused) {
                startPlayback();
            }
            window.removeEventListener('click', triggerPlay);
            window.removeEventListener('touchstart', triggerPlay);
            window.removeEventListener('scroll', triggerPlay);
        };
        window.addEventListener('click', triggerPlay, { passive: true });
        window.addEventListener('touchstart', triggerPlay, { passive: true });
        window.addEventListener('scroll', triggerPlay, { passive: true });
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

const handleLogoClick = (e) => {
    if (window.location.pathname === '/') {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};
</script>

<template>
    <Head title="Where Luxury Meets Farm Life" />

    <div class="bg-[#FAF8F5] text-[#2C3E2B] font-serif min-h-screen">
        
        <!-- 1. STICKY TOP NAVBAR -->
        <header class="sticky top-0 z-50 w-full px-6 py-4 md:px-12 flex justify-between items-center text-white bg-[#14231C]/95 backdrop-blur-md border-b border-white/10 shadow-md transition duration-300">
            
            <!-- Logo -->
            <Link :href="route('home')" @click="handleLogoClick" class="flex flex-col items-start group cursor-pointer">
                <span class="font-serif text-lg md:text-2xl font-light text-[#F5F1E8] tracking-[4px] uppercase leading-none transition group-hover:text-[#C98A3E] duration-300">
                    KITONGA
                </span>
                <span class="font-sans text-[8px] md:text-[9px] font-medium text-[#C98A3E] tracking-[6px] uppercase leading-none mt-1 pl-[2px] transition group-hover:text-[#F5F1E8] duration-300">
                    FARMS VILLAS
                </span>
            </Link>

            <!-- Desktop Navigation Links -->
            <nav class="hidden md:flex space-x-6 lg:space-x-8 text-xs font-semibold uppercase tracking-widest text-gray-200 font-sans items-center">
                <Link :href="route('home')" prefetch class="text-[#E6C387] font-bold border-b-2 border-[#E6C387] pb-0.5 hover:text-[#C98A3E] transition duration-200">Home</Link>
                <Link :href="route('villas')" prefetch class="hover:text-[#C98A3E] transition duration-200">Villas</Link>
                <Link :href="route('experiences')" prefetch class="hover:text-[#C98A3E] transition duration-200">Experiences</Link>
                <Link :href="route('farm')" prefetch class="hover:text-[#C98A3E] transition duration-200">Our Farm</Link>
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
                    @click="isMobileMenuOpen = !isMobileMenuOpen" 
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
                <Link :href="route('home')" prefetch @click="isMobileMenuOpen = false" class="text-[#E6C387] font-bold py-1">Home</Link>
                <Link :href="route('villas')" prefetch @click="isMobileMenuOpen = false" class="hover:text-[#C98A3E] transition py-1">Villas</Link>
                <Link :href="route('experiences')" prefetch @click="isMobileMenuOpen = false" class="hover:text-[#C98A3E] transition py-1">Experiences</Link>
                <Link :href="route('farm')" prefetch @click="isMobileMenuOpen = false" class="hover:text-[#C98A3E] transition py-1">Our Farm</Link>
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

        <!-- 1. CINEMATIC HERO VIDEO SECTION -->
        <section 
            @click="togglePlay"
            class="relative min-h-[85vh] lg:min-h-[92vh] w-full overflow-hidden bg-[#0A120E] flex items-center justify-center cursor-pointer select-none group"
            title="Click anywhere to Play / Pause"
        >
            <!-- Hero Video (Ultra-Fast 6.7MB Web Video with FastStart Streaming) -->
            <video 
                ref="videoPlayer"
                poster="/images/hero_poster.webp"
                class="absolute inset-0 w-full h-full object-cover opacity-90 group-hover:opacity-100 transition-opacity duration-700 pointer-events-none"
                autoplay 
                loop 
                muted
                :muted="true"
                playsinline
                webkit-playsinline="true"
                preload="auto"
            >
                <source src="/videos/hero_cinematic.mp4" type="video/mp4">
                <source src="/stream/hero-video" type="video/mp4">
                <source src="/videos/hero_cinematic.webm" type="video/webm">
            </video>

            <!-- Cinematic Gradient Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-black/50 pointer-events-none"></div>

            <!-- Main Centered Content -->
            <div class="relative z-10 text-center px-6 sm:px-10 max-w-4xl mx-auto space-y-5 sm:space-y-6 pt-12 pb-16 pointer-events-auto">
                
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-[#14231C]/80 border border-[#C98A3E]/50 text-[#E6C387] text-[11px] sm:text-xs font-bold uppercase tracking-[3px] backdrop-blur-md shadow-lg animate-fade-in">
                    <span class="w-2 h-2 rounded-full bg-[#C98A3E] animate-pulse"></span>
                    <span>Sanctuary & Agro-Tourism</span>
                </div>

                <!-- Hero Title -->
                <h1 class="text-3xl sm:text-5xl md:text-6xl lg:text-7xl font-light text-white tracking-tight leading-[1.15] drop-shadow-md">
                    Where Luxury Meets <span class="italic font-serif text-[#E6C387] font-normal">Farm Life</span>
                </h1>

                <!-- Hero Subtitle -->
                <p class="text-sm sm:text-base md:text-lg text-gray-200/90 font-sans font-light max-w-2xl mx-auto leading-relaxed drop-shadow-sm">
                    Immerse yourself in private sanctuary villas, pure organic harvest, serene river breezes, and authentic countryside living in Komkonga, Tanga.
                </p>

                <!-- Action CTAs -->
                <div class="pt-2 sm:pt-4 flex flex-wrap items-center justify-center gap-3.5 sm:gap-5 font-sans">
                    <Link 
                        :href="route('booking.form')" 
                        prefetch 
                        class="px-7 py-3.5 bg-[#C98A3E] hover:bg-[#b87a32] text-white text-xs sm:text-sm font-extrabold uppercase tracking-widest rounded-xl transition shadow-xl hover:shadow-[#C98A3E]/30 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer"
                    >
                        Book Your Stay
                    </Link>
                    <Link 
                        :href="route('experiences')" 
                        prefetch 
                        class="px-7 py-3.5 bg-white/10 hover:bg-white/20 text-white border border-white/30 text-xs sm:text-sm font-bold uppercase tracking-widest rounded-xl backdrop-blur-md transition hover:-translate-y-0.5 active:translate-y-0 cursor-pointer"
                    >
                        Explore Experiences
                    </Link>
                </div>
            </div>

            <!-- Subtle Bottom Subtitle Overlay -->
            <div class="hidden sm:block absolute bottom-6 left-8 z-10">
                <span class="text-[11px] uppercase tracking-[3px] text-gray-300/80 font-semibold font-sans">
                    ðŸ“ Komkonga, Tanga â€” Tanzania
                </span>
            </div>

            <!-- Video Controls (Play/Pause & Mute/Unmute) -->
            <div class="absolute bottom-6 right-6 z-20 flex items-center gap-2 font-sans">
                <button 
                    type="button"
                    @click="togglePlay" 
                    class="px-3 py-1.5 rounded-full bg-black/60 hover:bg-black/85 border border-white/25 text-white text-[11px] font-medium backdrop-blur-md transition flex items-center gap-1.5 shadow-md cursor-pointer"
                    :title="isPaused ? 'Play Video' : 'Pause Video'"
                >
                    <span>{{ isPaused ? 'â–¶ Play' : 'â¸ Pause' }}</span>
                </button>
                <button 
                    type="button"
                    @click="toggleMute" 
                    class="px-3 py-1.5 rounded-full bg-black/60 hover:bg-black/85 border border-white/25 text-white text-[11px] font-medium backdrop-blur-md transition flex items-center gap-1.5 shadow-md cursor-pointer"
                    :title="isMuted ? 'Unmute Audio' : 'Mute Audio'"
                >
                    <span>{{ isMuted ? 'ðŸ”‡ Sound Off' : 'ðŸ”Š Sound On' }}</span>
                </button>
            </div>
        </section>

        <!-- 2. SHORT BRAND STORY -->
        <section class="content-auto py-20 px-6 md:px-12 max-w-5xl mx-auto text-center space-y-6">
            <span class="text-xs text-emerald-800 uppercase tracking-widest font-sans font-bold">The Philosophy</span>
            <h2 class="text-3xl md:text-4xl font-extrabold leading-tight">Serenity, Privacy, and Local Heritage</h2>
            <p class="text-base text-[#4A5D49] leading-relaxed font-sans max-w-3xl mx-auto">
                Kitonga Farm Villas is not just an accommodation; it is a premium countryside destination in Komkonga, Tanga. We connect luxury villa hospitality with organic farmingâ€”offering fresh fruits, swimming pool, forest trails, and cattle farm experiences. Relish farm-to-table cuisine prepared with care by our kitchen.
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

        <!-- 2.2. THE DAILY RHYTHM AT KITONGA (PURE TEXT NARRATIVE â€” NO NEW IMAGES) -->
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
                        <Link :href="route('villas.show', villa.slug)" prefetch class="inline-block text-xs font-bold text-emerald-700 hover:text-emerald-950 font-sans tracking-wider uppercase border-b-2 border-emerald-700 pb-0.5 mt-2">Explore Villa Details âž”</Link>
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

