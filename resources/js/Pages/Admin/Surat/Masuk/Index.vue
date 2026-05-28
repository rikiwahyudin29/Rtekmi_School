<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    surat: Object,
    staff: Array
});

// Form Tambah Surat Masuk
const form = useForm({
    nomor_surat: '',
    tanggal_surat: '',
    tanggal_diterima: '',
    pengirim: '',
    perihal: '',
    file_scan: null
});

// Form Disposisi
const formDisposisi = useForm({
    id_surat: '',
    id_penerima: '',
    instruksi: ''
});

const isModalOpen = ref(false);
const isModalDisposisiOpen = ref(false);
const activeSurat = ref(null);

const submit = () => {
    form.post(route('admin.surat.masuk.store'), {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
        }
    });
};

const openDisposisi = (surat) => {
    activeSurat.value = surat;
    formDisposisi.id_surat = surat.id;
    isModalDisposisiOpen.value = true;
};

const submitDisposisi = () => {
    formDisposisi.post(route('admin.surat.masuk.disposisi'), {
        onSuccess: () => {
            isModalDisposisiOpen.value = false;
            formDisposisi.reset();
        }
    });
};

const deleteSurat = (id) => {
    if (confirm('Yakin ingin menghapus arsip surat masuk ini beserta file scan-nya?')) {
        useForm({}).delete(route('admin.surat.masuk.destroy', id));
    }
};

const handleFile = (e) => {
    form.file_scan = e.target.files[0];
};

const selectedIds = ref([]);

const selectAll = computed({
    get: () => props.surat.data.length > 0 && selectedIds.value.length === props.surat.data.length,
    set: (val) => {
        if (val) {
            selectedIds.value = props.surat.data.map(i => i.id);
        } else {
            selectedIds.value = [];
        }
    }
});

const bulkDelete = () => {
    if (confirm(`Yakin ingin menghapus ${selectedIds.value.length} surat masuk yang dipilih beserta file scannya?`)) {
        router.post(route('admin.surat.masuk.bulk-destroy'), { ids: selectedIds.value }, {
            onSuccess: () => selectedIds.value = []
        });
    }
};
</script>

