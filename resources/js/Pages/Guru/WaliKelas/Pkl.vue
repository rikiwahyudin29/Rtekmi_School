<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    kelas: Object,
    siswa: Array,
    pkl: Object,
    dudi_list: Array
});

const form = useForm({
    data: {}
});

if (props.siswa && props.siswa.length > 0) {
    props.siswa.forEach(s => {
        form.data[s.id] = {
            dudi_id: props.pkl[s.id] ? props.pkl[s.id].dudi_id : '',
            lokasi: props.pkl[s.id] ? props.pkl[s.id].lokasi : '',
            lama_bulan: props.pkl[s.id] ? props.pkl[s.id].lama_bulan : '',
            keterangan: props.pkl[s.id] ? props.pkl[s.id].keterangan : '',
            nilai: props.pkl[s.id] ? props.pkl[s.id].nilai : '',
        };
    });
}

const submit = () => {
    form.post(route('guru.walikelas.pkl.store'), {
        preserveScroll: true
    });
};
</script>

<template>
    <Head title="Data PKL Siswa" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-briefcase text-orange-500"></i>
                        Data Praktik Kerja Lapangan (PKL)
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Input data PKL untuk masing-masing siswa (opsional jika ada).
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('guru.walikelas.index')" class="px-4 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors font-medium">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </Link>
                </div>
            </div>

            <!-- Flash Messages -->
            <div v-if="$page.props.flash.success" class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
                <span class="font-medium">Sukses!</span> {{ $page.props.flash.success }}
            </div>

            <!-- Input Area -->
            <div v-if="siswa && siswa.length > 0" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <form @submit.prevent="submit">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                        <h3 class="font-bold text-gray-900 dark:text-white">Form Input PKL - {{ kelas?.nama_kelas || '-' }}</h3>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-300">
                                <tr>
                                    <th scope="col" class="px-4 py-4 w-12">No</th>
                                    <th scope="col" class="px-4 py-4 w-48">Siswa</th>
                                    <th scope="col" class="px-4 py-4 min-w-[200px]">DUDI / Mitra</th>
                                    <th scope="col" class="px-4 py-4 min-w-[150px]">Lokasi</th>
                                    <th scope="col" class="px-4 py-4 w-24">Lama (Bulan)</th>
                                    <th scope="col" class="px-4 py-4 w-24">Nilai</th>
                                    <th scope="col" class="px-4 py-4 min-w-[200px]">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(s, index) in siswa" :key="s.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-4">{{ index + 1 }}</td>
                                    <td class="px-4 py-4">
                                        <div class="font-bold text-gray-900 dark:text-white">{{ s.nama_lengkap }}</div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <select v-model="form.data[s.id].dudi_id" class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                            <option value="">-- Pilih --</option>
                                            <option v-for="d in dudi_list" :key="d.id" :value="d.id">{{ d.nama_dudi }}</option>
                                        </select>
                                    </td>
                                    <td class="px-4 py-4">
                                        <input v-model="form.data[s.id].lokasi" type="text" class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-orange-500 focus:ring-orange-500" placeholder="Kota/Alamat">
                                    </td>
                                    <td class="px-4 py-4">
                                        <input v-model="form.data[s.id].lama_bulan" type="number" min="1" class="w-full text-center text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-orange-500 focus:ring-orange-500" placeholder="1-6">
                                    </td>
                                    <td class="px-4 py-4">
                                        <input v-model="form.data[s.id].nilai" type="number" min="0" max="100" class="w-full text-center text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-orange-500 focus:ring-orange-500" placeholder="0-100">
                                    </td>
                                    <td class="px-4 py-4">
                                        <input v-model="form.data[s.id].keterangan" type="text" class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-orange-500 focus:ring-orange-500" placeholder="Keterangan Capaian">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="p-6 bg-gray-50 dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                        <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-orange-600 text-white rounded-xl hover:bg-orange-700 font-bold transition-colors shadow-sm shadow-orange-500/30 flex items-center gap-2">
                            <i class="fas fa-save"></i> 
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Data PKL' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>
