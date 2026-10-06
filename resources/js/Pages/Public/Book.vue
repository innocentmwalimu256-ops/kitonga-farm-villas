<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import SEOHead from '@/Components/SEOHead.vue';
import PublicNavbar from '@/Components/PublicNavbar.vue';

const props = defineProps({
    villas: Array,
    availability: Object,
    search: Object,
    settings: Object,
});

const preselectedVilla = props.villas?.find(v => v.id == props.search?.villa_id) || null;
const selectedVilla = ref(preselectedVilla);
const step = ref(preselectedVilla ? 2 : 1); // 1 = Select Dates/Villa, 2 = Guest Details, 3 = Review

const fileInputRef = ref(null);
const idPreview = ref(null);
const isPdfFile = ref(false);
const fileName = ref('');

const idTypeLabels = {
    nida: 'National ID (NIDA) / Kitambulisho cha Taifa',
    passport: 'International Passport / Pasi ya Kusafiria',
    driving_license: 'Driving License / Leseni ya Udereva',
    voter_id: 'Voter ID / Kitambulisho cha Mpiga Kura',
    other: 'Other Official ID / Kitambulisho Kingine',
};

const bookingForm = useForm({
    accommodation_type_id: preselectedVilla ? preselectedVilla.id : '',
    check_in: props.search?.check_in || new Date().toISOString().split('T')[0],
    check_out: props.search?.check_out || new Date(Date.now() + 86400000).toISOString().split('T')[0],
    guests_count: props.search?.guests ? String(props.search.guests) : '1',
    customer_name: '',
    customer_phone: '',
    customer_email: '',
    id_type: 'nida',
    id_number: '',
    id_document: null,
    notes: '',
});

const handleIdFileUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
        if (file.size > 10 * 1024 * 1024) {
            alert('File size exceeds 10MB limit. Please choose a smaller photo.');
            return;
        }
        bookingForm.id_document = file;
        fileName.value = file.name;
        if (file.type === 'application/pdf') {
            isPdfFile.value = true;
            idPreview.value = null;
        } else if (file.type.startsWith('image/')) {
            isPdfFile.value = false;
            const reader = new FileReader();
            reader.onload = (ev) => {
                idPreview.value = ev.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
};

const removeIdDocument = () => {
    bookingForm.id_document = null;
    idPreview.value = null;
    isPdfFile.value = false;
    fileName.value = '';
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
};

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

const guestError = ref('');
const maxBookingCapacity = computed(() => {
    return selectedVilla.value ? Number(selectedVilla.value.capacity) || 2 : 10;
});

const validateBookingGuests = () => {
    if (bookingForm.guests_count === '' || bookingForm.guests_count === null) {
        return;
    }
    const val = parseInt(bookingForm.guests_count, 10);
    const max = maxBookingCapacity.value;
    if (isNaN(val) || val < 1) {
        guestError.value = 'Minimum 1 guest required';
        bookingForm.guests_count = '1';
    } else if (val > max) {
        guestError.value = `Maximum ${max} guests allowed for ${selectedVilla.value?.name || 'this villa'}`;
        bookingForm.guests_count = String(max);
    } else {
        guestError.value = '';
        bookingForm.guests_count = String(val);
    }
};

const incrementBookingGuests = () => {
    const val = parseInt(bookingForm.guests_count || '1', 10);
    const max = maxBookingCapacity.value;
    if (val < max) {
        bookingForm.guests_count = String(val + 1);
        guestError.value = '';
    } else {
        guestError.value = `Maximum ${max} guests allowed for ${selectedVilla.value?.name || 'this villa'}`;
    }
};

const decrementBookingGuests = () => {
    const val = parseInt(bookingForm.guests_count || '1', 10);
    if (val > 1) {
        bookingForm.guests_count = String(val - 1);
        guestError.value = '';
    } else {
        guestError.value = 'Minimum 1 guest required';
    }
};

const selectVillaOption = (villa) => {
    selectedVilla.value = villa;
    bookingForm.accommodation_type_id = villa.id;
    if (parseInt(bookingForm.guests_count) > villa.capacity) {
        bookingForm.guests_count = String(villa.capacity);
    }
    guestError.value = '';
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
    <SEOHead 
        title="Direct Villa Reservation & Booking"
        description="Book your luxury private villa or farm getaway directly at Kitonga Farm Villas. Best rate guarantee, instant booking confirmation, and bespoke concierge service."
        canonical="/book"
        og-image="/images/luxury_villa_img.webp"
    />

    <div class="bg-[#FAF8F5] text-[#1F2420] font-sans min-h-screen selection:bg-[#C98A3E] selection:text-white">
        
        <!-- 1. STICKY TOP NAVBAR -->
        <PublicNavbar />

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
                            <option value="1">1 Guest (1 Person)</option>
                            <option value="2">2 Guests</option>
                            <option value="3">3 Guests</option>
                            <option value="4">4 Guests</option>
                            <option value="5">5 Guests</option>
                            <option value="6">6 Guests</option>
                            <option value="8">8 Guests</option>
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
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-100 pb-4">
                    <div>
                        <h3 class="font-serif font-bold text-2xl text-gray-900">Guest Profile Details</h3>
                        <p class="text-xs text-gray-500 mt-1">Please provide the lead guest's contact information and party size:</p>
                    </div>
                    <div v-if="selectedVilla" class="px-3 py-1.5 bg-[#FAF8F5] rounded-xl border border-gray-200 text-xs font-semibold text-gray-800 flex items-center gap-2">
                        <span>🏡 {{ selectedVilla.name }}</span>
                        <span class="text-gray-400 text-[10px]">(Max {{ selectedVilla.capacity }} Guests)</span>
                    </div>
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
                    
                    <!-- DIGITAL GUEST ID & VERIFICATION SECTION -->
                    <div class="sm:col-span-2 bg-[#FAF8F5] p-4 sm:p-5 rounded-2xl border border-gray-200 space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-[#14231C] text-[#E6C387] flex items-center justify-center text-xs font-bold">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Digital ID / Express Check-In</h4>
                                    <p class="text-[11px] text-gray-500">Picha ya Kitambulisho kwa ajili ya usajili wa kidijitali</p>
                                </div>
                            </div>
                            <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-full">Contactless Check-in</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Aina ya Kitambulisho / ID Type</label>
                                <select v-model="bookingForm.id_type" class="w-full text-xs p-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-[#C98A3E]">
                                    <option value="nida">Kitambulisho cha Taifa (NIDA)</option>
                                    <option value="passport">Passport / Pasi ya Kusafiria</option>
                                    <option value="driving_license">Leseni ya Udereva (Driver's License)</option>
                                    <option value="voter_id">Kitambulisho cha Mpiga Kura (Voter's Card)</option>
                                    <option value="other">Kinginecho (Other Official ID)</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Namba ya Kitambulisho / ID Number</label>
                                <input type="text" v-model="bookingForm.id_number" placeholder="Mf. 19900101-XXXXX-XXXXX" class="w-full text-xs p-3 rounded-xl border border-gray-300 bg-white focus:outline-none focus:border-[#C98A3E]">
                            </div>
                        </div>

                        <!-- ID Document Photo Upload -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block">
                                Picha ya Kitambulisho / Upload ID Photo or Document
                            </label>
                            
                            <!-- File input trigger container -->
                            <div v-if="!bookingForm.id_document" class="border-2 border-dashed border-gray-300 hover:border-[#C98A3E] bg-white rounded-xl p-5 text-center transition cursor-pointer" @click="fileInputRef.click()">
                                <input 
                                    ref="fileInputRef"
                                    type="file" 
                                    accept="image/*,application/pdf" 
                                    @change="handleIdFileUpload" 
                                    class="hidden"
                                />
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <div class="w-10 h-10 rounded-full bg-amber-50 text-[#C98A3E] flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="text-xs font-semibold text-gray-800">
                                        <span class="text-[#C98A3E] underline">Bofya hapa kupakia</span> au piga picha ya kitambulisho
                                    </div>
                                    <p class="text-[10px] text-gray-400">Inasaidia JPG, PNG, WEBP, au PDF (Max 10MB)</p>
                                </div>
                            </div>

                            <!-- Preview of uploaded ID -->
                            <div v-else class="bg-white p-3 sm:p-4 rounded-xl border border-gray-200 flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div v-if="idPreview" class="w-16 h-12 rounded-lg border border-gray-200 overflow-hidden bg-gray-100 shrink-0">
                                        <img :src="idPreview" alt="ID Document Preview" class="w-full h-full object-cover">
                                    </div>
                                    <div v-else class="w-16 h-12 rounded-lg border border-gray-200 bg-red-50 text-red-700 flex items-center justify-center font-bold text-xs shrink-0">
                                        PDF
                                    </div>
                                    <div class="space-y-0.5">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs font-bold text-gray-900">{{ fileName || 'Uploaded Document' }}</span>
                                            <span class="text-[9px] px-1.5 py-0.5 bg-emerald-50 text-emerald-700 font-bold rounded">Attached</span>
                                        </div>
                                        <p class="text-[10px] text-gray-500">Kitambulisho kimehifadhiwa tayari kwa usajili</p>
                                    </div>
                                </div>
                                <button 
                                    type="button" 
                                    @click="removeIdDocument" 
                                    class="px-3 py-1.5 text-xs font-bold text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition cursor-pointer"
                                >
                                    Ondoa / Change
                                </button>
                            </div>

                            <p class="text-[10px] text-gray-400 italic">
                                * Picha ya kitambulisho itakuepusha na usumbufu wa kutoa copy ukiwasili reception.
                            </p>
                        </div>
                    </div>

                    <div class="sm:col-span-2 space-y-1">
                        <div class="flex justify-between items-center">
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block">
                                Number of Guests (1 to {{ maxBookingCapacity }} people) *
                            </label>
                            <span v-if="guestError" class="text-[10px] text-red-600 font-bold">
                                {{ guestError }}
                            </span>
                        </div>
                        
                        <div class="flex items-center rounded-xl border border-gray-300 overflow-hidden bg-white shadow-xs focus-within:border-[#C98A3E] focus-within:ring-1 focus-within:ring-[#C98A3E]">
                            <button 
                                type="button" 
                                @click="decrementBookingGuests" 
                                :disabled="parseInt(bookingForm.guests_count || '1') <= 1"
                                class="px-4 py-2.5 bg-gray-50 hover:bg-gray-100 text-gray-700 font-bold text-sm border-r border-gray-200 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer"
                                title="Punguza wageni"
                            >
                                −
                            </button>
                            
                            <input 
                                type="number" 
                                v-model="bookingForm.guests_count" 
                                @input="validateBookingGuests"
                                @blur="validateBookingGuests"
                                min="1" 
                                :max="maxBookingCapacity"
                                class="w-full text-center text-xs font-bold text-gray-900 border-0 focus:ring-0 py-2.5 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                placeholder="1"
                                required
                            />
                            
                            <button 
                                type="button" 
                                @click="incrementBookingGuests" 
                                :disabled="parseInt(bookingForm.guests_count || '1') >= maxBookingCapacity"
                                class="px-4 py-2.5 bg-gray-50 hover:bg-gray-100 text-gray-700 font-bold text-sm border-l border-gray-200 disabled:opacity-30 disabled:cursor-not-allowed transition cursor-pointer"
                                title="Ongeza wageni"
                            >
                                +
                            </button>
                        </div>
                        <p class="text-[10px] text-gray-400">
                            {{ bookingForm.guests_count == '1' || bookingForm.guests_count == 1 ? '1 Guest (Single Occupancy)' : bookingForm.guests_count + ' Guests (Max ' + maxBookingCapacity + ' for ' + (selectedVilla?.name || 'this room') + ')' }}
                        </p>
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
                
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 p-4 rounded-xl bg-gray-50 border border-gray-200 text-xs">
                    <div class="space-y-1">
                        <span class="font-bold text-gray-400 uppercase text-[10px] block">Lead Guest</span>
                        <p class="font-bold text-gray-900">{{ bookingForm.customer_name }}</p>
                        <p class="text-gray-600">{{ bookingForm.customer_phone }}</p>
                        <p class="text-gray-600">{{ bookingForm.customer_email }}</p>
                    </div>
                    <div class="space-y-1">
                        <span class="font-bold text-gray-400 uppercase text-[10px] block">Digital ID Profile</span>
                        <p class="font-bold text-gray-900">{{ idTypeLabels[bookingForm.id_type] || bookingForm.id_type }}</p>
                        <p class="text-gray-600 font-mono">{{ bookingForm.id_number || 'Number not provided' }}</p>
                        <p v-if="bookingForm.id_document" class="text-emerald-700 font-bold text-[11px] flex items-center gap-1">
                            <span>✓</span> Document Attached
                        </p>
                        <p v-else class="text-gray-400 text-[11px]">No Photo Attached</p>
                    </div>
                    <div class="space-y-1">
                        <span class="font-bold text-gray-400 uppercase text-[10px] block">Villa Stay</span>
                        <p class="font-bold text-gray-900">{{ selectedVilla?.name }}</p>
                        <p class="text-gray-600">{{ bookingForm.check_in }} to {{ bookingForm.check_out }} ({{ calculateNights() }} Nights)</p>
                        <p class="text-gray-600">{{ bookingForm.guests_count }} {{ bookingForm.guests_count == '1' || bookingForm.guests_count == 1 ? 'Guest (1 Person)' : 'Guests' }} Occupancy</p>
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
