<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    riwayat: Object,
    siswa: Array,
    jenis: Array,
    stats: Object,
    filters: Object,
    set_sp: Object
});

const search = ref(props.filters?.search || '');
const startDate = ref(props.filters?.start_date || '');
const endDate = ref(props.filters?.end_date || '');

const applyFilter = () => {
    router.get(route('bk.pelanggaran.index'), {
        search: search.value,
        start_date: startDate.value,
        end_date: endDate.value
    }, { preserveState: true, replace: true });
};

const downloadPdf = () => {
    const params = new URLSearchParams({
        search: search.value,
        start_date: startDate.value,
        end_date: endDate.value
    }).toString();
    window.open(`${route('bk.pelanggaran.pdf')}?${params}`, '_blank');
};

const isModalOpen = ref(false);
const filterKelas = ref('');

const uniqueKelas = computed(() => {
    const kelasMap = new Map();
    props.siswa.forEach(s => {
        if (s.kelas) {
            kelasMap.set(s.kelas.id, s.kelas.nama_kelas);
        }
    });
    return Array.from(kelasMap, ([id, nama_kelas]) => ({ id, nama_kelas })).sort((a, b) => a.nama_kelas.localeCompare(b.nama_kelas));
});

const filteredSiswa = computed(() => {
    if (!filterKelas.value) return props.siswa;
    return props.siswa.filter(s => s.kelas_id === filterKelas.value);
});

const form = useForm({
    siswa_id: '',
    pelanggaran_id: '',
    catatan: ''
});

