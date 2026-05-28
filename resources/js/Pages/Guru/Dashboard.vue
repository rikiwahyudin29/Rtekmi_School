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
    <Head title="Guru Dashboard" />

    <DashboardLayout>
        
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-1">Dashboard Guru</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Selamat datang kembali, {{ auth.user.nama_lengkap || auth.user.username }}!</p>
                </div>
            </div>
        </div>

        <!-- 3 Stat Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Card 1 -->
            <div class="bg-primary-700 rounded-3xl p-6 text-white shadow-sm relative overflow-hidden group cursor-pointer hover:shadow-md transition-shadow">
                <div class="absolute top-6 right-6 w-8 h-8 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm group-hover:bg-white/30 transition-colors">
                    <i class="fas fa-database text-white text-sm"></i>
                </div>
                <h3 class="text-primary-50 font-medium mb-2">Bank Soal Anda</h3>
                <p class="text-5xl font-bold mb-4 tracking-tight">{{ stats.bank_soal }}</p>
                <div class="flex items-center gap-2 text-xs">
                    <Link :href="route('admin.cbt.bank-soal.index')" class="bg-primary-600/50 hover:bg-primary-500 px-3 py-1.5 rounded-lg text-primary-50 font-bold transition-colors">
                        <i class="fas fa-arrow-right mr-1"></i> Kelola
                    </Link>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group cursor-pointer hover:shadow-md transition-shadow">
                <div class="absolute top-6 right-6 w-8 h-8 border border-gray-200 dark:border-gray-600 rounded-full flex items-center justify-center group-hover:bg-gray-50 dark:group-hover:bg-gray-700 transition-colors">
                    <i class="fas fa-clipboard-list text-gray-400 text-sm"></i>
                </div>
                <h3 class="text-gray-500 dark:text-gray-400 font-medium mb-2">Draft Ujian Anda</h3>
                <p class="text-5xl font-bold mb-4 tracking-tight text-gray-900 dark:text-white">{{ stats.draft_ujian }}</p>
                <div class="flex items-center gap-2 text-xs">
                    <Link :href="route('admin.cbt.draft-ujian.index')" class="bg-primary-50 hover:bg-primary-100 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 px-3 py-1.5 rounded-lg font-bold border border-primary-100 dark:border-primary-800 transition-colors">
                        <i class="fas fa-arrow-right mr-1"></i> Kelola
                    </Link>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group cursor-pointer hover:shadow-md transition-shadow">
                <div class="absolute top-6 right-6 w-8 h-8 border border-gray-200 dark:border-gray-600 rounded-full flex items-center justify-center group-hover:bg-gray-50 dark:group-hover:bg-gray-700 transition-colors">
                    <i class="fas fa-calendar-alt text-gray-400 text-sm"></i>
                </div>
                <h3 class="text-gray-500 dark:text-gray-400 font-medium mb-2">Jadwal Ujian Terhubung</h3>
                <p class="text-5xl font-bold mb-4 tracking-tight text-gray-900 dark:text-white">{{ stats.jadwal_ujian }}</p>
                <div class="flex items-center gap-2 text-xs">
                    <Link :href="route('admin.cbt.jadwal-ujian.index')" class="bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 px-3 py-1.5 rounded-lg font-bold border border-blue-100 dark:border-blue-800 transition-colors">
                        <i class="fas fa-arrow-right mr-1"></i> Kelola
                    </Link>
                </div>
            </div>
        </div>

        <!-- Middle Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Analytics Chart Mockup -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 lg:col-span-1">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Aktivitas Mingguan Anda</h3>
                <div class="flex items-end justify-between h-40 gap-2">
                    <div v-for="(val, index) in chart.presensi" :key="index" class="w-full relative rounded-t-full flex flex-col justify-end" :class="val > 90 ? 'bg-primary-600' : (val > 0 ? 'bg-primary-400' : 'bg-gray-100 dark:bg-gray-700')" :style="`height: ${val > 0 ? val : 20}%`">
                        <div v-if="val === Math.max(...chart.presensi)" class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-white dark:bg-gray-800 text-xs font-bold px-2 py-1 rounded shadow border border-gray-100 dark:border-gray-700 z-10">{{ val }}%</div>
                    </div>
                </div>
                <div class="flex justify-between text-xs font-bold text-gray-400 mt-4 px-2 uppercase">
                    <span v-for="label in chart.labels" :key="label">{{ label.charAt(0) }}</span>
                </div>
            </div>

            <!-- Quick Action / Info -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 lg:col-span-1 flex flex-col justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Jalan Pintas CBT</h3>
                    
                    <div class="space-y-4">
                        <Link :href="route('admin.cbt.bank-soal.index')" class="flex items-center gap-4 p-3 rounded-2xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer border border-transparent hover:border-gray-100 dark:hover:border-gray-600">
                            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 flex items-center justify-center shrink-0">
                                <i class="fas fa-plus-circle text-lg"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-sm text-gray-900 dark:text-white">Buat Bank Soal Baru</h4>
                                <p class="text-xs text-gray-500">Mulai buat bank soal untuk ujian yang akan datang.</p>
                            </div>
                        </Link>
                        
                        <Link :href="route('admin.cbt.overview.index')" class="flex items-center gap-4 p-3 rounded-2xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer border border-transparent hover:border-gray-100 dark:hover:border-gray-600">
                            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 flex items-center justify-center shrink-0">
                                <i class="fas fa-desktop text-lg"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-sm text-gray-900 dark:text-white">Masuk Mengawas Ujian</h4>
                                <p class="text-xs text-gray-500">Pantau ujian siswa yang sedang berlangsung secara real-time.</p>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
        
    </DashboardLayout>
</template>
