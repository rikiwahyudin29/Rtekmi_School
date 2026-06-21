<script setup>
import { ref, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    peminjaman: Object,
    ruanganList: Array,
    filters: Object,
});

const isModalOpen = ref(false);
const form = useForm({
    ruangan_id: '',
    peminjam: '',
    kegiatan: '',
    tgl_pinjam: '',
    tgl_kembali: '',
    catatan: '',
});

const search = ref(props.filters.search || '');
const filterStatus = ref(props.filters.status || 'Semua');
let searchTimeout = null;

const applyFilters = () => {
    router.get(route('admin.sarpras.peminjaman.index'), {
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

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submitForm = () => {
    form.post(route('admin.sarpras.peminjaman.store'), {
        onSuccess: () => closeModal(),
    });
};

const updateStatus = (id, status) => {
    if (confirm(`Ubah status menjadi ${status}?`)) {
        router.put(route('admin.sarpras.peminjaman.update-status', id), { status });
    }
};

const hapusItem = (id) => {
    if (confirm('Hapus riwayat peminjaman ini?')) {
        router.delete(route('admin.sarpras.peminjaman.destroy', id));
    }
};

const getStatusClass = (status) => {
    if (status === 'Menunggu') return 'bg-amber-500/10 text-amber-600 border-amber-200';
    if (status === 'Disetujui') return 'bg-blue-500/10 text-blue-600 border-blue-200';
    if (status === 'Selesai') return 'bg-emerald-500/10 text-emerald-600 border-emerald-200';
    return 'bg-red-500/10 text-red-600 border-red-200';
};
</script>

<template>
    <Head title="Peminjaman Ruangan" />

    <DashboardLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="bg-gradient-to-br from-teal-600 via-teal-700 to-emerald-800 rounded-3xl p-8 shadow-2xl shadow-teal-500/20 relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                    <div class="absolute top-0 right-0 -mt-20 -mr-20 w-72 h-72 bg-white/10 rounded-full blur-3xl"></div>
                    <div class="relative z-10 flex-1">
                        <div class="flex items-center gap-3 mb-2.5">
                            <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-black text-white uppercase tracking-wider">Aset & Fasilitas</span>
                        </div>
                        <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight mb-3">Peminjaman Ruangan</h2>
                        <p class="text-teal-100/90 font-medium max-w-2xl">Manajemen jadwal pemakaian ruangan lab, aula, dan fasilitas sekolah lainnya.</p>
                    </div>
                    <div class="relative z-10 shrink-0">
                        <button @click="openModal" class="px-6 py-3 bg-white text-teal-700 font-black rounded-2xl shadow-xl hover:-translate-y-1 transition-transform">
                            <i class="fas fa-calendar-plus mr-2"></i> Ajukan Peminjaman
                        </button>
                    </div>
                </div>

                <div v-if="$page.props.flash?.message" class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-center text-emerald-800 shadow-sm animate-fade-in-down">
                    <i class="fas fa-check-circle text-emerald-600 mr-3 text-xl"></i>
                    <p class="font-bold">{{ $page.props.flash.message }}</p>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row justify-between gap-4">
                        <select v-model="filterStatus" class="w-full sm:w-48 bg-white border-gray-200 text-sm rounded-xl focus:ring-teal-500 font-medium">
                            <option value="Semua">Semua Status</option>
                            <option value="Menunggu">Menunggu</option>
                            <option value="Disetujui">Disetujui</option>
                            <option value="Ditolak">Ditolak</option>
                            <option value="Selesai">Selesai</option>
                        </select>
                        <div class="relative w-full sm:w-96">
                            <input v-model="search" @input="handleSearch" type="text" class="w-full pl-10 pr-4 py-2 bg-white border-gray-200 text-sm rounded-xl focus:ring-teal-500" placeholder="Cari peminjam, kegiatan, ruangan...">
                            <i class="fas fa-search absolute left-4 top-3 text-gray-400"></i>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100 font-bold">
                                <tr>
                                    <th class="px-6 py-4">Peminjam & Kegiatan</th>
                                    <th class="px-6 py-4">Ruangan</th>
                                    <th class="px-6 py-4">Jadwal Pakai</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                    <th class="px-6 py-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="item in peminjaman.data" :key="item.id" class="hover:bg-gray-50/80 group">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">{{ item.peminjam }}</div>
                                        <div class="text-xs text-gray-500">{{ item.kegiatan }}</div>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-gray-700">{{ item.ruangan?.nama_ruangan || '-' }}</td>
                                    <td class="px-6 py-4 text-gray-600">
                                        <div class="text-xs">Mulai: <span class="font-bold">{{ new Date(item.tgl_pinjam).toLocaleString('id-ID') }}</span></div>
                                        <div class="text-xs">Selesai: <span class="font-bold">{{ new Date(item.tgl_kembali).toLocaleString('id-ID') }}</span></div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span :class="['px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border', getStatusClass(item.status)]">
                                            {{ item.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button v-if="item.status === 'Menunggu'" @click="updateStatus(item.id, 'Disetujui')" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100" title="Setujui"><i class="fas fa-check"></i></button>
                                            <button v-if="item.status === 'Menunggu'" @click="updateStatus(item.id, 'Ditolak')" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100" title="Tolak"><i class="fas fa-times"></i></button>
                                            <button v-if="item.status === 'Disetujui'" @click="updateStatus(item.id, 'Selesai')" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100" title="Tandai Selesai"><i class="fas fa-flag-checkered"></i></button>
                                            <button @click="hapusItem(item.id)" class="w-8 h-8 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 ml-2" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="peminjaman.data.length === 0">
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">Belum ada data peminjaman ruangan.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div v-if="peminjaman.links && peminjaman.links.length > 3" class="px-6 py-4 bg-gray-50/50 border-t border-gray-100">
                        <div class="flex gap-1 justify-center">
                            <Link v-for="(link, k) in peminjaman.links" :key="k" :href="link.url || '#'" class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold" :class="link.active ? 'bg-teal-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'" v-html="link.label"></Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm" @click="closeModal"></div>
            <div class="bg-white rounded-3xl w-full max-w-lg relative z-10 overflow-hidden shadow-2xl border border-white/20">
                <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-xl font-black text-gray-900"><i class="fas fa-calendar-plus text-teal-600 mr-2"></i> Form Peminjaman Ruangan</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Pilih Ruangan</label>
                        <select v-model="form.ruangan_id" class="w-full rounded-xl border-gray-200 text-sm focus:ring-teal-500">
                            <option value="">-- Pilih Ruangan --</option>
                            <option v-for="r in ruanganList" :key="r.id" :value="r.id">{{ r.nama_ruangan }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nama Peminjam</label>
                        <input type="text" v-model="form.peminjam" class="w-full rounded-xl border-gray-200 text-sm focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kegiatan</label>
                        <input type="text" v-model="form.kegiatan" class="w-full rounded-xl border-gray-200 text-sm focus:ring-teal-500">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Mulai Pakai</label>
                            <input type="datetime-local" v-model="form.tgl_pinjam" class="w-full rounded-xl border-gray-200 text-sm focus:ring-teal-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Selesai Pakai</label>
                            <input type="datetime-local" v-model="form.tgl_kembali" class="w-full rounded-xl border-gray-200 text-sm focus:ring-teal-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Catatan</label>
                        <textarea v-model="form.catatan" class="w-full rounded-xl border-gray-200 text-sm focus:ring-teal-500" rows="2"></textarea>
                    </div>
                </div>
                <div class="p-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                    <button @click="closeModal" class="px-5 py-2 rounded-xl text-sm font-bold text-gray-600 bg-white border border-gray-200 hover:bg-gray-50">Batal</button>
                    <button @click="submitForm" class="px-5 py-2 rounded-xl text-sm font-bold text-white bg-teal-600 hover:bg-teal-700 disabled:opacity-50" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : 'Ajukan Peminjaman' }}
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
