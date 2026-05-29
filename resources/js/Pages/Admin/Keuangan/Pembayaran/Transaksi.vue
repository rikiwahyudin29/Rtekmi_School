<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    siswa: Object,
    tagihan: Array,
    riwayat: Array,
});

const isBayarModalOpen = ref(false);
const selectedTagihan = ref(null);

const form = useForm({
    id_tagihan: '',
    id_siswa: props.siswa.id,
    jumlah_bayar: '',
});

const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(number);
};

const handleNominalInput = (e) => {
    let value = e.target.value.replace(/\D/g, '');
    if (value) {
        e.target.value = new Intl.NumberFormat('id-ID').format(value);
        form.jumlah_bayar = e.target.value;
    } else {
        form.jumlah_bayar = '';
    }
};

const openBayarModal = (t) => {
    selectedTagihan.value = t;
    form.id_tagihan = t.id;
    // Default isi dengan sisa tagihan
    const sisa = t.nominal_tagihan - t.nominal_terbayar;
    form.jumlah_bayar = new Intl.NumberFormat('id-ID').format(sisa);
    isBayarModalOpen.value = true;
};

const submitBayar = () => {
    form.post('/admin/keuangan/pembayaran/proses', {
        onSuccess: () => {
            isBayarModalOpen.value = false;
            form.reset();
        }
    });
};

