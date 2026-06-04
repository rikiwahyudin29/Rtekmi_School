<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref, watch } from 'vue';

const props = defineProps({
    pendaftar: Object,
    stats: Object,
    filters: Object
});

const statusFilter = ref(props.filters?.status || '');

watch(statusFilter, (value) => {
    router.get(route('admin.ppdb.index'), { status: value }, { preserveState: true, replace: true });
});

const getStatusColor = (status) => {
    switch(status) {
        case 'Diterima': return 'bg-emerald-100 text-emerald-700 border-emerald-200';
        case 'Ditolak': return 'bg-red-100 text-red-700 border-red-200';
        case 'Cadangan': return 'bg-amber-100 text-amber-700 border-amber-200';
        default: return 'bg-slate-100 text-slate-700 border-slate-200';
    }
};

const formatDate = (dateString) => {
    if(!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'short', year: 'numeric'
    });
};
</script>

<template>
    <Head title="Manajemen PPDB Online" />

    <DashboardLayout>
        <template #header>
            <h2 class="font-black text-xl text-slate-800 leading-tight">Data Pendaftar PPDB</h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Statistik Card -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center text-xl"><i class="fas fa-users"></i></div>
                        <div>
                            <div class="text-2xl font-black text-slate-800">{{ stats.total }}</div>
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">Total</div>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center text-xl"><i class="fas fa-clock"></i></div>
                        <div>
                            <div class="text-2xl font-black text-blue-600">{{ stats.pending }}</div>
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">Pending</div>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-xl"><i class="fas fa-check-circle"></i></div>
                        <div>
                            <div class="text-2xl font-black text-emerald-600">{{ stats.diterima }}</div>
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">Diterima</div>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-red-50 text-red-500 flex items-center justify-center text-xl"><i class="fas fa-times-circle"></i></div>
                        <div>
                            <div class="text-2xl font-black text-red-600">{{ stats.ditolak }}</div>
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">Ditolak</div>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="bg-white overflow-hidden shadow-sm border border-slate-200 sm:rounded-3xl">
                    <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
                        <div class="flex items-center gap-3 w-full md:w-auto">
                            <i class="fas fa-filter text-slate-400"></i>
                            <select v-model="statusFilter" class="rounded-xl border-slate-200 text-sm font-bold text-slate-600 focus:border-emerald-500 focus:ring-emerald-500 bg-slate-50 w-full md:w-48">
                                <option value="">Semua Status</option>
                                <option value="Pending">Pending</option>
                                <option value="Diterima">Diterima</option>
                                <option value="Cadangan">Cadangan</option>
                                <option value="Ditolak">Ditolak</option>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-y border-slate-100">
                                    <th class="p-4 text-xs font-black text-slate-500 uppercase tracking-widest w-16 text-center">No</th>
                                    <th class="p-4 text-xs font-black text-slate-500 uppercase tracking-widest">Pendaftar</th>
                                    <th class="p-4 text-xs font-black text-slate-500 uppercase tracking-widest">Asal Sekolah</th>
                                    <th class="p-4 text-xs font-black text-slate-500 uppercase tracking-widest">Jurusan</th>
                                    <th class="p-4 text-xs font-black text-slate-500 uppercase tracking-widest">Status</th>
                                    <th class="p-4 text-xs font-black text-slate-500 uppercase tracking-widest text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="(item, index) in pendaftar.data" :key="item.id" class="hover:bg-slate-50/50 transition-colors">
                                    <td class="p-4 text-center text-sm font-bold text-slate-400">
                                        {{ (pendaftar.current_page - 1) * pendaftar.per_page + index + 1 }}
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-3">
                                            <img v-if="item.foto && item.foto !== 'default.png'" :src="`/uploads/ppdb/foto/${item.foto}`" class="w-10 h-10 rounded-full object-cover border border-slate-200">
                                            <div v-else class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400"><i class="fas fa-user"></i></div>
                                            <div>
                                                <div class="font-bold text-slate-800">{{ item.nama_lengkap }}</div>
                                                <div class="text-xs text-slate-500 font-medium">No. {{ item.no_pendaftaran }} &bull; {{ formatDate(item.tgl_daftar) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="text-sm font-bold text-slate-700">{{ item.asal_sekolah }}</div>
                                        <div class="text-xs text-slate-500">{{ item.kabupaten }}</div>
                                    </td>
                                    <td class="p-4">
                                        <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded-md text-xs font-bold border border-slate-200">
                                            {{ item.jurusan_minat }}
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-bold border" :class="getStatusColor(item.status_pendaftaran)">
                                            <i v-if="item.status_pendaftaran === 'Pending'" class="fas fa-clock mr-1"></i>
                                            <i v-else-if="item.status_pendaftaran === 'Diterima'" class="fas fa-check mr-1"></i>
                                            <i v-else-if="item.status_pendaftaran === 'Ditolak'" class="fas fa-times mr-1"></i>
                                            {{ item.status_pendaftaran }}
                                        </span>
                                        <div v-if="item.is_migrated" class="mt-1 text-[10px] text-emerald-600 font-bold"><i class="fas fa-user-check"></i> Sudah Aktif</div>
                                    </td>
                                    <td class="p-4 text-center">
                                        <Link :href="route('admin.ppdb.show', item.id)" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-colors">
                                            <i class="fas fa-search"></i>
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="pendaftar.data.length === 0">
                                    <td colspan="6" class="p-12 text-center text-slate-400">
                                        <i class="fas fa-inbox text-4xl mb-3"></i>
                                        <p class="font-medium">Belum ada data pendaftar.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="pendaftar.links.length > 3" class="p-4 border-t border-slate-100 flex justify-center">
                        <div class="flex gap-1">
                            <Link v-for="(link, i) in pendaftar.links" :key="i" :href="link.url" 
                                  v-html="link.label"
                                  class="px-3 py-1 text-sm rounded-lg border transition-colors"
                                  :class="link.active ? 'bg-emerald-600 text-white border-emerald-600 font-bold' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                                  :preserve-state="true" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
