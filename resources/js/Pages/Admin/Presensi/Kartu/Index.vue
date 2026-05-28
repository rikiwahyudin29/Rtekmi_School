<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    kelas: Array
});

const selectedKelas = ref('');

const cetakSiswa = () => {
    if (!selectedKelas.value) {
        alert('Pilih kelas terlebih dahulu!');
        return;
    }
    window.open(route('admin.presensi.kartu.cetak_siswa', { id_kelas: selectedKelas.value }), '_blank');
};

const cetakGuru = () => {
    window.open(route('admin.presensi.kartu.cetak_guru'), '_blank');
};

// Registrasi RFID (dummy functionality for UI completeness)
const inputRFID = ref('');
</script>

<template>
    <Head title="Cetak Kartu Pelajar & Guru" />

    <DashboardLayout>
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-1">Cetak Kartu Presensi & Pelajar</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Pilih mode cetak untuk Siswa atau Guru.</p>
        </div>

        <div class="py-4">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Kartu Siswa -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                        <div class="p-6">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-2xl">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">Cetak Kartu Pelajar (Siswa)</h3>
                                    <p class="text-sm text-gray-500">Cetak kartu pelajar beserta QR Code presensi.</p>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Kelas</label>
                                <select v-model="selectedKelas" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="" disabled>-- Pilih Kelas --</option>
                                    <option v-for="k in kelas" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                                </select>
                            </div>

                            <div class="mt-6">
                                <button @click="cetakSiswa" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow flex justify-center items-center gap-2">
                                    <i class="fas fa-print"></i> Cetak Berdasarkan Kelas
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Kartu Guru -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                        <div class="p-6">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-2xl">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">Cetak Kartu Guru & Staff</h3>
                                    <p class="text-sm text-gray-500">Cetak kartu identitas untuk seluruh Guru dan Staff.</p>
                                </div>
                            </div>
                            
                            <div class="mt-4 h-16 flex items-center text-sm text-gray-500 italic border-l-4 border-green-500 pl-4 bg-green-50 rounded-r-md">
                                Menarik data dari tabel Guru dan secara massal mencetak ID Card.
                            </div>

                            <div class="mt-6">
                                <button @click="cetakGuru" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded shadow flex justify-center items-center gap-2">
                                    <i class="fas fa-print"></i> Cetak Seluruh Guru
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </DashboardLayout>
</template>
