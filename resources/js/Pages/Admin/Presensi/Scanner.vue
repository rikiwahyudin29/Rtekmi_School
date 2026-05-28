<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import axios from 'axios';

const inputKode = ref('');
const result = ref(null);
const processing = ref(false);
const nfcStatus = ref(false);
const isNfcSupported = ref(typeof window !== 'undefined' && 'NDEFReader' in window);

let html5QrcodeScanner = null;

const submitScan = async (kode, tipeInput = 'MANUAL') => {
    if (!kode || processing.value) return;
    
    processing.value = true;
    result.value = null;
    
    // Pause scanner if it's running
    if (html5QrcodeScanner && html5QrcodeScanner.isScanning) {
        html5QrcodeScanner.pause();
    }

    try {
        const response = await axios.post(route('admin.presensi.proses_scan'), {
            kode: kode,
            tipe: tipeInput
        });

        if (response.data.status === 'success') {
            result.value = response.data;
        } else {
            alert(response.data.message);
        }
    } catch (err) {
        alert('Terjadi kesalahan saat memproses absensi.');
    } finally {
        processing.value = false;
        inputKode.value = '';
        document.getElementById('inputKode').focus();
        
        // Resume scanner after a delay if it was scanning
        if (html5QrcodeScanner && html5QrcodeScanner.isScanning) {
            setTimeout(() => {
                html5QrcodeScanner.resume();
            }, 2000);
        }
    }
};

const handleEnter = (e) => {
    if(e.key === 'Enter') {
        submitScan(inputKode.value);
    }
};

const initScanner = () => {
    if (typeof Html5Qrcode !== 'undefined') {
        html5QrcodeScanner = new Html5Qrcode("reader");
        const config = { fps: 10, qrbox: { width: 250, height: 250 } };
        
        html5QrcodeScanner.start({ facingMode: "environment" }, config, (decodedText) => {
            submitScan(decodedText, 'QR');
        }, () => {}).catch(err => {
            console.log("Scanner failed to start:", err);
        });
    }
};

const startNFC = async () => {
    if ('NDEFReader' in window) {
        try {
            const ndef = new NDEFReader();
            await ndef.scan();
            nfcStatus.value = true;
            
            ndef.onreading = event => {
                const serialNumber = event.serialNumber;
                if(serialNumber) {
                    const uidClean = serialNumber.replaceAll(":", "").toUpperCase();
                    submitScan(uidClean, 'NFC');
                }
            };
        } catch (error) {
            alert('NFC Gagal diaktifkan: ' + error);
        }
    } else {
        alert('Browser Tidak Mendukung fitur NFC.');
    }
};

onMounted(() => {
    document.getElementById('inputKode').focus();
    
    // Load html5-qrcode dynamically
    if (!document.getElementById('html5-qrcode-js')) {
        const script = document.createElement('script');
        script.id = 'html5-qrcode-js';
        script.src = 'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js';
        script.onload = () => {
            initScanner();
        };
        document.head.appendChild(script);
    } else {
        setTimeout(initScanner, 500);
    }
});

onUnmounted(() => {
    if (html5QrcodeScanner && html5QrcodeScanner.isScanning) {
        html5QrcodeScanner.stop();
    }
});
</script>

<template>
    <DashboardLayout>
        <div class="py-8">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-black text-gray-800 dark:text-white">Scanner Presensi</h1>
                    <p class="text-gray-500">Scan QR Code atau Tempel Kartu (NFC)</p>
                </div>

                <div v-if="!nfcStatus && isNfcSupported" class="mb-6 text-center">
                    <button @click="startNFC" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-purple-500/30 flex items-center justify-center gap-2 mx-auto transition-all">
                        <i class="fas fa-wifi animate-pulse"></i> Aktifkan Baca NFC
                    </button>
                </div>
                <div v-if="nfcStatus" class="mb-6 text-center text-purple-600 font-bold animate-pulse">
                    📡 NFC AKTIF! Tempelkan Kartu ke Belakang HP...
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Kiri: Kamera Scanner -->
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700">
                        <div id="reader" class="w-full rounded-xl overflow-hidden bg-black aspect-square max-h-80 mx-auto relative flex items-center justify-center">
                             <!-- Html5Qrcode will render here -->
                        </div>
                        <p class="text-center text-xs text-gray-400 mt-4 italic">Arahkan kamera ke QR Code Siswa/Guru</p>
                    </div>

                    <!-- Kanan: Hasil & Manual Input -->
                    <div class="space-y-6">
                        <!-- Result Box -->
                        <div v-show="result" class="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 text-center relative overflow-hidden transition-all duration-300">
                            <div class="absolute top-0 left-0 w-full h-2" :class="result?.ket === 'Terlambat' ? 'bg-red-500' : 'bg-green-500'"></div>
                            
                            <div class="w-24 h-24 mx-auto rounded-full flex items-center justify-center mb-4 text-4xl" :class="result?.ket === 'Terlambat' ? 'bg-red-50' : 'bg-green-50'">
                                {{ result?.ket === 'Terlambat' ? '⚠️' : '✅' }}
                            </div>

                            <h2 class="text-2xl font-black text-gray-800 dark:text-white mb-1">{{ result?.nama }}</h2>
                            <p class="text-lg font-bold text-gray-500">{{ result?.tipe }} - Presensi</p>
                            
                            <div class="mt-6 py-3 px-6 bg-gray-50 dark:bg-gray-900 rounded-xl inline-block">
                                <span class="text-3xl font-mono font-bold text-gray-800 dark:text-white">{{ result?.jam }}</span>
                            </div>

                            <div class="mt-4">
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase" 
                                      :class="result?.ket === 'Terlambat' ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600'">
                                    {{ result?.ket }}
                                </span>
                            </div>
                        </div>

                        <div v-show="!result" class="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 flex flex-col items-center justify-center h-64 text-gray-400">
                            <i class="fas fa-qrcode text-6xl mb-4 opacity-50"></i>
                            <p class="font-medium text-lg">Menunggu Scan...</p>
                        </div>

                        <!-- Manual Input -->
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
                            <h3 class="font-bold text-gray-700 dark:text-white mb-3">Input Manual (NIS/NIK)</h3>
                            <div class="flex gap-2 relative">
                                <input 
                                    id="inputKode"
                                    type="text" 
                                    v-model="inputKode" 
                                    @keypress="handleEnter"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl font-bold focus:border-indigo-500 outline-none dark:bg-gray-900 dark:border-gray-600 dark:text-white" 
                                    placeholder="Ketik NIS/NIK lalu Enter..."
                                    :disabled="processing"
                                >
                                <button @click="submitScan(inputKode, 'MANUAL')" :disabled="processing" class="absolute right-2 top-2 bottom-2 bg-indigo-600 text-white px-4 rounded-lg font-bold hover:bg-indigo-700 flex items-center justify-center">
                                    <i class="fas fa-paper-plane" v-if="!processing"></i>
                                    <i class="fas fa-spinner fa-spin" v-else></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </DashboardLayout>
</template>
