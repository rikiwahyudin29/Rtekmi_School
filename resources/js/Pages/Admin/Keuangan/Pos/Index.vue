<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    pos: Array,
});

const isModalOpen = ref(false);
const isEditMode = ref(false);

const form = useForm({
    id: null,
    nama_pos: '',
    keterangan: '',
});

const openModal = () => {
    isEditMode.value = false;
    form.reset();
    isModalOpen.value = true;
};

const editData = (data) => {
    isEditMode.value = true;
    form.id = data.id;
    form.nama_pos = data.nama_pos;
    form.keterangan = data.keterangan;
    isModalOpen.value = true;
};

const submit = () => {
    if (isEditMode.value) {
        form.put(`/admin/keuangan/pos/${form.id}`, {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            }
        });
    } else {
        form.post('/admin/keuangan/pos', {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            }
        });
    }
};

const hapus = (id) => {
    if (confirm('Yakin ingin menghapus Pos Pembayaran ini?')) {
        router.delete(`/admin/keuangan/pos/${id}`);
    }
};
</script>

<template>
    <Head title="Pos Pembayaran" />

    <DashboardLayout>
        <div class="flex flex-col h-full">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
                
                <!-- Sticky Header -->
                <div class="sticky top-0 z-20 bg-[#f4f6f8] dark:bg-gray-900 pt-6 pb-4">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                        <div>
                            <h2 class="font-bold text-2xl text-gray-900 dark:text-white tracking-tight">Master Pos Bayar</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kelola nama pos pembayaran seperti SPP, Uang Gedung, dll.</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <button @click="openModal" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2.5 px-5 rounded-xl text-sm transition-all shadow-lg shadow-primary-900/20 flex items-center gap-2">
                                <i class="fas fa-plus"></i> Tambah Pos Bayar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Flash Message -->
                <div v-if="$page.props.flash?.message" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-400 p-4 mb-6 rounded-2xl shadow-sm flex items-center gap-3">
                    <i class="fas fa-check-circle"></i> {{ $page.props.flash.message }}
                </div>
                <div v-if="$page.props.flash?.error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-400 p-4 mb-6 rounded-2xl shadow-sm flex items-center gap-3">
                    <i class="fas fa-exclamation-circle"></i> {{ $page.props.flash.error }}
                </div>

                <!-- Table Card -->
                <div class="bg-white dark:bg-gray-800 shadow-xl shadow-gray-200/50 dark:shadow-none rounded-3xl border border-gray-100 dark:border-gray-700 mb-6 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                                    <th class="px-6 py-5 font-bold w-16 text-center">No</th>
                                    <th class="px-6 py-5 font-bold">Nama Pos Pembayaran</th>
                                    <th class="px-6 py-5 font-bold">Keterangan</th>
                                    <th class="px-6 py-5 font-bold text-center w-32">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-for="(p, index) in pos" :key="p.id" class="hover:bg-gray-50/80 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-6 py-4 text-center text-sm font-medium text-gray-500">{{ index + 1 }}</td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900 dark:text-white text-base">
                                            {{ p.nama_pos }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ p.keterangan || '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button @click="editData(p)" class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 hover:bg-amber-100 dark:bg-amber-900/30 dark:text-amber-400 flex items-center justify-center transition-colors">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                            <button @click="hapus(p.id)" class="w-9 h-9 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 flex items-center justify-center transition-colors">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="pos.length === 0">
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                            <i class="fas fa-box-open text-4xl mb-3 text-gray-300 dark:text-gray-600"></i>
                                            <p class="text-lg font-medium">Belum Ada Data Pos Bayar</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <!-- Form Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800/80">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white flex items-center gap-2">
                        <i :class="isEditMode ? 'fas fa-pen text-amber-500' : 'fas fa-plus text-primary-500'"></i> 
                        {{ isEditMode ? 'Edit Pos Bayar' : 'Tambah Pos Bayar' }}
                    </h3>
                    <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form @submit.prevent="submit" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Pos Pembayaran <span class="text-red-500">*</span></label>
                        <input type="text" v-model="form.nama_pos" required placeholder="Cth: SPP Bulanan" class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <div v-if="form.errors.nama_pos" class="text-red-500 text-xs mt-1">{{ form.errors.nama_pos }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Keterangan</label>
                        <textarea v-model="form.keterangan" rows="3" placeholder="Opsional" class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                        <div v-if="form.errors.keterangan" class="text-red-500 text-xs mt-1">{{ form.errors.keterangan }}</div>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="isModalOpen = false" class="px-5 py-2.5 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-primary-600 hover:bg-primary-700 transition-colors shadow-lg shadow-primary-500/30 disabled:opacity-50">
                            <i class="fas fa-save mr-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>
