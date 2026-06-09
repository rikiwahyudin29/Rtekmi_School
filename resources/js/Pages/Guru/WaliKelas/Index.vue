<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    kelas: Object,
});
</script>

<template>
    <Head title="Wali Kelas eRapor" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-users text-teal-500"></i>
                        Dashboard Wali Kelas
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Kelola data eRapor khusus untuk kelas perwalian Anda ({{ kelas?.nama_kelas || 'Belum ada kelas' }}).
                    </p>
                </div>
            </div>

            <!-- Flash Messages -->
            <div v-if="$page.props.flash.success" class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
                <span class="font-medium">Sukses!</span> {{ $page.props.flash.success }}
            </div>

            <div v-if="!kelas" class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-2xl p-6 text-center text-yellow-800 dark:text-yellow-400">
                <i class="fas fa-exclamation-triangle text-3xl mb-3"></i>
                <p>Anda belum ditetapkan sebagai Wali Kelas untuk kelas manapun di sistem.</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Menu Kehadiran -->
                <Link :href="route('guru.walikelas.kehadiran')" class="group relative bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="absolute right-0 top-0 w-24 h-24 bg-blue-500/10 dark:bg-blue-500/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                    <div class="w-14 h-14 bg-blue-50 dark:bg-gray-700 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-user-clock text-2xl text-blue-500"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Input Kehadiran</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Rekap kehadiran siswa (Sakit, Izin, Alpha) selama 1 semester.</p>
                    <div class="mt-4 flex items-center text-blue-600 dark:text-blue-400 font-medium text-sm group-hover:translate-x-2 transition-transform">
                        Kelola Kehadiran <i class="fas fa-arrow-right ml-2"></i>
                    </div>
                </Link>

                <!-- Menu PKL -->
                <Link :href="route('guru.walikelas.pkl')" class="group relative bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="absolute right-0 top-0 w-24 h-24 bg-orange-500/10 dark:bg-orange-500/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                    <div class="w-14 h-14 bg-orange-50 dark:bg-gray-700 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-briefcase text-2xl text-orange-500"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Data PKL</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Input nilai dan keterangan Praktik Kerja Lapangan (PKL) siswa.</p>
                    <div class="mt-4 flex items-center text-orange-600 dark:text-orange-400 font-medium text-sm group-hover:translate-x-2 transition-transform">
                        Kelola PKL <i class="fas fa-arrow-right ml-2"></i>
                    </div>
                </Link>

                <!-- Menu Catatan Wali -->
                <Link :href="route('guru.walikelas.catatan')" class="group relative bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="absolute right-0 top-0 w-24 h-24 bg-emerald-500/10 dark:bg-emerald-500/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                    <div class="w-14 h-14 bg-emerald-50 dark:bg-gray-700 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-comment-dots text-2xl text-emerald-500"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Catatan Wali</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Input catatan motivasi dan perkembangan siswa untuk Rapor.</p>
                    <div class="mt-4 flex items-center text-emerald-600 dark:text-emerald-400 font-medium text-sm group-hover:translate-x-2 transition-transform">
                        Kelola Catatan <i class="fas fa-arrow-right ml-2"></i>
                    </div>
                </Link>

                <!-- Menu Status Kenaikan -->
                <Link :href="route('guru.walikelas.kenaikan')" class="group relative bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="absolute right-0 top-0 w-24 h-24 bg-purple-500/10 dark:bg-purple-500/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                    <div class="w-14 h-14 bg-purple-50 dark:bg-gray-700 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-level-up-alt text-2xl text-purple-500"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Status Kenaikan</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Input status kenaikan kelas atau kelulusan siswa.</p>
                    <div class="mt-4 flex items-center text-purple-600 dark:text-purple-400 font-medium text-sm group-hover:translate-x-2 transition-transform">
                        Kelola Kenaikan <i class="fas fa-arrow-right ml-2"></i>
                    </div>
                </Link>

                <!-- Menu Cetak Rapor -->
                <Link :href="route('guru.walikelas.cetak_rapor')" class="group relative bg-gradient-to-br from-primary-600 to-primary-800 rounded-3xl shadow-sm border border-primary-500 p-6 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="absolute right-0 top-0 w-32 h-32 bg-white/10 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                    <div class="w-14 h-14 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center mb-6 text-white">
                        <i class="fas fa-print text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Cetak eRapor</h3>
                    <p class="text-sm text-primary-100">Review hasil penilaian dan cetak Rapor Tengah/Akhir Semester.</p>
                    <div class="mt-4 flex items-center text-white font-bold text-sm group-hover:translate-x-2 transition-transform">
                        Cetak Rapor <i class="fas fa-arrow-right ml-2"></i>
                    </div>
                </Link>
            </div>
        </div>
    </DashboardLayout>
</template>
