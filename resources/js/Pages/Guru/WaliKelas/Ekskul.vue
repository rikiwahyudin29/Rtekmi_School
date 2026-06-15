<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { onMounted } from 'vue';

const props = defineProps({
    kelas: Object,
    siswa: Array,
    ekskul_list: Array,
    ekskul_nilai: Object
});

const form = useForm({
    input_data: {}
});

onMounted(() => {
    // Initialize form with existing data or empty array
    props.siswa.forEach(s => {
        if (props.ekskul_nilai && props.ekskul_nilai[s.id]) {
            form.input_data[s.id] = props.ekskul_nilai[s.id].map(e => ({
                ekskul_id: e.ekskul_id,
                nilai_huruf: e.nilai_huruf,
                deskripsi_dapodik: e.deskripsi_dapodik || ''
            }));
        } else {
            form.input_data[s.id] = [];
        }
    });
});

const addEkskul = (siswaId) => {
    if (!form.input_data[siswaId]) {
        form.input_data[siswaId] = [];
    }
    form.input_data[siswaId].push({
        ekskul_id: '',
        nilai_huruf: 'B',
        deskripsi_dapodik: 'Sangat Baik'
    });
};

const removeEkskul = (siswaId, index) => {
    form.input_data[siswaId].splice(index, 1);
};

const submit = () => {
    form.post(route('guru.walikelas.ekskul.store'), {
        preserveScroll: true
    });
};
</script>

<template>
    <Head title="Data Ekstrakurikuler" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-futbol text-primary-500"></i>
                        Input Nilai Ekstrakurikuler
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Kelas Perwalian: <span class="font-bold text-primary-600 dark:text-primary-400">{{ kelas?.nama_kelas || '-' }}</span>
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('guru.walikelas.index')" class="px-4 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors font-medium">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </Link>
                    <button @click="submit" :disabled="form.processing" class="px-5 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium transition-colors disabled:opacity-50 flex items-center gap-2 shadow-sm shadow-primary-500/30">
                        <i v-if="form.processing" class="fas fa-spinner fa-spin"></i>
                        <i v-else class="fas fa-save"></i>
                        Simpan Data
                    </button>
                </div>
            </div>

            <!-- Flash Messages -->
            <div v-if="$page.props.flash?.success" class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
                <span class="font-medium">Sukses!</span> {{ $page.props.flash.success }}
            </div>

            <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-xl p-4 flex items-start gap-3">
                <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
                <div class="text-sm text-blue-800 dark:text-blue-300">
                    <p>Silakan klik <strong>"Tambah Ekskul"</strong> jika siswa tersebut mengikuti kegiatan ekstrakurikuler. Siswa bisa memiliki lebih dari 1 ekskul.</p>
                </div>
            </div>

            <!-- Table Form -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300 border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-4 w-12 text-center">No</th>
                                <th scope="col" class="px-6 py-4 w-1/4">Nama Siswa</th>
                                <th scope="col" class="px-6 py-4">Data Ekstrakurikuler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(s, index) in siswa" :key="s.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 align-top">
                                <td class="px-6 py-4 text-center">{{ index + 1 }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900 dark:text-white">{{ s.nama_lengkap }}</div>
                                    <div class="text-xs text-gray-500 mt-1">{{ s.nisn }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-3">
                                        <div v-for="(e, eIdx) in form.input_data[s.id]" :key="eIdx" class="flex gap-2 items-start bg-gray-50 dark:bg-gray-700/50 p-3 rounded-xl border border-gray-100 dark:border-gray-600">
                                            <div class="flex-1 space-y-2">
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Ekstrakurikuler</label>
                                                        <select v-model="e.ekskul_id" required class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 p-2">
                                                            <option value="">Pilih Ekskul...</option>
                                                            <option v-for="ex in ekskul_list" :key="ex.id" :value="ex.id">{{ ex.nama_ekskul }}</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Nilai</label>
                                                        <select v-model="e.nilai_huruf" required class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 p-2">
                                                            <option value="A">A - Sangat Baik</option>
                                                            <option value="B">B - Baik</option>
                                                            <option value="C">C - Cukup</option>
                                                            <option value="D">D - Kurang</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi / Keterangan</label>
                                                    <input v-model="e.deskripsi_dapodik" type="text" placeholder="Catatan ekskul..." class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 p-2">
                                                </div>
                                            </div>
                                            <button type="button" @click="removeEkskul(s.id, eIdx)" class="text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/30 p-2 rounded-lg transition-colors mt-6" title="Hapus Ekskul">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                        
                                        <button type="button" @click="addEkskul(s.id)" class="px-3 py-1.5 text-xs font-medium text-primary-600 bg-primary-50 hover:bg-primary-100 dark:text-primary-400 dark:bg-primary-900/30 dark:hover:bg-primary-900/50 rounded-lg transition-colors flex items-center gap-1">
                                            <i class="fas fa-plus"></i> Tambah Ekskul
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="siswa.length === 0">
                                <td colspan="3" class="px-6 py-8 text-center text-gray-500">Belum ada data siswa di kelas ini.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="flex justify-end pt-4">
                <button @click="submit" :disabled="form.processing" class="px-6 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-bold transition-colors disabled:opacity-50 flex items-center gap-2 shadow-lg shadow-primary-500/30">
                    <i v-if="form.processing" class="fas fa-spinner fa-spin"></i>
                    <i v-else class="fas fa-save"></i>
                    Simpan Semua Data
                </button>
            </div>
        </div>
    </DashboardLayout>
</template>
