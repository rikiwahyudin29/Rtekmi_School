<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    binaan: Array,
    total_pending: Number
});
</script>

<template>
    <Head title="Dashboard Pembina Ekskul" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-running text-orange-500"></i>
                        Dashboard Pembina Ekstrakurikuler
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Selamat datang! Berikut adalah unit ekstrakurikuler yang Anda bina.
                    </p>
                </div>
            </div>

            <div v-if="total_pending > 0" class="bg-orange-50 dark:bg-orange-900/30 border-l-4 border-orange-500 p-4 rounded-r-xl shadow-sm flex items-start gap-4">
                <i class="fas fa-bell text-orange-500 text-xl mt-0.5"></i>
                <div>
                    <h4 class="font-bold text-orange-800 dark:text-orange-400">Pemberitahuan Pendaftar Baru</h4>
                    <p class="text-sm text-orange-700 dark:text-orange-300 mt-1">Ada <strong>{{ total_pending }}</strong> siswa yang mendaftar dan menunggu validasi Anda. Segera cek menu Kelola Anggota.</p>
                </div>
            </div>

            <div v-if="!binaan || binaan.length === 0" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-12 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-red-50 dark:bg-red-900/30 text-red-500 mb-4">
                    <i class="fas fa-ban text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Belum Ada Ekstrakurikuler Binaan</h3>
                <p class="text-gray-500 dark:text-gray-400">Anda belum ditugaskan sebagai pembina unit ekstrakurikuler manapun oleh Admin.</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <!-- Card Ekskul Binaan -->
                <div v-for="b in binaan" :key="b.id" class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-700 overflow-hidden group hover:border-orange-500 transition-colors duration-300">
                    <div class="h-24 bg-gradient-to-r from-orange-500 to-amber-500 relative">
                        <div class="absolute -bottom-8 left-6">
                            <div class="w-16 h-16 rounded-2xl bg-white dark:bg-gray-800 p-2 shadow-lg border border-gray-100 dark:border-gray-700">
                                <img :src="`/uploads/ekskul/${b.logo || 'default.png'}`" @error="$event.target.src='https://ui-avatars.com/api/?background=random&name='+b.nama_ekskul" class="w-full h-full object-contain">
                            </div>
                        </div>
                        <div class="absolute top-4 right-4 bg-white/20 backdrop-blur-md px-3 py-1 rounded-full text-white text-xs font-bold border border-white/30 shadow-sm">
                            <i class="fas fa-star mr-1"></i> Pembina
                        </div>
                    </div>
                    <div class="p-6 pt-10">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1 group-hover:text-orange-600 transition-colors">{{ b.nama_ekskul }}</h3>
                        
                        <div class="flex items-center gap-4 mt-4 py-4 border-y border-gray-100 dark:border-gray-700">
                            <div class="flex-1 text-center border-r border-gray-100 dark:border-gray-700">
                                <div class="text-2xl font-black text-gray-900 dark:text-white">{{ b.jml_aktif }}</div>
                                <div class="text-xs text-gray-500 font-medium uppercase tracking-wider">Anggota Aktif</div>
                            </div>
                            <div class="flex-1 text-center">
                                <div class="text-2xl font-black" :class="b.jml_pending > 0 ? 'text-orange-600' : 'text-gray-900 dark:text-white'">{{ b.jml_pending }}</div>
                                <div class="text-xs text-gray-500 font-medium uppercase tracking-wider">Pendaftar Baru</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 mt-4">
                            <Link :href="route('guru.ekskul.anggota', b.id)" class="px-4 py-2.5 bg-gray-50 dark:bg-gray-700 hover:bg-orange-50 dark:hover:bg-orange-900/30 text-gray-700 dark:text-gray-300 hover:text-orange-600 dark:hover:text-orange-400 rounded-xl text-sm font-bold text-center transition-all flex flex-col items-center gap-1 border border-transparent hover:border-orange-200 dark:hover:border-orange-800">
                                <i class="fas fa-users text-lg"></i>
                                Anggota
                            </Link>
                            <Link :href="route('guru.ekskul.jurnal', b.id)" class="px-4 py-2.5 bg-gray-50 dark:bg-gray-700 hover:bg-orange-50 dark:hover:bg-orange-900/30 text-gray-700 dark:text-gray-300 hover:text-orange-600 dark:hover:text-orange-400 rounded-xl text-sm font-bold text-center transition-all flex flex-col items-center gap-1 border border-transparent hover:border-orange-200 dark:hover:border-orange-800">
                                <i class="fas fa-book-open text-lg"></i>
                                Jurnal & Absen
                            </Link>
                            <Link :href="route('guru.ekskul.prestasi', b.id)" class="px-4 py-2.5 bg-gray-50 dark:bg-gray-700 hover:bg-orange-50 dark:hover:bg-orange-900/30 text-gray-700 dark:text-gray-300 hover:text-orange-600 dark:hover:text-orange-400 rounded-xl text-sm font-bold text-center transition-all flex flex-col items-center gap-1 border border-transparent hover:border-orange-200 dark:hover:border-orange-800">
                                <i class="fas fa-trophy text-lg"></i>
                                Prestasi
                            </Link>
                            <Link :href="route('guru.ekskul.penilaian', b.id)" class="px-4 py-2.5 bg-orange-600 hover:bg-orange-700 text-white shadow-lg shadow-orange-500/30 rounded-xl text-sm font-bold text-center transition-all flex flex-col items-center justify-center gap-1">
                                <i class="fas fa-star text-lg"></i>
                                Penilaian
                            </Link>
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <a :href="route('guru.ekskul.cetak_lpj', b.id)" target="_blank" class="w-full block px-4 py-2.5 bg-gray-900 dark:bg-gray-600 hover:bg-black dark:hover:bg-gray-500 text-white rounded-xl text-sm font-bold text-center transition-all shadow-md">
                                <i class="fas fa-print mr-2"></i> Cetak Laporan (LPJ)
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
