<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    kelompok: Array,
    guru: Array,
    dudi: Array
});

const isModalOpen = ref(false);

const form = useForm({
    nama_kelompok: '',
    guru_id: '',
    dudi_id: '',
    tgl_mulai: '',
    tgl_selesai: ''
});

const submitKelompok = () => {
    form.post(route('admin.pkl.kelompok.store'), {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
        }
    });
};
</script>

<template>
    <Head title="Manajemen Kelompok PKL" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-users-cog text-blue-500"></i>
                        Kelompok PKL & Pembimbing
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Kelola kelompok Praktik Kerja Lapangan (PKL), Mitra DUDI, dan Guru Pembimbing.
                    </p>
                </div>
                <button @click="isModalOpen = true" class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium shadow-sm flex items-center gap-2 transition-colors">
                    <i class="fas fa-plus"></i> Tambah Kelompok
                </button>
            </div>

            <!-- Tabel Kelompok -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                            <tr>
                                <th scope="col" class="px-6 py-4 w-16">No</th>
                                <th scope="col" class="px-6 py-4">Nama Kelompok</th>
                                <th scope="col" class="px-6 py-4">Mitra DUDI</th>
                                <th scope="col" class="px-6 py-4">Guru Pembimbing</th>
                                <th scope="col" class="px-6 py-4">Periode Pelaksanaan</th>
                                <th scope="col" class="px-6 py-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(k, index) in kelompok" :key="k.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4">{{ index + 1 }}</td>
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ k.nama_kelompok }}</td>
                                <td class="px-6 py-4">{{ k.dudi?.nama_dudi }}</td>
                                <td class="px-6 py-4">{{ k.guru?.nama_lengkap }}</td>
                                <td class="px-6 py-4">
                                    <div class="text-xs text-gray-500 font-mono">{{ k.tgl_mulai }} s/d {{ k.tgl_selesai }}</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <!-- Aksi Edit/Delete belum aktif untuk simplifikasi -->
                                    <button class="text-blue-600 hover:text-blue-900 mx-1"><i class="fas fa-edit"></i></button>
                                </td>
                            </tr>
                            <tr v-if="kelompok.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">Belum ada data Kelompok PKL.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Tambah Kelompok -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl w-full max-w-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-700/50">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Tambah Kelompok PKL</h3>
                    <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form @submit.prevent="submitKelompok" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Kelompok / Periode</label>
                        <input v-model="form.nama_kelompok" type="text" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" required placeholder="Contoh: Gelombang 1 2024">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mitra DUDI</label>
                        <select v-model="form.dudi_id" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" required>
                            <option value="">-- Pilih DUDI --</option>
                            <option v-for="d in dudi" :key="d.id" :value="d.id">{{ d.nama_dudi }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Guru Pembimbing PKL</label>
                        <select v-model="form.guru_id" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" required>
                            <option value="">-- Pilih Guru Pembimbing --</option>
                            <option v-for="g in guru" :key="g.id" :value="g.id">{{ g.nama_lengkap }}</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Mulai</label>
                            <input v-model="form.tgl_mulai" type="date" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Selesai</label>
                            <input v-model="form.tgl_selesai" type="date" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" required>
                        </div>
                    </div>
                    <div class="pt-4 flex justify-end gap-2">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 disabled:opacity-50">
                            Simpan Kelompok
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>
