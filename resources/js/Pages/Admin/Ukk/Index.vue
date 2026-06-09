<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    skkni: Array,
    paket: Array,
    asesor: Array,
    jurusan: Array,
    dudi: Array
});

const formSkkni = useForm({
    kode_unit: '',
    judul_unit: '',
    jurusan_id: ''
});

const submitSkkni = () => {
    formSkkni.post(route('admin.ukk.skkni.store'), {
        onSuccess: () => formSkkni.reset()
    });
};

const formAsesor = useForm({
    nama_asesor: '',
    no_sertifikat: '',
    dudi_id: ''
});

const submitAsesor = () => {
    formAsesor.post(route('admin.ukk.asesor.store'), {
        onSuccess: () => formAsesor.reset()
    });
};
</script>

<template>
    <Head title="Manajemen UKK & SKKNI" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-certificate text-purple-500"></i>
                        Manajemen UKK & SKKNI
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Kelola data Asesor, SKKNI, dan Paket Uji Kompetensi Keahlian.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- SKKNI Section -->
                <div class="space-y-6">
                    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 border-b pb-2">Tambah SKKNI</h3>
                        <form @submit.prevent="submitSkkni" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kode Unit</label>
                                <input v-model="formSkkni.kode_unit" type="text" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Judul Unit Kompetensi</label>
                                <input v-model="formSkkni.judul_unit" type="text" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jurusan</label>
                                <select v-model="formSkkni.jurusan_id" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500" required>
                                    <option value="">-- Pilih Jurusan --</option>
                                    <option v-for="j in jurusan" :key="j.id" :value="j.id">{{ j.nama_jurusan }}</option>
                                </select>
                            </div>
                            <button type="submit" :disabled="formSkkni.processing" class="w-full px-4 py-2 bg-purple-600 text-white rounded-xl hover:bg-purple-700 font-medium">Simpan SKKNI</button>
                        </form>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Kode Unit</th>
                                        <th scope="col" class="px-6 py-3">Judul Unit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="s in skkni" :key="s.id" class="border-b dark:border-gray-700">
                                        <td class="px-6 py-3 font-bold">{{ s.kode_unit }}</td>
                                        <td class="px-6 py-3">{{ s.judul_unit }}</td>
                                    </tr>
                                    <tr v-if="skkni.length === 0">
                                        <td colspan="2" class="px-6 py-4 text-center">Belum ada data SKKNI</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Asesor Section -->
                <div class="space-y-6">
                    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 border-b pb-2">Tambah Asesor Eksternal (DUDI)</h3>
                        <form @submit.prevent="submitAsesor" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Asesor</label>
                                <input v-model="formAsesor.nama_asesor" type="text" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">No Sertifikat Asesor</label>
                                <input v-model="formAsesor.no_sertifikat" type="text" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Asal DUDI</label>
                                <select v-model="formAsesor.dudi_id" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500" required>
                                    <option value="">-- Pilih DUDI --</option>
                                    <option v-for="d in dudi" :key="d.id" :value="d.id">{{ d.nama_dudi }}</option>
                                </select>
                            </div>
                            <button type="submit" :disabled="formAsesor.processing" class="w-full px-4 py-2 bg-purple-600 text-white rounded-xl hover:bg-purple-700 font-medium">Simpan Asesor</button>
                        </form>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Nama Asesor</th>
                                        <th scope="col" class="px-6 py-3">No Sertifikat</th>
                                        <th scope="col" class="px-6 py-3">DUDI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="a in asesor" :key="a.id" class="border-b dark:border-gray-700">
                                        <td class="px-6 py-3 font-bold">{{ a.nama_asesor }}</td>
                                        <td class="px-6 py-3">{{ a.no_sertifikat }}</td>
                                        <td class="px-6 py-3">{{ a.dudi?.nama_dudi }}</td>
                                    </tr>
                                    <tr v-if="asesor.length === 0">
                                        <td colspan="3" class="px-6 py-4 text-center">Belum ada data Asesor</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
