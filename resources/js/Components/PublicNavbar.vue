<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    currentPage: {
        type: String,
        default: '',
    },
    settings: {
        type: Object,
        default: () => ({
            contact_phone: '+255 758 774 695',
        }),
    },
});

const isMobileMenuOpen = ref(false);

const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
    if (isMobileMenuOpen.value) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
};

const closeMobileMenu = () => {
    isMobileMenuOpen.value = false;
    document.body.style.overflow = '';
};

onUnmounted(() => {
    document.body.style.overflow = '';
});

const navLinks = [
    { name: 'home', label: 'Home', route: 'home' },
    { name: 'villas', label: 'Villas', route: 'villas' },
    { name: 'experiences', label: 'Experiences', route: 'experiences' },
    { name: 'meet-mr-kitonga', label: 'Meet Mr. Kitonga', route: 'meet.mr.kitonga' },
    { name: 'farm', label: 'Our Farm', route: 'farm' },
    { name: 'products', label: 'Produce', route: 'products' },
    { name: 'gallery', label: 'Gallery', route: 'gallery' },
    { name: 'contact', label: 'Contact', route: 'contact' },
];
</script>

<template>
    <!-- TOP STICKY LUXURY NAVBAR -->
    <header class="sticky top-0 z-50 w-full bg-[#14231C]/95 backdrop-blur-md px-4 sm:px-8 md:px-12 py-3.5 flex justify-between items-center text-white border-b border-white/10 shadow-md transition duration-300">
        
        <!-- Brand Crest & Typography -->
        <Link :href="route('home')" class="flex items-center gap-3 group cursor-pointer" @click="closeMobileMenu">
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full border border-[#C98A3E]/60 flex items-center justify-center p-1 bg-[#1B2E24] shadow-xs group-hover:border-[#E6C387] transition shrink-0">
                <img src="/favicon.svg" alt="Kitonga Crest Logo" class="w-full h-full object-contain" />
            </div>
            <div class="flex flex-col">
                <span class="font-serif text-sm sm:text-base md:text-lg font-light text-[#F5F1E8] tracking-[3px] sm:tracking-[4px] uppercase leading-none transition group-hover:text-[#C98A3E] duration-300">
                    KITONGA
                </span>
                <span class="font-sans text-[7px] sm:text-[8px] md:text-[9px] font-medium text-[#C98A3E] tracking-[4px] sm:tracking-[5px] uppercase leading-none mt-1 transition group-hover:text-[#F5F1E8] duration-300">
                    FARMS VILLAS
                </span>
            </div>
        </Link>

        <!-- Desktop Navigation Links -->
        <nav class="hidden md:flex space-x-5 lg:space-x-7 text-xs font-semibold uppercase tracking-widest text-gray-200 font-sans items-center">
            <Link 
                v-for="link in navLinks" 
                :key="link.name"
                :href="route(link.route)" 
                prefetch 
                class="transition duration-200 py-1"
                :class="currentPage === link.name 
                    ? 'text-[#E6C387] font-bold border-b-2 border-[#C98A3E]' 
                    : 'hover:text-[#C98A3E]'"
            >
                {{ link.label }}
            </Link>
            <Link :href="route('login')" prefetch class="text-gray-400 hover:text-white transition duration-200">
                Sign In
            </Link>
        </nav>

        <!-- Right CTA & Mobile Toggle -->
        <div class="flex items-center gap-2.5 sm:gap-4">
            <!-- Desktop Book Stay Button -->
            <Link 
                :href="route('booking.form')" 
                prefetch 
                class="hidden md:inline-flex px-5 py-2.5 bg-white text-gray-900 hover:bg-[#FAF8F5] text-xs font-extrabold uppercase tracking-wider rounded-lg transition font-sans shadow-md hover:shadow-lg cursor-pointer"
            >
                BOOK STAY
            </Link>

            <!-- Mobile Book Stay Compact Button -->
            <Link 
                :href="route('booking.form')" 
                prefetch 
                class="md:hidden px-3 py-1.5 bg-[#C98A3E] text-white text-[10px] font-extrabold uppercase tracking-wider rounded transition font-sans shadow-xs cursor-pointer"
            >
                BOOK STAY
            </Link>

            <!-- Mobile Hamburger Button -->
            <button 
                type="button" 
                @click="toggleMobileMenu" 
                class="p-2 rounded-lg bg-white/5 hover:bg-white/10 text-white focus:outline-none transition cursor-pointer md:hidden border border-white/10"
                aria-label="Toggle navigation menu"
            >
                <svg v-if="!isMobileMenuOpen" class="w-5 h-5 text-[#E6C387]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg v-else class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </header>

    <!-- MOBILE NAVIGATION FULL-SCREEN DRAWER (TELEPORTED) -->
    <Teleport to="body">
        <!-- Backdrop -->
        <div 
            v-if="isMobileMenuOpen" 
            class="fixed inset-0 bg-black/80 backdrop-blur-xs z-[998] md:hidden transition-opacity duration-300"
            @click="closeMobileMenu"
        ></div>

        <!-- Slide Drawer with Solid Dark Forest Green Background -->
        <div 
            v-if="isMobileMenuOpen" 
            class="fixed top-0 right-0 w-[85%] max-w-[340px] h-full bg-[#0E1A14] text-white z-[999] md:hidden shadow-2xl flex flex-col justify-between p-6 overflow-y-auto border-l border-[#C98A3E]/30 font-sans animate-in slide-in-from-right duration-300"
        >
            <div class="space-y-6">
                <!-- Drawer Top Bar -->
                <div class="flex items-center justify-between pb-4 border-b border-white/10">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full border border-[#C98A3E]/60 flex items-center justify-center p-1 bg-[#1B2E24]">
                            <img src="/favicon.svg" alt="Kitonga Logo" class="w-full h-full object-contain" />
                        </div>
                        <div class="flex flex-col">
                            <span class="font-serif text-sm font-semibold tracking-[3px] text-[#F5F1E8] uppercase">KITONGA</span>
                            <span class="text-[7px] text-[#C98A3E] tracking-[4px] uppercase font-bold">FARMS VILLAS</span>
                        </div>
                    </div>
                    <button 
                        @click="closeMobileMenu"
                        class="p-2 rounded-full bg-white/10 hover:bg-white/20 text-white transition cursor-pointer"
                        aria-label="Close menu"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Navigation Links List (High Contrast & Large Touch Targets) -->
                <nav class="flex flex-col space-y-1 text-xs font-semibold uppercase tracking-[2px]">
                    <Link 
                        v-for="link in navLinks" 
                        :key="link.name"
                        :href="route(link.route)" 
                        prefetch 
                        @click="closeMobileMenu" 
                        class="flex items-center justify-between py-3 px-3.5 rounded-lg transition"
                        :class="currentPage === link.name 
                            ? 'bg-[#1E3326] text-[#E6C387] border-l-3 border-[#C98A3E] font-bold shadow-xs' 
                            : 'text-gray-100 hover:text-white hover:bg-white/5'"
                    >
                        <span>{{ link.label }}</span>
                        <span v-if="currentPage === link.name" class="text-xs text-[#E6C387]">✦</span>
                        <span v-else class="text-xs text-white/30">→</span>
                    </Link>
                    
                    <Link 
                        :href="route('login')" 
                        prefetch 
                        @click="closeMobileMenu" 
                        class="flex items-center justify-between py-3 px-3.5 rounded-lg text-gray-400 hover:text-white hover:bg-white/5 transition"
                    >
                        <span>Sign In</span>
                        <span class="text-xs text-gray-500">🔒</span>
                    </Link>
                </nav>
            </div>

            <!-- Drawer Bottom Action Buttons -->
            <div class="pt-6 border-t border-white/10 space-y-2.5">
                <Link 
                    :href="route('booking.form')" 
                    prefetch
                    @click="closeMobileMenu"
                    class="block w-full py-3 bg-[#C98A3E] hover:bg-[#b57a34] text-white text-center font-bold text-xs uppercase tracking-[2px] rounded-lg shadow-md transition"
                >
                    Book Stay
                </Link>
                <a 
                    href="https://wa.me/255758774695" 
                    target="_blank"
                    class="flex items-center justify-center gap-2 w-full py-2.5 bg-white/5 hover:bg-white/10 text-[#E6C387] text-center font-semibold text-[11px] uppercase tracking-wider rounded-lg border border-[#C98A3E]/30 transition"
                >
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>WhatsApp Concierge</span>
                </a>
            </div>
        </div>
    </Teleport>

    <!-- GLOBAL FLOATING "MEET MR. KITONGA" BUTTON (TELEPORTED TO BODY) -->
    <Teleport to="body" v-if="currentPage !== 'meet-mr-kitonga'">
        <div class="fixed bottom-5 right-5 z-[80] transition-all duration-300 hover:scale-105">
            <Link 
                :href="route('meet.mr.kitonga')"
                prefetch
                class="inline-flex items-center gap-2.5 px-4 py-3 bg-[#14231C]/95 hover:bg-[#1B2E24] text-white rounded-full border-2 border-[#C98A3E] shadow-2xl backdrop-blur-md transition group cursor-pointer"
                title="Book 1-on-1 Consultation with Mr. Kitonga"
            >
                <div class="w-7 h-7 rounded-full bg-[#C98A3E] text-white flex items-center justify-center font-serif font-bold text-xs shadow-inner shrink-0">
                    K
                </div>
                <div class="flex flex-col text-left">
                    <span class="font-bold text-[11px] uppercase tracking-wider text-[#F5F1E8] group-hover:text-[#E6C387] transition font-sans leading-none">
                        Meet Mr. Kitonga
                    </span>
                    <span class="text-[9px] text-[#C98A3E] font-semibold tracking-widest uppercase mt-0.5 leading-none">
                        1-on-1 Advisory
                    </span>
                </div>
                <span class="text-[#C98A3E] text-xs font-bold pl-0.5">✦</span>
            </Link>
        </div>
    </Teleport>
</template>
