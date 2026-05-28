<script setup>
import { useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    riwayat: Array
});

const form = useForm({
    tanggal: '',
    status: 'Izin',
    keterangan: '',
    bukti: null
});

const handleFile = (e) => {
    form.bukti = e.target.files[0];
};

const submit = () => {
    form.post(route('guru.presensi.ajukan'), {
        onSuccess: () => {
            form.reset();
        }
    });
};
</script>

<template>
    <DashboardLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pengajuan Izin / Dinas Luar (Guru)</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 flex flex-col md:flex-row gap-6">
                
                <div class="w-full md:w-1/3">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Form Pengajuan</h3>
                            <form @submit.prevent="submit" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tanggal</label>
                                    <input type="date" v-model="form.tanggal" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Status</label>
                                    <select v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                        <option value="Izin">Izin</option>
                                        <option value="Sakit">Sakit</option>
                                        <option value="Dinas Luar">Dinas Luar</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Keterangan / Alasan</label>
                                    <textarea v-model="form.keterangan" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required placeholder="Tulis alasan izin/sakit/dinas..."></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Upload Surat Bukti (Jika Ada)</label>
                                    <input type="file" @change="handleFile" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                </div>
                                <button type="submit" :disabled="form.processing" class="w-full bg-indigo-600 text-white font-bold py-2 px-4 rounded hover:bg-indigo-700 shadow">
                                    Kirim Pengajuan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="w-full md:w-2/3">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Riwayat Pengajuan</h3>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bukti</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Verifikasi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="item in riwayat" :key="item.id">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ item.tanggal }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                                    :class="{
                                                        'bg-yellow-100 text-yellow-800': item.status_kehadiran === 'Izin',
                                                        'bg-blue-100 text-blue-800': item.status_kehadiran === 'Sakit',
                                                        'bg-indigo-100 text-indigo-800': item.status_kehadiran === 'Dinas Luar'
                                                    }">
                                                    {{ item.status_kehadiran }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">{{ item.keterangan }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600">
                                                <a v-if="item.bukti_izin" :href="`/uploads/surat_izin/${item.bukti_izin}`" target="_blank" class="hover:underline">Lihat Surat</a>
                                                <span v-else class="text-gray-400">-</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                                    :class="{
                                                        'bg-yellow-100 text-yellow-800': item.status_verifikasi === 'Pending',
                                                        'bg-green-100 text-green-800': item.status_verifikasi === 'Disetujui',
                                                        'bg-red-100 text-red-800': item.status_verifikasi === 'Ditolak'
                                                    }">
                                                    {{ item.status_verifikasi }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr v-if="riwayat.length === 0">
                                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">Belum ada riwayat pengajuan izin/sakit.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </DashboardLayout>
</template>
