<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    siswa: Object,
    mapel: Array,
    nilai: Object,
    flash: Object
});

const form = useForm({
    siswa_id: props.siswa.id,
    nilai: {}
});

// Initialize form with existing grades or 0
props.mapel.forEach(m => {
    const existing = props.nilai[m.id] || {};
    form.nilai[m.id] = {
        s1: existing.s1 || 0,
        s2: existing.s2 || 0,
        s3: existing.s3 || 0,
        s4: existing.s4 || 0,
        s5: existing.s5 || 0,
        s6: existing.s6 || 0,
        nilai_us: existing.nilai_us || 0
    };
});

const submit = () => {
    form.post(route('admin.kelulusan.simpan_nilai'), {
        preserveScroll: true
    });
};

const hitungRataMapel = (mId) => {
    const n = form.nilai[mId];
    const s1 = parseFloat(n.s1) || 0;
    const s2 = parseFloat(n.s2) || 0;
    const s3 = parseFloat(n.s3) || 0;
    const s4 = parseFloat(n.s4) || 0;
    const s5 = parseFloat(n.s5) || 0;
    const s6 = parseFloat(n.s6) || 0;
    
    let total = s1 + s2 + s3 + s4 + s5 + s6;
    let pembagi = 0;
    if(s1 > 0) pembagi++;
    if(s2 > 0) pembagi++;
    if(s3 > 0) pembagi++;
    if(s4 > 0) pembagi++;
    if(s5 > 0) pembagi++;
    if(s6 > 0) pembagi++;

    return pembagi > 0 ? (total / pembagi).toFixed(2) : 0;
};
</script>

