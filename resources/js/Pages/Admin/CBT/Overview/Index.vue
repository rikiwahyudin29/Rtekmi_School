<script setup>
import { ref, onMounted, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import axios from 'axios';

const props = defineProps({
    page_title: String,
});

// Dashboard Data
const data = ref({
    ujian_today: 0,
    ujian_active: 0,
    ujiansiswa: 0,
    jawaban: 0,
    passcode_locked_app: '123456'
});

// Schedules Data
const schedules = ref([]);
const isLoading = ref(true);

// Calendar State
const currentDate = ref(new Date());
const selectedDate = ref(new Date());

const loadData = async () => {
    try {
        const startDate = Math.floor(new Date().setHours(0, 0, 0, 0) / 1000);
        const endDate = Math.floor(new Date().setHours(23, 59, 59, 999) / 1000);
        
        const response = await axios.get(route('admin.cbt.overview.data'), {
            params: { startDate, endDate }
        });
        
        data.value = response.data;
    } catch (error) {
        console.error("Gagal memuat data dashboard:", error);
    }
};

const refreshPasscodeApp = async () => {
    try {
        const response = await axios.get(route('admin.cbt.overview.change-passcode'));
        data.value.passcode_locked_app = response.data.passcode_locked_app;
    } catch (error) {
        console.error("Gagal mengubah passcode:", error);
    }
};

const loadSchedules = async () => {
    isLoading.value = true;
    try {
        // Fetch -1 month to +1 month to cover all visible calendar days
        const startDate = Math.floor(new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1).setHours(0, 0, 0, 0) / 1000);
        const endDate = Math.floor(new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 2, 0).setHours(23, 59, 59, 999) / 1000);
        
        const response = await axios.get(route('admin.cbt.jadwal-ujian.all'), {
            params: { startDate, endDate }
        });

        schedules.value = response.data.data.filter(v => v.status === 1);
    } catch (error) {
        console.error("Error fetching schedules:", error);
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    loadData();
    loadSchedules();
});

// Calendar Logic
const daysInMonth = computed(() => {
    const year = currentDate.value.getFullYear();
    const month = currentDate.value.getMonth();
    const date = new Date(year, month, 1);
    const days = [];
    
    // Fill empty days from previous month
    const firstDay = date.getDay() === 0 ? 6 : date.getDay() - 1; // Start Monday
    for (let i = 0; i < firstDay; i++) {
        days.push(null);
    }
    
    // Fill days of current month
    while (date.getMonth() === month) {
        days.push(new Date(date));
        date.setDate(date.getDate() + 1);
    }
    
    return days;
});

const isToday = (date) => {
    if (!date) return false;
    const today = new Date();
    return date.getDate() === today.getDate() &&
           date.getMonth() === today.getMonth() &&
           date.getFullYear() === today.getFullYear();
};

const isSelected = (date) => {
    if (!date) return false;
    return date.getDate() === selectedDate.value.getDate() &&
           date.getMonth() === selectedDate.value.getMonth() &&
           date.getFullYear() === selectedDate.value.getFullYear();
};

const selectDate = (date) => {
    if (date) {
        selectedDate.value = date;
    }
};

const prevMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1);
    loadSchedules();
};

const nextMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1);
    loadSchedules();
};

const hasSchedule = (date) => {
    if (!date) return false;
    return schedules.value.some(schedule => {
        const scheduleDate = new Date(schedule.startDateTime * 1000);
        return scheduleDate.getDate() === date.getDate() &&
               scheduleDate.getMonth() === date.getMonth() &&
               scheduleDate.getFullYear() === date.getFullYear();
    });
};

const getSchedulesForDate = (date) => {
    if (!date) return [];
    return schedules.value.filter(schedule => {
        const scheduleDate = new Date(schedule.startDateTime * 1000);
        return scheduleDate.getDate() === date.getDate() &&
               scheduleDate.getMonth() === date.getMonth() &&
               scheduleDate.getFullYear() === date.getFullYear();
    }).sort((a, b) => a.startDateTime - b.startDateTime);
};

const selectedDateSchedules = computed(() => {
    return getSchedulesForDate(selectedDate.value);
});

const formatTime = (unix) => {
    const d = new Date(unix * 1000);
    return String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
};

const formatFullDate = (date) => {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    return date.toLocaleDateString('id-ID', options);
};

const currentMonthYear = computed(() => {
    const options = { month: 'long', year: 'numeric' };
    return currentDate.value.toLocaleDateString('id-ID', options);
});

