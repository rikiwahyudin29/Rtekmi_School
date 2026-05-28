<script setup>
import { ref, watch, computed } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import FormModal from './FormModal.vue';
import PesertaModal from './PesertaModal.vue';
import UbahWaktuModal from './UbahWaktuModal.vue';
import HapusJawabanModal from './HapusJawabanModal.vue';

const props = defineProps({
    jadwalUjian: Object,
    draftUjians: Array,
    gurus: Array,
    jenisUjians: Array,
    tahunAjarans: Array,
    ruangans: Array,
    kelass: Array,
    jurusans: Array,
    filters: Object,
});

const showForm = ref(false);
const showPeserta = ref(false);
const showUbahWaktu = ref(false);
const showHapusJawaban = ref(false);
const editData = ref(null);
const selectedJadwal = ref(null);

const showFilter = ref(false);
const searchQuery = ref(props.filters?.search || '');
let searchTimer = null;
const filterForm = ref({
    status_ujian: props.filters?.status_ujian || '',
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || ''
});

watch(searchQuery, (val) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        router.get(route('admin.cbt.jadwal-ujian.index'), {
            ...filterForm.value,
            search: val || undefined
        }, {
            preserveState: true,
            preserveScroll: true
        });
    }, 400);
});

const applyFilter = () => {
    router.get(route('admin.cbt.jadwal-ujian.index'), filterForm.value, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => showFilter.value = false
    });
};

const selectedIds = ref([]);
const selectAll = ref(false);

const openAddModal = () => {
    editData.value = null;
    showForm.value = true;
};

const openEditModal = (jadwal) => {
    editData.value = jadwal;
    showForm.value = true;
};

const openPesertaModal = (jadwal) => {
    selectedJadwal.value = jadwal;
    showPeserta.value = true;
};

const openUbahWaktuModal = () => {
    if (selectedIds.value.length === 0) {
        alert('Pilih minimal satu jadwal ujian untuk mengubah waktu!');
        return;
    }
    showUbahWaktu.value = true;
};

const openHapusJawabanModal = () => {
    if (selectedIds.value.length === 0) {
        alert('Pilih minimal satu jadwal ujian untuk menghapus jawaban!');
        return;
    }
    showHapusJawaban.value = true;
};

const hapus = (id) => {
    if (confirm('Hapus Data? Data tidak dapat dikembalikan!')) {
        router.delete(route('admin.cbt.jadwal-ujian.destroy', id), {
            preserveScroll: true
        });
    }
};

const generateSoal = (id) => {
    if (confirm('Tindakan ini akan menggenerate soal untuk peserta yang belum di-generate. Lanjutkan?')) {
        router.post(route('admin.cbt.jadwal-ujian.generate', id), {}, {
            preserveScroll: true,
            onSuccess: () => alert('Berhasil meng-generate soal!')
        });
    }
};

const toggleSelectAll = (e) => {
    selectAll.value = e.target.checked;
    if (selectAll.value) {
        selectedIds.value = props.jadwalUjian.data.map(item => item.id);
    } else {
        selectedIds.value = [];
    }
};

const updateSelectAllState = () => {
    selectAll.value = props.jadwalUjian.data.length > 0 && selectedIds.value.length === props.jadwalUjian.data.length;
};
</script>

