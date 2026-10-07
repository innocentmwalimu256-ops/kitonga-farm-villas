<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import SEOHead from '@/Components/SEOHead.vue';
import PublicNavbar from '@/Components/PublicNavbar.vue';
import axios from 'axios';

const props = defineProps({
    settings: {
        type: Object,
        default: () => ({
            contact_phone: '+255 758 774 695',
            contact_email: 'kitongafarmvillas@gmail.com',
            consultation_fee: 100000,
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
});

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-TZ', { style: 'currency', currency: 'TZS', maximumFractionDigits: 0 }).format(val || 100000);
};

// Video audio & playback state
const heroVideo = ref(null);
const isMuted = ref(false);
const isPlaying = ref(true);

const toggleAudio = () => {
    if (heroVideo.value) {
        heroVideo.value.muted = !heroVideo.value.muted;
        isMuted.value = heroVideo.value.muted;
    }
};

onMounted(() => {
    if (heroVideo.value) {
        heroVideo.value.muted = false;
        const playPromise = heroVideo.value.play();
        if (playPromise !== undefined) {
            playPromise.catch(() => {
                // Autoplay with sound prevented by browser policy: start muted and allow 1-click unmute
                if (heroVideo.value) {
                    heroVideo.value.muted = true;
                    isMuted.value = true;
                    heroVideo.value.play().catch(() => {});
                }
            });
        }
    }
});

// Form state
const form = ref({
    customer_name: '',
    customer_phone: '',
    customer_email: '',
    format: 'physical', // 'physical' or 'online'
    preferred_date: props.defaultDate,
    preferred_time: '09:00 AM - 11:00 AM',
    topic: 'Agritourism & Farm Resort Setup',
    message: '',
});

const topicsList = [
    'Agritourism & Farm Resort Setup',
    'Commercial Poultry & Free-Range Layers',
    'Pasture Dairy, Milking & Mtindi Processing',
    'Organic Horticulture & Fruit Orchards',
    'Farm Master Planning, Zoning & Irrigation',
    'General Investment Mentorship & Strategy',
    'Other / Custom Farm Inquiries',
];

const timeSlots = [
    { label: 'Morning Session', time: '09:00 AM - 11:00 AM' },
    { label: 'Midday Session', time: '11:30 AM - 01:30 PM' },
    { label: 'Afternoon Session', time: '02:30 PM - 04:30 PM' },
    { label: 'Sunset Session', time: '04:30 PM - 06:30 PM' },
];

// Submission & Modal State
const isSubmitting = ref(false);
const errorMessage = ref('');
const fieldErrors = ref({});
const isModalOpen = ref(false);
const confirmedRequest = ref(null);
const whatsappUrl = ref('');
const whatsappMessage = ref('');
const isCopied = ref(false);

const scrollToForm = () => {
    const el = document.getElementById('consultation-booking-form');
    if (el) el.scrollIntoView({ behavior: 'smooth' });
};

const submitConsultationRequest = async () => {
    errorMessage.value = '';
    fieldErrors.value = {};

    if (!form.value.customer_name || form.value.customer_name.trim().length < 2) {
        fieldErrors.value.customer_name = 'Tafadhali jaza Jina lako kamili.';
        return;
    }
    if (!form.value.customer_phone || form.value.customer_phone.trim().length < 7) {
        fieldErrors.value.customer_phone = 'Tafadhali jaza Namba yako ya Simu (WhatsApp).';
        return;
    }
    if (!form.value.preferred_date) {
        fieldErrors.value.preferred_date = 'Tafadhali chagua tarehe unayopendekeza.';
        return;
    }

    isSubmitting.value = true;

    try {
        const response = await axios.post(route('meet.mr.kitonga.store'), {
            customer_name: form.value.customer_name,
            customer_phone: form.value.customer_phone,
            customer_email: form.value.customer_email || null,
            format: form.value.format,
            preferred_date: form.value.preferred_date,
            preferred_time: form.value.preferred_time,
            topic: form.value.topic,
            message: form.value.message || null,
        });

        if (response.data.success) {
            confirmedRequest.value = response.data.consultation;
            whatsappUrl.value = response.data.whatsapp_url;
            whatsappMessage.value = response.data.whatsapp_message;
            isModalOpen.value = true;
        } else {
            errorMessage.value = response.data.message || 'Hitilafu ilitokea. Tafadhali jaribu tena.';
        }
    } catch (err) {
        if (err.response && err.response.data && err.response.data.errors) {
            fieldErrors.value = err.response.data.errors;
            errorMessage.value = 'Tafadhali kagua taarifa ulizojaza kwenye fomu.';
        } else if (err.response && err.response.data && err.response.data.message) {
            errorMessage.value = err.response.data.message;
        } else {
            errorMessage.value = 'Kulitokea hitilafu ya mtandao. Tafadhali jaribu tena au wasiliana nasi kwa WhatsApp.';
        }
    } finally {
        isSubmitting.value = false;
    }
};

const handleOpenWhatsApp = async () => {
    if (!confirmedRequest.value || !whatsappUrl.value) return;

    try {
        axios.post(route('meet.mr.kitonga.track', confirmedRequest.value.reference)).catch(() => {});
    } catch (e) {}

    window.open(whatsappUrl.value, '_blank');
};

const copyWhatsAppMessage = () => {
    if (!whatsappMessage.value) return;
    navigator.clipboard.writeText(whatsappMessage.value);
    isCopied.value = true;
    setTimeout(() => {
        isCopied.value = false;
    }, 3000);
};

const closeModal = () => {
    isModalOpen.value = false;
};

const consultationSchema = {
    '@context': 'https://schema.org',
    '@type': 'ProfessionalService',
    'name': 'Meet Mr. Kitonga — Private Agritourism & Farm Mentorship',
    'description': 'Private strategic one-on-one consulting session with Mr. Kitonga covering commercial agriculture, agritourism retreat setup, poultry, dairy, and farm master planning.',
    'url': 'https://kitongafarm.com/meet-mr-kitonga',
    'priceRange': 'TZS 100,000',
    'address': {
        '@type': 'PostalAddress',
        'streetAddress': 'Kitonga Farm Estate',
        'addressLocality': 'Iringa',
        'addressRegion': 'Iringa',
        'addressCountry': 'TZ'
    },
    'telephone': '+255758774695',
    'offers': {
        '@type': 'Offer',
        'price': '100000',
        'priceCurrency': 'TZS',
        'availability': 'https://schema.org/InStock',
        'description': 'Private 1-on-1 Consultation Session with Mr. Kitonga'
    }
};
</script>

<template>
    <SEOHead 
        title="Meet Mr. Kitonga — 1-on-1 Agritourism & Farm Consultation"
        description="Book an exclusive 1-on-1 strategic consultation session with Mr. Kitonga. Learn master farm setup, organic agriculture, poultry & dairy farming, and countryside agritourism investment."
        canonical="/meet-mr-kitonga"
        og-image="/images/mr_kitonga_profile.jpg"
        :schema="consultationSchema"
    />

    <div class="min-h-screen bg-[#FAF8F5] text-[#2C3530] font-sans antialiased selection:bg-[#C98A3E] selection:text-white">

        <!-- ══════════════════════════════════════════════════════════════════════
             1. UNIVERSAL LUXURY NAVBAR
        ══════════════════════════════════════════════════════════════════════ -->
        <PublicNavbar current-page="meet-mr-kitonga" />

        <!-- ══════════════════════════════════════════════════════════════════════
             2. HERO SECTION — PURE CINEMATIC VIDEO (NO TEXT OVERLAY)
        ══════════════════════════════════════════════════════════════════════ -->
        <section class="relative bg-black w-full overflow-hidden flex flex-col items-center justify-center pt-16 sm:pt-20">
            
            <!-- Pure Video Player Container with 16:9 / cinematic framing -->
            <div class="relative w-full max-w-7xl mx-auto overflow-hidden shadow-2xl bg-black rounded-b-3xl sm:rounded-3xl my-0 sm:my-4">
                
                <video
                    ref="heroVideo"
                    src="/videos/mr_kitonga_hero.mp4"
                    autoplay
                    loop
                    playsinline
                    controls
                    preload="auto"
                    class="w-full h-auto max-h-[82vh] object-contain sm:object-cover mx-auto bg-black"
                ></video>

                <!-- Sound Control Floating Pill (Top-Right of Video) -->
                <button
                    type="button"
                    @click="toggleAudio"
                    class="absolute top-4 right-4 z-20 px-3.5 py-2 rounded-full bg-black/70 hover:bg-black/90 text-white text-xs font-bold backdrop-blur-md border border-white/20 shadow-lg flex items-center gap-2 transition cursor-pointer"
                    :title="isMuted ? 'Washa Sauti (Unmute)' : 'Zima Sauti (Mute)'"
                >
                    <span v-if="isMuted" class="flex items-center gap-1.5 text-amber-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/></svg>
                        <span>Unmute Audio</span>
                    </span>
                    <span v-else class="flex items-center gap-1.5 text-emerald-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/></svg>
                        <span>Sound ON</span>
                    </span>
                </button>
            </div>

            <!-- Sleek Luxury Request Consultation Bar (Directly below video) -->
            <div class="w-full max-w-5xl mx-auto px-4 py-6 sm:py-8 flex flex-col sm:flex-row items-center justify-between gap-4 bg-gradient-to-r from-[#14231C] via-[#1A2E24] to-[#14231C] rounded-2xl sm:rounded-3xl border border-[#C98A3E]/40 shadow-xl my-4 text-white">
                <div class="text-center sm:text-left space-y-1">
                    <div class="flex items-center justify-center sm:justify-start gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#E6C387] animate-ping"></span>
                        <span class="text-[11px] uppercase tracking-[3px] font-bold text-[#E6C387]">Direct 1-on-1 Consultation</span>
                    </div>
                    <h3 class="font-serif text-lg sm:text-xl font-light text-[#F5F1E8]">
                        Ongea na Jifunze Moja kwa Moja Kutoka kwa Mr. Kitonga
                    </h3>
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <button
                        type="button"
                        @click="scrollToForm"
                        class="w-full sm:w-auto px-6 py-3.5 bg-gradient-to-r from-[#C98A3E] to-[#b07833] hover:from-[#b07833] hover:to-[#966528] text-white font-bold uppercase tracking-widest text-xs rounded-xl shadow-lg hover:shadow-2xl transition duration-300 flex items-center justify-center gap-2 cursor-pointer shrink-0"
                    >
                        <span>Request Consultation</span>
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                    </button>
                </div>
            </div>

        </section>

        <!-- ══════════════════════════════════════════════════════════════════════
             3. MAIN BODY — ABOUT MR. KITONGA & VISION (LEFT: TEXT, RIGHT: PHOTO)
        ══════════════════════════════════════════════════════════════════════ -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-center">

                <!-- Left Column: Prestigious Bio & Guidance Overview (7 Cols) -->
                <div class="lg:col-span-7 space-y-6">
                    
                    <!-- Golden Accent Badge -->
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#14231C]/5 border border-[#C98A3E]/30 text-[#8C5D23] text-xs font-bold uppercase tracking-[2px]">
                        <span class="text-[#C98A3E]">✦</span>
                        <span>Visionary Founder &amp; Master Agritourism Mentor</span>
                    </div>

                    <!-- Main Section Title -->
                    <h2 class="font-serif text-3xl sm:text-4xl lg:text-5xl font-light text-[#14231C] leading-[1.15]">
                        Uongozi wa Vitendo na Mwongozo Sanifu wa <span class="font-normal italic text-[#8C5D23]">Kitonga Farm &amp; Villas</span>
                    </h2>

                    <!-- Descriptive Story -->
                    <div class="space-y-4 text-gray-700 text-sm sm:text-base leading-relaxed">
                        <p>
                            Karibu ujifunze moja kwa moja kutoka kwa <strong>Mr. Kitonga</strong>, mbunifu na mwanzilishi wa mfumo wa kipekee unaochanganya <strong>Kilimo Hai chenye tija kubwa</strong>, <strong>Ufugaji wa kisasa wa Kuku wa Mayai na Ng’ombe wa Maziwa</strong>, pamoja na <strong>Makazi ya Kifahari ya Utalii wa Kilimo (Agritourism Luxury Villas)</strong> mkoani Iringa.
                        </p>
                        
                        <p>
                            Kupitia uzoefu wa miaka mingi wa vitendo shambani, Mr. Kitonga hutoa ushauri wa kimkakati unaowawezesha wakulima, wawekezaji, na wajasiriamali wa Kitanzania na Diaspora kuanzisha miradi ya kilimo biashara yenye faida endelevu, huku wakiepuka hasara na makosa ya gharama kubwa katika upangaji wa mashamba (Farm Master Planning), miundombinu ya umwagiliaji, na kuongeza thamani ya mazao.
                        </p>
                    </div>

                    <!-- Core Mentorship Pillars Badges -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-2">
                        <div class="p-4 rounded-2xl bg-white border border-gray-200/90 shadow-2xs space-y-1 hover:border-[#C98A3E]/60 transition">
                            <div class="font-bold text-[#14231C] text-xs uppercase tracking-wider flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                Agritourism &amp; Villa Resorts
                            </div>
                            <p class="text-xs text-gray-500">Jinsi ya kuunganisha utalii, chakula asili, na malazi ya kifahari kwa mapato makubwa.</p>
                        </div>

                        <div class="p-4 rounded-2xl bg-white border border-gray-200/90 shadow-2xs space-y-1 hover:border-[#C98A3E]/60 transition">
                            <div class="font-bold text-[#14231C] text-xs uppercase tracking-wider flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-amber-600"></span>
                                Commercial Poultry &amp; Layers
                            </div>
                            <p class="text-xs text-gray-500">Ufugaji wa kuku wa mayai kwenye malisho asili na usimamizi wa soko la moja kwa moja.</p>
                        </div>

                        <div class="p-4 rounded-2xl bg-white border border-gray-200/90 shadow-2xs space-y-1 hover:border-[#C98A3E]/60 transition">
                            <div class="font-bold text-[#14231C] text-xs uppercase tracking-wider flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                                Pasture Dairy &amp; Mtindi
                            </div>
                            <p class="text-xs text-gray-500">Maziwa safi, usindikaji wa mtindi na mtindi mtamu (yogurt) bila kemikali.</p>
                        </div>

                        <div class="p-4 rounded-2xl bg-white border border-gray-200/90 shadow-2xs space-y-1 hover:border-[#C98A3E]/60 transition">
                            <div class="font-bold text-[#14231C] text-xs uppercase tracking-wider flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-purple-600"></span>
                                Farm Master Planning
                            </div>
                            <p class="text-xs text-gray-500">Mgawanyo sahihi wa ardhi, mifumo ya maji ya matone, na miundombinu ya kudumu.</p>
                        </div>
                    </div>

                    <!-- Direct CTA Link -->
                    <div class="pt-3">
                        <button
                            type="button"
                            @click="scrollToForm"
                            class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[#8C5D23] hover:text-[#6a4417] transition group cursor-pointer"
                        >
                            <span>Weka Nafasi ya Ushauri Sasa (TSh 100,000 / Session)</span>
                            <span class="group-hover:translate-x-1 transition-transform">➔</span>
                        </button>
                    </div>

                </div>

                <!-- Right Column: High Quality Picture of Mr. Kitonga (5 Cols) -->
                <div class="lg:col-span-5 flex justify-center">
                    <div class="relative w-full max-w-md group">
                        
                        <!-- Glowing Luxury Backdrop Blur -->
                        <div class="absolute -inset-2 bg-gradient-to-r from-[#C98A3E]/40 via-emerald-600/30 to-[#14231C]/50 rounded-3xl blur-xl opacity-75 group-hover:opacity-100 transition duration-700"></div>

                        <!-- Main Image Frame Container -->
                        <div class="relative rounded-3xl overflow-hidden bg-[#14231C] border-2 border-[#C98A3E]/60 shadow-2xl">
                            
                            <!-- Mr. Kitonga Photo -->
                            <img 
                                src="/images/mr_kitonga_profile.jpg" 
                                alt="Mr. Kitonga — Founder & Mentor at Kitonga Farm & Villas" 
                                class="w-full h-[460px] sm:h-[520px] object-cover object-top filter brightness-105 group-hover:scale-105 transition-transform duration-700 ease-out"
                            />

                            <!-- Gradient Shadow Bottom Overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>

                            <!-- Floating Badge over Photo -->
                            <div class="absolute bottom-5 left-5 right-5 p-4 rounded-2xl bg-[#14231C]/90 backdrop-blur-md border border-[#C98A3E]/50 text-white shadow-lg space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-serif text-lg font-bold text-[#F5F1E8]">Mr. Kitonga</span>
                                    <span class="px-2 py-0.5 rounded-md bg-[#C98A3E] text-white text-[10px] font-bold uppercase tracking-wider">
                                        Founder
                                    </span>
                                </div>
                                <p class="text-[11px] text-gray-300 font-sans leading-tight">
                                    Kitonga Farm &amp; Villas · Iringa, Tanzania
                                </p>
                            </div>

                        </div>

                    </div>
                </div>

            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════
             4. CONSULTATION BOOKING REQUEST FORM
        ══════════════════════════════════════════════════════════════════════ -->
        <section id="consultation-booking-form" class="max-w-4xl mx-auto px-4 sm:px-8 py-16 sm:py-24 space-y-8">
            
            <div class="text-center space-y-2">
                <span class="text-xs uppercase tracking-[3px] font-bold text-[#C98A3E] font-sans">
                    Submit Request
                </span>
                <h2 class="font-serif text-3xl sm:text-4xl md:text-5xl font-normal text-[#14231C]">
                    Book Your Session with Mr. Kitonga
                </h2>
                <p class="font-sans text-xs sm:text-sm text-gray-600 max-w-xl mx-auto leading-relaxed">
                    Submit your consultation request below. Our team will review Mr. Kitonga’s schedule, guide you on payment via WhatsApp, and confirm your appointment.
                </p>
            </div>

            <!-- Error Banner -->
            <div v-if="errorMessage" class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-xs font-sans">
                {{ errorMessage }}
            </div>

            <!-- Main Form Card -->
            <form @submit.prevent="submitConsultationRequest" class="bg-white rounded-3xl p-6 sm:p-10 border border-gray-200/90 shadow-lg space-y-6 font-sans text-xs">
                
                <!-- Format Toggle -->
                <div class="space-y-2">
                    <label class="font-bold text-gray-700 uppercase text-[11px] block">1. Select Consultation Format *</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <button 
                            type="button" 
                            @click="form.format = 'physical'"
                            :class="[
                                'py-3 px-4 rounded-xl border text-left font-sans transition cursor-pointer flex items-center justify-between',
                                form.format === 'physical'
                                    ? 'bg-[#14231C] text-white border-[#14231C] shadow-sm'
                                    : 'bg-[#FAF8F5] text-gray-700 border-gray-200 hover:bg-gray-100'
                            ]"
                        >
                            <div>
                                <span class="font-bold block text-sm">Physical at Kitonga Farm</span>
                                <span class="text-[10px] opacity-80">Iringa Farm Walkthrough + In-Person Meeting</span>
                            </div>
                            <span v-if="form.format === 'physical'" class="text-[#E6C387]">✓</span>
                        </button>

                        <button 
                            type="button" 
                            @click="form.format = 'online'"
                            :class="[
                                'py-3 px-4 rounded-xl border text-left font-sans transition cursor-pointer flex items-center justify-between',
                                form.format === 'online'
                                    ? 'bg-[#14231C] text-white border-[#14231C] shadow-sm'
                                    : 'bg-[#FAF8F5] text-gray-700 border-gray-200 hover:bg-gray-100'
                            ]"
                        >
                            <div>
                                <span class="font-bold block text-sm">Online Live Video Call</span>
                                <span class="text-[10px] opacity-80">Google Meet / Zoom / WhatsApp Video</span>
                            </div>
                            <span v-if="form.format === 'online'" class="text-[#E6C387]">✓</span>
                        </button>
                    </div>
                </div>

                <!-- Personal Information Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    
                    <!-- Customer Name -->
                    <div class="space-y-1">
                        <label class="font-bold text-gray-700 uppercase text-[10px]">Full Name (Jina Kamili) *</label>
                        <input 
                            v-model="form.customer_name" 
                            type="text" 
                            required
                            placeholder="e.g. Amani Mwamba"
                            class="w-full px-4 py-3 bg-[#FAF8F5] border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#C98A3E] focus:border-transparent text-xs transition"
                            :class="{ 'border-red-400': fieldErrors.customer_name }"
                        />
                        <span v-if="fieldErrors.customer_name" class="text-red-500 text-[10px]">{{ fieldErrors.customer_name }}</span>
                    </div>

                    <!-- Customer Phone -->
                    <div class="space-y-1">
                        <label class="font-bold text-gray-700 uppercase text-[10px]">WhatsApp / Phone Number *</label>
                        <input 
                            v-model="form.customer_phone" 
                            type="tel" 
                            required
                            placeholder="e.g. 0758 774 695"
                            class="w-full px-4 py-3 bg-[#FAF8F5] border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#C98A3E] focus:border-transparent text-xs transition"
                            :class="{ 'border-red-400': fieldErrors.customer_phone }"
                        />
                        <span v-if="fieldErrors.customer_phone" class="text-red-500 text-[10px]">{{ fieldErrors.customer_phone }}</span>
                    </div>

                </div>

                <!-- Email Address (Optional) -->
                <div class="space-y-1">
                    <label class="font-bold text-gray-700 uppercase text-[10px]">Email Address (Optional)</label>
                    <input 
                        v-model="form.customer_email" 
                        type="email" 
                        placeholder="you@email.com"
                        class="w-full px-4 py-3 bg-[#FAF8F5] border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#C98A3E] focus:border-transparent text-xs transition"
                    />
                </div>

                <!-- Focus Topic Dropdown -->
                <div class="space-y-1">
                    <label class="font-bold text-gray-700 uppercase text-[10px]">Primary Reason / Focus Topic *</label>
                    <select 
                        v-model="form.topic"
                        class="w-full px-4 py-3 bg-[#FAF8F5] border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#C98A3E] focus:border-transparent text-xs transition cursor-pointer"
                    >
                        <option v-for="t in topicsList" :key="t" :value="t">{{ t }}</option>
                    </select>
                </div>

                <!-- Preferred Date & Time Slot Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    
                    <!-- Preferred Date -->
                    <div class="space-y-1">
                        <label class="font-bold text-gray-700 uppercase text-[10px]">Preferred Appointment Date *</label>
                        <input 
                            v-model="form.preferred_date" 
                            type="date" 
                            required
                            :min="props.defaultDate"
                            class="w-full px-4 py-3 bg-[#FAF8F5] border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#C98A3E] focus:border-transparent text-xs transition"
                        />
                        <span v-if="fieldErrors.preferred_date" class="text-red-500 text-[10px]">{{ fieldErrors.preferred_date }}</span>
                    </div>

                    <!-- Preferred Time Slot -->
                    <div class="space-y-1">
                        <label class="font-bold text-gray-700 uppercase text-[10px]">Preferred Time Slot *</label>
                        <select 
                            v-model="form.preferred_time"
                            class="w-full px-4 py-3 bg-[#FAF8F5] border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#C98A3E] focus:border-transparent text-xs transition cursor-pointer"
                        >
                            <option v-for="ts in timeSlots" :key="ts.time" :value="ts.time">{{ ts.label }} ({{ ts.time }})</option>
                        </select>
                    </div>

                </div>

                <!-- Additional Project Details Message -->
                <div class="space-y-1">
                    <label class="font-bold text-gray-700 uppercase text-[10px]">Additional Project Details / Specific Questions (Optional)</label>
                    <textarea 
                        v-model="form.message" 
                        rows="3"
                        placeholder="Tell us a little about your farm size, current stage, location, or the main challenges you would like Mr. Kitonga to review..."
                        class="w-full px-4 py-3 bg-[#FAF8F5] border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#C98A3E] focus:border-transparent text-xs transition resize-y"
                    ></textarea>
                </div>

                <!-- Fee Summary Box -->
                <div class="p-4 sm:p-5 rounded-2xl bg-[#FAF8F5] border border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-gray-500 tracking-wider block">Proposed Consultation Fee:</span>
                        <span class="text-2xl font-black font-serif text-[#14231C]">
                            {{ formatCurrency(settings.consultation_fee) }}
                        </span>
                    </div>
                    <div class="text-[11px] text-gray-500 max-w-sm text-center sm:text-right">
                        How it works: Submit your consultation request and our team will guide you through availability, payment instructions and final confirmation on WhatsApp. No automatic payment processing is required.
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button 
                        type="submit"
                        :disabled="isSubmitting"
                        class="w-full py-4 px-6 bg-[#C98A3E] hover:bg-[#b57a32] text-white font-bold uppercase tracking-widest text-xs rounded-xl shadow-lg hover:shadow-xl transition duration-300 flex items-center justify-center gap-3 cursor-pointer disabled:opacity-50"
                    >
                        <svg v-if="isSubmitting" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span v-if="!isSubmitting">Submit Request &amp; Open WhatsApp</span>
                        <span v-else>Submitting Request...</span>
                        <svg v-if="!isSubmitting" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>

            </form>

        </section>

        <!-- ══════════════════════════════════════════════════════════════════════
             5. SUCCESS & WHATSAPP MODAL POPUP
        ══════════════════════════════════════════════════════════════════════ -->
        <div 
            v-if="isModalOpen" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-in fade-in duration-200"
            @click.self="closeModal"
        >
            <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-gray-100 space-y-6 text-center font-sans">
                
                <!-- Success Icon -->
                <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center mx-auto text-2xl font-bold">
                    ✓
                </div>

                <div class="space-y-2">
                    <span class="text-[10px] font-bold uppercase tracking-[2px] text-emerald-700">Request Created Successfully</span>
                    <h3 class="font-serif text-2xl font-bold text-[#14231C]">
                        Next Step: Connect on WhatsApp
                    </h3>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        Your consultation booking request has been logged with Reference:
                        <span class="font-mono font-bold text-gray-900 bg-gray-100 px-2 py-0.5 rounded">{{ confirmedRequest?.reference }}</span>
                    </p>
                </div>

                <!-- Pre-Filled WhatsApp Message Preview -->
                <div class="text-left bg-gray-50 border border-gray-200 rounded-2xl p-4 space-y-2">
                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-gray-500">
                        <span>Pre-Filled WhatsApp Message</span>
                        <button 
                            type="button" 
                            @click="copyWhatsAppMessage" 
                            class="text-emerald-700 hover:underline cursor-pointer"
                        >
                            {{ isCopied ? '✓ Copied!' : 'Copy Text' }}
                        </button>
                    </div>
                    <p class="text-xs text-gray-700 whitespace-pre-line font-mono bg-white p-3 rounded-xl border border-gray-100 max-h-40 overflow-y-auto">
                        {{ whatsappMessage }}
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3 pt-2">
                    <button 
                        type="button" 
                        @click="handleOpenWhatsApp"
                        class="w-full py-4 px-6 bg-emerald-700 hover:bg-emerald-800 text-white font-bold uppercase tracking-widest text-xs rounded-xl shadow-lg hover:shadow-xl transition duration-300 flex items-center justify-center gap-2.5 cursor-pointer"
                    >
                        <span>Open WhatsApp &amp; Send Message</span>
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                    </button>

                    <button 
                        type="button" 
                        @click="closeModal" 
                        class="w-full py-2.5 text-xs text-gray-500 hover:text-gray-800 font-bold transition cursor-pointer"
                    >
                        Close &amp; Return to Page
                    </button>
                </div>

            </div>
        </div>

    </div>
</template>
