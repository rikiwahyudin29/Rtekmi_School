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

    // Siswa fields (yang tidak overlap)
    nisn: props.profil?.nisn || '',
    nis: props.profil?.nis || '',
    nama_ayah: props.profil?.nama_ayah || '',
    nama_ibu: props.profil?.nama_ibu || '',
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
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Profile Information
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Update your account's profile information, photo, and details.
            </p>
        </header>

        <!-- Current Profile Photo Display -->
        <div class="mt-4 flex items-center">
            <div class="h-20 w-20 overflow-hidden rounded-full border-2 border-gray-200">
                <img v-if="photoPreview" :src="photoPreview" class="h-full w-full object-cover" />
                <img v-else-if="profil && profil.foto && profil.foto !== 'default.png'" :src="(role === 'guru' ? '/uploads/guru/' : '/uploads/siswa/') + profil.foto" class="h-full w-full object-cover" />
                <div v-else class="flex h-full w-full items-center justify-center bg-gray-100 text-gray-400">
                    <svg class="h-10 w-10" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <InputLabel for="foto" value="Photo Profile" />
                <input type="file" id="foto" @change="updatePhotoPreview" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100" accept="image/*" />
                <InputError class="mt-2" :message="form.errors.foto" />
            </div>
        </div>

        <form
            @submit.prevent="form.post(route('profile.update'), { preserveScroll: true, _method: 'patch' })"
            class="mt-6 space-y-6"
        >
            <!-- Basic User Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <InputLabel for="nama_lengkap" value="Nama Lengkap" />
                    <TextInput id="nama_lengkap" type="text" class="mt-1 block w-full" v-model="form.nama_lengkap" required autofocus autocomplete="name" />
                    <InputError class="mt-2" :message="form.errors.nama_lengkap" />
                </div>

                <div>
                    <InputLabel for="email" value="Email" />
                    <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required autocomplete="username" />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div>
                    <InputLabel for="nomor_wa" value="Nomor WhatsApp" />
                    <TextInput id="nomor_wa" type="text" class="mt-1 block w-full" v-model="form.nomor_wa" />
                    <InputError class="mt-2" :message="form.errors.nomor_wa" />
                </div>
            </div>

            <hr class="my-6 border-gray-200" />
            <h3 class="text-md font-medium text-gray-900 mb-4">Data Khusus {{ role === 'guru' ? 'Guru' : (role === 'siswa' ? 'Siswa' : 'Admin') }}</h3>

            <div v-if="role === 'guru'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <InputLabel for="nip" value="NIP" />
                    <TextInput id="nip" type="text" class="mt-1 block w-full" v-model="form.nip" />
                    <InputError class="mt-2" :message="form.errors.nip" />
                </div>
                <div>
                    <InputLabel for="nik" value="NIK" />
                    <TextInput id="nik" type="text" class="mt-1 block w-full" v-model="form.nik" />
                    <InputError class="mt-2" :message="form.errors.nik" />
                </div>
                <div>
                    <InputLabel for="gelar_depan" value="Gelar Depan" />
                    <TextInput id="gelar_depan" type="text" class="mt-1 block w-full" v-model="form.gelar_depan" />
                    <InputError class="mt-2" :message="form.errors.gelar_depan" />
                </div>
                <div>
                    <InputLabel for="gelar_belakang" value="Gelar Belakang" />
                    <TextInput id="gelar_belakang" type="text" class="mt-1 block w-full" v-model="form.gelar_belakang" />
                    <InputError class="mt-2" :message="form.errors.gelar_belakang" />
                </div>
                <div>
                    <InputLabel for="tempat_lahir" value="Tempat Lahir" />
                    <TextInput id="tempat_lahir" type="text" class="mt-1 block w-full" v-model="form.tempat_lahir" />
                    <InputError class="mt-2" :message="form.errors.tempat_lahir" />
                </div>
                <div>
                    <InputLabel for="tanggal_lahir" value="Tanggal Lahir" />
                    <TextInput id="tanggal_lahir" type="date" class="mt-1 block w-full" v-model="form.tanggal_lahir" />
                    <InputError class="mt-2" :message="form.errors.tanggal_lahir" />
                </div>
                <div>
                    <InputLabel for="jenis_kelamin" value="Jenis Kelamin" />
                    <select id="jenis_kelamin" v-model="form.jenis_kelamin" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.jenis_kelamin" />
                </div>
                <div class="md:col-span-2">
                    <InputLabel for="alamat" value="Alamat" />
                    <textarea id="alamat" v-model="form.alamat" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                    <InputError class="mt-2" :message="form.errors.alamat" />
                </div>
            </div>

            <div v-if="role === 'siswa'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <InputLabel for="nisn" value="NISN" />
                    <TextInput id="nisn" type="text" class="mt-1 block w-full" v-model="form.nisn" />
                    <InputError class="mt-2" :message="form.errors.nisn" />
                </div>
                <div>
                    <InputLabel for="nis" value="NIS" />
                    <TextInput id="nis" type="text" class="mt-1 block w-full" v-model="form.nis" />
                    <InputError class="mt-2" :message="form.errors.nis" />
                </div>
                <div>
                    <InputLabel for="tempat_lahir" value="Tempat Lahir" />
                    <TextInput id="tempat_lahir" type="text" class="mt-1 block w-full" v-model="form.tempat_lahir" />
                    <InputError class="mt-2" :message="form.errors.tempat_lahir" />
                </div>
                <div>
                    <InputLabel for="tanggal_lahir" value="Tanggal Lahir" />
                    <TextInput id="tanggal_lahir" type="date" class="mt-1 block w-full" v-model="form.tanggal_lahir" />
                    <InputError class="mt-2" :message="form.errors.tanggal_lahir" />
                </div>
                <div>
                    <InputLabel for="jenis_kelamin" value="Jenis Kelamin" />
                    <select id="jenis_kelamin" v-model="form.jenis_kelamin" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.jenis_kelamin" />
                </div>
                <div>
                    <InputLabel for="nama_ayah" value="Nama Ayah" />
                    <TextInput id="nama_ayah" type="text" class="mt-1 block w-full" v-model="form.nama_ayah" />
                    <InputError class="mt-2" :message="form.errors.nama_ayah" />
                </div>
                <div>
                    <InputLabel for="nama_ibu" value="Nama Ibu" />
                    <TextInput id="nama_ibu" type="text" class="mt-1 block w-full" v-model="form.nama_ibu" />
                    <InputError class="mt-2" :message="form.errors.nama_ibu" />
                </div>
                <div class="md:col-span-2">
                    <InputLabel for="alamat" value="Alamat" />
                    <textarea id="alamat" v-model="form.alamat" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                    <InputError class="mt-2" :message="form.errors.alamat" />
                </div>
            </div>


            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-gray-800">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600"
                    >
                        Saved.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
