<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    pengeluaran: Array,
    jenis: Array,
    divisi: Array,
    bulan: [String, Number],
    tahun: [String, Number],
});

const filterBulan = ref(props.bulan);
const filterTahun = ref(props.tahun);
const isModalOpen = ref(false);

const form = useForm({
    id_divisi: '',
    id_jenis: '',
    judul_pengeluaran: '',
    nominal: '',
    tanggal: new Date().toISOString().split('T')[0],
    keterangan: '',
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
        form.nominal = e.target.value;
    } else {
        form.nominal = '';
    }
};

watch([filterBulan, filterTahun], () => {
    router.get('/admin/keuangan/pengeluaran', { bulan: filterBulan.value, tahun: filterTahun.value }, {
        preserveState: true,
        replace: true,
    });
});

const submit = () => {
    form.post('/admin/keuangan/pengeluaran', {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
            form.tanggal = new Date().toISOString().split('T')[0];
        }
    });
};

const hapus = (id) => {
    if (confirm('Yakin ingin menghapus catatan pengeluaran ini?')) {
        router.delete(`/admin/keuangan/pengeluaran/${id}`);
    }
};

// Calculate total
const totalPengeluaran = props.pengeluaran.reduce((sum, p) => sum + parseFloat(p.nominal), 0);

const tambahDivisi = () => {
    const nama = prompt('Masukkan Nama Divisi Baru (Cth: Kurikulum, Kesiswaan):');
    if (nama) {
        router.post('/admin/keuangan/pengeluaran/divisi', { nama_divisi: nama }, { preserveScroll: true });
    }
};

