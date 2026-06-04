<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    libur: Array
});

const form = useForm({
    tanggal: '',
    keterangan: ''
});

const submit = () => {
    form.post(route('presensi.hari_libur.store'), {
        onSuccess: () => {
            form.reset();
            alert('Hari libur berhasil ditambahkan!');
        }
    });
};

const hapus = (id) => {
    if (confirm('Hapus Hari Libur? Data yang dihapus tidak bisa dikembalikan!')) {
        router.delete(route('presensi.hari_libur.destroy', id), {
            onSuccess: () => {
                alert('Hari libur telah dihapus.');
            }
        });
    }
};

const syncApi = () => {
    if (confirm('Sinkronisasi hari libur dari API.CO.ID? Tindakan ini akan mengambil data hari libur nasional tahun ini dan menyimpannya ke database.')) {
        router.post(route('presensi.hari_libur.sync'), {}, {
            onSuccess: (page) => {
                if (page.props.flash.success) {
                    alert(page.props.flash.success);
                }
            },
            onError: (errors) => {
                if (errors.error) {
                    alert(errors.error);
                }
            }
        });
    }
};
</script>

<template>
    <DashboardLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                
                <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-1">Pengaturan Hari Libur</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Atur kalender libur sekolah agar mesin presensi otomatis mengabaikan Alpha.</p>
                    </div>
                    <div>
                        <button @click="syncApi" class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-sm transition-all flex items-center gap-2 text-sm">
                            <i class="fas fa-sync"></i> Sync dari Pusat (API.CO.ID)
                        </button>
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row gap-6">
                
                <!-- Form Tambah -->
                <div class="w-full lg:w-1/3">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden rounded-2xl border border-gray-100 dark:border-gray-700">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 border-b border-gray-100 dark:border-gray-700 pb-3 flex items-center gap-2">
                                <i class="fas fa-calendar-plus text-indigo-500"></i> Tambah Hari Libur
                            </h3>
                            <form @submit.prevent="submit" class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Tanggal</label>
                                    <input type="date" v-model="form.tanggal" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Keterangan</label>
                                    <input type="text" v-model="form.keterangan" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required placeholder="Contoh: Libur Nasional">
                                </div>
                                <button type="submit" :disabled="form.processing" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-3 rounded-xl font-bold shadow-sm transition-all flex items-center justify-center gap-2 mt-4 text-sm">
                                    <i class="fas fa-save"></i> Simpan Hari Libur
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Daftar Hari Libur -->
                <div class="w-full lg:w-2/3">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden rounded-2xl border border-gray-100 dark:border-gray-700">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-700 pb-3 flex items-center gap-2">
                                <i class="fas fa-list text-green-500"></i> Daftar Hari Libur
                            </h3>
                            
                            <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-900">
                                        <tr>
                                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-1/3">Tanggal</th>
                                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-1/2">Keterangan</th>
                                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-1/6">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        <tr v-for="item in libur" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 font-bold flex items-center gap-2"><i class="fas fa-calendar-day text-gray-400"></i> {{ item.tanggal }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ item.keterangan }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                                <button @click="hapus(item.id)" class="bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 px-3 py-1.5 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors font-bold inline-flex items-center gap-1">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </td>
                                        </tr>
                                        <tr v-if="libur.length === 0">
                                            <td colspan="3" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">Belum ada hari libur.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
