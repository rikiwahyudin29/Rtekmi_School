<script setup>
import { ref, watch, computed } from 'vue';
import axios from 'axios';

const emit = defineEmits(['close', 'saved']);

const isVisible = ref(false);
const activeJadwalId = ref(null);
const activeUjianId = ref(null);

const isLoading = ref(false);
const isSaving = ref(false);
const errorMsg = ref('');

const jawaban = ref({
    pg: [],
    pgmulti: [],
    pgtf: [],
    pgcouple: [],
    shortentry: [],
    esai: []
});

const opsiList = ref({
    pg: [],
    pgmulti: [],
    pgtf: [],
    pgcouple: []
});

const coupleList = ref([]);

const activeTab = ref('esai'); // default tab

const tabs = computed(() => {
    let t = [];
    if (jawaban.value.esai.length > 0) t.push({ id: 'esai', name: 'Esai' });
    if (jawaban.value.shortentry.length > 0) t.push({ id: 'shortentry', name: 'Isian Singkat' });
    if (jawaban.value.pg.length > 0) t.push({ id: 'pg', name: 'Pilihan Ganda' });
    if (jawaban.value.pgmulti.length > 0) t.push({ id: 'pgmulti', name: 'PG Kompleks' });
    if (jawaban.value.pgtf.length > 0) t.push({ id: 'pgtf', name: 'Benar / Salah' });
    if (jawaban.value.pgcouple.length > 0) t.push({ id: 'pgcouple', name: 'Menjodohkan' });
    return t;
});

const loadJawaban = async () => {
    if (!activeUjianId.value) return;
    isLoading.value = true;
    errorMsg.value = '';
    
    try {
        const response = await axios.get(route('admin.cbt.jadwal-ujian.jawaban', { id: activeJadwalId.value, ujian_id: activeUjianId.value }));
        if (response.data.error) {
            errorMsg.value = response.data.error;
        } else {
            jawaban.value = {
                pg: response.data.jawaban.pg.soal || [],
                pgmulti: (response.data.jawaban.pgmulti.soal || []).map(s => { 
                    s.opsi_array = s.opsi ? s.opsi.split(',').map(Number) : []; 
                    return s; 
                }),
                pgtf: (response.data.jawaban.pgtf.soal || []).map(s => { 
                    s.tf_obj = {};
                    if(s.opsi) {
                        s.opsi.split(',').forEach(p => { 
                            const parts = p.split('-'); 
                            if(parts.length===2) s.tf_obj[parts[0]] = parts[1]; 
                        });
                    }
                    return s;
                }),
                pgcouple: (response.data.jawaban.pgcouple.soal || []).map(s => {
                    s.couple_obj = {};
                    if(s.opsi) {
                        s.opsi.split(',').forEach(p => { 
                            const parts = p.split('-'); 
                            if(parts.length===2) s.couple_obj[parts[0]] = parts[1]; 
                        });
                    }
                    return s;
                }),
                shortentry: response.data.jawaban.shortentry.soal || [],
                esai: response.data.jawaban.esai.soal || []
            };

            opsiList.value = {
                pg: response.data.jawaban.pg.opsi || [],
                pgmulti: response.data.jawaban.pgmulti.opsi || [],
                pgtf: response.data.jawaban.pgtf.opsi || [],
                pgcouple: response.data.jawaban.pgcouple.opsi || []
            };
            coupleList.value = response.data.jawaban.pgcouple.couple || [];

            // Set active tab to the first available tab
            if (tabs.value.length > 0 && !tabs.value.find(t => t.id === activeTab.value)) {
                activeTab.value = tabs.value[0].id;
            }
        }
    } catch (e) {
        errorMsg.value = 'Gagal memuat jawaban.';
    } finally {
        isLoading.value = false;
    }
};

const open = (peserta) => {
    activeJadwalId.value = peserta.jadwal_id;
    activeUjianId.value = peserta.id;
    isVisible.value = true;
    loadJawaban();
};

const close = () => {
    isVisible.value = false;
    emit('close');
};

defineExpose({ open, close });

