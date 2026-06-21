<script setup>
import { ref, onMounted, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    tamu: Array,
    tanggal: String
});

// Stats Computation
const totalTamu = computed(() => props.tamu.length);
const tamuMenunggu = computed(() => props.tamu.filter(t => t.status === 'Menunggu').length);
const tamuSelesai = computed(() => props.tamu.filter(t => t.status === 'Selesai').length);

// Modal Logic
const isModalOpen = ref(false);
const activeTamu = ref(null);

const openSignatureModal = (tamu) => {
    activeTamu.value = tamu;
    isModalOpen.value = true;
    setTimeout(() => {
        initCanvas();
    }, 200); // Give transition time to finish
};

const closeModal = () => {
    isModalOpen.value = false;
    setTimeout(() => {
        activeTamu.value = null;
        clearCanvas();
    }, 300);
};

// Canvas Logic
const canvasRef = ref(null);
let ctx = null;
let isDrawing = false;

const initCanvas = () => {
    if (!canvasRef.value) return;
    const canvas = canvasRef.value;
    ctx = canvas.getContext('2d');
    
    // Set proper resolution for high DPI displays
    const rect = canvas.getBoundingClientRect();
    canvas.width = rect.width;
    canvas.height = rect.height;
    
    ctx.lineWidth = 3;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.strokeStyle = '#312e81'; // Deep indigo for signature
    
    // Fill white background (important for saving as image)
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
};

const getPos = (e) => {
    const canvas = canvasRef.value;
    const rect = canvas.getBoundingClientRect();
    const clientX = e.touches ? e.touches[0].clientX : e.clientX;
    const clientY = e.touches ? e.touches[0].clientY : e.clientY;
    return {
        x: clientX - rect.left,
        y: clientY - rect.top
    };
};

const startDrawing = (e) => {
    isDrawing = true;
    const pos = getPos(e);
    ctx.beginPath();
    ctx.moveTo(pos.x, pos.y);
};

const draw = (e) => {
    if (!isDrawing) return;
    e.preventDefault(); // Prevent scrolling on touch devices
    const pos = getPos(e);
    ctx.lineTo(pos.x, pos.y);
    ctx.stroke();
};

const stopDrawing = () => {
    isDrawing = false;
    ctx.closePath();
};

const clearCanvas = () => {
    if (!ctx || !canvasRef.value) return;
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, canvasRef.value.width, canvasRef.value.height);
};

// Submission
const form = useForm({
    id_tamu: '',
    ttd_data: ''
});

const saveSignature = () => {
    if (!canvasRef.value) return;
    
    form.id_tamu = activeTamu.value.id;
    form.ttd_data = canvasRef.value.toDataURL('image/png');
    
    form.post(route('guru.piket.buku-tamu.ttd'), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        }
    });
};

const getStatusClass = (status) => {
    if (status === 'Selesai') return 'bg-emerald-500/10 text-emerald-600 border-emerald-200';
    return 'bg-amber-500/10 text-amber-600 border-amber-200';
};
</script>

