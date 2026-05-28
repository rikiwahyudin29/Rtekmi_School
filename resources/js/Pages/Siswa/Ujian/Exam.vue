<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    ujian: Object,
    jadwal: Object
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

const soals = ref([]);
const currentSoalIndex = ref(0);
const loading = ref(true);
const timeLeft = ref(props.jadwal.durasi * 60); // Detik
let timerInterval = null;

const fetchSoal = async () => {
    loading.value = true;
    try {
        const response = await fetch(`/api/cbt/get-soal/${props.ujian.id}`);
        const result = await response.json();
        if (result.status === 'success') {
            soals.value = result.data;
        }
    } catch (e) {
        console.error('Error fetching soal:', e);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchSoal();
    
    // Kalkulasi sisa waktu berdasarkan waktu_mulai jika ada
    if (props.ujian.waktu_mulai) {
        const startTime = new Date(props.ujian.waktu_mulai).getTime();
        const now = new Date().getTime();
        const diffInSeconds = Math.floor((now - startTime) / 1000);
        const maxDuration = props.jadwal.durasi * 60;
        
        timeLeft.value = Math.max(0, maxDuration - diffInSeconds);
    }

    timerInterval = setInterval(() => {
        if (timeLeft.value > 0) {
            timeLeft.value--;
        } else {
            clearInterval(timerInterval);
            selesaiUjian();
        }
    }, 1000);
});

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
});

const currentSoal = computed(() => {
    if (soals.value.length === 0) return null;
    return soals.value[currentSoalIndex.value];
});

const formattedTime = computed(() => {
    const h = Math.floor(timeLeft.value / 3600);
    const m = Math.floor((timeLeft.value % 3600) / 60);
    const s = timeLeft.value % 60;
    return `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
});

const nextSoal = () => {
    if (currentSoalIndex.value < soals.value.length - 1) {
        currentSoalIndex.value++;
    }
};

const prevSoal = () => {
    if (currentSoalIndex.value > 0) {
        currentSoalIndex.value--;
    }
};

const goToSoal = (index) => {
    currentSoalIndex.value = index;
};

const simpanJawaban = async (opsiId) => {
    // Implementasi simpan asinkron
    console.log('Menyimpan opsi', opsiId, 'untuk soal', currentSoal.value.id);
    // TODO: fetch/axios POST to /api/cbt/save-jawaban
};

const selesaiUjian = () => {
    if (confirm('Apakah Anda yakin ingin menyelesaikan ujian? Anda tidak bisa mengulangi lagi.')) {
        fetch(`/api/cbt/finish/${props.ujian.id}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' }
        }).then(() => {
            router.get('/siswa/cbt/overview');
        });
    }
};
</script>

