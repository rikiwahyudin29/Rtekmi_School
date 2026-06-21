<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    rekening: Object,
    mutasi: Array,
    tagihan: Array,
    rekeningLain: Array
});

const isModalOpen = ref(false);
const transaksiType = ref('');

const form = useForm({
    rekening_id: props.rekening.id,
    jenis_transaksi: '',
    nominal: '',
    keterangan: '',
    id_tagihan: '',
    rekening_tujuan: ''
});

const openModal = (type) => {
    transaksiType.value = type;
    form.jenis_transaksi = type;
    form.nominal = '';
    form.keterangan = '';
    form.id_tagihan = '';
    form.rekening_tujuan = '';
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset('nominal', 'keterangan', 'id_tagihan', 'rekening_tujuan');
};

const submitForm = () => {
    form.post(route('admin.keuangan.tabungan.transaksi'), {
        onSuccess: () => closeModal()
    });
};

const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
};
</script>

<template>
    <Head :title="'Buku Tabungan - ' + rekening.siswa?.nama_lengkap" />

    <DashboardLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="flex items-center gap-4 mb-4">
                    <Link :href="route('admin.keuangan.tabungan.index')" class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm border border-gray-200 text-gray-500 hover:text-indigo-600 hover:border-indigo-200 transition-colors">
                        <i class="fas fa-arrow-left"></i>
                    </Link>
                    <h2 class="text-2xl font-black text-gray-800 tracking-tight">Buku Tabungan Digital</h2>
                </div>

                <div v-if="$page.props.flash?.message" class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-center text-emerald-800 shadow-sm animate-fade-in-down">
                    <i class="fas fa-check-circle text-emerald-600 mr-3 text-xl"></i>
                    <p class="font-bold">{{ $page.props.flash.message }}</p>
                </div>
                <div v-if="$page.props.errors?.error" class="bg-red-50 border border-red-200 rounded-2xl p-4 flex items-center text-red-800 shadow-sm animate-fade-in-down">
                    <i class="fas fa-exclamation-triangle text-red-600 mr-3 text-xl"></i>
                    <p class="font-bold">{{ $page.props.errors.error }}</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Kartu Info -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Kartu Saldo -->
                        <div class="bg-gradient-to-br from-indigo-600 to-blue-800 rounded-3xl p-6 shadow-xl relative overflow-hidden text-white">
                            <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                            <div class="flex justify-between items-center mb-6 relative z-10">
                                <div>
                                    <p class="text-[10px] font-bold text-indigo-200 uppercase tracking-widest">Nasabah Aktif</p>
                                    <p class="font-black text-lg truncate w-48">{{ rekening.siswa?.nama_lengkap }}</p>
                                </div>
                                <i class="fas fa-wallet text-3xl opacity-50"></i>
                            </div>
                            <div class="relative z-10 mb-6">
                                <p class="text-xs text-indigo-200 font-medium mb-1">Saldo Tersedia</p>
                                <h3 class="text-4xl font-black tracking-tighter">{{ formatRupiah(rekening.saldo) }}</h3>
                            </div>
                            <div class="flex gap-2 relative z-10 pt-4 border-t border-white/20">
                                <div class="flex-1">
                                    <p class="text-[9px] text-indigo-200 uppercase tracking-widest">NISN</p>
                                    <p class="font-bold text-sm">{{ rekening.siswa?.nisn }}</p>
                                </div>
                                <div class="flex-1 text-right">
                                    <p class="text-[9px] text-indigo-200 uppercase tracking-widest">Kelas</p>
                                    <p class="font-bold text-sm">{{ rekening.siswa?.kelas?.nama_kelas || '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 space-y-3">
                            <h4 class="text-sm font-black text-gray-800 uppercase tracking-wider mb-4">Proses Transaksi</h4>
                            <button @click="openModal('Setor')" class="w-full py-3.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold rounded-2xl transition-colors flex items-center justify-center border border-emerald-100">
                                <i class="fas fa-arrow-down mr-2"></i> Setor Tunai
                            </button>
                            <button @click="openModal('Tarik')" class="w-full py-3.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold rounded-2xl transition-colors flex items-center justify-center border border-rose-100">
                                <i class="fas fa-arrow-up mr-2"></i> Tarik Tunai
                            </button>
                            <button @click="openModal('Transfer')" class="w-full py-3.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-2xl transition-colors flex items-center justify-center border border-indigo-100">
                                <i class="fas fa-exchange-alt mr-2"></i> Transfer Saldo
                            </button>
                            <button v-if="tagihan.length > 0" @click="openModal('Bayar_Sekolah')" class="w-full py-3.5 bg-amber-50 hover:bg-amber-100 text-amber-700 font-bold rounded-2xl transition-colors flex items-center justify-center border border-amber-100 mt-2">
                                <i class="fas fa-file-invoice-dollar mr-2"></i> Bayar Tagihan ({{ tagihan.length }})
                            </button>
                        </div>
                    </div>

                    <!-- Mutasi -->
                    <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col h-[700px]">
                        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="font-black text-gray-800 text-lg">Riwayat Mutasi Transaksi</h3>
                        </div>
                        <div class="p-0 overflow-y-auto flex-1">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-gray-500 uppercase bg-white border-b border-gray-100 font-bold sticky top-0 z-10 shadow-sm">
                                    <tr>
                                        <th class="px-6 py-4">Tanggal & Sandi</th>
                                        <th class="px-6 py-4">Keterangan</th>
                                        <th class="px-6 py-4 text-right">Nominal</th>
                                        <th class="px-6 py-4 text-right">Saldo Akhir</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="mut in mutasi" :key="mut.id" class="hover:bg-gray-50/80">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-gray-900">{{ new Date(mut.created_at).toLocaleString('id-ID') }}</div>
                                            <div class="text-[10px] font-bold mt-1 inline-flex px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 uppercase tracking-wider">{{ mut.jenis_transaksi }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600 text-xs font-medium">{{ mut.keterangan }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <div :class="[
                                                'font-black',
                                                mut.jenis_transaksi === 'Setor' || mut.jenis_transaksi === 'Transfer_Masuk' ? 'text-emerald-600' : 'text-rose-600'
                                            ]">
                                                {{ mut.jenis_transaksi === 'Setor' || mut.jenis_transaksi === 'Transfer_Masuk' ? '+' : '-' }}{{ formatRupiah(mut.nominal) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right font-bold text-gray-800">
                                            {{ formatRupiah(mut.saldo_setelah_transaksi) }}
                                        </td>
                                    </tr>
                                    <tr v-if="mutasi.length === 0">
                                        <td colspan="4" class="px-6 py-12 text-center text-gray-500 font-medium">Buku tabungan masih kosong. Belum ada mutasi.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Transaksi -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm" @click="closeModal"></div>
            <div class="bg-white rounded-3xl w-full max-w-md relative z-10 overflow-hidden shadow-2xl border border-white/20">
                <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex items-center gap-3">
                    <div :class="[
                        'w-10 h-10 rounded-full flex items-center justify-center text-white font-bold',
                        transaksiType === 'Setor' ? 'bg-emerald-500' :
                        transaksiType === 'Tarik' ? 'bg-rose-500' :
                        transaksiType === 'Transfer' ? 'bg-indigo-500' : 'bg-amber-500'
                    ]">
                        <i :class="[
                            transaksiType === 'Setor' ? 'fas fa-arrow-down' :
                            transaksiType === 'Tarik' ? 'fas fa-arrow-up' :
                            transaksiType === 'Transfer' ? 'fas fa-exchange-alt' : 'fas fa-file-invoice-dollar'
                        ]"></i>
                    </div>
                    <h3 class="text-xl font-black text-gray-900">
                        {{ transaksiType === 'Setor' ? 'Setor Tunai' :
                           transaksiType === 'Tarik' ? 'Tarik Tunai' :
                           transaksiType === 'Transfer' ? 'Transfer Antar Rekening' : 'Bayar Tagihan Sekolah' }}
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div v-if="transaksiType === 'Transfer'">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Rekening Tujuan</label>
                        <select v-model="form.rekening_tujuan" class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500">
                            <option value="">-- Pilih Siswa Tujuan --</option>
                            <option v-for="rekLain in rekeningLain" :key="rekLain.id" :value="rekLain.id">
                                {{ rekLain.siswa?.nama_lengkap }} ({{ rekLain.siswa?.nisn }})
                            </option>
                        </select>
                        <div v-if="form.errors.rekening_tujuan" class="text-red-500 text-xs mt-1">{{ form.errors.rekening_tujuan }}</div>
                    </div>

                    <div v-if="transaksiType === 'Bayar_Sekolah'">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Pilih Tagihan</label>
                        <select v-model="form.id_tagihan" class="w-full rounded-xl border-gray-200 text-sm focus:ring-amber-500">
                            <option value="">-- Pilih Tagihan yang Belum Lunas --</option>
                            <option v-for="tag in tagihan" :key="tag.id" :value="tag.id">
                                {{ tag.nama_pembayaran }} - Sisa: {{ formatRupiah(tag.nominal_tagihan - tag.nominal_terbayar) }}
                            </option>
                        </select>
                        <p class="text-[10px] text-gray-500 mt-1">Sisa tagihan akan dipotong dari saldo otomatis (bisa cicil parsial dengan mengisi nominal di bawah).</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nominal (Rp)</label>
                        <input type="number" v-model="form.nominal" class="w-full rounded-xl border-gray-200 font-black text-xl text-right focus:ring-indigo-500 py-3" placeholder="0">
                        <div v-if="form.errors.nominal" class="text-red-500 text-xs mt-1">{{ form.errors.nominal }}</div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Keterangan / Berita (Opsional)</label>
                        <input type="text" v-model="form.keterangan" class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500">
                    </div>
                </div>
                <div class="p-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                    <button @click="closeModal" class="px-5 py-2.5 rounded-xl text-sm font-bold text-gray-600 bg-white border border-gray-200 hover:bg-gray-50">Batal</button>
                    <button @click="submitForm" class="px-5 py-2.5 rounded-xl text-sm font-bold text-white shadow-md disabled:opacity-50 transition-colors"
                            :class="[
                                transaksiType === 'Setor' ? 'bg-emerald-600 hover:bg-emerald-700' :
                                transaksiType === 'Tarik' ? 'bg-rose-600 hover:bg-rose-700' :
                                transaksiType === 'Transfer' ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-amber-600 hover:bg-amber-700'
                            ]"
                            :disabled="form.processing">
                        {{ form.processing ? 'Memproses...' : 'Proses Transaksi' }}
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
