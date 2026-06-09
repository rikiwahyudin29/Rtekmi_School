<script setup>
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    rapor_akhir: Array,
    kehadiran: Object,
    catatan: Object,
    pkl: Array
});

// Calculate average
const rataRata = computed(() => {
    if (!props.rapor_akhir || props.rapor_akhir.length === 0) return 0;
    const sum = props.rapor_akhir.reduce((acc, curr) => acc + Number(curr.nilai_akhir), 0);
    return (sum / props.rapor_akhir.length).toFixed(2);
});
</script>

<template>
    <Head title="Capaian Rapor Siswa" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Header & Download Button -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-gradient-to-r from-blue-600 to-indigo-700 rounded-3xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden">
                <i class="fas fa-graduation-cap text-white/10 text-9xl absolute -right-10 -bottom-10 pointer-events-none transform -rotate-12"></i>
                <div class="relative z-10">
                    <h2 class="text-2xl sm:text-3xl font-bold flex items-center gap-3">
                        Capaian Hasil Belajar
                    </h2>
                    <p class="mt-2 text-blue-100 max-w-xl">
                        Berikut adalah rekapitulasi nilai rapor, kehadiran, dan catatan wali kelas Anda untuk semester ini. Terus tingkatkan prestasimu!
                    </p>
                </div>
                <div class="relative z-10 shrink-0">
                    <!-- Simulasi tombol cetak karena ini view siswa, kita arahkan ke route cetak admin/guru sementara (atau window.print) -->
                    <button onclick="window.print()" class="px-6 py-3 bg-white text-indigo-700 hover:bg-gray-50 rounded-xl font-bold shadow-md flex items-center gap-2 transition-transform hover:scale-105">
                        <i class="fas fa-file-pdf text-red-500"></i> Download Rapor PDF
                    </button>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Rata-rata -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-blue-500 flex items-center justify-center text-2xl shrink-0">
                        <i class="fas fa-star"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Rata-rata Nilai Akhir</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ rataRata }}</p>
                    </div>
                </div>

                <!-- Kehadiran -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-green-50 dark:bg-green-900/30 text-green-500 flex items-center justify-center text-2xl shrink-0">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Ketidakhadiran (S/I/A)</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ kehadiran ? `${kehadiran.sakit} / ${kehadiran.izin} / ${kehadiran.tanpa_keterangan}` : '0 / 0 / 0' }}
                        </p>
                    </div>
                </div>

                <!-- PKL -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-orange-50 dark:bg-orange-900/30 text-orange-500 flex items-center justify-center text-2xl shrink-0">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Status PKL</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white line-clamp-1">
                            {{ pkl && pkl.length > 0 ? pkl[0].dudi?.nama_dudi || 'Selesai' : 'Belum PKL' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <!-- Tabel Nilai Rapor -->
                <div class="xl:col-span-2 bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="font-bold text-lg text-gray-900 dark:text-white flex items-center gap-2">
                            <i class="fas fa-list text-indigo-500"></i> Detail Capaian Nilai Akademik
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                                <tr>
                                    <th class="px-6 py-4">No</th>
                                    <th class="px-6 py-4">Mata Pelajaran</th>
                                    <th class="px-6 py-4 text-center">Nilai Akhir</th>
                                    <th class="px-6 py-4">Capaian Kompetensi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(rapor, idx) in rapor_akhir" :key="rapor.id" class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-6 py-4">{{ idx + 1 }}</td>
                                    <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ rapor.mapel?.nama_mapel }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-3 py-1 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-full font-bold text-lg">
                                            {{ rapor.nilai_akhir }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-xs mb-2"><span class="font-bold text-green-600">Tercapai:</span> {{ rapor.deskripsi_tertinggi || 'Menunjukkan penguasaan yang baik dalam kompetensi dasar.' }}</p>
                                        <p class="text-xs"><span class="font-bold text-red-500">Perlu Peningkatan:</span> {{ rapor.deskripsi_terendah || 'Perlu bimbingan lebih lanjut untuk meningkatkan pemahaman.' }}</p>
                                    </td>
                                </tr>
                                <tr v-if="!rapor_akhir || rapor_akhir.length === 0">
                                    <td colspan="4" class="px-6 py-10 text-center text-gray-500">Nilai rapor belum di-generate oleh wali kelas.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Catatan Wali Kelas & Ekstrakurikuler -->
                <div class="xl:col-span-1 space-y-6">
                    <!-- Catatan Wali Kelas -->
                    <div class="bg-gradient-to-br from-indigo-50 to-blue-50 dark:from-gray-800 dark:to-gray-800 rounded-3xl shadow-sm border border-indigo-100 dark:border-gray-700 p-6 relative overflow-hidden">
                        <i class="fas fa-quote-right text-indigo-500/10 text-6xl absolute top-4 right-4 pointer-events-none"></i>
                        <h3 class="font-bold text-indigo-900 dark:text-white mb-4 flex items-center gap-2 relative z-10">
                            <i class="fas fa-comment-dots text-indigo-500"></i> Catatan Wali Kelas
                        </h3>
                        <div class="bg-white/60 dark:bg-gray-900/50 backdrop-blur-sm rounded-2xl p-4 text-gray-700 dark:text-gray-300 italic text-sm relative z-10 border border-white/50 dark:border-gray-600">
                            "{{ catatan?.catatan || 'Belum ada catatan dari wali kelas untuk semester ini.' }}"
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
