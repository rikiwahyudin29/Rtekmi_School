<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    profil: {
        type: Object,
        default: null,
    }
});

const user = usePage().props.auth.user;
const role = user.role;

const form = useForm({
    _method: 'patch',
    nama_lengkap: user.nama_lengkap,
    email: user.email,
    nomor_wa: user.nomor_wa || '',
    foto: null,

    // Guru fields
    nip: props.profil?.nip || '',
    nik: props.profil?.nik || '',
    gelar_depan: props.profil?.gelar_depan || '',
    gelar_belakang: props.profil?.gelar_belakang || '',
    jenis_kelamin: props.profil?.jenis_kelamin || 'L',
    tempat_lahir: props.profil?.tempat_lahir || '',
    tanggal_lahir: props.profil?.tanggal_lahir || '',
    alamat: props.profil?.alamat || '',
    nuptk: props.profil?.nuptk || '',
    pendidikan_terakhir: props.profil?.pendidikan_terakhir || '',
    sertifikasi: props.profil?.sertifikasi || '',
    status_guru: props.profil?.status_guru || 'GTY',
    ibu_kandung: props.profil?.ibu_kandung || '',
    status_kepegawaian: props.profil?.status_kepegawaian || '',

    // Siswa fields
    nisn: props.profil?.nisn || '',
    nis: props.profil?.nis || '',
    nama_ayah: props.profil?.nama_ayah || '',
    nama_ibu: props.profil?.nama_ibu || '',
    agama: props.profil?.agama || '',
    status_keluarga: props.profil?.status_keluarga || 'Anak Kandung',
    anak_ke: props.profil?.anak_ke || '',
    sekolah_asal: props.profil?.sekolah_asal || '',
    diterima_kelas: props.profil?.diterima_kelas || '',
    tanggal_diterima: props.profil?.tanggal_diterima || '',
    nama_wali: props.profil?.nama_wali || '',
    pekerjaan_wali: props.profil?.pekerjaan_wali || '',
    no_hp_ortu: props.profil?.no_hp_ortu || '',
    pekerjaan_ayah: props.profil?.pekerjaan_ayah || '',
    pekerjaan_ibu: props.profil?.pekerjaan_ibu || '',
});

const photoPreview = ref(null);

