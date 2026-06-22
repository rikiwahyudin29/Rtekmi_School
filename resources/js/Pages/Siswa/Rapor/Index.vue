<script setup>
import { Head, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    jadwal_pelajaran: Array,
    rapor_akhir: Object,
    formatif: Object,
    sumatif: Object,
    kehadiran: Object,
    catatan: Object,
    pkl: Array,
    siswa_id: Number,
    semua_tahun: Array,
    selected_ta_id: [Number, String]
});

const changeTahun = (e) => {
    router.get(route('siswa.rapor.index'), { tahun_ajaran_id: e.target.value }, { preserveState: true });
};

// Calculate average based on available rapor_akhir
const rataRata = computed(() => {
    if (!props.rapor_akhir || Object.keys(props.rapor_akhir).length === 0) return 0;
    let sum = 0;
    let count = 0;
    Object.values(props.rapor_akhir).forEach(rapor => {
        if (rapor && rapor.nilai_akhir) {
            sum += Number(rapor.nilai_akhir);
            count++;
        }
    });
    return count > 0 ? (sum / count).toFixed(2) : 0;
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
                <div class="relative z-10 flex flex-col sm:flex-row gap-3">
                    <select @change="changeTahun" :value="selected_ta_id" class="bg-white/20 border border-white/30 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-white/50 focus:border-white focus:outline-none appearance-none font-bold placeholder-white/70">
                        <option v-for="ta in semua_tahun" :key="ta.id" :value="ta.id" class="text-gray-900 font-normal">
                            Semester {{ ta.semester }} - {{ ta.tahun_ajaran }} {{ ta.status === 'Aktif' ? '(Aktif)' : '' }}
                        </option>
                    </select>

                    <!-- Link langsung ke view cetak rapor admin/guru -->
                    <a :href="route('cetak.rapor.nilai', { id: siswa_id })" target="_blank" class="px-6 py-3 bg-white text-indigo-700 hover:bg-gray-50 rounded-xl font-bold shadow-md flex items-center justify-center gap-2 transition-transform hover:scale-105">
                        <i class="fas fa-file-pdf text-red-500"></i> Download Rapor PDF
                    </a>
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
                                <template v-for="(jadwal, idx) in jadwal_pelajaran" :key="jadwal.id_mapel">
                                    <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-6 py-4">{{ idx + 1 }}</td>
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-gray-900 dark:text-white">{{ jadwal.mapel?.nama_mapel }}</div>
                                            <div class="text-xs text-gray-500 mt-1"><i class="fas fa-user-tie text-indigo-400 mr-1"></i> {{ jadwal.guru?.nama_lengkap || 'Guru belum diatur' }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span v-if="rapor_akhir[jadwal.id_mapel]" class="px-3 py-1 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-full font-bold text-lg">
                                                {{ rapor_akhir[jadwal.id_mapel].nilai_akhir }}
                                            </span>
                                            <span v-else class="text-gray-400 italic text-xs">-</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div v-if="rapor_akhir[jadwal.id_mapel]">
                                                <p class="text-xs mb-2"><span class="font-bold text-green-600">Tercapai:</span> {{ rapor_akhir[jadwal.id_mapel].deskripsi_tertinggi || 'Menunjukkan penguasaan yang baik dalam kompetensi dasar.' }}</p>
                                                <p class="text-xs"><span class="font-bold text-red-500">Perlu Peningkatan:</span> {{ rapor_akhir[jadwal.id_mapel].deskripsi_terendah || 'Perlu bimbingan lebih lanjut untuk meningkatkan pemahaman.' }}</p>
                                            </div>
                                            <div v-else class="text-gray-400 italic text-xs">Belum dinilai</div>
                                        </td>
                                    </tr>
                                    <tr class="bg-gray-50/50 dark:bg-gray-800/50 border-b dark:border-gray-700">
                                        <td></td>
                                        <td colspan="3" class="px-6 py-4">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <!-- Formatif -->
                                                <div>
                                                    <h4 class="text-xs font-bold text-gray-500 uppercase mb-2">Nilai Formatif (Per TP)</h4>
                                                    <div class="space-y-1">
                                                        <div v-for="f in formatif[jadwal.id_mapel]" :key="f.id" class="flex justify-between items-center text-xs bg-white dark:bg-gray-700 p-2 rounded border dark:border-gray-600">
                                                            <span :title="f.tp_deskripsi" class="font-medium truncate mr-2">{{ f.kode_tp }}</span>
                                                            <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ f.nilai }}</span>
                                                        </div>
                                                        <div v-if="!formatif[jadwal.id_mapel] || formatif[jadwal.id_mapel].length === 0" class="text-xs text-gray-400 italic">Belum ada nilai formatif.</div>
                                                    </div>
                                                </div>
                                                <!-- Sumatif -->
                                                <div>
                                                    <h4 class="text-xs font-bold text-gray-500 uppercase mb-2">Nilai Sumatif</h4>
                                                    <div class="space-y-1">
                                                        <div v-for="s in sumatif[jadwal.id_mapel]" :key="s.id" class="flex justify-between items-center text-xs bg-white dark:bg-gray-700 p-2 rounded border dark:border-gray-600">
                                                            <span class="font-medium">{{ s.jenis }}</span>
                                                            <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ s.nilai }}</span>
                                                        </div>
                                                        <div v-if="!sumatif[jadwal.id_mapel] || sumatif[jadwal.id_mapel].length === 0" class="text-xs text-gray-400 italic">Belum ada nilai sumatif.</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <tr v-if="!jadwal_pelajaran || jadwal_pelajaran.length === 0">
                                    <td colspan="4" class="px-6 py-10 text-center text-gray-500">Belum ada jadwal pelajaran untuk kelas ini.</td>
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
