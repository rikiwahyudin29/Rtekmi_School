<template>
    <Head title="Tugas Sekolah" />

    <DashboardLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tugas Sekolah
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">Daftar Tugas</h1>
                    <p class="mt-2 text-sm text-gray-600">Kerjakan tugas tepat waktu untuk mendapatkan nilai maksimal.</p>
                </div>

                <div v-if="tugas.length === 0" class="bg-white rounded-xl shadow-sm p-12 text-center border border-gray-100">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-50 text-blue-500 mb-4">
                        <i class="fas fa-tasks text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Belum Ada Tugas</h3>
                    <p class="mt-2 text-sm text-gray-500">Bagus! Belum ada tugas baru untuk Anda saat ini.</p>
                </div>

                <div v-else class="space-y-6">
                    <div v-for="item in tugas" :key="item.id" class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-100 overflow-hidden">
                        <div class="p-6 md:p-8 flex flex-col md:flex-row gap-6">
                            <!-- Info Tugas -->
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-3 mb-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                        {{ item.mapel?.nama_mapel || 'Mapel Umum' }}
                                    </span>
                                    <span v-if="!item.jawaban_saya" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                        Belum Dikerjakan
                                    </span>
                                    <span v-else-if="item.jawaban_saya.status_kumpul === 'Tepat Waktu'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        Dikumpulkan (Tepat Waktu)
                                    </span>
                                    <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">
                                        Dikumpulkan (Terlambat)
                                    </span>
                                </div>
                                
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ item.judul }}</h3>
                                <p class="text-gray-600 text-sm mb-4">{{ item.deskripsi }}</p>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600 mb-4">
                                    <div class="flex items-center bg-gray-50 p-3 rounded-lg">
                                        <i class="fas fa-user-tie text-gray-400 mr-3 text-lg"></i>
                                        <div>
                                            <p class="text-xs text-gray-500 font-semibold">Guru Pengampu</p>
                                            <p class="font-medium text-gray-900">{{ item.guru?.nama_lengkap || '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center bg-rose-50 p-3 rounded-lg border border-rose-100">
                                        <i class="fas fa-clock text-rose-400 mr-3 text-lg"></i>
                                        <div>
                                            <p class="text-xs text-rose-500 font-semibold">Batas Pengumpulan</p>
                                            <p class="font-bold text-rose-700">{{ formatDateTime(item.deadline) }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="item.file_pendukung" class="mb-4">
                                    <a :href="`/uploads/tugas_guru/${item.file_pendukung}`" target="_blank" class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                                        <i class="fas fa-paperclip mr-2"></i> File Pendukung dari Guru
                                    </a>
                                </div>
                            </div>

                            <!-- Area Pengumpulan -->
                            <div class="w-full md:w-1/3 bg-gray-50 rounded-xl p-5 border border-gray-100 flex flex-col justify-center">
                                <div v-if="item.jawaban_saya" class="mb-4">
                                    <h4 class="text-sm font-bold text-gray-900 mb-2">Jawaban Anda:</h4>
                                    <div v-if="item.jawaban_saya.file_jawaban" class="mb-2">
                                        <a :href="`/uploads/tugas_siswa/${item.jawaban_saya.file_jawaban}`" target="_blank" class="text-sm text-blue-600 hover:underline inline-flex items-center">
                                            <i class="fas fa-file-download mr-1"></i> File Jawaban Tersimpan
                                        </a>
                                    </div>
                                    <p class="text-xs text-gray-600 italic bg-white p-2 rounded border border-gray-200">
                                        "{{ item.jawaban_saya.catatan_siswa || 'Tidak ada catatan' }}"
                                    </p>
                                    <div v-if="item.jawaban_saya.nilai !== null" class="mt-3 text-center">
                                        <div class="text-xs text-gray-500 font-semibold mb-1">Nilai:</div>
                                        <div class="text-3xl font-extrabold text-emerald-600">{{ item.jawaban_saya.nilai }}</div>
                                        <p v-if="item.jawaban_saya.komentar_guru" class="text-xs text-emerald-700 mt-1 bg-emerald-50 p-2 rounded">
                                            {{ item.jawaban_saya.komentar_guru }}
                                        </p>
                                    </div>
                                </div>

                                <button @click="openModal(item)" class="w-full inline-flex justify-center items-center px-4 py-3 bg-blue-600 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md shadow-blue-500/30">
                                    <i :class="item.jawaban_saya ? 'fas fa-edit' : 'fas fa-upload'" class="mr-2"></i>
                                    {{ item.jawaban_saya ? (item.jawaban_saya.nilai !== null ? 'Lihat Detail' : 'Perbarui Jawaban') : 'Kumpulkan Tugas' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Kumpul Tugas -->
        <Modal :show="showModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    {{ form.jawaban_saya ? 'Perbarui Jawaban' : 'Kumpulkan Jawaban' }}
                </h2>
                <p class="text-sm text-gray-600 mb-6">
                    Tugas: <span class="font-semibold">{{ selectedTugas?.judul }}</span>
                </p>

                <form @submit.prevent="submit">
                    <div class="mb-4">
                        <InputLabel for="file_jawaban" value="File Jawaban (Opsional jika sudah ada/hanya teks)" />
                        <input id="file_jawaban" type="file" @change="e => form.file_jawaban = e.target.files[0]" class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none" />
                        <InputError class="mt-2" :message="form.errors.file_jawaban" />
                        <p v-if="selectedTugas?.jawaban_saya?.file_jawaban" class="text-xs text-blue-600 mt-1">
                            File saat ini: {{ selectedTugas.jawaban_saya.file_jawaban }}
                        </p>
                    </div>

                    <div class="mb-4">
                        <InputLabel for="catatan_siswa" value="Catatan / Jawaban Teks" />
                        <textarea id="catatan_siswa" v-model="form.catatan_siswa" rows="4" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm" placeholder="Tuliskan jawaban atau catatan untuk guru di sini..."></textarea>
                        <InputError class="mt-2" :message="form.errors.catatan_siswa" />
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <button type="button" @click="closeModal" class="mr-3 text-sm text-gray-600 hover:text-gray-900">Batal</button>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Kirim Jawaban
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

    </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    tugas: Array,
});

const showModal = ref(false);
const selectedTugas = ref(null);

const form = useForm({
    tugas_id: '',
    file_jawaban: null,
    catatan_siswa: '',
});

const formatDateTime = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const openModal = (item) => {
    selectedTugas.value = item;
    form.tugas_id = item.id;
    form.catatan_siswa = item.jawaban_saya ? item.jawaban_saya.catatan_siswa : '';
    form.file_jawaban = null;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    form.clearErrors();
};

const submit = () => {
    form.post(route('siswa.tugas.upload'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
};
</script>
