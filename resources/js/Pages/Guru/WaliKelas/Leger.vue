<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    kelas: Object,
    siswa: Array,
});

const exportLegerExcel = () => {
    window.open(`/cetak/leger-excel/${props.kelas.id}`, '_blank');
};

const printLegerPdf = () => {
    window.open(`/cetak/leger/${props.kelas.id}`, '_blank');
};
</script>

<template>
    <Head title="Leger Kelas" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-list-ol text-emerald-500"></i>
                        Leger Nilai Kelas
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Lihat dan cetak Leger nilai beserta peringkat siswa kelas perwalian Anda.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('guru.walikelas.index')" class="px-4 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors font-medium">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </Link>
                </div>
            </div>

            <!-- Tabel Siswa & Aksi -->
            <div v-if="siswa && siswa.length > 0" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <h3 class="font-bold text-gray-900 dark:text-white">Aksi Cetak Leger - {{ kelas?.nama_kelas || '-' }}</h3>
                    <div class="flex flex-wrap gap-2">
                        <button @click="exportLegerExcel" class="px-4 py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-bold flex items-center gap-2 transition-colors shadow-sm shadow-emerald-500/30">
                            <i class="fas fa-file-excel"></i> Export Leger Excel
                        </button>
                        <button @click="printLegerPdf" class="px-4 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 font-bold flex items-center gap-2 transition-colors shadow-sm shadow-red-500/30">
                            <i class="fas fa-file-pdf"></i> Cetak Leger PDF
                        </button>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-300 border-b dark:border-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-4 w-16">No</th>
                                <th scope="col" class="px-6 py-4">NISN</th>
                                <th scope="col" class="px-6 py-4">Nama Peserta Didik</th>
                                <th scope="col" class="px-6 py-4">L/P</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(s, index) in siswa" :key="s.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4">{{ index + 1 }}</td>
                                <td class="px-6 py-4">{{ s.nisn }}</td>
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ s.nama_lengkap }}</td>
                                <td class="px-6 py-4">{{ s.jenis_kelamin === 'P' || s.jk === 'P' ? 'P' : 'L' }}</td>
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
