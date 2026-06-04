<script setup>
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import axios from 'axios';

const props = defineProps({
    dataIzin: Object,
    filterRole: String,
    perPage: [Number, String],
    tanggal: String,
    kelas: Array,
    guruList: Array
});

const currentRole = ref(props.filterRole || 'siswa');
const currentPerPage = ref(props.perPage || 10);

watch([currentRole, currentPerPage], ([newRole, newPerPage]) => {
    router.get(
        route('admin.presensi.izin'),
        { role: newRole, per_page: newPerPage },
        { preserveState: true, replace: true }
    );
});

// Modal State
const showModal = ref(false);
const siswaList = ref([]);

const form = useForm({
    role: 'siswa',
    kelas_id: '',
    id_siswa: '',
    id_guru: '',
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
                        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-1">Data Izin/Sakit & Verifikasi</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Kelola pengajuan izin, sakit, dan input presensi manual untuk siswa maupun guru.</p>
                    </div>
                    <button @click="showModal = true" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-sm transition-all flex items-center gap-2 text-sm">
                        <i class="fas fa-plus"></i> Tambah Manual
                    </button>
                </div>
                
                <div class="bg-white dark:bg-gray-800 overflow-hidden rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="p-6">
                        
                        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4 border-b border-gray-100 dark:border-gray-700 pb-4">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <i class="fas" :class="currentRole === 'guru' ? 'fa-chalkboard-teacher text-teal-500' : 'fa-user-graduate text-indigo-500'"></i> 
                                Pengajuan {{ currentRole === 'guru' ? 'Guru' : 'Siswa' }}
                            </h3>
                            
                            <div class="flex flex-wrap items-center gap-3">
                                <div class="flex items-center gap-2">
                                    <label class="text-sm text-gray-600 dark:text-gray-400 font-medium">Tampilkan:</label>
                                    <select v-model="currentRole" class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm font-medium focus:ring-indigo-500 focus:border-indigo-500 py-1.5 pl-3 pr-8 shadow-sm">
                                        <option value="siswa">Data Siswa</option>
                                        <option value="guru">Data Guru</option>
                                    </select>
                                </div>
                                <div class="flex items-center gap-2">
                                    <label class="text-sm text-gray-600 dark:text-gray-400 font-medium">Baris:</label>
                                    <select v-model="currentPerPage" class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm font-medium focus:ring-indigo-500 focus:border-indigo-500 py-1.5 pl-3 pr-8 shadow-sm">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Nama {{ currentRole === 'guru' ? 'Guru' : 'Siswa' }}
                                        </th>
                                        <th v-if="currentRole === 'siswa'" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kelas</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Keterangan</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Bukti</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="item in dataIzin.data" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ item.tanggal }}</td>
                                        
                                        <td v-if="currentRole === 'siswa'" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 font-bold">
                                            {{ item.siswa?.nama_lengkap }}
                                        </td>
                                        <td v-else class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 font-bold">
                                            {{ item.guru?.nama_lengkap ?? item.guru?.nama_guru }}
                                        </td>

                                        <td v-if="currentRole === 'siswa'" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ item.siswa?.kelas?.nama_kelas }}
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-lg" 
                                                  :class="item.status_kehadiran == 'Izin' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400' : 
                                                         (item.status_kehadiran == 'Sakit' ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-400' :
                                                         (item.status_kehadiran == 'Dinas Luar' ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400'))">
                                                {{ item.status_kehadiran }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate" :title="item.keterangan">{{ item.keterangan }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 dark:text-blue-400 font-bold">
                                            <a v-if="item.bukti_izin" :href="`/uploads/surat_izin/${item.bukti_izin}`" target="_blank" class="hover:underline flex items-center gap-1"><i class="fas fa-external-link-alt"></i> Lihat</a>
                                            <span v-else class="text-gray-400 dark:text-gray-600 font-normal">-</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div v-if="item.status_verifikasi == 'Pending'" class="flex gap-2">
                                                <button @click="ubahStatus(item.id, 'Disetujui')" class="bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 px-3 py-1.5 rounded-lg hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors font-bold flex items-center gap-1"><i class="fas fa-check"></i> Setuju</button>
                                                <button @click="ubahStatus(item.id, 'Ditolak')" class="bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 px-3 py-1.5 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors font-bold flex items-center gap-1"><i class="fas fa-times"></i> Tolak</button>
                                            </div>
                                            <span v-else :class="item.status_verifikasi == 'Disetujui' ? 'text-green-600 dark:text-green-400 font-bold flex items-center gap-1' : 'text-red-600 dark:text-red-400 font-bold flex items-center gap-1'">
                                                <i class="fas" :class="item.status_verifikasi == 'Disetujui' ? 'fa-check-circle' : 'fa-times-circle'"></i> {{ item.status_verifikasi }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="dataIzin.data.length === 0">
                                        <td :colspan="currentRole === 'siswa' ? 7 : 6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">Belum ada data yang ditampilkan.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Container -->
                        <div class="mt-6">
                            <Pagination :links="dataIzin.links" />
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal Tambah Manual -->
        <div v-if="showModal" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100 dark:border-gray-700">
                    <form @submit.prevent="submitManual">
                        <div class="px-6 pt-6 pb-4">
                            <h3 class="text-xl leading-6 font-bold text-gray-900 dark:text-white mb-6" id="modal-title">Tambah Manual</h3>
                            
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Tanggal</label>
                                    <input type="date" v-model="form.tanggal" class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Role</label>
                                    <select v-model="form.role" class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                        <option value="siswa">Siswa</option>
                                        <option value="guru">Guru/Staff</option>
                                    </select>
                                </div>
                                <div v-if="form.role === 'siswa'">
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Kelas</label>
                                    <select v-model="form.kelas_id" @change="loadSiswa" class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" :required="form.role === 'siswa'">
                                        <option value="">-- Pilih Kelas --</option>
                                        <option v-for="k in kelas" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                                    </select>
                                </div>
                                <div v-if="form.role === 'siswa'">
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Siswa</label>
                                    <select v-model="form.id_siswa" class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" :required="form.role === 'siswa'" :disabled="!form.kelas_id">
                                        <option value="">-- Pilih Siswa --</option>
                                        <option v-for="s in siswaList" :key="s.id" :value="s.id">{{ s.nama_lengkap }} ({{ s.nis }})</option>
                                    </select>
                                </div>
                                <div v-if="form.role === 'guru'">
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Guru</label>
                                    <select v-model="form.id_guru" class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" :required="form.role === 'guru'">
                                        <option value="">-- Pilih Guru --</option>
                                        <option v-for="g in guruList" :key="g.id" :value="g.id">{{ g.nama_lengkap }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Status</label>
                                    <select v-model="form.status" class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                        <option value="Hadir">Hadir</option>
                                        <option value="Sakit">Sakit</option>
                                        <option value="Izin">Izin</option>
                                        <option value="Terlambat">Terlambat</option>
                                        <option value="Alpha">Alpha</option>
                                        <option v-if="form.role === 'guru'" value="Dinas Luar">Dinas Luar</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Keterangan</label>
                                    <textarea v-model="form.keterangan" rows="2" class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Surat Bukti (Opsional)</label>
                                    <input type="file" @change="handleFile" class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900/50 dark:file:text-indigo-300">
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 flex flex-row-reverse gap-3 rounded-b-2xl">
                            <button type="submit" :disabled="form.processing" class="inline-flex justify-center rounded-xl border border-transparent shadow-sm px-5 py-2 bg-indigo-600 text-sm font-bold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                Simpan Data
                            </button>
                            <button type="button" @click="showModal = false" class="inline-flex justify-center rounded-xl border border-gray-300 dark:border-gray-600 shadow-sm px-5 py-2 bg-white dark:bg-gray-800 text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
