<script setup>
import { ref, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    mapel: Object,
    jurusans: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const perPage = ref(props.filters.per_page || 10);
const kelompokFilter = ref(props.filters.kelompok || '');

const showModal = ref(false);
const showImportModal = ref(false);
const isEditing = ref(false);

const form = useForm({
    id: null,
    kode_mapel: '',
    nama_mapel: '',
    kelompok: '',
    jurusan_id: '0',
    tampil_raport: true,
    tampil_skl: true,
    tampil_transkrip: true,
});

const openModal = (mpl = null) => {
    isEditing.value = !!mpl;
    if (mpl) {
        form.id = mpl.id;
        form.kode_mapel = mpl.kode_mapel || '';
        form.nama_mapel = mpl.nama_mapel;
        form.kelompok = mpl.kelompok || '';
        form.jurusan_id = mpl.jurusan_id || '0';
        form.tampil_raport = !!mpl.tampil_raport;
        form.tampil_skl = !!mpl.tampil_skl;
        form.tampil_transkrip = !!mpl.tampil_transkrip;
    } else {
        form.reset();
        form.id = null;
        form.jurusan_id = '0';
        form.tampil_raport = true;
        form.tampil_skl = true;
        form.tampil_transkrip = true;
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    form.clearErrors();
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('admin.master.mapel.update', form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.master.mapel.store'), {
            onSuccess: () => closeModal(),
        });
    }
    }
};

const importForm = useForm({
    file: null,
});

const openImportModal = () => {
    importForm.reset();
    importForm.clearErrors();
    showImportModal.value = true;
};

const closeImportModal = () => {
    showImportModal.value = false;
    importForm.reset();
    importForm.clearErrors();
};

const submitImport = () => {
    importForm.post(route('admin.master.mapel.import'), {
        onSuccess: () => {
            closeImportModal();
            importForm.reset();
        },
    });
};

const deleteData = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus Mata Pelajaran ini?')) {
        router.delete(route('admin.master.mapel.destroy', id));
    }
};

let searchTimeout;
watch([search, perPage, kelompokFilter], ([newSearch, newPerPage, newKelompokFilter]) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            route('admin.master.mapel.index'),
            { search: newSearch, per_page: newPerPage, kelompok: newKelompokFilter },
            { preserveState: true, replace: true }
        );
    }, 300);
});
</script>

