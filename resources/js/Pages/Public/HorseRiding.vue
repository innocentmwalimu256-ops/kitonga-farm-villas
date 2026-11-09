<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import SEOHead from '@/Components/SEOHead.vue';
import PublicNavbar from '@/Components/PublicNavbar.vue';

const props = defineProps({
    settings: {
        type: Object,
        default: () => ({
            contact_phone: '+255 758 774 695',
            contact_email: 'info@kitongafarmvillas.com',
            whatsapp_number: '255758774695',
            location: 'Kitonga Farm, Komkonga Village, Tanga Region, Tanzania',
        }),
    },
    experience: {
        type: Object,
        default: () => ({
            title: 'Horse Riding Experience at Kitonga Farm',
            rate_per_person_per_hour: 50000,
            currency: 'TZS',
            location: 'Kitonga Farm, Komkonga, Tanga, Tanzania',
            opening_hours: 'Daily 08:00 AM – 06:00 PM',
            what_to_bring: 'Comfortable riding trousers, closed shoes or boots, and sun protection.',
            good_to_know: 'Advance booking recommended. Safety orientation provided before every session.',
        }),
    },
    defaultDate: {
        type: String,
        default: () => {
            const d = new Date();
            d.setDate(d.getDate() + 1);
            return d.toISOString().split('T')[0];
        },
    },
    hasCustomVideo: {
        type: Boolean,
        default: false,
    },
});

// Mobile navigation menu state
const isMobileMenuOpen = ref(false);
const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
};
const closeMobileMenu = () => {
    isMobileMenuOpen.value = false;
};

// Video playback handling
const heroVideoRef = ref(null);
onMounted(() => {
    if (heroVideoRef.value) {
        heroVideoRef.value.play().catch(() => {
            // Autoplay with audio may be blocked, keep muted and retry
            if (heroVideoRef.value) {
                heroVideoRef.value.muted = true;
                heroVideoRef.value.play().catch(() => {});
            }
        });
    }
});

// Booking Form State
const form = ref({
    fullName: '',
    phoneNumber: '',
    guestsCount: 1,
    durationHours: 1,
    preferredDate: props.defaultDate || '',
    preferredTime: '09:30 AM',
    message: '',
});

const formErrors = ref({});
const isSubmitting = ref(false);

const timeSlots = [
    '08:00 AM - Morning Trail',
    '09:30 AM - Morning Breeze',
    '11:00 AM - Late Morning',
    '02:00 PM - Afternoon Tour',
    '03:30 PM - Golden Afternoon',
    '05:00 PM - Sunset Ride',
];

// Dynamic Price Calculation: Total = Guests * Hours * 50,000
const ratePerHour = computed(() => Number(props.experience?.rate_per_person_per_hour) || 50000);

const calculatedTotal = computed(() => {
    const guests = Math.max(1, Number(form.value.guestsCount) || 1);
    const hours = Math.max(1, Number(form.value.durationHours) || 1);
    return guests * hours * ratePerHour.value;
});

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-TZ', {
        style: 'currency',
        currency: 'TZS',
        maximumFractionDigits: 0
    }).format(val || 0);
};

// Smooth Scroll Helper
const scrollToSection = (id) => {
    const el = document.getElementById(id);
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
};

