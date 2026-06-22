<template>
    <Head title="Materi & Bahan Ajar" />

    <DashboardLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Materi & Bahan Ajar
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">Materi Pelajaran</h1>
                    <p class="mt-2 text-sm text-gray-600">Pelajari materi yang telah diunggah oleh guru Anda.</p>
                </div>

                <div v-if="materi.length === 0" class="bg-white rounded-xl shadow-sm p-12 text-center border border-gray-100">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-50 text-blue-500 mb-4">
                        <i class="fas fa-book-open text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Belum Ada Materi</h3>
                    <p class="mt-2 text-sm text-gray-500">Guru belum mengunggah materi untuk kelas Anda.</p>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="item in materi" :key="item.id" class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 overflow-hidden group">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ item.mapel?.nama_mapel || 'Mapel Umum' }}
                                </span>
                                <span class="text-xs text-gray-400 font-medium">
                                    {{ formatDate(item.created_at) }}
                                </span>
                            </div>
                            
                            <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">
                                {{ item.judul }}
                            </h3>
                            
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                                {{ item.deskripsi }}
                            </p>
                            
                            <div class="flex items-center text-sm text-gray-500 mb-6">
                                <i class="fas fa-chalkboard-teacher mr-2"></i>
                                {{ item.guru?.nama_lengkap || 'Guru' }}
                            </div>

                            <div class="flex gap-3">
                                <a v-if="item.file_materi" :href="`/uploads/materi/${item.file_materi}`" target="_blank" class="flex-1 inline-flex justify-center items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors shadow-md shadow-blue-500/30">
                                    <i class="fas fa-download mr-2"></i> Unduh File
                                </a>
                                <a v-if="item.link_youtube" :href="item.link_youtube" target="_blank" class="flex-1 inline-flex justify-center items-center px-4 py-2 bg-red-50 text-red-600 text-sm font-medium rounded-lg hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                                    <i class="fab fa-youtube mr-2"></i> Tonton
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

defineProps({
    materi: {
        type: Array,
        required: true,
    },
});

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
};
</script>
