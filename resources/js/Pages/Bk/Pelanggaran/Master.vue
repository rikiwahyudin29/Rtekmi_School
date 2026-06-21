<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    master: Array
});

const isModalOpen = ref(false);
const isEditing = ref(false);

const form = useForm({
    id: null,
    nama_pelanggaran: '',
    kategori: '',
    poin: ''
});

const openModal = (data = null) => {
    form.reset();
    if (data) {
        isEditing.value = true;
        form.id = data.id;
        form.nama_pelanggaran = data.nama_pelanggaran;
        form.kategori = data.kategori;
        form.poin = data.poin;
    } else {
        isEditing.value = false;
    }
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('bk.master-pelanggaran.update', form.id), {
            onSuccess: () => closeModal()
        });
    } else {
        form.post(route('bk.master-pelanggaran.store'), {
            onSuccess: () => closeModal()
        });
    }
};

const deleteData = (id) => {
    if (confirm('Yakin ingin menghapus jenis pelanggaran ini?')) {
        router.delete(route('bk.master-pelanggaran.destroy', id));
    }
};
</script>

<template>
    <Head title="Master Data Pelanggaran" />

    <DashboardLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-3xl font-black text-gray-800 tracking-tight">Master Jenis Pelanggaran</h2>
                        <p class="text-sm text-gray-500 mt-1 font-medium">Kelola data referensi kasus indisipliner dan bobot poin untuk siswa.</p>
                    </div>
                    <button @click="openModal()" class="px-6 py-3.5 bg-rose-600 hover:bg-rose-700 text-white font-black rounded-2xl shadow-xl shadow-rose-600/20 transition-all flex items-center">
                        <i class="fas fa-plus-circle mr-3 text-lg"></i> Tambah Jenis
                    </button>
                </div>

                <div v-if="$page.props.flash?.message" class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-center text-emerald-800 shadow-sm animate-fade-in-down">
                    <i class="fas fa-check-circle text-emerald-600 mr-3 text-xl"></i>
                    <p class="font-bold">{{ $page.props.flash.message }}</p>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <h3 class="font-black text-gray-800 text-lg">Daftar Jenis Kasus & Poin</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-500 uppercase bg-white border-b border-gray-100 font-bold sticky top-0">
                                <tr>
                                    <th class="px-6 py-4">Nama Pelanggaran</th>
                                    <th class="px-6 py-4">Kategori</th>
                                    <th class="px-6 py-4 text-center">Poin</th>
                                    <th class="px-6 py-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="m in master" :key="m.id" class="hover:bg-gray-50/80 transition-colors">
                                    <td class="px-6 py-4 font-bold text-gray-900">{{ m.nama_pelanggaran }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-lg text-xs font-bold">{{ m.kategori }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="w-10 h-10 rounded-full font-black flex items-center justify-center mx-auto border shadow-sm text-base"
                                             :class="m.poin >= 50 ? 'bg-rose-100 text-rose-700 border-rose-200' : 'bg-amber-50 text-amber-600 border-amber-100'">
                                            {{ m.poin }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button @click="openModal(m)" class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-colors flex items-center justify-center">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button @click="deleteData(m.id)" class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-colors flex items-center justify-center">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="master.length === 0">
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-500 font-medium">Belum ada master data pelanggaran.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm" @click="closeModal"></div>
            <div class="bg-white rounded-3xl w-full max-w-md relative z-10 overflow-hidden shadow-2xl">
                <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-xl font-black text-gray-900"><i class="fas fa-balance-scale text-rose-600 mr-2"></i> {{ isEditing ? 'Edit' : 'Tambah' }} Pelanggaran</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nama Pelanggaran</label>
                        <input type="text" v-model="form.nama_pelanggaran" class="w-full rounded-xl border-gray-200 focus:ring-rose-500 font-medium" placeholder="Contoh: Terlambat Masuk Sekolah">
                        <div v-if="form.errors.nama_pelanggaran" class="text-red-500 text-xs mt-1">{{ form.errors.nama_pelanggaran }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kategori</label>
                        <select v-model="form.kategori" class="w-full rounded-xl border-gray-200 text-sm focus:ring-rose-500">
                            <option value="">-- Pilih Kategori --</option>
                            <option value="Ringan">Ringan</option>
                            <option value="Sedang">Sedang</option>
                            <option value="Berat">Berat</option>
                            <option value="Sangat Berat">Sangat Berat</option>
                        </select>
                        <div v-if="form.errors.kategori" class="text-red-500 text-xs mt-1">{{ form.errors.kategori }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Bobot Poin</label>
                        <input type="number" v-model="form.poin" class="w-full rounded-xl border-gray-200 focus:ring-rose-500 font-black text-xl">
                        <div v-if="form.errors.poin" class="text-red-500 text-xs mt-1">{{ form.errors.poin }}</div>
                    </div>
                </div>
                <div class="p-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                    <button @click="closeModal" class="px-5 py-2.5 rounded-xl text-sm font-bold text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 transition-colors">Batal</button>
                    <button @click="submitForm" class="px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-lg shadow-rose-600/20 disabled:opacity-50 transition-colors" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