<template>
    <Head title="Mengerjakan Ujian CBT" />

    <div class="min-h-screen bg-gray-100 flex flex-col">
        <!-- Header Ujian -->
        <header class="bg-primary-700 text-white shadow-md sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="font-bold text-xl">{{ jadwal.nama_ujian }}</div>
                    <div class="hidden md:block px-3 py-1 bg-white/20 rounded-lg text-sm">
                        {{ jadwal.bankSoal?.mapel?.nama_mapel }}
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2 bg-white/10 px-4 py-2 rounded-xl border border-white/20">
                        <i class="fas fa-clock text-yellow-300"></i>
                        <span class="font-mono text-xl font-bold tracking-wider" :class="{'text-red-400': timeLeft < 300}">
                            {{ formattedTime }}
                        </span>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 max-w-7xl mx-auto w-full px-4 py-6 flex flex-col lg:flex-row gap-6">
            
            <!-- Area Soal Kiri -->
            <div class="flex-1 flex flex-col gap-4">
                <div v-if="loading" class="bg-white rounded-2xl shadow-sm p-12 flex flex-col items-center justify-center min-h-[400px]">
                    <i class="fas fa-circle-notch fa-spin text-4xl text-primary-500 mb-4"></i>
                    <p class="text-gray-500">Memuat soal ujian...</p>
                </div>
                
                <template v-else-if="currentSoal">
                    <div class="bg-white rounded-2xl shadow-sm p-6 lg:p-8 flex-1 flex flex-col">
                        <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100">
                            <h2 class="text-lg font-bold text-gray-800">
                                SOAL NOMOR <span class="text-2xl text-primary-600 ml-1">{{ currentSoalIndex + 1 }}</span>
                            </h2>
                            <span class="px-3 py-1 bg-gray-100 text-gray-600 text-xs font-bold uppercase rounded-lg">
                                Pilihan Ganda
                            </span>
                        </div>
                        
                        <div :class="['prose max-w-none mb-8', hasArabic(currentSoal.question) ? 'font-arabic text-right text-2xl leading-relaxed' : 'text-gray-800 text-lg']" :dir="hasArabic(currentSoal.question) ? 'rtl' : 'ltr'" v-html="fixImageUrls(currentSoal.question)"></div>
                        
                        <!-- Opsi PG -->
                        <div v-if="currentSoal.jenis_soal == 1" class="space-y-3 mt-auto">
                            <label v-for="(opsi, index) in currentSoal.opsi" :key="opsi.id" 
                                class="flex items-start gap-4 p-4 border border-gray-200 rounded-xl cursor-pointer hover:bg-primary-50 hover:border-primary-300 transition-colors"
                                :dir="hasArabic(opsi.body) ? 'rtl' : 'ltr'">
                                <input type="radio" :name="`soal_${currentSoal.id}`" :value="opsi.id" @change="simpanJawaban(opsi.id)" class="mt-1 w-5 h-5 text-primary-600 focus:ring-primary-500">
                                <div :class="['flex-1', hasArabic(opsi.body) ? 'font-arabic text-right text-xl leading-relaxed' : 'text-gray-700']" :dir="hasArabic(opsi.body) ? 'rtl' : 'ltr'" v-html="fixImageUrls(opsi.body)"></div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Navigasi Bawah -->
                    <div class="bg-white rounded-2xl shadow-sm p-4 flex justify-between items-center">
                        <button @click="prevSoal" :disabled="currentSoalIndex === 0" class="px-6 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-bold hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-chevron-left mr-2"></i> Sebelumnya
                        </button>
                        <button @click="nextSoal" :disabled="currentSoalIndex === soals.length - 1" class="px-6 py-2.5 rounded-xl bg-primary-600 text-white font-bold hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            Selanjutnya <i class="fas fa-chevron-right ml-2"></i>
                        </button>
                    </div>
                </template>
            </div>
            
            <!-- Sidebar Kanan Navigasi Soal -->
            <div class="w-full lg:w-80 flex flex-col gap-4">
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden flex flex-col h-full lg:max-h-[calc(100vh-8rem)]">
                    <div class="p-4 bg-gray-50 border-b border-gray-100 font-bold text-gray-800 text-center">
                        Navigasi Soal
                    </div>
                    <div class="p-4 overflow-y-auto grid grid-cols-5 gap-2 content-start flex-1">
                        <button v-for="(soal, index) in soals" :key="soal.id"
                            @click="goToSoal(index)"
                            class="w-10 h-10 rounded-lg flex items-center justify-center font-bold text-sm transition-all"
                            :class="[
                                currentSoalIndex === index ? 'bg-primary-600 text-white ring-2 ring-primary-300 ring-offset-1' : 'bg-white border border-gray-300 text-gray-700 hover:border-primary-500 hover:text-primary-600',
                            ]">
                            {{ index + 1 }}
                        </button>
                    </div>
                    <div class="p-4 bg-gray-50 border-t border-gray-100">
                        <button @click="selesaiUjian" class="w-full py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl shadow-sm flex items-center justify-center gap-2 transition-colors">
                            <i class="fas fa-check-double"></i> SELESAI UJIAN
                        </button>
                    </div>
                </div>
            </div>
            
        </main>
    </div>
</template>

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
    margin-bottom: 0.5rem !important;
    padding: 0 !important;
}
.prose p:last-child {
    margin-bottom: 0 !important;
}
</style>
