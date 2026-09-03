<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    villas: Array,
    availability: Object,
    search: Object,
    settings: Object,
});

const isMobileMenuOpen = ref(false);
const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

const step = ref(1); // 1 = Select Dates/Villa, 2 = Guest Details, 3 = Review
const selectedVilla = ref(null);

const bookingForm = useForm({
    accommodation_type_id: '',
    check_in: props.search?.check_in || new Date().toISOString().split('T')[0],
    check_out: props.search?.check_out || new Date(Date.now() + 86400000).toISOString().split('T')[0],
    guests_count: props.search?.guests || '2',
    customer_name: '',
    customer_phone: '',
    customer_email: '',
    notes: '',
});

const calculateNights = () => {
    const start = new Date(bookingForm.check_in);
    const end = new Date(bookingForm.check_out);
    const diff = end - start;
    return Math.max(1, Math.round(diff / (1000 * 60 * 60 * 24)));
};

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-TZ', { style: 'currency', currency: 'TZS', maximumFractionDigits: 0 }).format(val || 0);
};

const subtotal = () => {
    if (!selectedVilla.value) return 0;
    return selectedVilla.value.base_price * calculateNights();
};

const tax = () => {
    const rate = parseFloat(props.settings?.tax_rate || 18);
    return subtotal() * (rate / 100);
};

const total = () => {
    return subtotal() + tax();
};

const deposit = () => {
    const pct = parseFloat(props.settings?.deposit_percentage || 50);
    return total() * (pct / 100);
};

