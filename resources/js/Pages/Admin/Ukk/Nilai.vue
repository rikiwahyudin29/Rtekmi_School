<script setup>
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { onMounted, watch } from 'vue';

const props = defineProps({
    paket: Array,
    asesor: Array,
    guru: Array,
    siswa: Array,
    nilai_existing: Object,
    selected_paket_id: [String, Number]
});

const form = useForm({
    paket_id: props.selected_paket_id,
    data: {}
});

const initForm = () => {
    const initData = {};
    if (props.siswa) {
        props.siswa.forEach(s => {
            const existing = props.nilai_existing[s.id];
            initData[s.id] = {
                asesor_internal_id: existing ? existing.asesor_internal_id : '',
                asesor_eksternal_id: existing ? existing.asesor_eksternal_id : '',
                nilai_pengetahuan: existing ? existing.nilai_pengetahuan : '',
                nilai_keterampilan: existing ? existing.nilai_keterampilan : '',
                kesimpulan: existing ? existing.kesimpulan : 'Cukup'
            };
        });
    }
    form.data = initData;
    form.paket_id = props.selected_paket_id;
};

onMounted(() => {
    initForm();
});

watch(() => props.siswa, () => {
    initForm();
});

const gantiPaket = (e) => {
    router.get(route('admin.ukk.nilai'), { paket_id: e.target.value }, {
        preserveState: true,
        preserveScroll: true
    });
};

const calculateKesimpulan = (s_id) => {
    const d = form.data[s_id];
    if(d.nilai_pengetahuan && d.nilai_keterampilan) {
        const p = parseFloat(d.nilai_pengetahuan);
        const k = parseFloat(d.nilai_keterampilan);
        const rata = (p + k) / 2;
        
        if (rata >= 90) d.kesimpulan = 'Sangat Baik';
        else if (rata >= 80) d.kesimpulan = 'Baik';
        else if (rata >= 70) d.kesimpulan = 'Cukup';
        else d.kesimpulan = 'Belum Kompeten';
    }
};

const submitNilai = () => {
    form.post(route('admin.ukk.nilai.store'));
};
</script>

<template>
    <Head title="Input Nilai UKK" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-star-half-alt text-purple-500"></i>
                        Input Nilai UKK
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Input nilai Uji Kompetensi Keahlian dari Asesor Internal dan Eksternal.
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('admin.ukk.index')" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 font-medium shadow-sm flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </Link>
                    <button @click="submitNilai" :disabled="form.processing || !siswa || siswa.length === 0" class="px-6 py-2 bg-purple-600 text-white rounded-xl hover:bg-purple-700 font-medium shadow-sm flex items-center gap-2 transition-colors disabled:opacity-50">
                        <i class="fas fa-save"></i> {{ form.processing ? 'Menyimpan...' : 'Simpan Nilai' }}
                    </button>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <div class="max-w-md w-full">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pilih Paket Ujian (UKK)</label>
                    <select v-model="form.paket_id" @change="gantiPaket" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-purple-500 focus:ring-purple-500">
                        <option value="">-- Pilih Paket UKK --</option>
                        <option v-for="p in paket" :key="p.id" :value="p.id">{{ p.nama_paket }} ({{ p.jurusan?.nama_jurusan }})</option>
                    </select>
                </div>
            </div>

            <!-- Tabel Input Nilai -->
            <div v-if="siswa && siswa.length > 0" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                            <tr>
                                <th scope="col" class="px-6 py-4 w-16">No</th>
                                <th scope="col" class="px-6 py-4">Nama Siswa</th>
                                <th scope="col" class="px-6 py-4">Asesor Internal</th>
                                <th scope="col" class="px-6 py-4">Asesor Eksternal</th>
                                <th scope="col" class="px-6 py-4 text-center">Pengetahuan</th>
                                <th scope="col" class="px-6 py-4 text-center">Keterampilan</th>
                                <th scope="col" class="px-6 py-4 text-center">Kesimpulan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(s, index) in siswa" :key="s.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4">{{ index + 1 }}</td>
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ s.nama_lengkap }}</td>
                                <td class="px-6 py-4">
                                    <select v-if="form.data[s.id]" v-model="form.data[s.id].asesor_internal_id" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-purple-500 focus:border-purple-500">
                                        <option value="">-- Pilih Internal --</option>
                                        <option v-for="g in guru" :key="g.id" :value="g.id">{{ g.nama_lengkap }}</option>
                                    </select>
                                </td>
                                <td class="px-6 py-4">
                                    <select v-if="form.data[s.id]" v-model="form.data[s.id].asesor_eksternal_id" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-purple-500 focus:border-purple-500">
                                        <option value="">-- Pilih Eksternal --</option>
                                        <option v-for="a in asesor" :key="a.id" :value="a.id">{{ a.nama_asesor }}</option>
                                    </select>
                                </td>
                                <td class="px-6 py-4">
                                    <input v-if="form.data[s.id]" v-model="form.data[s.id].nilai_pengetahuan" type="number" step="0.01" min="0" max="100" @change="calculateKesimpulan(s.id)" class="w-20 text-center text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-purple-500 focus:border-purple-500">
                                </td>
                                <td class="px-6 py-4">
                                    <input v-if="form.data[s.id]" v-model="form.data[s.id].nilai_keterampilan" type="number" step="0.01" min="0" max="100" @change="calculateKesimpulan(s.id)" class="w-20 text-center text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-purple-500 focus:border-purple-500">
                                </td>
                                <td class="px-6 py-4">
                                    <select v-if="form.data[s.id]" v-model="form.data[s.id].kesimpulan" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-purple-500 focus:border-purple-500 font-bold" :class="{'text-green-600': form.data[s.id].kesimpulan !== 'Belum Kompeten', 'text-red-600': form.data[s.id].kesimpulan === 'Belum Kompeten'}">
                                        <option value="Sangat Baik">Sangat Baik</option>
                                        <option value="Baik">Baik</option>
                                        <option value="Cukup">Cukup</option>
                                        <option value="Belum Kompeten">Belum Kompeten</option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div v-else-if="form.paket_id" class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-2xl p-6 text-center text-yellow-800 dark:text-yellow-400">
                <i class="fas fa-info-circle text-3xl mb-3"></i>
                <p>Tidak ada siswa di jurusan yang sesuai dengan paket UKK ini.</p>
            </div>
            
        </div>
    </DashboardLayout>
</template>
