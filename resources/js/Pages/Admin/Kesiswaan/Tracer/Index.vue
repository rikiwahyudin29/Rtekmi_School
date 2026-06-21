<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    pertanyaan: Array,
    responden: Array,
    stats: Array
});

const activeTab = ref('laporan');

// Form Tambah Pertanyaan
const formPertanyaan = useForm({
    pertanyaan: '',
    tipe: 'text',
    opsi_jawaban: '',
    is_required: 1
});

const submitPertanyaan = () => {
    formPertanyaan.post(route('admin.kesiswaan.tracer.pertanyaan.simpan'), {
        preserveScroll: true,
        onSuccess: () => {
            formPertanyaan.reset();
        }
    });
};

const hapusPertanyaan = (id) => {
    if(confirm('Yakin ingin menghapus pertanyaan ini beserta semua jawaban terkait?')) {
        router.delete(route('admin.kesiswaan.tracer.pertanyaan.hapus', id), {
            preserveScroll: true
        });
    }
};

const resetResponden = (id) => {
    if(confirm('Yakin ingin me-reset data alumni ini? Semua jawabannya akan dihapus dan ia bisa mengisi ulang kuesioner.')) {
        router.delete(route('admin.kesiswaan.tracer.reset', id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Tracer Study" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-search-location text-emerald-500"></i>
                        Tracer Study & Penelusuran Alumni
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Kelola kuesioner dan pantau laporan alumni (Kuliah, Kerja, Wirausaha, dll).
                    </p>
                </div>
                <div class="flex items-center gap-3 mt-4 sm:mt-0">
                    <a :href="route('admin.kesiswaan.tracer.export')" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-bold rounded-xl shadow-sm transition-colors flex items-center gap-2 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                        <i class="fas fa-file-export text-emerald-600"></i> Export Laporan
                    </a>
                    <a :href="route('tracer.index')" target="_blank" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-bold rounded-xl shadow-sm transition-colors flex items-center gap-2 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                        <i class="fas fa-external-link-alt text-emerald-600"></i> Buka Form Publik
                    </a>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-500">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-gray-800 dark:text-white">{{ responden.length }}</div>
                        <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Responden</div>
                    </div>
                </div>
                <div v-for="s in stats" :key="s.status_kegiatan" class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-xl" 
                         :class="s.status_kegiatan == 'Kuliah' ? 'bg-indigo-50 text-indigo-500 dark:bg-indigo-900/30' : 
                                (s.status_kegiatan == 'Bekerja' ? 'bg-emerald-50 text-emerald-500 dark:bg-emerald-900/30' : 
                                'bg-amber-50 text-amber-500 dark:bg-amber-900/30')">
                        <i class="fas" :class="s.status_kegiatan == 'Kuliah' ? 'fa-university' : (s.status_kegiatan == 'Bekerja' ? 'fa-briefcase' : 'fa-store')"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-gray-800 dark:text-white">{{ s.total }}</div>
                        <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ s.status_kegiatan }}</div>
                    </div>
                </div>
            </div>

            <!-- Pesan Sukses/Error -->
            <div v-if="$page.props.flash.message" class="p-4 mb-4 text-sm text-emerald-800 rounded-lg bg-emerald-50 dark:bg-gray-800 dark:text-emerald-400 ring-1 ring-emerald-200 dark:ring-emerald-800" role="alert">
                <span class="font-medium">Berhasil!</span> {{ $page.props.flash.message }}
            </div>

            <!-- Tabs Navigation -->
            <div class="border-b border-gray-200 dark:border-gray-700">
                <ul class="flex flex-wrap -mb-px text-sm font-medium text-center text-gray-500 dark:text-gray-400">
                    <li class="mr-2">
                        <button @click="activeTab = 'laporan'" class="inline-flex items-center justify-center p-4 border-b-2 rounded-t-lg group" :class="activeTab === 'laporan' ? 'text-emerald-600 border-emerald-600 dark:text-emerald-500 dark:border-emerald-500' : 'border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300'">
                            <i class="fas fa-chart-bar mr-2"></i> Laporan Responden
                        </button>
                    </li>
                    <li class="mr-2">
                        <button @click="activeTab = 'kuesioner'" class="inline-flex items-center justify-center p-4 border-b-2 rounded-t-lg group" :class="activeTab === 'kuesioner' ? 'text-emerald-600 border-emerald-600 dark:text-emerald-500 dark:border-emerald-500' : 'border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300'">
                            <i class="fas fa-list-check mr-2"></i> Kelola Kuesioner
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Tab Content: Laporan -->
            <div v-if="activeTab === 'laporan'" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden animate-fade-in">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                            <tr>
                                <th scope="col" class="px-6 py-3 w-16 text-center">No</th>
                                <th scope="col" class="px-6 py-3">Nama Alumni / NISN</th>
                                <th scope="col" class="px-6 py-3">Angkatan</th>
                                <th scope="col" class="px-6 py-3">Status Saat Ini</th>
                                <th scope="col" class="px-6 py-3">Nama Instansi / Kampus</th>
                                <th scope="col" class="px-6 py-3">Tgl Isi</th>
                                <th scope="col" class="px-6 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(r, i) in responden" :key="r.id" class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 text-center">{{ i + 1 }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900 dark:text-white">{{ r.nama_lengkap }}</div>
                                    <div class="text-xs">{{ r.nisn }}</div>
                                </td>
                                <td class="px-6 py-4">{{ r.tahun_angkatan }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded-md text-xs font-bold" 
                                          :class="r.status_kegiatan == 'Kuliah' ? 'bg-indigo-100 text-indigo-700' : (r.status_kegiatan == 'Bekerja' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700')">
                                        {{ r.status_kegiatan }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">{{ r.nama_instansi || '-' }}</td>
                                <td class="px-6 py-4 text-xs">{{ new Date(r.tanggal_isi).toLocaleDateString('id-ID') }}</td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <Link :href="route('admin.kesiswaan.tracer.detail', r.id)" class="px-3 py-1.5 text-xs font-bold text-white bg-indigo-500 rounded hover:bg-indigo-600 transition-colors">
                                        <i class="fas fa-eye"></i> Detail
                                    </Link>
                                    <button @click="resetResponden(r.id)" class="px-3 py-1.5 text-xs font-bold text-white bg-red-500 rounded hover:bg-red-600 transition-colors">
                                        <i class="fas fa-undo"></i> Reset
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="responden.length === 0">
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-3xl mb-3 text-gray-400 block"></i>
                                    Belum ada data tracer study yang masuk.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab Content: Kuesioner -->
            <div v-if="activeTab === 'kuesioner'" class="grid grid-cols-1 lg:grid-cols-3 gap-6 animate-fade-in">
                <!-- List Kuesioner -->
                <div class="lg:col-span-2 space-y-4">
                    <div v-for="(p, i) in pertanyaan" :key="p.id" class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700 flex gap-4">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 flex items-center justify-center font-black flex-shrink-0">
                            {{ i + 1 }}
                        </div>
                        <div class="flex-grow">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h4 class="font-bold text-gray-900 dark:text-white text-lg">{{ p.pertanyaan }}</h4>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                            {{ p.tipe === 'text' ? 'Teks Singkat' : (p.tipe === 'textarea' ? 'Teks Panjang' : (p.tipe === 'radio' ? 'Pilihan Ganda' : 'Checkbox')) }}
                                        </span>
                                        <span v-if="p.is_required" class="text-xs text-red-500 font-bold">*Wajib</span>
                                    </div>
                                </div>
                                <button @click="hapusPertanyaan(p.id)" class="text-red-400 hover:text-red-600 p-2">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            
                            <!-- Preview Input -->
                            <div class="mt-3 bg-gray-50 dark:bg-gray-900/50 p-3 rounded-lg border border-dashed border-gray-300 dark:border-gray-700">
                                <input v-if="p.tipe === 'text'" type="text" disabled class="w-full bg-transparent border-b border-gray-300 dark:border-gray-600 focus:border-emerald-500 px-0 py-1 text-sm text-gray-400" placeholder="Jawaban singkat...">
                                <textarea v-if="p.tipe === 'textarea'" disabled rows="2" class="w-full bg-transparent border-b border-gray-300 dark:border-gray-600 focus:border-emerald-500 px-0 py-1 text-sm text-gray-400" placeholder="Jawaban panjang..."></textarea>
                                
                                <div v-if="p.tipe === 'radio'" class="space-y-1 mt-2">
                                    <div v-for="opt in (p.opsi_jawaban ? p.opsi_jawaban.split(',') : [])" class="flex items-center gap-2 text-sm text-gray-500">
                                        <input type="radio" disabled class="w-4 h-4 text-emerald-600 border-gray-300 focus:ring-emerald-500">
                                        <span>{{ opt.trim() }}</span>
                                    </div>
                                </div>
                                
                                <div v-if="p.tipe === 'checkbox'" class="space-y-1 mt-2">
                                    <div v-for="opt in (p.opsi_jawaban ? p.opsi_jawaban.split(',') : [])" class="flex items-center gap-2 text-sm text-gray-500">
                                        <input type="checkbox" disabled class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                                        <span>{{ opt.trim() }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="pertanyaan.length === 0" class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-sm border border-gray-100 dark:border-gray-700 text-center">
                        <i class="fas fa-clipboard-list text-4xl text-gray-300 mb-3 block"></i>
                        <h4 class="font-bold text-gray-500">Belum ada pertanyaan</h4>
                        <p class="text-sm text-gray-400">Silakan tambahkan pertanyaan kuesioner pada panel di samping.</p>
                    </div>
                </div>

                <!-- Form Tambah -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700 sticky top-24">
                        <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-4 border-b pb-2 dark:border-gray-700">Tambah Pertanyaan</h3>
                        <form @submit.prevent="submitPertanyaan" class="space-y-4">
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pertanyaan</label>
                                <textarea v-model="formPertanyaan.pertanyaan" rows="2" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required placeholder="Contoh: Berapa gaji pertama anda?"></textarea>
                            </div>
                            
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tipe Jawaban</label>
                                <select v-model="formPertanyaan.tipe" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="text">Teks Singkat</option>
                                    <option value="textarea">Teks Panjang</option>
                                    <option value="radio">Pilihan Ganda (Satu Jawaban)</option>
                                    <option value="checkbox">Checkbox (Banyak Jawaban)</option>
                                </select>
                            </div>

                            <div v-if="['radio', 'checkbox'].includes(formPertanyaan.tipe)" class="animate-fade-in">
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Opsi Jawaban</label>
                                <textarea v-model="formPertanyaan.opsi_jawaban" rows="2" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Pisahkan dengan koma. Contoh: Kurang dari 1 Juta, 1 - 3 Juta, Diatas 3 Juta" required></textarea>
                                <p class="text-xs text-gray-500 mt-1">Pisahkan tiap opsi dengan tanda koma (,)</p>
                            </div>

                            <div>
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" v-model="formPertanyaan.is_required" :true-value="1" :false-value="0" class="w-4 h-4 text-emerald-600 bg-gray-100 border-gray-300 rounded focus:ring-emerald-500 dark:focus:ring-emerald-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <span class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Wajib Diisi</span>
                                </label>
                            </div>

                            <button type="submit" :disabled="formPertanyaan.processing" class="w-full px-4 py-2 text-sm font-bold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 focus:ring-4 focus:ring-emerald-300 dark:focus:ring-emerald-800 disabled:opacity-50 transition-colors">
                                <i class="fas fa-plus mr-1"></i> Simpan Pertanyaan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
