<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Masuk" />

    <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 font-sans">

        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
            <div class="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center mx-auto shadow-lg mb-4 transform -rotate-3 hover:rotate-0 transition-transform duration-300">
                <span class="text-3xl text-white font-black italic">POS</span>
            </div>
            <h2 class="text-3xl font-black text-gray-900 tracking-tight">
                Selamat Datang
            </h2>
            <p class="mt-2 text-sm text-gray-500 font-medium">
                Masuk untuk mengelola operasional tokomu.
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-6 shadow-2xl sm:rounded-3xl sm:px-10 border border-gray-100">

                <div v-if="status" class="mb-5 p-4 rounded-xl bg-green-50 border border-green-200 text-sm font-bold text-green-700 text-center shadow-sm">
                    {{ status }}
                </div>

                <form class="space-y-5" @submit.prevent="submit">

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Alamat Email</label>
                        <input v-model="form.email" type="email" required autofocus autocomplete="username"
                            class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm font-medium"
                            placeholder="nama@toko.com">
                        <InputError class="mt-1" :message="form.errors.email" />
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Kata Sandi</label>
                        <input v-model="form.password" type="password" required autocomplete="current-password"
                            class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm font-medium"
                            placeholder="Masukkan kata sandi">
                        <InputError class="mt-1" :message="form.errors.password" />
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <div class="flex items-center">
                            <input v-model="form.remember" id="remember" type="checkbox"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded cursor-pointer">
                            <label for="remember" class="ml-2 block text-sm text-gray-700 font-bold cursor-pointer">
                                Ingat saya
                            </label>
                        </div>

                        <div class="text-sm" v-if="canResetPassword">
                            <Link :href="route('password.request')" class="font-bold text-blue-600 hover:text-blue-800 hover:underline transition-all">
                                Lupa sandi?
                            </Link>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" :disabled="form.processing"
                            class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-md text-sm font-black text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all disabled:opacity-50 hover:-translate-y-0.5">
                            {{ form.processing ? 'Memeriksa Data...' : 'Masuk 🚀' }}
                        </button>
                    </div>

                    <div class="mt-6 text-center text-sm font-medium text-gray-600 border-t pt-5 border-gray-100">
                        Belum punya akun toko?
                        <Link :href="route('register')" class="text-blue-600 hover:text-blue-800 font-bold hover:underline transition-all">
                            Daftar di sini
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