const tambahJenis = () => {
    const nama = prompt('Masukkan Kategori Pengeluaran Baru (Cth: ATK, Konsumsi):');
    if (nama) {
        router.post('/admin/keuangan/pengeluaran/jenis', { nama_jenis: nama }, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Pengeluaran Keuangan" />

    <DashboardLayout>
        <div class="flex flex-col h-full bg-[#f4f6f8] dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full py-6">
                
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
                    <div>
                        <h2 class="font-bold text-2xl text-gray-900 dark:text-white tracking-tight">Pengeluaran Sekolah</h2>
                        <p class="text-sm text-gray-500 mt-1">Catat dan pantau arus kas keluar.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <select v-model="filterBulan" class="border-gray-300 rounded-xl focus:ring-rose-500 focus:border-rose-500 text-sm">
                            <option v-for="m in 12" :key="m" :value="m.toString().padStart(2, '0')">
                                {{ new Date(0, m - 1).toLocaleString('id-ID', { month: 'long' }) }}
                            </option>
                        </select>
                        <select v-model="filterTahun" class="border-gray-300 rounded-xl focus:ring-rose-500 focus:border-rose-500 text-sm">
                            <option v-for="y in [2024, 2025, 2026, 2027]" :key="y" :value="y">{{ y }}</option>
                        </select>
                        <button @click="isModalOpen = true" class="bg-rose-600 hover:bg-rose-700 text-white font-bold py-2.5 px-5 rounded-xl text-sm transition-all shadow-lg shadow-rose-900/20 flex items-center gap-2">
                            <i class="fas fa-plus"></i> Catat Pengeluaran
                        </button>
                    </div>
                </div>
                
                <div class="flex gap-2 mb-4 justify-end">
                    <button @click="tambahDivisi" class="text-xs bg-gray-200 hover:bg-gray-300 text-gray-700 py-1.5 px-3 rounded-lg font-medium"><i class="fas fa-plus"></i> Master Divisi</button>
                    <button @click="tambahJenis" class="text-xs bg-gray-200 hover:bg-gray-300 text-gray-700 py-1.5 px-3 rounded-lg font-medium"><i class="fas fa-plus"></i> Master Kategori</button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-gradient-to-br from-rose-500 to-rose-600 rounded-3xl p-6 text-white shadow-xl shadow-rose-500/30 col-span-1 md:col-span-4 relative overflow-hidden flex flex-col justify-center min-h-[140px]">
                        <div class="absolute right-0 top-0 bottom-0 opacity-10 pointer-events-none">
                            <i class="fas fa-chart-line text-[150px] -mr-10 -mt-4"></i>
                        </div>
                        <p class="text-rose-100 font-medium mb-1">Total Pengeluaran Bulan Ini</p>
                        <h3 class="text-4xl font-black">{{ formatRupiah(totalPengeluaran) }}</h3>
                    </div>
                </div>

                <!-- Flash Message -->
                <div v-if="$page.props.flash?.message" class="bg-green-50 border border-green-200 text-green-800 p-4 mb-6 rounded-2xl shadow-sm flex items-center gap-3">
                    <i class="fas fa-check-circle"></i> {{ $page.props.flash.message }}
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wider border-b border-gray-100">
                                    <th class="px-6 py-5 font-bold w-16 text-center">No</th>
                                    <th class="px-6 py-5 font-bold">Tanggal</th>
                                    <th class="px-6 py-5 font-bold">Judul & Keterangan</th>
                                    <th class="px-6 py-5 font-bold">Divisi & Kategori</th>
                                    <th class="px-6 py-5 font-bold text-right">Nominal (Rp)</th>
                                    <th class="px-6 py-5 font-bold text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-for="(p, index) in pengeluaran" :key="p.id" class="hover:bg-gray-50/80 transition-colors">
                                    <td class="px-6 py-4 text-center text-sm font-medium text-gray-500">{{ index + 1 }}</td>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-700 whitespace-nowrap">
                                        {{ new Date(p.tanggal).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900 text-base">{{ p.judul_pengeluaran }}</div>
                                        <div class="text-sm text-gray-500 mt-0.5">{{ p.keterangan || '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 flex flex-col gap-1 items-start">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-indigo-100 text-indigo-700 border border-indigo-200">
                                            {{ p.divisi?.nama_divisi || '-' }}
                                        </span>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-gray-100 text-gray-600 border border-gray-200">
                                            {{ p.jenis?.nama_jenis || 'Umum' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-black text-rose-600">
                                        {{ formatRupiah(p.nominal) }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button @click="hapus(p.id)" class="w-9 h-9 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition-colors mx-auto">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="pengeluaran.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-500">
                                            <i class="fas fa-wallet text-4xl mb-3 text-gray-300"></i>
                                            <p class="text-lg font-medium">Belum ada pengeluaran di bulan ini.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <!-- Form Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="font-bold text-lg text-gray-900 flex items-center gap-2">
                        <i class="fas fa-minus-circle text-rose-500"></i> Catat Pengeluaran
                    </h3>
                    <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form @submit.prevent="submit" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Judul Pengeluaran <span class="text-red-500">*</span></label>
                        <input type="text" v-model="form.judul_pengeluaran" required placeholder="Cth: Beli ATK Kantor" class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-rose-500 focus:border-rose-500 text-sm">
                        <div v-if="form.errors.judul_pengeluaran" class="text-red-500 text-xs mt-1">{{ form.errors.judul_pengeluaran }}</div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Divisi <span class="text-red-500">*</span></label>
                        <select v-model="form.id_divisi" required class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-rose-500 focus:border-rose-500 text-sm">
                            <option value="" disabled>Pilih Divisi...</option>
                            <option v-for="d in divisi" :key="d.id" :value="d.id">{{ d.nama_divisi }}</option>
                        </select>
                        <div v-if="form.errors.id_divisi" class="text-red-500 text-xs mt-1">{{ form.errors.id_divisi }}</div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
                            <input type="date" v-model="form.tanggal" required class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-rose-500 focus:border-rose-500 text-sm">
                            <div v-if="form.errors.tanggal" class="text-red-500 text-xs mt-1">{{ form.errors.tanggal }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kategori / Jenis <span class="text-red-500">*</span></label>
                            <select v-model="form.id_jenis" required class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-rose-500 focus:border-rose-500 text-sm">
                                <option value="" disabled>Pilih Kategori...</option>
                                <option v-for="j in jenis" :key="j.id" :value="j.id">{{ j.nama_jenis }}</option>
                            </select>
                            <div v-if="form.errors.id_jenis" class="text-red-500 text-xs mt-1">{{ form.errors.id_jenis }}</div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nominal (Rp) <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-4 top-2 text-gray-500 font-bold">Rp</span>
                            <input type="text" v-model="form.nominal" @input="handleNominalInput" required placeholder="0" class="w-full pl-12 pr-4 py-2 border border-gray-300 rounded-xl focus:ring-rose-500 focus:border-rose-500 text-lg font-black text-gray-900">
                        </div>
                        <div v-if="form.errors.nominal" class="text-red-500 text-xs mt-1">{{ form.errors.nominal }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                        <textarea v-model="form.keterangan" rows="2" placeholder="Catatan tambahan (Opsional)" class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-rose-500 focus:border-rose-500 text-sm"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="isModalOpen = false" class="px-5 py-2.5 rounded-xl text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 transition-colors shadow-lg shadow-rose-500/30 disabled:opacity-50">
                            <i class="fas fa-save mr-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>
