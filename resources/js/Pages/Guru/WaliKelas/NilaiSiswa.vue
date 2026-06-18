<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    kelas: Object,
    siswa: Array,
    rapor_akhir: Object,
});

const isModalOpen = ref(false);
const selectedSiswa = ref(null);

const form = useForm({
    nilai: {}
});

const openEditModal = (s) => {
    selectedSiswa.value = s;
    
    // Initialize form with existing grades
    const grades = {};
    if (props.rapor_akhir[s.id]) {
        props.rapor_akhir[s.id].forEach(rapor => {
            grades[rapor.mapel_id] = {
                nama_mapel: rapor.mapel ? rapor.mapel.nama_mapel : 'Unknown Mapel',
                nilai_akhir: rapor.nilai_akhir || 0,
                deskripsi_tertinggi: rapor.deskripsi_tertinggi || '',
                deskripsi_terendah: rapor.deskripsi_terendah || '',
            };
        });
    }
    
    form.nilai = grades;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    selectedSiswa.value = null;
    form.reset();
};

const submit = () => {
    if (!selectedSiswa.value) return;
    
    form.post(route('guru.walikelas.nilai_siswa.update', selectedSiswa.value.id), {
        preserveScroll: true,
        onSuccess: () => closeModal()
    });
};
</script>

<template>
    <Head title="Edit Nilai Siswa" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-edit text-primary-500"></i>
                        Edit Nilai Siswa Binaan
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Kelas Perwalian: <span class="font-bold text-primary-600 dark:text-primary-400">{{ kelas?.nama_kelas || '-' }}</span>
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('guru.walikelas.index')" class="px-4 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors font-medium">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </Link>
                </div>
            </div>

            <!-- Flash Messages -->
            <div v-if="$page.props.flash?.success" class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
                <span class="font-medium">Sukses!</span> {{ $page.props.flash.success }}
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300 border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-4">No</th>
                                <th scope="col" class="px-6 py-4">NISN</th>
                                <th scope="col" class="px-6 py-4">Nama Siswa</th>
                                <th scope="col" class="px-6 py-4">Jml Mapel Dinilai</th>
                                <th scope="col" class="px-6 py-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(s, index) in siswa" :key="s.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4">{{ index + 1 }}</td>
                                <td class="px-6 py-4 font-medium">{{ s.nisn }}</td>
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ s.nama_lengkap }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium dark:bg-green-900 dark:text-green-300">
                                        {{ rapor_akhir[s.id] ? rapor_akhir[s.id].length : 0 }} Mapel
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button @click="openEditModal(s)" class="px-3 py-1.5 bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50 rounded-lg transition-colors font-medium text-xs flex items-center gap-1 mx-auto">
                                        <i class="fas fa-edit"></i> Edit Nilai
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="siswa.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">Belum ada data siswa di kelas ini.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm transition-opacity overflow-y-auto">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 w-full max-w-5xl overflow-hidden my-8">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center sticky top-0 bg-white dark:bg-gray-800 z-10">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Edit Nilai: {{ selectedSiswa?.nama_lengkap }}</h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 p-2">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="p-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div v-if="Object.keys(form.nilai).length === 0" class="text-center text-gray-500 py-8">
                            Belum ada nilai yang diinput oleh guru mata pelajaran untuk siswa ini.
                        </div>
                        
                        <div v-for="(data, mapel_id) in form.nilai" :key="mapel_id" class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-xl border border-gray-200 dark:border-gray-600">
                            <h4 class="font-bold text-primary-600 dark:text-primary-400 mb-3">{{ data.nama_mapel }}</h4>
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nilai Akhir</label>
                                    <input v-model="form.nilai[mapel_id].nilai_akhir" type="number" required class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                                <div class="md:col-span-5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Capaian Tertinggi</label>
                                    <textarea v-model="form.nilai[mapel_id].deskripsi_tertinggi" rows="3" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                                </div>
                                <div class="md:col-span-5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Capaian Terendah (Perlu Bimbingan)</label>
                                    <textarea v-model="form.nilai[mapel_id].deskripsi_terendah" rows="3" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3 bg-gray-50 dark:bg-gray-800">
                    <button type="button" @click="closeModal" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 font-medium transition-colors">
                        Batal
                    </button>
                    <button type="button" @click="submit" :disabled="form.processing || Object.keys(form.nilai).length === 0" class="px-5 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium transition-colors disabled:opacity-50 flex items-center gap-2 shadow-sm shadow-primary-500/30">
                        <i v-if="form.processing" class="fas fa-spinner fa-spin"></i>
                        <i v-else class="fas fa-save"></i>
                        Simpan Nilai
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
