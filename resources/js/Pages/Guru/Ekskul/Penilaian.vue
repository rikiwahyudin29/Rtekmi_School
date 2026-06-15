<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref, onMounted } from 'vue';

const props = defineProps({
    ekskul: Object,
    anggota: Array
});

const form = useForm({
    ekskul_id: props.ekskul.id,
    semester: 1, // Default Ganjil
    nilai_data: []
});

onMounted(() => {
    initForm();
});

const initForm = () => {
    const initData = [];
    if (props.anggota) {
        props.anggota.forEach(a => {
            initData.push({
                siswa_id: a.siswa_id,
                nilai_huruf: a.nilai ? a.nilai.nilai_huruf : 'B',
                persen_hadir: a.persen_hadir
            });
        });
    }
    form.nilai_data = initData;
};

const getNilaiModel = (siswa_id) => {
    return form.nilai_data.find(n => n.siswa_id === siswa_id);
};

const submitNilai = () => {
    form.post(route('guru.ekskul.penilaian.simpan'), {
        preserveScroll: true
    });
};
</script>

<template>
    <Head :title="`Penilaian Ekskul - ${ekskul.nama_ekskul}`" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <Link :href="route('guru.ekskul.index')" class="w-10 h-10 rounded-full bg-white dark:bg-gray-800 shadow-sm flex items-center justify-center text-gray-500 hover:text-orange-600 transition-colors border border-gray-100 dark:border-gray-700">
                            <i class="fas fa-arrow-left"></i>
                        </Link>
                        <span>Penilaian & E-Sertifikat: {{ ekskul.nama_ekskul }}</span>
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1 ml-13">
                        Input nilai akhir semester untuk Rapor Dapodik dan cetak E-Sertifikat.
                    </p>
                </div>
                <button @click="submitNilai" :disabled="form.processing || anggota.length === 0" class="px-6 py-2.5 bg-orange-600 text-white rounded-xl hover:bg-orange-700 font-bold shadow-lg shadow-orange-500/30 flex items-center gap-2 transition-all disabled:opacity-50">
                    <i class="fas" :class="form.processing ? 'fa-spinner fa-spin' : 'fa-save'"></i> 
                    {{ form.processing ? 'Menyimpan & Menerbitkan Sertifikat...' : 'Simpan & Terbitkan Sertifikat' }}
                </button>
            </div>

            <!-- Flash Message -->
            <div v-if="$page.props.flash?.message" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-400 p-4 rounded-2xl shadow-sm flex items-center gap-3">
                <i class="fas fa-check-circle text-xl"></i> 
                <div>
                    <h4 class="font-bold">Sukses</h4>
                    <p class="text-sm mt-0.5">{{ $page.props.flash.message }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-xl flex items-center justify-center text-xl shrink-0">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div>
                    <h4 class="font-bold text-gray-900 dark:text-white">Syarat Kelayakan E-Sertifikat</h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Siswa berhak mendapatkan E-Sertifikat secara otomatis jika <strong class="text-gray-700 dark:text-gray-300">Nilai Minimal B</strong> dan <strong class="text-gray-700 dark:text-gray-300">Kehadiran Minimal 80%</strong>. Deskripsi capaian Dapodik akan digenerate secara otomatis.</p>
                </div>
                <div class="ml-auto w-32 shrink-0">
                    <select v-model="form.semester" class="w-full text-sm font-bold rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-orange-500 focus:ring-orange-500">
                        <option :value="1">Semester Ganjil</option>
                        <option :value="2">Semester Genap</option>
                    </select>
                </div>
            </div>

            <!-- Tabel Input Nilai -->
            <div v-if="anggota && anggota.length > 0" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-4 font-bold w-16">No</th>
                                <th class="px-6 py-4 font-bold">Nama Siswa</th>
                                <th class="px-6 py-4 font-bold text-center">Persentase Kehadiran</th>
                                <th class="px-6 py-4 font-bold text-center w-48">Nilai Huruf (Dapodik)</th>
                                <th class="px-6 py-4 font-bold text-center">Status Kelayakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="(a, index) in anggota" :key="a.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 text-sm text-gray-500">{{ index + 1 }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900 dark:text-white">{{ a.nama_lengkap }}</div>
                                    <div class="text-xs text-gray-500">{{ a.nama_kelas || '-' }}</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="font-black text-lg" :class="a.persen_hadir >= 80 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                            {{ a.persen_hadir }}%
                                        </div>
                                        <div class="w-16 bg-gray-200 dark:bg-gray-700 h-1.5 rounded-full mt-1 overflow-hidden">
                                            <div class="h-full rounded-full" :class="a.persen_hadir >= 80 ? 'bg-green-500' : 'bg-red-500'" :style="`width: ${a.persen_hadir}%`"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <select v-if="getNilaiModel(a.siswa_id)" v-model="getNilaiModel(a.siswa_id).nilai_huruf" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-orange-500 focus:border-orange-500 text-center font-bold">
                                        <option value="A">Sangat Baik (A)</option>
                                        <option value="B">Baik (B)</option>
                                        <option value="C">Cukup (C)</option>
                                        <option value="D">Kurang (D)</option>
                                    </select>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div v-if="a.nilai && a.nilai.layak_sertifikat === 'Y'" class="flex flex-col items-center gap-2">
                                        <span class="bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 px-3 py-1 rounded-full text-xs font-bold border border-green-200 dark:border-green-800">
                                            <i class="fas fa-certificate"></i> Memenuhi Syarat
                                        </span>
                                    </div>
                                    <div v-else-if="a.nilai && a.nilai.layak_sertifikat === 'N'" class="flex flex-col items-center gap-2">
                                        <span class="bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 px-3 py-1 rounded-full text-xs font-bold border border-red-200 dark:border-red-800">
                                            <i class="fas fa-times"></i> Tidak Memenuhi Syarat
                                        </span>
                                    </div>
                                    <div v-else class="text-gray-400 text-xs italic">- Belum dinilai -</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div v-else class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-12 text-center text-gray-500 dark:text-gray-400">
                <div class="w-20 h-20 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300 dark:text-gray-600">
                    <i class="fas fa-users-slash text-3xl"></i>
                </div>
                <p class="text-lg font-medium">Belum ada anggota aktif yang bisa dinilai.</p>
            </div>
        </div>
    </DashboardLayout>
</template>
