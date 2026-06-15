<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    siswa: Array
});

const isModalOpen = ref(false);
const selectedSiswa = ref(null);

const form = useForm({
    pkl_id: null,
    nilai_sekolah: 0,
    nilai_dudi: 0
});

const openModal = (s) => {
    selectedSiswa.value = s;
    form.pkl_id = s.id;
    form.nilai_sekolah = s.nilai_sekolah || 0;
    form.nilai_dudi = s.nilai_dudi || 0;
    isModalOpen.value = true;
};

const submitNilai = () => {
    form.post(route('guru.pkl.nilai.simpan'), {
        preserveScroll: true,
        onSuccess: () => {
            isModalOpen.value = false;
        }
    });
};
</script>

<template>
    <Head title="Penilaian & Sertifikat PKL" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-award text-blue-500"></i>
                        Penilaian & Sertifikat PKL
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Input nilai akhir PKL dan terbitkan E-Sertifikat untuk siswa.
                    </p>
                </div>
            </div>

            <div v-if="$page.props.flash?.message" class="bg-green-50 text-green-800 p-4 rounded-2xl shadow-sm flex items-center gap-3">
                <i class="fas fa-check-circle"></i> {{ $page.props.flash.message }}
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-4 font-bold">Nama Siswa</th>
                                <th class="px-6 py-4 font-bold">Mitra DU/DI</th>
                                <th class="px-6 py-4 font-bold text-center">Status Laporan</th>
                                <th class="px-6 py-4 font-bold text-center">Nilai Akhir</th>
                                <th class="px-6 py-4 font-bold text-center">Aksi / Sertifikat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="s in siswa" :key="s.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900 dark:text-white">{{ s.nama_siswa }}</div>
                                    <div class="text-xs text-gray-500">{{ s.nis }}</div>
                                </td>
                                <td class="px-6 py-4 font-bold text-blue-600 text-sm">
                                    {{ s.nama_dudi }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span v-if="s.status_laporan == 'Disetujui'" class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold">ACC</span>
                                    <span v-else-if="s.status_laporan == 'Revisi'" class="px-3 py-1 bg-rose-100 text-rose-700 rounded-full text-xs font-bold">Revisi</span>
                                    <span v-else-if="s.status_laporan == 'Menunggu'" class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-bold">Review</span>
                                    <span v-else class="px-3 py-1 bg-gray-100 text-gray-500 rounded-full text-xs font-bold">Belum Ada</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div v-if="s.nilai_akhir" class="flex flex-col items-center">
                                        <div class="text-xl font-bold text-emerald-600">{{ s.nilai_akhir }}</div>
                                        <div class="text-[10px] font-bold text-emerald-800 bg-emerald-100 px-2 rounded-full mt-1">{{ s.predikat }}</div>
                                    </div>
                                    <span v-else class="text-xs text-gray-500">-</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center gap-2">
                                        <button @click="openModal(s)" class="px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg text-xs font-bold transition-colors">
                                            <i class="fas fa-edit"></i> {{ s.nilai_akhir ? 'Edit Nilai' : 'Input Nilai' }}
                                        </button>
                                        <a v-if="s.nilai_akhir" :href="route('guru.pkl.cetak_sertifikat', s.id)" target="_blank" class="px-3 py-1.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 rounded-lg text-xs font-bold transition-colors flex items-center gap-1">
                                            <i class="fas fa-print"></i> Sertifikat
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="siswa.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    Belum ada data siswa binaan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Form Input Nilai -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-700/50">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white">Input Nilai PKL</h3>
                    <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                </div>
                <form @submit.prevent="submitNilai" class="p-6">
                    <div class="mb-5 bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl text-sm border border-blue-100 dark:border-blue-800 text-center">
                        <div class="font-bold text-blue-900 dark:text-blue-400">{{ selectedSiswa.nama_siswa }}</div>
                        <div class="text-blue-700">{{ selectedSiswa.nama_dudi }}</div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Nilai Dari Industri (DU/DI) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" v-model="form.nilai_dudi" min="0" max="100" class="w-full rounded-xl border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500" required>
                            <p class="text-xs text-gray-500 mt-1">Sikap, Pengetahuan, & Keterampilan Teknis (0-100)</p>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Nilai Sekolah (Laporan & Jurnal) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" v-model="form.nilai_sekolah" min="0" max="100" class="w-full rounded-xl border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500" required>
                            <p class="text-xs text-gray-500 mt-1">Berdasarkan keaktifan jurnal dan kualitas laporan (0-100)</p>
                        </div>
                    </div>
                    
                    <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-700/50 p-3 rounded-xl mb-4">
                            <span class="font-bold text-gray-700 dark:text-gray-300">Estimasi Nilai Akhir:</span>
                            <span class="text-xl font-bold text-blue-600">{{ (form.nilai_dudi + form.nilai_sekolah) / 2 || 0 }}</span>
                        </div>
                        <div class="flex justify-end gap-3">
                            <button type="button" @click="isModalOpen = false" class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold">Batal</button>
                            <button type="submit" :disabled="form.processing" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold flex items-center gap-2 disabled:opacity-50">
                                <i v-if="form.processing" class="fas fa-spinner fa-spin"></i>
                                <i v-else class="fas fa-save"></i>
                                Simpan Nilai
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </DashboardLayout>
</template>
