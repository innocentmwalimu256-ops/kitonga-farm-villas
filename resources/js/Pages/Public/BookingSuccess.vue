<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    booking: Object,
    settings: Object,
});

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-TZ', { style: 'currency', currency: 'TZS', maximumFractionDigits: 0 }).format(val || 0);
};

const whatsappPhone = computed(() => {
    const raw = props.settings?.contact_phone || '+255 758 774 695';
    return raw.replace(/[^0-9]/g, '');
});

const whatsappLink = computed(() => {
    const ref = props.booking?.reference || 'RES-2026';
    const total = formatCurrency(props.booking?.total);
    const guest = props.booking?.customer?.name || 'Mgeni';
    const message = encodeURIComponent(`Habari Kitonga Farm Villas,\n\nNaitwa ${guest}. Nimetuma booking request kupitia website.\n\n📌 Booking Ref: ${ref}\n💰 Total Amount: ${total}\n\nNingependa kuwasiliana nanyi kuthibitisha na kufanya malipo.`);
    return `https://wa.me/${whatsappPhone.value}?text=${message}`;
});
</script>

<template>
    <Head title="Booking Request Received — Kitonga Farm Villas" />

    <div class="bg-[#FAF8F5] text-[#1F2420] min-h-screen font-sans flex flex-col justify-between">
        
        <!-- Header -->
        <header class="bg-white border-b border-[#E5E0D8] py-4 px-6 md:px-12 flex justify-between items-center shadow-xs">
            <Link :href="route('home')" class="flex flex-col items-start group">
                <span class="font-serif text-lg font-light text-[#1F2420] tracking-[3px] uppercase leading-none">
                    KITONGA
                </span>
                <span class="font-sans text-[8px] font-medium text-[#C98A3E] tracking-[5px] uppercase leading-none mt-1">
                    FARMS VILLAS
                </span>
            </Link>
            <span class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Booking Request</span>
        </header>

        <!-- Main Confirmation Card -->
        <main class="max-w-2xl mx-auto px-6 py-12 w-full my-auto">
            <div class="bg-white rounded-3xl border border-[#E5E0D8] p-8 sm:p-12 shadow-lg space-y-8 text-center">
                
                <!-- Success Icon -->
                <div class="w-16 h-16 bg-emerald-50 text-emerald-700 rounded-full flex items-center justify-center mx-auto text-2xl border border-emerald-200">
                    ✓
                </div>

                <!-- Title & Ref -->
                <div class="space-y-2">
                    <span class="text-[11px] uppercase tracking-[4px] font-bold text-emerald-800 block">
                        BOOKING REQUEST RECEIVED
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-serif font-light text-[#1F2420]">
                        Booking Reference: <span class="font-semibold text-[#C98A3E]">{{ booking.reference }}</span>
                    </h1>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 border border-amber-200 text-amber-800 rounded-full text-xs font-bold uppercase tracking-wider mt-1">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        <span>Status: PENDING</span>
                    </div>
                </div>

                <!-- Booking Summary Box -->
                <div class="bg-[#FAF8F5] rounded-2xl p-6 border border-gray-200 text-left space-y-3 text-xs">
                    <div class="flex justify-between py-1 border-b border-gray-200/60">
                        <span class="text-gray-500">Mgeni (Guest Name):</span>
                        <span class="font-bold text-gray-900">{{ booking.customer?.name }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-200/60">
                        <span class="text-gray-500">Simu (Phone):</span>
                        <span class="font-bold text-gray-900">{{ booking.customer?.phone || '—' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-200/60">
                        <span class="text-gray-500">Tarehe (Dates):</span>
                        <span class="font-bold text-gray-900">{{ booking.check_in }} → {{ booking.check_out }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-200/60">
                        <span class="text-gray-500">Idadi ya Wageni:</span>
                        <span class="font-bold text-gray-900">{{ booking.guests_count }} Guests</span>
                    </div>
                    <div class="flex justify-between py-2 text-sm">
                        <span class="font-bold text-gray-900">Jumla ya Gharama (Total):</span>
                        <span class="font-extrabold text-[#1B2E22] text-base">{{ formatCurrency(booking.total) }}</span>
                    </div>
                </div>

                <!-- Next Steps Instructions -->
                <div class="space-y-2 text-xs text-gray-600 leading-relaxed max-w-lg mx-auto">
                    <p class="font-semibold text-gray-900">
                        Hatua inayofuata (Next Step):
                    </p>
                    <p>
                        Resort imepokea booking request yako. Endelea na mawasiliano na resort kupitia WhatsApp kuthibitisha na kufanya malipo.
                    </p>
                </div>

                <!-- WhatsApp CTA Button -->
                <div class="space-y-3 pt-2">
                    <a 
                        :href="whatsappLink" 
                        target="_blank" 
                        rel="noopener noreferrer"
                        class="w-full py-4 bg-[#25D366] hover:bg-[#1EBE5D] text-white text-sm font-bold uppercase tracking-wider rounded-xl transition duration-300 flex items-center justify-center gap-2.5 shadow-lg shadow-emerald-500/20 cursor-pointer"
                    >
                        <span>💬 CONTINUE ON WHATSAPP</span>
                    </a>

                    <Link 
                        :href="route('home')" 
                        prefetch
                        class="inline-block text-xs font-semibold text-gray-500 hover:text-gray-800 transition pt-2"
                    >
                        ← Return to Homepage
                    </Link>
                </div>

            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-[#14231C] text-gray-400 py-6 px-6 text-center text-xs border-t border-white/10">
            <p>© 2026 Kitonga Farm Villas. All rights reserved.</p>
        </footer>

    </div>
</template>
