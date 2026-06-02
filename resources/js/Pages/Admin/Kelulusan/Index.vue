<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    siswa: Array,
    kelas: Array,
    flash: Object
});

const selectedKelas = ref('');
const search = ref('');

const filterKelas = () => {
    router.get(route('admin.kelulusan.index'), { kelas_id: selectedKelas.value }, { preserveState: true });
};

const form = useForm({
    lulus: {}
});

// Initialize form data
props.siswa.forEach(s => {
    form.lulus[s.id] = {
        status: s.kelulusan ? s.kelulusan.status_lulus : 'Pending',
        catatan: s.kelulusan ? s.kelulusan.catatan : ''
    };
});

const filteredSiswa = computed(() => {
    if (!search.value) return props.siswa;
    return props.siswa.filter(s => s.nama_lengkap.toLowerCase().includes(search.value.toLowerCase()) || s.nis.includes(search.value));
});

const submitMassal = () => {
    form.post(route('admin.kelulusan.simpan_massal'), {
        preserveScroll: true
    });
};
</script>

<template>
    <Head title="Manajemen Kelulusan" />

    <DashboardLayout>
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Manajemen Kelulusan</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Tentukan status kelulusan siswa kelas 12</p>
            </div>
            <div class="flex gap-2">
                <select v-model="selectedKelas" @change="filterKelas" class="rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">Semua Kelas 12</option>
                    <option v-for="k in kelas" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                </select>
                <input v-model="search" type="text" placeholder="Cari Siswa..." class="rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500">
            </div>
        </div>

        <div v-if="flash?.success" class="mb-4 p-4 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-xl border border-green-200 dark:border-green-800 flex items-center gap-3">
            <i class="fas fa-check-circle text-xl"></i>
            <span class="font-medium">{{ flash.success }}</span>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                <h3 class="font-bold text-gray-700 dark:text-gray-300">Daftar Siswa</h3>
                <button @click="submitMassal" :disabled="form.processing" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center gap-2">
                    <i class="fas fa-save" v-if="!form.processing"></i>
                    <i class="fas fa-spinner fa-spin" v-else></i>
                    Simpan Perubahan
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-700/50 dark:text-gray-400">
                        <tr>
                            <th class="px-6 py-4 font-medium">No</th>
                            <th class="px-6 py-4 font-medium">NIS / Nama Siswa</th>
                            <th class="px-6 py-4 font-medium">Kelas / Jurusan</th>
                            <th class="px-6 py-4 font-medium">Status Kelulusan</th>
                            <th class="px-6 py-4 font-medium">Catatan / Pesan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <tr v-for="(s, index) in filteredSiswa" :key="s.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-4 text-gray-500">{{ index + 1 }}</td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900 dark:text-white">{{ s.nama_lengkap }}</div>
                                <div class="text-xs text-gray-500">{{ s.nis }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-gray-900 dark:text-white">{{ s.kelas?.nama_kelas }}</div>
                                <div class="text-xs text-gray-500">{{ s.kelas?.jurusan?.nama_jurusan }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <select v-model="form.lulus[s.id].status" :class="{
                                    'text-emerald-600 bg-emerald-50 border-emerald-200': form.lulus[s.id].status === 'Lulus',
                                    'text-amber-600 bg-amber-50 border-amber-200': form.lulus[s.id].status === 'Lulus Bersyarat',
                                    'text-red-600 bg-red-50 border-red-200': form.lulus[s.id].status === 'Tidak Lulus',
                                    'text-orange-600 bg-orange-50 border-orange-200': form.lulus[s.id].status === 'Pending'
                                }" class="w-full md:w-auto rounded-lg text-sm font-bold focus:ring-0">
                                    <option value="Pending" class="text-orange-600">PENDING</option>
                                    <option value="Lulus" class="text-emerald-600">LULUS</option>
                                    <option value="Lulus Bersyarat" class="text-amber-600">LULUS BERSYARAT</option>
                                    <option value="Tidak Lulus" class="text-red-600">TIDAK LULUS</option>
                                </select>
                            </td>
                            <td class="px-6 py-4">
                                <input v-model="form.lulus[s.id].catatan" type="text" placeholder="Catatan opsional..." class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm focus:border-primary-500 focus:ring-primary-500">
                            </td>
                        </tr>
                        <tr v-if="filteredSiswa.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-users-slash text-4xl mb-3 text-gray-300 dark:text-gray-600"></i>
                                    <p>Tidak ada data siswa kelas 12 ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>
