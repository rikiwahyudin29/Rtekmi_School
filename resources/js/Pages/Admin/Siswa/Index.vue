<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    siswa: Object,
    data_kelas: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const perPage = ref(props.filters?.per_page || 10);
const kelasFilter = ref(props.filters?.kelas_id || '');
const statusFilter = ref(props.filters?.status || '');

const openImportModal = ref(false);

const importForm = useForm({
    file: null,
});

const handleFileImport = (e) => {
    importForm.file = e.target.files[0];
};

// Debounced filter
let searchTimeout;
watch([search, perPage, kelasFilter, statusFilter], ([newSearch, newPerPage, newKelas, newStatus]) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const params = { search: newSearch, per_page: newPerPage };
        if (newKelas) params.kelas_id = newKelas;
        if (newStatus) params.status = newStatus;
        router.get('/admin/siswa', params, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }, 300);
});

const submitImport = () => {
    importForm.post('/admin/siswa/import', {
        preserveScroll: true,
        onSuccess: () => {
            openImportModal.value = false;
            importForm.reset();
        },
    });
};

const hapus = (id) => {
    if (confirm('Yakin ingin menghapus data siswa ini? Akun login juga akan terhapus!')) {
        router.delete(`/admin/siswa/${id}`);
    }
};

const resetPassword = (id) => {
    if (confirm('Yakin ingin mereset password siswa ini menjadi NISN-nya?')) {
        router.post(`/admin/siswa/${id}/reset-password`, {}, {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Data Siswa" />

    <DashboardLayout>
        <div class="flex flex-col h-full">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
                
                <!-- Sticky Header -->
                <div class="sticky top-0 z-20 bg-[#f4f6f8] dark:bg-gray-900 pt-6 pb-3 px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                        <div>
                            <h2 class="font-bold text-2xl text-gray-900 dark:text-white tracking-tight">Manajemen Siswa</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kelola data peserta didik, kontak, dan informasi akademik.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <a href="/admin/siswa/export" class="bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400 font-semibold py-2.5 px-4 rounded-xl text-sm transition-all flex items-center gap-2 border border-blue-200 dark:border-blue-800">
                                <i class="fas fa-download"></i> Download
                            </a>
                            <button @click="openImportModal = true" class="bg-green-50 text-green-600 hover:bg-green-100 dark:bg-green-900/30 dark:text-green-400 font-semibold py-2.5 px-4 rounded-xl text-sm transition-all flex items-center gap-2 border border-green-200 dark:border-green-800">
                                <i class="fas fa-upload"></i> Import
                            </button>
                            <Link href="/admin/siswa/create" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2.5 px-5 rounded-xl text-sm transition-all shadow-lg shadow-primary-900/20 flex items-center gap-2">
                                <i class="fas fa-user-plus"></i> Tambah
                            </Link>
                        </div>
                    </div>

                    <!-- Filter Bar -->
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div class="flex items-center gap-3 w-full sm:w-auto flex-wrap">
                            <span class="text-sm text-gray-500 dark:text-gray-400 font-medium">Tampilkan:</span>
                            <select v-model="perPage" class="border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500 shadow-sm text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2 pl-3 pr-8 cursor-pointer">
                                <option :value="10">10 Baris</option>
                                <option :value="25">25 Baris</option>
                                <option :value="50">50 Baris</option>
                                <option :value="100">100 Baris</option>
                            </select>
                            <select v-model="kelasFilter" class="border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500 shadow-sm text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2 pl-3 pr-8 cursor-pointer">
                                <option value="">Semua Kelas</option>
                                <option v-for="k in data_kelas" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                            </select>
                            <select v-model="statusFilter" class="border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500 shadow-sm text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2 pl-3 pr-8 cursor-pointer">
                                <option value="">Semua Status</option>
                                <option value="Aktif">Aktif</option>
                                <option value="Lulus">Lulus</option>
                                <option value="Keluar">Keluar</option>
                                <option value="Skorsing">Skorsing</option>
                            </select>
                        </div>
                        <div class="relative w-full sm:w-72">
                            <input type="text" v-model="search" placeholder="Cari NISN, NIS, atau Nama..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500 shadow-sm text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        </div>
                    </div>
                </div><!-- /sticky header -->

                <!-- Scrollable Content -->
                <div class="px-4 sm:px-6 lg:px-8 pb-8">
                
                <!-- Flash Message -->
                <div v-if="$page.props.flash?.message" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-400 p-4 mb-6 rounded-2xl shadow-sm flex items-center gap-3" v-html="$page.props.flash.message"></div>
                <div v-if="$page.props.flash?.error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-400 p-4 mb-6 rounded-2xl shadow-sm flex items-center gap-3"><i class="fas fa-exclamation-circle"></i> {{ $page.props.flash.error }}</div>

                <!-- Table Card -->
                <div class="bg-white dark:bg-gray-800 shadow-xl shadow-gray-200/50 dark:shadow-none rounded-3xl border border-gray-100 dark:border-gray-700 mb-6 max-h-[60vh] overflow-auto">
                    <div>
                        <table class="w-full text-left border-collapse">
                            <thead class="sticky top-0 z-10">
                                <tr class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                                    <th class="px-6 py-5 font-bold">Profil Siswa</th>
                                    <th class="px-6 py-5 font-bold">Kelas & Jurusan</th>
                                    <th class="px-6 py-5 font-bold">Kontak</th>
                                    <th class="px-6 py-5 font-bold">Status</th>
                                    <th class="px-6 py-5 font-bold text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-for="s in siswa.data" :key="s.id" class="hover:bg-gray-50/80 dark:hover:bg-gray-700/50 transition-colors group">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="relative">
                                                <img :src="`/uploads/siswa/${s.foto}`" @error="$event.target.src='https://ui-avatars.com/api/?background=random&name='+s.nama_lengkap" class="w-12 h-12 rounded-2xl object-cover shadow-md border border-gray-200 dark:border-gray-600">
                                                <div class="absolute -bottom-1 -right-1 w-4 h-4 border-2 border-white dark:border-gray-800 rounded-full" :class="s.status_siswa === 'Aktif' ? 'bg-green-500' : 'bg-gray-400'"></div>
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900 dark:text-white text-base group-hover:text-primary-600 transition-colors">
                                                    {{ s.nama_lengkap }}
                                                </div>
                                                <div class="text-sm text-gray-500 font-medium flex items-center gap-2 mt-0.5">
                                                    <span class="flex items-center gap-1"><i class="fas fa-id-badge text-gray-400"></i> NISN: {{ s.nisn || '-' }}</span>
                                                    <span v-if="s.nis" class="text-gray-300">|</span>
                                                    <span v-if="s.nis">NIS: {{ s.nis }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-1">
                                            <div class="text-sm text-gray-700 dark:text-gray-300 font-medium flex items-center gap-2">
                                                <i class="fas fa-layer-group text-gray-400 w-4"></i> {{ s.kelas?.nama_kelas || 'Belum ada kelas' }}
                                            </div>
                                            <div v-if="s.jurusan" class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-2">
                                                <i class="fas fa-book text-gray-400 w-4"></i> {{ s.jurusan?.nama_jurusan || '-' }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-1">
                                            <div class="text-sm text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                                <i class="fab fa-whatsapp text-green-500 w-4"></i> {{ s.no_hp_siswa || '-' }}
                                            </div>
                                            <div class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                                                <i class="fas fa-envelope text-gray-400 w-4"></i> {{ s.email_siswa || s.user?.email || '-' }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold"
                                              :class="{
                                                  'bg-green-50 text-green-700 border border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800': s.status_siswa === 'Aktif',
                                                  'bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800': s.status_siswa === 'Lulus',
                                                  'bg-red-50 text-red-700 border border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800': s.status_siswa === 'Keluar',
                                                  'bg-yellow-50 text-yellow-700 border border-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-400 dark:border-yellow-800': s.status_siswa === 'Skorsing',
                                              }">
                                            <i class="fas fa-circle text-[6px] mr-1.5" :class="{
                                                'text-green-500': s.status_siswa === 'Aktif',
                                                'text-blue-500': s.status_siswa === 'Lulus',
                                                'text-red-500': s.status_siswa === 'Keluar',
                                                'text-yellow-500': s.status_siswa === 'Skorsing',
                                            }"></i>
                                            {{ s.status_siswa }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <button @click="resetPassword(s.id)" class="w-9 h-9 rounded-xl bg-gray-50 text-gray-600 hover:bg-gray-100 dark:bg-gray-900/30 dark:text-gray-400 dark:hover:bg-gray-900/50 flex items-center justify-center transition-colors" title="Reset Password ke NISN">
                                                <i class="fas fa-key"></i>
                                            </button>
                                            <Link :href="`/admin/siswa/${s.id}/edit`" class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 hover:bg-amber-100 dark:bg-amber-900/30 dark:text-amber-400 dark:hover:bg-amber-900/50 flex items-center justify-center transition-colors" title="Edit Data Siswa">
                                                <i class="fas fa-pen"></i>
                                            </Link>
                                            <button @click="hapus(s.id)" class="w-9 h-9 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50 flex items-center justify-center transition-colors" title="Hapus Siswa">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="siswa.data.length === 0">
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                            <i class="fas fa-folder-open text-4xl mb-3 text-gray-300 dark:text-gray-600"></i>
                                            <p class="text-lg font-medium">Belum Ada Data Siswa</p>
                                            <p class="text-sm mt-1">Silakan tambah siswa secara manual atau import melalui Excel.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination (sticky bottom inside table card) -->
                    <div class="sticky bottom-0 z-10 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 px-6 py-4 flex flex-col md:flex-row justify-between items-center gap-4">
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            Menampilkan <span class="font-bold text-gray-900 dark:text-white">{{ siswa.from || 0 }}</span> sampai <span class="font-bold text-gray-900 dark:text-white">{{ siswa.to || 0 }}</span> dari <span class="font-bold text-gray-900 dark:text-white">{{ siswa.total }}</span> siswa
                        </div>
                        
                        <div class="flex items-center gap-1" v-if="siswa.links.length > 3">
                            <template v-for="(link, i) in siswa.links" :key="i">
                                <Link v-if="link.url" :href="link.url" class="px-4 py-2 text-sm font-medium rounded-xl border transition-colors" :class="link.active ? 'bg-primary-600 text-white border-primary-600 shadow-lg shadow-primary-500/30' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'" v-html="link.label"></Link>
                                <span v-else class="px-4 py-2 text-sm font-medium rounded-xl border border-gray-200 dark:border-gray-700 text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-800/50" v-html="link.label"></span>
                            </template>
                        </div>
                    </div>
                </div><!-- /table card -->

                </div><!-- /scrollable content -->
            </div>
        </div>

        <!-- Import Modal -->
        <div v-if="openImportModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-file-import text-primary-500"></i> Import Data Siswa
                    </h3>
                    <button @click="openImportModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form @submit.prevent="submitImport" class="p-6 space-y-6">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Upload file CSV/Excel yang sesuai format template. <a href="/admin/siswa/template-excel" class="text-primary-600 hover:underline font-medium">Download Template</a></p>
                        <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-2xl p-8 text-center hover:border-primary-400 transition-colors">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-300 dark:text-gray-500 mb-3"></i>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Pilih file CSV atau Excel</p>
                            <input type="file" @change="handleFileImport" accept=".csv,.xlsx,.xls" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 cursor-pointer">
                        </div>
                        <div v-if="importForm.errors.file" class="text-red-500 text-xs mt-2">{{ importForm.errors.file }}</div>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="openImportModal = false" class="px-5 py-2.5 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Batal</button>
                        <button type="submit" :disabled="importForm.processing || !importForm.file" class="px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-primary-600 hover:bg-primary-700 transition-colors shadow-lg shadow-primary-500/30 disabled:opacity-50">
                            <i class="fas fa-upload mr-1"></i> Import Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </DashboardLayout>
</template>
