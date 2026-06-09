<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    temas: Array
});

const isAddModalOpen = ref(false);

const form = useForm({
    nama_tema: '',
    deskripsi: ''
});

const submitTema = () => {
    form.post(route('admin.p5.tema.store'), {
        onSuccess: () => {
            isAddModalOpen.value = false;
            form.reset();
        }
    });
};
</script>

<template>
    <Head title="Manajemen P5" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-seedling text-green-500"></i>
                        Projek Penguatan Profil Pelajar Pancasila (P5)
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Kelola Tema, Projek, dan Dimensi untuk P5 Kurikulum Merdeka.
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('admin.p5.kelompok.index')" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 font-medium shadow-sm flex items-center gap-2">
                        <i class="fas fa-users"></i> Kelompok P5
                    </Link>
                    <button @click="isAddModalOpen = true" class="px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 font-medium shadow-sm flex items-center gap-2">
                        <i class="fas fa-plus"></i> Tambah Tema
                    </button>
                </div>
            </div>

            <!-- Tabel Tema -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                            <tr>
                                <th scope="col" class="px-6 py-4 w-16">No</th>
                                <th scope="col" class="px-6 py-4">Nama Tema</th>
                                <th scope="col" class="px-6 py-4">Deskripsi</th>
                                <th scope="col" class="px-6 py-4 text-center">Jumlah Projek</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(tema, index) in temas" :key="tema.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4">{{ index + 1 }}</td>
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ tema.nama_tema }}</td>
                                <td class="px-6 py-4">{{ tema.deskripsi || '-' }}</td>
                                <td class="px-6 py-4 text-center font-bold text-green-600">
                                    {{ tema.projek ? tema.projek.length : 0 }} Projek
                                </td>
                            </tr>
                            <tr v-if="temas.length === 0">
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">Belum ada tema P5.</td>
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
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <form @submit.prevent="submitTema">
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">Tambah Tema P5 Baru</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Tema</label>
                                    <input v-model="form.nama_tema" type="text" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" placeholder="Contoh: Gaya Hidup Berkelanjutan" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi</label>
                                    <textarea v-model="form.deskripsi" rows="3" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" placeholder="Opsional"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" :disabled="form.processing" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 sm:ml-3 sm:w-auto sm:text-sm">
                                Simpan
                            </button>
                            <button type="button" @click="isAddModalOpen = false" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