const updatePhotoPreview = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.foto = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            photoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const submitForm = () => {
    form.post(route('profile.update'), {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>

<template>
    <section>
        <!-- Photo Upload Section -->
        <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-primary-50 to-white dark:from-gray-800 dark:to-gray-900 border border-primary-100 dark:border-gray-700 p-6 mb-8 group hover:shadow-lg transition-all duration-300">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-primary-500/10 rounded-full blur-2xl transition-transform group-hover:scale-150 duration-500"></div>
            
            <div class="flex flex-col sm:flex-row items-center gap-6 relative z-10">
                <div class="relative group/avatar">
                    <div class="h-28 w-28 overflow-hidden rounded-[1.5rem] border-4 border-white dark:border-gray-700 shadow-xl ring-4 ring-primary-50 dark:ring-gray-800 transition-all duration-300 group-hover/avatar:shadow-primary-500/20">
                        <img v-if="photoPreview" :src="photoPreview" class="h-full w-full object-cover transition-transform duration-500 group-hover/avatar:scale-110" />
                        <img v-else-if="profil && profil.foto && profil.foto !== 'default.png'" :src="(role === 'guru' ? '/uploads/guru/' : '/uploads/siswa/') + profil.foto" class="h-full w-full object-cover transition-transform duration-500 group-hover/avatar:scale-110" />
                        <div v-else class="flex h-full w-full items-center justify-center bg-gray-100 dark:bg-gray-800 text-gray-400">
                            <i class="fas fa-user text-4xl"></i>
                        </div>
                    </div>
                    <!-- Edit overlay -->
                    <label for="foto" class="absolute inset-0 flex items-center justify-center bg-black/40 text-white opacity-0 group-hover/avatar:opacity-100 rounded-[1.2rem] cursor-pointer transition-opacity backdrop-blur-sm m-1">
                        <i class="fas fa-camera text-xl"></i>
                    </label>
                </div>
                
                <div class="text-center sm:text-left flex-1">
                    <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Ubah Foto Profil</h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 max-w-sm">Gunakan foto dengan rasio 1:1, maksimal ukuran 2MB. Format JPG atau PNG.</p>
                    
                    <div class="flex flex-wrap justify-center sm:justify-start items-center gap-3">
                        <label for="foto" class="cursor-pointer inline-flex items-center gap-2 px-5 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm transition-all hover:-translate-y-0.5">
                            <i class="fas fa-upload text-primary-500"></i> Pilih File
                        </label>
                        <input type="file" id="foto" @change="updatePhotoPreview" class="hidden" accept="image/*" />
                    </div>
                    <InputError class="mt-2" :message="form.errors.foto" />
                </div>
            </div>
        </div>

        <form @submit.prevent="submitForm" class="space-y-8">
            
            <!-- INFORMASI AKUN UTAMA -->
            <div class="bg-gray-50/50 dark:bg-gray-800/50 rounded-[2rem] p-6 border border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/40 text-primary-600 flex items-center justify-center">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <h4 class="text-base font-bold text-gray-900 dark:text-white">Informasi Akun</h4>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                    <div>
                        <InputLabel for="nama_lengkap" class="font-semibold text-gray-700 dark:text-gray-300">
                            <i class="fas fa-user-circle mr-1 text-gray-400"></i> Nama Lengkap
                        </InputLabel>
                        <TextInput id="nama_lengkap" type="text" class="mt-1.5 block w-full rounded-xl bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 focus:ring-primary-500 focus:border-primary-500 transition-shadow shadow-sm" v-model="form.nama_lengkap" required autocomplete="name" />
                        <InputError class="mt-1.5" :message="form.errors.nama_lengkap" />
                    </div>
                    <div>
                        <InputLabel for="email" class="font-semibold text-gray-700 dark:text-gray-300">
                            <i class="fas fa-envelope mr-1 text-gray-400"></i> Email Address
                        </InputLabel>
                        <TextInput id="email" type="email" class="mt-1.5 block w-full rounded-xl bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 focus:ring-primary-500 focus:border-primary-500 transition-shadow shadow-sm" v-model="form.email" required autocomplete="username" />
                        <InputError class="mt-1.5" :message="form.errors.email" />
                    </div>
                    <div>
                        <InputLabel for="nomor_wa" class="font-semibold text-gray-700 dark:text-gray-300">
                            <i class="fab fa-whatsapp mr-1 text-gray-400"></i> Nomor WhatsApp
                        </InputLabel>
                        <TextInput id="nomor_wa" type="text" class="mt-1.5 block w-full rounded-xl bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 focus:ring-primary-500 focus:border-primary-500 transition-shadow shadow-sm" v-model="form.nomor_wa" />
                        <InputError class="mt-1.5" :message="form.errors.nomor_wa" />
                    </div>
                </div>
            </div>

            <!-- GURU FORM -->
            <div v-if="role === 'guru'" class="bg-indigo-50/30 dark:bg-indigo-900/10 rounded-[2rem] p-6 border border-indigo-100 dark:border-indigo-900/30">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 flex items-center justify-center">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h4 class="text-base font-bold text-gray-900 dark:text-white">Data Spesifik Guru</h4>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                    <div>
                        <InputLabel for="nip" class="font-semibold text-gray-700 dark:text-gray-300">NIP</InputLabel>
                        <TextInput id="nip" type="text" class="mt-1.5 block w-full rounded-xl bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 shadow-sm" v-model="form.nip" />
                    </div>
                    <div>
                        <InputLabel for="nik" class="font-semibold text-gray-700 dark:text-gray-300">NIK</InputLabel>
                        <TextInput id="nik" type="text" class="mt-1.5 block w-full rounded-xl bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 shadow-sm" v-model="form.nik" />
                    </div>
                    <div>
                        <InputLabel for="nuptk" class="font-semibold text-gray-700 dark:text-gray-300">NUPTK</InputLabel>
                        <TextInput id="nuptk" type="text" class="mt-1.5 block w-full rounded-xl bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 shadow-sm" v-model="form.nuptk" />
                    </div>
                    <div>
                        <InputLabel for="status_guru" class="font-semibold text-gray-700 dark:text-gray-300">Status Kepegawaian</InputLabel>
                        <select id="status_guru" v-model="form.status_guru" class="mt-1.5 block w-full border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm">
                            <option value="PNS">PNS</option>
                            <option value="GTT">GTT</option>
                            <option value="GTY">GTY</option>
                            <option value="HONORER">Honorer</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="gelar_depan" class="font-semibold text-gray-700 dark:text-gray-300">Gelar Depan</InputLabel>
                            <TextInput id="gelar_depan" type="text" class="mt-1.5 block w-full rounded-xl bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 shadow-sm placeholder-gray-400" placeholder="Contoh: Drs." v-model="form.gelar_depan" />
                        </div>
                        <div>
                            <InputLabel for="gelar_belakang" class="font-semibold text-gray-700 dark:text-gray-300">Gelar Blkg</InputLabel>
                            <TextInput id="gelar_belakang" type="text" class="mt-1.5 block w-full rounded-xl bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 shadow-sm placeholder-gray-400" placeholder="Contoh: S.Pd., M.Pd." v-model="form.gelar_belakang" />
                        </div>
                    </div>
                    <div>
                        <InputLabel for="pendidikan_terakhir" class="font-semibold text-gray-700 dark:text-gray-300">Pendidikan Terakhir</InputLabel>
                        <TextInput id="pendidikan_terakhir" type="text" class="mt-1.5 block w-full rounded-xl bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 shadow-sm" v-model="form.pendidikan_terakhir" />
                    </div>
                    <div>
                        <InputLabel for="sertifikasi" class="font-semibold text-gray-700 dark:text-gray-300">Sertifikasi / Nomes Seri</InputLabel>
                        <TextInput id="sertifikasi" type="text" class="mt-1.5 block w-full rounded-xl bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 shadow-sm" v-model="form.sertifikasi" />
                    </div>
                    <div>
                        <InputLabel for="tempat_lahir" class="font-semibold text-gray-700 dark:text-gray-300">Tempat Lahir</InputLabel>
                        <TextInput id="tempat_lahir" type="text" class="mt-1.5 block w-full rounded-xl bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 shadow-sm" v-model="form.tempat_lahir" />
                    </div>
                    <div>
                        <InputLabel for="tanggal_lahir" class="font-semibold text-gray-700 dark:text-gray-300">Tanggal Lahir</InputLabel>
                        <TextInput id="tanggal_lahir" type="date" class="mt-1.5 block w-full rounded-xl bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 shadow-sm" v-model="form.tanggal_lahir" />
                    </div>
                    <div>
                        <InputLabel for="jenis_kelamin" class="font-semibold text-gray-700 dark:text-gray-300">Jenis Kelamin</InputLabel>
                        <select id="jenis_kelamin" v-model="form.jenis_kelamin" class="mt-1.5 block w-full border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm">
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel for="ibu_kandung" class="font-semibold text-gray-700 dark:text-gray-300">Nama Ibu Kandung</InputLabel>
                        <TextInput id="ibu_kandung" type="text" class="mt-1.5 block w-full rounded-xl bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 shadow-sm" v-model="form.ibu_kandung" />
                    </div>
                    <div class="md:col-span-2">
                        <InputLabel for="alamat" class="font-semibold text-gray-700 dark:text-gray-300">Alamat Lengkap</InputLabel>
                        <textarea id="alamat" v-model="form.alamat" rows="3" class="mt-1.5 block w-full border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm"></textarea>
                    </div>
                </div>
            </div>

            <!-- SISWA FORM -->
            <div v-if="role === 'siswa'" class="space-y-8">
                <!-- Data Akademik -->
                <div class="bg-blue-50/30 dark:bg-blue-900/10 rounded-[2rem] p-6 border border-blue-100 dark:border-blue-900/30">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-600 flex items-center justify-center">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <h4 class="text-base font-bold text-gray-900 dark:text-white">Data Akademik Siswa</h4>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                        <div>
                            <InputLabel for="nisn" class="font-semibold text-gray-700 dark:text-gray-300">NISN</InputLabel>
                            <TextInput id="nisn" type="text" class="mt-1.5 block w-full rounded-xl bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 shadow-sm" v-model="form.nisn" />
                        </div>
                        <div>
                            <InputLabel for="nis" class="font-semibold text-gray-700 dark:text-gray-300">NIS</InputLabel>
                            <TextInput id="nis" type="text" class="mt-1.5 block w-full rounded-xl bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 shadow-sm" v-model="form.nis" />
                        </div>
                        <div>
                            <InputLabel for="sekolah_asal" class="font-semibold text-gray-700 dark:text-gray-300">Sekolah Asal</InputLabel>
                            <TextInput id="sekolah_asal" type="text" class="mt-1.5 block w-full rounded-xl bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 shadow-sm" v-model="form.sekolah_asal" />
                        </div>
                        <div>
                            <InputLabel for="diterima_kelas" class="font-semibold text-gray-700 dark:text-gray-300">Diterima di Kelas</InputLabel>
                            <TextInput id="diterima_kelas" type="text" class="mt-1.5 block w-full rounded-xl bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 shadow-sm" v-model="form.diterima_kelas" />
                        </div>
                        <div>
                            <InputLabel for="tanggal_diterima" class="font-semibold text-gray-700 dark:text-gray-300">Tanggal Diterima</InputLabel>
                            <TextInput id="tanggal_diterima" type="date" class="mt-1.5 block w-full rounded-xl bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 shadow-sm" v-model="form.tanggal_diterima" />
                        </div>
                    </div>
                </div>

                <!-- Data Personal Siswa -->
                <div class="bg-purple-50/30 dark:bg-purple-900/10 rounded-[2rem] p-6 border border-purple-100 dark:border-purple-900/30">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900/40 text-purple-600 flex items-center justify-center">
                            <i class="fas fa-user-tag"></i>
                        </div>
                        <h4 class="text-base font-bold text-gray-900 dark:text-white">Data Personal</h4>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                        <div>
                            <InputLabel for="tempat_lahir" class="font-semibold text-gray-700 dark:text-gray-300">Tempat Lahir</InputLabel>
                            <TextInput id="tempat_lahir" type="text" class="mt-1.5 block w-full rounded-xl bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 shadow-sm" v-model="form.tempat_lahir" />
                        </div>
                        <div>
                            <InputLabel for="tanggal_lahir" class="font-semibold text-gray-700 dark:text-gray-300">Tanggal Lahir</InputLabel>
                            <TextInput id="tanggal_lahir" type="date" class="mt-1.5 block w-full rounded-xl bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 shadow-sm" v-model="form.tanggal_lahir" />
                        </div>
                        <div>
                            <InputLabel for="jenis_kelamin" class="font-semibold text-gray-700 dark:text-gray-300">Jenis Kelamin</InputLabel>
                            <select id="jenis_kelamin" v-model="form.jenis_kelamin" class="mt-1.5 block w-full border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 dark:text-gray-300 rounded-xl shadow-sm">
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel for="agama" class="font-semibold text-gray-700 dark:text-gray-300">Agama</InputLabel>
                            <TextInput id="agama" type="text" class="mt-1.5 block w-full rounded-xl bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 shadow-sm" v-model="form.agama" />
                        </div>
                        <div>
                            <InputLabel for="status_keluarga" class="font-semibold text-gray-700 dark:text-gray-300">Status dalam Keluarga</InputLabel>
                            <TextInput id="status_keluarga" type="text" class="mt-1.5 block w-full rounded-xl bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 shadow-sm" v-model="form.status_keluarga" />
                        </div>
                        <div>
                            <InputLabel for="anak_ke" class="font-semibold text-gray-700 dark:text-gray-300">Anak Ke-</InputLabel>
                            <TextInput id="anak_ke" type="number" class="mt-1.5 block w-full rounded-xl bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 shadow-sm" v-model="form.anak_ke" />
                        </div>
                        <div class="md:col-span-2">
                            <InputLabel for="alamat" class="font-semibold text-gray-700 dark:text-gray-300">Alamat Lengkap</InputLabel>
                            <textarea id="alamat" v-model="form.alamat" rows="3" class="mt-1.5 block w-full border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 dark:text-gray-300 rounded-xl shadow-sm"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Data Orang Tua -->
                <div class="bg-rose-50/30 dark:bg-rose-900/10 rounded-[2rem] p-6 border border-rose-100 dark:border-rose-900/30">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-full bg-rose-100 dark:bg-rose-900/40 text-rose-600 flex items-center justify-center">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4 class="text-base font-bold text-gray-900 dark:text-white">Data Orang Tua / Wali</h4>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                        <div>
                            <InputLabel for="nama_ayah" class="font-semibold text-gray-700 dark:text-gray-300">Nama Ayah</InputLabel>
                            <TextInput id="nama_ayah" type="text" class="mt-1.5 block w-full rounded-xl bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 shadow-sm" v-model="form.nama_ayah" />
                        </div>
                        <div>
                            <InputLabel for="pekerjaan_ayah" class="font-semibold text-gray-700 dark:text-gray-300">Pekerjaan Ayah</InputLabel>
                            <TextInput id="pekerjaan_ayah" type="text" class="mt-1.5 block w-full rounded-xl bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 shadow-sm" v-model="form.pekerjaan_ayah" />
                        </div>
                        <div>
                            <InputLabel for="nama_ibu" class="font-semibold text-gray-700 dark:text-gray-300">Nama Ibu</InputLabel>
                            <TextInput id="nama_ibu" type="text" class="mt-1.5 block w-full rounded-xl bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 shadow-sm" v-model="form.nama_ibu" />
                        </div>
                        <div>
                            <InputLabel for="pekerjaan_ibu" class="font-semibold text-gray-700 dark:text-gray-300">Pekerjaan Ibu</InputLabel>
                            <TextInput id="pekerjaan_ibu" type="text" class="mt-1.5 block w-full rounded-xl bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 shadow-sm" v-model="form.pekerjaan_ibu" />
                        </div>
                        <div>
                            <InputLabel for="nama_wali" class="font-semibold text-gray-700 dark:text-gray-300">Nama Wali (Bila ada)</InputLabel>
                            <TextInput id="nama_wali" type="text" class="mt-1.5 block w-full rounded-xl bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 shadow-sm" v-model="form.nama_wali" />
                        </div>
                        <div>
                            <InputLabel for="pekerjaan_wali" class="font-semibold text-gray-700 dark:text-gray-300">Pekerjaan Wali</InputLabel>
                            <TextInput id="pekerjaan_wali" type="text" class="mt-1.5 block w-full rounded-xl bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 shadow-sm" v-model="form.pekerjaan_wali" />
                        </div>
                        <div class="md:col-span-2">
                            <InputLabel for="no_hp_ortu" class="font-semibold text-gray-700 dark:text-gray-300">No. HP Orang Tua / Wali</InputLabel>
                            <TextInput id="no_hp_ortu" type="text" class="mt-1.5 block w-full md:w-1/2 rounded-xl bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 shadow-sm" v-model="form.no_hp_ortu" />
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-amber-600 dark:text-amber-400 font-medium">
                    <i class="fas fa-exclamation-circle mr-1"></i> Alamat email Anda belum terverifikasi.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="underline hover:text-amber-800 ml-1"
                    >
                        Klik di sini untuk mengirim ulang email verifikasi.
                    </Link>
                </p>
                <div v-show="status === 'verification-link-sent'" class="mt-2 text-sm font-bold text-emerald-600">
                    <i class="fas fa-check mr-1"></i> Link verifikasi baru telah dikirimkan ke email Anda.
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center gap-2 rounded-xl border border-transparent bg-primary-600 px-8 py-3.5 text-sm font-bold text-white shadow-lg shadow-primary-500/30 hover:bg-primary-700 hover:-translate-y-0.5 transition-all focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 active:bg-primary-800 disabled:opacity-50 disabled:hover:translate-y-0">
                    <i class="fas fa-save" v-if="!form.processing"></i>
                    <i class="fas fa-spinner fa-spin" v-else></i>
                    <span>Simpan Perubahan</span>
                </button>

                <Transition
                    enter-active-class="transition ease-out duration-300"
                    enter-from-class="opacity-0 translate-y-2 scale-95"
                    leave-active-class="transition ease-in duration-200"
                    leave-to-class="opacity-0 scale-95"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm font-bold text-emerald-600 bg-emerald-50 dark:bg-emerald-900/30 px-4 py-2 rounded-lg flex items-center gap-2">
                        <i class="fas fa-check-circle"></i> Berhasil disimpan!
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
