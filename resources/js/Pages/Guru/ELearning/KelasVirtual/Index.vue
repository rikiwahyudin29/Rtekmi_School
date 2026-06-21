<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import dayjs from 'dayjs';

const props = defineProps({
    jadwal: Array,
    kelas: Array,
    isConnected: Boolean,
    googleAuthUrl: String
});

const isModalOpen = ref(false);

const form = useForm({
    mapel: '',
    kelas_id: '',
    jam_mulai: '',
    jam_selesai: '',
});

const openModal = () => {
    form.reset();
    isModalOpen.value = true;
};

const submit = () => {
    form.post(route('guru.elearning.kelas-virtual.store'), {
        onSuccess: () => {
            isModalOpen.value = false;
        }
    });
};

const deleteJadwal = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus jadwal ini?')) {
        router.delete(route('guru.elearning.kelas-virtual.destroy', id));
    }
};

const formatTime = (time) => {
    if (!time) return '-';
    return time.substring(0, 5);
};

const formatDate = (date) => {
    if (!date) return '-';
    return dayjs(date).format('DD MMM YYYY, HH:mm');
};
</script>

<template>
    <Head title="Kelas Virtual" />

    <DashboardLayout>
        <div class="p-6">
            <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-gray-100 dark:border-gray-700 pb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                            <i class="fas fa-video"></i>
                        </div>
                        Kelas Virtual (Google Meet)
                    </h1>
                    <p class="text-gray-500 mt-1 dark:text-gray-400">Buat dan kelola sesi Google Meet untuk pembelajaran online.</p>
                </div>
                
                <div class="flex gap-3">
                    <button v-if="isConnected" @click="openModal" class="px-5 py-2.5 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 shadow-md shadow-blue-500/20 transition-all flex items-center gap-2">
                        <i class="fas fa-plus"></i> Buat Meet Baru
                    </button>
                    <a v-else :href="googleAuthUrl" class="px-5 py-2.5 bg-red-500 text-white font-medium rounded-xl hover:bg-red-600 shadow-md shadow-red-500/20 transition-all flex items-center gap-2">
                        <i class="fab fa-google"></i> Hubungkan Google
                    </a>
                </div>
            </div>

            <!-- Warning if not connected -->
            <div v-if="!isConnected" class="mb-8 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl p-6 flex flex-col md:flex-row items-center gap-6">
                <div class="w-16 h-16 shrink-0 bg-white dark:bg-red-800 rounded-full flex items-center justify-center text-red-500 shadow-sm">
                    <i class="fab fa-google text-3xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-red-800 dark:text-red-400">Autentikasi Google Dibutuhkan</h3>
                    <p class="text-red-600 dark:text-red-300 mt-1">Anda perlu menghubungkan akun Google Anda untuk memberikan izin akses agar sistem dapat membuat link Google Meet secara otomatis melalui Google Calendar Anda.</p>
                </div>
            </div>

            <!-- List Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="j in jadwal" :key="j.id" class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700 overflow-hidden shadow-sm hover:shadow-lg transition-all group relative">
                    <div class="p-5 border-b border-gray-50 dark:border-slate-700/50 flex justify-between items-start relative overflow-hidden">
                        <!-- Decorative background icon -->
                        <i class="fas fa-video absolute -right-4 -bottom-4 text-7xl text-gray-50 dark:text-slate-700/50 opacity-50 group-hover:scale-110 transition-transform duration-500"></i>
                        
                        <div class="relative z-10">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 mb-3">
                                <i class="fas fa-users"></i> {{ j.nama_kelas }}
                            </span>
                            <h3 class="text-xl font-bold text-gray-800 dark:text-white line-clamp-2 leading-tight">{{ j.judul_pertemuan }}</h3>
                        </div>
                    </div>
                    <div class="p-5 bg-gray-50/50 dark:bg-slate-800/50 space-y-3">
                        <div class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                            <div class="w-8 flex justify-center text-emerald-500"><i class="fas fa-calendar-day"></i></div>
                            <span>{{ formatDate(j.tgl_pertemuan) }}</span>
                        </div>
                        <div class="flex items-center gap-3 pt-3">
                            <a :href="j.link_meet" target="_blank" class="flex-1 py-2 px-4 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-bold rounded-xl flex items-center justify-center gap-2 transition-colors">
                                <i class="fas fa-external-link-alt"></i> Join Meet
                            </a>
                            <button @click="deleteJadwal(j.id)" class="w-10 h-10 flex items-center justify-center bg-red-50 text-red-500 hover:bg-red-500 hover:text-white rounded-xl transition-colors">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="jadwal.length === 0" class="col-span-full py-16 flex flex-col items-center justify-center bg-white dark:bg-slate-800 rounded-2xl border border-dashed border-gray-200 dark:border-slate-700">
                    <div class="w-20 h-20 bg-gray-50 dark:bg-slate-700 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-video-slash text-3xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">Belum ada kelas virtual</h3>
                    <p class="text-gray-500 text-sm mt-1">Buat sesi Google Meet baru untuk kelas Anda.</p>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-gray-900/50 backdrop-blur-sm p-4">
            <div class="relative w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl shadow-2xl animate-fade-in-up">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-5 border-b border-gray-100 dark:border-slate-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-video text-blue-500"></i> Buat Meet Baru
                    </h3>
                    <button @click="isModalOpen = false" class="text-gray-400 bg-transparent hover:bg-gray-100 hover:text-gray-900 rounded-lg text-sm w-8 h-8 flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <!-- Form -->
                <form @submit.prevent="submit" class="p-5">
                    <div class="space-y-4">
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-gray-900 dark:text-white">Mata Pelajaran (Judul)</label>
                            <input v-model="form.mapel" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-slate-700 dark:border-slate-600 dark:placeholder-gray-400 dark:text-white" required placeholder="Contoh: Matematika Wajib">
                        </div>
                        
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-gray-900 dark:text-white">Kelas</label>
                            <select v-model="form.kelas_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-slate-700 dark:border-slate-600 dark:text-white" required>
                                <option value="" disabled>Pilih Kelas</option>
                                <option v-for="k in kelas" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block mb-2 text-sm font-semibold text-gray-900 dark:text-white">Jam Mulai</label>
                                <input v-model="form.jam_mulai" type="time" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-slate-700 dark:border-slate-600 dark:text-white" required>
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-semibold text-gray-900 dark:text-white">Jam Selesai</label>
                                <input v-model="form.jam_selesai" type="time" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-slate-700 dark:border-slate-600 dark:text-white" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex items-center justify-end gap-3 pt-5 border-t border-gray-100 dark:border-slate-700">
                        <button type="button" @click="isModalOpen = false" class="px-5 py-2.5 text-sm font-medium text-gray-500 bg-white border border-gray-200 rounded-xl hover:bg-gray-100 hover:text-gray-900 dark:bg-slate-700 dark:text-gray-300 dark:border-slate-600 dark:hover:bg-slate-600 dark:hover:text-white">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 flex items-center gap-2 shadow-md shadow-blue-500/20 disabled:opacity-70">
                            <i class="fas fa-paper-plane"></i> Buat Link Meet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>

<style scoped>
.animate-fade-in-up {
    animation: fadeInUp 0.3s ease-out;
}
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px) scale(0.98);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
</style>
