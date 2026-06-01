<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    gurus: Object,
    available_roles: Array,
    filters: Object,
});

const openImportModal = ref(false);
const openRoleModal = ref(false);
const selectedGuru = ref(null);
const search = ref(props.filters?.search || '');
const perPage = ref(props.filters?.per_page || 10);

const importForm = useForm({
    file: null,
});

const roleForm = useForm({
    roles: [],
});

const handleFileImport = (e) => {
    importForm.file = e.target.files[0];
};

// Simple debounce for search and pagination length
let searchTimeout;
watch([search, perPage], ([newSearch, newPerPage]) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/admin/guru', { search: newSearch, per_page: newPerPage }, {
            preserveState: true,
            preserveScroll: true,
            replace: true
        });
    }, 300);
});

const submitImport = () => {
    importForm.post('/admin/guru/import', {
        preserveScroll: true,
        onSuccess: () => {
            openImportModal.value = false;
            importForm.reset();
        },
    });
};

const openAssignRoleModal = (guru) => {
    selectedGuru.value = guru;
    // Map existing roles
    roleForm.roles = guru.user && guru.user.roles ? guru.user.roles.map(r => r.id) : [];
    openRoleModal.value = true;
};

const submitRole = () => {
    roleForm.post(`/admin/guru/${selectedGuru.value.id}/roles`, {
        preserveScroll: true,
        onSuccess: () => {
            openRoleModal.value = false;
        },
    });
};

const hapus = (id) => {
    if (confirm('Yakin ingin menghapus data guru ini? Akun login juga akan terhapus!')) {
        router.delete(`/admin/guru/${id}`);
    }
};

