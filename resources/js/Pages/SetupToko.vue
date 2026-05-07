<script setup>
import { Head, useForm } from '@inertiajs/vue3';

// Siapkan form Inertia
const form = useForm({
    nama_toko: '',
    alamat_toko: '',
    telepon: '',
});

// Fungsi submit data
const submit = () => {
    form.post('/setup-toko');
};
</script>

<template>
    <Head title="Setup Toko" />

    <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 font-sans">
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
            <div class="w-20 h-20 bg-blue-600 rounded-3xl flex items-center justify-center mx-auto shadow-xl transform -rotate-3 hover:rotate-0 transition-transform duration-300">
                <span class="text-4xl">🏪</span>
            </div>
            <h2 class="mt-6 text-3xl font-black text-gray-900 tracking-tight">
                Lengkapi Profil Toko
            </h2>
            <p class="mt-2 text-sm text-gray-500 font-medium">
                Satu langkah lagi sebelum Anda bisa mulai berjualan!
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-6 shadow-2xl sm:rounded-3xl sm:px-10 border border-gray-100">
                <form class="space-y-6" @submit.prevent="submit">

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Nama Toko / Bisnis <span class="text-red-500">*</span></label>
                        <input
                            v-model="form.nama_toko"
                            type="text"
                            required
                            placeholder="Contoh: Toko Berkah Maju"
                            class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm font-medium"
                        >
                        <div v-if="form.errors.nama_toko" class="text-red-500 text-xs mt-1 font-medium">{{ form.errors.nama_toko }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Alamat Lengkap <span class="text-red-500">*</span></label>
                        <textarea
                            v-model="form.alamat_toko"
                            rows="3"
                            required
                            placeholder="Masukkan alamat lengkap toko..."
                            class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm font-medium"
                        ></textarea>
                        <div v-if="form.errors.alamat_toko" class="text-red-500 text-xs mt-1 font-medium">{{ form.errors.alamat_toko }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Nomor WhatsApp / Telepon</label>
                        <input
                            v-model="form.telepon"
                            type="text"
                            placeholder="Contoh: 081234567890"
                            class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm font-medium"
                        >
                    </div>

                    <div class="pt-2">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-md text-sm font-black text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all disabled:opacity-50 hover:-translate-y-0.5"
                        >
                            {{ form.processing ? 'Menyiapkan Toko...' : 'Simpan & Buka Kasir 🚀' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
