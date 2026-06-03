<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    tahun_aktif: Object,
    kelas: Array,
    mapel: Array,
    guru: Array,
    mapping: Array,
    active_tab: String
});

const currentTab = ref(props.active_tab || 'kelas');
const selectedKelas = ref('');
const selectedMapel = ref('');

const isSaving = ref(false);
const saveMessage = ref('');

// Switch Tab
const setTab = (tab) => {
    currentTab.value = tab;
    selectedKelas.value = '';
    selectedMapel.value = '';
    
    // Update URL without reloading page
    const url = new URL(window.location.href);
    url.searchParams.set('tab', tab);
    window.history.pushState({}, '', url);
};

// Filtered data for Tab "Kelas"
const filteredMapelForKelas = computed(() => {
    if (!selectedKelas.value) return [];
    
    const kelasObj = props.kelas.find(k => k.id === selectedKelas.value);
    if (!kelasObj) return [];
    
    return props.mapel.filter(m => {
        // Jika jurusan_id = 0 (Semua/Umum)
        if (!m.jurusan_id || m.jurusan_id === '0') return true;
        
        // Jika Mapel khusus jurusan tertentu
        const jurusanIds = m.jurusan_id.split(',');
        return jurusanIds.includes(kelasObj.id_jurusan.toString());
    });
});

// Filtered data for Tab "Mapel"
const filteredKelasForMapel = computed(() => {
    if (!selectedMapel.value) return [];
    
    const mapelObj = props.mapel.find(m => m.id === selectedMapel.value);
    if (!mapelObj) return [];
    
    return props.kelas.filter(k => {
        if (!mapelObj.jurusan_id || mapelObj.jurusan_id === '0') return true;
        
        const jurusanIds = mapelObj.jurusan_id.split(',');
        return jurusanIds.includes(k.id_jurusan.toString());
    });
});

// Get current assigned Guru
const getAssignedGuru = (id_kelas, id_mapel) => {
    const map = props.mapping.find(m => m.id_kelas === id_kelas && m.id_mapel === id_mapel);
    return map ? map.id_guru : '';
};

// Get current Beban Jam
const getAssignedBebanJam = (id_kelas, id_mapel) => {
    const map = props.mapping.find(m => m.id_kelas === id_kelas && m.id_mapel === id_mapel);
    return map ? map.beban_jam : 0;
};

// Auto Save Mapping
const updateMapping = async (id_kelas, id_mapel, id_guru, beban_jam) => {
    isSaving.value = true;
    saveMessage.value = 'Menyimpan...';
    
    try {
        const response = await axios.post('/admin/kurikulum/pembagian-tugas/update', {
            id_kelas: id_kelas,
            id_mapel: id_mapel,
            id_guru: id_guru || null,
            beban_jam: beban_jam || 0
        });
        
        // Update local mapping array to reflect changes immediately
        const existingIndex = props.mapping.findIndex(m => m.id_kelas === id_kelas && m.id_mapel === id_mapel);
        if (id_guru || beban_jam) {
            if (existingIndex >= 0) {
                props.mapping[existingIndex].id_guru = id_guru;
                props.mapping[existingIndex].beban_jam = beban_jam;
            } else {
                props.mapping.push({ id_kelas, id_mapel, id_guru, beban_jam });
            }
        } else {
            if (existingIndex >= 0) {
                props.mapping.splice(existingIndex, 1);
            }
        }
        
        saveMessage.value = 'Tersimpan ✓';
        setTimeout(() => { saveMessage.value = ''; }, 2000);
    } catch (error) {
        console.error(error);
        saveMessage.value = 'Gagal menyimpan ✕';
    } finally {
        isSaving.value = false;
    }
};

</script>

