<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref, watch } from 'vue';

const props = defineProps({
    tp_list: Array,
    kelas_list: Array,
    siswa: Array,
    nilai_formatif: Object,
    filters: Object
});

const formFilter = useForm({
    kelas_id: props.filters.kelas_id || '',
    tp_id: props.filters.tp_id || '',
});

const filterData = () => {
    if (formFilter.kelas_id && formFilter.tp_id) {
        formFilter.get(route('guru.penilaian.formatif'), {
            preserveState: true,
            preserveScroll: true
        });
    }
};

// Form untuk submit nilai
const formNilai = useForm({
    tp_id: props.filters.tp_id || '',
    nilai: {}
});

// Inisialisasi nilai form jika data sudah ada
if (props.siswa && props.siswa.length > 0) {
    props.siswa.forEach(s => {
        formNilai.nilai[s.id] = props.nilai_formatif[s.id] ? props.nilai_formatif[s.id].nilai : null;
    });
}

const submitNilai = () => {
    formNilai.tp_id = formFilter.tp_id;
    formNilai.post(route('guru.penilaian.formatif.store'), {
        preserveScroll: true,
        onSuccess: () => {
            // Flash message ditangani oleh layout
        }
    });
};

// Excel Import Logic
const isImportModalOpen = ref(false);
const formImport = useForm({
    tp_id: '',
    file_excel: null,
});

const openImportModal = () => {
    formImport.tp_id = formFilter.tp_id;
    formImport.file_excel = null;
    isImportModalOpen.value = true;
};

const closeImportModal = () => {
    isImportModalOpen.value = false;
};

const submitImport = () => {
    formImport.post(route('guru.penilaian.formatif.import'), {
        preserveScroll: true,
        onSuccess: () => {
            closeImportModal();
            filterData();
        }
    });
};

const downloadTemplate = () => {
    if(!formFilter.tp_id || !formFilter.kelas_id) {
        alert('Pilih Kelas dan Tujuan Pembelajaran (TP) terlebih dahulu!');
        return;
    }
    window.location.href = route('guru.penilaian.formatif.template', {
        kelas_id: formFilter.kelas_id,
        tp_id: formFilter.tp_id
    });
};
</script>

<template>
    <Head title="Nilai Formatif" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-tasks text-primary-500"></i>
                        Input Nilai Formatif
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Masukkan nilai berdasarkan Tujuan Pembelajaran (TP) yang dicapai.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('guru.penilaian.index')" class="px-4 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors font-medium">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </Link>
                </div>
            </div>

            <!-- Flash Messages -->
            <div v-if="$page.props.flash.success" class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
                <span class="font-medium">Sukses!</span> {{ $page.props.flash.success }}
            </div>

            <!-- Filter Card -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <form @submit.prevent="filterData" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pilih Kelas</label>
                        <select v-model="formFilter.kelas_id" @change="filterData" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="">-- Pilih Kelas --</option>
                            <option v-for="k in kelas_list" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pilih Tujuan Pembelajaran (TP)</label>
                        <select v-model="formFilter.tp_id" @change="filterData" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="">-- Pilih TP --</option>
                            <option v-for="tp in tp_list" :key="tp.id" :value="tp.id">{{ tp.kode_tp }} - {{ tp.mapel?.nama_mapel }}</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full md:w-auto px-6 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium transition-colors shadow-sm">
                            <i class="fas fa-search mr-2"></i> Tampilkan Siswa
                        </button>
                    </div>
                </form>
            </div>

            <!-- Input Area -->
            <div v-if="siswa && siswa.length > 0" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <form @submit.prevent="submitNilai">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 flex justify-between items-center">
                        <h3 class="font-bold text-gray-900 dark:text-white">Form Input Nilai: {{ tp_list.find(t => t.id == formFilter.tp_id)?.kode_tp }}</h3>
                        <div class="flex items-center gap-3">
                            <button type="button" @click="downloadTemplate" class="px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 font-medium transition-colors text-sm flex items-center gap-2">
                                <i class="fas fa-file-excel"></i> Download Template
                            </button>
                            <button type="button" @click="openImportModal" class="px-4 py-2 bg-yellow-500 text-white rounded-xl hover:bg-yellow-600 font-medium transition-colors text-sm flex items-center gap-2">
                                <i class="fas fa-upload"></i> Import Nilai
                            </button>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-300">
                                <tr>
                                    <th scope="col" class="px-6 py-4 w-16">No</th>
                                    <th scope="col" class="px-6 py-4 w-32">NISN</th>
                                    <th scope="col" class="px-6 py-4">Nama Siswa</th>
                                    <th scope="col" class="px-6 py-4 w-48 text-center">Nilai Formatif</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(s, index) in siswa" :key="s.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-6 py-4">{{ index + 1 }}</td>
                                    <td class="px-6 py-4 font-medium">{{ s.nisn }}</td>
                                    <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ s.nama_lengkap }}</td>
                                    <td class="px-6 py-4">
                                        <input v-model="formNilai.nilai[s.id]" type="number" min="0" max="100" step="0.01" class="w-full text-center rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="0">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="p-6 bg-gray-50 dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                        <button type="submit" :disabled="formNilai.processing" class="px-6 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-bold transition-colors shadow-sm shadow-primary-500/30 flex items-center gap-2">
                            <i class="fas fa-save"></i> 
                            {{ formNilai.processing ? 'Menyimpan...' : 'Simpan Nilai Formatif' }}
                        </button>
                    </div>
                </form>
            </div>
            
            <div v-else-if="formFilter.kelas_id && formFilter.tp_id" class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-2xl p-6 text-center text-yellow-800 dark:text-yellow-400">
                <i class="fas fa-exclamation-circle text-3xl mb-3"></i>
                <p>Data siswa tidak ditemukan di kelas ini atau kelas belum memiliki siswa aktif.</p>
            </div>
        </div>

        <!-- Modal Import -->
        <div v-if="isImportModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm transition-opacity">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 w-full max-w-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Import Nilai Formatif (.xlsx)</h3>
                    <button @click="closeImportModal" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form @submit.prevent="submitImport" class="p-6 space-y-4">
                    <div class="bg-blue-50 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 p-4 rounded-xl text-sm mb-4">
                        <i class="fas fa-info-circle mr-2"></i> Pastikan Anda mengunggah file hasil dari <b>Download Template</b>. Jangan merubah kolom ID SISWA.
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pilih File Excel</label>
                        <input type="file" @input="formImport.file_excel = $event.target.files[0]" accept=".xlsx,.xls" required class="block w-full text-sm text-gray-900 border border-gray-300 rounded-xl cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400">
                    </div>
                    <div class="pt-4 flex justify-end gap-3">
                        <button type="button" @click="closeImportModal" class="px-4 py-2 bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 font-medium transition-colors">
                            Batal
                        </button>
                        <button type="submit" :disabled="formImport.processing" class="px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 font-medium transition-colors flex items-center gap-2">
                            <i v-if="formImport.processing" class="fas fa-spinner fa-spin"></i>
                            <i v-else class="fas fa-upload"></i> Import Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>
