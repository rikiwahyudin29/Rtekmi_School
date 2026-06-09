<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    setting: Object
});

const form = useForm({
    npsn: props.setting?.npsn || '',
    ip_dapodik: props.setting?.ip_dapodik || '',
    key_integrasi: props.setting?.key_integrasi || ''
});

const testLoading = ref(false);
const testResult = ref(null);
const syncLoading = ref(false);

const saveSetting = () => {
    form.post(route('admin.master.dapodik.update'), {
        preserveScroll: true
    });
};

const testConnection = async () => {
    testLoading.value = true;
    testResult.value = null;
    
    try {
        const response = await fetch(route('admin.master.dapodik.test'), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        });
        const data = await response.json();
        testResult.value = data;
    } catch (error) {
        testResult.value = { success: false, message: 'Terjadi kesalahan jaringan / server saat menguji koneksi.' };
    } finally {
        testLoading.value = false;
    }
};

const syncData = (type) => {
    if (!confirm(`Apakah Anda yakin ingin menarik data ${type} dari Dapodik? Data lokal yang bentrok mungkin akan diupdate.`)) return;
    
    syncLoading.value = type;
    router.post(route('admin.master.dapodik.sync', type), {}, {
        onFinish: () => syncLoading.value = false
    });
};
</script>

<template>
    <Head title="Sinkronisasi Dapodik" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-server text-blue-500"></i>
                        Web Service Dapodik
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Konfigurasi koneksi ke aplikasi Dapodik lokal sekolah dan tarik data referensi.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Konfigurasi Dapodik -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-4 border-b pb-2">Pengaturan Web Service</h3>
                    
                    <form @submit.prevent="saveSetting" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">NPSN Sekolah</label>
                            <input v-model="form.npsn" type="text" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">IP Aplikasi Dapodik (Lokal/Publik)</label>
                            <input v-model="form.ip_dapodik" type="url" placeholder="http://localhost:5774" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Key Integrasi (Dapodik)</label>
                            <input v-model="form.key_integrasi" type="text" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" required>
                        </div>
                        
                        <div class="pt-4 flex gap-2">
                            <button type="submit" :disabled="form.processing" class="px-6 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium shadow-sm transition-colors flex-1 disabled:opacity-50">
                                <i class="fas fa-save mr-2"></i> Simpan Konfigurasi
                            </button>
                            <button type="button" @click="testConnection" :disabled="testLoading" class="px-6 py-2 bg-gray-600 text-white rounded-xl hover:bg-gray-700 font-medium shadow-sm transition-colors flex-1 disabled:opacity-50">
                                <i class="fas fa-plug mr-2"></i> {{ testLoading ? 'Menguji...' : 'Test Koneksi' }}
                            </button>
                        </div>
                    </form>
                    
                    <div v-if="testResult" class="mt-4 p-4 rounded-xl border" :class="testResult.success ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'">
                        <div class="font-bold mb-1 flex items-center gap-2">
                            <i class="fas" :class="testResult.success ? 'fa-check-circle' : 'fa-times-circle'"></i>
                            {{ testResult.success ? 'Koneksi Berhasil' : 'Koneksi Gagal' }}
                        </div>
                        <p class="text-sm">{{ testResult.message }}</p>
                    </div>
                </div>

                <!-- Aksi Tarik Data -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-4 border-b pb-2">Tarik Data dari Dapodik</h3>
                    
                    <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-xl p-4 mb-4 text-sm text-blue-800 dark:text-blue-400">
                        <i class="fas fa-info-circle mr-1"></i> Pastikan status koneksi Web Service Dapodik berhasil sebelum menarik data.
                    </div>
                    
                    <div class="space-y-3">
                        <button @click="syncData('siswa')" :disabled="syncLoading !== false" class="w-full flex items-center justify-between p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors disabled:opacity-50 group">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <div class="text-left">
                                    <h4 class="font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 transition-colors">Tarik Data Siswa</h4>
                                    <p class="text-xs text-gray-500">Update data diri dan NISN peserta didik.</p>
                                </div>
                            </div>
                            <i v-if="syncLoading === 'siswa'" class="fas fa-circle-notch fa-spin text-indigo-500"></i>
                            <i v-else class="fas fa-download text-gray-400 group-hover:text-indigo-500"></i>
                        </button>
                        
                        <button @click="syncData('guru')" :disabled="syncLoading !== false" class="w-full flex items-center justify-between p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors disabled:opacity-50 group">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                </div>
                                <div class="text-left">
                                    <h4 class="font-bold text-gray-900 dark:text-white group-hover:text-emerald-600 transition-colors">Tarik Data Guru</h4>
                                    <p class="text-xs text-gray-500">Update data GTK.</p>
                                </div>
                            </div>
                            <i v-if="syncLoading === 'guru'" class="fas fa-circle-notch fa-spin text-emerald-500"></i>
                            <i v-else class="fas fa-download text-gray-400 group-hover:text-emerald-500"></i>
                        </button>
                        
                        <button @click="syncData('rombel')" :disabled="syncLoading !== false" class="w-full flex items-center justify-between p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors disabled:opacity-50 group">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="text-left">
                                    <h4 class="font-bold text-gray-900 dark:text-white group-hover:text-purple-600 transition-colors">Tarik Data Rombel & Anggota</h4>
                                    <p class="text-xs text-gray-500">Update kelas dan mapping anggota kelas.</p>
                                </div>
                            </div>
                            <i v-if="syncLoading === 'rombel'" class="fas fa-circle-notch fa-spin text-purple-500"></i>
                            <i v-else class="fas fa-download text-gray-400 group-hover:text-purple-500"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
