<script setup>
import { ref, watch, onMounted, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import SearchableSelect from '@/Components/SearchableSelect.vue';

const props = defineProps({
    show: Boolean,
    editData: Object,
    mapels: Array,
    bankSoals: Array
});

const emit = defineEmits(['close']);

const form = useForm({
    nama: '',
    bank_soal_id: '',
    mapel_id: '',
    bobot_tf: 0,
    bobot_esai: 0,
    timeout: 0,
    minFinishTime: 0,
    acak_soal: false,
    acak_opsi: false,
    visible_pg: 0,
    visible_esai: 0,
    visible_pgmulti: 0,
    visible_pgtf: 0,
    visible_pgcouple: 0,
    visible_shortentry: 0,
});

watch(() => props.show, (newVal) => {
    if (newVal) {
        if (props.editData) {
            form.nama = props.editData.nama || '';
            form.bank_soal_id = props.editData.bank_soal_id || '';
            form.mapel_id = props.editData.mapel_id || '';
            form.bobot_tf = props.editData.bobot_tf || 0;
            form.bobot_esai = props.editData.bobot_esai || 0;
            form.timeout = props.editData.timeout || 0;
            form.minFinishTime = props.editData.minFinishTime || 0;
            form.acak_soal = props.editData.acak_soal == 1;
            form.acak_opsi = props.editData.acak_opsi == 1;
            form.visible_pg = props.editData.visible_pg || 0;
            form.visible_esai = props.editData.visible_esai || 0;
            form.visible_pgmulti = props.editData.visible_pgmulti || 0;
            form.visible_pgtf = props.editData.visible_pgtf || 0;
            form.visible_pgcouple = props.editData.visible_pgcouple || 0;
            form.visible_shortentry = props.editData.visible_shortentry || 0;
        } else {
            form.reset();
        }
    }
});

const submit = () => {
    if (props.editData) {
        form.put(route('admin.cbt.draft-ujian.update', props.editData.id), {
            onSuccess: () => emit('close')
        });
    } else {
        form.post(route('admin.cbt.draft-ujian.store'), {
            onSuccess: () => emit('close')
        });
    }
};

const mapelOptions = computed(() => {
    return props.mapels.map(m => ({
        value: m.id,
        label: m.nama_mapel
    }));
});

const bankSoalOptions = computed(() => {
    return props.bankSoals.map(bs => ({
        value: bs.id,
        label: `[${bs.kode}] ${bs.deskripsi || ''}`
    }));
});
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 dark:bg-gray-900/80 backdrop-blur-sm px-4">
        <div class="bg-white dark:bg-gray-800 rounded-3xl w-full max-w-2xl shadow-2xl overflow-hidden animate-[modal_0.2s_ease-out]">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex justify-between items-center">
                <h3 class="text-xl font-black text-gray-800 dark:text-white flex items-center gap-2">
                    <i :class="editData ? 'fas fa-edit text-amber-500' : 'fas fa-plus-circle text-primary-500'"></i>
                    {{ editData ? 'Edit Draft Ujian' : 'Buat Draft Ujian Baru' }}
                </h3>
                <button @click="$emit('close')" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-500 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form @submit.prevent="submit" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Nama Ujian</label>
                        <input v-model="form.nama" type="text" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary-500/20 text-gray-800 dark:text-gray-200" required placeholder="Contoh: PTS Ganjil Bahasa Indonesia">
                        <p v-if="form.errors.nama" class="text-rose-500 text-xs mt-1">{{ form.errors.nama }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Bank Soal Induk</label>
                        <SearchableSelect 
                            v-model="form.bank_soal_id"
                            :options="bankSoalOptions"
                            placeholder="Ketik untuk mencari bank soal..."
                        />
                        <p v-if="form.errors.bank_soal_id" class="text-rose-500 text-xs mt-1">{{ form.errors.bank_soal_id }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Mata Pelajaran</label>
                        <SearchableSelect 
                            v-model="form.mapel_id"
                            :options="mapelOptions"
                            placeholder="Ketik untuk mencari mapel..."
                        />
                        <p v-if="form.errors.mapel_id" class="text-rose-500 text-xs mt-1">{{ form.errors.mapel_id }}</p>
                    </div>

                    <!-- Waktu -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Waktu Pengerjaan (Menit)</label>
                        <input v-model="form.timeout" type="number" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary-500/20 text-gray-800 dark:text-gray-200" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Minimal Waktu Selesai (Menit)</label>
                        <input v-model="form.minFinishTime" type="number" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary-500/20 text-gray-800 dark:text-gray-200" required>
                    </div>

                    <!-- Bobot Khusus -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Bobot True/False</label>
                        <input v-model="form.bobot_tf" type="number" step="any" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary-500/20 text-gray-800 dark:text-gray-200" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Bobot Esai (Satu Soal)</label>
                        <input v-model="form.bobot_esai" type="number" step="any" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary-500/20 text-gray-800 dark:text-gray-200" required>
                    </div>

                    <!-- Kuantitas Soal -->
                    <div class="md:col-span-2 mt-2 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200 mb-4">Jumlah Soal Ditampilkan</h4>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">PG Biasa</label>
                                <input v-model="form.visible_pg" type="number" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500/20 text-gray-800 dark:text-gray-200" required>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">PG Kompleks</label>
                                <input v-model="form.visible_pgmulti" type="number" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500/20 text-gray-800 dark:text-gray-200" required>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Menjodohkan</label>
                                <input v-model="form.visible_pgcouple" type="number" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500/20 text-gray-800 dark:text-gray-200" required>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Isian Singkat</label>
                                <input v-model="form.visible_shortentry" type="number" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500/20 text-gray-800 dark:text-gray-200" required>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Esai</label>
                                <input v-model="form.visible_esai" type="number" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500/20 text-gray-800 dark:text-gray-200" required>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Benar/Salah</label>
                                <input v-model="form.visible_pgtf" type="number" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500/20 text-gray-800 dark:text-gray-200" required>
                            </div>
                        </div>
                    </div>

                    <!-- Checkboxes -->
                    <div class="md:col-span-2 mt-4 flex gap-6">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input v-model="form.acak_soal" type="checkbox" class="w-5 h-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            <span class="text-sm font-bold text-gray-700 dark:text-gray-300">Acak Soal</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input v-model="form.acak_opsi" type="checkbox" class="w-5 h-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            <span class="text-sm font-bold text-gray-700 dark:text-gray-300">Acak Opsi Pilihan</span>
                        </label>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <button type="button" @click="$emit('close')" class="px-5 py-2.5 rounded-xl bg-gray-100 text-gray-700 font-bold hover:bg-gray-200 transition-colors">
                        Batal
                    </button>
                    <button type="submit" :disabled="form.processing" class="px-5 py-2.5 rounded-xl bg-primary-600 text-white font-bold hover:bg-primary-700 transition-colors shadow-md shadow-primary-500/20 disabled:opacity-50">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Draft' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>
@keyframes modal {
    from { opacity: 0; transform: scale(0.95) translateY(10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
</style>
