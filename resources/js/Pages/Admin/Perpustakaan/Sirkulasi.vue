<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    dipinjam: Array
});

const isModalOpen = ref(false);

const form = useForm({
    nis: '',
    kode_buku: ''
});

const openModal = () => {
    form.reset();
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submitForm = () => {
    form.post(route('admin.perpus.proses-pinjam'), {
        onSuccess: () => closeModal()
    });
};

const kembalikanBuku = (id_pinjam) => {
    if (confirm('Proses pengembalian buku? Jika terlambat denda akan dihitung otomatis.')) {
        router.post(route('admin.perpus.proses-kembali', id_pinjam));
    }
};

const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
};

// Hitung keterlambatan untuk tampilan badge (Real-time di frontend)
const getKeterlambatan = (tgl_kembali_seharusnya) => {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const deadline = new Date(tgl_kembali_seharusnya);
    deadline.setHours(0, 0, 0, 0);
    
    if (today > deadline) {
        const diffTime = Math.abs(today - deadline);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        return diffDays;
    }
    return 0;
};
</script>

<template>
    <Head title="Sirkulasi Peminjaman Buku" />

    <DashboardLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-3xl font-black text-gray-800 tracking-tight">Sirkulasi Perpustakaan</h2>
                        <p class="text-sm text-gray-500 mt-1 font-medium">Monitoring buku yang sedang dipinjam dan proses transaksi sirkulasi.</p>
                    </div>
                    <button @click="openModal" class="px-6 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-2xl shadow-xl shadow-indigo-600/20 transition-all flex items-center">
                        <i class="fas fa-barcode mr-3 text-lg"></i> Transaksi Peminjaman (Scan)
                    </button>
                </div>

                <div v-if="$page.props.flash?.message" class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-center text-emerald-800 shadow-sm animate-fade-in-down">
                    <i class="fas fa-check-circle text-emerald-600 mr-3 text-xl"></i>
                    <p class="font-bold">{{ $page.props.flash.message }}</p>
                </div>
                <div v-if="$page.props.errors?.error" class="bg-rose-50 border border-rose-200 rounded-2xl p-4 flex items-center text-rose-800 shadow-sm animate-fade-in-down">
                    <i class="fas fa-exclamation-triangle text-rose-600 mr-3 text-xl"></i>
                    <p class="font-bold">{{ $page.props.errors.error }}</p>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col min-h-[500px]">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <h3 class="font-black text-gray-800 text-lg">Buku Sedang Dipinjam <span class="bg-indigo-100 text-indigo-700 text-xs px-2 py-0.5 rounded-full ml-2">{{ dipinjam.length }} Data</span></h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-500 uppercase bg-white border-b border-gray-100 font-bold sticky top-0">
                                <tr>
                                    <th class="px-6 py-4">Peminjam</th>
                                    <th class="px-6 py-4">Buku & Barcode</th>
                                    <th class="px-6 py-4">Tgl Pinjam</th>
                                    <th class="px-6 py-4">Batas Kembali</th>
                                    <th class="px-6 py-4 text-center">Status / Telat</th>
                                    <th class="px-6 py-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="pin in dipinjam" :key="pin.id" class="hover:bg-gray-50/80 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">{{ pin.siswa?.nama_lengkap }}</div>
                                        <div class="text-xs text-gray-500 font-medium">NIS: {{ pin.siswa?.nis || pin.siswa?.nisn }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-black text-gray-800">{{ pin.buku?.judul }}</div>
                                        <div class="text-xs text-indigo-600 font-mono font-bold mt-1 tracking-widest">{{ pin.buku?.kode_buku }}</div>
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-600">{{ pin.tgl_pinjam }}</td>
                                    <td class="px-6 py-4 font-bold text-gray-900">{{ pin.tgl_kembali_seharusnya }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <template v-if="getKeterlambatan(pin.tgl_kembali_seharusnya) > 0">
                                            <div class="inline-flex flex-col items-center">
                                                <span class="px-2 py-1 rounded-md text-[10px] font-black uppercase tracking-wider bg-rose-100 text-rose-700 mb-1">Terlambat</span>
                                                <span class="text-xs font-bold text-rose-600">{{ getKeterlambatan(pin.tgl_kembali_seharusnya) }} Hari</span>
                                            </div>
                                        </template>
                                        <template v-else>
                                            <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-emerald-100 text-emerald-700">Aman</span>
                                        </template>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button @click="kembalikanBuku(pin.id)" class="px-4 py-2 bg-indigo-50 text-indigo-700 font-bold rounded-xl hover:bg-indigo-600 hover:text-white transition-colors text-xs whitespace-nowrap">
                                            <i class="fas fa-undo-alt mr-1"></i> Kembalikan
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="dipinjam.length === 0">
                                    <td colspan="6" class="px-6 py-16 text-center text-gray-500">
                                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                            <i class="fas fa-box-open text-2xl text-gray-400"></i>
                                        </div>
                                        <p class="font-medium text-base">Tidak ada peminjaman aktif.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Peminjaman (Sirkulasi Scan) -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm" @click="closeModal"></div>
            <div class="bg-white rounded-3xl w-full max-w-md relative z-10 overflow-hidden shadow-2xl">
                <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex items-center gap-3">
                    <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center shadow-inner">
                        <i class="fas fa-barcode text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-gray-900">Sirkulasi Peminjaman</h3>
                        <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest mt-0.5">Scan Kartu & Buku</p>
                    </div>
                </div>
                <div class="p-6 space-y-5">
                    <div class="relative">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2 ml-1">1. Barcode NIS Siswa</label>
                        <div class="relative flex items-center">
                            <i class="fas fa-id-card absolute left-4 text-gray-400 text-lg"></i>
                            <input type="text" v-model="form.nis" class="w-full pl-12 pr-4 py-3.5 rounded-2xl border-gray-200 focus:ring-indigo-500 font-mono tracking-widest text-lg font-bold bg-gray-50 focus:bg-white transition-colors" placeholder="Scan NIS...">
                        </div>
                        <div v-if="form.errors.nis" class="text-rose-500 text-xs mt-1.5 ml-1 font-bold">{{ form.errors.nis }}</div>
                    </div>
                    
                    <div class="flex justify-center my-2">
                        <i class="fas fa-link text-gray-300 text-xl"></i>
                    </div>

                    <div class="relative">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2 ml-1">2. Barcode Kode Buku</label>
                        <div class="relative flex items-center">
                            <i class="fas fa-book absolute left-4 text-gray-400 text-lg"></i>
                            <input type="text" v-model="form.kode_buku" @keyup.enter="submitForm" class="w-full pl-12 pr-4 py-3.5 rounded-2xl border-gray-200 focus:ring-indigo-500 font-mono tracking-widest text-lg font-bold bg-gray-50 focus:bg-white transition-colors" placeholder="Scan Kode Buku...">
                        </div>
                        <div v-if="form.errors.kode_buku" class="text-rose-500 text-xs mt-1.5 ml-1 font-bold">{{ form.errors.kode_buku }}</div>
                    </div>

                    <div class="bg-amber-50 p-3 rounded-xl border border-amber-100 flex items-start gap-3 mt-4">
                        <i class="fas fa-info-circle text-amber-500 mt-0.5"></i>
                        <p class="text-[10px] text-amber-700 font-medium leading-relaxed">Batas waktu peminjaman otomatis diset <b>7 Hari</b> dari tanggal sekarang. Denda keterlambatan adalah Rp 500/hari.</p>
                    </div>
                </div>
                <div class="p-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                    <button @click="closeModal" class="px-5 py-3 rounded-xl text-sm font-bold text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 transition-colors">Batal</button>
                    <button @click="submitForm" class="px-6 py-3 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-600/20 disabled:opacity-50 transition-all flex items-center" :disabled="form.processing">
                        {{ form.processing ? 'Memproses...' : 'Proses Peminjaman' }} <i class="fas fa-check-circle ml-2" v-if="!form.processing"></i>
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
