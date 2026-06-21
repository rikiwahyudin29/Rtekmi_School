<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    kelompok: Array,
    guru: Array,
    dudi: Array,
    siswa: Array
});

const isModalOpen = ref(false);
const isEditing = ref(false);
const editId = ref(null);
const searchQuery = ref('');
const selectAll = ref(false);
const originalSiswaIds = ref([]);

// Pagination & Search for Groups
const searchGroupQuery = ref('');
const perPage = ref(10);
const currentPage = ref(1);

const form = useForm({
    guru_id: '',
    dudi_id: '',
    tgl_mulai: '',
    tgl_selesai: '',
    siswa_ids: []
});

const openAddModal = () => {
    isEditing.value = false;
    editId.value = null;
    form.reset();
    form.siswa_ids = [];
    originalSiswaIds.value = [];
    searchQuery.value = '';
    selectAll.value = false;
    isModalOpen.value = true;
};

const openEditModal = (k) => {
    isEditing.value = true;
    editId.value = k.id;
    form.dudi_id = k.dudi_id;
    form.guru_id = k.guru_id;
    form.tgl_mulai = k.peserta && k.peserta.length > 0 ? k.peserta[0].tgl_mulai : '';
    form.tgl_selesai = k.peserta && k.peserta.length > 0 ? k.peserta[0].tgl_selesai : '';
    form.siswa_ids = k.peserta ? k.peserta.map(p => p.siswa_id) : [];
    originalSiswaIds.value = [...form.siswa_ids];
    
    searchQuery.value = '';
    selectAll.value = false;
    isModalOpen.value = true;
};

const deleteKelompok = (k) => {
    if (confirm(`Hapus Penempatan?\n\nPenempatan siswa pada kelompok ${k.nama_kelompok} akan dibatalkan.`)) {
        router.delete(route('admin.pkl.kelompok.destroy', k.id), {
            preserveScroll: true
        });
    }
};

const filteredKelompok = computed(() => {
    if (!searchGroupQuery.value) return props.kelompok;
    const query = searchGroupQuery.value.toLowerCase();
    
    return props.kelompok.filter(k => {
        const matchNama = k.nama_kelompok?.toLowerCase().includes(query);
        const matchDudi = k.dudi?.nama_dudi?.toLowerCase().includes(query);
        const matchGuru = k.guru?.nama_lengkap?.toLowerCase().includes(query);
        const matchPeserta = k.peserta?.some(p => p.nama_lengkap.toLowerCase().includes(query));
        
        return matchNama || matchDudi || matchGuru || matchPeserta;
    });
});

const totalPages = computed(() => {
    return Math.ceil(filteredKelompok.value.length / perPage.value);
});

const paginatedKelompok = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    const end = start + perPage.value;
    return filteredKelompok.value.slice(start, end);
});

const changePage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

const availableDudi = computed(() => {
    const usedDudiIds = props.kelompok.map(k => k.dudi_id);
    
    let currentEditDudiId = null;
    if (isEditing.value && editId.value) {
        const editedGroup = props.kelompok.find(k => k.id === editId.value);
        if (editedGroup) {
            currentEditDudiId = editedGroup.dudi_id;
        }
    }
    
    return props.dudi.filter(d => {
        if (currentEditDudiId === d.id) return true;
        return !usedDudiIds.includes(d.id);
    });
});

const filteredSiswa = computed(() => {
    // Only show unassigned students OR students originally selected in this group
    let available = props.siswa.filter(s => !s.is_assigned || originalSiswaIds.value.includes(s.id));
    
    // Sort so that currently checked students are at the top
    available.sort((a, b) => {
        const aSelected = form.siswa_ids.includes(a.id);
        const bSelected = form.siswa_ids.includes(b.id);
        if (aSelected && !bSelected) return -1;
        if (!aSelected && bSelected) return 1;
        return 0;
    });
    
    if (!searchQuery.value) return available;
    const lowerCaseQuery = searchQuery.value.toLowerCase();
    return available.filter(s => 
        s.nama_lengkap.toLowerCase().includes(lowerCaseQuery) || 
        (s.kelas && s.kelas.nama_kelas.toLowerCase().includes(lowerCaseQuery)) ||
        (s.nis && s.nis.toLowerCase().includes(lowerCaseQuery))
    );
});

