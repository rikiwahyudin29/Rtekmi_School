<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    ekskul: Object,
    prestasi: Array
});

const isModalOpen = ref(false);

const form = useForm({
    ekskul_id: props.ekskul.id,
    nama_lomba: '',
    tingkat: 'Kabupaten/Kota',
    juara: 'Juara 1',
    tanggal: new Date().toISOString().split('T')[0],
    deskripsi_caption: '',
    foto_dokumentasi: null
});

const submitPrestasi = () => {
    form.post(route('guru.ekskul.prestasi.simpan'), {
        preserveScroll: true,
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset('nama_lomba', 'deskripsi_caption', 'foto_dokumentasi');
        }
    });
};
</script>

<template>
    <Head :title="`Galeri Prestasi - ${ekskul.nama_ekskul}`" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <Link :href="route('guru.ekskul.index')" class="w-10 h-10 rounded-full bg-white dark:bg-gray-800 shadow-sm flex items-center justify-center text-gray-500 hover:text-orange-600 transition-colors border border-gray-100 dark:border-gray-700">
                            <i class="fas fa-arrow-left"></i>
                        </Link>
                        <span>Galeri Prestasi: {{ ekskul.nama_ekskul }}</span>
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1 ml-13">
                        Dokumentasi pencapaian dan event yang telah diikuti oleh anggota ekstrakurikuler.
                    </p>
                </div>
                <button @click="isModalOpen = true" class="px-5 py-2.5 bg-orange-600 text-white rounded-xl hover:bg-orange-700 font-bold shadow-lg shadow-orange-500/30 flex items-center gap-2 transition-all">
                    <i class="fas fa-trophy"></i> Tambah Prestasi Baru
                </button>
            </div>

            <!-- Flash Message -->
            <div v-if="$page.props.flash?.message" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-400 p-4 rounded-2xl shadow-sm flex items-center gap-3">
                <i class="fas fa-check-circle"></i> {{ $page.props.flash.message }}
            </div>

            <!-- List Prestasi -->
            <div v-if="prestasi.length === 0" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-12 text-center text-gray-500 dark:text-gray-400">
                <div class="w-20 h-20 bg-yellow-50 dark:bg-yellow-900/30 rounded-full flex items-center justify-center mx-auto mb-4 text-yellow-500 dark:text-yellow-600">
                    <i class="fas fa-medal text-4xl"></i>
                </div>
                <p class="text-lg font-medium">Belum ada catatan prestasi.</p>
                <p class="text-sm mt-1">Klik tombol Tambah Prestasi Baru untuk mengabadikan momen juara.</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <!-- Card Prestasi -->
                <div v-for="p in prestasi" :key="p.id" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden group">
                    <div class="h-48 bg-gray-100 dark:bg-gray-700 relative overflow-hidden">
                        <img v-if="p.foto_dokumentasi" :src="`/uploads/ekskul_prestasi/${p.foto_dokumentasi}`" @error="$event.target.src='/assets/img/default_prestasi.png'" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                        <div v-else class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                            <i class="fas fa-image text-4xl mb-2"></i>
                        </div>
                        <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                        <div class="absolute bottom-4 left-4 right-4 flex justify-between items-end">
                            <div class="bg-orange-500 text-white text-xs font-black px-3 py-1 rounded-full shadow-lg border border-orange-400">
                                <i class="fas fa-medal"></i> {{ p.juara }}
                            </div>
                            <div class="text-white/80 text-xs font-medium flex items-center gap-1">
                                <i class="far fa-calendar-alt"></i> {{ p.tanggal }}
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="text-xs font-bold text-orange-500 mb-1 uppercase tracking-wider">{{ p.tingkat }}</div>
                        <h3 class="font-black text-gray-900 dark:text-white text-lg mb-2 line-clamp-2" :title="p.nama_lomba">{{ p.nama_lomba }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-3 leading-relaxed">{{ p.deskripsi_caption }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Buat Prestasi -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-xl overflow-hidden transform transition-all">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gradient-to-r from-orange-50 to-white dark:from-gray-700 dark:to-gray-800">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-medal text-orange-500 text-xl"></i> Publikasi Prestasi Baru
                    </h3>
                    <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form @submit.prevent="submitPrestasi" class="p-6 max-h-[80vh] overflow-y-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Nama Lomba / Event</label>
                            <input type="text" v-model="form.nama_lomba" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-orange-500 focus:ring-orange-500 shadow-sm" placeholder="Contoh: Lomba Paskibraka Tingkat Provinsi 2026" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Pencapaian / Juara</label>
                            <input type="text" v-model="form.juara" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-orange-500 focus:ring-orange-500 shadow-sm" placeholder="Contoh: Juara 1" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Tingkat Lomba</label>
                            <select v-model="form.tingkat" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-orange-500 focus:ring-orange-500 shadow-sm">
                                <option value="Sekolah">Tingkat Sekolah</option>
                                <option value="Kecamatan">Tingkat Kecamatan</option>
                                <option value="Kabupaten/Kota">Tingkat Kabupaten/Kota</option>
                                <option value="Provinsi">Tingkat Provinsi</option>
                                <option value="Nasional">Tingkat Nasional</option>
                                <option value="Internasional">Tingkat Internasional</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Tanggal Kegiatan</label>
                            <input type="date" v-model="form.tanggal" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-orange-500 focus:ring-orange-500 shadow-sm" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Caption / Deskripsi Singkat</label>
                            <textarea v-model="form.deskripsi_caption" rows="3" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-orange-500 focus:ring-orange-500 shadow-sm" placeholder="Ceritakan momen membanggakan ini..." required></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Foto Dokumentasi Pemenang</label>
                            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-4 flex justify-center items-center bg-gray-50 hover:bg-gray-100 dark:bg-gray-700/50 transition-colors">
                                <input type="file" @input="form.foto_dokumentasi = $event.target.files[0]" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 cursor-pointer">
                            </div>
                            <div v-if="form.errors.foto_dokumentasi" class="text-red-500 text-xs mt-1">{{ form.errors.foto_dokumentasi }}</div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-3 mt-8 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="isModalOpen = false" class="px-5 py-2.5 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-orange-600 hover:bg-orange-700 shadow-lg shadow-orange-500/30 transition-colors disabled:opacity-50 flex items-center gap-2">
                            <i v-if="form.processing" class="fas fa-spinner fa-spin"></i>
                            <i v-else class="fas fa-paper-plane"></i>
                            Publikasikan Prestasi
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </DashboardLayout>
</template>