const saveJawaban = async () => {
    isSaving.value = true;
    try {
        const payload = {
            pg: jawaban.value.pg,
            pgmulti: jawaban.value.pgmulti.map(s => ({ ...s, opsi: s.opsi_array.join(',') })),
            pgtf: jawaban.value.pgtf.map(s => ({ ...s, opsi: Object.entries(s.tf_obj).map(([k,v]) => `${k}-${v}`).join(',') })),
            pgcouple: jawaban.value.pgcouple.map(s => ({ ...s, opsi: Object.entries(s.couple_obj).map(([k,v]) => `${k}-${v}`).join(',') })),
            shortentry: jawaban.value.shortentry,
            esai: jawaban.value.esai
        };
        await axios.put(route('admin.cbt.jadwal-ujian.update_jawaban', { id: activeJadwalId.value, ujian_id: activeUjianId.value }), payload);
        window.Swal.fire('Berhasil', 'Jawaban berhasil dikoreksi', 'success');
        emit('saved');
        emit('close');
    } catch (e) {
        window.Swal.fire('Gagal', 'Gagal menyimpan koreksi jawaban', 'error');
    } finally {
        isSaving.value = false;
    }
};

const validateNilai = (index) => {
    let nilai = parseFloat(jawaban.value.esai[index].nilai);
    if (isNaN(nilai) || nilai < 0) nilai = 0;
    // max score per essay depends on logic, assume max 100 per essay for simplicity or 100/count
    const max = 100; // Adjust as needed
    if (nilai > max) nilai = max;
    jawaban.value.esai[index].nilai = nilai;
};
</script>

