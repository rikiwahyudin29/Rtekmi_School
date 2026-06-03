<script setup>
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    logs: Array
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Audit Trail & Log Aktivitas" />

    <DashboardLayout>
        <div class="flex flex-col h-full bg-[#f4f6f8] dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full py-6">
                
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
                    <div>
                        <h2 class="font-bold text-2xl text-gray-900 dark:text-white tracking-tight">Audit Trail & Log Aktivitas</h2>
                        <p class="text-sm text-gray-500 mt-1">Pantau seluruh aktivitas keuangan yang dilakukan di sistem.</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 p-6 overflow-hidden">
                    
                    <div class="relative border-l-2 border-indigo-200 dark:border-indigo-900/50 ml-4 mt-4">
                        
                        <div v-for="log in logs" :key="log.id" class="mb-8 ml-6 relative">
                            <!-- Timeline dot -->
                            <span class="absolute -left-[35px] flex items-center justify-center w-8 h-8 bg-indigo-100 rounded-full ring-4 ring-white dark:ring-gray-800 dark:bg-indigo-900/50">
                                <i class="fas" :class="[(log.aksi || log.aktivitas || '').toLowerCase().includes('hapus') ? 'fa-trash text-red-500' : 'fa-info-circle text-indigo-500']"></i>
                            </span>
                            
                            <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-xl border border-gray-100 dark:border-gray-800 shadow-sm">
                                <h3 class="flex items-center mb-1 text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ log.aksi || log.aktivitas || 'Aktivitas tidak diketahui' }}
                                </h3>
                                <div class="flex items-center gap-3 text-xs font-normal text-gray-500 dark:text-gray-400">
                                    <span class="flex items-center gap-1">
                                        <i class="fas fa-clock"></i> {{ formatDate(log.created_at) }}
                                    </span>
                                    <span v-if="log.user" class="flex items-center gap-1">
                                        <i class="fas fa-user-circle"></i> {{ log.user.nama_lengkap || log.user.name || 'User' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div v-if="logs.length === 0" class="text-center py-12 text-gray-500">
                            <i class="fas fa-history text-4xl mb-4 text-gray-300"></i>
                            <p class="text-base font-medium">Belum ada log aktivitas yang tercatat.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </DashboardLayout>
</template>
