<script setup>
import { ref, onMounted, watch } from 'vue';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';
import { Head, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    jamSekolah: Object,
    jamMaster: Array,
});

// Form for Jam Sekolah (Settings)
const sekolahForm = useForm({
    jam_masuk_mulai: props.jamSekolah.jam_masuk_mulai || '06:00:00',
    jam_masuk_akhir: props.jamSekolah.jam_masuk_akhir || '07:15:00',
    jam_pulang_mulai: props.jamSekolah.jam_pulang_mulai || '14:00:00',
    latitude: props.jamSekolah.latitude || '-6.200000',
    longitude: props.jamSekolah.longitude || '106.816666',
    radius: props.jamSekolah.radius || 100,
    qr_token: props.jamSekolah.qr_token || generateRandomToken(),
});

const submitSekolah = () => {
    sekolahForm.post(route('admin.master.jam-belajar.sekolah.update'), {
        preserveScroll: true,
    });
};

function generateRandomToken() {
    return Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15).toUpperCase();
}

const generateNewQR = () => {
    if (confirm('Yakin ingin generate QR baru? QR lama tidak akan bisa digunakan absen lagi.')) {
        sekolahForm.qr_token = generateRandomToken();
    }
};

// Map logic
const mapContainer = ref(null);
let map = null;
let marker = null;
let circle = null;

watch(() => sekolahForm.radius, (newVal) => {
    if (circle) {
        circle.setRadius(newVal || 100);
    }
});

const initMap = () => {
    const lat = parseFloat(sekolahForm.latitude) || -6.200000;
    const lng = parseFloat(sekolahForm.longitude) || 106.816666;

    map = L.map(mapContainer.value).setView([lat, lng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    marker = L.marker([lat, lng], { draggable: true }).addTo(map);

    circle = L.circle([lat, lng], {
        color: '#ef4444',
        fillColor: '#ef4444',
        fillOpacity: 0.15,
        radius: sekolahForm.radius || 100
    }).addTo(map);

    // Update form when marker is dragged
    marker.on('dragend', function (e) {
        const position = marker.getLatLng();
        sekolahForm.latitude = position.lat.toString();
        sekolahForm.longitude = position.lng.toString();
        if (circle) circle.setLatLng(position);
    });

    // Move marker when clicking on map
    map.on('click', function (e) {
        marker.setLatLng(e.latlng);
        sekolahForm.latitude = e.latlng.lat.toString();
        sekolahForm.longitude = e.latlng.lng.toString();
        if (circle) circle.setLatLng(e.latlng);
    });
};

const detectLocation = () => {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                sekolahForm.latitude = position.coords.latitude.toString();
                sekolahForm.longitude = position.coords.longitude.toString();
                if (map && marker) {
                    const newLatLng = new L.LatLng(position.coords.latitude, position.coords.longitude);
                    marker.setLatLng(newLatLng);
                    map.setView(newLatLng, 16);
                }
            },
            (error) => {
                alert('Gagal mendeteksi lokasi. Pastikan izin lokasi diberikan di browser.');
            }
        );
    } else {
        alert('Browser Anda tidak mendukung deteksi lokasi.');
    }
};

// State and Form for Jam Master (Table)
const showModal = ref(false);
const isEditing = ref(false);

const masterForm = useForm({
    id: null,
    urutan: '',
    nama_jam: '',
    jam_mulai: '',
    jam_selesai: '',
    is_istirahat: false,
});

const openModal = (jam = null) => {
    isEditing.value = !!jam;
    if (jam) {
        masterForm.id = jam.id;
        masterForm.urutan = jam.urutan;
        masterForm.nama_jam = jam.nama_jam;
        masterForm.jam_mulai = jam.jam_mulai;
        masterForm.jam_selesai = jam.jam_selesai;
        masterForm.is_istirahat = !!jam.is_istirahat;
    } else {
        masterForm.reset();
        masterForm.id = null;
        masterForm.is_istirahat = false;
        const lastUrutan = props.jamMaster.length > 0 ? Math.max(...props.jamMaster.map(j => j.urutan)) : 0;
        masterForm.urutan = lastUrutan + 1;
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    masterForm.reset();
    masterForm.clearErrors();
};

const submitMaster = () => {
    if (isEditing.value) {
        masterForm.put(route('admin.master.jam-belajar.master.update', masterForm.id), {
            onSuccess: () => closeModal(),
            preserveScroll: true,
        });
    } else {
        masterForm.post(route('admin.master.jam-belajar.master.store'), {
            onSuccess: () => closeModal(),
            preserveScroll: true,
        });
    }
};

const deleteMaster = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus Jam Belajar ini?')) {
        router.delete(route('admin.master.jam-belajar.master.destroy', id), {
            preserveScroll: true,
        });
    }
};

onMounted(() => {
    initMap();
});

const activeTab = ref('presensi');
</script>

