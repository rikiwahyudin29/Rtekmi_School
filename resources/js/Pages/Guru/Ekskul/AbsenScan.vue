<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    jurnal: Object,
    absen: Array
});

const scanInput = ref('');
const scanInputRef = ref(null);
const scanMessage = ref('');
const scanStatus = ref(''); // success, warning, error

onMounted(() => {
    // Auto focus the input field for barcode scanners
    if (scanInputRef.value) {
        scanInputRef.value.focus();
    }
});

// For external physical barcode scanner
const handleScan = () => {
    if (!scanInput.value) return;

    axios.post(route('guru.ekskul.proses_scan'), {
        nis: scanInput.value,
        jurnal_id: props.jurnal.id
    }).then(response => {
        scanMessage.value = response.data.message;
        scanStatus.value = response.data.status;
        
        if (response.data.status === 'success' || response.data.status === 'warning') {
            // Play a beep sound
            const audio = new Audio('/sounds/beep.mp3'); // Optional if you have it
            audio.play().catch(e => console.log('Audio play blocked'));
            
            // Reload partial data to reflect new status
            router.reload({ only: ['absen'], preserveScroll: true });
        }
    }).catch(error => {
        scanMessage.value = 'Terjadi kesalahan sistem!';
        scanStatus.value = 'error';
    }).finally(() => {
        scanInput.value = '';
        if (scanInputRef.value) scanInputRef.value.focus();
        
        // Clear message after 3 seconds
        setTimeout(() => {
            scanMessage.value = '';
            scanStatus.value = '';
        }, 3000);
    });
};

const updateAbsenManual = (id_absen, status) => {
    router.post(route('guru.ekskul.absen_manual'), {
        id_absen: id_absen,
        status_hadir: status
    }, {
        preserveScroll: true
    });
};

// Calculate stats
const getStats = () => {
    const total = props.absen.length;
    const hadir = props.absen.filter(a => a.status_hadir === 'Hadir').length;
    const izin = props.absen.filter(a => a.status_hadir === 'Izin' || a.status_hadir === 'Sakit').length;
    const alpa = props.absen.filter(a => a.status_hadir === 'Alpa').length;
    
    return { total, hadir, izin, alpa, percentage: total > 0 ? Math.round((hadir/total)*100) : 0 };
};
</script>

