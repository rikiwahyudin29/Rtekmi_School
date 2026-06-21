<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const props = defineProps({
    riwayat: Array,
    list_dudi: Array
});

const isModalOpen = ref(false);
const videoRef = ref(null);
const canvasRef = ref(null);
const stream = ref(null);
const hasCamera = ref(false);
const showCamera = ref(false);
const locationStatus = ref('');

const form = useForm({
    dudi_id: '',
    tanggal: new Date().toISOString().slice(0, 10),
    catatan: '',
    lat: '',
    long: '',
    foto_kunjungan_base64: ''
});

let map = null;
let marker = null;

const initMap = (lat, lng) => {
    if (!map) {
        map = L.map('map').setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);
        
        // Fix Leaflet marker icons not showing in Vue/Vite
        delete L.Icon.Default.prototype._getIconUrl;
        L.Icon.Default.mergeOptions({
            iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
            iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
        });
        
        marker = L.marker([lat, lng]).addTo(map);
    } else {
        map.setView([lat, lng], 15);
        marker.setLatLng([lat, lng]);
    }
};

// Camera logic sama dengan Ekskul absen scan
const initCamera = async () => {
    try {
        stream.value = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
        if (videoRef.value) {
            videoRef.value.srcObject = stream.value;
            hasCamera.value = true;
            showCamera.value = true;
        }
    } catch (err) {
        console.error("Camera error:", err);
        hasCamera.value = false;
        // Don't alert immediately to be less annoying, just set status
    }
};

const stopCamera = () => {
    if (stream.value) {
        stream.value.getTracks().forEach(track => track.stop());
    }
    showCamera.value = false;
};

const capturePhoto = () => {
    if (videoRef.value && canvasRef.value) {
        const video = videoRef.value;
        const canvas = canvasRef.value;
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        // Add watermark
        ctx.fillStyle = 'rgba(0, 0, 0, 0.5)';
        ctx.fillRect(0, canvas.height - 40, canvas.width, 40);
        ctx.fillStyle = 'white';
        ctx.font = '14px Arial';
        ctx.fillText(`Lokasi: ${form.lat}, ${form.long} | Tgl: ${new Date().toLocaleString()}`, 10, canvas.height - 15);

        form.foto_kunjungan_base64 = canvas.toDataURL('image/jpeg');
        stopCamera();
    }
};

const getLocation = () => {
    locationStatus.value = 'Mendapatkan lokasi...';
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            async (position) => {
                form.lat = position.coords.latitude;
                form.long = position.coords.longitude;
                locationStatus.value = 'Lokasi berhasil didapatkan!';
                await nextTick();
                if (document.getElementById('map')) {
                    initMap(form.lat, form.long);
                }
            },
            (error) => {
                locationStatus.value = 'Gagal mendapatkan lokasi. Pastikan GPS aktif.';
                console.error(error);
            }
        );
    } else {
        locationStatus.value = "Geolocation tidak didukung oleh browser ini.";
    }
};

const openModal = () => {
    form.reset();
    form.tanggal = new Date().toISOString().slice(0, 10);
    form.foto_kunjungan_base64 = ''; // Reset photo
    isModalOpen.value = true;
    getLocation();
    nextTick(() => {
        initCamera(); // Auto-start camera
    });
};

const closeModal = () => {
    isModalOpen.value = false;
    stopCamera();
    if (map) {
        map.remove();
        map = null;
    }
};

const submitKunjungan = () => {
    if (!form.foto_kunjungan_base64) {
        alert("Wajib mengambil foto kunjungan di lokasi!");
        return;
    }
    form.post(route('guru.pkl.kunjungan.simpan'), {
        preserveScroll: true,
        onSuccess: () => closeModal()
    });
};

onUnmounted(() => {
    stopCamera();
});
</script>

