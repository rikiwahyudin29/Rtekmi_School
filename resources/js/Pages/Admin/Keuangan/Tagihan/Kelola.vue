<script setup>
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import axios from 'axios';

const props = defineProps({
    info: Object,
    tagihan: Array,
    kelas: Array,
    filter: [String, Number],
});

const kelasFilter = ref(props.filter || '');
const isGenerateModalOpen = ref(false);

const generateForm = useForm({
    id_jenis_bayar: props.info.id,
    id_kelas: [],
});

const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(number);
};

// Toggle checkbox for generating tagihan
const toggleKelas = (id) => {
    const index = generateForm.id_kelas.indexOf(id);
    if (index === -1) {
        generateForm.id_kelas.push(id);
    } else {
        generateForm.id_kelas.splice(index, 1);
    }
};

const submitGenerate = () => {
    if (generateForm.id_kelas.length === 0) {
        alert('Pilih minimal satu kelas!');
        return;
    }
    
    generateForm.post('/admin/keuangan/tagihan/generate', {
        onSuccess: () => {
            isGenerateModalOpen.value = false;
            generateForm.reset('id_kelas');
        }
    });
};

watch(kelasFilter, (val) => {
    router.get(`/admin/keuangan/tagihan/kelola/${props.info.id}`, { kelas: val }, {
        preserveState: true,
        replace: true,
    });
});

// Inline Editing for Nominal Tagihan
const editNominalId = ref(null);
const editNominalValue = ref('');

const startEditNominal = (t) => {
    editNominalId.value = t.id;
    editNominalValue.value = new Intl.NumberFormat('id-ID').format(t.nominal_tagihan);
};

const saveNominal = async (t) => {
    const rawVal = editNominalValue.value.replace(/\D/g, '');
    try {
        const res = await axios.post('/admin/keuangan/tagihan/update-nominal', {
            pk: t.id,
            value: rawVal
        });
        if (res.data.status === 'success') {
            t.nominal_tagihan = parseInt(rawVal);
            editNominalId.value = null;
        }
    } catch (err) {
        alert(err.response?.data?.message || 'Gagal mengubah nominal');
        editNominalId.value = null;
    }
};

const handleNominalInput = (e) => {
    let value = e.target.value.replace(/\D/g, '');
    if (value) {
        e.target.value = new Intl.NumberFormat('id-ID').format(value);
        editNominalValue.value = e.target.value;
    } else {
        editNominalValue.value = '';
    }
};
</script>

