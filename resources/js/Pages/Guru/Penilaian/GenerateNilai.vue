<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    mapel_list: Array,
    kelas_list: Array
});

const form = useForm({
    mapel_id: '',
    kelas_id: ''
});

const submit = () => {
    form.post(route('guru.penilaian.store_generate_nilai'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        }
    });
};
</script>

<template>
    <Head title="Generate Nilai Akhir" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-calculator text-blue-500"></i>
                        Generate Nilai Akhir
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Kalkulasi otomatis Nilai Akhir (Formatif + Sumatif) dan Deskripsi Capaian.
                    </p>
                </div>
            </div>

            <!-- Flash Messages -->
            <div v-if="$page.props.flash.success" class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
                <span class="font-medium">Sukses!</span> {{ $page.props.flash.success }}
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <form @submit.prevent="submit" class="max-w-2xl">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mata Pelajaran</label>
                            <select v-model="form.mapel_id" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <option value="">Pilih Mata Pelajaran...</option>
                                <option v-for="mapel in mapel_list" :key="mapel.id" :value="mapel.id">
                                    {{ mapel.nama_mapel }}
                                </option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kelas</label>
                            <select v-model="form.kelas_id" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <option value="">Pilih Kelas...</option>
                                <option v-for="kelas in kelas_list" :key="kelas.id" :value="kelas.id">
                                    {{ kelas.nama_kelas }}
                                </option>
                            </select>
                        </div>

                        <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-xl p-4 text-blue-800 dark:text-blue-300 text-sm flex gap-3">
                            <i class="fas fa-info-circle mt-0.5"></i>
                            <p>Proses ini akan mengambil seluruh nilai Formatif dan Sumatif yang sudah Anda input, kemudian menghitung rata-rata serta membuat deskripsi rapor secara otomatis. Proses ini mungkin memakan waktu beberapa detik.</p>
                        </div>

                        <button type="submit" :disabled="form.processing" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-xl text-sm px-5 py-2.5 text-center dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-blue-800 flex items-center gap-2">
                            <i v-if="form.processing" class="fas fa-spinner fa-spin"></i>
                            <i v-else class="fas fa-magic"></i>
                            Proses Generate Nilai Akhir
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>