const selectVillaOption = (villa) => {
    selectedVilla.value = villa;
    bookingForm.accommodation_type_id = villa.id;
    step.value = 2;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const submitBooking = () => {
    bookingForm.post(route('booking.store'), {
        onError: (err) => {
            alert(Object.values(err).join('\n'));
        }
    });
};
</script>

<template>
    <Head title="Direct Villa Reservation — Kitonga Farm Villas" />

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
                <Link :href="route('contact')" prefetch class="hover:text-[#C98A3E] transition duration-200">Contact</Link>
                <Link :href="route('login')" prefetch class="hover:text-[#C98A3E] transition duration-200">Sign In</Link>
            </nav>

            <!-- Desktop Active Step Badge -->
            <div class="hidden md:inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 text-white text-xs font-bold uppercase tracking-wider">
                <span>Step {{ step }} of 3</span>
            </div>

            <!-- Mobile Hamburger Drawer Button -->
            <div class="flex items-center gap-2 md:hidden">
                <span class="text-xs font-bold text-[#E6C387]">Step {{ step }}/3</span>
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
                <Link :href="route('contact')" @click="isMobileMenuOpen = false" class="hover:text-[#C98A3E] transition py-1">Contact</Link>
                <Link :href="route('login')" @click="isMobileMenuOpen = false" class="hover:text-[#C98A3E] transition py-1">Sign In</Link>
            </div>
        </div>

        <!-- 2. MAIN BOOKING WIZARD CONTAINER -->
        <div class="max-w-4xl mx-auto py-10 sm:py-14 px-4 sm:px-6">
            
            <!-- STEP INDICATORS -->
            <div class="flex justify-between items-center mb-8 max-w-xs sm:max-w-md mx-auto">
                <div class="flex flex-col items-center">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs transition-colors" :class="step >= 1 ? 'bg-[#14231C] text-[#E6C387]' : 'bg-gray-200 text-gray-400'">1</div>
                    <span class="text-[10px] uppercase font-bold tracking-wider text-gray-500 mt-1.5">Dates & Villa</span>
                </div>
                <div class="flex-1 h-0.5 bg-gray-200 mx-2" :class="{ 'bg-[#14231C]': step >= 2 }"></div>
                <div class="flex flex-col items-center">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs transition-colors" :class="step >= 2 ? 'bg-[#14231C] text-[#E6C387]' : 'bg-gray-200 text-gray-400'">2</div>
                    <span class="text-[10px] uppercase font-bold tracking-wider text-gray-500 mt-1.5">Guest Profile</span>
                </div>
                <div class="flex-1 h-0.5 bg-gray-200 mx-2" :class="{ 'bg-[#14231C]': step >= 3 }"></div>
                <div class="flex flex-col items-center">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs transition-colors" :class="step >= 3 ? 'bg-[#14231C] text-[#E6C387]' : 'bg-gray-200 text-gray-400'">3</div>
                    <span class="text-[10px] uppercase font-bold tracking-wider text-gray-500 mt-1.5">Review</span>
                </div>
            </div>

            <!-- STEP 1: CHOOSE DATE & SELECT VILLA -->
            <div v-if="step === 1" class="space-y-6">
                
                <!-- Date Search Widget -->
                <form @submit.prevent="window.location.reload()" class="bg-white p-5 sm:p-6 rounded-2xl border border-gray-200 shadow-xs grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Check-In Date</label>
                        <input type="date" v-model="bookingForm.check_in" class="w-full text-xs p-2.5 rounded-xl border border-gray-300 focus:outline-none focus:border-[#C98A3E]" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Check-Out Date</label>
                        <input type="date" v-model="bookingForm.check_out" class="w-full text-xs p-2.5 rounded-xl border border-gray-300 focus:outline-none focus:border-[#C98A3E]" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Guests Count</label>
                        <select v-model="bookingForm.guests_count" class="w-full text-xs p-2.5 rounded-xl border border-gray-300 focus:outline-none focus:border-[#C98A3E]">
                            <option value="1">1 Guest</option>
                            <option value="2">2 Guests</option>
                            <option value="4">4 Guests</option>
                            <option value="6">6 Guests</option>
                        </select>
                    </div>
                    <div>
                        <button type="button" @click="window.location.reload()" class="w-full py-2.5 bg-[#14231C] hover:bg-[#C98A3E] text-white font-bold text-xs uppercase tracking-wider rounded-xl transition duration-200 cursor-pointer shadow-xs">
                            Update Dates
                        </button>
                    </div>
                </form>

                <!-- Villa Selection Cards -->
                <div class="space-y-4">
                    <h3 class="font-serif font-bold text-xl text-gray-900">Available Accommodations ({{ calculateNights() }} Nights)</h3>
                    
                    <div 
                        v-for="villa in villas" 
                        :key="villa.id" 
                        class="bg-white p-5 sm:p-6 rounded-2xl border border-gray-200 shadow-xs flex flex-col md:flex-row justify-between items-start md:items-center gap-5 transition-all" 
                        :class="{ 'opacity-65 pointer-events-none': !availability[villa.id]?.available }"
                    >
                        <div class="space-y-2 flex-1">
                            <div class="flex items-center gap-2">
                                <h4 class="font-serif font-bold text-lg text-gray-900">{{ villa.name }}</h4>
                                <span v-if="villa.has_interior_kitchen" class="text-[9px] uppercase font-bold tracking-wider px-2 py-0.5 bg-emerald-50 text-emerald-800 rounded">Kitchen</span>
                            </div>
                            <p class="text-xs text-gray-600 max-w-md leading-relaxed">{{ villa.description }}</p>
                            <div class="flex flex-wrap gap-3 text-[11px] text-gray-500 font-semibold pt-1">
                                <span>👥 Max {{ villa.capacity }} Guests</span>
                                <span>•</span>
                                <span>🛏️ {{ villa.bedrooms }} BR / {{ villa.beds }} Beds</span>
                            </div>
                        </div>

                        <div class="w-full md:w-auto flex md:flex-col justify-between items-center md:items-end gap-3 pt-3 md:pt-0 border-t md:border-t-0 border-gray-100">
                            <div>
                                <span class="text-[10px] text-gray-400 block uppercase tracking-wider md:text-right">Rate per night</span>
                                <span class="font-bold text-base sm:text-lg text-emerald-900 font-mono">{{ formatCurrency(villa.base_price) }}</span>
                            </div>
                            
                            <div v-if="availability[villa.id]?.available">
                                <button 
                                    @click="selectVillaOption(villa)" 
                                    class="px-5 py-2.5 bg-[#14231C] hover:bg-[#C98A3E] text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-xs transition cursor-pointer"
                                >
                                    Select Villa →
                                </button>
                            </div>
                            <div v-else class="text-xs font-bold text-red-500">SOLD OUT / BLOCKED</div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- STEP 2: GUEST PROFILE DETAILS -->
            <div v-if="step === 2" class="bg-white p-6 sm:p-8 rounded-2xl border border-gray-200 shadow-xs space-y-6">
                <div>
                    <h3 class="font-serif font-bold text-2xl text-gray-900">Guest Profile Details</h3>
                    <p class="text-xs text-gray-500 mt-1">Please provide the lead guest's contact information for your booking:</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Full Name *</label>
                        <input type="text" v-model="bookingForm.customer_name" placeholder="e.g. David Mwangi" class="w-full text-xs p-3 rounded-xl border border-gray-300 focus:outline-none focus:border-[#C98A3E]" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Phone / WhatsApp *</label>
                        <input type="text" v-model="bookingForm.customer_phone" placeholder="+255 7..." class="w-full text-xs p-3 rounded-xl border border-gray-300 focus:outline-none focus:border-[#C98A3E]" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Email Address *</label>
                        <input type="email" v-model="bookingForm.customer_email" placeholder="david@example.com" class="w-full text-xs p-3 rounded-xl border border-gray-300 focus:outline-none focus:border-[#C98A3E]" required>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Special Requests / Dietary Notes</label>
                        <textarea v-model="bookingForm.notes" rows="3" placeholder="Dietary preferences, arrival time, airport transfer inquiry..." class="w-full text-xs p-3 rounded-xl border border-gray-300 focus:outline-none focus:border-[#C98A3E]"></textarea>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-gray-150">
                    <button 
                        type="button" 
                        @click="step = 3" 
                        :disabled="!bookingForm.customer_name || !bookingForm.customer_phone || !bookingForm.customer_email"
                        class="px-6 py-3 bg-[#14231C] hover:bg-[#C98A3E] text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-xs transition cursor-pointer disabled:opacity-40"
                    >
                        Continue to Review →
                    </button>
                    <button 
                        type="button" 
                        @click="step = 1" 
                        class="px-5 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold uppercase tracking-wider rounded-xl transition cursor-pointer"
                    >
                        ← Back
                    </button>
                </div>
            </div>

            <!-- STEP 3: REVIEW SUMMARY -->
            <div v-if="step === 3" class="bg-white p-6 sm:p-8 rounded-2xl border border-gray-200 shadow-xs space-y-6">
                <div>
                    <h3 class="font-serif font-bold text-2xl text-gray-900">Review Reservation Details</h3>
                    <p class="text-xs text-gray-500 mt-1">Please confirm your reservation itinerary before submitting:</p>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 p-4 rounded-xl bg-gray-50 border border-gray-200 text-xs">
                    <div class="space-y-1">
                        <span class="font-bold text-gray-400 uppercase text-[10px] block">Lead Guest</span>
                        <p class="font-bold text-gray-900">{{ bookingForm.customer_name }}</p>
                        <p class="text-gray-600">{{ bookingForm.customer_phone }}</p>
                        <p class="text-gray-600">{{ bookingForm.customer_email }}</p>
                    </div>
                    <div class="space-y-1">
                        <span class="font-bold text-gray-400 uppercase text-[10px] block">Villa Stay</span>
                        <p class="font-bold text-gray-900">{{ selectedVilla?.name }}</p>
                        <p class="text-gray-600">{{ bookingForm.check_in }} to {{ bookingForm.check_out }} ({{ calculateNights() }} Nights)</p>
                        <p class="text-gray-600">{{ bookingForm.guests_count }} Guests Occupancy</p>
                    </div>
                </div>

                <div class="border-t border-b border-gray-200 py-4 space-y-2 text-xs font-mono">
                    <div class="flex justify-between text-gray-600">
                        <span>Stay Subtotal ({{ calculateNights() }} Nights):</span>
                        <span>{{ formatCurrency(subtotal()) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>VAT ({{ settings?.tax_rate || 18 }}%):</span>
                        <span>{{ formatCurrency(tax()) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-sm text-gray-900 border-t border-gray-200 pt-2">
                        <span>Grand Total:</span>
                        <span>{{ formatCurrency(total()) }}</span>
                    </div>
                    <div class="flex justify-between text-emerald-800 text-xs font-bold pt-1">
                        <span>Required Deposit ({{ settings?.deposit_percentage || 50 }}%):</span>
                        <span>{{ formatCurrency(deposit()) }}</span>
                    </div>
                </div>

                <div class="bg-emerald-50 p-4 rounded-xl text-xs space-y-1.5 text-emerald-900 border border-emerald-200">
                    <p class="font-bold">Confirmation & Payment Terms</p>
                    <p class="text-[11px] leading-relaxed">Our concierge will review your reservation dates and contact you via WhatsApp / Email with payment confirmation details.</p>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-gray-150">
                    <button 
                        type="button" 
                        @click="submitBooking" 
                        :disabled="bookingForm.processing"
                        class="px-6 py-3.5 bg-[#14231C] hover:bg-[#C98A3E] text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-md transition cursor-pointer disabled:opacity-50"
                    >
                        {{ bookingForm.processing ? 'Submitting...' : 'Confirm & Submit Reservation →' }}
                    </button>
                    <button 
                        type="button" 
                        @click="step = 2" 
                        class="px-5 py-3.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold uppercase tracking-wider rounded-xl transition cursor-pointer"
                    >
                        ← Back
                    </button>
                </div>
            </div>

        </div>

        <!-- 3. FOOTER -->
        <footer class="bg-[#14231C] text-gray-400 text-xs py-12 border-t border-white/10 font-sans">
            <div class="max-w-6xl mx-auto px-6 grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="space-y-2">
                    <p class="font-bold text-white text-sm font-serif uppercase tracking-widest">KITONGA FARM VILLAS</p>
                    <p class="text-gray-400 text-xs leading-relaxed">Direct countryside villa reservations in Komkonga, Tanga, Tanzania.</p>
                </div>
                <div class="space-y-1.5">
                    <p class="font-bold text-white uppercase tracking-wider text-xs">Direct Concierge Desk</p>
                    <p class="text-gray-400">Phone / WhatsApp: +255 758 774 695</p>
                    <p class="text-gray-400">Email: info@kitongafarmvillas.com</p>
                </div>
                <div class="space-y-1.5">
                    <p class="font-bold text-white uppercase tracking-wider text-xs">Guaranteed Best Rates</p>
                    <p class="text-gray-400">Booking directly ensures complimentary farm breakfast and priority check-in.</p>
                </div>
            </div>
            <div class="max-w-6xl mx-auto px-6 mt-8 pt-5 border-t border-white/10 text-center text-gray-500">
                © 2026 Kitonga Farm Villas. All rights reserved.
            </div>
        </footer>

    </div>
</template>
