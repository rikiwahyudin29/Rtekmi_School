<script setup>
import { ref, computed } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    draft: Object,
    soals: Array
});

const hasArabic = (text) => {
    if (!text) return false;
    const arabicPattern = /[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\uFB50-\uFDFF\uFE70-\uFEFF]/;
    return arabicPattern.test(text);
};

const fixImageUrls = (html) => {
    if (!html) return '';
    // Ganti absolute localhost / 127.0.0.1 url ke relative path
    return html.replace(/src="https?:\/\/[^\/]+\/(uploads\/bank_soal\/[^"]+)"/g, 'src="/$1"');
};

// Cloning array agar reaktif dan bisa diubah-ubah
const localSoals = ref(JSON.parse(JSON.stringify(props.soals)));
const saving = ref(false);

const selectedSoals = computed(() => {
    return localSoals.value.filter(s => s.selected);
});

const unselectedSoals = computed(() => {
    return localSoals.value.filter(s => !s.selected);
});

// Drag and drop state
const draggedIndex = ref(null);

const onDragStart = (index) => {
    draggedIndex.value = index;
};

const onDrop = (index) => {
    if (draggedIndex.value === null) return;
    
    // Temukan index sejati di localSoals berdasarkan ID soal yang di-drop
    const targetSoal = selectedSoals.value[index];
    const sourceSoal = selectedSoals.value[draggedIndex.value];
    
    const sourceIdx = localSoals.value.findIndex(s => s.id === sourceSoal.id);
    const targetIdx = localSoals.value.findIndex(s => s.id === targetSoal.id);

    // Swap item
    const temp = localSoals.value[sourceIdx];
    localSoals.value.splice(sourceIdx, 1);
    localSoals.value.splice(targetIdx, 0, temp);
    
    draggedIndex.value = null;
};

const toggleSelect = (soal) => {
    soal.selected = !soal.selected;
    
    // Pindahkan ke bawah list jika dipilih agar tidak merusak urutan teratas
    if (soal.selected) {
        const idx = localSoals.value.findIndex(s => s.id === soal.id);
        const temp = localSoals.value.splice(idx, 1)[0];
        localSoals.value.push(temp);
    }
};

const moveUp = (index) => {
    if (index === 0) return;
    const targetSoal = selectedSoals.value[index - 1];
    const sourceSoal = selectedSoals.value[index];
    
    const sourceIdx = localSoals.value.findIndex(s => s.id === sourceSoal.id);
    const targetIdx = localSoals.value.findIndex(s => s.id === targetSoal.id);

    const temp = localSoals.value[sourceIdx];
    localSoals.value.splice(sourceIdx, 1);
    localSoals.value.splice(targetIdx, 0, temp);
};

const moveDown = (index) => {
    if (index === selectedSoals.value.length - 1) return;
    const targetSoal = selectedSoals.value[index + 1];
    const sourceSoal = selectedSoals.value[index];
    
    const sourceIdx = localSoals.value.findIndex(s => s.id === sourceSoal.id);
    const targetIdx = localSoals.value.findIndex(s => s.id === targetSoal.id);

    const temp = localSoals.value[sourceIdx];
    localSoals.value.splice(sourceIdx, 1);
    localSoals.value.splice(targetIdx, 0, temp);
};

const selectAll = () => {
    localSoals.value.forEach(s => s.selected = true);
};

const unselectAll = () => {
    localSoals.value.forEach(s => s.selected = false);
};

const simpanUrutan = () => {
    saving.value = true;
    // Extract ID dari soal-soal yang dipilih, urutannya sesuai dengan selectedSoals
    const ids = selectedSoals.value.map(s => s.id);
    
    router.post(route('admin.cbt.draft-ujian.save-soal', props.draft.id), {
        soal_ids: ids
    }, {
        onFinish: () => saving.value = false
    });
};

const getJenisSoalBadge = (jenis) => {
    const map = {
        1: { label: 'PG', color: 'bg-blue-100 text-blue-700' },
        2: { label: 'PG Kompleks', color: 'bg-indigo-100 text-indigo-700' },
        3: { label: 'Menjodohkan', color: 'bg-purple-100 text-purple-700' },
        4: { label: 'Isian Singkat', color: 'bg-emerald-100 text-emerald-700' },
        5: { label: 'Esai', color: 'bg-orange-100 text-orange-700' },
        6: { label: 'Benar/Salah', color: 'bg-rose-100 text-rose-700' }
    };
    return map[jenis] || { label: 'Unknown', color: 'bg-gray-100 text-gray-700' };
};
</script>

