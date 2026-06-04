<script setup>
import { useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    profil: Object
});

const form = useForm({
    deskripsi_hero: props.profil?.deskripsi_hero || '',
    nama_kepsek: props.profil?.nama_kepsek || '',
    sambutan_kepsek: props.profil?.sambutan_kepsek || '',
    link_fb: props.profil?.link_fb || '',
    link_ig: props.profil?.link_ig || '',
    link_yt: props.profil?.link_yt || '',
    link_map: props.profil?.link_map || '',
    foto_kepsek: null,
    spot_hero_png: null,
    spot_ppdb_png: null,
});

const submit = () => {
    form.post(route('admin.web.profil.update'), {
        preserveScroll: true,
        onSuccess: () => {
            alert('Profil berhasil diperbarui!');
        }
    });
};

const handleFileUpload = (event, field) => {
    form[field] = event.target.files[0];
};
</script>

<template>
    <DashboardLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-bold text-xl text-slate-800 leading-tight">
                    <i class="fas fa-cogs mr-2 text-emerald-600"></i> Konfigurasi Website
                </h2>
            </div>
        </template>

        <div class="max-w-7xl mx-auto py-8">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <form @submit.prevent="submit" class="p-6 md:p-8 space-y-8">
                    
                    <!-- Section: Sambutan Kepsek -->
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 border-b pb-2 mb-4">Profil & Sambutan Pimpinan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Nama Kepala Sekolah</label>
                                <input v-model="form.nama_kepsek" type="text" class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Foto Kepala Sekolah</label>
                                <input type="file" @change="e => handleFileUpload(e, 'foto_kepsek')" class="w-full rounded-xl border border-slate-300 p-2 text-sm bg-slate-50">
                                <p class="text-xs text-slate-500 mt-1">Biarkan kosong jika tidak ingin mengubah foto. (Disarankan rasio 1:1 / portrait)</p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-2">Teks Sambutan / Profil Singkat</label>
                                <textarea v-model="form.sambutan_kepsek" rows="4" class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Spot Foto PNG -->
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 border-b pb-2 mb-4">Spot Foto Transparan (PNG)</h3>
                        <p class="text-sm text-slate-500 mb-6">Upload foto berformat PNG tanpa background (transparan) untuk menghidupkan Landing Page. Ini akan menimpa gambar dummy bawaan.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                                <label class="block text-sm font-bold text-slate-800 mb-2">Spot Foto Hero (Siswa Praktek)</label>
                                <p class="text-xs text-slate-500 mb-3">Muncul di banner utama sebelah kanan. Disarankan PNG transparan gambar siswa sedang praktek jurusan.</p>
                                <input type="file" accept=".png" @change="e => handleFileUpload(e, 'spot_hero_png')" class="w-full rounded-lg border border-slate-300 p-2 text-sm bg-white">
                                <div v-if="profil?.spot_hero_png" class="mt-3">
                                    <span class="text-xs text-emerald-600 font-bold"><i class="fas fa-check-circle"></i> Sudah Terpasang</span>
                                    <img :src="`/uploads/profil/${profil.spot_hero_png}`" class="h-20 object-contain mt-2 bg-slate-200 rounded p-1">
                                </div>
                            </div>
                            
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                                <label class="block text-sm font-bold text-slate-800 mb-2">Spot Foto PPDB (Guru/Admin)</label>
                                <p class="text-xs text-slate-500 mb-3">Muncul di bagian pendaftaran PPDB (bawah). Disarankan PNG transparan gambar guru atau admin tersenyum.</p>
                                <input type="file" accept=".png" @change="e => handleFileUpload(e, 'spot_ppdb_png')" class="w-full rounded-lg border border-slate-300 p-2 text-sm bg-white">
                                <div v-if="profil?.spot_ppdb_png" class="mt-3">
                                    <span class="text-xs text-emerald-600 font-bold"><i class="fas fa-check-circle"></i> Sudah Terpasang</span>
                                    <img :src="`/uploads/profil/${profil.spot_ppdb_png}`" class="h-20 object-contain mt-2 bg-slate-200 rounded p-1">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section: General -->
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 border-b pb-2 mb-4">Pengaturan Umum & Sosial Media</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-2">Deskripsi Singkat (Hero)</label>
                                <input v-model="form.deskripsi_hero" type="text" class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Link YouTube Video Profil</label>
                                <input v-model="form.link_yt" type="url" placeholder="https://youtube.com/..." class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Link Instagram</label>
                                <input v-model="form.link_ig" type="url" placeholder="https://instagram.com/..." class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t">
                        <button type="submit" :disabled="form.processing" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition-all flex items-center gap-2">
                            <i v-if="form.processing" class="fas fa-spinner fa-spin"></i>
                            <i v-else class="fas fa-save"></i>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>
