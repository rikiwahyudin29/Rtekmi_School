<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    setting: Object,
    sekolah: Object,
    asset_url: String
});

const form = ref({ nisn: '' });
const isLoading = ref(false);
const error = ref('');
const step = ref(1); // 1: Login, 2: Countdown/Wait, 3: Envelope, 4: Result

const siswaData = ref(null);
const hasilKelulusan = ref(null);

const countdownTimer = ref(null);
const timeRemaining = ref(0);

const calculateTimeRemaining = () => {
    if (!props.setting?.tgl_pengumuman) return 0;
    const targetDate = new Date(props.setting.tgl_pengumuman).getTime();
    const now = new Date().getTime();
    return Math.max(0, targetDate - now);
};

const formatTime = (ms) => {
    const days = Math.floor(ms / (1000 * 60 * 60 * 24));
    const hours = Math.floor((ms % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((ms % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((ms % (1000 * 60)) / 1000);
    return { days, hours, minutes, seconds };
};

const formattedCountdown = computed(() => formatTime(timeRemaining.value));

onMounted(() => {
    timeRemaining.value = calculateTimeRemaining();
    if (timeRemaining.value > 0) {
        countdownTimer.value = setInterval(() => {
            timeRemaining.value = calculateTimeRemaining();
            if (timeRemaining.value <= 0) {
                clearInterval(countdownTimer.value);
            }
        }, 1000);
    }
});

onUnmounted(() => {
    if (countdownTimer.value) clearInterval(countdownTimer.value);
});

// Auto-capitalize NISN while typing
const handleInput = (e) => {
    form.value.nisn = e.target.value.toUpperCase();
};

const handleLogin = async () => {
    error.value = '';
    
    if (!form.value.nisn) {
        error.value = 'NISN tidak boleh kosong!';
        return;
    }

    isLoading.value = true;
    try {
        const response = await axios.post(route('kelulusan.login'), { nisn: form.value.nisn });
        if (response.data.status === 'success') {
            siswaData.value = response.data.data;
            if (timeRemaining.value > 0) {
                step.value = 2; // Countdown waiting
            } else {
                startAnimation();
            }
        } else {
            error.value = response.data.message || 'NISN tidak terdaftar sebagai Siswa Kelas XII.';
        }
    } catch (err) {
        error.value = 'Terjadi kesalahan koneksi. Silakan coba lagi.';
    } finally {
        isLoading.value = false;
    }
};

const envelopeState = ref('closed'); // closed, opening, opened

const downloadState = ref({
    isDownloading: false,
    status: '', // 'requesting', 'antri', 'proses', 'ready'
    sisaAntrian: 0,
    tipe: ''
});

const pollingInterval = ref(null);

const handleDownload = async (tipe) => {
    downloadState.value = {
        isDownloading: true,
        status: 'requesting',
        sisaAntrian: 0,
        tipe: tipe
    };

    try {
        const response = await axios.get(route('kelulusan.antrian.request'), { params: { tipe } });
        if (response.data.status === 'success') {
            const idAntrian = response.data.id_antrian;
            startPolling(idAntrian, tipe);
        } else {
            alert(response.data.msg || 'Terjadi kesalahan sistem.');
            downloadState.value.isDownloading = false;
        }
    } catch (e) {
        alert('Gagal menghubungi server.');
        downloadState.value.isDownloading = false;
    }
};

const startPolling = (idAntrian, tipe) => {
    pollingInterval.value = setInterval(async () => {
        try {
            const response = await axios.get(route('kelulusan.antrian.cek', idAntrian));
            
            downloadState.value.status = response.data.status;
            downloadState.value.sisaAntrian = response.data.sisa || 0;

            if (response.data.status === 'ready') {
                clearInterval(pollingInterval.value);
                
                // Mulai download file beneran
                window.location.href = route('kelulusan.download') + '?tipe=' + tipe;
                
                setTimeout(() => {
                    downloadState.value.isDownloading = false;
                }, 2000);
            }
        } catch (e) {
            clearInterval(pollingInterval.value);
            alert('Koneksi terputus saat menunggu antrean.');
            downloadState.value.isDownloading = false;
        }
    }, 2000); // Poll every 2 seconds
};

const startAnimation = () => {
    step.value = 3; // Envelope animation
    setTimeout(() => {
        envelopeState.value = 'opening';
        
        setTimeout(async () => {
            try {
                const response = await axios.get(route('kelulusan.hasil'));
                if (response.data.status === 'success') {
                    hasilKelulusan.value = response.data;
                    envelopeState.value = 'opened';
                    setTimeout(() => {
                        step.value = 4; // Result
                    }, 1000);
                } else {
                    error.value = response.data.message;
                    step.value = 1;
                }
            } catch (err) {
                error.value = 'Gagal mengambil hasil.';
                step.value = 1;
            }
        }, 1500); // Envelope open duration
    }, 500);
};

const bgImage = computed(() => {
    return `url('${props.asset_url}images/bg-kelulusan.jpg')`; // placeholder fallback if needed
});
</script>

<template>
    <Head title="Portal Kelulusan" />

    <div class="min-h-screen bg-gray-900 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden font-sans">
        <!-- Background with glass effect -->
        <div class="absolute inset-0 z-0">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-900/80 to-purple-900/80 mix-blend-multiply z-10"></div>
            <div class="absolute inset-0 bg-gray-900/50 z-10 backdrop-blur-sm"></div>
            <!-- Animated shapes -->
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-blue-500/30 rounded-full mix-blend-screen filter blur-3xl opacity-50 animate-blob"></div>
            <div class="absolute top-0 right-1/4 w-96 h-96 bg-purple-500/30 rounded-full mix-blend-screen filter blur-3xl opacity-50 animate-blob animation-delay-2000"></div>
            <div class="absolute -bottom-32 left-1/2 w-96 h-96 bg-pink-500/30 rounded-full mix-blend-screen filter blur-3xl opacity-50 animate-blob animation-delay-4000"></div>
        </div>

        <div class="max-w-xl w-full z-20 transition-all duration-500 transform">
            <!-- Header -->
            <div class="text-center mb-10">
                <div class="inline-block p-4 rounded-full bg-white/10 backdrop-blur-md shadow-xl border border-white/20 mb-4 animate-bounce-slow">
                    <img v-if="sekolah?.logo" :src="`${asset_url}uploads/identitas/${sekolah.logo}`" alt="Logo" class="h-20 w-20 object-contain drop-shadow-xl" onerror="this.src='/images/logo.png'">
                    <i v-else class="fas fa-graduation-cap text-5xl text-white"></i>
                </div>
                <h1 class="text-4xl font-extrabold text-white tracking-tight drop-shadow-lg mb-2">
                    Pengumuman Kelulusan
                </h1>
                <h2 class="text-xl text-blue-200 font-medium drop-shadow-md">
                    {{ sekolah?.nama_sekolah || 'Nama Sekolah' }}
                </h2>
                <p class="text-sm text-gray-300 mt-2">Tahun Ajaran {{ new Date().getFullYear() - 1 }}/{{ new Date().getFullYear() }}</p>
            </div>

            <!-- STEP 1: Login Form -->
            <div v-if="step === 1" class="bg-white/10 backdrop-blur-xl rounded-3xl shadow-2xl p-8 border border-white/20 transform transition-all hover:scale-[1.02]">
                <h3 class="text-2xl font-bold text-white mb-6 text-center">Cek Hasil Kelulusan</h3>
                
                <div v-if="error" class="mb-6 p-4 rounded-xl bg-red-500/20 border border-red-500/50 text-red-200 text-sm flex items-start gap-3 backdrop-blur-md animate-shake">
                    <i class="fas fa-exclamation-circle mt-0.5"></i>
                    <span>{{ error }}</span>
                </div>

                <form @submit.prevent="handleLogin" class="space-y-6">
                    <div>
                        <label for="nisn" class="block text-sm font-medium text-blue-100 mb-2">Nomor Induk Siswa Nasional (NISN)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-id-card text-blue-300"></i>
                            </div>
                            <input 
                                id="nisn" 
                                v-model="form.nisn" 
                                @input="handleInput"
                                type="text" 
                                required
                                class="block w-full pl-12 pr-4 py-4 border border-white/20 rounded-2xl bg-black/20 text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all backdrop-blur-sm text-lg font-bold tracking-widest uppercase" 
                                placeholder="MASUKKAN NISN"
                                :disabled="isLoading"
                            >
                        </div>
                    </div>

                    <button 
                        type="submit" 
                        :disabled="isLoading"
                        class="w-full flex justify-center items-center py-4 px-4 border border-transparent rounded-2xl shadow-lg text-lg font-bold text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 focus:ring-offset-gray-900 transition-all transform hover:-translate-y-1"
                    >
                        <i v-if="isLoading" class="fas fa-spinner fa-spin mr-3"></i>
                        <i v-else class="fas fa-search mr-3"></i>
                        {{ isLoading ? 'Memeriksa Data...' : 'Cek Kelulusan' }}
                    </button>
                </form>
            </div>

            <!-- STEP 2: Countdown / Waiting -->
            <div v-else-if="step === 2" class="bg-white/10 backdrop-blur-xl rounded-3xl shadow-2xl p-8 border border-white/20 text-center">
                <div class="inline-block p-4 rounded-full bg-blue-500/20 text-blue-300 mb-6 border border-blue-500/30">
                    <i class="fas fa-clock text-4xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-2">Waktu Pengumuman Belum Tiba</h3>
                <p class="text-blue-200 mb-8">Halo <b class="text-white">{{ siswaData?.nama_lengkap }}</b>, pengumuman kelulusan akan dibuka pada:</p>
                
                <div class="text-xl text-white font-bold mb-8 bg-black/30 py-3 rounded-xl border border-white/10">
                    {{ new Date(props.setting?.tgl_pengumuman).toLocaleString('id-ID', { dateStyle: 'full', timeStyle: 'short' }) }}
                </div>

                <div class="grid grid-cols-4 gap-4 text-center">
                    <div class="bg-black/40 rounded-2xl p-4 border border-white/10 backdrop-blur-md">
                        <div class="text-3xl font-black text-white">{{ formattedCountdown.days }}</div>
                        <div class="text-xs text-blue-200 uppercase tracking-wider mt-1">Hari</div>
                    </div>
                    <div class="bg-black/40 rounded-2xl p-4 border border-white/10 backdrop-blur-md">
                        <div class="text-3xl font-black text-white">{{ formattedCountdown.hours }}</div>
                        <div class="text-xs text-blue-200 uppercase tracking-wider mt-1">Jam</div>
                    </div>
                    <div class="bg-black/40 rounded-2xl p-4 border border-white/10 backdrop-blur-md">
                        <div class="text-3xl font-black text-white">{{ formattedCountdown.minutes }}</div>
                        <div class="text-xs text-blue-200 uppercase tracking-wider mt-1">Menit</div>
                    </div>
                    <div class="bg-black/40 rounded-2xl p-4 border border-white/10 backdrop-blur-md">
                        <div class="text-3xl font-black text-white">{{ formattedCountdown.seconds }}</div>
                        <div class="text-xs text-blue-200 uppercase tracking-wider mt-1">Detik</div>
                    </div>
                </div>

                <button @click="step = 1" class="mt-8 text-blue-300 hover:text-white transition-colors underline text-sm">
                    Kembali ke Login
                </button>
            </div>

            <!-- STEP 3: Envelope Animation -->
            <div v-else-if="step === 3" class="flex flex-col items-center justify-center py-10">
                <h3 class="text-3xl font-bold text-white mb-12 drop-shadow-md animate-pulse">Membuka Amplop...</h3>
                
                <!-- CSS Envelope Animation -->
                <div class="envelope-wrapper" :class="{ 'open': envelopeState === 'opening' || envelopeState === 'opened' }">
                    <div class="envelope">
                        <div class="letter">
                            <div class="text-center font-bold text-gray-800 pt-4">SURAT KELULUSAN</div>
                            <div class="w-full h-1 bg-gray-200 mt-2"></div>
                            <div class="w-3/4 h-1 bg-gray-200 mt-2"></div>
                            <div class="w-5/6 h-1 bg-gray-200 mt-2"></div>
                            <div class="absolute bottom-4 right-4">
                                <i class="fas fa-stamp text-red-500/50 text-2xl"></i>
                            </div>
                        </div>
                        <div class="front"></div>
                        <div class="back"></div>
                        <div class="top-flap"></div>
                    </div>
                </div>
            </div>

            <!-- STEP 4: Result -->
            <div v-else-if="step === 4" class="transition-all duration-700 transform scale-100 opacity-100">
                <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
                    <div class="p-8 text-center" :class="{
                        'bg-gradient-to-br from-green-500 to-emerald-700': hasilKelulusan?.status_lulus === 'Lulus',
                        'bg-gradient-to-br from-amber-500 to-orange-600': hasilKelulusan?.status_lulus === 'Lulus Bersyarat',
                        'bg-gradient-to-br from-red-500 to-rose-700': hasilKelulusan?.status_lulus === 'Tidak Lulus',
                        'bg-gradient-to-br from-gray-500 to-slate-700': hasilKelulusan?.status_lulus === 'Pending'
                    }">
                        <div class="w-24 h-24 rounded-full bg-white/20 backdrop-blur-md mx-auto flex items-center justify-center mb-4 border-4 border-white/30 shadow-lg">
                            <i v-if="hasilKelulusan?.status_lulus === 'Lulus'" class="fas fa-check text-5xl text-white drop-shadow-md"></i>
                            <i v-else-if="hasilKelulusan?.status_lulus === 'Lulus Bersyarat'" class="fas fa-exclamation text-5xl text-white drop-shadow-md"></i>
                            <i v-else-if="hasilKelulusan?.status_lulus === 'Tidak Lulus'" class="fas fa-times text-5xl text-white drop-shadow-md"></i>
                            <i v-else class="fas fa-clock text-5xl text-white drop-shadow-md"></i>
                        </div>
                        
                        <h2 class="text-4xl font-black text-white tracking-wider mb-2 drop-shadow-md">
                            {{ hasilKelulusan?.status_lulus === 'Lulus' ? 'SELAMAT!' : 
                               hasilKelulusan?.status_lulus === 'Lulus Bersyarat' ? 'LULUS BERSYARAT' : 
                               hasilKelulusan?.status_lulus === 'Tidak Lulus' ? 'MOHON MAAF' : 'STATUS PENDING' }}
                        </h2>
                        <p class="text-white/90 text-lg font-medium">Anda Dinyatakan <b>{{ hasilKelulusan?.status_lulus?.toUpperCase() }}</b></p>
                    </div>

                    <div class="p-8 bg-gray-50">
                        <div class="mb-8 p-6 bg-white rounded-2xl shadow-sm border border-gray-100 text-left">
                            <table class="w-full text-sm">
                                <tbody>
                                    <tr>
                                        <td class="py-2 text-gray-500 w-1/3">Nama Lengkap</td>
                                        <td class="py-2 font-bold text-gray-900">: {{ hasilKelulusan?.siswa?.nama_lengkap }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 text-gray-500 w-1/3">NISN</td>
                                        <td class="py-2 font-bold text-gray-900">: {{ hasilKelulusan?.siswa?.nisn }}</td>
                                    </tr>
                                    <tr v-if="hasilKelulusan?.catatan">
                                        <td class="py-2 text-gray-500 w-1/3 align-top">Catatan</td>
                                        <td class="py-2 font-bold text-orange-600">: {{ hasilKelulusan.catatan }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="hasilKelulusan?.status_lulus === 'Lulus' || hasilKelulusan?.status_lulus === 'Lulus Bersyarat'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <button @click="handleDownload('skl')" class="flex items-center justify-center p-4 bg-emerald-100 text-emerald-700 hover:bg-emerald-200 rounded-xl font-bold transition-colors border border-emerald-200 group w-full">
                                <div class="w-10 h-10 rounded-full bg-emerald-200 flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                                    <i class="fas fa-graduation-cap text-lg"></i>
                                </div>
                                Unduh SKL
                            </button>
                            <button @click="handleDownload('transkrip')" class="flex items-center justify-center p-4 bg-blue-100 text-blue-700 hover:bg-blue-200 rounded-xl font-bold transition-colors border border-blue-200 group w-full">
                                <div class="w-10 h-10 rounded-full bg-blue-200 flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                                    <i class="fas fa-file-invoice text-lg"></i>
                                </div>
                                Unduh Transkrip
                            </button>
                        </div>
                        <div v-else class="p-4 bg-yellow-50 text-yellow-700 border border-yellow-200 rounded-xl text-center text-sm font-medium">
                            Dokumen kelulusan tidak dapat diunduh karena status kelulusan Anda.
                        </div>

                        <div class="mt-8 text-center">
                            <button @click="step = 1; form.nisn = ''" class="text-gray-500 hover:text-gray-800 font-medium underline text-sm transition-colors">
                                Keluar / Cek Siswa Lain
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Download Modal Queue -->
        <div v-if="downloadState.isDownloading" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity">
            <div class="bg-white rounded-3xl shadow-2xl p-8 max-w-sm w-full text-center border border-white/20 transform transition-all scale-100 opacity-100">
                
                <div v-if="downloadState.status === 'antri'" class="text-blue-500 mb-4">
                    <i class="fas fa-users text-5xl animate-bounce"></i>
                </div>
                <div v-else-if="downloadState.status === 'proses'" class="text-emerald-500 mb-4">
                    <i class="fas fa-cog fa-spin text-5xl"></i>
                </div>
                <div v-else-if="downloadState.status === 'ready'" class="text-green-500 mb-4">
                    <i class="fas fa-check-circle text-5xl"></i>
                </div>
                <div v-else class="text-gray-400 mb-4">
                    <i class="fas fa-circle-notch fa-spin text-5xl"></i>
                </div>

                <h3 class="text-xl font-bold text-gray-900 mb-2">
                    {{ 
                        downloadState.status === 'antri' ? 'Masuk Antrean' : 
                        downloadState.status === 'proses' ? 'Merender PDF...' :
                        downloadState.status === 'ready' ? 'Siap Diunduh!' : 'Menghubungkan...' 
                    }}
                </h3>
                
                <p class="text-sm text-gray-600 mb-4 font-medium">
                    <span v-if="downloadState.status === 'antri'">
                        Harap tunggu. Ada <b class="text-blue-600">{{ downloadState.sisaAntrian }}</b> orang di antrean depan Anda.
                    </span>
                    <span v-else-if="downloadState.status === 'proses'">
                        Server sedang menggambar dokumen PDF Anda. Mohon jangan tutup halaman ini.
                    </span>
                    <span v-else-if="downloadState.status === 'ready'">
                        Dokumen sedang diunduh ke perangkat Anda.
                    </span>
                    <span v-else>
                        Meminta tiket antrean ke server...
                    </span>
                </p>

                <div class="w-full bg-gray-200 rounded-full h-2 mb-4 overflow-hidden relative">
                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-500 absolute left-0 top-0" :style="`width: ${downloadState.status === 'ready' ? '100%' : downloadState.status === 'proses' ? '75%' : '25%'}`"></div>
                </div>

                <button v-if="downloadState.status === 'antri' || downloadState.status === 'requesting'" @click="downloadState.isDownloading = false; clearInterval(pollingInterval)" class="text-xs text-red-500 hover:text-red-700 underline font-medium">
                    Batalkan
                </button>
            </div>
        </div>

    </div>
</template>

<style scoped>
/* Animations */
@keyframes blob {
  0% { transform: translate(0px, 0px) scale(1); }
  33% { transform: translate(30px, -50px) scale(1.1); }
  66% { transform: translate(-20px, 20px) scale(0.9); }
  100% { transform: translate(0px, 0px) scale(1); }
}
.animate-blob {
  animation: blob 7s infinite;
}
.animation-delay-2000 {
  animation-delay: 2s;
}
.animation-delay-4000 {
  animation-delay: 4s;
}
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-5px); }
  50% { transform: translateX(5px); }
  75% { transform: translateX(-5px); }
}
.animate-shake {
  animation: shake 0.4s ease-in-out;
}
.animate-bounce-slow {
    animation: bounce 3s infinite ease-in-out;
}

/* Envelope Animation */
.envelope-wrapper {
    position: relative;
    width: 300px;
    height: 200px;
    margin: 50px auto;
    perspective: 1000px;
}

.envelope {
    position: absolute;
    width: 100%;
    height: 100%;
    background-color: #f3f4f6;
    border-radius: 8px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.5);
    transition: transform 0.5s;
    transform-style: preserve-3d;
}

.envelope .front {
    position: absolute;
    width: 0;
    height: 0;
    border-right: 150px solid #e5e7eb;
    border-left: 150px solid #e5e7eb;
    border-bottom: 100px solid #d1d5db;
    border-top: 100px solid transparent;
    z-index: 3;
    border-radius: 0 0 8px 8px;
}

.envelope .back {
    position: absolute;
    width: 100%;
    height: 100%;
    background-color: #f3f4f6;
    border-radius: 8px;
    z-index: 1;
}

.envelope .top-flap {
    position: absolute;
    width: 0;
    height: 0;
    border-right: 150px solid transparent;
    border-left: 150px solid transparent;
    border-top: 100px solid #cbd5e1;
    z-index: 4;
    transform-origin: top;
    transition: transform 0.8s ease-in-out;
}

.envelope .letter {
    position: absolute;
    width: 260px;
    height: 160px;
    background-color: white;
    top: 20px;
    left: 20px;
    border-radius: 4px;
    z-index: 2;
    transition: transform 0.8s ease-in-out, top 0.8s ease-in-out;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.envelope-wrapper.open .top-flap {
    transform: rotateX(180deg);
    z-index: 1;
}

.envelope-wrapper.open .letter {
    transform: translateY(-80px);
    z-index: 3;
}
</style>
