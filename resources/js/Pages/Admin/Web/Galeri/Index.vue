<script setup>
import { useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    galeri: Object
});

const isModalOpen = ref(false);

const form = useForm({
    judul: '',
    kategori: 'Kegiatan',
    gambar: null,
});

const submit = () => {
    form.post(route('admin.web.galeri.store'), {
        preserveScroll: true,
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
            alert('Foto berhasil diunggah ke Galeri!');
        }
    });
};

const deleteData = (id) => {
    if (confirm('Yakin ingin menghapus foto ini dari galeri?')) {
        router.delete(route('admin.web.galeri.destroy', id), {
            preserveScroll: true,
        });
    }
};

const handleFileUpload = (event) => {
    form.gambar = event.target.files[0];
};

const formatDate = (dateString) => {
    const options = { day: 'numeric', month: 'short', year: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};
</script>

<template>
    <DashboardLayout>
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="font-bold text-2xl text-slate-800 leading-tight">
                    <i class="fas fa-camera-retro mr-2 text-emerald-600"></i> Galeri Foto
                </h2>
                <button @click="isModalOpen = true" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-xl text-sm shadow-md flex items-center gap-2">
                    <i class="fas fa-upload"></i> Upload Foto
                </button>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden p-6">
                
                <!-- Grid Galeri -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <div v-for="item in galeri.data" :key="item.id" class="group relative rounded-xl overflow-hidden shadow-sm border border-slate-200 bg-slate-100 aspect-square">
                        <img :src="`/uploads/galeri/${item.gambar}`" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        
                        <!-- Hover Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end p-4">
                            <span class="text-emerald-400 text-xs font-bold uppercase tracking-wider mb-1">{{ item.kategori || 'Umum' }}</span>
                            <h4 class="text-white font-bold text-sm leading-tight line-clamp-2 mb-2">{{ item.judul || 'Tanpa Judul' }}</h4>
                            <div class="flex justify-between items-center text-xs text-slate-300">
                                <span>{{ formatDate(item.created_at) }}</span>
                                <button @click="deleteData(item.id)" class="w-8 h-8 rounded-full bg-red-600/80 hover:bg-red-600 text-white flex items-center justify-center transition-colors shadow-lg">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="galeri.data.length === 0" class="py-16 text-center text-slate-500 border-2 border-dashed border-slate-200 rounded-xl mt-4">
                    <div class="text-4xl text-slate-300 mb-3"><i class="fas fa-images"></i></div>
                    Belum ada foto di galeri.
                </div>

                <!-- Pagination -->
                <div v-if="galeri.links && galeri.links.length > 3" class="mt-8 pt-4 border-t border-slate-100 flex justify-center">
                    <div class="flex gap-1">
                        <template v-for="(link, k) in galeri.links" :key="k">
                            <Link v-if="link.url" :href="link.url" v-html="link.label" class="px-3 py-1 rounded-lg text-sm transition-colors" :class="link.active ? 'bg-emerald-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"></Link>
                            <span v-else v-html="link.label" class="px-3 py-1 rounded-lg text-sm text-slate-400"></span>
                        </template>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal Upload -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <h3 class="font-bold text-lg text-slate-800">Upload Foto Galeri</h3>
                    <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times"></i></button>
                </div>
                <form @submit.prevent="submit" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">File Foto (Wajib)</label>
                        <input type="file" required accept="image/*" @change="handleFileUpload" class="w-full rounded-xl border border-slate-300 p-2 text-sm bg-slate-50">
                        <p class="text-xs text-slate-500 mt-1">Gunakan resolusi proporsional, maks 2MB.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Kategori</label>
                        <select v-model="form.kategori" class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                            <option value="Kegiatan">Kegiatan</option>
                            <option value="Fasilitas">Fasilitas</option>
                            <option value="Prestasi">Prestasi</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Judul / Keterangan Singkat</label>
                        <input v-model="form.judul" type="text" placeholder="Misal: Lomba Kompetensi Siswa Tingkat Provinsi" class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                    </div>
                    
                    <div class="pt-4 flex justify-end gap-3 border-t border-slate-100 mt-4">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 text-sm font-bold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-6 py-2 text-sm font-bold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 flex items-center gap-2">
                            <i v-if="form.processing" class="fas fa-spinner fa-spin"></i>
                            <i v-else class="fas fa-upload"></i>
                            Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </DashboardLayout>
</template>
