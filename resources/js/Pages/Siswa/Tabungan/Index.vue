<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    siswa: Object,
    rekening: Object,
    mutasi: Array,
    tagihan: Array,
    rekeningLain: Array
});

const isModalOpen = ref(false);

const form = useForm({
    id_tujuan: '',
    nominal: '',
    keterangan: '',
    pin: ''
});

const openTransferModal = () => {
    form.reset();
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submitTransfer = () => {
    form.post(route('siswa.tabungan.transfer'), {
        onSuccess: () => closeModal()
    });
};

const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
};
</script>

<template>
    <Head title="Tabungan - Mobile Banking" />

    <DashboardLayout>
        <div class="py-8">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div v-if="$page.props.flash?.message" class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-center text-emerald-800 shadow-sm animate-fade-in-down mb-6">
                    <i class="fas fa-check-circle text-emerald-600 mr-3 text-xl"></i>
                    <p class="font-bold">{{ $page.props.flash.message }}</p>
                </div>
                <div v-if="$page.props.errors?.error" class="bg-rose-50 border border-rose-200 rounded-2xl p-4 flex items-center text-rose-800 shadow-sm animate-fade-in-down mb-6">
                    <i class="fas fa-exclamation-circle text-rose-600 mr-3 text-xl"></i>
                    <p class="font-bold">{{ $page.props.errors.error }}</p>
                </div>

                <div v-if="!rekening" class="bg-white rounded-3xl p-8 text-center shadow-sm border border-gray-100">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-100">
                        <i class="fas fa-wallet text-3xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-black text-gray-900 mb-2">Belum Ada Rekening</h3>
                    <p class="text-gray-500">Silakan hubungi pihak tata usaha / bendahara sekolah untuk membuka rekening tabungan Anda.</p>
                </div>

                <template v-else>
                    <!-- Virtual Card (Mobile Banking Style) -->
                    <div class="bg-gradient-to-tr from-slate-900 via-indigo-900 to-slate-800 rounded-3xl p-8 shadow-2xl relative overflow-hidden text-white border border-slate-700">
                        <!-- Holographic Effects -->
                        <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl mix-blend-screen"></div>
                        <div class="absolute bottom-0 left-0 w-48 h-48 bg-rose-500/10 rounded-full blur-3xl mix-blend-screen"></div>
                        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-10 mix-blend-overlay"></div>

                        <!-- Card Header -->
                        <div class="flex justify-between items-start relative z-10 mb-8">
                            <div>
                                <h1 class="text-sm font-bold tracking-widest text-slate-300 uppercase">Bank Mini Siakad</h1>
                                <p class="text-[10px] text-indigo-300 tracking-widest">SMART STUDENT SAVING</p>
                            </div>
                            <div class="bg-white/10 backdrop-blur-md px-3 py-1 rounded-full border border-white/20 flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                                <span class="text-xs font-bold text-white uppercase tracking-wider">{{ rekening.status_rekening }}</span>
                            </div>
                        </div>

                        <!-- Balance & Chip -->
                        <div class="flex justify-between items-end relative z-10 mb-8">
                            <div>
                                <p class="text-sm text-slate-300 font-medium mb-1">Saldo Tersedia</p>
                                <h2 class="text-4xl sm:text-5xl font-black tracking-tighter drop-shadow-md">
                                    {{ formatRupiah(rekening.saldo) }}
                                </h2>
                            </div>
                            <div class="w-12 h-10 bg-gradient-to-br from-yellow-200 to-yellow-500 rounded-md opacity-80 shadow-inner flex items-center justify-center overflow-hidden">
                                <div class="w-full h-px bg-yellow-600/50 absolute top-1/3"></div>
                                <div class="w-full h-px bg-yellow-600/50 absolute bottom-1/3"></div>
                                <div class="w-px h-full bg-yellow-600/50 absolute left-1/3"></div>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="flex justify-between items-end relative z-10 pt-6 border-t border-white/10">
                            <div>
                                <p class="text-[10px] text-slate-400 uppercase tracking-widest mb-1">Nama Nasabah</p>
                                <p class="text-sm font-bold tracking-wider uppercase">{{ siswa.nama_lengkap }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] text-slate-400 uppercase tracking-widest mb-1">NISN / No. Rekening</p>
                                <p class="text-sm font-mono tracking-widest font-bold">{{ siswa.nisn }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons Menu -->
                    <div class="grid grid-cols-2 gap-4 my-6">
                        <button @click="openTransferModal" class="bg-white rounded-3xl p-4 shadow-sm border border-gray-100 flex flex-col items-center justify-center gap-3 hover:bg-indigo-50 hover:border-indigo-100 transition-all group">
                            <div class="w-12 h-12 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl group-hover:scale-110 group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-sm">
                                <i class="fas fa-paper-plane"></i>
                            </div>
                            <span class="text-sm font-bold text-gray-800">Transfer</span>
                        </button>
                        <button class="bg-white rounded-3xl p-4 shadow-sm border border-gray-100 flex flex-col items-center justify-center gap-3 hover:bg-amber-50 hover:border-amber-100 transition-all group opacity-50 cursor-not-allowed">
                            <div class="w-12 h-12 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center text-xl shadow-sm">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                            <span class="text-sm font-bold text-gray-800">Bayar SPP <br><span class="text-[9px] font-normal">(Segera Hadir)</span></span>
                        </button>
                    </div>

                    <!-- Riwayat Transaksi -->
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mt-6">
                        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                            <h3 class="font-black text-gray-900 text-lg">Histori Transaksi</h3>
                        </div>
                        <div class="p-2">
                            <div v-for="mut in mutasi" :key="mut.id" class="p-4 hover:bg-gray-50 rounded-2xl transition-colors border-b border-gray-50 last:border-0 flex items-center gap-4">
                                <div :class="[
                                    'w-12 h-12 rounded-full flex items-center justify-center shrink-0 border shadow-sm',
                                    mut.jenis_transaksi === 'Setor' || mut.jenis_transaksi === 'Transfer_Masuk' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100'
                                ]">
                                    <i :class="mut.jenis_transaksi === 'Setor' || mut.jenis_transaksi === 'Transfer_Masuk' ? 'fas fa-arrow-down' : 'fas fa-arrow-up'"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-gray-900 text-sm truncate">{{ mut.keterangan }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ new Date(mut.created_at).toLocaleString('id-ID') }}</p>
                                </div>
                                <div class="text-right">
                                    <p :class="[
                                        'font-black text-sm whitespace-nowrap',
                                        mut.jenis_transaksi === 'Setor' || mut.jenis_transaksi === 'Transfer_Masuk' ? 'text-emerald-600' : 'text-rose-600'
                                    ]">
                                        {{ mut.jenis_transaksi === 'Setor' || mut.jenis_transaksi === 'Transfer_Masuk' ? '+' : '-' }}{{ formatRupiah(mut.nominal) }}
                                    </p>
                                    <p class="text-[10px] font-medium text-gray-400 mt-1">Saldo: {{ formatRupiah(mut.saldo_setelah_transaksi) }}</p>
                                </div>
                            </div>
                            <div v-if="mutasi.length === 0" class="p-8 text-center text-gray-500">
                                Belum ada riwayat transaksi.
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Transfer Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 sm:p-0">
            <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm" @click="closeModal"></div>
            <div class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-md relative z-10 overflow-hidden shadow-2xl">
                <div class="p-6 border-b border-gray-100 text-center relative">
                    <div class="absolute top-6 left-6 cursor-pointer text-gray-400 hover:text-gray-700" @click="closeModal">
                        <i class="fas fa-times text-lg"></i>
                    </div>
                    <h3 class="text-lg font-black text-gray-900">Transfer Saldo</h3>
                </div>
                <div class="p-6 space-y-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Rekening Tujuan</label>
                        <select v-model="form.id_tujuan" class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 font-medium">
                            <option value="">-- Pilih Penerima --</option>
                            <option v-for="rekLain in rekeningLain" :key="rekLain.id" :value="rekLain.id">
                                {{ rekLain.siswa?.nama_lengkap }} ({{ rekLain.siswa?.nisn }})
                            </option>
                        </select>
                        <div v-if="form.errors.id_tujuan" class="text-red-500 text-xs mt-1">{{ form.errors.id_tujuan }}</div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Nominal Transfer (Rp)</label>
                        <input type="number" v-model="form.nominal" class="w-full rounded-xl border-gray-200 text-xl font-black text-center focus:ring-indigo-500 py-3" placeholder="0">
                        <p class="text-[10px] text-gray-500 text-center mt-2">Saldo Anda: {{ formatRupiah(rekening?.saldo || 0) }}</p>
                        <div v-if="form.errors.nominal" class="text-red-500 text-xs mt-1">{{ form.errors.nominal }}</div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Keterangan / Berita (Opsional)</label>
                        <input type="text" v-model="form.keterangan" class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">PIN Keamanan Tabungan</label>
                        <input type="password" v-model="form.pin" class="w-full rounded-xl border-gray-200 text-center tracking-[0.5em] font-black text-xl focus:ring-indigo-500" placeholder="••••••" maxlength="6">
                        <div v-if="form.errors.pin" class="text-red-500 text-xs mt-1">{{ form.errors.pin }}</div>
                    </div>
                </div>
                <div class="p-6 pt-0">
                    <button @click="submitTransfer" class="w-full py-3.5 rounded-2xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-xl shadow-indigo-600/20 disabled:opacity-50 transition-colors" :disabled="form.processing">
                        <i v-if="form.processing" class="fas fa-spinner fa-spin mr-2"></i> {{ form.processing ? 'Memproses...' : 'Kirim Uang Sekarang' }}
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
