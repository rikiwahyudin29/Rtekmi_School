<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    setting: Object
});

const form = useForm({
    ip_dapodik: props.setting.ip_dapodik || '',
    key_integrasi: props.setting.key_integrasi || '',
    npsn: props.setting.npsn || '',
});

const submit = () => {
    form.post(route('admin.master.dapodik.update'), {
        preserveScroll: true
    });
};

const formTest = useForm({});
const testConnection = () => {
    formTest.post(route('admin.master.dapodik.test'), {
        preserveScroll: true
    });
};

const formSync = useForm({});
const syncData = (type) => {
    formSync.post(route('admin.master.dapodik.sync', type), {
        preserveScroll: true
    });
};
</script>

<template>
    <Head title="Integrasi Dapodik" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-sync text-primary-500"></i>
                        Integrasi Dapodik Lokal
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Konfigurasi Web Service dan tarik data dari Dapodik versi Lokal.
                    </p>
                </div>
            </div>

            <!-- Flash Messages -->
            <div v-if="$page.props.flash.success" class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
                <span class="font-medium">Sukses!</span> {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash.error" class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
                <span class="font-medium">Gagal!</span> <span v-html="$page.props.flash.error"></span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Setting Form -->
                <div class="md:col-span-1">
                    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                        <h3 class="font-bold text-gray-900 dark:text-white mb-4">Pengaturan Web Service</h3>
                        <form @submit.prevent="submit" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">IP Dapodik (http://...)</label>
                                <input v-model="form.ip_dapodik" type="text" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Web Service Token</label>
                                <input v-model="form.key_integrasi" type="text" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">NPSN Sekolah</label>
                                <input v-model="form.npsn" type="text" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            
                            <div class="flex items-center gap-3 mt-6">
                                <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium transition-colors">
                                    <i class="fas fa-save mr-1"></i> Simpan
                                </button>
                                <button type="button" @click="testConnection" :disabled="formTest.processing" class="px-4 py-2 bg-teal-600 text-white rounded-xl hover:bg-teal-700 font-medium transition-colors">
                                    <i class="fas fa-wifi mr-1" :class="{'animate-pulse': formTest.processing}"></i> Cek Koneksi
                                </button>
                            </div>
                        </form>

                        <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                            <h4 class="text-sm font-medium text-gray-500 mb-2">Status Koneksi Terakhir:</h4>
                            <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium" 
                                 :class="props.setting.status_koneksi === 'Terhubung' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'">
                                <i class="fas fa-circle text-[8px] mr-2"></i> {{ props.setting.status_koneksi }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sync Actions -->
                <div class="md:col-span-2">
                    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                        <h3 class="font-bold text-gray-900 dark:text-white mb-6">Penarikan Data (Tarik Data Lokal)</h3>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Sync Guru -->
                            <div class="border border-gray-200 dark:border-gray-700 rounded-2xl p-4 flex flex-col justify-between">
                                <div>
                                    <h4 class="font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                                        <i class="fas fa-chalkboard-teacher text-primary-500"></i> Tarik PTK (Guru)
                                    </h4>
                                    <p class="text-xs text-gray-500 mt-2">Update data guru, NIK, dan info GTK.</p>
                                </div>
                                <button @click="syncData('guru')" :disabled="formSync.processing" class="mt-4 w-full py-2 bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-primary-50 dark:hover:bg-primary-900/20 hover:text-primary-600 transition-colors text-sm font-bold border border-gray-200 dark:border-gray-600">
                                    <i class="fas fa-download mr-1"></i> Mulai Sinkronisasi
                                </button>
                            </div>

                            <!-- Sync Rombel & Mapel -->
                            <div class="border border-gray-200 dark:border-gray-700 rounded-2xl p-4 flex flex-col justify-between">
                                <div>
                                    <h4 class="font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                                        <i class="fas fa-layer-group text-blue-500"></i> Tarik Rombel & Mapel
                                    </h4>
                                    <p class="text-xs text-gray-500 mt-2">Update data Kelas, Jurusan, dan Mata Pelajaran.</p>
                                </div>
                                <button @click="syncData('rombel')" :disabled="formSync.processing" class="mt-4 w-full py-2 bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 transition-colors text-sm font-bold border border-gray-200 dark:border-gray-600">
                                    <i class="fas fa-download mr-1"></i> Mulai Sinkronisasi
                                </button>
                            </div>

                            <!-- Sync Siswa -->
                            <div class="border border-gray-200 dark:border-gray-700 rounded-2xl p-4 flex flex-col justify-between sm:col-span-2">
                                <div>
                                    <h4 class="font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                                        <i class="fas fa-user-graduate text-emerald-500"></i> Tarik Peserta Didik (Siswa)
                                    </h4>
                                    <p class="text-xs text-gray-500 mt-2">Update data induk siswa, mutasi, dan pendaftaran ke dalam kelas aktif.</p>
                                </div>
                                <button @click="syncData('siswa')" :disabled="formSync.processing" class="mt-4 w-full py-2 bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-emerald-50 dark:hover:bg-emerald-900/20 hover:text-emerald-600 transition-colors text-sm font-bold border border-gray-200 dark:border-gray-600">
                                    <i class="fas fa-download mr-1"></i> Mulai Sinkronisasi Peserta Didik
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
