<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { onMounted, watch } from 'vue';

const props = defineProps({
    kelompok: Array,
    tema: Array,
    siswa: Array,
    nilai: Object,
    selected_kelompok_id: [String, Number]
});

const form = useForm({
    kelompok_id: props.selected_kelompok_id,
    data: {}
});

const initForm = () => {
    const initData = {};
    if (props.siswa && props.tema) {
        props.siswa.forEach(s => {
            initData[s.id] = {};
            const existingSiswaNilai = props.nilai[s.id] || {};
            
            props.tema.forEach(t => {
                t.kegiatan.forEach(k => {
                    initData[s.id][k.id] = existingSiswaNilai[k.id] || '';
                });
            });
        });
    }
    form.data = initData;
    form.kelompok_id = props.selected_kelompok_id;
};

onMounted(() => initForm());
watch(() => props.siswa, () => initForm());

const gantiKelompok = (e) => {
    router.get(route('guru.kokurikuler.index'), { kelompok_id: e.target.value }, {
        preserveState: true,
        preserveScroll: true
    });
};

const submitNilai = () => {
    form.post(route('guru.kokurikuler.nilai.store'));
};
</script>

<template>
    <Head title="Input Nilai Kokurikuler" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-layer-group text-teal-500"></i>
                        Input Nilai Kokurikuler
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Input capaian kegiatan kokurikuler untuk kelompok bimbingan Anda.
                    </p>
                </div>
                <button @click="submitNilai" :disabled="form.processing || !siswa || siswa.length === 0" class="px-6 py-2 bg-teal-600 text-white rounded-xl hover:bg-teal-700 font-medium shadow-sm flex items-center gap-2 transition-colors disabled:opacity-50">
                    <i class="fas fa-save"></i> {{ form.processing ? 'Menyimpan...' : 'Simpan Nilai' }}
                </button>
            </div>

            <!-- Filter Card -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <div class="max-w-md w-full">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pilih Kelompok Kokurikuler</label>
                    <select v-model="form.kelompok_id" @change="gantiKelompok" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-teal-500 focus:ring-teal-500">
                        <option value="">-- Pilih Kelompok --</option>
                        <option v-for="k in kelompok" :key="k.id" :value="k.id">{{ k.nama_kelompok }}</option>
                    </select>
                </div>
            </div>

            <div v-if="siswa && siswa.length > 0 && tema && tema.length > 0" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                            <tr>
                                <th rowspan="2" class="px-6 py-4 border-r dark:border-gray-700">No</th>
                                <th rowspan="2" class="px-6 py-4 border-r dark:border-gray-700">Nama Siswa</th>
                                <template v-for="t in tema" :key="'header_'+t.id">
                                    <th v-if="t.kegiatan && t.kegiatan.length > 0" :colspan="t.kegiatan.length" class="px-6 py-2 border-b border-r dark:border-gray-700 text-center bg-teal-50 dark:bg-teal-900/20 text-teal-800 dark:text-teal-400">
                                        {{ t.nama_tema }}
                                    </th>
                                </template>
                            </tr>
                            <tr>
                                <template v-for="t in tema" :key="'sub_'+t.id">
                                    <th v-for="k in t.kegiatan" :key="k.id" class="px-3 py-2 border-r dark:border-gray-700 text-center font-normal text-[10px]" :title="k.deskripsi">
                                        {{ k.nama_kegiatan }}
                                    </th>
                                </template>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(s, index) in siswa" :key="s.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 border-r dark:border-gray-700">{{ index + 1 }}</td>
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white border-r dark:border-gray-700 whitespace-nowrap">{{ s.nama_lengkap }}</td>
                                
                                <template v-for="t in tema" :key="'nilai_'+t.id">
                                    <td v-for="k in t.kegiatan" :key="k.id" class="px-2 py-2 border-r dark:border-gray-700">
                                        <select v-if="form.data[s.id]" v-model="form.data[s.id][k.id]" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-teal-500 focus:border-teal-500 p-1">
                                            <option value="">-</option>
                                            <option value="SB">SB</option>
                                            <option value="B">B</option>
                                            <option value="C">C</option>
                                            <option value="K">K</option>
                                        </select>
                                    </td>
                                </template>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div v-else-if="form.kelompok_id" class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-2xl p-6 text-center text-yellow-800 dark:text-yellow-400">
                <i class="fas fa-info-circle text-3xl mb-3"></i>
                <p>Belum ada data siswa atau tema kegiatan yang disetting oleh admin.</p>
            </div>
        </div>
    </DashboardLayout>
</template>
