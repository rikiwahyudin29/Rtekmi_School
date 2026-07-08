<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    web: Object,
    setting: Object
});

const bgImage = 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?q=80&w=2070&auto=format&fit=crop';
const pathLogo = computed(() => props.web?.logo ? '/uploads/identitas/' + props.web.logo : '/assets/img/logo.png');
const namaSekolah = computed(() => props.web?.nama_sekolah || 'NAMA SEKOLAH');

// State: 'countdown', 'login', 'suspense', 'result'
const currentStep = ref('countdown'); 

// Countdown logic
const targetDate = new Date(props.setting?.tgl_pengumuman || Date.now()).getTime();
const cdHari = ref('00');
const cdJam = ref('00');
const cdMnt = ref('00');
const cdDtk = ref('00');
let timerInterval = null;

// Form & Data logic
const nisnForm = ref({
    nisn: '',
    tanggal_lahir: '',
    processing: false,
    error: ''
});

const siswaData = ref(null);
const hasilData = ref(null);

onMounted(() => {
    // Jalankan timer untuk cek kapan pengumuman dibuka
    timerInterval = setInterval(() => {
        const now = new Date().getTime();
        const distance = targetDate - now;

        if (distance < 0) {
            clearInterval(timerInterval);
            if (currentStep.value === 'countdown') {
                currentStep.value = 'login';
            }
        } else {
            currentStep.value = 'countdown';
            cdHari.value = String(Math.floor(distance / (1000 * 60 * 60 * 24))).padStart(2, '0');
            cdJam.value = String(Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))).padStart(2, '0');
            cdMnt.value = String(Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, '0');
            cdDtk.value = String(Math.floor((distance % (1000 * 60)) / 1000)).padStart(2, '0');
        }
    }, 1000);
});

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
});

const prosesLogin = async () => {
    if (!nisnForm.value.nisn || !nisnForm.value.tanggal_lahir) {
        nisnForm.value.error = 'Mohon masukkan NISN dan Tanggal Lahir Anda!';
        return;
    }
    
    nisnForm.value.processing = true;
    nisnForm.value.error = '';

    try {
        const response = await axios.post(route('kelulusan.cek'), { 
            nisn: nisnForm.value.nisn,
            tanggal_lahir: nisnForm.value.tanggal_lahir
        });
        if(response.data.status === 'success') {
            siswaData.value = response.data.data;
            
            // Animasi Amplop Deg-degan
            currentStep.value = 'suspense';
            
            // Jeda Jantung 3.5 Detik
            setTimeout(() => { 
                getHasilAkhir(); 
            }, 3500);
        }
    } catch (error) {
        nisnForm.value.error = error.response?.data?.message || 'Terjadi kesalahan jaringan server.';
    } finally {
        nisnForm.value.processing = false;
    }
};

const getHasilAkhir = async () => {
    try {
        const res = await axios.post(route('kelulusan.hasil'));
        if(res.data.status === 'success') {
            hasilData.value = res.data;
            currentStep.value = 'result';
        } else {
            alert(res.data.message);
            kembaliAwal();
        }
    } catch (error) {
        alert(error.response?.data?.message || 'Gagal mengambil hasil kelulusan.');
        kembaliAwal();
    }
};

const kembaliAwal = () => {
    nisnForm.value.nisn = '';
    nisnForm.value.tanggal_lahir = '';
    nisnForm.value.error = '';
    currentStep.value = 'login';
};

const downloadDokumen = (tipe) => {
    window.open(route('kelulusan.download') + '?tipe=' + tipe, '_blank');
};
</script>

