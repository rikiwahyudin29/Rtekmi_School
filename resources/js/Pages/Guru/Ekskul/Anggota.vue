<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref, computed, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    ekskul: Object,
    anggota: Array,
    kelas: Array
});

const activeTab = ref('pending');

const pendingMembers = computed(() => props.anggota.filter(a => a.status_anggota === 'Pending'));
const activeMembers = computed(() => props.anggota.filter(a => a.status_anggota === 'Approved'));

const validasi = (id, status, nama) => {
    if (confirm(`Anda yakin ingin merubah status ${nama} menjadi ${status}?`)) {
        router.post(route('guru.ekskul.anggota.validasi'), {
            id: id,
            status: status
        }, {
            preserveScroll: true
        });
    }
};

const showModalManual = ref(false);
const showModalImport = ref(false);

const kelasIdFilter = ref('');
const calonSiswa = ref([]);
const loadingSiswa = ref(false);
const selectedSiswaIds = ref([]);

const fetchCalonSiswa = async () => {
    loadingSiswa.value = true;
    try {
        const response = await axios.get(route('guru.ekskul.anggota.calon', props.ekskul.id), {
            params: { kelas_id: kelasIdFilter.value }
        });
        calonSiswa.value = response.data.siswa;
        selectedSiswaIds.value = [];
    } catch (error) {
        console.error(error);
    } finally {
        loadingSiswa.value = false;
    }
};

watch(kelasIdFilter, () => {
    fetchCalonSiswa();
});

watch(showModalManual, (val) => {
    if (val && calonSiswa.value.length === 0) {
        fetchCalonSiswa();
    }
});

const toggleSelectAll = (e) => {
    if(e.target.checked) {
        selectedSiswaIds.value = calonSiswa.value.map(s => s.id);
    } else {
        selectedSiswaIds.value = [];
    }
};

const formManual = useForm({
    ekskul_id: props.ekskul.id,
    siswa_ids: []
});

const submitManual = () => {
    if (selectedSiswaIds.value.length === 0) {
        alert("Pilih minimal satu siswa!");
        return;
    }
    formManual.siswa_ids = selectedSiswaIds.value;
    formManual.post(route('guru.ekskul.anggota.manual'), {
        preserveScroll: true,
        onSuccess: () => {
            showModalManual.value = false;
            selectedSiswaIds.value = [];
        }
    });
};

const formImport = useForm({
    ekskul_id: props.ekskul.id,
    file_excel: null
});

const submitImport = () => {
    formImport.post(route('guru.ekskul.anggota.import'), {
        preserveScroll: true,
        onSuccess: () => {
            showModalImport.value = false;
            formImport.reset('file_excel');
        }
    });
};
</script>

