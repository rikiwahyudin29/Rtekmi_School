<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    ekskul: Array,
    list_guru: Array
});

const isModalOpen = ref(false);
const isEdit = ref(false);

const form = useForm({
    id: null,
    nama_ekskul: '',
    kategori: 'Pilihan',
    hari: '',
    jam: '',
    kuota: '',
    visi_misi: '',
    status: 'Aktif',
    logo: null
});

const openModal = (e = null) => {
    isEdit.value = !!e;
    if (e) {
        form.id = e.id;
        form.nama_ekskul = e.nama_ekskul;
        form.kategori = e.kategori;
        form.hari = e.hari;
        form.jam = e.jam;
        form.kuota = e.kuota || '';
        form.visi_misi = e.visi_misi || '';
        form.status = e.status;
        form.logo = null;
    } else {
        form.reset();
    }
    isModalOpen.value = true;
};

const submitEkskul = () => {
    if (isEdit.value) {
        form.post(route('admin.ekskul.update'), {
            preserveScroll: true,
            onSuccess: () => isModalOpen.value = false
        });
    } else {
        form.post(route('admin.ekskul.simpan'), {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            }
        });
    }
};

const deleteEkskul = (id, nama) => {
    if (confirm(`Hapus unit ekskul ${nama}? Data anggota dan nilai juga akan terhapus!`)) {
        router.delete(route('admin.ekskul.delete', id), {
            preserveScroll: true
        });
    }
};

// Modal Pembina
const isModalPembinaOpen = ref(false);
const pembinaForm = useForm({
    ekskul_id: null,
    guru_id: ''
});
const activeEkskulName = ref('');

const openPembinaModal = (e) => {
    pembinaForm.ekskul_id = e.id;
    pembinaForm.guru_id = '';
    activeEkskulName.value = e.nama_ekskul;
    isModalPembinaOpen.value = true;
};

const submitPembina = () => {
    pembinaForm.post(route('admin.ekskul.tambah_pembina'), {
        preserveScroll: true,
        onSuccess: () => isModalPembinaOpen.value = false
    });
};

