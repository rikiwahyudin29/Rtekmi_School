<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    kelas: Array
});

const form = useForm({
    id_kelas: ''
});

const isSending = ref(false);

const submit = () => {
    isSending.value = true;
    form.post('/admin/keuangan/notif-tagihan/kirim', {
        onFinish: () => {
            isSending.value = false;
        }
    });
};
</script>

<template>
    <Head title="Pengingat Tagihan WA" />

    <DashboardLayout>
        <div class="flex flex-col h-full bg-[#f4f6f8] dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full py-6">
                
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
                    <div>
                        <h2 class="font-bold text-2xl text-gray-900 dark:text-white tracking-tight">Blast Pengingat Tagihan WA</h2>
                        <p class="text-sm text-gray-500 mt-1">Kirim notifikasi tagihan ke WhatsApp orang tua siswa per kelas.</p>
                    </div>
                </div>

                <!-- Flash Message -->
                <div v-if="$page.props.flash?.message" class="bg-green-50 border border-green-200 text-green-800 p-4 mb-6 rounded-2xl shadow-sm flex items-center gap-3">
                    <i class="fas fa-check-circle"></i> {{ $page.props.flash.message }}
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    <!-- Form Kirim -->
                    <div class="col-span-1 md:col-span-1 space-y-6">
                        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 p-6">
                            <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-700 pb-3">Pilih Sasaran Kelas</h3>
                            
                            <form @submit.prevent="submit" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Kelas <span class="text-red-500">*</span></label>
                                    <select v-model="form.id_kelas" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-xl focus:ring-rose-500 focus:border-rose-500 transition-colors shadow-sm">
                                        <option value="" disabled>-- Pilih Kelas --</option>
                                        <option v-for="k in kelas" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                                    </select>
                                    <div v-if="form.errors.id_kelas" class="text-red-500 text-xs mt-1">{{ form.errors.id_kelas }}</div>
                                </div>
                                
                                <div class="bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 p-4 rounded-xl text-sm border border-blue-100 dark:border-blue-800">
                                    <i class="fas fa-info-circle mb-1"></i> Sistem akan mengirim WA secara otomatis <b>hanya</b> kepada orang tua dari siswa yang masih memiliki tagihan berstatus <b>BELUM LUNAS</b> atau <b>MENCICIL</b> pada kelas yang dipilih.
                                </div>

                                <button type="submit" :disabled="form.processing || isSending" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-5 rounded-xl transition-all shadow-lg shadow-green-900/20 flex items-center justify-center gap-2 disabled:opacity-50">
                                    <i class="fas" :class="isSending ? 'fa-spinner fa-spin' : 'fa-paper-plane'"></i> 
                                    {{ isSending ? 'Mengirim Pesan...' : 'Kirim Blast Notifikasi WA' }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Preview Pesan -->
                    <div class="col-span-1 md:col-span-2">
                        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 p-6 h-full relative overflow-hidden">
                            <!-- Background pattern for Whatsapp aesthetic -->
                            <div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: url('https://cdn.pixabay.com/photo/2021/08/17/09/16/whatsapp-background-6552504_960_720.png'); background-size: cover;"></div>
                            
                            <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-700 pb-3 relative z-10 flex items-center gap-2">
                                <i class="fab fa-whatsapp text-green-500 text-xl"></i> Preview Format Pesan
                            </h3>
                            
                            <div class="bg-[#dcf8c6] dark:bg-green-900/50 p-5 rounded-2xl rounded-tl-none shadow-sm text-gray-800 dark:text-gray-100 font-mono text-sm max-w-lg relative z-10 mx-auto sm:ml-0 mt-4 border border-green-200/50">
                                <div>⚠️ *INFO TAGIHAN SEKOLAH* ⚠️</div>
                                <br>
                                <div>Yth. Bapak/Ibu Orang Tua/Wali dari:</div>
                                <div>👤 Nama : *[Nama Siswa]*</div>
                                <div>🔖 NIS : [NIS Siswa]</div>
                                <div>🏫 Kelas : [Nama Kelas]</div>
                                <br>
                                <div>Bersama ini kami informasikan rincian tagihan administrasi sekolah yang *belum lunas*:</div>
                                <br>
                                <div>1. SPP Bulanan (Bulan ke-7)</div>
                                <div>&nbsp;&nbsp;&nbsp;Sisa Tagihan: *Rp 150.000*</div>
                                <div>2. Uang Gedung</div>
                                <div>&nbsp;&nbsp;&nbsp;Sisa Tagihan: *Rp 500.000*</div>
                                <br>
                                <div>-----------------------------------</div>
                                <div class="text-red-600 dark:text-red-400">🔴 *TOTAL TUNGGAKAN : Rp 650.000*</div>
                                <br>
                                <div>Mohon berkenan untuk segera melakukan pelunasan tagihan tersebut. Jika Bapak/Ibu sudah melakukan pembayaran, mohon abaikan pesan ini atau konfirmasi dengan mengirimkan bukti transfer.</div>
                                <br>
                                <div>Terima kasih atas kerja samanya.</div>
                                <div class="italic">_Keuangan [Nama Sekolah]_</div>
                                
                                <!-- Read indicator -->
                                <div class="text-right text-[10px] text-green-600 dark:text-green-400 mt-2 flex justify-end items-center gap-1">
                                    10:45 AM <i class="fas fa-check-double text-blue-500"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
