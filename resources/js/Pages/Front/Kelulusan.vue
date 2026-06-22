<script setup>
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    web: Object,
    setting: Object
});

const step = ref(1); // 1 = Cek NISN, 2 = Loading Animasi, 3 = Hasil Kelulusan

// Step 1: Validasi NISN
const nisnForm = ref({
    nisn: '',
    processing: false,
    error: ''
});

const siswaData = ref(null);
const hasilData = ref(null);

const cekNisn = async () => {
    if(!nisnForm.value.nisn) return;
    nisnForm.value.processing = true;
    nisnForm.value.error = '';

    try {
        const response = await axios.post(route('kelulusan.cek'), { nisn: nisnForm.value.nisn });
        if(response.data.status === 'success') {
            siswaData.value = response.data.data;
            step.value = 2; // Pindah ke animasi loading
            fetchHasilKelulusan();
        }
    } catch (error) {
        nisnForm.value.error = error.response?.data?.message || 'Terjadi kesalahan jaringan.';
    } finally {
        nisnForm.value.processing = false;
    }
};

const fetchHasilKelulusan = () => {
    // Beri efek jeda dramatis 2 detik sebelum menampilkan hasil
    setTimeout(async () => {
        try {
            const res = await axios.post(route('kelulusan.hasil'));
            if(res.data.status === 'success') {
                hasilData.value = res.data;
                step.value = 3;
            }
        } catch (error) {
            step.value = 1;
            nisnForm.value.error = error.response?.data?.message || 'Gagal mengambil hasil kelulusan.';
        }
    }, 2000);
};

const downloadDokumen = (tipe) => {
    // Buka tab baru untuk download agar tidak mengganggu state aplikasi
    window.open(route('kelulusan.download') + '?tipe=' + tipe, '_blank');
};
</script>

