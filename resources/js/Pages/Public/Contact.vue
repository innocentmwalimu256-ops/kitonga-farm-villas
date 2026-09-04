<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
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
const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

const form = useForm({
    name: '',
    email: '',
    phone: '',
    subject: 'Villa Reservation',
    message: '',
});

const isSuccess = ref(false);

const submitContact = () => {
    form.post(route('contact.submit'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            isSuccess.value = true;
            setTimeout(() => {
                isSuccess.value = false;
            }, 6000);
        }
    });
};
</script>

<template>
    <Head title="Contact Us — Kitonga Farm Villas" />

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
                <Link :href="route('farm')" prefetch class="hover:text-[#C98A3E] transition duration-200">Our Farm</Link>
                <Link :href="route('products')" prefetch class="hover:text-[#C98A3E] transition duration-200">Produce</Link>
                <Link :href="route('gallery')" prefetch class="hover:text-[#C98A3E] transition duration-200">Gallery</Link>
                <Link :href="route('contact')" prefetch class="text-[#E6C387] font-bold border-b-2 border-[#E6C387] pb-0.5">Contact</Link>
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
                <Link :href="route('home')" @click="isMobileMenuOpen = false" class="hover:text-[#C98A3E] transition py-1">Home</Link>
                <Link :href="route('villas')" @click="isMobileMenuOpen = false" class="hover:text-[#C98A3E] transition py-1">Villas</Link>
                <Link :href="route('experiences')" @click="isMobileMenuOpen = false" class="hover:text-[#C98A3E] transition py-1">Experiences</Link>
                <Link :href="route('farm')" @click="isMobileMenuOpen = false" class="hover:text-[#C98A3E] transition py-1">Our Farm</Link>
                <Link :href="route('products')" @click="isMobileMenuOpen = false" class="hover:text-[#C98A3E] transition py-1">Produce</Link>
                <Link :href="route('gallery')" @click="isMobileMenuOpen = false" class="hover:text-[#C98A3E] transition py-1">Gallery</Link>
                <Link :href="route('contact')" @click="isMobileMenuOpen = false" class="text-[#E6C387] font-bold py-1">Contact</Link>
                <Link :href="route('login')" @click="isMobileMenuOpen = false" class="hover:text-[#C98A3E] transition py-1">Sign In</Link>
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

        <!-- 2. NATURAL WHITE HERO SECTION -->
        <section class="bg-white text-[#14231C] pt-14 pb-14 md:pt-20 md:pb-16 px-6 md:px-12 border-b border-gray-200">
            <div class="max-w-4xl mx-auto text-center space-y-4">
                <span class="text-xs uppercase tracking-[3px] font-bold text-[#C98A3E] font-sans">
                    Mawasiliano & Taarifa
                </span>
                
                <h1 class="font-serif text-3xl sm:text-4xl md:text-5xl font-semibold tracking-tight text-[#14231C]">
                    Wasiliana Nasi Kitonga Farm Villas
                </h1>
                
                <p class="font-sans text-sm sm:text-base text-gray-600 max-w-2xl mx-auto leading-relaxed">
                    Kwa maswali yoyote kuhusu ukaaji wa villa, kutembelea shamba letu, kununua bidhaa halisi za shamba, au maandalizi ya safari yako Komkonga, tuko tayari kukusaidia wakati wowote.
                </p>
            </div>
        </section>

        <!-- 3. NATURAL CONTACT DETAILS & FORM SECTION -->
        <section class="max-w-6xl mx-auto px-6 py-12 md:py-16">
            
            <div class="grid grid-cols-1 md:grid-cols-12 gap-10 lg:gap-14 items-start">
                
                <!-- Left Column: Njia za Mawasiliano -->
                <div class="md:col-span-5 space-y-6">
                    
                    <div class="space-y-2">
                        <h2 class="font-serif text-2xl font-bold text-[#14231C]">Taarifa za Mawasiliano</h2>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Unaweza kuwasiliana nasi moja kwa moja kwa simu, WhatsApp, au barua pepe:
                        </p>
                    </div>

                    <!-- Direct WhatsApp Button -->
                    <a 
                        :href="'https://wa.me/' + (settings.contact_phone || '+255758774695').replace(/[^0-9]/g, '') + '?text=' + encodeURIComponent('Habari Kitonga Farm Villas, ningependa kupata taarifa zaidi kuhusu ukaaji na huduma zenu.')"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex items-center gap-3.5 p-4 rounded-xl bg-[#25D366] text-white hover:bg-[#20ba59] transition shadow-sm font-sans"
                    >
                        <span class="text-2xl">💬</span>
                        <div class="flex-1">
                            <span class="text-xs font-bold block">Tuma Ujumbe WhatsApp</span>
                            <span class="text-[11px] text-white/90">Wasiliana moja kwa moja kwa WhatsApp</span>
                        </div>
                        <span class="text-sm font-bold">↗</span>
                    </a>

                    <!-- Direct Contacts List -->
                    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-xs space-y-5 text-xs font-sans">
                        
                        <div class="flex items-start gap-3.5">
                            <span class="text-lg text-[#C98A3E]">📞</span>
                            <div>
                                <strong class="text-gray-900 block text-xs">Simu ya Moja kwa Moja</strong>
                                <a :href="'tel:' + (settings.contact_phone || '+255758774695').replace(/\s+/g, '')" class="text-gray-600 hover:text-[#C98A3E] transition mt-0.5 inline-block">
                                    {{ settings.contact_phone || '+255 758 774 695' }}
                                </a>
                            </div>
                        </div>

                        <div class="flex items-start gap-3.5 pt-3 border-t border-gray-150">
                            <span class="text-lg text-[#C98A3E]">✉️</span>
                            <div>
                                <strong class="text-gray-900 block text-xs">Barua Pepe (Email)</strong>
                                <a :href="'mailto:' + (settings.contact_email || 'info@kitongafarmvillas.com')" class="text-gray-600 hover:text-[#C98A3E] transition mt-0.5 inline-block">
                                    {{ settings.contact_email || 'info@kitongafarmvillas.com' }}
                                </a>
                            </div>
                        </div>

                        <div class="flex items-start gap-3.5 pt-3 border-t border-gray-150">
                            <span class="text-lg text-[#C98A3E]">📍</span>
                            <div>
                                <strong class="text-gray-900 block text-xs">Mahali Lilipo Shamba</strong>
                                <span class="text-gray-600 block mt-0.5">Komkonga Village, Tanga Region, Tanzania</span>
                            </div>
                        </div>

                        <div class="flex items-start gap-3.5 pt-3 border-t border-gray-150">
                            <span class="text-lg text-[#C98A3E]">🕒</span>
                            <div>
                                <strong class="text-gray-900 block text-xs">Muda wa Huduma</strong>
                                <span class="text-gray-600 block mt-0.5">Jumatatu – Jumapili (Saa 24 kwa wageni waliopo)</span>
                            </div>
                        </div>

                    </div>

                </div>

                <!-- Right Column: Simple Clean Form -->
                <div class="md:col-span-7 bg-white p-7 sm:p-9 rounded-2xl border border-gray-200 shadow-xs">
                    
                    <div class="mb-5">
                        <h2 class="font-serif text-2xl font-bold text-[#14231C]">Tuma Ujumbe Wako</h2>
                        <p class="text-xs text-gray-500 mt-1 font-sans">
                            Tafadhali jaza fomu hii fupi na tutakujibu haraka iwezekanavyo:
                        </p>
                    </div>

                    <!-- Success Alert -->
                    <div v-if="isSuccess" class="mb-5 p-3.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-sans">
                        ✓ Ujumbe wako umetumwa kwa mafanikio. Tutawasiliana nawe hivi punde!
                    </div>

                    <form @submit.prevent="submitContact" class="space-y-4 font-sans text-xs">
                        
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Jina Lako Kamili *</label>
                            <input 
                                v-model="form.name" 
                                type="text" 
                                placeholder="Mfano: Juma Rashid"
                                class="w-full text-xs p-2.5 rounded-lg border border-gray-300 focus:outline-none focus:border-[#C98A3E] transition"
                                required 
                            />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block font-semibold text-gray-700 mb-1">Barua Pepe (Email) *</label>
                                <input 
                                    v-model="form.email" 
                                    type="email" 
                                    placeholder="juma@example.com"
                                    class="w-full text-xs p-2.5 rounded-lg border border-gray-300 focus:outline-none focus:border-[#C98A3E] transition"
                                    required 
                                />
                            </div>

                            <div>
                                <label class="block font-semibold text-gray-700 mb-1">Namba ya Simu / WhatsApp</label>
                                <input 
                                    v-model="form.phone" 
                                    type="tel" 
                                    placeholder="+255 7..."
                                    class="w-full text-xs p-2.5 rounded-lg border border-gray-300 focus:outline-none focus:border-[#C98A3E] transition"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Aina ya Ombi / Sababu</label>
                            <select 
                                v-model="form.subject"
                                class="w-full text-xs p-2.5 rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-[#C98A3E] transition"
                            >
                                <option value="Villa Reservation">Kuhifadhi Villa (Booking)</option>
                                <option value="Farm Tour & Experiences">Kutembelea Shamba & Ziara</option>
                                <option value="Produce & Direct Harvest">Kuagiza Mazao ya Shamba</option>
                                <option value="General Concierge Inquiry">Maulizo Mengine ya Jumla</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Ujumbe Wako *</label>
                            <textarea 
                                v-model="form.message" 
                                rows="4"
                                placeholder="Andika maelezo ya ujumbe wako hapa..."
                                class="w-full text-xs p-2.5 rounded-lg border border-gray-300 focus:outline-none focus:border-[#C98A3E] transition"
                                required
                            ></textarea>
                        </div>

                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="w-full py-3 bg-[#14231C] hover:bg-[#C98A3E] text-white text-xs font-bold uppercase tracking-wider rounded-lg transition duration-200 shadow-sm cursor-pointer disabled:opacity-50"
                        >
                            {{ form.processing ? 'Inatuma...' : 'Tuma Ujumbe' }}
                        </button>

                    </form>

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
