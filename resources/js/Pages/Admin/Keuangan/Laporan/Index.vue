<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    filter: String,
    start: String,
    end: String,
    transaksi: Array,
    pengeluaran: Array,
    total_masuk: Number,
    total_keluar: Number,
    saldo_akhir: Number,
    rekap: Array
});

const filterType = ref(props.filter || 'harian');
const startDate = ref(props.start || '');
const endDate = ref(props.end || '');

const applyFilter = () => {
    router.get('/admin/keuangan/laporan', {
        filter: filterType.value,
        start: startDate.value,
        end: endDate.value
    }, { preserveState: true, replace: true });
};

watch(filterType, (newVal) => {
    if (newVal !== 'custom') {
        startDate.value = '';
        endDate.value = '';
        applyFilter();
    }
});

const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(number || 0);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric'
    });
};

const exportExcel = () => {
    window.location.href = `/admin/keuangan/laporan/export?start=${props.start}&end=${props.end}`;
};

const cetakTransaksi = () => {
    window.open(`/admin/keuangan/laporan/cetak-transaksi?start=${props.start}&end=${props.end}`, '_blank');
};

const cetakTunggakan = () => {
    window.open(`/admin/keuangan/laporan/cetak-tunggakan`, '_blank');
};
</script>

<template>
    <Head title="Laporan Keuangan" />

    <DashboardLayout>
        <div class="flex flex-col h-full bg-[#f4f6f8] dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full py-6">
                
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
                    <div>
                        <h2 class="font-bold text-2xl text-gray-900 dark:text-white tracking-tight">Laporan Keuangan</h2>
                        <p class="text-sm text-gray-500 mt-1">Ringkasan arus kas masuk, keluar, dan tunggakan siswa.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <select v-model="filterType" class="border-gray-300 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                            <option value="harian">Hari Ini</option>
                            <option value="mingguan">Minggu Ini</option>
                            <option value="bulanan">Bulan Ini</option>
                            <option value="tahunan">Tahun Ini</option>
                            <option value="custom">Rentang Tanggal</option>
                        </select>

                        <template v-if="filterType === 'custom'">
                            <input type="date" v-model="startDate" class="border-gray-300 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                            <span class="text-gray-500">s/d</span>
                            <input type="date" v-model="endDate" class="border-gray-300 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                            <button @click="applyFilter" class="bg-emerald-600 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-emerald-700">Filter</button>
                        </template>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Pemasukan -->
                    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-xl shadow-gray-200/50 border border-gray-100 flex items-center gap-5 relative overflow-hidden">
                        <div class="absolute right-0 top-0 bottom-0 opacity-5 pointer-events-none">
                            <i class="fas fa-arrow-down text-8xl -mr-4 -mt-2"></i>
                        </div>
                        <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl shadow-sm">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Pemasukan</p>
                            <h3 class="text-2xl font-black text-gray-900 dark:text-white">{{ formatRupiah(total_masuk) }}</h3>
                        </div>
                    </div>

                    <!-- Pengeluaran -->
                    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-xl shadow-gray-200/50 border border-gray-100 flex items-center gap-5 relative overflow-hidden">
                        <div class="absolute right-0 top-0 bottom-0 opacity-5 pointer-events-none">
                            <i class="fas fa-arrow-up text-8xl -mr-4 -mt-2"></i>
                        </div>
                        <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-2xl shadow-sm">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Pengeluaran</p>
                            <h3 class="text-2xl font-black text-gray-900 dark:text-white">{{ formatRupiah(total_keluar) }}</h3>
                        </div>
                    </div>

                    <!-- Saldo -->
                    <div class="bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-3xl p-6 shadow-xl shadow-indigo-500/30 text-white flex items-center gap-5 relative overflow-hidden">
                        <div class="absolute right-0 top-0 bottom-0 opacity-10 pointer-events-none">
                            <i class="fas fa-wallet text-8xl -mr-4 -mt-2"></i>
                        </div>
                        <div class="w-14 h-14 rounded-2xl bg-white/20 text-white flex items-center justify-center text-2xl backdrop-blur-sm">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-indigo-100">Saldo Akhir</p>
                            <h3 class="text-2xl font-black">{{ formatRupiah(saldo_akhir) }}</h3>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 mb-6">
                    <button @click="exportExcel" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-xl text-sm transition-all shadow-md flex items-center gap-2">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                    <button @click="cetakTransaksi" class="bg-gray-800 hover:bg-gray-900 text-white font-bold py-2 px-4 rounded-xl text-sm transition-all shadow-md flex items-center gap-2">
                        <i class="fas fa-print"></i> Cetak PDF Laporan
                    </button>
                    <button @click="cetakTunggakan" class="bg-rose-600 hover:bg-rose-700 text-white font-bold py-2 px-4 rounded-xl text-sm transition-all shadow-md flex items-center gap-2">
                        <i class="fas fa-file-pdf"></i> Cetak Rekap Tunggakan
                    </button>
                </div>

                <!-- Tabel Rekap Tunggakan -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden mb-8">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                        <i class="fas fa-chart-pie text-gray-500"></i>
                        <h3 class="font-bold text-gray-900">Rekapitulasi Tunggakan per Jenis Pembayaran</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-white dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wider border-b border-gray-200">
                                    <th class="px-6 py-4 font-bold">Jenis Pembayaran</th>
                                    <th class="px-6 py-4 font-bold text-center">Siswa Lunas</th>
                                    <th class="px-6 py-4 font-bold text-center">Siswa Nunggak</th>
                                    <th class="px-6 py-4 font-bold text-right">Potensi (Rp)</th>
                                    <th class="px-6 py-4 font-bold text-right">Masuk (Rp)</th>
                                    <th class="px-6 py-4 font-bold text-right">Tunggakan (Rp)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-for="r in rekap" :key="r.id" class="hover:bg-gray-50/80 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900 text-sm">{{ r.pos_bayar?.nama_pos || 'N/A' }}</div>
                                        <div class="text-xs text-gray-500">{{ r.tahun_ajaran?.tahun_ajaran || 'Semua Tahun' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold text-emerald-600">{{ r.siswa_lunas }}</td>
                                    <td class="px-6 py-4 text-center font-bold text-rose-600">{{ r.siswa_belum_lunas }}</td>
                                    <td class="px-6 py-4 text-right text-sm">{{ formatRupiah(r.total_potensi_rupiah) }}</td>
                                    <td class="px-6 py-4 text-right font-bold text-emerald-600 text-sm">{{ formatRupiah(r.uang_masuk) }}</td>
                                    <td class="px-6 py-4 text-right font-bold text-rose-600 text-sm">{{ formatRupiah(r.uang_tunggakan) }}</td>
                                </tr>
                                <tr v-if="rekap.length === 0">
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">Tidak ada data rekap.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </DashboardLayout>
</template>
