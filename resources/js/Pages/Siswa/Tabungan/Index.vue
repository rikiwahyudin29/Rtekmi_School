<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    siswa: Object,
    rekening: Object,
    mutasi: Array,
    tagihan: Array,
    rekeningLain: Array,
    sekolah: Object
});

const showTransferModal = ref(false);
const showBayarModal = ref(false);

const formTransfer = useForm({
    id_tujuan: '',
    nominal: '',
    keterangan: '',
    pin: ''
});

const formBayar = useForm({
    tagihan_id: '',
    nominal: '',
    pin: ''
});

const openTransferModal = () => {
    showTransferModal.value = true;
    formTransfer.reset();
};

const openBayarModal = () => {
    showBayarModal.value = true;
    formBayar.reset();
};

const submitTransfer = () => {
    formTransfer.post(route('siswa.tabungan.transfer'), {
        onSuccess: () => {
            showTransferModal.value = false;
            formTransfer.reset();
        }
    });
};

const submitBayar = () => {
    formBayar.post(route('siswa.tabungan.bayar-tagihan'), {
        onSuccess: () => {
            showBayarModal.value = false;
            formBayar.reset();
        }
    });
};

const selectedTagihan = computed(() => {
    if (!formBayar.tagihan_id) return null;
    return props.tagihan.find(t => t.id === formBayar.tagihan_id);
});

