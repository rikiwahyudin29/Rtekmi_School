<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    bankSoal: Object,
    mapel: Array,
    guru: Array,
    filters: Object,
});

const searchFilter = ref({
    search: props.filters?.search || '',
    id_mapel: props.filters?.id_mapel || '',
    id_guru: props.filters?.id_guru || ''
});

// Debounce search
let searchTimeout;
watch(searchFilter, (newFilters) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/admin/cbt/bank-soal', newFilters, {
            preserveState: true,
            preserveScroll: true,
            replace: true
        });
    }, 300);
}, { deep: true });

const openModal = ref(false);
const isEdit = ref(false);
const selectedItem = ref(null);

const showImportModal = ref(false);
const activeBankSoal = ref(null);
const importForm = useForm({
    file_excel: null,
    file_word: null,
    type: 'excel' // excel or word
});

const openImport = (item) => {
    activeBankSoal.value = item;
    importForm.reset();
    importForm.clearErrors();
    importForm.type = 'excel';
    showImportModal.value = true;
};

const submitImport = () => {
    if (importForm.type === 'excel') {
        importForm.post(`/admin/cbt/bank-soal/${activeBankSoal.value.id}/import-excel`, {
            preserveScroll: true,
            onSuccess: () => { showImportModal.value = false; }
        });
    } else {
        importForm.post(`/admin/cbt/bank-soal/${activeBankSoal.value.id}/import-word`, {
            preserveScroll: true,
            onSuccess: () => { showImportModal.value = false; }
        });
    }
};

const form = useForm({
    kode: '',
    mapel_id: '',
});

const openCreateModal = () => {
    isEdit.value = false;
    selectedItem.value = null;
    form.reset();
    openModal.value = true;
};

const openEditModal = (item) => {
    isEdit.value = true;
    selectedItem.value = item;
    form.kode = item.kode;
    form.mapel_id = item.mapel_id;
    openModal.value = true;
};

const submitForm = () => {
    if (isEdit.value) {
        form.put(`/admin/cbt/bank-soal/${selectedItem.value.id}`, {
            preserveScroll: true,
            onSuccess: () => { openModal.value = false; },
        });
    } else {
        form.post('/admin/cbt/bank-soal', {
            preserveScroll: true,
            onSuccess: () => { openModal.value = false; form.reset(); },
        });
    }
};

const hapus = (id) => {
    if (confirm('Yakin ingin menghapus Bank Soal ini? Semua soal di dalamnya akan terhapus secara permanen.')) {
        router.delete(`/admin/cbt/bank-soal/${id}`);
    }
};
</script>

