<script setup>
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { onMounted, watch } from 'vue';

const props = defineProps({
    paket: Array,
    siswa: Array,
    skkni: Array,
    skill_passport: Object,
    selected_paket_id: [String, Number]
});

const form = useForm({
    paket_id: props.selected_paket_id,
    data: {}
});

const initForm = () => {
    const initData = {};
    if (props.siswa && props.skkni) {
        props.siswa.forEach(s => {
            initData[s.id] = {};
            const existingSp = props.skill_passport[s.id] || [];
            
            props.skkni.forEach(sk => {
                sk.kuk.forEach(k => {
                    const found = existingSp.find(e => e.kuk_id === k.id);
                    initData[s.id][k.id] = found ? found.status : 'Belum Kompeten';
                });
            });
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
    router.get(route('admin.ukk.skill_passport'), { paket_id: e.target.value }, {
        preserveState: true,
        preserveScroll: true
    });
};

const submitSkillPassport = () => {
    form.post(route('admin.ukk.skill_passport.store'));
};
</script>

<template>
    <Head title="Skill Passport UKK" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-passport text-indigo-500"></i>
                        Buku Skill Passport (UKK)
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Input capaian kompetensi per Kriteria Unjuk Kerja (KUK) untuk buku saku Skill Passport.
                    </p>
                </div>
                <button @click="submitSkillPassport" :disabled="form.processing || !siswa || siswa.length === 0" class="px-6 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 font-medium shadow-sm flex items-center gap-2 transition-colors disabled:opacity-50">
                    <i class="fas fa-save"></i> {{ form.processing ? 'Menyimpan...' : 'Simpan Skill Passport' }}
                </button>
            </div>

            <!-- Filter Card -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <div class="max-w-md w-full">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pilih Paket Ujian (UKK)</label>
                    <select v-model="form.paket_id" @change="gantiPaket" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- Pilih Paket UKK --</option>
                        <option v-for="p in paket" :key="p.id" :value="p.id">{{ p.nama_paket }} ({{ p.jurusan?.nama_jurusan }})</option>
                    </select>
                </div>
            </div>

            <!-- Detail per Siswa (Accordion or List) -->
            <div v-if="siswa && siswa.length > 0">
                <div v-for="(s, index) in siswa" :key="s.id" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 mb-6 overflow-hidden">
                    <div class="p-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                        <h3 class="font-bold text-lg text-gray-900 dark:text-white">{{ index + 1 }}. {{ s.nama_lengkap }} ({{ s.nisn }})</h3>
                    </div>
                    
                    <div class="p-6">
                        <div v-for="sk in skkni" :key="sk.id" class="mb-6 last:mb-0">
                            <h4 class="font-bold text-indigo-600 dark:text-indigo-400 mb-3 border-b pb-2">Unit Kompetensi: {{ sk.judul_unit }}</h4>
                            
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <tbody>
                                    <tr v-for="k in sk.kuk" :key="k.id" class="border-b dark:border-gray-700 last:border-0">
                                        <td class="py-3 pr-4 w-32 font-medium text-gray-900 dark:text-gray-300">{{ k.kode_kuk }}</td>
                                        <td class="py-3 pr-4">{{ k.deskripsi_kuk }}</td>
                                        <td class="py-3 w-48 text-right">
                                            <select v-if="form.data[s.id]" v-model="form.data[s.id][k.id]" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 font-bold" :class="{'text-green-600': form.data[s.id][k.id] === 'Kompeten', 'text-red-600': form.data[s.id][k.id] === 'Belum Kompeten'}">
                                                <option value="Kompeten">Kompeten</option>
                                                <option value="Belum Kompeten">Belum Kompeten</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr v-if="!sk.kuk || sk.kuk.length === 0">
                                        <td colspan="3" class="py-3 text-gray-400 italic">Kriteria Unjuk Kerja belum ditambahkan untuk unit ini.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div v-else-if="form.paket_id" class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-2xl p-6 text-center text-yellow-800 dark:text-yellow-400">
                <i class="fas fa-info-circle text-3xl mb-3"></i>
                <p>Tidak ada siswa di jurusan yang sesuai dengan paket UKK ini.</p>
            </div>
        </div>
    </DashboardLayout>
</template>
