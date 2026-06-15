<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    jurnal: Array,
    laporan_akhir: Array
});

const activeTab = ref('jurnal');
const selectedJurnal = ref(null);
const isModalJurnalOpen = ref(false);

const formJurnal = useForm({
    id: null,
    status_jurnal: '',
    komentar_guru: ''
});

const openValidasiJurnal = (j) => {
    selectedJurnal.value = j;
    formJurnal.id = j.id;
    formJurnal.status_jurnal = j.status_jurnal == 'Menunggu' ? 'Disetujui' : j.status_jurnal;
    formJurnal.komentar_guru = j.komentar_guru || '';
    isModalJurnalOpen.value = true;
};

const submitValidasiJurnal = () => {
    formJurnal.post(route('guru.pkl.jurnal.validasi'), {
        preserveScroll: true,
        onSuccess: () => {
            isModalJurnalOpen.value = false;
        }
    });
};

const selectedLaporan = ref(null);
const isModalLaporanOpen = ref(false);
const formLaporan = useForm({
    id: null,
    status_laporan: '',
    catatan_revisi: ''
});

const openValidasiLaporan = (l) => {
    selectedLaporan.value = l;
    formLaporan.id = l.id;
    formLaporan.status_laporan = l.status_laporan == 'Menunggu' ? 'Disetujui' : l.status_laporan;
    formLaporan.catatan_revisi = l.catatan_revisi || '';
    isModalLaporanOpen.value = true;
};

const submitValidasiLaporan = () => {
    formLaporan.post(route('guru.pkl.laporan.validasi'), {
        preserveScroll: true,
        onSuccess: () => {
            isModalLaporanOpen.value = false;
        }
    });
};
</script>

