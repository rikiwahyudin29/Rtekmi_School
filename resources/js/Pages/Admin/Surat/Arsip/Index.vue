<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref } from 'vue';

const props = defineProps({
    arsip: Object,
    filter: Object
});

const filterForm = useForm({
    keyword: props.filter.keyword || '',
    tgl_awal: props.filter.tgl_awal || '',
    tgl_akhir: props.filter.tgl_akhir || '',
    jenis: props.filter.jenis || 'Semua'
});

const doFilter = () => {
    filterForm.get(route('admin.surat.arsip.index'), {
        preserveState: true,
        preserveScroll: true
    });
};

const resetFilter = () => {
    filterForm.keyword = '';
    filterForm.tgl_awal = '';
    filterForm.tgl_akhir = '';
    filterForm.jenis = 'Semua';
    doFilter();
};
</script>

<template>
    <Head title="E-Arsip Digital" />
    <DashboardLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">E-Arsip Digital Terpadu</h2>
                <p class="text-sm text-gray-500 mt-1">Satu pintu pencarian untuk seluruh arsip Surat Masuk dan Surat Keluar sekolah.</p>
            </div>

            <!-- Filter Panel -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 mb-6">
                <form @submit.prevent="doFilter" class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">
                    <div class="lg:col-span-4">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Kata Kunci / Nomor / Perihal</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                <i class="fas fa-search"></i>
                            </span>
                            <input v-model="filterForm.keyword" type="text" class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-10 pr-4 py-2.5 outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500" placeholder="Cari surat...">
                        </div>
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Jenis</label>
                        <select v-model="filterForm.jenis" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 outline-none font-medium">
                            <option value="Semua">Semua Surat</option>
                            <option value="Masuk">Surat Masuk</option>
                            <option value="Keluar">Surat Keluar</option>
                        </select>
                    </div>
                    <div class="lg:col-span-4 flex gap-2 items-center">
                        <div class="flex-1">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Tgl Awal</label>
                            <input v-model="filterForm.tgl_awal" type="date" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 outline-none text-sm">
                        </div>
                        <div class="pt-6 text-gray-400">-</div>
                        <div class="flex-1">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Tgl Akhir</label>
                            <input v-model="filterForm.tgl_akhir" type="date" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 outline-none text-sm">
                        </div>
                    </div>
                    <div class="lg:col-span-2 flex gap-2">
                        <button type="submit" class="flex-1 bg-primary-600 hover:bg-primary-700 text-white font-bold py-2.5 px-4 rounded-xl shadow-md transition-colors flex items-center justify-center gap-2">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <button type="button" @click="resetFilter" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl font-bold transition-colors">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="uppercase tracking-wider border-b-2 border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 font-bold">
                            <tr>
                                <th class="px-6 py-4">Jenis</th>
                                <th class="px-6 py-4">Detail Surat</th>
                                <th class="px-6 py-4">Tujuan / Pihak</th>
                                <th class="px-6 py-4">Tanggal</th>
                                <th class="px-6 py-4 text-center">Berkas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-gray-800 dark:text-gray-200 font-medium">
                            <tr v-for="a in arsip.data" :key="a.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4">
                                    <span :class="['inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold border', a.warna_badge]">
                                        <i :class="['mr-1.5', a.jenis_surat === 'Surat Keluar' ? 'fas fa-paper-plane' : 'fas fa-inbox']"></i> 
                                        {{ a.jenis_surat }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="block font-bold text-gray-900 dark:text-white">{{ a.nomor_surat }}</span>
                                    <span class="block text-xs text-gray-500 mt-0.5 whitespace-normal max-w-xs">{{ a.perihal }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-normal max-w-[200px]">{{ a.pihak }}</td>
                                <td class="px-6 py-4">{{ a.tanggal }}</td>
                                <td class="px-6 py-4 text-center">
                                    <template v-if="a.file">
                                        <a v-if="a.is_cetak" :href="a.file" target="_blank" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors font-bold text-xs flex items-center justify-center gap-1.5 inline-flex">
                                            <i class="fas fa-print"></i> Cetak
                                        </a>
                                        <a v-else :href="a.file" target="_blank" class="text-blue-500 hover:text-blue-700 text-xl inline-flex items-center">
                                            <i class="fas fa-file-download"></i>
                                        </a>
                                    </template>
                                    <span v-else class="text-gray-400 text-xs italic">Tanpa Berkas</span>
                                </td>
                            </tr>
                            <tr v-if="!arsip.data.length">
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-folder-open text-5xl mb-4 text-gray-300"></i>
                                        <p class="text-lg font-bold">Arsip Tidak Ditemukan</p>
                                        <p class="text-sm mt-1">Coba ubah kata kunci atau rentang tanggal pada filter.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex justify-between items-center" v-if="arsip.links && arsip.data.length > 0">
                    <p class="text-sm text-gray-500">
                        Menampilkan <span class="font-bold text-gray-900 dark:text-white">{{ arsip.from }}</span> sampai <span class="font-bold text-gray-900 dark:text-white">{{ arsip.to }}</span> dari <span class="font-bold text-gray-900 dark:text-white">{{ arsip.total }}</span> arsip
                    </p>
                    <Pagination :links="arsip.links" />
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
