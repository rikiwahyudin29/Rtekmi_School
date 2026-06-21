<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    kelas_asal: Object,
    siswa: Array,
    kelas_all: Array
});

const form = useForm({
    siswa_id: [],
    aksi: 'pindah',
    kelas_tujuan: ''
});

// Checkbox logic
const selectAll = ref(false);

const toggleSelectAll = () => {
    if (selectAll.value) {
        form.siswa_id = props.siswa.map(s => s.id);
    } else {
        form.siswa_id = [];
    }
};

// Check if all are selected manually
const updateSelectAll = () => {
    selectAll.value = form.siswa_id.length === props.siswa.length && props.siswa.length > 0;
};

const submitForm = () => {
    if (form.siswa_id.length === 0) {
        alert('Pilih minimal satu siswa!');
        return;
    }
    if (form.aksi === 'pindah' && !form.kelas_tujuan) {
        alert('Pilih kelas tujuan!');
        return;
    }
    
    let confirmMsg = form.aksi === 'pindah' 
        ? `Yakin ingin memindahkan ${form.siswa_id.length} siswa terpilih ke kelas lain?`
        : `Yakin ingin MELULUSKAN ${form.siswa_id.length} siswa terpilih? Mereka akan dipindah ke Data Alumni.`;
        
    if(confirm(confirmMsg)) {
        form.post(route('admin.kesiswaan.rombel.pindah'), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head :title="'Atur Rombel ' + kelas_asal.nama_kelas" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <Link :href="route('admin.kesiswaan.rombel.index')" class="text-sm font-medium text-emerald-600 hover:text-emerald-700 mb-2 inline-flex items-center gap-1">
                        <i class="fas fa-arrow-left"></i> Kembali ke Daftar Kelas
                    </Link>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-users-cog text-emerald-500"></i>
                        Atur Rombel: {{ kelas_asal.nama_kelas }}
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Total {{ siswa.length }} Siswa Aktif. Pilih siswa untuk dipindahkan atau diluluskan.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                
                <!-- Left: Table of Students -->
                <div class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                                <tr>
                                    <th scope="col" class="p-4 w-10">
                                        <div class="flex items-center">
                                            <input type="checkbox" v-model="selectAll" @change="toggleSelectAll" class="w-4 h-4 text-emerald-600 bg-gray-100 border-gray-300 rounded focus:ring-emerald-500 dark:focus:ring-emerald-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 cursor-pointer">
                                        </div>
                                    </th>
                                    <th scope="col" class="px-4 py-3">NIS/NISN</th>
                                    <th scope="col" class="px-4 py-3">Nama Siswa</th>
                                    <th scope="col" class="px-4 py-3 text-center">L/P</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="s in siswa" :key="s.id" class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="p-4">
                                        <div class="flex items-center">
                                            <input type="checkbox" :value="s.id" v-model="form.siswa_id" @change="updateSelectAll" class="w-4 h-4 text-emerald-600 bg-gray-100 border-gray-300 rounded focus:ring-emerald-500 dark:focus:ring-emerald-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 cursor-pointer">
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ s.nis }} / {{ s.nisn || '-' }}</td>
                                    <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">{{ s.nama_lengkap }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-1 rounded-md text-xs font-bold" :class="s.jenis_kelamin == 'L' ? 'bg-blue-100 text-blue-700' : 'bg-pink-100 text-pink-700'">{{ s.jenis_kelamin }}</span>
                                    </td>
                                </tr>
                                <tr v-if="siswa.length === 0">
                                    <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                        Tidak ada siswa aktif di kelas ini.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Right: Action Panel -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 sticky top-24">
                        <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-4 border-b pb-2 dark:border-gray-700">Aksi Massal</h3>
                        
                        <div class="mb-4">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Terpilih:</span>
                            <div class="text-3xl font-black" :class="form.siswa_id.length > 0 ? 'text-emerald-600' : 'text-gray-300 dark:text-gray-600'">
                                {{ form.siswa_id.length }} <span class="text-sm font-medium text-gray-500">Siswa</span>
                            </div>
                        </div>

                        <form @submit.prevent="submitForm" class="space-y-4">
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Jenis Aksi</label>
                                <div class="space-y-2">
                                    <label class="flex items-center p-3 border rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors" :class="form.aksi == 'pindah' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20' : 'border-gray-200 dark:border-gray-700'">
                                        <input type="radio" v-model="form.aksi" value="pindah" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                                        <div class="ml-3">
                                            <span class="block text-sm font-bold text-gray-900 dark:text-white">Pindah / Naik Kelas</span>
                                        </div>
                                    </label>
                                    <label class="flex items-center p-3 border rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors" :class="form.aksi == 'lulus' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-200 dark:border-gray-700'">
                                        <input type="radio" v-model="form.aksi" value="lulus" class="w-4 h-4 text-indigo-600 focus:ring-indigo-500">
                                        <div class="ml-3">
                                            <span class="block text-sm font-bold text-gray-900 dark:text-white">Luluskan (Alumni)</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div v-if="form.aksi === 'pindah'" class="animate-fade-in-up">
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pilih Kelas Tujuan</label>
                                <select v-model="form.kelas_tujuan" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                                    <option value="">-- Pilih Kelas --</option>
                                    <option v-for="k in kelas_all" :key="k.id" :value="k.id" :disabled="k.id === kelas_asal.id">
                                        {{ k.nama_kelas }} {{ k.id === kelas_asal.id ? '(Kelas Saat Ini)' : '' }}
                                    </option>
                                </select>
                            </div>

                            <button type="submit" :disabled="form.siswa_id.length === 0 || form.processing" 
                                class="w-full text-white font-bold rounded-xl text-sm px-5 py-3 text-center transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                :class="form.aksi === 'pindah' ? 'bg-emerald-600 hover:bg-emerald-700 focus:ring-4 focus:ring-emerald-300 dark:focus:ring-emerald-800' : 'bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300 dark:focus:ring-indigo-800'">
                                {{ form.processing ? 'Memproses...' : (form.aksi === 'pindah' ? 'Proses Pindah Kelas' : 'Proses Kelulusan') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
