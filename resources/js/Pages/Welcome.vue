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
    background-color: rgba(255, 255, 255, 0.95); 
    backdrop-filter: blur(12px); 
    box-shadow: 0 4px 20px -5px rgba(5, 150, 105, 0.1); 
    padding-top: 0.75rem; 
    padding-bottom: 0.75rem; 
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.nav-link { position: relative; font-weight: 600; font-size: 0.95rem; }
.nav-link::after {
    content: ''; position: absolute; width: 0; height: 2px; bottom: -5px; left: 0;
    background-color: #10b981; 
    transition: width 0.3s;
}
.nav-link:hover::after { width: 100%; }

/* Lightbox Animation */
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.zoom-enter-active, .zoom-leave-active { transition: transform 0.3s; }
.zoom-enter-from, .zoom-leave-to { transform: scale(0.9); }

.bg-islamic-pattern {
    background-image: radial-gradient(#10b981 1px, transparent 1px);
    background-size: 20px 20px;
    opacity: 0.05;
}

input[type="number"]::-webkit-outer-spin-button, input[type="number"]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
input[type="number"] { -moz-appearance: textfield; }
</style>

<template>
    <Head :title="`${web.nama_sekolah || 'SIAKAD'} | Official Website`" />

    <div class="font-jakarta text-slate-600 bg-slate-50 overflow-x-hidden">
        
        <!-- Navbar -->
        <nav :class="['fixed w-full z-50 top-0 start-0 transition-all duration-300 ease-in-out', isScrolled ? 'nav-scrolled' : 'nav-transparent']">
            <div class="max-w-screen-xl flex items-center justify-between mx-auto p-4 md:px-8">
                <!-- LOGO -->
                <a href="#" class="flex items-center space-x-3 flex-shrink-0">
                    <img :src="pathLogo" class="h-10 w-10 md:h-12 md:w-12 object-contain drop-shadow-md bg-white rounded-full p-1 border-2 border-emerald-500/30" alt="Logo">
                    <span :class="['text-sm sm:text-lg md:text-xl font-bold truncate transition-colors duration-300 uppercase tracking-tight', isScrolled ? 'text-emerald-900' : 'text-white']">
                        {{ web.nama_sekolah || 'NAMA SEKOLAH' }}
                    </span>
                </a>
                
                <!-- MENU DESKTOP -->
                <div class="hidden lg:flex items-center justify-center flex-1 mx-4">
                    <ul class="flex space-x-6 items-center">
                        <li><a href="#home" :class="['nav-link font-medium transition-colors', isScrolled ? 'text-emerald-900' : 'text-white']">Beranda</a></li>
                        <li><a href="#spmb" :class="['nav-link font-medium transition-colors', isScrolled ? 'text-emerald-600' : 'text-amber-300']">Info PPDB</a></li>
                        <li><a href="#profile" :class="['nav-link font-medium transition-colors', isScrolled ? 'text-emerald-900' : 'text-white']">Profil</a></li>
                        <li><a href="#berita" :class="['nav-link font-medium transition-colors', isScrolled ? 'text-emerald-900' : 'text-white']">Berita</a></li>
                        <li><a href="#galeri" :class="['nav-link font-medium transition-colors', isScrolled ? 'text-emerald-900' : 'text-white']">Galeri</a></li>
                        <li><a href="#kontak" :class="['nav-link font-medium transition-colors', isScrolled ? 'text-emerald-900' : 'text-white']">Kontak</a></li>
                        <li>
                            <Link href="/tracer" :class="['nav-link font-bold flex items-center gap-1', isScrolled ? 'text-amber-500' : 'text-amber-400']">
                                <i class="fas fa-search-location"></i> Tracer
                            </Link>
                        </li>
                    </ul>
                </div>

                <!-- TOMBOL KANAN -->
                <div class="flex items-center gap-2 md:gap-3 flex-shrink-0">
                    <button @click="isModalOpen = true" class="hidden md:flex items-center gap-2 py-2 px-5 rounded-full bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-bold shadow-lg hover:shadow-emerald-500/50 hover:-translate-y-0.5 transition-all border border-emerald-400">
                        <i class="fas fa-wallet"></i> Cek Saldo
                    </button>
                    
                    <Link href="/login" class="hidden md:flex items-center gap-2 text-white bg-emerald-700 hover:bg-emerald-800 focus:ring-4 focus:outline-none focus:ring-emerald-300 font-bold rounded-xl px-5 py-2.5 shadow-lg transition-all hover:-translate-y-0.5">
                        <i class="fas fa-sign-in-alt"></i> Portal Siakad
                    </Link>
                    
                    <!-- Mobile Toggle -->
                    <button @click="isMobileMenuOpen = !isMobileMenuOpen" type="button" class="lg:hidden inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-white rounded-lg bg-emerald-600/30 backdrop-blur-sm border border-emerald-400/30">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- MOBILE MENU -->
            <transition name="fade">
                <div v-if="isMobileMenuOpen" class="lg:hidden bg-white/95 backdrop-blur-md shadow-xl border-t border-emerald-100 absolute w-full left-0 top-full">
                    <ul class="flex flex-col p-4 space-y-3 font-medium">
                        <li><a @click="isMobileMenuOpen=false" href="#home" class="block text-emerald-900 px-2 py-1 hover:bg-emerald-50 rounded">Beranda</a></li>
                        <li><a @click="isMobileMenuOpen=false" href="#spmb" class="block text-emerald-600 px-2 py-1 hover:bg-emerald-50 rounded">Info PPDB</a></li>
                        <li><a @click="isMobileMenuOpen=false" href="#profile" class="block text-emerald-900 px-2 py-1 hover:bg-emerald-50 rounded">Profil</a></li>
                        <li><a @click="isMobileMenuOpen=false" href="#berita" class="block text-emerald-900 px-2 py-1 hover:bg-emerald-50 rounded">Berita</a></li>
                        <li><a @click="isMobileMenuOpen=false" href="#galeri" class="block text-emerald-900 px-2 py-1 hover:bg-emerald-50 rounded">Galeri</a></li>
                        <li><a @click="isMobileMenuOpen=false" href="#kontak" class="block text-emerald-900 px-2 py-1 hover:bg-emerald-50 rounded">Kontak</a></li>
                        <li>
                            <Link href="/tracer" class="block text-amber-500 font-bold px-2 py-1 hover:bg-emerald-50 rounded">
                                <i class="fas fa-search-location mr-1"></i> Tracer Alumni
                            </Link>
                        </li>
                        <li class="pt-4 border-t border-emerald-100 flex flex-col gap-2">
                            <button @click="isModalOpen = true; isMobileMenuOpen = false" class="w-full text-center py-2.5 px-6 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-bold shadow-md">
                                <i class="fas fa-wallet mr-1.5"></i> Cek Saldo Tabungan
                            </button>
                            <Link href="/login" class="block w-full text-center text-white bg-emerald-700 font-bold rounded-xl px-4 py-2.5">
                                <i class="fas fa-sign-in-alt mr-2"></i> Portal Siakad
                            </Link>
                        </li>
                    </ul>
                </div>
            </transition>
        </nav>

        <!-- Hero Section -->
        <section id="home" class="relative h-screen flex items-center justify-center overflow-hidden bg-slate-900">
            <div class="absolute inset-0 z-0 w-full h-full">
                <div class="relative h-full w-full overflow-hidden">
                    <template v-if="sliders && sliders.length > 0">
                        <transition-group name="fade" tag="div">
                            <div v-for="(slide, index) in sliders" :key="slide.id" v-show="activeSlide === index" class="absolute inset-0 duration-1000 ease-in-out">
                                <img :src="`/uploads/slider/${slide.gambar}`" class="absolute block w-full h-full object-cover" :alt="slide.judul">
                                <div class="absolute inset-0 bg-gradient-to-t from-emerald-950 via-emerald-900/70 to-transparent opacity-90"></div>
                            </div>
                        </transition-group>
                    </template>
                    <template v-else>
                        <div class="absolute inset-0 duration-1000 ease-in-out">
                            <img src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?q=80&w=2070&auto=format&fit=crop" class="absolute block w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-emerald-950 via-emerald-900/70 to-transparent opacity-90"></div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="absolute inset-0 z-10 bg-islamic-pattern pointer-events-none"></div>

            <div class="relative z-20 px-4 md:px-8 mx-auto max-w-screen-xl text-center mt-16 md:mt-0 transition-transform duration-1000 transform translate-y-0 opacity-100">
                <span class="bg-amber-500/20 text-amber-300 border border-amber-400/50 text-[10px] md:text-xs font-bold inline-flex items-center px-4 py-1.5 rounded-full mb-4 md:mb-6 backdrop-blur-md uppercase tracking-widest shadow-lg">
                    <i class="fas fa-mosque mr-2"></i> Generasi Cerdas & Berakhlak
                </span>
                <h1 class="mb-4 md:mb-6 text-3xl sm:text-4xl md:text-5xl lg:text-6xl xl:text-7xl font-extrabold tracking-tight leading-tight md:leading-none text-white drop-shadow-2xl px-2">
                    {{ web.nama_sekolah || 'NAMA SEKOLAH' }}
                </h1>
                <p class="mb-8 text-sm sm:text-base md:text-lg font-medium text-emerald-50 px-4 sm:px-12 md:px-24 lg:px-48 drop-shadow-md leading-relaxed">
                    {{ web.deskripsi_hero || 'Mewujudkan peserta didik yang unggul dalam prestasi, berkarakter Islami, terampil, dan berdaya saing di era global.' }}
                </p>
                <div class="flex flex-col sm:flex-row justify-center items-center gap-4 w-full px-6 sm:px-0 mt-4">
                    <Link href="/spmb/register" class="w-full sm:w-auto inline-flex justify-center items-center py-3.5 md:py-4 px-8 md:px-10 text-sm md:text-base font-bold text-center text-emerald-900 rounded-full bg-gradient-to-r from-amber-400 to-yellow-500 hover:from-amber-300 hover:to-yellow-400 focus:ring-4 focus:ring-amber-300 transition-all shadow-xl shadow-amber-500/30 transform hover:-translate-y-1">
                        Daftar PPDB <i class="fas fa-arrow-right ml-2"></i>
                    </Link>
                    <a href="#profile" class="w-full sm:w-auto inline-flex justify-center items-center py-3.5 md:py-4 px-8 md:px-10 text-sm md:text-base font-bold text-center text-white rounded-full border border-emerald-400/50 hover:bg-emerald-800/50 hover:border-emerald-300 transition-all backdrop-blur-md">
                        <i class="fas fa-play-circle mr-2 text-amber-400"></i> Profil Madrasah
                    </a>
                </div>
            </div>
            
            <div class="absolute bottom-0 left-0 w-full overflow-hidden leading-none z-20">
                <svg class="relative block w-[calc(100%+1.3px)] h-[50px] md:h-[100px] lg:h-[120px]" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                    <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="fill-slate-50"></path>
                </svg>
            </div>
        </section>

        <!-- PPDB / SPMB Section -->
        <section id="spmb" class="relative py-16 md:py-20 bg-slate-50">
            <div class="max-w-screen-xl px-4 md:px-8 mx-auto text-center">
                <div class="flex items-center justify-center gap-2 mb-2">
                    <i class="fas fa-star text-amber-500 text-xs"></i>
                    <span class="text-emerald-600 font-bold uppercase tracking-widest text-xs block">Penerimaan Peserta Didik Baru</span>
                    <i class="fas fa-star text-amber-500 text-xs"></i>
                </div>
                <h2 class="text-3xl md:text-4xl font-extrabold text-slate-800 mb-8 md:mb-12">Bergabunglah Bersama Kami</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8 mb-12">
                    <div class="p-8 bg-white rounded-[2rem] border border-emerald-100 hover:shadow-xl hover:shadow-emerald-500/10 transition-all duration-300 group relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-50 rounded-bl-[4rem] -z-10 group-hover:scale-110 transition-transform"></div>
                        <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center text-2xl mb-6 mx-auto group-hover:-translate-y-2 transition-transform shadow-inner border border-emerald-200">
                            <i class="fas fa-laptop-house"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-3">1. Daftar Online</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">Isi formulir biodata diri dan sekolah asal melalui portal PPDB resmi di website ini.</p>
                    </div>
                    <div class="p-8 bg-white rounded-[2rem] border border-emerald-100 hover:shadow-xl hover:shadow-emerald-500/10 transition-all duration-300 group relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-amber-50 rounded-bl-[4rem] -z-10 group-hover:scale-110 transition-transform"></div>
                        <div class="w-16 h-16 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center text-2xl mb-6 mx-auto group-hover:-translate-y-2 transition-transform shadow-inner border border-amber-200">
                            <i class="fas fa-file-signature"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-3">2. Verifikasi</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">Tim panitia akan memverifikasi berkas pendaftaran Anda secara teliti dan digital.</p>
                    </div>
                    <div class="p-8 bg-white rounded-[2rem] border border-emerald-100 hover:shadow-xl hover:shadow-emerald-500/10 transition-all duration-300 group relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-teal-50 rounded-bl-[4rem] -z-10 group-hover:scale-110 transition-transform"></div>
                        <div class="w-16 h-16 bg-teal-100 text-teal-600 rounded-2xl flex items-center justify-center text-2xl mb-6 mx-auto group-hover:-translate-y-2 transition-transform shadow-inner border border-teal-200">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-3">3. Pengumuman</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">Pantau status kelulusan secara real-time dan cetak bukti pendaftaran mandiri.</p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-emerald-800 via-emerald-700 to-teal-900 rounded-[2.5rem] p-8 md:p-12 text-white shadow-2xl shadow-emerald-900/30 relative overflow-hidden border border-emerald-600/50">
                    <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-amber-400 opacity-20 rounded-full blur-3xl"></div>
                    
                    <div class="relative z-10 grid grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8 items-center">
                        <div class="text-center">
                            <h4 class="text-4xl md:text-5xl lg:text-6xl font-black mb-2 text-amber-400 drop-shadow-md">{{ stats.pendaftar || 0 }}</h4>
                            <p class="text-emerald-100 text-xs md:text-sm uppercase font-bold tracking-widest">Pendaftar</p>
                        </div>
                        <div class="text-center">
                            <h4 class="text-4xl md:text-5xl lg:text-6xl font-black mb-2 text-amber-400 drop-shadow-md">{{ stats.siswa || 0 }}</h4>
                            <p class="text-emerald-100 text-xs md:text-sm uppercase font-bold tracking-widest">Siswa Aktif</p>
                        </div>
                        <div class="col-span-2 sm:col-span-1 text-center">
                            <h4 class="text-4xl md:text-5xl lg:text-6xl font-black mb-2 text-amber-400 drop-shadow-md">{{ stats.guru || 0 }}</h4>
                            <p class="text-emerald-100 text-xs md:text-sm uppercase font-bold tracking-widest">Dewan Guru</p>
                        </div>
                        <div class="col-span-2 lg:col-span-1 flex flex-col justify-center mt-4 lg:mt-0">
                            <Link href="/spmb/register" class="w-full bg-amber-400 text-emerald-950 hover:bg-amber-300 font-extrabold py-4 px-6 rounded-2xl shadow-xl transition-all transform hover:-translate-y-1 text-center border border-amber-300">
                                DAFTAR SEKARANG
                            </Link>
                            <p class="mt-3 text-[11px] text-emerald-200 font-medium text-center tracking-wide">* Kuota Penerimaan Terbatas</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pengumuman Kelulusan -->
        <section id="pengumuman-kelulusan" class="py-12 bg-white">
            <div class="max-w-screen-xl px-4 md:px-8 mx-auto">
                <div class="bg-gradient-to-r from-amber-50 to-yellow-50 rounded-[2rem] p-8 md:p-10 shadow-lg border border-amber-200 flex flex-col md:flex-row items-center justify-between gap-8 relative overflow-hidden">
                    <div class="absolute -right-10 -bottom-10 opacity-10">
                        <i class="fas fa-graduation-cap text-9xl text-amber-600"></i>
                    </div>
                    
                    <div class="text-center md:text-left flex-1 relative z-10 flex flex-col md:flex-row items-center md:items-start gap-6">
                        <div class="w-20 h-20 bg-white rounded-2xl flex items-center justify-center text-4xl text-amber-500 shadow-md border border-amber-100 flex-shrink-0">
                            <i class="fas fa-award"></i>
                        </div>
                        <div>
                            <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[10px] md:text-xs font-bold bg-amber-200 text-amber-800 mb-3 tracking-widest uppercase shadow-sm">
                                <i class="fas fa-bell mr-2 animate-pulse"></i> Info Akademik Kelas Akhir
                            </span>
                            <h2 class="text-2xl md:text-3xl font-black text-slate-800 mb-2">Pengumuman Kelulusan</h2>
                            <p class="text-slate-600 text-sm md:text-base leading-relaxed">
                                Akses portal resmi pengumuman kelulusan untuk mengunduh SKL (Surat Keterangan Lulus) dan Transkrip Nilai yang telah dilegalisasi secara digital (E-Arsip).
                            </p>
                        </div>
                    </div>
                    <div class="flex-shrink-0 w-full md:w-auto relative z-10">
                        <Link href="/kelulusan" class="block w-full text-center px-8 py-4 bg-amber-500 text-white rounded-2xl font-bold text-lg hover:bg-amber-600 transition-all shadow-xl shadow-amber-500/30 transform hover:-translate-y-1">
                            Buka Portal Kelulusan <i class="fas fa-chevron-right ml-2 text-sm"></i>
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Sistem Antrian -->
        <section id="antrian" class="py-12 bg-slate-50">
            <div class="max-w-screen-xl px-4 md:px-8 mx-auto">
                <div class="bg-white rounded-[2.5rem] p-8 md:p-12 shadow-xl border border-slate-100 flex flex-col md:flex-row items-center justify-between gap-10">
                    <div class="text-center md:text-left flex-1">
                        <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold bg-teal-50 text-teal-600 mb-4 border border-teal-100">
                            <span class="relative flex h-2 w-2 mr-2">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-2 w-2 bg-teal-500"></span>
                            </span>
                            SISTEM ANTRIAN DIGITAL
                        </span>
                        <h2 class="text-3xl md:text-4xl font-extrabold text-slate-800 mb-4 tracking-tight">Kunjungan Tamu & Wali Santri</h2>
                        <p class="text-slate-500 text-base md:text-lg leading-relaxed">
                            Demi kenyamanan layanan terpadu, silakan ambil nomor antrian secara digital sebelum menuju meja resepsionis atau ruang tata usaha.
                        </p>
                    </div>
                    <div class="flex-shrink-0 w-full md:w-auto">
                        <Link href="/buku-tamu" class="group flex items-center justify-center gap-3 px-10 py-5 bg-teal-600 text-white rounded-2xl font-black text-lg hover:bg-teal-700 transition-all shadow-xl shadow-teal-500/30 transform hover:-translate-y-1">
                            <i class="fas fa-id-card text-2xl group-hover:rotate-12 transition-transform"></i>
                            AMBIL ANTRIAN SEKARANG
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Profil Section -->
        <section id="profile" class="py-16 md:py-24 bg-white">
            <div class="max-w-screen-xl px-4 md:px-8 mx-auto grid grid-cols-1 lg:grid-cols-2 gap-12 md:gap-16 items-center">
                
                <div class="relative order-2 lg:order-1">
                    <div class="absolute inset-0 bg-emerald-600 transform -rotate-3 rounded-[2rem] shadow-lg"></div>
                    <div class="absolute inset-0 bg-amber-400 transform rotate-2 rounded-[2rem] shadow-lg opacity-50"></div>
                    <img :src="web.foto_kepsek ? `/uploads/profil/${web.foto_kepsek}` : 'https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=2000&auto=format&fit=crop'" alt="Kepala Madrasah" class="relative rounded-[2rem] shadow-2xl w-full h-[350px] md:h-[450px] object-cover border-8 border-white">
                    
                    <div class="absolute bottom-6 left-6 md:bottom-8 md:left-8 bg-white/95 backdrop-blur-md p-4 md:p-5 rounded-2xl shadow-xl max-w-[85%] border border-slate-100 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                            <i class="fas fa-quote-left"></i>
                        </div>
                        <div>
                            <h4 class="font-extrabold text-slate-800 text-sm md:text-base truncate">{{ web.nama_kepsek || 'Nama Kepala Sekolah' }}</h4>
                            <p class="text-emerald-600 font-bold text-[10px] md:text-xs uppercase tracking-wider">Kepala Madrasah</p>
                        </div>
                    </div>
                </div>
                
                <div class="order-1 lg:order-2">
                    <span class="text-amber-500 font-bold uppercase tracking-widest text-xs mb-3 flex items-center gap-2">
                        <i class="fas fa-moon"></i> Sambutan Pimpinan
                    </span>
                    <h2 class="text-3xl md:text-4xl lg:text-5xl font-black text-slate-800 mb-6 leading-tight tracking-tight">Selamat Datang di <br class="hidden lg:block"><span class="text-emerald-600">{{ web.nama_sekolah }}</span></h2>
                    <div class="prose prose-sm md:prose-base text-slate-600 mb-8 text-justify leading-relaxed" v-html="web.sambutan_kepsek || '<p>Sambutan kepala sekolah belum diisi. Mari wujudkan generasi Islami yang cerdas dan mandiri.</p>'"></div>
                    <div class="flex items-center gap-4">
                        <a href="#galeri" class="inline-flex items-center px-6 py-3 bg-emerald-50 text-emerald-700 font-bold rounded-xl hover:bg-emerald-100 transition-colors">
                            Lihat Galeri <i class="fas fa-images ml-2"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div v-if="yt_embed" class="max-w-screen-xl px-4 md:px-8 mx-auto mt-20 md:mt-28">
                <div class="text-center mb-8 md:mb-10">
                    <span class="text-red-500 font-bold uppercase tracking-widest text-xs mb-2 flex items-center justify-center gap-2">
                        <i class="fab fa-youtube"></i> Video Profil
                    </span>
                    <h2 class="text-2xl md:text-3xl font-black text-slate-800">Tur Virtual Madrasah</h2>
                </div>
                <div class="relative w-full max-w-4xl mx-auto rounded-[2rem] overflow-hidden shadow-2xl border-8 border-slate-50 bg-slate-100">
                    <div style="padding-top: 56.25%; position: relative;">
                        <iframe class="absolute top-0 left-0 w-full h-full rounded-xl" :src="yt_embed" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </section>

        <!-- Berita Section -->
        <section id="berita" class="py-16 md:py-24 bg-slate-50">
            <div class="max-w-screen-xl px-4 md:px-8 mx-auto">
                <div class="text-center mb-12 md:mb-16">
                    <span class="text-emerald-600 font-bold uppercase tracking-widest text-xs mb-2 block">Informasi Terkini</span>
                    <h2 class="text-3xl md:text-4xl font-black text-slate-800">Berita & Artikel</h2>
                    <div class="w-24 h-1 bg-emerald-500 mx-auto mt-4 rounded-full"></div>
                </div>

                <div v-if="berita && berita.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div v-for="b in berita" :key="b.id" class="bg-white rounded-3xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden group flex flex-col border border-slate-100">
                        <div class="h-52 overflow-hidden relative">
                            <img :src="`/uploads/berita/${b.gambar}`" :alt="b.judul" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                            <div class="absolute top-4 left-4 bg-white/95 backdrop-blur px-4 py-1.5 rounded-full text-xs font-bold text-emerald-700 shadow-sm border border-emerald-100 flex items-center gap-2">
                                <i class="far fa-calendar-alt"></i> {{ formatDate(b.created_at) }}
                            </div>
                        </div>
                        <div class="p-6 md:p-8 flex-1 flex flex-col">
                            <h3 class="text-lg md:text-xl font-bold text-slate-800 mb-4 group-hover:text-emerald-600 transition-colors line-clamp-2 leading-snug">
                                {{ b.judul }}
                            </h3>
                            <div class="text-slate-500 text-sm line-clamp-3 mb-6 flex-1 leading-relaxed" v-html="b.isi.replace(/<[^>]*>?/gm, '')"></div>
                            <Link :href="`/berita/detail/${b.slug}`" class="inline-flex items-center justify-center w-full py-3 bg-slate-50 text-emerald-700 font-bold rounded-xl group-hover:bg-emerald-600 group-hover:text-white transition-all text-sm">
                                Baca Selengkapnya <i class="fas fa-arrow-right ml-2"></i>
                            </Link>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center p-12 bg-white rounded-3xl border border-dashed border-slate-300 shadow-sm">
                    <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
                        <i class="far fa-newspaper"></i>
                    </div>
                    <p class="text-slate-500 font-medium">Belum ada berita yang dipublikasikan.</p>
                </div>
            </div>
        </section>

        <!-- Galeri Section -->
        <section id="galeri" class="py-16 md:py-24 bg-emerald-950 text-white relative overflow-hidden">
            <div class="absolute inset-0 opacity-5 bg-islamic-pattern"></div>
            
            <div class="max-w-screen-xl px-4 md:px-8 mx-auto relative z-10">
                <div class="flex flex-col md:flex-row justify-between items-center md:items-end mb-12 text-center md:text-left gap-4">
                    <div>
                        <span class="text-amber-400 font-bold uppercase tracking-widest text-xs mb-2 block">Dokumentasi</span>
                        <h2 class="text-3xl md:text-4xl font-black">Galeri Kegiatan</h2>
                    </div>
                </div>

                <div v-if="galeri && galeri.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
                    <div v-for="(g, i) in galeri" :key="g.id" :class="['relative group overflow-hidden rounded-2xl h-40 md:h-64 cursor-pointer shadow-lg', (i===0 || i===3) ? 'col-span-2 md:col-span-2' : '']" @click="openLightbox(`/uploads/galeri/${g.gambar}`)">
                        <img :src="`/uploads/galeri/${g.gambar}`" :alt="g.judul" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-emerald-950/90 via-emerald-950/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-5 md:p-6">
                            <div>
                                <span class="inline-block bg-amber-500 text-white text-[10px] md:text-xs font-bold uppercase mb-2 px-2 py-1 rounded-md">{{ g.kategori }}</span>
                                <h4 class="text-white font-bold text-sm md:text-lg line-clamp-2 leading-tight">{{ g.judul }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center text-emerald-200/50 py-12 text-sm md:text-base border border-dashed border-emerald-800 rounded-3xl">Belum ada foto dokumentasi.</div>
            </div>
        </section>

        <!-- Footer -->
        <footer id="kontak" class="bg-emerald-900 border-t border-emerald-800 pt-16 pb-8 relative overflow-hidden text-emerald-50">
            <div class="absolute top-0 right-0 w-64 h-64 bg-emerald-800 rounded-full blur-3xl opacity-30"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-teal-900 rounded-full blur-3xl opacity-20"></div>

            <div class="max-w-screen-xl px-4 md:px-8 mx-auto relative z-10">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-12 mb-12 text-center md:text-left">
                    <div class="md:col-span-1 flex flex-col items-center md:items-start">
                        <div class="flex items-center space-x-3 mb-6 bg-white/10 p-3 rounded-2xl backdrop-blur-sm border border-white/10">
                            <img :src="pathLogo" class="h-10 w-10 md:h-12 md:w-12 object-contain bg-white rounded-full p-1" alt="Logo">
                            <span class="text-lg md:text-xl font-black text-white uppercase tracking-tight text-left">
                                {{ web.nama_sekolah }}
                            </span>
                        </div>
                        <p class="text-emerald-200 text-sm leading-relaxed mb-6 font-medium">{{ web.alamat }}</p>
                        <div class="flex space-x-3 justify-center md:justify-start">
                            <a v-if="web.facebook" :href="web.facebook" class="w-10 h-10 rounded-xl bg-emerald-800 flex items-center justify-center text-white hover:bg-blue-600 transition-all shadow-lg"><i class="fab fa-facebook-f"></i></a>
                            <a v-if="web.instagram" :href="web.instagram" class="w-10 h-10 rounded-xl bg-emerald-800 flex items-center justify-center text-white hover:bg-pink-600 transition-all shadow-lg"><i class="fab fa-instagram"></i></a>
                            <a v-if="web.youtube" :href="web.youtube" class="w-10 h-10 rounded-xl bg-emerald-800 flex items-center justify-center text-white hover:bg-red-600 transition-all shadow-lg"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-white font-bold text-lg mb-6 flex items-center justify-center md:justify-start gap-2">
                            <i class="fas fa-link text-amber-400"></i> Akses Cepat
                        </h4>
                        <ul class="space-y-4 text-sm text-emerald-200">
                            <li><a href="#home" class="hover:text-amber-400 transition-colors flex items-center justify-center md:justify-start gap-2"><i class="fas fa-chevron-right text-[10px]"></i> Beranda</a></li>
                            <li><a href="#profile" class="hover:text-amber-400 transition-colors flex items-center justify-center md:justify-start gap-2"><i class="fas fa-chevron-right text-[10px]"></i> Profil Madrasah</a></li>
                            <li><Link href="/spmb/register" class="hover:text-amber-400 font-bold text-amber-400 transition-colors flex items-center justify-center md:justify-start gap-2"><i class="fas fa-chevron-right text-[10px]"></i> Pendaftaran PPDB</Link></li>
                            <li><Link href="/login" class="hover:text-amber-400 transition-colors flex items-center justify-center md:justify-start gap-2"><i class="fas fa-chevron-right text-[10px]"></i> Portal Akademik</Link></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="text-white font-bold text-lg mb-6 flex items-center justify-center md:justify-start gap-2">
                            <i class="fas fa-headset text-amber-400"></i> Hubungi Kami
                        </h4>
                        <ul class="space-y-5 text-sm text-emerald-200">
                            <li class="flex items-start justify-center md:justify-start gap-3">
                                <div class="w-8 h-8 rounded-lg bg-emerald-800 flex items-center justify-center shrink-0 text-amber-400"><i class="fas fa-map-marker-alt"></i></div>
                                <span class="text-left mt-1">{{ web.alamat }}</span>
                            </li>
                            <li class="flex items-center justify-center md:justify-start gap-3">
                                <div class="w-8 h-8 rounded-lg bg-emerald-800 flex items-center justify-center shrink-0 text-amber-400"><i class="fas fa-envelope"></i></div>
                                <span>{{ web.email }}</span>
                            </li>
                            <li class="flex items-center justify-center md:justify-start gap-3">
                                <div class="w-8 h-8 rounded-lg bg-emerald-800 flex items-center justify-center shrink-0 text-amber-400"><i class="fas fa-phone-alt"></i></div>
                                <span>{{ web.no_telp }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="border-t border-emerald-800 pt-8 flex flex-col md:flex-row justify-between items-center text-xs md:text-sm text-emerald-400/80 text-center gap-3 md:gap-0">
                    <p>&copy; {{ new Date().getFullYear() }} {{ web.nama_sekolah }}. All rights reserved.</p>
                    <p class="flex items-center gap-2"><i class="fas fa-shield-alt"></i> Smart School System</p>
                </div>
            </div>
        </footer>

        <!-- Lightbox -->
        <transition name="fade">
            <div v-if="isLightboxOpen" class="fixed inset-0 z-[9999] bg-[#022c22]/95 flex justify-center items-center backdrop-blur-sm" @click="closeLightbox">
                <span class="absolute top-5 right-8 text-4xl text-emerald-500 cursor-pointer hover:text-white transition-colors" @click.stop="closeLightbox">
                    <i class="fas fa-times-circle"></i>
                </span>
                <transition name="zoom">
                    <img v-if="isLightboxOpen" :src="lightboxImage" class="max-w-[90%] max-h-[85%] rounded-xl shadow-[0_0_30px_rgba(0,0,0,0.5)] border-4 border-emerald-500" @click.stop>
                </transition>
            </div>
        </transition>

        <!-- Modal Tabungan -->
        <transition name="fade">
            <div v-if="isModalOpen" class="fixed inset-0 z-[10000] flex justify-center items-center bg-slate-900/80 backdrop-blur-md p-4">
                <div class="relative w-full max-w-sm">
                    <div class="relative bg-gradient-to-b from-emerald-700 to-teal-900 rounded-[2.5rem] shadow-2xl overflow-hidden border border-emerald-500">
                        
                        <div class="p-6 pb-4 text-center relative">
                            <button @click="resetModal" type="button" class="absolute top-5 right-5 text-emerald-200 hover:text-white bg-white/10 rounded-full text-sm w-8 h-8 inline-flex justify-center items-center transition-colors">
                                <i class="fas fa-times"></i>
                            </button>
                            <div class="w-16 h-16 bg-white rounded-[1.25rem] mx-auto mb-3 flex items-center justify-center shadow-lg p-2 border-2 border-amber-400">
                                <img :src="pathLogo" class="w-full h-full object-contain" alt="Logo">
                            </div>
                            <h3 class="text-xl font-black text-white tracking-wide uppercase">Tabungan Siswa</h3>
                            <p class="text-emerald-200 text-xs font-medium tracking-widest uppercase mt-1"><i class="fas fa-leaf text-[10px] mr-1"></i> Amanah & Terpadu</p>
                        </div>

                        <div class="bg-white rounded-t-[2.5rem] p-8 min-h-[320px] relative shadow-[0_-10px_40px_rgba(0,0,0,0.2)]">
                            
                            <!-- Step 1: NISN -->
                            <div v-if="modalStep === 1" class="transition-all duration-300">
                                <h4 class="text-lg font-black text-slate-800 mb-1 text-center">Assalamu'alaikum</h4>
                                <p class="text-sm text-slate-500 mb-6 text-center">Masukkan NISN untuk mengakses brankas.</p>
                                
                                <div class="mb-4">
                                    <input v-model="formTabungan.nisn" type="number" class="w-full text-center text-3xl font-black border-b-2 border-slate-200 focus:border-emerald-600 border-t-0 border-l-0 border-r-0 px-0 py-3 focus:ring-0 bg-transparent text-slate-800 tracking-wider placeholder-slate-200" placeholder="NISN..." required autocomplete="off">
                                </div>
                                <div v-if="errorMessage" class="text-red-500 text-xs text-center mb-4 font-bold">{{ errorMessage }}</div>
                                
                                <button @click="checkNISN" :disabled="isLoading" type="button" class="mt-4 w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-emerald-600/30 transition-all flex justify-center items-center gap-2 text-lg disabled:opacity-50">
                                    <span v-if="isLoading"><i class="fas fa-spinner fa-spin"></i> Loading</span>
                                    <span v-else>Lanjut Verifikasi <i class="fas fa-shield-alt"></i></span>
                                </button>
                            </div>

                            <!-- Step 2: PIN -->
                            <div v-else-if="modalStep === 2" class="transition-all duration-300">
                                <button @click="goToStep(1)" type="button" class="absolute top-6 left-6 w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors">
                                    <i class="fas fa-arrow-left"></i>
                                </button>
                                
                                <div class="text-center mb-6 mt-2">
                                    <div class="w-14 h-14 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-full flex items-center justify-center text-2xl mx-auto mb-3 shadow-sm">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">{{ siswaInfo.nama }}</h4>
                                    <p class="text-xs font-bold text-slate-500 font-mono mt-1 bg-slate-100 inline-block px-3 py-1 rounded-full">{{ siswaInfo.nisn }}</p>
                                </div>
                                
                                <p class="text-center text-sm font-bold text-slate-700 mb-2">PIN Keamanan (6 Digit)</p>
                                <div class="mb-4">
                                    <input v-model="formTabungan.pin" type="password" maxlength="6" class="w-full text-center text-4xl tracking-[0.4em] font-black border-b-2 border-slate-200 focus:border-emerald-600 border-t-0 border-l-0 border-r-0 px-0 py-2 focus:ring-0 bg-transparent text-slate-800 placeholder-slate-200" placeholder="••••••" required autocomplete="off">
                                </div>
                                <div v-if="errorMessage" class="text-red-500 text-xs text-center mb-4 font-bold">{{ errorMessage }}</div>
                                
                                <button @click="checkPINAndSaldo" :disabled="isLoading" type="button" class="mt-4 w-full bg-slate-800 hover:bg-slate-900 text-amber-400 font-bold py-4 rounded-2xl shadow-lg shadow-slate-900/30 transition-all flex justify-center items-center gap-2 text-lg disabled:opacity-50">
                                    <span v-if="isLoading"><i class="fas fa-spinner fa-spin"></i> Loading</span>
                                    <span v-else><i class="fas fa-unlock-alt"></i> Buka Brankas</span>
                                </button>
                            </div>

                            <!-- Step 3: Result -->
                            <div v-else-if="modalStep === 3" class="transition-all duration-300">
                                <div class="text-center mb-5 mt-2">
                                    <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-2xl mx-auto mb-3 shadow-inner">
                                        <i class="fas fa-check-double"></i>
                                    </div>
                                    <h4 class="text-lg font-black text-slate-800">Akses Berhasil</h4>
                                </div>

                                <div class="bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-800 rounded-[2rem] p-6 text-white shadow-xl relative overflow-hidden border border-emerald-500">
                                    <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 rounded-full bg-amber-400 opacity-10 blur-2xl"></div>
                                    <i class="fas fa-wallet absolute -right-4 -bottom-4 text-7xl text-white opacity-10 rotate-[-15deg]"></i>
                                    
                                    <div class="flex justify-between items-center mb-6 relative z-10">
                                        <span class="text-[10px] font-bold uppercase tracking-widest text-emerald-100 opacity-90 border border-emerald-400/50 bg-emerald-800/30 px-3 py-1 rounded-full">Saldo Utama</span>
                                        <i class="fas fa-shield-check text-amber-400"></i>
                                    </div>
                                    
                                    <h2 class="text-3xl font-black tracking-tight mb-8 relative z-10">{{ saldoResult }}</h2>
                                    
                                    <div class="flex justify-between items-end relative z-10">
                                        <div>
                                            <p class="text-[10px] text-emerald-200 uppercase tracking-wider mb-1">Pemilik Rekening</p>
                                            <p class="font-bold text-sm truncate max-w-[150px] uppercase">{{ siswaInfo.nama }}</p>
                                        </div>
                                        <div class="text-right">
                                            <img :src="pathLogo" class="w-8 h-8 opacity-50 grayscale mix-blend-overlay" alt="Watermark">
                                        </div>
                                    </div>
                                </div>
                                
                                <button @click="resetModal" class="w-full mt-6 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-3 rounded-xl transition-colors text-sm">
                                    Tutup & Keluar Sistem
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

    </div>
</template>
