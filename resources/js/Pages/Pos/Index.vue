<script setup>
import { ref, computed, onMounted, nextTick, onUnmounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// Fungsi Jam Realtime
const currentTime = ref('');
let timer;

onMounted(() => {
    if (searchInput.value) searchInput.value.focus();

    // Jalankan Jam Setiap Detik
    timer = setInterval(() => {
        const now = new Date();
        currentTime.value = now.toLocaleString('id-ID', {
            day: '2-digit', month: '2-digit', year: 'numeric',
            hour: '2-digit', minute: '2-digit', second: '2-digit'
        }).replace(/\//g, '.');
    }, 1000);
});
onUnmounted(() => {
    clearInterval(timer);
});

// Menerima data produk dari server
const props = defineProps({
    products: Array,
});

// State utama untuk keranjang, pembayaran, dan tampilan
const cart = ref([]);
const heldOrders = ref([]);
const payAmount = ref(0);
const paymentMethod = ref('Cash')
const showReceipt = ref(false);
const showQrisModal = ref(false);
const lastTransaction = ref(null);


//Pencarian Produk & Barcode Scan
const searchQuery = ref('');
const searchInput = ref(null);

// Fokus ke input pencarian saat halaman dimuat
onMounted(() => {
    if (searchInput.value) {
        searchInput.value.focus();
    }
});


// Filter produk secara otomatis berdasarkan ketikan kasir
const filteredProducts = computed(() => {
    if (!searchQuery.value) return props.products;
    const query = searchQuery.value.toLocaleLowerCase();
    return props.products.filter(product =>
        product.name.toLowerCase().includes(query) ||
        (product.sku && product.sku.toLowerCase().includes(query))
    );
});

// Fungsi Otomatis oleh scanner barcode
const handleBarcodeScan = () => {
    if (!searchQuery.value) return;

    const query = String(searchQuery.value).trim().toLowerCase();

    // cek apakah input cocok dengan SKU produk
    const exactMatch = props.products.find(p =>
        p.sku && String(p.sku).toLowerCase() === query);
    if (exactMatch) {
        addToCart(exactMatch); //masuk keranjang
        searchQuery.value = ''; // reset input
    } else if (filteredProducts.value.length === 1) {
        addToCart(filteredProducts.value[0]);
        searchQuery.value = '';
    }

    // Kursor fokus ke kotak pencarian
    nextTick(() => {
        if (searchInput.value) searchInput.value.focus();
    });
};

// Fungsi Keranjang
const addToCart = (product) => {
    const existingItem = cart.value.find(item => item.id === product.id);
    if (existingItem) {
        existingItem.qty++;
    } else {
        cart.value.push({ id: product.id, name: product.name, price: product.price, qty: 1 });
    }
};

const decreaseQty = (product) => {
    const existingItem = cart.value.find(item => item.id === product.id);
    if (existingItem) {
        if (existingItem.qty > 1) {
            existingItem.qty--;
        } else {
            cart.value = cart.value.filter(item => item.id !== product.id);
        }
    }
};

const validateQty = (item) => {
    if (!item.qty || item.qty < 1) item.qty = 1;
};

// Fungsi Void
const clearCart = () => {
    if (cart.value.length > 0 && confirm("Apakah Anda yakin ingin membatalkan transaksi ini?")) {
        cart.value = [];
        payAmount.value = 0;
    }
};

// fungsi Hold Order
const holdTransaction = () => {
    if (cart.value.length === 0) return;

    heldOrders.value.push({
        id: Date.now(),
        items: [...cart.value],
        time: new Date().toLocaleTimeString('id-ID'),
        total: totalBelanja.value
    });

    cart.value = [];
    payAmount.value = 0;
    alert('Pesanan berhasil di-hold!');
};

const resumeOrder = (order) => {
    if (cart.value.length > 0 && !confirm('Keranjang saat ini akan diganti. Lanjutkan?')) return;

    cart.value = [...order.items];
    heldOrders.value = heldOrders.value.filter(h => h.id !== order.id);
};

// Perhitungan Total, Kembalian, dan Pajak
const totalBelanja = computed(() => {
    return cart.value.reduce((total, item) => total + (item.price * item.qty), 0);
});

const kembalian = computed(() => {
    return payAmount.value - totalBelanja.value;
});

// Proses Bayar
const setPaymentMethod = (method) => {
    paymentMethod.value = method;
    // Kalau non-tunai, uang bayar otomatis nge-pas dengan total belanja
    if (method !== 'Cash') {
        payAmount.value = totalBelanja.value;
    } else {
        payAmount.value = 0; // Reset kalau balik ke Tunai
    }
};

// Fungsi Eksekusi ke database (dipisah agar bisa dipanggil dari tombol tunai atau qris)
const executeCheckout = () => {
    // Hitung PPN Mundur (Include TAX 11%)
    const dppAmount = Math.round(totalBelanja.value / 1.11);
    const ppnAmount = totalBelanja.value - dppAmount;

    router.post('/pos/checkout', {
        cart: cart.value,
        total_price: totalBelanja.value,
        pay_amount: payAmount.value,
        payment_method: paymentMethod.value
    }, {
        onSuccess: () => {
            lastTransaction.value = {
                cart: [...cart.value],
                total: totalBelanja.value,
                pay: payAmount.value,
                return: kembalian.value,
                method: paymentMethod.value,
                dpp: dppAmount,
                ppn: ppnAmount,
                date: new Date().toLocaleString('id-ID', { year: '2-digit', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' }).replace(/\//g, '.')
            };
            showQrisModal.value = false;
            showReceipt.value = true;
            cart.value = [];
            payAmount.value = 0;
            paymentMethod.value = 'Cash';
        }
    });
};

// Proses bayar Utama
const formatInputRupiah = (event) => {
    let rawValue = event.target.value.replace(/\D/g, '');
    payAmount.value = rawValue ? parseInt(rawValue, 10) : 0;
    event.target.value = payAmount.value === 0 ? '' : payAmount.value.toLocaleString('id-ID');
}

const processCheckout = () => {
    if (payAmount.value < totalBelanja.value) {
        alert("Maaf, uang pembayaran kurang!");
        return;
    }

    if (paymentMethod.value === 'QRIS') {
        // Tampilkan modal QRIS
        showQrisModal.value = true;
    } else {
        // Proses checkout langsung untuk Cash atau Debit
        executeCheckout();
    }
};

const printReceipt = () => {
    window.print();
};
</script>

<template>
    <Head title="Kasir POS" />

    <AppLayout>
        <div class="p-6 bg-gray-100 min-h-screen">
            <div class="bg-blue-800 text-white rounded-xl shadow-md mb-6 flex flex-col md:flex-row overflow-hidden border border-blue-900">
                <div class="p-3 px-5 bg-blue-900 md:w-1/4 flex flex-col justify-center">
                    <div class="font-black text-lg tracking-wider text-blue-50 text-center">POS KASIR</div>
                    <div class="text-center text-[10px] text-blue-300 uppercase mt-0.5 tracking-widest font-semibold">TC51 - Kebon Kosong</div>
                </div>

                <div class="flex-1 flex flex-col justify-between border-r border-blue-700">
                    <div class="bg-red-600 text-white text-xs font-bold py-1 px-4 text-center tracking-widest overflow-hidden whitespace-nowrap">
                        <marquee scrollamount="6">INFO KASIR: PASTIKAN SCAN BARCODE DENGAN BENAR | JANGAN LUPA TAWARKAN PRODUK BEST SELLER KEPADA KONSUMEN</marquee>
                    </div>

                    <div class="flex justify-between items-center px-4 py-2 bg-blue-800">
                        <div class="text-xs md:text-sm font-semibold text-blue-100 flex gap-3 md:gap-6">
                            <span class="flex items-center gap-1"><span class="opacity-70">Kasir:</span> Arif Juliawan.</span>
                            <span class="flex items-center gap-1"><span class="opacity-70">Shift:</span> 1</span>
                            <span class="flex items-center gap-1"><span class="opacity-70">Station:</span> 01</span>
                        </div>
                        <div class="font-mono text-sm md:text-lg font-bold text-yellow-300 tracking-wider">
                            {{ currentTime }}
                        </div>
                    </div>
                </div>

                <div class="p-2 w-full md:w-40 bg-white flex items-center justify-center shrink-0 border-b-4 border-red-600 md:border-b-0 md:border-l-4">
                    <div class="text-center flex gap-1 items-center">
                        <span class="text-red-600 font-black text-xl italic leading-none">Indo</span>
                        <span class="text-blue-600 font-black text-xl italic leading-none">UMKM</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Etalase Kiri -->
                <div class="w-full lg:w-8/12">
                    <div class="mb-4 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-400 text-lg">🔍</span>
                        </div>
                        <input
                            ref="searchInput"
                            type="text"
                            v-model="searchQuery"
                            @keydown.enter.prevent="handleBarcodeScan"
                            placeholder="Ketik nama atau Scan Barcode di sini... (Tekan Enter)"
                            class="w-full pl-10 pr-3 py-3 rounded-xl border border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-700 font-medium bg-white"
                        >
                    </div>

                    <div v-if="filteredProducts.length === 0" class="text-center py-10 bg-white rounded-xl shadow-sm border border-gray-200">
                        <p class="text-gray-500 font-medium">Barang tidak ditemukan.</p>
                    </div>

                    <div v-else class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                        <div v-for="product in filteredProducts" :key="product.id" @click="addToCart(product)" class="bg-white rounded-xl shadow-sm hover:shadow-md hover:border-blue-400 transition-all overflow-hidden cursor-pointer border border-gray-200">
                            <img :src="product.image || 'https://placehold.co/100x100?text=No+Image'" :alt="product.name" class="w-full h-32 object-contain p-2 bg-white border-b border-gray-100">
                            <div class="p-3">
                                <h2 class="font-bold text-gray-800 text-sm line-clamp-2" :title="product.name">{{ product.name }}</h2>
                                <p class="text-blue-600 font-extrabold mt-1 text-sm">Rp {{ product.price.toLocaleString('id-ID') }}</p>
                                <div
                                    class="mt-2 text-xs font-medium" :class="product.stock > 10 ? 'text-green-600' : 'text-red-500'">
                                    Stok: {{ product.stock }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="heldOrders.length > 0" class="mt-8">
                        <h3 class="text-sm font-black text-gray-500 uppercase tracking-widest mb-3">Pesanan Tertunda (Hold)</h3>
                        <div class="flex flex-wrap gap-3">
                            <div
                                v-for="order in heldOrders" :key="order.id" @click="resumeOrder(order)" class="bg-orange-50 border border-orange-200 p-3 rounded-xl cursor-pointer hover:shadow-md transition-all w-40">
                                <div
                                    class="text-[10px] font-bold text-orange-600">{{ order.time }}
                                </div>
                                <div
                                    class="text-xs font-black text-gray-800 mt-1">Rp {{ order.total.toLocaleString('id-ID') }}
                                </div>
                                <div
                                    class="text-[10px] text-gray-500 mt-1">{{ order.items.length }} Item
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full lg:w-4/12">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col h-[75vh]">
                        <div class="p-4 border-b border-gray-100 bg-gray-50 rounded-t-xl flex justify-between items-center">
                            <h2 class="text-lg font-bold text-gray-700">🛒 Rincian Pesanan</h2>
                            <button @click="clearCart" class="text-red-500 hover:text-red-700 text-xs font-bold uppercase tracking-wider">
                                Void
                            </button>
                        </div>

                        <div class="p-4 flex-1 overflow-y-auto">
                            <div v-if="cart.length === 0" class="text-center text-gray-400 mt-10">Belum ada barang yang dipilih</div>

                            <div v-for="item in cart" :key="item.id" class="flex justify-between items-center mb-4 pb-4 border-b border-gray-50 last:border-0">
                                <div class="flex-1 pr-2">
                                    <h3 class="font-semibold text-sm text-gray-800 leading-tight mb-1">{{ item.name }}</h3>
                                    <p class="text-xs text-gray-500">Rp {{ item.price.toLocaleString('id-ID') }}</p>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <div class="font-bold text-sm text-gray-800">Rp {{ (item.price * item.qty).toLocaleString('id-ID') }}</div>
                                    <div class="flex items-center bg-gray-100 rounded-lg p-1 border border-gray-200">
                                        <button @click="decreaseQty(item)" class="w-7 h-7 flex items-center justify-center bg-white rounded shadow-sm text-gray-600 hover:text-red-500 font-bold">-</button>
                                        <input type="number" v-model.number="item.qty" @change="validateQty(item)" class="w-10 text-center text-xs font-bold text-gray-700 bg-transparent border-none focus:ring-0 p-0 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                        <button @click="addToCart(item)" class="w-7 h-7 flex items-center justify-center bg-white rounded shadow-sm text-gray-600 hover:text-blue-500 font-bold">+</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 border-t border-gray-200 bg-gray-50 rounded-b-xl">
                            <div class="mb-4">
                                <span class="font-semibold text-xs text-gray-500 block mb-2 uppercase tracking-wider">Metode Pembayaran</span>
                                <div class="grid grid-cols-3 gap-2">
                                    <button @click="setPaymentMethod('Cash')" :class="paymentMethod === 'Cash' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'" class="py-2 rounded-lg font-bold text-sm border transition-colors shadow-sm">
                                        💵 Tunai
                                    </button>
                                    <button @click="setPaymentMethod('QRIS')" :class="paymentMethod === 'QRIS' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'" class="py-2 rounded-lg font-bold text-sm border transition-colors shadow-sm">
                                        📱 QRIS
                                    </button>
                                    <button @click="setPaymentMethod('Debit')" :class="paymentMethod === 'Debit' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'" class="py-2 rounded-lg font-bold text-sm border transition-colors shadow-sm">
                                        💳 Debit
                                    </button>
                                </div>
                            </div>

                            <div class="flex justify-between items-center mb-2">
                                <span class="font-bold text-sm text-gray-600">Total Belanja</span>
                                <span class="text-sm font-black text-gray-800">Rp {{ totalBelanja.toLocaleString('id-ID') }}</span>
                            </div>

                            <div class="grid grid-cols-1 mb-3">
                                <button @click="holdTransaction" :disabled="cart.length === 0" class="bg-orange-100 text-orange-700 py-2 rounded-lg font-bold text-xs uppercase border border-orange-200 hover:bg-orange-200 disabled:opacity-50 transition-colors">
                                    ⏸️ Hold / Simpan Pesanan
                                </button>
                            </div>

                            <div class="flex justify-between items-center mb-2">
                                <span class="font-semibold text-sm text-gray-600">Nominal Bayar</span>
                                <input
                                    type="text"
                                    :value="payAmount === 0 ? '' : payAmount.toLocaleString('id-ID')"
                                    @input="formatInputRupiah"
                                    :disabled="paymentMethod !== 'Cash'"
                                    :class="paymentMethod !== 'Tunai' ? 'bg-gray-100 text-gray-500' : 'bg-white text-gray-800 focus:ring-blue-500 focus:border-blue-500'"
                                    class="w-32 text-right text-sm font-bold border-gray-300 rounded-md p-1 transition-colors"
                                    placeholder="0">
                            </div>

                            <div class="flex justify-between items-center mb-4 pt-2 border-t border-dashed border-gray-300">
                                <span class="font-bold text-gray-600">Kembalian</span>
                                <span class="text-xl font-black" :class="kembalian >= 0 ? 'text-green-600' : 'text-red-500'">
                                    Rp {{ kembalian.toLocaleString('id-ID') }}
                                </span>
                            </div>

                            <button @click="processCheckout" :disabled="cart.length === 0 || payAmount < totalBelanja" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition-colors flex justify-center items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed shadow-md">
                                Proses Pembayaran
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div v-if="showQrisModal" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4 backdrop-blur-sm">
            <div class="bg-white p-6 rounded-2xl shadow-2xl w-full max-w-md text-center border-t-8 border-blue-600 transform transition-all flex flex-col max-h-[90vh]">
                <h2 class="text-2xl font-black text-gray-800 mb-1">Pembayaran QRIS</h2>
                <p class="text-gray-500 text-sm mb-4">Silahkan scan kode QR di bawah ini</p>

                <div class="bg-white p-2 rounded-xl border-2 border-dashed border-gray-300 w-full mb-6 shadow-inner flex-1 overflow-hidden">
                    <img src="/images/qris-toko.jpg" alt="QRIS Toko" class="w-full h-full max-h-80 object-contain mx-auto rounded-lg">
                </div>

                <div class="bg-blue-50 text-blue-800 p-3 rounded-lg mb-6 border border-blue-100 shrink-0">
                    <span class="block text-sm font-medium opacity-80">Total Tagihan</span>
                    <span class="text-3xl font-black">Rp {{ totalBelanja.toLocaleString('id-ID') }}</span>
                </div>

                <div class="flex gap-3 mt-auto shrink-0">
                    <button @click="showQrisModal = false" class="flex-1 bg-gray-100 py-3 rounded-xl font-bold text-gray-600 hover:bg-gray-200 transition-colors">Batal</button>
                    <button @click="executeCheckout" class="flex-1 bg-blue-600 py-3 rounded-xl font-bold text-white hover:bg-blue-700 transition-colors shadow-md">✅ Konfirmasi</button>
                </div>
            </div>
        </div>

        <!-- Struk Belanja -->
        <div v-if="showReceipt" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
            <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-sm overflow-hidden">
                <div id="print-area" class="text-left font-mono text-[11px] leading-tight uppercase text-black bg-white p-2">
                    <div class="text-center mb-2">
                        <h2 class="font-bold text-sm">POS UMKM MODERN</h2>
                        <p>JL. KEBON KOSONG NO 56 F</p>
                        <p>KEC. KEMAYORAN, JAKARTA PUSAT</p>
                        <p>NPWP: 00.123.456.7-890.000</p>
                    </div>

                    <div class="text-center my-2 font-bold tracking-widest">
                        <p>=> R E P R I N T <=</p>
                    </div>

                    <div class="mb-2">
                        <p>{{ lastTransaction?.date }}/TC51-14330/KASIR/01</p>
                    </div>

                    <p class="border-b border-dashed border-black mb-2"></p>

                    <div v-for="item in lastTransaction?.cart" :key="item.id" class="mb-1">
                        <div class="truncate w-full pr-2">{{ item.name }}</div>
                        <div class="flex justify-between pl-8">
                            <span>{{ item.qty }} x {{ item.price.toLocaleString('id-ID') }}</span>
                            <span>{{ (item.price * item.qty).toLocaleString('id-ID') }}</span>
                        </div>
                    </div>

                    <p class="border-t border-dashed border-black mt-2 pt-2"></p>

                    <div class="flex justify-between font-bold text-xs mb-2">
                        <span>TOTAL BELANJA :</span>
                        <span>{{ lastTransaction?.total.toLocaleString('id-ID') }}</span>
                    </div>

                    <p class="border-b border-dashed border-black mb-2"></p>

                    <div class="flex justify-between mb-1">
                        <span>{{ lastTransaction?.method === 'Cash' ? 'Cash' : 'NON TUNAI' }} :</span>
                        <span>{{ lastTransaction?.pay.toLocaleString('id-ID') }}</span>
                    </div>
                    <div v-if="lastTransaction?.method === 'Cash'" class="flex justify-between mb-2">
                        <span>KEMBALIAN :</span>
                        <span>{{ lastTransaction?.return.toLocaleString('id-ID') }}</span>
                    </div>

                    <div class="mt-3 text-[10px]">
                        <p>PPN   : DPP= {{ lastTransaction?.dpp.toLocaleString('id-ID') }} PPN= {{ lastTransaction?.ppn.toLocaleString('id-ID') }}</p>
                        <p>HARGA JUAL : {{ lastTransaction?.total.toLocaleString('id-ID') }}</p>
                        <p class="mt-1">{{ lastTransaction?.method }} - TRXID: {{ Date.now().toString().slice(-6) }}</p>
                    </div>

                    <div class="text-center mt-4">
                        <p>LAYANAN KONSUMEN</p>
                        <p>SMS/WA 0821.1339.8238</p>
                        <p>TERIMA KASIH</p>
                    </div>
                </div>

                <div class="mt-6 flex gap-2 no-print">
                    <button @click="printReceipt" class="flex-1 bg-green-600 text-white py-2 rounded font-bold hover:bg-green-700">CETAK</button>
                    <button @click="showReceipt = false" class="flex-1 bg-gray-200 py-2 rounded font-bold text-gray-800 hover:bg-gray-300">TUTUP</button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<style>
/* Sembunyikan tombol saat mencetak */
@media print {
    body * { visibility: hidden; }
    #print-area, #print-area * { visibility: visible; }
    #print-area { position: absolute; left: 0; top: 0; width: 100%; }
    .no-print { display: none !important; }
}
</style>
