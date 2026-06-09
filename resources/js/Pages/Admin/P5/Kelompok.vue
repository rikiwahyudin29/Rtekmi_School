<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    kelompok: Array,
    gurus: Array,
    kelas: Array,
    projeks: Array
});

const isAddModalOpen = ref(false);

const form = useForm({
    nama_kelompok: '',
    projek_id: '',
    guru_koordinator_id: '',
    kelas_id_list: []
});

const submitKelompok = () => {
    form.post(route('admin.p5.kelompok.store'), {
        onSuccess: () => {
            isAddModalOpen.value = false;
            form.reset();
        }
    });
};
</script>

<template>
    <Head title="Kelompok P5" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-users text-green-500"></i>
                        Kelompok Projek P5
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Kelola kelompok, koordinator, dan peserta projek P5.
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('admin.p5.index')" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 font-medium shadow-sm flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i> Kembali ke Tema
                    </Link>
                    <button @click="isAddModalOpen = true" class="px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 font-medium shadow-sm flex items-center gap-2">
                        <i class="fas fa-plus"></i> Tambah Kelompok
                    </button>
                </div>
            </div>

            <!-- Tabel Kelompok -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                            <tr>
                                <th scope="col" class="px-6 py-4 w-16">No</th>
                                <th scope="col" class="px-6 py-4">Nama Kelompok</th>
                                <th scope="col" class="px-6 py-4">Projek</th>
                                <th scope="col" class="px-6 py-4">Koordinator (Fasilitator)</th>
                                <th scope="col" class="px-6 py-4">Target Kelas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(k, index) in kelompok" :key="k.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4">{{ index + 1 }}</td>
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ k.nama_kelompok }}</td>
                                <td class="px-6 py-4">{{ k.projek?.nama_projek || '-' }}</td>
                                <td class="px-6 py-4">{{ k.koordinator?.nama_lengkap || '-' }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg text-xs border border-gray-200 dark:border-gray-600">
                                        ID Kelas: {{ k.kelas_id_list }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="kelompok.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">Belum ada kelompok P5.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add Modal -->
        <div v-if="isAddModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="isAddModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full">
                    <form @submit.prevent="submitKelompok">
                        <div class="bg-white dark:bg-gray-800 px-6 pt-6 pb-6">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Tambah Kelompok P5</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Kelompok</label>
                                    <input v-model="form.nama_kelompok" type="text" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" placeholder="Contoh: Kelompok X TKJ 1 - Gaya Hidup" required>
                                </div>
                                
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pilih Projek</label>
                                    <select v-model="form.projek_id" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required>
                                        <option value="">-- Pilih Projek --</option>
                                        <option v-for="p in projeks" :key="p.id" :value="p.id">{{ p.nama_projek }} (Tema: {{ p.tema?.nama_tema }})</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Guru Koordinator</label>
                                    <select v-model="form.guru_koordinator_id" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required>
                                        <option value="">-- Pilih Koordinator --</option>
                                        <option v-for="g in gurus" :key="g.id" :value="g.id">{{ g.nama_lengkap }}</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Target Kelas (Bisa Lebih Dari 1)</label>
                                    <div class="h-32 overflow-y-auto border border-gray-300 rounded-xl p-3 bg-gray-50 dark:bg-gray-700/50">
                                        <div v-for="k in kelas" :key="k.id" class="flex items-center mb-2">
                                            <input type="checkbox" :id="'kelas_'+k.id" :value="k.id" v-model="form.kelas_id_list" class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                            <label :for="'kelas_'+k.id" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">{{ k.nama_kelas }}</label>
                                        </div>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">Pilih kelas mana saja yang masuk ke kelompok ini.</div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 sm:flex sm:flex-row-reverse border-t border-gray-100 dark:border-gray-700">
                            <button type="submit" :disabled="form.processing" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-6 py-2.5 bg-green-600 text-base font-medium text-white hover:bg-green-700 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                                <i class="fas fa-save mr-2"></i> Simpan
                            </button>
                            <button type="button" @click="isAddModalOpen = false" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-6 py-2.5 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
