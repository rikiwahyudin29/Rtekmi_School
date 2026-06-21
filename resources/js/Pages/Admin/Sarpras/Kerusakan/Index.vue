<script setup>
import { ref, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    laporan: Object,
    inventarisList: Array,
    filters: Object,
});

const isModalOpen = ref(false);
const isUpdateModalOpen = ref(false);
const selectedLaporan = ref(null);

const form = useForm({
    inventaris_id: '',
    pelapor: '',
    deskripsi: '',
    tgl_lapor: new Date().toISOString().slice(0, 10),
});

const updateForm = useForm({
    status: '',
    tindakan_perbaikan: '',
});

const search = ref(props.filters.search || '');
const filterStatus = ref(props.filters.status || 'Semua');
let searchTimeout = null;

const applyFilters = () => {
    router.get(route('admin.sarpras.kerusakan.index'), {
        search: search.value,
        status: filterStatus.value
    }, { preserveState: true, replace: true });
};

const handleSearch = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 500);
};

watch(filterStatus, applyFilters);

const openModal = () => {
    form.reset();
    isModalOpen.value = true;
};

const openUpdateModal = (item) => {
    selectedLaporan.value = item;
    updateForm.status = item.status;
    updateForm.tindakan_perbaikan = item.tindakan_perbaikan || '';
    isUpdateModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    isUpdateModalOpen.value = false;
    form.reset();
    updateForm.reset();
};

const submitForm = () => {
    form.post(route('admin.sarpras.kerusakan.store'), {
        onSuccess: () => closeModal(),
    });
};

const submitUpdateForm = () => {
    updateForm.put(route('admin.sarpras.kerusakan.update-status', selectedLaporan.value.id), {
        onSuccess: () => closeModal(),
    });
};

const hapusItem = (id) => {
    if (confirm('Hapus laporan kerusakan ini?')) {
        router.delete(route('admin.sarpras.kerusakan.destroy', id));
    }
};

const getStatusClass = (status) => {
    if (status === 'Dilaporkan') return 'bg-red-500/10 text-red-600 border-red-200';
    if (status === 'Proses Perbaikan') return 'bg-amber-500/10 text-amber-600 border-amber-200';
    if (status === 'Selesai') return 'bg-emerald-500/10 text-emerald-600 border-emerald-200';
    return 'bg-gray-500/10 text-gray-600 border-gray-200';
};
</script>

