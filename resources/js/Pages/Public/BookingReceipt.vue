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

const formatDate = (dateStr) => {
    if (!dateStr) return '—';
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

const printReceipt = () => {
    window.print();
};

const isFullyPaid = computed(() => {
    return (Number(props.booking?.balance) || 0) <= 0;
});

const isPartiallyPaid = computed(() => {
    return (Number(props.booking?.amount_paid) || 0) > 0 && (Number(props.booking?.balance) || 0) > 0;
});

const isVillaBooking = computed(() => {
    return !!props.booking?.accommodation_unit_id;
});

const whatsappPhone = computed(() => {
    const raw = props.settings?.contact_phone || '+255 758 774 695';
    return raw.replace(/[^0-9]/g, '');
});

const whatsappShareUrl = computed(() => {
    const currentUrl = typeof window !== 'undefined' ? window.location.href : '';
    const guest = props.booking?.customer?.name || 'Mteja';
    const ref = props.booking?.reference || '';
    const status = (props.booking?.status || 'CONFIRMED').toUpperCase();
    const total = formatCurrency(props.booking?.total);
    const paid = formatCurrency(props.booking?.amount_paid);
    const balance = formatCurrency(props.booking?.balance);

    const msg = `*KITONGA FARM VILLAS — OFFICIAL RECEIPT*\n\n` +
        `Customer: ${guest}\n` +
        `Ref: ${ref}\n` +
        `Status: ${status}\n` +
        `Total: ${total}\n` +
        `Amount Paid: ${paid}\n` +
        `Balance: ${balance}\n\n` +
        `View digital receipt online: ${currentUrl}`;
    return `https://wa.me/${whatsappPhone.value}?text=${encodeURIComponent(msg)}`;
});
</script>

<template>
    <Head :title="`Official Receipt — ${booking.reference} | Kitonga Farm Villas`" />

    <div class="min-h-screen bg-[#F6F3EC] py-8 sm:py-12 print:bg-white print:py-0 text-[#1A1A1A] font-sans antialiased">
        
        <!-- Navigation bar (hidden on print) -->
        <div class="max-w-3xl mx-auto px-4 mb-6 flex flex-wrap items-center justify-between gap-3 print:hidden">
            <Link :href="route('home')" class="inline-flex items-center gap-2 text-xs font-bold text-[#14301F] hover:underline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Return to Home</span>
            </Link>

            <div class="flex items-center gap-2.5">
                <a
                    :href="whatsappShareUrl"
                    target="_blank"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-bold text-white shadow-sm transition-all hover:opacity-90"
                    style="background-color: #25D366;"
                >
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    <span>Send via WhatsApp</span>
                </a>

                <button
                    @click="printReceipt"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-bold text-white shadow-sm transition-all hover:opacity-90 cursor-pointer"
                    style="background-color: #14301F;"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Print / Download PDF</span>
                </button>
            </div>
        </div>

        <!-- MAIN RECEIPT PAPER CARD -->
        <main class="max-w-3xl mx-auto bg-white rounded-3xl p-8 sm:p-12 shadow-xl border border-[#E8E2D6] print:shadow-none print:border-none print:p-0">
            
            <!-- Receipt Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between border-b pb-8 gap-6" style="border-color: #E8E2D6;">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center font-bold text-white text-2xl shadow-sm" style="background-color: #14301F; font-family: 'Playfair Display', serif;">
                            K
                        </div>
                        <div>
                            <span class="font-bold tracking-[2.5px] leading-none text-base text-[#14301F]" style="font-family: 'Playfair Display', serif;">KITONGA</span>
                            <span class="text-[9px] tracking-[3px] font-semibold text-[#D98A3D] block mt-0.5">FARMS VILLAS</span>
                        </div>
                    </div>
                    <p class="text-xs text-[#5F6B63]">Komkonga, Handeni, Tanga, Tanzania</p>
                    <p class="text-xs text-[#5F6B63]">+255 758 774 695 • info@kitongafarm.com</p>
                </div>

                <div class="text-left sm:text-right space-y-1">
                    <span class="text-[10px] font-extrabold uppercase tracking-[0.2em] text-[#D98A3D] block">OFFICIAL RECEIPT / RISITI</span>
                    <h1 class="text-xl font-bold font-mono text-[#14301F]">{{ booking.reference }}</h1>
                    <p class="text-xs text-[#5F6B63]">Issued: {{ formatDate(booking.created_at) }}</p>

                    <!-- Payment Status Badge -->
                    <div class="pt-1">
                        <span
                            v-if="isFullyPaid && booking.status === 'confirmed'"
                            class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-300"
                        >
                            PAID IN FULL / IMELIPWA KAMILIFU
                        </span>
                        <span
                            v-else-if="isPartiallyPaid"
                            class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-300"
                        >
                            PARTIAL PAYMENT / SEHEMU YA MALIPO
                        </span>
                        <span
                            v-else-if="booking.status === 'pending'"
                            class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-50 text-amber-900 border border-amber-300"
                        >
                            PENDING APPROVAL &amp; PAYMENT
                        </span>
                        <span
                            v-else
                            class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-gray-100 text-gray-800 border border-gray-300"
                        >
                            STATUS: {{ booking.status.toUpperCase() }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Customer & Reservation Overview -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 py-6 border-b text-xs" style="border-color: #E8E2D6;">
                <div class="space-y-1.5">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#5F6B63]">Billed To / Mteja:</span>
                    <h3 class="text-sm font-bold text-[#14301F]">{{ booking.customer?.name }}</h3>
                    <p class="text-[#5F6B63]">Phone: <span class="font-mono font-semibold text-[#1A1A1A]">{{ booking.customer?.phone || 'N/A' }}</span></p>
                    <p class="text-[#5F6B63]">Email: <span class="font-mono text-[#1A1A1A]">{{ booking.customer?.email || 'N/A' }}</span></p>
                    <p v-if="booking.id_number || booking.customer?.id_number" class="text-[#5F6B63]">
                        ID Number: <span class="font-mono font-semibold text-[#1A1A1A]">{{ booking.id_number || booking.customer?.id_number }} ({{ booking.id_type || 'ID' }})</span>
                    </p>
                </div>

                <div class="space-y-1.5 sm:text-right">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#5F6B63]">Reservation Details / Taarifa za Safari:</span>
                    <h3 class="text-sm font-bold text-[#14301F]">
                        {{ isVillaBooking ? (booking.unit?.type?.name || 'Luxury Villa Stay') : 'Kitonga Farm Tour Experience' }}
                    </h3>
                    <p v-if="isVillaBooking" class="text-[#5F6B63]">
                        Dates: <span class="font-semibold text-[#1A1A1A]">{{ formatDate(booking.check_in) }} → {{ formatDate(booking.check_out) }}</span>
                    </p>
                    <p v-else class="text-[#5F6B63]">
                        Visit Date: <span class="font-semibold text-[#1A1A1A]">{{ formatDate(booking.check_in) }}</span>
                    </p>
                    <p class="text-[#5F6B63]">
                        Guests: <span class="font-semibold text-[#1A1A1A]">{{ booking.guests_count }} Person(s)</span>
                    </p>
                </div>
            </div>

            <!-- Items & Breakdown Table -->
            <div class="py-6 border-b" style="border-color: #E8E2D6;">
                <table class="w-full text-left text-xs font-mono">
                    <thead>
                        <tr class="text-[#5F6B63] font-bold uppercase border-b" style="border-color: #E8E2D6;">
                            <th class="py-2.5">Description / Maelezo</th>
                            <th class="py-2.5 text-center">Qty</th>
                            <th class="py-2.5 text-right">Unit Rate</th>
                            <th class="py-2.5 text-right">Amount (TZS)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: #E8E2D6;">
                        <tr v-for="item in booking.items" :key="item.id">
                            <td class="py-3 font-semibold text-[#14301F]">{{ item.description_snapshot }}</td>
                            <td class="py-3 text-center">{{ item.quantity }}</td>
                            <td class="py-3 text-right">{{ formatCurrency(item.unit_price_snapshot) }}</td>
                            <td class="py-3 text-right font-bold text-[#14301F]">{{ formatCurrency(item.total) }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Totals Section -->
                <div class="flex justify-end pt-5">
                    <div class="w-72 space-y-2 text-xs font-mono">
                        <div class="flex justify-between text-[#5F6B63]">
                            <span>Subtotal:</span>
                            <span>{{ formatCurrency(booking.subtotal) }}</span>
                        </div>
                        <div v-if="booking.discount > 0" class="flex justify-between text-red-600">
                            <span>Discount:</span>
                            <span>-{{ formatCurrency(booking.discount) }}</span>
                        </div>
                        <div v-if="booking.tax > 0" class="flex justify-between text-[#5F6B63]">
                            <span>VAT (18%):</span>
                            <span>{{ formatCurrency(booking.tax) }}</span>
                        </div>
                        <div class="flex justify-between font-bold text-base text-[#14301F] border-t pt-2" style="border-color: #E8E2D6;">
                            <span>Grand Total:</span>
                            <span>{{ formatCurrency(booking.total) }}</span>
                        </div>
                        <div class="flex justify-between font-bold text-emerald-800 border-b pb-2" style="border-color: #E8E2D6;">
                            <span>Total Paid / Malipo Yaliyopokelewa:</span>
                            <span>{{ formatCurrency(booking.amount_paid) }}</span>
                        </div>
                        <div class="flex justify-between font-extrabold text-sm text-[#14301F] pt-1">
                            <span>Balance Due / Salio:</span>
                            <span :class="booking.balance > 0 ? 'text-red-600' : 'text-emerald-700'">{{ formatCurrency(booking.balance) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payments Ledger (if any) -->
            <div v-if="booking.payments && booking.payments.length > 0" class="py-6 border-b" style="border-color: #E8E2D6;">
                <h4 class="text-xs font-bold uppercase tracking-wider text-[#14301F] mb-3">Transaction Receipts / Kumbukumbu za Malipo</h4>
                <div class="space-y-2 text-xs font-mono">
                    <div v-for="p in booking.payments" :key="p.id" class="p-3 rounded-xl border flex items-center justify-between" style="background-color: #FFFBF5; border-color: #E8E2D6;">
                        <div>
                            <span class="font-bold text-[#14301F] uppercase">{{ p.method }}</span>
                            <span class="text-[#5F6B63] ml-2">Ref: {{ p.reference || 'Auto/Direct' }}</span>
                            <span class="text-[10px] text-[#5F6B63] block">{{ formatDate(p.paid_at || p.created_at) }}</span>
                        </div>
                        <div class="font-bold text-emerald-800 text-sm">
                            {{ formatCurrency(p.amount) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Official Verification & Stamp -->
            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="text-left space-y-1">
                    <p class="text-[11px] font-bold text-[#14301F]">Thank you for choosing Kitonga Farm Villas.</p>
                    <p class="text-[10px] text-[#5F6B63]">This is a system-generated official electronic receipt. No physical signature is required.</p>
                    <p class="text-[10px] font-mono text-[#5F6B63]">Security Reference: {{ booking.reference }}-{{ new Date(booking.created_at).getTime() }}</p>
                </div>

                <!-- Digital Verification Badge -->
                <div class="px-5 py-3 rounded-2xl border flex items-center gap-3" style="background-color: #FFFBF5; border-color: #E8E2D6;">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center bg-[#14301F] text-[#D98A3D]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold text-[#14301F]">AUTHENTIC &amp; VERIFIED</div>
                        <div class="text-[9px] text-[#5F6B63]">Kitonga Estate Concierge</div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</template>

<style scoped>
@media print {
    body {
        background-color: white !important;
    }
}
</style>