<template>
    <Head title="Validasi Jurnal PKL" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-book text-blue-500"></i>
                        Validasi Jurnal & Laporan
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Review dan berikan persetujuan untuk jurnal harian dan laporan akhir siswa.
                    </p>
                </div>
            </div>

            <div v-if="$page.props.flash?.message" class="bg-green-50 text-green-800 p-4 rounded-2xl shadow-sm flex items-center gap-3">
                <i class="fas fa-check-circle"></i> {{ $page.props.flash.message }}
            </div>

            <!-- Tabs -->
            <div class="flex gap-2 border-b border-gray-200 dark:border-gray-700">
                <button @click="activeTab = 'jurnal'" class="px-6 py-3 font-bold text-sm rounded-t-xl transition-all" :class="activeTab === 'jurnal' ? 'bg-white dark:bg-gray-800 text-blue-600 border border-gray-200 dark:border-gray-700 border-b-0' : 'text-gray-500 hover:text-gray-700'">
                    <i class="fas fa-calendar-day mr-2"></i> Jurnal Harian Siswa
                </button>
                <button @click="activeTab = 'laporan'" class="px-6 py-3 font-bold text-sm rounded-t-xl transition-all" :class="activeTab === 'laporan' ? 'bg-white dark:bg-gray-800 text-blue-600 border border-gray-200 dark:border-gray-700 border-b-0' : 'text-gray-500 hover:text-gray-700'">
                    <i class="fas fa-file-pdf mr-2"></i> Laporan Akhir Siswa
                </button>
            </div>

            <!-- TAB JURNAL -->
            <div v-if="activeTab === 'jurnal'" class="bg-white dark:bg-gray-800 rounded-b-3xl rounded-tr-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-4 font-bold">Tanggal</th>
                                <th class="px-6 py-4 font-bold">Siswa / DUDI</th>
                                <th class="px-6 py-4 font-bold">Kegiatan</th>
                                <th class="px-6 py-4 font-bold text-center">Status</th>
                                <th class="px-6 py-4 font-bold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="j in jurnal" :key="j.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ j.tanggal }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900 dark:text-white">{{ j.nama_siswa }}</div>
                                    <div class="text-xs text-blue-600">{{ j.nama_dudi }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 dark:text-white max-w-sm line-clamp-2" :title="j.kegiatan">{{ j.kegiatan }}</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 text-xs font-bold rounded-full"
                                          :class="{
                                              'bg-blue-100 text-blue-700': j.status_jurnal == 'Menunggu',
                                              'bg-emerald-100 text-emerald-700': j.status_jurnal == 'Disetujui',
                                              'bg-rose-100 text-rose-700': j.status_jurnal == 'Revisi'
                                          }">
                                        {{ j.status_jurnal }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button @click="openValidasiJurnal(j)" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg text-xs font-bold transition-colors">
                                        <i class="fas fa-search-plus"></i> Review
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="jurnal.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    Belum ada entri jurnal.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB LAPORAN -->
            <div v-if="activeTab === 'laporan'" class="bg-white dark:bg-gray-800 rounded-b-3xl rounded-tr-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-4 font-bold">Tgl Upload</th>
                                <th class="px-6 py-4 font-bold">Siswa / DUDI</th>
                                <th class="px-6 py-4 font-bold">File Laporan</th>
                                <th class="px-6 py-4 font-bold text-center">Status</th>
                                <th class="px-6 py-4 font-bold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="l in laporan_akhir" :key="l.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ l.tgl_upload }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900 dark:text-white">{{ l.nama_siswa }}</div>
                                    <div class="text-xs text-blue-600">{{ l.nama_dudi }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <a :href="'/uploads/pkl_laporan/' + l.file_laporan" target="_blank" class="text-blue-600 hover:underline text-sm flex items-center gap-2 font-medium">
                                        <i class="fas fa-file-pdf text-red-500"></i> Download Laporan
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 text-xs font-bold rounded-full"
                                          :class="{
                                              'bg-blue-100 text-blue-700': l.status_laporan == 'Menunggu',
                                              'bg-emerald-100 text-emerald-700': l.status_laporan == 'Disetujui',
                                              'bg-rose-100 text-rose-700': l.status_laporan == 'Revisi'
                                          }">
                                        {{ l.status_laporan }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button @click="openValidasiLaporan(l)" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg text-xs font-bold transition-colors">
                                        <i class="fas fa-check-double"></i> Validasi
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="laporan_akhir.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    Belum ada laporan akhir yang diunggah.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Validasi Jurnal -->
        <div v-if="isModalJurnalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-xl overflow-hidden flex flex-col max-h-[90vh]">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-700/50">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white">Review Jurnal Harian</h3>
                    <button @click="isModalJurnalOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                </div>
                <div class="p-6 overflow-y-auto flex-1">
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl mb-4 text-sm">
                        <div class="font-bold text-blue-800 dark:text-blue-400 mb-1">{{ selectedJurnal.nama_siswa }} - {{ selectedJurnal.tanggal }}</div>
                        <div class="text-gray-700 dark:text-gray-300">{{ selectedJurnal.kegiatan }}</div>
                    </div>
                    
                    <div v-if="selectedJurnal.foto_kegiatan" class="mb-4">
                        <p class="text-xs font-bold text-gray-500 mb-2 uppercase">Bukti Kegiatan</p>
                        <img :src="'/uploads/jurnal_pkl/' + selectedJurnal.foto_kegiatan" class="w-full rounded-xl border border-gray-200">
                    </div>

                    <form @submit.prevent="submitValidasiJurnal">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Status Validasi</label>
                                <select v-model="formJurnal.status_jurnal" class="w-full rounded-xl border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500" required>
                                    <option value="Menunggu">Menunggu</option>
                                    <option value="Disetujui">Disetujui (ACC)</option>
                                    <option value="Revisi">Perlu Direvisi</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Komentar / Catatan Guru</label>
                                <textarea v-model="formJurnal.komentar_guru" rows="3" class="w-full rounded-xl border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500" placeholder="Opsional (Wajib jika revisi)"></textarea>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" @click="isModalJurnalOpen = false" class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold">Batal</button>
                            <button type="submit" :disabled="formJurnal.processing" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold disabled:opacity-50">
                                Simpan Validasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Validasi Laporan -->
        <div v-if="isModalLaporanOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-700/50">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white">Validasi Laporan Akhir</h3>
                    <button @click="isModalLaporanOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                </div>
                <form @submit.prevent="submitValidasiLaporan" class="p-6">
                    <div class="mb-4 bg-gray-50 dark:bg-gray-700/50 p-4 rounded-xl text-sm border border-gray-200 dark:border-gray-700">
                        <div class="font-bold text-gray-900 dark:text-white mb-1">{{ selectedLaporan.nama_siswa }}</div>
                        <div class="text-blue-600">{{ selectedLaporan.nama_dudi }}</div>
                        <div class="mt-2 text-gray-500">
                            <a :href="'/uploads/pkl_laporan/' + selectedLaporan.file_laporan" target="_blank" class="hover:underline">
                                <i class="fas fa-external-link-alt"></i> Buka File PDF
                            </a>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Status Laporan</label>
                            <select v-model="formLaporan.status_laporan" class="w-full rounded-xl border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500" required>
                                <option value="Menunggu">Menunggu</option>
                                <option value="Disetujui">Disetujui (Siap Dinilai)</option>
                                <option value="Revisi">Perlu Revisi</option>
                            </select>
                        </div>
                        <div v-if="formLaporan.status_laporan == 'Revisi'">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Catatan Revisi</label>
                            <textarea v-model="formLaporan.catatan_revisi" rows="3" class="w-full rounded-xl border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500" required></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="isModalLaporanOpen = false" class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold">Batal</button>
                        <button type="submit" :disabled="formLaporan.processing" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold disabled:opacity-50">
                            Simpan Status
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </DashboardLayout>
</template>
