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
        const element = document.getElementById('kartu-pendaftaran');
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
        
        <div class="w-full max-w-4xl bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden relative">
            <!-- Header Pattern -->
            <div class="h-32 bg-emerald-600 w-full relative overflow-hidden flex items-center justify-center print:hidden">
                <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 20px 20px;"></div>
                <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center shadow-lg border-4 border-emerald-500 z-10 text-emerald-500 text-3xl">
                    <i class="fas fa-check"></i>
                </div>
            </div>

            <div class="p-8 md:p-10 text-center">
                <h1 class="text-3xl font-black text-slate-800 mb-2 print:hidden">Pendaftaran Berhasil!</h1>
                <p class="text-slate-500 text-sm mb-8 print:hidden">Terima kasih telah mendaftar di {{ web?.nama_sekolah }}. Data Anda telah masuk ke sistem kami.</p>

                <!-- Kartu Pendaftaran -->
                <div class="bg-white rounded-2xl p-6 md:p-8 border-2 border-emerald-100 text-left w-full mx-auto mb-8 relative overflow-hidden" id="kartu-pendaftaran">
                    <!-- Watermark -->
                    <div class="absolute -right-10 -top-10 opacity-[0.03]">
                        <i class="fas fa-file-contract text-[250px]"></i>
                    </div>

                    <!-- Header Kartu -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between border-b-2 border-emerald-500 pb-4 mb-6 relative z-10 gap-4">
                        <div>
                            <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tight">{{ web?.nama_sekolah || 'SMK' }}</h2>
                            <p class="text-xs text-slate-500 mt-1 max-w-sm">{{ web?.alamat_sekolah || 'Panitia Penerimaan Peserta Didik Baru' }}</p>
                        </div>
                        <div class="sm:text-right">
                            <span class="px-3 py-1 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded-full uppercase tracking-wider inline-block">Bukti Pendaftaran</span>
                            <div class="text-sm font-bold text-slate-500 mt-2">TA. {{ new Date().getFullYear() }}/{{ new Date().getFullYear() + 1 }}</div>
                        </div>
                    </div>

                    <div class="text-center mb-8 relative z-10">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Nomor Registrasi</span>
                        <div class="text-3xl md:text-4xl font-black text-emerald-600 tracking-wider font-mono">{{ pendaftar.no_pendaftaran }}</div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6 relative z-10">
                        <!-- Kolom Kiri -->
                        <div class="space-y-4">
                            <div class="flex flex-col">
                                <span class="text-[10px] font-bold text-slate-400 uppercase">Nama Lengkap</span>
                                <span class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-1">{{ pendaftar.nama_lengkap }}</span>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="flex flex-col">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase">NISN / NIK</span>
                                    <span class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-1">{{ pendaftar.nisn }} / {{ pendaftar.nik }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase">Jenis Kelamin</span>
                                    <span class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-1">{{ pendaftar.jk === 'L' ? 'Laki-Laki' : 'Perempuan' }}</span>
                                </div>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[10px] font-bold text-slate-400 uppercase">Tempat, Tanggal Lahir</span>
                                <span class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-1">{{ pendaftar.tempat_lahir }}, {{ pendaftar.tgl_lahir }}</span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[10px] font-bold text-slate-400 uppercase">Asal Sekolah</span>
                                <span class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-1">{{ pendaftar.asal_sekolah }}</span>
                            </div>
                        </div>

                        <!-- Kolom Kanan -->
                        <div class="space-y-4">
                            <div class="flex flex-col">
                                <span class="text-[10px] font-bold text-slate-400 uppercase">Jurusan Pilihan</span>
                                <span class="text-sm font-black text-emerald-600 border-b border-slate-100 pb-1">{{ pendaftar.jurusan_minat }}</span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[10px] font-bold text-slate-400 uppercase">Kontak (Siswa / Ortu)</span>
                                <span class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-1">{{ pendaftar.no_hp_siswa }} / {{ pendaftar.no_hp_ortu }}</span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[10px] font-bold text-slate-400 uppercase">Nama Orang Tua (Ibu / Ayah)</span>
                                <span class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-1">{{ pendaftar.nama_ibu }} / {{ pendaftar.nama_ayah || '-' }}</span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[10px] font-bold text-slate-400 uppercase">Alamat Lengkap</span>
                                <span class="text-xs font-bold text-slate-800 border-b border-slate-100 pb-1 leading-relaxed">{{ pendaftar.alamat }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Kartu -->
                    <div class="mt-8 pt-4 border-t border-slate-100 text-[10px] text-slate-400 text-center relative z-10 flex justify-between items-center">
                        <span>Waktu Daftar: {{ new Date(pendaftar.tgl_daftar).toLocaleString('id-ID') }}</span>
                        <span>Dicetak: {{ new Date().toLocaleString('id-ID') }}</span>
                    </div>
                </div>

                <div class="bg-amber-50 border border-amber-100 text-amber-700 text-sm p-4 rounded-xl text-left flex gap-4 items-start mb-8 print:hidden">
                    <i class="fas fa-info-circle mt-1 text-amber-500 text-xl"></i>
                    <p>Panitia akan memverifikasi dokumen Anda. Harap simpan Bukti Pendaftaran ini dan pastikan nomor WhatsApp <b>{{ pendaftar.no_hp_siswa }}</b> selalu aktif.</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="print:hidden bg-slate-50 p-6 border-t border-slate-100 flex flex-col sm:flex-row gap-4 justify-center">
                <button @click="downloadPDF" class="px-6 py-3 rounded-xl bg-white border-2 border-emerald-500 text-emerald-600 font-bold hover:bg-emerald-50 transition-colors flex items-center justify-center gap-2">
                    <i class="fas fa-download"></i> Unduh PDF Bukti
                </button>
                <Link href="/" class="px-6 py-3 rounded-xl bg-slate-800 text-white font-bold hover:bg-slate-900 transition-colors flex items-center justify-center gap-2">
                    <i class="fas fa-home"></i> Kembali ke Beranda
                </Link>
            </div>
        </div>

    </div>
</template>
