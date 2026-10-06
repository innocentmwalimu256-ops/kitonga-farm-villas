<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    experience: Object,
});

// Tomorrow's date formatted as YYYY-MM-DD
const defaultDate = () => {
    const d = new Date();
    d.setDate(d.getDate() + 1);
    return d.toISOString().split('T')[0];
};

const guests = ref(2);
const bookingDate = ref(defaultDate());
const timeSlot = ref('09:00 AM - 11:00 AM');

const updateGuests = (val) => {
    let num = parseInt(val, 10);
    if (isNaN(num) || num < 1) {
        guests.value = 1;
    } else if (num > 500) {
        guests.value = 500;
    } else {
        guests.value = num;
    }
};

const incrementGuests = () => {
    const current = parseInt(guests.value, 10) || 1;
    if (current < 500) {
        guests.value = current + 1;
    }
};

const decrementGuests = () => {
    const current = parseInt(guests.value, 10) || 1;
    if (current > 1) {
        guests.value = current - 1;
    }
};

const setGuestPreset = (num) => {
    guests.value = num;
};

// Modal State
const isModalOpen = ref(false);
const isSubmitting = ref(false);
const errorMessage = ref('');
const confirmedBooking = ref(null);

// Form Fields
const form = ref({
    customer_name: '',
    customer_phone: '',
    customer_email: '',
    payment_method: 'arrival',
    mobile_network: 'mpesa',
    notes: '',
});

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-TZ', { style: 'currency', currency: 'TZS', maximumFractionDigits: 0 }).format(val);
};

const totalPrice = computed(() => {
    return (props.experience.price || 0) * guests.value;
});

