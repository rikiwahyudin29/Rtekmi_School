<script setup>
import { useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    kelas: Array,
    bulan: String,
    filter_kelas: [String, Number],
    filter_role: String,
    data_rekap: Array,
    jml_hari: [String, Number]
});

const filterForm = useForm({
    role: props.filter_role || 'siswa',
    bulan: props.bulan,
    id_kelas: props.filter_kelas || ''
});

import { watch } from 'vue';

watch(() => [filterForm.role, filterForm.bulan, filterForm.id_kelas], () => {
    filterForm.get(route('admin.presensi.rekap'), {
        preserveState: true,
        preserveScroll: true
    });
}, { deep: true });

const cetakRekap = () => {
    if (filterForm.role === 'siswa' && !filterForm.id_kelas) {
        alert('Silakan pilih kelas terlebih dahulu sebelum mencetak rekap siswa.');
        return;
    }
    // TODO: implement cetak rekap guru if needed. But for now, we'll just redirect to cetak rekap. 
    // Wait, let's use the new cetak harian guru/matrix guru as they asked for matrix guru, not rekap guru?
    // Let's pass the role anyway if we implement cetak_rekap for guru later.
    window.open(route('admin.presensi.cetak_rekap', {
        role: filterForm.role,
        bulan: filterForm.bulan,
        id_kelas: filterForm.id_kelas
    }), '_blank');
};

const cetakMatrix = () => {
    if (filterForm.role === 'siswa' && !filterForm.id_kelas) {
        alert('Silakan pilih kelas terlebih dahulu sebelum mencetak matrix siswa.');
        return;
    }
    
    const routeName = filterForm.role === 'guru' ? 'admin.presensi.cetak_matrix_guru' : 'admin.presensi.cetak_matrix';
    
    window.open(route(routeName, {
        bulan: filterForm.bulan,
        id_kelas: filterForm.id_kelas
    }), '_blank');
};
</script>

