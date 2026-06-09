<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { onMounted } from 'vue';

const props = defineProps({
    kelas: Object,
    siswa: Array,
    kenaikan: Object,
    kelas_all: Array
});

const form = useForm({
    data: {}
});

onMounted(() => {
    const initData = {};
    props.siswa.forEach(s => {
        const k = props.kenaikan[s.id];
        initData[s.id] = {
            status: k ? k.status : 'Naik',
            kelas_tujuan_id: k ? k.kelas_tujuan_id : ''
        };
    });
    form.data = initData;
});

const submitKenaikan = () => {
    form.post(route('guru.walikelas.kenaikan.store'));
};
</script>

<template>
    <Head title="Status Kenaikan Kelas" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-level-up-alt text-blue-500"></i>
                        Kenaikan & Kelulusan
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Input status kenaikan kelas atau kelulusan untuk siswa kelas {{ kelas?.nama_kelas }}.
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('guru.walikelas.index')" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 font-medium shadow-sm flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </Link>
                    <button @click="submitKenaikan" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium shadow-sm flex items-center gap-2 transition-colors">
                        <i class="fas fa-save"></i> {{ form.processing ? 'Menyimpan...' : 'Simpan Data' }}
                    </button>
                </div>
            </div>

            <!-- Warning Card -->
            <div class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-2xl p-4 flex items-start gap-3">
                <i class="fas fa-exclamation-triangle text-yellow-600 mt-1"></i>
                <p class="text-sm text-yellow-800 dark:text-yellow-400">
                    <strong>Penting:</strong> Status kenaikan ini akan dicetak di halaman akhir rapor siswa. Pastikan Anda telah melakukan rapat pleno kenaikan kelas sebelum menyimpan data ini secara permanen.
                </p>
            </div>

            <!-- Tabel Input Kenaikan -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                            <tr>
                                <th scope="col" class="px-6 py-4 w-16">No</th>
                                <th scope="col" class="px-6 py-4">NISN</th>
                                <th scope="col" class="px-6 py-4">Nama Siswa</th>
                                <th scope="col" class="px-6 py-4 w-48 text-center">Status</th>
                                <th scope="col" class="px-6 py-4">Diterima di Kelas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(s, index) in siswa" :key="s.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4">{{ index + 1 }}</td>
                                <td class="px-6 py-4">{{ s.nisn }}</td>
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ s.nama_lengkap }}</td>
                                <td class="px-6 py-4 text-center">
                                    <select v-if="form.data[s.id]" v-model="form.data[s.id].status" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500 font-bold" :class="{'text-red-600': form.data[s.id].status.includes('Tidak')}">
                                        <option value="Naik">Naik Kelas</option>
                                        <option value="Tidak Naik">Tinggal di Kelas</option>
                                        <option value="Lulus">Lulus</option>
                                        <option value="Tidak Lulus">Tidak Lulus</option>
                                    </select>
                                </td>
                                <td class="px-6 py-4">
                                    <select v-if="form.data[s.id]" v-model="form.data[s.id].kelas_tujuan_id" :disabled="form.data[s.id].status.includes('Lulus')" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50">
                                        <option value="">-- Pilih Kelas Tujuan --</option>
                                        <option v-for="k in kelas_all" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                                    </select>
                                </td>
                            </tr>
                            <tr v-if="siswa.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">Tidak ada siswa di kelas ini.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
