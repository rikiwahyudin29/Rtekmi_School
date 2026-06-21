<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    siswa: Object,
    detail: Object,
    kepribadian: Object,
    bakat: Array,
    penerimaan: Array
});

const form = useForm({
    siswa_id: props.siswa.id,
    
    // A. Pribadi
    nama_panggilan: props.detail.nama_panggilan || '',
    jml_saudara_kandung: props.detail.jml_saudara_kandung || 0,
    jml_saudara_tiri: props.detail.jml_saudara_tiri || 0,
    jml_saudara_angkat: props.detail.jml_saudara_angkat || 0,
    status_yatim_piatu: props.detail.status_yatim_piatu || '-',
    bahasa_sehari_hari: props.detail.bahasa_sehari_hari || 'Bahasa Indonesia',
    
    // B. Tinggal
    tinggal_bersama: props.detail.tinggal_bersama || 'Orang Tua',
    jarak_ke_sekolah: props.detail.jarak_ke_sekolah || '',
    transportasi: props.detail.transportasi || '',
    
    // C. Fisik
    gol_darah: props.detail.gol_darah || '-',
    penyakit_pernah_diderita: props.detail.penyakit_pernah_diderita || '',
    kelainan_jasmani: props.detail.kelainan_jasmani || '-',
    tinggi_badan: props.detail.tinggi_badan || 0,
    berat_badan: props.detail.berat_badan || 0,
    tinggi_meninggalkan: props.detail.tinggi_meninggalkan || 0,
    berat_meninggalkan: props.detail.berat_meninggalkan || 0,
    
    // D. SMP
    tgl_diterima: props.detail.tgl_diterima || new Date().toISOString().slice(0, 10),
    no_sttb_smp: props.detail.no_sttb_smp || '',
    tgl_sttb_smp: props.detail.tgl_sttb_smp || '',
    lama_belajar_smp: props.detail.lama_belajar_smp || '',
    
    // E. Ortu
    pendidikan_ayah: props.detail.pendidikan_ayah || '',
    penghasilan_ayah: props.detail.penghasilan_ayah || '',
    pendidikan_ibu: props.detail.pendidikan_ibu || '',
    penghasilan_ibu: props.detail.penghasilan_ibu || '',
    pendidikan_wali: props.detail.pendidikan_wali || '',
    penghasilan_wali: props.detail.penghasilan_wali || '',
    
    // F. Lulus
    tgl_meninggalkan: props.detail.tgl_meninggalkan || '',
    alasan_meninggalkan: props.detail.alasan_meninggalkan || '',
    no_ijazah_smk: props.detail.no_ijazah_smk || '',
    
    // G. Kepribadian
    iq: props.detail.iq || '',
    tgl_tes_iq: props.detail.tgl_tes_iq || '',
    kepribadian: props.kepribadian || {},
    
    // H. Bakat
    bakat: props.bakat && props.bakat.length > 0 ? props.bakat : Array(7).fill({}),
    
    // I. Prestasi Khusus
    prestasi_siswa: props.detail.prestasi_siswa || '',
    
    // J. Beasiswa
    penerimaan: props.penerimaan && props.penerimaan.length > 0 ? props.penerimaan : Array(4).fill({})
});

const submitForm = () => {
    form.post(route('admin.kesiswaan.buku_induk.simpan_detail'), {
        preserveScroll: true
    });
};

const kepribadianOptions = [
    { key: 'disiplin', label: 'Disiplin / Tertib' },
    { key: 'kreativitas', label: 'Prakarsa / Kreatif' },
    { key: 'tanggung_jawab', label: 'Tanggung Jawab' },
    { key: 'penyesuaian', label: 'Penyesuaian Diri' },
    { key: 'emosi', label: 'Kemantapan Emosi' },
    { key: 'kerjasama', label: 'Kerjasama' }
];

// Helper for initialzing matrix if undefined
kepribadianOptions.forEach(opt => {
    if (!form.kepribadian[opt.key]) {
        form.kepribadian[opt.key] = {};
        for (let i = 1; i <= 6; i++) {
            form.kepribadian[opt.key][i] = { B: '', C: '', K: '' };
        }
    }
});