</script>

<template>
    <DashboardLayout>
        <Head :title="page_title" />
        
        <div class="px-4 py-6 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="mb-8">
                <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Overview Ujian</h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Pantau seluruh aktivitas ujian, metrik harian, dan kelola jadwal dengan mudah.
                </p>
            </div>

            <!-- Modern Metric Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
                <!-- Ujian Hari Ini -->
                <div class="relative overflow-hidden rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-shadow duration-300" style="background: linear-gradient(to bottom right, #8b5cf6, #c026d3);">
                    <div class="relative z-10 flex flex-col h-full justify-between">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-white/80 text-sm font-medium">Ujian Hari Ini</h3>
                            <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                                <i class="fas fa-calendar-day text-lg"></i>
                            </div>
                        </div>
                        <p class="text-3xl font-bold">{{ data.ujian_today }}</p>
                    </div>
                    <!-- Decorative Circle -->
                    <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/10 blur-xl"></div>
                </div>

                <!-- Ujian Aktif -->
                <div class="relative overflow-hidden rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-shadow duration-300" style="background: linear-gradient(to bottom right, #34d399, #14b8a6);">
                    <div class="relative z-10 flex flex-col h-full justify-between">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-white/80 text-sm font-medium">Ujian Aktif</h3>
                            <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                                <i class="fas fa-play-circle text-lg"></i>
                            </div>
                        </div>
                        <p class="text-3xl font-bold">{{ data.ujian_active }}</p>
                    </div>
                    <div class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full bg-white/10 blur-xl"></div>
                </div>

                <!-- Data Peserta -->
                <div class="relative overflow-hidden rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-shadow duration-300" style="background: linear-gradient(to bottom right, #fbbf24, #f97316);">
                    <div class="relative z-10 flex flex-col h-full justify-between">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-white/80 text-sm font-medium">Data Peserta</h3>
                            <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                                <i class="fas fa-users text-lg"></i>
                            </div>
                        </div>
                        <p class="text-3xl font-bold">{{ data.ujiansiswa }}</p>
                    </div>
                    <div class="absolute -left-6 -top-6 w-24 h-24 rounded-full bg-white/10 blur-xl"></div>
                </div>

                <!-- Data Jawaban -->
                <div class="relative overflow-hidden rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-shadow duration-300" style="background: linear-gradient(to bottom right, #3b82f6, #4f46e5);">
                    <div class="relative z-10 flex flex-col h-full justify-between">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-white/80 text-sm font-medium">Data Jawaban</h3>
                            <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                                <i class="fas fa-check-double text-lg"></i>
                            </div>
                        </div>
                        <p class="text-3xl font-bold">{{ data.jawaban }}</p>
                    </div>
                    <div class="absolute -left-6 -bottom-6 w-24 h-24 rounded-full bg-white/10 blur-xl"></div>
                </div>

                <!-- Kode Aplikasi -->
                <div @click="refreshPasscodeApp" class="relative overflow-hidden rounded-2xl p-6 shadow-lg hover:shadow-xl hover:scale-[1.02] transition-all duration-300 cursor-pointer group" style="background-color: #111827;">
                    <div class="relative z-10 flex flex-col h-full justify-between">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-medium" style="color: #9ca3af;">Kode Aplikasi</h3>
                            <div class="p-2 rounded-lg backdrop-blur-sm transition-colors" style="background-color: rgba(255,255,255,0.1);">
                                <i class="fas fa-sync-alt text-lg group-hover:animate-spin" style="color: #ffffff;"></i>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-key" style="color: #818cf8;"></i>
                            <p class="text-3xl font-bold" style="color: #ffffff;">{{ data.passcode_locked_app }}</p>
                        </div>
                    </div>
                    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-32 h-32 rounded-full blur-2xl" style="background-color: rgba(99, 102, 241, 0.2);"></div>
                </div>
            </div>

            <!-- Calendar & Timeline Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left: Native Calendar Widget -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 sticky top-6">
                        <!-- Calendar Header -->
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white capitalize">{{ currentMonthYear }}</h2>
                            <div class="flex gap-2">
                                <button @click="prevMonth" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 transition-colors">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button @click="nextMonth" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 transition-colors">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Calendar Grid -->
                        <div class="grid grid-cols-7 gap-1 text-center mb-2">
                            <div class="text-xs font-semibold text-gray-400 pb-2">Sen</div>
                            <div class="text-xs font-semibold text-gray-400 pb-2">Sel</div>
                            <div class="text-xs font-semibold text-gray-400 pb-2">Rab</div>
                            <div class="text-xs font-semibold text-gray-400 pb-2">Kam</div>
                            <div class="text-xs font-semibold text-gray-400 pb-2">Jum</div>
                            <div class="text-xs font-semibold text-gray-400 pb-2">Sab</div>
                            <div class="text-xs font-semibold text-red-400 pb-2">Min</div>
                        </div>
                        <div class="grid grid-cols-7 gap-1 text-sm">
                            <div v-for="(day, index) in daysInMonth" :key="index" class="aspect-square flex justify-center items-center relative">
                                <button v-if="day" 
                                        @click="selectDate(day)"
                                        :class="[
                                            'w-8 h-8 rounded-full flex items-center justify-center transition-all duration-200 focus:outline-none',
                                            isSelected(day) ? 'bg-indigo-600 text-white font-bold shadow-md' : 
                                            isToday(day) ? 'bg-indigo-50 text-indigo-600 font-bold dark:bg-indigo-900/40 dark:text-indigo-400' : 
                                            'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white',
                                            day.getDay() === 0 && !isSelected(day) && !isToday(day) ? 'text-red-500 dark:text-red-400' : ''
                                        ]">
                                    {{ day.getDate() }}
                                </button>
                                <!-- Event Dot Indicator -->
                                <div v-if="day && hasSchedule(day)" 
                                     class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-1 h-1 rounded-full bg-indigo-500">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Daily Timeline / Agenda -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 min-h-[500px]">
                        <div class="flex items-center justify-between mb-8 pb-4 border-b border-gray-100 dark:border-gray-700">
                            <div>
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Agenda Ujian</h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ formatFullDate(selectedDate) }}</p>
                            </div>
                            <div v-if="isLoading" class="flex items-center text-indigo-500 text-sm font-medium">
                                <i class="fas fa-spinner fa-spin mr-2"></i> Memuat...
                            </div>
                            <div v-else class="bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 px-3 py-1 rounded-full text-xs font-semibold">
                                {{ selectedDateSchedules.length }} Jadwal
                            </div>
                        </div>

                        <!-- Timeline List -->
                        <div v-if="!isLoading && selectedDateSchedules.length > 0" class="relative">
                            <!-- Vertical Line -->
                            <div class="absolute left-[39px] top-4 bottom-4 w-px bg-gray-200 dark:bg-gray-700 hidden sm:block"></div>
                            
                            <div class="space-y-6">
                                <div v-for="schedule in selectedDateSchedules" :key="schedule.id" class="relative flex flex-col sm:flex-row gap-4 sm:gap-6 group">
                                    <!-- Time column -->
                                    <div class="sm:w-20 shrink-0 pt-1 text-left sm:text-right flex sm:flex-col items-center sm:items-end gap-2 sm:gap-0 z-10">
                                        <div class="text-sm font-bold text-gray-900 dark:text-white">{{ formatTime(schedule.startDateTime) }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ formatTime(schedule.endDateTime) }}</div>
                                        
                                        <!-- Node dot (visible on sm+) -->
                                        <div class="hidden sm:flex absolute left-[35px] mt-1.5 w-2.5 h-2.5 rounded-full bg-indigo-500 ring-4 ring-white dark:ring-gray-800 transition-transform duration-300 group-hover:scale-150"></div>
                                    </div>
                                    
                                    <!-- Content Card -->
                                    <div class="flex-1">
                                        <Link :href="route('admin.cbt.jadwal-ujian.detail', schedule.id)" class="block bg-gray-50 dark:bg-gray-700/30 hover:bg-white dark:hover:bg-gray-700 border border-gray-100 dark:border-gray-600 hover:border-indigo-200 dark:hover:border-indigo-500/50 rounded-xl p-4 transition-all duration-300 hover:shadow-md">
                                            <div class="flex flex-wrap items-start justify-between gap-2 mb-2">
                                                <h3 class="font-bold text-gray-900 dark:text-white">{{ schedule.kode_mapel }}</h3>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-200 text-gray-800 dark:bg-gray-600 dark:text-gray-200">
                                                    {{ schedule.kode_jenis }}
                                                </span>
                                            </div>
                                            
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ schedule.nama_draft }}</p>
                                            
                                            <div class="flex flex-wrap items-center gap-3 text-xs">
                                                <div class="flex items-center text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 px-2.5 py-1 rounded-md">
                                                    <i class="fas fa-door-open mr-1.5"></i>
                                                    <span class="font-medium">[{{ schedule.kode_ruang }}] {{ schedule.nama_ruang }}</span>
                                                </div>
                                                <div class="flex items-center text-gray-500 dark:text-gray-400">
                                                    <i class="fas fa-clock mr-1.5"></i>
                                                    {{ Math.round((schedule.endDateTime - schedule.startDateTime) / 60) }} Menit
                                                </div>
                                            </div>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Empty State -->
                        <div v-else-if="!isLoading" class="flex flex-col items-center justify-center h-64 text-center">
                            <div class="w-16 h-16 bg-gray-50 dark:bg-gray-800 rounded-full flex items-center justify-center mb-4 border border-gray-100 dark:border-gray-700">
                                <i class="fas fa-calendar-times text-2xl text-gray-400"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Tidak Ada Ujian</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tidak ada jadwal ujian yang diselenggarakan pada tanggal ini.</p>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </DashboardLayout>
