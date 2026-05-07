<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

// State sidebarOpen sekarang default-nya false untuk semua perangkat
const sidebarOpen = ref(false);
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex flex-col relative overflow-hidden">

        <header class="bg-white shadow-sm border-b border-gray-200 flex items-center justify-between px-4 py-3 sticky top-0 z-40">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = true" class="text-gray-600 hover:text-blue-600 focus:outline-none transition-colors p-2 rounded-lg hover:bg-gray-100">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <div class="font-black text-2xl text-blue-600 tracking-tighter hidden sm:block">
                    POS<span class="text-gray-800">UMKM</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-gray-50 rounded-full border border-gray-200">
                    <div class="w-6 h-6 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-bold">A</div>
                    <span class="text-sm font-bold text-gray-700">Arif Juliawan</span>
                </div>
            </div>
        </header>

        <div
            v-if="sidebarOpen"
            @click="sidebarOpen = false"
            class="fixed inset-0 bg-black/60 z-40 backdrop-blur-sm transition-opacity"
        ></div>

        <div
            :class="sidebarOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 w-72 bg-white border-r border-gray-200 transform transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)] z-50 flex flex-col"
        >
            <div class="flex items-center justify-between h-[61px] px-6 border-b border-gray-100 bg-gray-50 shrink-0">
                <div class="font-black text-2xl text-blue-600 tracking-tighter">
                    POS<span class="text-gray-800">UMKM</span>
                </div>
                <button @click="sidebarOpen = false" class="p-2 -mr-2 text-gray-400 hover:text-red-500 rounded-lg hover:bg-red-50 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                <div class="text-xs font-black text-gray-400 uppercase tracking-widest px-4 mb-4">Menu Utama</div>

                <Link
                    href="/pos"
                    @click="sidebarOpen = false"
                    class="flex items-center gap-4 px-4 py-3.5 rounded-xl text-sm font-bold transition-all"
                    :class="$page.url === '/pos' ? 'bg-blue-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                >
                    <span class="text-xl">🛒</span>
                    POS Kasir
                </Link>

                <Link
                    href="/products"
                    @click="sidebarOpen = false"
                    class="flex items-center gap-4 px-4 py-3.5 rounded-xl text-sm font-bold transition-all"
                    :class="$page.url.startsWith('/products') ? 'bg-blue-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                >
                    <span class="text-xl">📦</span>
                    Master Stock
                </Link>

                </nav>

            <div class="p-4 border-t border-gray-100 bg-gray-50 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gray-200 border-2 border-white shadow-sm overflow-hidden flex items-center justify-center">
                         <span class="text-gray-500 font-bold text-xs">AJ</span>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-gray-800 leading-tight">Arif Juliawan</div>
                        <div class="text-xs text-green-600 font-medium">Administrator</div>
                    </div>
                </div>
            </div>
        </div>

        <main class="flex-1 w-full max-w-full overflow-y-auto bg-gray-100 h-[calc(100vh-61px)]">
            <slot />
        </main>

    </div>
</template>
