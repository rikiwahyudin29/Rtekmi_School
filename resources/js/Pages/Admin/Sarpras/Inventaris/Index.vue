<script setup>
import { ref, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    inventaris: Object,
    stats: Object,
    filters: Object,
    kategoriList: Array,
});

// State untuk Modal Tambah/Edit
const isModalOpen = ref(false);
const isEditMode = ref(false);
const selectedItems = ref([]);

const form = useForm({
    id: null,
    kode_barang: '',
    nama_barang: '',
    kategori: 'Elektronik',
    lokasi: '',
    jumlah: 1,
    kondisi: 'Baik',
    tgl_masuk: new Date().toISOString().slice(0, 10),
    keterangan: '',
});

// State untuk Filter & Pencarian
const search = ref(props.filters.search || '');
const filterKategori = ref(props.filters.kategori || 'Semua');
let searchTimeout = null;

const printLabel = (id = null) => {
    let url = route('admin.sarpras.inventaris.print');
    if (id) {
        url += `?search=${id}`;
    } else {
        url += `?search=${search.value}&kategori=${filterKategori.value}`;
    }
    window.open(url, '_blank');
};

const downloadPdf = () => {
    const url = route('admin.sarpras.inventaris.pdf', {
        search: search.value,
        kategori: filterKategori.value
    });
    window.open(url, '_blank');
};

const applyFilters = () => {
    router.get(route('admin.sarpras.inventaris.index'), {
        search: search.value,
        kategori: filterKategori.value
    }, { preserveState: true, replace: true });
};

const handleSearch = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 500);
};

watch(filterKategori, applyFilters);

const openAddModal = () => {
    isEditMode.value = false;
    form.reset();
    form.tgl_masuk = new Date().toISOString().slice(0, 10);
    isModalOpen.value = true;
};

