<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    tema: Array,
    kelompok: Array,
    guru: Array
});

const isModalTemaOpen = ref(false);
const isModalKegiatanOpen = ref(false);
const isModalKelompokOpen = ref(false);

const formTema = useForm({ nama_tema: '' });
const formKegiatan = useForm({ tema_id: '', nama_kegiatan: '', deskripsi: '' });
const formKelompok = useForm({ guru_id: '', nama_kelompok: '' });

const submitTema = () => {
    formTema.post(route('admin.kokurikuler.tema.store'), {
        onSuccess: () => { isModalTemaOpen.value = false; formTema.reset(); }
    });
};

const submitKegiatan = () => {
    formKegiatan.post(route('admin.kokurikuler.kegiatan.store'), {
        onSuccess: () => { isModalKegiatanOpen.value = false; formKegiatan.reset(); }
    });
};

const submitKelompok = () => {
    formKelompok.post(route('admin.kokurikuler.kelompok.store'), {
        onSuccess: () => { isModalKelompokOpen.value = false; formKelompok.reset(); }
    });
};
</script>

<template>
    <Head title="Manajemen Kokurikuler" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-layer-group text-teal-500"></i>
                        Manajemen Kokurikuler
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Kelola data Tema, Kegiatan, dan Kelompok Kokurikuler.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Data Tema & Kegiatan -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-700/50">
                        <h3 class="font-bold text-gray-900 dark:text-white">Tema & Kegiatan</h3>
                        <div class="flex gap-2">
                            <button @click="isModalTemaOpen = true" class="px-3 py-1.5 bg-teal-600 text-white text-sm rounded-lg hover:bg-teal-700">
                                + Tema
                            </button>
                            <button @click="isModalKegiatanOpen = true" class="px-3 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
                                + Kegiatan
                            </button>
                        </div>
                    </div>
                    <div class="p-4">
                        <div v-for="(t, idx) in tema" :key="t.id" class="mb-4 last:mb-0">
                            <h4 class="font-bold text-teal-600 border-b pb-1 mb-2">{{ idx + 1 }}. Tema: {{ t.nama_tema }}</h4>
                            <ul class="list-disc pl-5 text-sm text-gray-600 dark:text-gray-400">
                                <li v-for="k in t.kegiatan" :key="k.id" class="mb-1">
                                    <span class="font-bold">{{ k.nama_kegiatan }}</span> - {{ k.deskripsi }}
                                </li>
                                <li v-if="!t.kegiatan || t.kegiatan.length === 0" class="italic text-gray-400">Belum ada kegiatan.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Data Kelompok -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-700/50">
                        <h3 class="font-bold text-gray-900 dark:text-white">Kelompok Kokurikuler</h3>
                        <button @click="isModalKelompokOpen = true" class="px-3 py-1.5 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700">
                            + Kelompok
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-3 w-10">No</th>
                                    <th class="px-4 py-3">Nama Kelompok</th>
                                    <th class="px-4 py-3">Koordinator Projek</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(k, idx) in kelompok" :key="k.id" class="border-b dark:border-gray-700">
                                    <td class="px-4 py-3">{{ idx + 1 }}</td>
                                    <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">{{ k.nama_kelompok }}</td>
                                    <td class="px-4 py-3">{{ k.guru?.nama_lengkap }}</td>
                                </tr>
                                <tr v-if="kelompok.length === 0">
                                    <td colspan="3" class="px-4 py-6 text-center text-gray-500">Belum ada kelompok.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <div v-if="isModalTemaOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl w-full max-w-md overflow-hidden p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Tambah Tema Kokurikuler</h3>
                <form @submit.prevent="submitTema">
                    <input v-model="formTema.nama_tema" type="text" class="w-full rounded-xl mb-4" placeholder="Nama Tema" required>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="isModalTemaOpen = false" class="px-4 py-2 bg-gray-200 rounded-xl">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-xl">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="isModalKegiatanOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl w-full max-w-md overflow-hidden p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Tambah Kegiatan Kokurikuler</h3>
                <form @submit.prevent="submitKegiatan">
                    <select v-model="formKegiatan.tema_id" class="w-full rounded-xl mb-4" required>
                        <option value="">-- Pilih Tema --</option>
                        <option v-for="t in tema" :key="t.id" :value="t.id">{{ t.nama_tema }}</option>
                    </select>
                    <input v-model="formKegiatan.nama_kegiatan" type="text" class="w-full rounded-xl mb-4" placeholder="Nama Kegiatan" required>
                    <textarea v-model="formKegiatan.deskripsi" class="w-full rounded-xl mb-4" placeholder="Deskripsi Kegiatan" required></textarea>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="isModalKegiatanOpen = false" class="px-4 py-2 bg-gray-200 rounded-xl">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="isModalKelompokOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl w-full max-w-md overflow-hidden p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Tambah Kelompok Kokurikuler</h3>
                <form @submit.prevent="submitKelompok">
                    <input v-model="formKelompok.nama_kelompok" type="text" class="w-full rounded-xl mb-4" placeholder="Nama Kelompok (Cth: Kelas 10 RPL A)" required>
                    <select v-model="formKelompok.guru_id" class="w-full rounded-xl mb-4" required>
                        <option value="">-- Pilih Koordinator Projek --</option>
                        <option v-for="g in guru" :key="g.id" :value="g.id">{{ g.nama_lengkap }}</option>
                    </select>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="isModalKelompokOpen = false" class="px-4 py-2 bg-gray-200 rounded-xl">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-xl">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>
