<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    kelas_list: Array,
    siswa: Array,
    filters: Object
});

const formFilter = useForm({
    kelas_id: props.filters.kelas_id || '',
});

const filterData = () => {
    if (formFilter.kelas_id) {
        formFilter.get(route('admin.monitoring.rapor'), {
            preserveState: true,
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Monitoring eRapor" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-file-signature text-primary-500"></i>
                        Monitoring Kelengkapan eRapor
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Pantau kelengkapan pengisian nilai rapor oleh Guru dan Wali Kelas.
                    </p>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <form @submit.prevent="filterData" class="flex flex-col md:flex-row gap-4 items-end">
                    <div class="flex-1 w-full">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pilih Kelas</label>
                        <select v-model="formFilter.kelas_id" @change="filterData" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="">-- Pilih Kelas --</option>
                            <option v-for="k in kelas_list" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="px-6 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium transition-colors shadow-sm">
                            <i class="fas fa-search mr-2"></i> Tampilkan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabel Siswa -->
            <div v-if="siswa && siswa.length > 0" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                    <h3 class="font-bold text-gray-900 dark:text-white">Status Kelengkapan eRapor</h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-300">
                            <tr>
                                <th scope="col" class="px-6 py-4 w-16">No</th>
                                <th scope="col" class="px-6 py-4">Siswa</th>
                                <th scope="col" class="px-6 py-4 text-center">Nilai Akademik</th>
                                <th scope="col" class="px-6 py-4 text-center">Kehadiran</th>
                                <th scope="col" class="px-6 py-4 text-center">Catatan Wali</th>
                                <th scope="col" class="px-6 py-4 text-center">Status Final</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(s, index) in siswa" :key="s.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4">{{ index + 1 }}</td>
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ s.nama_lengkap }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span v-if="s.rapor_akhir && s.rapor_akhir.length > 0" class="text-green-500"><i class="fas fa-check-circle"></i> Selesai</span>
                                    <span v-else class="text-red-500"><i class="fas fa-times-circle"></i> Kosong</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span v-if="s.rapor_kehadiran && s.rapor_kehadiran.length > 0" class="text-green-500"><i class="fas fa-check-circle"></i> Selesai</span>
                                    <span v-else class="text-red-500"><i class="fas fa-times-circle"></i> Kosong</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span v-if="s.rapor_catatan_wali && s.rapor_catatan_wali.length > 0" class="text-green-500"><i class="fas fa-check-circle"></i> Selesai</span>
                                    <span v-else class="text-red-500"><i class="fas fa-times-circle"></i> Kosong</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span v-if="s.rapor_akhir?.length > 0 && s.rapor_kehadiran?.length > 0 && s.rapor_catatan_wali?.length > 0" class="px-2 py-1 bg-green-100 text-green-800 rounded-lg text-xs font-bold">LENGKAP</span>
                                    <span v-else class="px-2 py-1 bg-red-100 text-red-800 rounded-lg text-xs font-bold">BELUM LENGKAP</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div v-else-if="formFilter.kelas_id" class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-2xl p-6 text-center text-yellow-800 dark:text-yellow-400">
                <i class="fas fa-info-circle text-3xl mb-3"></i>
                <p>Data siswa tidak ditemukan di kelas ini.</p>
            </div>
        </div>
    </DashboardLayout>
</template>
