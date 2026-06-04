<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref, onMounted } from 'vue';

const props = defineProps({
    berita: {
        type: Object,
        default: () => null
    }
});

const isEdit = !!props.berita;

const form = useForm({
    judul: props.berita?.judul || '',
    isi: props.berita?.isi || '',
    gambar: null,
    is_published: props.berita?.is_published !== undefined ? Boolean(props.berita.is_published) : true,
});

const submit = () => {
    if (isEdit) {
        form.post(route('admin.web.berita.update', props.berita.id), {
            preserveScroll: true,
        });
    } else {
        form.post(route('admin.web.berita.store'), {
            preserveScroll: true,
        });
    }
};

const handleFileUpload = (event) => {
    form.gambar = event.target.files[0];
};

// Simple textarea logic. For a real production app, you might want to integrate Quill/Summernote or Trix here.
// To avoid requiring NPM installs of Vue wrappers, we use a standard textarea that allows HTML or basic text.
</script>

<template>
    <DashboardLayout>
        <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.web.berita.index')" class="w-10 h-10 rounded-xl bg-white border border-slate-200 text-slate-500 flex items-center justify-center hover:bg-slate-50 transition-colors">
                        <i class="fas fa-arrow-left"></i>
                    </Link>
                    <h2 class="font-bold text-2xl text-slate-800 leading-tight">
                        {{ isEdit ? 'Edit Berita' : 'Tulis Berita Baru' }}
                    </h2>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <form @submit.prevent="submit" class="p-6 md:p-8 space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Judul Artikel <span class="text-red-500">*</span></label>
                        <input v-model="form.judul" type="text" required class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-lg font-bold px-4 py-3" placeholder="Masukkan judul berita di sini...">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Isi Konten Berita <span class="text-red-500">*</span></label>
                        <p class="text-xs text-slate-500 mb-2">Gunakan Tag HTML dasar seperti &lt;p&gt;, &lt;b&gt;, &lt;br&gt; jika diperlukan.</p>
                        <textarea v-model="form.isi" required rows="12" class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 p-4 font-mono text-sm" placeholder="Tuliskan isi berita Anda di sini..."></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-slate-50 rounded-xl border border-slate-100">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Gambar Utama (Thumbnail)</label>
                            <input type="file" accept="image/*" @change="handleFileUpload" class="w-full rounded-lg border border-slate-300 p-2 text-sm bg-white">
                            <p class="text-xs text-slate-500 mt-1">Format JPG/PNG/WEBP. Maksimal 2MB.</p>
                            
                            <div v-if="isEdit && props.berita.gambar && props.berita.gambar !== 'default.jpg'" class="mt-3">
                                <span class="text-xs font-bold text-slate-500 block mb-1">Gambar saat ini:</span>
                                <img :src="`/uploads/berita/${props.berita.gambar}`" class="h-24 rounded-lg object-cover border border-slate-200">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Status Publikasi</label>
                            <div class="flex items-center gap-4 mt-3">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" v-model="form.is_published" :value="true" class="text-emerald-600 focus:ring-emerald-500 h-5 w-5 border-slate-300">
                                    <span class="text-sm font-bold text-slate-700">Publik (Terbit)</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" v-model="form.is_published" :value="false" class="text-slate-600 focus:ring-slate-500 h-5 w-5 border-slate-300">
                                    <span class="text-sm font-bold text-slate-500">Draft (Sembunyikan)</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end gap-3 border-t border-slate-100 mt-6">
                        <Link :href="route('admin.web.berita.index')" class="px-6 py-3 text-sm font-bold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">Batal</Link>
                        <button type="submit" :disabled="form.processing" class="px-8 py-3 text-sm font-bold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition-colors flex items-center gap-2">
                            <i v-if="form.processing" class="fas fa-spinner fa-spin"></i>
                            <i v-else class="fas fa-paper-plane"></i>
                            {{ isEdit ? 'Simpan Perubahan' : 'Terbitkan Berita' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>
