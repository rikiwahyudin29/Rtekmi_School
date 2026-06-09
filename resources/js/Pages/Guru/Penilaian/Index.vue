<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    mapel: Array,
});

const formGenerate = useForm({
    mapel_id: '',
    kelas_id: ''
});

// Simulasi list kelas (Sebaiknya diambil dari backend berdasarkan pembagian tugas)
const dummyKelas = [
    { id: 1, nama_kelas: 'X RPL 1' },
    { id: 2, nama_kelas: 'X RPL 2' },
    { id: 3, nama_kelas: 'XI TKJ 1' }
];

const generateNilaiAkhir = () => {
    if (!formGenerate.mapel_id || !formGenerate.kelas_id) {
        alert('Pilih Mata Pelajaran dan Kelas terlebih dahulu.');
        return;
    }
    
    if (confirm('Proses ini akan mengkalkulasi ulang Nilai Akhir (Rapor) dan men-generate deskripsi capaian kompetensi siswa di kelas ini. Lanjutkan?')) {
        formGenerate.post(route('guru.penilaian.generate_nilai_akhir'), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Penilaian eRapor" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-chart-line text-primary-500"></i>
                        Penilaian eRapor (Kurikulum Merdeka)
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Kelola penilaian akademik, formatif, sumatif, dan nilai akhir rapor siswa.
                    </p>
                </div>
            </div>

            <!-- Flash Messages -->
            <div v-if="$page.props.flash.success" class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
                <span class="font-medium">Sukses!</span> {{ $page.props.flash.success }}
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- Menu Cards -->
                <Link :href="route('guru.penilaian.tp')" class="group relative bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="absolute right-0 top-0 w-24 h-24 bg-primary-500/10 dark:bg-primary-500/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                    <div class="w-14 h-14 bg-primary-50 dark:bg-gray-700 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-bullseye text-2xl text-primary-500"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Tujuan Pembelajaran</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Input dan kelola Tujuan Pembelajaran (TP) untuk setiap mata pelajaran.
                    </p>
                    <div class="mt-4 flex items-center text-primary-600 dark:text-primary-400 font-medium text-sm group-hover:translate-x-2 transition-transform">
                        Kelola TP <i class="fas fa-arrow-right ml-2"></i>
                    </div>
                </Link>

                <Link :href="route('guru.penilaian.formatif')" class="group relative bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="absolute right-0 top-0 w-24 h-24 bg-blue-500/10 dark:bg-blue-500/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                    <div class="w-14 h-14 bg-blue-50 dark:bg-gray-700 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-tasks text-2xl text-blue-500"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Nilai Formatif</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Input nilai capaian harian siswa berdasarkan Tujuan Pembelajaran.
                    </p>
                    <div class="mt-4 flex items-center text-blue-600 dark:text-blue-400 font-medium text-sm group-hover:translate-x-2 transition-transform">
                        Input Formatif <i class="fas fa-arrow-right ml-2"></i>
                    </div>
                </Link>

                <Link :href="route('guru.penilaian.sumatif')" class="group relative bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="absolute right-0 top-0 w-24 h-24 bg-purple-500/10 dark:bg-purple-500/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                    <div class="w-14 h-14 bg-purple-50 dark:bg-gray-700 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-file-signature text-2xl text-purple-500"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Nilai Sumatif</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Input nilai ujian akhir (SAS) dan tengah semester (STS).
                    </p>
                    <div class="mt-4 flex items-center text-purple-600 dark:text-purple-400 font-medium text-sm group-hover:translate-x-2 transition-transform">
                        Input Sumatif <i class="fas fa-arrow-right ml-2"></i>
                    </div>
                </Link>

            </div>

            <!-- Generate Nilai Akhir Section -->
            <div class="bg-gradient-to-br from-gray-900 to-gray-800 rounded-3xl shadow-lg p-1 sm:p-8 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-primary-500/20 rounded-full blur-3xl -mr-20 -mt-20"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-teal-500/20 rounded-full blur-3xl -ml-20 -mb-20"></div>
                
                <div class="relative z-10 p-6 sm:p-0">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-white mb-2">Generate Nilai Akhir & Deskripsi Capaian</h3>
                        <p class="text-gray-300">Kalkulasi otomatis nilai akhir rapor berdasarkan pembobotan nilai formatif dan sumatif, beserta pengisian otomatis deskripsi capaian tertinggi dan terendah siswa sesuai standar eRapor.</p>
                    </div>

                    <form @submit.prevent="generateNilaiAkhir" class="bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-6 grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Pilih Mata Pelajaran</label>
                            <select v-model="formGenerate.mapel_id" class="w-full rounded-xl border-gray-600 bg-gray-800/50 text-white shadow-sm focus:border-primary-400 focus:ring-primary-400 backdrop-blur-sm">
                                <option value="">-- Pilih Mapel --</option>
                                <option v-for="m in mapel" :key="m.id" :value="m.id">{{ m.nama_mapel }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-2">Pilih Kelas</label>
                            <select v-model="formGenerate.kelas_id" class="w-full rounded-xl border-gray-600 bg-gray-800/50 text-white shadow-sm focus:border-primary-400 focus:ring-primary-400 backdrop-blur-sm">
                                <option value="">-- Pilih Kelas --</option>
                                <option v-for="k in dummyKelas" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                            </select>
                        </div>
                        <div>
                            <button type="submit" :disabled="formGenerate.processing" class="w-full px-6 py-3 bg-gradient-to-r from-primary-500 to-teal-400 text-white rounded-xl hover:from-primary-600 hover:to-teal-500 font-bold shadow-lg shadow-primary-500/30 transition-all flex justify-center items-center gap-2">
                                <i class="fas fa-cogs" :class="{'animate-spin': formGenerate.processing}"></i> 
                                {{ formGenerate.processing ? 'Memproses...' : 'Generate Nilai Rapor' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </DashboardLayout>
</template>
