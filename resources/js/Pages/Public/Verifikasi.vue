<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    surat: Object,
    sekolah: Object,
    isValid: Boolean
});

// Helper for formatting date
const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
};

const logoUrl = computed(() => {
    if (props.sekolah && props.sekolah.logo) {
        return props.sekolah.logo.includes('default') ? '/images/' + props.sekolah.logo : '/uploads/identitas/' + props.sekolah.logo;
    }
    return '/assets/img/logo.png';
});
</script>

<template>
    <Head title="Verifikasi Dokumen Digital" />

    <div class="min-h-screen bg-[#f4f6f8] dark:bg-gray-900 font-sans flex items-center justify-center p-4 relative overflow-hidden transition-colors duration-300">
        
        <!-- Abstract Background Shapes -->
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-primary-400/20 dark:bg-primary-900/30 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-blue-400/20 dark:bg-blue-900/30 rounded-full blur-3xl pointer-events-none"></div>

        <div class="w-full max-w-lg relative z-10">
            <!-- Header Section with Logo -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center p-1.5 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 mb-4 hover:scale-105 transition-transform duration-300">
                    <img :src="logoUrl" alt="Logo Sekolah" class="w-16 h-16 object-contain rounded-xl">
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">{{ sekolah?.nama_sekolah || 'SIAKAD' }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Sistem Verifikasi Dokumen Elektronik</p>
            </div>

            <!-- Card Verifikasi Valid -->
            <transition appear enter-active-class="transition duration-500 ease-out" enter-from-class="transform translate-y-8 opacity-0" enter-to-class="transform translate-y-0 opacity-100">
                <div v-if="isValid" class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden relative">
                    <!-- Top Green Bar -->
                    <div class="h-2 bg-gradient-to-r from-emerald-400 to-emerald-600"></div>
                    
                    <div class="p-8">
                        <div class="flex flex-col items-center text-center mb-8">
                            <div class="w-16 h-16 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-500 rounded-full flex items-center justify-center text-3xl mb-4 shadow-inner ring-4 ring-emerald-50 dark:ring-emerald-900/20">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Dokumen Valid!</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Dokumen ini diterbitkan secara sah dan tercatat di pangkalan data sistem E-Arsip kami.</p>
                        </div>

                        <!-- Data Surat Box -->
                        <div class="bg-gray-50 dark:bg-gray-700/30 rounded-2xl p-5 space-y-4 border border-gray-100 dark:border-gray-700">
                            <div>
                                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Nomor Surat</span>
                                <span class="block text-gray-900 dark:text-white font-medium">{{ surat.no_surat }}</span>
                            </div>
                            <div>
                                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Perihal</span>
                                <span class="block text-gray-900 dark:text-white font-medium">{{ surat.perihal }}</span>
                            </div>
                            <div>
                                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Diberikan Kepada</span>
                                <span class="block text-gray-900 dark:text-white font-bold text-lg">{{ surat.siswa?.nama_lengkap }}</span>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Tingkat/Kelas</span>
                                    <span class="block text-gray-900 dark:text-white font-medium">{{ surat.siswa?.kelas?.nama_kelas || '-' }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Tanggal Terbit</span>
                                    <span class="block text-gray-900 dark:text-white font-medium">{{ formatDate(surat.tgl_surat) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Penandatangan -->
                        <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700 text-center">
                            <p class="text-xs text-gray-400 mb-1">Ditandatangani secara digital oleh:</p>
                            <p class="font-bold text-gray-900 dark:text-white">{{ surat.nama_kepsek || 'Kepala Sekolah' }}</p>
                            <p class="text-xs text-gray-500">Kepala Sekolah (NIP. {{ surat.nip || '-' }})</p>
                        </div>

                        <!-- Download Button -->
                        <div class="mt-6 flex justify-center">
                            <a :href="`/verifikasi/${surat.token_validasi}/cetak`" target="_blank" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-lg shadow-emerald-600/30 flex items-center gap-2 transition-colors">
                                <i class="fas fa-file-pdf"></i> Lihat / Download Asli (PDF)
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Card Verifikasi INVALID -->
                <div v-else class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden relative">
                    <!-- Top Red Bar -->
                    <div class="h-2 bg-gradient-to-r from-red-400 to-red-600"></div>
                    
                    <div class="p-8 text-center">
                        <div class="w-20 h-20 bg-red-50 dark:bg-red-900/30 text-red-500 rounded-full flex items-center justify-center text-4xl mx-auto mb-6 shadow-inner ring-4 ring-red-50 dark:ring-red-900/20 animate-pulse">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Dokumen Tidak Valid!</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed mb-6">
                            Sistem tidak dapat menemukan rekaman dokumen ini di pangkalan data kami. QR Code atau tautan mungkin salah, atau dokumen ini diduga <b class="text-red-500">PALSU</b>.
                        </p>
                        <div class="bg-red-50 dark:bg-red-900/20 rounded-xl p-4 border border-red-100 dark:border-red-900/50">
                            <p class="text-xs text-red-600 dark:text-red-400 font-medium">Jika Anda meyakini ini adalah kesalahan, harap hubungi pihak Tata Usaha (TU) {{ sekolah?.nama_sekolah || 'Sekolah' }}.</p>
                        </div>
                    </div>
                </div>
            </transition>

            <div class="text-center mt-8">
                <p class="text-xs text-gray-400">
                    &copy; {{ new Date().getFullYear() }} {{ sekolah?.nama_sekolah || 'SIAKAD' }} E-Arsip. Hak Cipta Dilindungi.
                </p>
            </div>
        </div>
    </div>
</template>