for (let i = 1; i <= 6; i++) {
    if (!form.bakat[i]) form.bakat[i] = { smth: '', kesenian: '', olahraga: '', organisasi: '', hasta_karya: '', karya_tulis: '' };
}

for (let i = 1; i <= 3; i++) {
    if (!form.penerimaan[i]) form.penerimaan[i] = { tahun: '', dari: '' };
}

</script>

<template>
    <Head title="Kelengkapan Buku Induk" />
    
    <DashboardLayout>
        <div class="space-y-6 max-w-7xl mx-auto">
            
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-3 uppercase">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                            <i class="fas fa-user-edit"></i>
                        </div>
                        Lengkapi Data Buku Induk
                    </h2>
                    <p class="text-sm font-bold text-gray-500 mt-2 ml-13">
                        <span class="text-gray-900 dark:text-gray-200">{{ siswa.nama_lengkap }}</span> | 
                        NIS: <span class="text-blue-600">{{ siswa.nis }}</span> | 
                        Kelas: <span class="text-indigo-600">{{ siswa.nama_kelas || '-' }}</span>
                    </p>
                </div>
                <Link :href="route('admin.kesiswaan.buku_induk.index')" class="px-5 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl font-bold transition-colors shadow-sm flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Kembali
                </Link>
            </div>

            <div v-if="$page.props.flash?.success" class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-6 py-4 rounded-2xl flex items-center gap-3 font-bold shadow-sm">
                <i class="fas fa-check-circle text-xl text-emerald-500"></i>
                {{ $page.props.flash.success }}
            </div>

            <form @submit.prevent="submitForm" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden relative">
                
                <div class="p-6 md:p-8 grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
                    
                    <!-- Kiri -->
                    <div class="space-y-8">
                        
                        <!-- A. Pribadi -->
                        <div class="bg-gray-50 dark:bg-gray-800/50 p-6 rounded-2xl border border-gray-100 dark:border-gray-700">
                            <h3 class="font-bold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-600 pb-3 mb-5 flex items-center gap-2">
                                <i class="fas fa-users text-indigo-500"></i> A. Pribadi & Keluarga
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Nama Panggilan</label>
                                    <input type="text" v-model="form.nama_panggilan" class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                </div>
                                <div class="grid grid-cols-3 gap-3">
                                    <div><label class="block text-[10px] font-bold text-gray-700 dark:text-gray-300 mb-1">Jml Sdr Kandung</label><input type="number" v-model="form.jml_saudara_kandung" class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm"></div>
                                    <div><label class="block text-[10px] font-bold text-gray-700 dark:text-gray-300 mb-1">Jml Sdr Tiri</label><input type="number" v-model="form.jml_saudara_tiri" class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm"></div>
                                    <div><label class="block text-[10px] font-bold text-gray-700 dark:text-gray-300 mb-1">Jml Sdr Angkat</label><input type="number" v-model="form.jml_saudara_angkat" class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm"></div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Status Anak</label>
                                        <select v-model="form.status_yatim_piatu" class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                                            <option value="-">Lengkap</option>
                                            <option value="Yatim">Yatim</option>
                                            <option value="Piatu">Piatu</option>
                                            <option value="Yatim Piatu">Yatim Piatu</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Bahasa Sehari-hari</label>
                                        <input type="text" v-model="form.bahasa_sehari_hari" class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- B. Tinggal -->
                        <div class="bg-gray-50 dark:bg-gray-800/50 p-6 rounded-2xl border border-gray-100 dark:border-gray-700">
                            <h3 class="font-bold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-600 pb-3 mb-5 flex items-center gap-2">
                                <i class="fas fa-map-marked-alt text-emerald-500"></i> B. Tempat Tinggal & Transportasi
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-700 dark:text-gray-300 mb-1">Tinggal Bersama</label>
                                    <select v-model="form.tinggal_bersama" class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                                        <option value="Orang Tua">Orang Tua</option>
                                        <option value="Wali">Wali</option>
                                        <option value="Asrama">Asrama</option>
                                        <option value="Kos">Kos</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>
                                <div><label class="block text-[10px] font-bold text-gray-700 dark:text-gray-300 mb-1">Jarak ke Sekolah</label><input type="text" v-model="form.jarak_ke_sekolah" placeholder="Cth: 5 KM" class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm"></div>
                                <div><label class="block text-[10px] font-bold text-gray-700 dark:text-gray-300 mb-1">Transportasi</label><input type="text" v-model="form.transportasi" placeholder="Motor/Angkot" class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm"></div>
                            </div>
                        </div>

                        <!-- C. Kesehatan -->
                        <div class="bg-gray-50 dark:bg-gray-800/50 p-6 rounded-2xl border border-gray-100 dark:border-gray-700">
                            <h3 class="font-bold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-600 pb-3 mb-5 flex items-center gap-2">
                                <i class="fas fa-heartbeat text-rose-500"></i> C. Kesehatan & Fisik
                            </h3>
                            <div class="space-y-4">
                                <div class="grid grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-700 dark:text-gray-300 mb-1">Gol. Darah</label>
                                        <select v-model="form.gol_darah" class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                                            <option value="-">-</option><option value="A">A</option><option value="B">B</option><option value="AB">AB</option><option value="O">O</option>
                                        </select>
                                    </div>
                                    <div class="col-span-2"><label class="block text-[10px] font-bold text-gray-700 dark:text-gray-300 mb-1">Penyakit Diderita</label><input type="text" v-model="form.penyakit_pernah_diderita" placeholder="Kosong jika tdk ada" class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm"></div>
                                </div>
                                <div><label class="block text-[10px] font-bold text-gray-700 dark:text-gray-300 mb-1">Kelainan Jasmani</label><input type="text" v-model="form.kelainan_jasmani" class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm"></div>
                                
                                <div class="bg-rose-50 dark:bg-rose-900/20 p-4 rounded-xl border border-rose-100 dark:border-rose-800 grid grid-cols-2 gap-4 mt-2">
                                    <div>
                                        <p class="text-[10px] font-bold text-rose-800 dark:text-rose-400 mb-2">FISIK SAAT DITERIMA</p>
                                        <div class="flex gap-2">
                                            <input type="number" v-model="form.tinggi_badan" placeholder="Tinggi(cm)" class="w-full rounded-lg border-gray-300 text-xs px-2 py-1.5">
                                            <input type="number" v-model="form.berat_badan" placeholder="Berat(kg)" class="w-full rounded-lg border-gray-300 text-xs px-2 py-1.5">
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-rose-800 dark:text-rose-400 mb-2">FISIK SAAT LULUS</p>
                                        <div class="flex gap-2">
                                            <input type="number" v-model="form.tinggi_meninggalkan" placeholder="Tinggi(cm)" class="w-full rounded-lg border-gray-300 text-xs px-2 py-1.5">
                                            <input type="number" v-model="form.berat_meninggalkan" placeholder="Berat(kg)" class="w-full rounded-lg border-gray-300 text-xs px-2 py-1.5">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Kanan -->
                    <div class="space-y-8">
                        
                        <!-- D. Pendidikan -->
                        <div class="bg-gray-50 dark:bg-gray-800/50 p-6 rounded-2xl border border-gray-100 dark:border-gray-700">
                            <h3 class="font-bold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-600 pb-3 mb-5 flex items-center gap-2">
                                <i class="fas fa-graduation-cap text-purple-500"></i> D. Pendidikan SMP/MTs Sebelumnya
                            </h3>
                            <div class="space-y-4">
                                <div><label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Tgl Diterima di Sekolah Ini</label><input type="date" v-model="form.tgl_diterima" class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm"></div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div><label class="block text-[10px] font-bold text-gray-700 dark:text-gray-300 mb-1">Nomor Ijazah / STTB SMP</label><input type="text" v-model="form.no_sttb_smp" class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm"></div>
                                    <div><label class="block text-[10px] font-bold text-gray-700 dark:text-gray-300 mb-1">Tanggal Ijazah SMP</label><input type="date" v-model="form.tgl_sttb_smp" class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm"></div>
                                </div>
                            </div>
                        </div>

                        <!-- E. Ortu -->
                        <div class="bg-gray-50 dark:bg-gray-800/50 p-6 rounded-2xl border border-gray-100 dark:border-gray-700">
                            <h3 class="font-bold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-600 pb-3 mb-5 flex items-center gap-2">
                                <i class="fas fa-user-tie text-blue-500"></i> E. Pendidikan & Penghasilan Ortu/Wali
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div><label class="block text-[10px] font-bold text-gray-700 dark:text-gray-300 mb-1">Ijazah Terakhir Ayah</label><input type="text" v-model="form.pendidikan_ayah" placeholder="Cth: SMA / S1" class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm"></div>
                                <div><label class="block text-[10px] font-bold text-gray-700 dark:text-gray-300 mb-1">Penghasilan Ayah/bln</label><input type="text" v-model="form.penghasilan_ayah" placeholder="Cth: Rp 3.000.000" class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm"></div>
                                
                                <div><label class="block text-[10px] font-bold text-gray-700 dark:text-gray-300 mb-1">Ijazah Terakhir Ibu</label><input type="text" v-model="form.pendidikan_ibu" placeholder="Cth: SMP / SMA" class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm"></div>
                                <div><label class="block text-[10px] font-bold text-gray-700 dark:text-gray-300 mb-1">Penghasilan Ibu/bln</label><input type="text" v-model="form.penghasilan_ibu" placeholder="Cth: Rp 1.500.000" class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm"></div>

                                <div><label class="block text-[10px] font-bold text-gray-700 dark:text-gray-300 mb-1">Ijazah Terakhir Wali</label><input type="text" v-model="form.pendidikan_wali" class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm"></div>
                                <div><label class="block text-[10px] font-bold text-gray-700 dark:text-gray-300 mb-1">Penghasilan Wali/bln</label><input type="text" v-model="form.penghasilan_wali" class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm"></div>
                            </div>
                        </div>

                        <!-- F. Keluar -->
                        <div class="bg-gray-50 dark:bg-gray-800/50 p-6 rounded-2xl border border-gray-100 dark:border-gray-700">
                            <h3 class="font-bold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-600 pb-3 mb-5 flex items-center gap-2">
                                <i class="fas fa-door-open text-amber-500"></i> F. Lulus / Keluar dari Sekolah
                            </h3>
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div><label class="block text-[10px] font-bold text-gray-700 dark:text-gray-300 mb-1">Tanggal Keluar/Lulus</label><input type="date" v-model="form.tgl_meninggalkan" class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm"></div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-700 dark:text-gray-300 mb-1">Alasan Keluar</label>
                                        <select v-model="form.alasan_meninggalkan" class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                                            <option value="">-- Belum Keluar --</option>
                                            <option value="Lulus">Lulus</option>
                                            <option value="Pindah Sekolah">Pindah Sekolah</option>
                                            <option value="Dikeluarkan">Dikeluarkan</option>
                                            <option value="Meninggal Dunia">Meninggal Dunia</option>
                                        </select>
                                    </div>
                                </div>
                                <div><label class="block text-[10px] font-bold text-gray-700 dark:text-gray-300 mb-1">Nomor Ijazah SMK (Jika Lulus)</label><input type="text" v-model="form.no_ijazah_smk" class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm"></div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Bagian Bawah Full Width (G, H, I, J) -->
                <div class="p-6 md:p-8 bg-gray-50/80 dark:bg-gray-800/80 border-t border-gray-200 dark:border-gray-700 space-y-8">
                    
                    <!-- G. Kepribadian -->
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-600 pb-3 mb-5 flex items-center gap-2">
                            <i class="fas fa-brain text-indigo-500"></i> G. Kepribadian & Bakat Khusus
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                            <div><label class="block text-[10px] font-bold text-gray-700 dark:text-gray-300 mb-1">Intelegensi (IQ)</label><input type="text" v-model="form.iq" class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm"></div>
                            <div class="md:col-span-3"><label class="block text-[10px] font-bold text-gray-700 dark:text-gray-300 mb-1">Berdasarkan tes pada tanggal / bulan / tahun</label><input type="text" v-model="form.tgl_tes_iq" placeholder="Cth: 12 Agustus 2025" class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm"></div>
                        </div>

                        <p class="text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">Aspek Kepribadian (Ketik B / C / K)</p>
                        <div class="overflow-x-auto mb-8 border border-gray-200 dark:border-gray-600 rounded-xl shadow-sm bg-white dark:bg-gray-800">
                            <table class="w-full text-xs text-center border-collapse">
                                <thead class="bg-indigo-50 dark:bg-indigo-900/30 text-indigo-900 dark:text-indigo-200 font-bold">
                                    <tr>
                                        <th rowspan="2" class="border border-indigo-100 dark:border-indigo-800 p-3 text-left">Aspek Dinilai</th>
                                        <th colspan="3" class="border border-indigo-100 dark:border-indigo-800 p-2">Smt 1</th>
                                        <th colspan="3" class="border border-indigo-100 dark:border-indigo-800 p-2">Smt 2</th>
                                        <th colspan="3" class="border border-indigo-100 dark:border-indigo-800 p-2">Smt 3</th>
                                        <th colspan="3" class="border border-indigo-100 dark:border-indigo-800 p-2">Smt 4</th>
                                        <th colspan="3" class="border border-indigo-100 dark:border-indigo-800 p-2">Smt 5</th>
                                        <th colspan="3" class="border border-indigo-100 dark:border-indigo-800 p-2">Smt 6</th>
                                    </tr>
                                    <tr>
                                        <template v-for="n in 6" :key="n">
                                            <th class="border border-indigo-100 dark:border-indigo-800 bg-white dark:bg-gray-800 p-1">B</th>
                                            <th class="border border-indigo-100 dark:border-indigo-800 bg-white dark:bg-gray-800 p-1">C</th>
                                            <th class="border border-indigo-100 dark:border-indigo-800 bg-white dark:bg-gray-800 p-1">K</th>
                                        </template>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="opt in kepribadianOptions" :key="opt.key" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="border border-gray-200 dark:border-gray-700 p-3 text-left font-bold text-gray-700 dark:text-gray-200">{{ opt.label }}</td>
                                        <template v-for="smt in 6" :key="smt">
                                            <td class="border border-gray-200 dark:border-gray-700 p-0"><input type="text" v-model="form.kepribadian[opt.key][smt].B" maxlength="1" class="w-8 text-center border-0 bg-transparent p-1 uppercase focus:ring-0 text-xs font-bold text-indigo-600 dark:text-indigo-400"></td>
                                            <td class="border border-gray-200 dark:border-gray-700 p-0"><input type="text" v-model="form.kepribadian[opt.key][smt].C" maxlength="1" class="w-8 text-center border-0 bg-transparent p-1 uppercase focus:ring-0 text-xs font-bold text-indigo-600 dark:text-indigo-400"></td>
                                            <td class="border border-gray-200 dark:border-gray-700 p-0"><input type="text" v-model="form.kepribadian[opt.key][smt].K" maxlength="1" class="w-8 text-center border-0 bg-transparent p-1 uppercase focus:ring-0 text-xs font-bold text-indigo-600 dark:text-indigo-400"></td>
                                        </template>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <p class="text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">Bakat Khusus & Prestasi Menonjol</p>
                        <div class="overflow-x-auto border border-gray-200 dark:border-gray-600 rounded-xl shadow-sm bg-white dark:bg-gray-800">
                            <table class="w-full text-xs text-center border-collapse">
                                <thead class="bg-indigo-50 dark:bg-indigo-900/30 text-indigo-900 dark:text-indigo-200 font-bold">
                                    <tr>
                                        <th class="border border-indigo-100 dark:border-indigo-800 p-3 w-8">No</th>
                                        <th class="border border-indigo-100 dark:border-indigo-800 p-3 w-24">SMTH/TH</th>
                                        <th class="border border-indigo-100 dark:border-indigo-800 p-3">Kesenian</th>
                                        <th class="border border-indigo-100 dark:border-indigo-800 p-3">Olahraga</th>
                                        <th class="border border-indigo-100 dark:border-indigo-800 p-3">Organisasi</th>
                                        <th class="border border-indigo-100 dark:border-indigo-800 p-3">Hasta Karya</th>
                                        <th class="border border-indigo-100 dark:border-indigo-800 p-3">Karya Tulis</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="i in 6" :key="i" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="border border-gray-200 dark:border-gray-700 p-2 font-bold text-gray-500">{{ i }}</td>
                                        <td class="border border-gray-200 dark:border-gray-700 p-0"><input type="text" v-model="form.bakat[i].smth" class="w-full border-0 bg-transparent p-2 text-center focus:ring-0 text-[11px]"></td>
                                        <td class="border border-gray-200 dark:border-gray-700 p-0"><input type="text" v-model="form.bakat[i].kesenian" class="w-full border-0 bg-transparent p-2 text-center focus:ring-0 text-[11px]"></td>
                                        <td class="border border-gray-200 dark:border-gray-700 p-0"><input type="text" v-model="form.bakat[i].olahraga" class="w-full border-0 bg-transparent p-2 text-center focus:ring-0 text-[11px]"></td>
                                        <td class="border border-gray-200 dark:border-gray-700 p-0"><input type="text" v-model="form.bakat[i].organisasi" class="w-full border-0 bg-transparent p-2 text-center focus:ring-0 text-[11px]"></td>
                                        <td class="border border-gray-200 dark:border-gray-700 p-0"><input type="text" v-model="form.bakat[i].hasta_karya" class="w-full border-0 bg-transparent p-2 text-center focus:ring-0 text-[11px]"></td>
                                        <td class="border border-gray-200 dark:border-gray-700 p-0"><input type="text" v-model="form.bakat[i].karya_tulis" class="w-full border-0 bg-transparent p-2 text-center focus:ring-0 text-[11px]"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12 pt-4">
                        <!-- I. Prestasi -->
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-600 pb-3 mb-5 flex items-center gap-2">
                                <i class="fas fa-chart-line text-emerald-500"></i> I. Perkembangan Peserta Didik
                            </h3>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">Prestasi Peserta Didik (Catatan Khusus)</label>
                                <textarea v-model="form.prestasi_siswa" rows="5" class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                                
                                <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 p-4 rounded-xl mt-4 flex items-start gap-3">
                                    <i class="fas fa-magic text-emerald-500 mt-0.5"></i>
                                    <p class="text-[11px] font-bold text-emerald-700 dark:text-emerald-400">
                                        Tabel Kehadiran (Bagian H) dan Tabel Nilai Rapor Semester 1-6 (Bagian I) akan ter-GENERATE SECARA OTOMATIS oleh sistem saat Anda mencetak PDF Buku Induk berdasarkan data dari Wali Kelas dan Guru Mata Pelajaran.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- J. Beasiswa -->
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-600 pb-3 mb-5 flex items-center gap-2">
                                <i class="fas fa-hand-holding-usd text-amber-500"></i> J. Penerimaan Beasiswa
                            </h3>
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                                <table class="w-full text-sm">
                                    <tr v-for="i in 3" :key="i">
                                        <td class="py-3 w-16 font-bold text-gray-600 dark:text-gray-400">Tahun:</td>
                                        <td class="py-3 pr-4"><input type="text" v-model="form.penerimaan[i].tahun" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 px-3 py-2 text-sm focus:ring-amber-500 focus:border-amber-500"></td>
                                        <td class="py-3 w-12 font-bold text-center text-gray-600 dark:text-gray-400">dari:</td>
                                        <td class="py-3"><input type="text" v-model="form.penerimaan[i].dari" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 px-3 py-2 text-sm focus:ring-amber-500 focus:border-amber-500"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Footer / Submit -->
                <div class="p-6 md:p-8 bg-gray-100 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                    <button type="submit" :disabled="form.processing" class="px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-black shadow-lg shadow-blue-500/30 transition-all uppercase tracking-wider flex items-center gap-3 disabled:opacity-50 text-sm">
                        <i class="fas fa-save text-lg"></i>
                        <span>Simpan Data Buku Induk</span>
                    </button>
                </div>

            </form>
        </div>
    </DashboardLayout>
</template>
