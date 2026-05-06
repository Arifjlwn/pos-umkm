<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    products: Array,
});// Menerima data 'products' dari backend

const cart = ref([]);// Keranjang belanja (state interaktif Vue)
const payAmount = ref(0) //state uang tunai pembeli
const showReceipt = ref(false);
const lastTransaction = ref(null);

// 1. Tambah Ke Keranjang
const addToCart = (product) => {
    // Cek apakah produk sudah ada dikeranjang sebelumnya
    const existingItem = cart.value.find(item => item.id === product.id);

    if (existingItem) {
        // Kalau sudah ada, cukup tambah jumlahnya (qty)
        existingItem.qty++;
    } else {
        // Kalau belum ada, masukan ke keranjang dengan qty 1
        cart.value.push({
            id: product.id,
            name: product.name,
            price: product.price,
            qty: 1,
        });
    }
};

//2. Mengurangi QTY atau Hapus Barang dari Keranjang
const decreaseQty = (product) => {
    const existingItem = cart.value.find(item => item.id === product.id);

    if (existingItem) {
        if (existingItem.qty > 1) {
            // kalau lebih dari 1, kurangi Qty
            existingItem.qty--;
        } else {
            // kalau cuman 1, hapus dari keranjang
            cart.value = cart.value.filter(item => item.id !== product.id);
        }
    }
};

// 3. Validasi Input qty manual (case borongan)
const validateQty = (item) => {
    // Kalau kasir tidak sengaja mengetik angka minus, nol, atau dikosongkan
    // Kita otomatis kembalikan jumlahnya jadi minimal 1
    if (!item.qty || item.qty < 1) {
        item.qty = 1;
    }
};

// Menghitung grand total belanjaan secara otomatis
const totalBelanja = computed(() => {
    return cart.value.reduce((total, item) => total + (item.price * item.qty), 0);
});

// Menghitung kembalian otomatis
const kembalian = computed(() => {
    return payAmount.value - totalBelanja.value;
});

// Proses Checkout/Bayar
const processCheckout = () => {
    // Validasi uang kurang
    if (payAmount.value < totalBelanja.value) {
        alert("Maaf, Uang Pembayaran Kurang !");
        return;
    }

    // Kirim data ke route POST /pos/checkout di laravel
    router.post('/pos/checkout', {
        cart: cart.value,
        total_price: totalBelanja.value,
        pay_amount: payAmount.value
    }, {
        onSuccess: () => {
            // Ambil data transaksi terakhir untuk di cetak
            lastTransaction.value = {
                cart: [...cart.value],
                total: totalBelanja.value,
                pay: payAmount.value,
                return: kembalian.value,
                date: new Date().toLocaleString('id-ID')
            };
            showReceipt.value = true; // Munculkan modal
            cart.value = [];// Kosongkan keranjang setelah checkout
            payAmount.value = 0;// Reset input tunai setelah checkout
        }
    });
};

const printReceipt = () => {
    window.print();
}
</script>

