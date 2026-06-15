<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    kelas: Object,
    siswa: Array,
});

const isModalOpen = ref(false);
const selectedSiswa = ref(null);

const form = useForm({
    nisn: '',
    nis: '',
    nama_lengkap: '',
    jenis_kelamin: 'L',
    tempat_lahir: '',
    tanggal_lahir: '',
    agama: '',
    alamat: '',
    no_hp_siswa: '',
    status_keluarga: '',
    anak_ke: '',
    sekolah_asal: '',
    diterima_kelas: '',
    tanggal_diterima: '',
    nama_ayah: '',
    nama_ibu: '',
    pekerjaan_ayah: '',
    pekerjaan_ibu: '',
    no_hp_ortu: '',
    nama_wali: '',
    pekerjaan_wali: '',
    alamat_wali: '',
    no_hp_wali: '',
});

const openEditModal = (s) => {
    selectedSiswa.value = s;
    form.nisn = s.nisn;
    form.nis = s.nis;
    form.nama_lengkap = s.nama_lengkap;
    form.jenis_kelamin = s.jenis_kelamin || 'L';
    form.tempat_lahir = s.tempat_lahir || '';
    form.tanggal_lahir = s.tanggal_lahir || '';
    form.agama = s.agama || '';
    form.alamat = s.alamat || '';
    form.no_hp_siswa = s.no_hp_siswa || '';
    form.status_keluarga = s.status_keluarga || '';
    form.anak_ke = s.anak_ke || '';
    form.sekolah_asal = s.sekolah_asal || '';
    form.diterima_kelas = s.diterima_kelas || '';
    form.tanggal_diterima = s.tanggal_diterima || '';
    form.nama_ayah = s.nama_ayah || '';
    form.nama_ibu = s.nama_ibu || '';
    form.pekerjaan_ayah = s.pekerjaan_ayah || '';
    form.pekerjaan_ibu = s.pekerjaan_ibu || '';
    form.no_hp_ortu = s.no_hp_ortu || '';
    form.nama_wali = s.nama_wali || '';
    form.pekerjaan_wali = s.pekerjaan_wali || '';
    form.alamat_wali = s.alamat_wali || '';
    form.no_hp_wali = s.no_hp_wali || '';
    
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    selectedSiswa.value = null;
    form.reset();
};

const submit = () => {
    if (!selectedSiswa.value) return;
    
    form.post(route('guru.walikelas.data_siswa.update', selectedSiswa.value.id), {
        preserveScroll: true,
        onSuccess: () => closeModal()
    });
};
</script>

