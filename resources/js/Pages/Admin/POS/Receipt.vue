<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    sale: Object,
    isPublic: {
        type: Boolean,
        default: false,
    },
});

const copied = ref(false);

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-TZ', { style: 'currency', currency: 'TZS', maximumFractionDigits: 0 }).format(Number(val) || 0);
};

const printReceipt = () => {
    window.print();
};

const publicReceiptUrl = computed(() => {
    if (!props.sale) return '';
    const ref = props.sale.reference || `SALE-${props.sale.id}`;
    if (typeof window !== 'undefined' && window.location) {
        return `${window.location.origin}/receipt/sale/${ref}`;
    }
    return `https://kitongafarm.com/receipt/sale/${ref}`;
});

const copyReceiptLink = async () => {
    try {
        await navigator.clipboard.writeText(publicReceiptUrl.value);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2500);
    } catch (e) {
        // Fallback prompt
        window.prompt('Copy this receipt link:', publicReceiptUrl.value);
    }
};

const whatsappShareUrl = computed(() => {
    if (!props.sale) return '#';
    const ref = props.sale.reference || `SALE-${props.sale.id}`;
    const total = formatCurrency(props.sale.total);
    const date = new Date(props.sale.created_at).toLocaleString();
    const customer = props.sale.customer?.name || 'Walk-in Guest';
    
    const itemsText = (props.sale.items || [])
        .map(i => `• ${i.product?.name || i.description_snapshot} (x${i.quantity}) - ${formatCurrency(i.total)}`)
        .join('\n');

    const msg = `🧾 *KITONGA FARM VILLAS — RISITI YA MAUZO*\n` +
        `Ref: *${ref}*\n` +
        `Tarehe: ${date}\n` +
        `Mteja: *${customer}*\n\n` +
        `*Bidhaa Zilizouzwa:*\n${itemsText}\n\n` +
        `*Jumla Kuu:* *${total}*\n` +
        `Hali ya Malipo: *Imelipwa (Completed)*\n\n` +
        `Tazama risiti yako mtandaoni hapa:\n${publicReceiptUrl.value}\n\n` +
        `Asante kwa kutembelea Kitonga Farm Villas! 🌾`;

    const phone = (props.sale.customer?.phone || '').replace(/[^0-9]/g, '');
    const phonePrefix = phone ? `phone=${phone}&` : '';
    return `https://api.whatsapp.com/send?${phonePrefix}text=${encodeURIComponent(msg)}`;
});
</script>

