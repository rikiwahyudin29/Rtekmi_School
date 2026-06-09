<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    tp: Array,
    mapel: Array,
});

const isModalOpen = ref(false);

const form = useForm({
    mapel_id: '',
    kode_tp: '',
    deskripsi: '',
});

const openModal = () => {
    isModalOpen.value = true;
    form.reset();
};

const closeModal = () => {
    isModalOpen.value = false;
};

const submit = () => {
    form.post(route('guru.penilaian.tp.store'), {
        preserveScroll: true,
        onSuccess: () => closeModal()
    });
};

const deleteTp = (id) => {
    if (confirm('Yakin ingin menghapus Tujuan Pembelajaran ini?')) {
        useForm({}).delete(route('guru.penilaian.tp.destroy', id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Tujuan Pembelajaran" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-bullseye text-primary-500"></i>
                        Tujuan Pembelajaran (TP)
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Kelola Tujuan Pembelajaran untuk penilaian Kurikulum Merdeka
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('guru.penilaian.index')" class="px-4 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors font-medium">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </Link>
                    <button @click="openModal" class="px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium transition-colors shadow-sm shadow-primary-500/30">
                        <i class="fas fa-plus mr-2"></i> Tambah TP
                    </button>
                </div>
            </div>

            <!-- Flash Messages -->
            <div v-if="$page.props.flash.success" class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
                <span class="font-medium">Sukses!</span> {{ $page.props.flash.success }}
            </div>

            <!-- Data Table -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300 border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-4">No</th>
                                <th scope="col" class="px-6 py-4">Mata Pelajaran</th>
                                <th scope="col" class="px-6 py-4">Kode TP</th>
                                <th scope="col" class="px-6 py-4">Deskripsi</th>
                                <th scope="col" class="px-6 py-4">Fase/Tingkat</th>
                                <th scope="col" class="px-6 py-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in tp" :key="item.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4">{{ index + 1 }}</td>
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ item.mapel?.nama_mapel }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400 rounded-lg">
                                        {{ item.kode_tp }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">{{ item.deskripsi }}</td>
                                <td class="px-6 py-4">{{ item.tingkat }} (Sem {{ item.semester }})</td>
                                <td class="px-6 py-4">
                                    <button @click="deleteTp(item.id)" class="text-red-500 hover:text-red-700 p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="tp.length === 0">
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                    Belum ada Tujuan Pembelajaran yang ditambahkan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add TP Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm transition-opacity">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 w-full max-w-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Tambah Tujuan Pembelajaran</h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form @submit.prevent="submit" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mata Pelajaran</label>
                        <select v-model="form.mapel_id" required class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            <option v-for="m in mapel" :key="m.id" :value="m.id">{{ m.nama_mapel }}</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kode TP</label>
                        <input v-model="form.kode_tp" type="text" placeholder="Contoh: TP.1" required class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi Tujuan Pembelajaran</label>
                        <textarea v-model="form.deskripsi" rows="3" placeholder="Contoh: Peserta didik mampu mengidentifikasi komponen..." required class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                        <p class="text-xs text-gray-500 mt-1">Gunakan kata kerja operasional dan hindari kalimat yang terlalu panjang agar rapi saat dicetak di rapor.</p>
                    </div>
                    
                    <div class="pt-4 flex justify-end gap-3">
                        <button type="button" @click="closeModal" class="px-4 py-2 bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 font-medium transition-colors">
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium transition-colors">
                            <i class="fas fa-save mr-1"></i> Simpan TP
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </DashboardLayout>
</template>
