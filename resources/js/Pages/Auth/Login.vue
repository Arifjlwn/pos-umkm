<template>
    <div class="login-container">
        <h2>Login POS UMKM</h2>

        <form @submit.prevent="handleLogin">
            <div class="form-group">
                <label>Email / NIK Kasir</label>
                <input
                    type="text"
                    v-model="identifier"
                    placeholder="Masukkan Email atau NIK"
                    required
                />
            </div>

            <div class="form-group">
                <label>Password</label>
                <input
                    type="password"
                    v-model="password"
                    placeholder="Masukkan Password"
                    required
                />
            </div>

            <button type="submit" :disabled="isLoading">
                {{ isLoading ? 'Memeriksa...' : 'Masuk' }}
            </button>

            <p v-if="errorMessage" class="error-text">{{ errorMessage }}</p>
        </form>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import api from '../../api.js'; // Panggil kurir Axios (Posisinya 2 folder di luar)

// State (Wadah penampung inputan)
const identifier = ref('');
const password = ref('');
const errorMessage = ref('');
const isLoading = ref(false);

// Fungsi saat tombol Masuk diklik
const handleLogin = async () => {
    isLoading.value = true;
    errorMessage.value = '';

    try {
        // Tembak API Golang
        const response = await api.post('/login', {
            identifier: identifier.value,
            password: password.value
        });

        // Kalau sukses, simpan tiket JWT ke brankas browser
        const token = response.data.token;
        localStorage.setItem('token', token);

        // Pakai cara bawaan browser/Inertia untuk pindah halaman
        window.location.href = '/dashboard';

    } catch (error) {
        // Tangkap pesan error dari Golang
        if (error.response && error.response.data.error) {
            errorMessage.value = error.response.data.error;
        } else {
            errorMessage.value = "Gagal terhubung ke server.";
        }
    } finally {
        isLoading.value = false;
    }
};
</script>

<style scoped>
.login-container {
    max-width: 400px;
    margin: 100px auto;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-family: sans-serif;
}
.form-group {
    margin-bottom: 15px;
}
.form-group label {
    display: block;
    margin-bottom: 5px;
}
.form-group input {
    width: 100%;
    padding: 8px;
    box-sizing: border-box;
}
button {
    width: 100%;
    padding: 10px;
    background-color: #007BFF;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
button:disabled {
    background-color: #aaa;
}
.error-text {
    color: red;
    margin-top: 10px;
    text-align: center;
}
</style>
