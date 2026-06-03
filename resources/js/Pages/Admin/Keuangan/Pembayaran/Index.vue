<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    siswa: Array,
    keyword: String,
});

const search = ref(props.keyword || '');

// Debounce search
let searchTimeout;
watch(search, (newSearch) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/admin/keuangan/pembayaran', { q: newSearch }, {
            preserveState: true,
            replace: true,
        });
    }, 500);
});
</script>

<template>
    <Head title="Kasir Pembayaran" />

    <DashboardLayout>
        <div class="flex flex-col h-full min-h-screen bg-gray-50 dark:bg-gray-900">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 w-full pt-12 pb-8">
                
                <div class="text-center mb-10">
                    <div class="w-20 h-20 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center mx-auto mb-4 shadow-inner shadow-primary-200">
                        <i class="fas fa-cash-register text-3xl"></i>
                    </div>
                    <h2 class="font-black text-3xl text-gray-900 dark:text-white tracking-tight">Kasir Pembayaran</h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-2 text-lg">Cari siswa untuk melihat atau memproses pembayaran tagihan.</p>
                </div>

                <!-- Search Box -->
                <div class="bg-white dark:bg-gray-800 p-3 rounded-2xl shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-700 max-w-2xl mx-auto relative group focus-within:ring-4 focus-within:ring-primary-500/20 transition-all">
                    <i class="fas fa-search absolute left-6 top-6 text-gray-400 text-xl group-focus-within:text-primary-500 transition-colors"></i>
                    <input type="text" v-model="search" autofocus placeholder="Masukkan NISN atau Nama Siswa..." class="w-full pl-14 pr-4 py-3 border-none bg-transparent focus:ring-0 text-lg text-gray-900 dark:text-white placeholder-gray-400">
                    
                    <div v-if="search && search.length > 0 && (!siswa || siswa.length === 0)" class="absolute top-full left-0 right-0 mt-2 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 p-6 text-center z-10">
                        <i class="fas fa-user-slash text-gray-300 text-4xl mb-2"></i>
                        <p class="text-gray-500 font-medium">Siswa tidak ditemukan</p>
                    </div>

                    <div v-if="siswa && siswa.length > 0" class="absolute top-full left-0 right-0 mt-2 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden z-20 divide-y divide-gray-100 dark:divide-gray-700 max-h-96 overflow-y-auto">
                        <Link v-for="s in siswa" :key="s.id" :href="`/admin/keuangan/pembayaran/siswa/${s.id}`" class="flex items-center gap-4 p-4 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors cursor-pointer group/item block w-full text-left">
                            <div class="relative">
                                <img :src="`/uploads/siswa/${s.foto}`" @error="$event.target.src='https://ui-avatars.com/api/?background=random&name='+s.nama_lengkap" class="w-12 h-12 rounded-xl object-cover border border-gray-200 group-hover/item:border-primary-300">
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-900 dark:text-white text-base group-hover/item:text-primary-700 transition-colors">{{ s.nama_lengkap }}</h4>
                                <p class="text-sm text-gray-500 font-medium">NISN: {{ s.nisn || '-' }} <span class="mx-1">•</span> <span class="text-primary-600">{{ s.kelas?.nama_kelas || '-' }}</span></p>
                            </div>
                            <div class="text-primary-400 group-hover/item:text-primary-600 group-hover/item:translate-x-1 transition-all">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </Link>
                    </div>
                </div>
                
                <div class="text-center mt-12 text-sm text-gray-400 flex items-center justify-center gap-2">
                    <i class="fas fa-keyboard"></i> Tekan <kbd class="px-2 py-1 bg-white border border-gray-200 rounded-lg text-xs font-sans text-gray-500">Esc</kbd> untuk menutup hasil
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