<template>
    <Head title="Laporan Kerusakan" />

    <DashboardLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="bg-gradient-to-br from-rose-600 via-rose-700 to-red-800 rounded-3xl p-8 shadow-2xl shadow-rose-500/20 relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                    <div class="absolute top-0 right-0 -mt-20 -mr-20 w-72 h-72 bg-white/10 rounded-full blur-3xl"></div>
                    <div class="relative z-10 flex-1">
                        <div class="flex items-center gap-3 mb-2.5">
                            <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-black text-white uppercase tracking-wider">Aset & Fasilitas</span>
                        </div>
                        <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight mb-3">Laporan Kerusakan</h2>
                        <p class="text-rose-100/90 font-medium max-w-2xl">Catat laporan aset yang bermasalah dan pantau proses perbaikannya (Maintenance Tracker).</p>
                    </div>
                    <div class="relative z-10 shrink-0">
                        <button @click="openModal" class="px-6 py-3 bg-white text-rose-700 font-black rounded-2xl shadow-xl hover:-translate-y-1 transition-transform">
                            <i class="fas fa-tools mr-2"></i> Buat Laporan Baru
                        </button>
                    </div>
                </div>

                <div v-if="$page.props.flash?.message" class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-center text-emerald-800 shadow-sm animate-fade-in-down">
                    <i class="fas fa-check-circle text-emerald-600 mr-3 text-xl"></i>
                    <p class="font-bold">{{ $page.props.flash.message }}</p>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row justify-between gap-4">
                        <select v-model="filterStatus" class="w-full sm:w-48 bg-white border-gray-200 text-sm rounded-xl focus:ring-rose-500 font-medium">
                            <option value="Semua">Semua Status</option>
                            <option value="Dilaporkan">Dilaporkan</option>
                            <option value="Proses Perbaikan">Proses Perbaikan</option>
                            <option value="Selesai">Selesai</option>
                        </select>
                        <div class="relative w-full sm:w-96">
                            <input v-model="search" @input="handleSearch" type="text" class="w-full pl-10 pr-4 py-2 bg-white border-gray-200 text-sm rounded-xl focus:ring-rose-500" placeholder="Cari pelapor, barang...">
                            <i class="fas fa-search absolute left-4 top-3 text-gray-400"></i>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100 font-bold">
                                <tr>
                                    <th class="px-6 py-4">Pelapor & Tanggal</th>
                                    <th class="px-6 py-4">Aset Rusak</th>
                                    <th class="px-6 py-4">Deskripsi / Keluhan</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                    <th class="px-6 py-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="item in laporan.data" :key="item.id" class="hover:bg-gray-50/80 group">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">{{ item.pelapor }}</div>
                                        <div class="text-xs text-gray-500">{{ new Date(item.tgl_lapor).toLocaleDateString('id-ID') }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-700">{{ item.inventaris?.nama_barang || 'Aset Dihapus' }}</div>
                                        <div class="text-xs text-gray-400">{{ item.inventaris?.kode_barang || '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 max-w-xs">
                                        <div class="truncate" :title="item.deskripsi">{{ item.deskripsi }}</div>
                                        <div v-if="item.tindakan_perbaikan" class="text-[10px] text-teal-600 font-bold mt-1 uppercase">Tindakan: {{ item.tindakan_perbaikan }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span :class="['px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border', getStatusClass(item.status)]">
                                            {{ item.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button @click="openUpdateModal(item)" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100" title="Update Status"><i class="fas fa-edit"></i></button>
                                            <button @click="hapusItem(item.id)" class="w-8 h-8 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 ml-2" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="laporan.data.length === 0">
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">Belum ada laporan kerusakan.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div v-if="laporan.links && laporan.links.length > 3" class="px-6 py-4 bg-gray-50/50 border-t border-gray-100">
                        <div class="flex gap-1 justify-center">
                            <Link v-for="(link, k) in laporan.links" :key="k" :href="link.url || '#'" class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold" :class="link.active ? 'bg-rose-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'" v-html="link.label"></Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Buat Laporan -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm" @click="closeModal"></div>
            <div class="bg-white rounded-3xl w-full max-w-lg relative z-10 overflow-hidden shadow-2xl border border-white/20">
                <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-xl font-black text-gray-900"><i class="fas fa-tools text-rose-600 mr-2"></i> Lapor Kerusakan Aset</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Pilih Barang</label>
                        <select v-model="form.inventaris_id" class="w-full rounded-xl border-gray-200 text-sm focus:ring-rose-500">
                            <option value="">-- Pilih Barang/Aset --</option>
                            <option v-for="inv in inventarisList" :key="inv.id" :value="inv.id">{{ inv.kode_barang }} - {{ inv.nama_barang }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nama Pelapor</label>
                        <input type="text" v-model="form.pelapor" class="w-full rounded-xl border-gray-200 text-sm focus:ring-rose-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Tanggal Lapor</label>
                        <input type="date" v-model="form.tgl_lapor" class="w-full rounded-xl border-gray-200 text-sm focus:ring-rose-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Deskripsi Kerusakan</label>
                        <textarea v-model="form.deskripsi" class="w-full rounded-xl border-gray-200 text-sm focus:ring-rose-500" rows="3" placeholder="Sebutkan detail kerusakan secara jelas..."></textarea>
                    </div>
                </div>
                <div class="p-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                    <button @click="closeModal" class="px-5 py-2 rounded-xl text-sm font-bold text-gray-600 bg-white border border-gray-200 hover:bg-gray-50">Batal</button>
                    <button @click="submitForm" class="px-5 py-2 rounded-xl text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 disabled:opacity-50" :disabled="form.processing">
                        {{ form.processing ? 'Mengirim...' : 'Kirim Laporan' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Update Status -->
        <div v-if="isUpdateModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm" @click="closeModal"></div>
            <div class="bg-white rounded-3xl w-full max-w-lg relative z-10 overflow-hidden shadow-2xl border border-white/20">
                <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-xl font-black text-gray-900"><i class="fas fa-edit text-blue-600 mr-2"></i> Update Status Perbaikan</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="bg-gray-50 p-3 rounded-xl text-sm mb-4 border border-gray-200">
                        <div class="font-bold text-gray-800">{{ selectedLaporan?.inventaris?.nama_barang }}</div>
                        <div class="text-xs text-gray-500">Keluhan: {{ selectedLaporan?.deskripsi }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Status Perbaikan</label>
                        <select v-model="updateForm.status" class="w-full rounded-xl border-gray-200 text-sm focus:ring-blue-500 font-bold">
                            <option value="Dilaporkan">Menerima Laporan (Dilaporkan)</option>
                            <option value="Proses Perbaikan">Sedang Diproses/Diperbaiki</option>
                            <option value="Selesai">Perbaikan Selesai (Barang Normal Lagi)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Tindakan / Solusi yang dilakukan</label>
                        <textarea v-model="updateForm.tindakan_perbaikan" class="w-full rounded-xl border-gray-200 text-sm focus:ring-blue-500" rows="3" placeholder="Opsional..."></textarea>
                    </div>
                </div>
                <div class="p-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                    <button @click="closeModal" class="px-5 py-2 rounded-xl text-sm font-bold text-gray-600 bg-white border border-gray-200 hover:bg-gray-50">Batal</button>
                    <button @click="submitUpdateForm" class="px-5 py-2 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50" :disabled="updateForm.processing">
                        {{ updateForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