<template>
    <Head title="Buku Tamu Lobi" />

    <DashboardLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <!-- Premium Gradient Banner -->
                <div class="bg-gradient-to-br from-indigo-600 via-blue-700 to-blue-800 rounded-3xl p-8 shadow-2xl shadow-indigo-500/20 relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                    <!-- Decorative background shapes -->
                    <div class="absolute top-0 right-0 -mt-20 -mr-20 w-72 h-72 bg-white/10 rounded-full blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 -mb-20 -ml-20 w-64 h-64 bg-indigo-400/20 rounded-full blur-3xl"></div>
                    <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-5 mix-blend-overlay"></div>
                    
                    <div class="relative z-10 flex-1">
                        <div class="flex items-center gap-3 mb-2.5">
                            <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-[10px] sm:text-xs font-black text-white uppercase tracking-wider border border-white/20 shadow-sm">Layanan Terpadu</span>
                            <span class="text-indigo-100 text-xs sm:text-sm font-semibold tracking-wide flex items-center gap-2">
                                <i class="fas fa-shield-alt opacity-70"></i> Petugas Piket Harian
                            </span>
                        </div>
                        <h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-white tracking-tight mb-3 drop-shadow-sm">Buku Tamu Lobi</h2>
                        <p class="text-indigo-100/90 font-medium text-sm sm:text-base max-w-2xl leading-relaxed">
                            Pusat pemantauan data kedatangan tamu, konfirmasi pelayanan, dan rekam jejak digital buku tamu madrasah.
                        </p>
                    </div>

                    <div class="relative z-10 flex flex-col items-end gap-3 shrink-0 w-full md:w-auto mt-4 md:mt-0">
                        <div class="w-full md:w-auto inline-flex items-center justify-center px-6 py-3.5 bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl font-black text-white shadow-xl shadow-black/5">
                            <i class="fas fa-calendar-day mr-3 text-indigo-300"></i> {{ tanggal }}
                        </div>
                        <div class="text-xs text-indigo-200 font-medium flex items-center bg-black/10 backdrop-blur-sm px-3.5 py-2 rounded-xl border border-white/5">
                            <i class="fas fa-info-circle mr-2 opacity-70"></i> Data kunjungan hari ini
                        </div>
                    </div>
                </div>

                <div v-if="$page.props.flash?.message" class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-center text-emerald-800 shadow-sm animate-fade-in-down">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center mr-4 flex-shrink-0">
                        <i class="fas fa-check text-emerald-600"></i>
                    </div>
                    <span class="font-bold">{{ $page.props.flash.message }}</span>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 relative overflow-hidden group">
                        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-users text-6xl text-blue-600"></i>
                        </div>
                        <p class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">Total Kunjungan</p>
                        <h3 class="text-4xl font-black text-gray-900">{{ totalTamu }}</h3>
                    </div>
                    <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-amber-100 relative overflow-hidden group">
                        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-clock text-6xl text-amber-600"></i>
                        </div>
                        <p class="text-sm font-bold text-amber-600/80 uppercase tracking-wider mb-2">Menunggu Konfirmasi</p>
                        <h3 class="text-4xl font-black text-amber-600">{{ tamuMenunggu }}</h3>
                    </div>
                    <div class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-emerald-100 relative overflow-hidden group">
                        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-check-circle text-6xl text-emerald-600"></i>
                        </div>
                        <p class="text-sm font-bold text-emerald-600/80 uppercase tracking-wider mb-2">Selesai & Terdokumentasi</p>
                        <h3 class="text-4xl font-black text-emerald-600">{{ tamuSelesai }}</h3>
                    </div>
                </div>

                <!-- Table Container -->
                <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <h3 class="font-bold text-lg text-gray-800"><i class="fas fa-list-ul mr-2 text-indigo-500"></i> Daftar Tamu Hari Ini</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-white">
                                <tr>
                                    <th scope="col" class="px-6 py-5 text-left text-xs font-black text-gray-400 uppercase tracking-widest w-16">No</th>
                                    <th scope="col" class="px-6 py-5 text-left text-xs font-black text-gray-400 uppercase tracking-widest">Identitas Tamu</th>
                                    <th scope="col" class="px-6 py-5 text-left text-xs font-black text-gray-400 uppercase tracking-widest">Keperluan</th>
                                    <th scope="col" class="px-6 py-5 text-center text-xs font-black text-gray-400 uppercase tracking-widest w-32">Status</th>
                                    <th scope="col" class="px-6 py-5 text-center text-xs font-black text-gray-400 uppercase tracking-widest w-40">Aksi Piket</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-if="tamu.length === 0">
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500 font-medium">
                                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                            <i class="fas fa-folder-open text-2xl text-gray-300"></i>
                                        </div>
                                        Belum ada kunjungan tamu hari ini.
                                    </td>
                                </tr>
                                <tr v-for="t in tamu" :key="t.id" class="hover:bg-gray-50/50 transition-colors group">
                                    <td class="px-6 py-5 whitespace-nowrap">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 font-black text-sm">
                                            {{ t.no_antrian || '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="text-sm font-black text-gray-900 group-hover:text-indigo-600 transition-colors">{{ t.nama_lengkap }}</div>
                                        <div class="text-xs text-gray-500 flex gap-2 items-center mt-1.5 font-medium">
                                            <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded-md">{{ t.kategori }}</span>
                                            <span v-if="t.instansi_asal" class="flex items-center"><i class="fas fa-building mr-1.5 text-gray-400"></i>{{ t.instansi_asal }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <p class="text-sm text-gray-600 font-medium line-clamp-2">{{ t.keperluan }}</p>
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap text-center">
                                        <span class="px-3 py-1.5 inline-flex text-xs leading-5 font-black rounded-xl border" :class="getStatusClass(t.status)">
                                            {{ t.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap text-center text-sm font-medium">
                                        <div class="flex items-center justify-center gap-2">
                                            <!-- Tombol Cetak Karcis Thermal -->
                                            <a :href="route('guru.piket.buku-tamu.cetak', t.id)" target="_blank" 
                                               class="inline-flex items-center px-3 py-2 bg-gray-100 hover:bg-gray-200 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 transition-colors tooltip" title="Cetak Karcis Antrian">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            
                                            <button v-if="t.status === 'Menunggu'" @click="openSignatureModal(t)" 
                                                  class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-blue-600 border border-transparent rounded-xl text-xs font-bold text-white uppercase tracking-wider hover:from-indigo-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-lg shadow-indigo-500/30 transform hover:-translate-y-0.5 transition-all duration-200">
                                                <i class="fas fa-signature mr-2"></i> Konfirmasi
                                            </button>
                                            <div v-else class="inline-flex items-center px-4 py-2 bg-emerald-50 text-emerald-600 rounded-xl text-xs font-bold border border-emerald-100">
                                                <i class="fas fa-check-double mr-2"></i> Selesai
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Glassmorphism Signature Modal -->
        <transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="isModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="closeModal"></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to-class="opacity-100 translate-y-0 sm:scale-100" leave-active-class="ease-in duration-200" leave-from-class="opacity-100 translate-y-0 sm:scale-100" leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        <div v-if="isModalOpen" class="inline-block align-bottom bg-white/95 backdrop-blur-xl rounded-3xl text-left overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.12)] transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-white">
                            
                            <!-- Header Modal -->
                            <div class="px-6 py-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-100 to-blue-50 flex items-center justify-center mr-3">
                                        <i class="fas fa-signature text-indigo-600"></i>
                                    </div>
                                    <h3 class="text-xl font-black text-gray-900" id="modal-title">
                                        Tanda Tangan Konfirmasi
                                    </h3>
                                </div>
                                <button @click="closeModal" class="text-gray-400 hover:text-gray-500 bg-gray-100 hover:bg-gray-200 rounded-full p-2 transition-colors">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>

                            <div class="px-6 py-6">
                                <div class="bg-indigo-50/50 border border-indigo-100 rounded-2xl p-4 mb-6">
                                    <p class="text-xs text-indigo-400 font-bold uppercase tracking-wider mb-1">Identitas Tamu</p>
                                    <p class="text-lg font-black text-indigo-900">{{ activeTamu?.nama_lengkap }}</p>
                                    <p class="text-sm text-indigo-600 font-medium mt-1">{{ activeTamu?.keperluan }}</p>
                                </div>
                                
                                <div>
                                    <div class="flex justify-between items-end mb-3">
                                        <label class="block text-sm font-bold text-gray-700">Area Tanda Tangan Guru Piket</label>
                                        <button @click="clearCanvas" type="button" class="text-rose-500 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">
                                            <i class="fas fa-eraser mr-1"></i> Bersihkan
                                        </button>
                                    </div>
                                    <div class="border-2 border-dashed border-indigo-200 rounded-2xl overflow-hidden bg-white relative h-64 touch-none shadow-inner group">
                                        <canvas 
                                            ref="canvasRef" 
                                            class="w-full h-full cursor-crosshair relative z-10"
                                            @mousedown="startDrawing"
                                            @mousemove="draw"
                                            @mouseup="stopDrawing"
                                            @mouseout="stopDrawing"
                                            @touchstart="startDrawing"
                                            @touchmove="draw"
                                            @touchend="stopDrawing"
                                        ></canvas>
                                        
                                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-40 group-hover:opacity-10 transition-opacity">
                                            <span class="text-indigo-200 font-black text-3xl rotate-12 select-none">Tanda Tangan Disini</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="px-6 py-5 bg-gray-50/80 border-t border-gray-100 sm:flex sm:flex-row-reverse gap-3">
                                <button type="button" @click="saveSignature" :disabled="form.processing" class="w-full inline-flex justify-center items-center rounded-xl border border-transparent shadow-lg shadow-indigo-500/30 px-6 py-3 bg-gradient-to-r from-indigo-600 to-blue-600 text-base font-bold text-white hover:from-indigo-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm transition-all transform hover:-translate-y-0.5 disabled:opacity-70 disabled:transform-none">
                                    <i class="fas fa-save mr-2" v-if="!form.processing"></i>
                                    <i class="fas fa-spinner fa-spin mr-2" v-else></i>
                                    {{ form.processing ? 'Menyimpan...' : 'Konfirmasi & Simpan' }}
                                </button>
                                <button type="button" @click="closeModal" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-200 shadow-sm px-6 py-3 bg-white text-base font-bold text-gray-700 hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors">
                                    Batal
                                </button>
                            </div>
                        </div>
                    </transition>
                </div>
            </div>
        </transition>

    </DashboardLayout>
</template>
