<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import axios from 'axios';

const props = defineProps({
    buku: Array,
});

const isModalOpen = ref(false);
const searchQuery = ref('');

const form = useForm({
    judul: '',
    kategori: '',
    penulis: '',
    ebook: null,
    cover: null,
});

const openModal = () => {
    form.reset();
    isModalOpen.value = true;
};

const handleEbookChange = (e) => {
    form.ebook = e.target.files[0];
};

const handleCoverChange = (e) => {
    form.cover = e.target.files[0];
};

const submit = () => {
    form.post(route('guru.elearning.perpustakaan.store'), {
        onSuccess: () => {
            isModalOpen.value = false;
        }
    });
};

const deleteBuku = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus buku ini?')) {
        router.delete(route('guru.elearning.perpustakaan.destroy', id));
    }
};

const filteredBuku = computed(() => {
    if (!searchQuery.value) return props.buku;
    const lowerQuery = searchQuery.value.toLowerCase();
    return props.buku.filter(b => 
        b.judul.toLowerCase().includes(lowerQuery) || 
        (b.penulis && b.penulis.toLowerCase().includes(lowerQuery)) ||
        (b.kategori && b.kategori.toLowerCase().includes(lowerQuery))
    );
});

const openBuku = (buku) => {
    axios.post(route('guru.elearning.perpustakaan.counter', buku.id)).then(() => {
        window.open(`/uploads/library/${buku.file_ebook}`, '_blank');
    });
};
</script>

