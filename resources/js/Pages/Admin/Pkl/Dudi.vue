<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    dudi: Array
});

const isModalOpen = ref(false);
const isEdit = ref(false);

const form = useForm({
    id: null,
    nama_dudi: '',
    bidang_usaha: '',
    nama_pimpinan: '',
    alamat_lengkap: '',
    latitude: '',
    longitude: '',
    radius_absen: 100 // default 100 meter
});

const openModal = (d = null) => {
    isEdit.value = !!d;
    if (d) {
        form.id = d.id;
        form.nama_dudi = d.nama_dudi;
        form.bidang_usaha = d.bidang_usaha;
        form.nama_pimpinan = d.nama_pimpinan;
        form.alamat_lengkap = d.alamat_lengkap;
        form.latitude = d.latitude || '';
        form.longitude = d.longitude || '';
        form.radius_absen = d.radius_absen || 100;
    } else {
        form.reset();
    }
    isModalOpen.value = true;
};

const submitDudi = () => {
    if (isEdit.value) {
        form.post(route('admin.pkl.dudi.update'), {
            preserveScroll: true,
            onSuccess: () => isModalOpen.value = false
        });
    } else {
        form.post(route('admin.pkl.dudi.simpan'), {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            }
        });
    }
};

const deleteDudi = (id, nama) => {
    if (confirm(`Hapus data DU/DI ${nama}? Data penempatan PKL terkait mungkin akan terdampak.`)) {
        router.delete(route('admin.pkl.dudi.delete', id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Master DU/DI PKL" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-building text-blue-500"></i>
                        Master DU/DI (Perusahaan)
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Kelola data Dunia Usaha / Dunia Industri tempat siswa melaksanakan PKL.
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('admin.pkl.kelompok')" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 font-bold shadow-sm flex items-center gap-2 transition-all">
                        <i class="fas fa-users-cog"></i> Kelompok PKL
                    </Link>
                    <button @click="openModal()" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-bold shadow-sm flex items-center gap-2 transition-all">
                        <i class="fas fa-plus"></i> Tambah DU/DI
                    </button>
                </div>
            </div>

            <div v-if="$page.props.flash?.message" class="bg-green-50 text-green-800 p-4 rounded-2xl shadow-sm flex items-center gap-3">
                <i class="fas fa-check-circle"></i> {{ $page.props.flash.message }}
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-4 font-bold">Nama Perusahaan</th>
                                <th class="px-6 py-4 font-bold">Pimpinan</th>
                                <th class="px-6 py-4 font-bold">Alamat</th>
                                <th class="px-6 py-4 font-bold text-center">Radius Absen</th>
                                <th class="px-6 py-4 font-bold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="d in dudi" :key="d.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900 dark:text-white">{{ d.nama_dudi }}</div>
                                    <div class="text-xs text-blue-600">{{ d.bidang_usaha }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                    {{ d.nama_pimpinan }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-600 dark:text-gray-400 max-w-xs line-clamp-2" :title="d.alamat_lengkap">{{ d.alamat_lengkap }}</div>
                                    <div v-if="d.latitude && d.longitude" class="text-xs text-gray-500 mt-1">
                                        <i class="fas fa-map-marker-alt text-red-500"></i> {{ d.latitude }}, {{ d.longitude }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-bold">{{ d.radius_absen }} M</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center gap-2">
                                        <button @click="openModal(d)" class="w-8 h-8 rounded-lg bg-gray-100 text-gray-600 hover:bg-blue-100 hover:text-blue-600 flex items-center justify-center transition-colors">
                                            <i class="fas fa-pen text-sm"></i>
                                        </button>
                                        <button @click="deleteDudi(d.id, d.nama_dudi)" class="w-8 h-8 rounded-lg bg-gray-100 text-gray-600 hover:bg-red-100 hover:text-red-600 flex items-center justify-center transition-colors">
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="dudi.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    Belum ada data perusahaan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Form DUDI -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white">
                        {{ isEdit ? 'Edit DU/DI' : 'Tambah DU/DI' }}
                    </h3>
                    <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                </div>
                <form @submit.prevent="submitDudi" class="p-6 max-h-[80vh] overflow-y-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Nama Perusahaan (DU/DI)</label>
                            <input type="text" v-model="form.nama_dudi" class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Bidang Usaha</label>
                            <input type="text" v-model="form.bidang_usaha" class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm" placeholder="Contoh: IT / Software House" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Nama Pimpinan / Direktur</label>
                            <input type="text" v-model="form.nama_pimpinan" class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Alamat Lengkap</label>
                            <textarea v-model="form.alamat_lengkap" rows="2" class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm" required></textarea>
                        </div>
                        <div class="md:col-span-2 bg-blue-50 rounded-xl p-4 border border-blue-100">
                            <h4 class="text-sm font-bold text-blue-800 mb-2"><i class="fas fa-map-marker-alt"></i> Pengaturan Kordinat Absensi</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Latitude</label>
                                    <input type="text" v-model="form.latitude" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Contoh: -6.123456">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Longitude</label>
                                    <input type="text" v-model="form.longitude" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Contoh: 106.123456">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Radius (Meter)</label>
                                    <input type="number" v-model="form.radius_absen" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="isModalOpen = false" class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold flex items-center gap-2 disabled:opacity-50">
                            <i v-if="form.processing" class="fas fa-spinner fa-spin"></i>
                            <i v-else class="fas fa-save"></i>
                            Simpan DU/DI
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </DashboardLayout>
</template>
