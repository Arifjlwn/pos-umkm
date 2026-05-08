<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    karyawan: Array,
});

const currentTime = ref('');
let timer;

// Pisahkan fungsi update jam agar bisa dipanggil langsung
const updateTime = () => {
    const now = new Date();
    // Gunakan replace agar selalu rapi menggunakan titik (06.30.00)
    currentTime.value = now.toLocaleTimeString('id-ID', {
        hour: '2-digit', minute: '2-digit', second: '2-digit'
    }).replace(/:/g, '.');
};

onMounted(() => {
    updateTime(); // 1. Panggil langsung detik itu juga (hilangkan delay)
    timer = setInterval(updateTime, 1000); // 2. Lanjutkan setiap 1 detik
});

onUnmounted(() => {
    clearInterval(timer);
});

const prosesAbsen = (nama, jenis) => {
    alert(`Fitur rekam ${jenis} untuk ${nama} akan segera disambungkan ke database!`);
};
</script>

<template>
    <Head title="Absensi Karyawan" />

    <AppLayout>
        <div class="p-6 md:p-8 max-w-7xl mx-auto">
            <div class="bg-blue-800 rounded-3xl p-6 md:p-8 mb-8 text-white shadow-xl flex flex-col md:flex-row items-center justify-between overflow-hidden relative border border-blue-900">
                <div class="absolute -right-10 -top-20 opacity-10 text-[200px] font-black italic pointer-events-none">
                    ⏱️
                </div>
                <div class="relative z-10 text-center md:text-left mb-6 md:mb-0">
                    <h1 class="text-3xl font-black trackingtight mb-2">
                        Mesin Absensi
                    </h1>
                    <p class="text-blue-200 font-medium text-sm">
                        Catat jam masuk dan pulang karyawan dengan akurat.
                    </p>
                </div>
                <div class="relative z-10 bg-white/10 backdrop-blur md border border-white/20 rounded-2xl p-4 md:px-8 text-center shadow-inner">
                    <div class="text-sm font-bold text-blue-200 uppercase tracking-widest mb-1">
                        Waktu Saat Ini
                    </div>
                    <div class="font-mono text-4xl md:text-5xl font-black text-yellow-300 tracking-wider drop-shadow-md">
                        {{ currentTime || '00.00.00' }}
                    </div>
                </div>
            </div>

            <h2 class="text-lg font-black text-gray-800 mb-4 flex items-center gap-2">
                👥 Daftar Karyawan Toko
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="user in karyawan" :key="user.id" class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center gap-4 mb-5 border-b border-gray-50 pb-4">
                        <div class="w-14 h-14 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 border-2 border-white shadow-md flex items-center justify-center shrink-0">
                            <span class="text-blue-700 font-black text-lg uppercase">{{ user.name.substring(0, 2) }}</span>
                        </div>
                        <div>
                            <h3 class="font-black text-gray-900 text-lg leading-tight">{{ user.name }}</h3>
                            <div class="text-xs font-bold px-2 py-0.5 mt-1 rounded-md inline-block uppercase tracking-wider"
                                 :class="user.role === 'owner' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'">
                                {{ user.role }}
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button @click="prosesAbsen(user.name, 'Masuk')" class="flex-1 bg-green-50 text-green-700 border border-green-200 hover:bg-green-600 hover:text-white py-3 rounded-xl font-black text-sm transition-all shadow-sm flex items-center justify-center gap-1.5 active:scale-95">
                            <span class="text-lg">📥</span> Masuk
                        </button>
                        <button @click="prosesAbsen(user.name, 'Pulang')" class="flex-1 bg-orange-50 text-orange-700 border border-orange-200 hover:bg-orange-600 hover:text-white py-3 rounded-xl font-black text-sm transition-all shadow-sm flex items-center justify-center gap-1.5 active:scale-95">
                            <span class="text-lg">📤</span> Pulang
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="karyawan.length === 1" class="mt-8 bg-blue-50 border border-blue-100 rounded-2xl p-6 text-center">
                <p class="text-blue-800 font-bold mb-2">Toko Anda belum memiliki karyawan tambahan.</p>
                <p class="text-blue-600 text-sm">Tambahkan akun kasir di menu Manajemen Karyawan agar mereka bisa melakukan absensi.</p>
            </div>
        </div>
    </AppLayout>
</template>
