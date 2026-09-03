<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    bookings: Array,
    units: Array,
    blocks: Array,
    can_reschedule: Boolean,
    current_month: String,
    current_year: String,
});

const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

const viewMode = ref('month'); // month, week, day
const selectedMonth = ref(parseInt(props.current_month));
const selectedYear = ref(parseInt(props.current_year));
const selectedBooking = ref(null);
const showRescheduleForm = ref(false);

const rescheduleForm = useForm({
    check_in: '',
    check_out: '',
});

// Generate days of the month
const daysInMonth = computed(() => {
    return new Date(selectedYear.value, selectedMonth.value, 0).getDate();
});

const calendarDays = computed(() => {
    const list = [];
    const firstDayIndex = new Date(selectedYear.value, selectedMonth.value - 1, 1).getDay();
    
    // Previous month padding
    for (let i = 0; i < firstDayIndex; i++) {
        list.push({ day: null, dateString: null });
    }

    // Days of current month
    for (let day = 1; day <= daysInMonth.value; day++) {
        const dateStr = `${selectedYear.value}-${String(selectedMonth.value).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        list.push({
            day,
            dateString: dateStr,
        });
    }

    return list;
});

const getEventsForDate = (dateStr) => {
    if (!dateStr) return [];
    
    const items = [];
    
    // Add Bookings
    props.bookings.forEach(b => {
        if (dateStr >= b.check_in && dateStr < b.check_out) {
            items.push({
                type: 'booking',
                id: b.id,
                label: `${b.reference}: ${b.customer.name}`,
                status: b.status,
                source: b.source,
                data: b
            });
        }
    });

    // Add Blocks
    props.blocks.forEach(bl => {
        if (dateStr >= bl.start_date && dateStr <= bl.end_date) {
            items.push({
                type: 'block',
                id: bl.id,
                label: `Blocked: ${bl.reason}`,
                data: bl
            });
        }
    });

    return items;
};

const openEvent = (event) => {
    if (event.type === 'booking') {
        selectedBooking.value = event.data;
        rescheduleForm.check_in = event.data.check_in;
        rescheduleForm.check_out = event.data.check_out;
        showRescheduleForm.value = false;
    } else {
        alert(`Maintenance Block:\nReason: ${event.data.reason}\nDates: ${event.data.start_date} to ${event.data.end_date}`);
    }
};

const closeEventModal = () => {
    selectedBooking.value = null;
};

const submitReschedule = () => {
    rescheduleForm.post(route('admin.bookings.reschedule', selectedBooking.value.id), {
        onSuccess: () => {
            closeEventModal();
            alert('Stay rescheduled successfully.');
        },
        onError: (err) => {
            alert(Object.values(err).join('\n'));
        }
    });
};

const triggerStatusUpdate = (status) => {
    useForm({
        status: status,
        notes: `Status changed from calendar modal.`
    }).post(route('admin.bookings.status', selectedBooking.value.id), {
        onSuccess: () => {
            closeEventModal();
            alert(`Booking status changed to ${status}`);
        }
    });
};

const changeMonth = (direction) => {
    if (direction === -1) {
        if (selectedMonth.value === 1) {
            selectedMonth.value = 12;
            selectedYear.value--;
        } else {
            selectedMonth.value--;
        }
    } else {
        if (selectedMonth.value === 12) {
            selectedMonth.value = 1;
            selectedYear.value++;
        } else {
            selectedMonth.value++;
        }
    }
    const url = route('admin.bookings.calendar', {
        month: selectedMonth.value,
        year: selectedYear.value
    });
    window.location.href = url;
};
</script>

<template>
    <Head title="Reservation Calendar Grid" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center space-x-4">
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Operational Grid & Schedule
                    </h2>
                    
                    <!-- VIEW PICKER -->
                    <div class="flex bg-gray-100 rounded p-1 text-xs border">
                        <button @click="viewMode = 'month'" class="px-2.5 py-1 rounded" :class="viewMode === 'month' ? 'bg-white font-bold shadow-xs' : 'text-gray-500'">Month</button>
                        <button @click="viewMode = 'week'" class="px-2.5 py-1 rounded" :class="viewMode === 'week' ? 'bg-white font-bold shadow-xs' : 'text-gray-500'">Timeline</button>
                    </div>
                </div>

                <div class="flex space-x-2">
                    <Link :href="route('admin.accommodation.index')" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded transition">
                        Block Room / Maintenance
                    </Link>
                    <Link :href="route('admin.bookings.create')" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded transition">
                        Create Reservation
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <!-- MONTH VIEW -->
                <div v-if="viewMode === 'month'" class="bg-white rounded-xl shadow-xs border border-gray-200 overflow-hidden">
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-3 p-4 sm:p-6 border-b border-gray-200 bg-gray-50">
                        <div class="flex space-x-2">
                            <button @click="changeMonth(-1)" class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-semibold hover:bg-gray-100 transition cursor-pointer">◀ Prev</button>
                            <button @click="changeMonth(1)" class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-semibold hover:bg-gray-100 transition cursor-pointer">Next ▶</button>
                        </div>
                        <h3 class="font-extrabold text-gray-900 text-sm sm:text-base uppercase tracking-wider">
                            {{ monthNames[selectedMonth - 1] }} {{ selectedYear }}
                        </h3>
                        <div class="text-xs text-gray-500 font-bold uppercase tracking-wider">
                            {{ bookings.length }} Bookings | {{ blocks.length }} Blocks
                        </div>
                    </div>

                    <!-- Responsive Horizontal Scroll Wrapper for 7-Day Grid -->
                    <div class="overflow-x-auto scrollbar-thin">
                        <div class="min-w-[640px]">
                            <div class="grid grid-cols-7 bg-gray-100 text-center text-[10px] font-bold text-gray-500 uppercase py-2.5 border-b border-gray-200">
                                <div>Sun</div>
                                <div>Mon</div>
                                <div>Tue</div>
                                <div>Wed</div>
                                <div>Thu</div>
                                <div>Fri</div>
                                <div>Sat</div>
                            </div>

                            <div class="grid grid-cols-7 gap-px bg-gray-200 border-b border-gray-200">
                                <div v-for="(cell, index) in calendarDays" :key="index" class="bg-white min-h-[100px] sm:min-h-[110px] p-1.5 sm:p-2 flex flex-col justify-between group">
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs font-bold" :class="cell.day ? 'text-gray-900' : 'text-gray-200'">{{ cell.day || '' }}</span>
                                        <span v-if="cell.dateString === new Date().toISOString().split('T')[0]" class="text-[9px] bg-red-100 text-red-700 px-1 rounded font-bold">TODAY</span>
                                    </div>

                            <div class="space-y-1 overflow-y-auto flex-1 mt-2 max-h-[75px]">
                                <div v-for="ev in getEventsForDate(cell.dateString)" :key="ev.id" @click="openEvent(ev)"
                                    class="text-[9px] p-1 rounded font-semibold cursor-pointer truncate transition border"
                                    :class="{
                                        'bg-emerald-50 border-emerald-100 text-emerald-800 hover:bg-emerald-100': ev.type === 'booking' && ev.status === 'confirmed',
                                        'bg-blue-50 border-blue-100 text-blue-800 hover:bg-blue-100': ev.type === 'booking' && ev.status === 'checked_in',
                                        'bg-yellow-50 border-yellow-100 text-yellow-800 hover:bg-yellow-100': ev.type === 'booking' && ev.status === 'pending',
                                        'bg-red-50 border-red-100 text-red-800 hover:bg-red-100': ev.type === 'block',
                                    }">
                                    {{ ev.label }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

                <!-- WEEKLY TIMELINE RESOURCE VIEW -->
                <div v-if="viewMode === 'week'" class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider">Unit Timeline Grid</h3>
                        <span class="text-xs text-gray-400 font-bold uppercase">Rows: Physical Room Units</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-gray-100 text-gray-500 uppercase tracking-wider text-[10px] font-bold">
                                    <th class="p-4 border border-gray-200">Room Unit</th>
                                    <th v-for="cell in calendarDays.filter(c => c.day)" :key="cell.day" class="p-2 border border-gray-200 text-center font-mono w-12">
                                        {{ cell.day }}<br><span class="text-[8px] font-normal">{{ monthNames[selectedMonth-1].substring(0,3) }}</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="unit in units" :key="unit.id" class="hover:bg-gray-50/50">
                                    <td class="p-4 border border-gray-200 font-bold text-gray-900">
                                        {{ unit.name }}
                                        <span class="block text-[9px] text-gray-400 font-semibold">{{ unit.type.name }}</span>
                                    </td>
                                    
                                    <td v-for="cell in calendarDays.filter(c => c.day)" :key="cell.day" class="border border-gray-200 p-1 text-center h-14 relative">
                                        <!-- Render events for this unit + this date -->
                                        <div v-for="ev in getEventsForDate(cell.dateString).filter(e => e.type === 'block' ? e.data.accommodation_unit_id === unit.id : e.data.accommodation_unit_id === unit.id)" :key="ev.id"
                                            @click="openEvent(ev)"
                                            class="absolute inset-1 rounded flex items-center justify-center text-[9px] font-bold p-1 cursor-pointer transition select-none truncate overflow-hidden"
                                            :class="{
                                                'bg-emerald-500 text-white hover:bg-emerald-600': ev.type === 'booking' && ev.status === 'confirmed',
                                                'bg-indigo-600 text-white hover:bg-indigo-700': ev.type === 'booking' && ev.status === 'checked_in',
                                                'bg-yellow-400 text-gray-900 hover:bg-yellow-500': ev.type === 'booking' && ev.status === 'pending',
                                                'bg-red-600 text-white hover:bg-red-700': ev.type === 'block',
                                            }"
                                            :title="ev.label">
                                            {{ ev.type === 'booking' ? ev.data.reference : 'BLOCKED' }}
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- EVENT INTERACTIVE MODAL -->
                <div v-if="selectedBooking" class="fixed inset-0 bg-gray-900/50 flex items-center justify-center z-50 p-4">
                    <div class="bg-white p-6 rounded-lg shadow-lg border w-full max-w-md space-y-4">
                        <div class="flex justify-between items-center border-b pb-2">
                            <h3 class="font-bold text-gray-800">Booking Details: {{ selectedBooking.reference }}</h3>
                            <button @click="closeEventModal" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
                        </div>

                        <div class="text-xs space-y-2">
                            <p><span class="font-bold text-gray-400 uppercase text-[9px] block">Customer / Guest</span> <strong>{{ selectedBooking.customer.name }}</strong></p>
                            <p><span class="font-bold text-gray-400 uppercase text-[9px] block">Dates of Stay</span> {{ selectedBooking.check_in }} to {{ selectedBooking.check_out }}</p>
                            <p><span class="font-bold text-gray-400 uppercase text-[9px] block">Booking Source</span> <span class="uppercase font-mono font-bold">{{ selectedBooking.source }}</span></p>
                            
                            <div>
                                <span class="font-bold text-gray-400 uppercase text-[9px] block">Current Status</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase inline-block mt-1"
                                    :class="{
                                        'bg-yellow-100 text-yellow-800': selectedBooking.status === 'pending',
                                        'bg-green-100 text-green-800': selectedBooking.status === 'confirmed',
                                        'bg-blue-100 text-blue-800': selectedBooking.status === 'checked_in',
                                        'bg-gray-100 text-gray-700': selectedBooking.status === 'checked_out',
                                        'bg-red-100 text-red-800': selectedBooking.status === 'cancelled',
                                    }">
                                    {{ selectedBooking.status }}
                                </span>
                            </div>
                        </div>

                        <!-- ACTIONS PANEL -->
                        <div class="border-t pt-4 space-y-3">
                            <h4 class="text-[10px] font-bold text-gray-400 uppercase">Operational Controls</h4>
                            <div class="grid grid-cols-3 gap-2">
                                <button v-if="selectedBooking.status === 'confirmed'" @click="triggerStatusUpdate('checked_in')" class="py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-bold rounded shadow-xs">
                                    Check In
                                </button>
                                <button v-if="selectedBooking.status === 'checked_in'" @click="triggerStatusUpdate('checked_out')" class="py-1.5 bg-gray-650 hover:bg-gray-700 text-white text-[10px] font-bold rounded shadow-xs">
                                    Check Out
                                </button>
                                <button v-if="['pending', 'confirmed'].includes(selectedBooking.status)" @click="triggerStatusUpdate('cancelled')" class="py-1.5 bg-red-600 hover:bg-red-700 text-white text-[10px] font-bold rounded shadow-xs">
                                    Cancel Stay
                                </button>
                                <Link :href="route('admin.bookings.show', selectedBooking.id)" class="py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-[10px] font-bold rounded shadow-xs text-center">
                                    View Details
                                </Link>
                            </div>

                            <!-- RESCHEDULE OVERRIDE SECTION -->
                            <div v-if="can_reschedule" class="border-t pt-3 space-y-2">
                                <button @click="showRescheduleForm = !showRescheduleForm" class="text-xs text-emerald-600 font-bold hover:underline">
                                    {{ showRescheduleForm ? 'Cancel Reschedule' : 'Reschedule Booking dates...' }}
                                </button>
                                
                                <form v-if="showRescheduleForm" @submit.prevent="submitReschedule" class="space-y-3 p-3 bg-gray-50 border rounded">
                                    <div>
                                        <label class="text-[9px] font-bold text-gray-400 uppercase">New Check-In</label>
                                        <input v-model="rescheduleForm.check_in" type="date" class="w-full text-xs rounded border-gray-300 mt-1" required />
                                    </div>
                                    <div>
                                        <label class="text-[9px] font-bold text-gray-400 uppercase">New Check-Out</label>
                                        <input v-model="rescheduleForm.check_out" type="date" class="w-full text-xs rounded border-gray-300 mt-1" required />
                                    </div>
                                    <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold rounded shadow transition">
                                        Apply Reschedule Change
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
