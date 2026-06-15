<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    ekskul: Object,
    pendaftar: Array,
    anggota: Array
});

const activeTab = ref('pendaftar');

const validasi = (id, status, nama) => {
    let msg = status === 'Approved' ? `Terima ${nama} sebagai anggota?` : `Tolak/Keluarkan ${nama} dari ekskul?`;
    if (confirm(msg)) {
        router.post(route('guru.ekskul.anggota.validasi', id), { status: status }, {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head :title="`Kelola Anggota - ${ekskul.nama_ekskul}`" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <Link :href="route('guru.ekskul.index')" class="w-10 h-10 rounded-full bg-white dark:bg-gray-800 shadow-sm flex items-center justify-center text-gray-500 hover:text-orange-600 transition-colors border border-gray-100 dark:border-gray-700">
                            <i class="fas fa-arrow-left"></i>
                        </Link>
                        <span>Kelola Anggota: {{ ekskul.nama_ekskul }}</span>
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1 ml-13">
                        Validasi pendaftaran siswa baru dan lihat daftar anggota aktif.
                    </p>
                </div>
            </div>

            <!-- Flash Message -->
            <div v-if="$page.props.flash?.message" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-400 p-4 rounded-2xl shadow-sm flex items-center gap-3">
                <i class="fas fa-check-circle"></i> {{ $page.props.flash.message }}
            </div>

            <!-- Tabs -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-2 flex flex-col sm:flex-row gap-2 max-w-fit">
                <button @click="activeTab = 'pendaftar'" 
                        class="px-6 py-2.5 rounded-2xl text-sm font-bold transition-all flex items-center gap-2"
                        :class="activeTab === 'pendaftar' ? 'bg-orange-600 text-white shadow-lg shadow-orange-500/30' : 'text-gray-500 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-700'">
                    <i class="fas fa-user-clock"></i>
                    Menunggu Validasi
                    <span v-if="pendaftar.length > 0" class="bg-white text-orange-600 px-2 py-0.5 rounded-full text-xs ml-1">{{ pendaftar.length }}</span>
                </button>
                <button @click="activeTab = 'anggota'" 
                        class="px-6 py-2.5 rounded-2xl text-sm font-bold transition-all flex items-center gap-2"
                        :class="activeTab === 'anggota' ? 'bg-green-600 text-white shadow-lg shadow-green-500/30' : 'text-gray-500 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-700'">
                    <i class="fas fa-users"></i>
                    Anggota Aktif
                    <span class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded-full text-xs ml-1" :class="activeTab === 'anggota' ? 'bg-white/20 text-white' : ''">{{ anggota.length }}</span>
                </button>
            </div>

            <!-- Tab Content: Pendaftar -->
            <div v-show="activeTab === 'pendaftar'" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div v-if="pendaftar.length === 0" class="p-12 text-center text-gray-500 dark:text-gray-400">
                    <div class="w-20 h-20 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300 dark:text-gray-600">
                        <i class="fas fa-clipboard-check text-3xl"></i>
                    </div>
                    <p class="text-lg font-medium">Belum ada pendaftar baru.</p>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-4 font-bold w-16">No</th>
                                <th class="px-6 py-4 font-bold">Nama Siswa</th>
                                <th class="px-6 py-4 font-bold">Kelas</th>
                                <th class="px-6 py-4 font-bold">Tgl Daftar</th>
                                <th class="px-6 py-4 font-bold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="(p, i) in pendaftar" :key="p.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 text-sm text-gray-500">{{ i + 1 }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-xs font-bold">
                                            {{ p.nama_lengkap.charAt(0) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900 dark:text-white">{{ p.nama_lengkap }}</div>
                                            <div class="text-xs text-gray-500">NIS: {{ p.nis || '-' }} | {{ p.jk === 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-700 dark:text-gray-300">{{ p.nama_kelas || '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ p.tgl_daftar }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-center gap-2">
                                        <button @click="validasi(p.id, 'Approved', p.nama_lengkap)" class="px-3 py-1.5 bg-green-50 text-green-600 hover:bg-green-100 dark:bg-green-900/30 dark:text-green-400 rounded-lg text-xs font-bold flex items-center gap-1 transition-colors">
                                            <i class="fas fa-check"></i> Terima
                                        </button>
                                        <button @click="validasi(p.id, 'Rejected', p.nama_lengkap)" class="px-3 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 rounded-lg text-xs font-bold flex items-center gap-1 transition-colors">
                                            <i class="fas fa-times"></i> Tolak
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab Content: Anggota Aktif -->
            <div v-show="activeTab === 'anggota'" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div v-if="anggota.length === 0" class="p-12 text-center text-gray-500 dark:text-gray-400">
                    <div class="w-20 h-20 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300 dark:text-gray-600">
                        <i class="fas fa-users-slash text-3xl"></i>
                    </div>
                    <p class="text-lg font-medium">Belum ada anggota aktif.</p>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-4 font-bold w-16">No</th>
                                <th class="px-6 py-4 font-bold">Nama Siswa</th>
                                <th class="px-6 py-4 font-bold">Kelas</th>
                                <th class="px-6 py-4 font-bold">Tgl Bergabung</th>
                                <th class="px-6 py-4 font-bold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="(a, i) in anggota" :key="a.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 text-sm text-gray-500">{{ i + 1 }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs font-bold">
                                            {{ a.nama_lengkap.charAt(0) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900 dark:text-white">{{ a.nama_lengkap }}</div>
                                            <div class="text-xs text-gray-500">NIS: {{ a.nis || '-' }} | {{ a.jk === 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-700 dark:text-gray-300">{{ a.nama_kelas || '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ a.tgl_daftar }}</td>
                                <td class="px-6 py-4 text-center">
                                    <button @click="validasi(a.id, 'Dikeluarkan', a.nama_lengkap)" class="px-3 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 rounded-lg text-xs font-bold flex items-center gap-1 transition-colors mx-auto" title="Keluarkan Anggota">
                                        <i class="fas fa-sign-out-alt"></i> Keluarkan
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
