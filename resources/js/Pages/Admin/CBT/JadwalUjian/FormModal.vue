<script setup>
import { useForm } from '@inertiajs/vue3';
import { onMounted, watch, ref, computed } from 'vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import CustomTimePicker from '@/Components/CustomTimePicker.vue';

const props = defineProps({
    show: Boolean,
    editData: Object,
    draftUjians: Array,
    jenisUjians: Array,
    tahunAjarans: Array,
    ruangans: Array,
    gurus: Array, // Pengawas
});

const emit = defineEmits(['close']);

const form = useForm({
    nama_ujian: '',
    id_bank_soal: '',
    id_jenis_ujian: '',
    id_tahun_ajaran: '',
    id_ruangan: '',
    id_guru: '', // Pengawas
    tanggal_mulai: '',
    jam_mulai: '',
    tanggal_selesai: '',
    jam_selesai: '',
    setting_strict: false,
    setting_afk_timeout: 0,
    setting_max_violation: 3,
    setting_show_score: false,
    setting_multi_login: false,
    setting_token: false,
    token: '',
    acak_soal: false,
    acak_opsi: false,
    status_ujian: 'AKTIF',
});

watch(() => props.show, (newVal) => {
    if (newVal) {
        if (props.editData) {
            form.nama_ujian = props.editData.nama_ujian || '';
            form.id_bank_soal = props.editData.id_bank_soal || '';
            form.id_jenis_ujian = props.editData.id_jenis_ujian || '';
            form.id_tahun_ajaran = props.editData.id_tahun_ajaran || '';
            form.id_ruangan = props.editData.id_ruangan || '';
            form.id_guru = props.editData.id_guru || '';
            
            if (props.editData.waktu_mulai) {
                const split = props.editData.waktu_mulai.split(' ');
                form.tanggal_mulai = split[0] || '';
                form.jam_mulai = (split[1] || '').substring(0, 5);
            }
            if (props.editData.waktu_selesai) {
                const split = props.editData.waktu_selesai.split(' ');
                form.tanggal_selesai = split[0] || '';
                form.jam_selesai = (split[1] || '').substring(0, 5);
            }
            
            form.setting_strict = props.editData.setting_strict == 1;
            form.setting_afk_timeout = props.editData.setting_afk_timeout || 0;
            form.setting_max_violation = props.editData.setting_max_violation ?? 3;
            form.setting_show_score = props.editData.setting_show_score == 1;
            form.setting_multi_login = props.editData.setting_multi_login == 1;
            form.setting_token = props.editData.setting_token == 1;
            form.token = props.editData.token || '';
            form.acak_soal = props.editData.acak_soal == 1;
            form.acak_opsi = props.editData.acak_opsi == 1;
            form.status_ujian = props.editData.status_ujian || 'AKTIF';
        } else {
            form.reset();
            form.setting_max_violation = 3;
            form.status_ujian = 'AKTIF';
        }
    }
});

const selectedDraft = computed(() => {
    return props.draftUjians?.find(d => d.id === form.id_bank_soal);
});

const draftInfo = computed(() => {
    if (!selectedDraft.value) return null;
    const d = selectedDraft.value;
    const totalVisible = 
        parseInt(d.visible_pg || 0) + 
        parseInt(d.visible_esai || 0) + 
        parseInt(d.visible_pgmulti || 0) + 
        parseInt(d.visible_pgtf || 0) + 
        parseInt(d.visible_pgcouple || 0) + 
        parseInt(d.visible_shortentry || 0);
        
    return {
        timeout: d.timeout || 0,
        mapel: d.mapel?.nama_mapel || '-',
        bank_soal: d.bank_soal?.kode || d.bankSoal?.kode || '-',
        total_visible: totalVisible
    };
});

