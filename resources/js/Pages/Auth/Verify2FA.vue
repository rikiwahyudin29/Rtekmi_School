<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';

const props = defineProps({
    web: {
        type: Object,
        default: () => ({})
    }
});

const page = usePage();
// Current Theme
const currentTheme = computed(() => page.props.theme || 'theme-green');

onMounted(() => {
    // Add the theme class to the document root to apply CSS variables globally
    document.documentElement.classList.add(currentTheme.value);
});

const form = useForm({
    otp_code: '',
    mode: 'verify'
});

const submit = () => {
    form.post(route('2fa.process'));
};
</script>

<template>
    <Head title="Verifikasi 2-Langkah" />

    <div class="min-h-screen flex w-full font-sans bg-gray-50">
        <!-- Left Side: Form -->
        <div class="w-full lg:w-1/2 flex flex-col justify-between p-8 sm:p-12 lg:p-16 bg-white relative">
            <!-- Logo -->
            <div class="flex items-center gap-3 mb-8 lg:mb-0">
                <img v-if="web.logo" :src="`/uploads/identitas/${web.logo}`" class="w-10 h-10 object-contain drop-shadow-md bg-white rounded-full p-0.5 border-2 border-primary-600/30" alt="Logo">
                <div v-else class="w-10 h-10 rounded-lg bg-primary-600 flex items-center justify-center text-white font-bold text-xl">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <span class="font-bold text-xl text-gray-900 tracking-tight uppercase">{{ web.nama_sekolah || 'SIAKAD' }}</span>
            </div>

            <!-- Form Container -->
            <div class="max-w-md w-full mx-auto my-auto text-center">
                <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight mb-2">Verifikasi 2-Langkah</h2>
                <p class="text-sm text-gray-500 font-medium mb-8">
                    Buka aplikasi Google Authenticator Anda dan masukkan kode 6-digit.
                </p>

                <div v-if="form.errors.otp_code" class="mb-4 text-sm font-bold text-red-600 bg-red-50 py-2 rounded-lg">
                    {{ form.errors.otp_code }}
                </div>

                <form @submit.prevent="submit" class="text-left">
                    <!-- Single Input for 6-digit Code -->
                    <div class="mb-6">
                        <input 
                            type="text" 
                            v-model="form.otp_code"
                            class="w-full text-center text-3xl font-mono tracking-[0.5em] text-gray-800 border border-gray-200 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 outline-none rounded-xl py-4 transition-all placeholder-gray-300"
                            placeholder="000000"
                            maxlength="6"
                            required
                            autofocus
                            autocomplete="one-time-code"
                            pattern="\d*"
                        />
                    </div>

                    <div class="mt-4">
                        <button 
                            type="submit" 
                            :disabled="form.processing || form.otp_code.length !== 6" 
                            class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-3.5 px-4 rounded-xl transition-all shadow-lg shadow-primary-600/30 hover:shadow-primary-600/50 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="form.processing">Memverifikasi...</span>
                            <span v-else>Masuk Sekarang</span>
                        </button>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                        <Link 
                            :href="route('logout')" 
                            method="post" 
                            as="button" 
                            class="text-sm font-bold text-gray-500 hover:text-gray-700 transition-colors"
                        >
                            <i class="fas fa-arrow-left mr-1"></i> Batal Login
                        </Link>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="flex justify-between items-center text-xs text-gray-400 font-medium mt-8 lg:mt-0">
                <p>&copy; {{ new Date().getFullYear() }} SIAKAD Terpadu. All rights reserved.</p>
                <a href="#" class="hover:text-gray-600 transition-colors">Privacy Policy</a>
            </div>
        </div>

        <!-- Right Side: Educational Illustration -->
        <div class="hidden lg:flex lg:w-1/2 p-4">
            <div class="w-full h-full bg-primary-600 rounded-[2rem] p-12 flex flex-col relative overflow-hidden shadow-2xl items-center justify-center">
                <!-- Decorative Elements -->
                <div class="absolute top-0 right-0 w-96 h-96 bg-white opacity-10 rounded-full blur-3xl transform translate-x-1/2 -translate-y-1/4"></div>
                <div class="absolute bottom-0 left-0 w-80 h-80 bg-primary-400 opacity-20 rounded-full blur-3xl transform -translate-x-1/4 translate-y-1/4"></div>
                <div class="absolute inset-0 bg-gradient-to-b from-transparent to-primary-900/50"></div>
                
                <!-- Text Content -->
                <div class="relative z-10 max-w-xl text-center mb-10">
                    <h2 class="text-4xl lg:text-5xl font-extrabold text-white mb-6 leading-tight drop-shadow-md">
                        Keamanan Akun Anda adalah Prioritas Kami.
                    </h2>
                    <p class="text-primary-100 text-lg font-medium opacity-90 drop-shadow-sm">
                        Kami menggunakan Autentikasi 2-Langkah untuk memastikan hanya Anda yang dapat mengakses akun Anda.
                    </p>
                </div>

                <!-- 3D Illustration / Graphic -->
                <div class="relative z-10 w-full max-w-md mt-4 transition-transform duration-500 hover:scale-105">
                    <!-- Menggunakan gambar edukasi yang sama dengan login -->
                    <img src="/images/school_login_illustration.png" class="w-full h-auto drop-shadow-2xl rounded-2xl" alt="Security Illustration" onerror="this.style.display='none'">
                </div>
            </div>
        </div>
    </div>
</template>
