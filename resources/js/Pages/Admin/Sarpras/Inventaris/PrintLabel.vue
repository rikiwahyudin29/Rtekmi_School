<script setup>
import { Head } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const props = defineProps({
    items: Array,
    sekolah: Object,
    appUrl: String
});

onMounted(() => {
    // Beri sedikit jeda agar gambar QR selesai dimuat
    setTimeout(() => {
        window.print();
    }, 1000);
});
</script>

<template>
    <Head title="Cetak Label Inventaris" />

    <div class="print-container bg-white text-black font-sans">
        <!-- Notifikasi untuk tampilan layar (Tidak akan ikut terprint) -->
        <div class="no-print bg-indigo-50 border-b border-indigo-100 p-4 text-center fixed top-0 w-full z-50">
            <p class="font-bold text-indigo-800 mb-2">Pratinjau Cetak Label Inventaris</p>
            <div class="flex justify-center gap-4">
                <button @click="() => window.print()" class="px-4 py-2 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-print mr-2"></i> Print Sekarang
                </button>
                <button @click="() => window.close()" class="px-4 py-2 bg-gray-200 text-gray-800 font-bold rounded-lg hover:bg-gray-300">
                    Tutup
                </button>
            </div>
            <p class="text-xs text-gray-500 mt-2">Atur kertas pada setting printer ke A4 dan ubah margin ke "None" atau "Minimum"</p>
        </div>

        <div class="grid-layout no-print-margin pt-24 print:pt-0">
            <!-- Label per item -->
            <div v-for="(item, index) in items" :key="index" class="label-box relative overflow-hidden">
                <!-- Watermark -->
                <div class="absolute inset-0 flex items-center justify-center opacity-5 pointer-events-none">
                    <i class="fas fa-boxes text-6xl transform -rotate-12"></i>
                </div>
                
                <div class="label-header relative z-10">
                    <img v-if="sekolah?.logo" :src="'/uploads/identitas/' + sekolah.logo" class="h-6 w-auto mr-2 object-contain" alt="Logo" @error="$event.target.style.display='none'">
                    <i v-else class="fas fa-school text-indigo-800 text-lg mr-2"></i>
                    <div>
                        <div class="font-black text-[9px] uppercase tracking-wider leading-tight text-gray-900">{{ sekolah?.nama_sekolah || 'INSTITUSI PENDIDIKAN' }}</div>
                        <div class="text-[7px] uppercase tracking-widest text-indigo-700 font-bold">Aset & Inventaris</div>
                    </div>
                </div>
                
                <div class="label-body relative z-10">
                    <div class="qr-container bg-white">
                        <img :src="`https://api.qrserver.com/v1/create-qr-code/?size=150x150&margin=1&data=${encodeURIComponent(appUrl + '/admin/sarpras/inventaris?search=' + item.kode_barang)}`" alt="QR Code" class="w-16 h-16">
                    </div>
                    <div class="details-container">
                        <div class="text-[10px] text-gray-500 font-bold mb-0.5 uppercase">KODE BARANG</div>
                        <div class="font-black text-sm tracking-wider leading-none mb-1.5 text-gray-900">{{ item.kode_barang || '-' }}</div>
                        
                        <div class="text-[10px] text-gray-500 font-bold mb-0.5 mt-1 uppercase">NAMA ASET</div>
                        <div class="font-bold text-xs truncate leading-tight w-full text-gray-800" :title="item.nama_barang">{{ item.nama_barang }}</div>
                    </div>
                </div>

                <div class="label-footer relative z-10">
                    <div>LOKASI: <span class="font-bold text-gray-800">{{ item.lokasi || '-' }}</span></div>
                    <div>TGL: <span class="font-bold text-gray-800">{{ item.tgl_masuk ? new Date(item.tgl_masuk).toLocaleDateString('id-ID') : '-' }}</span></div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
/* Reset basic styles for print */
@media print {
    @page {
        margin: 1cm;
        size: A4;
    }
    body {
        background-color: white !important;
        margin: 0;
        padding: 0;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .no-print {
        display: none !important;
    }
    .no-print-margin {
        padding-top: 0 !important;
    }
}

.print-container {
    width: 100%;
    min-height: 100vh;
    background-color: #f3f4f6; /* light gray for screen preview */
}

@media print {
    .print-container {
        background-color: white;
    }
}

.grid-layout {
    display: grid;
    /* Grid 3 kolom untuk label, pas untuk A4 portrait */
    grid-template-columns: repeat(3, 1fr);
    gap: 0.5cm;
    padding: 1cm;
    max-width: 21cm; /* A4 width */
    margin: 0 auto;
}

@media print {
    .grid-layout {
        padding: 0;
        gap: 0.3cm;
    }
}

.label-box {
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    padding: 8px;
    background: white;
    box-sizing: border-box;
    width: 100%;
    page-break-inside: avoid; /* Prevent label splitting across pages */
    display: flex;
    flex-direction: column;
}

@media print {
    .label-box {
        border: 2px solid #000;
    }
}

.label-header {
    display: flex;
    align-items: center;
    border-bottom: 1px solid #e5e7eb;
    padding-bottom: 6px;
    margin-bottom: 6px;
}

@media print {
    .label-header {
        border-bottom-color: #000;
    }
}

.label-body {
    display: flex;
    flex-direction: row;
    align-items: flex-start;
    gap: 8px;
    flex: 1;
}

.qr-container {
    flex-shrink: 0;
    border: 1px solid #e5e7eb;
    padding: 2px;
    border-radius: 4px;
}

@media print {
    .qr-container {
        border-color: #000;
    }
}

.details-container {
    flex: 1;
    min-width: 0; /* Important for truncate to work */
    display: flex;
    flex-direction: column;
}

.label-footer {
    display: flex;
    justify-content: space-between;
    font-size: 8px;
    color: #4b5563;
    margin-top: 8px;
    border-top: 1px dashed #e5e7eb;
    padding-top: 4px;
}

@media print {
    .label-footer {
        color: #000;
        border-top-color: #000;
    }
}
</style>
