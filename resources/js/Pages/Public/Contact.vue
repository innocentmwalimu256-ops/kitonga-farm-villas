<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
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
                    Contact & Inquiries
                </span>
                
                <h1 class="font-serif text-3xl sm:text-4xl md:text-5xl font-semibold tracking-tight text-[#14231C]">
                    Get in Touch with Kitonga Farm Villas
                </h1>
                
                <p class="font-sans text-sm sm:text-base text-gray-600 max-w-2xl mx-auto leading-relaxed">
                    For villa reservations, farm tours, fresh organic produce inquiries, or directions to our estate in Komkonga, our team is always delighted to assist you.
                </p>
            </div>
        </section>

        <!-- 3. NATURAL CONTACT DETAILS & FORM SECTION -->
        <section class="max-w-6xl mx-auto px-6 py-12 md:py-16">
            
            <div class="grid grid-cols-1 md:grid-cols-12 gap-10 lg:gap-14 items-start">
                
                <!-- Left Column: Contact Details -->
                <div class="md:col-span-5 space-y-6">
                    
                    <div class="space-y-2">
                        <h2 class="font-serif text-2xl font-bold text-[#14231C]">Contact Information</h2>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Reach out to our reservations and concierge desk directly:
                        </p>
                    </div>

                    <!-- Direct WhatsApp Button -->
                    <a 
                        :href="'https://wa.me/' + (settings.contact_phone || '+255758774695').replace(/[^0-9]/g, '') + '?text=' + encodeURIComponent('Hello Kitonga Farm Villas, I would like to inquire about reservations and experiences.')"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex items-center gap-3.5 p-4 rounded-xl bg-[#25D366] text-white hover:bg-[#20ba59] transition shadow-sm font-sans"
                    >
                        <svg class="w-6 h-6 fill-current shrink-0" viewBox="0 0 24 24">
                            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.698.074-2.128-.517-1.745-.722-2.888-2.518-2.977-2.637-.086-.118-.707-.941-.707-1.796 0-.854.448-1.275.607-1.448.16-.174.348-.218.465-.218.117 0 .234.001.336.006.107.005.251-.041.393.3.144.347.493 1.202.536 1.29.043.087.072.189.014.304-.058.117-.087.19-.174.29-.087.102-.183.228-.261.306-.089.088-.182.184-.078.362.104.178.463.765.994 1.238.683.608 1.259.797 1.438.885.178.087.283.073.388-.048.106-.12.453-.527.575-.708.121-.182.243-.151.408-.09.166.06 1.054.497 1.235.588.182.09.303.136.348.213.044.076.044.444-.1.849z"/>
                        </svg>
                        <div class="flex-1">
                            <span class="text-xs font-bold block">Chat on WhatsApp</span>
                            <span class="text-[11px] text-white/90">Instant concierge assistance via WhatsApp</span>
                        </div>
                        <span class="text-sm font-bold">↗</span>
                    </a>

                    <!-- Direct Contacts List -->
                    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-xs space-y-5 text-xs font-sans">
                        
                        <div class="flex items-start gap-3.5">
                            <svg class="w-5 h-5 text-[#C98A3E] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <div>
                                <strong class="text-gray-900 block text-xs">Direct Phone Line</strong>
                                <a :href="'tel:' + (settings.contact_phone || '+255758774695').replace(/\s+/g, '')" class="text-gray-600 hover:text-[#C98A3E] transition mt-0.5 inline-block">
                                    {{ settings.contact_phone || '+255 758 774 695' }}
                                </a>
                            </div>
                        </div>

                        <div class="flex items-start gap-3.5 pt-3 border-t border-gray-150">
                            <svg class="w-5 h-5 text-[#C98A3E] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <div>
                                <strong class="text-gray-900 block text-xs">Email Address</strong>
                                <a :href="'mailto:' + (settings.contact_email || 'kitongafarmvillas@gmail.com')" class="text-gray-600 hover:text-[#C98A3E] transition mt-0.5 inline-block">
                                    {{ settings.contact_email || 'kitongafarmvillas@gmail.com' }}
                                </a>
                            </div>
                        </div>

                        <div class="flex items-start gap-3.5 pt-3 border-t border-gray-150">
                            <svg class="w-5 h-5 text-[#C98A3E] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <div>
                                <strong class="text-gray-900 block text-xs">Estate Location</strong>
                                <span class="text-gray-600 block mt-0.5">Komkonga Village, Tanga Region, Tanzania</span>
                            </div>
                        </div>

                        <div class="flex items-start gap-3.5 pt-3 border-t border-gray-150">
                            <svg class="w-5 h-5 text-[#C98A3E] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <strong class="text-gray-900 block text-xs">Service Hours</strong>
                                <span class="text-gray-600 block mt-0.5">Monday – Sunday (24/7 for in-house guests)</span>
                            </div>
                        </div>

                    </div>

                    <!-- Compact Designer Map Card -->
                    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-xs font-sans">
                        <div class="px-4 py-2.5 bg-[#14231C] text-white flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#C98A3E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="font-serif text-xs font-medium text-[#F5F1E8] uppercase tracking-wider">Komkonga, Tanga Map</span>
                            </div>
                            <a 
                                :href="'https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(settings.location_coordinates || '-5.0889,39.0988')" 
                                target="_blank" 
                                rel="noopener noreferrer" 
                                class="inline-flex items-center gap-1 text-[10px] font-bold text-[#E6C387] hover:text-white transition uppercase tracking-wider"
                            >
                                <span>Open in Maps</span>
                                <span>↗</span>
                            </a>
                        </div>
                        
                        <!-- Compact Map Frame -->
                        <div class="relative w-full h-44 bg-[#e5e3df]">
                            <iframe
                                class="w-full h-full border-0"
                                :src="'https://maps.google.com/maps?q=' + (settings.location_coordinates || '-5.0889,39.0988') + '&hl=en&z=13&output=embed'"
                                loading="lazy"
                                allowfullscreen
                                referrerpolicy="no-referrer-when-downgrade"
                                title="Kitonga Location Map"
                            ></iframe>
                        </div>

                        <!-- Micro Travel Badges -->
                        <div class="px-3.5 py-2.5 bg-[#FAF8F5] border-t border-gray-150 flex flex-wrap items-center justify-between gap-2 text-[10px] text-gray-600">
                            <span class="inline-flex items-center gap-1">
                                <span class="font-semibold text-gray-800">Tanga:</span> 45 min
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <span class="font-semibold text-gray-800">Segera:</span> 1h 15m
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <span class="font-semibold text-gray-800">Transfer:</span> available
                            </span>
                        </div>
                    </div>

                </div>

                <!-- Right Column: Simple Clean Form -->
                <div class="md:col-span-7 bg-white p-7 sm:p-9 rounded-2xl border border-gray-200 shadow-xs">
                    
                    <div class="mb-5">
                        <h2 class="font-serif text-2xl font-bold text-[#14231C]">Send Us a Message</h2>
                        <p class="text-xs text-gray-500 mt-1 font-sans">
                            Please fill out the form below and our team will get back to you promptly:
                        </p>
                    </div>

                    <!-- Success Alert -->
                    <div v-if="isSuccess" class="mb-5 p-3.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-sans">
                        ✓ Your message has been sent successfully. We will get back to you shortly!
                    </div>

                    <form @submit.prevent="submitContact" class="space-y-4 font-sans text-xs">
                        
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Full Name *</label>
                            <input 
                                v-model="form.name" 
                                type="text" 
                                placeholder="e.g. Johnathan Smith"
                                class="w-full text-xs p-2.5 rounded-lg border border-gray-300 focus:outline-none focus:border-[#C98A3E] transition"
                                required 
                            />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block font-semibold text-gray-700 mb-1">Email Address *</label>
                                <input 
                                    v-model="form.email" 
                                    type="email" 
                                    placeholder="john@example.com"
                                    class="w-full text-xs p-2.5 rounded-lg border border-gray-300 focus:outline-none focus:border-[#C98A3E] transition"
                                    required 
                                />
                            </div>

                            <div>
                                <label class="block font-semibold text-gray-700 mb-1">Phone Number / WhatsApp</label>
                                <input 
                                    v-model="form.phone" 
                                    type="tel" 
                                    placeholder="+255 7..."
                                    class="w-full text-xs p-2.5 rounded-lg border border-gray-300 focus:outline-none focus:border-[#C98A3E] transition"
                                    required 
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Inquiry Type</label>
                            <select 
                                v-model="form.subject"
                                class="w-full text-xs p-2.5 rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-[#C98A3E] transition"
                            >
                                <option value="Villa Reservation">Villa Reservation</option>
                                <option value="Farm Tour & Experiences">Farm Tour & Experiences</option>
                                <option value="Produce & Direct Harvest">Produce & Direct Harvest</option>
                                <option value="General Concierge Inquiry">General Concierge Inquiry</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Your Message *</label>
                            <textarea 
                                v-model="form.message" 
                                rows="4"
                                placeholder="Write your message or inquiry details here..."
                                class="w-full text-xs p-2.5 rounded-lg border border-gray-300 focus:outline-none focus:border-[#C98A3E] transition"
                                required
                            ></textarea>
                        </div>

                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="w-full py-3 bg-[#14231C] hover:bg-[#C98A3E] text-white text-xs font-bold uppercase tracking-wider rounded-lg transition duration-200 shadow-sm cursor-pointer disabled:opacity-50"
                        >
                            {{ form.processing ? 'Sending...' : 'Send Message' }}
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