<template>
    <Head title="Kasir POS" />

    <div class="p-6 bg-gray-100 min-h-screen">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">Sistem Kasir Modern</h1>

        <!-- Memabagi Layar : Kiri Produk & Kanan Struk -->
        <div class="flex flex-col lg:flex-row gap-6">

            <!-- Bagian Kiri: Etalase Produk -->
            <div class="w-full lg:w-8/12">
                <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                    <!-- Looping Produk & Tambah event @click -->
                    <div
                    v-for="product in props.products"
                    :key="product.id"
                    @click="addToCart(product)"
                    class="bg-white rounded-xl shadow-sm hover:shadow-md hover:border-blue-400 transition-all overflow-hidden cursor-pointer border border-gray-200"
                    >
                        <!-- Foto Produk -->
                        <img :src="product.image" :alt="product.name" class="w-full h-32 object-cover bg-gray-50">

                        <!-- Detail Produk -->
                        <div class="p-3">
                            <h2 class="font-bold text-gray-800 text-sm line-clamp-2" :title="product.name">
                                {{ product.name }}
                            </h2>
                            <p class="text-blue-600 font-extrabold mt-1 text-sm">
                                Rp {{  product.price.toLocaleString('id-ID') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bagian Kanan: Struk / Rincian Pesanan -->
            <div class="w-full lg:w-4/12">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col h-[75vh]">
                    <div class="p-4 border-b border-gray-100 bg-gray-50 rounded-t-xl">
                        <h2 class="text-lg font-bold text-gray-700">🛒 Rincian Pesanan</h2>
                    </div>

                    <!-- Area Daftar Barang di Keranjang -->
                    <div class="p-4 flex-1 overflowy-auto">
                        <!-- Tampil Jika Keranjang Masih Kosong -->
                        <div v-if="cart.length === 0" class="text-center text-gray-400 mt-10">
                            Belum ada barang yang dipilih
                        </div>

                        <!-- Looping barang yang masuk keranjang -->
                        <div
                            v-for="(item, index) in cart"
                            :key="item.id"
                            class="flex justify-between items-center mb-4 pb-4 border-b border-gray-50 last:border-0">

                            <!-- info Nama & harga Satuan -->
                            <div class="flex-1 pr-2">
                                <h3 class="font-semibold text-sm text-gray-800 leading-tight mb-1">{{ item.name }}</h3>
                                <p class="text-xs text-gray-500">Rp {{ item.price.toLocaleString('id-ID') }}</p>
                            </div>

                            <!-- Area Kontrol: Subtotal & Tombol Plus Minus -->
                            <div class="flex flex-col items-end gap-2">
                                <div class="font-bold text-sm text-gray-800">
                                    Rp {{ (item.price * item.qty).toLocaleString('id-ID') }}
                                </div>

                                <!-- Tombol Plus Minus -->
                                <div class="flex items-center bg-gray-100 rounded-lg p-1 border border-gray-200">
                                    <button @click="decreaseQty(item)" class="w-7 h-7 flex items-center justify-center bg-white rounded shadow-sm text-gray-600 hover:text-red-500 hover:bg-red-50 transition-colors font-bold">
                                        -
                                    </button>

                                    <!-- Input Qty Manual -->
                                    <input
                                        type="number"
                                        v-model.number="item.qty"
                                        @change="validateQty(item)"
                                        class="w-10 text-center text-xs font-bold text-gray-700 bg-transparent border-none focus:ring-0 p-0 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                    >

                                    <button @click="addToCart(item)" class="w-7 h-7 flex items-center justify-center bg-white rounded shadow-sm text-gray-600 hover:text-green-500 hover:bg-green-50 transition-colors font-bold">
                                        +
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Area Pembayaran -->
                    <div class="p-4 border-t border-gray-200 bg-gray-50 rounded-b-xl">
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-semibold text-sm text-gray-600">Total Belanja</span>
                            <span class="text-lg font-black text-gray-800">Rp {{ totalBelanja.toLocaleString('id-ID') }}</span>
                        </div>

                        <!-- Input Tunai -->
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-semibold text-sm text-gray-600">Tunai (Rp)</span>
                            <input type="number" v-model.number="payAmount" class="w-32 text-right text-sm font-bold border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 p-1" placeholder="0">
                        </div>

                        <!-- Kembalian -->
                        <div class="flex justify-between items-center mb-4 pt-2 border-t border-dashed border-gray-300">
                            <span class="font-bold text-gray-600">Kembalian</span>
                            <span class="text-xl font-black" :class="kembalian >= 0 ? 'text-green-600' : 'text-red-500'">
                                Rp {{ kembalian.toLocaleString('id-ID') }}
                            </span>
                        </div>

                        <button @click="processCheckout" :disabled="cart.length === 0 || payAmount < totalBelanja" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition-colors flex justify-center items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            Proses Pembayaran
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <!-- Modal Struk -->
        <div v-if="showReceipt" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-sm overflow-hidden">
                <div id="print-area" class="text-center font-mono text-xs uppercase">
                    <h2 class="text-lg font-bold">POS UMKM MODERN</h2>
                    <p>--------------------------------</p>
                    <p class="text-left">{{ lastTransaction?.date }}</p>
                    <p>--------------------------------</p>
                    <div
                    v-for="item in lastTransaction?.cart"
                    :key="item.id"
                    class="flex justify-between mb-1 text-left">
                        <span>{{ item.name }} x {{ item.qty }}</span>
                        <span>Rp {{ (item.price * item.qty).toLocaleString() }}</span>
                    </div>
                    <p>--------------------------------</p>
                    <div class="flex justify-between font-bold">
                        <span>TOTAL:</span>
                        <span>Rp {{ lastTransaction?.total.toLocaleString() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>BAYAR:</span>
                        <span>Rp {{ lastTransaction?.pay.toLocaleString() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>KEMBALIAN:</span>
                        <span>Rp {{ lastTransaction?.return.toLocaleString() }}</span>
                    </div>
                    <p class="mt-4">*** TERIMA KASIH ***</p>
                </div>

                <div class="mt-6 flex gap-2 no-print">
                    <button @click="printReceipt" class="flex-1 bg-green-600 text-white py-2 rounded font-bold">
                        CETAK
                    </button>

                    <button @click="showReceipt = false" class="flex-1 bg-gray-200 py-2 rounded font-bold">
                        TUTUP
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
