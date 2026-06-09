<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    skkni: Array,
    kuk: Array
});

const isModalOpen = ref(false);

const form = useForm({
    skkni_id: '',
    kode_kuk: '',
    deskripsi_kuk: ''
});

const submitKuk = () => {
    form.post(route('admin.ukk.kuk.store'), {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
        }
    });
};
</script>

<template>
    <Head title="Manajemen KUK - Skill Passport" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-list-check text-indigo-500"></i>
                        Kriteria Unjuk Kerja (KUK)
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Kelola elemen Kriteria Unjuk Kerja (KUK) dari SKKNI untuk buku Skill Passport.
                    </p>
                </div>
                <div class="flex gap-2">
                    <button @click="isModalOpen = true" class="px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 font-medium shadow-sm flex items-center gap-2 transition-colors">
                        <i class="fas fa-plus"></i> Tambah KUK
                    </button>
                </div>
            </div>

            <!-- Tabel KUK -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                            <tr>
                                <th scope="col" class="px-6 py-4 w-16">No</th>
                                <th scope="col" class="px-6 py-4 w-64">Unit Kompetensi (SKKNI)</th>
                                <th scope="col" class="px-6 py-4 w-32">Kode KUK</th>
                                <th scope="col" class="px-6 py-4">Deskripsi KUK</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(k, index) in kuk" :key="k.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4">{{ index + 1 }}</td>
                                <td class="px-6 py-4 font-medium">{{ k.skkni?.judul_unit }}</td>
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ k.kode_kuk }}</td>
                                <td class="px-6 py-4">{{ k.deskripsi_kuk }}</td>
                            </tr>
                            <tr v-if="kuk.length === 0">
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">Belum ada Kriteria Unjuk Kerja.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Tambah KUK -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl w-full max-w-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-700/50">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Tambah KUK</h3>
                    <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form @submit.prevent="submitKuk" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Unit Kompetensi (SKKNI)</label>
                        <select v-model="form.skkni_id" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" required>
                            <option value="">-- Pilih SKKNI --</option>
                            <option v-for="s in skkni" :key="s.id" :value="s.id">{{ s.kode_unit }} - {{ s.judul_unit }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kode KUK</label>
                        <input v-model="form.kode_kuk" type="text" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" required placeholder="Contoh: KUK.01.1">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi Kriteria Unjuk Kerja</label>
                        <textarea v-model="form.deskripsi_kuk" rows="3" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" required placeholder="Jelaskan detail elemen dari KUK tersebut..."></textarea>
                    </div>
                    <div class="pt-4 flex justify-end gap-2">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 disabled:opacity-50">
                            Simpan KUK
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>
