<script setup>
import { Head } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const props = defineProps({
    tamu: Object,
    waktu_cetak: String,
});

onMounted(() => {
    // Auto print when component is loaded
    setTimeout(() => {
        window.print();
        // Option to close window after print:
        // window.onafterprint = () => window.close();
    }, 500);
});
</script>

<template>
    <Head title="Cetak Karcis Tamu" />
    
    <div class="print-container text-black font-mono mx-auto bg-white" style="width: 58mm; padding: 2mm;">
        <!-- Header -->
        <div class="text-center mb-4">
            <h1 class="font-bold text-lg mb-1">MTS Mariyadhul Jannah</h1>
            <p class="text-xs">Jl. Pendidikan No. 1, Kab. Bogor</p>
            <div class="border-b-2 border-dashed border-gray-400 my-2"></div>
            <h2 class="font-bold text-sm">KARCIS BUKU TAMU</h2>
        </div>

        <!-- Nomor Antrian -->
        <div class="text-center my-4">
            <p class="text-xs text-gray-500 mb-1">No Antrian:</p>
            <h1 class="text-4xl font-black">{{ tamu.no_antrian || '-' }}</h1>
        </div>

        <!-- Detail Tamu -->
        <div class="text-xs space-y-1 mb-4">
            <div class="border-b-2 border-dashed border-gray-400 my-2"></div>
            <div class="flex flex-col">
                <span class="text-gray-500">Tanggal:</span>
                <span class="font-bold">{{ tamu.tanggal || '-' }}</span>
            </div>
            <div class="flex flex-col">
                <span class="text-gray-500">Nama Tamu:</span>
                <span class="font-bold uppercase">{{ tamu.nama_lengkap }}</span>
            </div>
            <div class="flex flex-col">
                <span class="text-gray-500">Instansi/Asal:</span>
                <span class="font-bold uppercase">{{ tamu.instansi_asal || '-' }}</span>
            </div>
            <div class="flex flex-col">
                <span class="text-gray-500">Keperluan:</span>
                <span class="font-bold">{{ tamu.keperluan }}</span>
            </div>
            <div class="flex flex-col mt-2">
                <span class="text-gray-500">Penerima/Tujuan:</span>
                <span class="font-bold">{{ tamu.tujuan }}</span>
            </div>
        </div>

        <!-- QR Code Placeholder (Opsional jika ingin dipakai) -->
        <div class="text-center my-4">
            <div class="border-b-2 border-dashed border-gray-400 my-2"></div>
            <!-- If we had a QR Code generator here we could show it -->
            <div class="w-16 h-16 border-2 border-black mx-auto flex items-center justify-center p-1">
                <span class="text-[8px] leading-tight text-center">SCAN<br>DISINI</span>
            </div>
            <p class="text-[10px] mt-2">Simpan karcis ini selama kunjungan Anda.</p>
        </div>

        <!-- Footer -->
        <div class="text-center text-[9px] mt-4 pt-2 border-t-2 border-dashed border-gray-400">
            <p>Terima kasih atas kunjungannya.</p>
            <p class="mt-1 text-gray-500">Dicetak: {{ waktu_cetak }}</p>
            <p class="font-bold mt-1">SIAKAD v2.0</p>
        </div>
    </div>
</template>

<style>
/* CSS khusus untuk print (kertas thermal 58mm) */
@media print {
    @page {
        margin: 0;
        size: 58mm auto; /* 58mm width thermal printer */
    }
    body {
        margin: 0;
        padding: 0;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .print-container {
        width: 100% !important;
        padding: 2mm !important;
        margin: 0 !important;
    }
    /* Hide all other elements on the page except the printable area */
    body * {
        visibility: hidden;
    }
    .print-container, .print-container * {
        visibility: visible;
    }
    .print-container {
        position: absolute;
        left: 0;
        top: 0;
    }
}
</style>
