<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    buku: Array,
    filters: Object
});

const search = ref(props.filters.search || '');

const searchBuku = () => {
    router.get(route('admin.perpus.katalog'), { search: search.value }, { preserveState: true, replace: true });
};

const isModalOpen = ref(false);
const form = useForm({
    kode_buku: '',
    judul: '',
    pengarang: '',
    penerbit: '',
    tahun_terbit: '',
    stok_total: ''
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
    form.post(route('admin.perpus.simpan-buku'), {
        onSuccess: () => closeModal()
    });
};

const deleteBuku = (id) => {
    if (confirm('Yakin ingin menghapus buku ini dari katalog?')) {
        router.delete(route('admin.perpus.hapus-buku', id));
    }
};
</script>

<template>
    <Head title="OPAC - Katalog Buku" />

    <DashboardLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Banner -->
                <div class="bg-gradient-to-br from-teal-700 via-emerald-800 to-green-900 rounded-3xl p-8 shadow-2xl relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                    <div class="relative z-10 flex-1">
                        <div class="flex items-center gap-3 mb-2.5">
                            <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-[10px] font-black text-white uppercase tracking-wider border border-white/20">Perpustakaan</span>
                        </div>
                        <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight mb-2">Katalog Buku (OPAC)</h2>
                        <p class="text-teal-100/90 font-medium text-sm">Pusat data referensi pustaka, stok ketersediaan, dan kode rak buku.</p>
                    </div>
                    <div class="relative z-10 shrink-0 flex gap-3">
                        <button @click="openModal" class="px-6 py-3 bg-white text-emerald-700 font-black rounded-2xl shadow-xl hover:-translate-y-1 transition-transform">
                            <i class="fas fa-book-medical mr-2"></i> Tambah Buku Baru
                        </button>
                    </div>
                </div>

                <div v-if="$page.props.flash?.message" class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-center text-emerald-800 shadow-sm animate-fade-in-down">
                    <i class="fas fa-check-circle text-emerald-600 mr-3 text-xl"></i>
                    <p class="font-bold">{{ $page.props.flash.message }}</p>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <h3 class="font-black text-gray-800 text-lg">Daftar Buku Terdaftar</h3>
                        
                        <!-- Pencarian -->
                        <div class="w-full sm:w-72 relative">
                            <input type="text" v-model="search" @keyup.enter="searchBuku" class="w-full pl-10 pr-4 py-2.5 rounded-xl border-gray-200 text-sm focus:ring-emerald-500 font-medium" placeholder="Cari Judul, Pengarang, Barcode...">
                            <i class="fas fa-search absolute left-3.5 top-3 text-gray-400"></i>
                            <button @click="searchBuku" class="absolute right-2 top-1.5 bottom-1.5 px-3 bg-emerald-50 text-emerald-600 font-bold rounded-lg hover:bg-emerald-100 text-xs">Cari</button>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100 font-bold">
                                <tr>
                                    <th class="px-6 py-4">Kode / Barcode</th>
                                    <th class="px-6 py-4">Informasi Buku</th>
                                    <th class="px-6 py-4 text-center">Stok Total</th>
                                    <th class="px-6 py-4 text-center">Stok Tersedia</th>
                                    <th class="px-6 py-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="b in buku" :key="b.id" class="hover:bg-gray-50/80 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-mono text-gray-900 font-bold bg-gray-100 inline-block px-3 py-1 rounded-lg border border-gray-200 tracking-wider">
                                            <i class="fas fa-barcode mr-2 text-gray-400"></i>{{ b.kode_buku }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-black text-gray-900 text-base">{{ b.judul }}</div>
                                        <div class="text-xs text-gray-500 font-medium mt-1">
                                            <i class="fas fa-user-edit mr-1 text-gray-400"></i> {{ b.pengarang }} &nbsp;&bull;&nbsp; 
                                            <i class="fas fa-building mr-1 text-gray-400"></i> {{ b.penerbit }} ({{ b.tahun_terbit }})
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold text-gray-700 text-lg">{{ b.stok_total }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span :class="[
                                            'px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider',
                                            b.stok_tersedia > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'
                                        ]">
                                            {{ b.stok_tersedia }} Sisa
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button @click="deleteBuku(b.id)" class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-colors flex items-center justify-center mx-auto">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="buku.length === 0">
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500 font-medium">Buku tidak ditemukan di rak.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Tambah Buku -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm" @click="closeModal"></div>
            <div class="bg-white rounded-3xl w-full max-w-lg relative z-10 overflow-hidden shadow-2xl">
                <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-xl font-black text-gray-900"><i class="fas fa-book-medical text-emerald-600 mr-2"></i> Input Data Buku Baru</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Barcode / Kode Buku (Unik)</label>
                        <input type="text" v-model="form.kode_buku" class="w-full rounded-xl border-gray-200 focus:ring-emerald-500 font-mono tracking-widest text-lg bg-gray-50" placeholder="Scan Barcode disini...">
                        <div v-if="form.errors.kode_buku" class="text-red-500 text-xs mt-1">{{ form.errors.kode_buku }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Judul Buku</label>
                        <input type="text" v-model="form.judul" class="w-full rounded-xl border-gray-200 focus:ring-emerald-500 text-base font-bold">
                        <div v-if="form.errors.judul" class="text-red-500 text-xs mt-1">{{ form.errors.judul }}</div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nama Pengarang</label>
                            <input type="text" v-model="form.pengarang" class="w-full rounded-xl border-gray-200 focus:ring-emerald-500 text-sm">
                            <div v-if="form.errors.pengarang" class="text-red-500 text-xs mt-1">{{ form.errors.pengarang }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Penerbit</label>
                            <input type="text" v-model="form.penerbit" class="w-full rounded-xl border-gray-200 focus:ring-emerald-500 text-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Tahun Terbit</label>
                            <input type="number" v-model="form.tahun_terbit" class="w-full rounded-xl border-gray-200 focus:ring-emerald-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Total Stok Masuk</label>
                            <input type="number" v-model="form.stok_total" class="w-full rounded-xl border-gray-200 focus:ring-emerald-500 font-black text-xl text-center">
                            <div v-if="form.errors.stok_total" class="text-red-500 text-xs mt-1">{{ form.errors.stok_total }}</div>
                        </div>
                    </div>
                </div>
                <div class="p-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                    <button @click="closeModal" class="px-5 py-2.5 rounded-xl text-sm font-bold text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 transition-colors">Batal</button>
                    <button @click="submitForm" class="px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-lg shadow-emerald-600/20 transition-colors disabled:opacity-50" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan ke Katalog' }}
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
