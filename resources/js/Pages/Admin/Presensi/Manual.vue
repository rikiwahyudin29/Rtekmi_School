<script setup>
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import axios from 'axios';

const props = defineProps({
    kelas: Array
});

const filterForm = ref({
    role: 'siswa',
    tanggal: new Date().toISOString().split('T')[0],
    id_kelas: ''
});

const listBelumAbsen = ref([]);
const loadingList = ref(false);
const messageError = ref('');
const messageLibur = ref('');

const loadBelumAbsen = async () => {
    if (filterForm.value.role === 'siswa' && !filterForm.value.id_kelas) {
        listBelumAbsen.value = [];
        return;
    }
    
    loadingList.value = true;
    messageError.value = '';
    messageLibur.value = '';
    
    try {
        const res = await axios.get(route('admin.presensi.get_belum_absen'), { params: filterForm.value });
        if (res.data.status === 'libur') {
            messageLibur.value = res.data.message;
            listBelumAbsen.value = [];
        } else {
            // Inisialisasi properti form per user
            listBelumAbsen.value = res.data.data.map(u => ({
                ...u,
                formStatus: 'Hadir',
                formJam: '07:00',
                isSaving: false,
                saveSuccess: false
            }));
        }
    } catch (e) {
        messageError.value = 'Gagal mengambil data belum absen.';
    } finally {
        loadingList.value = false;
    }
};

watch(filterForm, () => {
    loadBelumAbsen();
}, { deep: true });

const simpanManual = async (user) => {
    user.isSaving = true;
    user.saveSuccess = false;
    
    try {
        const payload = {
            role: filterForm.value.role,
            user_id: user.id,
            status_kehadiran: user.formStatus,
            tanggal: filterForm.value.tanggal,
            jam_masuk: (user.formStatus === 'Hadir' || user.formStatus === 'Terlambat') ? user.formJam : null
        };
        
        const res = await axios.post(route('admin.presensi.simpan_manual_ajax'), payload);
        
        if (res.data.success) {
            user.saveSuccess = true;
            // Hapus dari list setelah 1 detik
            setTimeout(() => {
                listBelumAbsen.value = listBelumAbsen.value.filter(u => u.id !== user.id);
            }, 800);
        } else {
            alert(res.data.message);
        }
    } catch (e) {
        alert('Terjadi kesalahan saat menyimpan presensi.');
    } finally {
        user.isSaving = false;
    }
};

const handleStatusChange = (user) => {
    if (user.formStatus !== 'Hadir' && user.formStatus !== 'Terlambat') {
        // Langsung simpan jika bukan Hadir/Terlambat
        simpanManual(user);
    }
};
</script>

