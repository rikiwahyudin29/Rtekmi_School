<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    sekolah: Object
});

const form = useForm({
    nama_lengkap: '',
    instansi_asal: '',
    no_hp: '',
    keperluan: '',
    kategori: 'Umum'
});

const submit = () => {
    form.post(route('buku-tamu.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset()
    });
};
</script>

<template>
    <Head title="Buku Tamu Digital" />

    <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4 sm:p-6 lg:p-8">
        
        <!-- Main Card -->
        <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-6xl overflow-hidden flex flex-col md:flex-row relative z-10 animate-fade-in-up border border-white">
            
            <!-- Left Panel (Branding) -->
            <div class="md:w-5/12 bg-gradient-to-br from-indigo-700 via-purple-800 to-indigo-900 p-10 md:p-14 text-white flex flex-col justify-between relative overflow-hidden">
                <!-- Background Ornaments -->
                <div class="absolute -top-24 -left-24 w-64 h-64 bg-white/10 rounded-full blur-3xl mix-blend-screen"></div>
                <div class="absolute -bottom-24 -right-24 w-80 h-80 bg-indigo-400/20 rounded-full blur-3xl mix-blend-screen"></div>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-full bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
                
                <div class="relative z-10 flex flex-col items-center md:items-start text-center md:text-left">
                    <div class="w-24 h-24 bg-white/10 backdrop-blur-md rounded-2xl border border-white/20 p-4 mb-8 shadow-xl flex items-center justify-center">
                        <img v-if="sekolah?.logo" :src="sekolah.logo.includes('default') ? '/images/' + sekolah.logo : '/uploads/identitas/' + sekolah.logo" class="max-w-full max-h-full drop-shadow-md object-contain" alt="Logo" @error="$event.target.style.display='none'">
                        <i v-else class="fas fa-university text-5xl text-white drop-shadow-md"></i>
                    </div>
                    
                    <h1 class="text-3xl md:text-5xl font-black tracking-tight mb-4 leading-tight">
                        Selamat Datang di <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-200 to-yellow-400">{{ sekolah?.nama_sekolah || 'Sistem Terpadu' }}</span>
                    </h1>
                    
                    <p class="text-indigo-100/90 text-sm md:text-base leading-relaxed font-medium mb-8 max-w-sm">
                        Kami mengutamakan pelayanan yang cepat dan terstruktur. Silakan isi buku tamu digital ini untuk mendapatkan tiket antrean pelayanan di area Lobi/Resepsionis.
                    </p>
                </div>
                
                <div class="relative z-10 mt-8 hidden md:block">
                    <div class="flex items-center gap-4 text-xs font-medium text-indigo-200">
                        <i class="fas fa-shield-check text-emerald-400 text-lg"></i>
                        <p>Data Anda diamankan dengan enkripsi *end-to-end* dan hanya digunakan untuk keperluan pelayanan sekolah.</p>
                    </div>
                </div>
            </div>

            <!-- Right Panel (Form) -->
            <div class="md:w-7/12 p-8 md:p-14 bg-gray-50/50 flex flex-col justify-center">
                
                <div class="mb-10 text-center md:text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-[10px] font-black uppercase tracking-widest mb-4">
                        <i class="fas fa-edit"></i> Buku Tamu Digital
                    </div>
                    <h2 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight">Formulir Kunjungan</h2>
                    <p class="text-gray-500 text-sm font-medium mt-1">Lengkapi data di bawah ini dengan benar.</p>
                </div>

                <!-- Notifikasi Sukses -->
                <div v-if="$page.props.flash?.success" class="bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-3xl p-8 text-white text-center shadow-xl shadow-emerald-600/20 animate-fade-in-down mb-8 relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/20 rounded-full blur-xl"></div>
                    <div class="w-20 h-20 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center text-4xl mx-auto mb-5 border border-white/30 shadow-inner">
                        <i class="fas fa-check"></i>
                    </div>
                    <h3 class="text-2xl font-black mb-2">{{ $page.props.flash.success }}</h3>
                    <p class="text-emerald-50 text-sm mb-6 font-medium">Tiket antrean digital juga telah dikirimkan ke WhatsApp Anda.</p>
                    
                    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 mb-6 border border-white/20">
                        <p class="text-xs text-emerald-100 font-bold uppercase tracking-widest mb-2">Nomor Antrean Anda</p>
                        <p class="text-6xl font-black tracking-tighter">#{{ $page.props.flash.antrian }}</p>
                    </div>
                    
                    <a v-if="$page.props.flash?.tamu_id" :href="`/buku-tamu/${$page.props.flash.tamu_id}/cetak`" target="_blank" class="inline-flex justify-center items-center px-8 py-4 w-full md:w-auto border-2 border-white text-sm font-bold rounded-xl text-emerald-700 bg-white hover:bg-emerald-50 shadow-lg transition-all gap-2 transform hover:scale-105">
                        <i class="fas fa-print"></i> Cetak Tiket Thermal
                    </a>
                </div>

                <!-- Form Input -->
                <form @submit.prevent="submit" class="space-y-6" v-if="!$page.props.flash?.success">
                    
                    <!-- Kategori Tamu -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-3">Kategori Tamu</label>
                        <div class="grid grid-cols-3 gap-3">
                            <label class="cursor-pointer group">
                                <input type="radio" v-model="form.kategori" value="Umum" class="peer sr-only">
                                <div class="text-center px-4 py-3.5 rounded-2xl border-2 border-gray-100 bg-white text-sm font-bold text-gray-500 group-hover:border-indigo-200 peer-checked:bg-indigo-50 peer-checked:border-indigo-600 peer-checked:text-indigo-700 transition-all shadow-sm">
                                    <i class="fas fa-user mb-1 block text-lg"></i> Umum
                                </div>
                            </label>
                            <label class="cursor-pointer group">
                                <input type="radio" v-model="form.kategori" value="Orang Tua" class="peer sr-only">
                                <div class="text-center px-4 py-3.5 rounded-2xl border-2 border-gray-100 bg-white text-sm font-bold text-gray-500 group-hover:border-indigo-200 peer-checked:bg-indigo-50 peer-checked:border-indigo-600 peer-checked:text-indigo-700 transition-all shadow-sm">
                                    <i class="fas fa-users mb-1 block text-lg"></i> Orang Tua
                                </div>
                            </label>
                            <label class="cursor-pointer group">
                                <input type="radio" v-model="form.kategori" value="Dinas" class="peer sr-only">
                                <div class="text-center px-4 py-3.5 rounded-2xl border-2 border-gray-100 bg-white text-sm font-bold text-gray-500 group-hover:border-indigo-200 peer-checked:bg-indigo-50 peer-checked:border-indigo-600 peer-checked:text-indigo-700 transition-all shadow-sm">
                                    <i class="fas fa-user-tie mb-1 block text-lg"></i> Dinas
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label for="nama_lengkap" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Nama Lengkap</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                                    <i class="fas fa-id-card"></i>
                                </div>
                                <input id="nama_lengkap" v-model="form.nama_lengkap" type="text" required class="pl-12 block w-full px-4 py-3.5 border-2 border-gray-100 rounded-2xl shadow-sm placeholder-gray-400 focus:ring-0 focus:border-indigo-500 font-bold text-gray-800 bg-white transition-colors" placeholder="Masukkan nama Anda">
                            </div>
                            <div v-if="form.errors.nama_lengkap" class="text-rose-500 text-xs mt-1.5 font-bold">{{ form.errors.nama_lengkap }}</div>
                        </div>

                        <div>
                            <label for="instansi_asal" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Instansi / Asal (Opsional)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                                    <i class="fas fa-building"></i>
                                </div>
                                <input id="instansi_asal" v-model="form.instansi_asal" type="text" class="pl-12 block w-full px-4 py-3.5 border-2 border-gray-100 rounded-2xl shadow-sm placeholder-gray-400 focus:ring-0 focus:border-indigo-500 font-bold text-gray-800 bg-white transition-colors" placeholder="Dinas / Pribadi">
                            </div>
                        </div>

                        <div>
                            <label for="no_hp" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">No. WhatsApp</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-emerald-500">
                                    <i class="fab fa-whatsapp text-lg"></i>
                                </div>
                                <input id="no_hp" v-model="form.no_hp" type="text" required class="pl-12 block w-full px-4 py-3.5 border-2 border-gray-100 rounded-2xl shadow-sm placeholder-gray-400 focus:ring-0 focus:border-emerald-500 font-bold text-gray-800 bg-white transition-colors" placeholder="081234...">
                            </div>
                            <div v-if="form.errors.no_hp" class="text-rose-500 text-xs mt-1.5 font-bold">{{ form.errors.no_hp }}</div>
                        </div>

                        <div class="md:col-span-2">
                            <label for="keperluan" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Keperluan Kunjungan</label>
                            <textarea id="keperluan" v-model="form.keperluan" rows="3" required class="block w-full px-5 py-4 border-2 border-gray-100 rounded-2xl shadow-sm placeholder-gray-400 focus:ring-0 focus:border-indigo-500 font-bold text-gray-800 bg-white transition-colors leading-relaxed" placeholder="Tuliskan tujuan atau keperluan kunjungan secara spesifik..."></textarea>
                            <div v-if="form.errors.keperluan" class="text-rose-500 text-xs mt-1.5 font-bold">{{ form.errors.keperluan }}</div>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" :disabled="form.processing" class="w-full flex justify-center items-center py-4 px-6 rounded-2xl shadow-xl shadow-indigo-600/30 text-base font-black text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-500/50 transition-all transform hover:-translate-y-1 disabled:opacity-70 disabled:cursor-not-allowed disabled:transform-none">
                            <i class="fas fa-paper-plane mr-2" v-if="!form.processing"></i>
                            <i class="fas fa-spinner fa-spin mr-2" v-else></i>
                            {{ form.processing ? 'Sedang Memproses Data...' : 'Kirim & Dapatkan Tiket Antrean' }}
                        </button>
                    </div>
                </form>

            </div>
        </div>
        
    </div>
</template>
