<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Html5Qrcode } from 'html5-qrcode';

const props = defineProps({
    lokasi: Object,
    sudah_absen: Object,
});

const form = useForm({
    latitude: '',
    longitude: '',
    qr_token: '',
});

// ─── State ───────────────────────────────────────────────
const locationStatus   = ref('loading');   // loading | ok | error | out_of_range
const locationMsg      = ref('Mengambil lokasi GPS...');
const userLat          = ref(null);
const userLng          = ref(null);
const jarak            = ref(null);
const qrDetected       = ref(false);
const scannerStarted   = ref(false);
let   html5Qrcode      = null;

// ─── Helpers ─────────────────────────────────────────────
const isInRadius = computed(() => locationStatus.value === 'ok');
const alreadyDone = computed(() =>
    props.sudah_absen && props.sudah_absen.jam_masuk && props.sudah_absen.jam_pulang
);
const isAbsenMasukOnly = computed(() =>
    props.sudah_absen && props.sudah_absen.jam_masuk && !props.sudah_absen.jam_pulang
);
const absenLabel = computed(() => isAbsenMasukOnly.value ? 'Absen Pulang' : 'Absen Masuk');

function hitungJarak(lat1, lon1, lat2, lon2) {
    const R = 6371000;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2)**2 +
              Math.cos(lat1 * Math.PI/180) * Math.cos(lat2 * Math.PI/180) * Math.sin(dLon/2)**2;
    return Math.round(R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)));
}