<template>
    <DashboardLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                
                <div class="mb-8">
                    <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-1">Rekapitulasi Kehadiran Bulanan</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Lihat rekap presensi dalam satu bulan.</p>
                </div>
                

                        <form @submit.prevent="filterData" class="mb-6 bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Tipe Absen</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-users text-gray-400"></i>
                                        </div>
                                        <select v-model="filterForm.role" class="pl-10 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="siswa">Siswa</option>
                                            <option value="guru">Guru & Staff</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Pilih Bulan</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-calendar-alt text-gray-400"></i>
                                        </div>
                                        <input type="month" v-model="filterForm.bulan" class="pl-10 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                </div>

                                <div v-if="filterForm.role === 'siswa'">
                                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Filter Kelas</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-chalkboard text-gray-400"></i>
                                        </div>
                                        <select v-model="filterForm.id_kelas" class="pl-10 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                            <option value="">-- Pilih Kelas --</option>
                                            <option v-for="k in kelas" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700 flex flex-wrap gap-3 justify-end items-center">
                                <button type="button" @click="cetakRekap" class="bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 px-5 py-2.5 rounded-xl shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 hover:shadow transition-all flex items-center gap-2 text-sm font-medium">
                                    <i class="fas fa-print text-green-600 dark:text-green-400"></i> Rekap Total
                                </button>
                                <button type="button" @click="cetakMatrix" class="bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 px-5 py-2.5 rounded-xl shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 hover:shadow transition-all flex items-center gap-2 text-sm font-medium">
                                    <i class="fas fa-th text-teal-600 dark:text-teal-400"></i> Cetak Matrix
                                </button>
                            </div>
                        </form>

                        <div v-if="filterForm.role === 'siswa' && !filter_kelas" class="text-center py-8 text-gray-500">
                            <i class="fas fa-info-circle text-4xl mb-4 text-gray-300"></i>
                            <p>Silakan pilih Kelas terlebih dahulu untuk melihat rekap kehadiran Siswa.</p>
                        </div>

                        <div v-else class="overflow-x-auto bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300">
                                    <tr>
                                        <th rowspan="2" class="px-4 py-3 text-left font-bold uppercase tracking-wider text-xs border-r border-gray-200 dark:border-gray-700">No</th>
                                        <th rowspan="2" class="px-4 py-3 text-left font-bold uppercase tracking-wider text-xs border-r border-gray-200 dark:border-gray-700 w-48">Nama Lengkap</th>
                                        <th :colspan="jml_hari" class="px-2 py-2 text-center font-bold uppercase tracking-wider text-xs border-b border-gray-200 dark:border-gray-700 border-r border-gray-200 dark:border-gray-700">Tanggal</th>
                                        <th colspan="5" class="px-2 py-2 text-center font-bold uppercase tracking-wider text-xs border-b border-gray-200 dark:border-gray-700 border-r border-gray-200 dark:border-gray-700">Total</th>
                                        <th rowspan="2" class="px-2 py-3 text-center font-bold uppercase tracking-wider text-xs border-r border-gray-200 dark:border-gray-700">%</th>
                                    </tr>
                                    <tr>
                                        <th v-for="n in parseInt(jml_hari)" :key="n" class="px-1 py-1 text-center border-r border-gray-200 dark:border-gray-700 text-[10px] w-6">{{ n }}</th>
                                        <th class="px-2 py-1 text-center border-r border-gray-200 dark:border-gray-700 text-xs w-8 text-green-600 dark:text-green-400 font-bold">H</th>
                                        <th class="px-2 py-1 text-center border-r border-gray-200 dark:border-gray-700 text-xs w-8 text-yellow-600 dark:text-yellow-400 font-bold">S</th>
                                        <th class="px-2 py-1 text-center border-r border-gray-200 dark:border-gray-700 text-xs w-8 text-blue-600 dark:text-blue-400 font-bold">I</th>
                                        <th class="px-2 py-1 text-center border-r border-gray-200 dark:border-gray-700 text-xs w-8 text-red-600 dark:text-red-400 font-bold">A</th>
                                        <th class="px-2 py-1 text-center border-r border-gray-200 dark:border-gray-700 text-xs w-8 text-purple-600 dark:text-purple-400 font-bold">DL</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="(item, index) in data_rekap" :key="index" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <td class="px-4 py-2 border-r border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{{ index + 1 }}</td>
                                        <td class="px-4 py-2 border-r border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 font-medium truncate max-w-[200px]" :title="item.nama">{{ item.nama }}</td>
                                        
                                        <!-- Tanggal Loop -->
                                        <td v-for="n in parseInt(jml_hari)" :key="n" class="px-1 py-2 text-center border-r border-gray-200 dark:border-gray-700 font-semibold text-xs">
                                            <span v-if="item.harian[n] == 'Hadir'" class="text-green-600 dark:text-green-400">H</span>
                                            <span v-else-if="item.harian[n] == 'Terlambat'" class="text-green-500 dark:text-green-300">T</span>
                                            <span v-else-if="item.harian[n] == 'Sakit'" class="text-yellow-600 dark:text-yellow-400">S</span>
                                            <span v-else-if="item.harian[n] == 'Izin'" class="text-blue-600 dark:text-blue-400">I</span>
                                            <span v-else-if="item.harian[n] == 'Alpha'" class="text-red-600 dark:text-red-400">A</span>
                                            <span v-else-if="item.harian[n] == 'Dinas Luar'" class="text-purple-600 dark:text-purple-400">DL</span>
                                            <span v-else class="text-gray-300 dark:text-gray-600">-</span>
                                        </td>
                                        
                                        <!-- Total -->
                                        <td class="px-2 py-2 text-center font-bold text-green-600 dark:text-green-400 border-r border-gray-200 dark:border-gray-700">{{ item.total.H }}</td>
                                        <td class="px-2 py-2 text-center font-bold text-yellow-600 dark:text-yellow-400 border-r border-gray-200 dark:border-gray-700">{{ item.total.S }}</td>
                                        <td class="px-2 py-2 text-center font-bold text-blue-600 dark:text-blue-400 border-r border-gray-200 dark:border-gray-700">{{ item.total.I }}</td>
                                        <td class="px-2 py-2 text-center font-bold text-red-600 dark:text-red-400 border-r border-gray-200 dark:border-gray-700">{{ item.total.A }}</td>
                                        <td class="px-2 py-2 text-center font-bold text-purple-600 dark:text-purple-400 border-r border-gray-200 dark:border-gray-700">{{ item.total.DL }}</td>
                                        
                                        <!-- Persentase -->
                                        <td class="px-2 py-2 text-center font-bold border-r border-gray-200 dark:border-gray-700" :class="item.persen < 80 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">
                                            {{ item.persen }}%
                                        </td>
                                    </tr>
                                    <tr v-if="data_rekap.length === 0">
                                        <td :colspan="parseInt(jml_hari) + 8" class="px-6 py-8 text-center text-gray-500">
                                            Data tidak ditemukan.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>


            </div>
        </div>
    </DashboardLayout>
</template>
