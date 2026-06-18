<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    kelas: Object,
    siswa: Array,
    mapels: Array,
    peringkat_data: Object,
    rapor_data: Object,
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
                    <h3 class="font-bold text-gray-900 dark:text-white">Daftar Nilai Leger - {{ kelas?.nama_kelas || '-' }}</h3>
                    <div class="flex flex-wrap gap-2">
                        <button @click="exportLegerExcel" class="px-4 py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-bold flex items-center gap-2 transition-colors shadow-sm shadow-emerald-500/30">
                            <i class="fas fa-file-excel"></i> Export Leger Excel
                        </button>
                        <button @click="printLegerPdf" class="px-4 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 font-bold flex items-center gap-2 transition-colors shadow-sm shadow-red-500/30">
                            <i class="fas fa-file-pdf"></i> Cetak Leger PDF
                        </button>
                    </div>
                </div>
                
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-xs text-left text-gray-500 dark:text-gray-400 whitespace-nowrap">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-300 border-b dark:border-gray-700">
                            <tr>
                                <th scope="col" class="px-4 py-3 sticky left-0 bg-gray-100 dark:bg-gray-700 z-10 w-12 text-center">No</th>
                                <th scope="col" class="px-4 py-3 sticky left-[3rem] bg-gray-100 dark:bg-gray-700 z-10 min-w-[200px]">Nama Peserta Didik</th>
                                <th scope="col" class="px-4 py-3 text-center text-primary-600 dark:text-primary-400 font-extrabold w-16">Rank</th>
                                <th scope="col" class="px-4 py-3 text-center" v-for="mapel in mapels" :key="mapel.id" :title="mapel.nama_mapel">
                                    {{ mapel.singkatan || mapel.nama_mapel.substring(0, 10) }}
                                </th>
                                <th scope="col" class="px-4 py-3 text-center bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 font-bold">Total</th>
                                <th scope="col" class="px-4 py-3 text-center bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 font-bold">Rata</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(s, index) in siswa" :key="s.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                                <td class="px-4 py-3 sticky left-0 bg-white dark:bg-gray-800 group-hover:bg-gray-50 dark:group-hover:bg-gray-700/50 z-10 text-center font-medium">{{ index + 1 }}</td>
                                <td class="px-4 py-3 sticky left-[3rem] bg-white dark:bg-gray-800 group-hover:bg-gray-50 dark:group-hover:bg-gray-700/50 z-10 font-bold text-gray-900 dark:text-white truncate max-w-[200px]" :title="s.nama_lengkap">
                                    {{ s.nama_lengkap }}
                                </td>
                                <td class="px-4 py-3 text-center font-extrabold text-primary-600 dark:text-primary-400 text-sm">
                                    {{ peringkat_data && peringkat_data[s.id] ? peringkat_data[s.id].rank : '-' }}
                                </td>
                                <td class="px-4 py-3 text-center font-medium text-gray-700 dark:text-gray-300" v-for="mapel in mapels" :key="mapel.id">
                                    {{ rapor_data && rapor_data[s.id] && rapor_data[s.id][mapel.id] ? rapor_data[s.id][mapel.id] : '-' }}
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50/50 dark:bg-emerald-900/10">
                                    {{ peringkat_data && peringkat_data[s.id] ? peringkat_data[s.id].total : '0' }}
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-blue-600 dark:text-blue-400 bg-blue-50/50 dark:bg-blue-900/10">
                                    {{ peringkat_data && peringkat_data[s.id] ? Number(peringkat_data[s.id].rata).toFixed(1) : '0.0' }}
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