<template>
    <Head title="Kunjungan Monitoring PKL" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-camera-retro text-blue-500"></i>
                        Kunjungan Monitoring
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Catat aktivitas kunjungan guru pembimbing ke lokasi DU/DI.
                    </p>
                </div>
                <button @click="openModal" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-bold shadow-sm flex items-center gap-2 transition-all">
                    <i class="fas fa-plus"></i> Input Kunjungan
                </button>
            </div>

            <div v-if="$page.props.flash?.message" class="bg-green-50 text-green-800 p-4 rounded-2xl shadow-sm flex items-center gap-3">
                <i class="fas fa-check-circle"></i> {{ $page.props.flash.message }}
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-4 font-bold">Tanggal</th>
                                <th class="px-6 py-4 font-bold">Lokasi DU/DI</th>
                                <th class="px-6 py-4 font-bold">Catatan Kunjungan</th>
                                <th class="px-6 py-4 font-bold">Foto Bukti</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="r in riwayat" :key="r.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ r.tanggal }}
                                </td>
                                <td class="px-6 py-4 font-bold text-blue-600">
                                    {{ r.nama_dudi }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    {{ r.catatan }}
                                </td>
                                <td class="px-6 py-4">
                                    <img v-if="r.foto_kunjungan" :src="'/uploads/kunjungan_pkl/' + r.foto_kunjungan" class="w-24 h-16 object-cover rounded-lg border border-gray-200 cursor-pointer" onclick="window.open(this.src, '_blank')">
                                    <span v-else class="text-xs text-gray-500">Tidak ada foto</span>
                                </td>
                            </tr>
                            <tr v-if="riwayat.length === 0">
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                    Belum ada riwayat kunjungan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Form Input Kunjungan -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-700/50">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white">Form Kunjungan Monitoring</h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                </div>
                <form @submit.prevent="submitKunjungan" class="p-6 overflow-y-auto flex-1">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Pilih DU/DI</label>
                                <select v-model="form.dudi_id" class="w-full rounded-xl border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500" required>
                                    <option value="">-- Pilih Lokasi --</option>
                                    <option v-for="d in list_dudi" :key="d.id" :value="d.id">{{ d.nama_dudi }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Tanggal Kunjungan</label>
                                <input type="date" v-model="form.tanggal" class="w-full rounded-xl border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500" required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Catatan Kunjungan</label>
                            <textarea v-model="form.catatan" rows="3" class="w-full rounded-xl border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500" placeholder="Jelaskan kondisi siswa, masukan dari industri, dll." required></textarea>
                        </div>

                        <!-- Lokasi GPS & Map -->
                        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl border border-blue-100 dark:border-blue-800">
                            <div class="flex justify-between items-center mb-2">
                                <h4 class="font-bold text-blue-800 dark:text-blue-400 text-sm"><i class="fas fa-map-marker-alt"></i> Data Geolocation</h4>
                                <button type="button" @click="getLocation" class="text-xs text-blue-600 hover:text-blue-800 font-bold bg-blue-100 px-2 py-1 rounded">Refresh Lokasi</button>
                            </div>
                            <div class="text-xs text-blue-700 mb-2">{{ locationStatus }}</div>
                            <div class="grid grid-cols-2 gap-4 mb-3 hidden">
                                <div>
                                    <label class="block text-xs font-bold text-blue-700 mb-1">Latitude</label>
                                    <input type="text" v-model="form.lat" class="w-full rounded-lg border-blue-200 bg-white/50 text-sm" readonly required>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-blue-700 mb-1">Longitude</label>
                                    <input type="text" v-model="form.long" class="w-full rounded-lg border-blue-200 bg-white/50 text-sm" readonly required>
                                </div>
                            </div>
                            <!-- Peta -->
                            <div id="map" class="w-full h-40 rounded-xl border border-blue-200 shadow-inner z-10"></div>
                        </div>

                        <!-- Kamera Embedded -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Foto Bukti Kunjungan (Kamera Tertanam)</label>
                            
                            <div v-if="!form.foto_kunjungan_base64" class="relative rounded-xl overflow-hidden bg-black aspect-video w-full border border-gray-200 shadow-sm">
                                <video ref="videoRef" autoplay playsinline class="w-full h-full object-cover"></video>
                                <button type="button" @click="capturePhoto" class="absolute bottom-4 left-1/2 -translate-x-1/2 w-14 h-14 bg-white rounded-full border-4 border-gray-300 flex items-center justify-center shadow-lg active:scale-95 transition-transform" title="Ambil Foto">
                                    <div class="w-10 h-10 bg-red-500 rounded-full border-2 border-white"></div>
                                </button>
                                <div v-if="!hasCamera && !stream" class="absolute inset-0 flex items-center justify-center text-white text-sm bg-gray-800">
                                    Menghidupkan kamera... (Izinkan akses kamera jika diminta)
                                </div>
                            </div>
                            
                            <canvas ref="canvasRef" class="hidden"></canvas>

                            <div v-if="form.foto_kunjungan_base64" class="relative group rounded-xl overflow-hidden">
                                <img :src="form.foto_kunjungan_base64" class="w-full h-auto rounded-xl border border-gray-200">
                                <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button type="button" @click="form.foto_kunjungan_base64 = ''; initCamera()" class="px-4 py-2 bg-white text-gray-900 rounded-lg font-bold text-sm">
                                        <i class="fas fa-redo"></i> Foto Ulang
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="closeModal" class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold">Batal</button>
                        <button type="submit" :disabled="form.processing || !form.lat || !form.long" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold disabled:opacity-50">
                            Simpan Kunjungan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </DashboardLayout>
</template>
