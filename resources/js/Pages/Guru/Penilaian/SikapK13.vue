<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { onMounted, watch } from 'vue';

const props = defineProps({
    rombels: Array,
    selected_rombel_id: [String, Number],
    siswa: Array,
    nilai_sikap: Object
});

const form = useForm({
    rombel_id: props.selected_rombel_id,
    data: {}
});

const initForm = () => {
    const initData = {};
    if (props.siswa) {
        props.siswa.forEach(s => {
            const existing = props.nilai_sikap[s.id] || {};
            initData[s.id] = {
                nilai_spiritual: existing.nilai_spiritual || '',
                deskripsi_spiritual: existing.deskripsi_spiritual || '',
                nilai_sosial: existing.nilai_sosial || '',
                deskripsi_sosial: existing.deskripsi_sosial || '',
            };
        });
    }
    form.data = initData;
    form.rombel_id = props.selected_rombel_id;
};

onMounted(() => initForm());
watch(() => props.siswa, () => initForm());

const gantiRombel = (e) => {
    router.get(route('guru.penilaian.sikap_k13'), { rombel_id: e.target.value }, { preserveState: true, preserveScroll: true });
};

const submitSikap = () => {
    form.post(route('guru.penilaian.sikap_k13.store'), { preserveScroll: true });
};

// Excel Import Logic
const isImportModalOpen = ref(false);
const formImport = useForm({
    kelas_id: '',
    file_excel: null,
});

const openImportModal = () => {
    // Cari kelas_id dari rombels berdasarkan form.rombel_id yang dipilih
    const selected = props.rombels.find(r => r.id === form.rombel_id);
    formImport.kelas_id = selected ? selected.id_kelas : '';
    formImport.file_excel = null;
    isImportModalOpen.value = true;
};

const closeImportModal = () => {
    isImportModalOpen.value = false;
};

const submitImport = () => {
    formImport.post(route('guru.penilaian.sikap_k13.import'), {
        preserveScroll: true,
        onSuccess: () => {
            closeImportModal();
            // Trigger refresh data
            router.get(route('guru.penilaian.sikap_k13'), { rombel_id: form.rombel_id }, { preserveState: true, preserveScroll: true });
        }
    });
};

const downloadTemplate = () => {
    if(!form.rombel_id) {
        alert('Pilih Rombongan Belajar terlebih dahulu!');
        return;
    }
    const selected = props.rombels.find(r => r.id === form.rombel_id);
    if(selected) {
        window.location.href = route('guru.penilaian.sikap_k13.template', {
            kelas_id: selected.id_kelas
        });
    }
};
</script>

