<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    sekolah: Object,
});

const form = useForm({
    npsn: props.sekolah.npsn || '',
    status_sekolah: props.sekolah.status_sekolah || 'Swasta',
    nama_sekolah: props.sekolah.nama_sekolah || '',
    alamat: props.sekolah.alamat || '',
    desa_kelurahan: props.sekolah.desa_kelurahan || '',
    kecamatan: props.sekolah.kecamatan || '',
    kabupaten: props.sekolah.kabupaten || '',
    provinsi: props.sekolah.provinsi || '',
    kode_pos: props.sekolah.kode_pos || '',
    no_telp: props.sekolah.no_telp || '',
    email: props.sekolah.email || '',
    website: props.sekolah.website || '',
    slogan_sekolah: props.sekolah.slogan_sekolah || '',
    
    // Kepsek
    nama_kepsek: props.sekolah.nama_kepsek || '',
    nip_kepsek: props.sekolah.nip_kepsek || '',
    sambutan_kepsek: props.sekolah.sambutan_kepsek || '',



    // Files
    logo: null,
    kop_surat: null,
    ttd_kepsek: null,
    foto_kepsek: null,
});

// Image preview state
const previews = ref({
    logo: props.sekolah.logo ? (props.sekolah.logo.includes('default') ? '/images/' + props.sekolah.logo : '/uploads/identitas/' + props.sekolah.logo) : null,
    kop_surat: props.sekolah.kop_surat ? (props.sekolah.kop_surat.includes('default') ? '/images/' + props.sekolah.kop_surat : '/uploads/identitas/' + props.sekolah.kop_surat) : null,
    ttd_kepsek: props.sekolah.ttd_kepsek ? (props.sekolah.ttd_kepsek.includes('default') ? '/images/' + props.sekolah.ttd_kepsek : '/uploads/identitas/' + props.sekolah.ttd_kepsek) : null,
    foto_kepsek: props.sekolah.foto_kepsek ? (props.sekolah.foto_kepsek.includes('default') ? '/images/' + props.sekolah.foto_kepsek : '/uploads/identitas/' + props.sekolah.foto_kepsek) : null,
});