const openModal = () => {
    form.reset();
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submitForm = () => {
    form.post(route('bk.pelanggaran.store'), {
        onSuccess: () => closeModal()
    });
};

const deletePelanggaran = (id) => {
    if (confirm('Yakin ingin menghapus data pelanggaran ini?')) {
        router.delete(route('bk.pelanggaran.destroy', id));
    }
};

const isSettingModalOpen = ref(false);
const spForm = useForm({
    sp_1: props.set_sp?.sp_1 || 50,
    sp_2: props.set_sp?.sp_2 || 30,
    sp_3: props.set_sp?.sp_3 || 0
});

const openSettingModal = () => {
    spForm.sp_1 = props.set_sp?.sp_1 || 50;
    spForm.sp_2 = props.set_sp?.sp_2 || 30;
    spForm.sp_3 = props.set_sp?.sp_3 || 0;
    isSettingModalOpen.value = true;
};

const closeSettingModal = () => {
    isSettingModalOpen.value = false;
};

const submitSetting = () => {
    spForm.post(route('pelanggaran.sp.update'), {
        onSuccess: () => closeSettingModal()
    });
};
</script>

<template>
    <Head title="Buku Kasus Siswa" />

    <DashboardLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Header Stats & Action -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-2 bg-gradient-to-br from-rose-700 via-pink-800 to-red-900 rounded-3xl p-8 shadow-2xl relative overflow-hidden flex flex-col md:flex-row items-center justify-between text-white border border-rose-600/50">
                        <div class="absolute -top-24 -right-24 w-64 h-64 bg-white/10 rounded-full blur-3xl mix-blend-screen"></div>
                        <div class="relative z-10 flex-1">
                            <h2 class="text-3xl font-black tracking-tight mb-2">Buku Kasus Siswa</h2>
                            <p class="text-rose-200/90 font-medium text-sm">Monitoring kedisiplinan dan poin pelanggaran siswa.</p>
                        </div>
                        <div class="relative z-10 mt-6 md:mt-0 flex gap-3 flex-wrap">
                            <button @click="openSettingModal" class="px-5 py-3.5 bg-rose-800 border border-rose-600 text-rose-100 font-bold rounded-2xl shadow-xl hover:bg-rose-900 transition-all flex items-center">
                                <i class="fas fa-cog mr-2"></i> Pengaturan
                            </button>
                            <button @click="openModal" class="px-6 py-3.5 bg-white text-rose-700 font-black rounded-2xl shadow-xl shadow-rose-900/20 hover:scale-105 transition-all flex items-center">
                                <i class="fas fa-plus-circle mr-2 text-lg"></i> Catat Pelanggaran
                            </button>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex flex-col justify-center relative overflow-hidden group">
                        <div class="absolute -right-6 -top-6 w-32 h-32 bg-gray-50 rounded-full group-hover:scale-150 transition-transform duration-700 ease-out z-0"></div>
                        <div class="relative z-10">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center text-xl shadow-sm border border-rose-100">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">Total Kasus</p>
                                    <h3 class="text-2xl font-black text-gray-900">{{ stats.total }}</h3>
                                </div>
                            </div>
                            <div class="pt-4 border-t border-gray-100">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Siswa Paling Kritis</p>
                                <p class="text-sm font-black text-rose-600 flex items-center"><i class="fas fa-exclamation-circle text-rose-400 mr-2"></i> {{ stats.top_siswa }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="$page.props.flash?.message" class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-center text-emerald-800 shadow-sm animate-fade-in-down">
                    <i class="fas fa-check-circle text-emerald-600 mr-3 text-xl"></i>
                    <p class="font-bold">{{ $page.props.flash.message }}</p>
                </div>

                <!-- Filter & Search Bar -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex flex-col md:flex-row items-end md:items-center justify-between gap-4">
                    <div class="flex flex-col md:flex-row items-center gap-4 w-full md:w-auto">
                        <div class="w-full md:w-64 relative">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1 ml-1">Pencarian</label>
                            <input type="text" v-model="search" @keyup.enter="applyFilter" class="w-full pl-10 pr-4 py-2.5 rounded-xl border-gray-200 text-sm focus:ring-rose-500 font-medium" placeholder="Cari Nama / Jenis...">
                            <i class="fas fa-search absolute left-3.5 top-8 text-gray-400"></i>
                        </div>
                        <div class="flex items-center gap-2 w-full md:w-auto">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1 ml-1">Dari Tanggal</label>
                                <input type="date" v-model="startDate" class="w-full rounded-xl border-gray-200 text-sm focus:ring-rose-500 font-medium text-gray-600">
                            </div>
                            <div class="mt-5 text-gray-400"><i class="fas fa-minus"></i></div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1 ml-1">Sampai</label>
                                <input type="date" v-model="endDate" class="w-full rounded-xl border-gray-200 text-sm focus:ring-rose-500 font-medium text-gray-600">
                            </div>
                        </div>
                        <button @click="applyFilter" class="mt-5 px-5 py-2.5 bg-gray-100 text-gray-600 font-bold rounded-xl hover:bg-gray-200 transition-colors w-full md:w-auto">
                            Terapkan
                        </button>
                    </div>
                    
                    <button @click="downloadPdf" class="px-5 py-2.5 bg-rose-50 text-rose-600 font-bold rounded-xl hover:bg-rose-600 hover:text-white transition-all shadow-sm flex items-center shrink-0">
                        <i class="fas fa-file-pdf mr-2"></i> Download PDF
                    </button>
                </div>

                <!-- Table Riwayat -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col min-h-[400px]">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <h3 class="font-black text-gray-800 text-lg">Riwayat Kedisiplinan</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-500 uppercase bg-white border-b border-gray-100 font-bold sticky top-0">
                                <tr>
                                    <th class="px-6 py-4">Tanggal Kejadian</th>
                                    <th class="px-6 py-4">Nama Siswa</th>
                                    <th class="px-6 py-4">Kasus Pelanggaran</th>
                                    <th class="px-6 py-4 text-center">Poin</th>
                                    <th class="px-6 py-4">Pelapor</th>
                                    <th class="px-6 py-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="row in riwayat.data" :key="row.id" class="hover:bg-gray-50/80 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">{{ new Date(row.tanggal).toLocaleDateString('id-ID') }}</div>
                                        <div class="text-[10px] text-gray-500 font-mono tracking-widest mt-0.5">{{ new Date(row.tanggal).toLocaleTimeString('id-ID') }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">{{ row.siswa?.nama_lengkap }}</div>
                                        <div class="text-xs text-gray-500 mt-0.5 font-medium">{{ row.siswa?.kelas?.nama_kelas || '-' }} &bull; {{ row.siswa?.nisn }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-black text-rose-700">{{ row.pelanggaran?.nama_pelanggaran }}</div>
                                        <div class="text-xs text-gray-500 mt-1 italic line-clamp-1 border-l-2 border-gray-200 pl-2">"{{ row.catatan || 'Tanpa catatan' }}"</div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="w-10 h-10 rounded-full bg-rose-50 text-rose-600 font-black flex items-center justify-center mx-auto border border-rose-100 shadow-sm text-base">
                                            {{ row.pelanggaran?.poin }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-bold text-gray-700">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-[10px]"><i class="fas fa-user-tie"></i></div>
                                            {{ row.pelapor?.nama_lengkap || 'Sistem' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button @click="deletePelanggaran(row.id)" class="w-8 h-8 rounded-xl bg-gray-100 text-gray-400 hover:bg-rose-600 hover:text-white hover:shadow-md transition-all flex items-center justify-center mx-auto">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="riwayat.data.length === 0">
                                    <td colspan="6" class="px-6 py-16 text-center text-gray-500">
                                        <div class="w-16 h-16 bg-gray-50 border border-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                            <i class="fas fa-shield-alt text-2xl text-emerald-400"></i>
                                        </div>
                                        <p class="font-bold text-base text-gray-700">Nihil Pelanggaran</p>
                                        <p class="text-sm">Belum ada kasus indisipliner yang tercatat pada kriteria pencarian ini.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="p-6 border-t border-gray-100 bg-gray-50 flex justify-center">
                        <Pagination :links="riwayat.links" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Tambah Pelanggaran -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm" @click="closeModal"></div>
            <div class="bg-white rounded-3xl w-full max-w-lg relative z-10 overflow-hidden shadow-2xl">
                <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex items-center gap-3">
                    <div class="w-12 h-12 bg-rose-100 text-rose-600 rounded-2xl flex items-center justify-center shadow-inner">
                        <i class="fas fa-gavel text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-gray-900">Catat Pelanggaran</h3>
                        <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest mt-0.5">Buku Kasus Digital</p>
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-2 ml-1">Filter Kelas</label>
                            <select v-model="filterKelas" @change="form.siswa_id = ''" class="w-full rounded-xl border-gray-200 text-sm focus:ring-rose-500 font-medium">
                                <option value="">-- Semua Kelas --</option>
                                <option v-for="k in uniqueKelas" :key="k.id" :value="k.id">
                                    {{ k.nama_kelas }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-2 ml-1">Pilih Siswa (Pelaku)</label>
                            <select v-model="form.siswa_id" class="w-full rounded-xl border-gray-200 text-sm focus:ring-rose-500 font-medium disabled:opacity-50" :disabled="filteredSiswa.length === 0">
                                <option value="">-- Cari Nama Siswa --</option>
                                <option v-for="s in filteredSiswa" :key="s.id" :value="s.id">
                                    {{ s.nama_lengkap }} {{ s.kelas ? '(' + s.kelas.nama_kelas + ')' : '' }}
                                </option>
                            </select>
                            <div v-if="form.errors.siswa_id" class="text-rose-500 text-xs mt-1 ml-1 font-bold">{{ form.errors.siswa_id }}</div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2 ml-1">Jenis Pelanggaran / Kasus</label>
                        <select v-model="form.pelanggaran_id" class="w-full rounded-xl border-gray-200 text-sm focus:ring-rose-500 font-medium">
                            <option value="">-- Pilih Jenis Pelanggaran --</option>
                            <option v-for="j in jenis" :key="j.id" :value="j.id">
                                [Poin: {{ j.poin }}] - {{ j.nama_pelanggaran }}
                            </option>
                        </select>
                        <div v-if="form.errors.pelanggaran_id" class="text-rose-500 text-xs mt-1 ml-1 font-bold">{{ form.errors.pelanggaran_id }}</div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2 ml-1">Catatan Kronologi (Opsional)</label>
                        <textarea v-model="form.catatan" rows="3" class="w-full rounded-xl border-gray-200 text-sm focus:ring-rose-500" placeholder="Jelaskan detail kejadian..."></textarea>
                    </div>
                </div>
                <div class="p-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                    <button @click="closeModal" class="px-5 py-3 rounded-xl text-sm font-bold text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 transition-colors">Batal</button>
                    <button @click="submitForm" class="px-6 py-3 rounded-xl text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-lg shadow-rose-600/20 disabled:opacity-50 transition-all flex items-center" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Kasus' }} <i class="fas fa-save ml-2" v-if="!form.processing"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Pengaturan SP -->
        <div v-if="isSettingModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm" @click="closeSettingModal"></div>
            <div class="bg-white rounded-3xl w-full max-w-sm relative z-10 overflow-hidden shadow-2xl">
                <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex items-center gap-3">
                    <div class="w-12 h-12 bg-gray-200 text-gray-600 rounded-2xl flex items-center justify-center shadow-inner">
                        <i class="fas fa-cog text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-gray-900">Pengaturan SP</h3>
                        <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest mt-0.5">Batas Sisa Poin</p>
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2 ml-1">Ambang Batas SP 1</label>
                        <input type="number" v-model="spForm.sp_1" class="w-full rounded-xl border-gray-200 text-sm focus:ring-rose-500 font-bold text-center" placeholder="50">
                        <p class="text-[10px] text-gray-500 mt-1 ml-1">Sisa poin <= nilai ini memicu SP 1.</p>
                        <div v-if="spForm.errors.sp_1" class="text-rose-500 text-xs mt-1 ml-1 font-bold">{{ spForm.errors.sp_1 }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2 ml-1">Ambang Batas SP 2</label>
                        <input type="number" v-model="spForm.sp_2" class="w-full rounded-xl border-gray-200 text-sm focus:ring-rose-500 font-bold text-center" placeholder="30">
                        <p class="text-[10px] text-gray-500 mt-1 ml-1">Sisa poin <= nilai ini memicu SP 2.</p>
                        <div v-if="spForm.errors.sp_2" class="text-rose-500 text-xs mt-1 ml-1 font-bold">{{ spForm.errors.sp_2 }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2 ml-1">Ambang Batas SP 3 (DO)</label>
                        <input type="number" v-model="spForm.sp_3" class="w-full rounded-xl border-gray-200 text-sm focus:ring-rose-500 font-bold text-center text-rose-600" placeholder="0">
                        <p class="text-[10px] text-gray-500 mt-1 ml-1">Sisa poin <= nilai ini memicu SP 3 (Dikeluarkan).</p>
                        <div v-if="spForm.errors.sp_3" class="text-rose-500 text-xs mt-1 ml-1 font-bold">{{ spForm.errors.sp_3 }}</div>
                    </div>
                </div>
                <div class="p-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                    <button @click="closeSettingModal" class="px-5 py-3 rounded-xl text-sm font-bold text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 transition-colors">Batal</button>
                    <button @click="submitSetting" class="px-6 py-3 rounded-xl text-sm font-bold text-white bg-gray-800 hover:bg-gray-900 shadow-lg disabled:opacity-50 transition-all flex items-center" :disabled="spForm.processing">
                        {{ spForm.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
