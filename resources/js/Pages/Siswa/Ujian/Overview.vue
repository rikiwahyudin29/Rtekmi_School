<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { format } from 'date-fns';
import { id } from 'date-fns/locale';

defineProps({
    ujianAktif: Array
});

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return format(new Date(dateString), 'dd MMMM yyyy HH:mm', { locale: id });
};
</script>

<template>
    <Head title="Daftar Ujian CBT" />

    <DashboardLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    <i class="fas fa-laptop-code text-primary-500 mr-2"></i>Daftar Ujian Aktif
                </h2>
                <p class="text-gray-500 dark:text-gray-400 mt-1">
                    Pilih ujian yang sedang berlangsung untuk mulai mengerjakan.
                </p>
            </div>

            <!-- Flash Messages -->
            <div v-if="$page.props.flash?.error" class="bg-red-50 border border-red-200 text-red-800 p-4 mb-6 rounded-2xl flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-lg"></i> {{ $page.props.flash.error }}
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="ujian in ujianAktif" :key="ujian.id" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow">
                    <div class="bg-primary-50 dark:bg-primary-900/20 p-5 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex justify-between items-start mb-2">
                            <span class="px-2.5 py-1 text-xs font-bold bg-primary-100 text-primary-700 dark:bg-primary-800 dark:text-primary-300 rounded-lg">
                                {{ ujian.jadwal?.bankSoal?.mapel?.nama_mapel || 'Ujian' }}
                            </span>
                            <span v-if="ujian.status == 2" class="px-2.5 py-1 text-xs font-bold bg-green-100 text-green-700 rounded-lg">Selesai</span>
                            <span v-else-if="ujian.status == 1" class="px-2.5 py-1 text-xs font-bold bg-amber-100 text-amber-700 rounded-lg">Mengerjakan</span>
                            <span v-else class="px-2.5 py-1 text-xs font-bold bg-gray-100 text-gray-700 rounded-lg">Belum Mulai</span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white leading-tight">
                            {{ ujian.jadwal?.nama_ujian }}
                        </h3>
                    </div>
                    
                    <div class="p-5 space-y-3">
                        <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <i class="far fa-calendar-alt w-5 text-center text-gray-400"></i>
                            <span>Mulai: {{ formatDate(ujian.jadwal?.waktu_mulai) }}</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <i class="far fa-clock w-5 text-center text-gray-400"></i>
                            <span>Selesai: {{ formatDate(ujian.jadwal?.waktu_selesai) }}</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <i class="fas fa-hourglass-half w-5 text-center text-gray-400"></i>
                            <span>Durasi: {{ ujian.jadwal?.durasi }} Menit</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <i class="fas fa-chalkboard-teacher w-5 text-center text-gray-400"></i>
                            <span>Guru: {{ ujian.jadwal?.guru?.nama_lengkap || '-' }}</span>
                        </div>
                    </div>

                    <div class="p-5 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700">
                        <Link v-if="ujian.status == 2" href="#" class="block w-full py-2.5 px-4 text-center text-gray-500 bg-gray-200 rounded-xl font-bold cursor-not-allowed">
                            Ujian Selesai
                        </Link>
                        <Link v-else :href="`/siswa/cbt/confirm/${ujian.jadwal_id}`" class="block w-full py-2.5 px-4 text-center text-white bg-primary-600 hover:bg-primary-700 rounded-xl font-bold transition-colors shadow-sm">
                            {{ ujian.status == 1 ? 'Lanjutkan Ujian' : 'Mulai Ujian' }}
                        </Link>
                    </div>
                </div>

                <div v-if="ujianAktif.length === 0" class="col-span-full py-16 bg-white dark:bg-gray-800 rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 flex flex-col items-center justify-center text-gray-500">
                    <i class="fas fa-mug-hot text-5xl mb-4 text-gray-300"></i>
                    <p class="text-lg font-medium">Belum ada ujian yang aktif saat ini.</p>
                    <p class="text-sm">Silakan kembali lagi nanti atau hubungi guru Anda.</p>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
