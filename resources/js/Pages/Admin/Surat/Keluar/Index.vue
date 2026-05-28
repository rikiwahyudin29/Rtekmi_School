<script setup>
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    surat: Object,
    templates: Array,
    kelas: Array,
    siswa: Array
});

const form = useForm({
    template_id: '',
    siswa_id: ''
});

const isModalOpen = ref(false);

const filteredSiswa = ref([]);

const onKelasChange = (e) => {
    const kelasId = e.target.value;
    filteredSiswa.value = props.siswa.filter(s => s.kelas_id == kelasId);
};

const submit = () => {
    form.post(route('admin.surat.keluar.store'), {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
        }
    });
};

const deleteSurat = (id) => {
    if (confirm('Yakin ingin menghapus arsip ini?')) {
        router.delete(route('admin.surat.keluar.destroy', id));
    }
};

const selectedIds = ref([]);

const selectAll = computed({
    get: () => props.surat.data.length > 0 && selectedIds.value.length === props.surat.data.length,
    set: (val) => {
        if (val) {
            selectedIds.value = props.surat.data.map(i => i.id);
        } else {
            selectedIds.value = [];
        }
    }
});

const bulkDelete = () => {
    if (confirm(`Yakin ingin menghapus ${selectedIds.value.length} surat keluar yang dipilih?`)) {
        router.post(route('admin.surat.keluar.bulk-destroy'), { ids: selectedIds.value }, {
            onSuccess: () => selectedIds.value = []
        });
    }
};
</script>

<template>
    <Head title="E-Arsip & Surat Keluar" />
    <DashboardLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Surat Keluar & E-Arsip</h2>
                    <p class="text-sm text-gray-500 mt-1">Buat surat keluar secara otomatis menggunakan template.</p>
                </div>
                <div class="flex items-center gap-3">
                    <transition enter-active-class="transition duration-200" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-200" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                        <button v-if="selectedIds.length > 0" @click="bulkDelete" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2.5 px-4 rounded-xl shadow-md flex items-center gap-2">
                            <i class="fas fa-trash-alt"></i> Hapus Terpilih ({{ selectedIds.length }})
                        </button>
                    </transition>
                    <button @click="isModalOpen = true" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2.5 px-4 rounded-xl shadow-lg shadow-primary-600/30 flex items-center gap-2">
                        <i class="fas fa-magic"></i> Generate Surat
                    </button>
                </div>
            </div>

            <!-- Pesan Sukses jika ada di flash, Inertia mengatur ini biasanya di props.flash tapi kita asumsikan lewat toast jika perlu -->

            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="uppercase tracking-wider border-b-2 border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 font-bold">
                            <tr>
                                <th class="px-6 py-4 w-10">
                                    <input type="checkbox" v-model="selectAll" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                </th>
                                <th class="px-6 py-4">No. Surat</th>
                                <th class="px-6 py-4">Tgl Surat</th>
                                <th class="px-6 py-4">Perihal / Tujuan</th>
                                <th class="px-6 py-4 text-center">Validasi QR</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-gray-800 dark:text-gray-200 font-medium">
                            <tr v-for="s in surat.data" :key="s.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4">
                                    <input type="checkbox" v-model="selectedIds" :value="s.id" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                </td>
                                <td class="px-6 py-4 font-bold">{{ s.no_surat }}</td>
                                <td class="px-6 py-4">{{ s.tgl_surat }}</td>
                                <td class="px-6 py-4">
                                    <span class="block text-primary-600 font-bold">{{ s.perihal }}</span>
                                    <span class="block text-xs text-gray-500 mt-0.5"><i class="fas fa-user text-[10px]"></i> {{ s.siswa ? s.siswa.nama_lengkap : 'Umum' }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-bold bg-green-100 text-green-700 border border-green-200">
                                        <i class="fas fa-check-circle mr-1"></i> Disetujui
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right flex justify-end gap-2">
                                    <a :href="route('surat.cetak', s.id)" target="_blank" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors font-bold text-xs flex items-center gap-1.5">
                                        <i class="fas fa-print"></i> Cetak
                                    </a>
                                    <button @click="deleteSurat(s.id)" class="px-3 py-1.5 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 rounded-lg transition-colors font-bold text-xs flex items-center gap-1.5">
                                        <i class="fas fa-trash-alt"></i> Hapus
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!surat.data.length">
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-envelope-open-text text-4xl mb-3 text-gray-300"></i>
                                        <p>Belum ada riwayat surat keluar.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex justify-between items-center" v-if="surat.links && surat.data.length > 0">
                    <p class="text-sm text-gray-500">
                        Menampilkan <span class="font-bold text-gray-900 dark:text-white">{{ surat.from }}</span> sampai <span class="font-bold text-gray-900 dark:text-white">{{ surat.to }}</span> dari <span class="font-bold text-gray-900 dark:text-white">{{ surat.total }}</span> arsip
                    </p>
                    <Pagination :links="surat.links" />
                </div>
            </div>
        </div>

        <!-- Modal Generate Surat -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Buat Surat Otomatis</h3>
                    <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="p-6">
                    <form @submit.prevent="submit" class="space-y-5">
                        
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5">Jenis/Template Surat</label>
                            <select v-model="form.template_id" class="w-full bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none text-gray-800 dark:text-white font-medium" required>
                                <option value="" disabled>-- Pilih Template --</option>
                                <option v-for="t in templates" :key="t.id" :value="t.id">{{ t.nama_template }}</option>
                            </select>
                            <p v-if="!templates.length" class="text-xs text-red-500 mt-1">Belum ada template. Buat di menu Template Surat.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5">Filter Kelas</label>
                            <select @change="onKelasChange" class="w-full bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none text-gray-800 dark:text-white font-medium">
                                <option value="" selected disabled>-- Pilih Kelas --</option>
                                <option v-for="k in kelas" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5">Pilih Siswa</label>
                            <select v-model="form.siswa_id" class="w-full bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none text-gray-800 dark:text-white font-medium" required>
                                <option value="" disabled>-- Pilih Siswa --</option>
                                <option v-for="s in filteredSiswa" :key="s.id" :value="s.id">{{ s.nama_lengkap }} ({{ s.nis }})</option>
                            </select>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <button type="button" @click="isModalOpen = false" class="px-5 py-2.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-200 font-bold hover:bg-gray-50 dark:hover:bg-gray-600">Batal</button>
                            <button type="submit" :disabled="form.processing || !templates.length" class="px-5 py-2.5 bg-primary-600 text-white rounded-xl font-bold hover:bg-primary-700 shadow-lg shadow-primary-600/30 disabled:opacity-50 flex items-center gap-2">
                                <i class="fas fa-magic"></i> Buat Surat
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