</template>

<style scoped>
/* Fallback CSS for missing Tailwind classes (in case Vite is not running) */
.grid-cols-7 {
    display: grid !important;
    grid-template-columns: repeat(7, minmax(0, 1fr)) !important;
}
.grid-cols-5 {
    display: grid !important;
    grid-template-columns: repeat(5, minmax(0, 1fr)) !important;
}
.grid-cols-1 {
    display: grid !important;
    grid-template-columns: repeat(1, minmax(0, 1fr)) !important;
}
@media (min-width: 1024px) {
    .lg\:grid-cols-5 {
        grid-template-columns: repeat(5, minmax(0, 1fr)) !important;
    }
    .lg\:grid-cols-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    }
    .lg\:col-span-1 {
        grid-column: span 1 / span 1 !important;
    }
    .lg\:col-span-2 {
        grid-column: span 2 / span 2 !important;
    }
}
@media (min-width: 768px) {
    .md\:grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    }
}

/* Gradients */
.from-violet-500 { --tw-gradient-from: #8b5cf6 var(--tw-gradient-from-position); --tw-gradient-to: rgb(139 92 246 / 0) var(--tw-gradient-to-position); --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
.to-fuchsia-600 { --tw-gradient-to: #c026d3 var(--tw-gradient-to-position); }
.from-emerald-400 { --tw-gradient-from: #34d399 var(--tw-gradient-from-position); --tw-gradient-to: rgb(52 211 153 / 0) var(--tw-gradient-to-position); --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
.to-teal-500 { --tw-gradient-to: #14b8a6 var(--tw-gradient-to-position); }
.from-amber-400 { --tw-gradient-from: #fbbf24 var(--tw-gradient-from-position); --tw-gradient-to: rgb(251 191 36 / 0) var(--tw-gradient-to-position); --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
.to-orange-500 { --tw-gradient-to: #f97316 var(--tw-gradient-to-position); }
.from-blue-500 { --tw-gradient-from: #3b82f6 var(--tw-gradient-from-position); --tw-gradient-to: rgb(59 130 246 / 0) var(--tw-gradient-to-position); --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
.to-indigo-600 { --tw-gradient-to: #4f46e5 var(--tw-gradient-to-position); }
.bg-gradient-to-br { background-image: linear-gradient(to bottom right, var(--tw-gradient-stops)) !important; }

/* Aspect Square */
.aspect-square { aspect-ratio: 1 / 1 !important; }

/* Fallback Colors */
.bg-gray-900 { background-color: #111827 !important; }
.bg-gray-50 { background-color: #f9fafb !important; }
.bg-gray-200 { background-color: #e5e7eb !important; }
.bg-indigo-600 { background-color: #4f46e5 !important; }
.bg-indigo-500 { background-color: #6366f1 !important; }
.bg-indigo-50 { background-color: #eef2ff !important; }
.text-white { color: #ffffff !important; }
.text-gray-900 { color: #111827 !important; }
.text-gray-800 { color: #1f2937 !important; }
.text-gray-700 { color: #374151 !important; }
.text-gray-600 { color: #4b5563 !important; }
.text-gray-500 { color: #6b7280 !important; }
.text-gray-400 { color: #9ca3af !important; }
.text-indigo-600 { color: #4f46e5 !important; }
.text-indigo-500 { color: #6366f1 !important; }
.text-indigo-400 { color: #818cf8 !important; }
.text-red-500 { color: #ef4444 !important; }
.border-gray-100 { border-color: #f3f4f6 !important; }
.border-gray-200 { border-color: #e5e7eb !important; }
</style>