const submit = () => {
    // Gabung tanggal dan jam
    const data = form.data();
    data.waktu_mulai = `${form.tanggal_mulai} ${form.jam_mulai}:00`;
    data.waktu_selesai = `${form.tanggal_selesai} ${form.jam_selesai}:00`;

    if (props.editData) {
        form.transform(() => data).put(route('admin.cbt.jadwal-ujian.update', props.editData.id), {
            onSuccess: () => emit('close')
        });
    } else {
        form.transform(() => data).post(route('admin.cbt.jadwal-ujian.store'), {
            onSuccess: () => emit('close')
        });
    }
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900/60 dark:bg-gray-900/80 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-middle bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 w-full max-w-4xl border border-gray-100 dark:border-gray-700">
                <form @submit.prevent="submit" class="flex flex-col h-full max-h-[90vh]">
                    <!-- Header -->
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-calendar-plus text-primary-600 dark:text-primary-400 text-xl"></i>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ editData ? 'Edit Jadwal Ujian' : 'Tambah Jadwal Ujian' }}</h3>
                        </div>
                        <button type="button" @click="$emit('close')" class="text-gray-400 hover:text-rose-500 w-8 h-8 flex items-center justify-center rounded-full transition-colors">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-6 overflow-y-auto bg-white dark:bg-gray-900 flex-1 space-y-6 custom-scrollbar">
                        
                        <!-- Nama Jadwal -->
                        <div>
                            <label class="block text-sm font-bold text-gray-900 dark:text-white mb-2">Nama Jadwal</label>
                            <input required v-model="form.nama_ujian" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-primary-500 focus:border-primary-500 p-2.5 shadow-sm" placeholder="Contoh: PTS Ganjil 2026">
                            <div v-if="form.errors.nama_ujian" class="mt-1 text-sm text-rose-500 font-medium">{{ form.errors.nama_ujian }}</div>
                        </div>

                        <!-- Waktu Pelaksanaan -->
                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700">
                            <label class="block text-sm font-bold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-4"><i class="far fa-clock text-primary-500 mr-2"></i>Waktu Pelaksanaan</label>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Tanggal Mulai <span class="text-rose-500">*</span></label>
                                    <input required v-model="form.tanggal_mulai" type="date" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-primary-500 focus:border-primary-500 p-2.5">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Jam Mulai <span class="text-rose-500">*</span></label>
                                    <CustomTimePicker v-model="form.jam_mulai" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Tanggal Selesai <span class="text-rose-500">*</span></label>
                                    <input required v-model="form.tanggal_selesai" type="date" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-primary-500 focus:border-primary-500 p-2.5">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Jam Selesai <span class="text-rose-500">*</span></label>
                                    <CustomTimePicker v-model="form.jam_selesai" />
                                </div>
                            </div>
                        </div>

                        <!-- Pilihan Master -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-900 dark:text-white mb-2">Tahun Pelajaran</label>
                                <SearchableSelect 
                                    v-model="form.id_tahun_ajaran" 
                                    :options="tahunAjarans.map(ta => ({id: ta.id, label: `${ta.tahun_ajaran} ${ta.semester}`}))" 
                                    labelKey="label" 
                                    valueKey="id" 
                                    placeholder="-- Pilih Tahun Pelajaran --" 
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-900 dark:text-white mb-2">Jenis Ujian</label>
                                <SearchableSelect 
                                    v-model="form.id_jenis_ujian" 
                                    :options="jenisUjians" 
                                    labelKey="nama_jenis" 
                                    valueKey="id" 
                                    placeholder="-- Pilih Jenis Ujian --" 
                                />
                            </div>
                            
                            <!-- Draft Soal -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-gray-900 dark:text-white mb-2">Draft Soal</label>
                                <SearchableSelect 
                                    v-model="form.id_bank_soal" 
                                    :options="draftUjians.map(d => ({id: d.id, label: `${d.nama} - ${d.mapel?.nama_mapel || ''}`}))" 
                                    labelKey="label" 
                                    valueKey="id" 
                                    placeholder="-- Pilih Draft Ujian --" 
                                />

                                <!-- Draft Info -->
                                <div v-if="draftInfo" class="mt-3 flex flex-wrap gap-2">
                                    <span class="bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1.5 rounded-md border border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800/50"><i class="fas fa-book mr-1.5"></i>{{ draftInfo.mapel }}</span>
                                    <span class="bg-amber-100 text-amber-800 text-xs font-bold px-3 py-1.5 rounded-md border border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800/50"><i class="fas fa-stopwatch mr-1.5"></i>{{ draftInfo.timeout }} Menit</span>
                                    <span class="bg-purple-100 text-purple-800 text-xs font-bold px-3 py-1.5 rounded-md border border-purple-200 dark:bg-purple-900/30 dark:text-purple-400 dark:border-purple-800/50"><i class="fas fa-database mr-1.5"></i>Bank Soal: {{ draftInfo.bank_soal }}</span>
                                    <span class="bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1.5 rounded-md border border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800/50"><i class="fas fa-list-ol mr-1.5"></i>{{ draftInfo.total_visible }} Soal Tampil</span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-900 dark:text-white mb-2">Pengawas</label>
                                <SearchableSelect 
                                    v-model="form.id_guru" 
                                    :options="gurus" 
                                    labelKey="nama_lengkap" 
                                    valueKey="id" 
                                    placeholder="-- Pilih Pengawas --" 
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-900 dark:text-white mb-2">Ruang Ujian</label>
                                <SearchableSelect 
                                    v-model="form.id_ruangan" 
                                    :options="ruangans" 
                                    labelKey="nama_ruangan" 
                                    valueKey="id" 
                                    placeholder="-- Pilih Ruangan --" 
                                />
                            </div>
                        </div>

                        <!-- Keamanan (Strict, AFK) -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700">
                            <div>
                                <label class="block mb-2 text-xs font-semibold text-gray-900 dark:text-white">Strict Mode</label>
                                <label class="inline-flex items-center cursor-pointer">
                                  <input type="checkbox" v-model="form.setting_strict" class="sr-only peer" />
                                  <div class="relative w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                                  <span class="ms-3 text-sm font-bold text-gray-700 dark:text-gray-300">{{ form.setting_strict ? 'Aktif' : 'Tidak' }}</span>
                                </label>
                            </div>
                            <div>
                                <label class="block mb-2 text-xs font-semibold text-gray-900 dark:text-white">AFK Timeout (Detik)</label>
                                <input v-model="form.setting_afk_timeout" type="number" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-900 dark:border-gray-600 dark:text-white" />
                            </div>
                            <div class="md:col-span-2">
                                <label class="block mb-2 text-xs font-semibold text-gray-900 dark:text-white">Maks. Pelanggaran</label>
                                <input v-model="form.setting_max_violation" type="number" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-900 dark:border-gray-600 dark:text-white" />
                            </div>
                        </div>

                        <!-- Pengaturan Lainnya -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700">
                            <div>
                                <label class="block mb-2 text-xs font-semibold text-gray-900 dark:text-white">Status Aktif</label>
                                <label class="inline-flex items-center cursor-pointer">
                                  <input type="checkbox" :checked="form.status_ujian === 'AKTIF'" @change="form.status_ujian = $event.target.checked ? 'AKTIF' : 'NONAKTIF'" class="sr-only peer" />
                                  <div class="relative w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-green-500"></div>
                                  <span class="ms-3 text-sm font-bold text-gray-700 dark:text-gray-300">{{ form.status_ujian === 'AKTIF' ? 'Ya' : 'Tidak' }}</span>
                                </label>
                            </div>
                            <div>
                                <label class="block mb-2 text-xs font-semibold text-gray-900 dark:text-white">Gunakan Token</label>
                                <label class="inline-flex items-center cursor-pointer">
                                  <input type="checkbox" v-model="form.setting_token" class="sr-only peer" />
                                  <div class="relative w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-amber-300 dark:peer-focus:ring-amber-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-amber-500"></div>
                                  <span class="ms-3 text-sm font-bold text-gray-700 dark:text-gray-300">{{ form.setting_token ? 'Ya' : 'Tidak' }}</span>
                                </label>
                            </div>
                            <div>
                                <label class="block mb-2 text-xs font-semibold text-gray-900 dark:text-white">Tampil Nilai</label>
                                <label class="inline-flex items-center cursor-pointer">
                                  <input type="checkbox" v-model="form.setting_show_score" class="sr-only peer" />
                                  <div class="relative w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 dark:peer-focus:ring-purple-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-purple-500"></div>
                                  <span class="ms-3 text-sm font-bold text-gray-700 dark:text-gray-300">{{ form.setting_show_score ? 'Ya' : 'Tidak' }}</span>
                                </label>
                            </div>
                            <div>
                                <label class="block mb-2 text-xs font-semibold text-gray-900 dark:text-white">Multi Login</label>
                                <label class="inline-flex items-center cursor-pointer">
                                  <input type="checkbox" v-model="form.setting_multi_login" class="sr-only peer" />
                                  <div class="relative w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-rose-300 dark:peer-focus:ring-rose-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-rose-500"></div>
                                  <span class="ms-3 text-sm font-bold text-gray-700 dark:text-gray-300">{{ form.setting_multi_login ? 'Ya' : 'Tidak' }}</span>
                                </label>
                            </div>
                        </div>

                        <!-- Info Alert -->
                        <div class="flex p-4 text-sm text-primary-800 rounded-xl bg-primary-50 dark:bg-primary-900/30 dark:text-primary-300 border border-primary-200 dark:border-primary-800/50">
                            <i class="fas fa-info-circle flex-shrink-0 inline w-5 h-5 mr-3 mt-0.5"></i>
                            <div>
                                <span class="font-bold mb-1 block">Informasi:</span>
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Strict: Mengunci layar peserta (Fullscreen).</li>
                                    <li>Afk: Keluar otomatis jika pindah aplikasi.</li>
                                    <li>Multilogin: Login banyak perangkat.</li>
                                </ul>
                            </div>
                        </div>

                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex items-center justify-end gap-3 shrink-0">
                        <button type="button" @click="$emit('close')" class="px-5 py-2 text-sm text-gray-700 dark:text-gray-300 font-bold hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors">
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing" class="px-6 py-2 text-sm bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-lg shadow-md transition-all flex items-center gap-2">
                            <i v-if="form.processing" class="fas fa-circle-notch fa-spin"></i>
                            <i v-else class="fas fa-save"></i>
                            {{ editData ? 'Simpan' : 'Tambah' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
