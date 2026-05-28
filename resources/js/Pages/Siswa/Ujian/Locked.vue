<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    sesi: Object
});
</script>

<template>
    <Head title="Ujian Terkunci" />

    <DashboardLayout>
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 w-full py-12">
            
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden dark:bg-gray-800 border border-gray-100 dark:border-gray-700 text-center p-10">
                <div class="w-32 h-32 bg-rose-100 dark:bg-rose-900/30 text-rose-500 rounded-full flex items-center justify-center text-6xl mx-auto mb-6 shadow-inner">
                    <i class="fas fa-lock"></i>
                </div>
                
                <h1 class="text-3xl font-extrabold text-gray-800 dark:text-white mb-4 uppercase tracking-tight">UJIAN TERKUNCI</h1>
                
                <p class="text-gray-600 dark:text-gray-300 text-lg mb-8 max-w-lg mx-auto">
                    Sesi ujian Anda telah dikunci oleh sistem karena terdeteksi meninggalkan halaman ujian saat fitur <b>STRICT MODE</b> aktif, atau Anda kehabisan waktu saat peringatan muncul.
                </p>

                <div class="bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-2xl p-6 mb-8 text-rose-800 dark:text-rose-300 inline-block text-left">
                    <h3 class="font-bold text-sm uppercase tracking-widest mb-2 flex items-center gap-2"><i class="fas fa-info-circle"></i> Apa yang harus dilakukan?</h3>
                    <ul class="list-disc ml-5 text-sm space-y-1">
                        <li>Segera lapor kepada <b>Pengawas Ruangan</b> atau <b>Guru Mata Pelajaran</b>.</li>
                        <li>Sampaikan alasan Anda meninggalkan halaman ujian.</li>
                        <li>Hanya pengawas/guru yang dapat membuka kembali kunci ujian Anda.</li>
                    </ul>
                </div>

                <div>
                    <Link :href="route('siswa.ujian.index')" class="inline-flex items-center justify-center gap-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold py-4 px-8 rounded-2xl transition-all active:scale-95">
                        <i class="fas fa-arrow-left"></i> Kembali ke Jadwal Ujian
                    </Link>
                </div>
            </div>
            
        </div>
    </DashboardLayout>
</template>
