<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    jadwalId: [String, Number],
    show: Boolean
});
const emit = defineEmits(['close', 'saved']);

const isLoading = ref(false);
const errorMsg = ref('');
const addMode = ref(false);

const pesertaTerdaftar = ref([]);
const masterKelas = ref([]);
const masterSiswa = ref([]);

// Filters
const filterTerdaftar = ref({
    kelas: '0',
    query: ''
});

const filterMaster = ref({
    kelas: '',
    query: '',
    status: '0' // 0: semua, 1: sudah terdaftar, 2: belum
});

const loadPeserta = async () => {
    isLoading.value = true;
    errorMsg.value = '';
    try {
        const response = await axios.get(route('admin.cbt.jadwal-ujian.peserta', props.jadwalId));
        pesertaTerdaftar.value = response.data.data.map(p => ({
            ...p,
            selected: false,
            nama_kelas: p.kelas?.nama_kelas || '-'
        }));
    } catch (e) {
        errorMsg.value = 'Gagal memuat peserta terdaftar.';
    } finally {
        isLoading.value = false;
    }
};

const loadKelas = async () => {
    try {
        const response = await axios.get(route('admin.cbt.jadwal-ujian.master-kelas'));
        masterKelas.value = response.data.data;
        if(masterKelas.value.length > 0) {
            filterMaster.value.kelas = masterKelas.value[0].id;
        }
    } catch (e) {
        console.error(e);
    }
};

const loadMasterSiswa = async () => {
    if (!filterMaster.value.kelas && masterKelas.value.length === 0) return;
    
    isLoading.value = true;
    try {
        const response = await axios.get(route('admin.cbt.jadwal-ujian.master-siswa', props.jadwalId), {
            params: {
                kelas_id: filterMaster.value.kelas !== '0' ? filterMaster.value.kelas : null,
                search: filterMaster.value.query
            }
        });
        masterSiswa.value = response.data.data.map(s => ({
            ...s,
            selected: false
        }));
    } catch (e) {
        errorMsg.value = 'Gagal memuat master siswa.';
    } finally {
        isLoading.value = false;
    }
};

const onFilterMasterChange = () => {
    loadMasterSiswa();
};

onMounted(() => {
    loadKelas();
    loadPeserta();
});

// Computed properties for filtering
const filteredPeserta = computed(() => {
    let result = pesertaTerdaftar.value;
    if (filterTerdaftar.value.kelas !== '0') {
        result = result.filter(p => p.kelas?.id == filterTerdaftar.value.kelas);
    }
    if (filterTerdaftar.value.query) {
        const q = filterTerdaftar.value.query.toLowerCase();
        result = result.filter(p => 
            p.nama.toLowerCase().includes(q) || 
            p.nis.toLowerCase().includes(q)
        );
    }
    return result;
});

const filteredMasterSiswa = computed(() => {
    let result = masterSiswa.value;
    
    if (filterMaster.value.status === '1') {
        result = result.filter(s => s.exists);
    } else if (filterMaster.value.status === '2') {
        result = result.filter(s => !s.exists);
    }

    if (filterMaster.value.query) {
        const q = filterMaster.value.query.toLowerCase();
        result = result.filter(s => 
            s.nama.toLowerCase().includes(q) || 
            (s.nis && s.nis.toLowerCase().includes(q))
        );
    }
    return result;
});

// Actions
const toggleCheckAllTerdaftar = (e) => {
    filteredPeserta.value.forEach(p => p.selected = e.target.checked);
};

const toggleCheckAllMaster = (e) => {
    filteredMasterSiswa.value.forEach(s => {
        if (!s.exists) s.selected = e.target.checked;
    });
};

const hapusTerpilih = () => {
    const selectedCount = pesertaTerdaftar.value.filter(p => p.selected).length;
    if (selectedCount > 0) {
        pesertaTerdaftar.value = pesertaTerdaftar.value.filter(p => !p.selected);
    } else {
        // Hapus sesuai filter jika tidak ada yg dicentang tapi tombol ditekan
        if (filterTerdaftar.value.kelas !== '0') {
            pesertaTerdaftar.value = pesertaTerdaftar.value.filter(p => p.kelas?.id != filterTerdaftar.value.kelas);
        } else {
            pesertaTerdaftar.value = [];
        }
    }
};

const tambahKeJadwal = () => {
    filteredMasterSiswa.value.forEach(s => {
        if (s.selected && !s.exists) {
            const isExist = pesertaTerdaftar.value.some(p => p.id === s.id);
            if (!isExist) {
                s.selected = false;
                pesertaTerdaftar.value.push({
                    id: s.id,
                    nama: s.nama,
                    nis: s.nis,
                    nisn: s.nisn,
                    nama_kelas: masterKelas.value.find(k => k.id == filterMaster.value.kelas)?.nama_kelas || '-'
                });
                s.exists = true;
            }
        }
    });
};