<template>
    <Head title="Bank Soal (CBT)" />

    <DashboardLayout>
        <div class="flex flex-col h-full">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full py-6">
                
                <!-- Header Card (Sleek Theme Gradient & Shadows) -->
                <div class="bg-gradient-to-r from-primary-600 via-primary-500 to-primary-700 rounded-3xl p-6 md:p-8 shadow-xl shadow-primary-500/10 mb-8 border border-white/10 text-white relative overflow-hidden transition-all duration-300">
                    <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="absolute -left-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
                    
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
                        <div>
                            <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">CBT Module</span>
                            <h2 class="font-extrabold text-3xl tracking-tight mt-2 flex items-center gap-3">
                                <i class="fas fa-database"></i> Bank Soal Ujian
                            </h2>
                            <p class="text-white/80 text-sm mt-1 font-medium">
                                Kelola wadah pertanyaan dan kunci jawaban berdasarkan mata pelajaran
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <a href="/admin/cbt/bank-soal/template-excel" target="_blank" class="bg-white/20 hover:bg-white/30 text-white font-bold py-3 px-4 rounded-2xl text-sm transition-all shadow-md flex items-center gap-2 transform hover:-translate-y-0.5">
                                <i class="fas fa-file-excel"></i> Template Excel
                            </a>
                            <a href="/admin/cbt/bank-soal/template-word" target="_blank" class="bg-white/20 hover:bg-white/30 text-white font-bold py-3 px-4 rounded-2xl text-sm transition-all shadow-md flex items-center gap-2 transform hover:-translate-y-0.5">
                                <i class="fas fa-file-word"></i> Template Word
                            </a>
                            <button @click="openCreateModal" class="bg-white text-primary-700 hover:bg-gray-100 font-bold py-3 px-6 rounded-2xl text-sm transition-all shadow-md flex items-center gap-2 transform hover:-translate-y-0.5">
                                <i class="fas fa-plus"></i> Tambah Bank Soal
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sleek Glassmorphism Filters -->
                <div class="bg-white dark:bg-gray-800 p-4 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700/60 mb-6 flex flex-wrap items-center gap-4 transition-all">
                    <div class="flex-1 min-w-[240px]">
                        <div class="relative">
                            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="text" v-model="searchFilter.search" placeholder="Cari Kode atau Nama Mapel..." class="w-full pl-11 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border-none rounded-2xl focus:ring-2 focus:ring-primary-500/20 focus:bg-white dark:focus:bg-gray-950 transition-all shadow-inner text-sm text-gray-800 dark:text-gray-200">
                        </div>
                    </div>
                    <div class="w-full sm:w-auto min-w-[200px]">
                        <select v-model="searchFilter.id_mapel" class="w-full py-3 bg-gray-50 dark:bg-gray-900 border-none rounded-2xl focus:ring-2 focus:ring-primary-500/20 focus:bg-white dark:focus:bg-gray-950 transition-all text-sm text-gray-800 dark:text-gray-200 shadow-inner">
                            <option value="">-- Semua Mata Pelajaran --</option>
                            <option v-for="m in mapel" :key="m.id" :value="m.id">{{ m.nama_mapel }}</option>
                        </select>
                    </div>
                    <div class="w-full sm:w-auto min-w-[200px]">
                        <select v-model="searchFilter.id_guru" class="w-full py-3 bg-gray-50 dark:bg-gray-900 border-none rounded-2xl focus:ring-2 focus:ring-primary-500/20 focus:bg-white dark:focus:bg-gray-950 transition-all text-sm text-gray-800 dark:text-gray-200 shadow-inner">
                            <option value="">-- Dibuat Oleh --</option>
                            <option v-for="g in guru" :key="g.id" :value="g.id">{{ g.nama_lengkap }}</option>
                        </select>
                    </div>
                </div>

                <!-- Flash Messages -->
                <div v-if="$page.props.flash?.message" class="bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900 text-emerald-800 dark:text-emerald-400 p-4 mb-6 rounded-2xl shadow-sm flex items-center gap-3">
                    <i class="fas fa-check-circle text-lg"></i> {{ $page.props.flash.message }}
                </div>
                <div v-if="$page.props.flash?.error" class="bg-rose-50 dark:bg-rose-955/20 border border-rose-100 dark:border-rose-900 text-rose-800 dark:text-rose-400 p-4 mb-6 rounded-2xl shadow-sm flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-lg"></i> {{ $page.props.flash.error }}
                </div>

                <!-- Table Container -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-3xl border border-gray-100 dark:border-gray-700/60 mb-6 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/80 dark:bg-gray-700/30 text-gray-600 dark:text-gray-300 text-xs font-bold uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                                    <th class="px-6 py-4 font-bold">Kode Bank Soal</th>
                                    <th class="px-6 py-4 font-bold">Mata Pelajaran</th>
                                    <th class="px-6 py-4 font-bold">Dibuat Oleh</th>
                                    <th class="px-6 py-4 font-bold text-center">Jumlah Soal</th>
                                    <th class="px-6 py-4 font-bold text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                                <tr v-for="item in bankSoal.data" :key="item.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900 dark:text-white uppercase tracking-wider text-sm">{{ item.kode }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        {{ item.mapel?.nama_mapel ?? item.nama_mapel }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                        <span class="inline-flex items-center gap-1.5 bg-gray-100 dark:bg-gray-900 px-3 py-1 rounded-full text-xs font-medium text-gray-700 dark:text-gray-300 font-semibold">
                                            <i class="fas fa-user-circle text-gray-400"></i>
                                            {{ item.creator?.guru?.nama_lengkap ?? item.creator?.nama_lengkap ?? 'Administrator' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="inline-flex items-center justify-center bg-primary-50 dark:bg-primary-950/40 text-primary-700 dark:text-primary-400 font-bold px-3 py-1.5 rounded-2xl text-xs">
                                            <i class="fas fa-list-ol mr-1.5"></i> {{ item.jumlah_soal }} Soal
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <Link :href="`/admin/cbt/bank-soal/${item.id}/soal`" class="w-9 h-9 rounded-2xl bg-primary-50 text-primary-600 hover:bg-primary-100 dark:bg-primary-900/30 dark:text-primary-400 dark:hover:bg-primary-900/50 flex items-center justify-center transition-colors shadow-sm animate-pulse-once" title="Kelola Butir Soal">
                                                <i class="fas fa-edit"></i>
                                            </Link>
                                            <a :href="`/admin/cbt/bank-soal/${item.id}/export`" target="_blank" class="w-9 h-9 rounded-2xl bg-indigo-50 text-indigo-600 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-400 dark:hover:bg-indigo-900/50 flex items-center justify-center transition-colors shadow-sm" title="Export Soal (Excel/Zip)">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <button @click="openImport(item)" class="w-9 h-9 rounded-2xl bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400 dark:hover:bg-emerald-900/50 flex items-center justify-center transition-colors shadow-sm" title="Import Soal">
                                                <i class="fas fa-file-import"></i>
                                            </button>
                                            <button @click="openEditModal(item)" class="w-9 h-9 rounded-2xl bg-amber-50 text-amber-600 hover:bg-amber-100 dark:bg-amber-900/30 dark:text-amber-400 dark:hover:bg-amber-900/50 flex items-center justify-center transition-colors shadow-sm" title="Edit Metadata">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                            <button @click="hapus(item.id)" class="w-9 h-9 rounded-2xl bg-rose-50 text-rose-600 hover:bg-rose-100 dark:bg-rose-900/30 dark:text-rose-400 dark:hover:bg-rose-900/50 flex items-center justify-center transition-colors shadow-sm" title="Hapus Bank Soal">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="bankSoal.data.length === 0">
                                    <td colspan="5" class="px-6 py-16 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-900 rounded-full flex items-center justify-center text-gray-400 dark:text-gray-600 text-2xl mb-4">
                                                <i class="fas fa-folder-open"></i>
                                            </div>
                                            <p class="text-base font-semibold text-gray-700 dark:text-gray-300">Data Bank Soal Kosong</p>
                                            <p class="text-xs text-gray-500 mt-1">Gunakan tombol 'Tambah' untuk membuat baru.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                        <Pagination :links="bankSoal.links" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Modal (Sleek Glassmorphism Popup) -->
        <div v-if="openModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/40 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh] border border-gray-100 dark:border-gray-700 transition-all">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gradient-to-r from-primary-600 to-primary-700 text-white">
                    <h3 class="font-extrabold text-lg flex items-center gap-2">
                        <i :class="isEdit ? 'fas fa-pen' : 'fas fa-plus-circle'"></i>
                        {{ isEdit ? 'Edit Bank Soal' : 'Buat Bank Soal Baru' }}
                    </h3>
                    <button @click="openModal = false" class="text-white/80 hover:text-white transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="p-6 overflow-y-auto">
                    <form @submit.prevent="submitForm" class="space-y-5">
                        <!-- Kode Bank -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Kode Bank Soal</label>
                            <input type="text" v-model="form.kode" placeholder="Contoh: BIND-10-GASAL" class="w-full border-none bg-gray-50 dark:bg-gray-900 rounded-2xl py-3 px-4 focus:ring-2 focus:ring-primary-500/20 shadow-inner text-sm text-gray-800 dark:text-gray-200" required autocomplete="off" style="text-transform: uppercase;">
                            <p class="text-xs text-rose-500 mt-1" v-if="form.errors.kode">{{ form.errors.kode }}</p>
                        </div>
                        
                        <!-- Mapel -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Mata Pelajaran</label>
                            <select v-model="form.mapel_id" class="w-full border-none bg-gray-50 dark:bg-gray-900 rounded-2xl py-3 px-4 focus:ring-2 focus:ring-primary-500/20 shadow-inner text-sm text-gray-800 dark:text-gray-200" required>
                                <option value="">Pilih Mapel...</option>
                                <option v-for="m in mapel" :key="m.id" :value="m.id">{{ m.nama_mapel }}</option>
                            </select>
                            <p class="text-xs text-rose-500 mt-1" v-if="form.errors.mapel_id">{{ form.errors.mapel_id }}</p>
                        </div>

                        <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-gray-100 dark:border-gray-700">
                            <button type="button" @click="openModal = false" class="px-5 py-3 rounded-2xl bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-300 font-bold hover:bg-gray-200 dark:hover:bg-gray-950 transition-colors text-sm">
                                Batal
                            </button>
                            <button type="submit" :disabled="form.processing" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-3 px-6 rounded-2xl shadow-lg shadow-primary-500/20 transition-all disabled:opacity-50 text-sm flex items-center gap-2">
                                <i class="fas fa-spinner fa-spin" v-if="form.processing"></i>
                                <i class="fas fa-save" v-else></i>
                                Simpan Bank Soal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Import Form Modal (Sleek Glassmorphism Popup) -->
        <div v-if="showImportModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/40 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh] border border-gray-100 dark:border-gray-700 transition-all">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gradient-to-r from-emerald-600 to-emerald-700 text-white">
                    <h3 class="font-extrabold text-lg flex items-center gap-2">
                        <i class="fas fa-file-import"></i>
                        Import Soal - {{ activeBankSoal?.kode }}
                    </h3>
                    <button @click="showImportModal = false" class="text-white/80 hover:text-white transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="p-6 overflow-y-auto">
                    <form @submit.prevent="submitImport" class="space-y-5">
                        
                        <!-- Pilihan Tipe Import -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-3">Tipe Import</label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer bg-gray-50 dark:bg-gray-900 px-4 py-3 rounded-2xl border border-transparent has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 dark:has-[:checked]:bg-emerald-900/20 transition-all w-1/2">
                                    <input type="radio" v-model="importForm.type" value="excel" class="text-emerald-500 focus:ring-emerald-500">
                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200"><i class="fas fa-file-excel text-emerald-500 mr-1"></i> File Excel</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer bg-gray-50 dark:bg-gray-900 px-4 py-3 rounded-2xl border border-transparent has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50 dark:has-[:checked]:bg-blue-900/20 transition-all w-1/2">
                                    <input type="radio" v-model="importForm.type" value="word" class="text-blue-500 focus:ring-blue-500">
                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200"><i class="fas fa-file-word text-blue-500 mr-1"></i> File Word</span>
                                </label>
                            </div>
                        </div>

                        <!-- Upload Excel -->
                        <div v-if="importForm.type === 'excel'">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Pilih File Excel (.xlsx / .xls)</label>
                            <input type="file" @input="importForm.file_excel = $event.target.files[0]" accept=".xlsx,.xls" class="w-full border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 rounded-2xl py-2 px-3 text-sm focus:ring-2 focus:ring-emerald-500/20 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 transition-all" required>
                            <p class="text-xs text-rose-500 mt-1" v-if="importForm.errors.file_excel">{{ importForm.errors.file_excel }}</p>
                            <p class="text-xs text-gray-500 mt-2"><i class="fas fa-info-circle"></i> Pastikan format kolom sesuai dengan template Excel yang disediakan.</p>
                        </div>

                        <!-- Upload Word -->
                        <div v-if="importForm.type === 'word'">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Pilih File Word (.docx)</label>
                            <input type="file" @input="importForm.file_word = $event.target.files[0]" accept=".docx" class="w-full border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 rounded-2xl py-2 px-3 text-sm focus:ring-2 focus:ring-blue-500/20 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all" required>
                            <p class="text-xs text-rose-500 mt-1" v-if="importForm.errors.file_word">{{ importForm.errors.file_word }}</p>
                            <p class="text-xs text-gray-500 mt-2"><i class="fas fa-info-circle"></i> Gunakan Q-Format (.docx) agar dapat mengimport soal yang mengandung gambar.</p>
                        </div>

                        <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-gray-100 dark:border-gray-700">
                            <button type="button" @click="showImportModal = false" class="px-5 py-3 rounded-2xl bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-300 font-bold hover:bg-gray-200 dark:hover:bg-gray-950 transition-colors text-sm">
                                Batal
                            </button>
                            <button type="submit" :disabled="importForm.processing" :class="importForm.type === 'excel' ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-500/20' : 'bg-blue-600 hover:bg-blue-700 shadow-blue-500/20'" class="text-white font-bold py-3 px-6 rounded-2xl shadow-lg transition-all disabled:opacity-50 text-sm flex items-center gap-2">
                                <i class="fas fa-spinner fa-spin" v-if="importForm.processing"></i>
                                <i class="fas fa-upload" v-else></i>
                                Proses Import
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
