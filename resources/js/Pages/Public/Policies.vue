<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import SEOHead from '@/Components/SEOHead.vue';
import PublicNavbar from '@/Components/PublicNavbar.vue';

const props = defineProps({
    policy: {
        type: String,
        default: 'terms'
    },
    cms: {
        type: Object,
        default: () => ({})
    },
});

const policyTitle = computed(() => {
    if (props.policy === 'privacy') return 'Privacy Policy';
    if (props.policy === 'refund') return 'Refund & Cancellation Policy';
    return 'Terms & Booking Policies';
});

const policySchema = computed(() => ({
    '@context': 'https://schema.org',
    '@type': props.policy === 'privacy' ? 'PrivacyPolicy' : 'TermsPage',
    'name': policyTitle.value,
    'description': `Official ${policyTitle.value} for Kitonga Farm Villas reservations, guest conduct, and privacy safeguards.`,
    'url': `https://kitongafarm.com/policies/${props.policy || 'terms'}`
}));
</script>

<template>
    <SEOHead 
        :title="policyTitle"
        :description="`Review Kitonga Farm Villas ${policyTitle.toLowerCase()}. Learn about check-in/check-out guidelines, payment confirmations, and guest stay conditions.`"
        :canonical="`/policies/${policy || 'terms'}`"
        og-image="/images/luxury_villa_img.webp"
        :schema="policySchema"
    />

    <div class="bg-[#FAF8F5] text-[#2C3E2B] font-serif min-h-screen">
        
        <!-- HEADER -->
        <PublicNavbar />

        <!-- INTRO -->
        <section class="max-w-4xl mx-auto px-6 py-12 md:py-20 text-center space-y-4">
            <span class="text-xs uppercase tracking-widest font-sans text-amber-600 font-bold">Standard Policies</span>
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-wide uppercase">
                {{ policy }} Policy
            </h1>
            <p class="text-xs max-w-xl mx-auto text-gray-500 font-sans leading-relaxed">
                Please review our standard reservation rules, privacy protections, and refund guidelines.
            </p>
        </section>

        <!-- POLICY CONTENTS -->
        <section class="max-w-3xl mx-auto px-6 pb-20 space-y-8 text-xs font-sans text-gray-600 leading-relaxed text-justify bg-white p-8 rounded border border-gray-150 shadow-xs">
            <div v-if="policy === 'terms'" class="space-y-4">
                <h3 class="font-extrabold text-sm text-gray-900 font-serif">1. Reservation Agreements</h3>
                <p>All bookings must satisfy our minimum stay guidelines. Guest names must match verified national IDs presented during physical check-in.</p>
                <h3 class="font-extrabold text-sm text-gray-900 font-serif">2. Check-In & Check-Out Schedule</h3>
                <ul class="list-disc list-inside space-y-1.5 pl-2 text-gray-700">
                    <li><strong>Check-Out Time:</strong> Strictly 12:00 PM (Noon).</li>
                    <li><strong>Check-In Time:</strong> Standard at 1:00 PM (Anytime check-in is warmly welcomed whenever the villa is unoccupied prior to your arrival).</li>
                </ul>
                <h3 class="font-extrabold text-sm text-gray-900 font-serif">3. Stay Conduct</h3>
                <p>We maintain a peaceful countryside atmosphere. Excessive noise, illegal activities, and unapproved commercial filming are strictly prohibited.</p>
            </div>
            
            <div v-else-if="policy === 'privacy'" class="space-y-4">
                <h3 class="font-extrabold text-sm text-gray-900 font-serif">Data Protection Guidelines</h3>
                <p>We respect your privacy. Personal contact details, credentials, and transaction paths are stored securely and never sold to third-party aggregators.</p>
            </div>

            <div v-else-if="policy === 'refunds'" class="space-y-4">
                <h3 class="font-extrabold text-sm text-gray-900 font-serif">Refund Policy</h3>
                <p>Cancellations made 14 days or more prior to arrival are eligible for a full refund minus a 5% processing fee. Cancellations inside 14 days are subject to one-night villa charge.</p>
            </div>
        </section>

        <!-- FOOTER -->
        <footer class="bg-[#2C3E2B] text-gray-400 text-xs py-12 border-t border-emerald-900 text-center font-sans">
            <p class="font-extrabold text-white text-sm mb-2 font-serif uppercase tracking-widest">KITONGA FARM VILLAS</p>
            <p class="mb-4">Komkonga Village, Tanga Region, Tanzania</p>
            <div class="flex justify-center space-x-6 mb-6">
                <Link :href="route('about')" prefetch class="hover:text-white transition">About Us</Link>
                <Link :href="route('policies', 'terms')" prefetch class="hover:text-white transition">Terms & Policies</Link>
                <Link :href="route('contact')" prefetch class="hover:text-white transition">Contact</Link>
            </div>
            <div class="max-w-4xl mx-auto border-t border-emerald-900/50 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs px-6">
                <p>&copy; 2026 Kitonga Farm Villas. All rights reserved.</p>
                <div class="flex items-center gap-2 text-[11px]">
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

