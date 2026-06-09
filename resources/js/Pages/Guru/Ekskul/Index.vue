<script setup>
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref, watch, onMounted } from 'vue';

const props = defineProps({
    ekskuls: Array,
    selected_ekskul_id: [String, Number],
    anggota: Array,
    nilai_existing: Array
});

const form = useForm({
    ekskul_id: props.selected_ekskul_id,
    nilai_data: []
});

onMounted(() => {
    initForm();
});

watch(() => props.anggota, () => {
    initForm();
});

const initForm = () => {
    const initData = [];
    if (props.anggota) {
        props.anggota.forEach(a => {
            const existing = props.nilai_existing.find(n => n.siswa_id === a.siswa_id);
            initData.push({
                siswa_id: a.siswa_id,
                nilai_huruf: existing ? existing.nilai_huruf : 'B',
                deskripsi: existing ? existing.deskripsi_dapodik : 'Melaksanakan kegiatan ekstrakurikuler dengan Baik.'
            });
        });
    }
    form.nilai_data = initData;
    form.ekskul_id = props.selected_ekskul_id;
};

const gantiEkskul = (e) => {
    router.get(route('guru.ekskul.index'), { ekskul_id: e.target.value }, {
        preserveState: true,
        preserveScroll: true
    });
};

const getNilaiModel = (siswa_id) => {
    return form.nilai_data.find(n => n.siswa_id === siswa_id);
};

const submitNilai = () => {
    form.post(route('guru.ekskul.store_nilai'));
};
</script>

<template>
    <Head title="Nilai Ekstrakurikuler" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-running text-orange-500"></i>
                        Penilaian Ekstrakurikuler
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Input nilai capaian siswa untuk ekstrakurikuler yang Anda bina.
                    </p>
                </div>
                <div class="flex gap-2">
                    <button @click="submitNilai" :disabled="form.processing || !anggota || anggota.length === 0" class="px-6 py-2 bg-orange-600 text-white rounded-xl hover:bg-orange-700 font-medium shadow-sm flex items-center gap-2 transition-colors disabled:opacity-50">
                        <i class="fas fa-save"></i> {{ form.processing ? 'Menyimpan...' : 'Simpan Nilai' }}
                    </button>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <div class="max-w-md w-full">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pilih Ekstrakurikuler</label>
                    <select v-model="form.ekskul_id" @change="gantiEkskul" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-orange-500 focus:ring-orange-500">
                        <option value="">-- Pilih Ekstrakurikuler --</option>
                        <option v-for="e in ekskuls" :key="e.id" :value="e.id">{{ e.nama_ekskul }}</option>
                    </select>
                </div>
            </div>

            <!-- Tabel Input Nilai -->
            <div v-if="anggota && anggota.length > 0" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                            <tr>
                                <th scope="col" class="px-6 py-4 w-16">No</th>
                                <th scope="col" class="px-6 py-4">Nama Siswa</th>
                                <th scope="col" class="px-6 py-4">Kelas</th>
                                <th scope="col" class="px-6 py-4 w-40 text-center">Nilai</th>
                                <th scope="col" class="px-6 py-4">Deskripsi Capaian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(a, index) in anggota" :key="a.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 align-top">{{ index + 1 }}</td>
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white align-top">{{ a.siswa?.nama_lengkap }}</td>
                                <td class="px-6 py-4 align-top">{{ a.siswa?.kelas?.nama_kelas || '-' }}</td>
                                <td class="px-6 py-4 text-center align-top">
                                    <select v-if="getNilaiModel(a.siswa_id)" v-model="getNilaiModel(a.siswa_id).nilai_huruf" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-orange-500 focus:border-orange-500 text-center font-bold">
                                        <option value="A">Sangat Baik</option>
                                        <option value="B">Baik</option>
                                        <option value="C">Cukup</option>
                                        <option value="D">Kurang</option>
                                    </select>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <textarea v-if="getNilaiModel(a.siswa_id)" v-model="getNilaiModel(a.siswa_id).deskripsi" rows="2" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-orange-500 focus:border-orange-500 placeholder-gray-400"></textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div v-else-if="form.ekskul_id" class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-2xl p-6 text-center text-yellow-800 dark:text-yellow-400">
                <i class="fas fa-info-circle text-3xl mb-3"></i>
                <p>Belum ada anggota siswa yang terdaftar di ekstrakurikuler ini.</p>
            </div>
            
            <div v-if="!ekskuls || ekskuls.length === 0" class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-2xl p-6 text-center text-red-800 dark:text-red-400">
                <i class="fas fa-exclamation-triangle text-3xl mb-3"></i>
                <p>Anda belum ditugaskan sebagai Pembina Ekstrakurikuler manapun.</p>
            </div>
        </div>
    </DashboardLayout>
</template>
