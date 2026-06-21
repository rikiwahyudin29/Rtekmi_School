<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    konseling: Array,
    siswa: Array
});

const isModalOpen = ref(false);
const isEditing = ref(false);
const filterKelas = ref('');

const uniqueKelas = computed(() => {
    const kelasMap = new Map();
    props.siswa.forEach(s => {
        if (s.kelas) {
            kelasMap.set(s.kelas.id, s.kelas.nama_kelas);
        }
    });
    return Array.from(kelasMap, ([id, nama_kelas]) => ({ id, nama_kelas })).sort((a, b) => a.nama_kelas.localeCompare(b.nama_kelas));
});

const filteredSiswa = computed(() => {
    if (!filterKelas.value) return props.siswa;
    return props.siswa.filter(s => s.kelas_id === filterKelas.value);
});

const form = useForm({
    id: null,
    siswa_id: '',
    jenis_konseling: 'Pribadi',
    tanggal_konseling: new Date().toISOString().split('T')[0],
    topik: '',
    hasil_konseling: '',
    tindak_lanjut: '',
    status: 'Selesai'
});

const openModal = (data = null) => {
    form.reset();
    if (data) {
        isEditing.value = true;
        form.id = data.id;
        form.siswa_id = data.siswa_id;
        form.jenis_konseling = data.jenis_konseling;
        form.tanggal_konseling = data.tanggal_konseling;
        form.topik = data.topik;
        form.hasil_konseling = data.hasil_konseling || '';
        form.tindak_lanjut = data.tindak_lanjut || '';
        form.status = data.status;
    } else {
        isEditing.value = false;
        form.tanggal_konseling = new Date().toISOString().split('T')[0];
    }
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('bk.konseling.update', form.id), {
            onSuccess: () => closeModal()
        });
    } else {
        form.post(route('bk.konseling.store'), {
            onSuccess: () => closeModal()
        });
    }
};

const deleteData = (id) => {
    if (confirm('Yakin ingin menghapus catatan konseling ini?')) {
        router.delete(route('bk.konseling.destroy', id));
    }
};
</script>

