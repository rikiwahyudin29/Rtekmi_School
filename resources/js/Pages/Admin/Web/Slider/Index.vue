<script setup>
import { useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    sliders: Array
});

const isModalOpen = ref(false);

const form = useForm({
    judul: '',
    deskripsi: '',
    gambar: null,
});

const submit = () => {
    form.post(route('web.slider.store'), {
        preserveScroll: true,
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
            alert('Slider berhasil ditambahkan!');
        }
    });
};

const deleteData = (id) => {
    if (confirm('Yakin ingin menghapus slider ini?')) {
        router.delete(route('web.slider.destroy', id), {
            preserveScroll: true,
        });
    }
};

const handleFileUpload = (event) => {
    form.gambar = event.target.files[0];
};
</script>

<template>
    <DashboardLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-bold text-xl text-slate-800 leading-tight">
                    <i class="fas fa-images mr-2 text-emerald-600"></i> Slider Banner Depan
                </h2>
                <button @click="isModalOpen = true" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-xl text-sm shadow-md flex items-center gap-2">
                    <i class="fas fa-plus"></i> Tambah Banner
                </button>
            </div>
        </template>

        <div class="max-w-7xl mx-auto py-8">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-6">
                    <p class="text-sm text-slate-500 mb-6">Kelola foto-foto yang akan muncul sebagai background slider di halaman depan website.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="item in sliders" :key="item.id" class="border border-slate-200 rounded-xl overflow-hidden flex flex-col relative group">
                            
                            <button @click="deleteData(item.id)" class="absolute top-2 right-2 w-8 h-8 rounded-full bg-red-600/80 backdrop-blur text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity z-10 hover:bg-red-700">
                                <i class="fas fa-trash text-sm"></i>
                            </button>

                            <div class="h-40 w-full bg-slate-900 relative">
                                <img v-if="item.gambar" :src="`/uploads/slider/${item.gambar}`" class="w-full h-full object-cover opacity-80 mix-blend-luminosity">
                            </div>
                            <div class="p-4 bg-white">
                                <h4 class="font-bold text-slate-800 text-sm mb-1 truncate">{{ item.judul || 'Tanpa Judul' }}</h4>
                            </div>
                        </div>

                        <div v-if="sliders.length === 0" class="col-span-full py-12 text-center text-slate-500 border-2 border-dashed border-slate-200 rounded-xl">
                            Belum ada slider yang ditambahkan.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Tambah -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <h3 class="font-bold text-lg text-slate-800">Tambah Slider</h3>
                    <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times"></i></button>
                </div>
                <form @submit.prevent="submit" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Judul Banner</label>
                        <input v-model="form.judul" type="text" class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">File Gambar (Wajib)</label>
                        <input type="file" required accept="image/*" @change="handleFileUpload" class="w-full rounded-xl border border-slate-300 p-2 text-sm bg-slate-50">
                        <p class="text-[10px] text-slate-500 mt-1">Disarankan gambar landscape ukuran 1920x1080 (HD).</p>
                    </div>
                    
                    <div class="pt-4 flex justify-end gap-3">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 text-sm font-bold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-4 py-2 text-sm font-bold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 flex items-center gap-2">
                            <i v-if="form.processing" class="fas fa-spinner fa-spin"></i>
                            Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </DashboardLayout>
</template>
