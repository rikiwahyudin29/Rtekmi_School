<script setup>
import { useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    bulan: String,
    map: Object,
    total: Object,
    jml_hari: [String, Number]
});

const form = useForm({
    bulan: props.bulan
});

const filterData = () => {
    form.get(route('siswa.presensi.rekap'));
};

const cetakRekap = () => {
    window.open(route('siswa.presensi.cetak_rekap', { bulan: form.bulan }), '_blank');
};
</script>

<template>
    <DashboardLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Rekapitulasi Bulanan (Siswa)</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="filterData" class="flex flex-col sm:flex-row gap-4 items-end mb-6 bg-gray-50 p-4 rounded-lg">
                            <div class="w-full sm:w-1/3">
                                <label class="block text-sm font-medium text-gray-700">Pilih Bulan</label>
                                <input type="month" v-model="form.bulan" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded shadow hover:bg-indigo-700">
                                    <i class="fas fa-search mr-2"></i> Tampilkan
                                </button>
                                <button type="button" @click="cetakRekap" class="bg-green-600 text-white px-4 py-2 rounded shadow hover:bg-green-700">
                                    <i class="fas fa-print mr-2"></i> Cetak PDF
                                </button>
                            </div>
                        </form>

                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-8">
                            <div class="bg-green-100 p-4 rounded-lg text-center border border-green-200">
                                <div class="text-3xl font-bold text-green-600">{{ total.H }}</div>
                                <div class="text-sm font-semibold text-green-800">Hadir</div>
                            </div>
                            <div class="bg-yellow-100 p-4 rounded-lg text-center border border-yellow-200">
                                <div class="text-3xl font-bold text-yellow-600">{{ total.S }}</div>
                                <div class="text-sm font-semibold text-yellow-800">Sakit</div>
                            </div>
                            <div class="bg-blue-100 p-4 rounded-lg text-center border border-blue-200">
                                <div class="text-3xl font-bold text-blue-600">{{ total.I }}</div>
                                <div class="text-sm font-semibold text-blue-800">Izin</div>
                            </div>
                            <div class="bg-red-100 p-4 rounded-lg text-center border border-red-200">
                                <div class="text-3xl font-bold text-red-600">{{ total.A }}</div>
                                <div class="text-sm font-semibold text-red-800">Alpha</div>
                            </div>
                            <div class="bg-orange-100 p-4 rounded-lg text-center border border-orange-200">
                                <div class="text-3xl font-bold text-orange-600">{{ total.T }}</div>
                                <div class="text-sm font-semibold text-orange-800">Terlambat</div>
                            </div>
                        </div>

                        <h3 class="text-lg font-bold mb-4">Detail Harian</h3>
                        <div class="grid grid-cols-4 sm:grid-cols-7 lg:grid-cols-10 gap-2">
                            <div v-for="d in parseInt(jml_hari)" :key="d" 
                                class="p-3 border rounded-lg text-center flex flex-col items-center justify-center min-h-[80px]"
                                :class="{
                                    'bg-green-50 border-green-200': map[d] === 'Hadir',
                                    'bg-orange-50 border-orange-200': map[d] === 'Terlambat',
                                    'bg-yellow-50 border-yellow-200': map[d] === 'Sakit' || map[d] === 'Izin',
                                    'bg-red-50 border-red-200': map[d] === 'Alpha',
                                    'bg-gray-100 border-gray-200': map[d] === '-'
                                }">
                                <div class="text-xs font-bold text-gray-500 mb-1">Tgl {{ d }}</div>
                                <div class="font-bold text-sm"
                                    :class="{
                                        'text-green-600': map[d] === 'Hadir',
                                        'text-orange-600': map[d] === 'Terlambat',
                                        'text-yellow-600': map[d] === 'Sakit' || map[d] === 'Izin',
                                        'text-red-600': map[d] === 'Alpha',
                                        'text-gray-400': map[d] === '-'
                                    }">
                                    <span v-if="map[d] === 'Hadir'">H</span>
                                    <span v-else-if="map[d] === 'Terlambat'">T</span>
                                    <span v-else-if="map[d] === 'Sakit'">S</span>
                                    <span v-else-if="map[d] === 'Izin'">I</span>
                                    <span v-else-if="map[d] === 'Alpha'">A</span>
                                    <span v-else>-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </DashboardLayout>
</template>