<template>
    <DashboardLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                
                <div class="mb-8">
                    <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-1">Input Manual (Bulk)</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Pilih Role dan Tanggal, lalu klik status untuk menyimpan otomatis secara instan.</p>
                </div>
                
                <!-- Filter Section -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Tipe Absen</label>
                            <select v-model="filterForm.role" class="block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="siswa">Siswa</option>
                                <option value="guru">Guru & Staff</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Tanggal</label>
                            <input type="date" v-model="filterForm.tanggal" class="block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div v-if="filterForm.role === 'siswa'">
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Filter Kelas</label>
                            <select v-model="filterForm.id_kelas" class="block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                <option value="">-- Pilih Kelas --</option>
                                <option v-for="k in kelas" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Info Libur -->
                <div v-if="messageLibur" class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-2xl p-6 text-center">
                    <i class="fas fa-calendar-times text-4xl text-red-500 mb-3"></i>
                    <h3 class="text-lg font-bold text-red-700 dark:text-red-400">{{ messageLibur }}</h3>
                    <p class="text-sm text-red-600 dark:text-red-300 mt-1">Sistem memblokir input absen untuk hari libur.</p>
                </div>

                <!-- Daftar Belum Absen -->
                <div v-else-if="loadingList" class="text-center py-12 text-gray-500">
                    <i class="fas fa-circle-notch fa-spin text-4xl mb-4 text-indigo-500"></i>
                    <p>Memuat data yang belum absen...</p>
                </div>

                <div v-else-if="listBelumAbsen.length > 0" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex justify-between items-center">
                        <h3 class="font-bold text-gray-900 dark:text-white">Daftar {{ filterForm.role === 'siswa' ? 'Siswa' : 'Guru' }} Belum Absen</h3>
                        <span class="bg-indigo-100 text-indigo-800 text-xs px-2.5 py-1 rounded-full font-bold">{{ listBelumAbsen.length }} Orang</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/20">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama Lengkap</th>
                                    <th v-if="filterForm.role === 'siswa'" class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kelas</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-48">Jam (Kalo Hadir)</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="user in listBelumAbsen" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50" :class="{'bg-green-50 dark:bg-green-900/20': user.saveSuccess}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-bold text-gray-900 dark:text-white">{{ user.nama_lengkap ?? user.nama_guru }}</div>
                                        <div class="text-xs text-gray-500" v-if="user.nis">{{ user.nis }}</div>
                                    </td>
                                    <td v-if="filterForm.role === 'siswa'" class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                                        {{ user.kelas?.nama_kelas }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <select v-model="user.formStatus" @change="handleStatusChange(user)" :disabled="user.isSaving || user.saveSuccess" class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:ring-indigo-500 sm:text-sm font-semibold"
                                            :class="{'text-green-600': user.formStatus == 'Hadir', 'text-orange-600': user.formStatus == 'Terlambat', 'text-yellow-600': user.formStatus == 'Sakit' || user.formStatus == 'Izin', 'text-red-600': user.formStatus == 'Alpha', 'text-purple-600': user.formStatus == 'Dinas Luar'}">
                                            <option value="Hadir">Hadir</option>
                                            <option value="Terlambat">Terlambat</option>
                                            <option value="Sakit">Sakit</option>
                                            <option value="Izin">Izin</option>
                                            <option value="Alpha">Alpha</option>
                                            <option v-if="filterForm.role === 'guru'" value="Dinas Luar">Dinas Luar</option>
                                        </select>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="time" v-if="user.formStatus === 'Hadir' || user.formStatus === 'Terlambat'" v-model="user.formJam" @keyup.enter="simpanManual(user)" :disabled="user.isSaving || user.saveSuccess" class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:ring-indigo-500 sm:text-sm text-center" title="Tekan Enter untuk menyimpan">
                                        <span v-else class="text-xs text-gray-400 italic">Otomatis tersimpan</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button v-if="user.formStatus === 'Hadir' || user.formStatus === 'Terlambat'" @click="simpanManual(user)" :disabled="user.isSaving || user.saveSuccess" class="bg-indigo-600 hover:bg-indigo-700 text-white p-2 rounded-lg text-xs font-bold transition-all disabled:opacity-50 flex items-center justify-center w-10 h-10">
                                            <i v-if="user.isSaving" class="fas fa-circle-notch fa-spin"></i>
                                            <i v-else-if="user.saveSuccess" class="fas fa-check"></i>
                                            <i v-else class="fas fa-save"></i>
                                        </button>
                                        <div v-else-if="user.isSaving" class="text-indigo-600 flex items-center justify-center w-10 h-10">
                                            <i class="fas fa-circle-notch fa-spin"></i>
                                        </div>
                                        <div v-else-if="user.saveSuccess" class="text-green-600 flex items-center justify-center w-10 h-10 bg-green-100 rounded-lg">
                                            <i class="fas fa-check"></i>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div v-else-if="!loadingList && filterForm.role === 'siswa' && !filterForm.id_kelas" class="text-center py-12 text-gray-500">
                    <i class="fas fa-chalkboard text-4xl mb-4 text-gray-300"></i>
                    <p>Silakan pilih kelas terlebih dahulu.</p>
                </div>

                <div v-else-if="!loadingList && listBelumAbsen.length === 0" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-2xl p-8 text-center">
                    <i class="fas fa-check-circle text-5xl text-green-500 mb-4"></i>
                    <h3 class="text-xl font-bold text-green-700 dark:text-green-400">Semua Sudah Absen!</h3>
                    <p class="text-sm text-green-600 dark:text-green-300 mt-2">Tidak ada {{ filterForm.role === 'siswa' ? 'siswa' : 'guru' }} yang belum absen pada tanggal ini.</p>
                </div>

            </div>
        </div>
    </DashboardLayout>
</template>