const openEditModal = (item) => {
    isEditMode.value = true;
    form.id = item.id;
    form.kode_barang = item.kode_barang || '';
    form.nama_barang = item.nama_barang || '';
    form.kategori = item.kategori || 'Elektronik';
    form.lokasi = item.lokasi || '';
    form.jumlah = item.jumlah || 1;
    form.kondisi = item.kondisi || 'Baik';
    form.tgl_masuk = item.tgl_masuk || new Date().toISOString().slice(0, 10);
    form.keterangan = item.keterangan || '';
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submitForm = () => {
    if (isEditMode.value) {
        form.put(route('admin.sarpras.inventaris.update', form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.sarpras.inventaris.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const hapusItem = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus aset ini secara permanen?')) {
        router.delete(route('admin.sarpras.inventaris.destroy', id));
    }
};

const getKondisiClass = (kondisi) => {
    if (kondisi === 'Baik') return 'bg-emerald-500/10 text-emerald-600 border-emerald-200';
    if (kondisi === 'Rusak Ringan') return 'bg-amber-500/10 text-amber-600 border-amber-200';
    return 'bg-red-500/10 text-red-600 border-red-200';
};
</script>

<template>
    <Head title="Inventaris Sarpras" />

    <DashboardLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Premium Gradient Banner -->
                <div class="bg-gradient-to-br from-indigo-600 via-blue-700 to-blue-800 rounded-3xl p-8 shadow-2xl shadow-indigo-500/20 relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                    <div class="absolute top-0 right-0 -mt-20 -mr-20 w-72 h-72 bg-white/10 rounded-full blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 -mb-20 -ml-20 w-64 h-64 bg-indigo-400/20 rounded-full blur-3xl"></div>
                    <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-5 mix-blend-overlay"></div>
                    
                    <div class="relative z-10 flex-1">
                        <div class="flex items-center gap-3 mb-2.5">
                            <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-[10px] sm:text-xs font-black text-white uppercase tracking-wider border border-white/20 shadow-sm">Manajemen Sarpras</span>
                            <span class="text-indigo-100 text-xs sm:text-sm font-semibold tracking-wide flex items-center gap-2">
                                <i class="fas fa-boxes opacity-70"></i> Aset & Fasilitas
                            </span>
                        </div>
                        <h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-white tracking-tight mb-3 drop-shadow-sm">Inventaris Barang</h2>
                        <p class="text-indigo-100/90 font-medium text-sm sm:text-base max-w-2xl leading-relaxed">
                            Pusat data aset sekolah, pengelolaan sarana prasarana, serta monitoring kondisi barang secara digital.
                        </p>
                    </div>

                    <div class="relative z-10 shrink-0 mt-4 md:mt-0">
                        <button @click="openAddModal" class="w-full md:w-auto inline-flex items-center justify-center px-6 py-3.5 bg-white hover:bg-gray-50 border border-transparent rounded-2xl font-black text-indigo-700 shadow-xl shadow-black/10 focus:outline-none focus:ring-2 focus:ring-white/50 focus:ring-offset-2 focus:ring-offset-indigo-700 transform hover:-translate-y-1 transition-all duration-300 group">
                            <i class="fas fa-plus mr-2 text-indigo-500 group-hover:scale-110 transition-transform"></i> Tambah Aset Baru
                        </button>
                    </div>
                </div>

                <div v-if="$page.props.flash?.message" class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-center text-emerald-800 shadow-sm animate-fade-in-down">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center mr-4 flex-shrink-0">
                        <i class="fas fa-check text-emerald-600"></i>
                    </div>
                    <p class="font-bold">{{ $page.props.flash.message }}</p>
                </div>

                <!-- Quick Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 flex items-center justify-between group overflow-hidden relative">
                        <div class="absolute -right-6 -top-6 w-24 h-24 bg-blue-50 rounded-full blur-2xl group-hover:bg-blue-100 transition-colors"></div>
                        <div class="relative z-10">
                            <p class="text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1">TOTAL ASET</p>
                            <h3 class="text-3xl font-black text-gray-900 tracking-tight">{{ stats.total }}</h3>
                        </div>
                        <div class="w-14 h-14 rounded-2xl bg-blue-50/80 border border-blue-100 flex items-center justify-center relative z-10 shadow-sm">
                            <i class="fas fa-boxes text-blue-500 text-xl"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 flex items-center justify-between group overflow-hidden relative">
                        <div class="absolute -right-6 -top-6 w-24 h-24 bg-emerald-50 rounded-full blur-2xl group-hover:bg-emerald-100 transition-colors"></div>
                        <div class="relative z-10">
                            <p class="text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1">KONDISI BAIK</p>
                            <h3 class="text-3xl font-black text-emerald-600 tracking-tight">{{ stats.baik }}</h3>
                        </div>
                        <div class="w-14 h-14 rounded-2xl bg-emerald-50/80 border border-emerald-100 flex items-center justify-center relative z-10 shadow-sm">
                            <i class="fas fa-check-circle text-emerald-500 text-xl"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 flex items-center justify-between group overflow-hidden relative">
                        <div class="absolute -right-6 -top-6 w-24 h-24 bg-red-50 rounded-full blur-2xl group-hover:bg-red-100 transition-colors"></div>
                        <div class="relative z-10">
                            <p class="text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1">RUSAK / PERBAIKAN</p>
                            <h3 class="text-3xl font-black text-red-600 tracking-tight">{{ stats.rusak }}</h3>
                        </div>
                        <div class="w-14 h-14 rounded-2xl bg-red-50/80 border border-red-100 flex items-center justify-center relative z-10 shadow-sm">
                            <i class="fas fa-tools text-red-500 text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="bg-white overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:rounded-3xl border border-gray-100">
                    <div class="p-6 md:p-8 border-b border-gray-100/80 bg-gray-50/30 flex flex-col md:flex-row gap-4 justify-between items-center">
                        <div class="flex gap-4 w-full md:w-auto">
                            <!-- Filter Kategori -->
                            <div class="relative min-w-[200px]">
                                <select v-model="filterKategori" class="w-full pl-10 pr-10 py-3 bg-white border-gray-200 text-gray-700 text-sm rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 appearance-none font-medium shadow-sm transition-shadow hover:shadow-md">
                                    <option value="Semua">Semua Kategori</option>
                                    <option v-for="kat in kategoriList" :key="kat" :value="kat">{{ kat }}</option>
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-filter text-gray-400"></i>
                                </div>
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                </div>
                            </div>
                            
                            <!-- Cetak Masal Button -->
                            <div class="flex items-center gap-2">
                                <button @click="printLabel(null)" class="px-5 py-3 bg-white border border-gray-200 text-gray-700 text-sm font-bold rounded-2xl hover:bg-gray-50 transition-colors shadow-sm flex items-center">
                                    <i class="fas fa-print text-indigo-600 mr-2"></i> Cetak Semua
                                </button>
                                <button @click="downloadPdf" class="px-5 py-3 bg-white border border-red-200 text-red-700 text-sm font-bold rounded-2xl hover:bg-red-50 transition-colors shadow-sm flex items-center">
                                    <i class="fas fa-file-pdf text-red-600 mr-2"></i> Download PDF
                                </button>
                            </div>
                        </div>

                        <!-- Search Box -->
                        <div class="relative w-full md:w-96">
                            <input v-model="search" @input="handleSearch" type="text" class="w-full pl-11 pr-4 py-3 bg-white border-gray-200 text-gray-900 text-sm rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 font-medium placeholder-gray-400 shadow-sm transition-shadow hover:shadow-md" placeholder="Cari nama, kode, atau lokasi...">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-[10px] text-gray-400 uppercase bg-gray-50/50 border-b border-gray-100 font-black tracking-wider">
                                <tr>
                                    <th class="px-6 py-4">Kode Barang</th>
                                    <th class="px-6 py-4">Nama Aset</th>
                                    <th class="px-6 py-4">Lokasi</th>
                                    <th class="px-6 py-4 text-center">Jumlah</th>
                                    <th class="px-6 py-4 text-center">Kondisi</th>
                                    <th class="px-6 py-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="item in inventaris.data" :key="item.id" class="hover:bg-gray-50/80 transition-colors group">
                                    <td class="px-6 py-4 font-bold text-gray-900">{{ item.kode_barang || '-' }}</td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">{{ item.nama_barang }}</div>
                                        <div class="text-xs text-gray-500 font-medium">{{ item.kategori }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 font-medium">{{ item.lokasi }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-gray-100 text-gray-700 font-bold">
                                            {{ item.jumlah }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span :class="['px-3 py-1 rounded-full text-xs font-bold border', getKondisiClass(item.kondisi)]">
                                            {{ item.kondisi }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <a :href="route('admin.sarpras.inventaris.print', { ids: item.id })" target="_blank" class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition-colors" title="Cetak Label">
                                                <i class="fas fa-print text-xs"></i>
                                            </a>
                                            <button @click="openEditModal(item)" class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-100 flex items-center justify-center transition-colors" title="Edit">
                                                <i class="fas fa-edit text-xs"></i>
                                            </button>
                                            <button @click="hapusItem(item.id)" class="w-8 h-8 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition-colors" title="Hapus">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="inventaris.data.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                            <i class="fas fa-box-open text-2xl text-gray-400"></i>
                                        </div>
                                        <h3 class="text-sm font-bold text-gray-900 mb-1">Tidak Ada Data Aset</h3>
                                        <p class="text-xs text-gray-500">Belum ada data barang atau filter tidak cocok.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="inventaris.links && inventaris.links.length > 3" class="px-6 py-4 border-t border-gray-100 bg-gray-50/30 flex items-center justify-between">
                        <div class="text-xs font-medium text-gray-500">
                            Menampilkan <span class="font-bold text-gray-900">{{ inventaris.from || 0 }}</span> - <span class="font-bold text-gray-900">{{ inventaris.to || 0 }}</span> dari <span class="font-bold text-gray-900">{{ inventaris.total }}</span> aset
                        </div>
                        <div class="flex flex-wrap gap-1">
                            <Link v-for="(link, k) in inventaris.links" :key="k"
                                  :href="link.url || '#'"
                                  class="w-8 h-8 flex items-center justify-center rounded-xl text-xs font-bold transition-all"
                                  :class="[
                                    link.active ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50',
                                    !link.url ? 'opacity-50 cursor-not-allowed bg-gray-50' : ''
                                  ]"
                                  v-html="link.label">
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Form (Tambah/Edit) -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" @click="closeModal" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-white/20">
                    <div class="bg-white px-6 pt-6 pb-4 sm:p-8 sm:pb-6">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-50 sm:mx-0 sm:h-10 sm:w-10 border border-indigo-100">
                                <i class="fas fa-box text-indigo-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-xl leading-6 font-black text-gray-900" id="modal-title">
                                    {{ isEditMode ? 'Edit Data Aset' : 'Tambah Aset Baru' }}
                                </h3>
                                <div class="mt-6 space-y-5">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Kode Barang <span class="text-gray-400 font-normal lowercase ml-1">(Opsional)</span></label>
                                            <input type="text" v-model="form.kode_barang" class="w-full bg-gray-50 border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 font-medium transition-colors" placeholder="Otomatis dari sistem jika kosong">
                                            <div v-if="form.errors.kode_barang" class="text-red-500 text-xs mt-1">{{ form.errors.kode_barang }}</div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Nama Barang</label>
                                            <input type="text" v-model="form.nama_barang" class="w-full bg-gray-50 border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 font-medium transition-colors" placeholder="Nama aset">
                                            <div v-if="form.errors.nama_barang" class="text-red-500 text-xs mt-1">{{ form.errors.nama_barang }}</div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Kategori</label>
                                            <input type="text" list="kategori-options" v-model="form.kategori" class="w-full bg-gray-50 border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 font-medium transition-colors" placeholder="Pilih atau ketik kategori baru">
                                            <datalist id="kategori-options">
                                                <option v-for="kat in kategoriList" :key="kat" :value="kat"></option>
                                            </datalist>
                                            <div v-if="form.errors.kategori" class="text-red-500 text-xs mt-1">{{ form.errors.kategori }}</div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Lokasi / Ruangan</label>
                                            <input type="text" v-model="form.lokasi" class="w-full bg-gray-50 border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 font-medium transition-colors" placeholder="Cth: R. Guru">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-3 gap-5">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Jumlah</label>
                                            <input type="number" v-model="form.jumlah" class="w-full bg-gray-50 border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 font-medium transition-colors">
                                        </div>
                                        <div class="col-span-2">
                                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Kondisi Barang</label>
                                            <select v-model="form.kondisi" class="w-full bg-gray-50 border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 font-medium transition-colors">
                                                <option value="Baik">Baik</option>
                                                <option value="Rusak Ringan">Rusak Ringan</option>
                                                <option value="Rusak Berat">Rusak Berat</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <div class="col-span-2">
                                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Tgl Masuk</label>
                                            <input type="date" v-model="form.tgl_masuk" class="w-full bg-gray-50 border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 font-medium transition-colors">
                                        </div>
                                        <div class="col-span-2">
                                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Keterangan Tambahan</label>
                                            <textarea v-model="form.keterangan" rows="2" class="w-full bg-gray-50 border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 font-medium transition-colors" placeholder="Opsional..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-4 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-3xl border-t border-gray-100">
                        <button @click="submitForm" type="button" :disabled="form.processing" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-6 py-2.5 bg-indigo-600 text-base font-bold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 transition-colors">
                            <i v-if="form.processing" class="fas fa-spinner fa-spin mr-2"></i> {{ isEditMode ? 'Simpan Perubahan' : 'Tambahkan Aset' }}
                        </button>
                        <button @click="closeModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-200 shadow-sm px-6 py-2.5 bg-white text-base font-bold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
