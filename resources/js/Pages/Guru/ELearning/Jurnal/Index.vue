<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    jurnal: Array,
    jadwal: Array,
    jam_master: Array,
    hari_ini: String,
    tanggal: String,
    bulan: String,
    ta_aktif: Object,
});

import { computed } from 'vue';

const hariKerja = computed(() => {
    return props.ta_aktif?.hari_kerja == 5
        ? ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']
        : ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
});

const sortedJamMaster = computed(() => {
    return [...props.jam_master].sort((a, b) => a.urutan - b.urutan);
});

const gridData = computed(() => {
    const map = {};
    const occupied = {};
    const jamMaster = sortedJamMaster.value;

    for (const j of props.jadwal) {
        if (!j.jam_mulai || !j.jam_selesai) continue;

        const startIdx = jamMaster.findIndex(jm =>
            jm.jam_mulai.substring(0, 5) === j.jam_mulai.substring(0, 5)
        );
        if (startIdx === -1) continue;

        let endIdx = startIdx;
        for (let i = startIdx; i < jamMaster.length; i++) {
            endIdx = i;
            if (jamMaster[i].jam_selesai.substring(0, 5) === j.jam_selesai.substring(0, 5)) break;
        }

        const rowspan = endIdx - startIdx + 1;
        const key = `${j.hari}-${startIdx}`;
        map[key] = { jadwal: j, rowspan };

        for (let i = 1; i < rowspan; i++) {
            occupied[`${j.hari}-${startIdx + i}`] = true;
        }
    }

    return { map, occupied };
});

const getCellInfo = (hari, jamIdx) => {
    const key = `${hari}-${jamIdx}`;
    if (gridData.value.occupied[key]) return { type: 'occupied' };
    if (gridData.value.map[key]) return { type: 'filled', ...gridData.value.map[key] };
    return { type: 'empty' };
};

// Modal Detail Absensi
const isDetailModalOpen = ref(false);
const detailAbsen = ref([]);
const loadingDetail = ref(false);
const selectedJurnal = ref(null);

// Modal Form Jurnal
const isFormModalOpen = ref(false);
const selectedJadwal = ref(null);
const form = useForm({
    id_kelas: '',
    id_mapel: '',
    jam_ke: '',
    materi: '',
    keterangan: '',
    foto_kegiatan: null,
    tanggal: '',
});

const filterBulan = (e) => {
    router.get(route('guru.elearning.jurnal.index'), { bulan: e.target.value }, { preserveState: true });
};

const openDetailAbsen = async (item) => {
    selectedJurnal.value = item;
    isDetailModalOpen.value = true;
    loadingDetail.value = true;
    detailAbsen.value = [];

    try {
        const response = await fetch(route('guru.elearning.jurnal.detail', item.id));
        const data = await response.json();
        detailAbsen.value = data;
    } catch (error) {
        console.error('Failed to load detail:', error);
    } finally {
        loadingDetail.value = false;
    }
};

const closeDetailModal = () => {
    isDetailModalOpen.value = false;
    detailAbsen.value = [];
    selectedJurnal.value = null;
};

const deleteJurnal = (id) => {
    if (confirm('Yakin ingin menghapus jurnal ini? Data absensi siswa di mapel ini juga akan terhapus!')) {
        router.delete(route('guru.elearning.jurnal.hapus', id), {
            preserveScroll: true,
        });
    }
};

const formatTanggal = (tgl) => {
    if (!tgl) return '-';
    return new Date(tgl).toLocaleDateString('id-ID', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' });
};

const formatWaktu = (time) => {
    if (!time) return '-';
    return time.substring(0, 5);
};

const videoElement = ref(null);
const canvasElement = ref(null);
const cameraStream = ref(null);
const capturedImage = ref(null);

const startCamera = async () => {
    capturedImage.value = null;
    form.foto_kegiatan = null;
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
        cameraStream.value = stream;
        if (videoElement.value) {
            videoElement.value.srcObject = stream;
        }
    } catch (err) {
        console.error("Kamera tidak dapat diakses: ", err);
    }
};

const stopCamera = () => {
    if (cameraStream.value) {
        cameraStream.value.getTracks().forEach(track => track.stop());
        cameraStream.value = null;
    }
};

