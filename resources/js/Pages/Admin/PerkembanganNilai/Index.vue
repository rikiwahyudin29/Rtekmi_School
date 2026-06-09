<script setup>
import { Head, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    siswa: Array,
    selected_siswa_id: [String, Number],
    chart_data: Object,
    tabel_data: Object,
    mata_pelajaran: Array
});

const siswa_id = ref(props.selected_siswa_id || '');

const cariSiswa = () => {
    if (siswa_id.value) {
        router.get(route('admin.perkembangan-nilai.index'), { siswa_id: siswa_id.value }, { preserveState: true, preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Perkembangan Nilai Siswa" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-chart-line text-blue-500"></i>
                        Perkembangan Nilai Siswa
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Mengecek riwayat perkembangan nilai rapor siswa dari awal hingga akhir (Semester 1 - 6).
                    </p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <div class="max-w-md w-full flex gap-2">
                    <select v-model="siswa_id" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- Pilih Siswa --</option>
                        <option v-for="s in siswa" :key="s.id" :value="s.id">{{ s.nama_lengkap }} ({{ s.kelas?.nama_kelas }})</option>
                    </select>
                    <button @click="cariSiswa" class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium whitespace-nowrap">
                        <i class="fas fa-search"></i> Cek
                    </button>
                </div>
            </div>

            <div v-if="chart_data && tabel_data" class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <!-- Grafik Rata-rata -->
                <div class="xl:col-span-1 bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <h3 class="font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                        <i class="fas fa-chart-bar text-blue-500"></i> Grafik Rata-rata Rapor
                    </h3>
                    
                    <div class="flex flex-col gap-4 h-64 justify-end">
                        <div v-for="(val, idx) in chart_data.values" :key="idx" class="flex items-center gap-3">
                            <div class="w-20 text-xs font-medium text-gray-600 dark:text-gray-400">{{ chart_data.labels[idx] }}</div>
                            <div class="flex-1 h-8 bg-gray-100 dark:bg-gray-700 rounded-r-lg overflow-hidden relative">
                                <div v-if="val !== null" class="h-full bg-blue-500 rounded-r-lg transition-all duration-1000 flex items-center px-2 text-xs text-white font-bold" :style="{ width: `${val}%` }">
                                    {{ val }}
                                </div>
                                <div v-else class="absolute inset-0 flex items-center px-2 text-xs text-gray-400 italic">Belum ada data</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Rekap Lengkap -->
                <div class="xl:col-span-2 bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                        <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <i class="fas fa-table text-blue-500"></i> Tabel Rekapitulasi Nilai Tiap Mata Pelajaran
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-3 border-r dark:border-gray-600">No</th>
                                    <th class="px-4 py-3 border-r dark:border-gray-600">Mata Pelajaran</th>
                                    <th class="px-3 py-3 text-center border-r dark:border-gray-600">SMT 1</th>
                                    <th class="px-3 py-3 text-center border-r dark:border-gray-600">SMT 2</th>
                                    <th class="px-3 py-3 text-center border-r dark:border-gray-600">SMT 3</th>
                                    <th class="px-3 py-3 text-center border-r dark:border-gray-600">SMT 4</th>
                                    <th class="px-3 py-3 text-center border-r dark:border-gray-600">SMT 5</th>
                                    <th class="px-3 py-3 text-center">SMT 6</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(mapel, idx) in mata_pelajaran" :key="idx" class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3 border-r dark:border-gray-700">{{ idx + 1 }}</td>
                                    <td class="px-4 py-3 border-r dark:border-gray-700 font-medium text-gray-900 dark:text-white whitespace-nowrap">{{ mapel }}</td>
                                    <td v-for="smt in 6" :key="smt" class="px-3 py-3 text-center border-r dark:border-gray-700 font-bold" :class="{'text-gray-300 dark:text-gray-600 font-normal': !tabel_data[mapel][smt]}">
                                        {{ tabel_data[mapel][smt] || '-' }}
                                    </td>
                                </tr>
                                <tr v-if="mata_pelajaran.length === 0">
                                    <td colspan="8" class="px-4 py-6 text-center text-gray-500">Belum ada data nilai untuk siswa ini.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div v-else-if="selected_siswa_id" class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-2xl p-6 text-center text-yellow-800 dark:text-yellow-400">
                <i class="fas fa-exclamation-triangle text-3xl mb-3"></i>
                <p>Data Rapor belum ditemukan untuk siswa ini.</p>
            </div>
        </div>
    </DashboardLayout>
</template>
