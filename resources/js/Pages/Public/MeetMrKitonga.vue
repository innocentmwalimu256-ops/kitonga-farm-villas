<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
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
        // Track click asynchronously without blocking window.open
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
        og-image="/images/general_farm_hero.webp"
        :schema="consultationSchema"
    />

    <div class="min-h-screen bg-[#FAF8F5] text-[#2C3530] font-sans antialiased selection:bg-[#C98A3E] selection:text-white">

        <!-- ══════════════════════════════════════════════════════════════════════
             1. UNIVERSAL LUXURY NAVBAR
        ══════════════════════════════════════════════════════════════════════ -->
        <PublicNavbar current-page="meet-mr-kitonga" />

        <!-- ══════════════════════════════════════════════════════════════════════
             2. HERO MASTHEAD
        ══════════════════════════════════════════════════════════════════════ -->
        <section class="relative bg-[#14231C] text-white pt-24 pb-24 sm:pt-28 sm:pb-32 px-4 sm:px-8 overflow-hidden min-h-[520px] flex items-center justify-center select-none">
            <!-- Background Image with Dark Gradient -->
            <img 
                src="/images/general_farm_hero.webp" 
                alt="Kitonga Farm Strategic Agriculture" 
                class="absolute inset-0 w-full h-full object-cover object-center filter brightness-[0.38] scale-105 transition-transform duration-1000"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-[#14231C] via-[#14231C]/60 to-black/70"></div>

            <div class="relative z-10 max-w-5xl mx-auto text-center space-y-6">
                <!-- Golden Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-[#1B2E24]/90 border border-[#C98A3E]/40 text-[#E6C387] text-[10px] sm:text-xs font-bold uppercase tracking-[3px] shadow-lg backdrop-blur-md">
                    <span class="text-[#C98A3E] animate-pulse">✦</span>
                    <span>Exclusive 1-on-1 Advisory Session</span>
                    <span class="text-[#C98A3E] animate-pulse">✦</span>
                </div>

                <!-- Main Title -->
                <h1 class="font-serif text-4xl sm:text-6xl md:text-7xl font-light tracking-wide text-[#F5F1E8] uppercase leading-none drop-shadow-md">
                    Meet Mr. Kitonga
                </h1>

                <!-- Subtitle -->
                <p class="font-sans text-sm sm:text-base md:text-lg text-gray-200 max-w-2xl mx-auto font-light leading-relaxed">
                    Transformative guidance on sustainable agriculture, commercial farm operations, high-yield poultry & dairy, and profitable agritourism retreats.
                </p>

                <!-- Consultation Fee Highlight Card -->
                <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-4 max-w-md mx-auto">
                    <div class="w-full bg-white/10 backdrop-blur-md border border-[#C98A3E]/50 rounded-2xl p-4 sm:p-5 text-center shadow-xl">
                        <span class="text-[10px] uppercase font-bold tracking-[2px] text-[#E6C387] block mb-1">Consultation Fee</span>
                        <div class="flex items-baseline justify-center gap-1.5 text-white">
                            <span class="text-3xl sm:text-4xl font-extrabold font-serif text-[#F5F1E8]">
                                {{ formatCurrency(settings.consultation_fee) }}
                            </span>
                            <span class="text-xs text-gray-300 font-sans font-medium">/ session</span>
                        </div>
                        <span class="text-[11px] text-emerald-300 font-medium block mt-1">✓ Physical at Farm or Online Live Video Call</span>
                    </div>
                </div>

                <!-- Primary CTA Button to scroll to form -->
                <div class="pt-4 flex flex-col sm:flex-row justify-center items-center gap-4">
                    <button 
                        type="button" 
                        @click="scrollToForm"
                        class="w-full sm:w-auto px-8 py-4 bg-[#C98A3E] hover:bg-[#b57a32] text-white font-bold uppercase tracking-widest text-xs rounded-xl transition duration-300 shadow-xl hover:shadow-2xl cursor-pointer flex items-center justify-center gap-2.5 font-sans"
                    >
                        <span>Request Consultation via WhatsApp</span>
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                    </button>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════
             3. WHAT THE CONSULTATION COVERS (Pillars of Mastery)
        ══════════════════════════════════════════════════════════════════════ -->
        <section class="max-w-6xl mx-auto px-4 sm:px-8 py-16 sm:py-24 space-y-16">
            
            <div class="text-center space-y-3 max-w-3xl mx-auto">
                <span class="text-xs uppercase tracking-[3px] font-bold text-[#C98A3E] font-sans">
                    Practical Agri-Business Expertise
                </span>
                <h2 class="font-serif text-3xl sm:text-4xl md:text-5xl font-normal text-[#14231C]">
                    What the Consultation Covers
                </h2>
                <p class="font-sans text-xs sm:text-sm text-gray-600 leading-relaxed">
                    Tap into years of verified hands-on agricultural experience, regenerative design, and boutique countryside hospitality.
                </p>
            </div>

            <!-- Pillars Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                
                <!-- Card 1 -->
                <div class="bg-white rounded-2xl p-6 sm:p-8 border border-gray-200/80 shadow-xs hover:shadow-md transition duration-300 space-y-3 group">
                    <div class="w-12 h-12 rounded-xl bg-[#14231C] text-[#E6C387] flex items-center justify-center text-xl font-bold font-serif group-hover:scale-105 transition">
                        01
                    </div>
                    <h3 class="font-serif text-xl font-semibold text-[#14231C]">
                        Agritourism & Farm Resort Setup
                    </h3>
                    <p class="font-sans text-xs text-gray-600 leading-relaxed">
                        Learn how to combine organic agriculture with eco-luxury private villas, farm-to-table dining, and curated visitor itineraries for recurring high-yield hospitality revenue.
                    </p>
                </div>

                <!-- Card 2 -->
                <div class="bg-white rounded-2xl p-6 sm:p-8 border border-gray-200/80 shadow-xs hover:shadow-md transition duration-300 space-y-3 group">
                    <div class="w-12 h-12 rounded-xl bg-[#14231C] text-[#E6C387] flex items-center justify-center text-xl font-bold font-serif group-hover:scale-105 transition">
                        02
                    </div>
                    <h3 class="font-serif text-xl font-semibold text-[#14231C]">
                        Commercial Poultry & Free-Range Layers
                    </h3>
                    <p class="font-sans text-xs text-gray-600 leading-relaxed">
                        Best practices for free-range pasture housing, natural nutrition, disease prevention, egg collection workflows, tray grading, and high-margin direct-to-consumer distribution.
                    </p>
                </div>

                <!-- Card 3 -->
                <div class="bg-white rounded-2xl p-6 sm:p-8 border border-gray-200/80 shadow-xs hover:shadow-md transition duration-300 space-y-3 group">
                    <div class="w-12 h-12 rounded-xl bg-[#14231C] text-[#E6C387] flex items-center justify-center text-xl font-bold font-serif group-hover:scale-105 transition">
                        03
                    </div>
                    <h3 class="font-serif text-xl font-semibold text-[#14231C]">
                        Pasture Dairy, Milking & Value Addition
                    </h3>
                    <p class="font-sans text-xs text-gray-600 leading-relaxed">
                        Fodder cultivation, hygienic morning milking routines, and artisanal dairy processing including cultured sour milk (mtindi), drinking yogurts, and raw milk cold chain management.
                    </p>
                </div>

                <!-- Card 4 -->
                <div class="bg-white rounded-2xl p-6 sm:p-8 border border-gray-200/80 shadow-xs hover:shadow-md transition duration-300 space-y-3 group">
                    <div class="w-12 h-12 rounded-xl bg-[#14231C] text-[#E6C387] flex items-center justify-center text-xl font-bold font-serif group-hover:scale-105 transition">
                        04
                    </div>
                    <h3 class="font-serif text-xl font-semibold text-[#14231C]">
                        Organic Horticulture & Fruit Orchards
                    </h3>
                    <p class="font-sans text-xs text-gray-600 leading-relaxed">
                        Establishing profitable papaya, mango, and organic chilli orchards. Soil conditioning with organic compost, drip irrigation efficiency, and pest control without synthetic chemicals.
                    </p>
                </div>

                <!-- Card 5 -->
                <div class="bg-white rounded-2xl p-6 sm:p-8 border border-gray-200/80 shadow-xs hover:shadow-md transition duration-300 space-y-3 group">
                    <div class="w-12 h-12 rounded-xl bg-[#14231C] text-[#E6C387] flex items-center justify-center text-xl font-bold font-serif group-hover:scale-105 transition">
                        05
                    </div>
                    <h3 class="font-serif text-xl font-semibold text-[#14231C]">
                        Master Planning & Estate Infrastructure
                    </h3>
                    <p class="font-sans text-xs text-gray-600 leading-relaxed">
                        Zoning your land effectively: road access, water reservoir placement, natural fencing, animal pens, workers quarters, and guest privacy separation.
                    </p>
                </div>

                <!-- Card 6 -->
                <div class="bg-white rounded-2xl p-6 sm:p-8 border border-gray-200/80 shadow-xs hover:shadow-md transition duration-300 space-y-3 group">
                    <div class="w-12 h-12 rounded-xl bg-[#C98A3E] text-white flex items-center justify-center text-xl font-bold font-serif group-hover:scale-105 transition">
                        06
                    </div>
                    <h3 class="font-serif text-xl font-semibold text-[#14231C]">
                        1-on-1 Direct Strategic Roadmap
                    </h3>
                    <p class="font-sans text-xs text-gray-600 leading-relaxed">
                        Bring your specific farm questions, budget constraints, or project ideas. Receive tailored feedback, critical avoidance of costly mistakes, and step-by-step execution guidance.
                    </p>
                </div>

            </div>

        </section>

        <!-- ══════════════════════════════════════════════════════════════════════
             4. AVAILABLE MEETING FORMATS
        ══════════════════════════════════════════════════════════════════════ -->
        <section class="bg-[#14231C] text-white py-16 sm:py-20 px-4 sm:px-8">
            <div class="max-w-5xl mx-auto space-y-12">
                <div class="text-center space-y-2">
                    <span class="text-xs uppercase tracking-[3px] font-bold text-[#C98A3E] font-sans">
                        Flexible Engagement Formats
                    </span>
                    <h2 class="font-serif text-3xl sm:text-4xl font-normal text-[#F5F1E8]">
                        Choose Your Preferred Format
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Format 1: Physical at Farm -->
                    <div 
                        @click="form.format = 'physical'"
                        :class="[
                            'rounded-2xl p-6 sm:p-8 border transition cursor-pointer relative',
                            form.format === 'physical' 
                                ? 'bg-[#1D3227] border-[#C98A3E] ring-2 ring-[#C98A3E]/40 shadow-xl' 
                                : 'bg-white/5 border-white/10 hover:border-white/20'
                        ]"
                    >
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-3 py-1 bg-[#C98A3E] text-white text-[10px] font-bold uppercase tracking-wider rounded-md font-sans">
                                In-Person Experience
                            </span>
                            <div class="w-6 h-6 rounded-full border border-white/30 flex items-center justify-center">
                                <span v-if="form.format === 'physical'" class="w-3 h-3 rounded-full bg-[#E6C387]"></span>
                            </div>
                        </div>
                        <h3 class="font-serif text-2xl font-semibold text-[#F5F1E8] mb-2">Physical at Kitonga Farm</h3>
                        <p class="text-xs text-gray-300 leading-relaxed font-sans mb-4">
                            Meet Mr. Kitonga in person at Kitonga Farm Estate. Includes an exclusive guided walk across active poultry pastures, dairy milking stations, organic orchards, and fresh farm refreshments.
                        </p>
                        <span class="text-xs font-bold text-[#E6C387] font-sans">Fee: {{ formatCurrency(settings.consultation_fee) }} / Session</span>
                    </div>

                    <!-- Format 2: Online Video Call -->
                    <div 
                        @click="form.format = 'online'"
                        :class="[
                            'rounded-2xl p-6 sm:p-8 border transition cursor-pointer relative',
                            form.format === 'online' 
                                ? 'bg-[#1D3227] border-[#C98A3E] ring-2 ring-[#C98A3E]/40 shadow-xl' 
                                : 'bg-white/5 border-white/10 hover:border-white/20'
                        ]"
                    >
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-3 py-1 bg-emerald-700 text-white text-[10px] font-bold uppercase tracking-wider rounded-md font-sans">
                                Global Video Call
                            </span>
                            <div class="w-6 h-6 rounded-full border border-white/30 flex items-center justify-center">
                                <span v-if="form.format === 'online'" class="w-3 h-3 rounded-full bg-[#E6C387]"></span>
                            </div>
                        </div>
                        <h3 class="font-serif text-2xl font-semibold text-[#F5F1E8] mb-2">Online Video Call (Zoom / Google Meet)</h3>
                        <p class="text-xs text-gray-300 leading-relaxed font-sans mb-4">
                            Ideal for diaspora, busy professionals, and entrepreneurs outside Iringa or overseas. Deep-dive video conference with screensharing, satellite layout reviews, and actionable strategic notes.
                        </p>
                        <span class="text-xs font-bold text-[#E6C387] font-sans">Fee: {{ formatCurrency(settings.consultation_fee) }} / Session</span>
                    </div>

                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════
             5. CONSULTATION BOOKING REQUEST FORM
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

                <!-- Personal Contact Info -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    
                    <div>
                        <label class="font-bold text-gray-700 uppercase text-[11px] block mb-1">Full Name (Jina Kamili) *</label>
                        <input 
                            v-model="form.customer_name" 
                            type="text" 
                            placeholder="e.g. Amani Mwamba"
                            class="w-full text-xs rounded-xl border-gray-300 focus:border-[#14231C] focus:ring-1 focus:ring-[#14231C] py-2.5 px-3 bg-[#FAF8F5]"
                            required
                        />
                        <span v-if="fieldErrors.customer_name" class="text-red-600 text-[10px] mt-0.5 block">{{ fieldErrors.customer_name }}</span>
                    </div>

                    <div>
                        <label class="font-bold text-gray-700 uppercase text-[11px] block mb-1">WhatsApp / Phone Number *</label>
                        <input 
                            v-model="form.customer_phone" 
                            type="tel" 
                            placeholder="e.g. 0758 774 695"
                            class="w-full text-xs rounded-xl border-gray-300 focus:border-[#14231C] focus:ring-1 focus:ring-[#14231C] py-2.5 px-3 bg-[#FAF8F5]"
                            required
                        />
                        <span v-if="fieldErrors.customer_phone" class="text-red-600 text-[10px] mt-0.5 block">{{ fieldErrors.customer_phone }}</span>
                    </div>

                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    
                    <div>
                        <label class="font-bold text-gray-700 uppercase text-[11px] block mb-1">Email Address (Optional)</label>
                        <input 
                            v-model="form.customer_email" 
                            type="email" 
                            placeholder="you@email.com"
                            class="w-full text-xs rounded-xl border-gray-300 focus:border-[#14231C] focus:ring-1 focus:ring-[#14231C] py-2.5 px-3 bg-[#FAF8F5]"
                        />
                    </div>

                    <div>
                        <label class="font-bold text-gray-700 uppercase text-[11px] block mb-1">Primary Reason / Focus Topic *</label>
                        <select 
                            v-model="form.topic"
                            class="w-full text-xs rounded-xl border-gray-300 focus:border-[#14231C] focus:ring-1 focus:ring-[#14231C] py-2.5 px-3 bg-[#FAF8F5]"
                        >
                            <option v-for="t in topicsList" :key="t" :value="t">{{ t }}</option>
                        </select>
                    </div>

                </div>

                <!-- Date & Time Slot Selection -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    
                    <div>
                        <label class="font-bold text-gray-700 uppercase text-[11px] block mb-1">Preferred Appointment Date *</label>
                        <input 
                            v-model="form.preferred_date" 
                            type="date" 
                            class="w-full text-xs rounded-xl border-gray-300 focus:border-[#14231C] focus:ring-1 focus:ring-[#14231C] py-2.5 px-3 bg-[#FAF8F5]"
                            required
                        />
                        <span v-if="fieldErrors.preferred_date" class="text-red-600 text-[10px] mt-0.5 block">{{ fieldErrors.preferred_date }}</span>
                    </div>

                    <div>
                        <label class="font-bold text-gray-700 uppercase text-[11px] block mb-1">Preferred Time Slot *</label>
                        <select 
                            v-model="form.preferred_time"
                            class="w-full text-xs rounded-xl border-gray-300 focus:border-[#14231C] focus:ring-1 focus:ring-[#14231C] py-2.5 px-3 bg-[#FAF8F5]"
                        >
                            <option v-for="slot in timeSlots" :key="slot.time" :value="slot.time">
                                {{ slot.label }} ({{ slot.time }})
                            </option>
                        </select>
                    </div>

                </div>

                <!-- Additional Message -->
                <div>
                    <label class="font-bold text-gray-700 uppercase text-[11px] block mb-1">Additional Project Details / Specific Questions (Optional)</label>
                    <textarea 
                        v-model="form.message" 
                        rows="3" 
                        placeholder="Tell us a little about your farm size, current stage, location, or the main challenges you would like Mr. Kitonga to review..."
                        class="w-full text-xs rounded-xl border-gray-300 focus:border-[#14231C] focus:ring-1 focus:ring-[#14231C] p-3 bg-[#FAF8F5]"
                    ></textarea>
                </div>

                <!-- Summary Box & Disclaimer -->
                <div class="bg-[#FAF8F5] border border-gray-200 rounded-2xl p-4 sm:p-5 space-y-3">
                    <div class="flex items-center justify-between text-xs font-sans">
                        <span class="font-bold text-gray-700 uppercase tracking-wider">Proposed Consultation Fee:</span>
                        <span class="text-base sm:text-lg font-bold text-[#14231C]">{{ formatCurrency(settings.consultation_fee) }}</span>
                    </div>
                    <p class="text-[11px] text-gray-500 leading-relaxed border-t border-gray-200/80 pt-2.5">
                        <span class="font-bold text-[#14231C]">How it works:</span> 
                        Submit your consultation request and our team will guide you through availability, payment instructions and final confirmation on WhatsApp. No automatic payment processing is required.
                    </p>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    :disabled="isSubmitting"
                    class="w-full py-4 bg-[#14231C] hover:bg-[#C98A3E] text-[#F5F1E8] font-bold uppercase tracking-widest text-xs rounded-xl transition duration-300 shadow-md hover:shadow-xl cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50"
                >
                    <span v-if="!isSubmitting">Submit Request & Open WhatsApp</span>
                    <span v-else>Submitting request... please wait</span>
                    <svg v-if="!isSubmitting" class="w-4 h-4 text-[#E6C387]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>

            </form>

        </section>

        <!-- ══════════════════════════════════════════════════════════════════════
             6. INSTANT WHATSAPP CONFIRMATION MODAL & DIGITAL SLIP
        ══════════════════════════════════════════════════════════════════════ -->
        <teleport to="body">
            <div 
                v-if="isModalOpen" 
                class="fixed inset-0 z-[1000] bg-black/80 backdrop-blur-xs flex justify-center items-center p-4 overflow-y-auto"
                @click.self="closeModal"
            >
                <div class="bg-[#FAF8F5] rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-[#C98A3E]/30 relative my-8 text-[#1F2420] font-sans animate-in fade-in duration-300">
                    
                    <!-- Close button -->
                    <button 
                        @click="closeModal" 
                        class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-2xl font-light cursor-pointer w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-200 transition"
                    >
                        ✕
                    </button>

                    <!-- Header with Checkmark -->
                    <div class="text-center space-y-2 pb-4 border-b border-gray-200">
                        <div class="w-14 h-14 bg-emerald-100 text-emerald-800 rounded-full flex items-center justify-center mx-auto text-2xl font-bold shadow-xs">
                            ✓
                        </div>
                        <span class="text-[10px] uppercase tracking-[3px] font-bold text-[#C98A3E] block">
                            Consultation Request Created
                        </span>
                        <h3 class="text-2xl font-serif font-light text-[#14231C]">
                            Ready to Connect on WhatsApp
                        </h3>
                        <p class="text-xs text-gray-600 max-w-sm mx-auto">
                            Your booking reference has been generated. Press the button below to review your pre-filled message and start your conversation with Kitonga Farm.
                        </p>
                    </div>

                    <!-- Digital Request Slip -->
                    <div class="my-5 bg-white rounded-2xl p-4 sm:p-5 border border-gray-200 space-y-3 shadow-xs">
                        <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                            <span class="text-[10px] uppercase font-bold text-gray-400">Booking Reference</span>
                            <span class="font-mono font-bold text-sm text-[#14231C] bg-[#FAF8F5] px-2.5 py-0.5 rounded border border-gray-200">
                                {{ confirmedRequest?.reference }}
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <span class="text-[10px] text-gray-400 block font-bold uppercase">Customer</span>
                                <span class="font-semibold text-gray-800">{{ confirmedRequest?.customer_name }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-gray-400 block font-bold uppercase">Phone</span>
                                <span class="font-semibold text-gray-800">{{ confirmedRequest?.customer_phone }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-gray-400 block font-bold uppercase">Format</span>
                                <span class="font-semibold text-gray-800">{{ confirmedRequest?.format === 'online' ? 'Online Video Call' : 'Physical at Farm' }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-gray-400 block font-bold uppercase">Date & Time</span>
                                <span class="font-semibold text-gray-800">{{ confirmedRequest?.preferred_date }}</span>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-gray-100 flex justify-between items-center text-xs">
                            <span class="font-bold text-gray-600 uppercase text-[10px]">Consultation Fee:</span>
                            <span class="font-extrabold text-[#14231C] text-sm">{{ formatCurrency(confirmedRequest?.fee) }}</span>
                        </div>
                    </div>

                    <!-- Primary WhatsApp Action CTA -->
                    <div class="space-y-2.5">
                        <button 
                            type="button" 
                            @click="handleOpenWhatsApp"
                            class="w-full py-4 bg-[#25D366] hover:bg-[#20ba59] text-white font-extrabold uppercase tracking-wider text-xs rounded-xl transition font-sans flex items-center justify-center gap-2.5 cursor-pointer shadow-lg hover:shadow-xl"
                        >
                            <!-- WhatsApp SVG icon -->
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-5.805 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                            </svg>
                            <span>BOOK VIA WHATSAPP</span>
                        </button>

                        <!-- Secondary Fallback: Copy Message -->
                        <button 
                            type="button" 
                            @click="copyWhatsAppMessage"
                            class="w-full py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs rounded-xl transition cursor-pointer flex items-center justify-center gap-1.5"
                        >
                            <span v-if="!isCopied">📋 Copy Pre-Filled Message</span>
                            <span v-else class="text-emerald-700 font-bold">✓ Message Copied to Clipboard!</span>
                        </button>
                    </div>

                    <!-- Helpful Notice -->
                    <p class="text-[10px] text-gray-400 text-center mt-4">
                        Pressing the button will open WhatsApp with your pre-filled details. You can review the message before pressing Send.
                    </p>

                </div>
            </div>
        </teleport>

        <!-- ══════════════════════════════════════════════════════════════════════
             7. FOOTER
        ══════════════════════════════════════════════════════════════════════ -->
        <footer class="bg-[#14231C] text-gray-400 text-xs py-14 border-t border-emerald-950 font-sans">
            <div class="max-w-6xl mx-auto px-6 space-y-8">
                <div class="flex flex-col md:flex-row justify-between items-center gap-6 text-center md:text-left">
                    <div>
                        <p class="font-extrabold text-white text-base font-serif uppercase tracking-[3px]">KITONGA FARM VILLAS</p>
                        <p class="text-xs text-gray-400 mt-1">Komkonga Village, Tanga Region, Tanzania</p>
                    </div>
                    <div class="flex flex-wrap justify-center gap-6 text-xs uppercase tracking-wider font-semibold text-gray-300">
                        <Link :href="route('home')" class="hover:text-[#E6C387] transition">Home</Link>
                        <Link :href="route('villas')" class="hover:text-[#E6C387] transition">Villas</Link>
                        <Link :href="route('experiences')" class="hover:text-[#E6C387] transition">Experiences</Link>
                        <Link :href="route('meet.mr.kitonga')" class="text-[#E6C387] font-bold">Meet Mr. Kitonga</Link>
                        <Link :href="route('contact')" class="hover:text-[#E6C387] transition">Contact</Link>
                    </div>
                </div>
                <div class="border-t border-white/10 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
                    <p>&copy; 2026 Kitonga Farm Villas & Sanctuary. All rights reserved.</p>
                    <div class="flex items-center gap-2 text-[11px]">
                        <span class="text-gray-400">Created by</span>
                        <a 
                            href="https://wa.me/255675315279" 
                            target="_blank" 
                            rel="noopener noreferrer" 
                            class="inline-flex items-center gap-1.5 px-3 py-1 bg-[#1E3327] hover:bg-[#C98A3E] text-[#E6C387] hover:text-white rounded-full border border-[#C98A3E]/30 transition duration-300 font-medium"
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
