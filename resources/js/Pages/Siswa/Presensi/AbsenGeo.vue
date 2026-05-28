<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Html5QrcodeScanner } from 'html5-qrcode';

const props = defineProps({
    lokasi: Object,
    sudah_absen: Object
});

const form = useForm({
    latitude: '',
    longitude: '',
    qr_token: ''
});

const scannerRef = ref(null);
const locationStatus = ref('Mengambil lokasi Anda...');
const isLocationReady = ref(false);

const submitAbsen = () => {
    form.post(route('siswa.presensi.submit_absen'));
};

const getLocation = () => {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                form.latitude = position.coords.latitude;
                form.longitude = position.coords.longitude;
                locationStatus.value = `Lokasi didapatkan: ${form.latitude}, ${form.longitude}`;
                isLocationReady.value = true;
            },
            (error) => {
                locationStatus.value = `Gagal mendapatkan lokasi: ${error.message}. Pastikan GPS aktif.`;
                isLocationReady.value = false;
            },
            { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
        );
    } else {
        locationStatus.value = 'Browser Anda tidak mendukung Geolocation.';
    }
};

onMounted(() => {
    getLocation();

    if (!props.sudah_absen || !props.sudah_absen.jam_pulang) {
        const scanner = new Html5QrcodeScanner('reader', { 
            qrbox: { width: 250, height: 250 }, 
            fps: 10 
        });

        scanner.render((decodedText) => {
            if (!isLocationReady.value) {
                alert('Tunggu sampai lokasi berhasil didapatkan!');
                return;
            }
            form.qr_token = decodedText;
            scanner.clear();
            submitAbsen();
        }, (error) => {
            // Ignore scan errors
        });

        onUnmounted(() => {
            scanner.clear().catch(error => console.error("Failed to clear scanner", error));
        });
    }
});
</script>

<template>
    <DashboardLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Absensi Harian (Siswa)</h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        
                        <div v-if="sudah_absen && sudah_absen.jam_masuk && sudah_absen.jam_pulang" class="text-center py-12">
                            <div class="inline-flex items-center justify-center w-24 h-24 bg-green-100 rounded-full mb-4">
                                <i class="fas fa-check-circle text-5xl text-green-500"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-800">Selesai Absen Hari Ini</h3>
                            <p class="text-gray-600 mt-2">Terima kasih, Anda telah melakukan absen masuk dan pulang.</p>
                            <div class="mt-6 inline-block bg-gray-50 rounded-lg p-4 text-left border">
                                <p><strong>Waktu Masuk:</strong> <span class="text-green-600">{{ sudah_absen.jam_masuk }}</span></p>
                                <p><strong>Waktu Pulang:</strong> <span class="text-blue-600">{{ sudah_absen.jam_pulang }}</span></p>
                            </div>
                        </div>

                        <div v-else>
                            <div class="mb-6 p-4 rounded-lg flex items-center justify-between" :class="isLocationReady ? 'bg-green-50 border border-green-200' : 'bg-yellow-50 border border-yellow-200'">
                                <div>
                                    <h4 class="font-bold text-gray-800 flex items-center">
                                        <i class="fas fa-map-marker-alt mr-2" :class="isLocationReady ? 'text-green-500' : 'text-yellow-500'"></i>
                                        Status GPS
                                    </h4>
                                    <p class="text-sm mt-1" :class="isLocationReady ? 'text-green-700' : 'text-yellow-700'">{{ locationStatus }}</p>
                                </div>
                                <button @click="getLocation" class="px-3 py-1 bg-white border shadow-sm rounded text-sm hover:bg-gray-50 transition">
                                    <i class="fas fa-sync-alt mr-1"></i> Refresh
                                </button>
                            </div>

                            <div v-if="!isLocationReady" class="text-center py-8">
                                <i class="fas fa-spinner fa-spin text-4xl text-indigo-500 mb-4"></i>
                                <p class="text-gray-600">Sedang mencari lokasi...</p>
                            </div>

                            <div v-show="isLocationReady" class="text-center">
                                <h3 class="text-xl font-bold text-gray-800 mb-4">Scan QR Sekolah</h3>
                                <p class="text-gray-600 mb-6 text-sm">Arahkan kamera ke QR Code yang disediakan oleh pihak sekolah untuk merekam kehadiran.</p>
                                
                                <div id="reader" class="mx-auto overflow-hidden rounded-lg border-2 border-indigo-200" style="max-width: 400px;"></div>
                                
                                <form @submit.prevent="submitAbsen" class="mt-6" v-if="form.qr_token">
                                    <div class="bg-indigo-50 p-4 rounded-lg mb-4">
                                        <p class="text-sm text-indigo-800 font-medium mb-1">QR Code Terdeteksi!</p>
                                        <p class="text-xs font-mono text-indigo-600 truncate">{{ form.qr_token }}</p>
                                    </div>
                                    <button type="submit" :disabled="form.processing" class="w-full bg-indigo-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-indigo-700 transition duration-150 ease-in-out shadow-md">
                                        <i class="fas fa-paper-plane mr-2"></i> Kirim Presensi
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
