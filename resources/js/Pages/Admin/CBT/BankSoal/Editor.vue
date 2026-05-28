<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref, onMounted, onUnmounted, watch, computed } from 'vue';

const props = defineProps({
    bankSoal: Object,
    baseUrl: String
});

import TinyMceEditor from '@/Components/TinyMceEditor.vue';
const soals = ref([]);
const activeTab = ref('1'); // 1 = PG
const loading = ref(false);
const saving = ref(false);

const types = [
    { id: '1', name: 'Pilihan Ganda', icon: 'fa-list-ul' },
    { id: '3', name: 'PG Kompleks', icon: 'fa-tasks' },
    { id: '4', name: 'Menjodohkan', icon: 'fa-project-diagram' },
    { id: '6', name: 'Benar/Salah', icon: 'fa-check-double' },
    { id: '5', name: 'Isian Singkat', icon: 'fa-pen' },
    { id: '2', name: 'Esai', icon: 'fa-align-left' },
];

const activeTabName = computed(() => {
    return types.find(t => t.id === activeTab.value)?.name || '';
});

// Setup Pagination
const currentPage = ref(1);
const perPage = ref(5); // Render 5 soal per halaman agar tidak berat

watch(activeTab, () => {
    currentPage.value = 1;
});

// Soal yang difilter berdasarkan jenis yang sedang aktif
const activeSoals = computed(() => {
    return soals.value.filter(s => s.jenis_soal == activeTab.value);
});

const totalPages = computed(() => Math.ceil(activeSoals.value.length / perPage.value));

const paginatedSoals = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    return activeSoals.value.slice(start, start + perPage.value);
});

// Load soal dari database
const loadSoal = async () => {
    loading.value = true;
    try {
        const response = await fetch(`/admin/cbt/bank-soal/${props.bankSoal.id}/get-soal`);
        const result = await response.json();
        if (result.status === 'success') {
            soals.value = result.data.map(soal => ({
                id: soal.id,
                jenis_soal: soal.jenis_soal.toString(),
                question: soal.question || '',
                shortentry: soal.shortentry || '',
                true_default_point: soal.true_default_point || 1,
                false_default_point: soal.false_default_point || 0,
                options: (soal.opsi || []).map(o => ({
                    id: o.id,
                    body: o.body || '',
                    is_key: o.is_key || 0,
                    soal_couple_id: o.soal_couple_id
                })),
                couples: (soal.couples || []).map(c => ({
                    id: c.id,
                    body: c.body || ''
                })),
                option_selected: null,
                couple_selected: null
            }));
        }
    } catch (e) {
        console.error('Error loading soal:', e);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadSoal();
});

// Fungsi simpan ke database
const saveSoal = async () => {
    saving.value = true;
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const response = await fetch(`/admin/cbt/bank-soal/${props.bankSoal.id}/save-soal`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ soals: soals.value })
        });
        const result = await response.json();
        if (result.status === 'success') {
            alert('Soal berhasil disimpan ke database!');
            // Reload to get real IDs for newly inserted soals
            loadSoal();
        } else {
            alert('Gagal menyimpan soal: ' + result.message);
        }
    } catch (e) {
        console.error('Error saving soal:', e);
        alert('Terjadi kesalahan saat menyimpan.');
    } finally {
        saving.value = false;
    }
};