const toggleSelectAll = () => {
    if (selectAll.value) {
        const newIds = filteredSiswa.value.map(s => s.id);
        form.siswa_ids = [...new Set([...form.siswa_ids, ...newIds])];
    } else {
        const filteredIds = filteredSiswa.value.map(s => s.id);
        form.siswa_ids = form.siswa_ids.filter(id => !filteredIds.includes(id));
    }
};

const submitKelompok = () => {
    if (isEditing.value) {
        form.put(route('admin.pkl.kelompok.update', editId.value), {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            }
        });
    } else {
        form.post(route('admin.pkl.kelompok.store'), {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            }
        });
    }
};
</script>

<template>
    <Head title="Manajemen Penempatan PKL" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-users-cog text-blue-500"></i>
                        Kelompok PKL & Pembimbing
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Kelola penempatan siswa Praktik Kerja Lapangan (PKL), Mitra DUDI, dan Guru Pembimbing.
                    </p>
                </div>
                <button @click="openAddModal" class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium shadow-sm flex items-center gap-2 transition-colors">
                    <i class="fas fa-plus"></i> Tambah Penempatan
                </button>
            </div>

            <div v-if="$page.props.flash?.success" class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400 font-medium">
                <i class="fas fa-check-circle mr-2"></i> {{ $page.props.flash.success }}
            </div>

            <!-- Toolbar Pencarian & Paginasi -->
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 bg-white dark:bg-gray-800 p-4 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Tampilkan</span>
                    <select v-model="perPage" @change="currentPage = 1" class="rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-blue-500 focus:border-blue-500 py-1.5 pl-3 pr-8">
                        <option :value="5">5</option>
                        <option :value="10">10</option>
                        <option :value="25">25</option>
                        <option :value="50">50</option>
                    </select>
                    <span class="text-sm text-gray-500 dark:text-gray-400">baris</span>
                </div>
                
                <div class="relative w-full sm:w-72">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" v-model="searchGroupQuery" @input="currentPage = 1" class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Cari Kelompok / DUDI / Guru / Siswa...">
                </div>
            </div>

            <!-- Tabel Kelompok -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                            <tr>
                                <th scope="col" class="px-6 py-4 w-16 text-center">No</th>
                                <th scope="col" class="px-6 py-4">Nama Kelompok & Periode</th>
                                <th scope="col" class="px-6 py-4">Mitra DUDI</th>
                                <th scope="col" class="px-6 py-4">Guru Pembimbing</th>
                                <th scope="col" class="px-6 py-4">Daftar Peserta (Siswa)</th>
                                <th scope="col" class="px-6 py-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(k, index) in paginatedKelompok" :key="k.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 text-center">{{ (currentPage - 1) * perPage + index + 1 }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900 dark:text-white">{{ k.nama_kelompok }}</div>
                                    <div class="text-xs text-gray-500 mt-1 font-mono" v-if="k.peserta && k.peserta.length > 0">
                                        {{ k.peserta[0].tgl_mulai }} s/d {{ k.peserta[0].tgl_selesai }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">{{ k.dudi?.nama_dudi }}</td>
                                <td class="px-6 py-4">{{ k.guru?.nama_lengkap }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        <span v-for="p in k.peserta" :key="p.pkl_id" class="px-2 py-1 text-[10px] font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 rounded-md border border-blue-200 dark:border-blue-800">
                                            {{ p.nama_lengkap }}
                                        </span>
                                        <span v-if="!k.peserta || k.peserta.length === 0" class="text-xs text-red-500 italic">Belum ada peserta</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openEditModal(k)" class="text-yellow-600 hover:text-yellow-900" title="Edit"><i class="fas fa-edit"></i></button>
                                        <button @click="deleteKelompok(k)" class="text-red-600 hover:text-red-900" title="Hapus"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="paginatedKelompok.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-box-open text-3xl mb-3 text-gray-300"></i><br>
                                    Tidak ada data kelompok PKL yang sesuai.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination Controls -->
                <div v-if="totalPages > 1" class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        Menampilkan {{ (currentPage - 1) * perPage + 1 }} - {{ Math.min(currentPage * perPage, filteredKelompok.length) }} dari {{ filteredKelompok.length }} data
                    </span>
                    <div class="flex gap-1">
                        <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1" class="px-3 py-1 text-sm rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed text-gray-700 dark:text-gray-300">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button v-for="page in totalPages" :key="page" @click="changePage(page)" :class="['px-3 py-1 text-sm rounded-lg border', currentPage === page ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600']">
                            {{ page }}
                        </button>
                        <button @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages" class="px-3 py-1 text-sm rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed text-gray-700 dark:text-gray-300">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Tambah/Edit Penempatan PKL -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-700/50">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ isEditing ? 'Edit' : 'Tambah' }} Penempatan PKL</h3>
                    <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form @submit.prevent="submitKelompok" class="flex-1 overflow-y-auto p-6 flex flex-col md:flex-row gap-6">
                    
                    <!-- Form Kiri (Pengaturan) -->
                    <div class="w-full md:w-1/3 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mitra DUDI</label>
                            <select v-model="form.dudi_id" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="">-- Pilih DUDI --</option>
                                <option v-for="d in availableDudi" :key="d.id" :value="d.id">{{ d.nama_dudi }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Guru Pembimbing</label>
                            <select v-model="form.guru_id" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="">-- Pilih Pembimbing --</option>
                                <option v-for="g in guru" :key="g.id" :value="g.id">{{ g.nama_lengkap }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Mulai</label>
                            <input v-model="form.tgl_mulai" type="date" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Selesai</label>
                            <input v-model="form.tgl_selesai" type="date" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        
                        <!-- Error Validation -->
                        <div v-if="form.errors.siswa_ids" class="text-sm text-red-600 mt-2">
                            <i class="fas fa-exclamation-triangle"></i> Minimal pilih 1 siswa.
                        </div>
                    </div>
                    
                    <!-- Area Kanan (Pilihan Siswa) -->
                    <div class="w-full md:w-2/3 border border-gray-200 dark:border-gray-700 rounded-2xl flex flex-col overflow-hidden">
                        <div class="p-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                            <div class="font-bold text-gray-700 dark:text-gray-300 text-sm">Pilih Siswa ({{ form.siswa_ids.length }} dipilih)</div>
                            <div class="relative w-1/2">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input type="text" v-model="searchQuery" class="block w-full pl-10 pr-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="Cari nama / kelas...">
                            </div>
                        </div>
                        
                        <div class="flex-1 overflow-y-auto max-h-[400px]">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-gray-700 uppercase bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-10 shadow-sm">
                                    <tr>
                                        <th class="p-4 w-4">
                                            <div class="flex items-center">
                                                <input type="checkbox" v-model="selectAll" @change="toggleSelectAll" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                                            </div>
                                        </th>
                                        <th class="px-2 py-3">Nama Siswa</th>
                                        <th class="px-2 py-3">Kelas</th>
                                        <th class="px-2 py-3 w-20">L/P</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="s in filteredSiswa" :key="s.id" class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <td class="p-4">
                                            <div class="flex items-center">
                                                <input type="checkbox" :value="s.id" v-model="form.siswa_ids" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                                            </div>
                                        </td>
                                        <td class="px-2 py-3 font-medium text-gray-900 dark:text-white">
                                            {{ s.nama_lengkap }}
                                            <div class="text-xs text-gray-500 font-normal mt-0.5">{{ s.nis }}</div>
                                        </td>
                                        <td class="px-2 py-3 text-gray-600 dark:text-gray-400">
                                            {{ s.kelas ? s.kelas.nama_kelas : '-' }}
                                        </td>
                                        <td class="px-2 py-3 text-gray-600 dark:text-gray-400 text-center">
                                            {{ s.jk }}
                                        </td>
                                    </tr>
                                    <tr v-if="filteredSiswa.length === 0">
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                            <i class="fas fa-user-slash text-2xl mb-2 text-gray-300"></i><br>
                                            Tidak ada siswa yang ditemukan atau semua siswa sudah PKL.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>

                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex justify-end gap-3">
                    <button type="button" @click="isModalOpen = false" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 transition-colors">
                        Batal
                    </button>
                    <button type="button" @click="submitKelompok" :disabled="form.processing || form.siswa_ids.length === 0" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-xl hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 disabled:opacity-50 flex items-center gap-2 transition-colors">
                        <i class="fas fa-save"></i> Simpan Penempatan
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