<template>
    <Head title="Data Siswa" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-users text-primary-500"></i>
                        Data Biodata Siswa
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Kelas Perwalian: <span class="font-bold text-primary-600 dark:text-primary-400">{{ kelas?.nama_kelas || '-' }}</span>
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('guru.walikelas.index')" class="px-4 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors font-medium">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </Link>
                </div>
            </div>

            <!-- Flash Messages -->
            <div v-if="$page.props.flash?.success" class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
                <span class="font-medium">Sukses!</span> {{ $page.props.flash.success }}
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300 border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-4">No</th>
                                <th scope="col" class="px-6 py-4">NISN</th>
                                <th scope="col" class="px-6 py-4">Nama Siswa</th>
                                <th scope="col" class="px-6 py-4">JK</th>
                                <th scope="col" class="px-6 py-4">Tempat, Tgl Lahir</th>
                                <th scope="col" class="px-6 py-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(s, index) in siswa" :key="s.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4">{{ index + 1 }}</td>
                                <td class="px-6 py-4 font-medium">{{ s.nisn }}</td>
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ s.nama_lengkap }}</td>
                                <td class="px-6 py-4">{{ s.jenis_kelamin }}</td>
                                <td class="px-6 py-4">{{ s.tempat_lahir || '-' }}, {{ s.tanggal_lahir || '-' }}</td>
                                <td class="px-6 py-4 text-center">
                                    <button @click="openEditModal(s)" class="px-3 py-1.5 bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50 rounded-lg transition-colors font-medium text-xs flex items-center gap-1 mx-auto">
                                        <i class="fas fa-edit"></i> Edit Biodata
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="siswa.length === 0">
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">Belum ada data siswa di kelas ini.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm transition-opacity overflow-y-auto">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 w-full max-w-4xl overflow-hidden my-8">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center sticky top-0 bg-white dark:bg-gray-800 z-10">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Edit Biodata: {{ selectedSiswa?.nama_lengkap }}</h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 p-2">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="p-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Data Pribadi -->
                        <div>
                            <h4 class="font-bold text-primary-600 border-b pb-2 mb-4">Data Pribadi</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">NISN</label>
                                    <input v-model="form.nisn" type="text" required class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    <div v-if="form.errors.nisn" class="text-red-500 text-xs mt-1">{{ form.errors.nisn }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">NIS / NIPD</label>
                                    <input v-model="form.nis" type="text" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Lengkap</label>
                                    <input v-model="form.nama_lengkap" type="text" required class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    <div v-if="form.errors.nama_lengkap" class="text-red-500 text-xs mt-1">{{ form.errors.nama_lengkap }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tempat Lahir</label>
                                    <input v-model="form.tempat_lahir" type="text" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Lahir</label>
                                    <input v-model="form.tanggal_lahir" type="date" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jenis Kelamin</label>
                                    <select v-model="form.jenis_kelamin" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                        <option value="L">Laki-Laki (L)</option>
                                        <option value="P">Perempuan (P)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Agama</label>
                                    <select v-model="form.agama" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                        <option value="">Pilih Agama</option>
                                        <option value="Islam">Islam</option>
                                        <option value="Kristen">Kristen</option>
                                        <option value="Katolik">Katolik</option>
                                        <option value="Hindu">Hindu</option>
                                        <option value="Buddha">Buddha</option>
                                        <option value="Konghucu">Konghucu</option>
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alamat Lengkap</label>
                                    <textarea v-model="form.alamat" rows="2" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">No HP Siswa</label>
                                    <input v-model="form.no_hp_siswa" type="text" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                            </div>
                        </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status dalam Keluarga</label>
                                    <input v-model="form.status_keluarga" type="text" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Anak Ke-</label>
                                    <input v-model="form.anak_ke" type="number" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sekolah Asal</label>
                                    <input v-model="form.sekolah_asal" type="text" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Diterima di Kelas</label>
                                    <input v-model="form.diterima_kelas" type="text" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Diterima</label>
                                    <input v-model="form.tanggal_diterima" type="date" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                            </div>
                        </div>

                        <!-- Data Orang Tua -->
                        <div>
                            <h4 class="font-bold text-primary-600 border-b pb-2 mb-4 mt-6">Data Orang Tua</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Ayah</label>
                                    <input v-model="form.nama_ayah" type="text" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pekerjaan Ayah</label>
                                    <input v-model="form.pekerjaan_ayah" type="text" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Ibu</label>
                                    <input v-model="form.nama_ibu" type="text" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pekerjaan Ibu</label>
                                    <input v-model="form.pekerjaan_ibu" type="text" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">No HP Orang Tua / Ayah / Ibu</label>
                                    <input v-model="form.no_hp_ortu" type="text" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                            </div>
                        </div>

                        <!-- Data Wali -->
                        <div>
                            <h4 class="font-bold text-primary-600 border-b pb-2 mb-4 mt-6">Data Wali Siswa (Opsional)</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Wali</label>
                                    <input v-model="form.nama_wali" type="text" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pekerjaan Wali</label>
                                    <input v-model="form.pekerjaan_wali" type="text" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alamat Wali</label>
                                    <textarea v-model="form.alamat_wali" rows="2" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">No HP Wali</label>
                                    <input v-model="form.no_hp_wali" type="text" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3 bg-gray-50 dark:bg-gray-800">
                    <button type="button" @click="closeModal" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 font-medium transition-colors">
                        Batal
                    </button>
                    <button type="button" @click="submit" :disabled="form.processing" class="px-5 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium transition-colors disabled:opacity-50 flex items-center gap-2 shadow-sm shadow-primary-500/30">
                        <i v-if="form.processing" class="fas fa-spinner fa-spin"></i>
                        <i v-else class="fas fa-save"></i>
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
