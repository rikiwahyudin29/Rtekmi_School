<script setup>
import { ref, onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    jam: Object,
});

const form = useForm({
    jam_masuk_mulai: props.jam.jam_masuk_mulai || '06:00:00',
    jam_masuk_akhir: props.jam.jam_masuk_akhir || '07:15:00',
    jam_pulang_mulai: props.jam.jam_pulang_mulai || '14:00:00',
    latitude: props.jam.latitude || '-6.200000',
    longitude: props.jam.longitude || '106.816666',
    radius: props.jam.radius || 100,
    qr_token: props.jam.qr_token || generateRandomToken(),
});

const submit = () => {
    form.post(route('admin.presensi.setting_jam.update'), {
        preserveScroll: true,
    });
};

function generateRandomToken() {
    return Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15).toUpperCase();
}

const generateNewQR = () => {
    if (confirm('Yakin ingin generate QR baru? QR lama tidak akan bisa digunakan absen lagi.')) {
        form.qr_token = generateRandomToken();
    }
};

// Map logic
const mapContainer = ref(null);
let map = null;
let marker = null;

const initMap = () => {
    if (typeof L === 'undefined') return;

    const lat = parseFloat(form.latitude) || -6.200000;
    const lng = parseFloat(form.longitude) || 106.816666;

    map = L.map(mapContainer.value).setView([lat, lng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    marker = L.marker([lat, lng], { draggable: true }).addTo(map);

    // Update form when marker is dragged
    marker.on('dragend', function (e) {
        const position = marker.getLatLng();
        form.latitude = position.lat.toString();
        form.longitude = position.lng.toString();
    });

    // Move marker when clicking on map
    map.on('click', function (e) {
        marker.setLatLng(e.latlng);
        form.latitude = e.latlng.lat.toString();
        form.longitude = e.latlng.lng.toString();
    });
};

const detectLocation = () => {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                form.latitude = position.coords.latitude.toString();
                form.longitude = position.coords.longitude.toString();
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

onMounted(() => {
    // Load Leaflet dynamically
    if (!document.getElementById('leaflet-css')) {
        const link = document.createElement('link');
        link.id = 'leaflet-css';
        link.rel = 'stylesheet';
        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
        document.head.appendChild(link);
    }
    
    if (!document.getElementById('leaflet-js')) {
        const script = document.createElement('script');
        script.id = 'leaflet-js';
        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        script.onload = () => {
            initMap();
        };
        document.head.appendChild(script);
    } else {
        setTimeout(initMap, 500); // Wait for L to be available if already loaded
    }
});
</script>

<template>
    <Head title="Pengaturan Presensi & QR" />

    <DashboardLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Header -->
                <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 class="font-bold text-2xl text-gray-900 dark:text-white tracking-tight">Setting Jam & QR Presensi</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Atur jadwal jam kerja, lokasi validasi GPS, dan QR Code Absen.</p>
                    </div>
                </div>

                <div v-if="$page.props.flash?.message || $page.props.flash?.success" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-400 p-4 mb-6 rounded-2xl shadow-sm flex items-center gap-3">
                    <i class="fas fa-check-circle"></i> <span v-html="$page.props.flash.message || $page.props.flash.success"></span>
                </div>
                
                <div v-if="$page.props.flash?.error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-400 p-4 mb-6 rounded-2xl shadow-sm flex items-center gap-3">
                    <i class="fas fa-exclamation-circle"></i> <span v-html="$page.props.flash.error"></span>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden mb-8 p-6">
                    <form @submit.prevent="submit">
                        
                        <!-- QR Code Section -->
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
                            <h3 class="font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                                <i class="fas fa-qrcode text-orange-500"></i> QR Code Absensi (Scan Dinding)
                            </h3>
                            <div class="flex flex-col md:flex-row gap-6 items-start">
                                <div class="w-48 h-48 bg-white border-4 border-gray-100 rounded-xl overflow-hidden shrink-0 shadow-sm flex items-center justify-center">
                                    <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + form.qr_token" alt="QR Code" class="w-full h-full object-contain p-2">
                                </div>
                                <div class="flex-1 space-y-4 w-full">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Token Hash Saat Ini</label>
                                        <input v-model="form.qr_token" type="text" class="block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm" readonly>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 italic">
                                        *QR Code di samping ini yang <strong>dicetak dan ditempel</strong> di dinding sekolah untuk discan oleh Guru/Siswa yang menggunakan HP.
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
                                        <input v-model="form.jam_masuk_mulai" type="time" step="1" class="block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-red-500 dark:text-red-400 uppercase tracking-wider mb-2">Batas Terlambat</label>
                                        <input v-model="form.jam_masuk_akhir" type="time" step="1" class="block w-full rounded-xl border-red-300 dark:border-red-700 dark:bg-red-900/20 text-red-700 dark:text-red-300 shadow-sm focus:border-red-500 focus:ring-red-500 bg-red-50">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-blue-500 dark:text-blue-400 uppercase tracking-wider mb-2">Jam Buka Scan Pulang</label>
                                        <input v-model="form.jam_pulang_mulai" type="time" step="1" class="block w-full rounded-xl border-blue-300 dark:border-blue-700 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-blue-50">
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
                                        <input v-model="form.latitude" type="text" class="block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Longitude</label>
                                        <input v-model="form.longitude" type="text" class="block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-xs font-bold text-green-600 dark:text-green-400 uppercase tracking-wider mb-1">Jarak Toleransi (Meter)</label>
                                    <div class="relative">
                                        <input v-model="form.radius" type="number" class="block w-full rounded-xl border-green-300 dark:border-green-700 dark:bg-green-900/20 text-green-700 dark:text-green-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm pr-16 font-bold">
                                        <span class="absolute right-4 top-2 text-sm font-bold text-green-600 dark:text-green-400">Meter</span>
                                    </div>
                                </div>

                                <button type="button" @click="detectLocation" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 font-medium py-2 px-4 rounded-xl shadow-sm transition-all flex justify-center items-center gap-2 text-sm">
                                    <i class="fas fa-location-arrow text-red-500"></i> Deteksi Lokasi Saya Saat Ini
                                </button>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" :disabled="form.processing" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-xl shadow-sm transition-all flex items-center gap-2">
                                <i class="fas fa-check"></i> Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </DashboardLayout>
</template>

<style>
/* Leaflet z-index fix */
.leaflet-container {
    z-index: 1;
}
</style>
