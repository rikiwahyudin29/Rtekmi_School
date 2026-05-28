<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

defineProps({
    stats: Object,
    chart: Object,
});

const auth = usePage().props.auth;
</script>

<template>
    <Head title="Siswa Dashboard" />

    <DashboardLayout>
        
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-1">Dashboard Siswa</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Selamat datang kembali, {{ auth.user.nama_lengkap || auth.user.username }}!</p>
                </div>
            </div>
        </div>

        <!-- 3 Stat Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Card 1 -->
            <div class="bg-primary-700 rounded-3xl p-6 text-white shadow-sm relative overflow-hidden group cursor-pointer hover:shadow-md transition-shadow">
                <div class="absolute top-6 right-6 w-8 h-8 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm group-hover:bg-white/30 transition-colors">
                    <i class="fas fa-file-alt text-white text-sm"></i>
                </div>
                <h3 class="text-primary-50 font-medium mb-2">Ujian Diikuti</h3>
                <p class="text-5xl font-bold mb-4 tracking-tight">{{ stats.ujian_diikuti }}</p>
                <div class="flex items-center gap-2 text-xs">
                    <span class="bg-primary-600/50 px-2 py-1 rounded text-primary-50 font-bold"><i class="fas fa-check mr-1"></i> Selesai</span>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group cursor-pointer hover:shadow-md transition-shadow">
                <div class="absolute top-6 right-6 w-8 h-8 border border-gray-200 dark:border-gray-600 rounded-full flex items-center justify-center group-hover:bg-gray-50 dark:group-hover:bg-gray-700 transition-colors">
                    <i class="fas fa-clock text-gray-400 text-sm"></i>
                </div>
                <h3 class="text-gray-500 dark:text-gray-400 font-medium mb-2">Ujian Aktif Saat Ini</h3>
                <p class="text-5xl font-bold mb-4 tracking-tight text-gray-900 dark:text-white">{{ stats.ujian_aktif }}</p>
                <div class="flex items-center gap-2 text-xs">
                    <span class="bg-emerald-50 text-emerald-600 px-2 py-1 rounded font-bold border border-emerald-100"><i class="fas fa-bell mr-1"></i> Segera Kerjakan</span>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group cursor-pointer hover:shadow-md transition-shadow">
                <div class="absolute top-6 right-6 w-8 h-8 border border-gray-200 dark:border-gray-600 rounded-full flex items-center justify-center group-hover:bg-gray-50 dark:group-hover:bg-gray-700 transition-colors">
                    <i class="fas fa-book-open text-gray-400 text-sm"></i>
                </div>
                <h3 class="text-gray-500 dark:text-gray-400 font-medium mb-2">Tugas Belum Selesai</h3>
                <p class="text-5xl font-bold mb-4 tracking-tight text-gray-900 dark:text-white">{{ stats.tugas_belum }}</p>
                <div class="flex items-center gap-2 text-xs">
                    <span class="bg-blue-50 text-blue-600 px-2 py-1 rounded font-bold border border-blue-100"><i class="fas fa-tasks mr-1"></i> E-Learning</span>
                </div>
            </div>
        </div>

        <!-- Middle Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Analytics Chart Mockup -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 lg:col-span-1">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Grafik Kehadiran Belajar</h3>
                <div class="flex items-end justify-between h-40 gap-2">
                    <div v-for="(val, index) in chart.kehadiran" :key="index" class="w-full relative rounded-t-full flex flex-col justify-end" :class="val > 90 ? 'bg-primary-600' : (val > 0 ? 'bg-primary-400' : 'bg-gray-100 dark:bg-gray-700')" :style="`height: ${val > 0 ? val : 20}%`">
                        <div v-if="val === Math.max(...chart.kehadiran)" class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-white dark:bg-gray-800 text-xs font-bold px-2 py-1 rounded shadow border border-gray-100 dark:border-gray-700 z-10">{{ val }}%</div>
                    </div>
                </div>
                <div class="flex justify-between text-xs font-bold text-gray-400 mt-4 px-2 uppercase">
                    <span v-for="label in chart.labels" :key="label">{{ label.charAt(0) }}</span>
                </div>
            </div>

            <!-- Quick Action / Info -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 lg:col-span-1 flex flex-col justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Pengumuman Terbaru</h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-center gap-4 p-3 rounded-2xl bg-gray-50 dark:bg-gray-700 border border-transparent">
                            <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 flex items-center justify-center shrink-0">
                                <i class="fas fa-bullhorn text-lg"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-sm text-gray-900 dark:text-white">Ujian Akhir Semester</h4>
                                <p class="text-xs text-gray-500 mt-1">Ujian akan dilaksanakan minggu depan, persiapkan diri dengan baik.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-4 p-3 rounded-2xl bg-gray-50 dark:bg-gray-700 border border-transparent">
                            <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 flex items-center justify-center shrink-0">
                                <i class="fas fa-book text-lg"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-sm text-gray-900 dark:text-white">Pengembalian Buku</h4>
                                <p class="text-xs text-gray-500 mt-1">Mohon kembalikan buku perpustakaan sebelum tanggal 30.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </DashboardLayout>
</template>
