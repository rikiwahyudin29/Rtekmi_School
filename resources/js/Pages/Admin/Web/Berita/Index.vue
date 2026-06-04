<script setup>
import { router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    berita: Object
});

const deleteData = (id) => {
    if (confirm('Yakin ingin menghapus berita ini?')) {
        router.delete(route('admin.web.berita.destroy', id), {
            preserveScroll: true,
        });
    }
};

const formatDate = (dateString) => {
    const options = { day: 'numeric', month: 'long', year: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};
</script>

<template>
    <DashboardLayout>
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="font-bold text-2xl text-slate-800 leading-tight">
                    <i class="fas fa-newspaper mr-2 text-emerald-600"></i> Berita & Artikel
                </h2>
                <Link :href="route('admin.web.berita.create')" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-xl text-sm shadow-md flex items-center gap-2 transition-all">
                    <i class="fas fa-pen"></i> Tulis Berita
                </Link>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-sm font-bold text-slate-600">
                                <th class="p-4 w-16 text-center">No</th>
                                <th class="p-4 w-32">Gambar</th>
                                <th class="p-4">Judul & Info</th>
                                <th class="p-4 w-32 text-center">Status</th>
                                <th class="p-4 w-32 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm text-slate-700">
                            <tr v-for="(item, index) in berita.data" :key="item.id" class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                <td class="p-4 text-center">{{ index + 1 + (berita.current_page - 1) * berita.per_page }}</td>
                                <td class="p-4">
                                    <div class="h-16 w-24 bg-slate-200 rounded-lg overflow-hidden relative">
                                        <img v-if="item.gambar && item.gambar !== 'default.jpg'" :src="`/uploads/berita/${item.gambar}`" class="w-full h-full object-cover">
                                        <div v-else class="w-full h-full flex items-center justify-center text-slate-400">
                                            <i class="fas fa-image text-xl"></i>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <div class="font-bold text-slate-800 text-base mb-1">{{ item.judul }}</div>
                                    <div class="flex items-center gap-3 text-xs text-slate-500">
                                        <span><i class="fas fa-user mr-1"></i> {{ item.penulis }}</span>
                                        <span><i class="fas fa-calendar-alt mr-1"></i> {{ formatDate(item.created_at) }}</span>
                                        <span><i class="fas fa-eye mr-1"></i> {{ item.views }} views</span>
                                    </div>
                                </td>
                                <td class="p-4 text-center">
                                    <span v-if="item.is_published" class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded-md text-xs font-bold">Publik</span>
                                    <span v-else class="px-2 py-1 bg-slate-100 text-slate-600 rounded-md text-xs font-bold">Draft</span>
                                </td>
                                <td class="p-4">
                                    <div class="flex justify-center gap-2">
                                        <Link :href="route('admin.web.berita.edit', item.id)" class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center hover:bg-indigo-600 hover:text-white transition-colors">
                                            <i class="fas fa-edit"></i>
                                        </Link>
                                        <button @click="deleteData(item.id)" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition-colors">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="berita.data.length === 0">
                                <td colspan="5" class="p-8 text-center text-slate-500">
                                    <div class="text-4xl text-slate-300 mb-2"><i class="fas fa-folder-open"></i></div>
                                    Belum ada berita yang dipublikasikan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="berita.links && berita.links.length > 3" class="p-4 border-t border-slate-100 flex justify-center">
                    <div class="flex gap-1">
                        <template v-for="(link, k) in berita.links" :key="k">
                            <Link v-if="link.url" :href="link.url" v-html="link.label" class="px-3 py-1 rounded-lg text-sm transition-colors" :class="link.active ? 'bg-emerald-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"></Link>
                            <span v-else v-html="link.label" class="px-3 py-1 rounded-lg text-sm text-slate-400"></span>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
