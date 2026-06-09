<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    mapel_list: Array,
    kelas_list: Array,
    siswa: Array,
    rapor_akhir: Object,
    tps: Array,
    detail_nilai: Object,
    filters: Object
});

const form = useForm({
    mapel_id: props.filters?.mapel_id || '',
    kelas_id: props.filters?.kelas_id || '',
    kkm: props.filters?.kkm || 70,
    batas_sangat_baik: props.filters?.batas_sangat_baik || 85
});

const submit = () => {
    form.post(route('guru.penilaian.store_generate_nilai'), {
        preserveScroll: true
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

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">KKM (Minimal Baik)</label>
                                <input type="number" v-model="form.kkm" required min="0" max="100" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Batas Sangat Baik</label>
                                <input type="number" v-model="form.batas_sangat_baik" required min="0" max="100" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            </div>
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
        <!-- Hasil Generate -->
        <div v-if="siswa && siswa.length > 0 && rapor_akhir" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 flex flex-col md:flex-row justify-between items-center gap-4">
                <h3 class="font-bold text-gray-900 dark:text-white">Hasil Rapor Akhir Kelas</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700/50 dark:text-gray-300">
                        <tr>
                            <th scope="col" rowspan="2" class="px-6 py-4 font-semibold w-16">No</th>
                            <th scope="col" rowspan="2" class="px-6 py-4 font-semibold w-32">NISN</th>
                            <th scope="col" rowspan="2" class="px-6 py-4 font-semibold min-w-[200px]">Nama Siswa</th>
                            
                            <!-- Formatif Group -->
                            <th v-if="tps && tps.length > 0" scope="col" :colspan="tps.length" class="px-6 py-2 text-center border-l border-b border-gray-200 dark:border-gray-600 bg-blue-50/50 dark:bg-blue-900/20">Nilai Formatif (Per TP)</th>
                            
                            <!-- Sumatif Group -->
                            <th scope="col" colspan="2" class="px-6 py-2 text-center border-l border-b border-gray-200 dark:border-gray-600 bg-purple-50/50 dark:bg-purple-900/20">Nilai Sumatif</th>
                            
                            <th scope="col" rowspan="2" class="px-6 py-4 font-semibold w-32 text-center border-l border-gray-200 dark:border-gray-600">Nilai Akhir</th>
                            <th scope="col" rowspan="2" class="px-6 py-4 font-semibold min-w-[250px] border-l border-gray-200 dark:border-gray-600">Deskripsi Capaian Tertinggi</th>
                            <th scope="col" rowspan="2" class="px-6 py-4 font-semibold min-w-[250px] border-l border-gray-200 dark:border-gray-600">Deskripsi Capaian Terendah</th>
                        </tr>
                        <tr>
                            <th v-for="tp in tps" :key="tp.id" scope="col" class="px-3 py-2 font-medium text-xs text-center border-l border-gray-200 dark:border-gray-600 bg-blue-50/30 dark:bg-blue-900/10 whitespace-nowrap" :title="tp.deskripsi">{{ tp.kode_tp }}</th>
                            
                            <th scope="col" class="px-3 py-2 font-medium text-xs text-center border-l border-gray-200 dark:border-gray-600 bg-purple-50/30 dark:bg-purple-900/10">SAS</th>
                            <th scope="col" class="px-3 py-2 font-medium text-xs text-center border-l border-gray-200 dark:border-gray-600 bg-purple-50/30 dark:bg-purple-900/10">STS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <tr v-for="(s, index) in siswa" :key="s.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ index + 1 }}</td>
                            <td class="px-6 py-4">{{ s.nisn }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ s.nama_lengkap }}</td>
                            
                            <!-- Formatif Data -->
                            <td v-for="tp in tps" :key="tp.id" class="px-3 py-4 text-center border-l border-gray-200 dark:border-gray-600">
                                <span :class="{'text-red-500 font-medium': detail_nilai[s.id]?.formatif?.[tp.id]?.nilai < form.kkm}">
                                    {{ detail_nilai[s.id]?.formatif?.[tp.id]?.nilai ?? '-' }}
                                </span>
                            </td>
                            
                            <!-- Sumatif Data -->
                            <td class="px-3 py-4 text-center border-l border-gray-200 dark:border-gray-600 font-medium text-purple-700 dark:text-purple-400">
                                {{ detail_nilai[s.id]?.sas ?? '-' }}
                            </td>
                            <td class="px-3 py-4 text-center border-l border-gray-200 dark:border-gray-600 font-medium text-purple-700 dark:text-purple-400">
                                {{ detail_nilai[s.id]?.sts ?? '-' }}
                            </td>

                            <td class="px-6 py-4 text-center border-l border-gray-200 dark:border-gray-600">
                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full font-bold text-blue-700 bg-blue-50 dark:bg-blue-900/30 dark:text-blue-400">
                                    {{ rapor_akhir[s.id]?.nilai_akhir ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 border-l border-gray-200 dark:border-gray-600 text-green-600 dark:text-green-400">
                                {{ rapor_akhir[s.id]?.deskripsi_tertinggi ?? '-' }}
                            </td>
                            <td class="px-6 py-4 border-l border-gray-200 dark:border-gray-600 text-red-600 dark:text-red-400">
                                {{ rapor_akhir[s.id]?.deskripsi_terendah ?? '-' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>