<template>
    <Head title="Master Mata Pelajaran" />

    <DashboardLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col h-[calc(100vh-10rem)]">
                
                <!-- Header -->
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 shrink-0 gap-4">
                    <div>
                        <h2 class="font-bold text-2xl text-gray-900 dark:text-white tracking-tight">Mata Pelajaran</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kelola data mata pelajaran beserta kelompok dan pengaturannya.</p>
                    </div>
                    <div class="flex gap-2">
                        <button @click="openImportModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-5 rounded-xl shadow-sm transition-all flex items-center gap-2">
                            <i class="fas fa-file-excel"></i> Import Data
                        </button>
                        <button @click="openModal()" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2.5 px-5 rounded-xl shadow-sm transition-all flex items-center gap-2">
                            <i class="fas fa-plus"></i> Tambah Data
                        </button>
                    </div>
                </div>

                <div v-if="$page.props.flash?.message" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-400 p-4 mb-6 rounded-2xl shadow-sm flex items-center gap-3 shrink-0">
                    <i class="fas fa-check-circle"></i> <span v-html="$page.props.flash.message"></span>
                </div>
                
                <div v-if="$page.props.flash?.error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-400 p-4 mb-6 rounded-2xl shadow-sm flex items-center gap-3 shrink-0">
                    <i class="fas fa-exclamation-circle"></i> <span v-html="$page.props.flash.error"></span>
                </div>

                <!-- Table Card (Sticky Layout) -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col min-h-0 flex-1 overflow-hidden">
                    
                    <!-- Toolbar -->
                    <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex flex-col md:flex-row md:items-center justify-between gap-4 shrink-0 bg-gray-50/50 dark:bg-gray-800/50">
                        <div class="flex flex-wrap items-center gap-4 w-full md:w-auto">
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Tampilkan:</span>
                                <select v-model="perPage" class="border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-xl text-sm focus:ring-primary-500 focus:border-primary-500 py-2 pl-3 pr-8 cursor-pointer">
                                    <option value="10">10 Baris</option>
                                    <option value="25">25 Baris</option>
                                    <option value="50">50 Baris</option>
                                </select>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Kelompok:</span>
                                <select v-model="kelompokFilter" class="border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-xl text-sm focus:ring-primary-500 focus:border-primary-500 py-2 pl-3 pr-8 cursor-pointer w-32">
                                    <option value="">Semua</option>
                                    <option value="A">Kelompok A</option>
                                    <option value="B">Kelompok B</option>
                                    <option value="C">Kelompok C</option>
                                    <option value="Muok">Mulok</option>
                                </select>
                            </div>
                        </div>
                        <div class="relative w-full md:w-72">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-search"></i>
                            </div>
                            <input v-model="search" type="text" class="w-full pl-10 pr-4 py-2 border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-xl text-sm focus:ring-primary-500 focus:border-primary-500 transition-shadow" placeholder="Cari Mapel...">
                        </div>
                    </div>

                    <!-- Scrollable Table -->
                    <div class="flex-1 overflow-auto relative">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="sticky top-0 z-10">
                                <tr class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                                    <th class="py-4 px-5 font-bold w-16 text-center">No</th>
                                    <th class="py-4 px-5 font-bold">Kode</th>
                                    <th class="py-4 px-5 font-bold">Nama Mapel</th>
                                    <th class="py-4 px-5 font-bold text-center">Kel.</th>
                                    <th class="py-4 px-5 font-bold text-center">Pengaturan Tampil</th>
                                    <th class="py-4 px-5 font-bold text-center w-32">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                                <tr v-for="(mpl, index) in mapel.data" :key="mpl.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors group">
                                    <td class="py-4 px-5 text-gray-500 dark:text-gray-400 text-center">
                                        {{ (mapel.current_page - 1) * mapel.per_page + index + 1 }}
                                    </td>
                                    <td class="py-4 px-5 text-gray-900 dark:text-gray-100">
                                        <span class="bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 px-2 py-1 rounded text-xs font-mono">
                                            {{ mpl.kode_mapel || '-' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-5 text-gray-900 dark:text-gray-100 font-bold">
                                        {{ mpl.nama_mapel }}
                                        <div class="text-xs font-normal text-gray-500 mt-0.5">
                                            Jurusan: {{ mpl.jurusan_id == '0' ? 'Semua Jurusan' : 'Terkustomisasi' }}
                                        </div>
                                    </td>
                                    <td class="py-4 px-5 text-center">
                                        <span class="bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 w-8 h-8 rounded-lg flex items-center justify-center font-bold text-xs mx-auto">
                                            {{ mpl.kelompok || '-' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-5 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <span :class="mpl.tampil_raport ? 'text-green-500' : 'text-gray-300'" title="Tampil Raport"><i class="fas fa-file-alt"></i></span>
                                            <span :class="mpl.tampil_skl ? 'text-green-500' : 'text-gray-300'" title="Tampil SKL"><i class="fas fa-graduation-cap"></i></span>
                                            <span :class="mpl.tampil_transkrip ? 'text-green-500' : 'text-gray-300'" title="Tampil Transkrip"><i class="fas fa-scroll"></i></span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-5 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button @click="openModal(mpl)" class="w-8 h-8 rounded-lg bg-orange-50 text-orange-600 hover:bg-orange-100 flex items-center justify-center transition-colors dark:bg-orange-900/30 dark:text-orange-400 dark:hover:bg-orange-900/50">
                                                <i class="fas fa-edit text-xs"></i>
                                            </button>
                                            <button @click="deleteData(mpl.id)" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition-colors dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="mapel.data.length === 0">
                                    <td colspan="6" class="py-12 text-center text-gray-500 dark:text-gray-400">
                                        <i class="fas fa-inbox text-4xl mb-3 text-gray-300 dark:text-gray-600"></i>
                                        <p>Data Mata Pelajaran tidak ditemukan.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex flex-col sm:flex-row items-center justify-between gap-4 shrink-0">
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            Menampilkan <span class="font-bold text-gray-900 dark:text-white">{{ mapel.from || 0 }}</span> sampai <span class="font-bold text-gray-900 dark:text-white">{{ mapel.to || 0 }}</span> dari <span class="font-bold text-gray-900 dark:text-white">{{ mapel.total }}</span> data
                        </div>
                        <div class="flex gap-1">
                            <Link v-for="(link, index) in mapel.links" :key="index" :href="link.url || '#'" 
                                  class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
                                  :class="[
                                      link.active ? 'bg-primary-600 text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700',
                                      !link.url ? 'opacity-50 cursor-not-allowed' : ''
                                  ]"
                                  v-html="link.label">
                            </Link>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-900/75 backdrop-blur-sm" @click="closeModal"></div>

                <div class="relative inline-block w-full max-w-2xl p-6 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-2xl border border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-bold leading-6 text-gray-900 dark:text-white mb-4">
                        {{ isEditing ? 'Edit' : 'Tambah' }} Mata Pelajaran
                    </h3>
                    
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kode Mapel</label>
                                <input v-model="form.kode_mapel" type="text" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="Opsional">
                                <div v-if="form.errors.kode_mapel" class="text-red-500 text-xs mt-1">{{ form.errors.kode_mapel }}</div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kelompok Mapel</label>
                                <select v-model="form.kelompok" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    <option value="">-- Pilih Kelompok --</option>
                                    <option value="A">Kelompok A (Wajib)</option>
                                    <option value="B">Kelompok B (Wajib)</option>
                                    <option value="C">Kelompok C (Peminatan/Keahlian)</option>
                                    <option value="Muok">Muatan Lokal</option>
                                </select>
                                <div v-if="form.errors.kelompok" class="text-red-500 text-xs mt-1">{{ form.errors.kelompok }}</div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Mata Pelajaran <span class="text-red-500">*</span></label>
                            <input v-model="form.nama_mapel" type="text" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="Contoh: Pendidikan Agama Islam" required>
                            <div v-if="form.errors.nama_mapel" class="text-red-500 text-xs mt-1">{{ form.errors.nama_mapel }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ketersediaan Jurusan</label>
                            <select v-model="form.jurusan_id" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                <option value="0">Tersedia Untuk Semua Jurusan</option>
                                <optgroup label="Hanya Jurusan Tertentu">
                                    <option v-for="jrs in jurusans" :key="jrs.id" :value="jrs.id.toString()">
                                        Hanya {{ jrs.nama_jurusan }}
                                    </option>
                                </optgroup>
                            </select>
                            <div v-if="form.errors.jurusan_id" class="text-red-500 text-xs mt-1">{{ form.errors.jurusan_id }}</div>
                        </div>
                        
                        <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700 mt-2">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">Pengaturan Tampilan Output</label>
                            <div class="flex flex-col sm:flex-row gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" v-model="form.tampil_raport" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 bg-white dark:bg-gray-800 dark:border-gray-600">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Tampil di Raport</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" v-model="form.tampil_skl" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 bg-white dark:bg-gray-800 dark:border-gray-600">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Tampil di SKL</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" v-model="form.tampil_transkrip" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 bg-white dark:bg-gray-800 dark:border-gray-600">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Tampil di Transkrip</span>
                                </label>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <button type="button" @click="closeModal" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 transition-colors">
                                Batal
                            </button>
                            <button type="submit" :disabled="form.processing" class="px-5 py-2.5 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-xl hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors disabled:opacity-50 flex items-center gap-2">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </DashboardLayout>

    <!-- Import Modal -->
    <div v-if="showImportModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-900/75 backdrop-blur-sm" @click="closeImportModal"></div>

            <div class="relative inline-block w-full max-w-md p-6 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-2xl border border-gray-100 dark:border-gray-700">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold leading-6 text-gray-900 dark:text-white">
                        Import Mata Pelajaran
                    </h3>
                    <button @click="closeImportModal" class="text-gray-400 hover:text-red-500 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="mb-5 bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl border border-blue-100 dark:border-blue-800">
                    <p class="text-sm text-blue-800 dark:text-blue-400 mb-3">Silakan download template Excel di bawah ini, isi data mata pelajaran, lalu upload kembali file tersebut.</p>
                    <a :href="route('admin.master.mapel.template')" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 border border-blue-200 dark:border-blue-700 rounded-lg text-sm font-bold text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-gray-700 transition-colors shadow-sm">
                        <i class="fas fa-download"></i> Download Template
                    </a>
                </div>

                <form @submit.prevent="submitImport" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">File Excel (.xlsx, .xls) <span class="text-red-500">*</span></label>
                        <input type="file" @change="e => importForm.file = e.target.files[0]" accept=".xlsx, .xls" class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-xl file:border-0
                            file:text-sm file:font-semibold
                            file:bg-emerald-50 file:text-emerald-700
                            hover:file:bg-emerald-100
                            dark:file:bg-emerald-900/30 dark:file:text-emerald-400" required>
                        <div v-if="importForm.errors.file" class="text-red-500 text-xs mt-1">{{ importForm.errors.file }}</div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="closeImportModal" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 transition-colors">
                            Batal
                        </button>
                        <button type="submit" :disabled="importForm.processing" class="px-5 py-2.5 text-sm font-medium text-white bg-emerald-600 border border-transparent rounded-xl hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors disabled:opacity-50 flex items-center gap-2">
                            <i class="fas fa-upload"></i> Upload & Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
