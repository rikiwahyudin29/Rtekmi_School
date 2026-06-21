<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    stats: Object,
    chart: Object,
});

const auth = usePage().props.auth;

const maxChartValue = computed(() => {
    return Math.max(...props.chart.presensi, 1);
});
</script>

<template>
    <Head title="Guru Dashboard" />

    <DashboardLayout>
        
        <!-- Header & Alerts -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-1">Dashboard Guru</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Selamat datang kembali, {{ auth.user.nama_lengkap || auth.user.username }}!</p>
                </div>
                
                <div class="flex gap-3">
                    <div v-if="stats.tambahan.piket" class="flex items-center gap-2 bg-yellow-50 text-yellow-700 px-4 py-2 rounded-xl text-sm font-bold border border-yellow-200">
                        <i class="fas fa-user-shield"></i> Anda Piket Hari Ini
                    </div>
                    <Link v-if="stats.tambahan.disposisi > 0" :href="route('guru.disposisi.index')" class="flex items-center gap-2 bg-red-50 hover:bg-red-100 text-red-600 px-4 py-2 rounded-xl text-sm font-bold transition-colors border border-red-200">
                        <i class="fas fa-envelope-open-text"></i> {{ stats.tambahan.disposisi }} Disposisi Baru
                    </Link>
                </div>
            </div>
        </div>

        <!-- 1. KBM & Akademik -->
        <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
            <i class="fas fa-chalkboard-teacher text-primary-500"></i> Akademik & KBM
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Jadwal Hari Ini</p>
                    <h3 class="text-3xl font-black text-gray-900 dark:text-white mt-1">{{ stats.kbm.jadwal_hari_ini }} <span class="text-base font-medium text-gray-400">Kelas</span></h3>
                </div>
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center text-xl">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Jurnal Terisi</p>
                    <h3 class="text-3xl font-black text-gray-900 dark:text-white mt-1">{{ stats.kbm.jurnal_terisi }} <span class="text-base font-medium text-gray-400">Sesi</span></h3>
                </div>
                <div class="w-12 h-12 bg-green-50 text-green-600 rounded-full flex items-center justify-center text-xl">
                    <i class="fas fa-book"></i>
                </div>
            </div>
            <div class="bg-gradient-to-br from-primary-600 to-primary-800 rounded-2xl p-5 shadow-sm text-white flex flex-col justify-center">
                <div class="flex justify-between items-center mb-2">
                    <p class="text-primary-100 font-medium text-sm">Persentase KBM Hari Ini</p>
                    <i class="fas fa-chart-pie text-primary-300"></i>
                </div>
                <div class="flex items-end gap-2">
                    <h3 class="text-4xl font-black">{{ stats.kbm.persentase }}%</h3>
                </div>
                <div class="w-full bg-primary-900/50 rounded-full h-1.5 mt-3">
                    <div class="bg-white h-1.5 rounded-full" :style="`width: ${stats.kbm.persentase}%`"></div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- 2. E-Learning & E-Rapor -->
            <div class="space-y-8">
                <div>
                    <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                        <i class="fas fa-laptop-house text-orange-500"></i> E-Learning
                    </h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700">
                            <i class="fas fa-tasks text-orange-500 mb-2"></i>
                            <h4 class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.elearning.tugas }}</h4>
                            <p class="text-xs text-gray-500">Tugas Diberikan</p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700">
                            <i class="fas fa-file-pdf text-orange-500 mb-2"></i>
                            <h4 class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.elearning.materi }}</h4>
                            <p class="text-xs text-gray-500">Materi Tersedia</p>
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                        <i class="fas fa-star text-yellow-500"></i> E-Rapor
                    </h2>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700 text-center">
                            <h4 class="text-xl font-bold text-gray-900 dark:text-white">{{ stats.erapor.tp }}</h4>
                            <p class="text-xs text-gray-500 mt-1">Tujuan Pembelajaran</p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700 text-center">
                            <h4 class="text-xl font-bold text-gray-900 dark:text-white">{{ stats.erapor.formatif }}</h4>
                            <p class="text-xs text-gray-500 mt-1">Nilai Formatif</p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700 text-center">
                            <h4 class="text-xl font-bold text-gray-900 dark:text-white">{{ stats.erapor.sumatif }}</h4>
                            <p class="text-xs text-gray-500 mt-1">Nilai Sumatif</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. CBT & Chart -->
            <div class="space-y-8">
                <div>
                    <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                        <i class="fas fa-desktop text-purple-500"></i> Computer Based Test (CBT)
                    </h2>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700">
                            <h4 class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.cbt.bank_soal }}</h4>
                            <p class="text-xs text-gray-500 mt-1">Bank Soal</p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700">
                            <h4 class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.cbt.draft_ujian }}</h4>
                            <p class="text-xs text-gray-500 mt-1">Draft Ujian</p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700">
                            <h4 class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.cbt.jadwal_ujian }}</h4>
                            <p class="text-xs text-gray-500 mt-1">Jadwal Ujian</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-6">Tren Kehadiran Siswa Kelas Anda (7 Hari)</h3>
                    <div class="flex items-end justify-between h-32 gap-2">
                        <div v-for="(val, index) in chart.presensi" :key="index" class="w-full relative rounded-t-lg flex flex-col justify-end transition-all hover:opacity-80" :class="val >= 90 ? 'bg-primary-500' : (val >= 50 ? 'bg-primary-300' : 'bg-gray-200 dark:bg-gray-700')" :style="`height: ${val > 0 ? (val/maxChartValue)*100 : 10}%`">
                            <div class="opacity-0 hover:opacity-100 absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs font-bold px-2 py-1 rounded shadow z-10 transition-opacity">{{ val }}%</div>
                        </div>
                    </div>
                    <div class="flex justify-between text-[10px] font-bold text-gray-400 mt-3 px-2 uppercase">
                        <span v-for="label in chart.labels" :key="label">{{ label.split(' ')[0] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Quick Actions -->
        <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
            <i class="fas fa-bolt text-yellow-400"></i> Jalan Pintas
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <Link :href="route('guru.elearning.jurnal.index')" class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 hover:shadow-md hover:border-primary-300 transition-all text-center group">
                <div class="w-12 h-12 mx-auto bg-green-50 text-green-600 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                    <i class="fas fa-pen"></i>
                </div>
                <h4 class="text-sm font-bold text-gray-900 dark:text-white">Isi Jurnal KBM</h4>
            </Link>
            <Link :href="route('guru.elearning.kelas-virtual.index')" class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 hover:shadow-md hover:border-blue-300 transition-all text-center group">
                <div class="w-12 h-12 mx-auto bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                    <i class="fas fa-video"></i>
                </div>
                <h4 class="text-sm font-bold text-gray-900 dark:text-white">Kelas Virtual</h4>
            </Link>
            <Link :href="route('admin.cbt.bank-soal.index')" class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 hover:shadow-md hover:border-purple-300 transition-all text-center group">
                <div class="w-12 h-12 mx-auto bg-purple-50 text-purple-600 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                    <i class="fas fa-database"></i>
                </div>
                <h4 class="text-sm font-bold text-gray-900 dark:text-white">Buat Bank Soal</h4>
            </Link>
            <Link :href="route('guru.penilaian.tp')" class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 hover:shadow-md hover:border-yellow-300 transition-all text-center group">
                <div class="w-12 h-12 mx-auto bg-yellow-50 text-yellow-600 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                    <i class="fas fa-star"></i>
                </div>
                <h4 class="text-sm font-bold text-gray-900 dark:text-white">Input E-Rapor</h4>
            </Link>
        </div>

    </DashboardLayout>
</template>
