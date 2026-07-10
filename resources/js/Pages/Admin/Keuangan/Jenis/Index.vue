<script setup>
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    jenis: Array,
    pos: Array,
    tahun: Array,
    kelas: Array,
    jurusan: Array,
});

const isModalOpen = ref(false);
const isEditMode = ref(false);

const form = useForm({
    id: null,
    id_pos_bayar: '',
    id_tahun_ajaran: '',
    tipe_bayar: 'BULANAN',
    nominal_default: '',
    nominal_putri_default: '',
    is_per_jurusan: false,
    is_beda_gender: false,
    nominal_jurusan: {},
    nominal_jurusan_putri: {},
});

const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(number);
};

const handleNominalInput = (e, targetField = 'nominal_default') => {
    // Remove non-numeric characters
    let value = e.target.value.replace(/\D/g, '');
    if (value) {
        // Format with thousand separator
        e.target.value = new Intl.NumberFormat('id-ID').format(value);
        form[targetField] = e.target.value;
    } else {
        form[targetField] = '';
    }
};

const handleNominalJurusanInput = (e, id_jur, isPutri = false) => {
    let value = e.target.value.replace(/\D/g, '');
    if (value) {
        e.target.value = new Intl.NumberFormat('id-ID').format(value);
        if (isPutri) {
            form.nominal_jurusan_putri[id_jur] = e.target.value;
        } else {
            form.nominal_jurusan[id_jur] = e.target.value;
        }
    } else {
        if (isPutri) {
            form.nominal_jurusan_putri[id_jur] = '';
        } else {
            form.nominal_jurusan[id_jur] = '';
        }
    }
};

const openModal = () => {
    isEditMode.value = false;
    form.reset();
    form.tipe_bayar = 'BULANAN';
    form.is_per_jurusan = false;
    form.is_beda_gender = false;
    form.nominal_jurusan = {};
    form.nominal_jurusan_putri = {};
    props.jurusan.forEach(j => {
        form.nominal_jurusan[j.id] = '';
        form.nominal_jurusan_putri[j.id] = '';
    });
    isModalOpen.value = true;
};

const editData = (data) => {
    isEditMode.value = true;
    form.id = data.id;
    form.id_pos_bayar = data.id_pos_bayar;
    form.id_tahun_ajaran = data.id_tahun_ajaran;
    form.tipe_bayar = data.tipe_bayar;
    form.is_per_jurusan = data.is_per_jurusan == 1;
    form.is_beda_gender = data.is_beda_gender == 1;
    form.nominal_default = new Intl.NumberFormat('id-ID').format(data.nominal_default);
    form.nominal_putri_default = new Intl.NumberFormat('id-ID').format(data.nominal_putri_default || 0);
    
    form.nominal_jurusan = {};
    form.nominal_jurusan_putri = {};
    if (form.is_per_jurusan && data.jenis_bayar_jurusan) {
        props.jurusan.forEach(j => {
            const found = data.jenis_bayar_jurusan.find(x => x.id_jurusan == j.id);
            form.nominal_jurusan[j.id] = found ? new Intl.NumberFormat('id-ID').format(found.nominal) : '';
            form.nominal_jurusan_putri[j.id] = found ? new Intl.NumberFormat('id-ID').format(found.nominal_putri || 0) : '';
        });
    } else {
        props.jurusan.forEach(j => {
            form.nominal_jurusan[j.id] = '';
            form.nominal_jurusan_putri[j.id] = '';
        });
    }
    isModalOpen.value = true;
};

const submit = () => {
    if (isEditMode.value) {
        form.put(`/admin/keuangan/jenis/${form.id}`, {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            }
        });
    } else {
        form.post('/admin/keuangan/jenis', {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            }
        });
    }
};

const hapus = (id) => {
    if (confirm('Yakin ingin menghapus Setting Jenis Pembayaran ini?')) {
        router.delete(`/admin/keuangan/jenis/${id}`);
    }
};
</script>

