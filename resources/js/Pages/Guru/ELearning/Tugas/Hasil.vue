<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    tugas: Object,
    siswa: Array,
    pengumpulan: Object,
});

const form = useForm({
    tugas_id: props.tugas.id,
    siswa_id: '',
    nilai: '',
    komentar_guru: '',
});

const isFormOpen = ref(false);
const selectedSiswa = ref(null);

const openGradeModal = (s) => {
    selectedSiswa.value = s;
    form.siswa_id = s.id;
    
    // Populate if already graded
    const data = props.pengumpulan[s.id];
    if (data) {
        form.nilai = data.nilai || '';
        form.komentar_guru = data.komentar_guru || '';
    } else {
        form.nilai = '';
        form.komentar_guru = '';
    }

    isFormOpen.value = true;
};

const closeGradeModal = () => {
    isFormOpen.value = false;
    selectedSiswa.value = null;
    form.reset();
};

const submitGrade = () => {
    form.post(route('guru.elearning.tugas.simpan_nilai'), {
        preserveScroll: true,
        onSuccess: () => closeGradeModal(),
    });
};

const formatTanggal = (tgl) => {
    if (!tgl) return '-';
    return new Date(tgl).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <Head title="Hasil Pengumpulan Tugas" />
    <DashboardLayout>
        <div class="space-y-6">
            
            <div class="flex items-center gap-4">
                <Link :href="route('guru.elearning.tugas.index')" class="w-10 h-10 bg-white border border-gray-200 rounded-xl flex items-center justify-center text-gray-500 hover:bg-gray-50 transition shadow-sm">
                    <i class="fas fa-arrow-left"></i>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Koreksi Tugas</h1>
                    <p class="text-sm text-gray-500 mt-1">Berikan nilai pada tugas yang dikumpulkan siswa.</p>
                </div>
            </div>

            <!-- Detail Tugas Card -->
            <div class="bg-indigo-600 rounded-2xl p-6 text-white shadow-md relative overflow-hidden">
                <div class="relative z-10">
                    <div class="flex gap-2 mb-3">
                        <span class="px-2.5 py-1 bg-white/20 backdrop-blur-sm text-xs font-bold rounded-md">{{ tugas.nama_kelas }}</span>
                        <span class="px-2.5 py-1 bg-white/20 backdrop-blur-sm text-xs font-bold rounded-md">{{ tugas.nama_mapel }}</span>
                    </div>
                    <h2 class="text-3xl font-black mb-2">{{ tugas.judul }}</h2>
                    <p class="text-indigo-100 mb-4">{{ tugas.deskripsi }}</p>
                    <div class="flex items-center gap-4 text-sm text-indigo-100 bg-white/10 p-3 rounded-xl inline-flex backdrop-blur-sm">
                        <span><i class="fas fa-clock mr-1.5"></i> Deadline: <strong class="text-white">{{ formatTanggal(tugas.deadline) }}</strong></span>
                    </div>
                </div>
                <i class="fas fa-clipboard-check absolute -right-10 -bottom-10 text-9xl text-indigo-500/30 -rotate-12"></i>
            </div>

            <!-- List Siswa Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="font-bold text-gray-800 text-lg">Daftar Pengumpulan Siswa</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-200">
                                <th class="p-4 font-bold">No</th>
                                <th class="p-4 font-bold">Nama Siswa</th>
                                <th class="p-4 font-bold">Status</th>
                                <th class="p-4 font-bold">Waktu Kumpul</th>
                                <th class="p-4 font-bold">File Jawaban</th>
                                <th class="p-4 font-bold text-center">Nilai</th>
                                <th class="p-4 font-bold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            <tr v-for="(s, index) in siswa" :key="s.id" class="hover:bg-gray-50/50 transition">
                                <td class="p-4 text-gray-500">{{ index + 1 }}</td>
                                <td class="p-4">
                                    <p class="font-bold text-gray-800">{{ s.nama_lengkap }}</p>
                                    <p class="text-xs text-gray-500">NIS: {{ s.nis }}</p>
                                </td>
                                
                                <!-- Status -->
                                <td class="p-4">
                                    <span v-if="pengumpulan[s.id]" 
                                        :class="['px-2.5 py-1 text-xs font-bold rounded-full', 
                                            pengumpulan[s.id].status_kumpul === 'Tepat Waktu' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700']">
                                        {{ pengumpulan[s.id].status_kumpul }}
                                    </span>
                                    <span v-else class="px-2.5 py-1 text-xs font-bold rounded-full bg-rose-50 text-rose-600 border border-rose-100">
                                        Belum Kumpul
                                    </span>
                                </td>

                                <!-- Waktu -->
                                <td class="p-4 text-gray-600">
                                    {{ pengumpulan[s.id] ? formatTanggal(pengumpulan[s.id].created_at) : '-' }}
                                </td>

                                <!-- File -->
                                <td class="p-4">
                                    <a v-if="pengumpulan[s.id] && pengumpulan[s.id].file_jawaban" :href="`/uploads/tugas_kumpul/${pengumpulan[s.id].file_jawaban}`" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-700 text-xs font-bold rounded-lg hover:bg-blue-100 transition">
                                        <i class="fas fa-file-download"></i> Download
                                    </a>
                                    <span v-else class="text-gray-400 italic text-xs">-</span>
                                </td>

                                <!-- Nilai -->
                                <td class="p-4 text-center">
                                    <span v-if="pengumpulan[s.id] && pengumpulan[s.id].nilai !== null" class="inline-block px-3 py-1 bg-indigo-50 text-indigo-700 font-black rounded-lg border border-indigo-100 text-base">
                                        {{ pengumpulan[s.id].nilai }}
                                    </span>
                                    <span v-else class="text-gray-400 italic text-xs">Belum dinilai</span>
                                </td>

                                <!-- Aksi -->
                                <td class="p-4 text-right">
                                    <button @click="openGradeModal(s)" class="px-3 py-1.5 bg-gray-100 hover:bg-indigo-600 hover:text-white text-gray-700 text-xs font-bold rounded-lg transition">
                                        Beri Nilai
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Offcanvas / Modal Nilai -->
        <div v-if="isFormOpen" class="fixed inset-0 z-50 flex justify-end">
            <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" @click="closeGradeModal"></div>
            <div class="relative w-full max-w-md bg-white h-full shadow-2xl flex flex-col animate-slide-in-right">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="font-bold text-gray-800 text-lg">Input Nilai Siswa</h3>
                    <button @click="closeGradeModal" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 text-gray-600 hover:bg-rose-100 hover:text-rose-600 transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="p-6 flex-1 overflow-y-auto">
                    <div class="mb-6 p-4 bg-indigo-50 rounded-xl border border-indigo-100">
                        <p class="text-xs text-indigo-500 font-bold uppercase tracking-wider mb-1">Nama Siswa</p>
                        <p class="font-black text-indigo-900 text-xl">{{ selectedSiswa?.nama_lengkap }}</p>
                        <p class="text-sm text-indigo-600 mt-1">NIS: {{ selectedSiswa?.nis }}</p>
                    </div>

                    <form @submit.prevent="submitGrade" class="space-y-5">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Nilai Angka (0-100) <span class="text-rose-500">*</span></label>
                            <input type="number" v-model="form.nilai" required min="0" max="100" class="w-full text-2xl font-black text-center py-4 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm text-indigo-600" placeholder="0">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Komentar / Catatan Guru</label>
                            <textarea v-model="form.komentar_guru" rows="4" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm" placeholder="Tulis catatan untuk siswa (opsional)..."></textarea>
                        </div>
                    </form>
                </div>

                <div class="p-6 border-t border-gray-100 bg-gray-50 flex gap-3">
                    <button @click="closeGradeModal" class="flex-1 px-4 py-3 bg-white border border-gray-300 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition">Batal</button>
                    <button @click="submitGrade" :disabled="form.processing" class="flex-1 px-4 py-3 bg-indigo-600 text-white font-bold rounded-xl shadow-md hover:bg-indigo-700 transition">
                        Simpan Nilai
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<style scoped>
.animate-slide-in-right {
    animation: slideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes slideIn {
    from { transform: translateX(100%); }
    to { transform: translateX(0); }
}
</style>
