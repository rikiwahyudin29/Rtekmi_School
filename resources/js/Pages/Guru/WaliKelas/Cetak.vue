<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    kelas: Object,
    siswa: Array,
});

const printCover = (siswa_id) => {
    window.open(`/cetak/rapor/${siswa_id}/cover`, '_blank');
};

const printPelengkap = (siswa_id) => {
    window.open(`/cetak/rapor/${siswa_id}/pelengkap`, '_blank');
};

const printRapor = (siswa_id) => {
    window.open(`/cetak/rapor/${siswa_id}/nilai`, '_blank');
};
</script>

<template>
    <Head title="Cetak Rapor Siswa" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-print text-primary-600"></i>
                        Cetak Rapor Siswa
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Cetak dokumen rapor (Cover & Halaman Nilai) sesuai standar eRapor SMK.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('guru.walikelas.index')" class="px-4 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors font-medium">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </Link>
                </div>
            </div>

            <!-- Tabel Siswa -->
            <div v-if="siswa && siswa.length > 0" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-900 dark:text-white">Daftar Siswa - {{ kelas?.nama_kelas || '-' }}</h3>
                    <button class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 text-sm font-bold flex items-center gap-2 transition-colors">
                        <i class="fas fa-file-pdf"></i> Cetak Semua Rapor Kelas
                    </button>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-300">
                            <tr>
                                <th scope="col" class="px-6 py-4 w-16">No</th>
                                <th scope="col" class="px-6 py-4">NIS / NISN</th>
                                <th scope="col" class="px-6 py-4">Nama Peserta Didik</th>
                                <th scope="col" class="px-6 py-4 w-64 text-center">Aksi Cetak</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(s, index) in siswa" :key="s.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4">{{ index + 1 }}</td>
                                <td class="px-6 py-4">{{ s.nis || '-' }} / {{ s.nisn }}</td>
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ s.nama_lengkap }}</td>
                                <td class="px-6 py-4 flex gap-2 justify-center">
                                    <button @click="printCover(s.id)" class="px-3 py-1.5 bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded hover:bg-gray-200 dark:hover:bg-gray-600 font-medium transition-colors text-xs flex items-center gap-1" title="Cetak Cover Depan">
                                        <i class="fas fa-book"></i> Cover
                                    </button>
                                    <button @click="printPelengkap(s.id)" class="px-3 py-1.5 bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 rounded hover:bg-indigo-200 dark:hover:bg-indigo-900/50 font-medium transition-colors text-xs flex items-center gap-1" title="Cetak Pelengkap Rapor">
                                        <i class="fas fa-file-alt"></i> Pelengkap
                                    </button>
                                    <button @click="printRapor(s.id)" class="px-3 py-1.5 bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400 rounded hover:bg-primary-200 dark:hover:bg-primary-900/50 font-medium transition-colors text-xs flex items-center gap-1" title="Cetak Halaman Nilai">
                                        <i class="fas fa-print"></i> Nilai
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div v-else class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-2xl p-6 text-center text-yellow-800 dark:text-yellow-400">
                <i class="fas fa-exclamation-circle text-3xl mb-3"></i>
                <p>Data siswa tidak ditemukan di kelas ini.</p>
            </div>
        </div>
    </DashboardLayout>
</template>
