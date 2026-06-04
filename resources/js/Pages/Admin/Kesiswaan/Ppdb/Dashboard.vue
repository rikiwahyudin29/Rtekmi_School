<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    stats: Object,
    jurusanData: Array,
    asalSekolahData: Array
});
</script>

<template>
    <Head title="Dashboard PPDB" />

    <DashboardLayout>
        <template #header>
            <h2 class="font-black text-xl text-slate-800 leading-tight">Laporan & Dashboard PPDB</h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                
                <!-- Statistik Card -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200">
                        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-500 flex items-center justify-center text-xl mb-4"><i class="fas fa-users"></i></div>
                        <div class="text-3xl font-black text-slate-800">{{ stats.total }}</div>
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Total Pendaftar</div>
                    </div>
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center text-xl mb-4"><i class="fas fa-clock"></i></div>
                        <div class="text-3xl font-black text-blue-600">{{ stats.pending }}</div>
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Menunggu Verifikasi</div>
                    </div>
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-emerald-500">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-xl mb-4"><i class="fas fa-check-circle"></i></div>
                        <div class="text-3xl font-black text-emerald-600">{{ stats.diterima }}</div>
                        <div class="text-xs font-bold text-emerald-600 uppercase tracking-widest mt-1">Siswa Diterima</div>
                    </div>
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200">
                        <div class="w-12 h-12 rounded-2xl bg-red-50 text-red-500 flex items-center justify-center text-xl mb-4"><i class="fas fa-times-circle"></i></div>
                        <div class="text-3xl font-black text-red-600">{{ stats.ditolak }}</div>
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Ditolak / Batal</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <!-- Grafik Jurusan -->
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
                        <h4 class="font-black text-slate-800 mb-6 border-b border-slate-100 pb-2">Distribusi Pilihan Jurusan</h4>
                        
                        <div class="space-y-4">
                            <div v-for="jurusan in jurusanData" :key="jurusan.jurusan_minat" class="relative">
                                <div class="flex justify-between items-end mb-1">
                                    <span class="text-sm font-bold text-slate-700">{{ jurusan.jurusan_minat }}</span>
                                    <span class="text-xs font-black text-emerald-600">{{ jurusan.total }} Pendaftar</span>
                                </div>
                                <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-emerald-500 rounded-full" :style="`width: ${(jurusan.total / stats.total) * 100}%`"></div>
                                </div>
                            </div>
                            <div v-if="jurusanData.length === 0" class="text-center text-slate-400 py-8 text-sm">
                                Belum ada data
                            </div>
                        </div>
                    </div>

                    <!-- Asal Sekolah Terbanyak -->
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
                        <div class="flex items-center justify-between mb-6 border-b border-slate-100 pb-2">
                            <h4 class="font-black text-slate-800">Top 10 Asal Sekolah</h4>
                            <span class="px-3 py-1 bg-amber-100 text-amber-700 text-[10px] font-bold uppercase tracking-widest rounded-full">Pendaftar Terbanyak</span>
                        </div>
                        
                        <ul class="space-y-3">
                            <li v-for="(sekolah, index) in asalSekolahData" :key="index" class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 border border-transparent hover:border-slate-100 transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-black text-sm"
                                         :class="index < 3 ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-400'">
                                        {{ index + 1 }}
                                    </div>
                                    <span class="font-bold text-slate-700 text-sm">{{ sekolah.asal_sekolah }}</span>
                                </div>
                                <div class="px-3 py-1 bg-slate-100 rounded-lg text-xs font-black text-slate-600">
                                    {{ sekolah.total }}
                                </div>
                            </li>
                            <li v-if="asalSekolahData.length === 0" class="text-center text-slate-400 py-8 text-sm">
                                Belum ada data
                            </li>
                        </ul>
                    </div>

                </div>

            </div>
        </div>
    </DashboardLayout>
</template>
