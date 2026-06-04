<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    web: Object,
    jurusanList: Array
});

const form = useForm({
    nama_lengkap: '',
    nisn: '',
    nik: '',
    jk: 'L',
    tempat_lahir: '',
    tgl_lahir: '',
    agama: 'Islam',
    asal_sekolah: '',
    jurusan_minat: '',
    no_hp_siswa: '',
    alamat_jalan: '',
    rt_rw: '',
    desa_kelurahan: '',
    kecamatan: '',
    kabupaten: '',
    provinsi: '',
    nama_ayah: '',
    nama_ibu: '',
    no_hp_ortu: '',
    foto: null,
    berkas_kk: null,
    berkas_ijazah: null,
});

const currentStep = ref(1);
const totalSteps = 4;

const nextStep = () => {
    if (currentStep.value < totalSteps) currentStep.value++;
    window.scrollTo(0, 0);
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
    window.scrollTo(0, 0);
};

const submit = () => {
    form.post(route('public.ppdb.store'), {
        preserveScroll: true,
        onError: () => {
            // Jika error, kembali ke step pertama untuk melihat error jika ada
            // (Dalam produksi nyata bisa lebih canggih mendeteksi error ada di step mana)
            alert('Ada form yang belum lengkap atau salah format, mohon periksa kembali.');
        }
    });
};

const handleFile = (event, field) => {
    form[field] = event.target.files[0];
};

// Autocomplete Logic Asal Sekolah
const searchQuery = ref('');
const searchResults = ref([]);
const isSearching = ref(false);
const showDropdown = ref(false);

const searchSekolah = async () => {
    if (searchQuery.value.length < 3) {
        searchResults.value = [];
        showDropdown.value = false;
        return;
    }

    isSearching.value = true;
    try {
        const res = await fetch(`/api/sekolah/search?q=${encodeURIComponent(searchQuery.value)}`);
        const data = await res.json();
        searchResults.value = data;
        showDropdown.value = true;
    } catch (e) {
        console.error(e);
    } finally {
        isSearching.value = false;
    }
};

let searchTimeout;
const debounceSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        searchSekolah();
    }, 500);
};

const selectSekolah = (sekolah) => {
    form.asal_sekolah = sekolah.nama;
    searchQuery.value = sekolah.nama;
    showDropdown.value = false;
};
</script>