<template>
    <Head title="Setting Jenis Pembayaran" />

    <DashboardLayout>
        <div class="flex flex-col h-full">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
                
                <!-- Sticky Header -->
                <div class="sticky top-0 z-20 bg-[#f4f6f8] dark:bg-gray-900 pt-6 pb-4">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                        <div>
                            <h2 class="font-bold text-2xl text-gray-900 dark:text-white tracking-tight">Setting Jenis Pembayaran</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Atur tarif pembayaran per tahun ajaran dan tipe tagihannya.</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <button @click="openModal" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2.5 px-5 rounded-xl text-sm transition-all shadow-lg shadow-primary-900/20 flex items-center gap-2">
                                <i class="fas fa-plus"></i> Tambah Setting
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Flash Message -->
                <div v-if="$page.props.flash?.message" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-400 p-4 mb-6 rounded-2xl shadow-sm flex items-center gap-3">
                    <i class="fas fa-check-circle"></i> {{ $page.props.flash.message }}
                </div>
                <div v-if="$page.props.flash?.error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-400 p-4 mb-6 rounded-2xl shadow-sm flex items-center gap-3">
                    <i class="fas fa-exclamation-circle"></i> {{ $page.props.flash.error }}
                </div>

                <!-- Table Card -->
                <div class="bg-white dark:bg-gray-800 shadow-xl shadow-gray-200/50 dark:shadow-none rounded-3xl border border-gray-100 dark:border-gray-700 mb-6 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                                    <th class="px-6 py-5 font-bold w-16 text-center">No</th>
                                    <th class="px-6 py-5 font-bold">Pos & Tahun</th>
                                    <th class="px-6 py-5 font-bold text-center">Tipe Tagihan</th>
                                    <th class="px-6 py-5 font-bold text-right">Tarif (Rp)</th>
                                    <th class="px-6 py-5 font-bold text-center">Kelola Tagihan Siswa</th>
                                    <th class="px-6 py-5 font-bold text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-for="(j, index) in jenis" :key="j.id" class="hover:bg-gray-50/80 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-6 py-4 text-center text-sm font-medium text-gray-500">{{ index + 1 }}</td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-primary-600 dark:text-primary-400 text-base">
                                            {{ j.pos_bayar?.nama_pos || '-' }}
                                        </div>
                                        <div class="text-xs text-gray-500 font-medium mt-1">
                                            Tahun Ajaran: {{ j.tahun_ajaran?.tahun_ajaran || '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold"
                                              :class="j.tipe_bayar === 'BULANAN' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-purple-50 text-purple-700 border border-purple-200'">
                                            {{ j.tipe_bayar }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div v-if="j.is_per_jurusan" class="text-sm font-medium text-amber-600 dark:text-amber-400 cursor-help" :title="j.jenis_bayar_jurusan?.map(x => x.jurusan?.nama_jurusan + ': Rp ' + formatRupiah(x.nominal) + (j.is_beda_gender ? ' (P: Rp ' + formatRupiah(x.nominal_putri) + ')' : '')).join('\n')">
                                            <i class="fas fa-layer-group mr-1"></i> Beda per Jurusan <span v-if="j.is_beda_gender">& L/P</span>
                                        </div>
                                        <div v-else class="font-bold text-gray-900 dark:text-white">
                                            {{ formatRupiah(j.nominal_default) }}
                                            <div v-if="j.is_beda_gender" class="text-xs text-pink-500 font-normal mt-0.5">
                                                P: {{ formatRupiah(j.nominal_putri_default) }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <Link :href="`/admin/keuangan/tagihan/kelola/${j.id}`" class="bg-indigo-50 text-indigo-600 hover:bg-indigo-100 font-bold py-2 px-4 rounded-xl text-xs transition-colors border border-indigo-200 inline-flex items-center gap-1">
                                            <i class="fas fa-users-cog"></i> Atur Tagihan
                                        </Link>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button @click="editData(j)" class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 hover:bg-amber-100 flex items-center justify-center transition-colors">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                            <button @click="hapus(j.id)" class="w-9 h-9 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition-colors">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="jenis.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-500">
                                            <i class="fas fa-cog text-4xl mb-3 text-gray-300"></i>
                                            <p class="text-lg font-medium">Belum Ada Setting Pembayaran</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <!-- Form Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="font-bold text-lg text-gray-900 flex items-center gap-2">
                        <i :class="isEditMode ? 'fas fa-pen text-amber-500' : 'fas fa-plus text-primary-500'"></i> 
                        {{ isEditMode ? 'Edit Setting Pembayaran' : 'Tambah Setting Pembayaran' }}
                    </h3>
                    <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form @submit.prevent="submit" class="p-6 space-y-4">
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pos Pembayaran <span class="text-red-500">*</span></label>
                            <select v-model="form.id_pos_bayar" required class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500 text-sm">
                                <option value="" disabled>Pilih Pos...</option>
                                <option v-for="p in pos" :key="p.id" :value="p.id">{{ p.nama_pos }}</option>
                            </select>
                            <div v-if="form.errors.id_pos_bayar" class="text-red-500 text-xs mt-1">{{ form.errors.id_pos_bayar }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Ajaran <span class="text-red-500">*</span></label>
                            <select v-model="form.id_tahun_ajaran" required class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500 text-sm">
                                <option value="" disabled>Pilih Tahun...</option>
                                <option v-for="t in tahun" :key="t.id" :value="t.id">{{ t.tahun_ajaran }}</option>
                            </select>
                            <div v-if="form.errors.id_tahun_ajaran" class="text-red-500 text-xs mt-1">{{ form.errors.id_tahun_ajaran }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Tagihan <span class="text-red-500">*</span></label>
                            <select v-model="form.tipe_bayar" required class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500 text-sm">
                                <option value="BULANAN">BULANAN (Ditagih tiap bulan)</option>
                                <option value="BEBAS">BEBAS (Ditagih sekali lunas/cicil)</option>
                            </select>
                            <div v-if="form.errors.tipe_bayar" class="text-red-500 text-xs mt-1">{{ form.errors.tipe_bayar }}</div>
                        </div>
                        <div class="flex items-center mt-6">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="form.is_per_jurusan" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                                <span class="ml-3 text-sm font-medium text-gray-700">Tarif Beda Tiap Jurusan?</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center pb-2 border-b border-gray-100">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" v-model="form.is_beda_gender" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-pink-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-500"></div>
                            <span class="ml-3 text-sm font-medium text-gray-700">Tarif Beda Laki-laki & Perempuan?</span>
                        </label>
                    </div>
                    
                    <div v-if="!form.is_per_jurusan" class="grid" :class="form.is_beda_gender ? 'grid-cols-2 gap-4' : 'grid-cols-1'">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tarif {{ form.is_beda_gender ? 'Laki-laki' : 'Sama Rata' }} (Rp) <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500 font-bold">Rp</span>
                                <input type="text" v-model="form.nominal_default" @input="e => handleNominalInput(e, 'nominal_default')" :required="!form.is_per_jurusan" placeholder="0" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500 text-sm font-bold text-gray-900">
                            </div>
                            <div v-if="form.errors.nominal_default" class="text-red-500 text-xs mt-1">{{ form.errors.nominal_default }}</div>
                        </div>
                        <div v-if="form.is_beda_gender">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tarif Perempuan (Rp) <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500 font-bold">Rp</span>
                                <input type="text" v-model="form.nominal_putri_default" @input="e => handleNominalInput(e, 'nominal_putri_default')" :required="!form.is_per_jurusan && form.is_beda_gender" placeholder="0" class="w-full pl-10 pr-4 py-2 border border-pink-300 rounded-xl focus:ring-pink-500 focus:border-pink-500 text-sm font-bold text-gray-900">
                            </div>
                            <div v-if="form.errors.nominal_putri_default" class="text-red-500 text-xs mt-1">{{ form.errors.nominal_putri_default }}</div>
                        </div>
                    </div>
                    
                    <div v-if="form.is_per_jurusan" class="space-y-3 max-h-[40vh] overflow-y-auto pr-2">
                        <div class="flex border-b pb-2">
                            <div class="w-1/3 text-sm font-medium text-gray-700">Jurusan <span class="text-red-500">*</span></div>
                            <div :class="form.is_beda_gender ? 'w-1/3' : 'w-2/3'" class="text-sm font-medium text-gray-700 text-center">Tarif {{ form.is_beda_gender ? 'Laki-laki' : '' }}</div>
                            <div v-if="form.is_beda_gender" class="w-1/3 text-sm font-medium text-pink-600 text-center">Tarif Perempuan</div>
                        </div>
                        <div v-for="j in jurusan" :key="j.id" class="flex items-center gap-3">
                            <div class="w-1/3 text-sm font-medium text-gray-700 truncate" :title="j.nama_jurusan">
                                {{ j.nama_jurusan }}
                            </div>
                            <div class="relative" :class="form.is_beda_gender ? 'w-1/3' : 'w-2/3'">
                                <span class="absolute left-3 top-2 text-gray-500 font-bold text-xs">Rp</span>
                                <input type="text" v-model="form.nominal_jurusan[j.id]" @input="handleNominalJurusanInput($event, j.id, false)" required placeholder="0" class="w-full pl-9 pr-3 py-1.5 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 text-sm font-bold text-gray-900">
                            </div>
                            <div v-if="form.is_beda_gender" class="w-1/3 relative">
                                <span class="absolute left-3 top-2 text-pink-500 font-bold text-xs">Rp</span>
                                <input type="text" v-model="form.nominal_jurusan_putri[j.id]" @input="handleNominalJurusanInput($event, j.id, true)" required placeholder="0" class="w-full pl-9 pr-3 py-1.5 border border-pink-300 rounded-lg focus:ring-pink-500 focus:border-pink-500 text-sm font-bold text-gray-900">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="isModalOpen = false" class="px-5 py-2.5 rounded-xl text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-primary-600 hover:bg-primary-700 transition-colors shadow-lg shadow-primary-500/30 disabled:opacity-50">
                            <i class="fas fa-save mr-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>