// Menambah soal baru
const addSoal = () => {
    const tempId = 'new-' + Math.random().toString(36).substr(2, 9);
    const newSoal = {
        id: tempId,
        jenis_soal: activeTab.value,
        question: '',
        shortentry: '',
        true_default_point: 1,
        false_default_point: 0,
        options: [],
        couples: [],
        isnew: true,
        option_selected: null,
        couple_selected: null
    };

    if (activeTab.value === '1') { // PG
        newSoal.options = [
            { id: 'opt-' + Math.random().toString(36).substr(2, 9), body: '', is_key: 1, isnew: true },
            { id: 'opt-' + Math.random().toString(36).substr(2, 9), body: '', is_key: 0, isnew: true }
        ];
    } else if (activeTab.value === '3') { // PG Kompleks
        newSoal.options = [
            { id: 'opt-' + Math.random().toString(36).substr(2, 9), body: '', is_key: 1, isnew: true },
            { id: 'opt-' + Math.random().toString(36).substr(2, 9), body: '', is_key: 0, isnew: true }
        ];
    } else if (activeTab.value === '4') { // Menjodohkan
        const cId = 'couple-' + Math.random().toString(36).substr(2, 9);
        const oId = 'opt-' + Math.random().toString(36).substr(2, 9);
        newSoal.options = [
            { id: oId, body: '', soal_couple_id: cId, isnew: true }
        ];
        newSoal.couples = [
            { id: cId, body: '', isnew: true }
        ];
    } else if (activeTab.value === '6') { // Benar/Salah
        newSoal.options = [
            { id: 'opt-' + Math.random().toString(36).substr(2, 9), body: '', is_key: 1, isnew: true }
        ];
    }
    
    soals.value.push(newSoal);
};

// Menghapus soal dari array
const hapusSoal = (soal) => {
    if (confirm('Yakin ingin menghapus soal ini?')) {
        const realIndex = soals.value.findIndex(s => s === soal);
        if (realIndex !== -1) {
            soals.value.splice(realIndex, 1);
            
            // Adjust page if current page becomes empty
            if (paginatedSoals.value.length === 0 && currentPage.value > 1) {
                currentPage.value--;
            }
        }
    }
};

// Link options and couples for type 4
const optionClick = (soal, optionId) => {
    if (soal.option_selected === optionId) {
        soal.option_selected = null;
        return;
    }
    if (soal.couple_selected) {
        const option = soal.options.find(o => o.id === optionId);
        if (option) {
            soal.options.forEach(o => {
                if (o.soal_couple_id === soal.couple_selected) {
                    o.soal_couple_id = null;
                }
            });
            option.soal_couple_id = soal.couple_selected;
        }
        soal.couple_selected = null;
        soal.option_selected = null;
    } else {
        soal.option_selected = optionId;
    }
};

const coupleClick = (soal, coupleId) => {
    if (soal.couple_selected === coupleId) {
        soal.couple_selected = null;
        return;
    }
    if (soal.option_selected) {
        const option = soal.options.find(o => o.id === soal.option_selected);
        if (option) {
            soal.options.forEach(o => {
                if (o.soal_couple_id === coupleId) {
                    o.soal_couple_id = null;
                }
            });
            option.soal_couple_id = coupleId;
        }
        soal.option_selected = null;
        soal.couple_selected = null;
    } else {
        soal.couple_selected = coupleId;
    }
};

// Add Opsi
const addOpsi = (soal) => {
    soal.options.push({
        id: 'opt-' + Math.random().toString(36).substr(2, 9),
        body: '',
        is_key: 0,
        soal_couple_id: null,
        isnew: true
    });
};

// Add Couple
const addCouple = (soal) => {
    soal.couples.push({
        id: 'couple-' + Math.random().toString(36).substr(2, 9),
        body: '',
        isnew: true
    });
};

// Remove Opsi
const removeOpsi = (soal, index) => {
    soal.options.splice(index, 1);
};

// Remove Couple
const removeCouple = (soal, index) => {
    const coupleId = soal.couples[index].id;
    soal.couples.splice(index, 1);
    soal.options.forEach(o => {
        if (o.soal_couple_id === coupleId) {
            o.soal_couple_id = null;
        }
    });
};

// Set Kunci PG Tunggal
const setKeySingle = (soal, index) => {
    soal.options.forEach((o, i) => {
        o.is_key = (i === index) ? 1 : 0;
    });
};

// Toggle Kunci PG Kompleks
const toggleKeyMulti = (option) => {
    option.is_key = option.is_key == 1 ? 0 : 1;
};

