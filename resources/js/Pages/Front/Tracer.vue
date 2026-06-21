<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    web: Object,
    pertanyaan: Array
});

const step = ref(1); // 1 = Cek NISN, 2 = Isi Form Kuesioner, 3 = Sukses

// Step 1: Validasi NISN
const nisnForm = ref({
    nisn: '',
    processing: false,
    error: ''
});

const alumniData = ref(null);

const cekNisn = async () => {
    if(!nisnForm.value.nisn) return;
    nisnForm.value.processing = true;
    nisnForm.value.error = '';

    try {
        const response = await axios.post(route('tracer.cek_nisn'), { nisn: nisnForm.value.nisn });
        if(response.data.status === 'success') {
            alumniData.value = response.data;
            step.value = 2;
        }
    } catch (error) {
        nisnForm.value.error = error.response?.data?.message || 'Terjadi kesalahan jaringan.';
    } finally {
        nisnForm.value.processing = false;
    }
};

// Step 2: Form Kuesioner
const form = useForm({
    nisn: '',
    status_kegiatan: 'Bekerja',
    nama_instansi: '',
    jawaban: {}
});

// Inisialisasi jawaban array untuk checkbox
props.pertanyaan.forEach(p => {
    if(p.tipe === 'checkbox') {
        form.jawaban[p.id] = [];
    } else {
        form.jawaban[p.id] = '';
    }
});

const submitTracer = () => {
    form.nisn = nisnForm.value.nisn;
    form.post(route('tracer.simpan'), {
        preserveScroll: true,
        onSuccess: () => {
            step.value = 3;
        }
    });
};
</script>

