<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    semua_kelas: Array,
    list_kelas_aktif: Array,
    active_class_ids: Array,
    kelas_aktif: [String, Number],
    total_siswa: Number,
    siswa_ditempatkan: Number,
    siswa_belum: Number,
    pkl_aktif: Number,
    pkl_selesai: Number,
    total_dudi: Number,
    persen_penempatan: Number,
    performa: Array,
    rata_global: Number
});

const showModalKelas = ref(false);

const formKelas = useForm({
    kelas_ids: props.active_class_ids || []
});

const submitKelas = () => {
    formKelas.post(route('admin.pkl.set_kelas_pkl'), {
        preserveScroll: true,
        onSuccess: () => showModalKelas.value = false
    });
};

const filterByKelas = (e) => {
    router.get(route('admin.pkl.dashboard'), { kelas_id: e.target.value }, { preserveState: true });
};

const getGradeColor = (grade) => {
    switch(grade) {
        case 'A': return 'bg-emerald-100 text-emerald-800 border-emerald-200';
        case 'B': return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'C': return 'bg-amber-100 text-amber-800 border-amber-200';
        case 'D': return 'bg-orange-100 text-orange-800 border-orange-200';
        default: return 'bg-red-100 text-red-800 border-red-200';
    }
};

const getMedalColor = (index) => {
    if (index === 0) return 'text-yellow-400';
    if (index === 1) return 'text-gray-400';
    if (index === 2) return 'text-amber-600';
    return 'text-gray-300';
};
</script>

