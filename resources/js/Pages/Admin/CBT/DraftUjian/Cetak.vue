<script setup>
import { Head } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const props = defineProps({
    draft: Object,
    soals: Array,
    sekolah: Object,
    baseUrl: String
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

onMounted(() => {
    // Optionally trigger print automatically
    // window.print();
});
</script>

<template>
    <Head title="Cetak Soal - Draft Ujian" />

    <div class="print-actions no-print fixed top-4 right-4 flex gap-2">
        <button onclick="window.print()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg font-bold shadow-lg flex items-center gap-2">
            <i class="fas fa-print"></i> Cetak Sekarang
        </button>
        <button onclick="window.close()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-bold shadow-lg flex items-center gap-2">
            <i class="fas fa-times"></i> Tutup
        </button>
    </div>

    <div class="print-container">
        <!-- Header Print -->
        <div class="print-header text-center border-b-2 border-black pb-4 mb-6">
            <h1 class="text-xl font-bold uppercase">{{ sekolah?.nama_sekolah || 'NAMA SEKOLAH' }}</h1>
            <h2 class="text-lg font-bold uppercase">NASKAH SOAL {{ draft.nama }}</h2>
            <div class="text-sm mt-2 flex justify-center gap-6">
                <span>Mata Pelajaran: <strong>{{ draft.mapel?.nama_mapel || '-' }}</strong></span>
                <span>Waktu Pengerjaan: <strong>{{ draft.timeout }} Menit</strong></span>
            </div>
        </div>

        <!-- Daftar Soal -->
        <div class="soal-list space-y-8">
            <div v-for="(soal, index) in soals" :key="soal.id" class="soal-item pb-4 border-b border-gray-100 last:border-b-0 print:border-b-0 print:pb-0">
                <div class="flex gap-2 text-base">
                    <span class="font-bold min-w-[25px]">{{ index + 1 }}.</span>
                    <div class="w-full" :dir="hasArabic(soal.question) ? 'rtl' : 'ltr'">
                        <!-- Teks Soal -->
                        <div v-html="fixImageUrls(soal.question)" :class="['prose max-w-none mb-4 text-black', hasArabic(soal.question) ? 'font-arabic text-xl leading-relaxed text-right' : '']" :dir="hasArabic(soal.question) ? 'rtl' : 'ltr'"></div>
                        
                        <!-- 1. Pilihan Ganda (Single Select) -->
                        <div v-if="soal.jenis_soal == 1" class="grid grid-cols-1 gap-2.5 mt-2 ms-4">
                            <div v-for="(opsi, oIdx) in (soal.opsi || [])" :key="opsi.id" class="flex gap-2 items-start" :dir="hasArabic(opsi.body) ? 'rtl' : 'ltr'">
                                <span class="font-bold uppercase min-w-[20px]">{{ String.fromCharCode(65 + oIdx) }}.</span>
                                <div v-html="fixImageUrls(opsi.body)" :class="['prose max-w-none text-black', hasArabic(opsi.body) ? 'font-arabic text-xl leading-relaxed text-right' : '']" :dir="hasArabic(opsi.body) ? 'rtl' : 'ltr'"></div>
                            </div>
                        </div>

                        <!-- 2. Esai -->
                        <div v-else-if="soal.jenis_soal == 2" class="mt-4 ms-4 space-y-2 opacity-60 print:opacity-100 max-w-2xl">
                            <div class="border-b border-dashed border-gray-400 h-6 w-full"></div>
                            <div class="border-b border-dashed border-gray-400 h-6 w-full"></div>
                            <div class="border-b border-dashed border-gray-400 h-6 w-full"></div>
                            <div class="border-b border-dashed border-gray-400 h-6 w-full"></div>
                        </div>

                        <!-- 3. PG Kompleks (Multi Select) -->
                        <div v-else-if="soal.jenis_soal == 3" class="space-y-2.5 mt-2 ms-4">
                            <div v-for="(opsi, oIdx) in (soal.opsi || [])" :key="opsi.id" class="flex gap-3 items-start" :dir="hasArabic(opsi.body) ? 'rtl' : 'ltr'">
                                <span class="inline-block border border-black w-4 h-4 mt-1 flex-shrink-0 flex items-center justify-center font-mono text-[9px] font-bold">[ ]</span>
                                <span class="font-bold uppercase min-w-[20px]">{{ String.fromCharCode(65 + oIdx) }}.</span>
                                <div v-html="fixImageUrls(opsi.body)" :class="['prose max-w-none text-black', hasArabic(opsi.body) ? 'font-arabic text-xl leading-relaxed text-right' : '']" :dir="hasArabic(opsi.body) ? 'rtl' : 'ltr'"></div>
                            </div>
                        </div>

                        <!-- 4. Menjodohkan (Matching) -->
                        <div v-else-if="soal.jenis_soal == 4" class="grid grid-cols-2 gap-6 mt-4 ms-4 max-w-2xl border border-gray-300 p-4 rounded-lg bg-gray-50/50 print:bg-transparent print:border-black print:p-2">
                            <!-- Kiri: Pernyataan -->
                            <div class="space-y-3">
                                <h4 class="font-bold border-b border-black pb-1 mb-2 text-sm uppercase text-black">Pernyataan</h4>
                                <div v-for="(opsi, oIdx) in (soal.opsi || [])" :key="opsi.id" class="flex gap-2 items-start text-sm" :dir="hasArabic(opsi.body) ? 'rtl' : 'ltr'">
                                    <span class="font-bold">{{ oIdx + 1 }}.</span>
                                    <div v-html="fixImageUrls(opsi.body)" :class="['prose max-w-none text-black', hasArabic(opsi.body) ? 'font-arabic text-xl leading-relaxed text-right' : '']" :dir="hasArabic(opsi.body) ? 'rtl' : 'ltr'"></div>
                                </div>
                            </div>
                            <!-- Kanan: Pilihan Jawaban -->
                            <div class="space-y-3">
                                <h4 class="font-bold border-b border-black pb-1 mb-2 text-sm uppercase text-black">Pilihan Jawaban</h4>
                                <div v-for="(couple, cIdx) in (soal.couples || [])" :key="couple.id" class="flex gap-2 items-start text-sm" :dir="hasArabic(couple.body) ? 'rtl' : 'ltr'">
                                    <span class="font-bold uppercase">{{ String.fromCharCode(65 + cIdx) }}.</span>
                                    <div v-html="fixImageUrls(couple.body)" :class="['prose max-w-none text-black', hasArabic(couple.body) ? 'font-arabic text-xl leading-relaxed text-right' : '']" :dir="hasArabic(couple.body) ? 'rtl' : 'ltr'"></div>
                                </div>
                            </div>
                        </div>

                        <!-- 5. Isian Singkat -->
                        <div v-else-if="soal.jenis_soal == 5" class="mt-4 ms-4 max-w-2xl">
                            <p class="text-sm font-semibold text-black">Jawab: ..............................................................................................................................................</p>
                        </div>

                        <!-- 6. Benar / Salah (Statements Table) -->
                        <div v-else-if="soal.jenis_soal == 6" class="mt-4 ms-4 max-w-2xl">
                            <table class="w-full border-collapse border border-black">
                                <thead>
                                    <tr class="bg-gray-100 print:bg-transparent">
                                        <th class="border border-black p-2 text-left text-sm font-bold text-black">Pernyataan / Pernyataan Benar-Salah</th>
                                        <th class="border border-black p-2 text-center text-sm font-bold w-20 text-black">Benar</th>
                                        <th class="border border-black p-2 text-center text-sm font-bold w-20 text-black">Salah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="opsi in (soal.opsi || [])" :key="opsi.id">
                                        <td class="border border-black p-2 text-sm text-black" v-html="fixImageUrls(opsi.body)" :class="[hasArabic(opsi.body) ? 'font-arabic text-xl leading-relaxed text-right' : '']" :dir="hasArabic(opsi.body) ? 'rtl' : 'ltr'"></td>
                                        <td class="border border-black p-2 text-center font-mono text-base">[  ]</td>
                                        <td class="border border-black p-2 text-center font-mono text-base">[  ]</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div v-if="soals.length === 0" class="text-center italic py-10 text-gray-500">
                Belum ada soal yang dipilih untuk draft ini.
            </div>
        </div>
    </div>
</template>

<style>
/* CSS khusus untuk halaman cetak agar mirip lembar soal asli */
body {
    background: #f3f4f6;
    margin: 0;
    padding: 0;
}
.prose p {
    margin-top: 0 !important;
    margin-bottom: 0.5rem !important;
    padding: 0 !important;
}
.prose p:last-child {
    margin-bottom: 0 !important;
}
.prose img {
    max-width: 100%;
    height: auto;
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
.font-arabic {
    font-family: 'Amiri', 'Cairo', 'Noto Naskh Arabic', 'Droid Arabic Naskh', 'Geeza Pro', 'Traditional Arabic', 'Segoe UI Historic', 'Times New Roman', serif !important;
}
.print-container {
    max-width: 800px;
    margin: 2rem auto;
    padding: 3rem 4rem;
    background: white;
    color: black;
    font-family: "Times New Roman", Times, serif;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

@media print {
    body {
        background: white;
    }
    .print-container {
        max-width: 100%;
        margin: 0;
        padding: 0;
        box-shadow: none;
    }
    .no-print {
        display: none !important;
    }
    .soal-item {
        page-break-inside: avoid;
    }
    @page {
        margin: 2cm;
    }
}
</style>
