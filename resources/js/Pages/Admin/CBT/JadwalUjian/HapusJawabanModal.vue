<script setup>
import { ref } from 'vue';

const props = defineProps({
    show: Boolean,
    selectedIds: Array
});

const emit = defineEmits(['close']);

const close = () => {
    emit('close');
};

const submit = async () => {
    try {
        await axios.post(route('admin.cbt.jadwal-ujian.hapus_jawaban_masal'), { jadwals: props.selectedIds });
        alert('Berhasil menghapus jawaban ujian massal');
        close();
        window.location.reload();
    } catch (e) {
        alert('Gagal menghapus jawaban ujian massal');
        console.error(e);
    }
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-6 m-4 border border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-bold text-rose-600 dark:text-rose-400 mb-4">Hapus Jawaban Peserta ({{ selectedIds.length }} Jadwal)</h3>
            <p class="text-sm text-gray-500 mb-6">Fitur ini sedang dalam tahap pengembangan. Tindakan ini akan menghapus progress jawaban seluruh siswa di jadwal yang dipilih.</p>
            <div class="flex justify-end gap-3">
                <button @click="close" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg">Batal</button>
                <button @click="submit" class="px-4 py-2 bg-rose-500 text-white rounded-lg">Hapus Permanen</button>
            </div>
        </div>
    </div>
</template>
