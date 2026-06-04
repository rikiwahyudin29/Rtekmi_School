<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    pendaftar: Object
});

const formStatus = useForm({
    status: props.pendaftar.status_pendaftaran,
    catatan: props.pendaftar.catatan_admin || ''
});

const updateStatus = () => {
    formStatus.post(route('admin.ppdb.updateStatus', props.pendaftar.id), {
        preserveScroll: true
    });
};

const migrateSiswa = () => {
    if(confirm('Apakah Anda yakin ingin memasukkan data pendaftar ini menjadi Siswa Aktif? Aksi ini akan membuat akun siswa di sistem secara otomatis.')) {
        router.post(route('admin.ppdb.migrateToSiswa', props.pendaftar.id));
    }
};

const getStatusBadge = (status) => {
    switch(status) {
        case 'Diterima': return 'bg-emerald-100 text-emerald-700 border-emerald-200';
        case 'Ditolak': return 'bg-red-100 text-red-700 border-red-200';
        case 'Cadangan': return 'bg-amber-100 text-amber-700 border-amber-200';
        default: return 'bg-slate-100 text-slate-700 border-slate-200';
    }
};

const openDoc = (path) => {
    window.open(path, '_blank');
};
</script>

<template>
    <Head title="Verifikasi Pendaftar" />

    <DashboardLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('admin.ppdb.index')" class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 hover:bg-emerald-600 hover:text-white transition-colors">
                    <i class="fas fa-arrow-left"></i>
                </Link>
                <h2 class="font-black text-xl text-slate-800 leading-tight">Detail Pendaftar PPDB</h2>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Kiri: Info Pendaftar & Form Status -->
                    <div class="lg:col-span-1 space-y-6">
                        
                        <!-- Profile Card -->
                        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden text-center relative">
                            <div class="h-24 bg-slate-800"></div>
                            <div class="relative -mt-12 mb-4 flex justify-center">
                                <img v-if="pendaftar.foto && pendaftar.foto !== 'default.png'" :src="`/uploads/ppdb/foto/${pendaftar.foto}`" class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-md">
                                <div v-else class="w-24 h-24 rounded-full bg-slate-200 border-4 border-white shadow-md flex items-center justify-center text-slate-400 text-3xl"><i class="fas fa-user"></i></div>
                            </div>
                            <div class="px-6 pb-6">
                                <h3 class="font-black text-lg text-slate-800 mb-1">{{ pendaftar.nama_lengkap }}</h3>
                                <div class="text-xs font-bold text-slate-500 mb-4">{{ pendaftar.nisn }} &bull; {{ pendaftar.asal_sekolah }}</div>
                                
                                <span class="px-4 py-1.5 rounded-full text-xs font-bold border" :class="getStatusBadge(pendaftar.status_pendaftaran)">
                                    {{ pendaftar.status_pendaftaran }}
                                </span>
                            </div>
                        </div>

                        <!-- Panel Status -->
                        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
                            <h4 class="font-black text-slate-800 mb-4 border-b border-slate-100 pb-2">Ubah Status & Verifikasi</h4>
                            
                            <form @submit.prevent="updateStatus">
                                <div class="mb-4">
                                    <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-widest">Status Keputusan</label>
                                    <select v-model="formStatus.status" class="w-full rounded-xl border-slate-300 focus:border-emerald-500 font-bold text-sm">
                                        <option value="Pending">Pending (Menunggu)</option>
                                        <option value="Diterima">Diterima</option>
                                        <option value="Cadangan">Cadangan</option>
                                        <option value="Ditolak">Ditolak</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-widest">Catatan Panitia (Dikirim via WA)</label>
                                    <textarea v-model="formStatus.catatan" rows="3" class="w-full rounded-xl border-slate-300 focus:border-emerald-500 text-sm" placeholder="Contoh: Dokumen KK kurang jelas..."></textarea>
                                </div>
                                <button type="submit" :disabled="formStatus.processing" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold transition-colors">
                                    <i v-if="formStatus.processing" class="fas fa-spinner fa-spin mr-2"></i> Update Status
                                </button>
                            </form>

                            <div v-if="pendaftar.status_pendaftaran === 'Diterima' && !pendaftar.is_migrated" class="mt-4 pt-4 border-t border-slate-100">
                                <button @click="migrateSiswa" class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition-colors shadow-md shadow-emerald-500/20">
                                    <i class="fas fa-user-check mr-2"></i> Pindahkan ke Siswa Aktif
                                </button>
                            </div>
                            <div v-if="pendaftar.is_migrated" class="mt-4 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-xs font-bold text-center">
                                <i class="fas fa-check-circle mr-1"></i> Data sudah berada di Master Siswa.
                            </div>
                        </div>
                    </div>

                    <!-- Kanan: Detail & Berkas -->
                    <div class="lg:col-span-2 space-y-6">
                        
                        <!-- Berkas Dokumen -->
                        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
                            <h4 class="font-black text-slate-800 mb-4 border-b border-slate-100 pb-2">Dokumen Terlampir</h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="p-4 border border-slate-200 rounded-2xl flex items-center justify-between group hover:border-emerald-500 transition-colors cursor-pointer" @click="openDoc(`/uploads/ppdb/dokumen/${pendaftar.berkas_kk}`)">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-500 flex items-center justify-center text-lg"><i class="fas fa-file-invoice"></i></div>
                                        <div>
                                            <div class="font-bold text-slate-800 text-sm">Kartu Keluarga</div>
                                            <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Klik untuk lihat</div>
                                        </div>
                                    </div>
                                    <i class="fas fa-external-link-alt text-slate-300 group-hover:text-emerald-500 transition-colors"></i>
                                </div>
                                <div v-if="pendaftar.berkas_ijazah" class="p-4 border border-slate-200 rounded-2xl flex items-center justify-between group hover:border-emerald-500 transition-colors cursor-pointer" @click="openDoc(`/uploads/ppdb/dokumen/${pendaftar.berkas_ijazah}`)">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-500 flex items-center justify-center text-lg"><i class="fas fa-certificate"></i></div>
                                        <div>
                                            <div class="font-bold text-slate-800 text-sm">Ijazah / SKL</div>
                                            <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Klik untuk lihat</div>
                                        </div>
                                    </div>
                                    <i class="fas fa-external-link-alt text-slate-300 group-hover:text-emerald-500 transition-colors"></i>
                                </div>
                                <div v-else class="p-4 border border-dashed border-slate-200 rounded-2xl flex items-center gap-3 bg-slate-50 opacity-50">
                                    <div class="w-10 h-10 rounded-lg bg-slate-200 text-slate-400 flex items-center justify-center text-lg"><i class="fas fa-certificate"></i></div>
                                    <div>
                                        <div class="font-bold text-slate-600 text-sm">Ijazah / SKL</div>
                                        <div class="text-[10px] text-slate-400 font-bold">Tidak dilampirkan</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Detail Data -->
                        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
                            <h4 class="font-black text-slate-800 mb-6 border-b border-slate-100 pb-2">Informasi Lengkap</h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h5 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Identitas</h5>
                                    <ul class="space-y-2 text-sm text-slate-700">
                                        <li class="flex justify-between border-b border-slate-50 pb-1"><span class="font-medium text-slate-500">NIK</span> <b>{{ pendaftar.nik }}</b></li>
                                        <li class="flex justify-between border-b border-slate-50 pb-1"><span class="font-medium text-slate-500">TTL</span> <b>{{ pendaftar.tempat_lahir }}, {{ pendaftar.tgl_lahir }}</b></li>
                                        <li class="flex justify-between border-b border-slate-50 pb-1"><span class="font-medium text-slate-500">Gender</span> <b>{{ pendaftar.jk === 'L' ? 'Laki-Laki' : 'Perempuan' }}</b></li>
                                        <li class="flex justify-between border-b border-slate-50 pb-1"><span class="font-medium text-slate-500">Agama</span> <b>{{ pendaftar.agama }}</b></li>
                                    </ul>
                                </div>
                                <div>
                                    <h5 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Pendidikan Terakhir</h5>
                                    <ul class="space-y-2 text-sm text-slate-700">
                                        <li class="flex justify-between border-b border-slate-50 pb-1"><span class="font-medium text-slate-500">Asal Sekolah</span> <b>{{ pendaftar.asal_sekolah }}</b></li>
                                        <li class="flex justify-between border-b border-slate-50 pb-1"><span class="font-medium text-slate-500">Pilihan Jurusan</span> <b>{{ pendaftar.jurusan_minat }}</b></li>
                                        <li class="flex justify-between border-b border-slate-50 pb-1"><span class="font-medium text-slate-500">No. Pendaftaran</span> <b>{{ pendaftar.no_pendaftaran }}</b></li>
                                        <li class="flex justify-between border-b border-slate-50 pb-1"><span class="font-medium text-slate-500">Tanggal Daftar</span> <b>{{ new Date(pendaftar.tgl_daftar).toLocaleDateString('id-ID') }}</b></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="mt-6">
                                <h5 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Kontak & Alamat</h5>
                                <div class="bg-slate-50 p-4 rounded-xl text-sm text-slate-700 leading-relaxed font-medium">
                                    {{ pendaftar.alamat }}
                                </div>
                                <div class="mt-3 flex items-center gap-4 text-sm font-bold text-slate-700">
                                    <div class="flex items-center gap-2"><i class="fab fa-whatsapp text-emerald-500"></i> {{ pendaftar.no_hp_siswa }}</div>
                                </div>
                            </div>

                            <div class="mt-6 border-t border-slate-100 pt-6">
                                <h5 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Data Orang Tua</h5>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-slate-700">
                                    <div class="border border-slate-200 rounded-xl p-4 bg-slate-50">
                                        <div class="font-bold text-slate-800 mb-1">Ayah: {{ pendaftar.nama_ayah || '-' }}</div>
                                        <div class="text-xs text-slate-500">Pekerjaan: {{ pendaftar.pekerjaan_ayah || '-' }}</div>
                                    </div>
                                    <div class="border border-slate-200 rounded-xl p-4 bg-slate-50">
                                        <div class="font-bold text-slate-800 mb-1">Ibu: {{ pendaftar.nama_ibu }}</div>
                                        <div class="text-xs text-slate-500">Pekerjaan: {{ pendaftar.pekerjaan_ibu || '-' }}</div>
                                    </div>
                                </div>
                                <div class="mt-3 text-sm font-bold text-slate-700">
                                    <i class="fas fa-phone-alt text-slate-400 mr-2"></i> Kontak Ortu: {{ pendaftar.no_hp_ortu }}
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </DashboardLayout>
</template>