<template>
    <Head title="Portal Kelulusan" />

    <div class="min-h-screen bg-cover bg-center bg-no-repeat flex flex-col relative text-white" :style="`background-image: url('${bgImage}'); font-family: 'Plus Jakarta Sans', sans-serif;`">
        
        <!-- OVERLAY GELAP ELEGAN -->
        <div class="absolute inset-0 bg-slate-900/85 backdrop-blur-[4px] z-0"></div>

        <!-- NAVBAR HEADER -->
        <nav class="relative z-10 w-full p-6 lg:px-12 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="bg-white p-1 rounded-full shadow-lg">
                    <img :src="pathLogo" alt="Logo" class="w-10 h-10 md:w-12 md:h-12 object-contain">
                </div>
                <div class="hidden md:block">
                    <h1 class="font-extrabold text-lg tracking-wider uppercase text-white drop-shadow-md">{{ namaSekolah }}</h1>
                    <p class="text-blue-300 text-xs tracking-widest font-semibold">PORTAL RESMI SEKOLAH</p>
                </div>
            </div>
            <div class="flex gap-3">
                <a href="/" class="w-10 h-10 rounded-full border border-white/20 flex items-center justify-center hover:bg-white/20 transition-all shadow-lg backdrop-blur-sm" title="Kembali ke Beranda">
                    <i class="fas fa-home"></i>
                </a>
                <a v-if="web && web.instagram" :href="web.instagram" class="w-10 h-10 rounded-full border border-white/20 flex items-center justify-center hover:bg-white/20 transition-all shadow-lg backdrop-blur-sm">
                    <i class="fab fa-instagram"></i>
                </a>
            </div>
        </nav>

        <!-- MAIN CONTENT AREA -->
        <main class="relative z-10 flex-grow flex items-center justify-center p-4">
            
            <div class="w-full max-w-4xl flex flex-col items-center justify-center text-center">
                
                <h2 class="text-2xl md:text-5xl font-black uppercase tracking-tight mb-2 drop-shadow-lg leading-tight">
                    {{ namaSekolah }}
                </h2>
                <p class="text-slate-300 text-sm md:text-xl font-medium tracking-wide mb-10 drop-shadow-md">Portal Pengumuman Kelulusan Resmi TA {{ new Date().getFullYear() - 1 }}/{{ new Date().getFullYear() }}</p>

                <!-- ============================================== -->
                <!-- SECTION 1: COUNTDOWN -->
                <!-- ============================================== -->
                <div v-if="currentStep === 'countdown'" class="w-full transition-all duration-500 animate-fade-in">
                    <h3 class="text-3xl md:text-5xl font-black text-sky-400 mb-8 drop-shadow-[0_0_15px_rgba(56,189,248,0.5)]">Dibuka Dalam:</h3>
                    
                    <div class="flex justify-center gap-3 md:gap-6 text-white mb-2">
                        <div class="flex flex-col items-center">
                            <div class="clock-box rounded-xl md:rounded-2xl p-4 md:p-6 w-16 h-20 md:w-28 md:h-32 flex items-center justify-center mb-3">
                                <span class="text-3xl md:text-6xl font-black text-white relative z-20 font-mono tracking-tighter">{{ cdHari }}</span>
                            </div>
                            <span class="text-[10px] md:text-xs uppercase tracking-widest text-slate-300 font-bold">Days</span>
                        </div>
                        
                        <div class="flex flex-col items-center">
                            <div class="clock-box rounded-xl md:rounded-2xl p-4 md:p-6 w-16 h-20 md:w-28 md:h-32 flex items-center justify-center mb-3">
                                <span class="text-3xl md:text-6xl font-black text-white relative z-20 font-mono tracking-tighter">{{ cdJam }}</span>
                            </div>
                            <span class="text-[10px] md:text-xs uppercase tracking-widest text-slate-300 font-bold">Hours</span>
                        </div>
                        
                        <div class="flex flex-col items-center">
                            <div class="clock-box rounded-xl md:rounded-2xl p-4 md:p-6 w-16 h-20 md:w-28 md:h-32 flex items-center justify-center mb-3">
                                <span class="text-3xl md:text-6xl font-black text-white relative z-20 font-mono tracking-tighter">{{ cdMnt }}</span>
                            </div>
                            <span class="text-[10px] md:text-xs uppercase tracking-widest text-slate-300 font-bold">Minutes</span>
                        </div>
                        
                        <div class="flex flex-col items-center">
                            <div class="clock-box rounded-xl md:rounded-2xl p-4 md:p-6 w-16 h-20 md:w-28 md:h-32 flex items-center justify-center mb-3 border-b-sky-500" style="border-bottom-width: 3px; border-bottom-color: #38bdf8;">
                                <span class="text-3xl md:text-6xl font-black text-sky-400 relative z-20 font-mono tracking-tighter">{{ cdDtk }}</span>
                            </div>
                            <span class="text-[10px] md:text-xs uppercase tracking-widest text-sky-400 font-bold">Seconds</span>
                        </div>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- SECTION 2: FORM LOGIN -->
                <!-- ============================================== -->
                <div v-else-if="currentStep === 'login'" class="w-full max-w-lg transition-all duration-500 animate-fade-in-up">
                    <div class="bg-white/10 backdrop-blur-md border border-white/20 p-8 rounded-3xl shadow-[0_0_40px_rgba(0,0,0,0.5)] text-left">
                        <div class="text-center mb-6">
                            <h3 class="text-3xl font-black text-sky-400 drop-shadow-md mb-2">PENGUMUMAN DIBUKA</h3>
                            <p class="text-slate-300 text-sm">Masukkan Nomor Induk Siswa Nasional (NISN) Anda untuk melihat hasil kelulusan.</p>
                        </div>
                        
                        <form @submit.prevent="prosesLogin">
                            <div class="mb-4 relative">
                                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                    <i class="fas fa-id-card text-sky-400 text-lg"></i>
                                </div>
                                <input type="text" inputmode="numeric" v-model="nisnForm.nisn" class="glass-input w-full pl-14 pr-5 py-4 rounded-2xl font-bold text-xl tracking-[0.2em] transition-all" placeholder="NISN (Misal: 0012345678)" required autocomplete="off">
                            </div>

                            <div class="mb-6 relative">
                                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                    <i class="fas fa-calendar text-sky-400 text-lg"></i>
                                </div>
                                <input type="date" v-model="nisnForm.tanggal_lahir" class="glass-input w-full pl-14 pr-5 py-4 rounded-2xl font-bold text-lg transition-all" required title="Tanggal Lahir">
                            </div>

                            <div v-if="nisnForm.error" class="mb-5 p-3 bg-red-500/20 border border-red-500/50 text-red-200 text-sm font-bold rounded-xl text-center backdrop-blur-sm animate-fade-in">
                                {{ nisnForm.error }}
                            </div>

                            <button type="submit" :disabled="nisnForm.processing" class="w-full bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-400 hover:to-blue-500 text-white font-bold py-4 rounded-2xl shadow-[0_0_20px_rgba(14,165,233,0.4)] transition-all transform hover:scale-105 text-lg uppercase tracking-wider flex items-center justify-center disabled:opacity-75 disabled:scale-100">
                                <i v-if="nisnForm.processing" class="fas fa-spinner fa-spin mr-2"></i>
                                <i v-else class="fas fa-search mr-2"></i> 
                                {{ nisnForm.processing ? 'MEMERIKSA...' : 'LIHAT HASIL' }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- SECTION 3: ANIMASI DEG-DEGAN -->
                <!-- ============================================== -->
                <div v-else-if="currentStep === 'suspense'" class="w-full max-w-lg transition-all duration-500 animate-fade-in">
                    <div class="bg-white/10 backdrop-blur-md border border-white/20 p-10 rounded-3xl shadow-[0_0_40px_rgba(0,0,0,0.5)]">
                        <div class="relative w-32 h-32 mx-auto mb-8">
                            <div class="absolute inset-0 bg-yellow-400/20 rounded-full animate-ping"></div>
                            <div class="absolute inset-0 flex items-center justify-center text-7xl text-yellow-400 envelope-shake drop-shadow-[0_0_15px_rgba(250,204,21,0.5)]">
                                <i class="fas fa-envelope-open-text"></i>
                            </div>
                        </div>
                        <h2 class="text-2xl font-black text-white mb-2 tracking-wide">Membuka Dokumen...</h2>
                        <p class="text-sky-300 text-sm font-medium animate-pulse">Berdoa sejenak, siapkan jantung Anda...</p>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- SECTION 4: HASIL KELULUSAN (KERTAS PUTIH FORMAL) -->
                <!-- ============================================== -->
                <div v-else-if="currentStep === 'result'" class="w-full max-w-lg transition-all duration-500 animate-fade-in-up">
                    <div class="bg-white rounded-3xl shadow-[0_0_50px_rgba(0,0,0,0.7)] overflow-hidden text-slate-800 text-left">
                        
                        <!-- Area Status -->
                        <div class="pt-8 pb-6 px-6 text-center relative overflow-hidden transition-colors duration-500" 
                             :class="{
                                 'bg-emerald-500': hasilData?.status_lulus?.toUpperCase() === 'LULUS',
                                 'bg-red-500': hasilData?.status_lulus?.toUpperCase() === 'TIDAK LULUS',
                                 'bg-amber-500': hasilData?.status_lulus?.toUpperCase() !== 'LULUS' && hasilData?.status_lulus?.toUpperCase() !== 'TIDAK LULUS'
                             }">
                            <div class="absolute inset-0 opacity-10" style="background-image: url('https://www.transparenttextures.com/patterns/cubes.png')"></div>
                            
                            <div class="w-20 h-20 mx-auto rounded-full flex items-center justify-center text-4xl bg-white shadow-xl mb-4 relative z-10 animate-bounce-in"
                                 :class="{
                                    'text-emerald-600': hasilData?.status_lulus?.toUpperCase() === 'LULUS',
                                    'text-red-600': hasilData?.status_lulus?.toUpperCase() === 'TIDAK LULUS',
                                    'text-amber-600': hasilData?.status_lulus?.toUpperCase() !== 'LULUS' && hasilData?.status_lulus?.toUpperCase() !== 'TIDAK LULUS'
                                 }">
                                <i class="fas" :class="{
                                    'fa-graduation-cap': hasilData?.status_lulus?.toUpperCase() === 'LULUS',
                                    'fa-times': hasilData?.status_lulus?.toUpperCase() === 'TIDAK LULUS',
                                    'fa-exclamation': hasilData?.status_lulus?.toUpperCase() !== 'LULUS' && hasilData?.status_lulus?.toUpperCase() !== 'TIDAK LULUS'
                                }"></i>
                            </div>
                            <h2 class="text-4xl font-black tracking-tight text-white drop-shadow-md relative z-10 uppercase">
                                {{ hasilData?.status_lulus?.toUpperCase() || 'PENDING' }}
                            </h2>
                        </div>

                        <div class="p-8">
                            <h3 class="text-xl font-bold text-slate-800 mb-1">{{ hasilData?.siswa?.nama_lengkap }}</h3>
                            <p class="text-slate-500 text-sm font-mono mb-6">NISN: {{ hasilData?.siswa?.nisn }}</p>
                            
                            <div class="text-sm text-slate-600 mb-8 font-medium leading-relaxed bg-slate-50 p-4 rounded-xl border border-slate-100">
                                <template v-if="hasilData?.status_lulus?.toUpperCase() === 'LULUS'">
                                    Selamat! Anda dinyatakan lulus. Segera unduh dan simpan dokumen SKL beserta Transkrip Nilai Anda di bawah ini.
                                </template>
                                <template v-else-if="hasilData?.status_lulus?.toUpperCase() === 'TIDAK LULUS'">
                                    Tetap semangat dan jangan menyerah. Masa depan masih panjang, ini bukanlah akhir segalanya.
                                </template>
                                <template v-else>
                                    Dokumen kelulusan ditangguhkan sementara. Silakan hubungi Wali Kelas atau pihak Tata Usaha sekolah untuk informasi lebih lanjut.
                                </template>
                                
                                <div v-if="hasilData?.catatan" class="mt-3 pt-3 border-t border-slate-200">
                                    <strong>Catatan Sekolah:</strong> <br>
                                    <span class="italic">"{{ hasilData?.catatan }}"</span>
                                </div>
                            </div>

                            <!-- Tombol Download Khusus LULUS -->
                            <div v-if="hasilData?.status_lulus?.toUpperCase() === 'LULUS'" class="flex flex-col gap-3">
                                <button @click="downloadDokumen('skl')" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3.5 px-5 rounded-xl shadow-lg shadow-emerald-500/30 transition-all flex justify-between items-center group">
                                    <span class="flex items-center"><i class="fas fa-file-pdf mr-3 text-emerald-100 text-lg"></i> Download SKL</span>
                                    <i class="fas fa-download opacity-50 group-hover:opacity-100 group-hover:-translate-y-1 transition-all"></i>
                                </button>
                                
                                <button @click="downloadDokumen('transkrip')" class="w-full bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-3.5 px-5 rounded-xl shadow-lg shadow-indigo-500/30 transition-all flex justify-between items-center group">
                                    <span class="flex items-center"><i class="fas fa-file-alt mr-3 text-indigo-100 text-lg"></i> Download Transkrip</span>
                                    <i class="fas fa-download opacity-50 group-hover:opacity-100 group-hover:-translate-y-1 transition-all"></i>
                                </button>
                            </div>
                            
                            <div class="text-center">
                                <button @click="kembaliAwal" class="mt-6 text-slate-400 hover:text-slate-600 text-sm font-bold underline transition-colors">Cek Siswa Lain</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>

        <!-- FOOTER -->
        <footer class="relative z-10 w-full p-4 text-center text-slate-400 text-xs mt-auto">
            &copy; {{ new Date().getFullYear() }} {{ namaSekolah }}. All rights reserved.
        </footer>
    </div>
