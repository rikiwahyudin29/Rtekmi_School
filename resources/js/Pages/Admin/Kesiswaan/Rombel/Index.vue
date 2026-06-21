<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

defineProps({
    kelas: Array
});
</script>

<template>
    <Head title="Manajemen Rombel" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-chalkboard text-emerald-500"></i>
                        Manajemen Rombel & Kenaikan Kelas
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Kelola data siswa per kelas, perpindahan kelas, dan proses kelulusan.
                    </p>
                </div>
            </div>

            <!-- Pesan Sukses/Error -->
            <div v-if="$page.props.flash.message" class="p-4 mb-4 text-sm text-emerald-800 rounded-lg bg-emerald-50 dark:bg-gray-800 dark:text-emerald-400 ring-1 ring-emerald-200 dark:ring-emerald-800" role="alert">
                <span class="font-medium">Berhasil!</span> {{ $page.props.flash.message }}
            </div>
            <div v-if="$page.props.flash.error" class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 ring-1 ring-red-200 dark:ring-red-800" role="alert">
                <span class="font-medium">Gagal!</span> {{ $page.props.flash.error }}
            </div>

            <!-- Grid Kelas -->
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <div v-for="k in kelas" :key="k.id" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow">
                    <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
                        <h3 class="font-bold text-xl text-gray-800 dark:text-white flex items-center gap-2">
                            <i class="fas fa-door-open text-emerald-500"></i>
                            {{ k.nama_kelas }}
                        </h3>
                    </div>
                    <div class="p-5">
                        <div class="flex items-center gap-4 mb-5">
                            <div class="w-12 h-12 rounded-full bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-500">
                                <i class="fas fa-users text-xl"></i>
                            </div>
                            <div>
                                <div class="text-3xl font-black text-gray-800 dark:text-white">{{ k.total_siswa }}</div>
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">Siswa Aktif</div>
                            </div>
                        </div>
                        <Link :href="route('admin.kesiswaan.rombel.atur', k.id)" class="w-full flex justify-center items-center gap-2 px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-sm rounded-xl transition-colors">
                            <i class="fas fa-cog"></i> Atur Rombel
                        </Link>
                    </div>
                </div>

                <div v-if="kelas.length === 0" class="col-span-full py-12 text-center bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 mb-4">
                        <i class="fas fa-school text-2xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Belum ada data kelas</h3>
                    <p class="text-gray-500">Silakan tambahkan data kelas di menu Master Data terlebih dahulu.</p>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
