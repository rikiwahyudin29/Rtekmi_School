<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    materi: Array,
    kelas: Array,
    mapel: Array,
    filters: Object,
});

const form = useForm({
    id: null,
    kelas_id: '',
    mapel_id: '',
    judul: '',
    deskripsi: '',
    link_youtube: '',
    file_materi: null,
});

const isModalOpen = ref(false);
const isEditing = ref(false);

const openAddModal = () => {
    isEditing.value = false;
    form.reset();
    isModalOpen.value = true;
};

const openEditModal = (item) => {
    isEditing.value = true;
    form.id = item.id;
    form.kelas_id = item.kelas_id;
    form.mapel_id = item.mapel_id;
    form.judul = item.judul;
    form.deskripsi = item.deskripsi;
    form.link_youtube = item.link_youtube;
    form.file_materi = null; // Don't bind file input
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submitForm = () => {
    if (isEditing.value) {
        form.post(route('guru.elearning.materi.update', form.id), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('guru.elearning.materi.store'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    }
};

const deleteMateri = (id) => {
    if (confirm('Yakin ingin menghapus materi ini?')) {
        router.delete(route('guru.elearning.materi.destroy', id), {
            preserveScroll: true,
        });
    }
};

const getYoutubeId = (url) => {
    if (!url) return null;
    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
    const match = url.match(regExp);
    return (match && match[2].length === 11) ? match[2] : null;
};
</script>

<template>
    <Head title="Materi Pelajaran" />
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">📖 Materi Pelajaran</h1>
                    <p class="text-sm text-gray-500 mt-1">Kelola materi bahan ajar untuk siswa.</p>
                </div>
                <button @click="openAddModal" class="px-4 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700 transition">
                    <i class="fas fa-plus mr-2"></i> Tambah Materi
                </button>
            </div>

            <!-- List Materi -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="item in materi" :key="item.id" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition">
                    <!-- Youtube Thumbnail / Placeholder -->
                    <div class="h-40 bg-gray-200 relative">
                        <img v-if="getYoutubeId(item.link_youtube)" :src="`https://img.youtube.com/vi/${getYoutubeId(item.link_youtube)}/hqdefault.jpg`" class="w-full h-full object-cover" />
                        <div v-else class="w-full h-full flex flex-col justify-center items-center text-gray-400 bg-indigo-50">
                            <i class="fas fa-book-open text-4xl mb-2"></i>
                            <span class="text-sm font-medium">Bahan Ajar Teks/File</span>
                        </div>
                        <div class="absolute top-3 left-3 bg-white/90 backdrop-blur-sm px-2 py-1 rounded text-xs font-bold text-indigo-700 shadow-sm">
                            {{ item.nama_mapel }}
                        </div>
                        <div class="absolute top-3 right-3 bg-amber-500 text-white px-2 py-1 rounded text-xs font-bold shadow-sm">
                            {{ item.nama_kelas }}
                        </div>
                    </div>
                    
                    <div class="p-5">
                        <h3 class="font-bold text-gray-800 text-lg line-clamp-2 leading-tight" :title="item.judul">{{ item.judul }}</h3>
                        <p class="text-sm text-gray-500 mt-2 line-clamp-2">{{ item.deskripsi || 'Tidak ada deskripsi.' }}</p>
                        
                        <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between">
                            <div class="flex gap-2">
                                <a v-if="item.file_materi" :href="`/uploads/materi/${item.file_materi}`" target="_blank" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100 transition tooltip" title="Download File">
                                    <i class="fas fa-download text-xs"></i>
                                </a>
                                <a v-if="item.link_youtube" :href="item.link_youtube" target="_blank" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-50 text-red-600 hover:bg-red-100 transition tooltip" title="Nonton Video">
                                    <i class="fab fa-youtube text-xs"></i>
                                </a>
                            </div>
                            <div class="flex gap-2">
                                <button @click="openEditModal(item)" class="text-gray-400 hover:text-indigo-600 transition p-1">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button @click="deleteMateri(item.id)" class="text-gray-400 hover:text-rose-600 transition p-1">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="materi.length === 0" class="bg-white rounded-2xl border border-dashed border-gray-300 p-12 text-center">
                <i class="fas fa-folder-open text-4xl text-gray-300 mb-3"></i>
                <h3 class="text-lg font-bold text-gray-500">Belum ada materi</h3>
                <p class="text-gray-400 text-sm mt-1">Silakan tambah materi ajar untuk siswa.</p>
            </div>
        </div>

        <!-- Modal Tambah/Edit -->
        <Modal :show="isModalOpen" @close="closeModal" maxWidth="md">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">{{ isEditing ? 'Edit Materi' : 'Tambah Materi Baru' }}</h2>
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
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mata Pelajaran <span class="text-red-500">*</span></label>
                            <select v-model="form.mapel_id" required class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="" disabled>Pilih Mapel</option>
                                <option v-for="m in mapel" :key="m.id" :value="m.id">{{ m.nama_mapel }}</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Judul Materi <span class="text-red-500">*</span></label>
                        <input type="text" v-model="form.judul" required class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Contoh: Bab 1. Sistem Reproduksi">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Singkat</label>
                        <textarea v-model="form.deskripsi" rows="3" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Jelaskan ringkasan materi ini..."></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Link Youtube (Opsional)</label>
                        <input type="url" v-model="form.link_youtube" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="https://youtube.com/...">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">File Pendukung (PDF/PPT/DOC)</label>
                        <input type="file" @input="form.file_materi = $event.target.files[0]" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-gray-300 rounded-md">
                        <p v-if="isEditing" class="text-xs text-amber-600 mt-1">*Kosongkan jika tidak ingin mengubah file.</p>
                    </div>

                    <div class="flex justify-end pt-4 gap-2">
                        <button type="button" @click="closeModal" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                            {{ isEditing ? 'Simpan Perubahan' : 'Upload Materi' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </DashboardLayout>
</template>
