<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    pendaftar: Object,
    web: Object
});

const downloadPDF = () => {
    // Dynamically load html2pdf.js to avoid Vite build errors
    const script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
    script.onload = () => {
        const element = document.getElementById('bukti-pendaftaran');
        const opt = {
            margin: 0.5,
            filename: `Bukti_Pendaftaran_${props.pendaftar.no_pendaftaran}.pdf`,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2 },
            jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
        };

        window.html2pdf().set(opt).from(element).save();
    };
    document.head.appendChild(script);
};
</script>

<template>
    <Head>
        <title>Berhasil Mendaftar - {{ web?.nama_sekolah || 'SMK' }}</title>
    </Head>

    <div class="min-h-screen bg-slate-50 font-sans flex flex-col items-center justify-center p-4">
        
        <div class="w-full max-w-2xl bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden relative">
            <!-- Header Pattern -->
            <div class="h-32 bg-emerald-600 w-full relative overflow-hidden flex items-center justify-center">
                <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 20px 20px;"></div>
                <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center shadow-lg border-4 border-emerald-500 z-10 text-emerald-500 text-3xl">
                    <i class="fas fa-check"></i>
                </div>
            </div>

            <div class="p-8 md:p-12 text-center" id="bukti-pendaftaran">
                <h1 class="text-3xl font-black text-slate-800 mb-2">Pendaftaran Berhasil!</h1>
                <p class="text-slate-500 text-sm mb-8">Terima kasih telah mendaftar di {{ web?.nama_sekolah }}. Data Anda telah masuk ke sistem kami.</p>

                <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200 inline-block text-left w-full max-w-md mx-auto mb-8">
                    <div class="text-center mb-6 border-b border-slate-200 pb-4">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Nomor Registrasi Anda</span>
                        <div class="text-3xl font-black text-emerald-600 tracking-wider">{{ pendaftar.no_pendaftaran }}</div>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between border-b border-slate-100 pb-2">
                            <span class="text-xs font-medium text-slate-500">Nama Lengkap</span>
                            <span class="text-xs font-bold text-slate-800 text-right">{{ pendaftar.nama_lengkap }}</span>
                        </div>
                        <div class="flex justify-between border-b border-slate-100 pb-2">
                            <span class="text-xs font-medium text-slate-500">NISN</span>
                            <span class="text-xs font-bold text-slate-800 text-right">{{ pendaftar.nisn }}</span>
                        </div>
                        <div class="flex justify-between border-b border-slate-100 pb-2">
                            <span class="text-xs font-medium text-slate-500">Pilihan Jurusan</span>
                            <span class="text-xs font-bold text-slate-800 text-right">{{ pendaftar.jurusan_minat }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs font-medium text-slate-500">Waktu Daftar</span>
                            <span class="text-xs font-bold text-slate-800 text-right">{{ new Date(pendaftar.tgl_daftar).toLocaleString('id-ID') }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-amber-50 border border-amber-100 text-amber-700 text-sm p-4 rounded-xl text-left flex gap-4 items-start mb-8">
                    <i class="fas fa-info-circle mt-1 text-amber-500 text-xl"></i>
                    <p>Panitia akan memverifikasi dokumen Anda. Harap simpan Nomor Registrasi ini dan pastikan nomor WhatsApp <b>{{ pendaftar.no_hp_siswa }}</b> selalu aktif untuk menerima informasi kelulusan.</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="print:hidden bg-slate-50 p-6 border-t border-slate-100 flex flex-col sm:flex-row gap-4 justify-center">
                <button @click="downloadPDF" class="px-6 py-3 rounded-xl bg-white border-2 border-emerald-500 text-emerald-600 font-bold hover:bg-emerald-50 transition-colors flex items-center justify-center gap-2">
                    <i class="fas fa-download"></i> Cetak Bukti Pendaftaran
                </button>
                <Link href="/" class="px-6 py-3 rounded-xl bg-slate-800 text-white font-bold hover:bg-slate-900 transition-colors flex items-center justify-center gap-2">
                    <i class="fas fa-home"></i> Kembali ke Beranda
                </Link>
            </div>
        </div>

    </div>
</template>

<style scoped>
@media print {
    body * {
        visibility: hidden;
    }
    #bukti-pendaftaran, #bukti-pendaftaran * {
        visibility: visible;
    }
    #bukti-pendaftaran {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        padding: 0;
    }
}
</style>
