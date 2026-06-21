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
    tingkat: 'Kab/Kota',
    juara: '',
    tanggal: '',
    foto_dokumentasi: null,
    deskripsi_caption: ''
});

const submitPrestasi = () => {
    form.post(route('guru.ekskul.prestasi.simpan'), {
        preserveScroll: true,
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset('nama_lomba', 'juara', 'tanggal', 'foto_dokumentasi', 'deskripsi_caption');
        }
    });
};
</script>

<template>
    <Head :title="`Prestasi ${ekskul.nama_ekskul}`" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <Link :href="route('guru.ekskul.index')" class="text-indigo-600 hover:text-indigo-800 text-sm font-bold flex items-center gap-2 mb-2">
                        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                    </Link>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <i class="fas fa-trophy text-yellow-500"></i>
                        Galeri Prestasi: {{ ekskul.nama_ekskul }}
                    </h2>
                </div>
                <button @click="isModalOpen = true" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 font-bold shadow-sm flex items-center gap-2">
                    <i class="fas fa-plus"></i> Tambah Prestasi
                </button>
            </div>

            <div v-if="$page.props.flash?.message" class="bg-green-50 text-green-800 p-4 rounded-2xl shadow-sm flex items-center gap-3">
                <i class="fas fa-check-circle"></i> {{ $page.props.flash.message }}
            </div>

            <!-- Gallery -->
            <div v-if="prestasi.length === 0" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm p-12 text-center border border-gray-100 dark:border-gray-700">
                <i class="fas fa-medal text-4xl mb-3 text-gray-400"></i>
                <h3 class="font-bold text-lg text-gray-900 dark:text-white">Belum Ada Prestasi</h3>
                <p class="text-gray-500 mt-1">Tambahkan prestasi pertama untuk ekskul ini.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <div v-for="p in prestasi" :key="p.id" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden group">
                    <div class="h-48 bg-gray-200 overflow-hidden relative">
                        <img :src="`/uploads/ekskul/prestasi/${p.foto_dokumentasi}`" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute top-4 right-4 bg-yellow-500 text-white text-xs font-black px-3 py-1 rounded-full shadow-lg border-2 border-white">
                            {{ p.tingkat }}
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-lg font-black text-gray-900 dark:text-white leading-tight mb-2">{{ p.nama_lomba }}</h3>
                        <div class="font-bold text-indigo-600 mb-2">Juara {{ p.juara }}</div>
                        <div class="text-xs text-gray-500 flex items-center gap-2 mb-4">
                            <i class="far fa-calendar-alt"></i> {{ p.tanggal }}
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-3">{{ p.deskripsi_caption }}</p>
                    </div>
                </div>
            </div>

        </div>

        <!-- Modal Tambah Prestasi -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center shrink-0">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white">Tambah Prestasi</h3>
                    <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                </div>
                <div class="p-6 overflow-y-auto flex-1">
                    <form @submit.prevent="submitPrestasi" id="prestasiForm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Nama Perlombaan / Kejuaraan</label>
                                <input type="text" v-model="form.nama_lomba" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Tingkat Lomba</label>
                                <select v-model="form.tingkat" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 shadow-sm">
                                    <option value="Sekolah">Sekolah / Antar Kelas</option>
                                    <option value="Kab/Kota">Kabupaten / Kota</option>
                                    <option value="Provinsi">Provinsi</option>
                                    <option value="Nasional">Nasional</option>
                                    <option value="Internasional">Internasional</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Peringkat / Juara</label>
                                <input type="text" v-model="form.juara" placeholder="Contoh: 1 / Harapan 2 / Umum" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 shadow-sm" required>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Tanggal Kejuaraan</label>
                                <input type="date" v-model="form.tanggal" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 shadow-sm" required>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Deskripsi / Caption (Opsional)</label>
                                <textarea v-model="form.deskripsi_caption" rows="3" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 shadow-sm"></textarea>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Foto Dokumentasi (Wajib, Max 2MB)</label>
                                <input type="file" @input="form.foto_dokumentasi = $event.target.files[0]" accept="image/*" class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 cursor-pointer" required>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700 shrink-0 flex justify-end gap-3">
                    <button type="button" @click="isModalOpen = false" class="px-5 py-2.5 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold">Batal</button>
                    <button type="submit" form="prestasiForm" :disabled="form.processing" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold flex items-center gap-2">
                        <i v-if="form.processing" class="fas fa-spinner fa-spin"></i>
                        <i v-else class="fas fa-save"></i>
                        Simpan Prestasi
                    </button>
                </div>
            </div>
        </div>

    </DashboardLayout>
</template>