const hapusPembina = (id, nama) => {
    if(confirm(`Cabut tugas pembina dari ${nama}?`)) {
        router.delete(route('admin.ekskul.hapus_pembina', id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Manajemen Ekstrakurikuler" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-volleyball-ball text-indigo-500"></i>
                        Manajemen Ekstrakurikuler
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Kelola data unit ekstrakurikuler dan tugaskan guru pembina.
                    </p>
                </div>
                <button @click="openModal()" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 font-bold shadow-sm flex items-center gap-2 transition-all">
                    <i class="fas fa-plus"></i> Tambah Ekskul
                </button>
            </div>

            <div v-if="$page.props.flash?.message" class="bg-green-50 text-green-800 p-4 rounded-2xl shadow-sm flex items-center gap-3">
                <i class="fas fa-check-circle"></i> {{ $page.props.flash.message }}
            </div>
            <div v-if="$page.props.flash?.error" class="bg-red-50 text-red-800 p-4 rounded-2xl shadow-sm flex items-center gap-3">
                <i class="fas fa-exclamation-circle"></i> {{ $page.props.flash.error }}
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <!-- Card Ekskul -->
                <div v-for="e in ekskul" :key="e.id" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex flex-col md:flex-row gap-6 hover:border-indigo-300 transition-colors">
                    <div class="w-24 h-24 rounded-2xl bg-gray-50 dark:bg-gray-700 p-2 shadow-inner border border-gray-100 shrink-0 mx-auto md:mx-0 relative">
                        <img :src="`/uploads/ekskul/${e.logo || 'default_ekskul.png'}`" @error="$event.target.src='https://ui-avatars.com/api/?background=random&name='+e.nama_ekskul" class="w-full h-full object-contain">
                        <div v-if="e.status !== 'Aktif'" class="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">Nonaktif</div>
                    </div>
                    
                    <div class="flex-1 flex flex-col">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-[10px] font-bold px-2 py-1 rounded-md uppercase tracking-wider" :class="e.kategori === 'Wajib' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700'">
                                    {{ e.kategori }}
                                </span>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ e.nama_ekskul }}</h3>
                                <p class="text-xs text-gray-500 mt-1"><i class="far fa-calendar-alt w-4"></i> {{ e.hari }} | {{ e.jam }}</p>
                            </div>
                            <div class="flex gap-2">
                                <button @click="openModal(e)" class="w-8 h-8 rounded-lg bg-gray-100 text-gray-600 hover:bg-indigo-100 hover:text-indigo-600 flex items-center justify-center transition-colors">
                                    <i class="fas fa-pen text-sm"></i>
                                </button>
                                <button @click="deleteEkskul(e.id, e.nama_ekskul)" class="w-8 h-8 rounded-lg bg-gray-100 text-gray-600 hover:bg-red-100 hover:text-red-600 flex items-center justify-center transition-colors">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-xs text-gray-500 mb-1 font-bold">Anggota Aktif</div>
                                <div class="font-black text-lg text-gray-900 dark:text-white">{{ e.jml_anggota }} <span class="text-xs font-normal text-gray-500">/ {{ e.kuota || '∞' }}</span></div>
                            </div>
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <div class="text-xs text-gray-500 font-bold">Pembina</div>
                                    <button @click="openPembinaModal(e)" class="text-xs text-indigo-600 hover:underline"><i class="fas fa-plus"></i> Tambah</button>
                                </div>
                                <div v-if="e.pembina.length > 0" class="flex flex-col gap-1">
                                    <div v-for="p in e.pembina" :key="p.id" class="flex items-center justify-between bg-indigo-50 dark:bg-indigo-900/30 px-2 py-1 rounded border border-indigo-100 dark:border-indigo-800">
                                        <div class="text-xs font-bold text-indigo-700 dark:text-indigo-400 line-clamp-1 flex-1">{{ p.nama_guru }}</div>
                                        <button @click="hapusPembina(p.id, p.nama_guru)" class="text-red-500 hover:text-red-700 ml-2" title="Hapus Pembina">
                                            <i class="fas fa-times text-[10px]"></i>
                                        </button>
                                    </div>
                                </div>
                                <div v-else class="text-xs text-red-500 italic">Belum ada pembina</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Form Ekskul -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white">
                        {{ isEdit ? 'Edit Ekstrakurikuler' : 'Tambah Ekstrakurikuler' }}
                    </h3>
                    <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                </div>
                <form @submit.prevent="submitEkskul" class="p-6 max-h-[80vh] overflow-y-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Nama Ekskul</label>
                            <input type="text" v-model="form.nama_ekskul" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Kategori</label>
                            <select v-model="form.kategori" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                <option value="Wajib">Wajib (Pramuka, dll)</option>
                                <option value="Pilihan">Pilihan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Status</label>
                            <select v-model="form.status" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                <option value="Aktif">Aktif</option>
                                <option value="Nonaktif">Nonaktif</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Hari Pelaksanaan</label>
                            <input type="text" v-model="form.hari" placeholder="Contoh: Sabtu" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Jam Pelaksanaan</label>
                            <input type="text" v-model="form.jam" placeholder="Contoh: 14.00 - 16.00" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Kuota Anggota (Kosongkan jika tak terbatas)</label>
                            <input type="number" v-model="form.kuota" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Visi & Misi / Deskripsi</label>
                            <textarea v-model="form.visi_misi" rows="3" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Logo Ekskul (Opsional)</label>
                            <input type="file" @input="form.logo = $event.target.files[0]" accept="image/*" class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 cursor-pointer">
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="isModalOpen = false" class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold flex items-center gap-2 disabled:opacity-50">
                            <i v-if="form.processing" class="fas fa-spinner fa-spin"></i>
                            <i v-else class="fas fa-save"></i>
                            Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Tambah Pembina -->
        <div v-if="isModalPembinaOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white">Tugaskan Pembina</h3>
                    <button @click="isModalPembinaOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                </div>
                <form @submit.prevent="submitPembina" class="p-6">
                    <div class="mb-4">
                        <div class="text-xs font-bold text-gray-500 mb-1">Unit Ekstrakurikuler</div>
                        <div class="font-bold text-lg text-indigo-600">{{ activeEkskulName }}</div>
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Pilih Guru</label>
                        <select v-model="pembinaForm.guru_id" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" required>
                            <option value="">-- Pilih Guru Pembina --</option>
                            <option v-for="g in list_guru" :key="g.id" :value="g.id">{{ g.nama_lengkap }}</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="isModalPembinaOpen = false" class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold">Batal</button>
                        <button type="submit" :disabled="pembinaForm.processing" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold flex items-center gap-2">
                            Simpan Pembina
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </DashboardLayout>
</template>
