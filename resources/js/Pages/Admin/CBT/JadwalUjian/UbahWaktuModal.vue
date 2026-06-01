<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    show: Boolean,
    selectedIds: Array,
    jadwals: Object
});

const emit = defineEmits(['close']);

const editingJadwals = ref([]);

watch(() => props.show, (newVal) => {
    if (newVal && props.jadwals && props.jadwals.data) {
        editingJadwals.value = props.jadwals.data
            .filter(j => props.selectedIds.includes(j.id))
            .map(j => {
                const startStr = j.waktu_mulai ? j.waktu_mulai.replace(' ', 'T').slice(0, 16) : '';
                const endStr = j.waktu_selesai ? j.waktu_selesai.replace(' ', 'T').slice(0, 16) : '';

                return {
                    id: j.id,
                    nama_draft: j.nama_ujian || (j.draft_ujian ? j.draft_ujian.nama : 'Ujian'),
                    kode_ruang: j.ruangan ? j.ruangan.kode_ruangan : 'ALL',
                    startDateTime: startStr,
                    endDateTime: endStr,
                    status: j.status_ujian === 'AKTIF' ? 1 : 0,
                    use_token: j.setting_token,
                    show_point: j.setting_show_score,
                    multi_login: j.setting_multi_login
                };
            });
    }
});

const close = () => {
    emit('close');
};

const submit = async () => {
    try {
        const payload = editingJadwals.value.map(j => ({
            id: j.id,
            startDateTime: Math.floor(new Date(j.startDateTime).getTime() / 1000),
            endDateTime: Math.floor(new Date(j.endDateTime).getTime() / 1000),
            status: j.status,
            use_token: j.use_token,
            show_point: j.show_point,
            multi_login: j.multi_login
        }));

        await axios.post(route('admin.cbt.jadwal-ujian.batch_time'), { jadwals: payload });
        alert('Berhasil mengubah waktu ujian massal');
        close();
        window.location.reload();
    } catch (e) {
        alert('Gagal mengubah waktu ujian massal');
        console.error(e);
    }
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-5xl overflow-hidden border border-gray-100 dark:border-gray-700 flex flex-col max-h-full">
            <div class="p-5 border-b border-gray-100 flex justify-between items-center shrink-0">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Ubah Waktu Ujian ({{ selectedIds.length }} Jadwal)</h3>
                <button @click="close" class="text-gray-400 hover:text-red-500 transition-colors"><i class="fas fa-times text-xl"></i></button>
            </div>
            
            <div class="p-5 overflow-y-auto grow">
                <div class="overflow-x-auto w-full border border-gray-200 rounded-lg">
                    <table class="w-full text-sm text-left border-collapse table-striped">
                        <thead class="border-b border-gray-200 bg-gray-50 text-gray-700">
                            <tr>
                                <th class="p-3 font-bold uppercase whitespace-nowrap">Jadwal</th>
                                <th class="p-3 font-bold uppercase text-center whitespace-nowrap">Waktu Mulai</th>
                                <th class="p-3 font-bold uppercase text-center whitespace-nowrap">Waktu Selesai</th>
                                <th class="p-3 font-bold uppercase text-center whitespace-nowrap">Status</th>
                                <th class="p-3 font-bold uppercase text-center whitespace-nowrap">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(j, i) in editingJadwals" :key="j.id" class="border-b border-gray-100">
                                <td class="p-3 whitespace-nowrap">
                                    <div class="font-bold text-gray-800">{{ j.nama_draft }}</div>
                                    <div class="text-xs text-gray-500"><i class="fas fa-map-marker-alt text-emerald-500 w-4"></i> {{ j.kode_ruang }}</div>
                                </td>
                                <td class="p-3 text-center">
                                    <input type="datetime-local" v-model="j.startDateTime" class="border-gray-300 rounded p-1.5 text-sm w-full outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                                </td>
                                <td class="p-3 text-center">
                                    <input type="datetime-local" v-model="j.endDateTime" class="border-gray-300 rounded p-1.5 text-sm w-full outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                                </td>
                                <td class="p-3 text-center">
                                    <select v-model="j.status" class="border-gray-300 rounded p-1.5 text-sm w-full outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                                        <option :value="1">AKTIF</option>
                                        <option :value="0">NONAKTIF</option>
                                    </select>
                                </td>
                                <td class="p-3 text-center text-xs">
                                    <label class="flex items-center gap-1 mb-1 select-none cursor-pointer"><input type="checkbox" v-model="j.use_token" :true-value="1" :false-value="0" class="text-emerald-500 rounded"> Token</label>
                                    <label class="flex items-center gap-1 mb-1 select-none cursor-pointer"><input type="checkbox" v-model="j.show_point" :true-value="1" :false-value="0" class="text-emerald-500 rounded"> Nilai</label>
                                    <label class="flex items-center gap-1 select-none cursor-pointer"><input type="checkbox" v-model="j.multi_login" :true-value="1" :false-value="0" class="text-emerald-500 rounded"> Multi</label>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="p-5 border-t border-gray-100 bg-gray-50 flex justify-end gap-3 shrink-0">
                <button @click="close" class="px-5 py-2.5 bg-gray-200 text-gray-700 rounded-lg font-bold hover:bg-gray-300 transition-colors">Batal</button>
                <button @click="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold shadow-md shadow-emerald-500/30 transition-all">Simpan Perubahan</button>
            </div>
        </div>
    </div>
</template>