<template>
    <Head title="Pengaturan Jam Belajar" />

    <DashboardLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Header -->
                <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 class="font-bold text-2xl text-gray-900 dark:text-white tracking-tight">Setting Presensi & Jadwal</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Atur jadwal jam kerja, lokasi validasi GPS, dan QR Code Absen.</p>
                    </div>
                </div>

                <div v-if="$page.props.flash?.message" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-400 p-4 mb-6 rounded-2xl shadow-sm flex items-center gap-3">
                    <i class="fas fa-check-circle"></i> <span v-html="$page.props.flash.message"></span>
                </div>
                
                <div v-if="$page.props.flash?.error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-400 p-4 mb-6 rounded-2xl shadow-sm flex items-center gap-3">
                    <i class="fas fa-exclamation-circle"></i> <span v-html="$page.props.flash.error"></span>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden mb-8">
                    <!-- Tabs Navigation -->
                    <div class="flex overflow-x-auto border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 px-2">
                        <button @click="activeTab = 'presensi'" :class="activeTab === 'presensi' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors flex items-center gap-2">
                            <i class="fas fa-fingerprint"></i> Pengaturan Presensi & Lokasi
                        </button>
                        <button @click="activeTab = 'kbm'" :class="activeTab === 'kbm' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors flex items-center gap-2">
                            <i class="fas fa-list-ol"></i> Jadwal Mengajar (KBM)
                        </button>
                    </div>

                    <!-- Tab: Presensi & Lokasi -->
                    <div v-show="activeTab === 'presensi'" class="p-6 bg-gray-50/30 dark:bg-gray-900/30">
                        <form @submit.prevent="submitSekolah">
                            
                            <!-- QR Code Section -->
                            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
                                <h3 class="font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                                    <i class="fas fa-qrcode text-orange-500"></i> QR Code Absensi (Scan Dinding)
                                </h3>
                                <div class="flex flex-col md:flex-row gap-6 items-start">
                                    <div class="w-48 h-48 bg-white border-4 border-gray-100 rounded-xl overflow-hidden shrink-0 shadow-sm flex items-center justify-center">
                                        <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + sekolahForm.qr_token" alt="QR Code" class="w-full h-full object-contain p-2">
                                    </div>
                                    <div class="flex-1 space-y-4">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Token Hash Saat Ini</label>
                                            <input v-model="sekolahForm.qr_token" type="text" class="block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 font-mono text-sm" readonly>
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 italic">
                                            *QR Code di samping ini yang <strong>dicetak dan ditempel</strong> di dinding sekolah untuk discan oleh Guru/Siswa.
                                        </p>
                                        <button type="button" @click="generateNewQR" class="bg-orange-500 hover:bg-orange-600 text-white font-medium py-2 px-4 rounded-xl shadow-sm transition-all flex items-center gap-2 text-sm">
                                            <i class="fas fa-sync-alt"></i> Generate QR Baru (Hanguskan yang Lama)
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <!-- Aturan Jam -->
                                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
                                    <h3 class="font-bold text-gray-800 dark:text-gray-200 mb-6 flex items-center gap-2">
                                        <i class="fas fa-clock text-blue-500"></i> Aturan Jam
                                    </h3>
                                    <div class="space-y-5">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Jam Buka Scan Masuk</label>
                                            <input v-model="sekolahForm.jam_masuk_mulai" type="time" step="1" class="block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-red-500 dark:text-red-400 uppercase tracking-wider mb-2">Batas Terlambat</label>
                                            <input v-model="sekolahForm.jam_masuk_akhir" type="time" step="1" class="block w-full rounded-xl border-red-300 dark:border-red-700 dark:bg-red-900/20 text-red-700 dark:text-red-300 shadow-sm focus:border-red-500 focus:ring-red-500 bg-red-50">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider mb-2">Jam Buka Scan Pulang</label>
                                            <input v-model="sekolahForm.jam_pulang_mulai" type="time" step="1" class="block w-full rounded-xl border-blue-300 dark:border-blue-700 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-blue-50">
                                        </div>
                                    </div>
                                </div>

                                <!-- Lokasi Sekolah -->
                                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
                                    <h3 class="font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                                        <i class="fas fa-map-marker-alt text-red-500"></i> Lokasi Sekolah (Radius)
                                    </h3>
                                    
                                    <!-- Map Container -->
                                    <div ref="mapContainer" class="w-full h-64 rounded-xl border border-gray-300 dark:border-gray-600 mb-4 z-0 relative"></div>
                                    
                                    <p class="text-xs text-center text-gray-500 dark:text-gray-400 italic mb-4">
                                        *Geser pin atau klik pada peta untuk menentukan titik pusat sekolah.
                                    </p>

                                    <div class="grid grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Latitude</label>
                                            <input v-model="sekolahForm.latitude" type="text" class="block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Longitude</label>
                                            <input v-model="sekolahForm.longitude" type="text" class="block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="block text-xs font-bold text-green-600 dark:text-green-400 uppercase tracking-wider mb-1">Jarak Toleransi (Meter)</label>
                                        <div class="relative">
                                            <input v-model="sekolahForm.radius" type="number" class="block w-full rounded-xl border-green-300 dark:border-green-700 dark:bg-green-900/20 text-green-700 dark:text-green-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm pr-16 font-bold">
                                            <span class="absolute right-4 top-2 text-sm font-bold text-green-600 dark:text-green-400">Meter</span>
                                        </div>
                                    </div>

                                    <button type="button" @click="detectLocation" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 font-medium py-2 px-4 rounded-xl shadow-sm transition-all flex justify-center items-center gap-2 text-sm">
                                        <i class="fas fa-location-arrow text-red-500"></i> Deteksi Lokasi Saya Saat Ini
                                    </button>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <button type="submit" :disabled="sekolahForm.processing" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-xl shadow-sm transition-all flex items-center gap-2">
                                    <i class="fas fa-check"></i> Simpan Semua Pengaturan
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Tab: Jadwal KBM -->
                    <div v-show="activeTab === 'kbm'">
                        <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-white dark:bg-gray-800">
                            <h3 class="font-bold text-lg text-gray-900 dark:text-white flex items-center gap-2">
                                <i class="fas fa-list-ol text-primary-500"></i> Detail Jam Belajar (KBM)
                            </h3>
                            <button @click="openModal()" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-xl shadow-sm transition-all flex items-center gap-2 text-sm">
                                <i class="fas fa-plus"></i> Tambah Jam
                            </button>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm whitespace-nowrap">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr class="text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">
                                        <th class="py-4 px-5 font-bold w-16 text-center">Urutan</th>
                                        <th class="py-4 px-5 font-bold">Nama Jam</th>
                                        <th class="py-4 px-5 font-bold text-center">Waktu</th>
                                        <th class="py-4 px-5 font-bold text-center">Status</th>
                                        <th class="py-4 px-5 font-bold text-center w-32">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                    <tr v-for="jam in jamMaster" :key="jam.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors" :class="{'bg-orange-50/20 dark:bg-orange-900/10': jam.is_istirahat}">
                                        <td class="py-4 px-5 text-gray-900 dark:text-gray-100 font-bold text-center">
                                            {{ jam.urutan }}
                                        </td>
                                        <td class="py-4 px-5 text-gray-900 dark:text-gray-100 font-medium">
                                            {{ jam.nama_jam }}
                                        </td>
                                        <td class="py-4 px-5 text-gray-500 dark:text-gray-400 text-center font-mono text-sm font-medium">
                                            {{ jam.jam_mulai.substring(0,5) }} - {{ jam.jam_selesai.substring(0,5) }}
                                        </td>
                                        <td class="py-4 px-5 text-center">
                                            <span v-if="jam.is_istirahat" class="bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">
                                                Istirahat
                                            </span>
                                            <span v-else class="bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">
                                                Belajar
                                            </span>
                                        </td>
                                        <td class="py-4 px-5 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <button @click="openModal(jam)" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition-colors dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50">
                                                    <i class="fas fa-edit text-xs"></i>
                                                </button>
                                                <button @click="deleteMaster(jam.id)" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition-colors dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50">
                                                    <i class="fas fa-trash text-xs"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="jamMaster.length === 0">
                                        <td colspan="5" class="py-16 text-center text-gray-400 dark:text-gray-500">
                                            <div class="flex flex-col items-center justify-center">
                                                <i class="fas fa-clock text-5xl mb-4 text-gray-300 dark:text-gray-600"></i>
                                                <p class="text-lg">Belum ada jam belajar yang ditambahkan.</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal CRUD Jam Master -->
        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-900/75 backdrop-blur-sm" @click="closeModal"></div>

                <div class="relative inline-block w-full max-w-md p-6 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-2xl border border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-bold leading-6 text-gray-900 dark:text-white mb-4">
                        {{ isEditing ? 'Edit' : 'Tambah' }} Jam Belajar
                    </h3>
                    
                    <form @submit.prevent="submitMaster" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Urutan (Ke-)</label>
                                <input v-model="masterForm.urutan" type="number" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                            </div>
                            <div class="flex items-end pb-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" v-model="masterForm.is_istirahat" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 bg-white dark:bg-gray-800 dark:border-gray-600">
                                    <span class="text-sm font-medium text-orange-600 dark:text-orange-400">Jam Istirahat</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Jam (Label)</label>
                            <input v-model="masterForm.nama_jam" type="text" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="Contoh: Jam Ke-1 / Istirahat Pertama" required>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jam Mulai</label>
                                <input v-model="masterForm.jam_mulai" type="time" step="1" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jam Selesai</label>
                                <input v-model="masterForm.jam_selesai" type="time" step="1" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <button type="button" @click="closeModal" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 transition-colors">
                                Batal
                            </button>
                            <button type="submit" :disabled="masterForm.processing" class="px-5 py-2.5 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-xl hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors disabled:opacity-50 flex items-center gap-2">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<style>
/* Add missing leafet z-index fix */
.leaflet-container {
    z-index: 1;
}
</style>