watch(() => formBayar.tagihan_id, (newVal) => {
    if (newVal) {
        const tag = props.tagihan.find(t => t.id === newVal);
        if (tag) {
            formBayar.nominal = tag.nominal_tagihan - tag.nominal_terbayar;
        }
    }
});

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
                    <!-- Premium Virtual Card (Black Card Style) -->
                    <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-black rounded-3xl p-8 shadow-2xl relative overflow-hidden text-white border border-gray-700 aspect-[1.6/1] flex flex-col justify-between">
                        <!-- Card Texture & Effects -->
                        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10 mix-blend-overlay"></div>
                        <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl mix-blend-screen pointer-events-none"></div>
                        <div class="absolute bottom-0 left-0 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl mix-blend-screen pointer-events-none"></div>
                        
                        <!-- Premium Lines -->
                        <div class="absolute top-1/2 left-0 w-full h-px bg-gradient-to-r from-transparent via-gray-600 to-transparent opacity-20 pointer-events-none"></div>
                        <div class="absolute top-1/3 left-0 w-full h-px bg-gradient-to-r from-transparent via-gray-500 to-transparent opacity-10 pointer-events-none"></div>

                        <!-- Card Header -->
                        <div class="flex justify-between items-start relative z-10">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg border border-indigo-400/30">
                                    <i class="fas fa-university text-white text-sm"></i>
                                </div>
                                <div>
                                    <h1 class="text-sm font-black tracking-widest text-gray-200 uppercase">{{ sekolah?.nama_sekolah || 'BANK MINI SIAKAD' }}</h1>
                                    <p class="text-[9px] text-gray-400 tracking-[0.2em] font-medium mt-0.5">SMART STUDENT SAVING</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <i class="fas fa-wifi text-gray-400 rotate-90 text-lg opacity-80"></i>
                                <div class="bg-black/40 backdrop-blur-md px-3 py-1 rounded-full border border-gray-700 flex items-center gap-2 shadow-inner">
                                    <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shadow-[0_0_8px_rgba(52,211,153,0.8)]"></div>
                                    <span class="text-[10px] font-bold text-gray-300 uppercase tracking-wider">{{ rekening.status_rekening }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Balance & Chip -->
                        <div class="relative z-10 flex flex-col justify-center grow py-4">
                            <!-- Premium Chip -->
                            <div class="w-12 h-9 bg-gradient-to-br from-yellow-200 via-yellow-400 to-yellow-600 rounded-md opacity-90 shadow-md flex items-center justify-center mb-6 relative overflow-hidden border border-yellow-500/50">
                                <!-- Chip Lines -->
                                <div class="absolute w-full h-[1px] bg-yellow-700/40 top-1/3"></div>
                                <div class="absolute w-full h-[1px] bg-yellow-700/40 bottom-1/3"></div>
                                <div class="absolute w-[1px] h-full bg-yellow-700/40 left-1/3"></div>
                                <div class="absolute w-[1px] h-full bg-yellow-700/40 right-1/3"></div>
                                <div class="absolute w-5 h-5 border border-yellow-700/30 rounded-full bg-yellow-300/20"></div>
                            </div>

                            <p class="text-[11px] text-gray-400 font-medium mb-1 uppercase tracking-widest">Saldo Tersedia</p>
                            <h2 class="text-4xl sm:text-5xl font-black tracking-tight text-white drop-shadow-lg" style="font-family: 'Courier New', Courier, monospace;">
                                {{ formatRupiah(rekening.saldo) }}
                            </h2>
                        </div>

                        <!-- Card Footer -->
                        <div class="flex justify-between items-end relative z-10">
                            <div>
                                <p class="text-[9px] text-gray-500 uppercase tracking-[0.15em] mb-1">Cardholder Name</p>
                                <p class="text-sm font-bold tracking-[0.1em] text-gray-200 uppercase drop-shadow-sm">{{ siswa.nama_lengkap }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[9px] text-gray-500 uppercase tracking-[0.15em] mb-1">Student ID / NISN</p>
                                <p class="text-base font-bold tracking-[0.2em] text-gray-300 drop-shadow-sm" style="font-family: 'Courier New', Courier, monospace;">
                                    {{ siswa.nisn?.toString().match(/.{1,4}/g)?.join(' ') || '0000 0000 0000' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons Menu -->
                    <div class="grid grid-cols-2 gap-4 my-6">
                        <button @click="openTransferModal" class="bg-white rounded-3xl p-4 shadow-sm border border-gray-100 flex flex-col items-center justify-center gap-3 hover:bg-indigo-50 hover:border-indigo-100 transition-all group">
                            <div class="w-12 h-12 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl group-hover:scale-110 group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-sm">
                                <i class="fas fa-paper-plane"></i>
                            </div>
                            <span class="text-sm font-bold text-gray-800">Transfer Saldo</span>
                        </button>
                        <button @click="openBayarModal" class="bg-white rounded-3xl p-4 shadow-sm border border-gray-100 flex flex-col items-center justify-center gap-3 hover:bg-emerald-50 hover:border-emerald-100 transition-all group">
                            <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl group-hover:scale-110 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-sm">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                            <span class="text-sm font-bold text-gray-800">Bayar SPP / Tagihan</span>
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
        <div v-if="showTransferModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 sm:p-0">
            <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm" @click="showTransferModal = false"></div>
            <div class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-md relative z-10 overflow-hidden shadow-2xl">
                <div class="p-6 border-b border-gray-100 text-center relative">
                    <div class="absolute top-6 left-6 cursor-pointer text-gray-400 hover:text-gray-700" @click="showTransferModal = false">
                        <i class="fas fa-times text-lg"></i>
                    </div>
                    <h3 class="text-lg font-black text-gray-900">Transfer Saldo</h3>
                </div>
                <div class="p-6 space-y-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Rekening Tujuan</label>
                        <select v-model="formTransfer.id_tujuan" class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 font-medium">
                            <option value="">-- Pilih Penerima --</option>
                            <option v-for="rekLain in rekeningLain" :key="rekLain.id" :value="rekLain.id">
                                {{ rekLain.siswa?.nama_lengkap }} ({{ rekLain.siswa?.nisn }})
                            </option>
                        </select>
                        <div v-if="formTransfer.errors.id_tujuan" class="text-red-500 text-xs mt-1">{{ formTransfer.errors.id_tujuan }}</div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Nominal Transfer (Rp)</label>
                        <input type="number" v-model="formTransfer.nominal" class="w-full rounded-xl border-gray-200 text-xl font-black text-center focus:ring-indigo-500 py-3" placeholder="0">
                        <p class="text-[10px] text-gray-500 text-center mt-2">Saldo Anda: {{ formatRupiah(rekening?.saldo || 0) }}</p>
                        <div v-if="formTransfer.errors.nominal" class="text-red-500 text-xs mt-1">{{ formTransfer.errors.nominal }}</div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Keterangan / Berita (Opsional)</label>
                        <input type="text" v-model="formTransfer.keterangan" class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">PIN Keamanan Tabungan</label>
                        <input type="password" v-model="formTransfer.pin" class="w-full rounded-xl border-gray-200 text-center tracking-[0.5em] font-black text-xl focus:ring-indigo-500" placeholder="••••••" maxlength="6">
                        <div v-if="formTransfer.errors.pin" class="text-red-500 text-xs mt-1">{{ formTransfer.errors.pin }}</div>
                    </div>
                </div>
                <div class="p-6 pt-0">
                    <button @click="submitTransfer" class="w-full py-3.5 rounded-2xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-xl shadow-indigo-600/20 disabled:opacity-50 transition-colors" :disabled="formTransfer.processing">
                        <i v-if="formTransfer.processing" class="fas fa-spinner fa-spin mr-2"></i> {{ formTransfer.processing ? 'Memproses...' : 'Kirim Uang Sekarang' }}
                    </button>
                </div>
            </div>
        </div>
        <!-- Modal Bayar SPP -->
        <div v-if="showBayarModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="showBayarModal = false">
                    <div class="absolute inset-0 bg-gray-900 opacity-75 backdrop-blur-sm"></div>
                </div>

                <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-gray-100">
                    <form @submit.prevent="submitBayar">
                        <div class="bg-white px-6 pt-6 pb-6">
                            <div class="flex items-center justify-between mb-5">
                                <h3 class="text-xl font-black text-gray-900 flex items-center">
                                    <i class="fas fa-file-invoice-dollar text-emerald-500 mr-3"></i> Bayar Tagihan
                                </h3>
                                <button type="button" @click="showBayarModal = false" class="text-gray-400 hover:text-rose-500 transition-colors w-8 h-8 flex items-center justify-center rounded-full bg-gray-50 hover:bg-rose-50">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>

                            <!-- Saldo Mini -->
                            <div class="bg-slate-900 rounded-2xl p-4 text-white mb-6 flex justify-between items-center relative overflow-hidden">
                                <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-20 mix-blend-overlay"></div>
                                <div class="relative z-10">
                                    <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Saldo Tersedia</p>
                                    <p class="font-black text-lg text-emerald-400">{{ formatRupiah(rekening.saldo) }}</p>
                                </div>
                                <i class="fas fa-wallet text-3xl text-white/10 relative z-10"></i>
                            </div>

                            <div v-if="tagihan.length === 0" class="text-center py-6 bg-gray-50 rounded-2xl border border-gray-100">
                                <i class="fas fa-check-circle text-4xl text-emerald-400 mb-3"></i>
                                <p class="text-gray-900 font-bold">Hebat!</p>
                                <p class="text-sm text-gray-500">Anda tidak memiliki tagihan yang belum lunas.</p>
                            </div>

                            <div v-else class="space-y-4">
                                <!-- Pilih Tagihan -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wide">Pilih Tagihan</label>
                                    <select v-model="formBayar.tagihan_id" class="block w-full border-gray-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm bg-gray-50 shadow-sm" required>
                                        <option value="" disabled>-- Pilih Tagihan --</option>
                                        <option v-for="tag in tagihan" :key="tag.id" :value="tag.id">
                                            {{ tag.jenis_bayar?.nama_pembayaran || 'Tagihan' }} - Sisa: {{ formatRupiah(tag.nominal_tagihan - tag.nominal_terbayar) }}
                                        </option>
                                    </select>
                                    <p v-if="formBayar.errors.tagihan_id" class="mt-1 text-xs text-rose-500">{{ formBayar.errors.tagihan_id }}</p>
                                </div>

                                <!-- Nominal Bayar -->
                                <div v-if="formBayar.tagihan_id">
                                    <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wide">Nominal Dibayarkan (Rp)</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <span class="text-gray-500 font-bold">Rp</span>
                                        </div>
                                        <input type="number" v-model="formBayar.nominal" min="1" :max="selectedTagihan ? selectedTagihan.nominal_tagihan - selectedTagihan.nominal_terbayar : 0" class="pl-12 block w-full border-gray-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 sm:text-lg font-black text-gray-900 shadow-sm transition-shadow hover:shadow-md" placeholder="10000" required>
                                    </div>
                                    <div class="mt-2 flex justify-between items-center">
                                        <p class="text-[10px] text-gray-400 font-medium">Bisa bayar penuh atau dicicil.</p>
                                        <button type="button" @click="formBayar.nominal = selectedTagihan ? selectedTagihan.nominal_tagihan - selectedTagihan.nominal_terbayar : 0" class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded hover:bg-emerald-100 transition-colors">Bayar Penuh</button>
                                    </div>
                                    <p v-if="formBayar.errors.nominal" class="mt-1 text-xs text-rose-500">{{ formBayar.errors.nominal }}</p>
                                </div>

                                <!-- PIN -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wide">PIN Tabungan</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <i class="fas fa-lock text-gray-400"></i>
                                        </div>
                                        <input type="password" v-model="formBayar.pin" maxlength="6" class="pl-10 block w-full border-gray-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 sm:text-lg font-black tracking-widest text-center shadow-sm transition-shadow hover:shadow-md" placeholder="••••••" required>
                                    </div>
                                    <p v-if="formBayar.errors.pin" class="mt-1 text-xs text-rose-500">{{ formBayar.errors.pin }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t border-gray-100">
                            <button type="button" class="w-full sm:w-auto inline-flex justify-center rounded-xl border border-gray-200 px-5 py-2.5 bg-white text-sm font-bold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 transition-colors shadow-sm" @click="showBayarModal = false">
                                Batal
                            </button>
                            <button type="submit" v-if="tagihan.length > 0" class="w-full sm:w-auto inline-flex justify-center rounded-xl border border-transparent px-5 py-2.5 bg-emerald-600 text-sm font-bold text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors shadow-sm items-center disabled:opacity-50" :disabled="formBayar.processing">
                                <i class="fas fa-circle-notch fa-spin mr-2" v-if="formBayar.processing"></i>
                                <i class="fas fa-check-circle mr-2" v-else></i>
                                Bayar Tagihan Sekarang
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </DashboardLayout>
</template>
