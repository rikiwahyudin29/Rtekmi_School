<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    ekskul: Object,
    jurnal: Array
});

const isModalOpen = ref(false);

const form = useForm({
    ekskul_id: props.ekskul.id,
    tanggal: new Date().toISOString().split('T')[0],
    materi_kegiatan: '',
    foto_1: null
});

const submitJurnal = () => {
    form.post(route('guru.ekskul.jurnal.simpan'), {
        preserveScroll: true,
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset('materi_kegiatan', 'foto_1');
        }
    });
};
</script>

<template>
    <Head :title="`Jurnal & Absensi - ${ekskul.nama_ekskul}`" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <Link :href="route('guru.ekskul.index')" class="w-10 h-10 rounded-full bg-white dark:bg-gray-800 shadow-sm flex items-center justify-center text-gray-500 hover:text-orange-600 transition-colors border border-gray-100 dark:border-gray-700">
                            <i class="fas fa-arrow-left"></i>
                        </Link>
                        <span>Jurnal & Absensi: {{ ekskul.nama_ekskul }}</span>
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1 ml-13">
                        Catat kegiatan harian dan lakukan absensi kehadiran anggota.
                    </p>
                </div>
                <button @click="isModalOpen = true" class="px-5 py-2.5 bg-orange-600 text-white rounded-xl hover:bg-orange-700 font-bold shadow-lg shadow-orange-500/30 flex items-center gap-2 transition-all">
                    <i class="fas fa-plus"></i> Buat Jurnal Baru
                </button>
            </div>

            <!-- Flash Message -->
            <div v-if="$page.props.flash?.message" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-400 p-4 rounded-2xl shadow-sm flex items-center gap-3">
                <i class="fas fa-check-circle"></i> {{ $page.props.flash.message }}
            </div>

            <!-- List Jurnal -->
            <div v-if="jurnal.length === 0" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-12 text-center text-gray-500 dark:text-gray-400">
                <div class="w-20 h-20 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300 dark:text-gray-600">
                    <i class="fas fa-book-open text-3xl"></i>
                </div>
                <p class="text-lg font-medium">Belum ada jurnal kegiatan.</p>
                <p class="text-sm mt-1">Klik tombol Buat Jurnal Baru untuk memulai kegiatan hari ini.</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <div v-for="j in jurnal" :key="j.id" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden hover:border-orange-500 transition-colors duration-300 flex flex-col">
                    <div class="h-40 bg-gray-100 dark:bg-gray-700 relative group overflow-hidden">
                        <img v-if="j.foto_1" :src="`/uploads/ekskul_jurnal/${j.foto_1}`" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <div v-else class="w-full h-full flex flex-col items-center justify-center text-gray-400 dark:text-gray-500">
                            <i class="fas fa-camera text-3xl mb-2"></i>
                            <span class="text-xs">Tidak ada foto</span>
                        </div>
                        <div class="absolute top-3 right-3 bg-white/90 dark:bg-gray-900/90 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-bold text-gray-700 dark:text-gray-200 shadow-sm flex items-center gap-1">
                            <i class="far fa-calendar-alt text-orange-500"></i> {{ j.tanggal }}
                        </div>
                    </div>
                    <div class="p-5 flex-1 flex flex-col">
                        <h3 class="font-bold text-gray-900 dark:text-white line-clamp-2 mb-4 flex-1" :title="j.materi_kegiatan">{{ j.materi_kegiatan }}</h3>
                        
                        <div class="flex items-center justify-between mb-5 bg-gray-50 dark:bg-gray-700/50 p-3 rounded-xl border border-gray-100 dark:border-gray-700">
                            <div class="text-sm">
                                <div class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider font-bold">Kehadiran</div>
                                <div class="text-gray-900 dark:text-white font-black mt-0.5">
                                    <span class="text-green-600 dark:text-green-400">{{ j.hadir }}</span> / {{ j.total_anggota }}
                                </div>
                            </div>
                            <!-- Circular Progress Mini -->
                            <div class="relative w-10 h-10 flex items-center justify-center bg-white dark:bg-gray-800 rounded-full shadow-sm">
                                <svg class="w-10 h-10 transform -rotate-90">
                                    <circle cx="20" cy="20" r="16" stroke="currentColor" stroke-width="3" fill="transparent" class="text-gray-100 dark:text-gray-700" />
                                    <circle cx="20" cy="20" r="16" stroke="currentColor" stroke-width="3" fill="transparent" :stroke-dasharray="100" :stroke-dashoffset="100 - (j.total_anggota > 0 ? (j.hadir/j.total_anggota)*100 : 0)" class="text-green-500 transition-all duration-1000" />
                                </svg>
                                <span class="absolute text-[9px] font-bold text-gray-700 dark:text-gray-300">{{ j.total_anggota > 0 ? Math.round((j.hadir/j.total_anggota)*100) : 0 }}%</span>
                            </div>
                        </div>

                        <Link :href="route('guru.ekskul.absen_scan', j.id)" class="w-full py-2.5 bg-orange-50 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 hover:bg-orange-600 hover:text-white dark:hover:bg-orange-600 dark:hover:text-white rounded-xl text-sm font-bold text-center transition-all border border-orange-200 dark:border-orange-800 hover:border-transparent flex items-center justify-center gap-2">
                            <i class="fas fa-qrcode"></i> Scan Absen
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Buat Jurnal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-700/50">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-pen text-orange-500"></i> Buat Jurnal Kegiatan
                    </h3>
                    <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form @submit.prevent="submitJurnal" class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Tanggal Kegiatan</label>
                            <input type="date" v-model="form.tanggal" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-orange-500 focus:ring-orange-500 shadow-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Materi / Agenda Kegiatan</label>
                            <textarea v-model="form.materi_kegiatan" rows="3" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-orange-500 focus:ring-orange-500 shadow-sm" placeholder="Contoh: Latihan dasar kepemimpinan..." required></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Foto Dokumentasi (Opsional)</label>
                            <input type="file" @input="form.foto_1 = $event.target.files[0]" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 dark:file:bg-orange-900/30 dark:file:text-orange-400 cursor-pointer">
                            <div v-if="form.errors.foto_1" class="text-red-500 text-xs mt-1">{{ form.errors.foto_1 }}</div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-3 mt-8">
                        <button type="button" @click="isModalOpen = false" class="px-5 py-2.5 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-orange-600 hover:bg-orange-700 shadow-lg shadow-orange-500/30 transition-colors disabled:opacity-50 flex items-center gap-2">
                            <i v-if="form.processing" class="fas fa-spinner fa-spin"></i>
                            <i v-else class="fas fa-save"></i>
                            Simpan Jurnal & Buka Absensi
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </DashboardLayout>
</template>
