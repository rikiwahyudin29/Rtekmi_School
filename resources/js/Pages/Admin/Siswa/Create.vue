<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    data_kelas: Array,
    data_ekskul: Array,
});

const previewFoto = ref(null);

const form = useForm({
    nisn: '',
    nis: '',
    nama_lengkap: '',
    jenis_kelamin: 'L',
    tempat_lahir: '',
    tanggal_lahir: '',
    agama: 'Islam',
    nik: '',
    alamat: '',
    no_hp_siswa: '',
    email_siswa: '',
    kelas_id: '',
    ekskul_id: '',
    sekolah_asal: '',
    tahun_angkatan: '',
    nama_ayah: '',
    nama_ibu: '',
    nama_wali: '',
    pekerjaan_ayah: '',
    pekerjaan_ibu: '',
    pekerjaan_wali: '',
    no_hp_ortu: '',
    foto: null,
});

const handleFoto = (e) => {
    const file = e.target.files[0];
    form.foto = file;
    if (file) {
        const reader = new FileReader();
        reader.onload = (ev) => { previewFoto.value = ev.target.result; };
        reader.readAsDataURL(file);
    }
};

const submit = () => {
    form.post('/admin/siswa', {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Tambah Siswa" />

    <DashboardLayout>
        <div class="py-8">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="font-bold text-2xl text-gray-900 dark:text-white tracking-tight">Tambah Siswa Baru</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Masukkan data lengkap peserta didik baru.</p>
                    </div>
                    <Link href="/admin/siswa" class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-bold py-2.5 px-5 rounded-xl text-sm transition-all flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </Link>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    
                    <!-- Card: Data Identitas -->
                    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-700 p-8">
                        <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                            <i class="fas fa-id-card text-primary-500"></i> Data Identitas Siswa
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Foto -->
                            <div class="md:row-span-3 flex flex-col items-center">
                                <div class="w-36 h-36 rounded-3xl bg-gray-100 dark:bg-gray-700 overflow-hidden border-2 border-dashed border-gray-300 dark:border-gray-600 mb-3 flex items-center justify-center">
                                    <img v-if="previewFoto" :src="previewFoto" class="w-full h-full object-cover">
                                    <i v-else class="fas fa-user-graduate text-4xl text-gray-300 dark:text-gray-500"></i>
                                </div>
                                <label class="cursor-pointer bg-primary-50 text-primary-600 hover:bg-primary-100 dark:bg-primary-900/30 dark:text-primary-400 font-semibold py-2 px-4 rounded-xl text-xs transition-all border border-primary-200 dark:border-primary-800">
                                    <i class="fas fa-camera mr-1"></i> Pilih Foto
                                    <input type="file" @change="handleFoto" accept="image/*" class="hidden">
                                </label>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">NISN <span class="text-red-500">*</span></label>
                                <input type="text" v-model="form.nisn" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                                <div v-if="form.errors.nisn" class="text-red-500 text-xs mt-1">{{ form.errors.nisn }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">NIS</label>
                                <input type="text" v-model="form.nis" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                                <input type="text" v-model="form.nama_lengkap" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                                <div v-if="form.errors.nama_lengkap" class="text-red-500 text-xs mt-1">{{ form.errors.nama_lengkap }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">NIK</label>
                                <input type="text" v-model="form.nik" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jenis Kelamin</label>
                                <select v-model="form.jenis_kelamin" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tempat Lahir</label>
                                <input type="text" v-model="form.tempat_lahir" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Lahir</label>
                                <input type="date" v-model="form.tanggal_lahir" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Agama</label>
                                <select v-model="form.agama" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    <option value="Islam">Islam</option>
                                    <option value="Kristen">Kristen</option>
                                    <option value="Katolik">Katolik</option>
                                    <option value="Hindu">Hindu</option>
                                    <option value="Buddha">Buddha</option>
                                    <option value="Konghucu">Konghucu</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Data Akademik -->
                    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-700 p-8">
                        <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                            <i class="fas fa-graduation-cap text-primary-500"></i> Data Akademik
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kelas <span class="text-red-500">*</span></label>
                                <select v-model="form.kelas_id" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                                    <option value="" disabled>-- Pilih Kelas --</option>
                                    <option v-for="k in data_kelas" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                                </select>
                                <div v-if="form.errors.kelas_id" class="text-red-500 text-xs mt-1">{{ form.errors.kelas_id }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ekskul</label>
                                <select v-model="form.ekskul_id" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    <option value="">-- Pilih Ekskul --</option>
                                    <option v-for="e in data_ekskul" :key="e.id" :value="e.id">{{ e.nama_ekskul }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sekolah Asal</label>
                                <input type="text" v-model="form.sekolah_asal" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tahun Angkatan</label>
                                <input type="text" v-model="form.tahun_angkatan" placeholder="2024/2025" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                        </div>
                    </div>

                    <!-- Card: Kontak & Orang Tua -->
                    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-700 p-8">
                        <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                            <i class="fas fa-users text-primary-500"></i> Kontak & Data Orang Tua
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alamat Lengkap</label>
                                <textarea v-model="form.alamat" rows="2" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">No. HP Siswa</label>
                                <input type="text" v-model="form.no_hp_siswa" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email Siswa</label>
                                <input type="email" v-model="form.email_siswa" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">No. HP Orang Tua</label>
                                <input type="text" v-model="form.no_hp_ortu" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>

                            <div class="md:col-span-3 border-t border-gray-100 dark:border-gray-700 pt-4 mt-2">
                                <p class="text-xs text-gray-400 font-medium uppercase tracking-wider mb-4">Data Orang Tua / Wali</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Ayah</label>
                                <input type="text" v-model="form.nama_ayah" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pekerjaan Ayah</label>
                                <input type="text" v-model="form.pekerjaan_ayah" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Ibu</label>
                                <input type="text" v-model="form.nama_ibu" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pekerjaan Ibu</label>
                                <input type="text" v-model="form.pekerjaan_ibu" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Wali</label>
                                <input type="text" v-model="form.nama_wali" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pekerjaan Wali</label>
                                <input type="text" v-model="form.pekerjaan_wali" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-end gap-3">
                        <Link href="/admin/siswa" class="px-6 py-3 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Batal</Link>
                        <button type="submit" :disabled="form.processing" class="px-8 py-3 rounded-xl text-sm font-bold text-white bg-primary-600 hover:bg-primary-700 transition-colors shadow-lg shadow-primary-500/30 disabled:opacity-50 flex items-center gap-2">
                            <i class="fas fa-save"></i> Simpan Data Siswa
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </DashboardLayout>
</template>
