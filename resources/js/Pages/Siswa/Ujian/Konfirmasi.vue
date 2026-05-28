<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { computed } from 'vue';
import dayjs from 'dayjs';

const props = defineProps({
    jadwal: Object,
});

const flash = computed(() => usePage().props.flash);

const form = useForm({
    id_jadwal: props.jadwal.id,
    token: '',
});

const submitForm = () => {
    form.post(route('siswa.ujian.mulai'));
};

const formatDate = (date) => {
    return dayjs(date).format('DD MMM YYYY');
};

const formatTime = (date) => {
    return dayjs(date).format('HH:mm');
};
</script>

<template>
    <Head title="Konfirmasi Ujian (CBT)" />

    <DashboardLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 w-full py-10">
            
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                
                <!-- Header -->
                <div class="bg-gradient-to-r from-primary-600 to-indigo-700 p-8 text-center text-white relative overflow-hidden">
                    <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
                    <h2 class="text-3xl font-extrabold tracking-tight relative z-10 uppercase">Konfirmasi Ujian</h2>
                    <p class="text-primary-100 text-sm mt-2 relative z-10">Mohon periksa detail di bawah ini sebelum memulai.</p>
                </div>

                <div class="p-8">
                    <!-- Detail Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                        
                        <div class="md:col-span-1 flex flex-col items-center justify-center p-6 bg-gray-50 dark:bg-gray-700/50 rounded-2xl border-2 border-dashed border-gray-300 dark:border-gray-600 group hover:border-primary-400 transition-colors">
                            <div class="w-20 h-20 bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400 rounded-full flex items-center justify-center text-3xl mb-4 group-hover:scale-110 transition-transform shadow-sm">
                                <i class="fas fa-book-open"></i>
                            </div>
                            <span class="font-bold text-gray-800 dark:text-white text-lg text-center leading-tight mb-2">
                                {{ jadwal.nama_ujian }}
                            </span>
                            <span class="text-xs font-bold text-primary-700 bg-primary-100 dark:bg-primary-900/30 dark:text-primary-400 px-3 py-1 rounded-full uppercase tracking-wider">
                                {{ jadwal.nama_mapel }}
                            </span>
                        </div>

                        <div class="md:col-span-2 space-y-5 flex flex-col justify-center">
                            <div class="flex items-center justify-between border-b border-dashed border-gray-200 dark:border-gray-700 pb-4">
                                <span class="text-gray-500 dark:text-gray-400 text-sm flex items-center gap-2"><i class="far fa-clock text-amber-500 text-lg w-5"></i> Waktu Mulai</span>
                                <div class="text-right">
                                    <span class="font-bold text-gray-800 dark:text-white block">{{ formatDate(jadwal.waktu_mulai) }}</span>
                                    <span class="text-primary-600 dark:text-primary-400 font-bold text-sm">{{ formatTime(jadwal.waktu_mulai) }} WIB</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between border-b border-dashed border-gray-200 dark:border-gray-700 pb-4">
                                <span class="text-gray-500 dark:text-gray-400 text-sm flex items-center gap-2"><i class="fas fa-hourglass-half text-emerald-500 text-lg w-5"></i> Durasi</span>
                                <span class="font-bold text-gray-800 dark:text-white">{{ jadwal.durasi }} Menit</span>
                            </div>
                            <div class="flex items-center justify-between border-b border-dashed border-gray-200 dark:border-gray-700 pb-4">
                                <span class="text-gray-500 dark:text-gray-400 text-sm flex items-center gap-2"><i class="fas fa-file-alt text-indigo-500 text-lg w-5"></i> Sumber Soal</span>
                                <span class="font-bold text-gray-800 dark:text-white">{{ jadwal.nama_draft }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Peringatan -->
                    <div class="flex p-5 mb-8 text-sm text-amber-800 rounded-2xl bg-amber-50 dark:bg-amber-900/20 dark:text-amber-300 border border-amber-200 dark:border-amber-700 shadow-sm items-start gap-4" role="alert">
                        <i class="fas fa-exclamation-triangle text-2xl mt-1"></i>
                        <div>
                            <span class="font-extrabold block mb-2 text-base">PENTING:</span>
                            <ul class="list-disc list-outside ml-4 space-y-1 text-sm font-medium">
                                <li>Waktu akan langsung berjalan saat tombol <b>MULAI</b> diklik.</li>
                                <li v-if="jadwal.setting_strict == 1">Dilarang keluar dari mode layar penuh (Fullscreen) atau berpindah tab.</li>
                                <li v-if="jadwal.setting_strict == 1">Sistem akan mendeteksi dan mencatat kecurangan secara otomatis.</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Flash Messages -->
                    <div v-if="flash?.error" class="p-4 mb-6 text-sm text-rose-800 rounded-2xl bg-rose-50 dark:bg-rose-900/30 dark:text-rose-300 border border-rose-200 text-center font-bold">
                        <i class="fas fa-exclamation-circle mr-1"></i> {{ flash.error }}
                    </div>

                    <!-- Form Token -->
                    <form @submit.prevent="submitForm">
                        <div v-if="jadwal.setting_token == 1" class="mb-8 bg-gray-50 dark:bg-gray-900/50 p-8 rounded-3xl border border-gray-200 dark:border-gray-700">
                            <label for="token" class="block mb-4 text-sm font-bold text-gray-700 dark:text-gray-300 text-center uppercase tracking-widest">
                                Masukkan Token Ujian
                            </label>
                            <div class="relative max-w-sm mx-auto">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-5 pointer-events-none">
                                    <i class="fas fa-key text-primary-500 text-xl"></i>
                                </div>
                                <input type="text" v-model="form.token" id="token" 
                                    class="bg-white border-2 border-gray-300 text-gray-900 text-3xl rounded-2xl focus:ring-4 focus:ring-primary-500/20 focus:border-primary-500 block w-full pl-14 p-4 font-mono font-bold text-center uppercase tracking-[0.3em] shadow-inner dark:bg-gray-800 dark:text-white dark:border-gray-600 transition-all uppercase-input" 
                                    placeholder="TOKEN" required autocomplete="off" maxlength="10">
                            </div>
                            <p class="mt-4 text-xs font-semibold text-center text-gray-500 dark:text-gray-400">
                                Token dapat diperoleh dari Pengawas Ruangan.
                            </p>
                        </div>
                        
                        <div v-else class="mb-8 p-5 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl text-center text-emerald-700 dark:text-emerald-400 font-bold text-sm shadow-sm">
                            <i class="fas fa-unlock-alt mr-2 text-lg"></i> Ujian ini tidak memerlukan Token pengaman.
                        </div>

                        <div class="grid grid-cols-2 gap-4 max-w-lg mx-auto">
                            <Link :href="route('siswa.ujian.index')" class="py-4 px-5 w-full text-sm font-bold text-gray-700 bg-white rounded-2xl border border-gray-300 hover:bg-gray-50 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 text-center transition-all transform hover:-translate-y-0.5 shadow-sm">
                                <i class="fas fa-arrow-left mr-2"></i> BATAL
                            </Link>
                            
                            <button type="submit" :disabled="form.processing"
                                class="text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:ring-primary-300 font-bold rounded-2xl text-sm px-5 py-4 text-center shadow-lg shadow-primary-500/30 transition-all transform hover:-translate-y-0.5 active:scale-95 disabled:opacity-75 disabled:cursor-not-allowed">
                                <span v-if="form.processing"><i class="fas fa-circle-notch fa-spin mr-2"></i> MEMPROSES</span>
                                <span v-else>MULAI MENGERJAKAN <i class="fas fa-rocket ml-2"></i></span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
            
        </div>
    </DashboardLayout>
</template>

<style scoped>
.uppercase-input {
    text-transform: uppercase;
}
</style>
