<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    kelompok: Array
});
</script>

<template>
    <Head title="P5 Koordinator" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-seedling text-green-500"></i>
                        Koordinator P5
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Input nilai P5 untuk kelompok yang Anda bimbing.
                    </p>
                </div>
            </div>

            <!-- Tabel Kelompok -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                            <tr>
                                <th scope="col" class="px-6 py-4 w-16">No</th>
                                <th scope="col" class="px-6 py-4">Nama Kelompok</th>
                                <th scope="col" class="px-6 py-4">Projek / Tema</th>
                                <th scope="col" class="px-6 py-4">Target Kelas</th>
                                <th scope="col" class="px-6 py-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(k, index) in kelompok" :key="k.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4">{{ index + 1 }}</td>
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ k.nama_kelompok }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-green-600">{{ k.projek?.nama_projek || '-' }}</div>
                                    <div class="text-xs text-gray-500 mt-1">Tema: {{ k.projek?.tema?.nama_tema || '-' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg text-xs border border-gray-200 dark:border-gray-600">
                                        ID Kelas: {{ k.kelas_id_list }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <Link :href="route('guru.p5.input_nilai', k.id)" class="px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 font-medium shadow-sm transition-colors text-xs inline-flex items-center gap-2">
                                        <i class="fas fa-edit"></i> Input Nilai P5
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="kelompok.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">Anda belum ditugaskan sebagai koordinator kelompok P5 manapun.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