</template>

<style scoped>
/* Google Font Plus Jakarta Sans */
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800;900&display=swap');

/* Animasi Amplop Deg-degan */
.envelope-shake { animation: shake 0.5s infinite; }
@keyframes shake {
    0% { transform: translate(1px, 1px) rotate(0deg); }
    10% { transform: translate(-1px, -2px) rotate(-1deg); }
    20% { transform: translate(-3px, 0px) rotate(1deg); }
    30% { transform: translate(3px, 2px) rotate(0deg); }
    40% { transform: translate(1px, -1px) rotate(1deg); }
    50% { transform: translate(-1px, 2px) rotate(-1deg); }
    60% { transform: translate(-3px, 1px) rotate(0deg); }
    70% { transform: translate(3px, 1px) rotate(-1deg); }
    80% { transform: translate(-1px, -1px) rotate(1deg); }
    90% { transform: translate(1px, 2px) rotate(0deg); }
    100% { transform: translate(1px, -2px) rotate(-1deg); }
}

/* Box Countdown ala Flip Clock Modern */
.clock-box {
    background: linear-gradient(180deg, rgba(30,41,59,0.9) 0%, rgba(15,23,42,0.95) 100%);
    border-top: 1px solid rgba(255,255,255,0.1);
    border-bottom: 3px solid rgba(0,0,0,0.5);
    box-shadow: inset 0 2px 10px rgba(0,0,0,0.5), 0 10px 20px rgba(0,0,0,0.3);
    position: relative;
}
.clock-box::after {
    content: ''; position: absolute; top: 50%; left: 0; width: 100%; height: 2px;
    background: rgba(0,0,0,0.4); z-index: 10;
}

/* Glassmorphism Input */
.glass-input { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.2); color: white; }
.glass-input:focus { background: rgba(255,255,255,0.1); border-color: #3b82f6; outline: none; }
.glass-input::placeholder { color: rgba(255,255,255,0.4); }

/* Hide Spinner in Number Input */
input[type="number"]::-webkit-outer-spin-button, input[type="number"]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
input[type="number"] { -moz-appearance: textfield; }

/* Transitions & Animations */
.animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

.animate-fade-in-up { animation: fadeInUp 0.5s ease-out forwards; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

.animate-bounce-in { animation: bounceIn 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; transform: scale(0); }
@keyframes bounceIn { 
    0% { transform: scale(0); opacity: 0; }
    50% { transform: scale(1.1); opacity: 1; }
    100% { transform: scale(1); opacity: 1; }
}
</style>
