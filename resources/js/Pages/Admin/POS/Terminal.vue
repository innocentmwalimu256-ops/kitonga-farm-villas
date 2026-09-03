<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    products: Array,
    categories: Array,
    customers: Array,
});

const activeCategory = ref('all');
const cart = ref([]);
const discount = ref(0);
const paymentMethod = ref('cash');
const paymentReference = ref('');
const customerMode = ref('walkin'); // walkin, existing, new
const selectedCustomerId = ref('');
const newCustomerName = ref('');
const newCustomerPhone = ref('');
const newCustomerEmail = ref('');
const selectedPosCategory = ref('product'); // product, bar, tour, other
const searchQuery = ref('');

const filteredProducts = computed(() => {
    let prods = props.products;
    if (activeCategory.value !== 'all') {
        prods = prods.filter(p => p.category?.slug === activeCategory.value);
    }
    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase();
        prods = prods.filter(p => p.name.toLowerCase().includes(q) || (p.sku && p.sku.toLowerCase().includes(q)));
    }
    return prods;
});

const addToCart = (product) => {
    const existing = cart.value.find(item => item.product_id === product.id);
    if (existing) {
        if (existing.quantity < product.stock) {
            existing.quantity++;
        }
    } else {
        if (product.stock > 0) {
            cart.value.push({
                product_id: product.id,
                name: product.name,
                unit_price: product.selling_price,
                quantity: 1,
                stock: product.stock,
                unit: product.unit || 'units',
            });
        }
    }
};

const removeFromCart = (index) => {
    cart.value.splice(index, 1);
};

const updateQty = (item, qty) => {
    const newQty = parseInt(qty);
    if (newQty > 0 && newQty <= item.stock) {
        item.quantity = newQty;
    }
};

const incrementQty = (item) => {
    if (item.quantity < item.stock) {
        item.quantity++;
    }
};

const decrementQty = (item, index) => {
    if (item.quantity > 1) {
        item.quantity--;
    } else {
        removeFromCart(index);
    }
};

const cartSubtotal = computed(() => {
    return cart.value.reduce((sum, item) => sum + (item.unit_price * item.quantity), 0);
});

const cartTotal = computed(() => {
    return Math.max(0, cartSubtotal.value - discount.value);
});

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-TZ', { style: 'currency', currency: 'TZS', maximumFractionDigits: 0 }).format(val);
};

const submitSale = () => {
    if (cart.value.length === 0) {
        alert("Cart is empty. Please select products first.");
        return;
    }

    const form = useForm({
        customer_id: customerMode.value === 'existing' ? selectedCustomerId.value : null,
        customer_name: customerMode.value === 'new' ? newCustomerName.value : (customerMode.value === 'walkin' ? 'Walk-in Guest' : ''),
        customer_phone: customerMode.value === 'new' ? newCustomerPhone.value : null,
        customer_email: customerMode.value === 'new' ? newCustomerEmail.value : null,
        category: selectedPosCategory.value,
        discount: discount.value,
        amount_paid: cartTotal.value,
        payment_method: paymentMethod.value,
        payment_reference: paymentReference.value,
        items: cart.value.map(item => ({
            product_id: item.product_id,
            quantity: item.quantity,
        })),
    });

    form.post(route('admin.pos.store'), {
        onSuccess: () => {
            cart.value = [];
            discount.value = 0;
            paymentReference.value = '';
        },
        onError: (err) => {
            alert(Object.values(err).join('\n'));
        }
    });
};
</script>

