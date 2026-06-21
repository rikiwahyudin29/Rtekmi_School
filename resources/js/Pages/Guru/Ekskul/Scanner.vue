<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    jurnal: Object,
    absensi: Array
});

const scanInput = ref('');
const inputRef = ref(null);
const scanMessage = ref('');
const scanStatus = ref(''); // success, error
const lastScanned = ref(null);

const scanLoading = ref(false);

const processScan = async () => {
    if (!scanInput.value) return;
    
    scanLoading.value = true;
    scanStatus.value = '';
    
    try {
        const response = await axios.post(route('guru.ekskul.scanner.proses'), {
            jurnal_id: props.jurnal.id,
            nis: scanInput.value
        });
        
        if (response.data.success) {
            scanStatus.value = 'success';
            scanMessage.value = response.data.message;
            lastScanned.value = response.data.siswa;
            // Reload page smoothly to update list
            router.reload({ only: ['absensi'] });
            
            // Play success sound
            const audio = new Audio('/sounds/success.mp3'); // Optional if exists
            audio.play().catch(e => {});
        } else {
            scanStatus.value = 'error';
            scanMessage.value = response.data.message;
            lastScanned.value = null;
            
            // Play error sound
            const audio = new Audio('/sounds/error.mp3');
            audio.play().catch(e => {});
        }
    } catch (error) {
        scanStatus.value = 'error';
        scanMessage.value = 'Terjadi kesalahan jaringan atau server.';
        console.error(error);
    } finally {
        scanInput.value = '';
        scanLoading.value = false;
        // Re-focus input
        if (inputRef.value) inputRef.value.focus();
    }
};

// Prevent blur to keep scanner ready
const keepFocus = () => {
    setTimeout(() => {
        if (inputRef.value && document.activeElement !== inputRef.value) {
            inputRef.value.focus();
        }
    }, 100);
};

onMounted(() => {
    if (inputRef.value) inputRef.value.focus();
    window.addEventListener('click', keepFocus);
});

onUnmounted(() => {
    window.removeEventListener('click', keepFocus);
});

const updateManual = (id, status) => {
    router.post(route('guru.ekskul.absen_manual'), {
        id: id,
        status: status
    }, { preserveScroll: true });
};
</script>

