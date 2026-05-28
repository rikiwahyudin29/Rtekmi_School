<script setup>
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    show: Boolean,
    jadwal: Object,
    jurusans: Array
});

const emit = defineEmits(['close']);

const isAddMode = ref(false);
const isLoading = ref(false);

const pesertaTerdaftar = ref([]);
const masterSiswa = ref([]);
const kelass = ref([]);

// Filter States
const filterTerdaftar = ref('');
const filterQuery = ref('');

const filteredPesertaTerdaftar = computed(() => {
    return pesertaTerdaftar.value.filter(p => {
        const matchKelas = filterTerdaftar.value ? p.kelas_id == filterTerdaftar.value : true;
        const matchQuery = filterQuery.value ? (p.nama?.toLowerCase().includes(filterQuery.value.toLowerCase()) || p.nis?.toLowerCase().includes(filterQuery.value.toLowerCase()) || p.nisn?.toLowerCase().includes(filterQuery.value.toLowerCase())) : true;
        return matchKelas && matchQuery;
    });
});

const filterStatus = ref('');
const filterTingkat = ref('');
const filterJurusan = ref('');
const filterKelas = ref('');
const filterAgama = ref('');
const masterQuery = ref('');

const filteredMasterSiswa = computed(() => {
    return masterSiswa.value.filter(s => {
        const matchKelas = filterKelas.value ? s.kelas_id == filterKelas.value : true;
        const matchTingkat = filterTingkat.value ? s.tingkat == filterTingkat.value : true; // Assuming tingkat exists
        const matchJurusan = filterJurusan.value ? s.jurusan_id == filterJurusan.value : true; // Assuming jurusan exists
        const matchAgama = filterAgama.value ? s.agama == filterAgama.value : true; // Assuming agama exists
        const matchStatus = filterStatus.value ? s.status_siswa == filterStatus.value : true; // Assuming status exists
        const matchQuery = masterQuery.value ? (s.nama?.toLowerCase().includes(masterQuery.value.toLowerCase()) || s.nis?.toLowerCase().includes(masterQuery.value.toLowerCase()) || s.nisn?.toLowerCase().includes(masterQuery.value.toLowerCase())) : true;
        
        return matchKelas && matchTingkat && matchJurusan && matchAgama && matchStatus && matchQuery;
    });
});

const fetchPeserta = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get(route('admin.cbt.jadwal-ujian.peserta', props.jadwal.id));
        const allStudents = response.data.data;
        pesertaTerdaftar.value = allStudents.filter(s => s.exists);
        masterSiswa.value = allStudents;
    } catch (error) {
        console.error(error);
    } finally {
        isLoading.value = false;
    }
};

const fetchKelas = async () => {
    try {
        const response = await axios.get(route('admin.cbt.jadwal-ujian.master-kelas'));
        kelass.value = response.data.data;
    } catch (error) {
        console.error(error);
    }
};

watch(() => props.show, (newVal) => {
    if (newVal) {
        fetchPeserta();
        if (kelass.value.length === 0) fetchKelas();
    }
}, { immediate: true });

const close = () => {
    emit('close');
    isAddMode.value = false;
    pesertaTerdaftar.value = [];
    masterSiswa.value = [];
};

const simpanPeserta = async () => {
    isLoading.value = true;
    try {
        const ids = pesertaTerdaftar.value.map(p => p.id);
        await axios.put(route('admin.cbt.jadwal-ujian.sync-peserta', props.jadwal.id), { peserta: ids });
        alert('Berhasil menyimpan peserta ujian');
        fetchPeserta();
    } catch (error) {
        alert('Gagal menyimpan peserta');
        console.error(error);
    } finally {
        isLoading.value = false;
    }
};

const addPeserta = (siswa) => {
    if (!pesertaTerdaftar.value.find(p => p.id === siswa.id)) {
        pesertaTerdaftar.value.push(siswa);
        siswa.exists = true; 
    }
};

