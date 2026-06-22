<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    tagihan: Array,
    riwayat_transaksi: Array,
    ringkasan: Object
});

const processingTagihan = ref(null);
const showModalBayar = ref(false);
const activeTagihan = ref(null);
const inputNominal = ref(0);

const openModalBayar = (tagihan) => {
    activeTagihan.value = tagihan;
    inputNominal.value = tagihan.nominal_tagihan - tagihan.nominal_terbayar;
    showModalBayar.value = true;
};

const bayarQris = () => {
    if (!activeTagihan.value) return;
    
    if (inputNominal.value < 10000) {
        alert('Minimum pembayaran adalah Rp 10.000');
        return;
    }
    
    const tagihanId = activeTagihan.value.id;
    processingTagihan.value = tagihanId;
    
    router.post(route('siswa.keuangan.bayar-qris'), {
        tagihan_id: tagihanId,
        nominal_bayar: inputNominal.value
    }, {
        onFinish: () => {
            processingTagihan.value = null;
            showModalBayar.value = false;
        }
    });
};

const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka || 0);
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
};
</script>

<template>
    <Head title="Keuangan & Tagihan" />

    <DashboardLayout>
        <div class="py-8">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Financial Overview Card -->
                <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-3xl p-8 shadow-lg relative overflow-hidden text-white border border-emerald-500">
                    <i class="fas fa-wallet text-white/10 text-9xl absolute -right-10 -bottom-10 pointer-events-none transform -rotate-12"></i>
                    
                    <div class="relative z-10 mb-8">
                        <h2 class="text-3xl font-black mb-2">Administrasi Keuangan</h2>
                        <p class="text-emerald-100 max-w-xl text-sm">
                            Pantau rincian tagihan sekolah dan riwayat pembayaran Anda. Transparansi keuangan untuk ketenangan belajar.
                        </p>
                    </div>

                    <div class="relative z-10 grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="bg-white/10 backdrop-blur-md rounded-2xl p-5 border border-white/20">
                            <p class="text-[10px] uppercase tracking-widest text-emerald-200 font-bold mb-1">Total Tagihan</p>
                            <h3 class="text-2xl font-black truncate">{{ formatRupiah(ringkasan.total_tagihan) }}</h3>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md rounded-2xl p-5 border border-white/20">
                            <p class="text-[10px] uppercase tracking-widest text-emerald-200 font-bold mb-1">Total Dibayar</p>
                            <h3 class="text-2xl font-black truncate">{{ formatRupiah(ringkasan.total_dibayar) }}</h3>
                        </div>
                        <div class="bg-white/20 backdrop-blur-md rounded-2xl p-5 border border-white/30 shadow-inner">
                            <p class="text-[10px] uppercase tracking-widest text-white font-bold mb-1">Sisa Tunggakan</p>
                            <h3 class="text-2xl font-black truncate text-yellow-300">{{ formatRupiah(ringkasan.sisa_tagihan) }}</h3>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Left Column: Daftar Tagihan -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                                <h3 class="font-black text-gray-900 text-lg">Daftar Tagihan Siswa</h3>
                                <span class="bg-indigo-100 text-indigo-800 text-xs font-bold px-3 py-1 rounded-full">
                                    {{ tagihan.length }} Item
                                </span>
                            </div>
                            
                            <div v-if="tagihan.length === 0" class="p-12 text-center text-gray-500">
                                <i class="fas fa-check-circle text-4xl text-emerald-400 mb-3"></i>
                                <p class="font-bold">Semua Lunas</p>
                                <p class="text-sm">Tidak ada tagihan yang harus dibayar saat ini.</p>
                            </div>

                            <div v-else class="divide-y divide-gray-100 p-2">
                                <div v-for="tag in tagihan" :key="tag.id" class="p-4 hover:bg-gray-50 rounded-2xl transition-colors border border-transparent hover:border-gray-100 mb-1 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                                    <div class="flex items-start gap-4">
                                        <div :class="[
                                            'w-12 h-12 rounded-full flex items-center justify-center shrink-0 border shadow-sm',
                                            tag.status_bayar === 'Lunas' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100'
                                        ]">
                                            <i :class="tag.status_bayar === 'Lunas' ? 'fas fa-check' : 'fas fa-file-invoice-dollar'"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-gray-900">{{ tag.jenis_bayar?.nama_pembayaran || 'Tagihan Lainnya' }}</h4>
                                            <p class="text-xs text-gray-500 mb-1">Bulan ke-{{ tag.bulan_ke || '-' }} | Keterangan: {{ tag.keterangan || '-' }}</p>
                                            
                                            <!-- Status Label -->
                                            <span :class="[
                                                'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider',
                                                tag.status_bayar === 'Lunas' ? 'bg-emerald-100 text-emerald-800' : 
                                                (tag.status_bayar === 'Belum Lunas' && tag.nominal_terbayar > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')
                                            ]">
                                                {{ tag.status_bayar === 'Belum Lunas' && tag.nominal_terbayar > 0 ? 'Menyicil' : tag.status_bayar }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="text-right sm:text-right mt-2 sm:mt-0 ml-16 sm:ml-0 bg-gray-50 sm:bg-transparent p-3 sm:p-0 rounded-lg flex flex-col sm:items-end justify-between">
                                        <div>
                                            <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Nominal Tagihan</p>
                                            <p class="font-black text-gray-900 mb-1">{{ formatRupiah(tag.nominal_tagihan) }}</p>
                                            <div class="flex items-center justify-between sm:justify-end gap-2 text-xs">
                                                <span class="text-emerald-600">Dibayar: {{ formatRupiah(tag.nominal_terbayar) }}</span>
                                                <span class="text-gray-300">|</span>
                                                <span class="text-rose-600 font-bold">Sisa: {{ formatRupiah(tag.nominal_tagihan - tag.nominal_terbayar) }}</span>
                                            </div>
                                        </div>
                                        <button v-if="tag.status_bayar !== 'LUNAS'" @click="openModalBayar(tag)" :disabled="processingTagihan === tag.id" class="mt-3 inline-flex items-center justify-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all shadow-sm focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1 disabled:opacity-50 disabled:cursor-not-allowed w-full sm:w-auto">
                                            <i class="fas fa-qrcode mr-2" v-if="processingTagihan !== tag.id"></i>
                                            <i class="fas fa-circle-notch fa-spin mr-2" v-else></i>
                                            {{ processingTagihan === tag.id ? 'Memproses...' : 'Bayar via QRIS' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Riwayat Pembayaran -->
                    <div class="space-y-6">
                        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="p-5 border-b border-gray-100 bg-gray-50/50">
                                <h3 class="font-black text-gray-900">Riwayat Pembayaran Terbaru</h3>
                            </div>
                            
                            <div v-if="riwayat_transaksi.length === 0" class="p-8 text-center text-gray-500">
                                <i class="fas fa-receipt text-3xl text-gray-300 mb-2"></i>
                                <p class="text-sm">Belum ada riwayat pembayaran.</p>
                            </div>

                            <div v-else class="divide-y divide-gray-100">
                                <div v-for="trx in riwayat_transaksi" :key="trx.id" class="p-4 hover:bg-gray-50 transition-colors">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <p class="font-bold text-sm text-gray-900">{{ trx.tagihan?.jenis_bayar?.nama_pembayaran || 'Pembayaran' }}</p>
                                            <p class="text-xs text-gray-500">{{ formatDate(trx.tanggal_bayar) }}</p>
                                        </div>
                                        <span class="font-black text-emerald-600 bg-emerald-50 px-2 py-1 rounded text-sm">
                                            + {{ formatRupiah(trx.total_bayar) }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center text-[10px] text-gray-500 font-medium">
                                        <span class="bg-gray-100 px-2 py-0.5 rounded text-gray-600">
                                            TRX-{{ trx.id }}
                                        </span>
                                        <span v-if="trx.petugas">
                                            <i class="fas fa-user-tie mr-1"></i> {{ trx.petugas.name }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-indigo-50 border border-indigo-100 rounded-3xl p-5 relative overflow-hidden mt-6">
                            <div class="absolute -right-6 -top-6 w-24 h-24 bg-indigo-100/50 rounded-full blur-xl mix-blend-multiply"></div>
                            <h4 class="font-bold text-indigo-900 mb-2 flex items-center">
                                <i class="fas fa-info-circle text-indigo-500 mr-2"></i> Info Pembayaran
                            </h4>
                            <p class="text-xs text-indigo-700 leading-relaxed relative z-10">
                                Pembayaran online kini telah tersedia menggunakan metode <strong>QRIS</strong>. Klik tombol <strong>Bayar via QRIS</strong> pada tagihan Anda, lalu scan kode QR yang muncul melalui aplikasi M-Banking atau dompet digital Anda (Gopay, OVO, Dana, dll). Pembayaran akan otomatis terverifikasi.
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        
        <!-- Modal Bayar -->
        <div v-if="showModalBayar" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="showModalBayar = false">
                    <div class="absolute inset-0 bg-gray-900 opacity-75 backdrop-blur-sm"></div>
                </div>

                <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-gray-100">
                    <div class="bg-white px-6 pt-6 pb-6">
                        <div class="flex items-center justify-between mb-5">
                            <h3 class="text-xl font-black text-gray-900">
                                Nominal Pembayaran
                            </h3>
                            <button @click="showModalBayar = false" class="text-gray-400 hover:text-rose-500 transition-colors w-8 h-8 flex items-center justify-center rounded-full bg-gray-50 hover:bg-rose-50">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        
                        <div class="mt-2 text-sm text-gray-500 mb-6">
                            Anda dapat mencicil pembayaran ini. Silakan tentukan nominal yang ingin dibayarkan. Minimal pembayaran adalah Rp 10.000.
                        </div>

                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 mb-6">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Sisa Tagihan</span>
                                <span class="text-sm font-black text-rose-600">{{ activeTagihan ? formatRupiah(activeTagihan.nominal_tagihan - activeTagihan.nominal_terbayar) : '0' }}</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wide">Nominal yang Dibayarkan (Rp)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <span class="text-gray-500 font-bold">Rp</span>
                                </div>
                                <input type="number" v-model="inputNominal" min="10000" :max="activeTagihan ? activeTagihan.nominal_tagihan - activeTagihan.nominal_terbayar : 0" class="pl-12 block w-full border-gray-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 sm:text-lg font-black text-gray-900 bg-white py-3 shadow-sm transition-shadow hover:shadow-md" placeholder="Contoh: 50000">
                            </div>
                            <p class="mt-2 text-[10px] text-gray-400 font-medium">Batas minimal Rp 10.000. Ketikkan angka tanpa titik.</p>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t border-gray-100">
                        <button type="button" class="w-full sm:w-auto inline-flex justify-center rounded-xl border border-gray-200 px-5 py-2.5 bg-white text-sm font-bold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 transition-colors shadow-sm" @click="showModalBayar = false">
                            Batal
                        </button>
                        <button type="button" class="w-full sm:w-auto inline-flex justify-center rounded-xl border border-transparent px-5 py-2.5 bg-emerald-600 text-sm font-bold text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors shadow-sm items-center disabled:opacity-50" @click="bayarQris" :disabled="processingTagihan === activeTagihan?.id || inputNominal < 10000 || inputNominal > (activeTagihan ? activeTagihan.nominal_tagihan - activeTagihan.nominal_terbayar : 0)">
                            <i class="fas fa-qrcode mr-2" v-if="processingTagihan !== activeTagihan?.id"></i>
                            <i class="fas fa-circle-notch fa-spin mr-2" v-else></i>
                            Lanjutkan ke QRIS
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </DashboardLayout>
</template>
