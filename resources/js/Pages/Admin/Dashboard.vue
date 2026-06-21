<script setup>
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

defineProps({
    stats: Object,
    finance: Object,
    features: Object,
    kbm: Object,
    chart: Object,
    users: Array,
    activities: Array
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

        <!-- Middle Row (Features & Modules) -->
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
            <!-- KBM & Jurnal -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col justify-between group cursor-pointer hover:shadow-md transition-all">
                <div class="flex items-start justify-between mb-2">
                    <div class="w-10 h-10 rounded-xl bg-orange-50 dark:bg-orange-900/20 flex items-center justify-center text-orange-600 dark:text-orange-400 group-hover:scale-110 transition-transform">
                        <i class="fas fa-chalkboard"></i>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-bold px-2 py-0.5 rounded-full" :class="kbm.persentase < 50 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'">{{ kbm.persentase }}%</span>
                    </div>
                </div>
                <div>
                    <h3 class="text-gray-500 dark:text-gray-400 text-xs font-medium mb-1 uppercase tracking-wider">Persentase KBM</h3>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ kbm.jurnal_terisi }}<span class="text-sm font-normal text-gray-400">/{{ kbm.jadwal_hari_ini }}</span></p>
                </div>
            </div>

            <!-- E-Learning -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col justify-between group cursor-pointer hover:shadow-md transition-all">
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600 dark:text-blue-400 mb-2 group-hover:scale-110 transition-transform">
                    <i class="fas fa-laptop-code"></i>
                </div>
                <div>
                    <h3 class="text-gray-500 dark:text-gray-400 text-xs font-medium mb-1 uppercase tracking-wider">E-Learning & CBT</h3>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ features.ujian_aktif }} <span class="text-sm font-normal text-gray-400">Ujian Aktif</span></p>
                    <p class="text-xs text-gray-400 mt-1">{{ features.bank_soal }} Bank Soal</p>
                </div>
            </div>

            <!-- Kesiswaan & PPDB -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col justify-between group cursor-pointer hover:shadow-md transition-all">
                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center text-purple-600 dark:text-purple-400 mb-2 group-hover:scale-110 transition-transform">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div>
                    <h3 class="text-gray-500 dark:text-gray-400 text-xs font-medium mb-1 uppercase tracking-wider">PPDB & Siswa</h3>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ features.pendaftar_ppdb }} <span class="text-sm font-normal text-gray-400">Pendaftar</span></p>
                    <p class="text-xs text-gray-400 mt-1">{{ features.pelanggaran }} Kasus Pelanggaran</p>
                </div>
            </div>

            <!-- Perpustakaan -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col justify-between group cursor-pointer hover:shadow-md transition-all">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400 mb-2 group-hover:scale-110 transition-transform">
                    <i class="fas fa-book-reader"></i>
                </div>
                <div>
                    <h3 class="text-gray-500 dark:text-gray-400 text-xs font-medium mb-1 uppercase tracking-wider">E-Library</h3>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ features.buku }} <span class="text-sm font-normal text-gray-400">Buku Koleksi</span></p>
                </div>
            </div>

            <!-- Ekskul & PKL -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col justify-between group cursor-pointer hover:shadow-md transition-all">
                <div class="w-10 h-10 rounded-xl bg-pink-50 dark:bg-pink-900/20 flex items-center justify-center text-pink-600 dark:text-pink-400 mb-2 group-hover:scale-110 transition-transform">
                    <i class="fas fa-running"></i>
                </div>
                <div>
                    <h3 class="text-gray-500 dark:text-gray-400 text-xs font-medium mb-1 uppercase tracking-wider">Ekskul & PKL</h3>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ features.ekskul }} <span class="text-sm font-normal text-gray-400">Ekskul</span></p>
                    <p class="text-xs text-gray-400 mt-1">{{ features.pkl }} Kelompok PKL</p>
                </div>
            </div>

            <!-- Surat -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col justify-between group cursor-pointer hover:shadow-md transition-all">
                <div class="w-10 h-10 rounded-xl bg-yellow-50 dark:bg-yellow-900/20 flex items-center justify-center text-yellow-600 dark:text-yellow-400 mb-2 group-hover:scale-110 transition-transform">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
                <div>
                    <h3 class="text-gray-500 dark:text-gray-400 text-xs font-medium mb-1 uppercase tracking-wider">Surat Menyurat</h3>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ features.surat }} <span class="text-sm font-normal text-gray-400">Arsip</span></p>
                </div>
            </div>
        </div>

        <!-- Bottom Row -->
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
                    
                    <div v-for="(act, idx) in activities" :key="idx" class="flex items-center gap-4 mb-4">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center" :class="[act.bg, act.color]">
                            <i :class="act.icon"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-sm text-gray-900 dark:text-white">{{ act.title }}</h4>
                            <p class="text-xs text-gray-400">{{ act.desc }}</p>
                        </div>
                    </div>
                    
                    <div v-if="!activities.length" class="text-center py-4 text-sm text-gray-400 italic">
                        Belum ada aktivitas hari ini.
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
                    <div v-for="user in users" :key="user.name" class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-gray-200 overflow-hidden"><img :src="`https://ui-avatars.com/api/?name=${user.avatar_name}&background=random`" :alt="user.name"></div>
                        <div>
                            <h4 class="font-bold text-sm text-gray-900 dark:text-white line-clamp-1">{{ user.name }}</h4>
                            <p class="text-xs" :class="user.last_seen === 'Baru saja' ? 'text-primary-500 font-medium' : 'text-gray-400'">
                                {{ user.role }} • {{ user.last_seen }}
                            </p>
                        </div>
                    </div>
                    <div v-if="!users.length" class="text-center py-4 text-sm text-gray-400 italic">
                        Belum ada data login pengguna.
                    </div>
                </div>
            </div>
        </div>
        
    </DashboardLayout>
</template>