const removePeserta = (id) => {
    pesertaTerdaftar.value = pesertaTerdaftar.value.filter(p => p.id !== id);
    const masterIdx = masterSiswa.value.findIndex(s => s.id === id);
    if(masterIdx !== -1) masterSiswa.value[masterIdx].exists = false;
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center overflow-hidden bg-gray-900/50 backdrop-blur-sm transition-all">
        <!-- Modal Container -->
        <div class="relative w-full max-w-6xl max-h-[95vh] bg-white dark:bg-gray-800 rounded-3xl shadow-2xl flex flex-col border border-gray-100 dark:border-gray-700/60 m-4 overflow-hidden transform transition-all">
            
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-100 dark:border-gray-700/60 bg-indigo-50 dark:bg-indigo-900/10">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl shadow-sm border border-indigo-200 dark:border-indigo-800/50">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white">Kelola Peserta Ujian</h3>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ jadwal?.nama_ujian || 'Memuat...' }}</p>
                    </div>
                </div>
                <button @click="close" class="w-10 h-10 rounded-full bg-gray-100 hover:bg-rose-100 text-gray-500 hover:text-rose-600 flex items-center justify-center transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="p-6 overflow-y-auto flex-grow custom-scrollbar">
                
                <div v-if="isLoading" class="flex flex-col justify-center items-center py-12">
                    <i class="fas fa-circle-notch fa-spin text-4xl text-indigo-500 mb-3"></i>
                    <span class="text-gray-500 font-medium">Memuat data peserta...</span>
                </div>

                <div v-else class="flex flex-col md:flex-row gap-6 h-full">
                    
                    <!-- PANEL KIRI: Peserta Terdaftar -->
                    <div :class="isAddMode ? 'w-full md:w-1/2' : 'w-full'" class="transition-all duration-300 flex flex-col h-full">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="font-bold text-gray-800 dark:text-white text-lg flex items-center gap-2">
                                <i class="fas fa-user-check text-emerald-500"></i> Peserta Terdaftar
                            </h4>
                            <button @click="isAddMode = !isAddMode" class="text-xs bg-indigo-100 hover:bg-indigo-200 text-indigo-700 px-4 py-2 rounded-xl font-bold transition-colors shadow-sm">
                                <i class="fas" :class="isAddMode ? 'fa-arrow-left' : 'fa-user-plus'"></i> 
                                {{ isAddMode ? 'Tutup Panel Tambah' : 'Tambah Peserta' }}
                            </button>
                        </div>

                        <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-700/60 flex-grow flex flex-col">
                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <select v-model="filterTerdaftar" class="bg-white border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                                    <option value="">Semua Kelas</option>
                                    <option v-for="k in kelass" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                                </select>
                                <input type="text" v-model="filterQuery" placeholder="Cari peserta..." class="bg-white border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                            </div>

                            <div class="overflow-x-auto overflow-y-auto flex-grow max-h-[45vh] rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400 relative">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-900/80 dark:text-gray-400 sticky top-0 z-10 shadow-sm">
                                        <tr>
                                            <th class="px-4 py-3">Nama & Identitas Siswa</th>
                                            <th class="px-4 py-3 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        <tr v-if="filteredPesertaTerdaftar.length === 0">
                                            <td colspan="2" class="px-4 py-8 text-center text-gray-500 font-medium">Belum ada peserta di jadwal ini atau sesuai pencarian.</td>
                                        </tr>
                                        <tr v-for="v in filteredPesertaTerdaftar" :key="v.id" class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                            <td class="px-4 py-3">
                                                <div class="font-bold text-gray-900 dark:text-white">{{ v.nama }}</div>
                                                <div class="text-xs text-gray-500 mt-1 flex items-center gap-3">
                                                    <span><i class="fas fa-id-card"></i> {{ v.nis || v.nisn }}</span>
                                                    <span><i class="fas fa-door-open"></i> {{ v.kelas?.nama_kelas || '-' }}</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <button @click="removePeserta(v.id)" class="text-rose-500 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 w-8 h-8 rounded-lg flex items-center justify-center transition-colors">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="flex justify-between items-center pt-4 mt-4 border-t border-gray-200 dark:border-gray-700">
                                <span class="text-sm font-bold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">{{ pesertaTerdaftar.length }} Terdaftar</span>
                                <button @click="simpanPeserta" class="text-white bg-emerald-500 hover:bg-emerald-600 font-bold rounded-xl text-sm px-5 py-2.5 transition-all shadow-md shadow-emerald-500/20 flex items-center gap-2">
                                    <i class="fas fa-save"></i> Simpan Peserta
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- PANEL KANAN: Master Data Siswa -->
                    <div v-if="isAddMode" class="w-full md:w-1/2 flex flex-col h-full border-t md:border-t-0 md:border-l border-gray-200 dark:border-gray-700 md:pl-6 pt-6 md:pt-0">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="font-bold text-indigo-800 dark:text-indigo-400 text-lg flex items-center gap-2">
                                <i class="fas fa-database text-indigo-500"></i> Master Data Siswa
                            </h4>
                        </div>

                        <div class="p-4 bg-indigo-50/50 dark:bg-indigo-900/10 rounded-2xl border border-indigo-100 dark:border-indigo-900/50 flex-grow flex flex-col">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1">Filter Status</label>
                                    <select v-model="filterStatus" class="bg-white border-gray-200 text-gray-900 text-xs rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2 py-1.5 dark:bg-gray-800 dark:border-gray-700 dark:text-white shadow-sm">
                                        <option value="">Semua Status</option>
                                        <option value="Aktif">Aktif</option>
                                        <option value="Lulus">Lulus</option>
                                        <option value="Keluar">Keluar</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1">Tingkat</label>
                                    <select v-model="filterTingkat" class="bg-white border-gray-200 text-gray-900 text-xs rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2 py-1.5 dark:bg-gray-800 dark:border-gray-700 dark:text-white shadow-sm">
                                        <option value="">Semua Tingkat</option>
                                        <option value="10">10</option>
                                        <option value="11">11</option>
                                        <option value="12">12</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1">Jurusan</label>
                                    <select v-model="filterJurusan" class="bg-white border-gray-200 text-gray-900 text-xs rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2 py-1.5 dark:bg-gray-800 dark:border-gray-700 dark:text-white shadow-sm">
                                        <option value="">Semua Jurusan</option>
                                        <option v-for="j in jurusans" :key="j.id" :value="j.id">{{ j.nama_jurusan || j.kode_jurusan }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1">Kelas</label>
                                    <select v-model="filterKelas" class="bg-white border-gray-200 text-gray-900 text-xs rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2 py-1.5 dark:bg-gray-800 dark:border-gray-700 dark:text-white shadow-sm">
                                        <option value="">Semua</option>
                                        <option v-for="k in kelass" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1">Agama</label>
                                    <select v-model="filterAgama" class="bg-white border-gray-200 text-gray-900 text-xs rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2 py-1.5 dark:bg-gray-800 dark:border-gray-700 dark:text-white shadow-sm">
                                        <option value="">Semua Agama</option>
                                        <option value="Islam">Islam</option>
                                        <option value="Kristen">Kristen</option>
                                        <option value="Katolik">Katolik</option>
                                        <option value="Hindu">Hindu</option>
                                        <option value="Buddha">Buddha</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-indigo-500 mb-1">Pencarian</label>
                                    <input type="text" v-model="masterQuery" placeholder="Cari Siswa..." class="bg-white border-gray-200 text-gray-900 text-xs rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2 py-1.5 dark:bg-gray-800 dark:border-gray-700 dark:text-white shadow-sm">
                                </div>
                            </div>

                            <div class="relative overflow-x-auto overflow-y-auto flex-grow max-h-[45vh] rounded-xl border border-indigo-200 dark:border-indigo-800/50 bg-white dark:bg-gray-800">
                                <div v-if="isMasterLoading" class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 z-20 flex flex-col justify-center items-center">
                                    <i class="fas fa-spinner fa-spin text-3xl text-indigo-500 mb-2"></i>
                                    <span class="text-xs font-bold text-indigo-600">Menarik data...</span>
                                </div>

                                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-900/80 dark:text-gray-400 sticky top-0 z-10 shadow-sm">
                                        <tr>
                                            <th class="px-4 py-3">Nama Siswa</th>
                                            <th class="px-4 py-3 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        <tr v-if="filteredMasterSiswa.length === 0">
                                            <td colspan="2" class="px-4 py-8 text-center text-gray-500 font-medium">Tidak ada siswa ditemukan.</td>
                                        </tr>
                                        <tr v-for="v in filteredMasterSiswa" :key="v.id" class="bg-white dark:bg-gray-800 hover:bg-indigo-50 dark:hover:bg-indigo-900/30" :class="v.exists ? 'opacity-60 bg-gray-50 dark:bg-gray-900' : ''">
                                            <td class="px-4 py-3">
                                                <div class="font-bold" :class="v.exists ? 'text-gray-500' : 'text-gray-900 dark:text-white'">{{ v.nama }}</div>
                                                <div class="text-xs mt-1 flex items-center gap-3" :class="v.exists ? 'text-gray-400' : 'text-gray-500'">
                                                    <span><i class="fas fa-id-card"></i> {{ v.nis || v.nisn }}</span>
                                                    <span v-if="v.exists" class="text-emerald-600 dark:text-emerald-400 font-bold italic"><i class="fas fa-check mr-1"></i>Ditambahkan</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <button v-if="!v.exists" @click="addPeserta(v)" class="text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 w-8 h-8 rounded-lg flex items-center justify-center transition-colors">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                                <i v-else class="fas fa-check-circle text-emerald-500 text-lg"></i>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="flex justify-between items-center pt-4 mt-4">
                                <span class="text-sm font-bold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700">{{ filteredMasterSiswa.length }} Siswa</span>
                                <button @click="() => filteredMasterSiswa.forEach(s => { if(!s.exists) addPeserta(s) })" class="text-indigo-600 bg-indigo-50 hover:bg-indigo-100 font-bold rounded-xl text-sm px-4 py-2 transition-all border border-indigo-200 shadow-sm flex items-center gap-2">
                                    <i class="fas fa-plus-circle"></i> Tambah Semua
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>