<template>
    <Head title="Pembagian Tugas Mengajar" />

    <DashboardLayout>
        <div class="flex flex-col h-full">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Sticky Header -->
                <div class="sticky top-0 z-20 bg-[#f4f6f8] dark:bg-gray-900 pt-6 pb-3 px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                        <div>
                            <h2 class="font-bold text-2xl text-gray-900 dark:text-white tracking-tight">Pembagian Tugas Mengajar</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Tahun Ajaran Aktif: <span class="font-bold text-primary-600">{{ tahun_aktif ? tahun_aktif.tahun_ajaran : 'Belum Disetting' }}</span>
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <span v-if="saveMessage" :class="{'text-green-600': saveMessage.includes('✓'), 'text-red-600': saveMessage.includes('✕'), 'text-gray-500': saveMessage === 'Menyimpan...'}" class="font-medium text-sm flex items-center gap-2 bg-white dark:bg-gray-800 px-4 py-2 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 transition-all">
                                <i class="fas" :class="{'fa-spinner fa-spin': saveMessage === 'Menyimpan...', 'fa-check-circle': saveMessage.includes('✓'), 'fa-times-circle': saveMessage.includes('✕')}"></i>
                                {{ saveMessage }}
                            </span>
                            <Link href="/admin/kurikulum/jadwal-pelajaran" class="bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400 font-semibold py-2.5 px-4 rounded-xl text-sm transition-all flex items-center gap-2 border border-blue-200 dark:border-blue-800">
                                <i class="fas fa-calendar-alt"></i> Lihat Jadwal
                            </Link>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <div class="flex space-x-1 bg-gray-200/50 dark:bg-gray-800/50 p-1 rounded-2xl w-full max-w-md">
                        <button @click="setTab('kelas')" :class="{'bg-white dark:bg-gray-700 shadow-sm text-gray-900 dark:text-white': currentTab === 'kelas', 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300': currentTab !== 'kelas'}" class="w-full py-2.5 text-sm font-semibold rounded-xl transition-all">
                            <i class="fas fa-users-class mr-2"></i> Berdasarkan Kelas
                        </button>
                        <button @click="setTab('mapel')" :class="{'bg-white dark:bg-gray-700 shadow-sm text-gray-900 dark:text-white': currentTab === 'mapel', 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300': currentTab !== 'mapel'}" class="w-full py-2.5 text-sm font-semibold rounded-xl transition-all">
                            <i class="fas fa-book mr-2"></i> Berdasarkan Mapel
                        </button>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="px-4 sm:px-6 lg:px-8 pb-8 mt-4">
                    
                    <!-- TAB 1: Berdasarkan Kelas -->
                    <div v-if="currentTab === 'kelas'" class="space-y-6">
                        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pilih Kelas</label>
                            <select v-model="selectedKelas" class="w-full max-w-md border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500 shadow-sm text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2.5 pl-3 pr-8 cursor-pointer">
                                <option value="">-- Pilih Kelas --</option>
                                <option v-for="k in kelas" :key="k.id" :value="k.id">{{ k.nama_kelas }} - {{ k.jurusan?.nama_jurusan }}</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-2"><i class="fas fa-info-circle"></i> Setelah memilih kelas, daftar mata pelajaran yang sesuai dengan jurusan kelas tersebut akan muncul di bawah.</p>
                        </div>

                        <div v-if="selectedKelas" class="bg-white dark:bg-gray-800 shadow-xl shadow-gray-200/50 dark:shadow-none rounded-3xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                                            <th class="px-6 py-5 font-bold w-16">No</th>
                                            <th class="px-6 py-5 font-bold">Mata Pelajaran</th>
                                            <th class="px-6 py-5 font-bold">Kelompok</th>
                                            <th class="px-6 py-5 font-bold w-1/3">Guru Pengampu</th>
                                            <th class="px-6 py-5 font-bold w-24">Beban Jam</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                        <tr v-for="(m, index) in filteredMapelForKelas" :key="m.id" class="hover:bg-gray-50/80 dark:hover:bg-gray-700/50 transition-colors">
                                            <td class="px-6 py-4 font-medium text-gray-500">{{ index + 1 }}</td>
                                            <td class="px-6 py-4">
                                                <div class="font-bold text-gray-900 dark:text-white">{{ m.nama_mapel }}</div>
                                                <div class="text-xs text-gray-500">{{ m.kode_mapel }}</div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="px-2.5 py-1 text-xs font-semibold rounded-lg" :class="{'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400': m.jurusan_id === '0', 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400': m.jurusan_id !== '0'}">
                                                    {{ m.jurusan_id === '0' ? 'Pelajaran Umum' : 'Kejuruan' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <select 
                                                    :value="getAssignedGuru(selectedKelas, m.id)" 
                                                    @change="updateMapping(selectedKelas, m.id, $event.target.value, getAssignedBebanJam(selectedKelas, m.id))"
                                                    class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500 shadow-sm text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2 pl-3 pr-8 cursor-pointer"
                                                    :class="{'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800': getAssignedGuru(selectedKelas, m.id)}"
                                                >
                                                    <option value="">-- Tidak Diajarkan --</option>
                                                    <option v-for="g in guru" :key="g.id" :value="g.id">{{ g.nama_lengkap }}</option>
                                                </select>
                                            </td>
                                            <td class="px-6 py-4">
                                                <input 
                                                    type="number" 
                                                    min="0" 
                                                    max="20"
                                                    :value="getAssignedBebanJam(selectedKelas, m.id)"
                                                    @change="updateMapping(selectedKelas, m.id, getAssignedGuru(selectedKelas, m.id), parseInt($event.target.value))"
                                                    class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500 shadow-sm text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2 px-3 text-center"
                                                    :class="{'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800': getAssignedBebanJam(selectedKelas, m.id) > 0}"
                                                >
                                            </td>
                                        </tr>
                                        <tr v-if="filteredMapelForKelas.length === 0">
                                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">Tidak ada mapel ditemukan untuk kelas ini.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: Berdasarkan Mapel -->
                    <div v-if="currentTab === 'mapel'" class="space-y-6">
                        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pilih Mata Pelajaran</label>
                            <select v-model="selectedMapel" class="w-full max-w-md border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500 shadow-sm text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2.5 pl-3 pr-8 cursor-pointer">
                                <option value="">-- Pilih Mapel --</option>
                                <option v-for="m in mapel" :key="m.id" :value="m.id">{{ m.nama_mapel }} ({{ m.jurusan_id === '0' ? 'Umum' : 'Kejuruan' }})</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-2"><i class="fas fa-info-circle"></i> Setelah memilih mapel, daftar kelas yang relevan dengan mapel tersebut akan muncul di bawah.</p>
                        </div>

                        <div v-if="selectedMapel" class="bg-white dark:bg-gray-800 shadow-xl shadow-gray-200/50 dark:shadow-none rounded-3xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                                            <th class="px-6 py-5 font-bold w-16">No</th>
                                            <th class="px-6 py-5 font-bold">Kelas</th>
                                            <th class="px-6 py-5 font-bold">Jurusan</th>
                                            <th class="px-6 py-5 font-bold w-1/3">Guru Pengampu</th>
                                            <th class="px-6 py-5 font-bold w-24">Beban Jam</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                        <tr v-for="(k, index) in filteredKelasForMapel" :key="k.id" class="hover:bg-gray-50/80 dark:hover:bg-gray-700/50 transition-colors">
                                            <td class="px-6 py-4 font-medium text-gray-500">{{ index + 1 }}</td>
                                            <td class="px-6 py-4">
                                                <div class="font-bold text-gray-900 dark:text-white">{{ k.nama_kelas }}</div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-600 dark:text-gray-300">{{ k.jurusan?.nama_jurusan }}</div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <select 
                                                    :value="getAssignedGuru(k.id, selectedMapel)" 
                                                    @change="updateMapping(k.id, selectedMapel, $event.target.value, getAssignedBebanJam(k.id, selectedMapel))"
                                                    class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500 shadow-sm text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2 pl-3 pr-8 cursor-pointer"
                                                    :class="{'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800': getAssignedGuru(k.id, selectedMapel)}"
                                                >
                                                    <option value="">-- Tidak Diajarkan --</option>
                                                    <option v-for="g in guru" :key="g.id" :value="g.id">{{ g.nama_lengkap }}</option>
                                                </select>
                                            </td>
                                            <td class="px-6 py-4">
                                                <input 
                                                    type="number" 
                                                    min="0" 
                                                    max="20"
                                                    :value="getAssignedBebanJam(k.id, selectedMapel)"
                                                    @change="updateMapping(k.id, selectedMapel, getAssignedGuru(k.id, selectedMapel), parseInt($event.target.value))"
                                                    class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500 shadow-sm text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2 px-3 text-center"
                                                    :class="{'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800': getAssignedBebanJam(k.id, selectedMapel) > 0}"
                                                >
                                            </td>
                                        </tr>
                                        <tr v-if="filteredKelasForMapel.length === 0">
                                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">Tidak ada kelas ditemukan untuk mapel ini.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
