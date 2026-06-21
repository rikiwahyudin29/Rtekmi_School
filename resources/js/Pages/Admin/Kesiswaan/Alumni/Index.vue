<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref, watch } from 'vue';

const props = defineProps({
    alumni: Object,
    search: String,
    per_page: [String, Number]
});

// Search & Pagination
const searchInput = ref(props.search || '');
const perPageInput = ref(props.per_page || 10);

let searchTimeout = null;
watch([searchInput, perPageInput], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('admin.kesiswaan.alumni.index'), {
            search: searchInput.value,
            per_page: perPageInput.value
        }, {
            preserveState: true,
            replace: true
        });
    }, 500);
});

// Modal Logic
const showModal = ref(false);
const form = useForm({
    nisn: '',
    nama_lengkap: '',
    jenis_kelamin: 'L',
    no_hp_siswa: '',
    alamat: '',
    tahun_angkatan: ''
});

const submitForm = () => {
    form.post(route('admin.kesiswaan.alumni.simpan'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        }
    });
};

// Import Excel Logic
const fileInput = ref(null);
const importForm = useForm({
    file_excel: null
});

const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    if (!confirm('Anda yakin ingin meng-import data dari ' + file.name + '?')) {
        fileInput.value.value = '';
        return;
    }

    importForm.file_excel = file;
    importForm.post(route('admin.kesiswaan.alumni.import'), {
        preserveScroll: true,
        onSuccess: () => {
            fileInput.value.value = '';
        },
        onError: () => {
            fileInput.value.value = '';
            alert('Gagal memproses import. Pastikan format file sesuai template.');
        }
    });
};
</script>

<template>
    <Head title="Data Alumni" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-user-graduate text-indigo-500"></i>
                        Data Alumni
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Daftar siswa yang telah lulus dan penambahan data alumni lampau.
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3 mt-4 sm:mt-0">
                    <a :href="route('admin.kesiswaan.alumni.export', { search: search })" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-bold rounded-xl shadow-sm transition-colors flex items-center justify-center gap-2 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                        <i class="fas fa-file-export text-emerald-600"></i> Export Data
                    </a>
                    <a :href="route('admin.kesiswaan.alumni.template_import')" target="_blank" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-bold rounded-xl shadow-sm transition-colors flex items-center gap-2 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                        <i class="fas fa-file-excel text-emerald-600"></i> Download Template
                    </a>
                    <button @click="$refs.fileInput.click()" :disabled="importForm.processing" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-sm transition-colors flex items-center gap-2 disabled:opacity-50">
                        <i class="fas fa-upload"></i> {{ importForm.processing ? 'Mengupload...' : 'Import Excel' }}
                    </button>
                    <input type="file" ref="fileInput" accept=".xlsx, .xls" class="hidden" @change="handleFileSelect">
                    
                    <button @click="showModal = true" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-sm transition-colors flex items-center gap-2">
                        <i class="fas fa-plus"></i> Tambah Alumni Lampau
                    </button>
                </div>
            </div>

            <!-- Pesan Sukses/Error -->
            <div v-if="$page.props.flash.message" class="p-4 mb-4 text-sm text-emerald-800 rounded-lg bg-emerald-50 dark:bg-gray-800 dark:text-emerald-400 ring-1 ring-emerald-200 dark:ring-emerald-800" role="alert">
                <span class="font-medium">Berhasil!</span> {{ $page.props.flash.message }}
            </div>
            <div v-if="$page.props.flash.error || importForm.errors.file_excel" class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 ring-1 ring-red-200 dark:ring-red-800" role="alert">
                <span class="font-medium">Gagal!</span> {{ $page.props.flash.error || importForm.errors.file_excel }}
            </div>

            <!-- Toolbar & Filter -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <span class="text-sm text-gray-500">Tampilkan</span>
                    <select v-model="perPageInput" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="text-sm text-gray-500">baris</span>
                </div>
                
                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" v-model="searchInput" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Cari NISN / Nama / Angkatan...">
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                            <tr>
                                <th scope="col" class="px-6 py-3 w-16 text-center">No</th>
                                <th scope="col" class="px-6 py-3">NISN</th>
                                <th scope="col" class="px-6 py-3">Nama Lengkap</th>
                                <th scope="col" class="px-6 py-3 text-center">L/P</th>
                                <th scope="col" class="px-6 py-3">Tahun Angkatan</th>
                                <th scope="col" class="px-6 py-3">No. HP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(s, index) in alumni.data" :key="s.id" class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 text-center">{{ alumni.from + index }}</td>
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ s.nisn || '-' }}</td>
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ s.nama_lengkap }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-1 rounded-md text-xs font-bold" :class="s.jenis_kelamin == 'L' ? 'bg-blue-100 text-blue-700' : 'bg-pink-100 text-pink-700'">{{ s.jenis_kelamin }}</span>
                                </td>
                                <td class="px-6 py-4">{{ s.tahun_angkatan || '-' }}</td>
                                <td class="px-6 py-4">{{ s.no_hp_siswa || '-' }}</td>
                            </tr>
                            <tr v-if="alumni.data.length === 0">
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-3xl mb-3 text-gray-400 block"></i>
                                    Data alumni tidak ditemukan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div v-if="alumni.links && alumni.links.length > 3" class="p-4 border-t border-gray-100 dark:border-gray-700 flex justify-center">
                    <div class="flex flex-wrap gap-1">
                        <template v-for="(link, p) in alumni.links" :key="p">
                            <div v-if="link.url === null" class="px-3 py-2 text-sm text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg dark:text-gray-500 bg-gray-50 dark:bg-gray-800" v-html="link.label" />
                            <a v-else href="#" @click.prevent="router.get(link.url, { search: searchInput, per_page: perPageInput }, { preserveState: true })" 
                               class="px-3 py-2 text-sm border rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors" 
                               :class="link.active ? 'bg-indigo-50 border-indigo-500 text-indigo-600 font-bold dark:bg-indigo-900/50 dark:border-indigo-500 dark:text-indigo-400' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400'" 
                               v-html="link.label" />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Tambah Alumni -->
        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-blur-sm" @click="showModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <i class="fas fa-user-plus text-indigo-500"></i> Tambah Alumni Lampau
                        </h3>
                        <button @click="showModal = false" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    <form @submit.prevent="submitForm">
                        <div class="px-6 py-4 space-y-4">
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">NISN</label>
                                <input type="text" v-model="form.nisn" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Lengkap</label>
                                <input type="text" v-model="form.nama_lengkap" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Jenis Kelamin</label>
                                    <select v-model="form.jenis_kelamin" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        <option value="L">Laki-Laki (L)</option>
                                        <option value="P">Perempuan (P)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tahun Angkatan</label>
                                    <input type="text" v-model="form.tahun_angkatan" placeholder="Contoh: 2020/2021" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                                </div>
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">No. HP / WA</label>
                                <input type="text" v-model="form.no_hp_siswa" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Alamat Lengkap</label>
                                <textarea v-model="form.alamat" rows="2" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"></textarea>
                            </div>
                        </div>
                        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex justify-end gap-3 rounded-b-2xl">
                            <button type="button" @click="showModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                                Batal
                            </button>
                            <button type="submit" :disabled="form.processing" class="px-4 py-2 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300 dark:focus:ring-indigo-800 disabled:opacity-50">
                                {{ form.processing ? 'Menyimpan...' : 'Simpan Data Alumni' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