<template>
    <Head title="Input Nilai Sikap K13" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-heart text-pink-500"></i>
                        Input Nilai Sikap K13
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Input nilai sikap spiritual dan sosial khusus Kurikulum 2013.
                    </p>
                </div>
                <button @click="submitSikap" :disabled="form.processing || !siswa || siswa.length === 0" class="px-6 py-2 bg-pink-600 text-white rounded-xl hover:bg-pink-700 font-medium shadow-sm flex items-center gap-2 transition-colors disabled:opacity-50">
                    <i class="fas fa-save"></i> {{ form.processing ? 'Menyimpan...' : 'Simpan Nilai' }}
                </button>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div class="max-w-md w-full">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pilih Rombongan Belajar</label>
                    <select v-model="form.rombel_id" @change="gantiRombel" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-pink-500 focus:ring-pink-500">
                        <option value="">-- Pilih Rombel --</option>
                        <option v-for="r in rombels" :key="r.id" :value="r.id">{{ r.mapel?.nama_mapel }} - {{ r.kelas?.nama_kelas }}</option>
                    </select>
                </div>
                
                <div v-if="form.rombel_id" class="flex items-center gap-2 w-full md:w-auto">
                    <button type="button" @click="downloadTemplate" class="px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 font-medium transition-colors text-sm flex items-center gap-2 shadow-sm">
                        <i class="fas fa-file-excel"></i> Template
                    </button>
                    <button type="button" @click="openImportModal" class="px-4 py-2 bg-yellow-500 text-white rounded-xl hover:bg-yellow-600 font-medium transition-colors text-sm flex items-center gap-2 shadow-sm">
                        <i class="fas fa-upload"></i> Import
                    </button>
                </div>
            </div>

            <div v-if="siswa && siswa.length > 0" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                            <tr>
                                <th rowspan="2" class="px-4 py-3 border-r dark:border-gray-600">No</th>
                                <th rowspan="2" class="px-4 py-3 border-r dark:border-gray-600 min-w-[200px]">Nama Siswa</th>
                                <th colspan="2" class="px-4 py-2 text-center border-b border-r dark:border-gray-600 bg-blue-50 dark:bg-blue-900/20 text-blue-800 dark:text-blue-400">Sikap Spiritual (KI-1)</th>
                                <th colspan="2" class="px-4 py-2 text-center border-b dark:border-gray-600 bg-pink-50 dark:bg-pink-900/20 text-pink-800 dark:text-pink-400">Sikap Sosial (KI-2)</th>
                            </tr>
                            <tr>
                                <th class="px-4 py-2 text-center border-r dark:border-gray-600 w-24">Nilai</th>
                                <th class="px-4 py-2 text-center border-r dark:border-gray-600">Deskripsi</th>
                                <th class="px-4 py-2 text-center border-r dark:border-gray-600 w-24">Nilai</th>
                                <th class="px-4 py-2 text-center">Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(s, index) in siswa" :key="s.id" class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3 border-r dark:border-gray-700">{{ index + 1 }}</td>
                                <td class="px-4 py-3 border-r dark:border-gray-700 font-medium text-gray-900 dark:text-white">{{ s.nama_lengkap }}</td>
                                
                                <!-- Spiritual -->
                                <td class="px-2 py-2 border-r dark:border-gray-700">
                                    <select v-if="form.data[s.id]" v-model="form.data[s.id].nilai_spiritual" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500 p-1.5">
                                        <option value="">-</option>
                                        <option value="Sangat Baik">Sangat Baik</option>
                                        <option value="Baik">Baik</option>
                                        <option value="Cukup">Cukup</option>
                                        <option value="Kurang">Kurang</option>
                                    </select>
                                </td>
                                <td class="px-2 py-2 border-r dark:border-gray-700">
                                    <textarea v-if="form.data[s.id]" v-model="form.data[s.id].deskripsi_spiritual" rows="2" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500 p-1.5 resize-none" placeholder="Deskripsi spiritual..."></textarea>
                                </td>

                                <!-- Sosial -->
                                <td class="px-2 py-2 border-r dark:border-gray-700">
                                    <select v-if="form.data[s.id]" v-model="form.data[s.id].nilai_sosial" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-pink-500 focus:ring-pink-500 p-1.5">
                                        <option value="">-</option>
                                        <option value="Sangat Baik">Sangat Baik</option>
                                        <option value="Baik">Baik</option>
                                        <option value="Cukup">Cukup</option>
                                        <option value="Kurang">Kurang</option>
                                    </select>
                                </td>
                                <td class="px-2 py-2">
                                    <textarea v-if="form.data[s.id]" v-model="form.data[s.id].deskripsi_sosial" rows="2" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-pink-500 focus:ring-pink-500 p-1.5 resize-none" placeholder="Deskripsi sosial..."></textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div v-else-if="form.rombel_id" class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-2xl p-6 text-center text-yellow-800 dark:text-yellow-400">
                <i class="fas fa-info-circle text-3xl mb-3"></i>
                <p>Belum ada data siswa pada rombongan belajar ini.</p>
            </div>
        </div>

        <!-- Modal Import -->
        <div v-if="isImportModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm transition-opacity">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 w-full max-w-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Import Nilai Sikap K13 (.xlsx)</h3>
                    <button @click="closeImportModal" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form @submit.prevent="submitImport" class="p-6 space-y-4">
                    <div class="bg-pink-50 dark:bg-pink-900/30 text-pink-800 dark:text-pink-300 p-4 rounded-xl text-sm mb-4">
                        <i class="fas fa-info-circle mr-2"></i> Pastikan Anda mengunggah file hasil dari <b>Download Template</b>. Jangan merubah kolom ID SISWA.
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pilih File Excel</label>
                        <input type="file" @input="formImport.file_excel = $event.target.files[0]" accept=".xlsx,.xls" required class="block w-full text-sm text-gray-900 border border-gray-300 rounded-xl cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400">
                    </div>
                    <div class="pt-4 flex justify-end gap-3">
                        <button type="button" @click="closeImportModal" class="px-4 py-2 bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 font-medium transition-colors">
                            Batal
                        </button>
                        <button type="submit" :disabled="formImport.processing" class="px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 font-medium transition-colors flex items-center gap-2">
                            <i v-if="formImport.processing" class="fas fa-spinner fa-spin"></i>
                            <i v-else class="fas fa-upload"></i> Import Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>
