<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    web: Object,
    sliders: Array,
    berita: Array,
    galeri: Array,
    stats: Object,
    jurusanList: Array // New Prop for SMK Majors
});

// Setup Logo
const pathLogo = props.web.logo ? (props.web.logo.includes('default') ? '/images/' + props.web.logo : '/uploads/identitas/' + props.web.logo) : 'https://flowbite.com/docs/images/logo.svg';

// Process YouTube Link
const yt_embed = computed(() => {
    const url = props.web.link_yt;
    if (!url) return '';
    let matches = url.match(/youtu\.be\/([a-zA-Z0-9_-]+)/);
    if (matches) return `https://www.youtube.com/embed/${matches[1]}`;
    matches = url.match(/v=([a-zA-Z0-9_-]+)/);
    if (matches) return `https://www.youtube.com/embed/${matches[1]}`;
    return url;
});

// Format Date
const formatDate = (dateString) => {
    const options = { day: 'numeric', month: 'short', year: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};

// Navbar Scroll Effect
const isScrolled = ref(false);
const handleScroll = () => {
    isScrolled.value = window.scrollY > 50;
};

// Mobile Menu
const isMobileMenuOpen = ref(false);

// Carousel
const activeSlide = ref(0);
let carouselInterval = null;
const nextSlide = () => {
    if (!props.sliders || props.sliders.length === 0) return;
    activeSlide.value = (activeSlide.value + 1) % props.sliders.length;
};

// Lightbox
const isLightboxOpen = ref(false);
const lightboxImage = ref('');
const openLightbox = (imgSrc) => {
    lightboxImage.value = imgSrc;
    isLightboxOpen.value = true;
};
const closeLightbox = () => {
    isLightboxOpen.value = false;
};

// Modal Tabungan (Bank Mini)
const isModalOpen = ref(false);
const modalStep = ref(1); // 1: NISN, 2: PIN, 3: Result
const formTabungan = ref({
    nisn: '',
    pin: ''
});
const siswaInfo = ref({ nama: '', nisn: '' });
const saldoResult = ref('Rp 0');
const isLoading = ref(false);
const errorMessage = ref('');

const resetModal = () => {
    isModalOpen.value = false;
    setTimeout(() => {
        modalStep.value = 1;
        formTabungan.value.nisn = '';
        formTabungan.value.pin = '';
        errorMessage.value = '';
    }, 300);
};

const goToStep = (step) => {
    modalStep.value = step;
    errorMessage.value = '';
};

const checkNISN = async () => {
    if (!formTabungan.value.nisn) return;
    isLoading.value = true;
    errorMessage.value = '';
    try {
        const res = await axios.post('/api/cek-saldo', { nisn: formTabungan.value.nisn });
        if (res.data.success) {
            siswaInfo.value.nama = res.data.data.nama_lengkap;
            siswaInfo.value.nisn = res.data.data.nisn;
            goToStep(2);
        } else {
            errorMessage.value = res.data.message;
        }
    } catch (e) {
        errorMessage.value = "Terjadi kesalahan sistem.";
    }
    isLoading.value = false;
};

const checkPINAndSaldo = async () => {
    if (!formTabungan.value.pin) return;
    isLoading.value = true;
    errorMessage.value = '';
    try {
        const res = await axios.post('/api/cek-saldo', { 
            nisn: formTabungan.value.nisn, 
            pin: formTabungan.value.pin 
        });
        if (res.data.success) {
            saldoResult.value = res.data.saldo;
            goToStep(3);
        } else {
            errorMessage.value = res.data.message;
            formTabungan.value.pin = '';
        }
    } catch (e) {
        errorMessage.value = "Terjadi kesalahan sistem.";
    }
    isLoading.value = false;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    if (props.sliders && props.sliders.length > 1) {
        carouselInterval = setInterval(nextSlide, 6000);
    }
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    if (carouselInterval) clearInterval(carouselInterval);
});

const mitraIndustri = [
    { nama: 'PT Astra Honda Motor', icon: 'fa-cogs', color: 'text-red-500' },
    { nama: 'Yamaha Indonesia', icon: 'fa-motorcycle', color: 'text-blue-600' },
    { nama: 'Telkom Indonesia', icon: 'fa-satellite-dish', color: 'text-red-600' },
    { nama: 'PLN Persero', icon: 'fa-bolt', color: 'text-yellow-500' },
    { nama: 'Bank BNI', icon: 'fa-building-columns', color: 'text-orange-500' },
    { nama: 'Epson Indonesia', icon: 'fa-print', color: 'text-blue-500' },
    { nama: 'United Tractors', icon: 'fa-truck-monster', color: 'text-yellow-600' },
    { nama: 'PT Panasonic Gobel', icon: 'fa-tv', color: 'text-blue-700' },
];

const fallbackJurusan = [
    { nama_jurusan: 'Teknik Kendaraan Ringan', ikon: 'fa-car-side', singkatan: 'TKR' },
    { nama_jurusan: 'Teknik Komputer & Jaringan', ikon: 'fa-network-wired', singkatan: 'TKJ' },
    { nama_jurusan: 'Rekayasa Perangkat Lunak', ikon: 'fa-laptop-code', singkatan: 'RPL' },
    { nama_jurusan: 'Akuntansi Keuangan', ikon: 'fa-calculator', singkatan: 'AKL' },
];

const jurusanDisplay = computed(() => {
    if (props.jurusanList && props.jurusanList.length > 0) {
        // Map icon dynamically or use default
        return props.jurusanList.map(j => ({
            nama_jurusan: j.nama_jurusan,
            singkatan: j.kode_jurusan || j.singkatan || 'PRODI',
            ikon: j.ikon || 'fa-cogs' // fallback icon
        }));
    }
    return fallbackJurusan;
});
</script>

<style>
/* Font imported in html but defined here for safety */
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');

.font-jakarta {
    font-family: 'Plus Jakarta Sans', sans-serif;
}

