<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    jurnal: Object,
    siswa: Array,
    existing: Object,
});

const form = useForm({
    id_jurnal: props.jurnal.id,
    status: {}, // will be populated
});

// Initialize form status
props.siswa.forEach(s => {
    form.status[s.id] = props.existing[s.id] || 'Hadir'; // Default Hadir
});

const setAllStatus = (status) => {
    props.siswa.forEach(s => {
        form.status[s.id] = status;
    });
};

const submitAbsen = () => {
    form.post(route('guru.elearning.jurnal.simpan_absen'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Presensi Siswa KBM" />
    <DashboardLayout>
        <div class="space-y-6">
            
            <div class="flex items-center gap-4">
                <Link :href="route('guru.elearning.jurnal.index')" class="w-10 h-10 bg-white border border-gray-200 rounded-xl flex items-center justify-center text-gray-500 hover:bg-gray-50 transition shadow-sm">
                    <i class="fas fa-arrow-left"></i>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Presensi Kelas</h1>
                    <p class="text-sm text-gray-500 mt-1">Catat kehadiran siswa pada sesi mengajar ini.</p>
                </div>
            </div>

            <!-- Detail Jurnal Card -->
            <div class="bg-indigo-600 rounded-2xl p-6 text-white shadow-md relative overflow-hidden flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="relative z-10 flex-1">
                    <div class="flex gap-2 mb-3">
                        <span class="px-2.5 py-1 bg-white/20 backdrop-blur-sm text-xs font-bold rounded-md">{{ jurnal.nama_kelas }}</span>
                        <span class="px-2.5 py-1 bg-white/20 backdrop-blur-sm text-xs font-bold rounded-md">{{ jurnal.nama_mapel }}</span>
                        <span class="px-2.5 py-1 bg-white/20 backdrop-blur-sm text-xs font-bold rounded-md">Jam ke: {{ jurnal.jam_ke }}</span>
                    </div>
                    <h2 class="text-2xl font-bold mb-1">{{ jurnal.materi }}</h2>
                    <p class="text-indigo-200 text-sm"><i class="far fa-calendar-alt mr-1"></i> {{ new Date(jurnal.tanggal).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}</p>
                </div>
                
                <div class="relative z-10 bg-white/10 p-3 rounded-xl backdrop-blur-sm text-center min-w-[120px]">
                    <p class="text-xs text-indigo-200 font-bold uppercase tracking-widest mb-1">Total Siswa</p>
                    <p class="text-3xl font-black">{{ siswa.length }}</p>
                </div>

                <i class="fas fa-clipboard-list absolute -right-4 -bottom-4 text-8xl text-indigo-500/30 -rotate-12"></i>
            </div>

            <!-- Form Presensi -->
            <form @submit.prevent="submitAbsen" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                
                <div class="p-5 border-b border-gray-100 bg-gray-50 flex flex-col md:flex-row justify-between md:items-center gap-4">
                    <h3 class="font-bold text-gray-800 text-lg">Daftar Siswa</h3>
                    
                    <div class="flex items-center gap-2 text-sm bg-white p-1 rounded-lg border border-gray-200 shadow-sm">
                        <span class="px-3 text-gray-500 font-semibold text-xs uppercase">Set Semua:</span>
                        <button type="button" @click="setAllStatus('Hadir')" class="px-3 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold rounded-md transition">Hadir</button>
                        <button type="button" @click="setAllStatus('Sakit')" class="px-3 py-1.5 bg-blue-50 text-blue-700 hover:bg-blue-100 font-bold rounded-md transition">Sakit</button>
                        <button type="button" @click="setAllStatus('Izin')" class="px-3 py-1.5 bg-amber-50 text-amber-700 hover:bg-amber-100 font-bold rounded-md transition">Izin</button>
                        <button type="button" @click="setAllStatus('Alpa')" class="px-3 py-1.5 bg-rose-50 text-rose-700 hover:bg-rose-100 font-bold rounded-md transition">Alpa</button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[600px]">
                        <thead>
                            <tr class="bg-gray-50/50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                                <th class="p-4 font-bold w-16 text-center">No</th>
                                <th class="p-4 font-bold">Nama Siswa</th>
                                <th class="p-4 font-bold">NIS</th>
                                <th class="p-4 font-bold text-center">Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            <tr v-for="(s, index) in siswa" :key="s.id" class="hover:bg-gray-50/30 transition group">
                                <td class="p-4 text-center text-gray-400 font-medium">{{ index + 1 }}</td>
                                <td class="p-4 font-bold text-gray-800">{{ s.nama_lengkap }}</td>
                                <td class="p-4 text-gray-500">{{ s.nis }}</td>
                                <td class="p-4 text-center">
                                    <div class="inline-flex rounded-xl p-1 bg-gray-100 border border-gray-200">
                                        <label class="cursor-pointer">
                                            <input type="radio" :name="`status_${s.id}`" value="Hadir" v-model="form.status[s.id]" class="peer sr-only">
                                            <span class="px-4 py-2 rounded-lg text-sm font-bold transition-all peer-checked:bg-emerald-500 peer-checked:text-white peer-checked:shadow-sm text-gray-500 hover:text-gray-700 block">
                                                Hadir
                                            </span>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="radio" :name="`status_${s.id}`" value="Sakit" v-model="form.status[s.id]" class="peer sr-only">
                                            <span class="px-4 py-2 rounded-lg text-sm font-bold transition-all peer-checked:bg-blue-500 peer-checked:text-white peer-checked:shadow-sm text-gray-500 hover:text-gray-700 block">
                                                Sakit
                                            </span>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="radio" :name="`status_${s.id}`" value="Izin" v-model="form.status[s.id]" class="peer sr-only">
                                            <span class="px-4 py-2 rounded-lg text-sm font-bold transition-all peer-checked:bg-amber-500 peer-checked:text-white peer-checked:shadow-sm text-gray-500 hover:text-gray-700 block">
                                                Izin
                                            </span>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="radio" :name="`status_${s.id}`" value="Alpa" v-model="form.status[s.id]" class="peer sr-only">
                                            <span class="px-4 py-2 rounded-lg text-sm font-bold transition-all peer-checked:bg-rose-500 peer-checked:text-white peer-checked:shadow-sm text-gray-500 hover:text-gray-700 block">
                                                Alpa
                                            </span>
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer / Action -->
                <div class="p-6 border-t border-gray-100 bg-gray-50 flex justify-end">
                    <button type="submit" :disabled="form.processing" class="px-8 py-3 bg-indigo-600 text-white font-bold rounded-xl shadow-md hover:bg-indigo-700 transition inline-flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Presensi & Selesai
                    </button>
                </div>

            </form>
        </div>
    </DashboardLayout>
</template>
