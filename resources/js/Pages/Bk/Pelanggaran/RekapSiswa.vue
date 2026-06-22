<script setup>
import { ref, watch, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    kelas: Array,
    siswa: Array,
    set_sp: Object,
    selected_kelas: [String, Number]
});

const filterKelas = ref(props.selected_kelas || '');

watch(filterKelas, (newVal) => {
    router.get(route('bk.pelanggaran.rekap'), { kelas_id: newVal }, { preserveState: true, replace: true });
});

const getStatusSP = (sisaPoin) => {
    if (!props.set_sp) return { text: 'Aman', color: 'bg-emerald-100 text-emerald-700 border-emerald-200' };
    
    if (sisaPoin <= props.set_sp.sp_3) {
        return { text: 'SP 3 (DO)', color: 'bg-red-100 text-red-700 border-red-200' };
    } else if (sisaPoin <= props.set_sp.sp_2) {
        return { text: 'SP 2', color: 'bg-orange-100 text-orange-700 border-orange-200' };
    } else if (sisaPoin <= props.set_sp.sp_1) {
        return { text: 'SP 1', color: 'bg-yellow-100 text-yellow-700 border-yellow-200' };
    }
    
    return { text: 'Aman', color: 'bg-emerald-100 text-emerald-700 border-emerald-200' };
};

const getSisaPoinColor = (sisaPoin) => {
    if (!props.set_sp) return 'text-emerald-600';
    
    if (sisaPoin <= props.set_sp.sp_3) return 'text-red-600';
    if (sisaPoin <= props.set_sp.sp_2) return 'text-orange-600';
    if (sisaPoin <= props.set_sp.sp_1) return 'text-yellow-600';
    
    return 'text-emerald-600';
};
</script>

<template>
    <Head title="Rekap Poin Siswa" />

    <DashboardLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Header -->
                <div class="bg-gradient-to-br from-indigo-700 via-purple-800 to-indigo-900 rounded-3xl p-8 shadow-2xl relative overflow-hidden flex flex-col md:flex-row items-center justify-between text-white border border-indigo-600/50">
                    <div class="absolute -top-24 -right-24 w-64 h-64 bg-white/10 rounded-full blur-3xl mix-blend-screen"></div>
                    <div class="relative z-10 flex-1">
                        <h2 class="text-3xl font-black tracking-tight mb-2">Rekap Poin Kedisiplinan</h2>
                        <p class="text-indigo-200/90 font-medium text-sm">Pantau sisa poin dan status SP siswa secara kolektif per kelas.</p>
                    </div>
                    <div class="relative z-10 mt-6 md:mt-0">
                        <div class="bg-white/20 backdrop-blur-md px-5 py-3 rounded-2xl border border-white/20 flex items-center gap-3">
                            <i class="fas fa-users text-2xl text-indigo-200"></i>
                            <div>
                                <p class="text-[10px] font-bold text-indigo-200 uppercase tracking-widest mb-0.5">Filter Data Kelas</p>
                                <select v-model="filterKelas" class="bg-transparent border-none text-white font-bold p-0 focus:ring-0 cursor-pointer">
                                    <option value="" class="text-gray-900">-- Pilih Kelas --</option>
                                    <option v-for="k in kelas" :key="k.id" :value="k.id" class="text-gray-900">
                                        {{ k.nama_kelas }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State jika belum pilih kelas -->
                <div v-if="!selected_kelas" class="bg-white rounded-3xl shadow-sm border border-gray-100 p-12 text-center min-h-[400px] flex flex-col items-center justify-center">
                    <div class="w-24 h-24 bg-indigo-50 text-indigo-300 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-chalkboard-teacher text-4xl"></i>
                    </div>
                    <h3 class="text-xl font-black text-gray-800 mb-2">Pilih Kelas Terlebih Dahulu</h3>
                    <p class="text-gray-500 max-w-md">Silakan gunakan filter di pojok kanan atas untuk memilih kelas yang ingin ditampilkan rekap poin kedisiplinannya.</p>
                </div>

                <!-- Table Riwayat -->
                <div v-else class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col min-h-[400px]">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex flex-col md:flex-row justify-between items-center gap-4">
                        <div>
                            <h3 class="font-black text-gray-800 text-lg">Data Sisa Poin Kelas</h3>
                            <p class="text-xs font-bold text-gray-500 mt-1" v-if="siswa && siswa.length > 0">Menampilkan {{ siswa.length }} Siswa</p>
                        </div>
                        <div class="flex gap-2 text-[10px] font-bold">
                            <span class="px-2 py-1 rounded border bg-emerald-50 text-emerald-600 border-emerald-200">Aman (> {{ set_sp?.sp_1 || 50 }})</span>
                            <span class="px-2 py-1 rounded border bg-yellow-50 text-yellow-600 border-yellow-200">SP 1 (<= {{ set_sp?.sp_1 || 50 }})</span>
                            <span class="px-2 py-1 rounded border bg-orange-50 text-orange-600 border-orange-200">SP 2 (<= {{ set_sp?.sp_2 || 30 }})</span>
                            <span class="px-2 py-1 rounded border bg-red-50 text-red-600 border-red-200">SP 3 (<= {{ set_sp?.sp_3 || 0 }})</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-500 uppercase bg-white border-b border-gray-100 font-bold sticky top-0">
                                <tr>
                                    <th class="px-6 py-4 w-16 text-center">No</th>
                                    <th class="px-6 py-4">Nama Siswa</th>
                                    <th class="px-6 py-4">NISN</th>
                                    <th class="px-6 py-4 text-center">Sisa Poin</th>
                                    <th class="px-6 py-4 text-center">Status Kedisiplinan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="(row, index) in siswa" :key="row.id" class="hover:bg-gray-50/80 transition-colors">
                                    <td class="px-6 py-4 text-center text-gray-400 font-bold">{{ index + 1 }}</td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">{{ row.nama_lengkap }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 font-mono text-xs">
                                        {{ row.nisn || '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <h3 class="text-2xl font-black" :class="getSisaPoinColor(row.sisa_poin)">
                                            {{ row.sisa_poin }}
                                        </h3>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-3 py-1.5 rounded-lg text-xs font-bold border" :class="getStatusSP(row.sisa_poin).color">
                                            {{ getStatusSP(row.sisa_poin).text }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="siswa && siswa.length === 0">
                                    <td colspan="5" class="px-6 py-16 text-center text-gray-500">
                                        <div class="w-16 h-16 bg-gray-50 border border-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                            <i class="fas fa-users-slash text-2xl text-gray-400"></i>
                                        </div>
                                        <p class="font-bold text-base text-gray-700">Tidak ada siswa</p>
                                        <p class="text-sm">Kelas ini belum memiliki siswa yang terdaftar.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </DashboardLayout>
</template>