const capturePhoto = () => {
    if (!videoElement.value || !canvasElement.value) return;
    
    const context = canvasElement.value.getContext('2d');
    canvasElement.value.width = videoElement.value.videoWidth;
    canvasElement.value.height = videoElement.value.videoHeight;
    context.drawImage(videoElement.value, 0, 0, canvasElement.value.width, canvasElement.value.height);
    
    canvasElement.value.toBlob((blob) => {
        const file = new File([blob], `jurnal_${Date.now()}.png`, { type: 'image/png' });
        form.foto_kegiatan = file;
        capturedImage.value = URL.createObjectURL(blob);
        stopCamera();
    }, 'image/png');
};

const retakePhoto = () => {
    startCamera();
};

// Form Actions
const openForm = (j) => {
    selectedJadwal.value = j;
    
    // Hitung Jam Ke otomatis
    const jamMaster = sortedJamMaster.value;
    const startIdx = jamMaster.findIndex(jm => jm.jam_mulai.substring(0, 5) === j.jam_mulai.substring(0, 5));
    let endIdx = startIdx;
    for (let i = startIdx; i < jamMaster.length; i++) {
        endIdx = i;
        if (jamMaster[i].jam_selesai.substring(0, 5) === j.jam_selesai.substring(0, 5)) break;
    }
    const jamKeStr = (startIdx !== -1 && startIdx === endIdx) ? `${jamMaster[startIdx].urutan}` : 
                     (startIdx !== -1) ? `${jamMaster[startIdx].urutan}-${jamMaster[endIdx].urutan}` : '';

    form.id_kelas = j.id_kelas;
    form.id_mapel = j.id_mapel;
    form.tanggal = j.tanggal_kbm;
    form.jam_ke = jamKeStr; 
    form.materi = '';
    form.keterangan = '';
    form.foto_kegiatan = null;
    isFormModalOpen.value = true;
    startCamera();
};

const closeFormModal = () => {
    isFormModalOpen.value = false;
    selectedJadwal.value = null;
    stopCamera();
    form.reset();
};

const submitJurnal = () => {
    form.post(route('guru.elearning.jurnal.simpan'), {
        preserveScroll: true,
        onSuccess: () => closeFormModal(),
    });
};
</script>

