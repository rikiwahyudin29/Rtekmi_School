<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    monitoring: Array,
    buku_tamu: Array,
    izin_keluar: Array,
    siswa: Array,
    kelas: Array,
    semua_guru: Array,
    hari_ini: String,
    tanggal_ini: String,
    jurnal_saya: Object,
    id_guru_login: Number
});

const activeTab = ref('kbm');

const formTamu = useForm({
    nama_tamu: '',
    instansi_asal: '',
    keperluan: '',
    bertemu_dengan: '',
    jam_datang: new Date().toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'})
});

const simpanTamu = () => {
    formTamu.post(route('guru.piket.simpan_tamu'), {
        preserveScroll: true,
        onSuccess: () => formTamu.reset()
    });
};

const formIzin = useForm({
    siswa_id: '',
    alasan: ''
});

const simpanIzin = () => {
    formIzin.post(route('guru.piket.simpan_izin'), {
        preserveScroll: true,
        onSuccess: () => formIzin.reset()
    });
};

const formJurnal = useForm({
    keterangan: props.jurnal_saya?.keterangan || '',
    tugas: props.jurnal_saya?.tugas || '',
    guru_pengganti_id: props.jurnal_saya?.guru_pengganti_id || ''
});

const simpanJurnal = () => {
    formJurnal.post(route('guru.piket.simpan_jurnal'), {
        preserveScroll: true
    });
};
</script>

