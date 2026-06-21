<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    ekskul: Object,
    jurnals: Array
});

const isModalOpen = ref(false);

const form = useForm({
    ekskul_id: props.ekskul.id,
    tanggal: new Date().toISOString().split('T')[0],
    materi_kegiatan: '',
    foto_1: null,
    foto_2: null
});

const submitJurnal = () => {
    form.post(route('guru.ekskul.jurnal.simpan'), {
        preserveScroll: true,
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset('materi_kegiatan', 'foto_1', 'foto_2');
        }
    });
};
</script>

<template>
    <Head :title="`Jurnal ${ekskul.nama_ekskul}`" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <Link :href="route('guru.ekskul.index')" class="text-indigo-600 hover:text-indigo-800 text-sm font-bold flex items-center gap-2 mb-2">
                        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                    </Link>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <div class="w-10 h-10 bg-white rounded-xl shadow-sm border border-gray-100 p-1">
                            <img :src="`/uploads/ekskul/${ekskul.logo || 'default_ekskul.png'}`" class="w-full h-full object-contain">
                        </div>
                        Jurnal Kegiatan {{ ekskul.nama_ekskul }}
                    </h2>
                </div>
                <button @click="isModalOpen = true" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 font-bold shadow-sm flex items-center gap-2">
                    <i class="fas fa-plus"></i> Buat Jurnal Baru
                </button>
            </div>

            <div v-if="$page.props.flash?.message" class="bg-green-50 text-green-800 p-4 rounded-2xl shadow-sm flex items-center gap-3">
                <i class="fas fa-info-circle"></i> {{ $page.props.flash.message }}
            </div>

            <!-- Timeline Jurnal -->
            <div v-if="jurnals.length === 0" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm p-12 text-center border border-gray-100 dark:border-gray-700">
                <i class="fas fa-book-open text-4xl mb-3 text-gray-400"></i>
                <h3 class="font-bold text-lg text-gray-900 dark:text-white">Belum Ada Jurnal</h3>
                <p class="text-gray-500 mt-1">Buat jurnal kegiatan pertama Anda.</p>
            </div>

            <div v-else class="space-y-6">
                <div v-for="j in jurnals" :key="j.id" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 px-6 py-4 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-2xl flex flex-col items-center justify-center font-bold leading-tight">
                                <span class="text-lg">{{ j.tanggal.split('-')[2] }}</span>
                                <span class="text-[10px] uppercase">{{ new Date(j.tanggal).toLocaleString('id-ID', { month: 'short' }) }}</span>
                            </div>
                            <div>
                                <div class="font-bold text-gray-900 dark:text-white text-lg">Pertemuan</div>
                                <div class="text-xs text-gray-500 font-mono">{{ j.tanggal }}</div>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <Link :href="route('guru.ekskul.absen_scan', j.id)" class="px-4 py-2 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 flex items-center gap-2 transition-colors">
                                <i class="fas fa-barcode"></i> Scan Presensi
                            </Link>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <div class="font-bold text-gray-700 dark:text-gray-300 mb-2">Materi / Agenda Kegiatan:</div>
                        <p class="text-gray-600 dark:text-gray-400 whitespace-pre-wrap">{{ j.materi_kegiatan }}</p>
                        
                        <div v-if="j.foto_1 || j.foto_2" class="mt-4 flex gap-4 overflow-x-auto pb-2">
                            <a v-if="j.foto_1" :href="`/uploads/ekskul/jurnal/${j.foto_1}`" target="_blank" class="shrink-0 w-32 h-32 rounded-2xl overflow-hidden border border-gray-200">
                                <img :src="`/uploads/ekskul/jurnal/${j.foto_1}`" class="w-full h-full object-cover hover:scale-110 transition-transform">
                            </a>
                            <a v-if="j.foto_2" :href="`/uploads/ekskul/jurnal/${j.foto_2}`" target="_blank" class="shrink-0 w-32 h-32 rounded-2xl overflow-hidden border border-gray-200">
                                <img :src="`/uploads/ekskul/jurnal/${j.foto_2}`" class="w-full h-full object-cover hover:scale-110 transition-transform">
                            </a>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700/50 p-4 border-t border-gray-100 dark:border-gray-700">
                        <div class="flex justify-between items-center mb-2">
                            <div class="font-bold text-sm text-gray-700">Rekap Presensi Anggota</div>
                        </div>
                        <div class="grid grid-cols-4 gap-2">
                            <div class="bg-green-100 text-green-700 p-2 rounded-xl text-center">
                                <div class="font-black text-xl">{{ j.hadir }}</div>
                                <div class="text-[10px] font-bold uppercase">Hadir</div>
                            </div>
                            <div class="bg-blue-100 text-blue-700 p-2 rounded-xl text-center">
                                <div class="font-black text-xl">{{ j.sakit }}</div>
                                <div class="text-[10px] font-bold uppercase">Sakit</div>
                            </div>
                            <div class="bg-yellow-100 text-yellow-700 p-2 rounded-xl text-center">
                                <div class="font-black text-xl">{{ j.izin }}</div>
                                <div class="text-[10px] font-bold uppercase">Izin</div>
                            </div>
                            <div class="bg-red-100 text-red-700 p-2 rounded-xl text-center">
                                <div class="font-black text-xl">{{ j.alpa }}</div>
                                <div class="text-[10px] font-bold uppercase">Alpa</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Modal Buat Jurnal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center shrink-0">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white">Buat Jurnal Baru</h3>
                    <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                </div>
                <div class="p-6 overflow-y-auto flex-1">
                    <form @submit.prevent="submitJurnal" id="jurnalForm">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Tanggal Kegiatan</label>
                                <input type="date" v-model="form.tanggal" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Materi / Agenda Kegiatan</label>
                                <textarea v-model="form.materi_kegiatan" rows="4" placeholder="Tuliskan materi atau kegiatan yang dilakukan hari ini..." class="w-full rounded-xl border-gray-300 focus:border-indigo-500 shadow-sm" required></textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Foto 1 (Opsional)</label>
                                    <input type="file" @input="form.foto_1 = $event.target.files[0]" accept="image/*" class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 cursor-pointer">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Foto 2 (Opsional)</label>
                                    <input type="file" @input="form.foto_2 = $event.target.files[0]" accept="image/*" class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 cursor-pointer">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6 p-4 bg-yellow-50 text-yellow-800 rounded-xl text-sm border border-yellow-200">
                            <i class="fas fa-exclamation-triangle mr-2"></i> <b>Penting:</b> Saat jurnal disimpan, seluruh anggota aktif akan otomatis tercatat sebagai <b>ALPA</b>. Anda harus melakukan Scan Presensi untuk mengubah statusnya menjadi Hadir.
                        </div>
                    </form>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700 shrink-0 flex justify-end gap-3">
                    <button type="button" @click="isModalOpen = false" class="px-5 py-2.5 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold">Batal</button>
                    <button type="submit" form="jurnalForm" :disabled="form.processing" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold flex items-center gap-2">
                        <i v-if="form.processing" class="fas fa-spinner fa-spin"></i>
                        <i v-else class="fas fa-save"></i>
                        Simpan Jurnal
                    </button>
                </div>
            </div>
        </div>

    </DashboardLayout>
</template>
