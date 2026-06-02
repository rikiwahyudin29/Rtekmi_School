<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    ujian: Object,
    jadwal: Object
});

const form = useForm({
    token: ''
});

const submitToken = () => {
    form.post(`/siswa/cbt/start/${props.jadwal.id}`);
};
</script>

<template>
    <Head title="Konfirmasi Ujian" />

    <DashboardLayout>
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700 flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-primary-100 text-primary-600 rounded-2xl flex items-center justify-center text-2xl mb-4">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Konfirmasi Data Ujian</h2>
                    <p class="text-gray-500 dark:text-gray-400">Pastikan data ujian di bawah ini sudah benar sebelum memulai.</p>
                </div>

                <div class="p-6 md:p-8 bg-gray-50 dark:bg-gray-800/50">
                    <div class="bg-white dark:bg-gray-700 rounded-2xl p-6 border border-gray-200 dark:border-gray-600 shadow-sm mb-6">
                        <table class="w-full text-sm md:text-base text-left text-gray-700 dark:text-gray-300">
                            <tbody>
                                <tr class="border-b border-gray-100 dark:border-gray-600">
                                    <th class="py-3 font-semibold w-1/3">Nama Ujian</th>
                                    <td class="py-3 font-bold text-gray-900 dark:text-white">{{ jadwal.nama_ujian }}</td>
                                </tr>
                                <tr class="border-b border-gray-100 dark:border-gray-600">
                                    <th class="py-3 font-semibold">Mata Pelajaran</th>
                                    <td class="py-3">{{ jadwal.bankSoal?.mapel?.nama_mapel }}</td>
                                </tr>
                                <tr class="border-b border-gray-100 dark:border-gray-600">
                                    <th class="py-3 font-semibold">Durasi Waktu</th>
                                    <td class="py-3"><span class="font-bold text-primary-600">{{ jadwal.durasi }} Menit</span></td>
                                </tr>
                                <tr>
                                    <th class="py-3 font-semibold">Status Anda</th>
                                    <td class="py-3">
                                        <span v-if="ujian.status == 1" class="text-amber-600 font-bold">Sedang Mengerjakan</span>
                                        <span v-else class="text-gray-500 font-bold">Belum Mulai</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="$page.props.flash?.error" class="bg-red-50 border border-red-200 text-red-800 p-4 mb-6 rounded-2xl flex items-center gap-3">
                        <i class="fas fa-times-circle text-lg"></i> {{ $page.props.flash.error }}
                    </div>

                    <form @submit.prevent="submitToken" class="flex flex-col items-center">
                        <div v-if="jadwal.setting_token" class="w-full max-w-sm mb-6">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 text-center">Masukkan Token Ujian</label>
                            <input type="text" v-model="form.token" required placeholder="Contoh: AB12CD" 
                                @input="form.token = form.token.toUpperCase()"
                                class="w-full text-center text-xl font-mono uppercase tracking-widest border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500 shadow-sm p-4">
                        </div>

                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 p-4 rounded-xl text-yellow-800 dark:text-yellow-400 text-sm mb-6 w-full text-center">
                            <i class="fas fa-info-circle mr-1"></i> Jangan memuat ulang (refresh) halaman atau menekan tombol kembali saat ujian sedang berlangsung!
                        </div>

                        <button type="submit" :disabled="form.processing" class="w-full md:w-auto bg-primary-600 hover:bg-primary-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition-all disabled:opacity-50 flex items-center justify-center gap-2 text-lg">
                            <i class="fas fa-play" v-if="!form.processing"></i>
                            <i class="fas fa-spinner fa-spin" v-else></i>
                            {{ ujian.status == 1 ? 'LANJUTKAN UJIAN' : 'MULAI UJIAN SEKARANG' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