<template>
    <Head :title="`Atur Soal - ${draft.nama}`" />

    <DashboardLayout>
        <div class="flex flex-col h-full w-full py-6 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto w-full">
                <!-- Header -->
                <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <Link :href="route('admin.cbt.draft-ujian.index')" class="text-sm font-bold text-gray-500 hover:text-primary-600 mb-2 inline-flex items-center gap-2 transition-colors">
                            <i class="fas fa-arrow-left"></i> Kembali ke Draft Ujian
                        </Link>
                        <h2 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                            Atur Soal: <span class="text-primary-600">{{ draft.nama }}</span>
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Bank Soal: <span class="font-bold">{{ draft.bank_soal?.kode }}</span>
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button @click="simpanUrutan" :disabled="saving" class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-2xl font-bold text-sm shadow-lg shadow-primary-500/30 flex items-center gap-2 transition-all hover:-translate-y-0.5 disabled:opacity-70 disabled:hover:translate-y-0">
                            <i :class="saving ? 'fas fa-spinner fa-spin' : 'fas fa-save'"></i> 
                            {{ saving ? 'Menyimpan...' : 'Simpan Susunan Soal' }}
                        </button>
                    </div>
                </div>

                <!-- Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    
                    <!-- Kiri: Daftar Soal Tersedia -->
                    <div class="lg:col-span-5 flex flex-col h-[calc(100vh-200px)]">
                        <div class="bg-white dark:bg-gray-800 rounded-t-3xl border border-b-0 border-gray-100 dark:border-gray-700 p-4 flex items-center justify-between">
                            <h3 class="font-black text-gray-800 dark:text-white">Soal Tersedia ({{ unselectedSoals.length }})</h3>
                            <button @click="selectAll" class="text-xs font-bold text-primary-600 hover:text-primary-700">Pilih Semua</button>
                        </div>
                        <div class="bg-white dark:bg-gray-800 rounded-b-3xl border border-gray-100 dark:border-gray-700 flex-1 overflow-y-auto p-4 custom-scrollbar">
                            <div v-if="unselectedSoals.length === 0" class="h-full flex flex-col items-center justify-center text-center px-4">
                                <div class="w-16 h-16 bg-gray-50 dark:bg-gray-900 rounded-full flex items-center justify-center mb-4 text-2xl text-gray-400">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <h4 class="font-bold text-gray-700 dark:text-gray-300">Semua Soal Terpilih</h4>
                                <p class="text-sm text-gray-500 mt-1">Tidak ada soal tersisa di bank soal ini.</p>
                            </div>
                            
                            <div class="space-y-3">
                                <div v-for="soal in unselectedSoals" :key="soal.id" 
                                    class="p-4 rounded-2xl border border-gray-100 dark:border-gray-700 hover:border-primary-300 bg-gray-50/50 dark:bg-gray-800/50 cursor-pointer transition-all hover:shadow-md group"
                                    @click="toggleSelect(soal)">
                                    <div class="flex gap-4">
                                        <div class="mt-1 flex-shrink-0">
                                            <div class="w-6 h-6 rounded border-2 border-gray-300 dark:border-gray-600 group-hover:border-primary-500 flex items-center justify-center transition-colors">
                                                <i class="fas fa-plus text-primary-500 opacity-0 group-hover:opacity-100 text-xs"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold mb-2 uppercase" :class="getJenisSoalBadge(soal.jenis_soal).color">
                                                {{ getJenisSoalBadge(soal.jenis_soal).label }}
                                            </span>
                                            <div :class="['text-sm line-clamp-3', hasArabic(soal.question) ? 'font-arabic text-right text-lg leading-relaxed' : 'text-gray-700 dark:text-gray-300']" :dir="hasArabic(soal.question) ? 'rtl' : 'ltr'" v-html="fixImageUrls(soal.question)"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kanan: Soal Terpilih (Draft) -->
                    <div class="lg:col-span-7 flex flex-col h-[calc(100vh-200px)]">
                        <div class="bg-primary-50 dark:bg-primary-900/20 rounded-t-3xl border border-b-0 border-primary-100 dark:border-primary-900/30 p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <h3 class="font-black text-primary-800 dark:text-primary-300">Soal Terpilih ({{ selectedSoals.length }})</h3>
                                <span class="px-2 py-0.5 rounded-full bg-primary-200 dark:bg-primary-800 text-primary-700 dark:text-primary-300 text-xs font-bold">Total Poin: {{ selectedSoals.reduce((a, b) => a + (b.true_default_point || 0), 0) }}</span>
                            </div>
                            <button @click="unselectAll" class="text-xs font-bold text-rose-600 hover:text-rose-700">Kosongkan</button>
                        </div>
                        <div class="bg-primary-50/30 dark:bg-primary-900/10 rounded-b-3xl border border-primary-100 dark:border-primary-900/30 flex-1 overflow-y-auto p-4 custom-scrollbar">
                            <div v-if="selectedSoals.length === 0" class="h-full flex flex-col items-center justify-center text-center px-4">
                                <div class="w-16 h-16 bg-white dark:bg-gray-800 rounded-full flex items-center justify-center mb-4 text-2xl text-gray-300">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <h4 class="font-bold text-gray-700 dark:text-gray-300">Belum Ada Soal Terpilih</h4>
                                <p class="text-sm text-gray-500 mt-1 max-w-sm">Pilih soal dari panel di sebelah kiri untuk memasukkannya ke dalam draft ujian ini.</p>
                            </div>

                            <div class="space-y-3">
                                <div v-for="(soal, i) in selectedSoals" :key="soal.id" 
                                    draggable="true"
                                    @dragstart="onDragStart(i)"
                                    @dragover.prevent
                                    @drop="onDrop(i)"
                                    class="flex items-stretch gap-3 group">
                                    
                                    <!-- Nomor & Drag Handle -->
                                    <div class="w-10 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 flex flex-col items-center justify-center py-2 cursor-grab active:cursor-grabbing text-gray-400 hover:text-primary-500 hover:border-primary-300 transition-colors shadow-sm relative" title="Tahan dan geser untuk mengurutkan">
                                        <span class="text-xs font-bold mb-1 absolute top-2 left-1/2 -translate-x-1/2 text-gray-400">{{ i + 1 }}</span>
                                        <i class="fas fa-grip-vertical mt-4"></i>
                                    </div>

                                    <!-- Konten Soal -->
                                    <div class="flex-1 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm relative transition-all group-hover:border-primary-200">
                                        <div class="flex justify-between items-start mb-2">
                                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase" :class="getJenisSoalBadge(soal.jenis_soal).color">
                                                {{ getJenisSoalBadge(soal.jenis_soal).label }}
                                            </span>
                                            
                                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity bg-gray-50 dark:bg-gray-900 rounded-lg p-1 border border-gray-100 dark:border-gray-700">
                                                <button @click="moveUp(i)" :disabled="i === 0" class="w-7 h-7 flex items-center justify-center rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-500 disabled:opacity-30">
                                                    <i class="fas fa-arrow-up"></i>
                                                </button>
                                                <button @click="moveDown(i)" :disabled="i === selectedSoals.length - 1" class="w-7 h-7 flex items-center justify-center rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-500 disabled:opacity-30">
                                                    <i class="fas fa-arrow-down"></i>
                                                </button>
                                                <div class="w-px h-4 bg-gray-300 mx-1"></div>
                                                <button @click="toggleSelect(soal)" class="w-7 h-7 flex items-center justify-center rounded hover:bg-rose-100 text-rose-500">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div :class="['text-sm prose prose-sm max-w-none dark:prose-invert line-clamp-3', hasArabic(soal.question) ? 'font-arabic text-right text-lg leading-relaxed' : 'text-gray-800 dark:text-gray-200']" :dir="hasArabic(soal.question) ? 'rtl' : 'ltr'" v-html="fixImageUrls(soal.question)"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </DashboardLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 20px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #475569;
}
</style>

<style>
.font-arabic {
    font-family: 'Amiri', 'Cairo', 'Noto Naskh Arabic', 'Droid Arabic Naskh', 'Geeza Pro', 'Traditional Arabic', 'Segoe UI Historic', 'Times New Roman', serif !important;
}
img.fm-editor-equation {
    display: inline-block !important;
    vertical-align: middle !important;
    margin: 0 4px !important;
    max-height: 4.5rem !important;
    min-height: 1.2em !important;
    width: auto !important;
    background: transparent !important;
}
.prose p {
    margin-top: 0 !important;
    margin-bottom: 0.25rem !important;
    padding: 0 !important;
}
.prose p:last-child {
    margin-bottom: 0 !important;
}
</style>