<template>
    <Head title="POS Terminal — Kitonga Farm Villas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h2 class="text-lg sm:text-xl font-bold leading-tight text-gray-900">
                        POS Sales Terminal
                    </h2>
                    <p class="text-xs text-gray-500">Touch-first quick register for farm harvest, mini-bar, and tours</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-mono px-2.5 py-1 bg-emerald-50 text-emerald-800 font-bold rounded-md border border-emerald-200">
                        {{ cart.length }} items in register
                    </span>
                </div>
            </div>
        </template>

        <div class="py-6 px-3 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                
                <!-- LEFT / MAIN: PRODUCTS CATALOG -->
                <div class="lg:col-span-7 xl:col-span-8 space-y-4">
                    
                    <!-- Search & Filter Controls -->
                    <div class="bg-white p-4 rounded-xl shadow-xs border border-gray-200 space-y-3">
                        <div class="relative">
                            <input 
                                v-model="searchQuery" 
                                type="text" 
                                placeholder="Search products by name or SKU..." 
                                class="w-full text-xs pl-9 pr-4 py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:border-emerald-600 transition"
                            />
                            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <button v-if="searchQuery" @click="searchQuery = ''" class="absolute right-3 top-2.5 text-xs text-gray-400 hover:text-gray-600">✕</button>
                        </div>

                        <!-- Category Chips -->
                        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none">
                            <button 
                                @click="activeCategory = 'all'" 
                                class="px-3.5 py-1.5 text-xs font-bold rounded-lg transition shrink-0 cursor-pointer"
                                :class="activeCategory === 'all' ? 'bg-emerald-700 text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            >
                                All ({{ products.length }})
                            </button>
                            <button 
                                v-for="cat in categories" 
                                :key="cat.id" 
                                @click="activeCategory = cat.slug" 
                                class="px-3.5 py-1.5 text-xs font-bold rounded-lg transition shrink-0 cursor-pointer"
                                :class="activeCategory === cat.slug ? 'bg-emerald-700 text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            >
                                {{ cat.name }}
                            </button>
                        </div>
                    </div>

                    <!-- Products Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
                        <div 
                            v-for="prod in filteredProducts" 
                            :key="prod.id" 
                            @click="addToCart(prod)" 
                            class="bg-white p-3.5 sm:p-4 rounded-xl shadow-xs border border-gray-200 hover:border-emerald-500 hover:shadow-md cursor-pointer transition-all flex flex-col justify-between select-none group"
                            :class="{ 'opacity-50 pointer-events-none': prod.stock === 0 }"
                        >
                            <div>
                                <div class="flex justify-between items-start gap-1">
                                    <span class="text-[9px] uppercase font-bold tracking-wider bg-emerald-50 text-emerald-800 px-2 py-0.5 rounded">
                                        {{ prod.category?.name || 'Produce' }}
                                    </span>
                                    <span class="text-[10px] text-gray-400 font-mono">{{ prod.sku }}</span>
                                </div>
                                <h4 class="font-bold text-gray-900 text-xs sm:text-sm mt-2 line-clamp-2 group-hover:text-emerald-800 transition-colors">
                                    {{ prod.name }}
                                </h4>
                            </div>

                            <div class="mt-3 pt-2 border-t border-gray-100 flex justify-between items-center">
                                <span class="font-extrabold text-xs sm:text-sm text-gray-900 font-mono">
                                    {{ formatCurrency(prod.selling_price) }}
                                </span>
                                <span class="text-[10px] font-semibold" :class="prod.stock > 5 ? 'text-gray-500' : 'text-amber-600 font-bold'">
                                    {{ prod.stock }} {{ prod.unit }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div v-if="filteredProducts.length === 0" class="text-center py-12 bg-white rounded-xl border border-gray-200 text-gray-400 text-xs">
                        No products match your search.
                    </div>

                </div>

                <!-- RIGHT: CART AND CHECKOUT REGISTER -->
                <div class="lg:col-span-5 xl:col-span-4 bg-white p-5 sm:p-6 rounded-2xl shadow-xs border border-gray-200 lg:sticky lg:top-20 space-y-4">
                    
                    <!-- Header -->
                    <div class="border-b border-gray-200 pb-3 flex justify-between items-center">
                        <div>
                            <h3 class="font-bold text-sm text-gray-900">Current Sale Register</h3>
                            <span class="text-[11px] text-gray-500">{{ cart.length }} items selected</span>
                        </div>
                        <select v-model="selectedPosCategory" class="text-xs rounded-lg border-gray-300 py-1 pl-2 pr-6">
                            <option value="product">Store Products</option>
                            <option value="bar">Mini Bar / Drinks</option>
                            <option value="tour">Farm Tours & Activities</option>
                            <option value="other">Other Operations</option>
                        </select>
                    </div>

                    <!-- Cart Items List -->
                    <div class="space-y-2.5 max-h-56 overflow-y-auto pr-1">
                        <div 
                            v-for="(item, idx) in cart" 
                            :key="item.product_id" 
                            class="flex justify-between items-center p-2.5 rounded-lg bg-gray-50 border border-gray-200 text-xs"
                        >
                            <div class="flex-1 min-w-0 pr-2">
                                <h4 class="font-bold text-gray-900 truncate">{{ item.name }}</h4>
                                <span class="text-[11px] text-gray-500 font-mono">{{ formatCurrency(item.unit_price) }} / {{ item.unit }}</span>
                            </div>
                            
                            <!-- Stepper -->
                            <div class="flex items-center gap-1.5">
                                <button 
                                    type="button" 
                                    @click="decrementQty(item, idx)" 
                                    class="w-6 h-6 rounded bg-white border border-gray-300 font-bold text-gray-700 hover:bg-gray-100 flex items-center justify-center cursor-pointer"
                                >
                                    -
                                </button>
                                <span class="w-6 text-center font-mono font-bold text-xs">{{ item.quantity }}</span>
                                <button 
                                    type="button" 
                                    @click="incrementQty(item)" 
                                    class="w-6 h-6 rounded bg-white border border-gray-300 font-bold text-gray-700 hover:bg-gray-100 flex items-center justify-center cursor-pointer disabled:opacity-30"
                                    :disabled="item.quantity >= item.stock"
                                >
                                    +
                                </button>
                                <button 
                                    type="button" 
                                    @click="removeFromCart(idx)" 
                                    class="ml-1 text-red-500 hover:text-red-700 p-1 cursor-pointer"
                                    aria-label="Remove item"
                                >
                                    ✕
                                </button>
                            </div>
                        </div>

                        <div v-if="cart.length === 0" class="text-center text-gray-400 py-6 text-xs bg-gray-50/50 rounded-lg border border-dashed border-gray-200">
                            Register is empty.<br>Tap products on the left to add.
                        </div>
                    </div>

                    <!-- Customer Mode Selector -->
                    <div class="border-t border-gray-200 pt-3 space-y-2.5">
                        <span class="text-[10px] font-bold text-gray-400 uppercase block tracking-wider">Guest Attribution</span>
                        <div class="grid grid-cols-3 gap-1 bg-gray-100 p-1 rounded-lg">
                            <button 
                                type="button" 
                                @click="customerMode = 'walkin'" 
                                class="py-1 text-[11px] font-bold rounded transition cursor-pointer"
                                :class="customerMode === 'walkin' ? 'bg-white text-emerald-800 shadow-2xs' : 'text-gray-600'"
                            >
                                Walk-in
                            </button>
                            <button 
                                type="button" 
                                @click="customerMode = 'existing'" 
                                class="py-1 text-[11px] font-bold rounded transition cursor-pointer"
                                :class="customerMode === 'existing' ? 'bg-white text-emerald-800 shadow-2xs' : 'text-gray-600'"
                            >
                                Guest
                            </button>
                            <button 
                                type="button" 
                                @click="customerMode = 'new'" 
                                class="py-1 text-[11px] font-bold rounded transition cursor-pointer"
                                :class="customerMode === 'new' ? 'bg-white text-emerald-800 shadow-2xs' : 'text-gray-600'"
                            >
                                New
                            </button>
                        </div>

                        <div v-if="customerMode === 'existing'">
                            <select v-model="selectedCustomerId" class="w-full text-xs rounded-lg border-gray-300">
                                <option value="">-- Choose Registered Guest --</option>
                                <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }} ({{ c.phone || 'no phone' }})</option>
                            </select>
                        </div>

                        <div v-if="customerMode === 'new'" class="grid grid-cols-2 gap-2">
                            <input type="text" v-model="newCustomerName" placeholder="Guest Name *" class="w-full text-xs rounded-lg border-gray-300 p-2">
                            <input type="text" v-model="newCustomerPhone" placeholder="Phone Number" class="w-full text-xs rounded-lg border-gray-300 p-2">
                        </div>
                    </div>

                    <!-- Totals & Payment -->
                    <div class="border-t border-gray-200 pt-3 space-y-3">
                        <div class="space-y-1.5 text-xs text-gray-600">
                            <div class="flex justify-between">
                                <span>Subtotal</span>
                                <span class="font-mono font-semibold">{{ formatCurrency(cartSubtotal) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span>Discount (TZS)</span>
                                <input 
                                    type="number" 
                                    v-model.number="discount" 
                                    class="w-24 text-right border-gray-300 rounded-lg p-1 text-xs font-mono" 
                                    min="0" 
                                    :max="cartSubtotal"
                                />
                            </div>
                            <div class="flex justify-between font-extrabold text-sm text-gray-900 border-t border-gray-200 pt-2">
                                <span>Total Amount</span>
                                <span class="font-mono text-emerald-800 text-base">{{ formatCurrency(cartTotal) }}</span>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-gray-400 uppercase block tracking-wider">Payment Method</label>
                            <select v-model="paymentMethod" class="w-full text-xs rounded-lg border-gray-300 font-semibold text-gray-800">
                                <option value="cash">Cash Payment</option>
                                <option value="mobile_money">Mobile Money (M-Pesa / Tigo / Airtel)</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="card">Credit / Debit Card</option>
                            </select>
                            
                            <input 
                                v-if="paymentMethod !== 'cash'" 
                                type="text" 
                                v-model="paymentReference" 
                                placeholder="Reference / Transaction ID..." 
                                class="w-full text-xs rounded-lg border-gray-300 p-2 font-mono"
                            />
                        </div>

                        <button 
                            type="button"
                            @click="submitSale" 
                            :disabled="cart.length === 0"
                            class="w-full py-3.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-md hover:shadow-lg transition cursor-pointer disabled:opacity-50"
                        >
                            Complete Sale & Print Receipt →
                        </button>
                    </div>

                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
