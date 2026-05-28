<script setup>
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

defineProps({
    stats: Object,
    finance: Object,
    chart: Object,
});
</script>

<template>
    <Head title="Admin Dashboard" />

    <DashboardLayout>
        
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-1">Dashboard Administrator</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Ringkasan data akademik dan operasional sekolah.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button class="bg-primary-700 hover:bg-primary-800 text-white font-bold py-2.5 px-5 rounded-full text-sm shadow-sm transition-colors flex items-center gap-2">
                        <i class="fas fa-print"></i> Cetak Laporan
                    </button>
                </div>
            </div>
        </div>

        <!-- 4 Stat Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Card 1 -->
            <div class="bg-primary-700 rounded-3xl p-6 text-white shadow-sm relative overflow-hidden group cursor-pointer hover:shadow-md transition-shadow">
                <div class="absolute top-6 right-6 w-8 h-8 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm group-hover:bg-white/30 transition-colors">
                    <i class="fas fa-users text-white text-sm"></i>
                </div>
                <h3 class="text-primary-50 font-medium mb-2">Total Siswa Aktif</h3>
                <p class="text-5xl font-bold mb-4 tracking-tight">{{ stats.siswa }}</p>
                <div class="flex items-center gap-2 text-xs">
                    <span class="bg-primary-600/50 px-2 py-1 rounded text-primary-50 font-bold"><i class="fas fa-check mr-1"></i> Update</span>
                    <span class="text-primary-100/80">Tahun Ajaran Aktif</span>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group cursor-pointer hover:shadow-md transition-shadow">
                <div class="absolute top-6 right-6 w-8 h-8 border border-gray-200 dark:border-gray-600 rounded-full flex items-center justify-center group-hover:bg-gray-50 dark:group-hover:bg-gray-700 transition-colors">
                    <i class="fas fa-chalkboard-teacher text-gray-400 text-sm"></i>
                </div>
                <h3 class="text-gray-500 dark:text-gray-400 font-medium mb-2">Total Guru</h3>
                <p class="text-5xl font-bold mb-4 tracking-tight text-gray-900 dark:text-white">{{ stats.guru }}</p>
                <div class="flex items-center gap-2 text-xs">
                    <span class="bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 px-2 py-1 rounded font-bold border border-primary-100 dark:border-primary-800"><i class="fas fa-user-tie mr-1"></i> {{ stats.wali_kelas }}</span>
                    <span class="text-gray-400">Bertugas sebagai Wali Kelas</span>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group cursor-pointer hover:shadow-md transition-shadow">
                <div class="absolute top-6 right-6 w-8 h-8 border border-gray-200 dark:border-gray-600 rounded-full flex items-center justify-center group-hover:bg-gray-50 dark:group-hover:bg-gray-700 transition-colors">
                    <i class="fas fa-building text-gray-400 text-sm"></i>
                </div>
                <h3 class="text-gray-500 dark:text-gray-400 font-medium mb-2">Rombongan Belajar</h3>
                <p class="text-5xl font-bold mb-4 tracking-tight text-gray-900 dark:text-white">{{ stats.kelas }}</p>
                <div class="flex items-center gap-2 text-xs">
                    <span class="bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 px-2 py-1 rounded font-bold border border-blue-100 dark:border-blue-800"><i class="fas fa-book mr-1"></i> {{ stats.mapel }}</span>
                    <span class="text-gray-400">Mata Pelajaran</span>
                </div>
            </div>

            <!-- Card 4 -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group cursor-pointer hover:shadow-md transition-shadow">
                <div class="absolute top-6 right-6 w-8 h-8 border border-gray-200 dark:border-gray-600 rounded-full flex items-center justify-center group-hover:bg-gray-50 dark:group-hover:bg-gray-700 transition-colors">
                    <i class="fas fa-wallet text-red-400 text-sm"></i>
                </div>
                <h3 class="text-gray-500 dark:text-gray-400 font-medium mb-2">Tunggakan Siswa</h3>
                <p class="text-3xl font-bold mb-4 tracking-tight text-red-600 dark:text-red-400 mt-2">Rp {{ (finance.total_tunggakan / 1000000).toFixed(1) }} Jt</p>
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Uang Masuk: Rp {{ (finance.total_bayar / 1000000).toFixed(1) }} Jt</span>
                </div>
            </div>
        </div>

        <!-- Middle Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Analytics Chart Mockup -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 lg:col-span-1">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Grafik Kehadiran Mingguan</h3>
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
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Pemberitahuan Sistem</h3>
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600"><i class="fas fa-cloud-upload-alt"></i></div>
                        <div>
                            <h4 class="font-bold text-sm text-gray-900 dark:text-white">Backup Database Sukses</h4>
                            <p class="text-xs text-gray-400">Hari ini, 02:00 AM</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600"><i class="fas fa-exclamation-triangle"></i></div>
                        <div>
                            <h4 class="font-bold text-sm text-gray-900 dark:text-white">15 Siswa Belum Bayar SPP</h4>
                            <p class="text-xs text-gray-400">Segera kirimkan tagihan via WA</p>
                        </div>
                    </div>
                </div>
                <button class="w-full bg-primary-700 hover:bg-primary-800 text-white font-bold py-3 px-4 rounded-xl shadow-md transition-colors mt-6 flex justify-center items-center gap-2">
                    <i class="fab fa-whatsapp"></i> Broadcast Pesan
                </button>
            </div>

            <!-- User List -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 lg:col-span-1">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Pengguna Aktif</h3>
                    <button class="text-xs font-bold border border-gray-200 dark:border-gray-600 rounded-full px-3 py-1 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-gray-600 dark:text-gray-300">Lihat Semua</button>
                </div>
                <div class="space-y-4">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-gray-200 overflow-hidden"><img src="https://ui-avatars.com/api/?name=Admin+Sistem&background=random" alt="Admin"></div>
                        <div>
                            <h4 class="font-bold text-sm text-gray-900 dark:text-white">Admin Sistem</h4>
                            <p class="text-xs text-primary-500 font-medium">Sedang Online</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-gray-200 overflow-hidden"><img src="https://ui-avatars.com/api/?name=Budi+Santoso&background=random" alt="Guru"></div>
                        <div>
                            <h4 class="font-bold text-sm text-gray-900 dark:text-white">Budi Santoso</h4>
                            <p class="text-xs text-gray-400">Terakhir dilihat: 5 mnt lalu</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-gray-200 overflow-hidden"><img src="https://ui-avatars.com/api/?name=Siti+Aminah&background=random" alt="Siswa"></div>
                        <div>
                            <h4 class="font-bold text-sm text-gray-900 dark:text-white">Siti Aminah</h4>
                            <p class="text-xs text-gray-400">Terakhir dilihat: 1 jam lalu</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </DashboardLayout>
</template>
