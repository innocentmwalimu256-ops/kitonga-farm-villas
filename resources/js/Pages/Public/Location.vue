<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    settings: {
        type: Object,
        default: () => ({
            contact_phone: '+255 758 774 695',
            contact_email: 'kitongafarmvillas@gmail.com',
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
                    Journey to Kitonga Farm Villas
                </h1>
                
                <p class="font-sans text-sm sm:text-base text-gray-600 max-w-2xl mx-auto leading-relaxed">
                    Kitonga Farm Villas is located in Komkonga Village, Tanga Region, Tanzania. Experience pristine nature, countryside tranquility, and easy accessibility.
                </p>
            </div>
        </section>

        <!-- 3. REAL INTERACTIVE MAP SHOWCASE -->
        <section class="max-w-6xl mx-auto px-6 py-12 md:py-16 space-y-10">
            
            <div class="bg-white rounded-3xl border border-gray-200 overflow-hidden shadow-xl">
                
                <div class="p-6 sm:p-8 bg-[#14231C] text-white flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <span class="text-[10px] uppercase font-bold tracking-[3px] text-[#E6C387]">
                            GPS & Real Map
                        </span>
                        <h2 class="font-serif text-2xl sm:text-3xl font-light text-[#F7F3EA]">
                            Map of Komkonga, Tanga
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
                            <span>Open Google Maps</span>
                            <span>↗</span>
                        </a>
                        <a 
                            :href="'https://wa.me/' + (settings.contact_phone || '+255758774695').replace(/[^0-9]/g, '') + '?text=' + encodeURIComponent('Hello, I need driving directions to Kitonga Farm Villas.')" 
                            target="_blank" 
                            rel="noopener noreferrer" 
                            class="inline-flex items-center gap-2 px-5 py-3 bg-[#25D366] hover:bg-[#20ba59] text-white text-xs font-bold uppercase tracking-wider rounded-xl transition duration-300 shadow-md font-sans"
                        >
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.698.074-2.128-.517-1.745-.722-2.888-2.518-2.977-2.637-.086-.118-.707-.941-.707-1.796 0-.854.448-1.275.607-1.448.16-.174.348-.218.465-.218.117 0 .234.001.336.006.107.005.251-.041.393.3.144.347.493 1.202.536 1.29.043.087.072.189.014.304-.058.117-.087.19-.174.29-.087.102-.183.228-.261.306-.089.088-.182.184-.078.362.104.178.463.765.994 1.238.683.608 1.259.797 1.438.885.178.087.283.073.388-.048.106-.12.453-.527.575-.708.121-.182.243-.151.408-.09.166.06 1.054.497 1.235.588.182.09.303.136.348.213.044.076.044.444-.1.849z"/>
                            </svg>
                            <span>Chat on WhatsApp</span>
                        </a>
                    </div>
                </div>

                <!-- Google Maps Embed -->
                <div class="relative w-full h-80 sm:h-96 md:h-[480px] bg-[#e5e3df]">
                    <iframe
                        class="w-full h-full border-0"
                        :src="'https://maps.google.com/maps?q=' + (settings.location_coordinates || '-5.0889,39.0988') + '&hl=en&z=13&output=embed'"
                        loading="lazy"
                        allowfullscreen
                        referrerpolicy="no-referrer-when-downgrade"
                        title="Kitonga Farm Villas Location"
                    ></iframe>
                </div>

                <!-- 4 Travel Cards -->
                <div class="p-6 sm:p-8 bg-[#FAF8F5] border-t border-gray-200 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 font-sans text-xs">
                    <div class="p-5 rounded-2xl bg-white border border-gray-150 space-y-2">
                        <svg class="w-5 h-5 text-[#C98A3E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8m-8 4h8m-8 4h4m5 4H7a2 2 0 01-2-2V5a2 2 0 012-2h10a2 2 0 012 2v14a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="font-bold text-gray-900 text-sm">From Tanga City</h3>
                        <p class="text-gray-600 leading-relaxed">45 minutes by car (~35 km) via Tanga main route.</p>
                    </div>

                    <div class="p-5 rounded-2xl bg-white border border-gray-150 space-y-2">
                        <svg class="w-5 h-5 text-[#C98A3E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                        <h3 class="font-bold text-gray-900 text-sm">From Korogwe / Segera</h3>
                        <p class="text-gray-600 leading-relaxed">1 hour 15 minutes along the main road towards Komkonga.</p>
                    </div>

                    <div class="p-5 rounded-2xl bg-white border border-gray-150 space-y-2">
                        <svg class="w-5 h-5 text-[#C98A3E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="font-bold text-gray-900 text-sm">From Dar es Salaam</h3>
                        <p class="text-gray-600 leading-relaxed">4.5 – 5 hours via Chalinze - Segera - Tanga corridor.</p>
                    </div>

                    <div class="p-5 rounded-2xl bg-white border border-gray-150 space-y-2">
                        <svg class="w-5 h-5 text-[#C98A3E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <h3 class="font-bold text-gray-900 text-sm">Private Transfer Service</h3>
                        <p class="text-gray-600 leading-relaxed">Dedicated chauffeur pickup available from Tanga airport or regional bus terminal.</p>
                    </div>
                </div>

            </div>

        </section>

        <!-- 4. FOOTER -->
        <footer class="bg-[#14231C] text-gray-400 text-xs py-12 border-t border-white/10 font-sans">
            <div class="max-w-6xl mx-auto px-6 grid grid-cols-1 md:grid-cols-3 gap-10">
                <div class="space-y-2.5">
                    <p class="font-bold text-white text-sm font-serif uppercase tracking-widest">KITONGA FARM VILLAS</p>
                    <p class="text-gray-400 text-xs leading-relaxed">A luxury countryside retreat and organic farm-stay in Komkonga, Tanga, Tanzania.</p>
                </div>
                <div class="space-y-2">
                    <p class="font-bold text-white uppercase tracking-wider text-xs">Quick Links</p>
                    <div class="flex flex-col space-y-1.5">
                        <Link :href="route('villas')" class="hover:text-white transition">Villas & Accommodation</Link>
                        <Link :href="route('experiences')" class="hover:text-white transition">Farm Experiences</Link>
                        <Link :href="route('farm')" class="hover:text-white transition">Our Farm</Link>
                        <Link :href="route('products')" class="hover:text-white transition">Farm Produce</Link>
                        <Link :href="route('gallery')" class="hover:text-white transition">Photo Gallery</Link>
                    </div>
                </div>
                <div class="space-y-2">
                    <p class="font-bold text-white uppercase tracking-wider text-xs">Contact Information</p>
                    <p class="text-gray-400">Komkonga Village, Tanga, Tanzania</p>
                    <p class="text-gray-400">Phone: +255 758 774 695</p>
                    <p class="text-gray-400">Email: kitongafarmvillas@gmail.com</p>
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

