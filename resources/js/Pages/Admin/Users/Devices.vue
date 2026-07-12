<template>
    <DashboardLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col h-[calc(100vh-10rem)]">
                
                <!-- Header -->
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 shrink-0 gap-4">
                    <div>
                        <h2 class="font-bold text-2xl text-gray-900 dark:text-white tracking-tight">Manajemen Device</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kelola perangkat dan akses login pengguna aplikasi.</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-xl sm:rounded-3xl border border-gray-100 dark:border-gray-700">
                    <div class="p-6 text-slate-900 dark:text-slate-100">
                        
                        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                            <!-- Dropdown Pagination -->
                            <div class="flex items-center gap-2">
                                <label for="per_page" class="text-sm text-slate-600 dark:text-slate-400">Tampilkan:</label>
                                <select id="per_page" v-model="form.per_page" @change="fetchData" class="border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="10">10 Baris</option>
                                    <option value="25">25 Baris</option>
                                    <option value="50">50 Baris</option>
                                    <option value="100">100 Baris</option>
                                </select>
                            </div>

                            <!-- Search -->
                            <div class="w-full md:w-1/3">
                                <input 
                                    type="text" 
                                    v-model="form.search" 
                                    @keyup.enter="fetchData"
                                    placeholder="Cari nama, username, atau nama device..." 
                                    class="w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                >
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-700">
                            <table class="w-full text-sm text-left text-slate-500 dark:text-slate-400">
                                <thead class="text-xs text-slate-700 uppercase bg-slate-50 dark:bg-slate-700 dark:text-slate-400">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Pengguna</th>
                                        <th scope="col" class="px-6 py-3">Role</th>
                                        <th scope="col" class="px-6 py-3">Device Name</th>
                                        <th scope="col" class="px-6 py-3">Waktu Login</th>
                                        <th scope="col" class="px-6 py-3">Lokasi (Lat, Lng)</th>
                                        <th scope="col" class="px-6 py-3">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="device in devices.data" :key="device.id" class="bg-white border-b dark:bg-slate-800 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600">
                                        <td class="px-6 py-4 font-medium text-slate-900 whitespace-nowrap dark:text-white">
                                            {{ device.user?.nama_lengkap || 'Unknown' }}
                                            <div class="text-xs text-slate-500">{{ device.user?.username }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                {{ device.user?.role?.toUpperCase() || '-' }}
                                            </span>
                                            <div class="mt-2 text-xs text-slate-500 cursor-pointer hover:text-indigo-500 flex items-center gap-1" @click="editMaxDevice(device.user)" v-if="device.user && device.user.role !== 'admin'">
                                                <i class="fas fa-mobile-alt"></i> Max: {{ device.user.max_device ?? (device.user.role === 'siswa' ? 1 : 2) }}
                                                <i class="fas fa-edit ml-1"></i>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ device.device_name || 'Mobile Device' }}
                                            <div class="text-xs text-slate-500">IP: {{ device.last_ip }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ new Date(device.last_login_at).toLocaleString('id-ID') }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <div v-if="device.latitude && device.longitude">
                                                <a :href="`https://www.google.com/maps?q=${device.latitude},${device.longitude}`" target="_blank" class="text-blue-600 hover:underline inline-flex items-center gap-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                                    Lihat Map
                                                </a>
                                                <div class="text-xs text-slate-500 mt-1">
                                                    {{ device.latitude.substring(0,8) }}, {{ device.longitude.substring(0,8) }}
                                                </div>
                                            </div>
                                            <div v-else class="text-slate-400 italic text-xs">
                                                Lokasi belum tersedia
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <button @click="resetDevice(device.id)" class="px-3 py-1 text-xs font-medium text-white bg-red-600 hover:bg-red-700 rounded transition-colors">
                                                Reset
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="devices.data.length === 0">
                                        <td colspan="6" class="px-6 py-4 text-center text-slate-500">
                                            Tidak ada data perangkat ditemukan.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Default Laravel Inertia -->
                        <div class="mt-4 flex items-center justify-between" v-if="devices.links.length > 3">
                            <div class="flex flex-wrap gap-1">
                                <template v-for="(link, p) in devices.links" :key="p">
                                    <div v-if="link.url === null" class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-slate-400 border rounded" v-html="link.label" />
                                    <Link v-else class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-slate-200 dark:hover:bg-slate-600 focus:border-indigo-500 focus:text-indigo-500 transition-colors" :class="{ 'bg-blue-600 text-white border-blue-600 hover:bg-blue-700': link.active, 'dark:text-slate-300 dark:border-slate-700': !link.active }" :href="link.url + '&per_page=' + form.per_page + (form.search ? '&search=' + form.search : '')" v-html="link.label" />
                                </template>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    devices: Object,
    filters: Object,
});

const form = ref({
    search: props.filters.search || '',
    per_page: props.filters.per_page || '10',
});

const fetchData = () => {
    router.get(route('admin.user-devices.index'), {
        search: form.value.search,
        per_page: form.value.per_page,
    }, {
        preserveState: true,
        replace: true
    });
};

const resetDevice = (id) => {
    window.Swal.fire({
        title: 'Reset Perangkat?',
        text: "Perangkat ini akan dihapus. Pengguna harus login ulang di perangkat baru dan akan terikat dengan perangkat tersebut.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Reset!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('admin.user-devices.destroy', id), {
                onSuccess: () => {
                    window.Swal.fire('Berhasil!', 'Perangkat telah direset.', 'success');
                }
            });
        }
    });
};

const editMaxDevice = (user) => {
    const currentMax = user.max_device ?? (user.role === 'siswa' ? 1 : 2);
    window.Swal.fire({
        title: 'Atur Batas Perangkat',
        text: `Berapa maksimal perangkat untuk ${user.nama_lengkap}?`,
        input: 'number',
        inputValue: currentMax,
        showCancelButton: true,
        confirmButtonText: 'Simpan',
        cancelButtonText: 'Batal',
        inputValidator: (value) => {
            if (!value || value < 1) {
                return 'Harus lebih dari 0!'
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('admin.user-devices.set-max'), {
                user_id: user.id,
                max_device: result.value
            }, {
                preserveScroll: true,
                onSuccess: () => {
                    window.Swal.fire('Tersimpan!', 'Batas perangkat berhasil diperbarui.', 'success');
                }
            });
        }
    });
};
</script>
