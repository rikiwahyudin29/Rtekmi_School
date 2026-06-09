<script setup>
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { onMounted, watch } from 'vue';

const props = defineProps({
    kelas: Array,
    siswa: Array,
    ijazah_data: Object,
    selected_kelas_id: [String, Number]
});

const form = useForm({
    kelas_id: props.selected_kelas_id,
    data: {}
});

const initForm = () => {
    const initData = {};
    if (props.siswa) {
        props.siswa.forEach(s => {
            const existing = props.ijazah_data[s.id];
            initData[s.id] = {
                no_ijazah: existing ? existing.no_ijazah : '',
                tanggal_lulus: existing ? existing.tanggal_lulus : '',
                keterangan: existing ? existing.keterangan : ''
            };
        });
    }
    form.data = initData;
    form.kelas_id = props.selected_kelas_id;
};

onMounted(() => {
    initForm();
});

watch(() => props.siswa, () => {
    initForm();
});

const gantiKelas = (e) => {
    router.get(route('admin.ijazah.index'), { kelas_id: e.target.value }, {
        preserveState: true,
        preserveScroll: true
    });
};

const submitIjazah = () => {
    form.post(route('admin.ijazah.data.store'));
};
</script>

<template>
    <Head title="Manajemen Transkrip Ijazah" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-graduation-cap text-yellow-500"></i>
                        Manajemen Transkrip Ijazah
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Input Nomor Ijazah dan data pendukung kelulusan siswa Kelas 12.
                    </p>
                </div>
                <button @click="submitIjazah" :disabled="form.processing || !siswa || siswa.length === 0" class="px-6 py-2 bg-yellow-600 text-white rounded-xl hover:bg-yellow-700 font-medium shadow-sm flex items-center gap-2 transition-colors disabled:opacity-50">
                    <i class="fas fa-save"></i> {{ form.processing ? 'Menyimpan...' : 'Simpan Data Ijazah' }}
                </button>
            </div>

            <!-- Filter Card -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <div class="max-w-md w-full">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pilih Kelas 12</label>
                    <select v-model="form.kelas_id" @change="gantiKelas" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
                        <option value="">-- Pilih Kelas --</option>
                        <option v-for="k in kelas" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                    </select>
                </div>
            </div>

            <!-- Tabel Input Ijazah -->
            <div v-if="siswa && siswa.length > 0" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                            <tr>
                                <th scope="col" class="px-6 py-4 w-16">No</th>
                                <th scope="col" class="px-6 py-4">NISN</th>
                                <th scope="col" class="px-6 py-4">Nama Siswa</th>
                                <th scope="col" class="px-6 py-4">Nomor Ijazah</th>
                                <th scope="col" class="px-6 py-4">Tanggal Lulus</th>
                                <th scope="col" class="px-6 py-4">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(s, index) in siswa" :key="s.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4">{{ index + 1 }}</td>
                                <td class="px-6 py-4">{{ s.nisn }}</td>
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ s.nama_lengkap }}</td>
                                <td class="px-6 py-4">
                                    <input v-if="form.data[s.id]" v-model="form.data[s.id].no_ijazah" type="text" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-yellow-500 focus:border-yellow-500" placeholder="M-SMK/0000000">
                                </td>
                                <td class="px-6 py-4">
                                    <input v-if="form.data[s.id]" v-model="form.data[s.id].tanggal_lulus" type="date" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-yellow-500 focus:border-yellow-500">
                                </td>
                                <td class="px-6 py-4">
                                    <input v-if="form.data[s.id]" v-model="form.data[s.id].keterangan" type="text" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-yellow-500 focus:border-yellow-500" placeholder="Lulus Tepat Waktu">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div v-else-if="form.kelas_id" class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-2xl p-6 text-center text-yellow-800 dark:text-yellow-400">
                <i class="fas fa-info-circle text-3xl mb-3"></i>
                <p>Tidak ada siswa di kelas ini.</p>
            </div>
        </div>
    </DashboardLayout>
</template>
