<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    kelas: Object,
    siswa: Array,
    kehadiran: Object
});

const form = useForm({
    input_data: {}
});

if (props.siswa && props.siswa.length > 0) {
    props.siswa.forEach(s => {
        form.input_data[s.id] = {
            sakit: props.kehadiran[s.id] ? props.kehadiran[s.id].sakit : 0,
            izin: props.kehadiran[s.id] ? props.kehadiran[s.id].izin : 0,
            tanpa_keterangan: props.kehadiran[s.id] ? props.kehadiran[s.id].tanpa_keterangan : 0,
        };
    });
}

const submit = () => {
    form.post(route('guru.walikelas.kehadiran.store'), {
        preserveScroll: true
    });
};

const showImportModal = ref(false);
const importForm = useForm({
    file_excel: null
});

const submitImport = () => {
    importForm.post(route('guru.walikelas.kehadiran.import'), {
        preserveScroll: true,
        onSuccess: () => {
            showImportModal.value = false;
            importForm.reset();
            // Update local form data based on imported data
            // Inertia will reload props automatically
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }
    });
};
</script>

<template>
    <Head title="Input Kehadiran Rapor" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-user-clock text-blue-500"></i>
                        Input Kehadiran Rapor
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Kelas: <span class="font-bold">{{ kelas?.nama_kelas || '-' }}</span>
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a :href="route('guru.walikelas.kehadiran.template')" target="_blank" class="px-4 py-2 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800 rounded-xl hover:bg-green-100 dark:hover:bg-green-900/50 transition-colors font-medium flex items-center gap-2">
                        <i class="fas fa-file-excel"></i> Template
                    </a>
                    <button @click="showImportModal = true" class="px-4 py-2 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800 rounded-xl hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors font-medium flex items-center gap-2">
                        <i class="fas fa-upload"></i> Import
                    </button>
                    <Link :href="route('guru.walikelas.index')" class="px-4 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors font-medium">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </Link>
                </div>
            </div>

            <!-- Flash Messages -->
            <div v-if="$page.props.flash.success" class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
                <span class="font-medium">Sukses!</span> {{ $page.props.flash.success }}
            </div>

            <!-- Input Area -->
            <div v-if="siswa && siswa.length > 0" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <form @submit.prevent="submit">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                        <h3 class="font-bold text-gray-900 dark:text-white">Form Kehadiran Siswa</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-300">
                                <tr>
                                    <th scope="col" class="px-6 py-4 w-16">No</th>
                                    <th scope="col" class="px-6 py-4">Siswa</th>
                                    <th scope="col" class="px-6 py-4 w-32 text-center">Sakit (Hari)</th>
                                    <th scope="col" class="px-6 py-4 w-32 text-center">Izin (Hari)</th>
                                    <th scope="col" class="px-6 py-4 w-32 text-center">Tanpa Ket. (Hari)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(s, index) in siswa" :key="s.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-6 py-4">{{ index + 1 }}</td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900 dark:text-white">{{ s.nama_lengkap }}</div>
                                        <div class="text-xs text-gray-500">{{ s.nisn }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <input v-model="form.input_data[s.id].sakit" type="number" min="0" class="w-full text-center rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </td>
                                    <td class="px-6 py-4">
                                        <input v-model="form.input_data[s.id].izin" type="number" min="0" class="w-full text-center rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </td>
                                    <td class="px-6 py-4">
                                        <input v-model="form.input_data[s.id].tanpa_keterangan" type="number" min="0" class="w-full text-center rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="p-6 bg-gray-50 dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                        <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-bold transition-colors shadow-sm flex items-center gap-2">
                            <i class="fas fa-save"></i> 
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Kehadiran' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Import -->
        <div v-if="showImportModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 w-full max-w-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-file-import text-blue-500"></i> Import Kehadiran
                    </h3>
                    <button @click="showImportModal = false" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-6">
                    <form @submit.prevent="submitImport">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pilih File Excel (.xlsx)</label>
                            <input type="file" @input="importForm.file_excel = $event.target.files[0]" accept=".xlsx, .xls" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-xl cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400">
                            <div v-if="importForm.errors.file_excel" class="text-red-500 text-xs mt-1">{{ importForm.errors.file_excel }}</div>
                        </div>
                        <div class="flex justify-end gap-3 mt-6">
                            <button type="button" @click="showImportModal = false" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50">Batal</button>
                            <button type="submit" :disabled="importForm.processing" class="px-4 py-2 text-white bg-blue-600 rounded-xl hover:bg-blue-700 flex items-center gap-2">
                                <i v-if="importForm.processing" class="fas fa-spinner fa-spin"></i>
                                <i v-else class="fas fa-upload"></i>
                                Import Sekarang
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
