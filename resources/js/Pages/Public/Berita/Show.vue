<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    web: Object,
    artikel: Object,
    beritaTerkait: Array
});

const formatDate = (dateString) => {
    const options = { day: 'numeric', month: 'long', year: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};

// Open graph data could be computed here for SEO
</script>

<template>
    <Head>
        <title>{{ artikel.judul }} - {{ web?.nama_sekolah || 'SMK' }}</title>
        <meta name="description" :content="artikel.judul" />
    </Head>

    <div class="min-h-screen bg-slate-50 font-sans">
        <!-- Navigation Bar -->
        <nav class="bg-white shadow-sm sticky top-0 z-50 border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
                <Link :href="route('public.berita.index')" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                        <i class="fas fa-arrow-left"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-black text-slate-800 leading-none">PORTAL BERITA</span>
                        <span class="text-xs font-bold text-emerald-600">{{ web?.nama_sekolah || 'SMK' }}</span>
                    </div>
                </Link>
                <div class="hidden sm:flex items-center gap-4">
                    <Link href="/" class="text-sm font-bold text-slate-600 hover:text-emerald-600 transition-colors">Beranda Utama</Link>
                </div>
            </div>
        </nav>

        <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
            <article class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                
                <!-- Header Artikel -->
                <div class="p-6 md:p-10 border-b border-slate-100">
                    <div class="flex items-center gap-2 mb-6">
                        <span class="px-3 py-1 bg-emerald-100 text-emerald-700 text-xs font-black uppercase tracking-wider rounded-md">Berita Sekolah</span>
                        <span class="text-xs font-bold text-slate-400">&bull;</span>
                        <span class="text-xs font-bold text-slate-400">{{ formatDate(artikel.created_at) }}</span>
                    </div>
                    
                    <h1 class="text-3xl md:text-4xl lg:text-5xl font-black text-slate-900 leading-tight mb-6">{{ artikel.judul }}</h1>
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-slate-200 flex items-center justify-center text-slate-500 text-xl overflow-hidden border-2 border-white shadow-sm">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <div class="text-sm font-black text-slate-800">{{ artikel.penulis }}</div>
                                <div class="text-xs text-slate-500 font-medium">Jurnalis Sekolah</div>
                            </div>
                        </div>
                        
                        <div class="flex gap-2">
                            <button class="w-10 h-10 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center hover:bg-slate-200 transition-colors"><i class="fas fa-share-alt"></i></button>
                        </div>
                    </div>
                </div>

                <!-- Gambar Utama -->
                <div v-if="artikel.gambar && artikel.gambar !== 'default.jpg'" class="w-full bg-slate-100 border-b border-slate-100">
                    <img :src="`/uploads/berita/${artikel.gambar}`" class="w-full h-auto max-h-[600px] object-cover" :alt="artikel.judul">
                </div>

                <!-- Isi Artikel -->
                <div class="p-6 md:p-12">
                    <!-- Gunakan v-html untuk render konten dari text editor -->
                    <div class="prose prose-slate prose-lg max-w-none text-slate-700 leading-relaxed font-serif text-lg
                                prose-headings:font-sans prose-headings:font-bold prose-headings:text-slate-900 
                                prose-a:text-emerald-600 prose-a:no-underline hover:prose-a:underline
                                prose-img:rounded-2xl prose-img:shadow-sm" 
                         v-html="artikel.isi">
                    </div>
                    
                    <div class="mt-12 pt-8 border-t border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-2 text-slate-500 text-sm font-bold">
                            <i class="fas fa-eye"></i> Dibaca {{ artikel.views }} kali
                        </div>
                    </div>
                </div>
            </article>

            <!-- Berita Terkait -->
            <div v-if="beritaTerkait && beritaTerkait.length > 0" class="mt-16">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-2xl font-black text-slate-800">Baca Juga</h3>
                    <Link :href="route('public.berita.index')" class="text-sm font-bold text-emerald-600 hover:text-emerald-700">Lihat Semua Berita &rarr;</Link>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <Link v-for="terkait in beritaTerkait" :key="terkait.id" :href="route('public.berita.show', terkait.slug)" class="group">
                        <div class="h-40 bg-slate-200 rounded-2xl overflow-hidden mb-4 relative shadow-sm border border-slate-100">
                            <img v-if="terkait.gambar && terkait.gambar !== 'default.jpg'" :src="`/uploads/berita/${terkait.gambar}`" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <div v-else class="w-full h-full flex items-center justify-center text-slate-400"><i class="fas fa-image text-2xl"></i></div>
                        </div>
                        <h4 class="font-bold text-slate-800 leading-snug group-hover:text-emerald-600 transition-colors line-clamp-2 mb-2">{{ terkait.judul }}</h4>
                        <span class="text-xs font-bold text-slate-400">{{ formatDate(terkait.created_at) }}</span>
                    </Link>
                </div>
            </div>
        </main>
        
        <footer class="bg-slate-950 py-12 border-t border-slate-900 mt-12 text-center text-slate-500 text-sm">
            &copy; {{ new Date().getFullYear() }} {{ web?.nama_sekolah || 'SMK' }}. Hak Cipta Dilindungi.<br>
            Portal Berita Resmi Sekolah
        </footer>
    </div>
</template>