<template>
    <Head title="Jurnal Mengajar KBM" />
    <DashboardLayout>
        <div class="space-y-8">
            
            <!-- HEADER JADWAL HARI INI -->
            <div>
                <div class="flex flex-col md:flex-row justify-between md:items-center gap-4 mb-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">🏫 Jadwal Mengajar Hari Ini</h1>
                        <p class="text-sm text-gray-500 mt-1">{{ hari_ini }}, {{ formatTanggal(tanggal) }}</p>
                    </div>
                </div>

                <!-- Grid Jadwal Mingguan (Jurnal KBM) -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mt-4">
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-sm text-left border-collapse min-w-[800px]">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 text-center border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-4 border-r border-gray-200 w-16 font-bold tracking-wider">Jam</th>
                                    <th class="px-4 py-4 border-r border-gray-200 w-32 font-bold tracking-wider">Waktu</th>
                                    <th v-for="hari in hariKerja" :key="hari" class="px-4 py-4 border-r border-gray-200 font-bold tracking-wider" :class="{'bg-indigo-50 text-indigo-700': hari === hari_ini}">{{ hari }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(jm, jamIdx) in sortedJamMaster" :key="jm.id" class="border-t border-gray-100">
                                    <td class="px-3 py-3 font-bold text-center border-r border-gray-200 bg-gray-50/50">{{ jm.urutan }}</td>
                                    <td class="px-3 py-3 text-center border-r border-gray-200 whitespace-nowrap bg-gray-50/50 text-xs text-gray-500 font-medium">
                                        {{ jm.jam_mulai.substring(0, 5) }} - {{ jm.jam_selesai.substring(0, 5) }}
                                    </td>
                                    
                                    <template v-if="jm.is_istirahat">
                                        <td :colspan="hariKerja.length" class="px-4 py-3 text-center bg-orange-50/50 border-gray-100">
                                            <span class="text-orange-600 font-bold tracking-[0.2em] text-xs uppercase">
                                                <i class="fas fa-utensils mr-2 opacity-50"></i>ISTIRAHAT
                                            </span>
                                        </td>
                                    </template>
                                    <template v-else>
                                        <template v-for="hari in hariKerja" :key="hari">
                                            <!-- Empty Cell -->
                                            <td v-if="getCellInfo(hari, jamIdx).type === 'empty'" class="px-3 py-3 border-r border-gray-100 text-center"></td>
                                            
                                            <!-- Filled Cell -->
                                            <td v-else-if="getCellInfo(hari, jamIdx).type === 'filled'"
                                                :rowspan="getCellInfo(hari, jamIdx).rowspan"
                                                class="border-r border-b border-gray-200 p-1 align-top relative group"
                                                :class="[
                                                    getCellInfo(hari, jamIdx).jadwal.status_kbm === 'Selesai' ? 'bg-emerald-50' : 
                                                    getCellInfo(hari, jamIdx).jadwal.status_kbm === 'Tugas Saja' ? 'bg-amber-50' : 
                                                    getCellInfo(hari, jamIdx).jadwal.status_kbm === 'Belum Waktunya' ? 'bg-gray-100' : 'bg-rose-50'
                                                ]">
                                                <div class="h-full w-full rounded-xl p-2.5 flex flex-col items-center justify-center text-center cursor-pointer transition hover:shadow-md"
                                                    :class="[
                                                        getCellInfo(hari, jamIdx).jadwal.status_kbm === 'Selesai' ? 'border-2 border-emerald-300 hover:border-emerald-500' : 
                                                        getCellInfo(hari, jamIdx).jadwal.status_kbm === 'Tugas Saja' ? 'border-2 border-amber-300 hover:border-amber-500' : 
                                                        getCellInfo(hari, jamIdx).jadwal.status_kbm === 'Belum Waktunya' ? 'border-2 border-gray-300 cursor-not-allowed opacity-70' : 'border-2 border-rose-300 hover:border-rose-500'
                                                    ]"
                                                    @click="getCellInfo(hari, jamIdx).jadwal.status_kbm !== 'Selesai' && getCellInfo(hari, jamIdx).jadwal.status_kbm !== 'Belum Waktunya' ? openForm(getCellInfo(hari, jamIdx).jadwal) : null">
                                                    
                                                    <div class="font-extrabold text-xs mb-1"
                                                        :class="[
                                                            getCellInfo(hari, jamIdx).jadwal.status_kbm === 'Selesai' ? 'text-emerald-700' : 
                                                            getCellInfo(hari, jamIdx).jadwal.status_kbm === 'Tugas Saja' ? 'text-amber-700' : 
                                                            getCellInfo(hari, jamIdx).jadwal.status_kbm === 'Belum Waktunya' ? 'text-gray-600' : 'text-rose-700'
                                                        ]">
                                                        {{ getCellInfo(hari, jamIdx).jadwal.nama_mapel }}
                                                    </div>
                                                    <div class="inline-flex items-center gap-1.5 bg-white rounded-md shadow-sm py-0.5 px-2 mb-2">
                                                        <span class="text-[10px] font-bold text-gray-700">
                                                            {{ getCellInfo(hari, jamIdx).jadwal.nama_kelas }}
                                                        </span>
                                                    </div>

                                                    <div v-if="getCellInfo(hari, jamIdx).jadwal.status_kbm === 'Selesai'" class="text-[10px] font-bold text-emerald-600 mt-auto bg-emerald-100 px-2 py-0.5 rounded-full w-full max-w-[90%] mx-auto truncate">
                                                        <i class="fas fa-check-circle mr-1"></i> Jurnal Terisi
                                                    </div>
                                                    <div v-else-if="getCellInfo(hari, jamIdx).jadwal.status_kbm === 'Tugas Saja'" class="text-[10px] font-bold text-amber-600 mt-auto bg-amber-100 px-2 py-0.5 rounded-full w-full max-w-[90%] mx-auto truncate">
                                                        <i class="fas fa-tasks mr-1"></i> Isi Jurnal
                                                    </div>
                                                    <div v-else-if="getCellInfo(hari, jamIdx).jadwal.status_kbm === 'Belum Waktunya'" class="text-[10px] font-bold text-gray-500 mt-auto bg-gray-200 px-2 py-0.5 rounded-full w-full max-w-[90%] mx-auto truncate">
                                                        <i class="fas fa-clock mr-1"></i> Belum Saatnya
                                                    </div>
                                                    <div v-else class="text-[10px] font-bold text-rose-600 mt-auto bg-rose-100 px-2 py-0.5 rounded-full w-full max-w-[90%] mx-auto truncate animate-pulse">
                                                        <i class="fas fa-exclamation-circle mr-1"></i> Isi Jurnal
                                                    </div>

                                                    <!-- Tooltip helper (visible on hover if needed) -->
                                                    <div v-if="getCellInfo(hari, jamIdx).jadwal.tanggal_kbm" class="absolute -top-2 -right-2 bg-gray-800 text-white text-[8px] px-1.5 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                                        {{ getCellInfo(hari, jamIdx).jadwal.tanggal_kbm }}
                                                    </div>
                                                </div>
                                            </td>
                                        </template>
                                    </template>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 bg-gray-50 border-t border-gray-200 text-xs flex flex-wrap gap-4 justify-center">
                        <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> <span class="font-semibold text-gray-600">Selesai (Sudah Isi Jurnal)</span></div>
                        <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-500"></span> <span class="font-semibold text-gray-600">Ada Tugas KBM</span></div>
                        <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-rose-500"></span> <span class="font-semibold text-gray-600">Belum Isi Jurnal</span></div>
                        <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-gray-400"></span> <span class="font-semibold text-gray-600">Belum Saatnya KBM</span></div>
                    </div>
                </div>
            </div>

            <!-- RIWAYAT JURNAL BULANAN -->
            <div>
                <div class="flex flex-col md:flex-row justify-between md:items-center gap-4 mb-4 mt-8">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800"><i class="fas fa-history text-indigo-500 mr-2"></i> Riwayat Jurnal KBM</h2>
                        <p class="text-sm text-gray-500 mt-1">Daftar jurnal yang telah Anda isi bulan ini.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="month" :value="bulan" @change="filterBulan" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm">
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[800px]">
                            <thead>
                                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-200">
                                    <th class="p-4 font-bold">No</th>
                                    <th class="p-4 font-bold">Tanggal & Mapel</th>
                                    <th class="p-4 font-bold">Materi KBM</th>
                                    <th class="p-4 font-bold">Jam Ke</th>
                                    <th class="p-4 font-bold text-center">Kehadiran</th>
                                    <th class="p-4 font-bold text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm">
                                <tr v-for="(item, index) in jurnal" :key="item.id" class="hover:bg-gray-50/50 transition">
                                    <td class="p-4 text-gray-500">{{ index + 1 }}</td>
                                    <td class="p-4">
                                        <p class="font-bold text-gray-800 mb-1">{{ formatTanggal(item.tanggal) }}</p>
                                        <div class="flex gap-2">
                                            <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded text-xs font-bold">{{ item.nama_mapel }}</span>
                                            <span class="px-2 py-0.5 bg-amber-50 text-amber-700 rounded text-xs font-bold">{{ item.nama_kelas }}</span>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <p class="font-semibold text-gray-800 line-clamp-2 max-w-xs">{{ item.materi }}</p>
                                        <p class="text-xs text-gray-500 mt-1 line-clamp-1 max-w-xs">{{ item.keterangan || '-' }}</p>
                                    </td>
                                    <td class="p-4">
                                        <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-lg font-bold text-xs">{{ item.jam_ke }}</span>
                                    </td>
                                    <td class="p-4 text-center">
                                        <button @click="openDetailAbsen(item)" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg text-xs font-bold hover:bg-blue-100 transition">
                                            <i class="fas fa-users"></i> Lihat
                                        </button>
                                    </td>
                                    <td class="p-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="route('guru.elearning.jurnal.absen', item.id)" class="w-8 h-8 flex items-center justify-center bg-gray-100 text-gray-600 rounded-full hover:bg-indigo-100 hover:text-indigo-600 transition tooltip" title="Edit Absensi">
                                                <i class="fas fa-user-edit text-xs"></i>
                                            </Link>
                                            <button @click="deleteJurnal(item.id)" class="w-8 h-8 flex items-center justify-center bg-gray-100 text-gray-600 rounded-full hover:bg-rose-100 hover:text-rose-600 transition tooltip" title="Hapus Jurnal">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div v-if="jurnal.length === 0" class="p-12 text-center text-gray-500">
                        <i class="fas fa-book-open text-4xl text-gray-300 mb-3"></i>
                        <p>Tidak ada catatan jurnal di bulan ini.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Detail Absensi -->
        <Modal :show="isDetailModalOpen" @close="closeDetailModal" maxWidth="sm">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-800">Detail Kehadiran Siswa</h2>
                        <p class="text-xs text-gray-500 mt-0.5">{{ selectedJurnal?.nama_mapel }} - {{ selectedJurnal?.nama_kelas }}</p>
                    </div>
                    <button @click="closeDetailModal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div v-if="loadingDetail" class="py-10 text-center">
                    <i class="fas fa-spinner fa-spin text-3xl text-indigo-500 mb-2"></i>
                    <p class="text-sm text-gray-500">Memuat data absensi...</p>
                </div>

                <div v-else class="max-h-[60vh] overflow-y-auto pr-2 space-y-2">
                    <div v-for="(s, i) in detailAbsen" :key="i" class="flex justify-between items-center p-3 bg-gray-50 rounded-xl border border-gray-100">
                        <span class="font-semibold text-gray-800 text-sm">{{ s.nama_lengkap }}</span>
                        <span :class="[
                            'px-2 py-1 text-xs font-bold rounded',
                            s.status === 'Hadir' ? 'bg-emerald-100 text-emerald-700' :
                            s.status === 'Sakit' ? 'bg-blue-100 text-blue-700' :
                            s.status === 'Izin'  ? 'bg-amber-100 text-amber-700' :
                            'bg-rose-100 text-rose-700'
                        ]">
                            {{ s.status }}
                        </span>
                    </div>
                    <div v-if="detailAbsen.length === 0" class="text-center py-6 text-sm text-gray-500 italic">
                        Absensi belum diisi.
                    </div>
                </div>
            </div>
        </Modal>

        <!-- Modal Form Jurnal -->
        <Modal :show="isFormModalOpen" @close="closeFormModal" maxWidth="md">
            <div class="p-6 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50 rounded-bl-full -z-10"></div>
                
                <h2 class="text-2xl font-bold text-gray-800 mb-1">Form Jurnal Mengajar</h2>
                <p class="text-sm text-gray-500 mb-6">{{ selectedJadwal?.nama_mapel }} - {{ selectedJadwal?.nama_kelas }}</p>

                <form @submit.prevent="submitJurnal" class="space-y-5">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5">Jam Ke <span class="text-rose-500">*</span></label>
                        <input type="text" v-model="form.jam_ke" readonly required class="w-full bg-gray-100 text-gray-600 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm cursor-not-allowed" placeholder="Otomatis">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5">Materi Pokok / Pembahasan <span class="text-rose-500">*</span></label>
                        <textarea v-model="form.materi" required rows="3" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm" placeholder="Materi yang diajarkan hari ini..."></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5">Keterangan / Catatan (Opsional)</label>
                        <textarea v-model="form.keterangan" rows="2" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm" placeholder="Catatan tambahan kejadian di kelas..."></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5">Foto Kegiatan (Opsional)</label>
                        <div class="w-full bg-gray-100 rounded-xl border-2 border-dashed border-gray-300 overflow-hidden relative">
                            <!-- Tampilan Video Kamera -->
                            <div v-show="!capturedImage && cameraStream" class="relative">
                                <video ref="videoElement" autoplay playsinline class="w-full h-48 object-cover"></video>
                                <button type="button" @click="capturePhoto" class="absolute bottom-3 left-1/2 -translate-x-1/2 w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-lg border-4 border-indigo-200 text-indigo-600 hover:scale-110 transition">
                                    <i class="fas fa-camera text-xl"></i>
                                </button>
                            </div>
                            
                            <!-- Tampilan Hasil Foto -->
                            <div v-show="capturedImage" class="relative">
                                <img :src="capturedImage" class="w-full h-48 object-cover">
                                <button type="button" @click="retakePhoto" class="absolute bottom-3 left-1/2 -translate-x-1/2 px-4 py-2 bg-rose-600 text-white rounded-lg shadow-lg font-bold text-sm hover:bg-rose-700 transition">
                                    <i class="fas fa-undo mr-1.5"></i> Ulangi
                                </button>
                            </div>
                            
                            <!-- Fallback Kamera Tidak Tersedia -->
                            <div v-if="!cameraStream && !capturedImage" class="flex flex-col items-center justify-center h-48 py-5">
                                <i class="fas fa-camera-slash text-3xl text-gray-400 mb-2"></i>
                                <p class="text-sm text-gray-500 font-semibold mb-2">Kamera tidak aktif</p>
                                <label class="px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm text-sm font-bold text-indigo-600 cursor-pointer hover:bg-gray-50 transition">
                                    Pilih File
                                    <input type="file" class="hidden" accept="image/*" @change="form.foto_kegiatan = $event.target.files[0]">
                                </label>
                            </div>

                            <!-- Canvas (Sembunyi) -->
                            <canvas ref="canvasElement" class="hidden"></canvas>
                        </div>
                        <p v-if="form.foto_kegiatan && !capturedImage" class="mt-2 text-sm text-emerald-600 font-semibold"><i class="fas fa-check mr-1"></i> Foto dipilih: {{ form.foto_kegiatan.name }}</p>
                    </div>

                    <div class="flex justify-end pt-4 gap-3">
                        <button type="button" @click="closeFormModal" class="px-5 py-2.5 bg-white border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 font-bold transition">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl shadow-md hover:bg-indigo-700 font-bold transition inline-flex items-center gap-2">
                            Lanjut ke Presensi <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </DashboardLayout>
</template>
