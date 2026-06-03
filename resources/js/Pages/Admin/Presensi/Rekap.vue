<script setup>
import { useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    kelas: Array,
    bulan: String,
    filter_kelas: [String, Number],
    filter_role: String,
    filter_role: String,
    data_rekap: Array,
    dates: Array,
    info_libur: Object
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
                                        <th :colspan="dates?.length || 0" class="px-2 py-2 text-center font-bold uppercase tracking-wider text-xs border-b border-gray-200 dark:border-gray-700 border-r border-gray-200 dark:border-gray-700">Tanggal (19-18)</th>
                                        <th colspan="6" class="px-2 py-2 text-center font-bold uppercase tracking-wider text-xs border-b border-gray-200 dark:border-gray-700 border-r border-gray-200 dark:border-gray-700">Total</th>
                                        <th rowspan="2" class="px-2 py-3 text-center font-bold uppercase tracking-wider text-xs border-r border-gray-200 dark:border-gray-700">%</th>
                                    </tr>
                                    <tr>
                                        <th v-for="dateStr in dates" :key="dateStr" class="px-1 py-1 text-center border-r border-gray-200 dark:border-gray-700 text-[10px] w-6">{{ dateStr.substring(8, 10) }}</th>
                                        <th class="px-2 py-1 text-center border-r border-gray-200 dark:border-gray-700 text-xs w-8 text-green-600 dark:text-green-400 font-bold" title="Hadir">H</th>
                                        <th class="px-2 py-1 text-center border-r border-gray-200 dark:border-gray-700 text-xs w-8 text-yellow-600 dark:text-yellow-400 font-bold" title="Sakit">S</th>
                                        <th class="px-2 py-1 text-center border-r border-gray-200 dark:border-gray-700 text-xs w-8 text-blue-600 dark:text-blue-400 font-bold" title="Izin">I</th>
                                        <th class="px-2 py-1 text-center border-r border-gray-200 dark:border-gray-700 text-xs w-8 text-red-600 dark:text-red-400 font-bold" title="Alpha">A</th>
                                        <th class="px-2 py-1 text-center border-r border-gray-200 dark:border-gray-700 text-xs w-8 text-purple-600 dark:text-purple-400 font-bold" title="Dinas Luar">DL</th>
                                        <th class="px-2 py-1 text-center border-r border-gray-200 dark:border-gray-700 text-xs w-16 text-orange-600 dark:text-orange-400 font-bold" title="Total Keterlambatan">Menit Terlambat</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="(item, index) in data_rekap" :key="index" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <td class="px-4 py-2 border-r border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">{{ index + 1 }}</td>
                                        <td class="px-4 py-2 border-r border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 font-medium truncate max-w-[200px]" :title="item.nama">{{ item.nama }}</td>
                                        
                                        <!-- Tanggal Loop -->
                                        <!-- Tanggal Loop -->
                                        <template v-for="dateStr in dates" :key="dateStr">
                                        <td v-if="!info_libur || !info_libur[dateStr] || index === 0"
                                            :rowspan="info_libur && info_libur[dateStr] ? (data_rekap.length || 1) : 1"
                                            class="px-1 py-2 text-center border-r border-gray-200 dark:border-gray-700 font-semibold text-xs relative"
                                            :class="{
                                                'bg-red-200 dark:bg-red-900/30': info_libur && info_libur[dateStr],
                                                'bg-green-500 text-white': !info_libur?.[dateStr] && item.harian[dateStr] == 'Hadir',
                                                'bg-orange-400 text-white': !info_libur?.[dateStr] && item.harian[dateStr] == 'Terlambat',
                                                'bg-blue-500 text-white': !info_libur?.[dateStr] && item.harian[dateStr] == 'Sakit',
                                                'bg-cyan-500 text-white': !info_libur?.[dateStr] && item.harian[dateStr] == 'Izin',
                                                'bg-red-500 text-white': !info_libur?.[dateStr] && item.harian[dateStr] == 'Alpha',
                                                'bg-purple-500 text-white': !info_libur?.[dateStr] && item.harian[dateStr] == 'Dinas Luar',
                                                'text-gray-300 dark:text-gray-600': !info_libur?.[dateStr] && !['Hadir', 'Terlambat', 'Sakit', 'Izin', 'Alpha', 'Dinas Luar'].includes(item.harian[dateStr])
                                            }">
                                            
                                            <!-- Jika Libur (render sekali berkat rowspan) -->
                                            <div v-if="info_libur && info_libur[dateStr]" class="absolute inset-0 flex items-center justify-center overflow-hidden">
                                                <div style="writing-mode: vertical-rl; transform: rotate(180deg);" class="text-red-700 dark:text-red-400 font-extrabold text-[10px] whitespace-nowrap tracking-widest uppercase">
                                                    {{ info_libur[dateStr] }}
                                                </div>
                                            </div>
                                            
                                            <!-- Jika Tidak Libur -->
                                            <template v-else>
                                                <span v-if="item.harian[dateStr] == 'Hadir'">H</span>
                                                <span v-else-if="item.harian[dateStr] == 'Terlambat'">T</span>
                                                <span v-else-if="item.harian[dateStr] == 'Sakit'">S</span>
                                                <span v-else-if="item.harian[dateStr] == 'Izin'">I</span>
                                                <span v-else-if="item.harian[dateStr] == 'Alpha'">A</span>
                                                <span v-else-if="item.harian[dateStr] == 'Dinas Luar'">DL</span>
                                                <span v-else>-</span>
                                            </template>
                                        </td>
                                        </template>
                                        
                                        <!-- Total -->
                                        <td class="px-2 py-2 text-center font-bold text-white bg-green-500 border-r border-white dark:border-gray-700">{{ item.total.H }}</td>
                                        <td class="px-2 py-2 text-center font-bold text-white bg-blue-500 border-r border-white dark:border-gray-700">{{ item.total.S }}</td>
                                        <td class="px-2 py-2 text-center font-bold text-white bg-cyan-500 border-r border-white dark:border-gray-700">{{ item.total.I }}</td>
                                        <td class="px-2 py-2 text-center font-bold text-white bg-red-500 border-r border-white dark:border-gray-700">{{ item.total.A }}</td>
                                        <td class="px-2 py-2 text-center font-bold text-white bg-purple-500 border-r border-white dark:border-gray-700">{{ item.total.DL }}</td>
                                        <td class="px-2 py-2 text-center font-bold text-white bg-orange-500 border-r border-white dark:border-gray-700">{{ item.format_terlambat || '-' }}</td>
                                        
                                        <!-- Persentase -->
                                        <td class="px-2 py-2 text-center font-bold border-r border-gray-200 dark:border-gray-700" :class="item.persen < 80 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">
                                            {{ item.persen }}%
                                        </td>
                                    </tr>
                                    <tr v-if="data_rekap.length === 0">
                                        <td :colspan="(dates?.length || 0) + 9" class="px-6 py-8 text-center text-gray-500">
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
