<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    rekap: Array,
    tahun_aktif: Object,
});

</script>

<template>
    <Head title="Rekap Jam Mengajar" />

    <DashboardLayout>
        <div class="flex flex-col h-full">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Sticky Header -->
                <div class="sticky top-0 z-20 bg-[#f4f6f8] dark:bg-gray-900 pt-6 pb-3 px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                        <div>
                            <div class="flex items-center gap-3">
                                <Link href="/admin/kurikulum/jadwal-pelajaran" class="w-8 h-8 rounded-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-500 hover:text-primary-600 transition-colors">
                                    <i class="fas fa-arrow-left"></i>
                                </Link>
                                <h2 class="font-bold text-2xl text-gray-900 dark:text-white tracking-tight">Rekap Jam Mengajar</h2>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-11">
                                Berdasarkan Jadwal Pelajaran Aktif
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <a href="/admin/kurikulum/jadwal-pelajaran/cetak-rekap" target="_blank" class="bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400 font-semibold py-2.5 px-4 rounded-xl text-sm transition-all flex items-center gap-2 border border-blue-200 dark:border-blue-800">
                                <i class="fas fa-print"></i> Cetak PDF Rekap
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Scrollable Content -->
                <div class="px-4 sm:px-6 lg:px-8 pb-8 mt-2">
                    
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-400 p-4 mb-6 rounded-2xl shadow-sm flex items-start gap-3">
                        <i class="fas fa-info-circle mt-1"></i>
                        <div>
                            <p class="font-bold text-sm mb-1">Informasi Perhitungan</p>
                            <p class="text-xs">
                                Jam pelajaran dihitung berdasarkan selisih waktu mulai dan waktu selesai dari setiap jadwal pelajaran guru.
                                Kami juga menyediakan estimasi (pembulatan 1 angka di belakang koma) jika 1 JP = 40 Menit dan 1 JP = 45 Menit untuk membantu perhitungan honor.
                            </p>
                        </div>
                    </div>

                    <!-- Table Card -->
                    <div class="bg-white dark:bg-gray-800 shadow-xl shadow-gray-200/50 dark:shadow-none rounded-3xl border border-gray-100 dark:border-gray-700 mb-6 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                                        <th class="px-6 py-5 font-bold">No</th>
                                        <th class="px-6 py-5 font-bold">Nama Guru</th>
                                        <th class="px-6 py-5 font-bold">Kelas Diajar</th>
                                        <th class="px-6 py-5 font-bold text-center">Durasi Asli</th>
                                        <th class="px-6 py-5 font-bold text-center">Estimasi (40 Menit)</th>
                                        <th class="px-6 py-5 font-bold text-center">Estimasi (45 Menit)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <tr v-for="(item, index) in rekap" :key="item.id_guru" class="hover:bg-gray-50/80 dark:hover:bg-gray-700/50 transition-colors">
                                        <td class="px-6 py-4 w-12 text-center text-gray-500">
                                            {{ index + 1 }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-gray-900 dark:text-white">{{ item.nama }}</div>
                                            <div class="text-xs text-gray-500">NIP: {{ item.nip || '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-wrap gap-1">
                                                <span v-for="(k, i) in item.kelas_ajar" :key="i" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
                                                    {{ k }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="font-bold text-primary-600 dark:text-primary-400">{{ item.jam_asli }}</div>
                                            <div class="text-xs text-gray-500">{{ item.total_menit }} Menit</div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="font-bold text-green-600 dark:text-green-400">{{ item.total_jp_40 }} JP</div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="font-bold text-purple-600 dark:text-purple-400">{{ item.total_jp_45 }} JP</div>
                                        </td>
                                    </tr>
                                    <tr v-if="rekap.length === 0">
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                            <i class="fas fa-box-open text-4xl mb-3 text-gray-300 dark:text-gray-600"></i>
                                            <p class="text-lg font-medium">Belum Ada Rekap</p>
                                            <p class="text-sm mt-1">Belum ada data jadwal pelajaran yang aktif.</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
