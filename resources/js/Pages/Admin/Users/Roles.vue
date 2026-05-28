<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    roles: Array,
});

const isModalOpen = ref(false);
const editingRole = ref(null);

const form = useForm({
    role_key: '',
    role_name: '',
});

const openModal = (role = null) => {
    editingRole.value = role;
    if (role) {
        form.role_key = role.role_key;
        form.role_name = role.role_name || '';
    } else {
        form.reset();
    }
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    form.clearErrors();
};

const saveRole = () => {
    if (editingRole.value) {
        form.put(route('admin.roles.update', editingRole.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.roles.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteRole = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus role ini?')) {
        router.delete(route('admin.roles.destroy', id));
    }
};
</script>

<template>
    <Head title="Hak Akses & Roles" />

    <DashboardLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-xl text-gray-800 dark:text-gray-100 leading-tight">Manajemen Hak Akses</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kelola peran (roles) untuk seluruh pengguna sistem.</p>
                </div>
                <button @click="openModal()" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2.5 px-5 rounded-xl text-sm transition-all shadow-lg shadow-primary-900/20 flex items-center gap-2">
                    <i class="fas fa-plus"></i> Tambah Role
                </button>
            </div>
        </template>

        <div class="py-6">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700/50 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-bold">No</th>
                                <th scope="col" class="px-6 py-4 font-bold">Nama Role (ID)</th>
                                <th scope="col" class="px-6 py-4 font-bold">Deskripsi Tugas</th>
                                <th scope="col" class="px-6 py-4 font-bold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(role, index) in roles" :key="role.id" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                    {{ index + 1 }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-lg bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold text-xs uppercase">
                                            {{ role.role_key.substring(0, 2) }}
                                        </div>
                                        <span class="font-bold text-gray-900 dark:text-white">{{ role.role_key }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    {{ role.role_name || '-' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openModal(role)" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50 flex items-center justify-center transition-colors" title="Edit Role">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button @click="deleteRole(role.id)" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50 flex items-center justify-center transition-colors" title="Hapus Role">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="roles.length === 0">
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                    Belum ada data role / hak akses.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Role Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white">
                        {{ editingRole ? 'Edit Hak Akses' : 'Tambah Hak Akses Baru' }}
                    </h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="p-6 overflow-y-auto">
                    <form @submit.prevent="saveRole" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ID / Role Key</label>
                            <input v-model="form.role_key" type="text" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="e.g. superadmin, guru, kurikulum" required :disabled="editingRole && ['superadmin', 'admin', 'kepsek', 'guru', 'siswa'].includes(editingRole.role_key)">
                            <p v-if="form.errors.role_key" class="mt-1 text-sm text-red-600">{{ form.errors.role_key }}</p>
                            <p class="text-xs text-gray-500 mt-1">Gunakan huruf kecil tanpa spasi (disarankan).</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Role (Tampilan)</label>
                            <input v-model="form.role_name" type="text" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="e.g. Administrator dengan akses penuh ke seluruh fitur" required>
                            <p v-if="form.errors.role_name" class="mt-1 text-sm text-red-600">{{ form.errors.role_name }}</p>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700 mt-6">
                            <button type="button" @click="closeModal" class="px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium transition-colors">
                                Batal
                            </button>
                            <button type="submit" :disabled="form.processing" class="bg-primary-600 hover:bg-primary-700 text-white px-5 py-2.5 rounded-xl font-medium transition-colors flex items-center gap-2">
                                <i class="fas fa-save" v-if="!form.processing"></i>
                                <i class="fas fa-circle-notch fa-spin" v-else></i>
                                {{ editingRole ? 'Simpan Perubahan' : 'Simpan Role' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