<template>
    <Head title="Pengumuman Kelulusan" />
    
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex flex-col font-sans">
        
        <!-- Header Profil Sekolah -->
        <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-100 dark:border-gray-700 py-4">
            <div class="max-w-4xl mx-auto px-4 flex items-center gap-4">
                <img v-if="web && web.logo" :src="'/uploads/identitas/' + web.logo" alt="Logo" class="w-12 h-12 object-contain">
                <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xl" v-else>
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white leading-tight">
                        Pengumuman Kelulusan
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ web ? web.nama_sekolah : 'Portal Akademik' }}
                    </p>
                </div>
            </div>
        </header>

        <main class="flex-grow flex flex-col items-center justify-center py-10 px-4">
            <div class="max-w-2xl w-full bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                
                <!-- Banner Image / Accent -->
                <div class="h-32 bg-gradient-to-r from-blue-600 to-indigo-700 relative overflow-hidden">
                    <div class="absolute inset-0 opacity-20" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
                </div>

                <div class="px-6 py-8 md:p-10 relative -mt-10 bg-white dark:bg-gray-800 rounded-t-3xl min-h-[400px] flex flex-col justify-center">
                    
                    <!-- STEP 1: CEK NISN -->
                    <div v-if="step === 1" class="animate-fade-in-up text-center max-w-md mx-auto w-full">
                        <div class="w-20 h-20 mx-auto bg-blue-50 dark:bg-blue-900/30 rounded-full flex items-center justify-center text-3xl text-blue-600 mb-6 shadow-sm ring-4 ring-blue-50/50 dark:ring-gray-800">
                            <i class="fas fa-search"></i>
                        </div>
                        <h2 class="text-2xl font-black text-gray-900 dark:text-white mb-2">Cek Status Kelulusan</h2>
                        <p class="text-gray-500 dark:text-gray-400 mb-8 text-sm">
                            Silakan masukkan NISN Anda untuk melihat status kelulusan tahun ajaran ini.
                        </p>

                        <form @submit.prevent="cekNisn" class="space-y-4">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-id-card text-gray-400"></i>
                                </div>
                                <input type="text" v-model="nisnForm.nisn" class="block w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 text-gray-900 rounded-xl focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700/50 dark:border-gray-600 dark:text-white text-lg tracking-wide font-medium placeholder-gray-400" placeholder="Masukkan NISN..." required>
                            </div>

                            <div v-if="nisnForm.error" class="text-sm text-red-500 font-medium bg-red-50 dark:bg-red-900/20 py-2 px-3 rounded-lg flex items-start gap-2 text-left">
                                <i class="fas fa-exclamation-circle mt-0.5"></i> <span>{{ nisnForm.error }}</span>
                            </div>

                            <button type="submit" :disabled="nisnForm.processing" class="w-full py-3.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-md transition-all hover:shadow-lg focus:ring-4 focus:ring-blue-200 dark:focus:ring-blue-900 disabled:opacity-70 flex justify-center items-center gap-2">
                                <i v-if="nisnForm.processing" class="fas fa-spinner fa-spin"></i>
                                <span>{{ nisnForm.processing ? 'Mencari Data...' : 'Cek Kelulusan' }}</span>
                                <i v-if="!nisnForm.processing" class="fas fa-arrow-right"></i>
                            </button>
                        </form>
                    </div>

                    <!-- STEP 2: LOADING DRAMATIS -->
                    <div v-else-if="step === 2" class="animate-pulse text-center max-w-md mx-auto w-full">
                        <div class="w-24 h-24 mx-auto bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center text-4xl text-blue-500 mb-6 shadow-inner relative overflow-hidden">
                            <i class="fas fa-envelope-open-text absolute animate-bounce"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-2">Membuka Dokumen...</h2>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">
                            Harap tunggu, sistem sedang memverifikasi data kelulusan Saudara/i <strong>{{ siswaData?.nama_lengkap }}</strong>.
                        </p>
                    </div>

                    <!-- STEP 3: HASIL -->
                    <div v-else-if="step === 3" class="animate-fade-in-up text-center w-full">
                        
                        <!-- Header Hasil -->
                        <div class="mb-6">
                            <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Hasil Kelulusan Siswa</h3>
                            <div class="text-xl font-black text-gray-900 dark:text-white">{{ hasilData?.siswa?.nama_lengkap }}</div>
                            <div class="text-sm text-gray-500">NISN: {{ hasilData?.siswa?.nisn }}</div>
                        </div>

                        <!-- KOTAK STATUS -->
                        <div v-if="hasilData?.status_lulus === 'Lulus'" class="bg-emerald-50 border-2 border-emerald-500 rounded-2xl p-6 mb-8 dark:bg-emerald-900/20">
                            <div class="w-16 h-16 mx-auto bg-emerald-500 text-white rounded-full flex items-center justify-center text-3xl mb-4 shadow-lg shadow-emerald-200 dark:shadow-none animate-bounce">
                                <i class="fas fa-check"></i>
                            </div>
                            <h2 class="text-3xl font-black text-emerald-600 dark:text-emerald-400 mb-2">LULUS</h2>
                            <p class="text-emerald-800 dark:text-emerald-200 font-medium text-sm px-4">
                                Selamat! Anda dinyatakan Lulus dari {{ web?.nama_sekolah ?? 'sekolah ini' }}.
                            </p>
                        </div>

                        <div v-else-if="hasilData?.status_lulus === 'Tidak Lulus'" class="bg-red-50 border-2 border-red-500 rounded-2xl p-6 mb-8 dark:bg-red-900/20">
                            <div class="w-16 h-16 mx-auto bg-red-500 text-white rounded-full flex items-center justify-center text-3xl mb-4 shadow-lg shadow-red-200 dark:shadow-none">
                                <i class="fas fa-times"></i>
                            </div>
                            <h2 class="text-3xl font-black text-red-600 dark:text-red-400 mb-2">TIDAK LULUS</h2>
                            <p class="text-red-800 dark:text-red-200 font-medium text-sm px-4">
                                Mohon maaf, Anda dinyatakan Tidak Lulus. Silakan hubungi pihak sekolah untuk informasi lebih lanjut.
                            </p>
                        </div>

                        <div v-else class="bg-amber-50 border-2 border-amber-500 rounded-2xl p-6 mb-8 dark:bg-amber-900/20">
                            <div class="w-16 h-16 mx-auto bg-amber-500 text-white rounded-full flex items-center justify-center text-3xl mb-4 shadow-lg shadow-amber-200 dark:shadow-none">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h2 class="text-3xl font-black text-amber-600 dark:text-amber-400 mb-2">DITANGGUHKAN</h2>
                            <p class="text-amber-800 dark:text-amber-200 font-medium text-sm px-4">
                                Status kelulusan Anda saat ini masih tertunda (Pending). Silakan cek kembali beberapa saat lagi.
                            </p>
                        </div>

                        <!-- Catatan Opsional -->
                        <div v-if="hasilData?.catatan" class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-xl mb-8 text-left border border-gray-200 dark:border-gray-600">
                            <div class="text-xs font-bold text-gray-500 uppercase mb-1"><i class="fas fa-info-circle"></i> Catatan dari Sekolah:</div>
                            <p class="text-gray-700 dark:text-gray-300 text-sm italic">"{{ hasilData?.catatan }}"</p>
                        </div>

                        <!-- Tombol Aksi -->
                        <div v-if="hasilData?.status_lulus === 'Lulus'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <button @click="downloadDokumen('skl')" class="py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-md transition-all hover:shadow-lg focus:ring-4 focus:ring-emerald-200 flex justify-center items-center gap-2">
                                <i class="fas fa-file-pdf"></i> Download SKL
                            </button>
                            <button @click="downloadDokumen('transkrip')" class="py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-md transition-all hover:shadow-lg focus:ring-4 focus:ring-blue-200 flex justify-center items-center gap-2">
                                <i class="fas fa-file-alt"></i> Download Transkrip
                            </button>
                        </div>

                        <div class="mt-8">
                            <button @click="step = 1; nisnForm.nisn = ''" class="text-gray-500 hover:text-blue-600 dark:hover:text-blue-400 font-medium text-sm transition-colors">
                                <i class="fas fa-redo mr-1"></i> Cek Siswa Lain
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-8 text-center text-sm text-gray-400 dark:text-gray-500">
                &copy; {{ new Date().getFullYear() }} {{ web ? web.nama_sekolah : 'Portal Akademik' }}. All rights reserved.
            </div>
        </main>
    </div>
</template>

<style scoped>
.animate-fade-in-up {
    animation: fadeInUp 0.5s ease-out;
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
