<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    products: Array,
});

// State untuk mode edit
const isEditing = ref(false);
const editId = ref(null);

// Menggunakan useForm dari inertia untuk mengelola form
const form = useForm({
    name: '',
    price: '',
    stock: '',
    image: null,
});

// Fungsi untuk memulai mode edit
const editProduct = (product) => {
    isEditing.value = true;
    editId.value = product.id;
    form.name = product.name;
    form.price = product.price;
    form.stock = product.stock;
    form.image = null; //Dikosongkan agar kasir bisa milih tetep pakai foto lama atau upload baru
};

// fungsi untuk membatalkan mode edit
const cancelEdit = () => {
    isEditing.value = false;
    editId.value = null;
    form.reset(); // Reset form ke nilai awal
};

// Fungsi hapus barang
const deleteProduct = (id) => {
    if (confirm('Yakin ingin menghapus produk ini?')) {
        router.delete(`/products/${id}`, { 
            preserveScroll: true,
            onError: (errors) => {
                // Munculkan notifikasi jika ada error dari Controller
                if (errors.message) {
                    alert(errors.message);
                }
            }
        });
    }
};

// Fungsi Submit (untuk tambah dan update)
const submitProduct = () => {
    if (isEditing.value) {
        // Mode UPDATE (Harus pakai router.post dengan _method: 'put' kalau ada upload file di Inertia)
        router.post(`/products/${editId.value}`, {
            _method: 'put',
            name: form.name,
            price: form.price,
            stock: form.stock,
            image: form.image,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                cancelEdit();
                alert('Produk berhasil diperbarui!');
            }
        });
    } else {
        // Mode Tambah Baru
        form.post('/products', {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                alert('Produk berhasil ditambahkan!');
            },
        });
    }
};
</script>

<template>
    <Head title="Master Produk" />

    <div class="p-6 bg-gray-100 min-h-screen">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row gap-6">
            <!-- Form Produk (Dinamis: Tambah / Edit) -->
            <div class="w-full md:w-1/3 bg-white p-6 rounded-xl shadow-sm h-fit border-t-4" :class="isEditing ? 'border-yellow-400' : 'border-blue-600'">
                <h2 class="text-xl font-bold mb-4 text-gray-800">
                    {{ isEditing ? '✏️ Edit Data Produk' : '➕ Tambah Produk Baru' }}
                </h2>
                
                <form @submit.prevent="submitProduct" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Barang</label>
                        <input type="text" v-model="form.name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                    </div>
                    
                    <div class="flex gap-4">
                        <div class="w-1/2">
                            <label class="block text-sm font-medium text-gray-700">Harga (Rp)</label>
                            <input type="number" v-model="form.price" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        </div>
                        <div class="w-1/2">
                            <label class="block text-sm font-medium text-gray-700">Stok Barang</label>
                            <input type="number" v-model="form.stock" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Foto Produk {{ isEditing ? '(Opsional)' : '' }}</label>
                        <input type="file" @input="form.image = $event.target.files[0]" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" accept="image/*">
                        <p v-if="isEditing" class="text-xs text-gray-400 mt-1">*Kosongkan jika tidak ingin mengubah foto</p>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" :disabled="form.processing" class="flex-1 bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700 transition disabled:opacity-50">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Data' }}
                        </button>
                        <button v-if="isEditing" type="button" @click="cancelEdit" class="flex-1 bg-gray-200 text-gray-700 font-bold py-2 px-4 rounded-lg hover:bg-gray-300 transition">
                            Batal
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabel Daftar Produk -->
            <div class="w-full md:w-2/3 bg-white p-6 rounded-xl shadow-sm">
                <h2 class="text-xl font-bold mb-4 text-gray-800">Daftar Produk</h2>
                <div class="overflow-x-auto">
                    <table class="w-full txt-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-700 border-b">
                                <th class="p-3">Foto</th>
                                <th class="p-3">Nama Barang</th>
                                <th class="p-3">Harga</th>
                                <th class="p-3">Stok</th>
                                <th class="p-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="product in products"
                            :key="product.id"
                            class="border-b hover:bg-gray-50">
                                <td class="p-3">
                                    <img :src="product.image || 'https://placehold.co/100x100?text=No+Image'" class="w-12 h-12 object-cover rounded-lg border bg-white">
                                </td>
                                <td class="p-3 font-medium">{{ product.name }}</td>
                                <td class="p-3 text-blue-600 font-bold">Rp {{ product.price.toLocaleString('id-ID') }}</td>
                                <td class="p-3">
                                    <span class="px-2 py-1 text-xs rounded-full font-bold" :class="product.stock > 10 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                                        {{ product.stock }}
                                    </span>
                                </td>
                                <td class="p-3 flex justify-center gap-2">
                                    <!-- Tombol Edit -->
                                    <button @click="editProduct(product)" class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded shadow-sm hover:bg-yellow-200 text-sm font-bold transition">
                                        Edit
                                    </button>
                                    <!-- Tombol Hapus -->
                                    <button @click="deleteProduct(product.id)" class="bg-red-100 text-red-700 px-3 py-1 rounded shadow-sm hover:bg-red-200 text-sm font-bold transition">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>