const batalTrx = (id) => {
    if (confirm('Yakin ingin membatalkan transaksi ini? Saldo tagihan akan dikembalikan.')) {
        router.post('/admin/keuangan/pembayaran/batal', { id_transaksi: id }, {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Transaksi Pembayaran" />

    <DashboardLayout>
        <div class="flex flex-col h-full bg-[#f4f6f8] dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full py-6">
                
                <div class="flex items-center gap-4 mb-6">
                    <Link href="/admin/keuangan/pembayaran" class="w-10 h-10 bg-white border border-gray-200 rounded-xl flex items-center justify-center text-gray-500 hover:bg-gray-50 transition-colors shadow-sm">
                        <i class="fas fa-arrow-left"></i>
                    </Link>
                    <div>
                        <h2 class="font-bold text-2xl text-gray-900 dark:text-white tracking-tight">Transaksi Siswa</h2>
                        <p class="text-sm text-gray-500 mt-1">Selesaikan tagihan administrasi sekolah.</p>
                    </div>
                </div>

                <!-- Flash Message -->
                <div v-if="$page.props.flash?.message" class="bg-green-50 border border-green-200 text-green-800 p-4 mb-6 rounded-2xl shadow-sm flex items-center gap-3">
                    <i class="fas fa-check-circle"></i> {{ $page.props.flash.message }}
                </div>
                <div v-if="$page.props.flash?.error" class="bg-red-50 border border-red-200 text-red-800 p-4 mb-6 rounded-2xl shadow-sm flex items-center gap-3">
                    <i class="fas fa-exclamation-circle"></i> {{ $page.props.flash.error }}
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Kiri: Profil & List Tagihan -->
                    <div class="lg:col-span-2 space-y-6">
                        
                        <!-- Profil Siswa Card -->
                        <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-xl shadow-gray-200/50 border border-gray-100 flex items-center gap-6 relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-primary-50 rounded-bl-full -mr-8 -mt-8 z-0"></div>
                            
                            <img :src="`/uploads/siswa/${siswa.foto}`" @error="$event.target.src='https://ui-avatars.com/api/?background=random&name='+siswa.nama_lengkap" class="w-20 h-20 rounded-2xl object-cover border-4 border-white shadow-md relative z-10">
                            
                            <div class="relative z-10">
                                <h3 class="font-black text-xl text-gray-900">{{ siswa.nama_lengkap }}</h3>
                                <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-sm text-gray-500 font-medium">
                                    <span class="flex items-center gap-1"><i class="fas fa-id-badge text-gray-400"></i> NIS: {{ siswa.nis || '-' }}</span>
                                    <span class="flex items-center gap-1"><i class="fas fa-layer-group text-gray-400"></i> Kelas: <span class="text-primary-600 font-bold">{{ siswa.kelas?.nama_kelas || '-' }}</span></span>
                                </div>
                            </div>
                        </div>

                        <!-- Tagihan Card -->
                        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                                <h3 class="font-bold text-gray-900 flex items-center gap-2">
                                    <i class="fas fa-file-invoice text-primary-500"></i> Daftar Tagihan
                                </h3>
                            </div>
                            <div class="divide-y divide-gray-100 max-h-[500px] overflow-y-auto">
                                <div v-for="t in tagihan" :key="t.id" class="p-5 hover:bg-gray-50 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="font-bold text-gray-900">{{ t.jenis_bayar?.pos_bayar?.nama_pos }}</span>
                                            <span class="text-xs font-bold px-2 py-0.5 rounded-full"
                                                :class="{
                                                    'bg-red-100 text-red-700': t.status_bayar === 'BELUM',
                                                    'bg-amber-100 text-amber-700': t.status_bayar === 'CICIL',
                                                    'bg-green-100 text-green-700': t.status_bayar === 'LUNAS',
                                                }">{{ t.status_bayar }}</span>
                                        </div>
                                        <p class="text-sm text-gray-500">{{ t.keterangan }}</p>
                                        <div class="mt-2 flex items-center gap-4 text-sm">
                                            <div>Tarif: <span class="font-medium text-gray-700">{{ formatRupiah(t.nominal_tagihan) }}</span></div>
                                            <div>Dibayar: <span class="font-medium text-green-600">{{ formatRupiah(t.nominal_terbayar) }}</span></div>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0 text-right">
                                        <div class="font-black text-lg text-red-600 mb-2" v-if="t.status_bayar !== 'LUNAS'">
                                            Sisa: {{ formatRupiah(t.nominal_tagihan - t.nominal_terbayar) }}
                                        </div>
                                        <div class="font-black text-lg text-green-600 mb-2 flex items-center justify-end gap-1" v-else>
                                            <i class="fas fa-check-circle"></i> LUNAS
                                        </div>
                                        
                                        <button v-if="t.status_bayar !== 'LUNAS'" @click="openBayarModal(t)" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2 px-6 rounded-xl text-sm transition-all shadow-md shadow-primary-500/30">
                                            Bayar
                                        </button>
                                    </div>
                                </div>
                                <div v-if="tagihan.length === 0" class="p-8 text-center text-gray-500">
                                    Siswa ini tidak memiliki tagihan.
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Kanan: Riwayat Transaksi -->
                    <div class="space-y-6">
                        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden sticky top-24">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                                <h3 class="font-bold text-gray-900 flex items-center gap-2">
                                    <i class="fas fa-history text-gray-500"></i> Riwayat 10 Terakhir
                                </h3>
                            </div>
                            <div class="p-4 space-y-4">
                                <div v-for="trx in riwayat" :key="trx.id" class="border border-gray-100 rounded-2xl p-4 bg-white hover:shadow-md transition-shadow relative group">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <div class="text-xs font-bold text-gray-400 mb-1">{{ trx.kode_transaksi }}</div>
                                            <div class="font-bold text-gray-900 text-sm leading-tight">{{ trx.tagihan?.jenis_bayar?.pos_bayar?.nama_pos }} {{ trx.tagihan?.keterangan }}</div>
                                        </div>
                                        <span class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-0.5 rounded">{{ trx.status_transaksi }}</span>
                                    </div>
                                    <div class="flex justify-between items-end mt-3">
                                        <div class="text-xs text-gray-500">
                                            <i class="fas fa-calendar-alt w-3"></i> {{ new Date(trx.created_at).toLocaleDateString('id-ID') }}<br>
                                            <i class="fas fa-user-tag w-3 mt-1"></i> {{ trx.petugas?.nama_lengkap || 'System (Tripay)' }}
                                        </div>
                                        <div class="font-black text-green-600">
                                            + {{ formatRupiah(trx.jumlah_bayar) }}
                                        </div>
                                    </div>
                                    
                                    <!-- Aksi (Batal/Cetak) on Hover -->
                                    <div class="absolute inset-0 bg-white/90 backdrop-blur-sm rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2 z-10">
                                        <button class="bg-gray-100 text-gray-700 hover:bg-gray-200 w-10 h-10 rounded-xl flex items-center justify-center" title="Cetak Struk (On Development)">
                                            <i class="fas fa-print"></i>
                                        </button>
                                        <button v-if="trx.status_transaksi === 'SUCCESS' && (trx.payment_type === 'TUNAI' || !trx.payment_type)" @click="batalTrx(trx.id)" class="bg-red-50 text-red-600 hover:bg-red-100 w-10 h-10 rounded-xl flex items-center justify-center" title="Batalkan Transaksi">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </div>
                                </div>

                                <div v-if="riwayat.length === 0" class="text-center py-6 text-sm text-gray-500">
                                    Belum ada transaksi
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal Bayar -->
        <div v-if="isBayarModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden transform transition-all">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="font-bold text-lg text-gray-900 flex items-center gap-2">
                        <i class="fas fa-money-bill-wave text-green-500"></i> Proses Pembayaran
                    </h3>
                    <button @click="isBayarModalOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form @submit.prevent="submitBayar" class="p-6">
                    <div class="mb-5 text-center">
                        <p class="text-sm text-gray-500 font-medium mb-1">Tagihan:</p>
                        <p class="font-bold text-gray-900">{{ selectedTagihan?.jenis_bayar?.pos_bayar?.nama_pos }} {{ selectedTagihan?.keterangan }}</p>
                        <div class="mt-3 inline-block bg-red-50 border border-red-100 text-red-700 px-4 py-2 rounded-xl text-sm">
                            Sisa Tagihan: <span class="font-black">{{ formatRupiah(selectedTagihan?.nominal_tagihan - selectedTagihan?.nominal_terbayar) }}</span>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2 text-center">Jumlah Bayar (Rp)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-3.5 text-gray-500 font-bold text-lg">Rp</span>
                            <input type="text" v-model="form.jumlah_bayar" @input="handleNominalInput" required autofocus class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-2xl focus:ring-0 focus:border-green-500 text-2xl font-black text-center text-gray-900 transition-colors">
                        </div>
                        <div v-if="form.errors.jumlah_bayar" class="text-red-500 text-xs mt-1 text-center">{{ form.errors.jumlah_bayar }}</div>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" @click="isBayarModalOpen = false" class="flex-1 py-3 rounded-xl text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors">Batal</button>
                        <button type="submit" :disabled="form.processing" class="flex-1 py-3 rounded-xl text-sm font-bold text-white bg-green-500 hover:bg-green-600 transition-colors shadow-lg shadow-green-500/30 disabled:opacity-50">
                            Bayar Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </DashboardLayout>
</template>
