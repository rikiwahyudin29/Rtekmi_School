<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref, onMounted } from 'vue';

const props = defineProps({
    kelompok: Object,
    siswa: Array,
    subElemen: Array,
    nilai_existing: Array
});

const form = useForm({
    nilai_data: []
});

onMounted(() => {
    // Initialize form with existing values or defaults
    const initData = [];
    props.siswa.forEach(s => {
        props.subElemen.forEach(se => {
            const existing = props.nilai_existing.find(n => n.siswa_id === s.id && n.sub_elemen_id === se.id);
            initData.push({
                siswa_id: s.id,
                sub_elemen_id: se.id,
                nilai: existing ? existing.nilai : 'MB'
            });
        });
    });
    form.nilai_data = initData;
});

const getNilaiModel = (siswa_id, sub_elemen_id) => {
    const item = form.nilai_data.find(n => n.siswa_id === siswa_id && n.sub_elemen_id === sub_elemen_id);
    return item;
};

const submitNilai = () => {
    form.post(route('guru.p5.store_nilai', props.kelompok.id));
};
</script>

<template>
    <Head title="Input Nilai P5" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-edit text-green-500"></i>
                        Input Nilai P5: {{ kelompok.nama_kelompok }}
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Projek: {{ kelompok.projek?.nama_projek }} | Tema: {{ kelompok.projek?.tema?.nama_tema }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('guru.p5.index')" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 font-medium shadow-sm flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </Link>
                    <button @click="submitNilai" :disabled="form.processing" class="px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 font-medium shadow-sm flex items-center gap-2 transition-colors">
                        <i class="fas fa-save"></i> {{ form.processing ? 'Menyimpan...' : 'Simpan Nilai' }}
                    </button>
                </div>
            </div>

            <!-- Tabel Input Nilai -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                            <tr>
                                <th scope="col" class="px-6 py-4 w-16 whitespace-nowrap sticky left-0 z-10 bg-gray-50 dark:bg-gray-700">No</th>
                                <th scope="col" class="px-6 py-4 min-w-[200px] whitespace-nowrap sticky left-[64px] z-10 bg-gray-50 dark:bg-gray-700">Nama Siswa</th>
                                <th v-for="(se, idx) in subElemen" :key="se.id" scope="col" class="px-6 py-4 text-center min-w-[150px]">
                                    <div class="font-bold text-green-600 mb-1" :title="se.deskripsi_sub_elemen">Sub Elemen {{ idx + 1 }}</div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(s, index) in siswa" :key="s.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 sticky left-0 z-10 bg-white dark:bg-gray-800">{{ index + 1 }}</td>
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white sticky left-[64px] z-10 bg-white dark:bg-gray-800">{{ s.nama_lengkap }}</td>
                                <td v-for="se in subElemen" :key="se.id" class="px-6 py-4 text-center">
                                    <select v-if="getNilaiModel(s.id, se.id)" v-model="getNilaiModel(s.id, se.id).nilai" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-green-500 focus:border-green-500">
                                        <option value="MB">MB (Mulai Berkembang)</option>
                                        <option value="SB">SB (Sedang Berkembang)</option>
                                        <option value="BSH">BSH (Berkembang Sesuai Harapan)</option>
                                        <option value="SAB">SAB (Sangat Berkembang)</option>
                                    </select>
                                </td>
                            </tr>
                            <tr v-if="siswa.length === 0">
                                <td :colspan="2 + subElemen.length" class="px-6 py-8 text-center text-gray-500">Tidak ada siswa di kelas target.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-2xl p-6 flex gap-4">
                <div class="text-blue-500 text-2xl mt-1"><i class="fas fa-info-circle"></i></div>
                <div class="text-sm text-blue-800 dark:text-blue-300">
                    <strong class="block mb-2">Keterangan Capaian P5:</strong>
                    <ul class="list-disc ml-5 space-y-1">
                        <li><strong>MB (Mulai Berkembang):</strong> Siswa mulai mengenali dan memahami elemen profil pelajar pancasila.</li>
                        <li><strong>SB (Sedang Berkembang):</strong> Siswa sedang dalam proses mengembangkan elemen profil secara konsisten.</li>
                        <li><strong>BSH (Berkembang Sesuai Harapan):</strong> Siswa telah menunjukkan elemen profil sesuai harapan di fase usianya.</li>
                        <li><strong>SAB (Sangat Berkembang):</strong> Siswa menunjukkan perkembangan melampaui harapan dan menjadi teladan.</li>
                    </ul>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
