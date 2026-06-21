<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref, watch } from 'vue';

const props = defineProps({
    kelas: Array,
    filter_kelas: [String, Number],
    search: String,
    per_page: [String, Number],
    siswa: Object // Because it's paginated now
});

const selectedKelas = ref(props.filter_kelas || '');
const searchQuery = ref(props.search || '');
const perPage = ref(props.per_page || 10);

let timeout = null;
watch([selectedKelas, searchQuery, perPage], ([newKelas, newSearch, newPerPage]) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('admin.kesiswaan.buku_induk.index'), { 
            kelas_id: newKelas,
            search: newSearch,
            per_page: newPerPage
        }, {
            preserveState: true,
            replace: true
        });
    }, 300);
});
</script>

<template>
    <Head title="Manajemen Buku Induk Siswa" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                            <i class="fas fa-book-open"></i>
                        </div>
                        Manajemen Buku Induk Siswa
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1 ml-13">
                        Lengkapi data kesehatan, hobi, dan cetak lembaran Buku Induk Resmi.
                    </p>
                </div>
            </div>

            <!-- Main Card -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                
                <!-- Filter Section -->
                <div class="p-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex flex-col md:flex-row gap-4 items-center justify-between">
                    <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <select v-model="perPage" class="rounded-xl border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500 bg-white dark:bg-gray-700 font-bold text-gray-700 dark:text-gray-200 cursor-pointer shadow-sm text-sm">
                                <option value="10">10 Baris</option>
                                <option value="25">25 Baris</option>
                                <option value="50">50 Baris</option>
                                <option value="100">100 Baris</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <select v-model="selectedKelas" class="w-full sm:w-48 rounded-xl border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500 bg-white dark:bg-gray-700 font-bold text-gray-700 dark:text-gray-200 cursor-pointer shadow-sm text-sm">
                                <option value="">Semua Kelas</option>
                                <option v-for="k in kelas" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="w-full md:w-72">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input v-model="searchQuery" type="text" class="w-full pl-10 pr-4 py-2.5 rounded-xl border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500 bg-white dark:bg-gray-700 text-sm shadow-sm" placeholder="Cari NIS / Nama Siswa...">
                        </div>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-4 font-bold text-center">No</th>
                                <th class="px-6 py-4 font-bold">NIS / NISN</th>
                                <th class="px-6 py-4 font-bold">Nama Siswa</th>
                                <th class="px-6 py-4 font-bold">Kelas</th>
                                <th class="px-6 py-4 font-bold text-center">Kelengkapan Data</th>
                                <th class="px-6 py-4 font-bold text-center w-48">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="(s, index) in siswa.data" :key="s.id" class="hover:bg-blue-50/50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 text-sm font-bold text-gray-500 text-center">
                                    {{ (siswa.current_page - 1) * siswa.per_page + index + 1 }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-mono font-bold text-gray-900 dark:text-white">{{ s.nis }}</div>
                                    <div class="text-xs text-gray-500">{{ s.nisn }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900 dark:text-white uppercase">{{ s.nama_lengkap }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-3 py-1 rounded-lg text-sm">
                                        {{ s.nama_kelas || '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div v-if="s.detail_id" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 font-bold text-xs">
                                        <i class="fas fa-check-circle"></i> Lengkap
                                    </div>
                                    <div v-else class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 font-bold text-xs animate-pulse">
                                        <i class="fas fa-exclamation-circle"></i> Belum Diisi
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-2 justify-center">
                                        <Link :href="route('admin.kesiswaan.buku_induk.detail', s.id)" class="w-10 h-10 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-600 flex items-center justify-center transition-colors shadow-sm" title="Lengkapi Data Buku Induk">
                                            <i class="fas fa-edit"></i>
                                        </Link>
                                        <a :href="route('admin.kesiswaan.buku_induk.cetak_pdf', s.id)" target="_blank" class="w-10 h-10 rounded-xl bg-gray-800 hover:bg-gray-900 text-white flex items-center justify-center transition-colors shadow-sm" title="Cetak PDF">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="siswa.data.length === 0">
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                        <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                                            <i class="fas fa-search text-3xl text-gray-400"></i>
                                        </div>
                                        <p class="font-bold text-lg text-gray-900 dark:text-white">Tidak ada data siswa</p>
                                        <p class="text-sm mt-1">Data yang Anda cari tidak ditemukan atau pilih kelas yang berbeda.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Section -->
                <div v-if="siswa.links && siswa.data.length > 0" class="p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400 font-medium">
                        Menampilkan <span class="font-bold text-gray-900 dark:text-white">{{ siswa.from }}</span> sampai <span class="font-bold text-gray-900 dark:text-white">{{ siswa.to }}</span> dari <span class="font-bold text-gray-900 dark:text-white">{{ siswa.total }}</span> entri
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-1">
                        <template v-for="(link, key) in siswa.links" :key="key">
                            <div v-if="link.url === null" class="px-3 py-1.5 text-sm font-medium text-gray-400 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg cursor-not-allowed" v-html="link.label"></div>
                            <Link v-else :href="link.url" class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors border shadow-sm" :class="link.active ? 'bg-blue-600 text-white border-blue-600 hover:bg-blue-700' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'" v-html="link.label"></Link>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
