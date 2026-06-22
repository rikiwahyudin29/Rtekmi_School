<script setup>
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    riwayat_pelanggaran: Array,
    sisa_poin: Number,
    status_sp: String,
    set_sp: Object
});

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};
</script>

<template>
    <Head title="Kedisiplinan Saya" />

    <DashboardLayout>
        <div class="py-8">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Header Card -->
                <div class="bg-gradient-to-r from-red-600 to-rose-700 rounded-3xl p-8 shadow-lg relative overflow-hidden text-white border border-red-500">
                    <i class="fas fa-shield-alt text-white/10 text-9xl absolute -right-10 -bottom-10 pointer-events-none transform -rotate-12"></i>
                    <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                        <div>
                            <h2 class="text-3xl font-black mb-2">Kedisiplinan Saya</h2>
                            <p class="text-red-100 max-w-lg text-sm">
                                Rekam jejak tata tertib dan poin kedisiplinan Anda. Jaga tingkah laku dan patuhi peraturan sekolah untuk masa depan yang lebih baik.
                            </p>
                        </div>
                        
                        <!-- Poin Counter -->
                        <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/20 flex flex-col gap-2 min-w-[200px]">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-full bg-white/20 flex items-center justify-center">
                                    <i class="fas fa-star text-2xl text-yellow-300"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] uppercase tracking-widest text-red-200 font-bold mb-1">Sisa Poin Anda</p>
                                    <h3 class="text-3xl font-black">{{ sisa_poin }}</h3>
                                </div>
                            </div>
                            <div class="bg-white/20 px-3 py-1.5 rounded-xl border border-white/30 mt-2 text-center">
                                <p class="text-xs font-bold uppercase tracking-widest">
                                    Status: <span class="text-yellow-300 font-black">{{ status_sp }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Peringatan Visual berdasarkan Status SP -->
                <div v-if="status_sp === 'SP 3 (Dikeluarkan)'" class="bg-red-50 border border-red-200 rounded-2xl p-4 flex items-start text-red-800 shadow-sm animate-fade-in-down">
                    <i class="fas fa-ban text-red-600 mt-1 mr-3 text-xl"></i>
                    <div>
                        <p class="font-bold text-red-900">Perhatian Sangat Kritis!</p>
                        <p class="text-sm mt-1">Sisa poin Anda sudah habis (SP 3). Hubungi Bimbingan Konseling atau Wali Kelas sekarang juga.</p>
                    </div>
                </div>
                <div v-else-if="status_sp === 'SP 2' || status_sp === 'SP 1'" class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4 flex items-start text-yellow-800 shadow-sm animate-fade-in-down">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mt-1 mr-3 text-xl"></i>
                    <div>
                        <p class="font-bold text-yellow-900">Peringatan: Anda berada dalam status {{ status_sp }}</p>
                        <p class="text-sm mt-1">Sisa poin Anda adalah {{ sisa_poin }}. Harap segera perbaiki perilaku agar tidak mendapat sanksi lebih berat.</p>
                    </div>
                </div>
                <div v-else class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-center text-emerald-800 shadow-sm">
                    <i class="fas fa-check-circle text-emerald-600 mr-3 text-xl"></i>
                    <p class="font-bold">Luar Biasa! Status kedisiplinan Anda aman. Pertahankan prestasimu!</p>
                </div>

                <!-- Riwayat Pelanggaran Table -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mt-6">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <h3 class="font-black text-gray-900 text-lg">
                            <i class="fas fa-history text-gray-400 mr-2"></i> Riwayat Pelanggaran
                        </h3>
                    </div>
                    
                    <div v-if="riwayat_pelanggaran.length === 0" class="p-12 text-center text-gray-500">
                        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-100">
                            <i class="fas fa-clipboard-check text-3xl text-emerald-400"></i>
                        </div>
                        <p class="font-bold text-gray-700">Bersih dari Sanksi</p>
                        <p class="text-sm mt-1">Belum ada riwayat pelanggaran tata tertib.</p>
                    </div>

                    <div v-else class="divide-y divide-gray-100">
                        <div v-for="item in riwayat_pelanggaran" :key="item.id" class="p-6 hover:bg-gray-50 transition-colors flex flex-col sm:flex-row gap-4 sm:items-center">
                            
                            <!-- Ikon Status -->
                            <div class="w-12 h-12 rounded-full flex items-center justify-center shrink-0 border shadow-sm bg-rose-50 text-rose-600 border-rose-100">
                                <i class="fas fa-times-circle text-xl"></i>
                            </div>

                            <!-- Detail -->
                            <div class="flex-1">
                                <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-2 mb-1">
                                    <h4 class="font-bold text-gray-900">{{ item.pelanggaran?.nama_pelanggaran || 'Pelanggaran Tidak Diketahui' }}</h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 self-start">
                                        +{{ item.pelanggaran?.poin || 0 }} Poin
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 mb-2">
                                    {{ item.catatan || 'Tidak ada deskripsi spesifik.' }}
                                </p>
                                <div class="flex flex-wrap gap-4 text-xs font-medium text-gray-500">
                                    <span class="flex items-center gap-1">
                                        <i class="far fa-calendar-alt"></i> {{ formatDate(item.tanggal) }}
                                    </span>
                                    <span class="flex items-center gap-1" v-if="item.pelapor">
                                        <i class="far fa-user"></i> Dilaporkan: {{ item.pelapor?.nama_lengkap || 'Sistem' }}
                                    </span>
                                    <span class="flex items-center gap-1" v-if="item.status">
                                        <i class="fas fa-info-circle"></i> Status: <span class="uppercase tracking-wider font-bold">{{ item.status }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </DashboardLayout>
</template>
