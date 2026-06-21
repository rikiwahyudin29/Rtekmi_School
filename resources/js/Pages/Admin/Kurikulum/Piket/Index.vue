<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    tanggal: String,
    jurnal: Array,
    buku_tamu: Array,
    izin_keluar: Array
});

const changeDate = (e) => {
    window.location.href = route('admin.kurikulum.piket.index') + '?tanggal=' + e.target.value;
};
</script>

<template>
    <Head title="Rekapitulasi Piket" />

    <DashboardLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <!-- Header -->
            <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        Rekapitulasi Piket
                    </h1>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Pantau pengisian jurnal, buku tamu, dan izin keluar oleh guru piket.
                    </p>
                </div>
                
                <div class="flex items-center gap-3">
                    <input type="date" :value="tanggal" @change="changeDate" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-amber-500 focus:border-amber-500 block p-2.5 dark:bg-gray-800 dark:border-gray-600 dark:text-white font-bold shadow-sm">
                    <Link :href="route('admin.kurikulum.piket.jadwal')" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow-sm transition-colors flex items-center gap-2">
                        <i class="fas fa-calendar-alt"></i> Atur Jadwal
                    </Link>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-50 dark:bg-emerald-900/20 rounded-bl-full -mr-16 -mt-16 transition-transform group-hover:scale-110 duration-500"></div>
                    <div class="flex items-start justify-between relative z-10">
                        <div>
                            <p class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Total Jurnal Piket</p>
                            <h3 class="text-4xl font-black text-gray-900 dark:text-white">{{ jurnal.length }}</h3>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl shadow-inner">
                            <i class="fas fa-book-open"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 dark:bg-blue-900/20 rounded-bl-full -mr-16 -mt-16 transition-transform group-hover:scale-110 duration-500"></div>
                    <div class="flex items-start justify-between relative z-10">
                        <div>
                            <p class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Total Tamu</p>
                            <h3 class="text-4xl font-black text-gray-900 dark:text-white">{{ buku_tamu.length }}</h3>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xl shadow-inner">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-rose-50 dark:bg-rose-900/20 rounded-bl-full -mr-16 -mt-16 transition-transform group-hover:scale-110 duration-500"></div>
                    <div class="flex items-start justify-between relative z-10">
                        <div>
                            <p class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Izin Keluar</p>
                            <h3 class="text-4xl font-black text-gray-900 dark:text-white">{{ izin_keluar.length }}</h3>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xl shadow-inner">
                            <i class="fas fa-running"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Jurnal Section -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden mb-8">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white"><i class="fas fa-book-open text-emerald-500 mr-2"></i> Laporan Jurnal Piket</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 border-b border-gray-200 dark:border-gray-600">
                            <tr>
                                <th class="px-6 py-4 font-bold">Guru Piket</th>
                                <th class="px-6 py-4 font-bold">Waktu Pengisian</th>
                                <th class="px-6 py-4 font-bold">Keterangan Singkat</th>
                                <th class="px-6 py-4 font-bold">Detail Laporan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="jurnal.length === 0">
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">Belum ada jurnal piket diisi pada tanggal ini.</td>
                            </tr>
                            <tr v-for="j in jurnal" :key="j.id" class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600/50">
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                                    {{ j.nama_guru }}
                                    <div v-if="j.guru_pengganti_id" class="text-xs text-rose-500 mt-1"><i class="fas fa-exchange-alt"></i> Digantikan: {{ j.nama_pengganti }}</div>
                                </td>
                                <td class="px-6 py-4">{{ new Date(j.created_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'}) }} WIB</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs font-bold">{{ j.keterangan || '-' }}</span>
                                </td>
                                <td class="px-6 py-4 max-w-xs truncate">{{ j.tugas || '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Grid 2 Columns for Buku Tamu & Izin Keluar -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Buku Tamu -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white"><i class="fas fa-users text-blue-500 mr-2"></i> Buku Tamu</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 border-b border-gray-200 dark:border-gray-600">
                                <tr>
                                    <th class="px-6 py-4 font-bold">Identitas Tamu</th>
                                    <th class="px-6 py-4 font-bold">Waktu</th>
                                    <th class="px-6 py-4 font-bold">Keperluan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="buku_tamu.length === 0">
                                    <td colspan="3" class="px-6 py-8 text-center text-gray-500">Tidak ada tamu terdaftar.</td>
                                </tr>
                                <tr v-for="t in buku_tamu" :key="t.id" class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600/50">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900 dark:text-white">{{ t.nama_lengkap }}</div>
                                        <div class="text-xs">{{ t.instansi_asal || '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded font-bold text-xs">{{ t.jam_datang.substring(0,5) }}</span> - 
                                        <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded font-bold text-xs">{{ t.jam_pulang ? t.jam_pulang.substring(0,5) : 'Belum' }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm">{{ t.keperluan }}</div>
                                        <div class="text-xs text-gray-400 mt-1">Bertemu: <span class="font-bold">{{ t.bertemu_dengan }}</span></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Izin Keluar -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white"><i class="fas fa-running text-rose-500 mr-2"></i> Izin Keluar Siswa</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 border-b border-gray-200 dark:border-gray-600">
                                <tr>
                                    <th class="px-6 py-4 font-bold">Siswa</th>
                                    <th class="px-6 py-4 font-bold">Waktu Keluar</th>
                                    <th class="px-6 py-4 font-bold">Alasan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="izin_keluar.length === 0">
                                    <td colspan="3" class="px-6 py-8 text-center text-gray-500">Tidak ada data izin keluar.</td>
                                </tr>
                                <tr v-for="i in izin_keluar" :key="i.id" class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600/50">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900 dark:text-white">{{ i.nama_lengkap }}</div>
                                        <div class="text-xs">{{ i.nama_kelas }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 bg-rose-50 text-rose-700 rounded font-bold text-xs">{{ new Date(i.waktu_keluar).toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'}) }} WIB</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        {{ i.alasan }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </DashboardLayout>
</template>
