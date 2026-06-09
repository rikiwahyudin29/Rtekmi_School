<script setup>
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    kelompok: Array,
    tp_list: Array,
    selected_kelompok_id: [String, Number]
});

const isModalOpen = ref(false);

const form = useForm({
    kelompok_id: props.selected_kelompok_id,
    kode_tp: '',
    deskripsi: ''
});

const gantiKelompok = (e) => {
    router.get(route('guru.pkl.index'), { kelompok_id: e.target.value }, {
        preserveState: true,
        preserveScroll: true
    });
};

const submitTp = () => {
    form.post(route('guru.pkl.tp.store'), {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset('kode_tp', 'deskripsi');
        }
    });
};
</script>

<template>
    <Head title="Pembimbing PKL" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-briefcase text-orange-500"></i>
                        Pembimbing PKL
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Kelola Tujuan Pembelajaran (TP) untuk siswa bimbingan PKL Anda.
                    </p>
                </div>
            </div>

            <!-- Filter Kelompok -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <div class="max-w-md w-full">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pilih Kelompok PKL Bimbingan Anda</label>
                    <select v-model="form.kelompok_id" @change="gantiKelompok" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-orange-500 focus:ring-orange-500">
                        <option value="">-- Pilih Kelompok --</option>
                        <option v-for="k in kelompok" :key="k.id" :value="k.id">{{ k.nama_kelompok }} (DUDI: {{ k.dudi?.nama_dudi }})</option>
                    </select>
                </div>
            </div>

            <!-- Tabel Tujuan Pembelajaran -->
            <div v-if="form.kelompok_id" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-700/50">
                    <h3 class="font-bold text-gray-900 dark:text-white">Daftar Tujuan Pembelajaran (TP) PKL</h3>
                    <button @click="isModalOpen = true" class="px-4 py-2 bg-orange-600 text-white rounded-xl hover:bg-orange-700 font-medium shadow-sm flex items-center gap-2 transition-colors">
                        <i class="fas fa-plus"></i> Tambah TP PKL
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-white dark:bg-gray-800 dark:text-gray-300">
                            <tr>
                                <th scope="col" class="px-6 py-4 w-16">No</th>
                                <th scope="col" class="px-6 py-4 w-32">Kode TP</th>
                                <th scope="col" class="px-6 py-4">Deskripsi Kompetensi DUDI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(tp, index) in tp_list" :key="tp.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4">{{ index + 1 }}</td>
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ tp.kode_tp }}</td>
                                <td class="px-6 py-4">{{ tp.deskripsi }}</td>
                            </tr>
                            <tr v-if="tp_list.length === 0">
                                <td colspan="3" class="px-6 py-8 text-center text-gray-500">Belum ada Tujuan Pembelajaran untuk kelompok ini.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div v-else class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-2xl p-6 text-center text-yellow-800 dark:text-yellow-400">
                <i class="fas fa-info-circle text-3xl mb-3"></i>
                <p>Silakan pilih kelompok PKL terlebih dahulu.</p>
            </div>
        </div>

        <!-- Modal Tambah TP -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl w-full max-w-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-700/50">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Tambah TP PKL</h3>
                    <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form @submit.prevent="submitTp" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kode TP</label>
                        <input v-model="form.kode_tp" type="text" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" required placeholder="Contoh: TP01">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi Kompetensi DUDI</label>
                        <textarea v-model="form.deskripsi" rows="3" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" required placeholder="Contoh: Peserta didik mampu merakit komponen PC dengan benar."></textarea>
                    </div>
                    <div class="pt-4 flex justify-end gap-2">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-orange-600 text-white rounded-xl hover:bg-orange-700 disabled:opacity-50">
                            Simpan TP
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>
