<script setup>
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    jadwal: Array,
    tahun_aktif: Object,
    jam_master: Array,
    guru: Object,
    total_siswa: Number,
    mengajar_list: Array,
    total_jam: Number
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
            if (jamMaster[i].jam_selesai.substring(0, 5) >= j.jam_selesai.substring(0, 5)) break;
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

const colors = [
    { bg: 'bg-indigo-50/30', border: 'border-indigo-300', text: 'text-indigo-700', badgeBg: 'bg-indigo-50', badgeBorder: 'border-indigo-100', badgeText: 'text-indigo-800' },
    { bg: 'bg-emerald-50/30', border: 'border-emerald-300', text: 'text-emerald-700', badgeBg: 'bg-emerald-50', badgeBorder: 'border-emerald-100', badgeText: 'text-emerald-800' },
    { bg: 'bg-rose-50/30', border: 'border-rose-300', text: 'text-rose-700', badgeBg: 'bg-rose-50', badgeBorder: 'border-rose-100', badgeText: 'text-rose-800' },
    { bg: 'bg-amber-50/30', border: 'border-amber-300', text: 'text-amber-700', badgeBg: 'bg-amber-50', badgeBorder: 'border-amber-100', badgeText: 'text-amber-800' },
    { bg: 'bg-sky-50/30', border: 'border-sky-300', text: 'text-sky-700', badgeBg: 'bg-sky-50', badgeBorder: 'border-sky-100', badgeText: 'text-sky-800' },
    { bg: 'bg-purple-50/30', border: 'border-purple-300', text: 'text-purple-700', badgeBg: 'bg-purple-50', badgeBorder: 'border-purple-100', badgeText: 'text-purple-800' },
];

const getColorClass = (id) => {
    return colors[(id || 0) % colors.length];
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
                        <div class="flex items-center gap-4">
                            <img v-if="$page.props.web_settings?.logo" :src="$page.props.web_settings.logo.includes('default') ? '/images/' + $page.props.web_settings.logo : '/uploads/identitas/' + $page.props.web_settings.logo" class="h-12 w-12 object-contain hidden print:block md:block" alt="Logo Sekolah" />
                            <div>
                                <h3 class="text-xl font-extrabold text-gray-900 dark:text-white">Jadwal Pelajaran</h3>
                                <p class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ $page.props.web_settings?.nama_sekolah || 'Nama Sekolah' }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Tahun Ajaran {{ tahun_aktif?.tahun_ajaran }} - Semester {{ tahun_aktif?.semester }}</p>
                            </div>
                        </div>
                        <button onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-bold flex items-center gap-2 transition-colors print:hidden">
                            <i class="fas fa-print"></i> Cetak Jadwal
                        </button>
                    </div>

                    <!-- Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                        <div class="bg-indigo-50 rounded-2xl p-4 border border-indigo-100 flex flex-col justify-center">
                            <div class="text-indigo-600 font-bold text-xs uppercase mb-1"><i class="fas fa-user-tie mr-1"></i> Identitas Guru</div>
                            <div class="font-extrabold text-gray-900 text-lg line-clamp-1" :title="guru?.nama_lengkap">{{ guru?.nama_lengkap || 'Guru' }}</div>
                            <div class="text-sm text-gray-500">{{ guru?.nip || '-' }}</div>
                        </div>
                        <div class="bg-emerald-50 rounded-2xl p-4 border border-emerald-100 flex flex-col justify-center">
                            <div class="text-emerald-600 font-bold text-xs uppercase mb-1"><i class="fas fa-clock mr-1"></i> Total Jam Ajar</div>
                            <div class="font-extrabold text-gray-900 text-2xl">{{ total_jam }} <span class="text-sm font-semibold text-gray-500">Jam/Minggu</span></div>
                        </div>
                        <div class="bg-amber-50 rounded-2xl p-4 border border-amber-100 flex flex-col justify-center">
                            <div class="text-amber-600 font-bold text-xs uppercase mb-1"><i class="fas fa-users mr-1"></i> Total Siswa</div>
                            <div class="font-extrabold text-gray-900 text-2xl">{{ total_siswa }} <span class="text-sm font-semibold text-gray-500">Siswa Diajar</span></div>
                        </div>
                        <div class="bg-rose-50 rounded-2xl p-4 border border-rose-100 flex flex-col justify-center h-full max-h-24 overflow-y-auto custom-scrollbar">
                            <div class="text-rose-600 font-bold text-xs uppercase mb-1 sticky top-0 bg-rose-50"><i class="fas fa-book mr-1"></i> Kelas & Mapel</div>
                            <div class="space-y-1">
                                <div v-for="(item, idx) in mengajar_list" :key="idx" class="text-xs font-semibold text-gray-700 flex justify-between border-b border-rose-100 pb-1 last:border-0 last:pb-0">
                                    <span class="truncate pr-2">{{ item.mapel }}</span>
                                    <span class="text-rose-700 font-bold whitespace-nowrap">{{ item.kelas }}</span>
                                </div>
                            </div>
                        </div>
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
                                                class="h-[1px] border-r border-b border-gray-200 p-1.5 align-top relative group"
                                                :class="getColorClass(getCellInfo(hari, jamIdx).jadwal.id_kelas).bg">
                                                
                                                <div class="h-full w-full rounded-xl p-2.5 flex flex-col items-center justify-center text-center transition hover:shadow-md"
                                                    :class="[
                                                        getColorClass(getCellInfo(hari, jamIdx).jadwal.id_kelas).badgeBg,
                                                        getColorClass(getCellInfo(hari, jamIdx).jadwal.id_kelas).border,
                                                        'border'
                                                    ]">
                                                    <div class="font-extrabold text-xs mb-1"
                                                        :class="getColorClass(getCellInfo(hari, jamIdx).jadwal.id_kelas).text">
                                                        {{ getCellInfo(hari, jamIdx).jadwal.mapel.nama_mapel }}
                                                    </div>
                                                    <div class="inline-flex items-center gap-1.5 rounded-md shadow-sm py-0.5 px-2 mb-1 border bg-white"
                                                        :class="getColorClass(getCellInfo(hari, jamIdx).jadwal.id_kelas).badgeBorder">
                                                        <span class="text-[10px] font-extrabold"
                                                            :class="getColorClass(getCellInfo(hari, jamIdx).jadwal.id_kelas).text">
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
    </DashboardLayout>
</template>

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