/* Navbar Transition */
.nav-transparent { 
    background-color: transparent; 
    padding-top: 1.25rem; 
    padding-bottom: 1.25rem; 
}
.nav-scrolled { 
    background-color: rgba(15, 23, 42, 0.95); /* slate-900 */
    backdrop-filter: blur(12px); 
    box-shadow: 0 4px 20px -5px rgba(0, 0, 0, 0.5); 
    padding-top: 0.75rem; 
    padding-bottom: 0.75rem; 
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.nav-link { position: relative; font-weight: 700; font-size: 0.95rem; }
.nav-link::after {
    content: ''; position: absolute; width: 0; height: 2px; bottom: -5px; left: 0;
    background-color: #fbbf24; /* amber-400 */
    transition: width 0.3s;
}
.nav-link:hover::after { width: 100%; }

/* Marquee for Mitra */
@keyframes marquee {
  0% { transform: translateX(0); }
  100% { transform: translateX(-50%); }
}
.animate-marquee {
  display: inline-flex;
  animation: marquee 30s linear infinite;
  white-space: nowrap;
}
.animate-marquee:hover {
  animation-play-state: paused;
}

/* Lightbox Animation */
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.zoom-enter-active, .zoom-leave-active { transition: transform 0.3s; }
.zoom-enter-from, .zoom-leave-to { transform: scale(0.9); }

.bg-tech-pattern {
    background-image: radial-gradient(rgba(16, 185, 129, 0.15) 1px, transparent 1px);
    background-size: 30px 30px;
}

.hex-clip {
    clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
}

input[type="number"]::-webkit-outer-spin-button, input[type="number"]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
input[type="number"] { -moz-appearance: textfield; }
</style>

<template>
    <Head :title="`${web.nama_sekolah || 'SIAKAD'} | Vokasi Siap Kerja`" />

    <div class="font-jakarta text-slate-300 bg-slate-900 overflow-x-hidden">
        
        <!-- Navbar -->
        <nav :class="['fixed w-full z-50 top-0 start-0 transition-all duration-300 ease-in-out', isScrolled ? 'nav-scrolled' : 'nav-transparent']">
            <div class="max-w-screen-xl flex items-center justify-between mx-auto p-4 md:px-8">
                <!-- LOGO -->
                <a href="#" class="flex items-center space-x-3 flex-shrink-0 group">
                    <div class="relative">
                        <div class="absolute inset-0 bg-amber-400 blur group-hover:blur-md transition-all rounded-full opacity-50"></div>
                        <img :src="pathLogo" class="relative h-10 w-10 md:h-12 md:w-12 object-contain bg-white rounded-full p-1 border-2 border-emerald-500" alt="Logo">
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm sm:text-lg md:text-xl font-black text-white tracking-tight uppercase group-hover:text-amber-400 transition-colors">
                            {{ web.nama_sekolah || 'SMK BISA' }}
                        </span>
                        <span class="text-[9px] sm:text-[10px] text-emerald-400 font-bold uppercase tracking-widest hidden sm:block">Pusat Keunggulan (Center of Excellence)</span>
                    </div>
                </a>
                
                <!-- MENU DESKTOP -->
                <div class="hidden xl:flex items-center justify-center flex-1 mx-4">
                    <ul class="flex space-x-6 items-center">
                        <li><a href="#home" class="nav-link transition-colors text-white hover:text-amber-400">Beranda</a></li>
                        <li><a href="#jurusan" class="nav-link transition-colors text-white hover:text-amber-400">Program Keahlian</a></li>
                        <li><a href="#mitra" class="nav-link transition-colors text-white hover:text-amber-400">Mitra BKK</a></li>
                        <li><a href="#spmb" class="nav-link transition-colors text-emerald-400 hover:text-amber-400">PPDB</a></li>
                        <li><a href="#berita" class="nav-link transition-colors text-white hover:text-amber-400">Berita</a></li>
                        <li><a href="#galeri" class="nav-link transition-colors text-white hover:text-amber-400">Galeri</a></li>
                    </ul>
                </div>

                <!-- TOMBOL KANAN -->
                <div class="flex items-center gap-2 md:gap-3 flex-shrink-0">
                    <Link href="/tracer" class="hidden lg:flex items-center gap-1.5 text-amber-400 hover:text-amber-300 font-bold text-sm px-2">
                        <i class="fas fa-search-location"></i> Tracer
                    </Link>

                    <button @click="isModalOpen = true" class="hidden md:flex items-center gap-2 py-2 px-4 rounded-xl bg-slate-800 text-emerald-400 font-bold hover:bg-slate-700 transition-all border border-slate-700 hover:border-emerald-500 text-sm">
                        <i class="fas fa-wallet"></i> Bank Mini
                    </button>
                    
                    <Link href="/login" class="hidden md:flex items-center gap-2 text-slate-900 bg-amber-400 hover:bg-amber-300 focus:ring-4 focus:outline-none focus:ring-amber-500/50 font-black rounded-xl px-5 py-2 transition-all hover:-translate-y-0.5 text-sm uppercase tracking-wide">
                        <i class="fas fa-fingerprint"></i> Portal
                    </Link>
                    
                    <!-- Mobile Toggle -->
                    <button @click="isMobileMenuOpen = !isMobileMenuOpen" type="button" class="xl:hidden inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-white rounded-lg bg-emerald-600/30 backdrop-blur-sm border border-emerald-400/30">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- MOBILE MENU -->
            <transition name="fade">
                <div v-if="isMobileMenuOpen" class="xl:hidden bg-slate-900/95 backdrop-blur-xl shadow-2xl border-t border-slate-800 absolute w-full left-0 top-full pb-4">
                    <ul class="flex flex-col px-4 pt-4 space-y-2 font-bold text-white text-sm tracking-wide">
                        <li><a @click="isMobileMenuOpen=false" href="#home" class="block px-4 py-2 hover:bg-slate-800 rounded-lg">Beranda</a></li>
                        <li><a @click="isMobileMenuOpen=false" href="#jurusan" class="block px-4 py-2 hover:bg-slate-800 rounded-lg">Program Keahlian</a></li>
                        <li><a @click="isMobileMenuOpen=false" href="#mitra" class="block px-4 py-2 hover:bg-slate-800 rounded-lg">Mitra Industri / BKK</a></li>
                        <li><a @click="isMobileMenuOpen=false" href="#spmb" class="block px-4 py-2 text-emerald-400 hover:bg-slate-800 rounded-lg">Info PPDB</a></li>
                        <li><a @click="isMobileMenuOpen=false" href="#berita" class="block px-4 py-2 hover:bg-slate-800 rounded-lg">Berita & Artikel</a></li>
                        <li><a @click="isMobileMenuOpen=false" href="#galeri" class="block px-4 py-2 hover:bg-slate-800 rounded-lg">Galeri Dokumentasi</a></li>
                        <li>
                            <Link href="/tracer" class="block text-amber-400 px-4 py-2 hover:bg-slate-800 rounded-lg">
                                <i class="fas fa-search-location mr-2"></i> Tracer Alumni
                            </Link>
                        </li>
                        <li class="pt-4 border-t border-slate-800 flex flex-col gap-3 px-2">
                            <button @click="isModalOpen = true; isMobileMenuOpen = false" class="w-full text-center py-3 rounded-xl bg-slate-800 text-emerald-400 font-bold border border-slate-700">
                                <i class="fas fa-wallet mr-2"></i> Cek Saldo Bank Mini
                            </button>
                            <Link href="/login" class="block w-full text-center text-slate-900 bg-amber-400 font-black rounded-xl py-3 uppercase tracking-wider">
                                <i class="fas fa-sign-in-alt mr-2"></i> Login Portal
                            </Link>
                        </li>
                    </ul>
                </div>
            </transition>
        </nav>

        <!-- Tech/SMK Hero Section -->
        <section id="home" class="relative min-h-screen flex items-center justify-center pt-20 overflow-hidden bg-slate-950">
            <!-- Dynamic Background Slider -->
            <div class="absolute inset-0 z-0 w-full h-full">
                <template v-if="sliders && sliders.length > 0">
                    <transition-group name="fade" tag="div">
                        <div v-for="(slide, index) in sliders" :key="slide.id" v-show="activeSlide === index" class="absolute inset-0 duration-1000 ease-in-out">
                            <img :src="`/uploads/slider/${slide.gambar}`" class="absolute block w-full h-full object-cover opacity-30 mix-blend-luminosity" :alt="slide.judul">
                        </div>
                    </transition-group>
                </template>
                <template v-else>
                    <img src="https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?q=80&w=2070&auto=format&fit=crop" class="absolute block w-full h-full object-cover opacity-30 mix-blend-luminosity" alt="Tech Background">
                </template>
                <!-- Heavy Tech Gradient Overlay -->
                <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-900/90 to-emerald-950/40"></div>
                <div class="absolute inset-0 bg-tech-pattern opacity-20 pointer-events-none"></div>
            </div>

            <!-- Glowing Orbs -->
            <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-emerald-600 rounded-full mix-blend-multiply filter blur-[128px] opacity-40 animate-pulse"></div>

            <div class="relative z-20 px-4 md:px-8 mx-auto max-w-7xl w-full grid grid-cols-1 lg:grid-cols-12 gap-8 items-center pt-10">
                
                <!-- Left: Text Content -->
                <div class="lg:col-span-7 flex flex-col items-center lg:items-start text-center lg:text-left space-y-6">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-emerald-500/30 bg-emerald-900/30 text-emerald-400 text-[10px] md:text-xs font-bold uppercase tracking-widest backdrop-blur-md">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        SMK Pusat Keunggulan
                    </div>
                    
                    <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-black tracking-tight leading-none text-white drop-shadow-2xl relative z-10">
                        SMK <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-teal-200">BISA</span><br>
                        SMK <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-300 to-yellow-500">HEBAT</span>
                    </h1>
                    
                    <p class="text-base md:text-lg font-medium text-slate-300 max-w-xl leading-relaxed relative z-10">
                        {{ web.deskripsi_hero || 'Mencetak lulusan kompeten, siap kerja, santun, mandiri, dan kreatif. Dibekali skill industri terkini dan karakter profesional.' }}
                    </p>
                    
                    <div class="flex flex-col sm:flex-row items-center gap-4 pt-4 w-full lg:w-auto relative z-10">
                        <Link href="/spmb/register" class="w-full sm:w-auto inline-flex justify-center items-center py-3.5 md:py-4 px-8 md:px-10 text-sm md:text-base font-black text-slate-900 uppercase tracking-widest rounded-xl bg-amber-400 hover:bg-amber-300 focus:ring-4 focus:ring-amber-500/30 transition-all shadow-lg shadow-amber-500/20 transform hover:-translate-y-1">
                            Daftar PPDB <i class="fas fa-rocket ml-3"></i>
                        </Link>
                        <a href="#jurusan" class="w-full sm:w-auto inline-flex justify-center items-center py-3.5 md:py-4 px-8 md:px-10 text-sm md:text-base font-bold text-white rounded-xl border border-slate-600 hover:bg-slate-800 hover:border-emerald-500 transition-all backdrop-blur-md">
                            Lihat Jurusan <i class="fas fa-arrow-down ml-3 text-emerald-400"></i>
                        </a>
                    </div>
                    
                    <!-- Stats Badges (Moved under text for better layout) -->
                    <div class="grid grid-cols-2 gap-4 mt-8 w-full max-w-md relative z-10">
                        <div class="bg-slate-900/60 backdrop-blur-md border border-slate-700 p-4 rounded-xl shadow-xl flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-emerald-900/50 flex items-center justify-center text-emerald-400 text-xl border border-emerald-500/30 shrink-0">
                                <i class="fas fa-users-cog"></i>
                            </div>
                            <div class="text-left">
                                <h4 class="text-2xl font-black text-white leading-none">{{ stats.siswa || 0 }}</h4>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Siswa Aktif</p>
                            </div>
                        </div>
                        <div class="bg-slate-900/60 backdrop-blur-md border border-slate-700 p-4 rounded-xl shadow-xl flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-amber-900/50 flex items-center justify-center text-amber-400 text-xl border border-amber-500/30 shrink-0">
                                <i class="fas fa-industry"></i>
                            </div>
                            <div class="text-left">
                                <h4 class="text-2xl font-black text-white leading-none">{{ stats.jurusan || 4 }}</h4>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Program</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Transparent PNG Spot -->
                <div class="lg:col-span-5 relative hidden md:block h-full min-h-[500px]">
                    <!-- Glowing Background for the PNG -->
                    <div class="absolute bottom-0 right-0 w-full h-[80%] bg-emerald-500/20 rounded-full blur-3xl"></div>
                    <div class="absolute bottom-10 left-10 w-48 h-48 bg-amber-500/20 rounded-full blur-2xl"></div>
                    
                    <!-- Spot Foto PNG -->
                    <div class="absolute bottom-0 right-0 w-[120%] max-w-[600px] transform translate-x-12">
                        <!-- Nanti user tinggal ganti file gambar-siswa.png di public/images -->
                        <!-- Fallback ke placeholder online transparan jika local belum ada -->
                        <img src="https://raw.githubusercontent.com/Rikiwahyudin29/assets/main/siswa-smk-praktek.png" 
                             onerror="this.src='https://www.pngmart.com/files/22/Student-PNG-Photos.png'" 
                             alt="Siswa Praktek" 
                             class="w-full h-auto object-contain drop-shadow-[0_20px_30px_rgba(0,0,0,0.5)] z-20 relative">
                        
                        <!-- Accent Elements Floating Around the PNG -->
                        <div class="absolute top-1/4 right-10 bg-slate-800/80 backdrop-blur-md p-3 rounded-xl border border-slate-600 shadow-xl z-30 animate-bounce" style="animation-duration: 3s;">
                            <i class="fas fa-laptop-code text-amber-400 text-xl"></i>
                        </div>
                        <div class="absolute top-1/2 left-0 bg-slate-800/80 backdrop-blur-md p-3 rounded-xl border border-slate-600 shadow-xl z-30 animate-bounce" style="animation-duration: 4s;">
                            <i class="fas fa-cogs text-emerald-400 text-xl"></i>
                        </div>
                    </div>
                </div>
                
            </div>
            
            <!-- Bottom Fade -->
            <div class="absolute bottom-0 left-0 w-full h-40 bg-gradient-to-t from-slate-900 to-transparent z-30 pointer-events-none"></div>
        </section>

        <!-- Program Keahlian (Jurusan) Section -->
        <section id="jurusan" class="py-20 relative bg-slate-900 border-b border-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <span class="text-emerald-500 font-bold uppercase tracking-widest text-xs mb-3 block">Kompetensi Keahlian</span>
                    <h2 class="text-3xl md:text-5xl font-black text-white">Program <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-300 to-yellow-500">Studi</span></h2>
                    <p class="mt-4 text-slate-400 max-w-2xl mx-auto">Kami menyediakan program keahlian yang relevan dengan kebutuhan industri saat ini, dilengkapi dengan fasilitas praktik modern (Teaching Factory).</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="(j, index) in jurusanDisplay" :key="index" class="bg-slate-800/50 border border-slate-700 hover:border-emerald-500/50 rounded-2xl p-6 transition-all duration-300 hover:shadow-[0_0_30px_rgba(16,185,129,0.15)] group relative overflow-hidden">
                        <!-- BG Glow on hover -->
                        <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 rounded-full blur-2xl group-hover:bg-emerald-500/20 transition-all -mr-10 -mt-10"></div>
                        
                        <div class="w-14 h-14 bg-slate-900 border border-slate-600 rounded-xl flex items-center justify-center text-emerald-400 text-2xl mb-6 shadow-inner group-hover:scale-110 group-hover:border-emerald-500 transition-all">
                            <i class="fas" :class="j.ikon"></i>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2 leading-snug">{{ j.nama_jurusan }}</h3>
                        <div class="mt-4 pt-4 border-t border-slate-700/50 flex justify-between items-center">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Kode:</span>
                            <span class="px-3 py-1 bg-slate-900 border border-slate-700 rounded-md text-amber-400 font-black text-xs">{{ j.singkatan }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Mitra Industri (BKK) -->
        <section id="mitra" class="py-16 bg-slate-950 border-b border-slate-900 overflow-hidden relative">
            <div class="absolute top-0 left-0 w-32 h-full bg-gradient-to-r from-slate-950 to-transparent z-10"></div>
            <div class="absolute top-0 right-0 w-32 h-full bg-gradient-to-l from-slate-950 to-transparent z-10"></div>
            
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8 text-center">
                <span class="text-slate-500 font-bold uppercase tracking-widest text-xs">Bursa Kerja Khusus & Mitra DUDI</span>
            </div>

            <!-- Marquee Container -->
            <div class="flex overflow-hidden group">
                <div class="animate-marquee flex gap-12 items-center px-6">
                    <div v-for="(mitra, i) in mitraIndustri" :key="'A'+i" class="flex items-center gap-3 grayscale opacity-60 hover:grayscale-0 hover:opacity-100 transition-all cursor-pointer">
                        <i class="fas text-4xl" :class="[mitra.icon, mitra.color]"></i>
                        <span class="text-xl font-bold text-slate-400">{{ mitra.nama }}</span>
                    </div>
                    <!-- Duplicate for infinite effect -->
                    <div v-for="(mitra, i) in mitraIndustri" :key="'B'+i" class="flex items-center gap-3 grayscale opacity-60 hover:grayscale-0 hover:opacity-100 transition-all cursor-pointer">
                        <i class="fas text-4xl" :class="[mitra.icon, mitra.color]"></i>
                        <span class="text-xl font-bold text-slate-400">{{ mitra.nama }}</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- PPDB / SPMB Section (Industrial Style) -->
        <section id="spmb" class="py-24 relative bg-slate-900 overflow-hidden">
            <div class="absolute inset-0 bg-tech-pattern opacity-10"></div>
            
            <!-- Transparent PNG Spot (Teacher/Admin) -->
            <div class="absolute bottom-0 left-0 w-[400px] h-[500px] hidden lg:block opacity-70 transform -translate-x-10">
                <img src="https://raw.githubusercontent.com/Rikiwahyudin29/assets/main/guru-smk-png.png" 
                     onerror="this.src='https://www.pngmart.com/files/7/Teacher-PNG-Transparent.png'" 
                     alt="Guru Admin PPDB" 
                     class="w-full h-full object-contain object-bottom drop-shadow-[0_10px_20px_rgba(0,0,0,0.5)] relative z-10">
                <div class="absolute bottom-0 left-20 w-48 h-48 bg-emerald-500/20 rounded-full blur-3xl z-0"></div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-20">
                <div class="bg-gradient-to-br from-slate-800/90 to-slate-900/90 backdrop-blur-md border border-slate-700 rounded-[2.5rem] p-8 md:p-12 shadow-2xl relative overflow-hidden lg:ml-64">
                    <!-- Accent Line -->
                    <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-emerald-500 via-amber-400 to-emerald-500"></div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                        <div>
                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-400 text-xs font-bold uppercase tracking-widest mb-6 border border-amber-500/30">
                                <i class="fas fa-door-open"></i> Pendaftaran Dibuka
                            </span>
                            <h2 class="text-3xl md:text-5xl font-black text-white mb-6 leading-tight">Jadilah Bagian dari<br><span class="text-emerald-400">Generasi Siap Kerja</span></h2>
                            <p class="text-slate-400 text-lg leading-relaxed mb-8">
                                Sistem Informasi Akademik Terpadu memudahkan Anda mendaftar secara online. Pantau status kelulusan, bayar biaya sekolah, dan cetak bukti pendaftaran dalam satu portal.
                            </p>
                            
                            <div class="flex flex-col sm:flex-row gap-4">
                                <Link href="/spmb/register" class="inline-flex justify-center items-center py-4 px-8 font-black text-slate-900 uppercase tracking-widest rounded-xl bg-amber-400 hover:bg-amber-300 transition-all shadow-lg shadow-amber-500/20">
                                    Mulai Pendaftaran <i class="fas fa-arrow-right ml-3"></i>
                                </Link>
                                <Link href="/pengumuman" class="inline-flex justify-center items-center py-4 px-8 font-bold text-white rounded-xl border border-slate-600 hover:bg-slate-700 transition-all">
                                    <i class="fas fa-bullhorn mr-3 text-emerald-400"></i> Info Kelulusan
                                </Link>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Dashboard UI Mockup snippet -->
                            <div class="col-span-2 bg-slate-950 rounded-xl border border-slate-800 p-4 shadow-inner flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-400"><i class="fas fa-check"></i></div>
                                    <div>
                                        <div class="text-white font-bold text-sm">Pendaftaran Online</div>
                                        <div class="text-slate-500 text-xs">Sistem Terintegrasi PPDB</div>
                                    </div>
                                </div>
                                <div class="w-12 h-6 bg-slate-800 rounded-full p-1"><div class="w-4 h-4 bg-emerald-400 rounded-full translate-x-6"></div></div>
                            </div>
                            
                            <div class="bg-slate-800/80 backdrop-blur-sm rounded-xl border border-slate-700 p-5 hover:border-emerald-500/50 transition-colors cursor-default">
                                <i class="fas fa-laptop-house text-3xl text-emerald-400 mb-4 block"></i>
                                <h4 class="text-white font-bold mb-1">Daftar Online</h4>
                                <p class="text-xs text-slate-400">Isi formulir dari rumah</p>
                            </div>
                            
                            <div class="bg-slate-800/80 backdrop-blur-sm rounded-xl border border-slate-700 p-5 hover:border-amber-500/50 transition-colors cursor-default">
                                <i class="fas fa-file-signature text-3xl text-amber-400 mb-4 block"></i>
                                <h4 class="text-white font-bold mb-1">Verifikasi</h4>
                                <p class="text-xs text-slate-400">Pengecekan berkas digital</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Sistem Antrian & Tracer -->
        <section id="antrian" class="py-16 bg-slate-950 border-t border-slate-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Antrian Tamu -->
                    <div class="bg-gradient-to-br from-slate-900 to-slate-800 border border-slate-700 rounded-3xl p-8 flex flex-col justify-between hover:border-emerald-500/50 transition-all group">
                        <div>
                            <div class="w-14 h-14 bg-slate-950 border border-slate-700 rounded-xl flex items-center justify-center text-emerald-400 text-2xl mb-6 shadow-inner group-hover:scale-110 transition-transform">
                                <i class="fas fa-ticket-alt"></i>
                            </div>
                            <h3 class="text-2xl font-black text-white mb-3">Sistem Antrian Digital</h3>
                            <p class="text-slate-400 text-sm leading-relaxed mb-8">Ambil nomor antrian secara digital sebelum menuju meja resepsionis (PTSP) untuk layanan terpadu yang lebih efisien.</p>
                        </div>
                        <Link href="/buku-tamu" class="inline-flex items-center justify-between w-full py-4 px-6 bg-slate-800 hover:bg-emerald-600 border border-slate-700 hover:border-emerald-500 rounded-xl text-white font-bold transition-all">
                            Ambil Antrian <i class="fas fa-arrow-right"></i>
                        </Link>
                    </div>

                    <!-- Tracer Alumni -->
                    <div class="bg-gradient-to-br from-slate-900 to-slate-800 border border-slate-700 rounded-3xl p-8 flex flex-col justify-between hover:border-amber-500/50 transition-all group">
                        <div>
                            <div class="w-14 h-14 bg-slate-950 border border-slate-700 rounded-xl flex items-center justify-center text-amber-400 text-2xl mb-6 shadow-inner group-hover:scale-110 transition-transform">
                                <i class="fas fa-search-location"></i>
                            </div>
                            <h3 class="text-2xl font-black text-white mb-3">Tracer Study Alumni</h3>
                            <p class="text-slate-400 text-sm leading-relaxed mb-8">Pendataan jejak rekam alumni SMK untuk memantau serapan kerja, wirausaha, maupun melanjutkan studi ke Perguruan Tinggi.</p>
                        </div>
                        <Link href="/tracer" class="inline-flex items-center justify-between w-full py-4 px-6 bg-slate-800 hover:bg-amber-500 border border-slate-700 hover:border-amber-400 rounded-xl text-white font-bold transition-all text-slate-900 hover:text-slate-900">
                            Isi Data Alumni <i class="fas fa-arrow-right"></i>
                        </Link>
                    </div>

                </div>
            </div>
        </section>

        <!-- Profil & Video Section -->
        <section id="profile" class="py-24 bg-slate-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                
                <div class="relative order-2 lg:order-1">
                    <div class="absolute inset-0 bg-emerald-600 transform -rotate-3 rounded-3xl shadow-lg opacity-20"></div>
                    <div class="absolute inset-0 border-2 border-emerald-500 transform rotate-2 rounded-3xl"></div>
                    <img :src="web.foto_kepsek ? `/uploads/profil/${web.foto_kepsek}` : 'https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=2000&auto=format&fit=crop'" alt="Kepala Sekolah" class="relative rounded-3xl shadow-2xl w-full h-[350px] md:h-[450px] object-cover grayscale hover:grayscale-0 transition-all duration-700 bg-slate-800">
                    
                    <div class="absolute -bottom-6 -left-6 md:-bottom-8 md:-left-8 bg-slate-950 border border-slate-800 p-5 rounded-2xl shadow-2xl flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-slate-800 border border-slate-700 text-amber-400 flex items-center justify-center text-xl shrink-0">
                            <i class="fas fa-quote-left"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-white text-sm md:text-base truncate">{{ web.nama_kepsek || 'Nama Kepala Sekolah' }}</h4>
                            <p class="text-emerald-500 font-bold text-[10px] md:text-xs uppercase tracking-wider">Kepala SMK</p>
                        </div>
                    </div>
                </div>
                
                <div class="order-1 lg:order-2">
                    <span class="text-amber-500 font-bold uppercase tracking-widest text-xs mb-3 flex items-center gap-2">
                        <i class="fas fa-user-tie"></i> Sambutan Pimpinan
                    </span>
                    <h2 class="text-3xl md:text-4xl lg:text-5xl font-black text-white mb-6 leading-tight tracking-tight">Selamat Datang di <br><span class="text-emerald-400">{{ web.nama_sekolah }}</span></h2>
                    <div class="prose prose-sm md:prose-base prose-invert text-slate-400 mb-8 text-justify leading-relaxed" v-html="web.sambutan_kepsek || '<p>Sambutan kepala sekolah belum diisi. Mari wujudkan SMK Hebat.</p>'"></div>
                    
                    <div v-if="yt_embed" class="mt-8">
                        <span class="text-red-500 font-bold uppercase tracking-widest text-xs mb-4 flex items-center gap-2">
                            <i class="fab fa-youtube"></i> Video Profil
                        </span>
                        <div class="relative w-full rounded-2xl overflow-hidden border border-slate-700 shadow-2xl bg-slate-950">
                            <div style="padding-top: 56.25%; position: relative;">
                                <iframe class="absolute top-0 left-0 w-full h-full" :src="yt_embed" title="YouTube video player" frameborder="0" allowfullscreen></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Berita Section -->
        <section id="berita" class="py-24 bg-slate-950 border-y border-slate-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-6">
                    <div>
                        <span class="text-emerald-500 font-bold uppercase tracking-widest text-xs mb-2 block">Informasi Terkini</span>
                        <h2 class="text-3xl md:text-4xl font-black text-white">Berita & Artikel</h2>
                    </div>
                    <Link href="/berita" class="text-emerald-400 hover:text-white font-bold text-sm flex items-center gap-2 transition-colors">
                        Lihat Semua Berita <i class="fas fa-long-arrow-alt-right"></i>
                    </Link>
                </div>

                <div v-if="berita && berita.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div v-for="b in berita" :key="b.id" class="bg-slate-900 border border-slate-800 rounded-2xl hover:border-emerald-500/50 transition-all duration-300 overflow-hidden group flex flex-col">
                        <div class="h-48 overflow-hidden relative bg-slate-800">
                            <img :src="`/uploads/berita/${b.gambar}`" :alt="b.judul" class="w-full h-full object-cover group-hover:scale-105 group-hover:opacity-80 transition-all duration-700 grayscale-[30%]">
                            <div class="absolute top-4 left-4 bg-slate-950/80 backdrop-blur border border-slate-700 px-3 py-1 rounded-md text-xs font-bold text-amber-400 flex items-center gap-2">
                                <i class="far fa-calendar-alt"></i> {{ formatDate(b.created_at) }}
                            </div>
                        </div>
                        <div class="p-6 flex-1 flex flex-col">
                            <h3 class="text-lg font-bold text-white mb-3 group-hover:text-emerald-400 transition-colors line-clamp-2 leading-snug">
                                {{ b.judul }}
                            </h3>
                            <div class="text-slate-400 text-sm line-clamp-3 mb-6 flex-1 leading-relaxed" v-html="b.isi.replace(/<[^>]*>?/gm, '')"></div>
                            <Link :href="`/berita/detail/${b.slug}`" class="inline-flex items-center text-emerald-500 font-bold text-sm hover:text-white transition-colors">
                                Baca Selengkapnya <i class="fas fa-arrow-right ml-2 text-[10px]"></i>
                            </Link>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center p-12 bg-slate-900 rounded-2xl border border-dashed border-slate-700">
                    <div class="w-16 h-16 bg-slate-800 text-slate-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
                        <i class="far fa-newspaper"></i>
                    </div>
                    <p class="text-slate-400 font-medium">Belum ada berita yang dipublikasikan.</p>
                </div>
            </div>
        </section>

        <!-- Galeri Section -->
        <section id="galeri" class="py-24 bg-slate-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <span class="text-amber-500 font-bold uppercase tracking-widest text-xs mb-2 block">Dokumentasi</span>
                    <h2 class="text-3xl md:text-4xl font-black text-white">Galeri Kegiatan</h2>
                </div>

                <div v-if="galeri && galeri.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
                    <div v-for="(g, i) in galeri" :key="g.id" :class="['relative group overflow-hidden rounded-xl h-40 md:h-56 cursor-pointer bg-slate-800', (i===0 || i===3) ? 'col-span-2 md:col-span-2' : '']" @click="openLightbox(`/uploads/galeri/${g.gambar}`)">
                        <img :src="`/uploads/galeri/${g.gambar}`" :alt="g.judul" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110 grayscale-[50%] group-hover:grayscale-0">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                            <div>
                                <span class="inline-block bg-emerald-600 text-white text-[9px] md:text-[10px] font-bold uppercase tracking-wider mb-1.5 px-2 py-0.5 rounded">{{ g.kategori }}</span>
                                <h4 class="text-white font-bold text-sm line-clamp-1 leading-tight">{{ g.judul }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center text-slate-500 py-12 text-sm border border-dashed border-slate-700 rounded-2xl">Belum ada foto dokumentasi.</div>
            </div>
        </section>

        <!-- Footer -->
        <footer id="kontak" class="bg-slate-950 border-t border-slate-800 pt-16 pb-8 text-slate-400">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-12 mb-12">
                    
                    <div class="md:col-span-5 flex flex-col">
                        <div class="flex items-center space-x-3 mb-6">
                            <img :src="pathLogo" class="h-10 w-10 md:h-12 md:w-12 object-contain bg-white rounded-lg p-1 border border-slate-700" alt="Logo">
                            <div class="flex flex-col">
                                <span class="text-lg md:text-xl font-black text-white uppercase tracking-tight">
                                    {{ web.nama_sekolah }}
                                </span>
                                <span class="text-[10px] text-emerald-500 font-bold uppercase tracking-widest">Sekolah Menengah Kejuruan</span>
                            </div>
                        </div>
                        <p class="text-sm leading-relaxed mb-6 max-w-sm">{{ web.alamat }}</p>
                        <div class="flex space-x-3">
                            <a v-if="web.facebook" :href="web.facebook" class="w-9 h-9 rounded-md bg-slate-900 border border-slate-800 flex items-center justify-center hover:bg-blue-600 hover:text-white hover:border-blue-500 transition-all"><i class="fab fa-facebook-f"></i></a>
                            <a v-if="web.instagram" :href="web.instagram" class="w-9 h-9 rounded-md bg-slate-900 border border-slate-800 flex items-center justify-center hover:bg-pink-600 hover:text-white hover:border-pink-500 transition-all"><i class="fab fa-instagram"></i></a>
                            <a v-if="web.youtube" :href="web.youtube" class="w-9 h-9 rounded-md bg-slate-900 border border-slate-800 flex items-center justify-center hover:bg-red-600 hover:text-white hover:border-red-500 transition-all"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>

                    <div class="md:col-span-3">
                        <h4 class="text-white font-bold mb-6 text-sm uppercase tracking-widest border-b border-slate-800 pb-3">Akses Cepat</h4>
                        <ul class="space-y-3 text-sm">
                            <li><a href="#home" class="hover:text-emerald-400 transition-colors flex items-center gap-2"><i class="fas fa-angle-right text-[10px] text-slate-600"></i> Beranda</a></li>
                            <li><a href="#jurusan" class="hover:text-emerald-400 transition-colors flex items-center gap-2"><i class="fas fa-angle-right text-[10px] text-slate-600"></i> Program Keahlian</a></li>
                            <li><Link href="/spmb/register" class="hover:text-emerald-400 transition-colors flex items-center gap-2"><i class="fas fa-angle-right text-[10px] text-slate-600"></i> Portal PPDB</Link></li>
                            <li><Link href="/login" class="hover:text-emerald-400 transition-colors flex items-center gap-2"><i class="fas fa-angle-right text-[10px] text-slate-600"></i> SIAKAD Login</Link></li>
                        </ul>
                    </div>

                    <div class="md:col-span-4">
                        <h4 class="text-white font-bold mb-6 text-sm uppercase tracking-widest border-b border-slate-800 pb-3">Hubungi Kami</h4>
                        <ul class="space-y-4 text-sm">
                            <li class="flex items-start gap-3">
                                <i class="fas fa-map-marker-alt text-emerald-500 mt-1 w-4 text-center"></i>
                                <span class="flex-1">{{ web.alamat }}</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <i class="fas fa-envelope text-emerald-500 w-4 text-center"></i>
                                <span>{{ web.email }}</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <i class="fas fa-phone-alt text-emerald-500 w-4 text-center"></i>
                                <span>{{ web.no_telp }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="border-t border-slate-900 pt-8 flex flex-col md:flex-row justify-between items-center text-xs text-slate-600 text-center gap-2">
                    <p>&copy; {{ new Date().getFullYear() }} {{ web.nama_sekolah }}. All rights reserved.</p>
                    <p class="flex items-center gap-2 uppercase tracking-widest"><i class="fas fa-cogs"></i> Sistem Informasi Akademik Terpadu</p>
                </div>
            </div>
        </footer>

        <!-- Lightbox -->
        <transition name="fade">
            <div v-if="isLightboxOpen" class="fixed inset-0 z-[9999] bg-slate-950/95 flex justify-center items-center backdrop-blur-md" @click="closeLightbox">
                <button type="button" class="absolute top-6 right-6 text-slate-500 hover:text-white bg-slate-900 w-10 h-10 rounded-full flex items-center justify-center border border-slate-700 transition-colors" @click.stop="closeLightbox">
                    <i class="fas fa-times"></i>
                </button>
                <transition name="zoom">
                    <img v-if="isLightboxOpen" :src="lightboxImage" class="max-w-[90%] max-h-[85%] rounded-xl shadow-2xl border-4 border-slate-800" @click.stop>
                </transition>
            </div>
        </transition>

        <!-- Modal Tabungan Bank Mini -->
        <transition name="fade">
            <div v-if="isModalOpen" class="fixed inset-0 z-[10000] flex justify-center items-center bg-slate-950/80 backdrop-blur-md p-4">
                <div class="relative w-full max-w-sm">
                    <div class="bg-slate-900 border border-slate-700 rounded-3xl shadow-2xl overflow-hidden">
                        
                        <!-- Header -->
                        <div class="p-6 border-b border-slate-800 bg-slate-950 text-center relative">
                            <button @click="resetModal" type="button" class="absolute top-4 right-4 text-slate-500 hover:text-white transition-colors">
                                <i class="fas fa-times"></i>
                            </button>
                            <div class="w-12 h-12 bg-slate-800 border border-slate-700 rounded-xl mx-auto mb-3 flex items-center justify-center text-emerald-400 text-xl shadow-inner">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <h3 class="text-lg font-black text-white tracking-wide uppercase">Bank Mini SMK</h3>
                            <p class="text-slate-500 text-[10px] font-bold tracking-widest uppercase mt-1">Cek Saldo Tabungan</p>
                        </div>

                        <div class="p-8 min-h-[300px] relative">
                            
                            <!-- Step 1: NISN -->
                            <div v-if="modalStep === 1" class="transition-all duration-300 text-center">
                                <p class="text-sm text-slate-400 mb-6">Masukkan NISN untuk mengakses brankas digital.</p>
                                
                                <div class="mb-6">
                                    <input v-model="formTabungan.nisn" type="number" class="w-full text-center text-2xl font-black border-b-2 border-slate-700 focus:border-emerald-500 border-t-0 border-l-0 border-r-0 px-0 py-2 focus:ring-0 bg-transparent text-white tracking-wider placeholder-slate-700" placeholder="NISN..." required autocomplete="off">
                                </div>
                                <div v-if="errorMessage" class="text-red-500 text-xs mb-4 font-bold bg-red-500/10 py-2 px-3 rounded-md border border-red-500/20">{{ errorMessage }}</div>
                                
                                <button @click="checkNISN" :disabled="isLoading" type="button" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3.5 rounded-xl shadow-lg transition-all flex justify-center items-center gap-2 disabled:opacity-50 text-sm uppercase tracking-wider">
                                    <span v-if="isLoading"><i class="fas fa-spinner fa-spin"></i> Memproses</span>
                                    <span v-else>Verifikasi Identitas <i class="fas fa-fingerprint"></i></span>
                                </button>
                            </div>

                            <!-- Step 2: PIN -->
                            <div v-else-if="modalStep === 2" class="transition-all duration-300 text-center">
                                <div class="mb-6 bg-slate-950 p-4 rounded-xl border border-slate-800">
                                    <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mb-1">Nasabah</p>
                                    <h4 class="text-emerald-400 font-black text-lg">{{ siswaInfo.nama }}</h4>
                                    <p class="text-xs text-slate-400">{{ siswaInfo.nisn }}</p>
                                </div>
                                
                                <p class="text-sm text-slate-400 mb-4">Masukkan 6 Digit PIN Keamanan</p>
                                <div class="mb-6">
                                    <input v-model="formTabungan.pin" type="password" maxlength="6" class="w-full text-center text-3xl font-black border-b-2 border-slate-700 focus:border-amber-500 border-t-0 border-l-0 border-r-0 px-0 py-2 focus:ring-0 bg-transparent text-white tracking-[1em] placeholder-slate-700" placeholder="••••••" required autocomplete="off">
                                </div>
                                <div v-if="errorMessage" class="text-red-500 text-xs mb-4 font-bold bg-red-500/10 py-2 px-3 rounded-md border border-red-500/20">{{ errorMessage }}</div>
                                
                                <button @click="checkPINAndSaldo" :disabled="isLoading || formTabungan.pin.length < 4" type="button" class="w-full bg-amber-500 hover:bg-amber-400 text-slate-900 font-black py-3.5 rounded-xl shadow-lg transition-all flex justify-center items-center gap-2 disabled:opacity-50 text-sm uppercase tracking-wider">
                                    <span v-if="isLoading"><i class="fas fa-spinner fa-spin"></i> Mengakses</span>
                                    <span v-else>Buka Brankas <i class="fas fa-unlock"></i></span>
                                </button>
                                <button @click="goToStep(1)" type="button" class="w-full mt-3 text-xs text-slate-500 hover:text-white font-bold py-2">
                                    Bukan saya, ganti NISN
                                </button>
                            </div>

                            <!-- Step 3: Result -->
                            <div v-else-if="modalStep === 3" class="text-center transition-all duration-300">
                                <div class="w-16 h-16 bg-emerald-500/20 text-emerald-400 rounded-full flex items-center justify-center text-3xl mx-auto mb-4 border border-emerald-500/50">
                                    <i class="fas fa-check"></i>
                                </div>
                                <h4 class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Total Saldo Tersedia</h4>
                                <div class="text-3xl font-black text-white mb-6 p-4 bg-slate-950 rounded-2xl border border-slate-800">
                                    {{ saldoResult }}
                                </div>
                                <div class="text-xs text-slate-500 mb-6 flex items-center justify-center gap-1">
                                    <i class="fas fa-lock"></i> Data terenkripsi dengan aman
                                </div>
                                
                                <button @click="resetModal" type="button" class="w-full bg-slate-800 hover:bg-slate-700 text-white font-bold py-3.5 rounded-xl border border-slate-700 transition-all uppercase tracking-wider text-sm">
                                    Selesai & Tutup
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </transition>

    </div>
</template>