<template>
    <div v-if="isVisible" class="fixed inset-0 z-[70] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" @click="close"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl w-full">
                <!-- Header -->
                <div class="bg-indigo-50 border-b border-indigo-100 px-6 py-4 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-indigo-800 flex items-center gap-2">
                        <i class="fas fa-check-double"></i> Koreksi Jawaban
                    </h3>
                    <button @click="close" class="text-gray-400 hover:text-red-500 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Body -->
                <div class="bg-white px-6 py-5 h-[75vh] overflow-y-auto flex flex-col relative">
                    <div v-if="isLoading" class="absolute inset-0 bg-white/80 z-10 flex flex-col items-center justify-center">
                        <i class="fas fa-spinner fa-spin text-4xl text-indigo-500 mb-3"></i>
                        <span class="font-medium text-gray-600">Memuat jawaban...</span>
                    </div>

                    <div v-else-if="errorMsg" class="bg-red-50 text-red-700 p-4 rounded-lg text-center font-medium">
                        {{ errorMsg }}
                    </div>

                    <div v-else-if="tabs.length === 0" class="text-center py-10 text-gray-500 font-medium">
                        Belum ada jawaban tersimpan.
                    </div>

                    <div v-else class="flex flex-col h-full">
                        <!-- Tabs Navigation -->
                        <div class="border-b border-gray-200 mb-4">
                            <nav class="-mb-px flex space-x-6">
                                <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
                                    :class="activeTab === tab.id ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap pb-3 px-1 border-b-2 font-medium text-sm transition-colors">
                                    {{ tab.name }}
                                </button>
                            </nav>
                        </div>

                        <!-- Tab Content: Esai -->
                        <div v-show="activeTab === 'esai'" class="flex-grow overflow-y-auto">
                            <div v-for="(soal, index) in jawaban.esai" :key="soal.soal_id" class="mb-6 bg-gray-50 rounded-xl p-4 border border-gray-200">
                                <div class="flex gap-3 mb-4">
                                    <span class="font-bold text-gray-700">{{ index + 1 }}.</span>
                                    <div class="prose max-w-none prose-sm text-gray-800" v-html="soal.question"></div>
                                </div>
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Jawaban Siswa</label>
                                        <textarea v-model="soal.esai" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 h-32 text-sm p-3 bg-white" placeholder="Kosong..."></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Nilai</label>
                                        <div class="flex items-center gap-3">
                                            <input type="range" v-model="soal.nilai" @input="validateNilai(index)" class="flex-grow w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer" min="0" max="100">
                                            <input type="number" v-model="soal.nilai" @change="validateNilai(index)" class="w-20 border-gray-300 rounded-lg text-center focus:ring-indigo-500 focus:border-indigo-500 shadow-sm font-bold text-indigo-700" min="0" max="100">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Content: Short Entry -->
                        <div v-show="activeTab === 'shortentry'" class="flex-grow overflow-y-auto">
                            <div v-for="(soal, index) in jawaban.shortentry" :key="soal.soal_id" class="mb-6 bg-gray-50 rounded-xl p-4 border border-gray-200">
                                <div class="flex gap-3 mb-4">
                                    <span class="font-bold text-gray-700">{{ index + 1 }}.</span>
                                    <div class="w-full">
                                        <div class="prose max-w-none prose-sm text-gray-800 mb-3" v-html="soal.question"></div>
                                        <div class="flex items-center justify-between bg-emerald-50 text-emerald-800 p-2 rounded border border-emerald-200 text-sm">
                                            <span class="font-semibold">Kunci Jawaban:</span>
                                            <span class="font-mono bg-white px-2 py-1 rounded">{{ soal.correct_answer }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Jawaban Siswa</label>
                                    <input type="text" v-model="soal.respond" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2.5 text-sm font-medium" 
                                           :class="(soal.respond || '').toLowerCase() === (soal.correct_answer || '').toLowerCase() ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-red-300 bg-red-50 text-red-700'">
                                </div>
                            </div>
                        </div>

                        <!-- Tab Content: Pilihan Ganda -->
                        <div v-show="activeTab === 'pg'" class="flex-grow overflow-y-auto">
                            <div v-for="(soal, index) in jawaban.pg" :key="soal.soal_id" class="mb-6 bg-gray-50 rounded-xl p-4 border border-gray-200">
                                <div class="flex gap-3 mb-4">
                                    <span class="font-bold text-gray-700">{{ index + 1 }}.</span>
                                    <div class="prose max-w-none prose-sm text-gray-800" v-html="soal.question"></div>
                                </div>
                                <div class="pl-6 grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4">
                                    <div v-for="opsi in opsiList.pg.filter(o => o.soal_id === soal.soal_id)" :key="opsi.id">
                                        <label class="flex items-start gap-2 p-3 rounded-xl border cursor-pointer transition-all hover:shadow-sm" 
                                               :class="[
                                                   soal.opsi_id == opsi.id ? 'bg-indigo-50 border-indigo-400 ring-1 ring-indigo-400' : 'bg-white border-gray-200',
                                                   opsi.is_key == 1 ? 'border-emerald-400 bg-emerald-50/50' : ''
                                               ]">
                                            <input type="radio" v-model="soal.opsi_id" :value="opsi.id" class="mt-1 text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer">
                                            <div class="flex-grow">
                                                <div class="prose prose-sm" v-html="opsi.body"></div>
                                                <div v-if="opsi.is_key == 1" class="mt-2 inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">
                                                    <i class="fas fa-check-circle"></i> Kunci Jawaban
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Content: PG Kompleks -->
                        <div v-show="activeTab === 'pgmulti'" class="flex-grow overflow-y-auto">
                            <div v-for="(soal, index) in jawaban.pgmulti" :key="soal.soal_id" class="mb-6 bg-gray-50 rounded-xl p-4 border border-gray-200">
                                <div class="flex gap-3 mb-4">
                                    <span class="font-bold text-gray-700">{{ index + 1 }}.</span>
                                    <div class="prose max-w-none prose-sm text-gray-800" v-html="soal.question"></div>
                                </div>
                                <div class="pl-6 grid grid-cols-1 gap-3 mt-4">
                                    <div v-for="opsi in opsiList.pgmulti.filter(o => o.soal_id === soal.soal_id)" :key="opsi.id">
                                        <label class="flex items-start gap-2 p-3 rounded-xl border cursor-pointer transition-all hover:shadow-sm" 
                                               :class="[
                                                   soal.opsi_array.includes(opsi.id) ? 'bg-indigo-50 border-indigo-400 ring-1 ring-indigo-400' : 'bg-white border-gray-200',
                                                   opsi.is_key == 1 ? 'border-emerald-400 bg-emerald-50/50' : ''
                                               ]">
                                            <input type="checkbox" v-model="soal.opsi_array" :value="opsi.id" class="mt-1 text-indigo-600 focus:ring-indigo-500 rounded cursor-pointer">
                                            <div class="flex-grow">
                                                <div class="prose prose-sm" v-html="opsi.body"></div>
                                                <div v-if="opsi.is_key == 1" class="mt-2 inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">
                                                    <i class="fas fa-check-circle"></i> Kunci Jawaban
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Content: Benar Salah -->
                        <div v-show="activeTab === 'pgtf'" class="flex-grow overflow-y-auto">
                            <div v-for="(soal, index) in jawaban.pgtf" :key="soal.soal_id" class="mb-6 bg-gray-50 rounded-xl p-4 border border-gray-200">
                                <div class="flex gap-3 mb-4">
                                    <span class="font-bold text-gray-700">{{ index + 1 }}.</span>
                                    <div class="prose max-w-none prose-sm text-gray-800" v-html="soal.question"></div>
                                </div>
                                <div class="pl-6 grid grid-cols-1 gap-3 mt-4">
                                    <div v-for="opsi in opsiList.pgtf.filter(o => o.soal_id === soal.soal_id)" :key="opsi.id" class="bg-white border border-gray-200 p-3 rounded-xl flex items-center justify-between gap-4">
                                        <div class="flex-grow prose prose-sm" v-html="opsi.body"></div>
                                        <div class="flex items-center gap-3 bg-gray-50 p-1.5 rounded-lg border border-gray-100 shrink-0">
                                            <label class="flex items-center gap-1 cursor-pointer px-2 py-1 rounded transition-colors" :class="soal.tf_obj[opsi.id] == '1' ? 'bg-indigo-100 text-indigo-700' : 'hover:bg-gray-200'">
                                                <input type="radio" v-model="soal.tf_obj[opsi.id]" value="1" class="text-indigo-600 w-3 h-3">
                                                <span class="text-xs font-bold">Benar</span>
                                            </label>
                                            <label class="flex items-center gap-1 cursor-pointer px-2 py-1 rounded transition-colors" :class="soal.tf_obj[opsi.id] == '0' ? 'bg-rose-100 text-rose-700' : 'hover:bg-gray-200'">
                                                <input type="radio" v-model="soal.tf_obj[opsi.id]" value="0" class="text-rose-600 w-3 h-3">
                                                <span class="text-xs font-bold">Salah</span>
                                            </label>
                                        </div>
                                        <div class="shrink-0 w-24 text-right">
                                            <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold" :class="opsi.is_key == 1 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'">
                                                <i :class="opsi.is_key == 1 ? 'fas fa-check' : 'fas fa-times'"></i> {{ opsi.is_key == 1 ? 'Kunci Benar' : 'Kunci Salah' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Content: Menjodohkan -->
                        <div v-show="activeTab === 'pgcouple'" class="flex-grow overflow-y-auto">
                            <div v-for="(soal, index) in jawaban.pgcouple" :key="soal.soal_id" class="mb-6 bg-gray-50 rounded-xl p-4 border border-gray-200">
                                <div class="flex gap-3 mb-4">
                                    <span class="font-bold text-gray-700">{{ index + 1 }}.</span>
                                    <div class="prose max-w-none prose-sm text-gray-800" v-html="soal.question"></div>
                                </div>
                                <div class="pl-6 mt-4">
                                    <div v-for="couple in coupleList.filter(c => c.soal_id === soal.soal_id)" :key="couple.id" class="mb-3 flex items-start gap-3 bg-white p-3 rounded-xl border border-gray-200">
                                        <div class="w-1/2 prose prose-sm border-r border-gray-100 pr-3" v-html="couple.body"></div>
                                        <div class="w-1/2 pl-2">
                                            <select v-model="soal.couple_obj[couple.id]" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                                <option value="" disabled>-- Pilih Pasangan --</option>
                                                <option v-for="opsi in opsiList.pgcouple.filter(o => o.soal_id === soal.soal_id)" :key="opsi.id" :value="opsi.id">
                                                    {{ opsi.body.replace(/<[^>]*>?/gm, '').substring(0, 50) }}{{ opsi.body.length > 50 ? '...' : '' }}
                                                </option>
                                            </select>
                                            
                                            <!-- Show correct answer hint -->
                                            <div v-if="opsiList.pgcouple.find(o => o.soal_couple_id === couple.id)" class="mt-2 text-xs text-emerald-600 bg-emerald-50 px-2 py-1 rounded inline-flex items-center gap-1">
                                                <i class="fas fa-key"></i> Kunci: {{ opsiList.pgcouple.find(o => o.soal_couple_id === couple.id).body.replace(/<[^>]*>?/gm, '').substring(0, 30) }}...
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 border-t border-gray-200 px-6 py-4 flex justify-end gap-3">
                    <button @click="close" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Batal
                    </button>
                    <button @click="saveJawaban" :disabled="isSaving || isLoading || tabs.length === 0" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-bold shadow-sm transition-colors flex items-center gap-2">
                        <i v-if="isSaving" class="fas fa-circle-notch fa-spin"></i>
                        <i v-else class="fas fa-save"></i>
                        Simpan Koreksi
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
