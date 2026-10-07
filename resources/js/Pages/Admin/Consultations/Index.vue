<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    consultations: Object,
    metrics: Object,
    filters: Object,
    whatsappNumber: String,
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || 'all');
const format = ref(props.filters?.format || 'all');
const startDate = ref(props.filters?.start_date || '');
const endDate = ref(props.filters?.end_date || '');

const applyFilters = () => {
    router.get(route('admin.consultations.index'), {
        search: search.value || undefined,
        status: status.value !== 'all' ? status.value : undefined,
        format: format.value !== 'all' ? format.value : undefined,
        start_date: startDate.value || undefined,
        end_date: endDate.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const resetFilters = () => {
    search.value = '';
    status.value = 'all';
    format.value = 'all';
    startDate.value = '';
    endDate.value = '';
    applyFilters();
};

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-TZ', { style: 'currency', currency: 'TZS', maximumFractionDigits: 0 }).format(val || 0);
};

const formatDate = (val) => {
    if (!val) return '—';
    return new Date(val).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

const formatDateTime = (val) => {
    if (!val) return '—';
    return new Date(val).toLocaleString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

// Modal Edit State
const isEditModalOpen = ref(false);
const activeConsultation = ref(null);

const editForm = useForm({
    status: 'request_created',
    staff_notes: '',
    payment_reference: '',
    confirmed_date_time: '',
});

const openEditModal = (item) => {
    activeConsultation.value = item;
    editForm.status = item.status || 'request_created';
    editForm.staff_notes = item.staff_notes || '';
    editForm.payment_reference = item.payment_reference || '';
    editForm.confirmed_date_time = item.confirmed_date_time ? item.confirmed_date_time.slice(0, 16) : '';
    isEditModalOpen.value = true;
};

const closeEditModal = () => {
    isEditModalOpen.value = false;
    activeConsultation.value = null;
};

const saveConsultation = () => {
    if (!activeConsultation.value) return;

    editForm.patch(route('admin.consultations.update', activeConsultation.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeEditModal();
        }
    });
};

const getDirectCustomerWhatsAppLink = (phone, reference, name) => {
    if (!phone) return '#';
    let clean = phone.replace(/[^0-9]/g, '');
    if (clean.startsWith('0')) clean = '255' + clean.slice(1);
    else if (!clean.startsWith('255') && clean.length === 9) clean = '255' + clean;

    const msg = encodeURIComponent(`Habari ${name},\n\nTunawasiliana nawe kutoka Kitonga Farm kuhusu Ombi lako la Ushauri na Mr. Kitonga (Ref: *${reference}*).\n\nJe, ungependa tukusaidie vipi kuhusu ratiba na maelekezo ya malipo?`);
    return `https://wa.me/${clean}?text=${msg}`;
};

const statusOptions = [
    { value: 'request_created', label: 'Request Created' },
    { value: 'whatsapp_initiated', label: 'WhatsApp Initiated' },
    { value: 'awaiting_staff_response', label: 'Awaiting Staff Response' },
    { value: 'awaiting_payment', label: 'Awaiting Payment Instructions' },
    { value: 'payment_verified', label: 'Payment Verified' },
    { value: 'confirmed', label: 'Confirmed' },
    { value: 'completed', label: 'Completed' },
    { value: 'cancelled', label: 'Cancelled' },
];

const getStatusBadge = (statusKey) => {
    switch (statusKey) {
        case 'request_created':
            return { label: 'Request Created', class: 'bg-amber-100 text-amber-800 border-amber-300' };
        case 'whatsapp_initiated':
            return { label: 'WhatsApp Contact Initiated', class: 'bg-blue-100 text-blue-800 border-blue-300' };
        case 'awaiting_staff_response':
            return { label: 'Awaiting Staff Response', class: 'bg-purple-100 text-purple-800 border-purple-300' };
        case 'awaiting_payment':
            return { label: 'Awaiting Payment', class: 'bg-orange-100 text-orange-800 border-orange-300' };
        case 'payment_verified':
            return { label: 'Payment Verified', class: 'bg-teal-100 text-teal-800 border-teal-300' };
        case 'confirmed':
            return { label: 'Confirmed', class: 'bg-emerald-100 text-emerald-800 border-emerald-300' };
        case 'completed':
            return { label: 'Completed', class: 'bg-gray-100 text-gray-800 border-gray-300' };
        case 'cancelled':
            return { label: 'Cancelled', class: 'bg-red-100 text-red-800 border-red-300' };
        default:
            return { label: statusKey, class: 'bg-gray-100 text-gray-800 border-gray-300' };
    }
};
</script>

<template>
    <Head title="Meet Mr. Kitonga — Consultation Requests Desk" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-xl sm:text-2xl font-serif font-bold text-[#14231C]">
                        Meet Mr. Kitonga — Consultation Requests
                    </h2>
                    <p class="text-xs text-gray-600 mt-0.5">
                        Manage private 1-on-1 advisory sessions, manual WhatsApp coordination, payment verification, and schedule appointments.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a 
                        :href="route('meet.mr.kitonga')" 
                        target="_blank" 
                        class="px-3.5 py-2 bg-white text-gray-700 hover:bg-gray-50 border border-gray-300 rounded-lg text-xs font-bold uppercase tracking-wider transition shadow-2xs inline-flex items-center gap-1.5"
                    >
                        <span>View Landing Page</span>
                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    </a>
                </div>
            </div>
        </template>

        <div class="py-6 sm:py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <!-- ══════════════════════════════════════════════════════════════════
                 1. METRICS OVERVIEW CARDS
            ══════════════════════════════════════════════════════════════════ -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
                
                <div class="bg-white rounded-2xl p-4 border border-gray-200 shadow-xs space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block">Total Requests</span>
                    <span class="text-2xl font-extrabold text-[#14231C]">{{ metrics?.total_requests || 0 }}</span>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-blue-200/80 bg-blue-50/20 shadow-xs space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700 block">Pending Contact</span>
                    <span class="text-2xl font-extrabold text-blue-800">{{ metrics?.pending_contact || 0 }}</span>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-orange-200/80 bg-orange-50/20 shadow-xs space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-orange-700 block">Awaiting Payment</span>
                    <span class="text-2xl font-extrabold text-orange-800">{{ metrics?.awaiting_payment || 0 }}</span>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-emerald-200/80 bg-emerald-50/20 shadow-xs space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 block">Confirmed</span>
                    <span class="text-2xl font-extrabold text-emerald-800">{{ metrics?.confirmed || 0 }}</span>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-gray-200 shadow-xs space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-600 block">Completed</span>
                    <span class="text-2xl font-extrabold text-gray-800">{{ metrics?.completed || 0 }}</span>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-[#C98A3E]/40 bg-[#FAF8F5] shadow-xs space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#C98A3E] block">Verified Revenue</span>
                    <span class="text-base sm:text-lg font-extrabold text-[#14231C] leading-tight block">
                        {{ formatCurrency(metrics?.verified_revenue || 0) }}
                    </span>
                </div>

            </div>

            <!-- ══════════════════════════════════════════════════════════════════
                 2. SEARCH & FILTER TOOLBAR
            ══════════════════════════════════════════════════════════════════ -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-200 shadow-xs space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                    
                    <!-- Search Input -->
                    <div>
                        <label class="font-bold text-gray-600 uppercase text-[10px] block mb-1">Search Requests</label>
                        <input 
                            v-model="search"
                            @keyup.enter="applyFilters"
                            type="text" 
                            placeholder="Ref, Name, Phone, Topic..."
                            class="w-full text-xs rounded-xl border-gray-300 focus:border-[#14231C] focus:ring-1 focus:ring-[#14231C] p-2"
                        />
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="font-bold text-gray-600 uppercase text-[10px] block mb-1">Status Filter</label>
                        <select 
                            v-model="status" 
                            @change="applyFilters"
                            class="w-full text-xs rounded-xl border-gray-300 focus:border-[#14231C] focus:ring-1 focus:ring-[#14231C] p-2"
                        >
                            <option value="all">All Statuses</option>
                            <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                        </select>
                    </div>

                    <!-- Format Filter -->
                    <div>
                        <label class="font-bold text-gray-600 uppercase text-[10px] block mb-1">Format</label>
                        <select 
                            v-model="format" 
                            @change="applyFilters"
                            class="w-full text-xs rounded-xl border-gray-300 focus:border-[#14231C] focus:ring-1 focus:ring-[#14231C] p-2"
                        >
                            <option value="all">All Formats</option>
                            <option value="physical">Physical at Kitonga Farm</option>
                            <option value="hq_dar">Head Office (Dar es Salaam)</option>
                            <option value="online">Online Video Call</option>
                        </select>
                    </div>

                    <!-- Filter Action Buttons -->
                    <div class="flex items-end gap-2">
                        <button 
                            type="button" 
                            @click="applyFilters" 
                            class="flex-1 py-2 px-3 bg-[#14231C] hover:bg-[#C98A3E] text-white rounded-xl font-bold uppercase tracking-wider text-xs transition cursor-pointer"
                        >
                            Filter
                        </button>
                        <button 
                            type="button" 
                            @click="resetFilters" 
                            class="py-2 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-bold uppercase tracking-wider text-xs transition cursor-pointer"
                        >
                            Reset
                        </button>
                    </div>

                </div>
            </div>

            <!-- ══════════════════════════════════════════════════════════════════
                 3. CONSULTATION REQUESTS TABLE
            ══════════════════════════════════════════════════════════════════ -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs font-sans">
                        <thead class="bg-[#14231C] text-white uppercase text-[10px] tracking-wider">
                            <tr>
                                <th class="py-3.5 px-4 font-bold">Reference</th>
                                <th class="py-3.5 px-4 font-bold">Customer</th>
                                <th class="py-3.5 px-4 font-bold">Format & Topic</th>
                                <th class="py-3.5 px-4 font-bold">Requested Date/Time</th>
                                <th class="py-3.5 px-4 font-bold">Fee</th>
                                <th class="py-3.5 px-4 font-bold">Status</th>
                                <th class="py-3.5 px-4 font-bold">Payment & Confirmed</th>
                                <th class="py-3.5 px-4 font-bold text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            
                            <tr 
                                v-for="item in consultations.data" 
                                :key="item.id" 
                                class="hover:bg-gray-50/80 transition"
                            >
                                <!-- Reference -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="font-mono font-bold text-[#14231C] bg-gray-100 px-2 py-0.5 rounded border border-gray-200 block w-fit">
                                        {{ item.reference }}
                                    </span>
                                    <span class="text-[10px] text-gray-400 mt-1 block">
                                        {{ formatDateTime(item.created_at) }}
                                    </span>
                                </td>

                                <!-- Customer Details -->
                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-gray-900 block text-sm">{{ item.customer_name }}</span>
                                    <span class="text-gray-600 block">{{ item.customer_phone }}</span>
                                    <span v-if="item.customer_email" class="text-gray-400 text-[11px] block">{{ item.customer_email }}</span>
                                </td>

                                <!-- Format & Topic -->
                                <td class="py-3.5 px-4 max-w-xs">
                                    <span 
                                        :class="[
                                            'px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider inline-block mb-1 border',
                                            item.format === 'online' ? 'bg-blue-50 text-blue-800 border-blue-200' : (item.format === 'hq_dar' ? 'bg-amber-50 text-amber-900 border-amber-300' : 'bg-emerald-50 text-emerald-800 border-emerald-200')
                                        ]"
                                    >
                                        {{ item.format === 'online' ? 'Online Call' : (item.format === 'hq_dar' ? 'Dar HQ Office' : 'Physical Farm') }}
                                    </span>
                                    <span class="font-semibold text-gray-800 block truncate" :title="item.topic">
                                        {{ item.topic }}
                                    </span>
                                    <p v-if="item.message" class="text-[10px] text-gray-500 line-clamp-1 italic mt-0.5" :title="item.message">
                                        "{{ item.message }}"
                                    </p>
                                </td>

                                <!-- Requested Date & Time -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="font-semibold text-gray-800 block">{{ formatDate(item.preferred_date) }}</span>
                                    <span class="text-gray-500 text-[11px] block">{{ item.preferred_time }}</span>
                                </td>

                                <!-- Fee -->
                                <td class="py-3.5 px-4 whitespace-nowrap font-bold text-gray-900">
                                    {{ formatCurrency(item.fee) }}
                                </td>

                                <!-- Status Badge -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span 
                                        :class="[
                                            'px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border inline-block shadow-2xs',
                                            getStatusBadge(item.status).class
                                        ]"
                                    >
                                        {{ getStatusBadge(item.status).label }}
                                    </span>
                                </td>

                                <!-- Payment & Confirmed Date -->
                                <td class="py-3.5 px-4 text-xs">
                                    <div v-if="item.payment_reference">
                                        <span class="text-[10px] uppercase font-bold text-gray-400 block">Payment Ref:</span>
                                        <span class="font-mono font-bold text-emerald-800">{{ item.payment_reference }}</span>
                                    </div>
                                    <div v-if="item.confirmed_date_time" class="mt-1">
                                        <span class="text-[10px] uppercase font-bold text-gray-400 block">Confirmed For:</span>
                                        <span class="text-emerald-700 font-semibold">{{ formatDateTime(item.confirmed_date_time) }}</span>
                                    </div>
                                    <span v-if="!item.payment_reference && !item.confirmed_date_time" class="text-gray-400 italic text-[11px]">
                                        Pending staff action
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td class="py-3.5 px-4 whitespace-nowrap text-right space-x-2">
                                    <!-- Direct Customer WhatsApp Link -->
                                    <a 
                                        :href="getDirectCustomerWhatsAppLink(item.customer_phone, item.reference, item.customer_name)" 
                                        target="_blank"
                                        title="Chat with customer on WhatsApp"
                                        class="p-2 bg-[#25D366] hover:bg-[#20ba59] text-white rounded-lg transition inline-flex items-center justify-center shadow-2xs"
                                    >
                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-5.805 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                        </svg>
                                    </a>

                                    <!-- Manage Button -->
                                    <button 
                                        type="button" 
                                        @click="openEditModal(item)" 
                                        class="px-3 py-1.5 bg-[#14231C] hover:bg-[#C98A3E] text-white rounded-lg font-bold text-xs transition cursor-pointer shadow-2xs inline-flex items-center gap-1"
                                    >
                                        <span>Manage</span>
                                        <svg class="w-3.5 h-3.5 text-[#E6C387]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                    </button>
                                </td>
                            </tr>

                            <!-- Empty State -->
                            <tr v-if="!consultations.data || consultations.data.length === 0">
                                <td colspan="8" class="py-12 text-center text-gray-500 font-sans">
                                    <p class="text-sm font-semibold">No consultation requests found.</p>
                                    <p class="text-xs text-gray-400 mt-1">Requests submitted through the "Meet Mr. Kitonga" landing page will appear here.</p>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="consultations.links && consultations.links.length > 3" class="px-6 py-4 border-t border-gray-200 flex justify-between items-center text-xs">
                    <span class="text-gray-500">
                        Showing {{ consultations.from || 0 }} to {{ consultations.to || 0 }} of {{ consultations.total || 0 }} requests
                    </span>
                    <div class="flex space-x-1">
                        <Link 
                            v-for="(link, idx) in consultations.links" 
                            :key="idx" 
                            :href="link.url || '#'" 
                            v-html="link.label"
                            :class="[
                                'px-3 py-1.5 rounded-md transition font-medium',
                                link.active ? 'bg-[#14231C] text-white font-bold' : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                                !link.url ? 'opacity-40 pointer-events-none' : ''
                            ]"
                        />
                    </div>
                </div>

            </div>

        </div>

        <!-- ══════════════════════════════════════════════════════════════════════
             4. STAFF WORKFLOW & EDIT MODAL
        ══════════════════════════════════════════════════════════════════════ -->
        <teleport to="body">
            <div 
                v-if="isEditModalOpen && activeConsultation" 
                class="fixed inset-0 z-50 bg-black/75 backdrop-blur-xs flex justify-center items-center p-4 overflow-y-auto"
                @click.self="closeEditModal"
            >
                <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl border border-gray-200 relative my-8 text-gray-900 font-sans space-y-6">
                    
                    <!-- Close button -->
                    <button 
                        @click="closeEditModal" 
                        class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-2xl font-light cursor-pointer w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition"
                    >
                        ✕
                    </button>

                    <!-- Header -->
                    <div class="border-b border-gray-200 pb-4">
                        <span class="text-[10px] uppercase tracking-[2px] font-bold text-[#C98A3E] block">
                            Staff Coordination Desk
                        </span>
                        <h3 class="text-xl sm:text-2xl font-serif font-bold text-[#14231C]">
                            Consultation #{{ activeConsultation.reference }}
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Customer: <strong class="text-gray-900">{{ activeConsultation.customer_name }}</strong> ({{ activeConsultation.customer_phone }})
                        </p>
                    </div>

                    <!-- Customer Request Summary -->
                    <div class="bg-[#FAF8F5] rounded-2xl p-4 border border-gray-200 space-y-2 text-xs">
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-gray-400 block">Meeting Format</span>
                                <span class="font-bold text-gray-900">{{ activeConsultation.format === 'online' ? 'Online Video Call' : 'Physical at Farm' }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase font-bold text-gray-400 block">Requested Date</span>
                                <span class="font-bold text-gray-900">{{ formatDate(activeConsultation.preferred_date) }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase font-bold text-gray-400 block">Preferred Time</span>
                                <span class="font-bold text-gray-900">{{ activeConsultation.preferred_time }}</span>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-gray-200/80">
                            <span class="text-[10px] uppercase font-bold text-gray-400 block">Topic / Purpose:</span>
                            <span class="font-semibold text-[#14231C]">{{ activeConsultation.topic }}</span>
                        </div>

                        <div v-if="activeConsultation.message" class="pt-1">
                            <span class="text-[10px] uppercase font-bold text-gray-400 block">Customer Message / Context:</span>
                            <p class="text-gray-700 bg-white p-2.5 rounded-lg border border-gray-200 mt-1 italic">
                                "{{ activeConsultation.message }}"
                            </p>
                        </div>
                    </div>

                    <!-- Form to Update Status, Payment & Notes -->
                    <form @submit.prevent="saveConsultation" class="space-y-4 text-xs">
                        
                        <!-- Status Selector -->
                        <div>
                            <label class="font-bold text-gray-700 uppercase text-[10px] block mb-1">
                                Update Request Status *
                            </label>
                            <select 
                                v-model="editForm.status"
                                class="w-full text-xs rounded-xl border-gray-300 focus:border-[#14231C] focus:ring-1 focus:ring-[#14231C] p-2.5 font-bold"
                            >
                                <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
                                    {{ opt.label }}
                                </option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            
                            <!-- Payment Reference -->
                            <div>
                                <label class="font-bold text-gray-700 uppercase text-[10px] block mb-1">
                                    Payment Verification Reference (e.g. M-Pesa Code)
                                </label>
                                <input 
                                    v-model="editForm.payment_reference"
                                    type="text" 
                                    placeholder="e.g. QK89271892 or Cash Receipt"
                                    class="w-full text-xs rounded-xl border-gray-300 focus:border-[#14231C] focus:ring-1 focus:ring-[#14231C] p-2.5"
                                />
                            </div>

                            <!-- Confirmed Appointment Date & Time -->
                            <div>
                                <label class="font-bold text-gray-700 uppercase text-[10px] block mb-1">
                                    Confirmed Appointment Date & Time
                                </label>
                                <input 
                                    v-model="editForm.confirmed_date_time"
                                    type="datetime-local" 
                                    class="w-full text-xs rounded-xl border-gray-300 focus:border-[#14231C] focus:ring-1 focus:ring-[#14231C] p-2.5"
                                />
                            </div>

                        </div>

                        <!-- Internal Staff Notes -->
                        <div>
                            <label class="font-bold text-gray-700 uppercase text-[10px] block mb-1">
                                Internal Staff Notes (Audit & Follow-up Log)
                            </label>
                            <textarea 
                                v-model="editForm.staff_notes"
                                rows="3" 
                                placeholder="Record notes from WhatsApp chat, agreed venue, meeting link, or special prep instructions..."
                                class="w-full text-xs rounded-xl border-gray-300 focus:border-[#14231C] focus:ring-1 focus:ring-[#14231C] p-2.5"
                            ></textarea>
                        </div>

                        <!-- Action Buttons -->
                        <div class="pt-2 flex items-center justify-between gap-3 border-t border-gray-200">
                            <a 
                                :href="getDirectCustomerWhatsAppLink(activeConsultation.customer_phone, activeConsultation.reference, activeConsultation.customer_name)" 
                                target="_blank"
                                class="px-4 py-2.5 bg-[#25D366] hover:bg-[#20ba59] text-white rounded-xl font-bold uppercase tracking-wider text-xs transition inline-flex items-center gap-2 shadow-2xs"
                            >
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-5.805 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                </svg>
                                <span>Open Customer Chat</span>
                            </a>

                            <div class="flex items-center gap-2">
                                <button 
                                    type="button" 
                                    @click="closeEditModal" 
                                    class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-bold uppercase tracking-wider text-xs transition"
                                >
                                    Cancel
                                </button>
                                <button 
                                    type="submit" 
                                    :disabled="editForm.processing"
                                    class="px-5 py-2.5 bg-[#14231C] hover:bg-[#C98A3E] text-white rounded-xl font-bold uppercase tracking-wider text-xs transition shadow-md disabled:opacity-50"
                                >
                                    Save Updates
                                </button>
                            </div>
                        </div>

                    </form>

                </div>
            </div>
        </teleport>

    </AuthenticatedLayout>
</template>
