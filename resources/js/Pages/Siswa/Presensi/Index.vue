<script setup>
import { useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    data: Array,
    bulan: String
});

const form = useForm({
    bulan: props.bulan
});

const filterData = () => {
    form.get(route('siswa.presensi.index'));
};
</script>

<template>
    <DashboardLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Riwayat Kehadiran (Siswa)</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="filterData" class="flex gap-4 items-end mb-6 bg-gray-50 p-4 rounded-lg">
                            <div class="w-full sm:w-1/3">
                                <label class="block text-sm font-medium text-gray-700">Pilih Bulan</label>
                                <input type="month" v-model="form.bulan" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded shadow hover:bg-indigo-700">
                                    <i class="fas fa-filter mr-2"></i> Filter
                                </button>
                            </div>
                        </form>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Masuk</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Pulang</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metode</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="item in data" :key="item.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">{{ item.tanggal }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-bold">{{ item.jam_masuk ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-bold">{{ item.jam_pulang ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                                :class="{
                                                    'bg-green-100 text-green-800': item.display_status === 'Hadir',
                                                    'bg-red-100 text-red-800': item.display_status.includes('Alpha') || item.display_status === 'Terlambat',
                                                    'bg-yellow-100 text-yellow-800': item.display_status === 'Izin' || item.display_status === 'Sakit'
                                                }">
                                                {{ item.display_status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.metode }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ item.keterangan || '-' }}</td>
                                    </tr>
                                    <tr v-if="data.length === 0">
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada data presensi pada bulan ini.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </DashboardLayout>
</template>
