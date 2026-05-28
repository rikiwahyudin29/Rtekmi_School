<script setup>
import { Head, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    siswa: Array,
    kelas: Array,
    flash: Object
});

const selectedKelas = ref('');
const search = ref('');

const filterKelas = () => {
    router.get(route('admin.kelulusan.nilai'), { kelas_id: selectedKelas.value }, { preserveState: true });
};

const filteredSiswa = computed(() => {
    if (!search.value) return props.siswa;
    return props.siswa.filter(s => s.nama_lengkap.toLowerCase().includes(search.value.toLowerCase()) || s.nis.includes(search.value));
});

</script>

<template>
    <Head title="Rekap Nilai Kelulusan" />

    <DashboardLayout>
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Rekapitulasi Nilai SKL</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Nilai Rapor, Ujian Sekolah, SKL, dan fitur Cetak</p>
            </div>
            <div class="flex gap-2">
                <select v-model="selectedKelas" @change="filterKelas" class="rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">Semua Kelas 12</option>
                    <option v-for="k in kelas" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                </select>
                <input v-model="search" type="text" placeholder="Cari Siswa..." class="rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500">
            </div>
        </div>

        <div v-if="flash?.success" class="mb-4 p-4 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-xl border border-green-200 dark:border-green-800 flex items-center gap-3">
            <i class="fas fa-check-circle text-xl"></i>
            <span class="font-medium">{{ flash.success }}</span>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                <h3 class="font-bold text-gray-700 dark:text-gray-300">Daftar Rekap Nilai</h3>
                <div class="flex gap-2">
                    <a :href="route('admin.kelulusan.download_template')" target="_blank" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center gap-2">
                        <i class="fas fa-file-excel"></i> Download Template
                    </a>
                    
                    <label class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center gap-2 cursor-pointer">
                        <i class="fas fa-upload"></i> Import Nilai
                        <input type="file" class="hidden" accept=".xlsx,.xls" @change="(e) => {
                            if(e.target.files.length > 0) {
                                router.post(route('admin.kelulusan.import'), { file_excel: e.target.files[0] }, { forceFormData: true, preserveScroll: true });
                            }
                        }">
                    </label>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-700/50 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3 font-medium">No</th>
                            <th class="px-4 py-3 font-medium">Nama Siswa</th>
                            <th class="px-4 py-3 font-medium text-center">Jml Mapel</th>
                            <th class="px-4 py-3 font-medium text-center">Rata-Rata Rapor</th>
                            <th class="px-4 py-3 font-medium text-center">Rata-Rata US</th>
                            <th class="px-4 py-3 font-medium text-center text-primary-600">Nilai SKL</th>
                            <th class="px-4 py-3 font-medium text-center">Aksi / Cetak</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <tr v-for="(s, index) in filteredSiswa" :key="s.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-4 py-3 text-gray-500">{{ index + 1 }}</td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-gray-900 dark:text-white">{{ s.nama_lengkap }}</div>
                                <div class="text-xs text-gray-500">{{ s.kelas?.nama_kelas }}</div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded-lg font-bold">{{ s.jml_mapel }}</span>
                            </td>
                            <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ s.rata_rapor }}</td>
                            <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ s.rata_us }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-3 py-1 bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 rounded-xl font-bold border border-primary-200 dark:border-primary-800">
                                    {{ s.nilai_skl }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-center gap-2">
                                    <button @click="router.get(route('admin.kelulusan.detail_nilai', s.id))" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition-colors" title="Lihat Detail Nilai">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button @click="router.get(route('admin.kelulusan.input_nilai', s.id))" class="w-8 h-8 rounded-lg bg-orange-50 text-orange-600 hover:bg-orange-100 flex items-center justify-center transition-colors" title="Input Nilai Manual">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a :href="route('admin.kelulusan.cetak_transkrip', s.id)" target="_blank" class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 hover:bg-purple-100 flex items-center justify-center transition-colors" title="Cetak Transkrip">
                                        <i class="fas fa-file-invoice"></i>
                                    </a>
                                    <a :href="route('admin.kelulusan.cetak_skl', s.id)" target="_blank" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 flex items-center justify-center transition-colors" title="Cetak SKL">
                                        <i class="fas fa-graduation-cap"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredSiswa.length === 0">
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-search text-4xl mb-3 text-gray-300 dark:text-gray-600"></i>
                                    <p>Tidak ada data siswa ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>