<template>
    <Head>
        <title>Pendaftaran PPDB - {{ web?.nama_sekolah || 'SMK' }}</title>
    </Head>

    <div class="min-h-screen bg-slate-50 font-sans flex flex-col">
        <!-- Header -->
        <header class="bg-white border-b border-slate-200 sticky top-0 z-50">
            <div class="max-w-4xl mx-auto px-4 h-20 flex items-center justify-between">
                <Link href="/" class="flex items-center gap-3">
                    <img v-if="web?.logo" :src="web.logo.includes('default') ? '/images/' + web.logo : '/uploads/identitas/' + web.logo" class="h-10">
                    <div class="flex flex-col">
                        <span class="font-black text-slate-800 leading-none">PORTAL PPDB</span>
                        <span class="text-[10px] font-bold text-emerald-600">{{ web?.nama_sekolah || 'SMK' }}</span>
                    </div>
                </Link>
                <Link href="/" class="text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors"><i class="fas fa-home"></i> Beranda</Link>
            </div>
        </header>

        <main class="flex-1 max-w-4xl w-full mx-auto px-4 py-8 md:py-12">
            
            <div class="text-center mb-10">
                <h1 class="text-3xl md:text-4xl font-black text-slate-800 mb-3">Formulir Pendaftaran Siswa Baru</h1>
                <p class="text-slate-500 text-sm md:text-base">Mohon lengkapi data di bawah ini sesuai dengan dokumen asli (KK/Ijazah).</p>
            </div>

            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="flex items-center justify-between relative">
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1 bg-slate-200 z-0 rounded-full"></div>
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-emerald-500 z-0 rounded-full transition-all duration-500" :style="`width: ${((currentStep-1)/(totalSteps-1))*100}%`"></div>
                    
                    <div v-for="step in totalSteps" :key="step" class="z-10 flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-colors duration-500 shadow-sm border-2"
                             :class="currentStep >= step ? 'bg-emerald-500 text-white border-emerald-500' : 'bg-white text-slate-400 border-slate-200'">
                            <i v-if="currentStep > step" class="fas fa-check"></i>
                            <span v-else>{{ step }}</span>
                        </div>
                        <span class="text-[10px] font-bold mt-2 uppercase tracking-wider hidden sm:block"
                              :class="currentStep >= step ? 'text-emerald-600' : 'text-slate-400'">
                            <template v-if="step === 1">Identitas</template>
                            <template v-if="step === 2">Alamat Lengkap</template>
                            <template v-if="step === 3">Asal & Ortu</template>
                            <template v-if="step === 4">Dokumen</template>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <form @submit.prevent="submit" class="p-6 md:p-10">

                    <!-- Step 1: Identitas Diri -->
                    <div v-show="currentStep === 1" class="space-y-6">
                        <h3 class="text-xl font-bold text-slate-800 border-b border-slate-100 pb-4 mb-6">1. Identitas Diri</h3>
                        
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Nama Lengkap Sesuai Ijazah <span class="text-red-500">*</span></label>
                            <input v-model="form.nama_lengkap" type="text" required class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 uppercase">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">NISN <span class="text-red-500">*</span></label>
                                <input v-model="form.nisn" type="text" required pattern="[0-9]{5,20}" title="Hanya angka, 5-20 digit" class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                                <p class="text-[10px] text-slate-500 mt-1">Nomor Induk Siswa Nasional</p>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">NIK (Sesuai KK) <span class="text-red-500">*</span></label>
                                <input v-model="form.nik" type="text" required pattern="[0-9]{16}" title="Harus 16 digit angka" class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Tempat Lahir <span class="text-red-500">*</span></label>
                                <input v-model="form.tempat_lahir" type="text" required class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Tanggal Lahir <span class="text-red-500">*</span></label>
                                <input v-model="form.tgl_lahir" type="date" required class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Jenis Kelamin <span class="text-red-500">*</span></label>
                                <select v-model="form.jk" required class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                                    <option value="L">Laki-Laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Agama <span class="text-red-500">*</span></label>
                                <select v-model="form.agama" required class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                                    <option value="Islam">Islam</option>
                                    <option value="Kristen">Kristen</option>
                                    <option value="Katolik">Katolik</option>
                                    <option value="Hindu">Hindu</option>
                                    <option value="Buddha">Buddha</option>
                                    <option value="Konghucu">Konghucu</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Alamat Lengkap -->
                    <div v-show="currentStep === 2" class="space-y-6">
                        <h3 class="text-xl font-bold text-slate-800 border-b border-slate-100 pb-4 mb-6">2. Alamat & Kontak</h3>
                        
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Jalan / Dusun <span class="text-red-500">*</span></label>
                            <input v-model="form.alamat_jalan" type="text" required placeholder="Jl. Raya Utama No. 12" class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-bold text-slate-700 mb-2">RT / RW <span class="text-red-500">*</span></label>
                                <input v-model="form.rt_rw" type="text" required placeholder="001/002" class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                            <div class="col-span-2 md:col-span-3">
                                <label class="block text-sm font-bold text-slate-700 mb-2">Desa / Kelurahan <span class="text-red-500">*</span></label>
                                <input v-model="form.desa_kelurahan" type="text" required class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Kecamatan <span class="text-red-500">*</span></label>
                                <input v-model="form.kecamatan" type="text" required class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Kabupaten / Kota <span class="text-red-500">*</span></label>
                                <input v-model="form.kabupaten" type="text" required class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Provinsi <span class="text-red-500">*</span></label>
                                <input v-model="form.provinsi" type="text" required class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">No. HP / WhatsApp Siswa <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">+62</span>
                                <input v-model="form.no_hp_siswa" type="text" required placeholder="81234567890" class="w-full pl-12 rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                            <p class="text-[10px] text-slate-500 mt-1">Pastikan nomor aktif untuk menerima notifikasi PPDB.</p>
                        </div>
                    </div>

                    <!-- Step 3: Asal Sekolah & Ortu -->
                    <div v-show="currentStep === 3" class="space-y-6">
                        <h3 class="text-xl font-bold text-slate-800 border-b border-slate-100 pb-4 mb-6">3. Data Orang Tua & Pendidikan</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 p-6 rounded-2xl border border-slate-100 relative z-20">
                            <div class="relative">
                                <label class="block text-sm font-bold text-slate-700 mb-2">Asal Sekolah (Ketik min 3 huruf) <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input v-model="searchQuery" @input="debounceSearch" type="text" required placeholder="Contoh: SMPN 1 SUBANG" class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 uppercase" autocomplete="off">
                                    <i v-if="isSearching" class="fas fa-spinner fa-spin absolute right-4 top-1/2 -translate-y-1/2 text-emerald-500"></i>
                                </div>
                                <!-- Dropdown Autocomplete -->
                                <div v-if="showDropdown && searchResults.length > 0" class="absolute w-full mt-2 bg-white border border-slate-200 rounded-xl shadow-xl max-h-60 overflow-y-auto z-50">
                                    <div v-for="sek in searchResults" :key="sek.id" @click="selectSekolah(sek)" class="p-3 hover:bg-emerald-50 border-b border-slate-50 cursor-pointer transition-colors">
                                        <div class="font-bold text-slate-800 text-sm">{{ sek.nama }}</div>
                                        <div class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">{{ sek.lokasi }}</div>
                                    </div>
                                </div>
                                <div v-if="showDropdown && searchResults.length === 0 && searchQuery.length >= 3" class="absolute w-full mt-2 bg-white border border-slate-200 rounded-xl shadow-xl z-50 p-4 text-center">
                                    <div class="text-sm text-slate-500 mb-2">Sekolah tidak ditemukan di database pusat.</div>
                                    <button type="button" @click="selectSekolah({nama: searchQuery.toUpperCase(), lokasi: 'Input Manual'})" class="text-xs font-bold bg-slate-100 hover:bg-slate-200 px-4 py-2 rounded-lg text-slate-700">
                                        Gunakan "{{ searchQuery.toUpperCase() }}"
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Pilihan Jurusan <span class="text-red-500">*</span></label>
                                <select v-model="form.jurusan_minat" required class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                                    <option value="" disabled>-- Pilih Jurusan --</option>
                                    <option v-for="jur in jurusanList" :key="jur.id" :value="jur.nama_jurusan">{{ jur.nama_jurusan }} ({{ jur.singkatan }})</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Nama Ayah Sesuai KK</label>
                                <input v-model="form.nama_ayah" type="text" class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Nama Ibu Sesuai KK <span class="text-red-500">*</span></label>
                                <input v-model="form.nama_ibu" type="text" required class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">No. HP Orang Tua / Wali <span class="text-red-500">*</span></label>
                            <input v-model="form.no_hp_ortu" type="text" required placeholder="081234567890" class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                    </div>

                    <!-- Step 4: Upload Dokumen -->
                    <div v-show="currentStep === 4" class="space-y-6">
                        <h3 class="text-xl font-bold text-slate-800 border-b border-slate-100 pb-4 mb-6">4. Upload Dokumen Persyaratan</h3>
                        <p class="text-sm text-amber-600 bg-amber-50 p-4 rounded-xl border border-amber-100 font-medium mb-6">
                            Siapkan file scan/foto dokumen Anda. Format yang diterima: JPG, PNG, PDF. Maksimal ukuran per file: 2MB untuk foto, 5MB untuk dokumen.
                        </p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                                <label class="block text-sm font-bold text-slate-700 mb-2">Pas Foto Resmi <span class="text-red-500">*</span></label>
                                <input type="file" required accept="image/*" @change="e => handleFile(e, 'foto')" class="w-full text-sm bg-white border border-slate-300 rounded-lg p-2">
                                <p class="text-[10px] text-slate-500 mt-1">Latar belakang merah/biru, berpakaian rapi.</p>
                            </div>
                            
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                                <label class="block text-sm font-bold text-slate-700 mb-2">Scan Kartu Keluarga (KK) <span class="text-red-500">*</span></label>
                                <input type="file" required accept=".jpg,.jpeg,.png,.pdf" @change="e => handleFile(e, 'berkas_kk')" class="w-full text-sm bg-white border border-slate-300 rounded-lg p-2">
                                <p class="text-[10px] text-slate-500 mt-1">Harus terbaca dengan jelas.</p>
                            </div>
                        </div>

                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 w-full md:w-1/2">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Scan Ijazah / SKL (Jika Ada)</label>
                            <input type="file" accept=".jpg,.jpeg,.png,.pdf" @change="e => handleFile(e, 'berkas_ijazah')" class="w-full text-sm bg-white border border-slate-300 rounded-lg p-2">
                            <p class="text-[10px] text-slate-500 mt-1">Surat Keterangan Lulus / Ijazah.</p>
                        </div>

                        <div class="mt-8 p-4 bg-emerald-50 rounded-xl border border-emerald-100">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" required class="mt-1 text-emerald-600 focus:ring-emerald-500 rounded border-slate-300">
                                <span class="text-sm text-slate-700 font-medium">Saya menyatakan bahwa data dan dokumen yang saya isikan di formulir ini adalah benar dan dapat dipertanggungjawabkan keasliannya.</span>
                            </label>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="pt-8 mt-8 border-t border-slate-100 flex justify-between items-center">
                        <button type="button" @click="prevStep" :disabled="currentStep === 1" 
                                class="px-6 py-3 rounded-xl font-bold text-sm transition-colors"
                                :class="currentStep === 1 ? 'text-slate-400 bg-slate-100 cursor-not-allowed' : 'text-slate-600 bg-slate-200 hover:bg-slate-300'">
                            <i class="fas fa-arrow-left mr-2"></i> Sebelumnya
                        </button>
                        
                        <button v-if="currentStep < totalSteps" type="button" @click="nextStep" 
                                class="px-8 py-3 rounded-xl font-bold text-sm text-white bg-emerald-600 hover:bg-emerald-700 transition-colors shadow-md shadow-emerald-500/20">
                            Selanjutnya <i class="fas fa-arrow-right ml-2"></i>
                        </button>

                        <button v-if="currentStep === totalSteps" type="submit" :disabled="form.processing" 
                                class="px-8 py-3 rounded-xl font-black text-sm text-white bg-emerald-600 hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-500/30 flex items-center gap-2">
                            <i v-if="form.processing" class="fas fa-spinner fa-spin"></i>
                            <i v-else class="fas fa-paper-plane"></i>
                            KIRIM PENDAFTARAN
                        </button>
                    </div>

                </form>
            </div>
            
        </main>
        
        <footer class="text-center py-6 text-slate-500 text-xs bg-slate-100 border-t border-slate-200">
            &copy; {{ new Date().getFullYear() }} {{ web?.nama_sekolah || 'SMK' }}. Sistem PPDB Online Terintegrasi Dapodik.
        </footer>
    </div>
</template>