<template>
    <Head :title="`Scanner Absensi`" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <Link :href="route('guru.ekskul.jurnal', jurnal.ekskul_id)" class="text-indigo-600 hover:text-indigo-800 text-sm font-bold flex items-center gap-2 mb-2">
                        <i class="fas fa-arrow-left"></i> Kembali ke Jurnal
                    </Link>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <i class="fas fa-barcode text-indigo-500"></i>
                        Scanner Presensi: {{ jurnal.nama_ekskul }}
                    </h2>
                    <p class="text-gray-500 mt-1">
                        Pertemuan Tanggal: <b>{{ jurnal.tanggal }}</b>
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- SCANNER AREA -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex flex-col items-center">
                        <div class="w-full relative mb-6">
                            <div class="absolute inset-0 bg-indigo-500/10 rounded-2xl animate-pulse"></div>
                            <div class="w-full h-48 border-4 border-dashed border-indigo-300 rounded-2xl flex flex-col items-center justify-center text-indigo-400 relative z-10 bg-white/50 backdrop-blur-sm">
                                <i class="fas fa-qrcode text-6xl mb-2"></i>
                                <div class="font-bold">Arahkan Scanner ke Kartu Siswa</div>
                                <div class="text-xs text-center px-4 mt-2">Pastikan kursor selalu berada di kolom input di bawah ini.</div>
                            </div>
                        </div>

                        <form @submit.prevent="processScan" class="w-full">
                            <input 
                                type="text" 
                                v-model="scanInput" 
                                ref="inputRef" 
                                placeholder="Scan Barcode / Ketik NIS lalu Enter..." 
                                class="w-full rounded-2xl border-4 border-indigo-100 focus:border-indigo-500 text-center font-mono text-xl py-4 shadow-inner outline-none transition-all"
                                :disabled="scanLoading"
                                autocomplete="off"
                                autofocus
                            >
                        </form>

                        <!-- Scan Result Status -->
                        <div v-if="scanMessage" class="mt-6 w-full p-4 rounded-2xl text-center border animate-fade-in" :class="scanStatus === 'success' ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700'">
                            <i class="text-3xl mb-2 block" :class="scanStatus === 'success' ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i>
                            <div class="font-bold">{{ scanMessage }}</div>
                        </div>

                        <!-- Last Scanned Info -->
                        <div v-if="lastScanned" class="mt-4 w-full bg-indigo-50 dark:bg-indigo-900/30 rounded-2xl p-4 flex items-center gap-4 border border-indigo-100">
                            <img :src="`/uploads/siswa/${lastScanned.foto || 'default.png'}`" @error="$event.target.src='https://ui-avatars.com/api/?name='+lastScanned.nama_lengkap" class="w-16 h-16 rounded-xl object-cover border-2 border-white shadow-sm">
                            <div>
                                <div class="text-xs font-bold text-indigo-500 uppercase">Baru Saja Hadir</div>
                                <div class="font-black text-gray-900 dark:text-white text-lg leading-tight">{{ lastScanned.nama_lengkap }}</div>
                                <div class="text-sm text-gray-500 font-mono">{{ lastScanned.nis }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- LIST ABSENSI -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="p-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 flex justify-between items-center">
                            <h3 class="font-bold text-gray-900 dark:text-white">Daftar Kehadiran Anggota</h3>
                        </div>
                        <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-white dark:bg-gray-800 text-gray-600 font-bold uppercase text-xs sticky top-0 shadow-sm z-10">
                                    <tr>
                                        <th class="px-6 py-4">Siswa</th>
                                        <th class="px-6 py-4">Kelas</th>
                                        <th class="px-6 py-4 text-center">Status</th>
                                        <th class="px-6 py-4 text-center">Waktu</th>
                                        <th class="px-6 py-4 text-center">Ubah Manual</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <tr v-for="a in absensi" :key="a.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50" :class="a.status_hadir === 'Hadir' ? 'bg-green-50/30' : ''">
                                        <td class="px-6 py-3">
                                            <div class="font-bold text-gray-900 dark:text-white">{{ a.nama_lengkap }}</div>
                                            <div class="text-xs text-gray-500">{{ a.nis }}</div>
                                        </td>
                                        <td class="px-6 py-3">{{ a.nama_kelas }}</td>
                                        <td class="px-6 py-3 text-center">
                                            <span class="px-3 py-1 rounded-full text-xs font-bold" 
                                                :class="{
                                                    'bg-green-100 text-green-700': a.status_hadir === 'Hadir',
                                                    'bg-blue-100 text-blue-700': a.status_hadir === 'Sakit',
                                                    'bg-yellow-100 text-yellow-700': a.status_hadir === 'Izin',
                                                    'bg-red-100 text-red-700': a.status_hadir === 'Alpa',
                                                }">
                                                {{ a.status_hadir }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 text-center font-mono text-xs text-gray-500">
                                            {{ a.waktu_scan || '-' }}
                                        </td>
                                        <td class="px-6 py-3">
                                            <div class="flex justify-center gap-1">
                                                <button @click="updateManual(a.id, 'Hadir')" class="w-7 h-7 rounded bg-green-50 text-green-600 hover:bg-green-500 hover:text-white text-xs tooltip" data-tip="Hadir"><i class="fas fa-check"></i></button>
                                                <button @click="updateManual(a.id, 'Sakit')" class="w-7 h-7 rounded bg-blue-50 text-blue-600 hover:bg-blue-500 hover:text-white text-xs tooltip" data-tip="Sakit"><i class="fas fa-briefcase-medical"></i></button>
                                                <button @click="updateManual(a.id, 'Izin')" class="w-7 h-7 rounded bg-yellow-50 text-yellow-600 hover:bg-yellow-500 hover:text-white text-xs tooltip" data-tip="Izin"><i class="fas fa-envelope"></i></button>
                                                <button @click="updateManual(a.id, 'Alpa')" class="w-7 h-7 rounded bg-red-50 text-red-600 hover:bg-red-500 hover:text-white text-xs tooltip" data-tip="Alpa"><i class="fas fa-times"></i></button>
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
<style scoped>
.animate-fade-in {
    animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