<template>
    <Head title="Kelola Tagihan Siswa" />

    <DashboardLayout>
        <div class="flex flex-col h-full">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
                
                <!-- Sticky Header -->
                <div class="sticky top-0 z-20 bg-[#f4f6f8] dark:bg-gray-900 pt-6 pb-4">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <Link href="/admin/keuangan/jenis" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <i class="fas fa-arrow-left"></i>
                                </Link>
                                <h2 class="font-bold text-2xl text-gray-900 dark:text-white tracking-tight">Kelola Tagihan Siswa</h2>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Pos: <strong>{{ info.pos_bayar?.nama_pos }}</strong> | T.A: <strong>{{ info.tahun_ajaran?.tahun_ajaran }}</strong> | Tipe: <strong>{{ info.tipe_bayar }}</strong>
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <select v-model="kelasFilter" class="border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500 shadow-sm text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2.5 pl-3 pr-8 cursor-pointer">
                                <option value="">Semua Kelas</option>
                                <option v-for="k in kelas" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                            </select>
                            <button @click="isGenerateModalOpen = true" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-5 rounded-xl text-sm transition-all shadow-lg shadow-indigo-900/20 flex items-center gap-2">
                                <i class="fas fa-magic"></i> Generate Tagihan
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Flash Message -->
                <div v-if="$page.props.flash?.message" class="bg-green-50 border border-green-200 text-green-800 p-4 mb-6 rounded-2xl shadow-sm flex items-center gap-3">
                    <i class="fas fa-check-circle"></i> {{ $page.props.flash.message }}
                </div>
                <div v-if="$page.props.flash?.error" class="bg-red-50 border border-red-200 text-red-800 p-4 mb-6 rounded-2xl shadow-sm flex items-center gap-3">
                    <i class="fas fa-exclamation-circle"></i> {{ $page.props.flash.error }}
                </div>

                <!-- Table Card -->
                <div class="bg-white dark:bg-gray-800 shadow-xl shadow-gray-200/50 dark:shadow-none rounded-3xl border border-gray-100 dark:border-gray-700 mb-6 overflow-hidden max-h-[60vh] overflow-y-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 z-10">
                            <tr class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                                <th class="px-6 py-5 font-bold w-16 text-center">No</th>
                                <th class="px-6 py-5 font-bold">Nama Siswa</th>
                                <th class="px-6 py-5 font-bold">Kelas</th>
                                <th class="px-6 py-5 font-bold">Bulan/Keterangan</th>
                                <th class="px-6 py-5 font-bold">Tagihan (Rp)</th>
                                <th class="px-6 py-5 font-bold text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="(t, index) in tagihan" :key="t.id" class="hover:bg-gray-50/80 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 text-center text-sm font-medium text-gray-500">{{ index + 1 }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900 dark:text-white text-base">
                                        {{ t.siswa?.nama_lengkap }}
                                    </div>
                                    <div class="text-xs text-gray-500 font-medium">
                                        NIS: {{ t.siswa?.nis || '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ t.kelas?.nama_kelas || '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ t.keterangan }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <!-- Inline Edit Nominal -->
                                    <div v-if="editNominalId === t.id" class="flex items-center gap-2">
                                        <input type="text" v-model="editNominalValue" @input="handleNominalInput" class="w-32 px-2 py-1 text-sm border rounded focus:ring-primary-500" @keyup.enter="saveNominal(t)" autofocus>
                                        <button @click="saveNominal(t)" class="text-green-600 hover:text-green-700"><i class="fas fa-check"></i></button>
                                        <button @click="editNominalId = null" class="text-red-600 hover:text-red-700"><i class="fas fa-times"></i></button>
                                    </div>
                                    <div v-else class="font-bold text-gray-900 dark:text-white group flex items-center gap-2">
                                        {{ formatRupiah(t.nominal_tagihan) }}
                                        <button @click="startEditNominal(t)" class="text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity hover:text-primary-500">
                                            <i class="fas fa-pen text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold"
                                            :class="{
                                                'bg-red-50 text-red-700 border border-red-200': t.status_bayar === 'BELUM',
                                                'bg-amber-50 text-amber-700 border border-amber-200': t.status_bayar === 'CICIL',
                                                'bg-green-50 text-green-700 border border-green-200': t.status_bayar === 'LUNAS',
                                            }">
                                        {{ t.status_bayar }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="tagihan.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-500">
                                        <i class="fas fa-file-invoice text-4xl mb-3 text-gray-300"></i>
                                        <p class="text-lg font-medium">Belum Ada Tagihan</p>
                                        <p class="text-sm mt-1">Silakan klik tombol "Generate Tagihan" untuk membuat tagihan otomatis.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        <!-- Generate Modal -->
        <div v-if="isGenerateModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="font-bold text-lg text-gray-900 flex items-center gap-2">
                        <i class="fas fa-magic text-indigo-500"></i> Generate Tagihan
                    </h3>
                    <button @click="isGenerateModalOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form @submit.prevent="submitGenerate" class="p-6 space-y-4">
                    <div class="bg-indigo-50 border border-indigo-100 text-indigo-800 p-4 rounded-xl text-sm mb-4">
                        <p>Fitur ini akan men-generate tagihan <strong>{{ info.pos_bayar?.nama_pos }}</strong> untuk siswa di kelas yang dipilih.</p>
                        <p class="mt-1 font-bold">Tarif: {{ formatRupiah(info.nominal_default) }} / {{ info.tipe_bayar }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Kelas <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-3 max-h-48 overflow-y-auto p-2 border border-gray-200 rounded-xl">
                            <label v-for="k in kelas" :key="k.id" class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer border border-transparent hover:border-gray-200 transition-colors">
                                <input type="checkbox" :value="k.id" @change="toggleKelas(k.id)" :checked="generateForm.id_kelas.includes(k.id)" class="rounded text-indigo-600 focus:ring-indigo-500 w-5 h-5">
                                <span class="text-sm font-bold text-gray-700">{{ k.nama_kelas }}</span>
                            </label>
                        </div>
                        <div v-if="generateForm.errors.id_kelas" class="text-red-500 text-xs mt-1">{{ generateForm.errors.id_kelas }}</div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="isGenerateModalOpen = false" class="px-5 py-2.5 rounded-xl text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors">Batal</button>
                        <button type="submit" :disabled="generateForm.processing" class="px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-500/30 disabled:opacity-50">
                            <i class="fas fa-cogs mr-1"></i> Proses Generate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>
