<script setup>
import { Head, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    siswa: Object,
    nilai: Array
});

const hitungRataMapel = (n) => {
    const s1 = parseFloat(n.s1) || 0;
    const s2 = parseFloat(n.s2) || 0;
    const s3 = parseFloat(n.s3) || 0;
    const s4 = parseFloat(n.s4) || 0;
    const s5 = parseFloat(n.s5) || 0;
    const s6 = parseFloat(n.s6) || 0;
    
    let total = s1 + s2 + s3 + s4 + s5 + s6;
    let pembagi = 0;
    if(s1 > 0) pembagi++;
    if(s2 > 0) pembagi++;
    if(s3 > 0) pembagi++;
    if(s4 > 0) pembagi++;
    if(s5 > 0) pembagi++;
    if(s6 > 0) pembagi++;

    return pembagi > 0 ? (total / pembagi).toFixed(2) : 0;
};
</script>

<template>
    <Head :title="`Detail Nilai - ${siswa.nama_lengkap}`" />

    <DashboardLayout>
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Detail Nilai Siswa</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Rincian nilai per mata pelajaran</p>
            </div>
            <button @click="router.get(route('admin.kelulusan.nilai'))" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Kembali ke Rekap
            </button>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden mb-6 flex flex-col md:flex-row">
            <div class="p-6 md:w-1/3 bg-gray-50 dark:bg-gray-700/30 border-b md:border-b-0 md:border-r border-gray-100 dark:border-gray-700 flex flex-col items-center justify-center text-center">
                <div class="w-24 h-24 rounded-full bg-primary-100 dark:bg-primary-900/50 flex items-center justify-center mb-4">
                    <i class="fas fa-user-graduate text-4xl text-primary-600 dark:text-primary-400"></i>
                </div>
                <h3 class="font-bold text-xl text-gray-900 dark:text-white mb-1">{{ siswa.nama_lengkap }}</h3>
                <p class="text-sm font-medium text-primary-600 dark:text-primary-400 mb-2">{{ siswa.nisn || '-' }} / {{ siswa.nis }}</p>
                <span class="px-3 py-1 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-full text-xs font-bold">{{ siswa.kelas?.nama_kelas }}</span>
            </div>
            <div class="p-6 md:w-2/3 flex flex-col justify-center">
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 rounded-xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800">
                        <div class="text-xs text-gray-500 mb-1">Jurusan</div>
                        <div class="font-bold text-gray-900 dark:text-white">{{ siswa.kelas?.jurusan?.nama_jurusan || '-' }}</div>
                    </div>
                    <div class="p-4 rounded-xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800">
                        <div class="text-xs text-gray-500 mb-1">Total Mapel Terdaftar</div>
                        <div class="font-bold text-gray-900 dark:text-white">{{ nilai.length }} Mata Pelajaran</div>
                    </div>
                </div>
                <div class="mt-4 flex gap-2">
                    <button @click="router.get(route('admin.kelulusan.input_nilai', siswa.id))" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                        <i class="fas fa-edit"></i> Edit Nilai Manual
                    </button>
                    <a :href="route('admin.kelulusan.cetak_transkrip', siswa.id)" target="_blank" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                        <i class="fas fa-file-invoice"></i> Preview Transkrip
                    </a>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                <h3 class="font-bold text-gray-700 dark:text-gray-300">Rincian Nilai per Mata Pelajaran</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-700/50 dark:text-gray-400">
                        <tr>
                            <th class="px-6 py-4 font-medium">Mata Pelajaran</th>
                            <th class="px-4 py-4 font-medium text-center">Smt 1</th>
                            <th class="px-4 py-4 font-medium text-center">Smt 2</th>
                            <th class="px-4 py-4 font-medium text-center">Smt 3</th>
                            <th class="px-4 py-4 font-medium text-center">Smt 4</th>
                            <th class="px-4 py-4 font-medium text-center">Smt 5</th>
                            <th class="px-4 py-4 font-medium text-center">Smt 6</th>
                            <th class="px-4 py-4 font-medium text-center bg-blue-50 dark:bg-blue-900/20 text-blue-700">Rata2 Rapor</th>
                            <th class="px-4 py-4 font-medium text-center bg-primary-50 dark:bg-primary-900/20 text-primary-700">Nilai US</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <tr v-for="n in nilai" :key="n.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900 dark:text-white">{{ n.nama_mapel }}</div>
                                <div class="text-xs text-gray-500">{{ n.kelompok }}</div>
                            </td>
                            <td class="px-4 py-4 text-center">{{ n.s1 || '-' }}</td>
                            <td class="px-4 py-4 text-center">{{ n.s2 || '-' }}</td>
                            <td class="px-4 py-4 text-center">{{ n.s3 || '-' }}</td>
                            <td class="px-4 py-4 text-center">{{ n.s4 || '-' }}</td>
                            <td class="px-4 py-4 text-center">{{ n.s5 || '-' }}</td>
                            <td class="px-4 py-4 text-center">{{ n.s6 || '-' }}</td>
                            <td class="px-4 py-4 text-center bg-blue-50/50 dark:bg-blue-900/10">
                                <span class="font-bold text-blue-700 dark:text-blue-400">{{ hitungRataMapel(n) }}</span>
                            </td>
                            <td class="px-4 py-4 text-center bg-primary-50/50 dark:bg-primary-900/10">
                                <span class="font-bold text-primary-700 dark:text-primary-400">{{ n.nilai_us || '-' }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>
