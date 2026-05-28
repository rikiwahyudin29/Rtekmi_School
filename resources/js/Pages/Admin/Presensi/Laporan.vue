<script setup>
import { useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    data: Array,
    kelas: Array,
    bulan: String,
    filter_kelas: [String, Number],
    filter_role: String
});

const filterForm = useForm({
    role: props.filter_role || 'siswa',
    bulan: props.bulan,
    id_kelas: props.filter_kelas || ''
});

import { watch } from 'vue';

watch(() => [filterForm.role, filterForm.bulan, filterForm.id_kelas], () => {
    filterForm.get(route('admin.presensi.laporan'), {
        preserveState: true,
        preserveScroll: true
    });
}, { deep: true });

const cetakHarian = () => {
    const routeName = filterForm.role === 'guru' ? 'admin.presensi.cetak_harian_guru' : 'admin.presensi.cetak_harian';
    window.open(route(routeName, {
        tanggal: filterForm.bulan + '-01',
        id_kelas: filterForm.id_kelas
    }), '_blank');
};
</script>

<template>
    <DashboardLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                
                <div class="mb-8">
                    <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-1">Laporan Kehadiran Harian</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Lihat laporan presensi setiap harinya.</p>
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
                                        <select v-model="filterForm.id_kelas" class="pl-10 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="">Semua Kelas</option>
                                            <option v-for="k in kelas" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700 flex flex-wrap gap-3 justify-end items-center">
                                <button type="button" @click="cetakHarian" class="bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 px-5 py-2.5 rounded-xl shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 hover:shadow transition-all flex items-center gap-2 text-sm font-medium">
                                    <i class="fas fa-print text-green-600 dark:text-green-400"></i> Cetak Harian (PDF)
                                </button>
                            </div>
                        </form>

                        <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Siswa</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kelas</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Waktu Masuk</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Metode</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="item in data" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ item.tanggal }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 font-bold">
                                            {{ item.siswa?.nama_lengkap ?? item.guru?.nama_lengkap }}<br>
                                            <span class="text-xs text-gray-500 dark:text-gray-400 font-normal">{{ item.siswa?.nis ?? item.guru?.nik }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ item.siswa?.kelas?.nama_kelas ?? 'Guru & Staff' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            <span v-if="item.jam_masuk" class="bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 px-3 py-1 rounded-lg text-xs font-bold">{{ item.jam_masuk }}</span>
                                            <span v-else class="text-gray-400">-</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-lg" 
                                                :class="{
                                                    'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400': item.status_kehadiran === 'Hadir',
                                                    'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400': item.status_kehadiran === 'Terlambat',
                                                    'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400': item.status_kehadiran === 'Sakit' || item.status_kehadiran === 'Izin',
                                                    'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400': item.status_kehadiran === 'Dinas Luar',
                                                    'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400': item.status_kehadiran === 'Alpha',
                                                }">
                                                {{ item.status_kehadiran }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <span v-if="item.metode === 'QR'" class="text-blue-600 dark:text-blue-400 font-bold flex items-center gap-1"><i class="fas fa-qrcode"></i> QR</span>
                                            <span v-else-if="item.metode === 'NFC'" class="text-purple-600 dark:text-purple-400 font-bold flex items-center gap-1"><i class="fas fa-wifi"></i> NFC</span>
                                            <span v-else class="text-gray-600 dark:text-gray-400 font-bold flex items-center gap-1"><i class="fas fa-keyboard"></i> Manual</span>
                                        </td>
                                    </tr>
                                    <tr v-if="data.length === 0">
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">Tidak ada data kehadiran ditemukan pada bulan/kelas tersebut.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>


            </div>
        </div>
    </DashboardLayout>
</template>
