<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    setting: Object,
    flash: Object
});

const form = useForm({
    tgl_pengumuman: props.setting?.tgl_pengumuman || '',
    nomor_surat: props.setting?.nomor_surat || '',
    titimangsa: props.setting?.titimangsa || '',
    pembuka_surat: props.setting?.pembuka_surat || '',
    penutup_surat: props.setting?.penutup_surat || ''
});

const submit = () => {
    form.post(route('admin.kelulusan.setting.simpan'), {
        preserveScroll: true
    });
};
</script>

<template>
    <Head title="Setting Kelulusan & SKL" />

    <DashboardLayout>
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Pengaturan Kelulusan & SKL</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Atur tanggal pengumuman dan format cetak SKL</p>
        </div>

        <div v-if="flash?.success" class="mb-4 p-4 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-xl border border-green-200 dark:border-green-800 flex items-center gap-3">
            <i class="fas fa-check-circle text-xl"></i>
            <span class="font-medium">{{ flash.success }}</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="font-bold text-gray-700 dark:text-gray-300">Form Pengaturan</h3>
                    </div>
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        
                        <!-- Tanggal Pengumuman -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Tanggal & Jam Pengumuman</label>
                            <input v-model="form.tgl_pengumuman" type="datetime-local" class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 focus:border-primary-500 focus:ring-primary-500">
                            <p class="text-xs text-gray-500 mt-1">Siswa hanya bisa melihat status kelulusan dan nilai setelah melewati waktu ini.</p>
                        </div>

                        <!-- Format Nomor Surat -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Format Nomor SKL</label>
                            <input v-model="form.nomor_surat" type="text" class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 focus:border-primary-500 focus:ring-primary-500" placeholder="Contoh: 421.3/[NO_URUT]/SMK/2026">
                            <p class="text-xs text-gray-500 mt-1">Gunakan tag <b>[NO_URUT]</b> untuk men-generate nomor urut secara otomatis.</p>
                        </div>

                        <!-- Paragraf Pembuka -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Paragraf Pembuka SKL</label>
                            <textarea v-model="form.pembuka_surat" rows="3" class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 focus:border-primary-500 focus:ring-primary-500" placeholder="Yang bertanda tangan di bawah ini Kepala Sekolah..."></textarea>
                        </div>

                        <!-- Paragraf Penutup -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Paragraf Penutup SKL</label>
                            <textarea v-model="form.penutup_surat" rows="3" class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 focus:border-primary-500 focus:ring-primary-500" placeholder="Demikian Surat Keterangan Lulus ini dibuat..."></textarea>
                        </div>

                        <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                            <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-sm font-bold transition-colors shadow-lg shadow-primary-500/30 flex items-center gap-2">
                                <i class="fas fa-save" v-if="!form.processing"></i>
                                <i class="fas fa-spinner fa-spin" v-else></i>
                                Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Petunjuk -->
            <div class="lg:col-span-1">
                <div class="bg-blue-50 dark:bg-blue-900/30 rounded-2xl p-6 border border-blue-100 dark:border-blue-800">
                    <h3 class="text-blue-800 dark:text-blue-300 font-bold mb-4 flex items-center gap-2">
                        <i class="fas fa-info-circle text-xl"></i> Petunjuk Pengisian
                    </h3>
                    <ul class="space-y-3 text-sm text-blue-700 dark:text-blue-400">
                        <li class="flex gap-2">
                            <i class="fas fa-check mt-0.5 opacity-50"></i>
                            <span><b>Tanggal Pengumuman</b> menentukan kapan siswa bisa login ke portal mereka untuk melihat Lulus/Tidak.</span>
                        </li>
                        <li class="flex gap-2">
                            <i class="fas fa-check mt-0.5 opacity-50"></i>
                            <span><b>[NO_URUT]</b> akan otomatis di-replace dengan nomor urut siswa (berdasarkan ID) saat SKL dicetak.</span>
                        </li>
                        <li class="flex gap-2">
                            <i class="fas fa-check mt-0.5 opacity-50"></i>
                            <span>Titimangsa (Tanggal TTD Surat) otomatis mengikuti tanggal hari ini saat surat dicetak atau mengambil dari pengaturan sekolah.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
