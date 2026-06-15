<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    ekskul: Array,
    filters: Object
});

const search = ref(props.filters.search || '');

watch(search, debounce(function (value) {
    router.get(route('admin.master.ekskul.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
}, 300));

const isModalOpen = ref(false);
const isEdit = ref(false);
const editingId = ref(null);

const form = useForm({
    nama_ekskul: '',
    keterangan: ''
});

const openCreateModal = () => {
    isEdit.value = false;
    editingId.value = null;
    form.reset();
    isModalOpen.value = true;
};

const openEditModal = (item) => {
    isEdit.value = true;
    editingId.value = item.id;
    form.nama_ekskul = item.nama_ekskul;
    form.keterangan = item.keterangan || '';
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submit = () => {
    if (isEdit.value) {
        form.put(route('admin.master.ekskul.update', editingId.value), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.master.ekskul.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const destroy = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus data ekstrakurikuler ini? Data nilai ekskul siswa yang terkait juga bisa terpengaruh.')) {
        router.delete(route('admin.master.ekskul.destroy', id));
    }
};
</script>

<template>
    <Head title="Master Ekstrakurikuler" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-futbol text-primary-500"></i>
                        Master Ekstrakurikuler
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kelola daftar ekstrakurikuler sekolah.</p>
                </div>
                
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <!-- Search -->
                    <div class="relative flex-1 sm:flex-none">
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input v-model="search" type="text" class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white text-sm rounded-xl focus:ring-primary-500 focus:border-primary-500 block w-full ps-10 p-2.5 shadow-sm" placeholder="Cari ekskul...">
                    </div>
                    
                    <button @click="openCreateModal" class="px-4 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium transition-colors flex items-center gap-2 shadow-sm shadow-primary-500/30 whitespace-nowrap">
                        <i class="fas fa-plus"></i> Tambah
                    </button>
                </div>
            </div>

            <!-- Flash Message -->
            <div v-if="$page.props.flash?.success" class="p-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400">
                <span class="font-medium">Sukses!</span> {{ $page.props.flash.success }}
            </div>

            <!-- Table Card -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300 border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-4 w-16 text-center">No</th>
                                <th scope="col" class="px-6 py-4">Nama Ekstrakurikuler</th>
                                <th scope="col" class="px-6 py-4">Keterangan</th>
                                <th scope="col" class="px-6 py-4 w-32 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in ekskul" :key="item.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 text-center">{{ index + 1 }}</td>
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ item.nama_ekskul }}</td>
                                <td class="px-6 py-4">{{ item.keterangan || '-' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openEditModal(item)" class="p-2 text-blue-600 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50 rounded-lg transition-colors" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button @click="destroy(item.id)" class="p-2 text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50 rounded-lg transition-colors" title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="ekskul.length === 0">
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-box-open text-4xl mb-3 text-gray-300 dark:text-gray-600"></i>
                                        <p>Belum ada data ekstrakurikuler.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Form -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl w-full max-w-md overflow-hidden transform transition-all">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        {{ isEdit ? 'Edit Ekstrakurikuler' : 'Tambah Ekstrakurikuler' }}
                    </h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form @submit.prevent="submit" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Ekstrakurikuler <span class="text-red-500">*</span></label>
                        <input v-model="form.nama_ekskul" type="text" required class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary-500 focus:border-primary-500 shadow-sm" placeholder="Contoh: Pramuka">
                        <div v-if="form.errors.nama_ekskul" class="text-red-500 text-xs mt-1">{{ form.errors.nama_ekskul }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Keterangan / Deskripsi Singkat</label>
                        <textarea v-model="form.keterangan" rows="3" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary-500 focus:border-primary-500 shadow-sm" placeholder="Opsional..."></textarea>
                    </div>
                    
                    <div class="pt-4 flex justify-end gap-3 border-t border-gray-100 dark:border-gray-700 mt-6">
                        <button type="button" @click="closeModal" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 font-medium transition-colors">
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium transition-colors disabled:opacity-50 flex items-center gap-2">
                            <i v-if="form.processing" class="fas fa-spinner fa-spin"></i>
                            <i v-else class="fas fa-save"></i>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>