<template>
    <Head title="Dashboard PKL" />

    <DashboardLayout>
        <div class="space-y-6 animate-fade-in-up">
            
            <!-- Hero Section -->
            <div class="relative overflow-hidden bg-gradient-to-br from-indigo-600 via-blue-600 to-indigo-800 rounded-3xl shadow-xl p-8 sm:p-10 text-white">
                <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-white opacity-10 blur-3xl"></div>
                <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-48 h-48 rounded-full bg-white opacity-10 blur-2xl"></div>
                
                <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <span class="px-3 py-1 bg-white/20 backdrop-blur-md text-white text-xs font-bold rounded-full border border-white/20">
                                Monitoring Center
                            </span>
                            <span class="text-indigo-100 text-sm">Praktik Kerja Lapangan</span>
                        </div>
                        <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-2">Dashboard PKL</h1>
                        <p class="text-indigo-100 max-w-xl text-sm leading-relaxed">
                            Pusat pemantauan data penempatan siswa, performa DUDI, dan KPI Guru Pembimbing.
                        </p>
                    </div>
                    
                    <div class="flex flex-col items-center sm:items-end gap-3 w-full md:w-auto">
                        <button @click="showModalKelas = true" class="w-full sm:w-auto px-5 py-2.5 bg-white text-indigo-700 hover:bg-indigo-50 font-bold rounded-xl shadow-lg hover:shadow-xl transition-all flex items-center justify-center gap-2">
                            <i class="fas fa-sliders-h"></i> Set Target Kelas
                        </button>
                        <div class="text-xs text-indigo-100 bg-black/20 px-4 py-2 rounded-lg border border-white/10 flex items-center gap-2">
                            <i class="fas fa-info-circle text-indigo-300"></i>
                            <span>{{ list_kelas_aktif.length }} Kelas ditargetkan tahun ini.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="$page.props.flash?.message" class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl shadow-sm flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                    <i class="fas fa-check text-emerald-600"></i>
                </div>
                <div class="font-medium">{{ $page.props.flash.message }}</div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 group hover:border-indigo-200 transition-colors">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <h3 class="text-3xl font-black text-gray-900 dark:text-white">{{ total_siswa }}</h3>
                    <p class="text-sm font-medium text-gray-500 mt-1">Siswa Target PKL</p>
                </div>
                
                <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 group hover:border-emerald-200 transition-colors">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                            <i class="fas fa-briefcase"></i>
                        </div>
                    </div>
                    <h3 class="text-3xl font-black text-gray-900 dark:text-white">{{ siswa_ditempatkan }}</h3>
                    <p class="text-sm font-medium text-gray-500 mt-1">Sudah Ditempatkan</p>
                </div>

                <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 group hover:border-orange-200 transition-colors relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4" v-if="siswa_belum > 0">
                        <span class="flex h-3 w-3">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-3 w-3 bg-orange-500"></span>
                        </span>
                    </div>
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                            <i class="fas fa-user-clock"></i>
                        </div>
                    </div>
                    <h3 class="text-3xl font-black text-gray-900 dark:text-white">{{ siswa_belum }}</h3>
                    <p class="text-sm font-medium text-gray-500 mt-1">Belum Ditempatkan</p>
                </div>

                <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 group hover:border-purple-200 transition-colors">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                            <i class="fas fa-building"></i>
                        </div>
                    </div>
                    <h3 class="text-3xl font-black text-gray-900 dark:text-white">{{ total_dudi }}</h3>
                    <p class="text-sm font-medium text-gray-500 mt-1">Total Mitra DU/DI</p>
                </div>
            </div>

            <!-- Progress Card -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex justify-between items-end mb-2">
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white">Progres Penempatan</h4>
                        <p class="text-xs text-gray-500">Dari total {{ total_siswa }} siswa target</p>
                    </div>
                    <div class="text-right">
                        <span class="text-2xl font-black" :class="persen_penempatan >= 100 ? 'text-emerald-500' : 'text-indigo-600'">{{ persen_penempatan }}%</span>
                    </div>
                </div>
                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-4 mb-1 overflow-hidden relative">
                    <div class="h-4 rounded-full transition-all duration-1000 ease-out" 
                         :class="persen_penempatan >= 100 ? 'bg-emerald-500' : 'bg-indigo-600'" 
                         :style="`width: ${persen_penempatan}%`">
                         <div class="absolute top-0 left-0 bottom-0 right-0 bg-white/20" style="background-image: linear-gradient(45deg, rgba(255,255,255,.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,.15) 50%, rgba(255,255,255,.15) 75%, transparent 75%, transparent); background-size: 1rem 1rem;"></div>
                    </div>
                </div>
            </div>

            <!-- Leaderboard -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-gray-50 dark:bg-gray-800/50">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <i class="fas fa-trophy text-amber-500"></i> Leaderboard Kinerja Kemitraan
                        </h3>
                        <p class="text-sm text-gray-500">Peringkat performa DU/DI berdasarkan KPI Guru & Siswa.</p>
                    </div>
                    <div class="w-full sm:w-auto">
                        <select @change="filterByKelas" :value="kelas_aktif || ''" class="w-full px-4 py-2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all text-sm font-medium">
                            <option value="">Semua Kelas PKL</option>
                            <option v-for="k in list_kelas_aktif" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                        </select>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 font-bold uppercase text-xs">
                            <tr>
                                <th class="px-6 py-4 text-center w-16">Rank</th>
                                <th class="px-6 py-4">Mitra DU/DI</th>
                                <th class="px-6 py-4">Guru Pembimbing</th>
                                <th class="px-6 py-4 text-center">Jml Siswa</th>
                                <th class="px-6 py-4 text-center">Kinerja Siswa</th>
                                <th class="px-6 py-4 text-center">Kinerja Guru</th>
                                <th class="px-6 py-4 text-center">Skor Akhir</th>
                                <th class="px-6 py-4 text-center">Grade</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-if="performa.length === 0">
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                                        <i class="fas fa-chart-line text-2xl text-gray-400"></i>
                                    </div>
                                    <p class="font-bold text-lg">Belum Ada Data Penempatan</p>
                                    <p class="text-sm mt-1">Silakan tempatkan siswa di DU/DI terlebih dahulu.</p>
                                </td>
                            </tr>
                            <tr v-for="(p, index) in performa" :key="index" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 text-center">
                                    <div v-if="index < 3" class="text-2xl drop-shadow-sm" :class="getMedalColor(index)">
                                        <i class="fas fa-medal"></i>
                                    </div>
                                    <div v-else class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center font-bold text-gray-500 mx-auto">
                                        {{ index + 1 }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                                            <i class="fas fa-building"></i>
                                        </div>
                                        {{ p.nama_dudi }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                    {{ p.nama_guru }}
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-indigo-600">
                                    {{ p.total_siswa }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="text-sm font-bold">{{ p.rata_siswa }}</div>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                        <div class="bg-blue-500 h-1.5 rounded-full" :style="`width: ${p.rata_siswa}%`"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="text-sm font-bold">{{ p.kpi_guru }}</div>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                        <div class="bg-emerald-500 h-1.5 rounded-full" :style="`width: ${p.kpi_guru}%`"></div>
                                    </div>
                                    <div class="text-[10px] text-gray-500 mt-0.5">{{ p.kunjungan_guru }} Kunjungan</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-lg font-black" :class="p.skor_akumulasi >= 80 ? 'text-emerald-600' : 'text-gray-900'">
                                        {{ p.skor_akumulasi }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 rounded-xl text-sm font-black border" :class="getGradeColor(p.grade)">
                                        {{ p.grade }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Modal Set Kelas PKL -->
        <div v-if="showModalKelas" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-gray-800 w-full max-w-lg rounded-3xl shadow-xl overflow-hidden animate-fade-in-up">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Pengaturan Kelas PKL</h3>
                    <button @click="showModalKelas = false" class="text-gray-400 hover:text-red-500 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="p-6">
                    <div class="bg-indigo-50 text-indigo-800 p-4 rounded-xl text-sm mb-6 flex gap-3">
                        <i class="fas fa-info-circle text-xl shrink-0 mt-0.5"></i>
                        <div>
                            Pilih kelas mana saja yang diwajibkan mengikuti PKL. Data siswa dari kelas yang dipilih akan muncul di statistik penempatan.
                        </div>
                    </div>

                    <form @submit.prevent="submitKelas">
                        <div class="space-y-2 max-h-64 overflow-y-auto pr-2 mb-6 border border-gray-100 rounded-xl p-2">
                            <label v-for="k in semua_kelas" :key="k.id" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 cursor-pointer border border-transparent hover:border-gray-200 transition-colors">
                                <span class="font-bold text-gray-700">{{ k.nama_kelas }}</span>
                                <input type="checkbox" v-model="formKelas.kelas_ids" :value="k.id" class="w-5 h-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                            </label>
                        </div>

                        <div class="flex justify-end gap-2 border-t border-gray-100 pt-6">
                            <button type="button" @click="showModalKelas = false" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition-colors">Batal</button>
                            <button type="submit" :disabled="formKelas.processing" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-sm transition-colors flex items-center gap-2">
                                <i v-if="formKelas.processing" class="fas fa-spinner fa-spin"></i>
                                <span v-else>Simpan Pengaturan</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </DashboardLayout>
</template>
