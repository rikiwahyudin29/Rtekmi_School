<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

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

const sliderVerified = ref(false);
const sliderValue = ref(0);

const checkSlider = () => {
    if (sliderValue.value >= 100) {
        sliderVerified.value = true;
    } else {
        sliderValue.value = 0;
    }
};

const form = useForm({
    login_id: '',
    password: '',
    remember: false,
});

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
                <img v-if="web.logo" :src="`/uploads/identitas/${web.logo}`" class="w-10 h-10 object-contain drop-shadow-md bg-white rounded-full p-0.5 border-2 border-blue-600/30" alt="Logo">
                <div v-else class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold text-xl">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <span class="font-bold text-xl text-gray-900 tracking-tight uppercase">{{ web.nama_sekolah || 'SIAKAD' }}</span>
            </div>

            <!-- Form Container -->
            <div class="max-w-md w-full mx-auto my-auto">
                <div class="text-center mb-10">
                    <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight mb-2">Welcome Back</h2>
                    <p class="text-sm text-gray-500 font-medium">Enter your username/email and password to access your account.</p>
                </div>

                <div v-if="status" class="mb-4 font-medium text-sm text-green-600 text-center">
                    {{ status }}
                </div>

                <form @submit.prevent="submit">
                    <!-- Login ID -->
                    <div class="mb-5">
                        <label for="login_id" class="block text-sm font-bold text-gray-700 mb-1.5">Email / Username</label>
                        <input 
                            id="login_id" 
                            type="text" 
                            v-model="form.login_id" 
                            class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none transition-colors text-sm text-gray-800 placeholder-gray-400 font-medium"
                            placeholder="nama@sekolah.com"
                            required 
                            autofocus 
                        />
                        <div v-if="form.errors.login_id" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.login_id }}</div>
                    </div>

                    <!-- Password -->
                    <div class="mb-5">
                        <label for="password" class="block text-sm font-bold text-gray-700 mb-1.5">Password</label>
                        <div class="relative">
                            <input 
                                id="password" 
                                type="password" 
                                v-model="form.password" 
                                class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none transition-colors text-sm text-gray-800 placeholder-gray-400 font-medium"
                                placeholder="••••••••"
                                required 
                            />
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-eye-slash text-sm"></i>
                            </div>
                        </div>
                        <div v-if="form.errors.password" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.password }}</div>
                    </div>

                    <!-- Slider Captcha -->
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-1.5">Security Verification</label>
                        <div class="relative bg-gray-100 rounded-lg h-12 overflow-hidden border border-gray-200" :class="sliderVerified ? 'border-green-500' : ''">
                            <div class="absolute left-0 top-0 h-full bg-green-500 transition-all" :style="`width: ${sliderVerified ? 100 : sliderValue}%`"></div>
                            <input 
                                v-if="!sliderVerified"
                                type="range" 
                                min="0" max="100" 
                                v-model="sliderValue" 
                                @change="checkSlider"
                                @mouseup="checkSlider"
                                @touchend="checkSlider"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                            >
                            <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-20 transition-colors" :class="sliderVerified || sliderValue > 50 ? 'text-white' : 'text-gray-500'">
                                <span v-if="sliderVerified" class="font-bold text-sm drop-shadow-md"><i class="fas fa-check-circle mr-1"></i> Verified</span>
                                <span v-else class="font-bold text-sm drop-shadow-sm">Slide to Verify <i class="fas fa-arrow-right ml-1"></i></span>
                            </div>
                        </div>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between mb-8">
                        <label class="flex items-center cursor-pointer group">
                            <input type="checkbox" v-model="form.remember" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 cursor-pointer">
                            <span class="ml-2 text-sm text-gray-500 font-medium group-hover:text-gray-700 transition-colors">Remember Me</span>
                        </label>
                        
                        <Link v-if="canResetPassword" :href="route('password.request')" class="text-sm font-bold text-blue-600 hover:text-blue-700 transition-colors">
                            Forgot Your Password?
                        </Link>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" :disabled="form.processing || !sliderVerified" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 px-4 rounded-lg transition-all shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                        Log In
                    </button>

                    <!-- Divider -->
                    <div class="relative flex items-center justify-center mt-8 mb-6">
                        <div class="w-full border-t border-gray-200"></div>
                        <span class="absolute bg-white px-3 text-xs text-gray-400 font-medium uppercase tracking-wider">Or Login With</span>
                    </div>

                    <!-- Social Buttons -->
                    <div class="grid grid-cols-1 gap-4">
                        <button type="button" @click="$inertia.get('/auth/google')" class="w-full flex items-center justify-center gap-3 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 font-bold py-3 px-4 rounded-lg transition-colors shadow-sm">
                            <svg class="w-5 h-5" viewBox="0 0 48 48">
                                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.73 17.74 9.5 24 9.5z"></path>
                                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"></path>
                                <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"></path>
                                <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"></path>
                            </svg>
                            Google
                        </button>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="flex justify-between items-center text-xs text-gray-400 font-medium mt-8 lg:mt-0">
                <p>&copy; {{ new Date().getFullYear() }} SIAKAD Enterprises LTD.</p>
                <a href="#" class="hover:text-gray-600 transition-colors">Privacy Policy</a>
            </div>
        </div>

        <!-- Right Side: Blue Graphic Section -->
        <div class="hidden lg:flex lg:w-1/2 p-4">
            <div class="w-full h-full bg-blue-600 rounded-[2rem] p-12 flex flex-col relative overflow-hidden shadow-2xl">
                <!-- Decorative Elements -->
                <div class="absolute top-0 right-0 w-96 h-96 bg-white opacity-5 rounded-full blur-3xl transform translate-x-1/2 -translate-y-1/4"></div>
                <div class="absolute bottom-0 left-0 w-80 h-80 bg-blue-400 opacity-20 rounded-full blur-3xl transform -translate-x-1/4 translate-y-1/4"></div>
                
                <!-- Text Content -->
                <div class="relative z-10 max-w-lg mt-8">
                    <h2 class="text-4xl lg:text-5xl font-bold text-white mb-6 leading-tight">
                        Effortlessly manage your school and operations.
                    </h2>
                    <p class="text-blue-100 text-lg font-medium opacity-90">
                        Log in to access your SIAKAD dashboard and manage your academic administration seamlessly.
                    </p>
                </div>

                <!-- Dashboard Mockup Image -->
                <div class="relative z-10 flex-1 w-full mt-12 flex items-end justify-center perspective-1000">
                    <div class="w-[110%] ml-[10%] h-[90%] bg-white/10 backdrop-blur-sm rounded-tl-2xl rounded-tr-2xl border border-white/20 p-2 shadow-2xl transform rotate-1 lg:rotate-2">
                        <div class="w-full h-full bg-white rounded-xl shadow-inner overflow-hidden border border-gray-100 relative">
                            <!-- Fake Dashboard UI -->
                            <div class="h-10 border-b border-gray-100 flex items-center px-4 gap-2 bg-gray-50/50">
                                <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                                <div class="w-3 h-3 rounded-full bg-green-400"></div>
                            </div>
                            <div class="p-6 grid grid-cols-3 gap-6 opacity-80 pointer-events-none">
                                <div class="col-span-2 space-y-4">
                                    <div class="h-32 bg-blue-50 rounded-xl flex items-center p-6 gap-4">
                                        <div class="w-16 h-16 rounded-full bg-blue-100"></div>
                                        <div class="space-y-2">
                                            <div class="h-4 w-32 bg-blue-200 rounded"></div>
                                            <div class="h-3 w-24 bg-blue-100 rounded"></div>
                                        </div>
                                    </div>
                                    <div class="h-48 bg-gray-50 rounded-xl border border-gray-100"></div>
                                </div>
                                <div class="col-span-1 space-y-4">
                                    <div class="h-24 bg-purple-50 rounded-xl border border-purple-100"></div>
                                    <div class="h-56 bg-green-50 rounded-xl border border-green-100 relative overflow-hidden flex items-center justify-center">
                                        <!-- Fake donut chart -->
                                        <div class="w-32 h-32 rounded-full border-[12px] border-green-200 border-t-green-400 border-r-green-500"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
