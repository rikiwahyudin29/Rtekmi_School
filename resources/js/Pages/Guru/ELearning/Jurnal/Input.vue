<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    jadwal: Array,
    ta_aktif: Object,
    hari_ini: String,
    tanggal: String,
});

const isModalOpen = ref(false);
const selectedJadwal = ref(null);

const form = useForm({
    id_kelas: '',
    id_mapel: '',
    jam_ke: '',
    materi: '',
    keterangan: '',
    foto_kegiatan: null,
});

const openForm = (j) => {
    selectedJadwal.value = j;
    form.id_kelas = j.id_kelas;
    form.id_mapel = j.id_mapel;
    // Extract jam ke based on jam_mulai (Simple heuristic or just leave empty for teacher to input)
    form.jam_ke = ''; 
    form.materi = '';
    form.keterangan = '';
    form.foto_kegiatan = null;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    selectedJadwal.value = null;
    form.reset();
};

const submitJurnal = () => {
    form.post(route('guru.elearning.jurnal.simpan'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
};

const formatWaktu = (time) => {
    if (!time) return '-';
    // Remove seconds from HH:MM:SS
    return time.substring(0, 5);
};
</script>

<template>
    <Head title="Isi Jurnal Mengajar" />
    <DashboardLayout>
        <div class="space-y-6">
            
            <div class="flex items-center gap-4">
                <Link :href="route('guru.elearning.jurnal.index')" class="w-10 h-10 bg-white border border-gray-200 rounded-xl flex items-center justify-center text-gray-500 hover:bg-gray-50 transition shadow-sm">
                    <i class="fas fa-arrow-left"></i>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Isi Jurnal Hari Ini</h1>
                    <p class="text-sm text-gray-500 mt-1">{{ hari_ini }}, {{ new Date(tanggal).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}</p>
                </div>
            </div>

            <!-- List Jadwal Hari Ini -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="j in jadwal" :key="j.id" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 flex flex-col relative overflow-hidden transition hover:shadow-md">
                    <!-- Status Badge -->
                    <div class="absolute top-4 right-4">
                        <span v-if="j.sudah_isi" class="flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-full border border-emerald-200 shadow-sm">
                            <i class="fas fa-check-circle"></i> Selesai
                        </span>
                        <span v-else class="flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-700 text-xs font-bold rounded-full border border-amber-200 shadow-sm">
                            <i class="fas fa-clock"></i> Belum
                        </span>
                    </div>

                    <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center text-xl mb-4">
                        <i class="fas fa-book-open"></i>
                    </div>

                    <h3 class="font-bold text-gray-800 text-xl">{{ j.nama_mapel }}</h3>
                    <p class="text-indigo-600 font-semibold mt-1">{{ j.nama_kelas }}</p>
                    
                    <div class="mt-4 pt-4 border-t border-gray-100 flex items-center text-sm text-gray-500 gap-2">
                        <i class="far fa-clock"></i>
                        <span>{{ formatWaktu(j.jam_mulai) }} - {{ formatWaktu(j.jam_selesai) }}</span>
                    </div>

                    <div class="mt-5 pt-4">
                        <button v-if="!j.sudah_isi" @click="openForm(j)" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-sm transition inline-flex items-center justify-center gap-2">
                            <i class="fas fa-pencil-alt"></i> Buat Jurnal
                        </button>
                        <button v-else disabled class="w-full py-2.5 bg-gray-100 text-gray-400 font-bold rounded-xl cursor-not-allowed inline-flex items-center justify-center gap-2">
                            <i class="fas fa-check"></i> Jurnal Terisi
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="jadwal.length === 0" class="bg-white rounded-2xl border border-dashed border-gray-300 p-16 text-center">
                <div class="w-20 h-20 bg-indigo-50 text-indigo-300 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-calendar-times text-4xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-600">Tidak Ada Jadwal</h3>
                <p class="text-gray-500 mt-2">Anda tidak memiliki jadwal mengajar pada hari ini.</p>
            </div>
        </div>

        <!-- Modal Form Jurnal -->
        <Modal :show="isModalOpen" @close="closeModal" maxWidth="md">
            <div class="p-6 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50 rounded-bl-full -z-10"></div>
                
                <h2 class="text-2xl font-bold text-gray-800 mb-1">Form Jurnal Mengajar</h2>
                <p class="text-sm text-gray-500 mb-6">{{ selectedJadwal?.nama_mapel }} - {{ selectedJadwal?.nama_kelas }}</p>

                <form @submit.prevent="submitJurnal" class="space-y-5">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5">Jam Ke <span class="text-rose-500">*</span></label>
                        <input type="text" v-model="form.jam_ke" required class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm" placeholder="Contoh: 1-2">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5">Materi Pokok / Pembahasan <span class="text-rose-500">*</span></label>
                        <textarea v-model="form.materi" required rows="3" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm" placeholder="Materi yang diajarkan hari ini..."></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5">Keterangan / Catatan (Opsional)</label>
                        <textarea v-model="form.keterangan" rows="2" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm" placeholder="Catatan tambahan kejadian di kelas..."></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5">Foto Kegiatan (Opsional)</label>
                        <div class="flex items-center justify-center w-full">
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-xl cursor-pointer bg-gray-50 hover:bg-gray-100 transition relative overflow-hidden">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <i class="fas fa-camera text-3xl text-gray-400 mb-2"></i>
                                    <p class="text-sm text-gray-500 font-semibold"><span class="text-indigo-600">Klik untuk upload</span> atau drag and drop</p>
                                </div>
                                <input type="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/*" @change="form.foto_kegiatan = $event.target.files[0]">
                            </label>
                        </div>
                        <p v-if="form.foto_kegiatan" class="mt-2 text-sm text-emerald-600 font-semibold"><i class="fas fa-check mr-1"></i> Foto dipilih: {{ form.foto_kegiatan.name }}</p>
                    </div>

                    <div class="flex justify-end pt-4 gap-3">
                        <button type="button" @click="closeModal" class="px-5 py-2.5 bg-white border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 font-bold transition">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl shadow-md hover:bg-indigo-700 font-bold transition inline-flex items-center gap-2">
                            Lanjut ke Presensi <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </DashboardLayout>
</template>