<template>
    <Head :title="`Anggota ${ekskul.nama_ekskul}`" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <Link :href="route('guru.ekskul.index')" class="text-indigo-600 hover:text-indigo-800 text-sm font-bold flex items-center gap-2 mb-2">
                        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                    </Link>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <div class="w-10 h-10 bg-white rounded-xl shadow-sm border border-gray-100 p-1">
                            <img :src="`/uploads/ekskul/${ekskul.logo || 'default_ekskul.png'}`" class="w-full h-full object-contain">
                        </div>
                        Anggota {{ ekskul.nama_ekskul }}
                    </h2>
                </div>
                <!-- Action Buttons -->
                <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                    <button @click="showModalManual = true" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-sm text-sm flex items-center gap-2 transition-colors">
                        <i class="fas fa-plus"></i> Tambah Manual
                    </button>
                    <button @click="showModalImport = true" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl shadow-sm text-sm flex items-center gap-2 transition-colors">
                        <i class="fas fa-file-excel"></i> Import Excel
                    </button>
                </div>
            </div>

            <div v-if="$page.props.flash?.message" class="bg-green-50 text-green-800 p-4 rounded-2xl shadow-sm flex items-center gap-3">
                <i class="fas fa-check-circle"></i> {{ $page.props.flash.message }}
            </div>
            <div v-if="Object.keys($page.props.errors).length > 0" class="bg-red-50 text-red-800 p-4 rounded-2xl shadow-sm flex items-center gap-3">
                <i class="fas fa-exclamation-circle"></i> Terdapat kesalahan, pastikan data yang dimasukkan benar.
            </div>

            <!-- Tabs -->
            <div class="flex gap-2 border-b border-gray-200 dark:border-gray-700">
                <button @click="activeTab = 'pending'" :class="activeTab === 'pending' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-6 py-3 font-bold border-b-2 transition-colors flex items-center gap-2">
                    Pendaftar Baru 
                    <span v-if="pendingMembers.length > 0" class="bg-red-500 text-white px-2 py-0.5 rounded-full text-xs">{{ pendingMembers.length }}</span>
                </button>
                <button @click="activeTab = 'aktif'" :class="activeTab === 'aktif' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-6 py-3 font-bold border-b-2 transition-colors flex items-center gap-2">
                    Anggota Aktif
                    <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full text-xs">{{ activeMembers.length }}</span>
                </button>
            </div>

            <!-- Content Pending -->
            <div v-if="activeTab === 'pending'" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div v-if="pendingMembers.length === 0" class="p-12 text-center text-gray-500">
                    <i class="fas fa-check-circle text-4xl mb-3 text-green-500"></i>
                    <p class="font-bold text-lg">Tidak ada pendaftar baru.</p>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 font-bold uppercase text-xs">
                            <tr>
                                <th class="px-6 py-4">Siswa</th>
                                <th class="px-6 py-4">Kelas</th>
                                <th class="px-6 py-4">Tgl Daftar</th>
                                <th class="px-6 py-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="a in pendingMembers" :key="a.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gray-200 overflow-hidden">
                                            <img :src="`/uploads/siswa/${a.foto || 'default.png'}`" @error="$event.target.src='https://ui-avatars.com/api/?name='+a.nama_lengkap" class="w-full h-full object-cover">
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900 dark:text-white">{{ a.nama_lengkap }}</div>
                                            <div class="text-xs text-gray-500">{{ a.nisn }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-bold text-indigo-600">{{ a.nama_kelas }}</td>
                                <td class="px-6 py-4">{{ a.tgl_daftar }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-center gap-2">
                                        <button @click="validasi(a.id, 'Approved', a.nama_lengkap)" class="px-3 py-1.5 bg-green-100 text-green-700 hover:bg-green-200 font-bold rounded-lg transition-colors">
                                            <i class="fas fa-check"></i> Terima
                                        </button>
                                        <button @click="validasi(a.id, 'Rejected', a.nama_lengkap)" class="px-3 py-1.5 bg-red-100 text-red-700 hover:bg-red-200 font-bold rounded-lg transition-colors">
                                            <i class="fas fa-times"></i> Tolak
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Content Aktif -->
            <div v-if="activeTab === 'aktif'" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div v-if="activeMembers.length === 0" class="p-12 text-center text-gray-500">
                    <i class="fas fa-users-slash text-4xl mb-3"></i>
                    <p class="font-bold text-lg">Belum ada anggota aktif di ekstrakurikuler ini.</p>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 font-bold uppercase text-xs">
                            <tr>
                                <th class="px-6 py-4">No</th>
                                <th class="px-6 py-4">Siswa</th>
                                <th class="px-6 py-4">Kelas</th>
                                <th class="px-6 py-4">Tgl Bergabung</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-center"><i class="fas fa-cog"></i></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="(a, index) in activeMembers" :key="a.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4">{{ index + 1 }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gray-200 overflow-hidden">
                                            <img :src="`/uploads/siswa/${a.foto || 'default.png'}`" @error="$event.target.src='https://ui-avatars.com/api/?name='+a.nama_lengkap" class="w-full h-full object-cover">
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900 dark:text-white">{{ a.nama_lengkap }}</div>
                                            <div class="text-xs text-gray-500">{{ a.nisn }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-bold">{{ a.nama_kelas }}</td>
                                <td class="px-6 py-4">{{ a.tgl_daftar }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">Aktif</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button @click="validasi(a.id, 'Dikeluarkan', a.nama_lengkap)" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition-colors tooltip" data-tip="Keluarkan Anggota">
                                        <i class="fas fa-user-times"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Modal Tambah Manual -->
        <div v-if="showModalManual" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-gray-800 w-full max-w-2xl rounded-3xl shadow-xl overflow-hidden animate-fade-in-up flex flex-col max-h-[90vh]">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50 shrink-0">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Tambah Anggota Manual</h3>
                    <button @click="showModalManual = false" class="text-gray-400 hover:text-red-500 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="p-6 flex-1 overflow-y-auto">
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Filter Kelas</label>
                        <select v-model="kelasIdFilter" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all text-sm font-medium text-gray-900 dark:text-white">
                            <option value="">Semua Kelas</option>
                            <option v-for="k in kelas" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                        </select>
                    </div>

                    <div v-if="loadingSiswa" class="py-12 flex justify-center items-center">
                        <i class="fas fa-spinner fa-spin text-3xl text-indigo-500"></i>
                    </div>
                    
                    <div v-else>
                        <div class="flex items-center justify-between mb-2">
                            <label class="flex items-center gap-2 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-300">
                                <input type="checkbox" @change="toggleSelectAll" :checked="selectedSiswaIds.length === calonSiswa.length && calonSiswa.length > 0" class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500">
                                Pilih Semua
                            </label>
                            <span class="text-xs text-gray-500 font-bold bg-gray-100 px-2 py-1 rounded-lg">{{ selectedSiswaIds.length }} Terpilih</span>
                        </div>
                        
                        <div v-if="calonSiswa.length === 0" class="py-8 text-center text-gray-500 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                            Tidak ada data siswa / Semua siswa sudah menjadi anggota.
                        </div>
                        
                        <div v-else class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                            <div class="max-h-64 overflow-y-auto">
                                <label v-for="s in calonSiswa" :key="s.id" class="flex items-center gap-4 p-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700 cursor-pointer transition-colors">
                                    <input type="checkbox" v-model="selectedSiswaIds" :value="s.id" class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500 shrink-0 ml-1">
                                    <div class="flex items-center gap-3 w-full">
                                        <div class="w-8 h-8 rounded-full bg-gray-200 overflow-hidden shrink-0">
                                            <img :src="`/uploads/siswa/${s.foto || 'default.png'}`" @error="$event.target.src='https://ui-avatars.com/api/?name='+s.nama_lengkap" class="w-full h-full object-cover">
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-bold text-sm text-gray-900 dark:text-white">{{ s.nama_lengkap }}</div>
                                            <div class="text-xs text-gray-500">{{ s.nisn }} | {{ s.nama_kelas }}</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-6 border-t border-gray-100 dark:border-gray-700 bg-gray-50 shrink-0 flex justify-end gap-2">
                    <button type="button" @click="showModalManual = false" class="px-5 py-2.5 bg-white border border-gray-200 hover:bg-gray-100 text-gray-700 font-bold rounded-xl transition-colors shadow-sm">Batal</button>
                    <button @click="submitManual" :disabled="formManual.processing || selectedSiswaIds.length === 0" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-bold rounded-xl shadow-sm transition-colors flex items-center gap-2">
                        <i v-if="formManual.processing" class="fas fa-spinner fa-spin"></i>
                        <span v-else>Tambahkan ({{ selectedSiswaIds.length }}) Siswa</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Import Excel -->
        <div v-if="showModalImport" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-gray-800 w-full max-w-lg rounded-3xl shadow-xl overflow-hidden animate-fade-in-up">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Import Anggota dari Excel</h3>
                    <button @click="showModalImport = false" class="text-gray-400 hover:text-red-500 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="p-6">
                    <div class="bg-blue-50 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 p-4 rounded-xl text-sm mb-6 flex gap-3">
                        <i class="fas fa-info-circle text-xl shrink-0 mt-0.5"></i>
                        <div>
                            Pastikan format file sesuai dengan template. Sistem akan otomatis menyetujui (Approved) siswa yang di-import. Siswa yang sudah menjadi anggota akan dilewati.
                        </div>
                    </div>

                    <a :href="route('guru.ekskul.anggota.template')" class="inline-flex w-full justify-center items-center gap-2 px-4 py-3 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-bold rounded-xl transition-colors mb-6 text-sm border border-dashed border-gray-300 dark:border-gray-600">
                        <i class="fas fa-download"></i> Download Template Excel
                    </a>

                    <form @submit.prevent="submitImport">
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Upload File Excel (.xlsx)</label>
                            <input type="file" @change="e => formImport.file_excel = e.target.files[0]" accept=".xlsx, .xls" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" required>
                            <p v-if="formImport.errors.file_excel" class="text-red-500 text-xs mt-1">{{ formImport.errors.file_excel }}</p>
                        </div>
                        <div class="flex justify-end gap-2 mt-8 border-t border-gray-100 dark:border-gray-700 pt-6">
                            <button type="button" @click="showModalImport = false" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition-colors">Batal</button>
                            <button type="submit" :disabled="formImport.processing" class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl shadow-sm transition-colors flex items-center gap-2">
                                <i v-if="formImport.processing" class="fas fa-spinner fa-spin"></i>
                                <span v-else>Mulai Import</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </DashboardLayout>
</template>
