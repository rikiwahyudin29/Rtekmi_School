<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import axios from 'axios';

const props = defineProps({
    manualSiswa: Array,
    manualGuru: Array,
    tanggal: String,
    kelas: Array,
    guruList: Array
});

// Modal State
const showModal = ref(false);
const siswaList = ref([]);

const form = useForm({
    role: 'siswa',
    kelas_id: '',
    id_siswa: '',
    id_guru: '',
    status: 'Hadir',
    keterangan: '',
    tanggal: props.tanggal,
    jam_masuk: '07:00'
});

const loadSiswa = async () => {
    if (!form.kelas_id) {
        siswaList.value = [];
        return;
    }
    const res = await axios.get(route('admin.presensi.get_siswa_by_kelas', form.kelas_id));
    siswaList.value = res.data;
};

const submitManual = () => {
    form.post(route('admin.presensi.simpan_manual'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
            form.tanggal = props.tanggal;
            form.jam_masuk = '07:00';
            form.status = 'Hadir';
            form.role = 'siswa';
        }
    });
};

const hapusManual = (id) => {
    if (confirm(`Anda yakin ingin menghapus data presensi manual ini?`)) {
        form.delete(route('admin.presensi.hapus_manual', id));
    }
};
</script>

<template>
    <DashboardLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                
                <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-1">Input Presensi Manual</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Kelola dan input data kehadiran secara manual jika siswa/guru lupa absen.</p>
                    </div>
                    <button @click="showModal = true" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-sm transition-all flex items-center gap-2 text-sm">
                        <i class="fas fa-plus"></i> Tambah Presensi
                    </button>
                </div>
                
                <!-- Tabel Manual Siswa -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden rounded-2xl border border-gray-100 dark:border-gray-700">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-700 pb-3 flex items-center gap-2">
                            <i class="fas fa-user-graduate text-indigo-500"></i> Presensi Manual Siswa
                        </h3>
                        <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jam</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama Siswa</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kelas</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Keterangan</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="item in manualSiswa" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ item.tanggal }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ item.jam_masuk ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 font-bold">{{ item.siswa?.nama_lengkap }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ item.siswa?.kelas?.nama_kelas }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-lg" :class="item.status_kehadiran == 'Hadir' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400' : (item.status_kehadiran == 'Terlambat' ? 'bg-orange-100 text-orange-800' : (item.status_kehadiran == 'Alpha' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'))">
                                                {{ item.status_kehadiran }}
                                            </span>
                                            <div v-if="item.menit_terlambat > 0" class="text-[10px] text-red-500 mt-1">Telat {{ item.menit_terlambat }}m</div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ item.keterangan || '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button @click="hapusManual(item.id)" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-colors"><i class="fas fa-trash"></i> Hapus</button>
                                        </td>
                                    </tr>
                                    <tr v-if="manualSiswa.length === 0">
                                        <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">Belum ada input presensi manual.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tabel Manual Guru -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden rounded-2xl border border-gray-100 dark:border-gray-700">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-700 pb-3 flex items-center gap-2">
                            <i class="fas fa-chalkboard-teacher text-teal-500"></i> Presensi Manual Guru
                        </h3>
                        <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jam</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama Guru</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Keterangan</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="item in manualGuru" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ item.tanggal }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ item.jam_masuk ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 font-bold">{{ item.guru?.nama_lengkap ?? item.guru?.nama_guru }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-lg" :class="item.status_kehadiran == 'Hadir' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400' : (item.status_kehadiran == 'Terlambat' ? 'bg-orange-100 text-orange-800' : (item.status_kehadiran == 'Alpha' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'))">
                                                {{ item.status_kehadiran }}
                                            </span>
                                            <div v-if="item.menit_terlambat > 0" class="text-[10px] text-red-500 mt-1">Telat {{ item.menit_terlambat }}m</div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ item.keterangan || '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button @click="hapusManual(item.id)" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-colors"><i class="fas fa-trash"></i> Hapus</button>
                                        </td>
                                    </tr>
                                    <tr v-if="manualGuru.length === 0">
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">Belum ada input presensi manual dari guru.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal Tambah Manual -->
        <div v-if="showModal" class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="submitManual">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">Input Presensi Manual</h3>
                            
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Tanggal</label>
                                        <input type="date" v-model="form.tanggal" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Jam Masuk (07:00)</label>
                                        <input type="time" v-model="form.jam_masuk" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Role</label>
                                    <select v-model="form.role" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                        <option value="siswa">Siswa</option>
                                        <option value="guru">Guru/Staff</option>
                                    </select>
                                </div>
                                
                                <div v-if="form.role === 'siswa'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Kelas</label>
                                        <select v-model="form.kelas_id" @change="loadSiswa" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" :required="form.role === 'siswa'">
                                            <option value="">-- Pilih Kelas --</option>
                                            <option v-for="k in kelas" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Siswa</label>
                                        <select v-model="form.id_siswa" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" :required="form.role === 'siswa'" :disabled="!form.kelas_id">
                                            <option value="">-- Pilih Siswa --</option>
                                            <option v-for="s in siswaList" :key="s.id" :value="s.id">{{ s.nama_lengkap }} ({{ s.nis }})</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div v-if="form.role === 'guru'">
                                    <label class="block text-sm font-medium text-gray-700">Guru</label>
                                    <select v-model="form.id_guru" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" :required="form.role === 'guru'">
                                        <option value="">-- Pilih Guru --</option>
                                        <option v-for="g in guruList" :key="g.id" :value="g.id">{{ g.nama_lengkap }}</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Status Kehadiran</label>
                                    <select v-model="form.status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                        <option value="Hadir">Hadir</option>
                                        <option value="Terlambat">Terlambat</option>
                                        <option value="Sakit">Sakit</option>
                                        <option value="Izin">Izin</option>
                                        <option value="Alpha">Alpha</option>
                                        <option v-if="form.role === 'guru'" value="Dinas Luar">Dinas Luar</option>
                                    </select>
                                    <p class="text-[11px] text-gray-500 mt-1" v-if="form.status == 'Terlambat'">Jika Terlambat, pastikan "Jam Masuk" diisi melewati batas jam masuk agar sistem dapat menghitung akumulasi telat secara otomatis.</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Keterangan (Opsional)</label>
                                    <textarea v-model="form.keterangan" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Alasan lupa absen / keterangan lain..."></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" :disabled="form.processing" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Simpan Data
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