const simpanPeserta = async () => {
    isLoading.value = true;
    errorMsg.value = '';
    try {
        const payload = pesertaTerdaftar.value.map(p => p.id);
        await axios.put(route('admin.cbt.jadwal-ujian.sync-peserta', props.jadwalId), {
            peserta: payload
        });
        window.Swal.fire('Berhasil', 'Daftar peserta berhasil disinkronisasi.', 'success');
        emit('saved');
        emit('close');
    } catch (e) {
        errorMsg.value = e.response?.data?.message || 'Gagal menyimpan peserta.';
        window.Swal.fire('Gagal', errorMsg.value, 'error');
    } finally {
        isLoading.value = false;
    }
};

</script>

<template>
    <div v-if="show" class="fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" @click="emit('close')"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl w-full">
                <!-- Header -->
                <div class="bg-indigo-50 border-b border-indigo-100 px-6 py-4 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-indigo-800 flex items-center gap-2" id="modal-title">
                        <i class="fas fa-users-cog"></i> Kelola Peserta Ujian
                    </h3>
                    <button @click="emit('close')" class="text-gray-400 hover:text-red-500 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Body -->
                <div class="bg-white px-6 py-5 h-[75vh] overflow-y-auto flex flex-col">
                    <div v-if="errorMsg" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-3">
                        <i class="fas fa-exclamation-circle text-lg"></i>
                        <span class="font-medium">{{ errorMsg }}</span>
                    </div>

                    <div class="flex flex-col md:flex-row gap-6 h-full">
                        <!-- Terdaftar Panel -->
                        <div :class="addMode ? 'w-full md:w-1/2' : 'w-full'" class="transition-all duration-300 flex flex-col h-full">
                            <div class="flex justify-between items-center mb-3">
                                <h4 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                                    <i class="fas fa-user-check text-emerald-500"></i> Peserta Terdaftar
                                </h4>
                                <button @click="addMode = !addMode" class="text-sm bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-4 py-2 rounded-lg border border-indigo-200 font-bold transition-colors">
                                    <i class="fas" :class="addMode ? 'fa-arrow-left' : 'fa-user-plus'"></i> 
                                    {{ addMode ? 'Tutup Panel Tambah' : 'Tambah Peserta' }}
                                </button>
                            </div>

                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 flex-grow flex flex-col">
                                <div class="grid grid-cols-2 gap-3 mb-3">
                                    <select v-model="filterTerdaftar.kelas" class="border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 w-full p-2">
                                        <option value="0">-- Semua Kelas --</option>
                                        <option v-for="k in masterKelas" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                                    </select>
                                    <input type="text" v-model="filterTerdaftar.query" placeholder="Cari nama / NIS..." class="border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 w-full p-2">
                                </div>

                                <div class="overflow-y-auto border border-gray-200 rounded-lg bg-white flex-grow relative">
                                    <div v-if="isLoading && !addMode" class="absolute inset-0 bg-white/80 z-10 flex flex-col items-center justify-center">
                                        <i class="fas fa-spinner fa-spin text-3xl text-indigo-500"></i>
                                    </div>
                                    <table class="w-full text-sm text-left">
                                        <thead class="bg-gray-50 sticky top-0 z-10 border-b border-gray-200">
                                            <tr>
                                                <th class="p-3 w-4">
                                                    <input type="checkbox" @change="toggleCheckAllTerdaftar" class="rounded text-red-500 border-gray-300 focus:ring-red-500">
                                                </th>
                                                <th class="px-3 py-3 font-semibold text-gray-700">Identitas Siswa</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            <tr v-if="filteredPeserta.length === 0">
                                                <td colspan="2" class="px-3 py-8 text-center text-gray-500 font-medium">Belum ada peserta di jadwal ini.</td>
                                            </tr>
                                            <tr v-for="p in filteredPeserta" :key="p.id" class="hover:bg-gray-50">
                                                <td class="p-3">
                                                    <input type="checkbox" v-model="p.selected" class="rounded text-red-500 border-gray-300 focus:ring-red-500">
                                                </td>
                                                <td class="px-3 py-2">
                                                    <div class="font-bold text-gray-900">{{ p.nama }}</div>
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        <i class="fas fa-id-card text-gray-400 mr-1"></i> {{ p.nis || '-' }} 
                                                        <span class="mx-2 text-gray-300">|</span> 
                                                        <i class="fas fa-door-open text-gray-400 mr-1"></i> {{ p.nama_kelas }}
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="flex justify-between items-center mt-4">
                                    <span class="text-sm font-bold text-gray-700 bg-white px-3 py-1.5 rounded-lg border border-gray-200 shadow-sm">
                                        {{ filteredPeserta.length }} Terdaftar
                                    </span>
                                    <div class="flex gap-2">
                                        <button @click="hapusTerpilih" class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 px-4 py-2 rounded-lg text-sm font-bold transition-colors">
                                            <i class="fas fa-trash-alt mr-1"></i> Hapus
                                        </button>
                                        <button @click="simpanPeserta" class="bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-2 rounded-lg text-sm font-bold shadow-md transition-colors">
                                            <i class="fas fa-save mr-1"></i> Simpan Peserta
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Add Master Panel -->
                        <div v-if="addMode" class="w-full md:w-1/2 flex flex-col h-full border-t md:border-t-0 md:border-l border-gray-200 md:pl-6 pt-6 md:pt-0">
                            <div class="flex justify-between items-center mb-3">
                                <h4 class="font-bold text-indigo-800 text-lg flex items-center gap-2">
                                    <i class="fas fa-database text-indigo-500"></i> Master Data Siswa
                                </h4>
                            </div>

                            <div class="p-4 bg-indigo-50/50 rounded-xl border border-indigo-100 flex-grow flex flex-col">
                                <div class="grid grid-cols-2 gap-3 mb-3">
                                    <select v-model="filterMaster.kelas" @change="onFilterMasterChange" class="border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 w-full p-2">
                                        <option value="0">-- Pilih Kelas --</option>
                                        <option v-for="k in masterKelas" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                                    </select>
                                    <select v-model="filterMaster.status" class="border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 w-full p-2">
                                        <option value="0">Semua Status</option>
                                        <option value="2">Belum Ditambahkan</option>
                                        <option value="1">Sudah Ditambahkan</option>
                                    </select>
                                    <div class="col-span-2">
                                        <input type="text" v-model="filterMaster.query" placeholder="Cari nama / NIS..." class="border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 w-full p-2">
                                    </div>
                                </div>

                                <div class="overflow-y-auto border border-indigo-200 rounded-lg bg-white flex-grow relative">
                                    <div v-if="isLoading" class="absolute inset-0 bg-white/80 z-10 flex flex-col items-center justify-center">
                                        <i class="fas fa-spinner fa-spin text-3xl text-indigo-500"></i>
                                    </div>
                                    <table class="w-full text-sm text-left">
                                        <thead class="bg-gray-50 sticky top-0 z-10 border-b border-gray-200">
                                            <tr>
                                                <th class="p-3 w-4">
                                                    <input type="checkbox" @change="toggleCheckAllMaster" class="rounded text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                                </th>
                                                <th class="px-3 py-3 font-semibold text-gray-700">Nama Siswa</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            <tr v-if="filteredMasterSiswa.length === 0">
                                                <td colspan="2" class="px-3 py-8 text-center text-gray-500 font-medium">Data siswa tidak ditemukan / pilih kelas.</td>
                                            </tr>
                                            <tr v-for="s in filteredMasterSiswa" :key="s.id" :class="s.exists ? 'bg-gray-50 opacity-60' : 'hover:bg-indigo-50/50'">
                                                <td class="p-3">
                                                    <input v-if="!s.exists" type="checkbox" v-model="s.selected" class="rounded text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                                    <i v-else class="fas fa-check-circle text-emerald-500"></i>
                                                </td>
                                                <td class="px-3 py-2">
                                                    <div class="font-bold" :class="s.exists ? 'text-gray-500' : 'text-gray-900'">{{ s.nama }}</div>
                                                    <div class="text-xs mt-1" :class="s.exists ? 'text-gray-400' : 'text-gray-500'">
                                                        <i class="fas fa-id-card mr-1"></i> {{ s.nis || '-' }}
                                                        <span v-if="s.exists" class="text-emerald-600 font-bold ml-2 italic"><i class="fas fa-check mr-1"></i>Sudah Masuk</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="flex justify-between items-center mt-4">
                                    <span class="text-sm font-bold text-gray-700 bg-white px-3 py-1.5 rounded-lg border border-indigo-200 shadow-sm">
                                        {{ filteredMasterSiswa.length }} Siswa
                                    </span>
                                    <button @click="tambahKeJadwal" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-bold shadow-md transition-colors">
                                        <i class="fas fa-plus-circle mr-1"></i> Tambah ke Jadwal
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
