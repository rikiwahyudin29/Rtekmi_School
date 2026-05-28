<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    templates: Object
});

const form = useForm({
    nama_template: '',
    format_nomor: '',
    isi_html: ''
});

const isModalOpen = ref(false);
const editMode = ref(false);
const activeId = ref(null);

const openCreateModal = () => {
    editMode.value = false;
    form.reset();
    isModalOpen.value = true;
};

const openEditModal = (t) => {
    editMode.value = true;
    activeId.value = t.id;
    form.nama_template = t.nama_template;
    form.format_nomor = t.format_nomor;
    form.isi_html = t.isi_html;
    isModalOpen.value = true;
};

const submit = () => {
    if (editMode.value) {
        form.put(route('admin.surat.template.update', activeId.value), {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            }
        });
    } else {
        form.post(route('admin.surat.template.store'), {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            }
        });
    }
};

const deleteTemplate = (id) => {
    if (confirm('Yakin ingin menghapus template ini?')) {
        router.delete(route('admin.surat.template.destroy', id));
    }
};

const selectedIds = ref([]);

const selectAll = computed({
    get: () => props.templates.data.length > 0 && selectedIds.value.length === props.templates.data.length,
    set: (val) => {
        if (val) {
            selectedIds.value = props.templates.data.map(i => i.id);
        } else {
            selectedIds.value = [];
        }
    }
});

const bulkDelete = () => {
    if (confirm(`Yakin ingin menghapus ${selectedIds.value.length} template yang dipilih?`)) {
        router.post(route('admin.surat.template.bulk-destroy'), { ids: selectedIds.value }, {
            onSuccess: () => selectedIds.value = []
        });
    }
};
</script>

<template>
    <Head title="Master Template Surat" />
    <DashboardLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Master Template Surat</h2>
                    <p class="text-sm text-gray-500 mt-1">Kelola template HTML untuk fitur auto-generate surat keluar.</p>
                </div>
                <div class="flex items-center gap-3">
                    <transition enter-active-class="transition duration-200" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-200" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                        <button v-if="selectedIds.length > 0" @click="bulkDelete" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2.5 px-4 rounded-xl shadow-md flex items-center gap-2">
                            <i class="fas fa-trash-alt"></i> Hapus Terpilih ({{ selectedIds.length }})
                        </button>
                    </transition>
                    <button @click="openCreateModal" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2.5 px-4 rounded-xl shadow-lg shadow-primary-600/30 flex items-center gap-2">
                        <i class="fas fa-plus"></i> Tambah Template
                    </button>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="uppercase tracking-wider border-b-2 border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 font-bold">
                            <tr>
                                <th class="px-6 py-4 w-10">
                                    <input type="checkbox" v-model="selectAll" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                </th>
                                <th class="px-6 py-4">Nama Template</th>
                                <th class="px-6 py-4">Format Nomor</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-gray-800 dark:text-gray-200 font-medium">
                            <tr v-for="t in templates.data" :key="t.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4">
                                    <input type="checkbox" v-model="selectedIds" :value="t.id" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                </td>
                                <td class="px-6 py-4">{{ t.nama_template }}</td>
                                <td class="px-6 py-4"><code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-xs">{{ t.format_nomor }}</code></td>
                                <td class="px-6 py-4 text-right flex justify-end gap-2">
                                    <button @click="openEditModal(t)" class="text-blue-500 hover:text-blue-700 px-2">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button @click="deleteTemplate(t.id)" class="text-red-500 hover:text-red-700 px-2">
                                        <i class="fas fa-trash-alt"></i> Hapus
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!templates.data.length">
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-file-alt text-4xl mb-3 text-gray-300"></i>
                                        <p>Belum ada template surat.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex justify-between items-center" v-if="templates.links && templates.data.length > 0">
                    <p class="text-sm text-gray-500">
                        Menampilkan <span class="font-bold text-gray-900 dark:text-white">{{ templates.from }}</span> sampai <span class="font-bold text-gray-900 dark:text-white">{{ templates.to }}</span> dari <span class="font-bold text-gray-900 dark:text-white">{{ templates.total }}</span> template
                    </p>
                    <Pagination :links="templates.links" />
                </div>
            </div>
        </div>

        <!-- Modal Buat Template -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-3xl w-full max-w-4xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ editMode ? 'Edit' : 'Tambah' }} Template Surat</h3>
                    <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="p-6 overflow-y-auto">
                    <form @submit.prevent="submit" class="space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5">Nama Template (Perihal)</label>
                                <input v-model="form.nama_template" type="text" class="w-full bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none text-gray-800 dark:text-white" placeholder="Contoh: Surat Panggilan Orang Tua" required>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5">Format Penomoran</label>
                                <input v-model="form.format_nomor" type="text" class="w-full bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none text-gray-800 dark:text-white" placeholder="02{NO}/SMK/{BLN}/{THN}" required>
                                <p class="text-[11px] text-gray-500 mt-1">Variabel: {NO}, {BLN}, {THN}</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5">Kode HTML Isi Surat</label>
                            <textarea v-model="form.isi_html" rows="12" class="w-full font-mono text-sm bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none text-gray-800 dark:text-white" placeholder="Ketikan kode HTML lengkap di sini..." required></textarea>
                            <p class="text-[11px] text-gray-500 mt-1">
                                <strong>Gunakan variabel berikut (Otomatis diganti sesuai data Siswa):</strong><br>
                                {NAMA}, {NIS}, {KELAS}, {ALAMAT}, {TAHUN_AJARAN}, {HARI_INI}
                            </p>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <button type="button" @click="isModalOpen = false" class="px-5 py-2.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-200 font-bold hover:bg-gray-50 dark:hover:bg-gray-600">Batal</button>
                            <button type="submit" :disabled="form.processing" class="px-5 py-2.5 bg-primary-600 text-white rounded-xl font-bold hover:bg-primary-700 shadow-lg shadow-primary-600/30 disabled:opacity-50">
                                <i class="fas fa-save mr-1.5"></i> Simpan Template
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