const openBookingModal = () => {
    if (!bookingDate.value) {
        bookingDate.value = defaultDate();
    }
    errorMessage.value = '';
    confirmedBooking.value = null;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const submitTourBooking = async () => {
    if (!form.value.customer_name || !form.value.customer_phone) {
        errorMessage.value = 'Tafadhali jaza Jina lako na Namba ya Simu ili kuweka nafasi.';
        return;
    }

    isSubmitting.value = true;
    errorMessage.value = '';

    try {
        const response = await axios.post(route('experiences.book'), {
            farm_tour_id: props.experience.id,
            tour_date: bookingDate.value,
            time_slot: timeSlot.value,
            guests_count: guests.value,
            customer_name: form.value.customer_name,
            customer_phone: form.value.customer_phone,
            customer_email: form.value.customer_email || null,
            payment_method: form.value.payment_method,
            mobile_network: form.value.mobile_network,
            notes: form.value.notes || null,
        });

        if (response.data.success) {
            confirmedBooking.value = response.data.booking;
        }
    } catch (err) {
        if (err.response && err.response.data && err.response.data.message) {
            errorMessage.value = err.response.data.message;
        } else {
            errorMessage.value = 'Kulitokea hitilafu wakati wa kuweka booking. Tafadhali jaribu tena au wasiliana nasi kwa WhatsApp.';
        }
    } finally {
        isSubmitting.value = false;
    }
};

const whatsappLink = computed(() => {
    if (!confirmedBooking.value) return '#';
    const text = encodeURIComponent(
        `Habari Kitonga Farm Villas,\n\n` +
        `Jina langu ni *${confirmedBooking.value.customer_name}*.\n` +
        `Ninaomba kufanya booking ya Farm Tour:\n` +
        `• Namba ya Kumbukumbu: *${confirmedBooking.value.reference}*\n` +
        `• Aina ya Tour: *${props.experience.name}*\n` +
        `• Tarehe & Muda: *${confirmedBooking.value.date}* (${confirmedBooking.value.time_slot})\n` +
        `• Idadi ya Wageni: *${confirmedBooking.value.guests}* Person(s)\n` +
        `• Makadirio ya Malipo: *${formatCurrency(confirmedBooking.value.total)}*\n\n` +
        `Naomba maelekezo ya jinsi ya kukamilisha malipo ili kuthibitisha booking yangu. Asante sana!`
    );
    return `https://wa.me/255758774695?text=${text}`;
});

const printTicket = () => {
    window.print();
};
</script>

<template>
    <!-- Desktop Sticky Sidebar Panel -->
    <div class="bg-white border border-[#E5E0D8] rounded-xl p-6 shadow-sm space-y-6">
        <div class="space-y-1">
            <span class="text-[10px] uppercase tracking-widest text-[#C98A3E] font-sans font-bold block">Day Tour Price</span>
            <div class="flex items-baseline gap-1.5">
                <span class="text-2xl md:text-3xl font-extrabold text-[#1B2E22] font-sans">
                    {{ formatCurrency(experience.price) }}
                </span>
                <span class="text-[11px] text-gray-500 font-sans">/ person</span>
            </div>
            <span class="text-[11px] text-emerald-700 font-sans block pt-0.5 font-medium">✓ No villa stay required</span>
        </div>

        <div class="space-y-4 font-sans text-xs">
            <!-- Date Picker -->
            <div class="space-y-1">
                <label class="font-bold text-gray-600 uppercase text-[10px] block">1. Select Visit Date</label>
                <input 
                    v-model="bookingDate" 
                    type="date" 
                    class="w-full text-xs rounded-lg border-gray-300 focus:border-[#1B2E22] focus:ring-1 focus:ring-[#1B2E22] mt-1 py-2 px-3" 
                    required 
                />
            </div>

            <!-- Time Slot Selection -->
            <div class="space-y-1.5">
                <label class="font-bold text-gray-600 uppercase text-[10px] block">2. Preferred Time Slot</label>
                <div class="grid grid-cols-2 gap-2">
                    <button 
                        type="button" 
                        @click="timeSlot = '09:00 AM - 11:00 AM'"
                        :class="[
                            'py-2 px-2.5 rounded-lg text-[11px] font-medium border text-center transition cursor-pointer',
                            timeSlot === '09:00 AM - 11:00 AM' 
                                ? 'bg-[#1B2E22] text-white border-[#1B2E22] shadow-xs' 
                                : 'bg-[#FAF8F5] text-gray-700 border-gray-200 hover:bg-gray-100'
                        ]"
                    >
                        Morning<br /><span class="text-[9px] opacity-80">9:00 AM</span>
                    </button>
                    <button 
                        type="button" 
                        @click="timeSlot = '02:00 PM - 04:00 PM'"
                        :class="[
                            'py-2 px-2.5 rounded-lg text-[11px] font-medium border text-center transition cursor-pointer',
                            timeSlot === '02:00 PM - 04:00 PM' 
                                ? 'bg-[#1B2E22] text-white border-[#1B2E22] shadow-xs' 
                                : 'bg-[#FAF8F5] text-gray-700 border-gray-200 hover:bg-gray-100'
                        ]"
                    >
                        Afternoon<br /><span class="text-[9px] opacity-80">2:00 PM</span>
                    </button>
                </div>
            </div>

            <!-- 3. GUEST COUNTER WITH DIRECT NUMBER TYPING & STEPPER -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="font-bold text-gray-700 uppercase text-[10px] block">3. Number of Guests (Idadi ya Wageni)</label>
                    <span class="text-[10px] text-amber-700 font-semibold font-sans">Andika idadi yoyote</span>
                </div>

                <!-- Interactive Stepper & Direct Typing Input -->
                <div class="flex items-center bg-[#FAF8F5] border border-gray-300 focus-within:border-[#1B2E22] focus-within:ring-2 focus-within:ring-[#1B2E22]/20 rounded-xl p-1 shadow-xs transition">
                    <!-- Minus Button -->
                    <button 
                        type="button" 
                        @click="decrementGuests" 
                        :disabled="guests <= 1"
                        class="w-10 h-10 rounded-lg bg-white border border-gray-200 text-gray-700 hover:bg-[#1B2E22] hover:text-white disabled:opacity-30 disabled:hover:bg-white disabled:hover:text-gray-700 flex items-center justify-center font-bold text-xl transition cursor-pointer shrink-0 shadow-2xs select-none"
                        aria-label="Punguza idadi ya wageni"
                    >
                        −
                    </button>

                    <!-- Direct Number Input (User can type any number) -->
                    <div class="flex-1 text-center px-2 flex items-center justify-center gap-1.5">
                        <input 
                            type="number" 
                            :value="guests"
                            @input="updateGuests($event.target.value)"
                            min="1" 
                            max="500" 
                            class="w-16 sm:w-20 text-center text-lg sm:text-xl font-extrabold text-[#1B2E22] bg-white rounded-md border border-gray-200 focus:border-[#C98A3E] focus:ring-1 focus:ring-[#C98A3E] py-1 px-1.5 font-sans [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none shadow-2xs"
                            placeholder="1"
                        />
                        <span class="text-xs text-gray-700 font-bold uppercase tracking-wider font-sans select-none">
                            {{ guests === 1 ? 'Guest' : 'Guests' }}
                        </span>
                    </div>

                    <!-- Plus Button -->
                    <button 
                        type="button" 
                        @click="incrementGuests" 
                        class="w-10 h-10 rounded-lg bg-white border border-gray-200 text-gray-700 hover:bg-[#1B2E22] hover:text-white flex items-center justify-center font-bold text-xl transition cursor-pointer shrink-0 shadow-2xs select-none"
                        aria-label="Ongeza idadi ya wageni"
                    >
                        +
                    </button>
                </div>

                <!-- Quick Presets -->
                <div class="flex items-center gap-1.5 pt-0.5">
                    <span class="text-[9px] uppercase tracking-wider text-gray-400 font-bold shrink-0">Haraka:</span>
                    <div class="flex flex-wrap gap-1">
                        <button 
                            v-for="p in [1, 2, 3, 5, 10, 20]" 
                            :key="p"
                            type="button"
                            @click="setGuestPreset(p)"
                            :class="[
                                'px-2 py-0.5 rounded-md text-[10px] font-bold transition cursor-pointer',
                                guests === p 
                                    ? 'bg-[#1B2E22] text-[#E6C387] shadow-2xs ring-1 ring-[#C98A3E]/40' 
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                            ]"
                        >
                            {{ p }}
                        </button>
                    </div>
                </div>

                <!-- Rate calculation detail -->
                <div class="text-[11px] text-gray-500 pt-1 flex justify-between items-center bg-gray-50/80 px-2.5 py-1.5 rounded-lg border border-gray-200/60 font-sans">
                    <span>{{ formatCurrency(experience.price) }} × {{ guests }} wageni</span>
                    <span class="font-extrabold text-[#1B2E22]">{{ formatCurrency(totalPrice) }}</span>
                </div>
            </div>
        </div>

        <!-- Total Price Summary -->
        <div class="border-t border-gray-150 pt-4 flex justify-between items-baseline">
            <span class="text-[11px] uppercase tracking-wider text-gray-500 font-sans font-bold">Total Price:</span>
            <span class="text-xl font-bold text-[#1B2E22] font-sans">{{ formatCurrency(totalPrice) }}</span>
        </div>

        <!-- Primary Instant Tour Reservation CTA -->
        <button 
            type="button"
            @click="openBookingModal" 
            class="w-full py-3.5 bg-[#1B2E22] hover:bg-[#14231C] text-[#F7F3EA] font-bold uppercase tracking-wider text-xs rounded-lg transition font-sans flex items-center justify-center gap-2 cursor-pointer shadow-sm"
        >
            <span>Book This Tour Now</span>
            <svg class="w-4 h-4 text-[#C98A3E]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
            </svg>
        </button>

        <!-- Optional Link for Overnight Villa Stay -->
        <div class="pt-2 text-center border-t border-gray-100">
            <span class="text-[11px] text-gray-500 block font-sans">Want to stay overnight in a villa as well?</span>
            <Link 
                :href="route('booking.form')" 
                class="text-[11px] text-[#C98A3E] hover:underline font-bold uppercase tracking-wider mt-1 inline-block font-sans"
            >
                Book with Villa Stay →
            </Link>
        </div>

        <!-- Mobile Sticky Floating Bottom Bar -->
        <teleport to="body">
            <div class="md:hidden fixed bottom-0 left-0 w-full bg-white border-t border-gray-200 p-3.5 z-50 flex items-center justify-between shadow-2xl">
                <div class="flex flex-col">
                    <span class="text-[9px] uppercase tracking-widest text-gray-400 font-sans font-bold">Total ({{ guests }} {{ guests === 1 ? 'Guest' : 'Guests' }})</span>
                    <span class="text-base font-extrabold text-[#1B2E22] font-sans">{{ formatCurrency(totalPrice) }}</span>
                </div>
                <button 
                    type="button"
                    @click="openBookingModal" 
                    class="px-6 py-2.5 bg-[#1B2E22] text-[#F7F3EA] hover:bg-[#14231C] text-xs font-bold uppercase tracking-wider rounded-lg transition font-sans cursor-pointer shadow-sm"
                >
                    Book Tour Now
                </button>
            </div>
        </teleport>

        <!-- INSTANT TOUR BOOKING MODAL (DAY VISIT CHECKOUT & DIGITAL TICKET) -->
        <teleport to="body">
            <div 
                v-if="isModalOpen" 
                class="fixed inset-0 z-100 bg-black/75 backdrop-blur-xs flex justify-center items-center p-4 overflow-y-auto"
                @click.self="closeModal"
            >
                <div class="bg-[#FAF8F5] rounded-2xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-gray-200 relative my-8 text-[#1F2420]">
                    
                    <!-- Close button -->
                    <button 
                        @click="closeModal" 
                        class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-2xl font-light cursor-pointer w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-200 transition"
                    >
                        ✕
                    </button>

                    <!-- STEP 1: FORM (IF NOT YET CONFIRMED) -->
                    <div v-if="!confirmedBooking" class="space-y-6 font-sans">
                        
                        <!-- Header -->
                        <div class="border-b border-gray-200 pb-4">
                            <span class="text-[10px] uppercase tracking-[3px] font-bold text-[#C98A3E] block">Instant Day Tour Reservation</span>
                            <h3 class="text-2xl font-serif font-light text-[#1B2E22] mt-0.5">{{ experience.name }}</h3>
                            
                            <!-- Reservation Summary Badges -->
                            <div class="flex flex-wrap items-center gap-2 pt-2.5 text-xs text-gray-700">
                                <span class="bg-[#ECE7DF] px-2.5 py-1 rounded-md font-medium">📅 {{ bookingDate }}</span>
                                <span class="bg-[#ECE7DF] px-2.5 py-1 rounded-md font-medium">⏰ {{ timeSlot }}</span>
                                <div class="inline-flex items-center gap-1.5 bg-[#ECE7DF] px-2.5 py-1 rounded-md font-medium">
                                    <span>👥 Idadi:</span>
                                    <button type="button" @click="decrementGuests" :disabled="guests <= 1" class="w-5 h-5 rounded bg-white text-gray-800 flex items-center justify-center font-bold text-xs disabled:opacity-30 cursor-pointer hover:bg-gray-100 shadow-2xs">−</button>
                                    <span class="font-extrabold text-[#1B2E22] min-w-[20px] text-center">{{ guests }}</span>
                                    <button type="button" @click="incrementGuests" class="w-5 h-5 rounded bg-white text-gray-800 flex items-center justify-center font-bold text-xs cursor-pointer hover:bg-gray-100 shadow-2xs">+</button>
                                    <span class="text-[11px] text-gray-500">{{ guests === 1 ? 'Guest' : 'Guests' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Error Alert -->
                        <div v-if="errorMessage" class="p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-xs">
                            {{ errorMessage }}
                        </div>

                        <!-- Contact Details -->
                        <div class="space-y-3.5 text-xs">
                            <div>
                                <label class="font-bold text-gray-700 block mb-1">Full Name *</label>
                                <input 
                                    v-model="form.customer_name" 
                                    type="text" 
                                    placeholder="e.g. Amani Mwamba" 
                                    class="w-full text-xs rounded-lg border-gray-300 focus:border-[#1B2E22] focus:ring-1 focus:ring-[#1B2E22] py-2.5 px-3 bg-white" 
                                    required 
                                />
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="font-bold text-gray-700 block mb-1">Phone Number (M-Pesa/WhatsApp) *</label>
                                    <input 
                                       v-model="form.customer_phone" 
                                        type="tel" 
                                        placeholder="0758 774 695" 
                                        class="w-full text-xs rounded-lg border-gray-300 focus:border-[#1B2E22] focus:ring-1 focus:ring-[#1B2E22] py-2.5 px-3 bg-white" 
                                        required 
                                    />
                                </div>
                                <div>
                                    <label class="font-bold text-gray-700 block mb-1">Email Address (Optional)</label>
                                    <input 
                                        v-model="form.customer_email" 
                                        type="email" 
                                        placeholder="you@email.com" 
                                        class="w-full text-xs rounded-lg border-gray-300 focus:border-[#1B2E22] focus:ring-1 focus:ring-[#1B2E22] py-2.5 px-3 bg-white" 
                                    />
                                </div>
                            </div>

                            <!-- Optional Notes -->
                            <div>
                                <label class="font-bold text-gray-700 block mb-1">Special Requests (Optional)</label>
                                <input 
                                    v-model="form.notes" 
                                    type="text" 
                                    placeholder="e.g. Vegetarian refreshments, wheelchair access, etc." 
                                    class="w-full text-xs rounded-lg border-gray-300 focus:border-[#1B2E22] focus:ring-1 focus:ring-[#1B2E22] py-2 px-3 bg-white" 
                                />
                            </div>
                        </div>

                        <!-- Total & Submit -->
                        <div class="border-t border-gray-200 pt-4 space-y-3">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600 font-medium">Total Amount Due:</span>
                                <span class="text-xl font-bold text-[#1B2E22]">{{ formatCurrency(totalPrice) }}</span>
                            </div>

                            <button 
                                type="button"
                                @click="submitTourBooking" 
                                :disabled="isSubmitting"
                                class="w-full py-3.5 bg-[#1B2E22] hover:bg-[#14231C] text-[#F7F3EA] font-bold uppercase tracking-wider text-xs rounded-xl transition font-sans flex items-center justify-center gap-2 cursor-pointer shadow-md disabled:opacity-50"
                            >
                                <span v-if="!isSubmitting">Send Booking Request ({{ formatCurrency(totalPrice) }})</span>
                                <span v-else>Submitting request... please wait</span>
                            </button>
                        </div>

                    </div>

                    <!-- STEP 2: BOOKING CONFIRMATION & DIGITAL TOUR PASS -->
                    <div v-else class="space-y-6 text-center font-sans">
                        
                        <div class="w-16 h-16 bg-emerald-100 text-emerald-800 rounded-full flex items-center justify-center mx-auto text-3xl shadow-sm">
                            ✓
                        </div>

                        <div class="space-y-1">
                            <span class="text-[11px] uppercase tracking-[3px] font-bold text-emerald-800 block">Booking Request Received</span>
                            <h3 class="text-2xl font-serif font-light text-[#1B2E22]">Your Farm Pass is Ready</h3>
                            <p class="text-xs text-gray-600 max-w-sm mx-auto">
                                Thank you {{ confirmedBooking.customer_name }}! Please connect with the resort concierge via WhatsApp to confirm and complete payment.
                            </p>
                        </div>

                        <!-- Ticket Pass Card -->
                        <div class="bg-white border-2 border-dashed border-[#C98A3E]/60 rounded-xl p-5 text-left space-y-3 shadow-sm text-xs">
                            <div class="flex justify-between items-center border-b border-gray-150 pb-2.5">
                                <div>
                                    <span class="text-[9px] uppercase tracking-widest text-gray-400 font-bold block">Booking Reference</span>
                                    <span class="font-mono text-base font-bold text-[#C98A3E]">{{ confirmedBooking.reference }}</span>
                                </div>
                                <span class="px-2.5 py-1 bg-amber-50 border border-amber-200 text-amber-800 rounded-full text-[10px] font-bold uppercase">
                                    PENDING
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-gray-700">
                                <div>
                                    <span class="text-[9px] uppercase text-gray-400 block font-bold">Experience</span>
                                    <span class="font-bold text-gray-900">{{ experience.name }}</span>
                                </div>
                                <div>
                                    <span class="text-[9px] uppercase text-gray-400 block font-bold">Guests</span>
                                    <span class="font-bold text-gray-900">{{ confirmedBooking.guests }} Person(s)</span>
                                </div>
                                <div>
                                    <span class="text-[9px] uppercase text-gray-400 block font-bold">Date</span>
                                    <span class="font-bold text-gray-900">{{ confirmedBooking.date }}</span>
                                </div>
                                <div>
                                    <span class="text-[9px] uppercase text-gray-400 block font-bold">Time Slot</span>
                                    <span class="font-bold text-gray-900">{{ confirmedBooking.time_slot }}</span>
                                </div>
                            </div>

                            <div class="border-t border-gray-150 pt-2 flex justify-between items-center text-xs font-bold text-gray-900">
                                <span>Total:</span>
                                <span class="text-sm text-emerald-800">{{ formatCurrency(confirmedBooking.total) }}</span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="space-y-2.5 pt-2">
                            <!-- WhatsApp Confirmation -->
                            <a 
                                :href="whatsappLink" 
                                target="_blank" 
                                rel="noopener"
                                class="w-full py-3.5 bg-[#25D366] hover:bg-[#1EBE5D] text-white font-bold text-xs uppercase tracking-wider rounded-xl transition flex items-center justify-center gap-2 shadow-md cursor-pointer"
                            >
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.698.074-2.128-.517-1.745-.722-2.888-2.518-2.977-2.637-.086-.118-.707-.941-.707-1.796 0-.854.448-1.275.607-1.448.16-.174.348-.218.465-.218.117 0 .234.001.336.006.107.005.251-.041.393.3.144.347.493 1.202.536 1.29.043.087.072.189.014.304-.058.117-.087.19-.174.29-.087.102-.183.228-.261.306-.089.088-.182.184-.078.362.104.178.463.765.994 1.238.683.608 1.259.797 1.438.885.178.087.283.073.388-.048.106-.12.453-.527.575-.708.121-.182.243-.151.408-.09.166.06 1.054.497 1.235.588.182.09.303.136.348.213.044.076.044.444-.1.849z"/>
                                </svg>
                                <span>CONTINUE ON WHATSAPP</span>
                            </a>

                            <div class="flex gap-2">
                                <button 
                                    @click="printTicket" 
                                    class="w-1/2 py-2.5 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 text-xs font-bold rounded-xl transition cursor-pointer flex items-center justify-center gap-1.5"
                                >
                                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                    </svg>
                                    Print / Save Pass
                                </button>
                                <button 
                                    @click="closeModal" 
                                    class="w-1/2 py-2.5 bg-[#1B2E22] text-[#F7F3EA] hover:bg-[#14231C] text-xs font-bold rounded-xl transition cursor-pointer"
                                >
                                    Done
                                </button>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </teleport>

    </div>
</template>
