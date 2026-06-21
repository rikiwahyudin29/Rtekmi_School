<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    data_siswa: Array,
    data_kunjungan: Array,
    total_siswa: Number,
    total_lulus: Number,
    total_dudi: Number,
    persen_kunjungan: Number
});
</script>

<template>
    <Head title="Dashboard Guru Pembimbing PKL" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-chart-pie text-blue-500"></i>
                        Dashboard Pembimbing PKL
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Monitoring performa siswa dan pencapaian target bimbingan.
                    </p>
                </div>
            </div>

            <!-- Statistik Global -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="text-gray-500 dark:text-gray-400 text-sm font-bold mb-1">Total Siswa Binaan</div>
                    <div class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <i class="fas fa-users text-blue-500 bg-blue-50 dark:bg-blue-900/30 p-2 rounded-xl"></i>
                        {{ total_siswa }}
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="text-gray-500 dark:text-gray-400 text-sm font-bold mb-1">Selesai (Lulus)</div>
                    <div class="text-3xl font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-3">
                        <i class="fas fa-check-circle bg-emerald-50 dark:bg-emerald-900/30 p-2 rounded-xl"></i>
                        {{ total_lulus }}
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="text-gray-500 dark:text-gray-400 text-sm font-bold mb-1">Mitra DU/DI</div>
                    <div class="text-3xl font-bold text-purple-600 dark:text-purple-400 flex items-center gap-3">
                        <i class="fas fa-building bg-purple-50 dark:bg-purple-900/30 p-2 rounded-xl"></i>
                        {{ total_dudi }}
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="text-gray-500 dark:text-gray-400 text-sm font-bold mb-1">Target Kunjungan Guru</div>
                    <div class="text-3xl font-bold flex items-center gap-3" :class="persen_kunjungan >= 100 ? 'text-emerald-600' : 'text-amber-500'">
                        <i class="fas fa-route p-2 rounded-xl" :class="persen_kunjungan >= 100 ? 'bg-emerald-50 dark:bg-emerald-900/30' : 'bg-amber-50 dark:bg-amber-900/30'"></i>
                        {{ persen_kunjungan }}%
                    </div>
                </div>
            </div>

            <!-- Menu Cepat (Akses Fitur) -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <Link :href="route('guru.pkl.monitoring')" class="bg-blue-600 hover:bg-blue-700 text-white p-4 rounded-3xl shadow-sm transition-all flex flex-col items-center justify-center gap-2 group">
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-map-marker-alt text-xl"></i>
                    </div>
                    <span class="font-bold text-sm mt-1">Monitoring Kehadiran</span>
                </Link>
                <Link :href="route('guru.pkl.jurnal')" class="bg-purple-600 hover:bg-purple-700 text-white p-4 rounded-3xl shadow-sm transition-all flex flex-col items-center justify-center gap-2 group">
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-book text-xl"></i>
                    </div>
                    <span class="font-bold text-sm mt-1">Jurnal & Laporan</span>
                </Link>
                <Link :href="route('guru.pkl.kunjungan')" class="bg-amber-500 hover:bg-amber-600 text-white p-4 rounded-3xl shadow-sm transition-all flex flex-col items-center justify-center gap-2 group">
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-camera-retro text-xl"></i>
                    </div>
                    <span class="font-bold text-sm mt-1">Jurnal Kunjungan</span>
                </Link>
                <Link :href="route('guru.pkl.nilai')" class="bg-emerald-600 hover:bg-emerald-700 text-white p-4 rounded-3xl shadow-sm transition-all flex flex-col items-center justify-center gap-2 group">
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-certificate text-xl"></i>
                    </div>
                    <span class="font-bold text-sm mt-1">Nilai & Sertifikat</span>
                </Link>
            </div>

            <!-- KPI Siswa Binaan -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-clipboard-list text-blue-500"></i>
                        Performa Siswa Binaan (KPI)
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-4 font-bold">Nama Siswa</th>
                                <th class="px-6 py-4 font-bold text-center">Kehadiran (40%)</th>
                                <th class="px-6 py-4 font-bold text-center">Jurnal (40%)</th>
                                <th class="px-6 py-4 font-bold text-center">Laporan (20%)</th>
                                <th class="px-6 py-4 font-bold text-center">Est. Nilai Sistem</th>
                                <th class="px-6 py-4 font-bold text-center">Nilai Final (Sertifikat)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="s in data_siswa" :key="s.id_pkl" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900 dark:text-white">{{ s.nama_siswa }}</div>
                                    <div class="text-xs text-gray-500">{{ s.nis }} - <span class="text-blue-600 font-bold">{{ s.nama_dudi }}</span></div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="w-full bg-gray-200 rounded-full h-2 mb-1">
                                        <div class="bg-blue-500 h-2 rounded-full" :style="{ width: s.p_hadir + '%' }"></div>
                                    </div>
                                    <span class="text-xs font-bold" :class="s.p_hadir < 80 ? 'text-red-500' : 'text-gray-600'">{{ s.p_hadir }}%</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="w-full bg-gray-200 rounded-full h-2 mb-1">
                                        <div class="bg-purple-500 h-2 rounded-full" :style="{ width: s.p_jurnal + '%' }"></div>
                                    </div>
                                    <span class="text-xs font-bold" :class="s.p_jurnal < 80 ? 'text-red-500' : 'text-gray-600'">{{ s.p_jurnal }}%</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full" :class="s.badge_lap">
                                        {{ s.status_lap }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="text-lg font-bold" :class="s.nilai_sistem >= 80 ? 'text-emerald-600' : 'text-amber-500'">
                                        {{ s.nilai_sistem }}
                                    </div>
                                    <div class="text-xs text-gray-500">Grade: {{ s.grade }}</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span v-if="s.is_lulus" class="px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full text-sm font-bold">
                                        <i class="fas fa-check-circle"></i> {{ s.nilai_akhir }}
                                    </span>
                                    <span v-else class="px-3 py-1 bg-gray-100 text-gray-500 rounded-full text-xs font-bold">
                                        Belum Dinilai
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="data_siswa.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    Belum ada data siswa binaan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Progress Kunjungan Guru -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <h3 class="font-bold text-lg text-gray-900 dark:text-white flex items-center gap-2 mb-4">
                    <i class="fas fa-route text-amber-500"></i>
                    Realisasi Kunjungan Monitoring (Target 3x per DUDI)
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="k in data_kunjungan" :key="k.nama_dudi" class="border border-gray-100 dark:border-gray-700 p-4 rounded-2xl bg-gray-50/50 dark:bg-gray-700/50">
                        <div class="font-bold text-gray-900 dark:text-white mb-2">{{ k.nama_dudi }}</div>
                        <div class="flex justify-between text-xs font-bold mb-1">
                            <span class="text-gray-500">Realisasi: {{ k.jml_kunjungan }} Kunjungan</span>
                            <span :class="k.persen >= 100 ? 'text-emerald-500' : 'text-blue-500'">{{ Math.round(k.persen) }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="h-2 rounded-full" :class="k.persen >= 100 ? 'bg-emerald-500' : 'bg-blue-500'" :style="{ width: Math.min(k.persen, 100) + '%' }"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
