<script setup>
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    siswa_binaan: Array,
    absen_hari_ini: Object,
    tanggal: String
});

const selectedDate = ref(props.tanggal);

watch(selectedDate, (newDate) => {
    router.get(route('guru.pkl.monitoring'), { tanggal: newDate }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
});

const minDate = computed(() => {
    if(!props.siswa_binaan || props.siswa_binaan.length === 0) return '';
    return props.siswa_binaan.reduce((min, s) => (s.tgl_mulai && s.tgl_mulai < min) ? s.tgl_mulai : min, props.siswa_binaan[0].tgl_mulai || '2000-01-01');
});
const maxDate = computed(() => {
    if(!props.siswa_binaan || props.siswa_binaan.length === 0) return '';
    return props.siswa_binaan.reduce((max, s) => (s.tgl_selesai && s.tgl_selesai > max) ? s.tgl_selesai : max, props.siswa_binaan[0].tgl_selesai || '2099-12-31');
});

const cetakMulai = ref(minDate.value || new Date().toISOString().split('T')[0]);
const cetakSelesai = ref(maxDate.value || new Date().toISOString().split('T')[0]);

// Watcher to sync refs when props load
watch(() => props.siswa_binaan, () => {
    if(props.siswa_binaan && props.siswa_binaan.length > 0) {
        if(!cetakMulai.value || cetakMulai.value === '2000-01-01') cetakMulai.value = minDate.value;
        if(!cetakSelesai.value || cetakSelesai.value === '2099-12-31') cetakSelesai.value = maxDate.value;
    }
}, { deep: true, immediate: true });

</script>

<template>
    <Head title="Monitoring Kehadiran PKL" />
    
    <DashboardLayout>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-map-marker-alt text-red-500"></i>
                        Monitoring Kehadiran Siswa
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        Pantau absensi harian (Geotagging & Selfie) siswa di lokasi PKL.
                    </p>
                </div>
                
                <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                    <!-- Date Picker View (Harian) -->
                    <div class="relative w-full sm:w-auto" title="Tanggal Monitoring Harian">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-calendar-day text-gray-400"></i>
                        </div>
                        <input type="date" v-model="selectedDate" class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 text-sm font-bold text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                    </div>
                    
                    <!-- Cetak Rekap (Rentang Tanggal) -->
                    <div class="flex flex-col sm:flex-row items-center gap-2 w-full sm:w-auto bg-gray-50 dark:bg-gray-800 p-2 rounded-xl border border-gray-200 dark:border-gray-700">
                        <span class="text-xs font-bold text-gray-500 uppercase hidden md:inline">Rekap:</span>
                        <input type="date" v-model="cetakMulai" :min="minDate" :max="maxDate" class="py-1.5 px-3 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 font-bold focus:ring-2 focus:ring-emerald-500" title="Dari Tanggal">
                        <span class="text-xs text-gray-400 font-bold">S.D</span>
                        <input type="date" v-model="cetakSelesai" :min="minDate" :max="maxDate" class="py-1.5 px-3 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 font-bold focus:ring-2 focus:ring-emerald-500" title="Sampai Tanggal">
                        <a :href="route('guru.pkl.monitoring.cetak') + '?mulai=' + cetakMulai + '&selesai=' + cetakSelesai" target="_blank" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg shadow-sm text-xs flex items-center gap-1 transition-colors whitespace-nowrap">
                            <i class="fas fa-print"></i> Cetak Rekap
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="s in siswa_binaan" :key="s.id" class="bg-white dark:bg-gray-800 rounded-3xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xl flex-shrink-0">
                            {{ s.nama_siswa.charAt(0) }}
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white line-clamp-1" :title="s.nama_siswa">{{ s.nama_siswa }}</h3>
                            <p class="text-xs text-gray-500">{{ s.nis }} - <span class="font-bold text-blue-600">{{ s.nama_dudi }}</span></p>
                        </div>
                    </div>
                    
                    <div class="border-t border-gray-100 dark:border-gray-700 pt-4">
                        <div v-if="absen_hari_ini[s.id]" class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Status Harian</span>
                                <span class="px-2.5 py-1 text-xs font-bold rounded-full" 
                                      :class="{
                                          'bg-emerald-100 text-emerald-700': absen_hari_ini[s.id].status == 'Hadir',
                                          'bg-amber-100 text-amber-700': absen_hari_ini[s.id].status == 'Izin',
                                          'bg-rose-100 text-rose-700': absen_hari_ini[s.id].status == 'Sakit'
                                      }">
                                    {{ absen_hari_ini[s.id].status }}
                                </span>
                            </div>
                            
                            <div v-if="absen_hari_ini[s.id].status == 'Hadir'" class="grid grid-cols-2 gap-2 text-xs">
                                <div class="bg-gray-50 dark:bg-gray-700/50 p-2 rounded-lg text-center">
                                    <div class="text-gray-500 mb-1"><i class="fas fa-sign-in-alt text-emerald-500"></i> Jam Masuk</div>
                                    <div class="font-bold text-gray-900 dark:text-white">{{ absen_hari_ini[s.id].jam_masuk || '--:--' }}</div>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700/50 p-2 rounded-lg text-center">
                                    <div class="text-gray-500 mb-1"><i class="fas fa-sign-out-alt text-amber-500"></i> Jam Pulang</div>
                                    <div class="font-bold text-gray-900 dark:text-white">{{ absen_hari_ini[s.id].jam_pulang || '--:--' }}</div>
                                </div>
                                <div class="col-span-2 mt-1">
                                    <div class="text-gray-500 mb-1"><i class="fas fa-map-pin text-red-500"></i> Jarak ke Lokasi DUDI</div>
                                    <div class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                        <span>{{ absen_hari_ini[s.id].jarak_meter ? absen_hari_ini[s.id].jarak_meter + ' Meter' : 'N/A' }}</span>
                                        <span v-if="absen_hari_ini[s.id].jarak_meter <= s.radius_absen" class="text-emerald-500 text-[10px] bg-emerald-50 px-2 py-0.5 rounded-full"><i class="fas fa-check-circle"></i> Sesuai Radius</span>
                                        <span v-else class="text-rose-500 text-[10px] bg-rose-50 px-2 py-0.5 rounded-full"><i class="fas fa-exclamation-triangle"></i> Di Luar Radius</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div v-if="absen_hari_ini[s.id].status != 'Hadir'" class="bg-gray-50 dark:bg-gray-700/50 p-3 rounded-xl text-sm">
                                <div class="font-bold text-gray-700 dark:text-gray-300 mb-1">Keterangan:</div>
                                <p class="text-gray-600 dark:text-gray-400">{{ absen_hari_ini[s.id].keterangan || '-' }}</p>
                            </div>
                        </div>
                        <div v-else class="py-6 text-center text-gray-500">
                            <i class="fas fa-clock text-3xl mb-2 text-gray-300"></i>
                            <p class="text-sm">Belum melakukan absensi hari ini.</p>
                        </div>
                    </div>
                </div>
                
                <div v-if="siswa_binaan.length === 0" class="col-span-1 md:col-span-2 lg:col-span-3 py-12 text-center text-gray-500 bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <i class="fas fa-users-slash text-4xl mb-3 text-gray-300"></i>
                    <p>Tidak ada data siswa binaan aktif.</p>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