const reset2FA = (id) => {
    if (confirm('Yakin ingin mereset Google Authenticator guru ini? Guru harus menautkan ulang authenticator-nya pada saat login berikutnya.')) {
        router.post(`/admin/guru/${id}/reset-2fa`, {}, {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Data Guru" />

    <DashboardLayout>
        <div class="flex flex-col h-full">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Sticky Header: Title + Buttons + Filter -->
                <div class="sticky top-0 z-20 bg-[#f4f6f8] dark:bg-gray-900 pt-6 pb-3 px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                        <div>
                            <h2 class="font-bold text-2xl text-gray-900 dark:text-white tracking-tight">Manajemen Guru</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kelola data tenaga pendidik, kontak, dan kredensial login.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <a href="/admin/guru/export" class="bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400 font-semibold py-2.5 px-4 rounded-xl text-sm transition-all flex items-center gap-2 border border-blue-200 dark:border-blue-800">
                                <i class="fas fa-download"></i> Download
                            </a>
                            <button @click="openImportModal = true" class="bg-green-50 text-green-600 hover:bg-green-100 dark:bg-green-900/30 dark:text-green-400 font-semibold py-2.5 px-4 rounded-xl text-sm transition-all flex items-center gap-2 border border-green-200 dark:border-green-800">
                                <i class="fas fa-upload"></i> Import
                            </button>
                            <Link href="/admin/guru/create" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2.5 px-5 rounded-xl text-sm transition-all shadow-lg shadow-primary-900/20 flex items-center gap-2">
                                <i class="fas fa-user-plus"></i> Tambah
                            </Link>
                        </div>
                    </div>

                    <!-- Filter Bar -->
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div class="flex items-center gap-3 w-full sm:w-auto">
                            <span class="text-sm text-gray-500 dark:text-gray-400 font-medium">Tampilkan:</span>
                            <select v-model="perPage" class="border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500 shadow-sm text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2 pl-3 pr-8 cursor-pointer">
                                <option :value="10">10 Baris</option>
                                <option :value="25">25 Baris</option>
                                <option :value="50">50 Baris</option>
                                <option :value="100">100 Baris</option>
                            </select>
                        </div>
                        <div class="relative w-full sm:w-72">
                            <input type="text" v-model="search" placeholder="Cari NIP atau Nama..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500 shadow-sm text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
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
                                    <th class="px-6 py-5 font-bold">Profil Guru</th>
                                    <th class="px-6 py-5 font-bold">Kontak & Kredensial</th>
                                    <th class="px-6 py-5 font-bold">Jabatan</th>
                                    <th class="px-6 py-5 font-bold text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-for="guru in gurus.data" :key="guru.id" class="hover:bg-gray-50/80 dark:hover:bg-gray-700/50 transition-colors group">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="relative">
                                                <img :src="`/uploads/guru/${guru.foto}`" @error="$event.target.src='https://ui-avatars.com/api/?background=random&name='+guru.nama_lengkap" class="w-12 h-12 rounded-2xl object-cover shadow-md border border-gray-200 dark:border-gray-600">
                                                <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 border-2 border-white dark:border-gray-800 rounded-full"></div>
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900 dark:text-white text-base group-hover:text-primary-600 transition-colors">
                                                    {{ guru.gelar_depan ? guru.gelar_depan + ' ' : '' }}{{ guru.nama_lengkap }}{{ guru.gelar_belakang ? ', ' + guru.gelar_belakang : '' }}
                                                </div>
                                                <div class="text-sm text-gray-500 font-medium flex items-center gap-1 mt-0.5">
                                                    <i class="fas fa-id-badge text-gray-400"></i> NIP: {{ guru.nip || '-' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-1">
                                            <div class="text-sm text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                                <i class="fas fa-envelope text-gray-400 w-4"></i> {{ guru.user?.email || '-' }}
                                            </div>
                                            <div class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                                                <i class="fab fa-whatsapp text-green-500 w-4"></i> {{ guru.nomor_wa || guru.user?.nomor_wa || '-' }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            <span v-for="role in (guru.user?.roles || [])" :key="role.id" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400 border border-primary-100 dark:border-primary-800">
                                                {{ role.role_name }}
                                            </span>
                                            <span v-if="!(guru.user?.roles?.length)" class="text-xs text-gray-400 italic">Belum Ada Role</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <button @click="reset2FA(guru.id)" class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50 flex items-center justify-center transition-colors" title="Reset Google Authenticator 2FA">
                                                <i class="fas fa-shield-alt"></i>
                                            </button>
                                            <button @click="openAssignRoleModal(guru)" class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 hover:bg-purple-100 dark:bg-purple-900/30 dark:text-purple-400 dark:hover:bg-purple-900/50 flex items-center justify-center transition-colors" title="Tugas Tambahan">
                                                <i class="fas fa-user-tag"></i>
                                            </button>
                                            <Link :href="`/admin/guru/${guru.id}/edit`" class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 hover:bg-amber-100 dark:bg-amber-900/30 dark:text-amber-400 dark:hover:bg-amber-900/50 flex items-center justify-center transition-colors" title="Edit Data Guru">
                                                <i class="fas fa-pen"></i>
                                            </Link>
                                            <button @click="hapus(guru.id)" class="w-9 h-9 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50 flex items-center justify-center transition-colors" title="Hapus Guru">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="gurus.data.length === 0">
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                            <i class="fas fa-folder-open text-4xl mb-3 text-gray-300 dark:text-gray-600"></i>
                                            <p class="text-lg font-medium">Belum Ada Data Guru</p>
                                            <p class="text-sm mt-1">Silakan tambah guru secara manual atau import melalui Excel.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination (sticky bottom inside table card) -->
                    <div class="sticky bottom-0 z-10 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 px-6 py-4 flex flex-col md:flex-row justify-between items-center gap-4">
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            Menampilkan <span class="font-bold text-gray-900 dark:text-white">{{ gurus.from || 0 }}</span> sampai <span class="font-bold text-gray-900 dark:text-white">{{ gurus.to || 0 }}</span> dari <span class="font-bold text-gray-900 dark:text-white">{{ gurus.total }}</span> guru
                        </div>
                        
                        <div class="flex items-center gap-1" v-if="gurus.links.length > 3">
                            <template v-for="(link, i) in gurus.links" :key="i">
                                <Link v-if="link.url" :href="link.url" class="px-4 py-2 text-sm font-medium rounded-xl border transition-colors" :class="link.active ? 'bg-primary-600 text-white border-primary-600 shadow-lg shadow-primary-500/30' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'" v-html="link.label"></Link>
                                <span v-else class="px-4 py-2 text-sm font-medium rounded-xl border border-gray-200 dark:border-gray-700 text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-800/50" v-html="link.label"></span>
                            </template>
                        </div>
                    </div>
                </div><!-- /table card -->

                </div><!-- /scrollable content -->
            </div><!-- /max-w-7xl -->
        </div><!-- /flex col h-full -->

        <!-- Import Modal -->
        <div v-if="openImportModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-file-import text-primary-500"></i> Import Data Guru
                    </h3>
                    <button @click="openImportModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="p-6">
                    <form @submit.prevent="submitImport">
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Upload File Excel/CSV</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-2xl hover:border-primary-500 transition-colors bg-gray-50 dark:bg-gray-900/50">
                                <div class="space-y-1 text-center">
                                    <i class="fas fa-file-excel text-3xl text-green-500 mb-2"></i>
                                    <div class="flex text-sm text-gray-600 dark:text-gray-400 justify-center">
                                        <label for="file-upload" class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-primary-600 hover:text-primary-500 focus-within:outline-none px-1">
                                            <span>Pilih File</span>
                                            <input id="file-upload" type="file" class="sr-only" @change="handleFileImport" accept=".csv,.xlsx,.xls">
                                        </label>
                                        <p class="pl-1">atau drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">CSV, XLS, XLSX up to 5MB</p>
                                    <p v-if="importForm.file" class="text-sm font-bold text-primary-600 mt-2">{{ importForm.file.name }}</p>
                                </div>
                            </div>
                            <p v-if="importForm.errors.file" class="mt-2 text-sm text-red-600">{{ importForm.errors.file }}</p>
                        </div>
                        
                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <button type="button" @click="openImportModal = false" class="px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                Batal
                            </button>
                            <button type="submit" :disabled="importForm.processing || !importForm.file" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2.5 px-6 rounded-xl shadow transition-colors disabled:opacity-50 flex items-center gap-2">
                                <i class="fas fa-spinner fa-spin" v-if="importForm.processing"></i>
                                <i class="fas fa-cloud-upload-alt" v-else></i>
                                Upload & Import
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Role Modal -->
        <div v-if="openRoleModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-user-tag text-purple-500"></i> Tugas Tambahan Guru
                    </h3>
                    <button @click="openRoleModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="p-6">
                    <div class="mb-4" v-if="selectedGuru">
                        <p class="text-sm text-gray-500">Atur hak akses / peran tambahan untuk:</p>
                        <p class="font-bold text-gray-900 dark:text-white">{{ selectedGuru.nama_lengkap }}</p>
                    </div>

                    <form @submit.prevent="submitRole">
                        <div class="space-y-3 max-h-[40vh] overflow-y-auto mb-6 p-2">
                            <label v-for="role in available_roles" :key="role.id" class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors">
                                <input type="checkbox" :value="role.id" v-model="roleForm.roles" class="w-5 h-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500" :disabled="role.role_key === 'guru'">
                                <div>
                                    <p class="font-bold text-sm text-gray-800 dark:text-gray-200">{{ role.role_name }}</p>
                                    <p class="text-xs text-gray-500">{{ role.description }}</p>
                                </div>
                            </label>
                        </div>
                        
                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <button type="button" @click="openRoleModal = false" class="px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                Batal
                            </button>
                            <button type="submit" :disabled="roleForm.processing" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2.5 px-6 rounded-xl shadow transition-colors disabled:opacity-50 flex items-center gap-2">
                                <i class="fas fa-spinner fa-spin" v-if="roleForm.processing"></i>
                                <i class="fas fa-save" v-else></i>
                                Simpan Peran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </DashboardLayout>
</template>