<template>
    <Head title="Tracer Study & Penelusuran Alumni" />
    
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex flex-col font-sans">
        
        <!-- Header Profil Sekolah -->
        <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-100 dark:border-gray-700 py-4">
            <div class="max-w-4xl mx-auto px-4 flex items-center gap-4">
                <img v-if="web && web.logo" :src="'/uploads/identitas/' + web.logo" alt="Logo" class="w-12 h-12 object-contain">
                <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl" v-else>
                    <i class="fas fa-school"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white leading-tight">
                        Tracer Study Alumni
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ web ? web.nama_sekolah : 'Portal Alumni' }}
                    </p>
                </div>
            </div>
        </header>

        <main class="flex-grow flex flex-col items-center justify-center py-10 px-4">
            <div class="max-w-3xl w-full bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                
                <!-- Banner Image / Accent -->
                <div class="h-32 bg-gradient-to-r from-emerald-500 to-teal-600 relative overflow-hidden">
                    <div class="absolute inset-0 opacity-20" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
                </div>

                <div class="px-6 py-8 md:p-10 relative -mt-10 bg-white dark:bg-gray-800 rounded-t-3xl">
                    
                    <!-- Progress Bar -->
                    <div class="flex items-center justify-center mb-8 gap-2" v-if="step < 3">
                        <div class="h-2 w-16 rounded-full" :class="step >= 1 ? 'bg-emerald-500' : 'bg-gray-200 dark:bg-gray-700'"></div>
                        <div class="h-2 w-16 rounded-full" :class="step >= 2 ? 'bg-emerald-500' : 'bg-gray-200 dark:bg-gray-700'"></div>
                    </div>

                    <!-- STEP 1: CEK NISN -->
                    <div v-if="step === 1" class="animate-fade-in-up text-center max-w-md mx-auto">
                        <div class="w-20 h-20 mx-auto bg-emerald-50 dark:bg-emerald-900/30 rounded-full flex items-center justify-center text-3xl text-emerald-500 mb-6 shadow-sm ring-4 ring-emerald-50/50 dark:ring-gray-800">
                            <i class="fas fa-search"></i>
                        </div>
                        <h2 class="text-2xl font-black text-gray-900 dark:text-white mb-2">Verifikasi Data Alumni</h2>
                        <p class="text-gray-500 dark:text-gray-400 mb-8 text-sm">
                            Silakan masukkan NISN (Nomor Induk Siswa Nasional) Anda untuk memulai pengisian Tracer Study.
                        </p>

                        <form @submit.prevent="cekNisn" class="space-y-4">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-id-card text-gray-400"></i>
                                </div>
                                <input type="text" v-model="nisnForm.nisn" class="block w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 text-gray-900 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700/50 dark:border-gray-600 dark:text-white text-lg tracking-wide font-medium placeholder-gray-400" placeholder="Masukkan NISN..." required>
                            </div>

                            <div v-if="nisnForm.error" class="text-sm text-red-500 font-medium bg-red-50 dark:bg-red-900/20 py-2 px-3 rounded-lg flex items-start gap-2 text-left">
                                <i class="fas fa-exclamation-circle mt-0.5"></i> <span>{{ nisnForm.error }}</span>
                            </div>
                            
                            <div v-if="$page.props.flash.error" class="text-sm text-red-500 font-medium bg-red-50 dark:bg-red-900/20 py-2 px-3 rounded-lg flex items-start gap-2 text-left">
                                <i class="fas fa-exclamation-circle mt-0.5"></i> <span>{{ $page.props.flash.error }}</span>
                            </div>

                            <button type="submit" :disabled="nisnForm.processing" class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-md transition-all hover:shadow-lg focus:ring-4 focus:ring-emerald-200 dark:focus:ring-emerald-900 disabled:opacity-70 flex justify-center items-center gap-2">
                                <i v-if="nisnForm.processing" class="fas fa-spinner fa-spin"></i>
                                <span>{{ nisnForm.processing ? 'Memverifikasi...' : 'Lanjutkan' }}</span>
                                <i v-if="!nisnForm.processing" class="fas fa-arrow-right"></i>
                            </button>
                        </form>
                    </div>

                    <!-- STEP 2: FORM KUESIONER -->
                    <div v-if="step === 2" class="animate-fade-in-up">
                        <div class="flex items-center gap-4 p-4 mb-8 bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl border border-emerald-100 dark:border-emerald-800/30">
                            <div class="w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-800 text-emerald-600 dark:text-emerald-300 flex items-center justify-center text-xl font-black">
                                <i class="fas fa-check"></i>
                            </div>
                            <div>
                                <div class="text-sm text-emerald-600/80 dark:text-emerald-400 font-bold uppercase tracking-wider mb-0.5">Halo, Alumni!</div>
                                <div class="font-black text-gray-900 dark:text-white text-lg">{{ alumniData?.nama_lengkap }}</div>
                                <div class="text-sm text-gray-500">Angkatan {{ alumniData?.tahun_angkatan }} &bull; NISN: {{ nisnForm.nisn }}</div>
                            </div>
                        </div>

                        <form @submit.prevent="submitTracer" class="space-y-8">
                            
                            <!-- Basic Info -->
                            <div class="space-y-5 border-b border-gray-100 dark:border-gray-700 pb-8">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-md bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs">1</span>
                                    Status Saat Ini
                                </h3>
                                
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Kegiatan Utama Setelah Lulus</label>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                        <label class="flex items-center p-3 border rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors" :class="form.status_kegiatan == 'Bekerja' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20' : 'border-gray-200 dark:border-gray-700'">
                                            <input type="radio" v-model="form.status_kegiatan" value="Bekerja" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                                            <span class="ml-3 font-bold text-sm dark:text-gray-200">Bekerja</span>
                                        </label>
                                        <label class="flex items-center p-3 border rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors" :class="form.status_kegiatan == 'Kuliah' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-200 dark:border-gray-700'">
                                            <input type="radio" v-model="form.status_kegiatan" value="Kuliah" class="w-4 h-4 text-indigo-600 focus:ring-indigo-500">
                                            <span class="ml-3 font-bold text-sm dark:text-gray-200">Kuliah</span>
                                        </label>
                                        <label class="flex items-center p-3 border rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors" :class="form.status_kegiatan == 'Wirausaha' ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20' : 'border-gray-200 dark:border-gray-700'">
                                            <input type="radio" v-model="form.status_kegiatan" value="Wirausaha" class="w-4 h-4 text-amber-600 focus:ring-amber-500">
                                            <span class="ml-3 font-bold text-sm dark:text-gray-200">Wirausaha</span>
                                        </label>
                                        <label class="flex items-center p-3 border rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors" :class="form.status_kegiatan == 'Belum Bekerja' ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-gray-200 dark:border-gray-700'">
                                            <input type="radio" v-model="form.status_kegiatan" value="Belum Bekerja" class="w-4 h-4 text-red-600 focus:ring-red-500">
                                            <span class="ml-3 font-bold text-sm dark:text-gray-200">Sedang Mencari Kerja</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="animate-fade-in" v-if="form.status_kegiatan !== 'Belum Bekerja'">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Perusahaan / Kampus / Usaha</label>
                                    <input type="text" v-model="form.nama_instansi" class="block w-full p-3 bg-gray-50 border border-gray-200 text-gray-900 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700/50 dark:border-gray-600 dark:text-white" :placeholder="'Masukkan nama ' + (form.status_kegiatan == 'Kuliah' ? 'Kampus' : (form.status_kegiatan == 'Bekerja' ? 'Perusahaan' : 'Usaha')) + '...'" required>
                                </div>
                            </div>

                            <!-- Dynamic Questions -->
                            <div class="space-y-6" v-if="pertanyaan && pertanyaan.length > 0">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2 mb-4">
                                    <span class="w-6 h-6 rounded-md bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs">2</span>
                                    Kuesioner Penelusuran
                                </h3>
                                
                                <div v-for="(p, index) in pertanyaan" :key="p.id" class="bg-gray-50 dark:bg-gray-700/30 p-5 rounded-2xl border border-gray-100 dark:border-gray-700">
                                    <label class="block text-base font-bold text-gray-900 dark:text-white mb-3">
                                        {{ index + 1 }}. {{ p.pertanyaan }}
                                        <span v-if="p.is_required" class="text-red-500 ml-1" title="Wajib Diisi">*</span>
                                    </label>
                                    
                                    <!-- Teks Singkat -->
                                    <input v-if="p.tipe === 'text'" type="text" v-model="form.jawaban[p.id]" :required="!!p.is_required" class="block w-full p-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white" placeholder="Ketik jawaban singkat...">
                                    
                                    <!-- Teks Panjang -->
                                    <textarea v-if="p.tipe === 'textarea'" rows="3" v-model="form.jawaban[p.id]" :required="!!p.is_required" class="block w-full p-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white" placeholder="Ketik jawaban rinci..."></textarea>
                                    
                                    <!-- Pilihan Ganda -->
                                    <div v-if="p.tipe === 'radio'" class="space-y-2">
                                        <label v-for="opt in (p.opsi_jawaban ? p.opsi_jawaban.split(',') : [])" class="flex items-center p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer transition-colors">
                                            <input type="radio" v-model="form.jawaban[p.id]" :value="opt.trim()" :required="!!p.is_required" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500 bg-white border-gray-300">
                                            <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">{{ opt.trim() }}</span>
                                        </label>
                                    </div>

                                    <!-- Checkbox -->
                                    <div v-if="p.tipe === 'checkbox'" class="space-y-2">
                                        <label v-for="opt in (p.opsi_jawaban ? p.opsi_jawaban.split(',') : [])" class="flex items-center p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer transition-colors">
                                            <input type="checkbox" v-model="form.jawaban[p.id]" :value="opt.trim()" class="w-4 h-4 text-emerald-600 bg-white border-gray-300 rounded focus:ring-emerald-500">
                                            <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">{{ opt.trim() }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-6 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                                <button type="button" @click="step = 1" class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white rounded-xl transition-colors">
                                    <i class="fas fa-arrow-left mr-1"></i> Batal
                                </button>
                                <button type="submit" :disabled="form.processing" class="py-3 px-8 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-md transition-all hover:shadow-lg focus:ring-4 focus:ring-emerald-200 dark:focus:ring-emerald-900 disabled:opacity-70 flex justify-center items-center gap-2">
                                    <i v-if="form.processing" class="fas fa-spinner fa-spin"></i>
                                    <span>{{ form.processing ? 'Menyimpan...' : 'Kirim Laporan' }}</span>
                                    <i v-if="!form.processing" class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- STEP 3: SUKSES -->
                    <div v-if="step === 3" class="animate-fade-in-up text-center py-10">
                        <div class="w-24 h-24 mx-auto bg-emerald-100 text-emerald-500 rounded-full flex items-center justify-center text-5xl mb-6 shadow-lg shadow-emerald-100 dark:shadow-none dark:bg-emerald-900/40">
                            <i class="fas fa-check-circle animate-bounce"></i>
                        </div>
                        <h2 class="text-3xl font-black text-gray-900 dark:text-white mb-4">Terima Kasih, {{ alumniData?.nama_lengkap }}!</h2>
                        <p class="text-gray-500 dark:text-gray-400 mb-8 max-w-md mx-auto leading-relaxed">
                            Data Tracer Study Anda berhasil disimpan ke dalam sistem kami. Kontribusi Anda sangat berarti untuk kemajuan dan evaluasi sekolah kita ke depannya.
                        </p>
                        <a href="/" class="inline-block py-3 px-8 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-md transition-colors">
                            Kembali ke Beranda
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="mt-8 text-center text-sm text-gray-400 dark:text-gray-500">
                &copy; {{ new Date().getFullYear() }} {{ web ? web.nama_sekolah : 'Portal Alumni' }}. All rights reserved.
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
