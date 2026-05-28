<script setup>
import { ref, watch, computed } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import FormModal from './FormModal.vue';

const props = defineProps({
    drafts: Object,
    filters: Object,
    mapels: Array,
    bank_soals: Array
});

const search = ref(props.filters.search || '');
const showForm = ref(false);
const editData = ref(null);

let searchTimeout = null;
watch(search, (value) => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('admin.cbt.draft-ujian.index'), { search: value }, {
            preserveState: true,
            replace: true
        });
    }, 300);
});

const openAddModal = () => {
    editData.value = null;
    showForm.value = true;
};

const openEditModal = (draft) => {
    editData.value = draft;
    showForm.value = true;
};

const hapus = (id) => {
    if (confirm('Yakin ingin menghapus Draft Ujian ini? Data yang terkait juga bisa terhapus.')) {
        router.delete(route('admin.cbt.draft-ujian.destroy', id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Draft Ujian (CBT)" />

    <DashboardLayout>
        <div class="flex flex-col h-full">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full py-6">
                <!-- Header -->
                <div class="mb-8 bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-sm border border-gray-100 dark:border-gray-700/60 flex flex-col md:flex-row md:items-center justify-between gap-6 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-primary-500/10 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
                    
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 text-xs font-bold tracking-wide uppercase mb-3">
                            <i class="fas fa-clipboard-list"></i> CBT Management
                        </div>
                        <h2 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight mb-2">Draft Ujian</h2>
                        <p class="text-gray-500 dark:text-gray-400 font-medium">Atur paket soal yang akan diujikan kepada siswa.</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <button @click="openAddModal" class="px-5 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-2xl font-bold text-sm shadow-md shadow-primary-500/20 flex items-center gap-2 transition-all hover:-translate-y-0.5">
                            <i class="fas fa-plus"></i> Tambah Draft
                        </button>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700/60 overflow-hidden relative z-10">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700/60">
                        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                            <h3 class="text-lg font-black text-gray-800 dark:text-white">Daftar Draft Ujian</h3>
                            <div class="w-full sm:w-72 relative">
                                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input v-model="search" type="text" placeholder="Cari draft ujian..." class="w-full pl-11 pr-4 py-2.5 bg-gray-50 dark:bg-gray-900 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary-500/20 text-gray-800 dark:text-gray-200">
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50/50 dark:bg-gray-900/50 text-gray-500 dark:text-gray-400 uppercase text-[11px] font-black tracking-wider">
                                <tr>
                                    <th class="px-6 py-4">Informasi Ujian</th>
                                    <th class="px-6 py-4">Sumber Bank Soal</th>
                                    <th class="px-6 py-4 min-w-[250px]">Komposisi Soal</th>
                                    <th class="px-6 py-4 text-center">Pengaturan</th>
                                    <th class="px-6 py-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                <tr v-for="item in drafts.data" :key="item.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors">
                                    <td class="px-6 py-4 align-top">
                                        <div class="font-bold text-gray-800 dark:text-white mb-1.5 text-base">{{ item.nama }}</div>
                                        <div class="flex flex-col gap-1">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 font-bold text-[10px] w-fit uppercase">
                                                <i class="fas fa-book"></i> {{ item.mapel?.nama_mapel || '-' }}
                                            </span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-1">
                                                <i class="fas fa-clock mr-1"></i> Durasi: {{ item.timeout }} Menit
                                            </span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                                                <i class="fas fa-hourglass-half mr-1"></i> Min Finish: {{ item.minFinishTime }} Menit
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 align-top">
                                        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-gray-50 dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700">
                                            <i class="fas fa-database text-gray-400"></i>
                                            <span class="text-gray-800 dark:text-gray-200 font-bold text-xs">{{ item.bank_soal?.kode || '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 align-top">
                                        <div class="flex flex-wrap gap-2">
                                            <!-- PG -->
                                            <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg text-[11px] font-bold border shadow-sm" :class="item.visible_pg > item.count_pg ? 'bg-rose-50 border-rose-200 text-rose-700 dark:bg-rose-900/30 dark:border-rose-800/50 dark:text-rose-400' : 'bg-white border-gray-200 text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300'">
                                                PG: {{ item.visible_pg }}/{{ item.count_pg }}
                                                <i v-if="item.visible_pg > item.count_pg" class="fas fa-exclamation-circle text-rose-500" title="Soal di Bank Soal kurang!"></i>
                                            </div>
                                            <!-- PG Kompleks -->
                                            <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg text-[11px] font-bold border shadow-sm" :class="item.visible_pgmulti > item.count_pgmulti ? 'bg-rose-50 border-rose-200 text-rose-700 dark:bg-rose-900/30 dark:border-rose-800/50 dark:text-rose-400' : 'bg-white border-gray-200 text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300'">
                                                PG Kompleks: {{ item.visible_pgmulti }}/{{ item.count_pgmulti }}
                                                <i v-if="item.visible_pgmulti > item.count_pgmulti" class="fas fa-exclamation-circle text-rose-500" title="Soal di Bank Soal kurang!"></i>
                                            </div>
                                            <!-- Menjodohkan -->
                                            <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg text-[11px] font-bold border shadow-sm" :class="item.visible_pgcouple > item.count_menjodohkan ? 'bg-rose-50 border-rose-200 text-rose-700 dark:bg-rose-900/30 dark:border-rose-800/50 dark:text-rose-400' : 'bg-white border-gray-200 text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300'">
                                                Menjodohkan: {{ item.visible_pgcouple }}/{{ item.count_menjodohkan }}
                                                <i v-if="item.visible_pgcouple > item.count_menjodohkan" class="fas fa-exclamation-circle text-rose-500" title="Soal di Bank Soal kurang!"></i>
                                            </div>
                                            <!-- Isian -->
                                            <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg text-[11px] font-bold border shadow-sm" :class="item.visible_shortentry > item.count_isian ? 'bg-rose-50 border-rose-200 text-rose-700 dark:bg-rose-900/30 dark:border-rose-800/50 dark:text-rose-400' : 'bg-white border-gray-200 text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300'">
                                                Isian: {{ item.visible_shortentry }}/{{ item.count_isian }}
                                                <i v-if="item.visible_shortentry > item.count_isian" class="fas fa-exclamation-circle text-rose-500" title="Soal di Bank Soal kurang!"></i>
                                            </div>
                                            <!-- Esai -->
                                            <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg text-[11px] font-bold border shadow-sm" :class="item.visible_esai > item.count_esai ? 'bg-rose-50 border-rose-200 text-rose-700 dark:bg-rose-900/30 dark:border-rose-800/50 dark:text-rose-400' : 'bg-white border-gray-200 text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300'">
                                                Esai: {{ item.visible_esai }}/{{ item.count_esai }}
                                                <i v-if="item.visible_esai > item.count_esai" class="fas fa-exclamation-circle text-rose-500" title="Soal di Bank Soal kurang!"></i>
                                            </div>
                                            <!-- Benar Salah -->
                                            <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg text-[11px] font-bold border shadow-sm" :class="item.visible_pgtf > item.count_bs ? 'bg-rose-50 border-rose-200 text-rose-700 dark:bg-rose-900/30 dark:border-rose-800/50 dark:text-rose-400' : 'bg-white border-gray-200 text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300'">
                                                B/S: {{ item.visible_pgtf }}/{{ item.count_bs }}
                                                <i v-if="item.visible_pgtf > item.count_bs" class="fas fa-exclamation-circle text-rose-500" title="Soal di Bank Soal kurang!"></i>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 align-top text-center">
                                        <div class="flex flex-col gap-1.5 items-center justify-center">
                                            <span v-if="item.acak_soal" class="px-2 py-0.5 bg-purple-50 text-purple-600 border border-purple-200 dark:bg-purple-900/30 dark:border-purple-800/50 dark:text-purple-400 rounded-lg text-[10px] font-bold shadow-sm">ACAK SOAL</span>
                                            <span v-if="item.acak_opsi" class="px-2 py-0.5 bg-indigo-50 text-indigo-600 border border-indigo-200 dark:bg-indigo-900/30 dark:border-indigo-800/50 dark:text-indigo-400 rounded-lg text-[10px] font-bold shadow-sm">ACAK OPSI</span>
                                            <span v-if="!item.acak_soal && !item.acak_opsi" class="text-gray-400 text-xs">-</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 align-top">
                                        <div class="flex items-center justify-center gap-2">
                                            <Link :href="route('admin.cbt.draft-ujian.atur-soal', item.id)" class="px-3 py-1.5 rounded-xl bg-primary-50 text-primary-600 hover:bg-primary-100 dark:bg-primary-900/30 dark:text-primary-400 font-bold text-xs transition-colors shadow-sm flex items-center gap-1.5 border border-primary-100 dark:border-primary-800/50">
                                                <i class="fas fa-list-ol"></i> Soal
                                            </Link>
                                            <button @click="openEditModal(item)" class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 hover:bg-amber-100 flex items-center justify-center transition-colors shadow-sm border border-amber-100 dark:bg-amber-900/30 dark:border-amber-800/50 dark:text-amber-400" title="Edit Draft">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button @click="hapus(item.id)" class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 flex items-center justify-center transition-colors shadow-sm border border-rose-100 dark:bg-rose-900/30 dark:border-rose-800/50 dark:text-rose-400" title="Hapus Draft">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <a :href="route('admin.cbt.draft-ujian.cetak', item.id)" target="_blank" class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 hover:bg-emerald-100 flex items-center justify-center transition-colors shadow-sm border border-emerald-100 dark:bg-emerald-900/30 dark:border-emerald-800/50 dark:text-emerald-400" title="Print Draft">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="drafts.data.length === 0">
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="w-16 h-16 bg-gray-50 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-3">
                                            <i class="fas fa-box-open text-2xl text-gray-400"></i>
                                        </div>
                                        <p class="text-base font-semibold text-gray-700 dark:text-gray-300">Data Draft Ujian Kosong</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="drafts.data.length > 0" class="mt-6 flex justify-center">
                    <div class="flex items-center gap-1 bg-white dark:bg-gray-800 p-1.5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/60">
                        <Link v-for="(link, i) in drafts.links" :key="i" :href="link.url || '#'" 
                            v-html="link.label"
                            class="px-4 py-2 rounded-xl text-sm font-bold transition-all"
                            :class="[
                                link.active ? 'bg-primary-600 text-white shadow-md shadow-primary-500/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700',
                                !link.url ? 'opacity-50 cursor-not-allowed' : ''
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>

        <FormModal v-if="showForm" :show="showForm" :editData="editData" :mapels="mapels" :bankSoals="bank_soals" @close="showForm = false" />
    </DashboardLayout>
</template>