<template>
    <Head :title="`Input Nilai - ${siswa.nama_lengkap}`" />

    <DashboardLayout>
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Input Nilai Manual</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Nilai Semester 1-6 dan Ujian Sekolah</p>
            </div>
            <button @click="router.get(route('admin.kelulusan.nilai'))" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Kembali ke Rekap
            </button>
        </div>

        <div v-if="flash?.success" class="mb-4 p-4 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-xl border border-green-200 dark:border-green-800 flex items-center gap-3">
            <i class="fas fa-check-circle text-xl"></i>
            <span class="font-medium">{{ flash.success }}</span>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden mb-6 flex flex-col md:flex-row">
            <div class="p-6 md:w-1/3 bg-gray-50 dark:bg-gray-700/30 border-b md:border-b-0 md:border-r border-gray-100 dark:border-gray-700 flex flex-col items-center justify-center text-center">
                <div class="w-24 h-24 rounded-full bg-primary-100 dark:bg-primary-900/50 flex items-center justify-center mb-4">
                    <i class="fas fa-user-graduate text-4xl text-primary-600 dark:text-primary-400"></i>
                </div>
                <h3 class="font-bold text-xl text-gray-900 dark:text-white mb-1">{{ siswa.nama_lengkap }}</h3>
                <p class="text-sm font-medium text-primary-600 dark:text-primary-400 mb-2">{{ siswa.nisn || '-' }} / {{ siswa.nis }}</p>
                <span class="px-3 py-1 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-full text-xs font-bold">{{ siswa.kelas?.nama_kelas }}</span>
            </div>
            <div class="p-6 md:w-2/3 flex flex-col justify-center">
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 rounded-xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800">
                        <div class="text-xs text-gray-500 mb-1">Jurusan</div>
                        <div class="font-bold text-gray-900 dark:text-white">{{ siswa.kelas?.jurusan?.nama_jurusan || '-' }}</div>
                    </div>
                    <div class="p-4 rounded-xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800">
                        <div class="text-xs text-gray-500 mb-1">Tempat, Tgl Lahir</div>
                        <div class="font-bold text-gray-900 dark:text-white">{{ siswa.tempat_lahir || '-' }}, {{ siswa.tanggal_lahir || '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                <h3 class="font-bold text-gray-700 dark:text-gray-300">Form Input Nilai Mata Pelajaran</h3>
                <button @click="submit" :disabled="form.processing" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center gap-2">
                    <i class="fas fa-save" v-if="!form.processing"></i>
                    <i class="fas fa-spinner fa-spin" v-else></i>
                    Simpan Nilai
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-700/50 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3 font-medium">Mata Pelajaran</th>
                            <th class="px-2 py-3 font-medium text-center w-20">Smt 1</th>
                            <th class="px-2 py-3 font-medium text-center w-20">Smt 2</th>
                            <th class="px-2 py-3 font-medium text-center w-20">Smt 3</th>
                            <th class="px-2 py-3 font-medium text-center w-20">Smt 4</th>
                            <th class="px-2 py-3 font-medium text-center w-20">Smt 5</th>
                            <th class="px-2 py-3 font-medium text-center w-20">Smt 6</th>
                            <th class="px-2 py-3 font-medium text-center w-24 bg-blue-50 dark:bg-blue-900/20 text-blue-700">Rata2</th>
                            <th class="px-2 py-3 font-medium text-center w-24 bg-primary-50 dark:bg-primary-900/20 text-primary-700">Nilai US</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <tr v-for="m in mapel" :key="m.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-4 py-3">
                                <div class="font-bold text-gray-900 dark:text-white text-xs">{{ m.nama_mapel }}</div>
                                <div class="text-[10px] text-gray-500">{{ m.kelompok }}</div>
                            </td>
                            <td class="px-1 py-2 text-center">
                                <input v-model.number="form.nilai[m.id].s1" type="number" step="0.01" min="0" max="100" class="w-full text-center rounded text-xs border-gray-300 dark:border-gray-600 focus:border-primary-500 focus:ring-primary-500 px-1 py-1 h-8">
                            </td>
                            <td class="px-1 py-2 text-center">
                                <input v-model.number="form.nilai[m.id].s2" type="number" step="0.01" min="0" max="100" class="w-full text-center rounded text-xs border-gray-300 dark:border-gray-600 focus:border-primary-500 focus:ring-primary-500 px-1 py-1 h-8">
                            </td>
                            <td class="px-1 py-2 text-center">
                                <input v-model.number="form.nilai[m.id].s3" type="number" step="0.01" min="0" max="100" class="w-full text-center rounded text-xs border-gray-300 dark:border-gray-600 focus:border-primary-500 focus:ring-primary-500 px-1 py-1 h-8">
                            </td>
                            <td class="px-1 py-2 text-center">
                                <input v-model.number="form.nilai[m.id].s4" type="number" step="0.01" min="0" max="100" class="w-full text-center rounded text-xs border-gray-300 dark:border-gray-600 focus:border-primary-500 focus:ring-primary-500 px-1 py-1 h-8">
                            </td>
                            <td class="px-1 py-2 text-center">
                                <input v-model.number="form.nilai[m.id].s5" type="number" step="0.01" min="0" max="100" class="w-full text-center rounded text-xs border-gray-300 dark:border-gray-600 focus:border-primary-500 focus:ring-primary-500 px-1 py-1 h-8">
                            </td>
                            <td class="px-1 py-2 text-center">
                                <input v-model.number="form.nilai[m.id].s6" type="number" step="0.01" min="0" max="100" class="w-full text-center rounded text-xs border-gray-300 dark:border-gray-600 focus:border-primary-500 focus:ring-primary-500 px-1 py-1 h-8">
                            </td>
                            <td class="px-1 py-2 text-center bg-blue-50/50 dark:bg-blue-900/10">
                                <span class="font-bold text-blue-700 dark:text-blue-400">{{ hitungRataMapel(m.id) }}</span>
                            </td>
                            <td class="px-1 py-2 text-center bg-primary-50/50 dark:bg-primary-900/10">
                                <input v-model.number="form.nilai[m.id].nilai_us" type="number" step="0.01" min="0" max="100" class="w-full text-center rounded text-xs border-primary-300 dark:border-primary-600 focus:border-primary-500 focus:ring-primary-500 px-1 py-1 h-8 font-bold text-primary-700">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                <button @click="submit" :disabled="form.processing" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center gap-2">
                    <i class="fas fa-save" v-if="!form.processing"></i>
                    <i class="fas fa-spinner fa-spin" v-else></i>
                    Simpan Nilai
                </button>
            </div>
        </div>
    </DashboardLayout>
</template>