<template>
    <Head title="Catatan Konseling" />

    <DashboardLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="bg-gradient-to-br from-indigo-700 via-purple-800 to-violet-900 rounded-3xl p-8 shadow-2xl relative overflow-hidden flex flex-col md:flex-row items-center justify-between text-white">
                    <div class="absolute -top-24 -right-24 w-64 h-64 bg-white/10 rounded-full blur-3xl mix-blend-screen"></div>
                    <div class="relative z-10 flex-1">
                        <div class="flex items-center gap-3 mb-2.5">
                            <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-[10px] font-black text-white uppercase tracking-wider border border-white/20">Bimbingan Konseling</span>
                        </div>
                        <h2 class="text-3xl font-black tracking-tight mb-2">Catatan Konseling Siswa</h2>
                        <p class="text-indigo-200/90 font-medium text-sm">Dokumentasi hasil bimbingan pribadi, sosial, belajar, dan karir.</p>
                    </div>
                    <div class="relative z-10 mt-6 md:mt-0">
                        <button @click="openModal()" class="px-6 py-3.5 bg-white text-indigo-700 font-black rounded-2xl shadow-xl shadow-indigo-900/20 hover:scale-105 transition-all flex items-center">
                            <i class="fas fa-edit mr-2 text-lg"></i> Tulis Catatan Baru
                        </button>
                    </div>
                </div>

                <div v-if="$page.props.flash?.message" class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-center text-emerald-800 shadow-sm animate-fade-in-down">
                    <i class="fas fa-check-circle text-emerald-600 mr-3 text-xl"></i>
                    <p class="font-bold">{{ $page.props.flash.message }}</p>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col min-h-[500px]">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <h3 class="font-black text-gray-800 text-lg">Riwayat Sesi Konseling</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-500 uppercase bg-white border-b border-gray-100 font-bold sticky top-0">
                                <tr>
                                    <th class="px-6 py-4">Tanggal</th>
                                    <th class="px-6 py-4">Siswa</th>
                                    <th class="px-6 py-4">Topik & Jenis</th>
                                    <th class="px-6 py-4">Konselor (Guru)</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                    <th class="px-6 py-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="k in konseling" :key="k.id" class="hover:bg-gray-50/80 transition-colors">
                                    <td class="px-6 py-4 font-bold text-gray-900 whitespace-nowrap">{{ new Date(k.tanggal_konseling).toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'}) }}</td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">{{ k.siswa?.nama_lengkap }}</div>
                                        <div class="text-[10px] uppercase tracking-widest text-gray-500 mt-0.5">{{ k.siswa?.kelas?.nama_kelas || '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-black text-indigo-700">{{ k.topik }}</div>
                                        <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-[10px] font-bold uppercase mt-1 inline-block">{{ k.jenis_konseling }}</span>
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-600 text-xs">{{ k.guru?.nama_lengkap }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span :class="[
                                            'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider',
                                            k.status === 'Selesai' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'
                                        ]">
                                            {{ k.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button @click="openModal(k)" class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-colors flex items-center justify-center">
                                                <i class="fas fa-search-plus"></i>
                                            </button>
                                            <button @click="deleteData(k.id)" class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-colors flex items-center justify-center">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="konseling.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500 font-medium">Belum ada catatan konseling.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="closeModal"></div>
            <div class="bg-white rounded-3xl w-full max-w-3xl relative z-10 overflow-hidden shadow-2xl max-h-[90vh] flex flex-col">
                <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center shrink-0">
                    <div>
                        <h3 class="text-xl font-black text-gray-900"><i class="fas fa-clipboard-list text-indigo-600 mr-2"></i> {{ isEditing ? 'Detail & Edit' : 'Tulis' }} Catatan Konseling</h3>
                        <p class="text-xs text-gray-500 font-medium mt-1">Formulir Bimbingan & Konseling Siswa</p>
                    </div>
                    <button @click="closeModal" class="text-gray-400 hover:text-rose-500"><i class="fas fa-times text-xl"></i></button>
                </div>
                <div class="p-6 space-y-5 overflow-y-auto grow custom-scrollbar">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="flex gap-3">
                            <div class="w-1/3">
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-2 ml-1">Filter Kelas</label>
                                <select v-model="filterKelas" @change="form.siswa_id = ''" :disabled="isEditing" class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 font-medium disabled:bg-gray-100">
                                    <option value="">-- Semua Kelas --</option>
                                    <option v-for="k in uniqueKelas" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                                </select>
                            </div>
                            <div class="w-2/3">
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-2 ml-1">Siswa</label>
                                <select v-model="form.siswa_id" :disabled="isEditing" class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 font-medium disabled:bg-gray-100" :class="{'opacity-50': filteredSiswa.length === 0 && !isEditing}">
                                    <option value="">-- Pilih Siswa --</option>
                                    <option v-for="s in filteredSiswa" :key="s.id" :value="s.id">{{ s.nama_lengkap }} {{ s.kelas ? '(' + s.kelas.nama_kelas + ')' : '' }}</option>
                                </select>
                                <div v-if="form.errors.siswa_id" class="text-rose-500 text-xs mt-1 ml-1 font-bold">{{ form.errors.siswa_id }}</div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-2 ml-1">Tanggal Konseling</label>
                            <input type="date" v-model="form.tanggal_konseling" class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 font-bold">
                            <div v-if="form.errors.tanggal_konseling" class="text-rose-500 text-xs mt-1 ml-1 font-bold">{{ form.errors.tanggal_konseling }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-2 ml-1">Jenis Layanan BK</label>
                            <select v-model="form.jenis_konseling" class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 font-bold text-indigo-700">
                                <option value="Pribadi">Bimbingan Pribadi</option>
                                <option value="Sosial">Bimbingan Sosial</option>
                                <option value="Belajar">Bimbingan Belajar</option>
                                <option value="Karir">Bimbingan Karir</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-2 ml-1">Status Penanganan</label>
                            <select v-model="form.status" class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 font-bold" :class="form.status === 'Selesai' ? 'text-emerald-600' : 'text-amber-600'">
                                <option value="Selesai">Selesai (Closed)</option>
                                <option value="Follow-Up">Butuh Tindak Lanjut (Follow-Up)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2 ml-1">Topik / Judul Permasalahan</label>
                        <input type="text" v-model="form.topik" class="w-full rounded-xl border-gray-200 focus:ring-indigo-500 font-bold text-base bg-indigo-50/50" placeholder="Contoh: Kesulitan mengatur waktu belajar">
                        <div v-if="form.errors.topik" class="text-rose-500 text-xs mt-1 ml-1 font-bold">{{ form.errors.topik }}</div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2 ml-1">Hasil Konseling (Deskripsi)</label>
                        <textarea v-model="form.hasil_konseling" rows="4" class="w-full rounded-xl border-gray-200 focus:ring-indigo-500 text-sm leading-relaxed" placeholder="Tuliskan uraian hasil pembicaraan dengan siswa..."></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2 ml-1">Saran / Tindak Lanjut</label>
                        <textarea v-model="form.tindak_lanjut" rows="3" class="w-full rounded-xl border-gray-200 focus:ring-indigo-500 text-sm leading-relaxed bg-amber-50/30" placeholder="Apa langkah selanjutnya yang disepakati?"></textarea>
                    </div>
                </div>
                <div class="p-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3 shrink-0">
                    <button @click="closeModal" class="px-5 py-2.5 rounded-xl text-sm font-bold text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 transition-colors">Batal</button>
                    <button @click="submitForm" class="px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-600/20 disabled:opacity-50 transition-colors flex items-center" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Catatan' }} <i class="fas fa-save ml-2" v-if="!form.processing"></i>
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #e5e7eb;
    border-radius: 10px;
}
</style>