<template>
    <Head :title="`Risiti ya Mauzo — ${sale.reference || 'KFV-POS'}`" />

    <div class="bg-gray-100 min-h-screen py-6 sm:py-10 print:bg-white print:py-0 px-3 sm:px-4">
        
        <!-- TOP CONTROL BAR (Screen Only) -->
        <div class="max-w-md mx-auto mb-4 flex items-center justify-between print:hidden">
            <Link 
                v-if="!isPublic"
                :href="route('admin.pos.terminal')" 
                class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-700 hover:text-emerald-800 bg-white px-3 py-1.5 rounded-lg border border-gray-200 shadow-2xs transition"
            >
                <span>←</span>
                <span>POS Terminal (Uza Tena)</span>
            </Link>
            <Link 
                v-else
                :href="route('home')" 
                class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-700 hover:text-emerald-800 bg-white px-3 py-1.5 rounded-lg border border-gray-200 shadow-2xs transition"
            >
                <span>←</span>
                <span>Kitonga Farm Villas</span>
            </Link>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="printReceipt"
                    class="inline-flex items-center gap-1.5 text-xs font-bold bg-emerald-700 hover:bg-emerald-800 text-white px-3.5 py-1.5 rounded-lg shadow-2xs transition cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Print (Chapa)</span>
                </button>
            </div>
        </div>

        <!-- MAIN RECEIPT CARD -->
        <div class="max-w-md mx-auto bg-white p-6 sm:p-7 shadow-lg rounded-2xl border border-gray-200 print:border-none print:shadow-none print:p-0">
            
            <!-- Receipt Header -->
            <div class="text-center border-b border-gray-200 pb-5 mb-5">
                <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-2.5 text-emerald-800 font-bold text-lg">
                    🌾
                </div>
                <h1 class="font-extrabold text-base sm:text-lg text-emerald-900 uppercase tracking-widest">
                    Kitonga Farm Villas
                </h1>
                <p class="text-[11px] text-gray-500 font-medium">Komkonga Village, Handeni, Tanga Region</p>
                <p class="text-[11px] text-gray-500 font-mono">+255 758 774 695 | kitongafarmvillas@gmail.com</p>
                <div class="mt-2.5 inline-block px-3 py-1 bg-emerald-50 text-emerald-900 border border-emerald-200 rounded-full text-[10px] font-bold uppercase tracking-wider">
                    Official POS Receipt / Risiti Rasmi ya Mauzo
                </div>
            </div>

            <!-- Receipt Info -->
            <div class="space-y-1.5 text-xs text-gray-600 mb-5 font-mono bg-gray-50/70 p-3.5 rounded-xl border border-gray-200/80">
                <div class="flex justify-between">
                    <span class="text-gray-500">Namba ya Risiti:</span>
                    <span class="font-bold text-gray-900">{{ sale.reference }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Tarehe / Muda:</span>
                    <span>{{ new Date(sale.created_at).toLocaleString() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Mteja (Customer):</span>
                    <span class="font-bold text-gray-900">{{ sale.customer?.name || 'Walk-in Guest' }}</span>
                </div>
                <div v-if="sale.customer?.phone" class="flex justify-between">
                    <span class="text-gray-500">Simu:</span>
                    <span>{{ sale.customer.phone }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Mhudumu (Cashier):</span>
                    <span>{{ sale.creator?.name || 'Admin Desk' }}</span>
                </div>
                <div class="flex justify-between pt-1 border-t border-gray-200">
                    <span class="text-gray-500">Hali (Status):</span>
                    <span class="uppercase font-bold text-[11px] text-emerald-700">
                        {{ sale.status === 'completed' ? 'Paid & Completed' : sale.status }}
                    </span>
                </div>
            </div>

            <!-- Items list -->
            <div class="mb-5">
                <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2 font-mono">
                    Bidhaa Zilizouzwa (Items Purchased)
                </h4>
                <table class="w-full text-left text-xs border-t border-b border-gray-200 py-2 font-mono">
                    <thead>
                        <tr class="text-gray-400 font-semibold uppercase text-[10px]">
                            <th class="py-2">Item</th>
                            <th class="py-2 text-center">Qty</th>
                            <th class="py-2 text-right">Price</th>
                            <th class="py-2 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="item in sale.items" :key="item.id" class="border-b border-dashed border-gray-100">
                            <td class="py-2 font-medium text-gray-900">
                                {{ item.product?.name || item.description_snapshot }}
                            </td>
                            <td class="py-2 text-center font-bold">{{ item.quantity }}</td>
                            <td class="py-2 text-right text-gray-500">{{ formatCurrency(item.unit_price) }}</td>
                            <td class="py-2 text-right font-bold text-gray-900">{{ formatCurrency(item.total) }}</td>
                        </tr>
                        <tr v-if="!sale.items || sale.items.length === 0">
                            <td colspan="4" class="py-3 text-center text-gray-400 italic">No items recorded</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Financial summary -->
            <div class="space-y-2 text-xs text-gray-700 font-mono pr-1 bg-emerald-50/40 p-3.5 rounded-xl border border-emerald-100 mb-5">
                <div class="flex justify-between">
                    <span class="text-gray-600">Subtotal (Jumla Ndogo):</span>
                    <span class="font-semibold">{{ formatCurrency(sale.subtotal) }}</span>
                </div>
                <div v-if="Number(sale.discount) > 0" class="flex justify-between text-red-600">
                    <span>Discount (Punguzo):</span>
                    <span>-{{ formatCurrency(sale.discount) }}</span>
                </div>
                <div class="flex justify-between font-extrabold text-sm text-emerald-950 border-t border-emerald-200 pt-2">
                    <span>Grand Total (Jumla Kuu):</span>
                    <span>{{ formatCurrency(sale.total) }}</span>
                </div>
                <div v-for="payment in sale.payments" :key="payment.id" class="flex justify-between text-emerald-800 text-[11px] pt-1 border-t border-dashed border-emerald-200">
                    <span>Malipo ({{ payment.method }}<span v-if="payment.reference"> - Ref: {{ payment.reference }}</span>):</span>
                    <span class="font-bold">{{ formatCurrency(payment.amount) }}</span>
                </div>
            </div>

            <!-- Footer Message -->
            <div class="text-center text-[10px] text-gray-400 font-mono border-t border-gray-200 pt-4">
                <p class="font-bold text-gray-600">Asante kwa kututembelea Kitonga Farm Villas!</p>
                <p>Where luxury meets farm life.</p>
                <p class="text-[9px] text-gray-400 mt-1">Hii ni risiti halali ya kidijitali iliyotolewa na mfumo wa Kitonga Farm.</p>
            </div>

            <!-- ACTION BUTTONS (Print & WhatsApp - Screen Only) -->
            <div class="mt-6 pt-4 border-t border-gray-200 space-y-2.5 print:hidden">
                <!-- WhatsApp Share Button -->
                <a 
                    :href="whatsappShareUrl" 
                    target="_blank" 
                    class="w-full py-3 bg-[#25D366] hover:bg-[#1EBE5D] text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-md transition flex items-center justify-center gap-2 cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    <span>Mtumie Mteja Risiti (WhatsApp)</span>
                </a>

                <!-- Print & Copy Link Grid -->
                <div class="grid grid-cols-2 gap-2">
                    <button 
                        type="button" 
                        @click="printReceipt" 
                        class="py-2.5 bg-emerald-800 hover:bg-emerald-900 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span>Chapa Risiti (Print)</span>
                    </button>

                    <button 
                        type="button" 
                        @click="copyReceiptLink" 
                        class="py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition flex items-center justify-center gap-1.5 cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <span>{{ copied ? 'Imenakiliwa!' : 'Nakili Link' }}</span>
                    </button>
                </div>

                <!-- Back Navigation (Admin Only) -->
                <div v-if="!isPublic" class="flex items-center justify-between gap-2 pt-2 border-t border-gray-100 text-xs">
                    <Link :href="route('admin.pos.terminal')" class="text-emerald-800 hover:underline font-bold py-1">
                        🛒 Rudi POS (Uza Tena)
                    </Link>
                    <Link :href="route('admin.pos.index')" class="text-gray-600 hover:text-gray-900 font-semibold py-1">
                        📋 Orodha ya Mauzo (Sales Log)
                    </Link>
                    <Link :href="route('dashboard')" class="text-gray-600 hover:text-gray-900 font-semibold py-1">
                        🏡 Dashboard
                    </Link>
                </div>
            </div>

        </div>
    </div>
</template>

<style>
@media print {
    body {
        background-color: #fff !important;
    }
}
</style>
