<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import Vcode from "vue3-puzzle-vcode";

const props = defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
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

// Puzzle Captcha State
const isShowCaptcha = ref(false);

const form = useForm({
    login_id: '',
    password: '',
    remember: false,
});

// Trigger Captcha when "Log In" is clicked
const triggerLogin = () => {
    // Only show captcha if form is filled
    if (form.login_id && form.password) {
        isShowCaptcha.value = true;
    } else {
        // Fallback to let HTML5 validation trigger
        submit();
    }
};

// When puzzle is successful
const onCaptchaSuccess = () => {
    isShowCaptcha.value = false;
    submit();
};

const onCaptchaClose = () => {
    isShowCaptcha.value = false;
};

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Log in" />

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
            <div class="max-w-md w-full mx-auto my-auto">
                <div class="text-center mb-10">
                    <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight mb-2">Selamat Datang</h2>
                    <p class="text-sm text-gray-500 font-medium">Masukkan email/username dan password untuk mengakses dashboard akademik Anda.</p>
                </div>

                <div v-if="status" class="mb-4 font-medium text-sm text-green-600 text-center">
                    {{ status }}
                </div>

                <form @submit.prevent="triggerLogin">
                    <!-- Login ID -->
                    <div class="mb-5">
                        <label for="login_id" class="block text-sm font-bold text-gray-700 mb-1.5">Email / Username</label>
                        <input 
                            id="login_id" 
                            type="text" 
                            v-model="form.login_id" 
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 outline-none transition-all text-sm text-gray-800 placeholder-gray-400 font-medium"
                            placeholder="nama@sekolah.com"
                            required 
                            autofocus 
                        />
                        <div v-if="form.errors.login_id" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.login_id }}</div>
                    </div>

                    <!-- Password -->
                    <div class="mb-6">
                        <label for="password" class="block text-sm font-bold text-gray-700 mb-1.5">Password</label>
                        <div class="relative">
                            <input 
                                id="password" 
                                type="password" 
                                v-model="form.password" 
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 outline-none transition-all text-sm text-gray-800 placeholder-gray-400 font-medium"
                                placeholder="••••••••"
                                required 
                            />
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-lock text-sm"></i>
                            </div>
                        </div>
                        <div v-if="form.errors.password" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.password }}</div>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between mb-8">
                        <label class="flex items-center cursor-pointer group">
                            <input type="checkbox" v-model="form.remember" class="w-4 h-4 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500 focus:ring-2 cursor-pointer transition-colors">
                            <span class="ml-2 text-sm text-gray-500 font-medium group-hover:text-gray-700 transition-colors">Ingat Saya</span>
                        </label>
                        
                        <Link v-if="canResetPassword" :href="route('password.request')" class="text-sm font-bold text-primary-600 hover:text-primary-700 transition-colors">
                            Lupa Password?
                        </Link>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" :disabled="form.processing" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-3.5 px-4 rounded-xl transition-all shadow-lg shadow-primary-600/30 hover:shadow-primary-600/50 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                        <i class="fas fa-sign-in-alt"></i> Masuk Sistem
                    </button>


                </form>
            </div>

            <!-- Footer -->
            <div class="flex justify-between items-center text-xs text-gray-400 font-medium mt-8 lg:mt-0">
                <p>&copy; {{ new Date().getFullYear() }} SIAKAD Terpadu. All rights reserved.</p>
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
                        Membangun Generasi Emas Masa Depan.
                    </h2>
                    <p class="text-primary-100 text-lg font-medium opacity-90 drop-shadow-sm">
                        Sistem Informasi Akademik Terpadu untuk memudahkan manajemen sekolah, kegiatan belajar, dan evaluasi siswa secara modern.
                    </p>
                </div>

                <!-- 3D Illustration / Graphic -->
                <div class="relative z-10 w-full max-w-md mt-4 transition-transform duration-500 hover:scale-105">
                    <!-- Gunakan file gambar yang baru kita generate (atau fallback icon) -->
                    <img src="/images/school_login_illustration.png" class="w-full h-auto drop-shadow-2xl rounded-2xl" alt="School Illustration" onerror="this.style.display='none'">
                    
                    <!-- Fallback Icon if image not copied yet -->
                    <div v-if="false" class="w-full aspect-square bg-white/10 backdrop-blur-md rounded-3xl border border-white/20 flex flex-col items-center justify-center p-8 shadow-2xl">
                        <i class="fas fa-school text-8xl text-white mb-6 drop-shadow-lg"></i>
                        <h3 class="text-2xl font-bold text-white text-center">Smart School</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Puzzle Captcha Pop-Up -->
    <Vcode :show="isShowCaptcha" @success="onCaptchaSuccess" @close="onCaptchaClose" sliderText="Geser puzzle ke tempatnya" successText="Verifikasi Berhasil!" failText="Coba Lagi" />
</template>

