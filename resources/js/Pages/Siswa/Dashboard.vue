<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    siswa: Object,
    stats: Object,
    keuangan: Object,
    kedisiplinan: Object,
    kehadiran: Object,
    chart: Object,
});

const auth = usePage().props.auth;

const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(angka);
};
</script>

<template>
    <Head title="Siswa Dashboard" />

    <DashboardLayout>
        
        <!-- Premium Profile Card Section -->
        <div class="mb-8">
            <div class="bg-gradient-to-r from-primary-900 to-primary-700 rounded-3xl p-8 text-white shadow-xl relative overflow-hidden">
                <!-- Decorative Elements -->
                <div class="absolute top-0 right-0 -mr-20 -mt-20 w-64 h-64 rounded-full bg-white opacity-5 blur-2xl"></div>
                <div class="absolute bottom-0 right-40 w-32 h-32 rounded-full bg-primary-400 opacity-20 blur-xl"></div>
                
                <div class="relative z-10 flex flex-col md:flex-row items-center md:items-start gap-6">
                    <div class="shrink-0">
                        <div class="w-24 h-24 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 p-1 shadow-lg">
                            <img v-if="siswa?.foto" :src="`/uploads/siswa/${siswa.foto}`" alt="Foto Profil" class="w-full h-full rounded-xl object-cover">
                            <div v-else class="w-full h-full rounded-xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-3xl font-bold text-white shadow-inner">
                                {{ (auth.user.nama_lengkap || auth.user.username).charAt(0).toUpperCase() }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center md:text-left flex-1">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-500/30 text-emerald-100 text-xs font-semibold mb-3">
                            <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                            Siswa Aktif
                        </div>
                        <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-2 text-white">
                            {{ auth.user.nama_lengkap || auth.user.username }}
                        </h1>
                        
                        <div class="flex flex-wrap items-center justify-center md:justify-start gap-y-2 gap-x-6 text-primary-100 text-sm font-medium">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-id-card opacity-70"></i>
                                <span>NIS: {{ siswa?.nis || '-' }} / NISN: {{ siswa?.nisn || '-' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-chalkboard-teacher opacity-70"></i>
                                <span>Kelas: {{ siswa?.kelas?.nama_kelas || '-' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-graduation-cap opacity-70"></i>
                                <span>Jurusan: {{ siswa?.jurusan?.nama_jurusan || '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5 Feature Grid Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            
            <!-- Saldo Tabungan Card -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group hover:shadow-lg transition-all duration-300">
                <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-50 dark:bg-emerald-900/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 rounded-2xl flex items-center justify-center text-xl mb-4 relative z-10">
                    <i class="fas fa-wallet"></i>
                </div>
                <h3 class="text-gray-500 dark:text-gray-400 font-medium text-sm mb-1">Saldo Tabungan</h3>
                <p class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-4">{{ formatRupiah(keuangan?.saldo_tabungan || 0) }}</p>
                <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center text-xs">
                    <span class="text-gray-500">Bank Mini Sekolah</span>
                </div>
            </div>

            <!-- Tunggakan Card -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group hover:shadow-lg transition-all duration-300">
                <div class="absolute top-0 right-0 w-24 h-24 bg-red-50 dark:bg-red-900/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <div class="w-12 h-12 bg-red-100 dark:bg-red-900/50 text-red-600 rounded-2xl flex items-center justify-center text-xl mb-4 relative z-10">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <h3 class="text-gray-500 dark:text-gray-400 font-medium text-sm mb-1">Total Tunggakan</h3>
                <p class="text-2xl font-extrabold text-red-600 tracking-tight mb-4">{{ formatRupiah(keuangan?.tagihan_aktif || 0) }}</p>
                <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center text-xs">
                    <span class="text-gray-500">Harap Segera Dilunasi</span>
                </div>
            </div>

            <!-- E-Learning Card -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group hover:shadow-lg transition-all duration-300">
                <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 dark:bg-blue-900/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/50 text-blue-600 rounded-2xl flex items-center justify-center text-xl mb-4 relative z-10">
                    <i class="fas fa-laptop-code"></i>
                </div>
                <h3 class="text-gray-500 dark:text-gray-400 font-medium text-sm mb-1">Tugas Belum Selesai</h3>
                <p class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-4">{{ stats?.tugas_belum || 0 }}</p>
                <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center text-xs">
                    <span class="text-gray-500">Ujian Aktif Saat Ini</span>
                    <span class="font-bold" :class="stats?.ujian_aktif > 0 ? 'text-orange-500' : 'text-gray-400'">{{ stats?.ujian_aktif || 0 }} Ujian</span>
                </div>
            </div>

            <!-- Kedisiplinan Card -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group hover:shadow-lg transition-all duration-300">
                <div class="absolute top-0 right-0 w-24 h-24 bg-rose-50 dark:bg-rose-900/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <div class="w-12 h-12 bg-rose-100 dark:bg-rose-900/50 text-rose-600 rounded-2xl flex items-center justify-center text-xl mb-4 relative z-10">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 class="text-gray-500 dark:text-gray-400 font-medium text-sm mb-1">Sisa Poin Disiplin</h3>
                <p class="text-3xl font-extrabold tracking-tight mb-4" :class="(kedisiplinan?.sisa_poin ?? 100) < 50 ? 'text-red-600' : ((kedisiplinan?.sisa_poin ?? 100) < 100 ? 'text-orange-500' : 'text-gray-900 dark:text-white')">
                    {{ kedisiplinan?.sisa_poin ?? 100 }}
                </p>
                <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center text-xs">
                    <span class="text-gray-500">Status</span>
                    <span class="font-bold" :class="(kedisiplinan?.status_sp === 'Aman') ? 'text-emerald-500' : 'text-red-500'">
                        <i class="fas" :class="(kedisiplinan?.status_sp === 'Aman') ? 'fa-check-circle' : 'fa-exclamation-triangle'"></i> {{ kedisiplinan?.status_sp || 'Aman' }}
                    </span>
                </div>
            </div>

            <!-- Kehadiran Card -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group hover:shadow-lg transition-all duration-300">
                <div class="absolute top-0 right-0 w-24 h-24 bg-purple-50 dark:bg-purple-900/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/50 text-purple-600 rounded-2xl flex items-center justify-center text-xl mb-4 relative z-10">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3 class="text-gray-500 dark:text-gray-400 font-medium text-sm mb-1">Kehadiran Bulan Ini</h3>
                <p class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-4">{{ kehadiran?.hadir || 0 }} <span class="text-lg text-gray-400 font-normal">Hari</span></p>
                <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center text-xs">
                    <span class="text-gray-500">Total Alpha/Bolos</span>
                    <span class="font-bold" :class="(kehadiran?.alpha || 0) > 0 ? 'text-red-500' : 'text-gray-400'">{{ kehadiran?.alpha || 0 }} Hari</span>
                </div>
            </div>

        </div>

        <!-- Middle Row: Chart & Announcements -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Analytics Chart Mockup -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col justify-between">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Grafik Kehadiran (7 Hari Terakhir)</h3>
                
                <div class="flex items-end justify-between h-40 gap-2 mb-4">
                    <div v-for="(val, index) in chart?.kehadiran || []" :key="index" class="w-full relative rounded-t-xl flex flex-col justify-end transition-all duration-500 hover:opacity-80" :class="val === 100 ? 'bg-primary-500' : (val === 50 ? 'bg-yellow-400' : 'bg-gray-100 dark:bg-gray-700')" :style="`height: ${val > 0 ? val : 15}%`">
                        <!-- Tooltip on hover (optional enhancement) -->
                        <div v-if="val === 100" class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-[10px] font-bold px-2 py-1 rounded shadow-lg opacity-0 hover:opacity-100 transition-opacity">Hadir</div>
                        <div v-else-if="val === 50" class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-[10px] font-bold px-2 py-1 rounded shadow-lg opacity-0 hover:opacity-100 transition-opacity">Izin/Sakit</div>
                    </div>
                </div>
                
                <div class="flex justify-between text-xs font-bold text-gray-400 px-2">
                    <span v-for="label in chart?.labels || []" :key="label" class="text-center w-full truncate">{{ label.split(' ')[0] }}</span>
                </div>
            </div>

            <!-- Quick Action / Info -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Pengumuman Terbaru</h3>
                    
                    <div class="space-y-4">
                        <div class="flex gap-4 p-4 rounded-2xl bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-100 dark:border-blue-800/30 group hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 rounded-xl bg-blue-500 text-white flex items-center justify-center shrink-0 shadow-sm group-hover:scale-105 transition-transform">
                                <i class="fas fa-bullhorn text-lg"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-sm text-gray-900 dark:text-white">Ujian Akhir Semester</h4>
                                <p class="text-xs text-gray-500 mt-1 leading-relaxed">Persiapkan diri dengan baik, ujian akan dilaksanakan serentak mulai minggu depan.</p>
                            </div>
                        </div>
                        
                        <div class="flex gap-4 p-4 rounded-2xl bg-gray-50 dark:bg-gray-700/50 border border-gray-100 dark:border-gray-600 group hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 rounded-xl bg-white dark:bg-gray-800 text-emerald-500 border border-gray-200 dark:border-gray-600 flex items-center justify-center shrink-0 shadow-sm group-hover:scale-105 transition-transform">
                                <i class="fas fa-book text-lg"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-sm text-gray-900 dark:text-white">Pengembalian Buku Perpus</h4>
                                <p class="text-xs text-gray-500 mt-1 leading-relaxed">Mohon segera kembalikan buku perpustakaan sebelum tanggal 30 untuk menghindari denda.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </DashboardLayout>
</template>
