<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage } from '@inertiajs/vue3';

defineProps({
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

const auth = usePage().props.auth;
</script>

<template>
    <Head title="Profile Saya" />

    <DashboardLayout>
        <div class="py-6 space-y-6">
            
            <!-- Header Banner -->
            <div class="bg-gradient-to-r from-primary-600 to-primary-800 rounded-[2rem] p-8 md:p-10 text-white shadow-xl relative overflow-hidden">
                <div class="absolute -right-10 -top-10 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute right-32 -bottom-10 w-40 h-40 bg-black/10 rounded-full blur-2xl"></div>
                
                <div class="relative z-10 flex flex-col md:flex-row items-center gap-8">
                    <!-- Profile Picture -->
                    <div class="w-32 h-32 rounded-[2rem] border-4 border-white/20 overflow-hidden bg-white/10 shrink-0 shadow-2xl rotate-3 hover:rotate-0 transition-transform duration-300">
                        <img v-if="profil && profil.foto && profil.foto !== 'default.png'" :src="(auth.user.role === 'guru' ? '/uploads/guru/' : '/uploads/siswa/') + profil.foto" class="w-full h-full object-cover">
                        <img v-else :src="`https://ui-avatars.com/api/?name=${auth.user.nama_lengkap || auth.user.username}&background=random`" class="w-full h-full object-cover">
                    </div>
                    
                    <!-- Profile Info -->
                    <div class="text-center md:text-left">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md mb-3 border border-white/20">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-white">{{ auth.user.role }}</span>
                        </div>
                        <h1 class="text-3xl md:text-4xl font-black mb-2 tracking-tight">{{ auth.user.nama_lengkap || auth.user.username }}</h1>
                        <div class="flex flex-col md:flex-row items-center gap-4 text-primary-100 font-medium">
                            <span class="flex items-center gap-2"><i class="fas fa-envelope"></i> {{ auth.user.email }}</span>
                            <span v-if="auth.user.nomor_wa" class="flex items-center gap-2"><i class="fab fa-whatsapp"></i> {{ auth.user.nomor_wa }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                
                <!-- Left Column: Update Info -->
                <div class="xl:col-span-2 space-y-6">
                    <div class="bg-white dark:bg-gray-800 rounded-[2rem] p-6 md:p-8 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group hover:shadow-md transition-shadow">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-primary-50 dark:bg-primary-900/10 rounded-bl-[100px] -z-10 transition-transform group-hover:scale-110"></div>
                        
                        <div class="mb-8 border-b border-gray-100 dark:border-gray-700 pb-4">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-primary-50 dark:bg-primary-900/20 text-primary-600 flex items-center justify-center shrink-0">
                                    <i class="fas fa-user-edit text-lg"></i>
                                </div>
                                <div>
                                    Informasi Personal
                                    <p class="text-xs font-normal text-gray-500 dark:text-gray-400 mt-1">Perbarui data diri, foto profil, dan informasi khusus Anda.</p>
                                </div>
                            </h3>
                        </div>

                        <UpdateProfileInformationForm
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                            :profil="profil"
                        />
                    </div>
                </div>

                <!-- Right Column: Security & Danger Zone -->
                <div class="space-y-6">
                    
                    <!-- Update Password -->
                    <div class="bg-white dark:bg-gray-800 rounded-[2rem] p-6 md:p-8 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group hover:shadow-md transition-shadow">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-amber-50 dark:bg-amber-900/10 rounded-bl-[100px] -z-10 transition-transform group-hover:scale-110"></div>
                        
                        <div class="mb-8 border-b border-gray-100 dark:border-gray-700 pb-4">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-amber-50 dark:bg-amber-900/20 text-amber-600 flex items-center justify-center shrink-0">
                                    <i class="fas fa-shield-alt text-lg"></i>
                                </div>
                                <div>
                                    Keamanan Akun
                                    <p class="text-xs font-normal text-gray-500 dark:text-gray-400 mt-1">Pastikan akun menggunakan password yang kuat.</p>
                                </div>
                            </h3>
                        </div>

                        <UpdatePasswordForm />
                    </div>

                    <!-- Delete Account -->
                    <div class="bg-white dark:bg-gray-800 rounded-[2rem] p-6 md:p-8 shadow-sm border border-red-100 dark:border-red-900/30 relative overflow-hidden group hover:shadow-md transition-shadow">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-red-50 dark:bg-red-900/10 rounded-bl-[100px] -z-10 transition-transform group-hover:scale-110"></div>
                        
                        <div class="mb-8 border-b border-gray-100 dark:border-gray-700 pb-4">
                            <h3 class="text-xl font-bold text-red-600 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-red-50 dark:bg-red-900/20 text-red-600 flex items-center justify-center shrink-0">
                                    <i class="fas fa-exclamation-triangle text-lg"></i>
                                </div>
                                <div>
                                    Zona Bahaya
                                    <p class="text-xs font-normal text-gray-500 dark:text-gray-400 mt-1">Hapus akun secara permanen dari sistem.</p>
                                </div>
                            </h3>
                        </div>

                        <DeleteUserForm />
                    </div>

                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
