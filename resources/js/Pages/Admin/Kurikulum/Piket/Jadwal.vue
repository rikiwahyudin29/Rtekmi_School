<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    guru: Array,
    jadwal_per_hari: Object
});

const form = useForm({
    hari: 'Senin',
    guru_id: []
});

const simpanJadwal = () => {
    form.post(route('admin.kurikulum.piket.simpan_jadwal'), {
        preserveScroll: true,
        onSuccess: () => form.reset('guru_id')
    });
};
</script>

<template>
    <Head title="Pengaturan Jadwal Piket" />

    <DashboardLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <!-- Header -->
            <div class="mb-8">
                <Link :href="route('admin.kurikulum.piket.index')" class="inline-flex items-center text-sm font-medium text-amber-600 hover:text-amber-800 mb-3 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali ke Rekap
                </Link>
                <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    Pengaturan Jadwal Piket
                </h1>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Tentukan guru piket untuk setiap harinya agar menu tugas piket muncul di dashboard mereka.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Form Tambah -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 sticky top-24">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6 border-b border-gray-100 dark:border-gray-700 pb-4">Tambah Guru Piket</h2>
                        
                        <div v-if="$page.props.flash.error" class="mb-4 p-3 bg-red-50 text-red-700 rounded-xl text-sm font-bold flex items-center gap-2">
                            <i class="fas fa-exclamation-triangle"></i> {{ $page.props.flash.error }}
                        </div>

                        <form @submit.prevent="simpanJadwal" class="space-y-4">
                            <div>
                                <label class="block mb-2 text-sm font-bold text-gray-900 dark:text-white">Pilih Hari</label>
                                <select v-model="form.hari" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-amber-500 focus:border-amber-500 block w-full p-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                                    <option value="Senin">Senin</option>
                                    <option value="Selasa">Selasa</option>
                                    <option value="Rabu">Rabu</option>
                                    <option value="Kamis">Kamis</option>
                                    <option value="Jumat">Jumat</option>
                                    <option value="Sabtu">Sabtu</option>
                                    <option value="Minggu">Minggu</option>
                                </select>
                            </div>

                            <div>
                                <label class="block mb-2 text-sm font-bold text-gray-900 dark:text-white">Pilih Guru (Bisa Lebih Dari Satu)</label>
                                <div class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl max-h-60 overflow-y-auto p-3 space-y-2">
                                    <label v-for="g in guru" :key="g.id" class="flex items-center gap-3 p-2 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg cursor-pointer transition-colors">
                                        <input type="checkbox" :value="g.id" v-model="form.guru_id" class="w-4 h-4 text-amber-600 bg-gray-100 border-gray-300 rounded focus:ring-amber-500 dark:focus:ring-amber-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-300">{{ g.nama_lengkap }}</span>
                                    </label>
                                </div>
                            </div>

                            <button type="submit" :disabled="form.processing" class="w-full py-3 px-4 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow-md transition-all focus:ring-4 focus:ring-amber-200 flex justify-center items-center gap-2 mt-6">
                                <i v-if="form.processing" class="fas fa-spinner fa-spin"></i>
                                <i v-else class="fas fa-plus"></i>
                                <span>{{ form.processing ? 'Menyimpan...' : 'Tambahkan ke Jadwal' }}</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Daftar Jadwal -->
                <div class="lg:col-span-2 space-y-6">
                    <div v-for="(jadwalArray, hari) in jadwal_per_hari" :key="hari" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex items-center justify-between">
                            <h3 class="font-black text-gray-900 dark:text-white text-lg flex items-center gap-2">
                                <span class="w-2 h-6 bg-amber-500 rounded-full"></span> Hari {{ hari }}
                            </h3>
                            <span class="px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-bold">{{ jadwalArray.length }} Guru Piket</span>
                        </div>
                        
                        <div class="p-4">
                            <div v-if="jadwalArray.length === 0" class="text-center py-6 text-gray-400 text-sm">
                                Belum ada guru yang dijadwalkan piket pada hari ini.
                            </div>
                            
                            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div v-for="j in jadwalArray" :key="j.id" class="flex items-center justify-between p-4 rounded-xl border border-gray-100 dark:border-gray-700 hover:border-amber-300 dark:hover:border-amber-700 transition-colors group">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-500 flex items-center justify-center font-bold">
                                            {{ j.nama_lengkap.substring(0,2).toUpperCase() }}
                                        </div>
                                        <div class="font-bold text-sm text-gray-900 dark:text-white">{{ j.nama_lengkap }}</div>
                                    </div>
                                    <Link :href="route('admin.kurikulum.piket.hapus_jadwal', j.id)" method="delete" as="button" class="text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity p-2">
                                        <i class="fas fa-trash"></i>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </DashboardLayout>
</template>
