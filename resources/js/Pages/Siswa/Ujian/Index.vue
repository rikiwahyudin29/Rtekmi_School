<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { computed } from 'vue';
const props = defineProps({
    ujian: Array,
});

const flash = computed(() => usePage().props.flash);

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
    const d = date.getDate().toString().padStart(2, '0');
    const m = months[date.getMonth()];
    const h = date.getHours().toString().padStart(2, '0');
    const min = date.getMinutes().toString().padStart(2, '0');
    return `${d} ${m} ${h}:${min}`;
};
</script>

<template>
    <Head title="Jadwal Ujian (CBT)" />

    <DashboardLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full py-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Jadwal Ujian</h1>
                    <p class="text-gray-500 text-sm">Daftar ujian yang tersedia untuk Anda.</p>
                </div>
            </div>

            <!-- Flash Messages -->
            <div v-if="flash?.error" class="bg-rose-100 border border-rose-400 text-rose-700 px-4 py-3 rounded-xl relative mb-6 flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-lg"></i> {{ flash.error }}
            </div>
            <div v-if="flash?.message" class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded-xl relative mb-6 flex items-center gap-3">
                <i class="fas fa-check-circle text-lg"></i> {{ flash.message }}
            </div>

            <div v-if="ujian.length === 0" class="bg-white dark:bg-gray-800 p-10 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 text-center">
                <div class="w-24 h-24 bg-gray-100 dark:bg-gray-900 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400 text-4xl">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-700 dark:text-gray-300">Belum Ada Ujian</h3>
                <p class="text-gray-500 text-sm mt-2">Anda tidak memiliki jadwal ujian aktif saat ini.</p>
            </div>
            
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="u in ujian" :key="u.id" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm hover:shadow-xl transition-all border border-gray-100 dark:border-gray-700 overflow-hidden group flex flex-col h-full transform hover:-translate-y-1">
                    
                    <!-- Header -->
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-br from-primary-50 to-white dark:from-gray-750 dark:to-gray-800 relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-4 opacity-10 text-6xl transform rotate-12 transition-transform group-hover:scale-110 group-hover:rotate-6">
                            <i class="fas fa-laptop-code"></i>
                        </div>
                        <span class="text-xs font-bold text-primary-700 bg-primary-100 dark:bg-primary-900/30 dark:text-primary-400 px-3 py-1 rounded-full uppercase tracking-wider relative z-10">{{ u.nama_mapel }}</span>
                        <h3 class="text-xl font-extrabold text-gray-800 dark:text-white mt-3 leading-tight group-hover:text-primary-600 transition-colors relative z-10">{{ u.judul_ujian }}</h3>
                        <p class="text-xs font-medium text-gray-500 mt-2 relative z-10 flex items-center gap-1.5"><i class="fas fa-chalkboard-teacher"></i> {{ u.nama_guru || 'Guru Mapel' }}</p>
                    </div>
                    
                    <!-- Info -->
                    <div class="p-6 space-y-4 flex-1">
                        <div class="flex justify-between items-center text-sm border-b border-dashed border-gray-200 dark:border-gray-700 pb-3">
                            <span class="text-gray-500 dark:text-gray-400 flex items-center gap-2"><i class="far fa-clock text-primary-500"></i> Durasi</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200">{{ u.durasi }} Menit</span>
                        </div>
                        <div class="flex justify-between items-center text-sm border-b border-dashed border-gray-200 dark:border-gray-700 pb-3">
                            <span class="text-gray-500 dark:text-gray-400 flex items-center gap-2"><i class="far fa-calendar-alt text-amber-500"></i> Mulai</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200">{{ formatDate(u.waktu_mulai) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500 dark:text-gray-400 flex items-center gap-2"><i class="fas fa-list-ol text-emerald-500"></i> Sumber</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200 text-xs">{{ u.nama_draft }}</span>
                        </div>
                    </div>

                    <!-- Footer Action -->
                    <div class="p-5 bg-gray-50 dark:bg-gray-850 border-t border-gray-100 dark:border-gray-700 mt-auto">
                        <template v-if="u.status_ujian_text === 'SELESAI'">
                            <div class="text-center">
                                <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-900/30 px-3 py-1 rounded-full mb-3 uppercase tracking-wide">
                                    <i class="fas fa-check-circle"></i> Selesai Mengerjakan
                                </span>
                                
                                <div v-if="u.setting_show_score == 1" class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
                                    <div class="text-4xl font-black text-gray-800 dark:text-white">{{ Number(u.nilai_saya) }}</div>
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1 block">Nilai Akhir</span>
                                </div>
                                <div v-else class="bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-400 px-4 py-3 rounded-2xl text-xs font-bold shadow-sm flex items-center justify-center gap-2">
                                    <i class="fas fa-lock"></i> Menunggu Hasil Ujian
                                </div>
                            </div>
                        </template>

                        <template v-else-if="u.status_ujian_text === 'MENGERJAKAN'">
                            <Link :href="route('siswa.ujian.kerjakan', u.id_sesi)" class="flex w-full items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 text-white font-bold py-3.5 rounded-2xl shadow-lg shadow-primary-500/30 transition-all transform hover:-translate-y-0.5 active:scale-95 text-sm">
                                <i class="fas fa-play-circle"></i> Lanjutkan Ujian
                            </Link>
                        </template>

                        <template v-else-if="u.status_ujian_text === 'TERKUNCI'">
                            <Link :href="route('siswa.ujian.locked', u.id_sesi)" class="flex w-full items-center justify-center gap-2 bg-rose-600 hover:bg-rose-700 text-white font-bold py-3.5 rounded-2xl shadow-lg shadow-rose-500/30 transition-all transform hover:-translate-y-0.5 active:scale-95 text-sm">
                                <i class="fas fa-lock"></i> Ujian Terkunci
                            </Link>
                        </template>

                        <template v-else>
                            <button v-if="u.status_waktu === 'BELUM_MULAI'" disabled class="w-full flex justify-center items-center gap-2 bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400 font-bold py-3.5 rounded-2xl cursor-not-allowed text-sm">
                                <i class="fas fa-clock"></i> Belum Dibuka
                            </button>
                            <button v-else-if="u.status_waktu === 'TERLEWAT'" disabled class="w-full flex justify-center items-center gap-2 bg-rose-100 dark:bg-rose-900/30 text-rose-500 font-bold py-3.5 rounded-2xl cursor-not-allowed text-sm">
                                <i class="fas fa-times-circle"></i> Waktu Habis
                            </button>
                            <Link v-else :href="route('siswa.ujian.konfirmasi', u.id_jadwal)" class="flex w-full justify-center items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3.5 rounded-2xl shadow-lg shadow-emerald-500/30 transition-all transform hover:-translate-y-0.5 active:scale-95 text-sm">
                                <i class="fas fa-rocket"></i> Mulai Ujian Sekarang
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