// ─── GPS ─────────────────────────────────────────────────
function getLocation() {
    locationStatus.value = 'loading';
    locationMsg.value = 'Mengambil lokasi GPS...';
    jarak.value = null;

    if (!navigator.geolocation) {
        locationStatus.value = 'error';
        locationMsg.value = 'Browser tidak mendukung Geolocation.';
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (pos) => {
            userLat.value = pos.coords.latitude;
            userLng.value = pos.coords.longitude;
            form.latitude  = userLat.value;
            form.longitude = userLng.value;

            const radius  = props.lokasi?.radius ?? 100;
            const sekolahLat = parseFloat(props.lokasi?.latitude ?? 0);
            const sekolahLng = parseFloat(props.lokasi?.longitude ?? 0);

            jarak.value = hitungJarak(sekolahLat, sekolahLng, userLat.value, userLng.value);

            if (jarak.value <= radius) {
                locationStatus.value = 'ok';
                locationMsg.value = `✅ Dalam radius! Jarak: ${jarak.value}m dari sekolah.`;
                // Auto start kamera jika belum
                if (!scannerStarted.value && !alreadyDone.value) {
                    startCamera();
                }
            } else {
                locationStatus.value = 'out_of_range';
                locationMsg.value = `🚫 Di luar radius! Jarak Anda: ${jarak.value}m (Batas: ${radius}m).`;
                stopCamera();
            }
        },
        (err) => {
            locationStatus.value = 'error';
            locationMsg.value = `GPS gagal: ${err.message}. Aktifkan GPS Anda.`;
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}

// ─── QR Camera ───────────────────────────────────────────
function startCamera() {
    if (scannerStarted.value) return;
    scannerStarted.value = true;

    html5Qrcode = new Html5Qrcode('qr-reader');
    html5Qrcode.start(
        { facingMode: 'environment' },
        { fps: 15, qrbox: { width: 260, height: 260 }, aspectRatio: 1.0 },
        (decodedText) => {
            if (!isInRadius.value) {
                alert('Anda tidak dalam radius sekolah!');
                return;
            }
            // QR detected
            qrDetected.value = true;
            form.qr_token = decodedText;
            stopCamera();
            // Auto submit
            form.post(route('guru.presensi.submit_absen'));
        },
        () => { /* scan fail, ignore */ }
    ).catch((err) => {
        scannerStarted.value = false;
        locationMsg.value = 'Kamera gagal dibuka: ' + err;
    });
}

function stopCamera() {
    if (html5Qrcode && scannerStarted.value) {
        html5Qrcode.stop().then(() => {
            html5Qrcode.clear();
            scannerStarted.value = false;
        }).catch(() => {});
    }
}

// ─── Lifecycle ───────────────────────────────────────────
onMounted(() => {
    if (!alreadyDone.value) {
        getLocation();
    }
});

onUnmounted(() => {
    stopCamera();
});
</script>

<template>
    <Head title="Absen Sekarang" />
    <DashboardLayout>
        <div class="max-w-lg mx-auto space-y-5 px-2">

            <!-- ── Header ── -->
            <div class="text-center pt-2">
                <h1 class="text-2xl font-black text-gray-800">📸 Absen Sekarang</h1>
                <p class="text-sm text-gray-500 mt-1">Scan QR Code sekolah untuk mencatat kehadiran.</p>
            </div>

            <!-- ── Sudah Selesai ── -->
            <div v-if="alreadyDone"
                class="bg-emerald-50 border border-emerald-200 rounded-2xl p-8 text-center shadow-sm">
                <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check-circle text-4xl text-emerald-500"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800">Absensi Hari Ini Selesai! 🎉</h3>
                <p class="text-gray-500 text-sm mt-1">Anda sudah melakukan absen masuk dan pulang.</p>
                <div class="mt-6 grid grid-cols-2 gap-3 text-sm">
                    <div class="bg-white rounded-xl p-4 border border-emerald-100">
                        <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">Masuk</p>
                        <p class="text-xl font-black text-emerald-600">{{ sudah_absen.jam_masuk }}</p>
                    </div>
                    <div class="bg-white rounded-xl p-4 border border-blue-100">
                        <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">Pulang</p>
                        <p class="text-xl font-black text-blue-600">{{ sudah_absen.jam_pulang }}</p>
                    </div>
                </div>
            </div>

            <template v-else>

                <!-- ── Sudah Masuk, Belum Pulang ── -->
                <div v-if="isAbsenMasukOnly"
                    class="bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4 flex items-center gap-3">
                    <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-clock text-amber-600"></i>
                    </div>
                    <div>
                        <p class="font-bold text-amber-800 text-sm">Sudah Absen Masuk pukul {{ sudah_absen.jam_masuk }}</p>
                        <p class="text-xs text-amber-600">Scan QR lagi untuk <strong>Absen Pulang</strong>.</p>
                    </div>
                </div>

                <!-- ── Status GPS Card ── -->
                <div class="rounded-2xl p-4 border shadow-sm transition-all"
                    :class="{
                        'bg-gray-50 border-gray-200': locationStatus === 'loading',
                        'bg-emerald-50 border-emerald-200': locationStatus === 'ok',
                        'bg-rose-50 border-rose-200':   locationStatus === 'out_of_range',
                        'bg-yellow-50 border-yellow-200': locationStatus === 'error',
                    }">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <!-- Icon status -->
                            <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0"
                                :class="{
                                    'bg-gray-200': locationStatus === 'loading',
                                    'bg-emerald-200': locationStatus === 'ok',
                                    'bg-rose-200':    locationStatus === 'out_of_range',
                                    'bg-yellow-200':  locationStatus === 'error',
                                }">
                                <i class="fas text-lg"
                                    :class="{
                                        'fa-spinner fa-spin text-gray-500': locationStatus === 'loading',
                                        'fa-map-marker-alt text-emerald-600': locationStatus === 'ok',
                                        'fa-map-marker-alt text-rose-600':   locationStatus === 'out_of_range',
                                        'fa-exclamation-triangle text-yellow-600': locationStatus === 'error',
                                    }">
                                </i>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Status GPS</p>
                                <p class="text-sm font-semibold text-gray-800">{{ locationMsg }}</p>
                            </div>
                        </div>
                        <button @click="getLocation"
                            class="flex-shrink-0 px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-semibold text-gray-600 hover:bg-gray-50 shadow-sm transition-all">
                            <i class="fas fa-sync-alt mr-1"></i> Refresh
                        </button>
                    </div>

                    <!-- Jarak Meter Bar -->
                    <div v-if="jarak !== null" class="mt-3 pt-3 border-t border-current border-opacity-10">
                        <div class="flex justify-between text-xs text-gray-500 mb-1.5">
                            <span>Jarak dari sekolah</span>
                            <span class="font-bold" :class="isInRadius ? 'text-emerald-600' : 'text-rose-600'">
                                {{ jarak }}m / {{ lokasi?.radius ?? 100 }}m radius
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                            <div class="h-2.5 rounded-full transition-all duration-500"
                                :class="isInRadius ? 'bg-emerald-500' : 'bg-rose-500'"
                                :style="`width: ${Math.min(100, (jarak / (lokasi?.radius ?? 100)) * 100)}%`">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── QR Scanner Area ── -->
                <div class="bg-white rounded-2xl border-2 shadow-sm overflow-hidden"
                    :class="isInRadius ? 'border-indigo-300' : 'border-gray-200'">

                    <!-- Header Scanner -->
                    <div class="px-5 py-4 border-b"
                        :class="isInRadius ? 'bg-indigo-50 border-indigo-100' : 'bg-gray-50 border-gray-100'">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                                :class="isInRadius ? 'bg-indigo-600' : 'bg-gray-300'">
                                <i class="fas fa-qrcode text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800">Scan QR Code Sekolah</h3>
                                <p class="text-xs text-gray-500">{{ absenLabel }} — {{ isInRadius ? 'Kamera aktif, arahkan ke QR Code!' : 'Masuk ke radius sekolah untuk mengaktifkan kamera.' }}</p>
                            </div>
                            <!-- Indicator -->
                            <div class="ml-auto flex-shrink-0">
                                <span class="flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-full"
                                    :class="scannerStarted ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500'">
                                    <span class="w-2 h-2 rounded-full"
                                        :class="scannerStarted ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400'">
                                    </span>
                                    {{ scannerStarted ? 'Live' : 'Mati' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Camera viewport -->
                    <div class="relative bg-black" style="min-height: 300px;">

                        <!-- Overlay saat di luar radius -->
                        <div v-if="!isInRadius && locationStatus !== 'loading'"
                            class="absolute inset-0 bg-gray-900/90 flex flex-col items-center justify-center z-10 text-white text-center p-6">
                            <div class="w-16 h-16 bg-white/10 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-map-marker-alt text-3xl text-rose-400"></i>
                            </div>
                            <h4 class="font-bold text-lg">Di Luar Radius</h4>
                            <p class="text-sm text-gray-300 mt-2">
                                Kamera dinonaktifkan. Dekatkan diri ke area sekolah agar bisa absen.
                            </p>
                            <p v-if="jarak !== null" class="text-2xl font-black text-rose-400 mt-4">{{ jarak }}m</p>
                            <p v-if="jarak !== null" class="text-xs text-gray-400">dari sekolah (batas {{ lokasi?.radius }}m)</p>
                        </div>

                        <!-- Loading GPS -->
                        <div v-if="locationStatus === 'loading'"
                            class="absolute inset-0 bg-gray-900 flex flex-col items-center justify-center z-10 text-white">
                            <i class="fas fa-spinner fa-spin text-4xl text-indigo-400 mb-4"></i>
                            <p class="text-sm text-gray-300">Mendeteksi lokasi GPS...</p>
                        </div>

                        <!-- QR Detected Success overlay -->
                        <div v-if="qrDetected"
                            class="absolute inset-0 bg-emerald-900/95 flex flex-col items-center justify-center z-10 text-white">
                            <div class="w-20 h-20 bg-emerald-400 rounded-full flex items-center justify-center mb-4 animate-bounce">
                                <i class="fas fa-check text-4xl text-white"></i>
                            </div>
                            <h4 class="font-bold text-xl">QR Terdeteksi!</h4>
                            <p class="text-sm text-emerald-200 mt-1">Sedang memproses absensi...</p>
                            <i class="fas fa-spinner fa-spin text-2xl text-emerald-300 mt-4"></i>
                        </div>

                        <!-- The actual camera element -->
                        <div id="qr-reader" class="w-full"></div>
                    </div>

                    <!-- Hint bottom -->
                    <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 text-center">
                        <p class="text-xs text-gray-400">
                            <i class="fas fa-info-circle mr-1 text-indigo-400"></i>
                            Arahkan kamera ke QR Code yang dipasang di sekolah. Absensi otomatis setelah QR terbaca.
                        </p>
                    </div>
                </div>

            </template>
        </div>
    </DashboardLayout>
</template>

<style scoped>
/* Force the html5-qrcode video to fill container */
#qr-reader {
    width: 100% !important;
    min-height: 300px;
}
#qr-reader video {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover;
}
/* Hide the ugly html5-qrcode default UI buttons */
#qr-reader__dashboard_section_csr button,
#qr-reader__header_message,
#qr-reader__status_span {
    display: none !important;
}
#qr-reader__scan_region {
    background: black !important;
}
</style>
