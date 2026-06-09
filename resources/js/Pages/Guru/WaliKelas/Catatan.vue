<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    kelas: Object,
    siswa: Array,
    catatan: Object
});

const form = useForm({
    input_data: {}
});

if (props.siswa && props.siswa.length > 0) {
    props.siswa.forEach(s => {
        form.input_data[s.id] = props.catatan[s.id] ? props.catatan[s.id].catatan : '';
    });
}

const submit = () => {
    form.post(route('guru.walikelas.catatan.store'), {
        preserveScroll: true
    });
};
</script>

<template>
    <Head title="Catatan Wali Kelas" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-comment-dots text-emerald-500"></i>
                        Catatan Wali Kelas
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Berikan deskripsi motivasi dan saran untuk peserta didik.
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
                        <h3 class="font-bold text-gray-900 dark:text-white">Form Catatan Wali Kelas - {{ kelas?.nama_kelas || '-' }}</h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <div v-for="(s, index) in siswa" :key="s.id" class="flex flex-col md:flex-row gap-4 pb-6 border-b border-gray-100 dark:border-gray-700 last:border-0 last:pb-0">
                            <div class="w-full md:w-1/4">
                                <div class="font-bold text-gray-900 dark:text-white text-lg">{{ index + 1 }}. {{ s.nama_lengkap }}</div>
                                <div class="text-sm text-gray-500">{{ s.nisn }}</div>
                            </div>
                            <div class="w-full md:w-3/4 relative">
                                <textarea v-model="form.input_data[s.id]" rows="2" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition-all placeholder:text-gray-400" placeholder="Ketik catatan motivasi atau saran pengembangan untuk siswa..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="p-6 bg-gray-50 dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                        <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 font-bold transition-colors shadow-sm shadow-emerald-500/30 flex items-center gap-2">
                            <i class="fas fa-save"></i> 
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Catatan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>
