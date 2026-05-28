<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import axios from 'axios';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    jadwal: Array,
    kelas: Array,
    mapel: Array,
    guru: Array,
    jurusan: Array,
    tahun_aktif: Object,
    jam_master: Array,
    mapping: Array,
    filters: Object,
});

// =============================================
// STATE
// =============================================
const hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
const selectedKelas = ref(props.filters?.id_kelas ? parseInt(props.filters.id_kelas) : '');
const openModal = ref(false);
const openImportModal = ref(false);
const isEdit = ref(false);
const selectedJadwal = ref(null);

// =============================================
// FILTER: Reload when kelas changes
// =============================================
watch(selectedKelas, (val) => {
    if (val) {
        router.get('/admin/kurikulum/jadwal-pelajaran', { id_kelas: val }, {
            preserveState: true,
            preserveScroll: true,
            replace: true
        });
    }
});

// =============================================
// FORM
// =============================================
const form = useForm({
    id_tahun_ajaran: props.tahun_aktif?.id || '',
    id_kelas: '',
    id_mapel: '',
    id_guru: '',
    hari: 'Senin',
    start_jp: '',
    durasi_jp: 1,
    jam_mulai: '',
    jam_selesai: ''
});

const importForm = useForm({ file: null });
const handleFileImport = (e) => { importForm.file = e.target.files[0]; };
const submitImport = () => {
    importForm.post('/admin/kurikulum/jadwal-pelajaran/import', {
        preserveScroll: true,
        onSuccess: () => { openImportModal.value = false; importForm.reset(); },
    });
};

// =============================================
// MAPPED MAPELS (from Pembagian Tugas)
// =============================================
const mappedMapels = computed(() => {
    const kelasId = selectedKelas.value || form.id_kelas;
    if (!kelasId || !props.mapping) return props.mapel; // fallback: show all
    const maps = props.mapping.filter(m => m.id_kelas === parseInt(kelasId));
    if (maps.length === 0) return props.mapel; // fallback if no mapping yet
    return props.mapel.filter(mapel => maps.some(m => m.id_mapel === mapel.id));
});

// Auto-fill Guru from mapping when mapel changes
watch(() => form.id_mapel, (newMapel) => {
    if (!newMapel || !form.id_kelas || !props.mapping) return;
    const map = props.mapping.find(m => m.id_kelas === parseInt(form.id_kelas) && m.id_mapel === parseInt(newMapel));
    if (map) {
        form.id_guru = map.id_guru;
    }
});

// Auto Calculate Jam Mulai & Jam Selesai
watch(() => [form.start_jp, form.durasi_jp], ([startJp, durasi]) => {
    if (!startJp || !durasi) return;
    const jamMaster = props.jam_master;
    const jamAwal = jamMaster.find(j => parseInt(j.urutan) === parseInt(startJp));

    if (jamAwal) {
        form.jam_mulai = jamAwal.jam_mulai.substring(0, 5);
        let countJP = 0;
        let targetJam = null;
        const startIndex = jamMaster.findIndex(j => parseInt(j.urutan) === parseInt(startJp));
        for (let i = startIndex; i < jamMaster.length; i++) {
            if (jamMaster[i].is_istirahat == 0) {
                countJP++;
            }
            if (countJP === parseInt(durasi)) {
                targetJam = jamMaster[i];
                break;
            }
        }
        if (targetJam) {
            form.jam_selesai = targetJam.jam_selesai.substring(0, 5);
        } else {
            form.jam_selesai = jamMaster[jamMaster.length - 1].jam_selesai.substring(0, 5);
        }
    }
});

// =============================================
// GRID LOGIC
// =============================================
const sortedJamMaster = computed(() => {
    return [...props.jam_master].sort((a, b) => a.urutan - b.urutan);
});