// Colors mapping for couples
const coupleColors = ['bg-blue-500 hover:bg-blue-600', 'bg-emerald-500 hover:bg-emerald-600', 'bg-cyan-500 hover:bg-cyan-600', 'bg-amber-500 hover:bg-amber-600', 'bg-rose-500 hover:bg-rose-600', 'bg-indigo-500 hover:bg-indigo-600', 'bg-violet-500 hover:bg-violet-600'];
const getCoupleColorClass = (soal, coupleId) => {
    const optIndex = soal.options.findIndex(o => o.soal_couple_id === coupleId);
    if (optIndex !== -1) {
        return coupleColors[optIndex % coupleColors.length] + ' text-white border-transparent';
    }
    return 'bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700';
};
const getOptionColorClass = (soal, option, index) => {
    if (option.soal_couple_id) {
        const optIndex = soal.options.findIndex(o => o.id === option.id);
        return coupleColors[optIndex % coupleColors.length] + ' text-white border-transparent';
    }
    return 'bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700';
};

</script>

<template>
    <Head :title="`Editor Soal - ${bankSoal.kode}`" />

    <DashboardLayout>
        <div class="h-[calc(100vh-10rem)] lg:h-[calc(100vh-11rem)] flex flex-col bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 overflow-hidden shadow-sm">
            <!-- Header Editor -->
            <div class="bg-gray-50/50 dark:bg-gray-700/30 border-b border-gray-100 dark:border-gray-700 p-4 flex items-center justify-between shrink-0 relative z-10">
                <div class="flex items-center gap-4">
                    <Link href="/admin/cbt/bank-soal" class="w-10 h-10 rounded-2xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-900 dark:hover:bg-gray-950 flex items-center justify-center text-gray-600 dark:text-gray-300 transition-colors">
                        <i class="fas fa-arrow-left"></i>
                    </Link>
                    <div>
                        <h2 class="text-lg font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                            {{ bankSoal.nama_mapel }}
                            <span class="px-2.5 py-0.5 rounded-full text-xs bg-primary-50 dark:bg-primary-950 text-primary-700 dark:text-primary-400 font-bold border border-primary-100 dark:border-primary-900">{{ bankSoal.kode }}</span>
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Dibuat oleh: {{ bankSoal.creator?.guru?.nama_lengkap ?? bankSoal.creator?.nama_lengkap ?? 'Administrator' }}
                        </p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <span v-if="saving" class="text-xs font-semibold text-gray-500 animate-pulse"><i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...</span>
                    <button @click="saveSoal" :disabled="saving" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 disabled:opacity-50 text-white rounded-2xl text-sm font-bold shadow-md shadow-primary-500/20 flex items-center gap-2 transition-all transform hover:-translate-y-0.5">
                        <i class="fas fa-save"></i> Simpan ke Database
                    </button>
                </div>
            </div>

            <!-- Body Editor -->
            <div class="flex-1 flex overflow-hidden">
                <!-- Sidebar Tipe Soal -->
                <div class="w-64 bg-white dark:bg-gray-800 border-r border-gray-100 dark:border-gray-700 shrink-0 flex flex-col">
                    <div class="p-4 font-bold text-xs text-gray-400 dark:text-gray-500 uppercase tracking-widest border-b border-gray-100 dark:border-gray-700">
                        Kategori Jenis Soal
                    </div>
                    <div class="flex-1 overflow-y-auto p-3 space-y-1.5">
                        <button v-for="t in types" :key="t.id"
                            @click="activeTab = t.id"
                            class="w-full flex items-center gap-3 px-3.5 py-3 rounded-2xl text-left transition-all font-semibold text-sm"
                            :class="activeTab === t.id ? 'bg-primary-50 dark:bg-primary-950/40 text-primary-700 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-900'">
                            <i :class="[`fas ${t.icon} w-5 text-center`, activeTab === t.id ? 'text-primary-500' : 'text-slate-400']"></i>
                            {{ t.name }}
                            <!-- Badge count -->
                            <span v-if="soals.filter(s => s.jenis_soal == t.id).length > 0" 
                                class="ml-auto bg-gray-100 dark:bg-gray-900 text-gray-600 dark:text-gray-400 text-xs px-2.5 py-0.5 rounded-full font-bold">
                                {{ soals.filter(s => s.jenis_soal == t.id).length }}
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Workspace Area -->
                <div class="flex-1 bg-gray-50 dark:bg-gray-900 overflow-y-auto p-6 flex flex-col items-center">
                    <div class="w-full max-w-4xl">
                        
                        <!-- Workspace Toolbar -->
                        <div class="flex justify-between items-center mb-6 bg-white dark:bg-gray-800 p-4 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
                            <h3 class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight flex items-center gap-2.5">
                                <i :class="`fas ${types.find(t => t.id === activeTab)?.icon} text-primary-500`"></i>
                                Soal {{ activeTabName }}
                            </h3>
                            <button @click="addSoal" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-2xl font-bold text-sm shadow-md shadow-primary-500/20 flex items-center gap-2 transition-all">
                                <i class="fas fa-plus"></i> Tambah Soal
                            </button>
                        </div>

                        <!-- Loading State -->
                        <div v-if="loading" class="flex flex-col items-center justify-center p-16 bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm">
                            <i class="fas fa-circle-notch fa-spin text-4xl text-primary-500 mb-4"></i>
                            <p class="font-bold text-gray-500">Memuat Data Soal...</p>
                        </div>

                        <!-- Empty State -->
                        <div v-else-if="activeSoals.length === 0" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-dashed border-gray-250 dark:border-gray-700 p-16 flex flex-col items-center justify-center min-h-[400px] transition-all">
                            <div class="w-20 h-20 bg-primary-50 dark:bg-primary-950/40 text-primary-500 rounded-full flex items-center justify-center text-3xl mb-4">
                                <i :class="`fas ${types.find(t => t.id === activeTab)?.icon}`"></i>
                            </div>
                            <h4 class="text-xl font-bold text-gray-800 dark:text-white mb-2">Belum Ada Soal {{ activeTabName }}</h4>
                            <p class="text-gray-500 dark:text-gray-400 text-center max-w-md mb-6 text-sm">
                                Silakan klik tombol "Tambah Soal" untuk mulai membuat butir soal baru pada kategori ini.
                            </p>
                        </div>

                        <!-- Render Soal -->
                        <div v-else class="space-y-6">
                            <div v-for="(soal, index) in paginatedSoals" :key="soal.id" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700/60 overflow-hidden transition-all hover:shadow-md">
                                
                                <!-- Header Soal -->
                                <div class="bg-gray-50/50 dark:bg-gray-700/20 border-b border-gray-100 dark:border-gray-700 p-4 flex justify-between items-center">
                                    <h4 class="font-black text-gray-800 dark:text-white">Soal No. {{ (currentPage - 1) * perPage + index + 1 }}</h4>
                                    <div class="flex items-center gap-3">
                                        <!-- Pengaturan Poin -->
                                        <div class="flex gap-2">
                                            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-1.5 flex items-center gap-2">
                                                <label class="text-xs font-bold text-green-600 dark:text-green-500 uppercase tracking-wider">Poin Benar:</label>
                                                <input v-model="soal.true_default_point" type="number" step="any" class="w-12 h-6 text-sm bg-transparent border-none p-0 text-center focus:ring-0 text-gray-800 dark:text-gray-200 font-bold">
                                            </div>
                                            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-1.5 flex items-center gap-2">
                                                <label class="text-xs font-bold text-rose-600 dark:text-rose-500 uppercase tracking-wider">Poin Salah:</label>
                                                <input v-model="soal.false_default_point" type="number" step="any" class="w-12 h-6 text-sm bg-transparent border-none p-0 text-center focus:ring-0 text-gray-800 dark:text-gray-200 font-bold">
                                            </div>
                                        </div>
                                        <button @click="hapusSoal(soal)" class="w-9 h-9 flex items-center justify-center rounded-xl text-rose-500 hover:bg-rose-50 dark:hover:bg-gray-900/25 transition-all" title="Hapus Soal">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Body Soal -->
                                <div class="p-6">
                                    <!-- Input Pertanyaan (TinyMCE) -->
                                    <div class="mb-6">
                                        <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">Isi Pertanyaan / Instruksi</label>
                                        <TinyMceEditor v-model="soal.question" :id="`editor-q-${soal.id}`" placeholder="Tulis pertanyaan disini..." />
                                    </div>

                                    <!-- 1. Block Opsi PG (Single Select) -->
                                    <div v-if="activeTab === '1'" class="bg-gray-50/50 dark:bg-gray-900/30 p-5 rounded-2xl border border-gray-100 dark:border-gray-700">
                                        <label class="block text-xs font-extrabold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4"><i class="fas fa-list-ul mr-1.5"></i> Pilihan Opsi Jawaban (Pilih Satu Kunci)</label>
                                        
                                        <div class="space-y-3">
                                            <div v-for="(opsi, oIndex) in soal.options" :key="opsi.id" class="flex items-start gap-4 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/60 p-4 rounded-2xl shadow-sm transition-all hover:border-gray-300">
                                                <div class="pt-3.5 flex flex-col items-center">
                                                    <input type="radio" :name="`key_${soal.id}`" :checked="opsi.is_key == 1" @change="setKeySingle(soal, oIndex)" class="w-5 h-5 text-primary-600 focus:ring-primary-500 cursor-pointer">
                                                    <span v-if="opsi.is_key == 1" class="text-[9px] font-black text-primary-600 dark:text-primary-400 mt-1 uppercase tracking-wider">Kunci</span>
                                                </div>
                                                <div class="flex-1">
                                                    <TinyMceEditor v-model="opsi.body" :id="`editor-opt-${soal.id}-${opsi.id || oIndex}`" placeholder="Tulis opsi jawaban disini..." />
                                                </div>
                                                <button @click="removeOpsi(soal, oIndex)" class="mt-2.5 w-8 h-8 rounded-xl text-rose-500 hover:bg-rose-50 dark:hover:bg-gray-900/25 flex items-center justify-center transition-colors">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <button v-if="soal.options.length < 5" @click="addOpsi(soal)" class="mt-4 px-4 py-3 bg-white hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-900 border border-gray-200 dark:border-gray-700 border-dashed text-gray-600 dark:text-gray-400 rounded-2xl text-xs font-bold shadow-sm flex items-center justify-center w-full transition-all">
                                            <i class="fas fa-plus mr-2"></i> Tambah Opsi Jawaban
                                        </button>
                                    </div>

                                    <!-- 3. Block PG Kompleks (Multi Select) -->
                                    <div v-else-if="activeTab === '3'" class="bg-gray-50/50 dark:bg-gray-900/30 p-5 rounded-2xl border border-gray-100 dark:border-gray-700">
                                        <label class="block text-xs font-extrabold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4"><i class="fas fa-tasks mr-1.5"></i> PG Kompleks (Centang Semua Kunci Jawaban Benar)</label>
                                        
                                        <div class="space-y-3">
                                            <div v-for="(opsi, oIndex) in soal.options" :key="opsi.id" class="flex items-start gap-4 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/60 p-4 rounded-2xl shadow-sm transition-all hover:border-gray-300">
                                                <div class="pt-3.5 flex flex-col items-center">
                                                    <input type="checkbox" :checked="opsi.is_key == 1" @change="toggleKeyMulti(opsi)" class="w-5 h-5 text-primary-600 focus:ring-primary-500 cursor-pointer rounded">
                                                    <span v-if="opsi.is_key == 1" class="text-[9px] font-black text-primary-600 dark:text-primary-400 mt-1 uppercase tracking-wider">Kunci</span>
                                                </div>
                                                <div class="flex-1">
                                                    <TinyMceEditor v-model="opsi.body" :id="`editor-optk-${soal.id}-${opsi.id || oIndex}`" placeholder="Tulis opsi jawaban disini..." />
                                                </div>
                                                <button @click="removeOpsi(soal, oIndex)" class="mt-2.5 w-8 h-8 rounded-xl text-rose-500 hover:bg-rose-50 dark:hover:bg-gray-900/25 flex items-center justify-center transition-colors">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <button v-if="soal.options.length < 5" @click="addOpsi(soal)" class="mt-4 px-4 py-3 bg-white hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-900 border border-gray-200 dark:border-gray-700 border-dashed text-gray-600 dark:text-gray-400 rounded-2xl text-xs font-bold shadow-sm flex items-center justify-center w-full transition-all">
                                            <i class="fas fa-plus mr-2"></i> Tambah Opsi Jawaban
                                        </button>
                                    </div>

                                    <!-- 4. Block Menjodohkan (Matching) -->
                                    <div v-else-if="activeTab === '4'" class="bg-gray-50/50 dark:bg-gray-900/30 p-5 rounded-2xl border border-gray-100 dark:border-gray-700">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <!-- Pertanyaan Blok -->
                                            <div>
                                                <label class="block text-xs font-extrabold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-3 border-b border-gray-200 dark:border-gray-700 pb-2"><i class="fas fa-question-circle mr-1"></i> Blok Kiri (Pertanyaan)</label>
                                                <div class="space-y-3">
                                                    <div v-for="(opsi, oIndex) in soal.options" :key="opsi.id" class="flex items-center gap-2 bg-white dark:bg-gray-800 border p-2.5 rounded-xl shadow-sm transition-all" :class="soal.option_selected === opsi.id ? 'ring-2 ring-primary-500 border-transparent bg-primary-50/20' : 'border-gray-200 dark:border-gray-700'">
                                                        <button type="button" @click="removeOpsi(soal, oIndex)" class="text-rose-500 hover:bg-rose-50 p-2 rounded-lg transition-colors" title="Hapus"><i class="fas fa-times"></i></button>
                                                        <input v-model="opsi.body" type="text" class="flex-1 border-none bg-gray-50 dark:bg-gray-900 rounded-lg p-2 text-xs text-gray-800 dark:text-gray-200 shadow-inner" placeholder="Tulis pertanyaan...">
                                                        
                                                        <button type="button" @click="optionClick(soal, opsi.id)" class="px-2.5 py-1.5 rounded-lg border font-bold text-[10px] transition-all flex items-center gap-1 shadow-sm" :class="getOptionColorClass(soal, opsi, oIndex)">
                                                            <i :class="opsi.soal_couple_id ? 'fas fa-link' : 'fas fa-link-slash'"></i>
                                                            {{ opsi.soal_couple_id ? 'Link' : 'Hubung' }}
                                                        </button>
                                                    </div>
                                                </div>
                                                <button @click="addOpsi(soal)" class="w-full mt-3 px-4 py-2 text-xs font-bold text-primary-600 bg-primary-50 hover:bg-primary-100 dark:bg-primary-950 dark:text-primary-400 rounded-xl transition-all border border-primary-200 dark:border-primary-900 border-dashed"><i class="fas fa-plus mr-1"></i> Tambah Pertanyaan</button>
                                            </div>

                                            <!-- Jawaban Blok -->
                                            <div>
                                                <label class="block text-xs font-extrabold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-3 border-b border-gray-200 dark:border-gray-700 pb-2"><i class="fas fa-key mr-1"></i> Blok Kanan (Jawaban)</label>
                                                <div class="space-y-3">
                                                    <div v-for="(couple, cIndex) in soal.couples" :key="couple.id" class="flex items-center gap-2 bg-white dark:bg-gray-800 border p-2.5 rounded-xl shadow-sm transition-all" :class="soal.couple_selected === couple.id ? 'ring-2 ring-primary-500 border-transparent bg-primary-50/20' : 'border-gray-200 dark:border-gray-700'">
                                                        <button type="button" @click="coupleClick(soal, couple.id)" class="px-2.5 py-1.5 rounded-lg border font-bold text-[10px] transition-all flex items-center gap-1 shadow-sm" :class="getCoupleColorClass(soal, couple.id)">
                                                            <i class="fas fa-link"></i> Link
                                                        </button>
                                                        
                                                        <input v-model="couple.body" type="text" class="flex-1 border-none bg-gray-50 dark:bg-gray-900 rounded-lg p-2 text-xs text-gray-800 dark:text-gray-200 shadow-inner" placeholder="Tulis jawaban...">
                                                        <button type="button" @click="removeCouple(soal, cIndex)" class="text-rose-500 hover:bg-rose-50 p-2 rounded-lg transition-colors" title="Hapus"><i class="fas fa-times"></i></button>
                                                    </div>
                                                </div>
                                                <button @click="addCouple(soal)" class="w-full mt-3 px-4 py-2 text-xs font-bold text-primary-600 bg-primary-50 hover:bg-primary-100 dark:bg-primary-900/40 dark:text-primary-400 rounded-xl transition-all border border-primary-200 dark:border-primary-900 border-dashed"><i class="fas fa-plus mr-1"></i> Tambah Jawaban</button>
                                            </div>
                                        </div>
                                        <div class="mt-4 p-3 bg-amber-50 dark:bg-amber-955/20 text-[11px] text-amber-800 dark:text-amber-400 rounded-xl flex items-start gap-2 border border-amber-100 dark:border-amber-900">
                                            <i class="fas fa-info-circle mt-0.5"></i>
                                            <div>
                                                <strong>Cara Menghubungkan:</strong>
                                                Klik tombol <span class="bg-gray-200 dark:bg-gray-700 px-1 py-0.5 rounded text-[10px]">Hubung / Link</span> di baris pertanyaan kiri, lalu klik tombol <span class="bg-gray-200 dark:bg-gray-700 px-1 py-0.5 rounded text-[10px]">Link</span> pada jawaban kanan yang cocok. Warna yang sama menandakan pasangan terhubung!
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 6. Block Benar/Salah (Multiple Statements) -->
                                    <div v-else-if="activeTab === '6'" class="bg-gray-50/50 dark:bg-gray-900/30 p-5 rounded-2xl border border-gray-100 dark:border-gray-700">
                                        <label class="block text-xs font-extrabold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4"><i class="fas fa-check-double mr-1.5"></i> Benar atau Salah (Pernyataan & Kunci)</label>
                                        
                                        <div class="space-y-4">
                                            <div v-for="(opsi, oIndex) in soal.options" :key="opsi.id" class="flex flex-col lg:flex-row lg:items-center gap-4 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/60 p-4 rounded-2xl shadow-sm">
                                                <div class="flex-1 flex items-start">
                                                    <button @click="removeOpsi(soal, oIndex)" class="mt-1 w-8 h-8 rounded-xl text-rose-500 hover:bg-rose-50 dark:hover:bg-gray-900/25 flex items-center justify-center transition-colors mr-2">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                    <div class="flex-1">
                                                        <TinyMceEditor v-model="opsi.body" :id="`editor-optbs-${soal.id}-${opsi.id || oIndex}`" placeholder="Tulis pernyataan disini..." />
                                                    </div>
                                                </div>
                                                <div class="w-full lg:w-48 flex justify-around items-center bg-gray-50 dark:bg-gray-900 p-2.5 rounded-xl border border-gray-100 dark:border-gray-700/60">
                                                    <label class="flex items-center cursor-pointer p-1.5 rounded-lg">
                                                        <input type="radio" :name="`radio_${soal.id}_${opsi.id}`" :checked="opsi.is_key == 1" @change="opsi.is_key = 1" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500 mr-1.5 cursor-pointer">
                                                        <span class="text-xs font-black text-emerald-600 dark:text-emerald-500">BENAR</span>
                                                    </label>
                                                    <div class="w-px h-5 bg-gray-200 dark:bg-gray-700 mx-1"></div>
                                                    <label class="flex items-center cursor-pointer p-1.5 rounded-lg">
                                                        <input type="radio" :name="`radio_${soal.id}_${opsi.id}`" :checked="opsi.is_key == 0" @change="opsi.is_key = 0" class="w-4 h-4 text-rose-600 focus:ring-rose-500 mr-1.5 cursor-pointer">
                                                        <span class="text-xs font-black text-rose-600 dark:text-rose-500">SALAH</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <button v-if="soal.options.length < 5" @click="addOpsi(soal)" class="mt-4 px-4 py-3 bg-white hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-900 border border-gray-200 dark:border-gray-700 border-dashed text-gray-600 dark:text-gray-400 rounded-2xl text-xs font-bold shadow-sm flex items-center justify-center w-full transition-all">
                                            <i class="fas fa-plus mr-2"></i> Tambah Pernyataan
                                        </button>
                                    </div>

                                    <!-- 5. Block Isian Singkat -->
                                    <div v-else-if="activeTab === '5'" class="bg-gray-50/50 dark:bg-gray-900/30 p-5 rounded-2xl border border-gray-100 dark:border-gray-700">
                                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Kunci Jawaban Tepat (Isian Singkat)</label>
                                        <input v-model="soal.shortentry" type="text" class="w-full border-none bg-white dark:bg-gray-900 rounded-2xl py-3 px-4 focus:ring-2 focus:ring-primary-500/20 shadow-inner text-sm text-gray-800 dark:text-gray-200" placeholder="Ketik jawaban yang tepat/eksak...">
                                        <p class="text-[11px] text-gray-500 mt-2">Jawaban siswa harus sama persis (case-insensitive) dengan kunci ini untuk mendapatkan nilai benar.</p>
                                    </div>

                                    <!-- 2. Block Esai -->
                                    <div v-else-if="activeTab === '2'" class="bg-primary-50/50 dark:bg-primary-900/20 p-5 rounded-2xl border border-primary-100 dark:border-primary-900 text-primary-800 dark:text-primary-300 text-xs flex items-start gap-3">
                                        <i class="fas fa-info-circle text-lg mt-0.5"></i>
                                        <div>
                                            <h5 class="font-bold mb-1">Informasi Soal Esai:</h5>
                                            <p>Siswa akan menjawab menggunakan kotak input teks bebas (TinyMCE) saat ujian berlangsung. Guru hanya perlu menuliskan pertanyaan di kolom editor di atas tanpa perlu mengisi opsi kunci jawaban apapun.</p>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- Pagination Controls -->
                        <div v-if="totalPages > 1" class="flex justify-center items-center gap-2 mt-8 mb-4">
                            <button @click="currentPage--" :disabled="currentPage === 1" class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-600 disabled:opacity-50 hover:bg-gray-50 flex items-center shadow-sm transition-all"><i class="fas fa-chevron-left mr-2"></i> Prev</button>
                            
                            <div class="flex items-center gap-1 overflow-x-auto max-w-[50vw] px-2 scrollbar-hide">
                                <button v-for="p in totalPages" :key="p" @click="currentPage = p" class="w-10 h-10 flex shrink-0 items-center justify-center rounded-xl text-sm font-bold transition-all shadow-sm" :class="currentPage === p ? 'bg-primary-600 text-white shadow-primary-500/30' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'">
                                    {{ p }}
                                </button>
                            </div>
                            
                            <button @click="currentPage++" :disabled="currentPage === totalPages" class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-600 disabled:opacity-50 hover:bg-gray-50 flex items-center shadow-sm transition-all">Next <i class="fas fa-chevron-right ml-2"></i></button>
                        </div>

                        <!-- Save Reminder Footer -->
                        <div v-if="activeSoals.length > 0" class="mt-8 mb-16 p-6 bg-amber-50 dark:bg-amber-955/20 border border-amber-200 dark:border-amber-900 rounded-3xl flex items-center justify-between shadow-sm">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-amber-200 dark:bg-amber-900 flex items-center justify-center text-amber-700 dark:text-amber-400 text-xl shrink-0">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-amber-800 dark:text-amber-400">Perubahan Belum Disimpan!</h4>
                                    <p class="text-xs text-amber-700 dark:text-amber-500">Pertanyaan dan kunci jawaban yang Anda edit di atas belum tersimpan permanen. Klik tombol 'Simpan Sekarang'.</p>
                                </div>
                            </div>
                            <button @click="saveSoal" :disabled="saving" class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-2xl shadow-md transition-all shrink-0">
                                <i class="fas fa-save mr-1.5"></i> Simpan Sekarang
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
