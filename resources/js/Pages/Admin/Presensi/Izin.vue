<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import axios from 'axios';

const props = defineProps({
    izinSiswa: Array,
    izinGuru: Array,
    tanggal: String,
    kelas: Array
});

// Modal State
const showModal = ref(false);
const siswaList = ref([]);

const form = useForm({
    kelas_id: '',
    id_siswa: '',
    status: 'Izin',
    keterangan: '',
    tanggal: props.tanggal,
    bukti: null
});

const loadSiswa = async () => {
    if (!form.kelas_id) {
        siswaList.value = [];
        return;
    }
    const res = await axios.get(route('admin.presensi.get_siswa_by_kelas', form.kelas_id));
    siswaList.value = res.data;
};

const handleFile = (e) => {
    form.bukti = e.target.files[0];
};

const submitManual = () => {
    form.post(route('admin.presensi.simpan_izin'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        }
    });
};

const ubahStatus = (id, status) => {
    if (confirm(`Anda yakin ingin mengubah status menjadi ${status}?`)) {
        axios.get(route('admin.presensi.verifikasi', { id, status })).then(() => {
            window.location.reload();
        });
    }
};
</script>

<template>
    <DashboardLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                
                <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-1">Verifikasi Izin & Sakit</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Kelola pengajuan izin dan sakit siswa maupun guru.</p>
                    </div>
                    <button @click="showModal = true" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-sm transition-all flex items-center gap-2 text-sm">
                        <i class="fas fa-plus"></i> Tambah Manual
                    </button>
                </div>
                
                <!-- Tabel Izin Siswa -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden rounded-2xl border border-gray-100 dark:border-gray-700">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-700 pb-3 flex items-center gap-2">
                            <i class="fas fa-user-graduate text-indigo-500"></i> Pengajuan Siswa
                        </h3>
                        <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama Siswa</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kelas</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Keterangan</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Bukti</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="item in izinSiswa" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ item.tanggal }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 font-bold">{{ item.siswa?.nama_lengkap }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ item.siswa?.kelas?.nama_kelas }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-lg" :class="item.status_kehadiran == 'Izin' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400'">
                                                {{ item.status_kehadiran }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ item.keterangan }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 dark:text-blue-400 font-bold">
                                            <a v-if="item.bukti_izin" :href="`/uploads/surat_izin/${item.bukti_izin}`" target="_blank" class="hover:underline flex items-center gap-1"><i class="fas fa-external-link-alt"></i> Lihat Surat</a>
                                            <span v-else class="text-gray-400 dark:text-gray-600 font-normal">-</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div v-if="item.status_verifikasi == 'Pending'" class="flex gap-2">
                                                <button @click="ubahStatus(item.id, 'Disetujui')" class="bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 px-3 py-1.5 rounded-lg hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors font-bold flex items-center gap-1"><i class="fas fa-check"></i> Setujui</button>
                                                <button @click="ubahStatus(item.id, 'Ditolak')" class="bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 px-3 py-1.5 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors font-bold flex items-center gap-1"><i class="fas fa-times"></i> Tolak</button>
                                            </div>
                                            <span v-else :class="item.status_verifikasi == 'Disetujui' ? 'text-green-600 dark:text-green-400 font-bold flex items-center gap-1' : 'text-red-600 dark:text-red-400 font-bold flex items-center gap-1'">
                                                <i class="fas" :class="item.status_verifikasi == 'Disetujui' ? 'fa-check-circle' : 'fa-times-circle'"></i> {{ item.status_verifikasi }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="izinSiswa.length === 0">
                                        <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">Belum ada pengajuan izin/sakit.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tabel Izin Guru -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden rounded-2xl border border-gray-100 dark:border-gray-700">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-700 pb-3 flex items-center gap-2">
                            <i class="fas fa-chalkboard-teacher text-teal-500"></i> Pengajuan Guru
                        </h3>
                        <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama Guru</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Keterangan</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Bukti</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="item in izinGuru" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ item.tanggal }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 font-bold">{{ item.guru?.nama_lengkap ?? item.guru?.nama_guru }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-lg" :class="item.status_kehadiran == 'Dinas Luar' ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400' : 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400'">
                                                {{ item.status_kehadiran }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ item.keterangan }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 dark:text-blue-400 font-bold">
                                            <a v-if="item.bukti_izin" :href="`/uploads/surat_izin/${item.bukti_izin}`" target="_blank" class="hover:underline flex items-center gap-1"><i class="fas fa-external-link-alt"></i> Lihat Surat</a>
                                            <span v-else class="text-gray-400 dark:text-gray-600 font-normal">-</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div v-if="item.status_verifikasi == 'Pending'" class="flex gap-2">
                                                <button @click="ubahStatus(item.id, 'Disetujui')" class="bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 px-3 py-1.5 rounded-lg hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors font-bold flex items-center gap-1"><i class="fas fa-check"></i> Setujui</button>
                                                <button @click="ubahStatus(item.id, 'Ditolak')" class="bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 px-3 py-1.5 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors font-bold flex items-center gap-1"><i class="fas fa-times"></i> Tolak</button>
                                            </div>
                                            <span v-else :class="item.status_verifikasi == 'Disetujui' ? 'text-green-600 dark:text-green-400 font-bold flex items-center gap-1' : 'text-red-600 dark:text-red-400 font-bold flex items-center gap-1'">
                                                <i class="fas" :class="item.status_verifikasi == 'Disetujui' ? 'fa-check-circle' : 'fa-times-circle'"></i> {{ item.status_verifikasi }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="izinGuru.length === 0">
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">Belum ada pengajuan izin/sakit dari guru.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal Tambah Manual (Siswa) -->
        <div v-if="showModal" class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="submitManual">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">Tambah Izin/Sakit Manual</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tanggal</label>
                                    <input type="date" v-model="form.tanggal" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Kelas</label>
                                    <select v-model="form.kelas_id" @change="loadSiswa" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                        <option value="">-- Pilih Kelas --</option>
                                        <option v-for="k in kelas" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Siswa</label>
                                    <select v-model="form.id_siswa" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required :disabled="!form.kelas_id">
                                        <option value="">-- Pilih Siswa --</option>
                                        <option v-for="s in siswaList" :key="s.id" :value="s.id">{{ s.nama_lengkap }} ({{ s.nis }})</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Status</label>
                                    <select v-model="form.status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                        <option value="Izin">Izin</option>
                                        <option value="Sakit">Sakit</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Keterangan</label>
                                    <textarea v-model="form.keterangan" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Surat Bukti (Opsional)</label>
                                    <input type="file" @change="handleFile" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" :disabled="form.processing" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Simpan
                            </button>
                            <button type="button" @click="showModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