<template>
    <Head title="Dashboard Piket" />

    <DashboardLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <div class="mb-8">
                <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    Dashboard Piket Harian
                </h1>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Selamat bertugas piket hari ini ({{ hari_ini }}, {{ tanggal_ini }}). Silakan jalankan tugas Anda.
                </p>
            </div>

            <div v-if="$page.props.flash.message" class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-xl font-bold flex items-center gap-2">
                <i class="fas fa-check-circle"></i> {{ $page.props.flash.message }}
            </div>

            <!-- Tabs -->
            <div class="flex overflow-x-auto border-b border-gray-200 dark:border-gray-700 mb-6 pb-px custom-scrollbar">
                <button @click="activeTab = 'kbm'" :class="[activeTab === 'kbm' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300', 'whitespace-nowrap py-4 px-6 border-b-2 font-bold text-sm transition-colors flex items-center gap-2']">
                    <i class="fas fa-chalkboard-teacher"></i> Monitoring KBM
                </button>
                <button @click="activeTab = 'tamu'" :class="[activeTab === 'tamu' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300', 'whitespace-nowrap py-4 px-6 border-b-2 font-bold text-sm transition-colors flex items-center gap-2']">
                    <i class="fas fa-users"></i> Buku Tamu
                </button>
                <button @click="activeTab = 'izin'" :class="[activeTab === 'izin' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300', 'whitespace-nowrap py-4 px-6 border-b-2 font-bold text-sm transition-colors flex items-center gap-2']">
                    <i class="fas fa-running"></i> Izin Keluar
                </button>
                <button @click="activeTab = 'jurnal'" :class="[activeTab === 'jurnal' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300', 'whitespace-nowrap py-4 px-6 border-b-2 font-bold text-sm transition-colors flex items-center gap-2']">
                    <i class="fas fa-book-open"></i> Jurnal Piket Anda
                </button>
            </div>

            <!-- TAB 1: Monitoring KBM -->
            <div v-show="activeTab === 'kbm'" class="space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Jadwal Mengajar Hari Ini</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 border-b border-gray-200 dark:border-gray-600">
                                <tr>
                                    <th class="px-6 py-4 font-bold">Jam Mengajar</th>
                                    <th class="px-6 py-4 font-bold">Guru & Kelas</th>
                                    <th class="px-6 py-4 font-bold">Status Kehadiran</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="monitoring.length === 0">
                                    <td colspan="3" class="px-6 py-8 text-center text-gray-500">Tidak ada jadwal mengajar pada hari ini.</td>
                                </tr>
                                <tr v-for="m in monitoring" :key="m.id" class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600/50">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900 dark:text-white">{{ m.jam_mulai.substring(0,5) }} - {{ m.jam_selesai.substring(0,5) }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900 dark:text-white">{{ m.nama_guru }}</div>
                                        <div class="text-xs text-gray-500">Kls {{ m.nama_kelas }} - {{ m.nama_mapel }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span :class="['px-3 py-1 rounded-full text-xs font-bold border', m.badge_color]">{{ m.status_kbm }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 2: Buku Tamu -->
            <div v-show="activeTab === 'tamu'" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6 border-b pb-4 dark:border-gray-700">Input Buku Tamu</h2>
                        <form @submit.prevent="simpanTamu" class="space-y-4">
                            <div>
                                <label class="block mb-2 text-sm font-bold text-gray-900 dark:text-white">Nama Tamu</label>
                                <input type="text" v-model="formTamu.nama_tamu" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-bold text-gray-900 dark:text-white">Instansi Asal</label>
                                <input type="text" v-model="formTamu.instansi_asal" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Kosongkan jika tamu pribadi">
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-bold text-gray-900 dark:text-white">Bertemu Dengan (Siapa)</label>
                                <input type="text" v-model="formTamu.bertemu_dengan" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-bold text-gray-900 dark:text-white">Jam Kedatangan</label>
                                <input type="time" v-model="formTamu.jam_datang" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-bold text-gray-900 dark:text-white">Keperluan</label>
                                <textarea v-model="formTamu.keperluan" rows="3" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required></textarea>
                            </div>
                            <button type="submit" :disabled="formTamu.processing" class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md transition-all">
                                {{ formTamu.processing ? 'Menyimpan...' : 'Simpan Tamu' }}
                            </button>
                        </form>
                    </div>
                </div>
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Daftar Tamu Hari Ini</h2>
                        </div>
                        <div class="p-6">
                            <div v-if="buku_tamu.length === 0" class="text-center text-gray-500 py-4">Belum ada tamu hari ini.</div>
                            <div v-else class="space-y-4">
                                <div v-for="t in buku_tamu" :key="t.id" class="flex flex-col sm:flex-row sm:items-center justify-between p-4 border border-gray-100 dark:border-gray-700 rounded-xl hover:border-indigo-300 transition-colors">
                                    <div>
                                        <div class="font-bold text-gray-900 dark:text-white text-lg">{{ t.nama_lengkap }}</div>
                                        <div class="text-sm text-gray-500">{{ t.instansi_asal || 'Pribadi' }} • Tujuan: {{ t.bertemu_dengan }}</div>
                                        <div class="text-xs mt-1">{{ t.keperluan }}</div>
                                        <div class="flex items-center gap-2 mt-2">
                                            <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded text-xs font-bold"><i class="fas fa-sign-in-alt text-blue-500 mr-1"></i> {{ t.jam_datang.substring(0,5) }}</span>
                                            <span v-if="t.jam_pulang" class="px-2 py-1 bg-emerald-50 text-emerald-600 rounded text-xs font-bold"><i class="fas fa-sign-out-alt mr-1"></i> {{ t.jam_pulang.substring(0,5) }}</span>
                                        </div>
                                    </div>
                                    <div class="mt-4 sm:mt-0 flex gap-2">
                                        <Link v-if="!t.jam_pulang" :href="route('guru.piket.pulang_tamu', t.id)" method="post" as="button" class="px-3 py-2 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white rounded-lg text-xs font-bold transition-colors">Set Pulang</Link>
                                        <Link :href="route('guru.piket.hapus_tamu', t.id)" method="delete" as="button" class="px-3 py-2 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white rounded-lg text-xs font-bold transition-colors"><i class="fas fa-trash"></i></Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: Izin Keluar -->
            <div v-show="activeTab === 'izin'" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6 border-b pb-4 dark:border-gray-700">Catat Izin Keluar</h2>
                        <form @submit.prevent="simpanIzin" class="space-y-4">
                            <div>
                                <label class="block mb-2 text-sm font-bold text-gray-900 dark:text-white">Pilih Siswa</label>
                                <select v-model="formIzin.siswa_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                                    <option value="" disabled>-- Cari/Pilih Siswa --</option>
                                    <option v-for="s in siswa" :key="s.id" :value="s.id">{{ s.nama_lengkap }} ({{ s.nisn }})</option>
                                </select>
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-bold text-gray-900 dark:text-white">Alasan / Keperluan</label>
                                <textarea v-model="formIzin.alasan" rows="3" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required></textarea>
                            </div>
                            <button type="submit" :disabled="formIzin.processing" class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md transition-all">
                                {{ formIzin.processing ? 'Menyimpan...' : 'Simpan Izin Keluar' }}
                            </button>
                        </form>
                    </div>
                </div>
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Izin Siswa Hari Ini</h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 border-b border-gray-200 dark:border-gray-600">
                                    <tr>
                                        <th class="px-6 py-4 font-bold">Nama Siswa</th>
                                        <th class="px-6 py-4 font-bold">Waktu Keluar</th>
                                        <th class="px-6 py-4 font-bold">Alasan</th>
                                        <th class="px-6 py-4 font-bold text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="izin_keluar.length === 0">
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">Belum ada siswa izin keluar.</td>
                                    </tr>
                                    <tr v-for="i in izin_keluar" :key="i.id" class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600/50">
                                        <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                                            {{ i.nama_lengkap }}
                                            <div class="text-xs text-gray-500 font-normal mt-1">{{ i.nama_kelas }}</div>
                                        </td>
                                        <td class="px-6 py-4">{{ new Date(i.waktu_keluar).toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'}) }} WIB</td>
                                        <td class="px-6 py-4 max-w-xs">{{ i.alasan }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <Link :href="route('guru.piket.hapus_izin', i.id)" method="delete" as="button" class="text-red-500 hover:text-red-700 ml-3 transition-colors" title="Batal/Hapus">
                                                <i class="fas fa-trash"></i>
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 4: Jurnal Piket -->
            <div v-show="activeTab === 'jurnal'" class="max-w-2xl mx-auto">
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-700 pb-4 mb-6">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-lg">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Laporan Jurnal Piket Anda</h2>
                            <p class="text-sm text-gray-500">Isi sebelum mengakhiri tugas piket Anda hari ini.</p>
                        </div>
                    </div>

                    <form @submit.prevent="simpanJurnal" class="space-y-5">
                        <div>
                            <label class="block mb-2 text-sm font-bold text-gray-900 dark:text-white">Keterangan Singkat</label>
                            <input type="text" v-model="formJurnal.keterangan" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Contoh: KBM Berjalan Lancar" required>
                        </div>
                        
                        <div>
                            <label class="block mb-2 text-sm font-bold text-gray-900 dark:text-white">Tugas / Detail Kegiatan Selama Piket</label>
                            <textarea v-model="formJurnal.tugas" rows="5" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Sebutkan kegiatan detail seperti pengecekan keliling 3 kali, memproses izin 2 siswa, dll." required></textarea>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-bold text-gray-900 dark:text-white">Menggantikan Guru Lain? (Opsional)</label>
                            <select v-model="formJurnal.guru_pengganti_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">-- Tidak Menggantikan Siapapun --</option>
                                <option v-for="g in semua_guru" :key="g.id" :value="g.id">{{ g.nama_lengkap }}</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Hanya diisi jika Anda menggantikan jadwal piket guru lain hari ini.</p>
                        </div>

                        <button type="submit" :disabled="formJurnal.processing" class="w-full py-4 px-4 bg-blue-600 hover:bg-blue-700 text-white font-bold text-lg rounded-xl shadow-md transition-all">
                            <i class="fas fa-save mr-2"></i> {{ formJurnal.processing ? 'Menyimpan Laporan...' : 'Kirim Laporan Piket' }}
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </DashboardLayout>
</template>
