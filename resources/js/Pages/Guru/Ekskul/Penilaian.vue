<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    ekskul: Object,
    penilaian: Array,
    total_pertemuan: Number
});

const saveNilai = (n) => {
    router.post(route('guru.ekskul.penilaian.simpan'), {
        id: n.id,
        nilai_huruf: n.nilai_huruf,
        deskripsi_dapodik: n.deskripsi_dapodik,
        layak_sertifikat: n.layak_sertifikat
    }, {
        preserveScroll: true
    });
};
</script>

<template>
    <Head :title="`Penilaian ${ekskul.nama_ekskul}`" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <Link :href="route('guru.ekskul.index')" class="text-indigo-600 hover:text-indigo-800 text-sm font-bold flex items-center gap-2 mb-2">
                        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                    </Link>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <i class="fas fa-star text-indigo-500"></i>
                        Penilaian: {{ ekskul.nama_ekskul }}
                    </h2>
                </div>
                <button class="px-5 py-2.5 bg-green-600 text-white rounded-xl hover:bg-green-700 font-bold shadow-sm flex items-center gap-2">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
            </div>

            <div v-if="$page.props.flash?.message" class="bg-green-50 text-green-800 p-4 rounded-2xl shadow-sm flex items-center gap-3">
                <i class="fas fa-check-circle"></i> {{ $page.props.flash.message }}
            </div>

            <!-- Total Pertemuan Info -->
            <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-4 flex gap-4 items-center">
                <div class="w-12 h-12 bg-indigo-600 text-white rounded-xl flex items-center justify-center font-bold text-xl shrink-0">
                    {{ total_pertemuan }}
                </div>
                <div>
                    <div class="font-bold text-indigo-900">Total Pertemuan/Jurnal</div>
                    <div class="text-sm text-indigo-700">Persentase kehadiran dihitung otomatis berdasarkan total pertemuan berjalan. Syarat minimal sertifikat adalah 75% kehadiran.</div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 font-bold uppercase text-xs sticky top-0 shadow-sm z-10">
                            <tr>
                                <th class="px-6 py-4">No</th>
                                <th class="px-6 py-4">Siswa</th>
                                <th class="px-6 py-4 text-center">Persentase Kehadiran</th>
                                <th class="px-6 py-4 text-center">Nilai Huruf</th>
                                <th class="px-6 py-4">Deskripsi Dapodik</th>
                                <th class="px-6 py-4 text-center">Sertifikat</th>
                                <th class="px-6 py-4 text-center">Simpan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="(n, index) in penilaian" :key="n.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4">{{ index + 1 }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900 dark:text-white whitespace-nowrap">{{ n.nama_lengkap }}</div>
                                    <div class="text-xs text-gray-500">{{ n.nama_kelas }} | {{ n.nisn }}</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="font-black text-lg" :class="n.persen_hadir >= 75 ? 'text-green-600' : 'text-red-500'">
                                        {{ n.persen_hadir }}%
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <select v-model="n.nilai_huruf" class="w-20 rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm font-bold text-center mx-auto block">
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="C">C</option>
                                        <option value="D">D</option>
                                    </select>
                                </td>
                                <td class="px-6 py-4">
                                    <textarea v-model="n.deskripsi_dapodik" rows="2" class="w-full min-w-[250px] rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-xs"></textarea>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <select v-model="n.layak_sertifikat" class="w-24 rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-xs font-bold text-center" :class="n.layak_sertifikat === 'Y' ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'">
                                            <option value="Y">Layak (Y)</option>
                                            <option value="N">Tidak (N)</option>
                                        </select>
                                        <a v-if="n.layak_sertifikat === 'Y'" :href="route('guru.ekskul.penilaian.cetak', n.id)" target="_blank" class="text-[10px] text-indigo-600 font-bold hover:underline">
                                            <i class="fas fa-print"></i> Cetak PDF
                                        </a>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button @click="saveNilai(n)" class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-colors flex items-center justify-center tooltip" data-tip="Simpan Nilai">
                                        <i class="fas fa-save"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </DashboardLayout>
</template>
