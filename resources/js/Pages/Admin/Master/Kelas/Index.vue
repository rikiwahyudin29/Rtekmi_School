<script setup>
import { ref, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    kelas: Object,
    jurusans: Array,
    gurus: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const perPage = ref(props.filters.per_page || 10);
const jurusanFilter = ref(props.filters.jurusan_id || '');

const showModal = ref(false);
const isEditing = ref(false);

const form = useForm({
    id: null,
    nama_kelas: '',
    tingkat: '',
    id_jurusan: '',
    guru_id: '',
    dapodik_id: '',
});

const openModal = (kls = null) => {
    isEditing.value = !!kls;
    if (kls) {
        form.id = kls.id;
        form.nama_kelas = kls.nama_kelas;
        form.tingkat = kls.tingkat || '';
        form.id_jurusan = kls.id_jurusan || '';
        form.guru_id = kls.guru_id || '';
        form.dapodik_id = kls.dapodik_id || '';
    } else {
        form.reset();
        form.id = null;
        form.id_jurusan = '';
        form.guru_id = '';
        form.tingkat = '';
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
        form.put(route('admin.master.kelas.update', form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.master.kelas.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteData = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus Kelas ini?')) {
        router.delete(route('admin.master.kelas.destroy', id));
    }
};

let searchTimeout;
watch([search, perPage, jurusanFilter], ([newSearch, newPerPage, newJurusanFilter]) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            route('admin.master.kelas.index'),
            { search: newSearch, per_page: newPerPage, jurusan_id: newJurusanFilter },
            { preserveState: true, replace: true }
        );
    }, 300);
});
</script>