// WhatsApp Direct Booking Generator
const handleWhatsAppBooking = () => {
    formErrors.value = {};

    if (!form.value.fullName || form.value.fullName.trim().length < 2) {
        formErrors.value.fullName = 'Please enter your full name.';
    }

    if (!form.value.phoneNumber || form.value.phoneNumber.trim().length < 8) {
        formErrors.value.phoneNumber = 'Please enter a valid phone number.';
    }

    if (!form.value.preferredDate) {
        formErrors.value.preferredDate = 'Please select your preferred date.';
    }

    if (!form.value.preferredTime) {
        formErrors.value.preferredTime = 'Please select a preferred time slot.';
    }

    if (Object.keys(formErrors.value).length > 0) {
        // Scroll to the first error in the booking form
        scrollToSection('booking-form-card');
        return;
    }

    isSubmitting.value = true;

    const formattedTotalStr = formatCurrency(calculatedTotal.value);
    const guestLabel = form.value.guestsCount == 1 ? '1 Guest' : `${form.value.guestsCount} Guests`;
    const hourLabel = form.value.durationHours == 1 ? '1 Hour' : `${form.value.durationHours} Hours`;

    const messageLines = [
        `*🌿 BOOKING INQUIRY: HORSE RIDING AT KITONGA FARM*`,
        ``,
        `Hello Kitonga Farm Villas Team! I would like to book a Horse Riding Experience:`,
        ``,
        `👤 *Full Name:* ${form.value.fullName.trim()}`,
        `📞 *Phone Number:* ${form.value.phoneNumber.trim()}`,
        `👥 *Number of Guests:* ${guestLabel}`,
        `⏱ *Duration:* ${hourLabel}`,
        `📅 *Preferred Date:* ${form.value.preferredDate}`,
        `⏰ *Preferred Time:* ${form.value.preferredTime}`,
        `💰 *Estimated Total:* ${formattedTotalStr}`,
    ];

    if (form.value.message && form.value.message.trim().length > 0) {
        messageLines.push(`📝 *Special Requests / Message:* ${form.value.message.trim()}`);
    }

    messageLines.push(``);
    messageLines.push(`Please let me know if this slot is available. Thank you!`);

    const fullMessage = messageLines.join('\n');
    const targetPhone = props.settings?.whatsapp_number || '255758774695';
    const whatsappUrl = `https://wa.me/${targetPhone}?text=${encodeURIComponent(fullMessage)}`;

    // Open WhatsApp in a new tab
    window.open(whatsappUrl, '_blank');

    setTimeout(() => {
        isSubmitting.value = false;
    }, 1500);
};

// Structured Schema.org for SEO
const schemaData = computed(() => ({
    '@context': 'https://schema.org',
    '@type': 'TouristAttraction',
    'name': 'Enjoy Horse Riding at Kitonga Farm',
    'description': 'Enjoy a memorable horse riding experience at Kitonga Farm. Explore nature on horseback and book your experience for TSh 50,000 per person per hour.',
    'url': 'https://kitongafarm.com/experiences/horse-riding',
    'image': 'https://kitongafarm.com/images/horse-riding/horse-01.jpg',
    'isAccessibleForFree': false,
    'offers': {
        '@type': 'Offer',
        'price': '50000',
        'priceCurrency': 'TZS',
        'availability': 'https://schema.org/InStock',
        'validFrom': '2026-01-01'
    },
    'location': {
        '@type': 'Place',
        'name': 'Kitonga Farm Villas',
        'address': {
            '@type': 'PostalAddress',
            'streetAddress': 'Komkonga Village, Tanga Region',
            'addressLocality': 'Tanga',
            'addressRegion': 'Tanga',
            'addressCountry': 'TZ'
        }
    }
}));
</script>

