<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    tamu: Object, // Changed to Object because it's paginated
    stats: Object,
    filters: Object
});

const tgl_awal = ref(props.filters.tgl_awal);
const tgl_akhir = ref(props.filters.tgl_akhir);
const kategori = ref(props.filters.kategori);
const search = ref(props.filters.search || '');

let searchTimeout = null;

const applyFilters = () => {
    router.get(route('admin.humas.buku-tamu.index'), {
        tgl_awal: tgl_awal.value,
        tgl_akhir: tgl_akhir.value,
        kategori: kategori.value,
        search: search.value
    }, { preserveState: true, replace: true });
};

const handleSearch = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500); // 500ms debounce
};

watch([tgl_awal, tgl_akhir, kategori], applyFilters);

const getStatusClass = (status) => {
    if (status === 'Selesai') return 'bg-emerald-500/10 text-emerald-600 border-emerald-200';
    return 'bg-amber-500/10 text-amber-600 border-amber-200';
};

</script>

<template>
    <Head title="Rekap Buku Tamu" />

    <DashboardLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Premium Gradient Banner -->
                <div class="bg-gradient-to-br from-indigo-600 via-blue-700 to-blue-800 rounded-3xl p-8 shadow-2xl shadow-indigo-500/20 relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                    <!-- Decorative background shapes -->
                    <div class="absolute top-0 right-0 -mt-20 -mr-20 w-72 h-72 bg-white/10 rounded-full blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 -mb-20 -ml-20 w-64 h-64 bg-indigo-400/20 rounded-full blur-3xl"></div>
                    <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-5 mix-blend-overlay"></div>
                    
                    <div class="relative z-10 flex-1">
                        <div class="flex items-center gap-3 mb-2.5">
                            <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-[10px] sm:text-xs font-black text-white uppercase tracking-wider border border-white/20 shadow-sm">Rekap Data</span>
                            <span class="text-indigo-100 text-xs sm:text-sm font-semibold tracking-wide flex items-center gap-2">
                                <i class="fas fa-building opacity-70"></i> Hubungan Industri & Humas
                            </span>
                        </div>
                        <h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-white tracking-tight mb-3 drop-shadow-sm">Rekap Buku Tamu</h2>
                        <p class="text-indigo-100/90 font-medium text-sm sm:text-base max-w-2xl leading-relaxed">
                            Pusat pemantauan laporan kunjungan, antrean tamu, dan rekam jejak digital pelayanan lobi terpadu.
                        </p>
                    </div>

                    <div class="relative z-10 flex flex-col items-end gap-3 shrink-0 w-full md:w-auto mt-4 md:mt-0">
                        <a :href="route('admin.humas.buku-tamu.cetak', { tgl_awal, tgl_akhir, kategori })" target="_blank" class="w-full md:w-auto inline-flex items-center justify-center px-6 py-3.5 bg-white hover:bg-gray-50 border border-transparent rounded-2xl font-black text-indigo-700 shadow-xl shadow-black/10 focus:outline-none focus:ring-2 focus:ring-white/50 focus:ring-offset-2 focus:ring-offset-indigo-700 transform hover:-translate-y-1 transition-all duration-300 group">
                            <i class="fas fa-print mr-2 text-indigo-500 group-hover:scale-110 transition-transform"></i> Cetak Rekap PDF
                        </a>
                        <div class="text-xs text-indigo-200 font-medium flex items-center bg-black/10 backdrop-blur-sm px-3.5 py-2 rounded-xl border border-white/5">
                            <i class="fas fa-info-circle mr-2 opacity-70"></i> Cetak data sesuai filter saat ini
                        </div>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 flex items-center justify-between group overflow-hidden relative">
                        <div class="absolute right-0 top-0 -mr-6 -mt-6 w-32 h-32 rounded-full bg-blue-50/50 group-hover:scale-150 transition-transform duration-500"></div>
                        <div class="relative z-10">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Total Tamu Filtered</p>
                            <h3 class="text-4xl font-black text-gray-900">{{ stats.total }}</h3>
                        </div>
                        <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl relative z-10 shadow-sm border border-blue-100">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 flex items-center justify-between group overflow-hidden relative">
                        <div class="absolute right-0 top-0 -mr-6 -mt-6 w-32 h-32 rounded-full bg-emerald-50/50 group-hover:scale-150 transition-transform duration-500"></div>
                        <div class="relative z-10">
                            <p class="text-xs font-bold text-emerald-600/70 uppercase tracking-wider mb-1">Tamu Selesai</p>
                            <h3 class="text-4xl font-black text-emerald-600">{{ stats.selesai }}</h3>
                        </div>
                        <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl relative z-10 shadow-sm border border-emerald-100">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 flex items-center justify-between group overflow-hidden relative">
                        <div class="absolute right-0 top-0 -mr-6 -mt-6 w-32 h-32 rounded-full bg-amber-50/50 group-hover:scale-150 transition-transform duration-500"></div>
                        <div class="relative z-10">
                            <p class="text-xs font-bold text-amber-600/70 uppercase tracking-wider mb-1">Masih Menunggu</p>
                            <h3 class="text-4xl font-black text-amber-500">{{ stats.menunggu }}</h3>
                        </div>
                        <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-2xl relative z-10 shadow-sm border border-amber-100">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:rounded-3xl p-6 border border-gray-100 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1.5 h-full bg-gradient-to-b from-indigo-500 to-blue-500"></div>
                    <div class="flex flex-col md:flex-row gap-5 items-end">
                        <div class="flex-1 w-full">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"><i class="fas fa-calendar-day mr-1"></i> Mulai Tanggal</label>
                            <input type="date" v-model="tgl_awal" class="w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-medium bg-gray-50/50 focus:bg-white transition-colors py-2.5">
                        </div>
                        <div class="flex-1 w-full">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"><i class="fas fa-calendar-day mr-1"></i> Sampai Tanggal</label>
                            <input type="date" v-model="tgl_akhir" class="w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-medium bg-gray-50/50 focus:bg-white transition-colors py-2.5">
                        </div>
                        <div class="flex-1 w-full">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"><i class="fas fa-filter mr-1"></i> Filter Kategori</label>
                            <div class="relative">
                                <select v-model="kategori" class="w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-medium bg-gray-50/50 focus:bg-white transition-colors py-2.5 appearance-none">
                                    <option value="Semua">Tampilkan Semua Kategori</option>
                                    <option value="Umum">Umum</option>
                                    <option value="Orang Tua">Orang Tua Wali</option>
                                    <option value="Dinas">Instansi / Dinas</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex-1 w-full">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"><i class="fas fa-search mr-1"></i> Cari Data</label>
                            <input type="text" v-model="search" @input="handleSearch" placeholder="Nama / Instansi..." class="w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-medium bg-gray-50/50 focus:bg-white transition-colors py-2.5">
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:rounded-3xl border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50/80">
                                <tr>
                                    <th scope="col" class="px-6 py-5 text-left text-xs font-black text-gray-400 uppercase tracking-widest w-16">No</th>
                                    <th scope="col" class="px-6 py-5 text-left text-xs font-black text-gray-400 uppercase tracking-widest w-32">Tanggal</th>
                                    <th scope="col" class="px-6 py-5 text-left text-xs font-black text-gray-400 uppercase tracking-widest">Identitas Tamu</th>
                                    <th scope="col" class="px-6 py-5 text-left text-xs font-black text-gray-400 uppercase tracking-widest">Keperluan</th>
                                    <th scope="col" class="px-6 py-5 text-center text-xs font-black text-gray-400 uppercase tracking-widest w-32">Status</th>
                                    <th scope="col" class="px-6 py-5 text-center text-xs font-black text-gray-400 uppercase tracking-widest w-24">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-if="tamu.data.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500 font-medium">
                                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                            <i class="fas fa-search text-2xl text-gray-300"></i>
                                        </div>
                                        Tidak ada data kunjungan pada filter tersebut.
                                    </td>
                                </tr>
                                <tr v-for="t in tamu.data" :key="t.id" class="hover:bg-indigo-50/30 transition-colors group">
                                    <td class="px-6 py-5 whitespace-nowrap">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-700 font-black text-sm group-hover:bg-indigo-100 group-hover:text-indigo-700 transition-colors">
                                            {{ t.no_antrian }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-600 font-bold">
                                        {{ t.tanggal }}
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="text-sm font-black text-gray-900 group-hover:text-indigo-600 transition-colors">{{ t.nama_lengkap }}</div>
                                        <div class="text-xs text-gray-500 flex gap-2 items-center mt-1.5 font-medium">
                                            <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded-md">{{ t.kategori }}</span>
                                            <span v-if="t.instansi_asal" class="flex items-center"><i class="fas fa-building mr-1.5 text-gray-400"></i>{{ t.instansi_asal }}</span>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1.5 flex items-center"><i class="fab fa-whatsapp text-emerald-500 mr-1.5"></i>{{ t.no_hp }}</div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <p class="text-sm text-gray-600 font-medium line-clamp-2">{{ t.keperluan }}</p>
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap text-center">
                                        <span class="px-3 py-1.5 inline-flex text-xs leading-5 font-black rounded-xl border" :class="getStatusClass(t.status)">
                                            {{ t.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap text-center text-sm font-medium">
                                        <Link :href="route('admin.humas.buku-tamu.destroy', t.id)" method="delete" as="button" preserve-scroll
                                              class="text-rose-500 hover:text-white bg-rose-50 hover:bg-rose-500 w-9 h-9 rounded-xl inline-flex items-center justify-center transition-all duration-200 transform hover:scale-110 shadow-sm"
                                              @click="!confirm('Hapus tamu ini secara permanen?') && $event.preventDefault()">
                                            <i class="fas fa-trash-alt"></i>
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div v-if="tamu.links && tamu.links.length > 3" class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-sm text-gray-500 font-medium">
                            Menampilkan <span class="font-bold text-gray-900">{{ tamu.from || 0 }}</span> sampai <span class="font-bold text-gray-900">{{ tamu.to || 0 }}</span> dari <span class="font-bold text-gray-900">{{ tamu.total }}</span> data
                        </span>
                        <div class="flex gap-1">
                            <template v-for="(link, i) in tamu.links" :key="i">
                                <Link v-if="link.url" :href="link.url"
                                      class="px-3 py-1.5 rounded-lg text-sm font-bold transition-colors"
                                      :class="link.active ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-100'"
                                      v-html="link.label" preserve-scroll />
                                <span v-else class="px-3 py-1.5 rounded-lg text-sm font-medium bg-gray-100 text-gray-400" v-html="link.label" />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
