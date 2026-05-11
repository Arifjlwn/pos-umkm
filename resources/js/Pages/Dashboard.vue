<script setup>
import { ref, onMounted } from 'vue';
import api from '../api.js'; // Panggil si kurir Axios

const reportData = ref(null);
const isLoading = ref(true);
const errorMessage = ref('');

// Fungsi untuk format angka ke Rupiah
const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID').format(angka);
};

// Fungsi Logout (Hapus tiket dari brankas, balik ke login)
const handleLogout = () => {
    localStorage.removeItem('token');
    window.location.href = '/login';
};

// onMounted akan berjalan otomatis saat halaman pertama kali dibuka
onMounted(async () => {
    try {
        // Tembak API Golang untuk minta data laporan
        const response = await api.get('/report/dashboard');
        reportData.value = response.data.data;
    } catch (error) {
        if (error.response && error.response.status === 403) {
            errorMessage.value = "Halaman ini khusus Bos (Owner). Kasir dilarang masuk! 🛑";
        } else if (error.response && error.response.status === 401) {
            // Kalau token kadaluarsa atau belum login
            handleLogout();
        } else {
            errorMessage.value = "Gagal menarik data dari server Golang.";
        }
    } finally {
        isLoading.value = false;
    }
});
</script>

<template>
    <div class="dashboard-container">
        <header class="header">
            <h2>📊 Dashboard POS UMKM</h2>
            <button @click="handleLogout" class="btn-logout">Logout</button>
        </header>

        <div v-if="isLoading" class="loading">Memuat data laporan...</div>

        <div v-else-if="errorMessage" class="error">
            {{ errorMessage }}
        </div>

        <div v-else class="content">
            <div class="summary-cards">
                <div class="card bg-blue">
                    <h3>Omzet Hari Ini</h3>
                    <p>Rp {{ formatRupiah(reportData?.summary?.omzet_hari_ini || 0) }}</p>
                </div>
                <div class="card bg-green">
                    <h3>Total Transaksi</h3>
                    <p>{{ reportData?.summary?.jumlah_transaksi || 0 }} Struk</p>
                </div>
                <div class="card bg-orange">
                    <h3>Barang Terjual</h3>
                    <p>{{ reportData?.summary?.total_produk_terjual || 0 }} Pcs</p>
                </div>
            </div>

            <div class="low-stock-section">
                <h3>⚠️ Peringatan Stok Menipis (< 10)</h3>
                <table v-if="reportData?.low_stock?.length > 0">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Nama Barang</th>
                            <th>Sisa Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in reportData.low_stock" :key="item.id">
                            <td>{{ item.sku }}</td>
                            <td>{{ item.nama_produk }}</td>
                            <td class="text-red"><strong>{{ item.stok }}</strong></td>
                        </tr>
                    </tbody>
                </table>
                <p v-else class="safe-stock">Aman Bos! Belum ada barang yang perlu di-restock.</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* CSS Sederhana untuk mempercantik Dashboard */
.dashboard-container {
    max-width: 900px;
    margin: 40px auto;
    font-family: sans-serif;
}
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.btn-logout {
    background-color: #dc3545;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
}
.summary-cards {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
}
.card {
    flex: 1;
    padding: 20px;
    border-radius: 8px;
    color: white;
    text-align: center;
}
.card h3 { margin: 0 0 10px 0; font-size: 16px; font-weight: normal; }
.card p { margin: 0; font-size: 24px; font-weight: bold; }
.bg-blue { background-color: #007bff; }
.bg-green { background-color: #28a745; }
.bg-orange { background-color: #fd7e14; }

.low-stock-section { margin-top: 30px; }
table { width: 100%; border-collapse: collapse; margin-top: 10px; }
table, th, td { border: 1px solid #ddd; }
th, td { padding: 12px; text-align: left; }
th { background-color: #f4f4f4; }
.text-red { color: red; }
.safe-stock { color: #28a745; font-style: italic; }
.error { background: #ffe6e6; color: red; padding: 15px; border-radius: 5px; }
</style>