const gridData = computed(() => {
    const map = {};       // key: `hari-jamIdx` -> { jadwal, rowspan }
    const occupied = {};  // key: `hari-jamIdx` -> true (covered by rowspan above)

    for (const j of props.jadwal) {
        const jamMaster = sortedJamMaster.value;
        // Find start index in jam_master
        const startIdx = jamMaster.findIndex(jm =>
            jm.jam_mulai.substring(0, 5) === j.jam_mulai.substring(0, 5)
        );
        if (startIdx === -1) continue;

        // Find end index
        let endIdx = startIdx;
        for (let i = startIdx; i < jamMaster.length; i++) {
            endIdx = i;
            if (jamMaster[i].jam_selesai.substring(0, 5) === j.jam_selesai.substring(0, 5)) break;
        }

        const rowspan = endIdx - startIdx + 1;
        const key = `${j.hari}-${startIdx}`;
        map[key] = { jadwal: j, rowspan };

        // Mark subsequent cells as occupied
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

// =============================================
// COLOR PALETTE for Mapel
// =============================================
const mapelColorPalette = [
    { bg: 'bg-blue-100 dark:bg-blue-900/40', text: 'text-blue-800 dark:text-blue-200', border: 'border-l-blue-500', accent: 'bg-blue-500' },
    { bg: 'bg-emerald-100 dark:bg-emerald-900/40', text: 'text-emerald-800 dark:text-emerald-200', border: 'border-l-emerald-500', accent: 'bg-emerald-500' },
    { bg: 'bg-violet-100 dark:bg-violet-900/40', text: 'text-violet-800 dark:text-violet-200', border: 'border-l-violet-500', accent: 'bg-violet-500' },
    { bg: 'bg-amber-100 dark:bg-amber-900/40', text: 'text-amber-800 dark:text-amber-200', border: 'border-l-amber-500', accent: 'bg-amber-500' },
    { bg: 'bg-rose-100 dark:bg-rose-900/40', text: 'text-rose-800 dark:text-rose-200', border: 'border-l-rose-500', accent: 'bg-rose-500' },
    { bg: 'bg-cyan-100 dark:bg-cyan-900/40', text: 'text-cyan-800 dark:text-cyan-200', border: 'border-l-cyan-500', accent: 'bg-cyan-500' },
    { bg: 'bg-fuchsia-100 dark:bg-fuchsia-900/40', text: 'text-fuchsia-800 dark:text-fuchsia-200', border: 'border-l-fuchsia-500', accent: 'bg-fuchsia-500' },
    { bg: 'bg-lime-100 dark:bg-lime-900/40', text: 'text-lime-800 dark:text-lime-200', border: 'border-l-lime-500', accent: 'bg-lime-500' },
    { bg: 'bg-indigo-100 dark:bg-indigo-900/40', text: 'text-indigo-800 dark:text-indigo-200', border: 'border-l-indigo-500', accent: 'bg-indigo-500' },
    { bg: 'bg-orange-100 dark:bg-orange-900/40', text: 'text-orange-800 dark:text-orange-200', border: 'border-l-orange-500', accent: 'bg-orange-500' },
];

const getMapelColor = (mapelId) => {
    if (!mapelId) return mapelColorPalette[0];
    return mapelColorPalette[mapelId % mapelColorPalette.length];
};

// =============================================
// MODAL ACTIONS
// =============================================
const openAddModal = (hari, jamIdx) => {
    const jm = sortedJamMaster.value[jamIdx];
    if (!jm || jm.is_istirahat) return;

    isEdit.value = false;
    selectedJadwal.value = null;
    form.reset();
    form.id_tahun_ajaran = props.tahun_aktif?.id || '';
    form.id_kelas = selectedKelas.value;
    form.hari = hari;
    form.start_jp = jm.urutan;
    form.durasi_jp = 1;
    openModal.value = true;
};

const openEditModal = (item) => {
    isEdit.value = true;
    selectedJadwal.value = item;
    form.id_tahun_ajaran = item.id_tahun_ajaran;
    form.id_kelas = item.id_kelas;
    form.id_mapel = item.id_mapel;
    form.id_guru = item.id_guru;
    form.hari = item.hari;

    // Find start_jp from jam_master
    const jm = sortedJamMaster.value.find(j => j.jam_mulai.substring(0,5) === item.jam_mulai.substring(0,5));
    form.start_jp = jm ? jm.urutan : '';

    // Calculate durasi (count non-istirahat slots between start and end)
    const startIdx = sortedJamMaster.value.findIndex(j => j.jam_mulai.substring(0,5) === item.jam_mulai.substring(0,5));
    const endIdx = sortedJamMaster.value.findIndex(j => j.jam_selesai.substring(0,5) === item.jam_selesai.substring(0,5));
    let dur = 0;
    if (startIdx !== -1 && endIdx !== -1) {
        for (let i = startIdx; i <= endIdx; i++) {
            if (!sortedJamMaster.value[i].is_istirahat) dur++;
        }
    }
    form.durasi_jp = dur || 1;

    form.jam_mulai = item.jam_mulai.substring(0, 5);
    form.jam_selesai = item.jam_selesai.substring(0, 5);
    openModal.value = true;
};

const submitForm = () => {
    if (isEdit.value) {
        form.put(`/admin/kurikulum/jadwal-pelajaran/${selectedJadwal.value.id}`, {
            preserveScroll: true,
            onSuccess: () => { openModal.value = false; },
        });
    } else {
        form.post('/admin/kurikulum/jadwal-pelajaran', {
            preserveScroll: true,
            onSuccess: () => {
                openModal.value = false;
                form.reset();
                form.id_tahun_ajaran = props.tahun_aktif?.id || '';
            },
        });
    }
};

const hapus = (id) => {
    if (confirm('Yakin ingin menghapus jadwal ini?')) {
        router.delete(`/admin/kurikulum/jadwal-pelajaran/${id}`, { preserveScroll: true });
    }
};

const buildPrintUrl = () => {
    let url = '/admin/kurikulum/jadwal-pelajaran/cetak?';
    if (selectedKelas.value) url += `id_kelas=${selectedKelas.value}&`;
    return url;
};

// Get selected kelas name
const selectedKelasName = computed(() => {
    if (!selectedKelas.value) return '';
    const k = props.kelas.find(k => k.id === parseInt(selectedKelas.value));
    return k ? k.nama_kelas : '';
});
</script>

<template>
    <Head title="Jadwal Pelajaran" />

    <DashboardLayout>
        <div class="flex flex-col h-full">
            <div class="w-full mx-auto">

                <!-- Sticky Header -->
                <div class="sticky top-0 z-20 bg-[#f4f6f8] dark:bg-gray-900 pt-6 pb-3 px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                        <div>
                            <h2 class="font-bold text-2xl text-gray-900 dark:text-white tracking-tight">
                                <i class="fas fa-calendar-alt text-primary-500 mr-2"></i>Jadwal Pelajaran
                            </h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Tahun Ajaran: <span class="font-bold text-primary-600">{{ tahun_aktif ? tahun_aktif.tahun_ajaran : 'Belum Disetting' }}</span>
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <Link href="/admin/kurikulum/pembagian-tugas" class="bg-indigo-50 text-indigo-600 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-400 font-semibold py-2.5 px-4 rounded-xl text-sm transition-all flex items-center gap-2 border border-indigo-200 dark:border-indigo-800">
                                <i class="fas fa-tasks"></i> Pembagian Tugas
                            </Link>
                            <Link href="/admin/kurikulum/jadwal-pelajaran/rekap" class="bg-purple-50 text-purple-600 hover:bg-purple-100 dark:bg-purple-900/30 dark:text-purple-400 font-semibold py-2.5 px-4 rounded-xl text-sm transition-all flex items-center gap-2 border border-purple-200 dark:border-purple-800">
                                <i class="fas fa-list"></i> Rekap Jam
                            </Link>
                            <button @click="openImportModal = true" class="bg-green-50 text-green-600 hover:bg-green-100 dark:bg-green-900/30 dark:text-green-400 font-semibold py-2.5 px-4 rounded-xl text-sm transition-all flex items-center gap-2 border border-green-200 dark:border-green-800">
                                <i class="fas fa-upload"></i> Import
                            </button>
                            <a v-if="selectedKelas" :href="buildPrintUrl()" target="_blank" class="bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400 font-semibold py-2.5 px-4 rounded-xl text-sm transition-all flex items-center gap-2 border border-blue-200 dark:border-blue-800">
                                <i class="fas fa-print"></i> Cetak
                            </a>
                        </div>
                    </div>

                    <!-- Kelas Selector -->
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row items-center gap-4">
                        <div class="flex items-center gap-3 w-full sm:w-auto sm:min-w-[300px]">
                            <span class="text-sm text-gray-500 dark:text-gray-400 font-bold whitespace-nowrap"><i class="fas fa-chalkboard mr-1"></i> Pilih Kelas:</span>
                            <select v-model="selectedKelas" class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500 shadow-sm text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2.5 pl-3 pr-8 cursor-pointer font-semibold">
                                <option value="">-- Pilih Kelas untuk Melihat Grid --</option>
                                <option v-for="k in kelas" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                            </select>
                        </div>
                        <div v-if="selectedKelas" class="text-sm text-gray-500 dark:text-gray-400">
                            <span class="bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 font-bold px-3 py-1.5 rounded-lg text-xs">
                                <i class="fas fa-eye mr-1"></i> Menampilkan jadwal: {{ selectedKelasName }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Scrollable Content -->
                <div class="px-4 sm:px-6 lg:px-8 pb-8 mt-4">
                    <!-- Flash Message -->
                    <div v-if="$page.props.flash?.message" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-400 p-4 mb-6 rounded-2xl shadow-sm flex items-center gap-3">
                        <i class="fas fa-check-circle text-lg"></i>
                        <span v-html="$page.props.flash.message"></span>
                    </div>
                    <div v-if="$page.props.flash?.error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-400 p-4 mb-6 rounded-2xl shadow-sm flex items-center gap-3">
                        <i class="fas fa-exclamation-circle text-lg"></i> {{ $page.props.flash.error }}
                    </div>

                    <!-- Empty State: No Kelas Selected -->
                    <div v-if="!selectedKelas" class="bg-white dark:bg-gray-800 shadow-xl shadow-gray-200/50 dark:shadow-none rounded-3xl border border-gray-100 dark:border-gray-700 p-16 text-center">
                        <div class="w-24 h-24 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-calendar-alt text-4xl text-primary-500"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Pilih Kelas untuk Memulai</h3>
                        <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                            Silakan pilih kelas di atas untuk menampilkan grid jadwal pelajaran. Anda dapat langsung mengklik kotak kosong untuk menambah jadwal baru.
                        </p>
                    </div>

                    <!-- VISUAL GRID TIMETABLE -->
                    <div v-if="selectedKelas" class="bg-white dark:bg-gray-800 shadow-xl shadow-gray-200/50 dark:shadow-none rounded-3xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse timetable-grid">
                                <!-- Header Row: Days -->
                                <thead>
                                    <tr>
                                        <th class="sticky left-0 z-10 bg-gray-50 dark:bg-gray-700/80 px-4 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-r border-gray-200 dark:border-gray-600 w-28 min-w-[112px]">
                                            Jam
                                        </th>
                                        <th v-for="hari in hariList" :key="hari"
                                            class="bg-gray-50 dark:bg-gray-700/80 px-3 py-4 text-center text-xs font-bold uppercase tracking-wider border-b border-r border-gray-200 dark:border-gray-600 min-w-[140px]"
                                            :class="hari === 'Jumat' ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-300'">
                                            {{ hari }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(jm, jamIdx) in sortedJamMaster" :key="jm.id">
                                        <!-- Jam Label (Sticky Left) -->
                                        <td class="sticky left-0 z-10 bg-white dark:bg-gray-800 px-3 py-0 border-r border-b border-gray-200 dark:border-gray-600 text-center"
                                            :class="jm.is_istirahat ? 'bg-amber-50 dark:bg-amber-900/20' : ''">
                                            <div class="flex flex-col items-center gap-0.5 py-2">
                                                <span class="text-[10px] font-bold uppercase tracking-wider"
                                                      :class="jm.is_istirahat ? 'text-amber-600 dark:text-amber-400' : 'text-gray-400 dark:text-gray-500'">
                                                    {{ jm.is_istirahat ? 'Istirahat' : jm.nama_jam }}
                                                </span>
                                                <span class="text-[11px] font-semibold"
                                                      :class="jm.is_istirahat ? 'text-amber-500 dark:text-amber-400' : 'text-gray-700 dark:text-gray-300'">
                                                    {{ jm.jam_mulai.substring(0,5) }}
                                                </span>
                                                <span class="text-[10px] text-gray-400 dark:text-gray-500">
                                                    {{ jm.jam_selesai.substring(0,5) }}
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Day Cells -->
                                        <template v-for="hari in hariList" :key="hari">
                                            <!-- ISTIRAHAT: Full-width block -->
                                            <td v-if="jm.is_istirahat"
                                                class="border-b border-r border-gray-200 dark:border-gray-600 bg-amber-50/50 dark:bg-amber-900/10 text-center py-2">
                                                <span class="text-[10px] font-bold text-amber-400 dark:text-amber-600 uppercase tracking-widest">
                                                    <i class="fas fa-coffee mr-1"></i>
                                                </span>
                                            </td>

                                            <!-- OCCUPIED: Spanned by cell above, skip rendering -->
                                            <!-- (don't render <td> at all so rowspan works) -->

                                            <!-- FILLED: Jadwal exists here -->
                                            <td v-else-if="getCellInfo(hari, jamIdx).type === 'filled'"
                                                :rowspan="getCellInfo(hari, jamIdx).rowspan"
                                                class="border-b border-r border-gray-200 dark:border-gray-600 p-1 align-top cursor-pointer group"
                                                @click="openEditModal(getCellInfo(hari, jamIdx).jadwal)">
                                                <div class="h-full rounded-xl border-l-4 p-2.5 transition-all duration-200 hover:shadow-lg hover:scale-[1.02] active:scale-[0.98]"
                                                     :class="[
                                                         getMapelColor(getCellInfo(hari, jamIdx).jadwal.id_mapel).bg,
                                                         getMapelColor(getCellInfo(hari, jamIdx).jadwal.id_mapel).border,
                                                     ]">
                                                    <div class="font-bold text-xs leading-tight mb-1 truncate"
                                                         :class="getMapelColor(getCellInfo(hari, jamIdx).jadwal.id_mapel).text">
                                                        {{ getCellInfo(hari, jamIdx).jadwal.mapel?.nama_mapel }}
                                                    </div>
                                                    <div class="text-[10px] text-gray-500 dark:text-gray-400 truncate flex items-center gap-1">
                                                        <i class="fas fa-user-tie"></i>
                                                        {{ getCellInfo(hari, jamIdx).jadwal.guru?.nama_lengkap }}
                                                    </div>
                                                    <div class="text-[9px] text-gray-400 dark:text-gray-500 mt-1">
                                                        {{ getCellInfo(hari, jamIdx).jadwal.jam_mulai.substring(0,5) }} - {{ getCellInfo(hari, jamIdx).jadwal.jam_selesai.substring(0,5) }}
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- EMPTY: Clickable to Add -->
                                            <td v-else-if="getCellInfo(hari, jamIdx).type === 'empty'"
                                                class="border-b border-r border-gray-200 dark:border-gray-600 p-1 align-top cursor-pointer group hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors"
                                                @click="openAddModal(hari, jamIdx)">
                                                <div class="h-full min-h-[52px] rounded-xl border-2 border-dashed border-transparent group-hover:border-primary-300 dark:group-hover:border-primary-600 flex items-center justify-center transition-all">
                                                    <i class="fas fa-plus text-transparent group-hover:text-primary-400 dark:group-hover:text-primary-500 transition-colors text-sm"></i>
                                                </div>
                                            </td>

                                            <!-- OCCUPIED: cell hidden by rowspan above - DON'T RENDER -->
                                            <!-- This is handled by v-else-if chain: occupied cells simply won't match any condition and won't render a <td> -->
                                        </template>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div v-if="selectedKelas && jadwal.length > 0" class="mt-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-4">
                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Legenda Mata Pelajaran</p>
                        <div class="flex flex-wrap gap-2">
                            <template v-for="j in jadwal" :key="j.id">
                                <span v-if="j.mapel" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold"
                                      :class="[getMapelColor(j.id_mapel).bg, getMapelColor(j.id_mapel).text]">
                                    <span class="w-2 h-2 rounded-full" :class="getMapelColor(j.id_mapel).accent"></span>
                                    {{ j.mapel.nama_mapel }}
                                </span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================= -->
        <!-- FORM MODAL (Add/Edit Jadwal) -->
        <!-- ============================================= -->
        <div v-if="openModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="openModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all flex flex-col max-h-[90vh]">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center shrink-0 bg-gradient-to-r from-primary-500 to-primary-600">
                    <h3 class="font-bold text-lg text-white flex items-center gap-2">
                        <i :class="isEdit ? 'fas fa-pen' : 'fas fa-plus-circle'"></i>
                        {{ isEdit ? 'Edit Jadwal' : 'Tambah Jadwal' }}
                    </h3>
                    <button @click="openModal = false" class="text-white/70 hover:text-white transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto">
                    <form @submit.prevent="submitForm">
                        <!-- Context Info -->
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 mb-5 flex items-center gap-4 text-sm">
                            <div class="flex items-center gap-2">
                                <span class="text-gray-500 dark:text-gray-400 font-medium">Hari:</span>
                                <span class="font-bold text-gray-900 dark:text-white bg-white dark:bg-gray-600 px-3 py-1 rounded-lg shadow-sm border border-gray-200 dark:border-gray-500">{{ form.hari }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-gray-500 dark:text-gray-400 font-medium">Kelas:</span>
                                <span class="font-bold text-gray-900 dark:text-white bg-white dark:bg-gray-600 px-3 py-1 rounded-lg shadow-sm border border-gray-200 dark:border-gray-500">{{ selectedKelasName }}</span>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <!-- Mata Pelajaran -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Mata Pelajaran</label>
                                <select v-model="form.id_mapel" class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500 shadow-sm text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2.5" required>
                                    <option value="">Pilih Mapel...</option>
                                    <option v-for="m in mappedMapels" :key="m.id" :value="m.id">{{ m.nama_mapel }}</option>
                                </select>
                            </div>

                            <!-- Guru (Auto) -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Guru Pengajar
                                    <span class="text-xs text-gray-400 font-normal">(Otomatis dari mapping)</span>
                                </label>
                                <select v-model="form.id_guru" class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500 shadow-sm text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2.5" required>
                                    <option value="">Pilih Guru...</option>
                                    <option v-for="g in guru" :key="g.id" :value="g.id">{{ g.nama_lengkap }}</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <!-- Mulai Jam Ke- -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Mulai Jam Ke-</label>
                                    <select v-model="form.start_jp" class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500 shadow-sm text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2.5 bg-gray-50 dark:bg-gray-600" required>
                                        <option value="">- Pilih -</option>
                                        <template v-for="jm in jam_master" :key="jm.id">
                                            <option v-if="jm.is_istirahat == 0" :value="jm.urutan">{{ jm.nama_jam }} ({{ jm.jam_mulai.substring(0,5) }})</option>
                                        </template>
                                    </select>
                                </div>

                                <!-- Durasi -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Durasi</label>
                                    <select v-model="form.durasi_jp" class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500 shadow-sm text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2.5" required>
                                        <option :value="1">1 Jam Pelajaran</option>
                                        <option :value="2">2 Jam Pelajaran</option>
                                        <option :value="3">3 Jam Pelajaran</option>
                                        <option :value="4">4 Jam Pelajaran</option>
                                        <option :value="5">5 Jam Pelajaran</option>
                                        <option :value="6">6 Jam Pelajaran</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Computed Time Display -->
                            <div v-if="form.jam_mulai && form.jam_selesai" class="bg-primary-50 dark:bg-primary-900/20 rounded-xl p-3 border border-primary-200 dark:border-primary-800 flex items-center justify-center gap-4 text-sm">
                                <div class="text-center">
                                    <div class="text-[10px] text-primary-600 dark:text-primary-400 uppercase font-bold tracking-wider">Jam Mulai</div>
                                    <div class="text-lg font-black text-primary-700 dark:text-primary-300">{{ form.jam_mulai }}</div>
                                </div>
                                <i class="fas fa-arrow-right text-primary-400"></i>
                                <div class="text-center">
                                    <div class="text-[10px] text-primary-600 dark:text-primary-400 uppercase font-bold tracking-wider">Jam Selesai</div>
                                    <div class="text-lg font-black text-primary-700 dark:text-primary-300">{{ form.jam_selesai }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 pt-5 mt-5 border-t border-gray-100 dark:border-gray-700">
                            <button v-if="isEdit" type="button" @click="hapus(selectedJadwal.id)" class="bg-red-100 hover:bg-red-200 dark:bg-red-900/30 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 font-semibold py-2.5 px-4 rounded-xl text-sm transition-all flex items-center gap-2">
                                <i class="fas fa-trash-alt"></i> Hapus
                            </button>
                            <div class="flex-1"></div>
                            <button type="button" @click="openModal = false" class="px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-sm">
                                Batal
                            </button>
                            <button type="submit" :disabled="form.processing" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-lg shadow-primary-900/20 transition-all disabled:opacity-50 flex items-center gap-2 text-sm">
                                <i class="fas fa-spinner fa-spin" v-if="form.processing"></i>
                                <i class="fas fa-save" v-else></i>
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ============================================= -->
        <!-- IMPORT MODAL -->
        <!-- ============================================= -->
        <div v-if="openImportModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="openImportModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-file-import text-primary-500"></i> Import Jadwal
                    </h3>
                    <button @click="openImportModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="p-6">
                    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                        <p>Pastikan format file sesuai dengan template.</p>
                        <a href="/admin/kurikulum/jadwal-pelajaran/template" class="text-primary-600 hover:underline font-semibold mt-1 inline-block">
                            <i class="fas fa-download"></i> Download Template CSV
                        </a>
                    </div>
                    <form @submit.prevent="submitImport">
                        <div class="mb-6">
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-2xl hover:border-primary-500 transition-colors bg-gray-50 dark:bg-gray-900/50">
                                <div class="space-y-1 text-center">
                                    <i class="fas fa-file-excel text-3xl text-green-500 mb-2"></i>
                                    <div class="flex text-sm text-gray-600 dark:text-gray-400 justify-center">
                                        <label for="file-upload" class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-primary-600 hover:text-primary-500 focus-within:outline-none px-1">
                                            <span>Pilih File</span>
                                            <input id="file-upload" type="file" class="sr-only" @change="handleFileImport" accept=".csv,.xlsx,.xls">
                                        </label>
                                        <p class="pl-1">atau drag & drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">CSV up to 5MB</p>
                                    <p v-if="importForm.file" class="text-sm font-bold text-primary-600 mt-2">{{ importForm.file.name }}</p>
                                </div>
                            </div>
                            <p v-if="importForm.errors.file" class="mt-2 text-sm text-red-600">{{ importForm.errors.file }}</p>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <button type="button" @click="openImportModal = false" class="px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                Batal
                            </button>
                            <button type="submit" :disabled="importForm.processing || !importForm.file" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2.5 px-6 rounded-xl shadow transition-colors disabled:opacity-50 flex items-center gap-2">
                                <i class="fas fa-spinner fa-spin" v-if="importForm.processing"></i>
                                <i class="fas fa-cloud-upload-alt" v-else></i>
                                Upload & Import
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<style scoped>
.timetable-grid td {
    height: 60px;
}
.timetable-grid td:first-child {
    height: auto;
}
/* Hide scrollbar for Chrome, Safari and Opera */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
/* Hide scrollbar for IE, Edge and Firefox */
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
