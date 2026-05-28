<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    tables: Array
});

// Restore Form
const formRestore = useForm({
    file_sql: null
});

const handleFileUpload = (e) => {
    formRestore.file_sql = e.target.files[0];
};

const submitRestore = () => {
    if (!formRestore.file_sql) {
        alert('Pilih file .sql terlebih dahulu!');
        return;
    }
    if (confirm('PERINGATAN! Proses ini akan menimpa seluruh database saat ini. Lanjutkan?')) {
        formRestore.post(route('admin.master.backup.restore'), {
            preserveScroll: true,
            onSuccess: () => {
                document.getElementById('file_sql').value = '';
                formRestore.file_sql = null;
            }
        });
    }
};

// Hapus Data Massal Form
const formHapus = useForm({
    tables: []
});

const selectAll = ref(false);

const toggleSelectAll = () => {
    if (selectAll.value) {
        formHapus.tables = [...props.tables];
    } else {
        formHapus.tables = [];
    }
};

const submitHapus = () => {
    if (formHapus.tables.length === 0) {
        alert('Pilih minimal satu tabel untuk dikosongkan!');
        return;
    }
    if (confirm('PERINGATAN BAHAYA! Tabel yang dipilih akan dikosongkan secara permanen. Lanjutkan?')) {
        formHapus.post(route('admin.master.backup.hapusData'), {
            preserveScroll: true,
            onSuccess: () => {
                selectAll.value = false;
                formHapus.tables = [];
            }
        });
    }
};
</script>

<template>
    <Head title="Manajemen Database" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-database text-red-500"></i>
                    Manajemen Database
                </h2>
                <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">
                    Backup, Restore, atau bersihkan data sistem dengan aman.
                </p>
            </div>

            <!-- Flash Messages -->
            <div v-if="$page.props.flash.success" class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
                <span class="font-medium">Sukses!</span> {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash.error" class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
                <span class="font-medium">Peringatan!</span> {{ $page.props.flash.error }}
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- 1. Backup Data -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <h3 class="font-bold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                        <i class="fas fa-download text-primary-500"></i> 1. Backup Data
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-6">
                        Unduh seluruh isi database saat ini ke dalam format .SQL. Sangat disarankan untuk rutin melakukan backup mingguan.
                    </p>
                    <a :href="route('admin.master.backup.database')" target="_blank" class="block w-full text-center px-4 py-3 bg-[#4c51bf] hover:bg-[#434190] text-white rounded-lg font-bold transition-colors">
                        Download File .SQL
                    </a>
                </div>

                <!-- 2. Restore Data -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <h3 class="font-bold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                        <i class="fas fa-upload text-orange-500"></i> 2. Restore Data
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                        Kembalikan data dari file backup .SQL sebelumnya. (Peringatan: Data yang ada saat ini akan ditimpa!)
                    </p>
                    <form @submit.prevent="submitRestore" class="flex flex-col sm:flex-row gap-3">
                        <input type="file" id="file_sql" accept=".sql" @change="handleFileUpload" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 p-2">
                        <button type="submit" :disabled="formRestore.processing" class="px-6 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg font-bold transition-colors shrink-0">
                            {{ formRestore.processing ? 'Memproses...' : 'Mulai Restore Database' }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- 3. Hapus Data Massal -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-red-200 dark:border-red-900/30 p-6 relative overflow-hidden">
                <i class="fas fa-radiation text-red-50 dark:text-red-900/10 text-9xl absolute -right-4 top-0 pointer-events-none"></i>
                <div class="relative z-10">
                    <h3 class="font-bold text-red-600 dark:text-red-400 mb-2 flex items-center gap-2">
                        <i class="fas fa-trash-alt"></i> 3. Hapus Data Massal (Reset Sistem)
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-6">
                        Fitur ini digunakan untuk mengosongkan tabel (misal: saat kenaikan kelas / ajaran baru). Jangan khawatir! Sistem secara otomatis akan mencegah penghapusan akun yang memiliki hak akses Admin.
                    </p>

                    <form @submit.prevent="submitHapus">
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-900/50 p-4 mb-4">
                            <label class="flex items-center gap-2 mb-4 pb-4 border-b border-gray-200 dark:border-gray-700 cursor-pointer">
                                <input type="checkbox" v-model="selectAll" @change="toggleSelectAll" class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                                <span class="text-sm font-bold text-gray-700 dark:text-gray-300">Pilih Semua Tabel</span>
                            </label>
                            
                            <div class="max-h-64 overflow-y-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2 pr-2">
                                <label v-for="table in tables" :key="table" class="flex items-center gap-2 cursor-pointer hover:bg-white dark:hover:bg-gray-800 p-1.5 rounded transition-colors">
                                    <input type="checkbox" :value="table" v-model="formHapus.tables" class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                                    <span class="text-xs text-gray-600 dark:text-gray-400 truncate" :title="table">{{ table }}</span>
                                </label>
                            </div>
                        </div>

                        <button type="submit" :disabled="formHapus.processing" class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-bold transition-colors flex items-center gap-2">
                            <i class="fas fa-skull-crossbones"></i>
                            {{ formHapus.processing ? 'Sedang Menghapus...' : 'KOSONGKAN TABEL TERPILIH' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
