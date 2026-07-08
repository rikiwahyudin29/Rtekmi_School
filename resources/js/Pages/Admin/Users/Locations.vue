<template>
    <DashboardLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
                Peta Sebaran Lokasi Pengguna
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 text-slate-900 dark:text-slate-100">
                        
                        <div class="mb-4">
                            <p class="text-slate-600 dark:text-slate-400">
                                Peta ini menampilkan titik koordinat terakhir seluruh pengguna (Siswa, Guru, Admin) yang login melalui aplikasi perangkat bergerak.
                            </p>
                        </div>

                        <!-- Map View -->
                        <div class="w-full">
                            <div id="map" style="height: 500px;" class="w-full rounded-lg border border-slate-200 dark:border-slate-700 relative z-0"></div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';

// Fix Leaflet's default icon missing issue in Vite
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
  iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
  shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
});

const props = defineProps({
    locations: Array,
});

let map = null;
let markersLayer = null;

const initMap = () => {
    if (!map) {
        map = L.map('map').setView([-6.200000, 106.816666], 5);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        markersLayer = L.layerGroup().addTo(map);
    }
    updateMapMarkers();
};

const updateMapMarkers = () => {
    if (!map || !markersLayer) return;
    
    markersLayer.clearLayers();
    const bounds = [];
    
    if (props.locations && props.locations.length > 0) {
        props.locations.forEach(device => {
            if (device.latitude && device.longitude) {
                const lat = parseFloat(device.latitude);
                const lng = parseFloat(device.longitude);
                if (!isNaN(lat) && !isNaN(lng)) {
                    let lastLogin = device.last_login_at ? new Date(device.last_login_at).toLocaleString('id-ID') : '-';
                    const marker = L.marker([lat, lng]).bindPopup(`
                        <div class="text-sm">
                            <b>${device.user?.nama_lengkap || 'Unknown'}</b><br>
                            <span class="text-xs text-gray-500">Role: ${device.user?.role || '-'}</span><br>
                            <span class="text-xs text-gray-500">Device: ${device.device_name || 'Mobile'}</span><br>
                            <span class="text-xs text-gray-500">Login: ${lastLogin}</span>
                        </div>
                    `);
                    markersLayer.addLayer(marker);
                    bounds.push([lat, lng]);
                }
            }
        });
    }

    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [50, 50], maxZoom: 16 });
    } else {
        map.setView([-6.200000, 106.816666], 5);
    }
};

onMounted(() => {
    initMap();
});

watch(() => props.locations, () => {
    nextTick(() => {
        updateMapMarkers();
    });
}, { deep: true });

</script>
