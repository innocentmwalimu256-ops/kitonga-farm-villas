<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    settings: {
        type: Object,
        default: () => ({
            contact_phone: '+255 758 774 695',
            contact_email: 'info@kitongafarmvillas.com',
            location_coordinates: '-5.0889, 39.0988',
        }),
    },
});

const isMobileMenuOpen = ref(false);
</script>

<template>
    <Head title="Directions & Location — Kitonga Farm Villas" />

    <div class="bg-[#FAF8F5] text-[#1F2420] font-sans min-h-screen selection:bg-[#C98A3E] selection:text-white">
        
        <!-- 1. STICKY TOP NAVBAR -->
        <header class="sticky top-0 z-50 w-full px-6 py-4 md:px-12 flex justify-between items-center text-white bg-[#14231C]/95 backdrop-blur-md border-b border-white/10 shadow-md transition duration-300">
            <Link :href="route('home')" class="flex flex-col items-start group cursor-pointer">
                <span class="font-serif text-lg md:text-2xl font-light text-[#F5F1E8] tracking-[4px] uppercase leading-none transition group-hover:text-[#C98A3E] duration-300">
                    KITONGA
                </span>
                <span class="font-sans text-[8px] md:text-[9px] font-medium text-[#C98A3E] tracking-[6px] uppercase leading-none mt-1 pl-[2px] transition group-hover:text-[#F5F1E8] duration-300">
                    FARMS VILLAS
                </span>
            </Link>

            <nav class="hidden md:flex space-x-6 lg:space-x-8 text-xs font-semibold uppercase tracking-widest text-gray-200 font-sans items-center">
                <Link :href="route('home')" prefetch class="hover:text-[#C98A3E] transition duration-200">Home</Link>
                <Link :href="route('villas')" prefetch class="hover:text-[#C98A3E] transition duration-200">Villas</Link>
                <Link :href="route('experiences')" prefetch class="hover:text-[#C98A3E] transition duration-200">Experiences</Link>
                <Link :href="route('farm')" prefetch class="hover:text-[#C98A3E] transition duration-200">Our Farm</Link>
                <Link :href="route('products')" prefetch class="hover:text-[#C98A3E] transition duration-200">Produce</Link>
                <Link :href="route('gallery')" prefetch class="hover:text-[#C98A3E] transition duration-200">Gallery</Link>
                <Link :href="route('contact')" prefetch class="hover:text-[#C98A3E] transition duration-200">Contact</Link>
                <Link :href="route('login')" prefetch class="hover:text-[#C98A3E] transition duration-200">Sign In</Link>
            </nav>

            <Link 
                :href="route('booking.form')" 
                prefetch 
                class="hidden md:inline-flex px-5 py-2.5 bg-white text-gray-900 hover:bg-[#FAF8F5] text-xs font-extrabold uppercase tracking-wider rounded-lg transition font-sans shadow-md hover:shadow-lg cursor-pointer"
            >
                BOOK STAY
            </Link>

            <!-- Mobile Menu Button -->
            <button 
                type="button" 
                @click="isMobileMenuOpen = !isMobileMenuOpen" 
                class="p-1.5 text-white hover:text-[#C98A3E] focus:outline-none transition cursor-pointer md:hidden"
                aria-label="Toggle navigation menu"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </header>

        <!-- Mobile Drawer -->
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
                <Link :href="route('gallery')" @click="isMobileMenuOpen = false" class="hover:text-[#C98A3E] transition py-1">Gallery</Link>
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

        <!-- 2. HERO BANNER -->
        <section class="bg-white text-[#14231C] pt-14 pb-14 md:pt-20 md:pb-16 px-6 md:px-12 border-b border-gray-200">
            <div class="max-w-4xl mx-auto text-center space-y-4">
                <span class="text-xs uppercase tracking-[3px] font-bold text-[#C98A3E] font-sans">
                    DIRECTIONS & LOCATION
                </span>
                
                <h1 class="font-serif text-3xl sm:text-4xl md:text-5xl font-semibold tracking-tight text-[#14231C]">
                    Safari ya Kufika Kitonga Farm Villas
                </h1>
                
                <p class="font-sans text-sm sm:text-base text-gray-600 max-w-2xl mx-auto leading-relaxed">
                    Kitonga Farm Villas ipo katika Kijiji cha Komkonga, Mkoani Tanga, Tanzania. Mazingira ya asili, utulivu wa mashambani, na njia rahisi ya kufika.
                </p>
            </div>
        </section>

        <!-- 3. REAL INTERACTIVE MAP SHOWCASE -->
        <section class="max-w-6xl mx-auto px-6 py-12 md:py-16 space-y-10">
            
            <div class="bg-white rounded-3xl border border-gray-200 overflow-hidden shadow-xl">
                
                <div class="p-6 sm:p-8 bg-[#14231C] text-white flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <span class="text-[10px] uppercase font-bold tracking-[3px] text-[#E6C387]">
                            📍 GPS & RAMANI HALISI
                        </span>
                        <h2 class="font-serif text-2xl sm:text-3xl font-light text-[#F7F3EA]">
                            Ramani ya Komkonga, Tanga
                        </h2>
                        <p class="text-xs text-gray-300 font-sans">
                            Coordinates: {{ settings.location_coordinates || '-5.0889, 39.0988' }}
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <a 
                            :href="'https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(settings.location_coordinates || '-5.0889,39.0988')" 
                            target="_blank" 
                            rel="noopener noreferrer" 
                            class="inline-flex items-center gap-2 px-5 py-3 bg-[#C98A3E] hover:bg-[#b57a34] text-white text-xs font-bold uppercase tracking-wider rounded-xl transition duration-300 shadow-md font-sans"
                        >
                            <span>Fungua Google Maps App</span>
                            <span>↗</span>
                        </a>
                        <a 
                            :href="'https://wa.me/' + (settings.contact_phone || '+255758774695').replace(/[^0-9]/g, '') + '?text=' + encodeURIComponent('Habari, ninaomba mwongozo wa njia ya kufika Kitonga Farm Villas.')" 
                            target="_blank" 
                            rel="noopener noreferrer" 
                            class="inline-flex items-center gap-2 px-5 py-3 bg-[#25D366] hover:bg-[#20ba59] text-white text-xs font-bold uppercase tracking-wider rounded-xl transition duration-300 shadow-md font-sans"
                        >
                            <span>Piga Simu / WhatsApp</span>
                            <span>💬</span>
                        </a>
                    </div>
                </div>

                <!-- Google Maps Embed -->
                <div class="relative w-full h-80 sm:h-96 md:h-[480px] bg-[#e5e3df]">
                    <iframe
                        class="w-full h-full border-0"
                        :src="'https://maps.google.com/maps?q=' + (settings.location_coordinates || '-5.0889,39.0988') + '&hl=sw&z=13&output=embed'"
                        loading="lazy"
                        allowfullscreen
                        referrerpolicy="no-referrer-when-downgrade"
                        title="Kitonga Farm Villas Location"
                    ></iframe>
                </div>

                <!-- 4 Travel Cards -->
                <div class="p-6 sm:p-8 bg-[#FAF8F5] border-t border-gray-200 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 font-sans text-xs">
                    <div class="p-5 rounded-2xl bg-white border border-gray-150 space-y-1.5">
                        <span class="text-base">🚗</span>
                        <h3 class="font-bold text-gray-900 text-sm">Kutokea Tanga Mjini</h3>
                        <p class="text-gray-600 leading-relaxed">Dakika 45 kwa gari (~35 km) kupitia barabara ya Tanga.</p>
                    </div>

                    <div class="p-5 rounded-2xl bg-white border border-gray-150 space-y-1.5">
                        <span class="text-base">🚗</span>
                        <h3 class="font-bold text-gray-900 text-sm">Kutokea Korogwe / Segera</h3>
                        <p class="text-gray-600 leading-relaxed">Saa 1 na dakika 15 kwa barabara kuu kuelekea Komkonga.</p>
                    </div>

                    <div class="p-5 rounded-2xl bg-white border border-gray-150 space-y-1.5">
                        <span class="text-base">🚗</span>
                        <h3 class="font-bold text-gray-900 text-sm">Kutokea Dar es Salaam</h3>
                        <p class="text-gray-600 leading-relaxed">Saa 4.5 – 5 kupitia njia ya Chalinze - Segera - Tanga.</p>
                    </div>

                    <div class="p-5 rounded-2xl bg-white border border-gray-150 space-y-1.5">
                        <span class="text-base">✈️</span>
                        <h3 class="font-bold text-gray-900 text-sm">Usafiri wa Mapokezi</h3>
                        <p class="text-gray-600 leading-relaxed">Huduma ya gari binafsi (Private Transfer) ipo kwa uwanja wa ndege na stendi.</p>
                    </div>
                </div>

            </div>

        </section>

        <!-- 4. FOOTER -->
        <footer class="bg-[#14231C] text-gray-400 text-xs py-12 border-t border-white/10 font-sans">
            <div class="max-w-6xl mx-auto px-6 grid grid-cols-1 md:grid-cols-3 gap-10">
                <div class="space-y-2.5">
                    <p class="font-bold text-white text-sm font-serif uppercase tracking-widest">KITONGA FARM VILLAS</p>
                    <p class="text-gray-400 text-xs leading-relaxed">Makazi ya kifahari ya mashambani Komkonga, Tanga, Tanzania.</p>
                </div>
                <div class="space-y-2">
                    <p class="font-bold text-white uppercase tracking-wider text-xs">Kurasa za Haraka</p>
                    <div class="flex flex-col space-y-1.5">
                        <Link :href="route('villas')" class="hover:text-white transition">Villa na Makazi</Link>
                        <Link :href="route('experiences')" class="hover:text-white transition">Ziara za Shamba</Link>
                        <Link :href="route('farm')" class="hover:text-white transition">Shamba Letu</Link>
                        <Link :href="route('products')" class="hover:text-white transition">Mazao ya Shamba</Link>
                        <Link :href="route('gallery')" class="hover:text-white transition">Gallery ya Picha</Link>
                    </div>
                </div>
                <div class="space-y-2">
                    <p class="font-bold text-white uppercase tracking-wider text-xs">Mawasiliano</p>
                    <p class="text-gray-400">Komkonga Village, Tanga, Tanzania</p>
                    <p class="text-gray-400">Simu: +255 758 774 695</p>
                    <p class="text-gray-400">Email: info@kitongafarmvillas.com</p>
                </div>
            </div>
            <div class="max-w-6xl mx-auto px-6 mt-8 pt-5 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-4 text-gray-400 text-xs">
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

