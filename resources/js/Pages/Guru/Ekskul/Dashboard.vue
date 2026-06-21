<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    ekskuls: Array
});

// Pilih ekskul pertama sebagai default jika ada
const activeEkskulId = ref(props.ekskuls.length > 0 ? props.ekskuls[0].id : null);
const activeEkskul = computed(() => props.ekskuls.find(e => e.id === activeEkskulId.value));
</script>

<template>
    <Head title="Dashboard Pembina Ekskul" />

    <DashboardLayout>
        <div class="space-y-8">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
                <div>
                    <h2 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                        Ruang Pembina Ekskul
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Kelola administrasi, absensi, dan prestasi ekstrakurikuler binaan Anda.
                    </p>
                </div>
                
                <!-- Ekskul Selector (jika lebih dari 1) -->
                <div v-if="ekskuls.length > 1" class="flex p-1 bg-gray-100 dark:bg-gray-800 rounded-2xl">
                    <button 
                        v-for="e in ekskuls" 
                        :key="e.id"
                        @click="activeEkskulId = e.id"
                        :class="[
                            'px-5 py-2.5 rounded-xl text-sm font-bold transition-all duration-300',
                            activeEkskulId === e.id 
                                ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-400 shadow-sm' 
                                : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'
                        ]"
                    >
                        {{ e.nama_ekskul }}
                    </button>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="ekskuls.length === 0" class="bg-indigo-50 dark:bg-indigo-900/20 p-12 rounded-3xl border border-indigo-100 dark:border-indigo-800 flex flex-col items-center justify-center text-center">
                <div class="w-24 h-24 bg-white dark:bg-gray-800 rounded-full flex items-center justify-center shadow-sm mb-6 text-indigo-500">
                    <i class="fas fa-ghost text-4xl"></i>
                </div>
                <h3 class="font-black text-2xl text-indigo-900 dark:text-indigo-100 mb-2">Belum Ada Tugas</h3>
                <p class="text-indigo-700 dark:text-indigo-300 max-w-md">Anda belum ditugaskan sebagai pembina ekstrakurikuler manapun. Silakan hubungi Wakasek Kesiswaan.</p>
            </div>

            <div v-else-if="activeEkskul" class="space-y-8">
                <!-- HERO CARD -->
                <div class="relative overflow-hidden rounded-[2.5rem] bg-gray-900 shadow-xl border border-gray-800 group">
                    <!-- Background Elements -->
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 via-purple-700 to-gray-900 opacity-90"></div>
                    <div class="absolute -top-24 -right-24 w-96 h-96 bg-indigo-500 rounded-full mix-blend-multiply filter blur-3xl opacity-50 group-hover:scale-110 transition-transform duration-700"></div>
                    <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-50 group-hover:scale-110 transition-transform duration-700 delay-100"></div>
                    
                    <div class="relative p-8 md:p-12 z-10 flex flex-col md:flex-row items-center gap-8">
                        <div class="shrink-0 relative">
                            <div class="absolute inset-0 bg-white/20 blur-xl rounded-full"></div>
                            <div class="w-32 h-32 md:w-40 md:h-40 bg-white rounded-3xl p-2 shadow-2xl relative border-4 border-white/10 backdrop-blur-sm">
                                <img :src="`/uploads/ekskul/${activeEkskul.logo || 'default_ekskul.png'}`" @error="$event.target.src='https://ui-avatars.com/api/?background=random&name='+activeEkskul.nama_ekskul" class="w-full h-full object-contain rounded-2xl">
                            </div>
                        </div>
                        <div class="text-center md:text-left text-white">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-bold uppercase tracking-wider mb-4">
                                <i class="fas fa-star text-yellow-400"></i> Unit Ekstrakurikuler
                            </div>
                            <h1 class="text-4xl md:text-5xl font-black mb-4 tracking-tight drop-shadow-md">{{ activeEkskul.nama_ekskul }}</h1>
                            <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 text-white/80">
                                <div class="flex items-center gap-2 bg-black/20 px-4 py-2 rounded-xl backdrop-blur-sm">
                                    <i class="far fa-calendar-alt"></i> Hari: <b class="text-white">{{ activeEkskul.hari }}</b>
                                </div>
                                <div class="flex items-center gap-2 bg-black/20 px-4 py-2 rounded-xl backdrop-blur-sm">
                                    <i class="far fa-clock"></i> Jam: <b class="text-white">{{ activeEkskul.jam }}</b>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- QUICK STATS -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 hover:-translate-y-1 transition-transform duration-300">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 flex items-center justify-center text-xl">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="text-3xl font-black text-gray-900 dark:text-white mb-1">{{ activeEkskul.jml_anggota }}</div>
                        <div class="text-sm font-bold text-gray-500 uppercase tracking-wide">Anggota Aktif</div>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-orange-100 dark:border-orange-900/30 hover:-translate-y-1 transition-transform duration-300 relative overflow-hidden">
                        <div v-if="activeEkskul.jml_pending > 0" class="absolute top-0 right-0 w-24 h-24 bg-orange-500/10 rounded-bl-full -mr-4 -mt-4"></div>
                        <div class="flex justify-between items-start mb-4 relative z-10">
                            <div class="w-12 h-12 rounded-2xl bg-orange-50 dark:bg-orange-900/30 text-orange-600 flex items-center justify-center text-xl">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <span v-if="activeEkskul.jml_pending > 0" class="flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-orange-500"></span>
                            </span>
                        </div>
                        <div class="text-3xl font-black text-gray-900 dark:text-white mb-1 relative z-10">{{ activeEkskul.jml_pending }}</div>
                        <div class="text-sm font-bold text-orange-500 uppercase tracking-wide relative z-10">Pendaftar Baru</div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 hover:-translate-y-1 transition-transform duration-300">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 flex items-center justify-center text-xl">
                                <i class="fas fa-book-open"></i>
                            </div>
                        </div>
                        <div class="text-3xl font-black text-gray-900 dark:text-white mb-1">{{ activeEkskul.total_jurnal }}</div>
                        <div class="text-sm font-bold text-gray-500 uppercase tracking-wide">Total Pertemuan</div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 hover:-translate-y-1 transition-transform duration-300">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-12 h-12 rounded-2xl bg-yellow-50 dark:bg-yellow-900/30 text-yellow-600 flex items-center justify-center text-xl">
                                <i class="fas fa-trophy"></i>
                            </div>
                        </div>
                        <div class="text-3xl font-black text-gray-900 dark:text-white mb-1">{{ activeEkskul.total_prestasi }}</div>
                        <div class="text-sm font-bold text-gray-500 uppercase tracking-wide">Total Prestasi</div>
                    </div>
                </div>

                <!-- ACTION MENUS -->
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mt-10 mb-4 px-2">Menu & Manajemen Ekstrakurikuler</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Menu: Anggota -->
                    <Link :href="route('guru.ekskul.anggota', activeEkskul.id)" class="group flex items-center p-6 bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 hover:border-indigo-300 hover:shadow-lg transition-all duration-300 relative overflow-hidden">
                        <div class="absolute right-0 top-0 w-32 h-32 bg-gradient-to-bl from-indigo-50 to-transparent dark:from-indigo-900/20 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
                        <div class="w-16 h-16 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 rounded-2xl flex items-center justify-center text-2xl shadow-sm shrink-0 mr-6 group-hover:scale-110 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">Manajemen Anggota</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">Terima atau tolak pendaftar baru, lihat daftar anggota aktif, dan keluarkan anggota.</p>
                        </div>
                    </Link>

                    <!-- Menu: Jurnal & Absensi -->
                    <Link :href="route('guru.ekskul.jurnal', activeEkskul.id)" class="group flex items-center p-6 bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 hover:border-blue-300 hover:shadow-lg transition-all duration-300 relative overflow-hidden">
                        <div class="absolute right-0 top-0 w-32 h-32 bg-gradient-to-bl from-blue-50 to-transparent dark:from-blue-900/20 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
                        <div class="w-16 h-16 bg-blue-50 dark:bg-blue-900/30 text-blue-600 rounded-2xl flex items-center justify-center text-2xl shadow-sm shrink-0 mr-6 group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white transition-all duration-300">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-1 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Jurnal & Smart Scanner</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">Catat jurnal pertemuan mingguan dan absen anggota via scanner barcode.</p>
                        </div>
                    </Link>

                    <!-- Menu: Prestasi -->
                    <Link :href="route('guru.ekskul.prestasi', activeEkskul.id)" class="group flex items-center p-6 bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 hover:border-yellow-300 hover:shadow-lg transition-all duration-300 relative overflow-hidden">
                        <div class="absolute right-0 top-0 w-32 h-32 bg-gradient-to-bl from-yellow-50 to-transparent dark:from-yellow-900/20 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
                        <div class="w-16 h-16 bg-yellow-50 dark:bg-yellow-900/30 text-yellow-600 rounded-2xl flex items-center justify-center text-2xl shadow-sm shrink-0 mr-6 group-hover:scale-110 group-hover:bg-yellow-500 group-hover:text-white transition-all duration-300">
                            <i class="fas fa-medal"></i>
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-1 group-hover:text-yellow-600 dark:group-hover:text-yellow-400 transition-colors">Galeri Prestasi</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">Upload dan dokumentasikan piala, kejuaraan, atau penghargaan yang diraih ekskul.</p>
                        </div>
                    </Link>

                    <!-- Menu: Penilaian -->
                    <Link :href="route('guru.ekskul.penilaian', activeEkskul.id)" class="group flex items-center p-6 bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 hover:border-green-300 hover:shadow-lg transition-all duration-300 relative overflow-hidden">
                        <div class="absolute right-0 top-0 w-32 h-32 bg-gradient-to-bl from-green-50 to-transparent dark:from-green-900/20 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
                        <div class="w-16 h-16 bg-green-50 dark:bg-green-900/30 text-green-600 rounded-2xl flex items-center justify-center text-2xl shadow-sm shrink-0 mr-6 group-hover:scale-110 group-hover:bg-green-600 group-hover:text-white transition-all duration-300">
                            <i class="fas fa-star"></i>
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-1 group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors">Penilaian & E-Sertifikat</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">Input nilai ekskul untuk Dapodik dan cetak E-Sertifikat otomatis untuk siswa aktif.</p>
                        </div>
                    </Link>

                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