<template>
    <Head title="Perpustakaan Digital" />

    <DashboardLayout>
        <div class="p-6">
            <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-gray-100 dark:border-gray-700 pb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center">
                            <i class="fas fa-book-reader"></i>
                        </div>
                        Perpustakaan Digital
                    </h1>
                    <p class="text-gray-500 mt-1 dark:text-gray-400">Kelola dan unggah modul atau E-Book pembelajaran.</p>
                </div>
                
                <div class="flex gap-3 items-center w-full md:w-auto">
                    <div class="relative w-full md:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input v-model="searchQuery" type="text" class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-gray-900 dark:text-white text-sm rounded-xl focus:ring-orange-500 focus:border-orange-500 block w-full pl-10 p-2.5 shadow-sm" placeholder="Cari buku atau penulis...">
                    </div>
                    <button @click="openModal" class="shrink-0 px-5 py-2.5 bg-orange-500 text-white font-medium rounded-xl hover:bg-orange-600 shadow-md shadow-orange-500/20 transition-all flex items-center gap-2">
                        <i class="fas fa-upload"></i> <span class="hidden sm:inline">Upload Buku</span>
                    </button>
                </div>
            </div>

            <!-- List Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                <div v-for="b in filteredBuku" :key="b.id" class="group bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 relative flex flex-col">
                    
                    <!-- Cover Area -->
                    <div class="relative aspect-[3/4] bg-gray-100 dark:bg-slate-700 overflow-hidden cursor-pointer" @click="openBuku(b)">
                        <img v-if="b.cover && b.cover !== 'default_book.png'" :src="`/uploads/library/covers/${b.cover}`" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" alt="Cover" />
                        <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-orange-100 to-amber-50 dark:from-slate-700 dark:to-slate-600">
                            <i class="fas fa-book text-5xl text-orange-200 dark:text-slate-500"></i>
                        </div>

                        <!-- Hover Overlay -->
                        <div class="absolute inset-0 bg-gray-900/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center backdrop-blur-[2px]">
                            <button class="w-12 h-12 bg-white text-orange-600 rounded-full flex items-center justify-center shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-all duration-300">
                                <i class="fas fa-book-open"></i>
                            </button>
                        </div>
                        
                        <div class="absolute top-2 right-2 flex flex-col gap-1">
                            <button @click.stop="deleteBuku(b.id)" class="w-8 h-8 bg-white/90 backdrop-blur-sm text-red-500 rounded-lg flex items-center justify-center hover:bg-red-500 hover:text-white transition-colors opacity-0 group-hover:opacity-100">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Detail Area -->
                    <div class="p-4 flex-1 flex flex-col">
                        <div class="text-xs font-bold text-orange-600 dark:text-orange-400 mb-1 uppercase tracking-wider">{{ b.kategori || 'Umum' }}</div>
                        <h3 class="text-sm font-bold text-gray-800 dark:text-white line-clamp-2 leading-snug mb-1" :title="b.judul">{{ b.judul }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate mb-3"><i class="fas fa-user-edit mr-1"></i> {{ b.penulis || '-' }}</p>
                        
                        <div class="mt-auto pt-3 border-t border-gray-50 dark:border-slate-700/50 flex justify-between items-center text-xs text-gray-400">
                            <span class="flex items-center gap-1"><i class="fas fa-eye"></i> {{ b.diakses || 0 }}x dibaca</span>
                            <span class="flex items-center gap-1"><i class="fas fa-file-pdf text-red-400"></i> PDF</span>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="filteredBuku.length === 0" class="col-span-full py-16 flex flex-col items-center justify-center bg-white dark:bg-slate-800 rounded-2xl border border-dashed border-gray-200 dark:border-slate-700">
                    <div class="w-20 h-20 bg-gray-50 dark:bg-slate-700 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-book text-3xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">Belum ada buku</h3>
                    <p class="text-gray-500 text-sm mt-1">Rak buku digital Anda masih kosong.</p>
                </div>
            </div>
        </div>

        <!-- Upload Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-gray-900/50 backdrop-blur-sm p-4">
            <div class="relative w-full max-w-lg bg-white dark:bg-slate-800 rounded-2xl shadow-2xl animate-fade-in-up">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-5 border-b border-gray-100 dark:border-slate-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-upload text-orange-500"></i> Upload E-Book
                    </h3>
                    <button @click="isModalOpen = false" class="text-gray-400 bg-transparent hover:bg-gray-100 hover:text-gray-900 rounded-lg text-sm w-8 h-8 flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <!-- Form -->
                <form @submit.prevent="submit" class="p-5">
                    <div class="space-y-4">
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-gray-900 dark:text-white">Judul Buku</label>
                            <input v-model="form.judul" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5 dark:bg-slate-700 dark:border-slate-600 dark:placeholder-gray-400 dark:text-white" required placeholder="Judul buku / modul">
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block mb-2 text-sm font-semibold text-gray-900 dark:text-white">Penulis</label>
                                <input v-model="form.penulis" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5 dark:bg-slate-700 dark:border-slate-600 dark:text-white" required placeholder="Nama penulis">
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-semibold text-gray-900 dark:text-white">Kategori</label>
                                <input v-model="form.kategori" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5 dark:bg-slate-700 dark:border-slate-600 dark:text-white" required placeholder="Buku Paket / Modul dll">
                            </div>
                        </div>

                        <div class="mt-4 bg-orange-50 dark:bg-slate-700/50 p-4 rounded-xl border border-orange-100 dark:border-slate-600">
                            <label class="block mb-2 text-sm font-semibold text-gray-900 dark:text-white">File E-Book (PDF) <span class="text-red-500">*</span></label>
                            <input @change="handleEbookChange" type="file" accept=".pdf" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-white dark:text-gray-400 focus:outline-none dark:bg-slate-700 dark:border-slate-600 dark:placeholder-gray-400" required>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Maksimal 10MB. Format harus PDF.</p>
                        </div>

                        <div class="mt-4 bg-gray-50 dark:bg-slate-700/50 p-4 rounded-xl border border-gray-100 dark:border-slate-600">
                            <label class="block mb-2 text-sm font-semibold text-gray-900 dark:text-white">Cover Buku (Opsional)</label>
                            <input @change="handleCoverChange" type="file" accept="image/*" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-white dark:text-gray-400 focus:outline-none dark:bg-slate-700 dark:border-slate-600 dark:placeholder-gray-400">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Gambar rasio potret (3:4) direkomendasikan.</p>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex items-center justify-end gap-3 pt-5 border-t border-gray-100 dark:border-slate-700">
                        <button type="button" @click="isModalOpen = false" class="px-5 py-2.5 text-sm font-medium text-gray-500 bg-white border border-gray-200 rounded-xl hover:bg-gray-100 hover:text-gray-900 dark:bg-slate-700 dark:text-gray-300 dark:border-slate-600 dark:hover:bg-slate-600 dark:hover:text-white">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2.5 text-sm font-medium text-white bg-orange-500 rounded-xl hover:bg-orange-600 flex items-center gap-2 shadow-md shadow-orange-500/20 disabled:opacity-70">
                            <i class="fas fa-upload" :class="{'animate-bounce': form.processing}"></i> 
                            {{ form.processing ? 'Mengunggah...' : 'Upload Buku' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>

<style scoped>
.animate-fade-in-up {
    animation: fadeInUp 0.3s ease-out;
}
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px) scale(0.98);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
</style>