<template>
    <Head title="Jadwal Ujian" />

    <DashboardLayout>
        <div class="p-1 sm:ml-0 mt-16" id="appJadwal">
            <div>
                <div class="p-4 bg-white dark:bg-slate-800 rounded-lg shadow-md border border-gray-100 dark:border-slate-700 mb-4">
                    <div class="flex justify-between items-center mb-4 flex-wrap gap-3">
                        <div>
                            <h2 class="text-xl font-bold text-gray-800 dark:text-white"><i class="fas fa-calendar-alt text-blue-600 mr-2"></i> Jadwal Ujian</h2>
                            <p class="text-sm text-gray-500">Atur dan terbitkan jadwal ujian untuk siswa.</p>
                        </div>
                        <div class="flex gap-2 flex-wrap justify-end">
                            <button title="Filter Pencarian" class="px-3 py-2 bg-white hover:bg-gray-50 text-gray-700 rounded-lg text-sm font-medium transition-all border border-gray-200 shadow-sm" @click="showFilter = true">
                                <i class="fas fa-filter text-gray-400"></i> Filter
                            </button>
                            <button title="Bersihkan Jawaban Ujian" class="px-3 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg text-sm font-medium transition-all border border-rose-200 shadow-sm" @click="openHapusJawabanModal">
                                <i class="fas fa-eraser"></i> Hapus Jawaban
                            </button>
                            <button title="Ubah Waktu Ujian" class="px-3 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-sm font-medium transition-all border border-emerald-200 shadow-sm" @click="openUbahWaktuModal">
                                <i class="fas fa-clock"></i> Ubah Waktu
                            </button>
                            <button title="Tambah Jadwal" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition-all shadow-md shadow-emerald-500/30" @click="openAddModal">
                                <i class="fas fa-plus"></i> Tambah Jadwal
                            </button>
                            <button title="Segarkan Data" class="px-3 py-2 bg-white hover:bg-gray-50 text-gray-700 rounded-lg text-sm font-medium transition-all border border-gray-200 shadow-sm" @click="router.reload()">
                                <i class="fas fa-sync-alt text-gray-400"></i> Refresh
                            </button>
                        </div>
                    </div>

                    <!-- Search bar -->
                    <div class="mb-4">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="text" v-model="searchQuery" placeholder="Cari nama jadwal ujian..." class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all">
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-slate-700">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400 display whitespace-nowrap" id="tbl">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-slate-700 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3 text-center">
                                        <input type="checkbox" :checked="selectAll" @change="toggleSelectAll">
                                    </th>
                                    <th class="px-4 py-3">Nama</th>
                                    <th class="px-4 py-3">Draft</th>
                                    <th class="px-4 py-3">Pengawas</th>
                                    <th class="px-4 py-3">Detail Ujian</th>
                                    <th class="px-4 py-3">Kelas</th>
                                    <th class="px-4 py-3">Waktu Ujian</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                    <th class="px-4 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="jadwalUjian.data.length === 0">
                                    <td colspan="9" class="px-4 py-6 text-center text-gray-500">Belum ada data jadwal ujian.</td>
                                </tr>
                                <tr v-for="item in jadwalUjian.data" :key="item.id" class="border-b dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-800">
                                    <td class="px-4 py-3 text-center">
                                        <input type="checkbox" v-model="selectedIds" :value="item.id" @change="updateSelectAllState">
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-gray-900 dark:text-white">{{ item.nama_ujian }}</div>
                                        <div class="text-xs text-gray-500">{{ item.jenis_ujian?.nama_jenis || '-' }}</div>
                                        <div v-if="!item.total_siswa || item.total_siswa == 0" class="mt-1">
                                            <span class="text-[10px] font-bold text-rose-600 bg-rose-50 px-2 py-1 rounded">0 Peserta</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center">
                                            <i class="fas fa-file-alt text-blue-500 mr-2"></i>
                                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ item.draft_ujian?.nama || '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                        {{ item.guru?.nama_lengkap || 'Pengawas' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col gap-1 text-xs">
                                            <span class="text-blue-600"><i class="fas fa-users w-4 text-center"></i> {{ item.total_siswa || 0 }} Peserta</span>
                                            <span class="text-emerald-600"><i class="fas fa-door-open w-4 text-center"></i> {{ item.ruangan?.nama_ruangan || 'Bebas' }}</span>
                                            <span class="text-amber-600"><i class="fas fa-key w-4 text-center"></i> {{ item.setting_token ? item.token : 'Tanpa Token' }}</span>
                                            <div class="flex gap-1 mt-1">
                                                <span v-if="item.setting_strict" class="bg-red-100 text-red-800 text-[10px] px-1.5 py-0.5 rounded font-bold">Strict</span>
                                                <span v-if="item.setting_multi_login" class="bg-purple-100 text-purple-800 text-[10px] px-1.5 py-0.5 rounded font-bold">Multi</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-1 max-w-[180px]">
                                            <template v-if="!item.kelas_concat || item.kelas_concat === ''">
                                                <span @click="openPesertaModal(item)" class="cursor-pointer inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-orange-100 text-orange-800 shadow-sm transition-colors hover:bg-orange-200"><i class="fas fa-users-slash mr-1"></i> Belum Ada Peserta</span>
                                            </template>
                                            <template v-else>
                                                <span v-for="k in item.kelas_concat.split(', ')" :key="k" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800">{{ k }}</span>
                                            </template>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-xs">
                                        <div class="text-gray-900 dark:text-white font-medium"><i class="far fa-calendar text-gray-400 mr-1"></i> {{ item.waktu_mulai }}</div>
                                        <div class="text-gray-900 dark:text-white font-medium mt-1"><i class="far fa-calendar-check text-gray-400 mr-1"></i> {{ item.waktu_selesai }}</div>
                                        <div class="text-gray-500 mt-1"><i class="far fa-clock text-gray-400 mr-1"></i> {{ item.durasi }} Menit</div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span v-if="item.status_ujian === 'AKTIF'" class="bg-emerald-100 text-emerald-800 text-xs font-medium px-2.5 py-0.5 rounded-full dark:bg-emerald-900 dark:text-emerald-300">Aktif</span>
                                        <span v-else-if="item.status_ujian === 'NONAKTIF'" class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded-full dark:bg-gray-700 dark:text-gray-300">Nonaktif</span>
                                        <span v-else class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full dark:bg-blue-900 dark:text-blue-300">Selesai</span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <Link :href="route('admin.cbt.jadwal-ujian.detail', item.id)" class="px-2 py-1 bg-emerald-100 text-emerald-700 hover:bg-emerald-200 rounded text-xs font-medium" title="Detail Progres Ujian">
                                                <i class="fas fa-tv"></i>
                                            </Link>
                                            <button @click="openPesertaModal(item)" class="px-2 py-1 bg-blue-100 text-blue-700 hover:bg-blue-200 rounded text-xs font-medium" title="Peserta Ujian">
                                                <i class="fas fa-users"></i>
                                            </button>
                                            <button @click="openEditModal(item)" class="px-2 py-1 bg-amber-100 text-amber-700 hover:bg-amber-200 rounded text-xs font-medium" title="Edit Jadwal">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button @click="hapus(item.id)" class="px-2 py-1 bg-rose-100 text-rose-700 hover:bg-rose-200 rounded text-xs font-medium" title="Hapus Jadwal">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="jadwalUjian.last_page > 1" class="flex items-center justify-between mt-4 px-2">
                        <div class="text-sm text-gray-500">
                            Menampilkan {{ jadwalUjian.from }}–{{ jadwalUjian.to }} dari {{ jadwalUjian.total }} jadwal
                        </div>
                        <div class="flex items-center gap-1">
                            <template v-for="link in jadwalUjian.links" :key="link.label">
                                <button
                                    v-if="link.url"
                                    @click="router.get(link.url, {}, { preserveState: true, preserveScroll: true })"
                                    :class="[
                                        'px-3 py-1.5 text-sm rounded-lg border transition-all',
                                        link.active
                                            ? 'bg-blue-600 text-white border-blue-600 font-bold shadow-sm'
                                            : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'
                                    ]"
                                    v-html="link.label"
                                ></button>
                                <span v-else class="px-3 py-1.5 text-sm text-gray-300" v-html="link.label"></span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
            
            <FormModal 
                :show="showForm" 
                :editData="editData" 
                :draftUjians="draftUjians"
                :gurus="gurus"
                :jenisUjians="jenisUjians"
                :tahunAjarans="tahunAjarans"
                :ruangans="ruangans"
                @close="showForm = false" 
            />

            <PesertaModal 
                :show="showPeserta" 
                :jadwal="selectedJadwal" 
                :kelass="kelass"
                :jurusans="jurusans"
                @close="showPeserta = false" 
            />

            <UbahWaktuModal 
                :show="showUbahWaktu"
                :selectedIds="selectedIds"
                :jadwals="jadwalUjian"
                @close="showUbahWaktu = false; selectedIds = []; selectAll = false;" 
            />

            <HapusJawabanModal 
                :show="showHapusJawaban"
                :selectedIds="selectedIds"
                @close="showHapusJawaban = false; selectedIds = []; selectAll = false;" 
            />

            <!-- Modal Filter -->
            <div v-if="showFilter" class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/50 backdrop-blur-sm px-4">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden border border-gray-200">
                    <div class="flex justify-between items-center p-5 border-b border-gray-100">
                        <h5 class="text-xl font-bold text-slate-800">Filter Jadwal Ujian</h5>
                        <button @click="showFilter = false" class="text-gray-400 hover:text-red-500 text-2xl transition-colors"><i class="fas fa-times-circle"></i></button>
                    </div>
                    <div class="p-5">
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Status Ujian</label>
                            <select v-model="filterForm.status_ujian" class="w-full p-3 border border-gray-300 rounded-lg bg-gray-50 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                                <option value="">Semua Status</option>
                                <option value="AKTIF">AKTIF</option>
                                <option value="NONAKTIF">NONAKTIF</option>
                                <option value="SELESAI">SELESAI</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Waktu Mulai (Dari)</label>
                            <input type="date" v-model="filterForm.start_date" class="w-full p-3 border border-gray-300 rounded-lg bg-gray-50 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Waktu Selesai (Sampai)</label>
                            <input type="date" v-model="filterForm.end_date" class="w-full p-3 border border-gray-300 rounded-lg bg-gray-50 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                        </div>
                        <div class="text-right mt-6 flex justify-end gap-3">
                            <button @click="showFilter = false" class="bg-gray-200 text-gray-700 px-5 py-2.5 rounded-lg font-bold shadow hover:bg-gray-300 transition-colors">Batal</button>
                            <button @click="applyFilter" class="bg-blue-600 text-white px-5 py-2.5 rounded-lg font-bold shadow hover:bg-blue-700 transition-colors">Terapkan Filter</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
