<script setup>
import { ref } from 'vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    tugas: Array,
    kelas: Array,
    mapel: Array,
});

const form = useForm({
    kelas_id: '',
    mapel_id: '',
    judul: '',
    deskripsi: '',
    deadline: '',
    file_pendukung: null,
});

const isModalOpen = ref(false);

const openAddModal = () => {
    form.reset();
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submitForm = () => {
    form.post(route('guru.elearning.tugas.store'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
};

const deleteTugas = (id) => {
    if (confirm('Yakin ingin menghapus tugas ini? Semua data pengumpulan siswa juga akan terhapus!')) {
        router.delete(route('guru.elearning.tugas.destroy', id), {
            preserveScroll: true,
        });
    }
};

const isDeadlinePassed = (deadline) => {
    return new Date(deadline) < new Date();
};
</script>

<template>
    <Head title="Manajemen Tugas" />
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex flex-col md:flex-row justify-between md:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">📝 Tugas & Ujian</h1>
                    <p class="text-sm text-gray-500 mt-1">Kelola tugas siswa, berikan nilai, dan pantau pengumpulan.</p>
                </div>
                <button @click="openAddModal" class="px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg shadow hover:bg-indigo-700 transition inline-flex items-center gap-2">
                    <i class="fas fa-plus"></i> Terbitkan Tugas
                </button>
            </div>

            <!-- List Tugas -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div v-for="item in tugas" :key="item.id" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition p-5 flex flex-col">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-md">{{ item.nama_kelas }}</span>
                            <span class="px-2.5 py-1 bg-blue-100 text-blue-800 text-xs font-bold rounded-md">{{ item.nama_mapel }}</span>
                        </div>
                        <span v-if="isDeadlinePassed(item.deadline)" class="px-2.5 py-1 bg-rose-100 text-rose-700 text-xs font-bold rounded-full border border-rose-200">
                            Closed
                        </span>
                        <span v-else class="px-2.5 py-1 bg-emerald-100 text-emerald-700 text-xs font-bold rounded-full border border-emerald-200">
                            Active
                        </span>
                    </div>

                    <h3 class="font-bold text-gray-900 text-xl mb-2">{{ item.judul }}</h3>
                    <p class="text-gray-500 text-sm line-clamp-2 mb-4">{{ item.deskripsi }}</p>

                    <div class="bg-gray-50 rounded-xl p-3 mb-4 flex justify-between items-center text-sm border border-gray-100">
                        <div>
                            <p class="text-gray-500 text-xs font-semibold mb-0.5">Terkumpul</p>
                            <p class="font-bold text-indigo-600">{{ item.total_kumpul }} Siswa</p>
                        </div>
                        <div class="text-right">
                            <p class="text-gray-500 text-xs font-semibold mb-0.5">Deadline</p>
                            <p class="font-bold" :class="isDeadlinePassed(item.deadline) ? 'text-rose-600' : 'text-gray-800'">
                                {{ new Date(item.deadline).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-auto pt-4 border-t border-gray-100 flex items-center justify-between">
                        <div class="flex gap-2">
                            <Link :href="route('guru.elearning.tugas.hasil', item.id)" class="px-4 py-2 bg-indigo-50 text-indigo-700 font-semibold text-sm rounded-lg hover:bg-indigo-100 transition inline-flex items-center gap-2">
                                <i class="fas fa-check-square"></i> Koreksi
                            </Link>
                            <a v-if="item.file_pendukung" :href="`/uploads/tugas/${item.file_pendukung}`" target="_blank" class="px-3 py-2 border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 transition tooltip" title="File Soal">
                                <i class="fas fa-file-download"></i>
                            </a>
                        </div>
                        <button @click="deleteTugas(item.id)" class="text-rose-500 hover:text-rose-700 font-semibold text-sm p-2 transition">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="tugas.length === 0" class="bg-white rounded-2xl border border-dashed border-gray-300 p-16 text-center">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-tasks text-4xl text-gray-300"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-600">Belum ada tugas</h3>
                <p class="text-gray-400 mt-2">Terbitkan tugas pertama Anda untuk siswa.</p>
            </div>
        </div>

        <!-- Modal Tambah -->
        <Modal :show="isModalOpen" @close="closeModal" maxWidth="md">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Terbitkan Tugas Baru</h2>
                <form @submit.prevent="submitForm" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kelas <span class="text-red-500">*</span></label>
                            <select v-model="form.kelas_id" required class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="" disabled>Pilih Kelas</option>
                                <option v-for="k in kelas" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mapel <span class="text-red-500">*</span></label>
                            <select v-model="form.mapel_id" required class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="" disabled>Pilih Mapel</option>
                                <option v-for="m in mapel" :key="m.id" :value="m.id">{{ m.nama_mapel }}</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Judul Tugas <span class="text-red-500">*</span></label>
                        <input type="text" v-model="form.judul" required class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Contoh: Latihan Soal Bab 1">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Instruksi / Deskripsi Tugas</label>
                        <textarea v-model="form.deskripsi" rows="3" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Jelaskan apa yang harus dikerjakan siswa..."></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Batas Pengumpulan (Deadline) <span class="text-red-500">*</span></label>
                        <input type="datetime-local" v-model="form.deadline" required class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">File Soal / Pendukung (Opsional)</label>
                        <input type="file" @input="form.file_pendukung = $event.target.files[0]" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-gray-300 rounded-md">
                    </div>

                    <div class="flex justify-end pt-4 gap-2 border-t">
                        <button type="button" @click="closeModal" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition font-semibold">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition font-semibold">
                            Terbitkan Tugas
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </DashboardLayout>
</template>
