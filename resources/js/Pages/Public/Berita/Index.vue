<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    web: Object,
    headline: Object,
    berita: Object,
    populer: Array
});

const formatDate = (dateString) => {
    const options = { day: 'numeric', month: 'long', year: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};

const stripHtml = (html) => {
    let doc = new DOMParser().parseFromString(html, 'text/html');
    return doc.body.textContent || "";
};

const truncate = (text, length = 150) => {
    if (!text) return '';
    text = stripHtml(text);
    if (text.length <= length) return text;
    return text.substring(0, length) + '...';
};
</script>

<template>
    <Head>
        <title>Portal Berita - {{ web?.nama_sekolah || 'SMK' }}</title>
        <meta name="description" content="Portal Berita Resmi" />
    </Head>

    <div class="min-h-screen bg-slate-50 font-sans">
        <!-- Navigation Bar -->
        <nav class="bg-white shadow-sm sticky top-0 z-50 border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
                <Link href="/" class="flex items-center gap-3">
                    <img v-if="web.logo" :src="web.logo.includes('default') ? '/images/' + web.logo : '/uploads/identitas/' + web.logo" class="h-10">
                    <div class="flex flex-col">
                        <span class="font-black text-slate-800 leading-none">PORTAL BERITA</span>
                        <span class="text-xs font-bold text-emerald-600">{{ web?.nama_sekolah || 'SMK' }}</span>
                    </div>
                </Link>
                <div class="flex items-center gap-4">
                    <Link href="/" class="text-sm font-bold text-slate-600 hover:text-emerald-600 transition-colors">Kembali ke Beranda</Link>
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Kiri: Headline & Berita Grid -->
                <div class="lg:col-span-2 space-y-8">
                    
                    <!-- Headline News -->
                    <div v-if="headline" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden group">
                        <Link :href="route('public.berita.show', headline.slug)" class="block relative h-72 sm:h-96 w-full bg-slate-900 overflow-hidden">
                            <img v-if="headline.gambar && headline.gambar !== 'default.jpg'" :src="`/uploads/berita/${headline.gambar}`" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 opacity-90">
                            <div v-else class="w-full h-full flex items-center justify-center bg-slate-200 text-slate-400"><i class="fas fa-image text-5xl"></i></div>
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/40 to-transparent"></div>
                            
                            <div class="absolute bottom-0 left-0 w-full p-6 sm:p-8">
                                <span class="px-3 py-1 bg-emerald-600 text-white text-xs font-bold uppercase tracking-wider rounded-md mb-3 inline-block">Sorotan Utama</span>
                                <h2 class="text-2xl sm:text-3xl font-bold text-white leading-tight mb-2 group-hover:text-emerald-400 transition-colors">{{ headline.judul }}</h2>
                                <div class="flex items-center gap-4 text-sm text-slate-300 mt-3">
                                    <span class="flex items-center gap-1"><i class="fas fa-user-circle"></i> {{ headline.penulis }}</span>
                                    <span class="flex items-center gap-1"><i class="fas fa-calendar-alt"></i> {{ formatDate(headline.created_at) }}</span>
                                </div>
                            </div>
                        </Link>
                    </div>

                    <div v-if="!headline && berita.data.length === 0" class="bg-white p-12 rounded-2xl border border-slate-200 text-center text-slate-500">
                        <i class="fas fa-newspaper text-5xl text-slate-300 mb-4"></i>
                        <p>Belum ada berita yang dipublikasikan.</p>
                    </div>

                    <!-- Grid Berita Biasa -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div v-for="item in berita.data" :key="item.id" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-lg transition-all group flex flex-col">
                            <Link :href="route('public.berita.show', item.slug)" class="block relative h-48 bg-slate-200 overflow-hidden">
                                <img v-if="item.gambar && item.gambar !== 'default.jpg'" :src="`/uploads/berita/${item.gambar}`" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                <div v-else class="w-full h-full flex items-center justify-center text-slate-400"><i class="fas fa-image text-3xl"></i></div>
                            </Link>
                            <div class="p-5 flex-1 flex flex-col">
                                <Link :href="route('public.berita.show', item.slug)" class="block">
                                    <h3 class="text-lg font-bold text-slate-800 leading-snug mb-2 group-hover:text-emerald-600 transition-colors line-clamp-2">{{ item.judul }}</h3>
                                </Link>
                                <p class="text-sm text-slate-600 mb-4 line-clamp-3 flex-1">{{ truncate(item.isi) }}</p>
                                <div class="flex items-center justify-between text-xs font-bold text-slate-400 pt-4 border-t border-slate-100 mt-auto">
                                    <span>{{ formatDate(item.created_at) }}</span>
                                    <Link :href="route('public.berita.show', item.slug)" class="text-emerald-600 hover:text-emerald-700">Baca Selengkapnya &rarr;</Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="berita.links && berita.links.length > 3" class="pt-6 flex justify-center">
                        <div class="flex gap-2">
                            <template v-for="(link, k) in berita.links" :key="k">
                                <Link v-if="link.url" :href="link.url" v-html="link.label" class="px-4 py-2 rounded-lg text-sm font-bold shadow-sm border transition-colors" :class="link.active ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"></Link>
                                <span v-else v-html="link.label" class="px-4 py-2 rounded-lg text-sm font-bold border border-slate-100 bg-slate-50 text-slate-400"></span>
                            </template>
                        </div>
                    </div>

                </div>

                <!-- Kanan: Sidebar Populer -->
                <div class="space-y-8">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sticky top-28">
                        <h3 class="text-lg font-black text-slate-800 border-b-2 border-emerald-500 pb-2 mb-6 inline-block">BERITA TERPOPULER</h3>
                        
                        <div class="space-y-6">
                            <Link v-for="(pop, i) in populer" :key="pop.id" :href="route('public.berita.show', pop.slug)" class="flex gap-4 group">
                                <div class="w-20 h-20 shrink-0 rounded-lg overflow-hidden bg-slate-200 relative">
                                    <div class="absolute top-0 left-0 w-6 h-6 bg-emerald-600 text-white flex items-center justify-center text-xs font-black z-10 rounded-br-lg">{{ i+1 }}</div>
                                    <img v-if="pop.gambar && pop.gambar !== 'default.jpg'" :src="`/uploads/berita/${pop.gambar}`" class="w-full h-full object-cover group-hover:scale-110 transition-transform">
                                    <div v-else class="w-full h-full flex items-center justify-center text-slate-400"><i class="fas fa-image"></i></div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-bold text-slate-800 leading-tight group-hover:text-emerald-600 transition-colors line-clamp-2 mb-1">{{ pop.judul }}</h4>
                                    <span class="text-xs font-bold text-slate-400"><i class="fas fa-eye"></i> {{ pop.views }} kali dibaca</span>
                                </div>
                            </Link>
                        </div>

                        <div class="mt-8 pt-6 border-t border-slate-100 text-center">
                            <div class="text-xs font-bold text-slate-400 mb-2">Ikuti Kami di Sosial Media</div>
                            <div class="flex justify-center gap-3">
                                <a v-if="web.link_fb" :href="web.link_fb" target="_blank" class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center hover:bg-blue-700 transition-transform hover:-translate-y-1"><i class="fab fa-facebook-f"></i></a>
                                <a v-if="web.link_ig" :href="web.link_ig" target="_blank" class="w-10 h-10 rounded-full bg-pink-600 text-white flex items-center justify-center hover:bg-pink-700 transition-transform hover:-translate-y-1"><i class="fab fa-instagram"></i></a>
                                <a v-if="web.link_yt" :href="web.link_yt" target="_blank" class="w-10 h-10 rounded-full bg-red-600 text-white flex items-center justify-center hover:bg-red-700 transition-transform hover:-translate-y-1"><i class="fab fa-youtube"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>
        
        <footer class="bg-slate-950 py-8 border-t border-slate-900 mt-12 text-center text-slate-500 text-sm">
            &copy; {{ new Date().getFullYear() }} {{ web?.nama_sekolah || 'SMK' }}. Hak Cipta Dilindungi.
        </footer>
    </div>
</template>
