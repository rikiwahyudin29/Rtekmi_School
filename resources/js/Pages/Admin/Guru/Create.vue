<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const form = useForm({
    nip: '',
    nama_lengkap: '',
    gelar_depan: '',
    gelar_belakang: '',
    jenis_kelamin: 'L',
    nik: '',
    tempat_lahir: '',
    tanggal_lahir: '',
    ibu_kandung: '',
    nuptk: '',
    dapodik_id: '',
    pendidikan_terakhir: '',
    sertifikasi: '',
    status_guru: 'GTY',
    status_kepegawaian: '',
    email: '',
    nomor_wa: '',
    alamat: '',
    foto: null
});

const imagePreview = ref(null);

const handleFotoUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.foto = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const submit = () => {
    form.post('/admin/guru', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Tambah Guru" />

    <DashboardLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-xl text-gray-800 dark:text-gray-100 leading-tight">Tambah Data Guru</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Masukkan seluruh data tenaga pendidik secara lengkap.</p>
                </div>
                <Link href="/admin/guru" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-2.5 px-5 rounded-xl text-sm transition-all flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Kembali
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-5xl mx-auto">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Kartu Informasi Kepegawaian -->
                    <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                            <h3 class="font-bold text-gray-800 flex items-center gap-2"><i class="fas fa-briefcase text-primary-500"></i> Informasi Kepegawaian & Akademik</h3>
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">NIP</label>
                                <input type="text" v-model="form.nip" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                <div v-if="form.errors.nip" class="text-red-500 text-xs mt-1">{{ form.errors.nip }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">NUPTK</label>
                                <input type="text" v-model="form.nuptk" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">ID Dapodik</label>
                                <input type="text" v-model="form.dapodik_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status Guru</label>
                                <select v-model="form.status_guru" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    <option value="PNS">PNS</option>
                                    <option value="GTT">GTT</option>
                                    <option value="GTY">GTY</option>
                                    <option value="HONORER">Honorer</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status Kepegawaian</label>
                                <input type="text" v-model="form.status_kepegawaian" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="Contoh: Tetap Yayasan">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Pendidikan Terakhir</label>
                                <input type="text" v-model="form.pendidikan_terakhir" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="Contoh: S1 - Pendidikan">
                            </div>
                            
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700">Data Sertifikasi</label>
                                <input type="text" v-model="form.sertifikasi" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                        </div>
                    </div>

                    <!-- Kartu Informasi Pribadi -->
                    <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                            <h3 class="font-bold text-gray-800 flex items-center gap-2"><i class="fas fa-user text-primary-500"></i> Data Pribadi & Kelahiran</h3>
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="md:col-span-3 grid grid-cols-1 md:grid-cols-4 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Gelar Depan</label>
                                    <input type="text" v-model="form.gelar_depan" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="Misal: Drs.">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Nama Lengkap <span class="text-red-500">*</span></label>
                                    <input type="text" v-model="form.nama_lengkap" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Gelar Belakang</label>
                                    <input type="text" v-model="form.gelar_belakang" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="Misal: S.Pd">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">NIK (KTP) / Username <span class="text-red-500">*</span></label>
                                <input type="text" v-model="form.nik" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                                <div v-if="form.errors.nik" class="text-red-500 text-xs mt-1">{{ form.errors.nik }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tempat Lahir</label>
                                <input type="text" v-model="form.tempat_lahir" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
                                <input type="date" v-model="form.tanggal_lahir" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
                                <select v-model="form.jenis_kelamin" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Nama Ibu Kandung</label>
                                <input type="text" v-model="form.ibu_kandung" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                        </div>
                    </div>

                    <!-- Kartu Kontak & Lainnya -->
                    <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                            <h3 class="font-bold text-gray-800 flex items-center gap-2"><i class="fas fa-address-book text-primary-500"></i> Kontak & Informasi Tambahan</h3>
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Alamat Email</label>
                                <input type="email" v-model="form.email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="Otomatis digenerate jika kosong">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nomor WhatsApp / HP</label>
                                <input type="text" v-model="form.nomor_wa" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Alamat Lengkap Domisili</label>
                                <textarea v-model="form.alamat" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Foto Profil (Opsional)</label>
                                <input type="file" @change="handleFotoUpload" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100" accept="image/*">
                                <div v-if="form.errors.foto" class="text-red-500 text-xs mt-1">{{ form.errors.foto }}</div>
                                
                                <div v-if="imagePreview" class="mt-4">
                                    <img :src="imagePreview" class="w-24 h-24 object-cover rounded-xl shadow-sm border border-gray-200">
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end">
                            <button type="submit" :disabled="form.processing" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2.5 px-8 rounded-xl shadow transition disabled:opacity-50 flex items-center gap-2">
                                <i class="fas fa-save"></i> Simpan Data Guru
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>