<template>
    <Head :title="`Absensi Scanner - ${jurnal.nama_ekskul}`" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <Link :href="route('guru.ekskul.jurnal', jurnal.ekskul_id)" class="w-10 h-10 rounded-full bg-white dark:bg-gray-800 shadow-sm flex items-center justify-center text-gray-500 hover:text-orange-600 transition-colors border border-gray-100 dark:border-gray-700">
                            <i class="fas fa-arrow-left"></i>
                        </Link>
                        <span>Smart Attendance: {{ jurnal.nama_ekskul }}</span>
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1 ml-13">
                        Silakan scan Kartu Pelajar siswa menggunakan Barcode/QR Scanner.
                    </p>
                </div>
            </div>

            <!-- Jurnal Info & Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Info Jurnal -->
                <div class="md:col-span-2 bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex items-center gap-6">
                    <div class="w-24 h-24 rounded-2xl overflow-hidden shrink-0 shadow-md">
                        <img v-if="jurnal.foto_1" :src="`/uploads/ekskul_jurnal/${jurnal.foto_1}`" class="w-full h-full object-cover">
                        <div v-else class="w-full h-full bg-gray-100 dark:bg-gray-700 flex flex-col items-center justify-center text-gray-400">
                            <i class="fas fa-camera text-2xl mb-1"></i>
                            <span class="text-[10px] uppercase font-bold">No Photo</span>
                        </div>
                    </div>
                    <div>
                        <div class="inline-block px-3 py-1 bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400 text-xs font-bold rounded-full mb-2 border border-orange-200 dark:border-orange-800">
                            <i class="far fa-calendar-alt"></i> {{ jurnal.tanggal }}
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ jurnal.materi_kegiatan }}</h3>
                    </div>
                </div>
                
                <!-- Stats -->
                <div class="bg-gradient-to-br from-orange-500 to-amber-500 rounded-3xl shadow-lg p-6 text-white relative overflow-hidden">
                    <i class="fas fa-chart-pie absolute -bottom-4 -right-4 text-7xl opacity-20"></i>
                    <h4 class="text-white/80 text-sm font-bold uppercase tracking-wider mb-2">Statistik Kehadiran</h4>
                    <div class="flex items-end gap-2 mb-4">
                        <span class="text-4xl font-black">{{ getStats().hadir }}</span>
                        <span class="text-white/80 pb-1">/ {{ getStats().total }} Hadir</span>
                    </div>
                    <div class="w-full bg-white/20 rounded-full h-2 mb-4">
                        <div class="bg-white h-2 rounded-full" :style="`width: ${getStats().percentage}%`"></div>
                    </div>
                    <div class="flex justify-between text-xs font-bold text-white/90">
                        <span>Izin/Sakit: {{ getStats().izin }}</span>
                        <span>Alpa: {{ getStats().alpa }}</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Scanner Input Box -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl shadow-orange-500/10 dark:shadow-none border border-orange-200 dark:border-orange-800 p-6 sticky top-24">
                        <div class="text-center mb-6">
                            <div class="w-20 h-20 bg-orange-100 dark:bg-orange-900/30 text-orange-600 rounded-full flex items-center justify-center mx-auto mb-4 animate-pulse">
                                <i class="fas fa-qrcode text-4xl"></i>
                            </div>
                            <h3 class="font-bold text-lg text-gray-900 dark:text-white">Arahkan Scanner</h3>
                            <p class="text-xs text-gray-500 mt-1">Pastikan kursor berada di kolom bawah ini.</p>
                        </div>
                        
                        <form @submit.prevent="handleScan">
                            <div class="relative">
                                <i class="fas fa-keyboard absolute left-4 top-4 text-gray-400"></i>
                                <input type="text" ref="scanInputRef" v-model="scanInput" class="w-full pl-12 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border-2 border-orange-200 dark:border-orange-800 rounded-2xl focus:border-orange-500 focus:ring-orange-500 text-gray-900 dark:text-white font-mono shadow-inner transition-colors text-center font-bold tracking-widest uppercase" placeholder="SCAN BARCODE / NIS">
                            </div>
                            <button type="submit" class="w-full mt-3 bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 rounded-2xl shadow-lg transition-colors hidden">
                                Submit Manual
                            </button>
                        </form>

                        <!-- Scan Notification Alert -->
                        <div v-if="scanMessage" class="mt-6 p-4 rounded-2xl text-center border font-bold text-sm transform transition-all"
                             :class="{
                                 'bg-green-50 border-green-200 text-green-700 dark:bg-green-900/30 dark:border-green-800 dark:text-green-400 shadow-lg shadow-green-500/20 scale-105': scanStatus === 'success',
                                 'bg-yellow-50 border-yellow-200 text-yellow-700 dark:bg-yellow-900/30 dark:border-yellow-800 dark:text-yellow-400 shadow-lg shadow-yellow-500/20 scale-105': scanStatus === 'warning',
                                 'bg-red-50 border-red-200 text-red-700 dark:bg-red-900/30 dark:border-red-800 dark:text-red-400 shadow-lg shadow-red-500/20 scale-105': scanStatus === 'error'
                             }">
                            <i class="fas mb-2 text-2xl" :class="{
                                'fa-check-circle': scanStatus === 'success',
                                'fa-exclamation-triangle': scanStatus === 'warning',
                                'fa-times-circle': scanStatus === 'error'
                            }"></i>
                            <div v-html="scanMessage"></div>
                        </div>
                    </div>
                </div>

                <!-- Absen List & Manual -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="p-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                            <h3 class="font-bold text-gray-900 dark:text-white">Daftar Kehadiran Anggota</h3>
                        </div>
                        <div class="overflow-x-auto max-h-[60vh] overflow-y-auto">
                            <table class="w-full text-left border-collapse">
                                <thead class="sticky top-0 bg-gray-50 dark:bg-gray-700/90 backdrop-blur-sm text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wider border-b border-gray-100 dark:border-gray-700 z-10">
                                    <tr>
                                        <th class="px-6 py-4 font-bold">Nama Siswa</th>
                                        <th class="px-6 py-4 font-bold text-center">Waktu Scan</th>
                                        <th class="px-6 py-4 font-bold text-center">Status</th>
                                        <th class="px-6 py-4 font-bold text-center">Ubah Manual</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <tr v-for="a in absen" :key="a.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/50 transition-colors" :class="a.status_hadir === 'Hadir' ? 'bg-green-50/30 dark:bg-green-900/10' : ''">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-gray-900 dark:text-white">{{ a.nama_lengkap }}</div>
                                            <div class="text-xs text-gray-500">NIS: {{ a.nis || '-' }} | {{ a.nama_kelas || '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span v-if="a.waktu_scan" class="text-sm font-mono text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded-md border border-gray-200 dark:border-gray-700">{{ a.waktu_scan }}</span>
                                            <span v-else class="text-gray-400">-</span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-3 py-1 rounded-full text-xs font-bold"
                                                  :class="{
                                                      'bg-green-100 text-green-700 border border-green-200 dark:bg-green-900/50 dark:text-green-400 dark:border-green-800': a.status_hadir === 'Hadir',
                                                      'bg-blue-100 text-blue-700 border border-blue-200 dark:bg-blue-900/50 dark:text-blue-400 dark:border-blue-800': ['Izin','Sakit'].includes(a.status_hadir),
                                                      'bg-red-100 text-red-700 border border-red-200 dark:bg-red-900/50 dark:text-red-400 dark:border-red-800': a.status_hadir === 'Alpa'
                                                  }">
                                                {{ a.status_hadir }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex justify-center bg-gray-100 dark:bg-gray-800 p-1 rounded-xl w-fit mx-auto border border-gray-200 dark:border-gray-700">
                                                <button @click="updateAbsenManual(a.id, 'Hadir')" class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors text-xs" :class="a.status_hadir === 'Hadir' ? 'bg-green-500 text-white shadow-sm' : 'text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700'" title="Hadir">
                                                    H
                                                </button>
                                                <button @click="updateAbsenManual(a.id, 'Izin')" class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors text-xs" :class="a.status_hadir === 'Izin' ? 'bg-blue-500 text-white shadow-sm' : 'text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700'" title="Izin">
                                                    I
                                                </button>
                                                <button @click="updateAbsenManual(a.id, 'Sakit')" class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors text-xs" :class="a.status_hadir === 'Sakit' ? 'bg-blue-500 text-white shadow-sm' : 'text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700'" title="Sakit">
                                                    S
                                                </button>
                                                <button @click="updateAbsenManual(a.id, 'Alpa')" class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors text-xs" :class="a.status_hadir === 'Alpa' ? 'bg-red-500 text-white shadow-sm' : 'text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700'" title="Alpa">
                                                    A
                                                </button>
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
    </DashboardLayout>
</template>