const handleFileChange = (e, field) => {
    const file = e.target.files[0];
    if (file) {
        form[field] = file;
        previews.value[field] = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post(route('admin.master.sekolah.update'), {
        preserveScroll: true,
        forceFormData: true,
    });
};

const activeTab = ref('utama');
</script>

<template>
    <Head title="Identitas Sekolah" />

    <DashboardLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 class="font-bold text-2xl text-gray-900 dark:text-white tracking-tight">Identitas Sekolah</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Konfigurasi data utama, kontak, dan logo instansi sekolah.</p>
                    </div>
                    <button @click="submit" :disabled="form.processing" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-sm transition-all flex items-center gap-2">
                        <i class="fas fa-save" :class="{'fa-spin': form.processing}"></i> Simpan Perubahan
                    </button>
                </div>

                <div v-if="$page.props.flash?.message" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-400 p-4 mb-6 rounded-2xl shadow-sm flex items-center gap-3">
                    <i class="fas fa-check-circle"></i> <span v-html="$page.props.flash.message"></span>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    
                    <!-- Tabs Navigation -->
                    <div class="flex overflow-x-auto border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 px-2">
                        <button @click="activeTab = 'utama'" :class="activeTab === 'utama' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors flex items-center gap-2">
                            <i class="fas fa-school"></i> Data Utama
                        </button>
                        <button @click="activeTab = 'kontak'" :class="activeTab === 'kontak' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors flex items-center gap-2">
                            <i class="fas fa-map-marker-alt"></i> Kontak & Alamat
                        </button>
                        <button @click="activeTab = 'kepsek'" :class="activeTab === 'kepsek' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors flex items-center gap-2">
                            <i class="fas fa-user-tie"></i> Kepala Sekolah
                        </button>

                        <button @click="activeTab = 'media'" :class="activeTab === 'media' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors flex items-center gap-2">
                            <i class="fas fa-images"></i> Logo & Berkas
                        </button>
                    </div>

                    <!-- Tab Content -->
                    <div class="p-6 md:p-8">
                        <form @submit.prevent="submit">
                            
                            <!-- Tab: Utama -->
                            <div v-show="activeTab === 'utama'" class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Sekolah <span class="text-red-500">*</span></label>
                                        <input v-model="form.nama_sekolah" type="text" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                                        <div v-if="form.errors.nama_sekolah" class="text-red-500 text-xs mt-1">{{ form.errors.nama_sekolah }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">NPSN <span class="text-red-500">*</span></label>
                                        <input v-model="form.npsn" type="text" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status Sekolah</label>
                                        <select v-model="form.status_sekolah" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                            <option value="Negeri">Negeri</option>
                                            <option value="Swasta">Swasta</option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Slogan / Motto Sekolah</label>
                                        <input v-model="form.slogan_sekolah" type="text" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    </div>
                                </div>
                            </div>

                            <!-- Tab: Kontak & Alamat -->
                            <div v-show="activeTab === 'kontak'" class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat Lengkap</label>
                                        <textarea v-model="form.alamat" rows="3" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Desa / Kelurahan</label>
                                        <input v-model="form.desa_kelurahan" type="text" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kecamatan</label>
                                        <input v-model="form.kecamatan" type="text" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kabupaten / Kota</label>
                                        <input v-model="form.kabupaten" type="text" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Provinsi</label>
                                        <input v-model="form.provinsi" type="text" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kode Pos</label>
                                        <input v-model="form.kode_pos" type="text" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">No. Telepon / WhatsApp</label>
                                        <input v-model="form.no_telp" type="text" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email Utama</label>
                                        <input v-model="form.email" type="email" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Website</label>
                                        <input v-model="form.website" type="text" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    </div>
                                </div>
                            </div>

                            <!-- Tab: Kepala Sekolah -->
                            <div v-show="activeTab === 'kepsek'" class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap Kepala Sekolah</label>
                                        <input v-model="form.nama_kepsek" type="text" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">NIP Kepala Sekolah</label>
                                        <input v-model="form.nip_kepsek" type="text" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kata Sambutan</label>
                                        <textarea v-model="form.sambutan_kepsek" rows="4" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                                    </div>
                                </div>
                            </div>


                            <!-- Tab: Logo & Berkas -->
                            <div v-show="activeTab === 'media'" class="space-y-8">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    
                                    <!-- Logo Sekolah -->
                                    <div class="border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-2xl p-6 flex flex-col items-center justify-center text-center">
                                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-4">Logo Sekolah</label>
                                        <div class="w-32 h-32 mb-4 bg-gray-50 dark:bg-gray-900 rounded-full border-4 border-white dark:border-gray-800 shadow-lg overflow-hidden flex items-center justify-center">
                                            <img v-if="previews.logo" :src="previews.logo" class="w-full h-full object-contain">
                                            <i v-else class="fas fa-image text-4xl text-gray-300"></i>
                                        </div>
                                        <label class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-sm font-medium">
                                            Pilih Gambar
                                            <input type="file" class="hidden" accept="image/*" @change="e => handleFileChange(e, 'logo')">
                                        </label>
                                    </div>

                                    <!-- Kop Surat -->
                                    <div class="border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-2xl p-6 flex flex-col items-center justify-center text-center">
                                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-4">Kop Surat Resmi</label>
                                        <div class="w-full h-32 mb-4 bg-gray-50 dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden flex items-center justify-center">
                                            <img v-if="previews.kop_surat" :src="previews.kop_surat" class="w-full h-full object-contain">
                                            <i v-else class="fas fa-file-invoice text-4xl text-gray-300"></i>
                                        </div>
                                        <label class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-sm font-medium">
                                            Pilih Gambar
                                            <input type="file" class="hidden" accept="image/*" @change="e => handleFileChange(e, 'kop_surat')">
                                        </label>
                                    </div>

                                    <!-- TTD Kepsek -->
                                    <div class="border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-2xl p-6 flex flex-col items-center justify-center text-center">
                                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-4">Tanda Tangan Kepsek</label>
                                        <div class="w-48 h-24 mb-4 bg-gray-50 dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden flex items-center justify-center">
                                            <img v-if="previews.ttd_kepsek" :src="previews.ttd_kepsek" class="w-full h-full object-contain">
                                            <i v-else class="fas fa-signature text-4xl text-gray-300"></i>
                                        </div>
                                        <label class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-sm font-medium">
                                            Pilih Gambar
                                            <input type="file" class="hidden" accept="image/*" @change="e => handleFileChange(e, 'ttd_kepsek')">
                                        </label>
                                    </div>

                                    <!-- Foto Kepsek -->
                                    <div class="border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-2xl p-6 flex flex-col items-center justify-center text-center">
                                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-4">Foto Kepala Sekolah</label>
                                        <div class="w-32 h-40 mb-4 bg-gray-50 dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden flex items-center justify-center">
                                            <img v-if="previews.foto_kepsek" :src="previews.foto_kepsek" class="w-full h-full object-cover">
                                            <i v-else class="fas fa-user-tie text-4xl text-gray-300"></i>
                                        </div>
                                        <label class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-sm font-medium">
                                            Pilih Gambar
                                            <input type="file" class="hidden" accept="image/*" @change="e => handleFileChange(e, 'foto_kepsek')">
                                        </label>
                                    </div>

                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </DashboardLayout>
</template>