<template>
    <Head title="Data Surat Masuk" />
    <DashboardLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Data Surat Masuk</h2>
                    <p class="text-sm text-gray-500 mt-1">Kelola arsip surat masuk dan teruskan via WhatsApp (Disposisi).</p>
                </div>
                <div class="flex items-center gap-3">
                    <transition enter-active-class="transition duration-200" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-200" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                        <button v-if="selectedIds.length > 0" @click="bulkDelete" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2.5 px-4 rounded-xl shadow-md flex items-center gap-2">
                            <i class="fas fa-trash-alt"></i> Hapus Terpilih ({{ selectedIds.length }})
                        </button>
                    </transition>
                    <button @click="isModalOpen = true" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2.5 px-4 rounded-xl shadow-lg shadow-primary-600/30 flex items-center gap-2">
                        <i class="fas fa-plus"></i> Tambah Surat Masuk
                    </button>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="uppercase tracking-wider border-b-2 border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 font-bold">
                            <tr>
                                <th class="px-6 py-4 w-10">
                                    <input type="checkbox" v-model="selectAll" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                </th>
                                <th class="px-6 py-4">No. Surat</th>
                                <th class="px-6 py-4">Pengirim & Perihal</th>
                                <th class="px-6 py-4 text-center">Tgl Terima</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-center">File</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-gray-800 dark:text-gray-200 font-medium">
                            <tr v-for="s in surat.data" :key="s.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4">
                                    <input type="checkbox" v-model="selectedIds" :value="s.id" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                </td>
                                <td class="px-6 py-4 font-bold">{{ s.nomor_surat }}</td>
                                <td class="px-6 py-4">
                                    <span class="block font-bold text-gray-900 dark:text-white">{{ s.pengirim }}</span>
                                    <span class="block text-xs text-gray-500 mt-0.5">{{ s.perihal }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">{{ s.tanggal_diterima }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span v-if="s.status_disposisi === 'Belum Disposisi'" class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200">
                                        Menunggu
                                    </span>
                                    <span v-else class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                        <i class="fas fa-check mr-1"></i> Disposisi
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a v-if="s.file_scan" :href="'/uploads/surat_masuk/' + s.file_scan" target="_blank" class="text-blue-500 hover:text-blue-700 text-lg">
                                        <i class="fas fa-file-pdf text-red-500" v-if="s.file_scan.endsWith('.pdf')"></i>
                                        <i class="fas fa-file-image text-blue-500" v-else></i>
                                    </a>
                                    <span v-else class="text-gray-400 text-xs">-</span>
                                </td>
                                <td class="px-6 py-4 text-right flex justify-end gap-2">
                                    <button @click="openDisposisi(s)" class="px-3 py-1.5 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/40 rounded-lg transition-colors font-bold text-xs flex items-center gap-1.5">
                                        <i class="fab fa-whatsapp"></i> Disposisi
                                    </button>
                                    <button @click="deleteSurat(s.id)" class="px-3 py-1.5 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 rounded-lg transition-colors font-bold text-xs flex items-center gap-1.5">
                                        <i class="fas fa-trash-alt"></i> Hapus
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!surat.data.length">
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-inbox text-4xl mb-3 text-gray-300"></i>
                                        <p>Belum ada surat masuk tercatat.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex justify-between items-center" v-if="surat.links && surat.data.length > 0">
                    <p class="text-sm text-gray-500">
                        Menampilkan <span class="font-bold text-gray-900 dark:text-white">{{ surat.from }}</span> sampai <span class="font-bold text-gray-900 dark:text-white">{{ surat.to }}</span> dari <span class="font-bold text-gray-900 dark:text-white">{{ surat.total }}</span> arsip
                    </p>
                    <Pagination :links="surat.links" />
                </div>
            </div>
        </div>

        <!-- Modal Tambah Surat Masuk -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-3xl w-full max-w-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Tambah Data Surat Masuk</h3>
                    <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="p-6 overflow-y-auto">
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5">Nomor Surat</label>
                                <input v-model="form.nomor_surat" type="text" class="w-full bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 outline-none" required>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5">Pengirim Instansi/Personal</label>
                                <input v-model="form.pengirim" type="text" class="w-full bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 outline-none" required>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5">Tanggal Surat</label>
                                <input v-model="form.tanggal_surat" type="date" class="w-full bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 outline-none" required>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5">Tanggal Diterima</label>
                                <input v-model="form.tanggal_diterima" type="date" class="w-full bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 outline-none" required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5">Perihal / Hal</label>
                            <input v-model="form.perihal" type="text" class="w-full bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 outline-none" required>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5">Upload File Scan (PDF/JPG/PNG) - Opsional</label>
                            <input @change="handleFile" type="file" accept=".pdf,.jpg,.jpeg,.png" class="w-full bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 outline-none file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <button type="button" @click="isModalOpen = false" class="px-5 py-2.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl font-bold">Batal</button>
                            <button type="submit" :disabled="form.processing" class="px-5 py-2.5 bg-primary-600 text-white rounded-xl font-bold hover:bg-primary-700 disabled:opacity-50">
                                <i class="fas fa-save mr-1.5"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Disposisi -->
        <div v-if="isModalDisposisiOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Disposisi & Teruskan (WA)</h3>
                        <button @click="isModalDisposisiOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="text-xs text-gray-500 p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <strong>Surat:</strong> {{ activeSurat?.nomor_surat }}<br>
                        <strong>Perihal:</strong> {{ activeSurat?.perihal }}
                    </div>
                </div>
                
                <div class="p-6">
                    <form @submit.prevent="submitDisposisi" class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5">Diteruskan Kepada (Penerima)</label>
                            <select v-model="formDisposisi.id_penerima" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 outline-none font-medium" required>
                                <option value="" disabled>-- Pilih Guru/Staff --</option>
                                <option v-for="st in staff" :key="st.id" :value="st.id">{{ st.nama_lengkap }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5">Instruksi / Catatan Disposisi</label>
                            <textarea v-model="formDisposisi.instruksi" rows="4" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 outline-none" placeholder="Tolong segera ditindaklanjuti..." required></textarea>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                            <button type="button" @click="isModalDisposisiOpen = false" class="px-5 py-2.5 bg-white border border-gray-200 rounded-xl font-bold">Batal</button>
                            <button type="submit" :disabled="formDisposisi.processing" class="px-5 py-2.5 bg-green-500 text-white rounded-xl font-bold hover:bg-green-600 shadow-lg shadow-green-500/30 flex items-center gap-2">
                                <i class="fab fa-whatsapp"></i> Disposisikan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
