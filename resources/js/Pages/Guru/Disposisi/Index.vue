<script setup>
import { Head, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    disposisi: Array,
    unread_count: Number,
    status: String,
});

const formatTanggal = (tgl) => {
    if (!tgl) return '-';
    return new Date(tgl).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
};

const filterStatus = (val) => {
    router.get(route('guru.disposisi.index'), { status: val }, { preserveState: true });
};

const tandaiBaca = (id) => {
    router.patch(route('guru.disposisi.baca', id), {}, { preserveScroll: true });
};

const bacaSemua = () => {
    router.post(route('guru.disposisi.baca_semua'), {}, { preserveScroll: true });
};

const getFileUrl = (file) => {
    if (!file) return null;
    if (file.startsWith('http')) return file;
    return '/uploads/surat_masuk/' + file;
};
</script>

<template>
    <Head title="Kotak Disposisi" />
    <DashboardLayout>
        <div class="space-y-6">

            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">📬 Kotak Disposisi</h1>
                    <p class="text-sm text-gray-500 mt-1">Surat masuk yang didisposisikan kepada Anda.</p>
                </div>
                <div class="flex items-center gap-3">
                    <span v-if="unread_count > 0" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-100 text-rose-700 text-sm font-semibold rounded-full">
                        <i class="fas fa-bell"></i> {{ unread_count }} Belum Dibaca
                    </span>
                    <button v-if="unread_count > 0" @click="bacaSemua"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition-all">
                        <i class="fas fa-check-double"></i> Tandai Semua Dibaca
                    </button>
                </div>
            </div>

            <!-- Filter Tabs -->
            <div class="flex gap-2 border-b border-gray-200 pb-0">
                <button @click="filterStatus('semua')"
                    :class="['px-4 py-2 text-sm font-semibold border-b-2 transition-all', status === 'semua' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700']">
                    Semua
                </button>
                <button @click="filterStatus('belum_dibaca')"
                    :class="['px-4 py-2 text-sm font-semibold border-b-2 transition-all', status === 'belum_dibaca' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700']">
                    Belum Dibaca
                </button>
                <button @click="filterStatus('sudah_dibaca')"
                    :class="['px-4 py-2 text-sm font-semibold border-b-2 transition-all', status === 'sudah_dibaca' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700']">
                    Sudah Dibaca
                </button>
            </div>

            <!-- List Disposisi -->
            <div v-if="disposisi.length > 0" class="space-y-3">
                <div v-for="item in disposisi" :key="item.id"
                    :class="['bg-white rounded-2xl shadow-sm border transition-all hover:shadow-md p-5',
                        item.is_read == 0 ? 'border-l-4 border-l-indigo-500 border-gray-100' : 'border-gray-100 opacity-80']">

                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-4 flex-1 min-w-0">
                            <!-- Icon -->
                            <div :class="['w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0',
                                item.is_read == 0 ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-400']">
                                <i class="fas fa-envelope text-lg"></i>
                            </div>

                            <div class="flex-1 min-w-0">
                                <!-- Badge belum dibaca -->
                                <span v-if="item.is_read == 0"
                                    class="inline-block mb-1 px-2 py-0.5 text-xs font-bold bg-indigo-600 text-white rounded-full uppercase tracking-wide">
                                    Baru
                                </span>

                                <h3 class="font-bold text-gray-800 text-base leading-tight truncate">
                                    {{ item.perihal || 'Tanpa Perihal' }}
                                </h3>

                                <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-500">
                                    <span><i class="fas fa-hashtag mr-1"></i>{{ item.nomor_surat || '-' }}</span>
                                    <span><i class="fas fa-building mr-1"></i>{{ item.pengirim || '-' }}</span>
                                    <span><i class="fas fa-calendar mr-1"></i>{{ formatTanggal(item.tanggal_disposisi) }}</span>
                                </div>

                                <p v-if="item.catatan" class="mt-2 text-sm text-gray-600 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 italic">
                                    <i class="fas fa-quote-left mr-1 text-amber-400"></i>{{ item.catatan }}
                                </p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col items-end gap-2 flex-shrink-0">
                            <a v-if="getFileUrl(item.file_scan)" :href="getFileUrl(item.file_scan)" target="_blank"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-lg hover:bg-emerald-100 transition-all border border-emerald-200">
                                <i class="fas fa-file-pdf"></i> Lihat Surat
                            </a>
                            <button v-if="item.is_read == 0" @click="tandaiBaca(item.id)"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 text-indigo-700 text-xs font-semibold rounded-lg hover:bg-indigo-100 transition-all border border-indigo-200">
                                <i class="fas fa-check"></i> Tandai Dibaca
                            </button>
                            <span v-else class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-gray-400">
                                <i class="fas fa-check-double"></i> Sudah Dibaca
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-20 bg-white rounded-2xl border border-dashed border-gray-300">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-inbox text-4xl text-gray-300"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-500">Kotak Disposisi Kosong</h3>
                <p class="text-sm text-gray-400 mt-1">
                    Belum ada surat yang didisposisikan kepada Anda.
                </p>
            </div>

        </div>
    </DashboardLayout>
</template>
