<script setup>
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    jadwal: Array,
    tahun_aktif: Object,
    jam_master: Array,
});

const hariKerja = computed(() => {
    return props.tahun_aktif?.hari_kerja == 5
        ? ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']
        : ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
});

const sortedJamMaster = computed(() => {
    return [...props.jam_master].sort((a, b) => a.urutan - b.urutan);
});

const gridData = computed(() => {
    const map = {};
    const occupied = {};
    const jamMaster = sortedJamMaster.value;

    for (const j of props.jadwal) {
        if (!j.jam_mulai || !j.jam_selesai) continue;

        const startIdx = jamMaster.findIndex(jm =>
            jm.jam_mulai.substring(0, 5) === j.jam_mulai.substring(0, 5)
        );
        if (startIdx === -1) continue;

        let endIdx = startIdx;
        for (let i = startIdx; i < jamMaster.length; i++) {
            endIdx = i;
            if (jamMaster[i].jam_selesai.substring(0, 5) === j.jam_selesai.substring(0, 5)) break;
        }

        const rowspan = endIdx - startIdx + 1;
        const key = `${j.hari}-${startIdx}`;
        map[key] = { jadwal: j, rowspan };

        for (let i = 1; i < rowspan; i++) {
            occupied[`${j.hari}-${startIdx + i}`] = true;
        }
    }

    return { map, occupied };
});

const getCellInfo = (hari, jamIdx) => {
    const key = `${hari}-${jamIdx}`;
    if (gridData.value.occupied[key]) return { type: 'occupied' };
    if (gridData.value.map[key]) return { type: 'filled', ...gridData.value.map[key] };
    return { type: 'empty' };
};
</script>

<template>
    <Head title="Jadwal Mengajar Saya" />

    <DashboardLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                    <i class="fas fa-calendar-day text-lg"></i>
                </div>
                <div>
                    <h2 class="font-bold text-xl text-gray-900 dark:text-white leading-tight">Jadwal Mengajar Saya</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Area Akademik Guru</p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 md:p-8 shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-gray-100 dark:border-gray-700 pb-6">
                        <div>
                            <h3 class="text-xl font-extrabold text-gray-900 dark:text-white">Jadwal Pelajaran</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Tahun Ajaran {{ tahun_aktif?.tahun_ajaran }} - Semester {{ tahun_aktif?.semester }}</p>
                        </div>
                        <button onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-bold flex items-center gap-2 transition-colors">
                            <i class="fas fa-print"></i> Cetak Jadwal
                        </button>
                    </div>

                    <div v-if="jadwal.length === 0" class="py-16 text-center">
                        <div class="w-24 h-24 bg-gray-50 dark:bg-gray-700/50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-calendar-times text-4xl text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Belum Ada Jadwal</h3>
                        <p class="text-gray-500 dark:text-gray-400">Anda belum memiliki jadwal mengajar pada tahun ajaran aktif ini.</p>
                    </div>

                    <div v-else class="overflow-x-auto print:overflow-visible custom-scrollbar rounded-2xl border border-gray-100 dark:border-gray-700">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 dark:text-gray-300 uppercase bg-gray-50 dark:bg-gray-700/50 text-center">
                                <tr>
                                    <th class="px-4 py-4 border-r dark:border-gray-600 w-16 font-bold tracking-wider">Jam</th>
                                    <th class="px-4 py-4 border-r dark:border-gray-600 w-36 font-bold tracking-wider">Waktu</th>
                                    <th v-for="hari in hariKerja" :key="hari" class="px-4 py-4 border-r dark:border-gray-600 font-bold tracking-wider">{{ hari }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(jam, jamIdx) in sortedJamMaster" :key="jam.id" class="border-t border-gray-100 dark:border-gray-700">
                                    <td class="px-4 py-3 font-bold text-center border-r dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">{{ jam.urutan }}</td>
                                    <td class="px-4 py-3 text-center border-r dark:border-gray-700 whitespace-nowrap bg-gray-50/50 dark:bg-gray-800/50 text-xs text-gray-500 dark:text-gray-400 font-medium">
                                        {{ jam.jam_mulai.substring(0, 5) }} - {{ jam.jam_selesai.substring(0, 5) }}
                                    </td>
                                    
                                    <template v-if="jam.is_istirahat">
                                        <td :colspan="hariKerja.length" class="px-4 py-3 text-center bg-orange-50/50 dark:bg-orange-900/20 border-gray-100 dark:border-gray-700">
                                            <span class="text-orange-600 dark:text-orange-400 font-bold tracking-[0.2em] text-xs uppercase">
                                                <i class="fas fa-utensils mr-2 opacity-50"></i>ISTIRAHAT
                                            </span>
                                        </td>
                                    </template>
                                    <template v-else>
                                        <template v-for="hari in hariKerja" :key="hari">
                                            <!-- Empty Cell -->
                                            <td v-if="getCellInfo(hari, jamIdx).type === 'empty'" class="px-4 py-3 border-r dark:border-gray-700 text-center border-gray-100 dark:bg-gray-800"></td>
                                            
                                            <!-- Filled Cell -->
                                            <td v-else-if="getCellInfo(hari, jamIdx).type === 'filled'"
                                                :rowspan="getCellInfo(hari, jamIdx).rowspan"
                                                class="px-3 py-3 border-r border-b dark:border-gray-600 border-primary-200 bg-primary-50 dark:bg-primary-900/20 text-center relative align-middle group hover:bg-primary-100 dark:hover:bg-primary-900/40 transition-colors">
                                                
                                                <div class="flex flex-col items-center justify-center gap-1.5 h-full">
                                                    <div class="font-extrabold text-primary-700 dark:text-primary-400 text-xs leading-tight">
                                                        {{ getCellInfo(hari, jamIdx).jadwal.mapel.nama_mapel }}
                                                    </div>
                                                    <div class="inline-flex items-center gap-1.5 bg-white dark:bg-gray-800 rounded-lg shadow-sm py-1 px-2.5 border border-primary-100 dark:border-primary-800">
                                                        <i class="fas fa-layer-group text-[10px] text-primary-500"></i>
                                                        <span class="text-[10px] font-bold text-gray-700 dark:text-gray-300">
                                                            {{ getCellInfo(hari, jamIdx).jadwal.kelas.nama_kelas }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                        </template>
                                    </template>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <style>
            @media print {
                .print\:overflow-visible { overflow: visible !important; }
                body { background-color: white !important; }
                aside, header, button { display: none !important; }
                .py-8 { padding: 0 !important; }
                .border, .border-b, .border-r { border-color: #000 !important; }
                table { border-collapse: collapse !important; }
                th, td { border: 1px solid #000 !important; color: #000 !important; }
                .bg-primary-50 { background-color: #f3f4f6 !important; }
                .text-primary-700, .text-gray-700, .text-gray-500 { color: #000 !important; }
            }
        </style>
    </DashboardLayout>
</template>