<template>
    <Head title="Master Kelas" />

    <DashboardLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col h-[calc(100vh-10rem)]">
                
                <!-- Header -->
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 shrink-0 gap-4">
                    <div>
                        <h2 class="font-bold text-2xl text-gray-900 dark:text-white tracking-tight">Data Kelas</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kelola data rombongan belajar (rombel) beserta Wali Kelas.</p>
                    </div>
                    <div class="flex gap-2">
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
                                <span class="text-sm text-gray-500 dark:text-gray-400">Jurusan:</span>
                                <select v-model="jurusanFilter" class="border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-xl text-sm focus:ring-primary-500 focus:border-primary-500 py-2 pl-3 pr-8 cursor-pointer max-w-[200px]">
                                    <option value="">Semua Jurusan</option>
                                    <option v-for="jrs in jurusans" :key="jrs.id" :value="jrs.id">{{ jrs.nama_jurusan }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="relative w-full md:w-72">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-search"></i>
                            </div>
                            <input v-model="search" type="text" class="w-full pl-10 pr-4 py-2 border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-xl text-sm focus:ring-primary-500 focus:border-primary-500 transition-shadow" placeholder="Cari Nama Kelas...">
                        </div>
                    </div>

                    <!-- Scrollable Table -->
                    <div class="flex-1 overflow-auto relative">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="sticky top-0 z-10">
                                <tr class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                                    <th class="py-4 px-5 font-bold w-16 text-center">No</th>
                                    <th class="py-4 px-5 font-bold text-center">Tingkat</th>
                                    <th class="py-4 px-5 font-bold">Nama Kelas</th>
                                    <th class="py-4 px-5 font-bold">Jurusan</th>
                                    <th class="py-4 px-5 font-bold">Wali Kelas</th>
                                    <th class="py-4 px-5 font-bold text-center w-32">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                                <tr v-for="(kls, index) in kelas.data" :key="kls.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors group">
                                    <td class="py-4 px-5 text-gray-500 dark:text-gray-400 text-center">
                                        {{ (kelas.current_page - 1) * kelas.per_page + index + 1 }}
                                    </td>
                                    <td class="py-4 px-5 text-gray-900 dark:text-gray-100 font-bold text-center text-lg">
                                        {{ kls.tingkat || '-' }}
                                    </td>
                                    <td class="py-4 px-5 text-gray-900 dark:text-gray-100 font-bold">
                                        {{ kls.nama_kelas }}
                                    </td>
                                    <td class="py-4 px-5 text-gray-500 dark:text-gray-400">
                                        <span v-if="kls.jurusan" class="bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 px-2 py-1 rounded text-xs font-medium">
                                            {{ kls.jurusan.kode_jurusan }}
                                        </span>
                                        <span v-else>-</span>
                                    </td>
                                    <td class="py-4 px-5 text-gray-500 dark:text-gray-400">
                                        {{ kls.wali_kelas?.nama_lengkap || '-' }}
                                    </td>
                                    <td class="py-4 px-5 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button @click="openModal(kls)" class="w-8 h-8 rounded-lg bg-orange-50 text-orange-600 hover:bg-orange-100 flex items-center justify-center transition-colors dark:bg-orange-900/30 dark:text-orange-400 dark:hover:bg-orange-900/50">
                                                <i class="fas fa-edit text-xs"></i>
                                            </button>
                                            <button @click="deleteData(kls.id)" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition-colors dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="kelas.data.length === 0">
                                    <td colspan="6" class="py-12 text-center text-gray-500 dark:text-gray-400">
                                        <i class="fas fa-inbox text-4xl mb-3 text-gray-300 dark:text-gray-600"></i>
                                        <p>Data Kelas tidak ditemukan.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex flex-col sm:flex-row items-center justify-between gap-4 shrink-0">
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            Menampilkan <span class="font-bold text-gray-900 dark:text-white">{{ kelas.from || 0 }}</span> sampai <span class="font-bold text-gray-900 dark:text-white">{{ kelas.to || 0 }}</span> dari <span class="font-bold text-gray-900 dark:text-white">{{ kelas.total }}</span> data
                        </div>
                        <div class="flex gap-1">
                            <Link v-for="(link, index) in kelas.links" :key="index" :href="link.url || '#'" 
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

                <div class="relative inline-block w-full max-w-lg p-6 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-2xl border border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-bold leading-6 text-gray-900 dark:text-white mb-4">
                        {{ isEditing ? 'Edit' : 'Tambah' }} Kelas
                    </h3>
                    
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tingkat <span class="text-red-500">*</span></label>
                                <select v-model="form.tingkat" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                                    <option value="">-- Pilih Tingkat --</option>
                                    <option value="10">Kelas 10</option>
                                    <option value="11">Kelas 11</option>
                                    <option value="12">Kelas 12</option>
                                    <option value="13">Kelas 13</option>
                                </select>
                                <div v-if="form.errors.tingkat" class="text-red-500 text-xs mt-1">{{ form.errors.tingkat }}</div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jurusan</label>
                                <select v-model="form.id_jurusan" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    <option value="">-- Tanpa Jurusan --</option>
                                    <option v-for="jrs in jurusans" :key="jrs.id" :value="jrs.id">
                                        {{ jrs.nama_jurusan }} ({{ jrs.kode_jurusan }})
                                    </option>
                                </select>
                                <div v-if="form.errors.id_jurusan" class="text-red-500 text-xs mt-1">{{ form.errors.id_jurusan }}</div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Kelas <span class="text-red-500">*</span></label>
                            <input v-model="form.nama_kelas" type="text" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="Contoh: X RPL 1" required>
                            <div v-if="form.errors.nama_kelas" class="text-red-500 text-xs mt-1">{{ form.errors.nama_kelas }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Wali Kelas</label>
                            <select v-model="form.guru_id" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                <option value="">-- Pilih Wali Kelas --</option>
                                <option v-for="guru in gurus" :key="guru.id" :value="guru.id">
                                    {{ guru.nama_lengkap }} ({{ guru.nip || '-' }})
                                </option>
                            </select>
                            <div v-if="form.errors.guru_id" class="text-red-500 text-xs mt-1">{{ form.errors.guru_id }}</div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">ID Dapodik</label>
                            <input v-model="form.dapodik_id" type="text" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="Opsional">
                            <div v-if="form.errors.dapodik_id" class="text-red-500 text-xs mt-1">{{ form.errors.dapodik_id }}</div>
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
</template>
