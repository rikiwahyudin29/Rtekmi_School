<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    responden: Object,
    jawaban: Array
});
</script>

<template>
    <Head :title="'Detail Tracer - ' + responden.nama_lengkap" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <Link :href="route('admin.kesiswaan.tracer.index')" class="text-sm font-medium text-emerald-600 hover:text-emerald-700 mb-2 inline-flex items-center gap-1">
                        <i class="fas fa-arrow-left"></i> Kembali ke Data Tracer
                    </Link>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-file-invoice text-indigo-500"></i>
                        Laporan Lulusan: {{ responden.nama_lengkap }}
                    </h2>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Profil Alumni -->
                <div class="md:col-span-1">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 sticky top-24">
                        <div class="flex flex-col items-center mb-6">
                            <div class="w-24 h-24 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-500 flex items-center justify-center text-4xl mb-4 border-4 border-white dark:border-gray-800 shadow-md">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white text-center">{{ responden.nama_lengkap }}</h3>
                            <p class="text-sm text-gray-500">Angkatan {{ responden.tahun_angkatan }}</p>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="flex flex-col">
                                <span class="text-xs text-gray-500 font-bold uppercase">NISN</span>
                                <span class="text-sm text-gray-900 dark:text-white">{{ responden.nisn }}</span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs text-gray-500 font-bold uppercase">No. HP / WA</span>
                                <span class="text-sm text-gray-900 dark:text-white">{{ responden.no_hp_siswa || '-' }}</span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs text-gray-500 font-bold uppercase">Status Saat Ini</span>
                                <span class="mt-1">
                                    <span class="px-2 py-1 rounded-md text-xs font-bold" 
                                          :class="responden.status_kegiatan == 'Kuliah' ? 'bg-indigo-100 text-indigo-700' : (responden.status_kegiatan == 'Bekerja' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700')">
                                        {{ responden.status_kegiatan }}
                                    </span>
                                </span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs text-gray-500 font-bold uppercase">Nama Instansi / Perusahaan / Kampus</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ responden.nama_instansi || '-' }}</span>
                            </div>
                            <div class="flex flex-col border-t dark:border-gray-700 pt-4 mt-4">
                                <span class="text-xs text-gray-500 font-bold uppercase">Waktu Pengisian</span>
                                <span class="text-sm text-gray-900 dark:text-white">
                                    <i class="far fa-calendar-alt mr-1"></i> {{ new Date(responden.tanggal_isi).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hasil Kuesioner -->
                <div class="md:col-span-2 space-y-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                        <i class="fas fa-tasks text-emerald-500"></i> Hasil Kuesioner
                    </h3>
                    
                    <div v-for="(j, i) in jawaban" :key="j.id" class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                        <div class="flex gap-4">
                            <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 flex items-center justify-center font-bold flex-shrink-0">
                                {{ i + 1 }}
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white text-base mb-2">{{ j.pertanyaan }}</h4>
                                <div class="bg-emerald-50 dark:bg-emerald-900/20 text-emerald-800 dark:text-emerald-300 px-4 py-3 rounded-xl border border-emerald-100 dark:border-emerald-800/30">
                                    {{ j.jawaban }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="jawaban.length === 0" class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-sm border border-gray-100 dark:border-gray-700 text-center">
                        <i class="fas fa-file-excel text-4xl text-gray-300 mb-3 block"></i>
                        <h4 class="font-bold text-gray-500">Kuesioner Kosong</h4>
                        <p class="text-sm text-gray-400">Responden ini tidak menjawab pertanyaan kuesioner tambahan.</p>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
