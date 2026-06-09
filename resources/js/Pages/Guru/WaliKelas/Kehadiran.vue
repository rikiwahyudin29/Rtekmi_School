<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    kelas: Object,
    siswa: Array,
    kehadiran: Object
});

const form = useForm({
    data: {}
});

if (props.siswa && props.siswa.length > 0) {
    props.siswa.forEach(s => {
        form.data[s.id] = {
            sakit: props.kehadiran[s.id] ? props.kehadiran[s.id].sakit : 0,
            izin: props.kehadiran[s.id] ? props.kehadiran[s.id].izin : 0,
            tanpa_keterangan: props.kehadiran[s.id] ? props.kehadiran[s.id].tanpa_keterangan : 0,
        };
    });
}

const submit = () => {
    form.post(route('guru.walikelas.kehadiran.store'), {
        preserveScroll: true
    });
};
</script>

<template>
    <Head title="Input Kehadiran Rapor" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-user-clock text-blue-500"></i>
                        Input Kehadiran Rapor
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Kelas: <span class="font-bold">{{ kelas?.nama_kelas || '-' }}</span>
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
                        <h3 class="font-bold text-gray-900 dark:text-white">Form Kehadiran Siswa</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-300">
                                <tr>
                                    <th scope="col" class="px-6 py-4 w-16">No</th>
                                    <th scope="col" class="px-6 py-4">Siswa</th>
                                    <th scope="col" class="px-6 py-4 w-32 text-center">Sakit (Hari)</th>
                                    <th scope="col" class="px-6 py-4 w-32 text-center">Izin (Hari)</th>
                                    <th scope="col" class="px-6 py-4 w-32 text-center">Tanpa Ket. (Hari)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(s, index) in siswa" :key="s.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-6 py-4">{{ index + 1 }}</td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900 dark:text-white">{{ s.nama_lengkap }}</div>
                                        <div class="text-xs text-gray-500">{{ s.nisn }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <input v-model="form.data[s.id].sakit" type="number" min="0" class="w-full text-center rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </td>
                                    <td class="px-6 py-4">
                                        <input v-model="form.data[s.id].izin" type="number" min="0" class="w-full text-center rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </td>
                                    <td class="px-6 py-4">
                                        <input v-model="form.data[s.id].tanpa_keterangan" type="number" min="0" class="w-full text-center rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="p-6 bg-gray-50 dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                        <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-bold transition-colors shadow-sm flex items-center gap-2">
                            <i class="fas fa-save"></i> 
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Kehadiran' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>