<template>
    <SEOHead 
        title="Horse Riding Experience at Kitonga Farm | Enjoy Horse Riding"
        description="Enjoy a memorable horse riding experience at Kitonga Farm. Explore nature on horseback and book your experience for TSh 50,000 per person per hour."
        canonical="/experiences/horse-riding"
        og-image="/images/horse-riding/horse-01.jpg"
        og-type="article"
        :schema="schemaData"
    />

    <div class="min-h-screen bg-[#F5F0E6] text-[#252525] font-sans antialiased selection:bg-[#C99A43] selection:text-white">
        
        <!-- =========================================================================
             1. GLOBAL LUXURY NAVIGATION (UNIFIED SITE NAVBAR)
             Horse Riding sits right after Meet Mr. Kitonga in the menu hierarchy
        ========================================================================= -->
        <PublicNavbar current-page="horse-riding" :settings="settings" />

        <!-- =========================================================================
             2. HERO SECTION – CINEMATIC VIDEO (LARGE, FULL & PURE VIDEO)
             Large, full-width cinematic hero video, pure video with NO text overlay
        ========================================================================= -->
        <section class="relative w-full h-[60vh] sm:h-[70vh] md:h-[80vh] max-h-[850px] min-h-[420px] bg-[#071F13] overflow-hidden">
            <!-- Background Video: Exact segment from client original video (01:02 to 01:23) -->
            <video 
                ref="heroVideoRef"
                class="w-full h-full object-cover object-center"
                autoplay 
                muted 
                loop 
                playsinline
                webkit-playsinline
                x5-playsinline
                preload="metadata"
                poster="/images/horse-riding/horse-riding-hero-poster.jpg"
            >
                <source src="/videos/horse-riding-main.mp4" type="video/mp4" />
                <source src="/stream/horse-riding-video" type="video/mp4" />
            </video>
        </section>

        <!-- =========================================================================
             3. MAIN CONTENT LAYOUT
             Recreates the 3-column / 66%-34% desktop composition:
             - Left (approx 66% width): Introduction, Highlights, Pricing Card
             - Right (approx 34% width): Complete Booking Form with Dynamic Calculation
        ========================================================================= -->
        <main id="experience-overview" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-14">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">
                
                <!-- =====================================================================
                     LEFT CONTENT COLUMN (~66% Width / 8 of 12 columns)
                ===================================================================== -->
                <div class="lg:col-span-8 space-y-10 sm:space-y-12">
                    
                    <!-- SECTION A: INTRODUCTION -->
                    <section class="bg-white rounded-2xl p-6 sm:p-8 border border-[#E5E0D8] shadow-xs">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 md:gap-8 items-center">
                            
                            <!-- Left Photo: horse-01.jpg -->
                            <div class="md:col-span-5 overflow-hidden rounded-xl bg-[#EFE8D9] border border-[#E5E0D8] shadow-xs aspect-[4/3] md:aspect-square group">
                                <img 
                                    src="/images/horse-riding/horse-01.jpg" 
                                    alt="Horse Riding at Kitonga Farm" 
                                    class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                                    loading="eager"
                                />
                            </div>

                            <!-- Right Content: Introduction Text -->
                            <div class="md:col-span-7 space-y-3.5">
                                <div>
                                    <h2 class="font-serif text-2xl sm:text-3xl font-bold text-[#092B18] leading-tight">
                                        Discover the Joy of Horse Riding
                                    </h2>
                                    <!-- Decorative Gold Underline -->
                                    <div class="w-12 h-0.5 bg-[#C99A43] mt-2 mb-3"></div>
                                </div>

                                <p class="text-xs sm:text-sm text-[#252525]/85 leading-relaxed">
                                    At Kitonga Farm, horse riding is more than an activity – it is an experience. Enjoy the beauty of nature, fresh air and memorable moments as you explore the countryside on horseback.
                                </p>

                                <p class="text-xs sm:text-sm text-[#252525]/85 leading-relaxed">
                                    Whether you are looking for a peaceful outdoor experience or an enjoyable adventure, discover the joy of horse riding at Kitonga Farm.
                                </p>

                                <div class="pt-2">
                                    <button 
                                        type="button" 
                                        @click="scrollToSection('experience-highlights')"
                                        class="px-5 py-2 bg-[#092B18] hover:bg-[#071F13] text-white text-xs font-bold uppercase tracking-wider rounded transition cursor-pointer"
                                    >
                                        LEARN MORE
                                    </button>
                                </div>
                            </div>

                        </div>
                    </section>

                    <!-- SECTION B: EXPERIENCE HIGHLIGHTS -->
                    <section id="experience-highlights" class="bg-white rounded-2xl p-6 sm:p-8 border border-[#E5E0D8] shadow-xs">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 md:gap-8 items-center">
                            
                            <!-- Left Photo: horse-02.jpg -->
                            <div class="md:col-span-5 overflow-hidden rounded-xl bg-[#EFE8D9] border border-[#E5E0D8] shadow-xs aspect-[4/3] md:aspect-square group">
                                <img 
                                    src="/images/horse-riding/horse-02.jpg" 
                                    alt="Countryside Horse Riding Tour" 
                                    class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                                    loading="eager"
                                />
                            </div>

                            <!-- Right Content: Highlights and 3 Feature Icons -->
                            <div class="md:col-span-7 space-y-4">
                                <div>
                                    <h2 class="font-serif text-2xl sm:text-3xl font-bold text-[#092B18] leading-tight">
                                        An Experience Worth Remembering
                                    </h2>
                                    <!-- Decorative Gold Underline -->
                                    <div class="w-12 h-0.5 bg-[#C99A43] mt-2 mb-3"></div>
                                </div>

                                <p class="text-xs sm:text-sm text-[#252525]/85 leading-relaxed">
                                    Immerse yourself in scenic trails, fresh farm breeze, and gentle horses in a serene countryside environment.
                                </p>

                                <!-- Three Feature Icons -->
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                                    
                                    <!-- Feature 1: Guided Experience -->
                                    <div class="p-3 rounded-xl bg-[#F5F0E6]/80 border border-[#E5DFD4] space-y-1.5 text-center sm:text-left">
                                        <div class="w-8 h-8 rounded-lg bg-[#092B18] text-[#C99A43] flex items-center justify-center mx-auto sm:mx-0 shadow-xs">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                        <h4 class="text-xs font-bold text-[#092B18] uppercase tracking-wide">Guided Experience</h4>
                                        <p class="text-[11px] text-[#252525]/70 leading-snug">Personal handler support and orientation.</p>
                                    </div>

                                    <!-- Feature 2: Beautiful Countryside -->
                                    <div class="p-3 rounded-xl bg-[#F5F0E6]/80 border border-[#E5DFD4] space-y-1.5 text-center sm:text-left">
                                        <div class="w-8 h-8 rounded-lg bg-[#092B18] text-[#C99A43] flex items-center justify-center mx-auto sm:mx-0 shadow-xs">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064" />
                                            </svg>
                                        </div>
                                        <h4 class="text-xs font-bold text-[#092B18] uppercase tracking-wide">Beautiful Countryside</h4>
                                        <p class="text-[11px] text-[#252525]/70 leading-snug">Scenic trails across lush farm landscapes.</p>
                                    </div>

                                    <!-- Feature 3: Safety First -->
                                    <div class="p-3 rounded-xl bg-[#F5F0E6]/80 border border-[#E5DFD4] space-y-1.5 text-center sm:text-left">
                                        <div class="w-8 h-8 rounded-lg bg-[#092B18] text-[#C99A43] flex items-center justify-center mx-auto sm:mx-0 shadow-xs">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                            </svg>
                                        </div>
                                        <h4 class="text-xs font-bold text-[#092B18] uppercase tracking-wide">Safety First</h4>
                                        <p class="text-[11px] text-[#252525]/70 leading-snug">Riding gear, gentle horses, &amp; safety briefing.</p>
                                    </div>

                                </div>

                            </div>

                        </div>
                    </section>

                    <!-- SECTION C: PRICING CARD -->
                    <!-- Matches reference: Dark-green background (#092B18/#071F13), gold borders (#C99A43), gold typography -->
                    <section class="bg-gradient-to-br from-[#092B18] via-[#071F13] to-[#04140C] rounded-2xl p-6 sm:p-8 border-2 border-[#C99A43] shadow-xl text-white space-y-6">
                        
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#C99A43]/30 pb-6">
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-[0.25em] text-[#C99A43] block">
                                    OFFICIAL EXPERIENCE RATE
                                </span>
                                <h3 class="font-serif text-2xl sm:text-3xl font-bold text-white mt-1">
                                    HORSE RIDING EXPERIENCE
                                </h3>
                                <p class="text-xs text-[#F5F0E6]/75 mt-1">
                                    Enjoy a memorable horse riding experience at Kitonga Farm.
                                </p>
                            </div>

                            <!-- Price Tag -->
                            <div class="sm:text-right bg-white/5 sm:bg-transparent p-4 sm:p-0 rounded-xl border sm:border-0 border-white/10">
                                <div class="font-serif text-3xl sm:text-4xl font-bold text-[#C99A43]">
                                    TSh 50,000
                                </div>
                                <div class="text-[10px] sm:text-[11px] font-bold uppercase tracking-[0.18em] text-[#F5F0E6]/80 mt-0.5">
                                    PER PERSON / PER HOUR
                                </div>
                            </div>
                        </div>

                        <!-- Verified Inclusions with Gold Check Icons -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs text-[#F5F0E6]/90">
                            
                            <div class="flex items-center gap-2.5">
                                <span class="w-5 h-5 rounded-full bg-[#C99A43]/20 border border-[#C99A43] flex items-center justify-center text-[#C99A43] shrink-0">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                </span>
                                <span>1 Hour Scenic Countryside Trail Ride</span>
                            </div>

                            <div class="flex items-center gap-2.5">
                                <span class="w-5 h-5 rounded-full bg-[#C99A43]/20 border border-[#C99A43] flex items-center justify-center text-[#C99A43] shrink-0">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                </span>
                                <span>Dedicated Trail Guide &amp; Handler</span>
                            </div>

                            <div class="flex items-center gap-2.5">
                                <span class="w-5 h-5 rounded-full bg-[#C99A43]/20 border border-[#C99A43] flex items-center justify-center text-[#C99A43] shrink-0">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                </span>
                                <span>Safety Briefing &amp; Riding Gear Included</span>
                            </div>

                            <div class="flex items-center gap-2.5">
                                <span class="w-5 h-5 rounded-full bg-[#C99A43]/20 border border-[#C99A43] flex items-center justify-center text-[#C99A43] shrink-0">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                </span>
                                <span>Suitable for Beginners &amp; Experienced Riders</span>
                            </div>

                            <div class="flex items-center gap-2.5">
                                <span class="w-5 h-5 rounded-full bg-[#C99A43]/20 border border-[#C99A43] flex items-center justify-center text-[#C99A43] shrink-0">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                </span>
                                <span>Serene Nature Trails &amp; Panoramic Photo Stops</span>
                            </div>

                            <div class="flex items-center gap-2.5">
                                <span class="w-5 h-5 rounded-full bg-[#C99A43]/20 border border-[#C99A43] flex items-center justify-center text-[#C99A43] shrink-0">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                </span>
                                <span>Complimentary Chilled Farm Mineral Water</span>
                            </div>

                        </div>

                        <!-- Card CTA Button -->
                        <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <span class="text-xs text-[#F5F0E6]/70 italic">
                                *All rides require advance reservation for horse and handler readiness.
                            </span>
                            <button 
                                type="button" 
                                @click="scrollToSection('booking-form-card')"
                                class="w-full sm:w-auto px-8 py-3 bg-[#C99A43] hover:bg-[#d8a952] active:bg-[#b88935] text-[#071F13] font-bold text-xs uppercase tracking-wider rounded-xl shadow transition duration-300 cursor-pointer"
                            >
                                BOOK NOW
                            </button>
                        </div>

                    </section>

                </div>

                <!-- =====================================================================
                     RIGHT BOOKING COLUMN (~34% Width / 4 of 12 columns)
                     Matches reference: Clean, elegant booking form on right with
                     2 columns per field row, dynamic total calculation, WhatsApp button.
                ===================================================================== -->
                <div class="lg:col-span-4">
                    <div id="booking-form-card" class="bg-white rounded-2xl p-6 sm:p-7 border border-[#E5E0D8] shadow-sm sticky top-24 space-y-5">
                        
                        <!-- Header -->
                        <div class="border-b border-[#E5E0D8] pb-4">
                            <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#C99A43] block">
                                DIRECT INQUIRY
                            </span>
                            <h3 class="font-serif text-xl sm:text-2xl font-bold text-[#092B18] mt-0.5">
                                Ready for Your Horse Riding Experience?
                            </h3>
                            <p class="text-xs text-[#252525]/70 mt-1">
                                Complete your details below to inquire directly via WhatsApp.
                            </p>
                        </div>

                        <!-- Booking Form Fields -->
                        <form @submit.prevent="handleWhatsAppBooking" class="space-y-4 text-xs">
                            
                            <!-- ROW 1: Full Name & Phone Number (2 columns) -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-[#092B18] mb-1">
                                        Full Name <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        v-model="form.fullName" 
                                        placeholder="e.g. Salum Said" 
                                        class="w-full px-3 py-2 rounded-xl border text-xs bg-[#FAF8F5] focus:bg-white focus:outline-none transition"
                                        :class="formErrors.fullName ? 'border-red-400 ring-1 ring-red-400' : 'border-[#D9D2C5] focus:border-[#C99A43] focus:ring-1 focus:ring-[#C99A43]'"
                                        required
                                    />
                                    <span v-if="formErrors.fullName" class="text-[10px] text-red-600 block mt-0.5">{{ formErrors.fullName }}</span>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-[#092B18] mb-1">
                                        Phone Number <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="tel" 
                                        v-model="form.phoneNumber" 
                                        placeholder="e.g. 0712 345 678" 
                                        class="w-full px-3 py-2 rounded-xl border text-xs bg-[#FAF8F5] focus:bg-white focus:outline-none transition"
                                        :class="formErrors.phoneNumber ? 'border-red-400 ring-1 ring-red-400' : 'border-[#D9D2C5] focus:border-[#C99A43] focus:ring-1 focus:ring-[#C99A43]'"
                                        required
                                    />
                                    <span v-if="formErrors.phoneNumber" class="text-[10px] text-red-600 block mt-0.5">{{ formErrors.phoneNumber }}</span>
                                </div>
                            </div>

                            <!-- ROW 2: Number of Guests & Riding Duration (2 columns) -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-[#092B18] mb-1">
                                        Number of Guests
                                    </label>
                                    <select 
                                        v-model="form.guestsCount" 
                                        class="w-full px-3 py-2 rounded-xl border border-[#D9D2C5] text-xs bg-[#FAF8F5] focus:bg-white focus:border-[#C99A43] focus:outline-none"
                                    >
                                        <option v-for="n in 8" :key="n" :value="n">
                                            {{ n }} {{ n === 1 ? 'Guest' : 'Guests' }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-[#092B18] mb-1">
                                        Riding Duration
                                    </label>
                                    <select 
                                        v-model="form.durationHours" 
                                        class="w-full px-3 py-2 rounded-xl border border-[#D9D2C5] text-xs bg-[#FAF8F5] focus:bg-white focus:border-[#C99A43] focus:outline-none"
                                    >
                                        <option :value="1">1 Hour (Standard)</option>
                                        <option :value="2">2 Hours (Extended)</option>
                                        <option :value="3">3 Hours (Half Day)</option>
                                        <option :value="4">4 Hours (Adventure)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- ROW 3: Preferred Date & Preferred Time (2 columns) -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-[#092B18] mb-1">
                                        Preferred Date <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="date" 
                                        v-model="form.preferredDate" 
                                        :min="defaultDate"
                                        class="w-full px-3 py-2 rounded-xl border text-xs bg-[#FAF8F5] focus:bg-white focus:outline-none transition"
                                        :class="formErrors.preferredDate ? 'border-red-400 ring-1 ring-red-400' : 'border-[#D9D2C5] focus:border-[#C99A43] focus:ring-1 focus:ring-[#C99A43]'"
                                        required
                                    />
                                    <span v-if="formErrors.preferredDate" class="text-[10px] text-red-600 block mt-0.5">{{ formErrors.preferredDate }}</span>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-[#092B18] mb-1">
                                        Preferred Time <span class="text-red-500">*</span>
                                    </label>
                                    <select 
                                        v-model="form.preferredTime" 
                                        class="w-full px-3 py-2 rounded-xl border text-xs bg-[#FAF8F5] focus:bg-white focus:outline-none transition"
                                        :class="formErrors.preferredTime ? 'border-red-400 ring-1 ring-red-400' : 'border-[#D9D2C5] focus:border-[#C99A43] focus:ring-1 focus:ring-[#C99A43]'"
                                        required
                                    >
                                        <option v-for="slot in timeSlots" :key="slot" :value="slot">
                                            {{ slot }}
                                        </option>
                                    </select>
                                    <span v-if="formErrors.preferredTime" class="text-[10px] text-red-600 block mt-0.5">{{ formErrors.preferredTime }}</span>
                                </div>
                            </div>

                            <!-- ROW 4: Additional Message (Full width) -->
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-[#092B18] mb-1">
                                    Additional Message (Optional)
                                </label>
                                <textarea 
                                    v-model="form.message" 
                                    rows="3" 
                                    placeholder="Rider skill level, children accompanying, or special requests..." 
                                    class="w-full px-3 py-2 rounded-xl border border-[#D9D2C5] text-xs bg-[#FAF8F5] focus:bg-white focus:border-[#C99A43] focus:outline-none resize-none"
                                ></textarea>
                            </div>

                            <!-- DYNAMIC PRICE CALCULATION SUMMARY BOX -->
                            <div class="bg-[#F8F5EE] border border-[#E5DFD4] rounded-xl p-4 space-y-2">
                                <div class="flex items-center justify-between text-xs text-[#252525]/75">
                                    <span>Rate Per Person:</span>
                                    <span class="font-semibold text-[#092B18]">{{ formatCurrency(ratePerHour) }} / hr</span>
                                </div>
                                <div class="flex items-center justify-between text-xs text-[#252525]/75">
                                    <span>Calculation:</span>
                                    <span class="font-mono text-[11px]">
                                        {{ form.guestsCount }} {{ form.guestsCount == 1 ? 'Guest' : 'Guests' }} × {{ form.durationHours }} {{ form.durationHours == 1 ? 'Hr' : 'Hrs' }}
                                    </span>
                                </div>
                                <div class="border-t border-[#E5DFD4] pt-2 flex items-center justify-between">
                                    <span class="font-serif font-bold text-sm text-[#092B18]">Estimated Total:</span>
                                    <span class="font-serif text-lg font-bold text-[#092B18]">
                                        {{ formatCurrency(calculatedTotal) }}
                                    </span>
                                </div>
                            </div>

                            <!-- FULL-WIDTH WHATSAPP BOOKING BUTTON -->
                            <button 
                                type="submit" 
                                :disabled="isSubmitting"
                                class="w-full py-3.5 px-4 bg-[#092B18] hover:bg-[#143B23] active:bg-[#071F13] text-white rounded-xl font-bold text-xs tracking-wider uppercase transition shadow-md flex items-center justify-center gap-2.5 cursor-pointer disabled:opacity-75"
                            >
                                <svg class="w-5 h-5 fill-current text-[#25D366]" viewBox="0 0 24 24">
                                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.275.072.376-.044c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.155.57 4.179 1.564 5.927l-1.564 5.717 5.86-1.536c1.693.923 3.633 1.458 5.704 1.458 6.627 0 12-5.373 12-12 0-6.627-5.373-12-12-12z" />
                                </svg>
                                <span>{{ isSubmitting ? 'Opening WhatsApp...' : 'BOOK VIA WHATSAPP' }}</span>
                            </button>

                            <!-- Subtext Note -->
                            <div class="text-center space-y-1 pt-1">
                                <p class="text-[11px] text-[#252525]/75 font-medium">
                                    Booking confirmation will be provided through WhatsApp.
                                </p>
                                <p class="text-[10px] text-[#252525]/50 leading-snug">
                                    Inquiry only — reservations depend on horse &amp; handler schedule confirmation.
                                </p>
                            </div>

                        </form>

                    </div>
                </div>

            </div>
        </main>

        <!-- =========================================================================
             4. FOOTER
             Matches reference: Dark-green background (#071F13), 5-column layout:
             Column 1: Brand (Crest, description, social icons)
             Column 2: Navigation
             Column 3: Experiences
             Column 4: Contact Us
             Column 5: Newsletter / Concierge
             Bottom: Copyright line.
        ========================================================================= -->
        <footer class="bg-[#071F13] text-[#F5F0E6]/80 pt-16 pb-12 border-t border-white/10 font-sans text-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                
                <!-- 5 Columns Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-8 sm:gap-10">
                    
                    <!-- COLUMN 1: BRAND -->
                    <div class="space-y-4 lg:col-span-1">
                        <Link :href="route('home')" class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full border border-[#C99A43]/50 bg-[#092B18] flex items-center justify-center p-1.5">
                                <img src="/favicon.svg" alt="Kitonga Crest Logo" class="w-full h-full object-contain" />
                            </div>
                            <span class="font-serif text-sm font-bold tracking-[0.2em] text-[#F5F0E6] uppercase">
                                KITONGA FARM
                            </span>
                        </Link>
                        <p class="text-xs text-[#F5F0E6]/70 leading-relaxed font-light">
                            A luxury countryside retreat and integrated agritourism estate nestled in the scenic highlands of Tanga.
                        </p>
                        <!-- Social Media Icons -->
                        <div class="flex items-center gap-3 pt-1 text-[#C99A43]">
                            <a 
                                :href="`https://wa.me/${settings.whatsapp_number || '255758774695'}`" 
                                target="_blank" 
                                class="w-7 h-7 rounded-full bg-white/5 hover:bg-[#C99A43] hover:text-[#071F13] flex items-center justify-center transition"
                                title="Chat on WhatsApp"
                            >
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.275.072.376-.044c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.155.57 4.179 1.564 5.927l-1.564 5.717 5.86-1.536c1.693.923 3.633 1.458 5.704 1.458 6.627 0 12-5.373 12-12 0-6.627-5.373-12-12-12z" />
                                </svg>
                            </a>
                            <a 
                                href="https://instagram.com" 
                                target="_blank" 
                                class="w-7 h-7 rounded-full bg-white/5 hover:bg-[#C99A43] hover:text-[#071F13] flex items-center justify-center transition"
                                title="Instagram"
                            >
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- COLUMN 2: NAVIGATION -->
                    <div class="space-y-3">
                        <h4 class="text-[11px] font-bold uppercase tracking-[0.2em] text-[#C99A43]">
                            NAVIGATION
                        </h4>
                        <ul class="space-y-2 text-[#F5F0E6]/75">
                            <li><Link :href="route('home')" class="hover:text-white transition">Home</Link></li>
                            <li><Link :href="route('about')" class="hover:text-white transition">About Us</Link></li>
                            <li><Link :href="route('experiences')" class="hover:text-white transition">Experiences</Link></li>
                            <li><Link :href="route('contact')" class="hover:text-white transition">Contact</Link></li>
                            <li><Link :href="route('policies')" class="hover:text-white transition">Terms &amp; Policies</Link></li>
                        </ul>
                    </div>

                    <!-- COLUMN 3: EXPERIENCES -->
                    <div class="space-y-3">
                        <h4 class="text-[11px] font-bold uppercase tracking-[0.2em] text-[#C99A43]">
                            EXPERIENCES
                        </h4>
                        <ul class="space-y-2 text-[#F5F0E6]/75">
                            <li><Link :href="route('experiences')" class="hover:text-white transition">Farm Day Tours</Link></li>
                            <li><Link :href="route('experiences.horse-riding')" class="text-[#C99A43] font-semibold">Horse Riding</Link></li>
                            <li><Link :href="route('experiences')" class="hover:text-white transition">Greenhouse Exploration</Link></li>
                            <li><Link :href="route('meet.mr.kitonga')" class="hover:text-white transition">Meet Mr. Kitonga</Link></li>
                            <li><Link :href="route('farm')" class="hover:text-white transition">Agroforestry Walks</Link></li>
                        </ul>
                    </div>

                    <!-- COLUMN 4: CONTACT US (NO EMOJIS, CLEAN LUXURY TYPOGRAPHY) -->
                    <div class="space-y-3">
                        <h4 class="text-[11px] font-bold uppercase tracking-[0.2em] text-[#C99A43]">
                            CONTACT US
                        </h4>
                        <ul class="space-y-2 text-[#F5F0E6]/75">
                            <li>
                                <a :href="`tel:${settings.contact_phone || '+255758774695'}`" class="hover:text-white transition block">
                                    {{ settings.contact_phone || '+255 758 774 695' }}
                                </a>
                            </li>
                            <li>
                                <a :href="`https://wa.me/${settings.whatsapp_number || '255758774695'}`" target="_blank" class="hover:text-white transition block">
                                    WhatsApp Concierge
                                </a>
                            </li>
                            <li>
                                <a :href="`mailto:${settings.contact_email || 'kitongafarmvillas@gmail.com'}`" class="hover:text-white transition block break-all">
                                    {{ settings.contact_email || 'kitongafarmvillas@gmail.com' }}
                                </a>
                            </li>
                            <li>
                                <span>Komkonga Village, Tanga Region, Tanzania</span>
                            </li>
                        </ul>
                    </div>

                    <!-- COLUMN 5: NEWSLETTER / CONCIERGE -->
                    <div class="space-y-3">
                        <h4 class="text-[11px] font-bold uppercase tracking-[0.2em] text-[#C99A43]">
                            STAY CONCIERGE
                        </h4>
                        <p class="text-xs text-[#F5F0E6]/70 leading-relaxed font-light">
                            Plan your countryside getaway with our dedicated front-desk concierge.
                        </p>
                        <div class="pt-1">
                            <a 
                                :href="`https://wa.me/${settings.whatsapp_number || '255758774695'}?text=${encodeURIComponent('Hello Kitonga Farm Villas Concierge! I would like to inquire about stays and experiences.')}`"
                                target="_blank"
                                class="inline-flex items-center gap-2 px-3.5 py-2 bg-[#092B18] hover:bg-[#C99A43] text-[#F5F0E6] hover:text-[#071F13] rounded-lg border border-[#C99A43]/40 transition text-xs font-semibold shadow-xs"
                            >
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span>Direct Chat Support</span>
                            </a>
                        </div>
                    </div>

                </div>

                <!-- Copyright & Bottom Bar -->
                <div class="pt-8 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between text-[11px] text-[#F5F0E6]/60 gap-4">
                    <p>© 2026 Kitonga Farm. All Rights Reserved.</p>
                    <div class="flex items-center gap-2">
                        <span class="text-gray-400">Created by</span>
                        <a 
                            href="https://wa.me/255675315279" 
                            target="_blank" 
                            rel="noopener noreferrer" 
                            class="inline-flex items-center gap-1.5 px-3 py-1 bg-[#092B18] hover:bg-[#C99A43] text-[#C99A43] hover:text-[#071F13] rounded-full border border-[#C99A43]/30 transition duration-300 font-medium shadow-xs"
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

<style scoped>
/* High-performance transitions */
input[type="date"]::-webkit-calendar-picker-indicator {
    cursor: pointer;
    filter: invert(0.2) sepia(0.8) saturate(5) hue-rotate(85deg);
}
</style>
