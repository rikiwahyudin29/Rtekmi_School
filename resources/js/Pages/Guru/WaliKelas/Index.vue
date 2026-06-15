<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    kelas: Object,
    status_penilaian: Array
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
                <!-- Menu Data Siswa -->
                <Link :href="route('guru.walikelas.data_siswa')" class="group relative bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="absolute right-0 top-0 w-24 h-24 bg-indigo-500/10 dark:bg-indigo-500/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                    <div class="w-14 h-14 bg-indigo-50 dark:bg-gray-700 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-user-edit text-2xl text-indigo-500"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Data Siswa</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Edit biodata dan identitas siswa di kelas perwalian Anda.</p>
                    <div class="mt-4 flex items-center text-indigo-600 dark:text-indigo-400 font-medium text-sm group-hover:translate-x-2 transition-transform">
                        Kelola Data <i class="fas fa-arrow-right ml-2"></i>
                    </div>
                </Link>

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

                <!-- Menu Data Ekskul -->
                <Link :href="route('guru.walikelas.ekskul')" class="group relative bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="absolute right-0 top-0 w-24 h-24 bg-pink-500/10 dark:bg-pink-500/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                    <div class="w-14 h-14 bg-pink-50 dark:bg-gray-700 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-futbol text-2xl text-pink-500"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Data Ekskul</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Input nilai kegiatan ekstrakurikuler siswa.</p>
                    <div class="mt-4 flex items-center text-pink-600 dark:text-pink-400 font-medium text-sm group-hover:translate-x-2 transition-transform">
                        Kelola Ekskul <i class="fas fa-arrow-right ml-2"></i>
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

            <!-- Status Penilaian Mata Pelajaran -->
            <div v-if="kelas" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden mt-8">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 flex justify-between items-center">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-tasks text-blue-500"></i> Status Penilaian Guru Mata Pelajaran
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-white dark:bg-gray-800 dark:text-gray-300">
                            <tr>
                                <th class="px-6 py-4 border-b dark:border-gray-700">No</th>
                                <th class="px-6 py-4 border-b dark:border-gray-700">Mata Pelajaran</th>
                                <th class="px-6 py-4 border-b dark:border-gray-700">Guru Pengampu</th>
                                <th class="px-6 py-4 border-b dark:border-gray-700">Status Penyelesaian Nilai Akhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(status, index) in status_penilaian" :key="index" class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4">{{ index + 1 }}</td>
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ status.mapel }}</td>
                                <td class="px-6 py-4">{{ status.guru }}</td>
                                <td class="px-6 py-4">
                                    <span v-if="status.tuntas" class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold flex items-center w-max gap-1">
                                        <i class="fas fa-check-circle"></i> {{ status.status }}
                                    </span>
                                    <span v-else-if="status.status === 'Belum Ada Siswa'" class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-bold flex items-center w-max gap-1">
                                        <i class="fas fa-info-circle"></i> {{ status.status }}
                                    </span>
                                    <span v-else class="px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-xs font-bold flex items-center w-max gap-1">
                                        <i class="fas fa-clock"></i> {{ status.status }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!status_penilaian || status_penilaian.length === 0">
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">Belum ada mata pelajaran yang dijadwalkan untuk kelas ini.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
