<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    rekening: Array,
    siswaBaru: Array,
    logGlobal: Array,
    totalUang: Number,
    kelasList: Array
});

const isModalOpen = ref(false);
const searchSiswa = ref('');
const selectedKelas = ref('');

const form = useForm({
    siswa_id: '',
    pin: ''
});

const filteredSiswaBaru = computed(() => {
    let result = props.siswaBaru;
    if (selectedKelas.value) {
        result = result.filter(s => s.kelas_id === selectedKelas.value);
    }
    if (searchSiswa.value) {
        const query = searchSiswa.value.toLowerCase();
        result = result.filter(s => 
            s.nama_lengkap.toLowerCase().includes(query) || 
            (s.nisn && s.nisn.toLowerCase().includes(query))
        );
    }
    return result;
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
    form.post(route('admin.keuangan.tabungan.store'), {
        onSuccess: () => closeModal()
    });
};

const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
};
</script>

<template>
    <Head title="Bank Mini Siswa" />

    <DashboardLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Banner -->
                <div class="bg-gradient-to-br from-blue-700 via-indigo-800 to-purple-900 rounded-3xl p-8 shadow-2xl relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                    <div class="absolute top-0 right-0 -mt-20 -mr-20 w-72 h-72 bg-white/10 rounded-full blur-3xl"></div>
                    <div class="relative z-10 flex-1">
                        <div class="flex items-center gap-3 mb-2.5">
                            <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-[10px] font-black text-white uppercase tracking-wider border border-white/20">Keuangan Sekolah</span>
                        </div>
                        <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight mb-2">Bank Mini Siswa</h2>
                        <p class="text-blue-100/90 font-medium text-sm">Pusat kelola tabungan, transaksi setor/tarik, dan transfer antar nasabah (siswa).</p>
                    </div>
                    <div class="relative z-10 shrink-0 flex flex-col gap-3 items-end">
                        <div class="text-right bg-white/10 p-4 rounded-2xl border border-white/20 backdrop-blur-md">
                            <p class="text-xs text-blue-200 uppercase font-bold tracking-wider mb-1">Total Saldo Beredar</p>
                            <p class="text-2xl font-black text-white">{{ formatRupiah(totalUang) }}</p>
                        </div>
                        <button @click="openModal" class="px-6 py-3 bg-white text-indigo-700 font-black rounded-2xl shadow-xl hover:-translate-y-1 transition-transform border border-indigo-100">
                            <i class="fas fa-plus-circle mr-2"></i> Buka Rekening Baru
                        </button>
                    </div>
                </div>

                <div v-if="$page.props.flash?.message" class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-center text-emerald-800 shadow-sm animate-fade-in-down">
                    <i class="fas fa-check-circle text-emerald-600 mr-3 text-xl"></i>
                    <p class="font-bold">{{ $page.props.flash.message }}</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Tabel Rekening -->
                    <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                        <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                            <h3 class="font-black text-gray-800 text-lg">Daftar Nasabah (Rekening Aktif)</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100 font-bold">
                                    <tr>
                                        <th class="px-6 py-4">Siswa (Nasabah)</th>
                                        <th class="px-6 py-4 text-center">Kelas</th>
                                        <th class="px-6 py-4 text-right">Saldo Saat Ini</th>
                                        <th class="px-6 py-4 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="rek in rekening" :key="rek.id" class="hover:bg-gray-50/80 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-gray-900">{{ rek.siswa?.nama_lengkap }}</div>
                                            <div class="text-xs text-gray-500 font-medium">NISN: {{ rek.siswa?.nisn }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-center font-bold text-gray-700">{{ rek.siswa?.kelas?.nama_kelas || '-' }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="font-black text-emerald-600 text-base">{{ formatRupiah(rek.saldo) }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <Link :href="route('admin.keuangan.tabungan.show', rek.id)" class="px-4 py-2 bg-indigo-50 text-indigo-600 font-bold rounded-xl hover:bg-indigo-100 transition-colors text-xs inline-flex items-center">
                                                Buku Tabungan <i class="fas fa-arrow-right ml-2"></i>
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="rekening.length === 0">
                                        <td colspan="4" class="px-6 py-12 text-center text-gray-500 font-medium">Belum ada rekening yang dibuka.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Log Mutasi Global -->
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col h-[600px]">
                        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="font-black text-gray-800 text-lg">Aktivitas Terbaru</h3>
                        </div>
                        <div class="p-6 overflow-y-auto flex-1 space-y-4">
                            <div v-for="log in logGlobal" :key="log.id" class="flex gap-4 items-start p-3 rounded-2xl hover:bg-gray-50 border border-transparent hover:border-gray-100 transition-colors">
                                <div :class="[
                                    'w-10 h-10 rounded-full flex items-center justify-center shrink-0 shadow-sm border',
                                    log.jenis_transaksi === 'Setor' || log.jenis_transaksi === 'Transfer_Masuk' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100'
                                ]">
                                    <i :class="log.jenis_transaksi === 'Setor' || log.jenis_transaksi === 'Transfer_Masuk' ? 'fas fa-arrow-down' : 'fas fa-arrow-up'"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-gray-900 truncate">{{ log.rekening?.siswa?.nama_lengkap }}</p>
                                    <p class="text-xs text-gray-500 line-clamp-2 mt-0.5">{{ log.keterangan }}</p>
                                    <p class="text-[10px] text-gray-400 font-medium mt-1">{{ new Date(log.created_at).toLocaleString('id-ID') }}</p>
                                </div>
                                <div :class="[
                                    'text-sm font-black whitespace-nowrap',
                                    log.jenis_transaksi === 'Setor' || log.jenis_transaksi === 'Transfer_Masuk' ? 'text-emerald-600' : 'text-rose-600'
                                ]">
                                    {{ log.jenis_transaksi === 'Setor' || log.jenis_transaksi === 'Transfer_Masuk' ? '+' : '-' }}{{ formatRupiah(log.nominal) }}
                                </div>
                            </div>
                            <div v-if="logGlobal.length === 0" class="text-center text-sm text-gray-500 py-10">Belum ada aktivitas transaksi.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Buka Rekening -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm" @click="closeModal"></div>
            <div class="bg-white rounded-3xl w-full max-w-md relative z-10 overflow-hidden shadow-2xl border border-white/20">
                <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-xl font-black text-gray-900"><i class="fas fa-id-card text-indigo-600 mr-2"></i> Buka Rekening Baru</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Filter Kelas</label>
                            <select v-model="selectedKelas" class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500">
                                <option value="">Semua Kelas</option>
                                <option v-for="kelas in kelasList" :key="kelas.id" :value="kelas.id">{{ kelas.nama_kelas }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Cari Nama/NISN</label>
                            <input type="text" v-model="searchSiswa" class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500" placeholder="Ketik nama...">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Pilih Siswa</label>
                        <select v-model="form.siswa_id" class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500">
                            <option value="">-- Pilih Siswa Tanpa Rekening --</option>
                            <option v-for="siswa in filteredSiswaBaru" :key="siswa.id" :value="siswa.id">
                                {{ siswa.nisn }} - {{ siswa.nama_lengkap }} {{ siswa.kelas ? '(' + siswa.kelas.nama_kelas + ')' : '' }}
                            </option>
                        </select>
                        <div v-if="filteredSiswaBaru.length === 0" class="text-xs text-amber-600 mt-1"><i class="fas fa-info-circle mr-1"></i> Tidak ada siswa cocok / belum punya rekening.</div>
                        <div v-if="form.errors.siswa_id" class="text-red-500 text-xs mt-1">{{ form.errors.siswa_id }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">PIN Tabungan (Default)</label>
                        <input type="text" v-model="form.pin" class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 font-mono tracking-widest text-center text-lg" placeholder="123456" maxlength="6">
                        <p class="text-[10px] text-gray-500 mt-1 text-center">Siswa dapat mengubah PIN ini nanti.</p>
                        <div v-if="form.errors.pin" class="text-red-500 text-xs mt-1">{{ form.errors.pin }}</div>
                    </div>
                </div>
                <div class="p-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                    <button @click="closeModal" class="px-5 py-2 rounded-xl text-sm font-bold text-gray-600 bg-white border border-gray-200 hover:bg-gray-50">Batal</button>
                    <button @click="submitForm" class="px-5 py-2 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50" :disabled="form.processing">
                        {{ form.processing ? 'Memproses...' : 'Buka Rekening' }}
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
