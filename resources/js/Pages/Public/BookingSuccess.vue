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
    const guest = props.booking?.customer?.name || 'Guest';
    const message = encodeURIComponent(`Hello Kitonga Farm Villas,\n\nMy name is ${guest}. I submitted a booking request on your website.\n\nBooking Ref: ${ref}\nTotal Amount: ${total}\n\nI would like to confirm my reservation and arrange payment.`);
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
                        <span class="text-gray-500">Lead Guest:</span>
                        <span class="font-bold text-gray-900">{{ booking.customer?.name }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-200/60">
                        <span class="text-gray-500">Phone / WhatsApp:</span>
                        <span class="font-bold text-gray-900">{{ booking.customer?.phone || '—' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-200/60">
                        <span class="text-gray-500">Reservation Dates:</span>
                        <span class="font-bold text-gray-900">{{ booking.check_in }} → {{ booking.check_out }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-200/60">
                        <span class="text-gray-500">Check-In Time:</span>
                        <span class="font-bold text-gray-900">1:00 PM <span class="text-[10px] text-emerald-700 font-normal">(*Anytime if vacant)</span></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-200/60">
                        <span class="text-gray-500">Check-Out Time:</span>
                        <span class="font-bold text-gray-900">12:00 PM <span class="text-[10px] text-gray-500 font-normal">(Strictly)</span></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-200/60">
                        <span class="text-gray-500">Total Guests:</span>
                        <span class="font-bold text-gray-900">{{ booking.guests_count }} Guests</span>
                    </div>
                    <div class="flex justify-between py-2 text-sm">
                        <span class="font-bold text-gray-900">Total Stay Amount:</span>
                        <span class="font-extrabold text-[#1B2E22] text-base">{{ formatCurrency(booking.total) }}</span>
                    </div>
                </div>

                <!-- Next Steps Instructions -->
                <div class="space-y-2 text-xs text-gray-600 leading-relaxed max-w-lg mx-auto">
                    <p class="font-semibold text-gray-900">
                        Next Steps:
                    </p>
                    <p>
                        We have successfully received your reservation request. Please proceed to WhatsApp to connect directly with our concierge desk to confirm availability and complete your payment.
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
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.698.074-2.128-.517-1.745-.722-2.888-2.518-2.977-2.637-.086-.118-.707-.941-.707-1.796 0-.854.448-1.275.607-1.448.16-.174.348-.218.465-.218.117 0 .234.001.336.006.107.005.251-.041.393.3.144.347.493 1.202.536 1.29.043.087.072.189.014.304-.058.117-.087.19-.174.29-.087.102-.183.228-.261.306-.089.088-.182.184-.078.362.104.178.463.765.994 1.238.683.608 1.259.797 1.438.885.178.087.283.073.388-.048.106-.12.453-.527.575-.708.121-.182.243-.151.408-.09.166.06 1.054.497 1.235.588.182.09.303.136.348.213.044.076.044.444-.1.849z"/>
                        </svg>
                        <span>CONTINUE ON WHATSAPP</span>
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
        <footer class="bg-[#14231C] text-gray-400 py-6 px-6 border-t border-white/10 font-sans text-xs">
            <div class="max-w-4xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
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
