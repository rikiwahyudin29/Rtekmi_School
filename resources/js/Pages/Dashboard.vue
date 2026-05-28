<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <DashboardLayout>
        
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-1">Dashboard</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Plan, prioritize, and accomplish your tasks with ease.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button class="bg-green-700 hover:bg-green-800 text-white font-bold py-2.5 px-5 rounded-full text-sm shadow-sm transition-colors flex items-center gap-2">
                        <i class="fas fa-plus"></i> Add Project
                    </button>
                    <button class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-bold py-2.5 px-5 rounded-full text-sm shadow-sm transition-colors">
                        Import Data
                    </button>
                </div>
            </div>
        </div>

        <!-- 4 Stat Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Card 1 -->
            <div class="bg-green-700 rounded-3xl p-6 text-white shadow-sm relative overflow-hidden group cursor-pointer hover:shadow-md transition-shadow">
                <div class="absolute top-6 right-6 w-8 h-8 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm group-hover:bg-white/30 transition-colors">
                    <i class="fas fa-arrow-up text-white transform rotate-45 text-sm"></i>
                </div>
                <h3 class="text-green-50 font-medium mb-2">Total Projects</h3>
                <p class="text-5xl font-bold mb-4 tracking-tight">24</p>
                <div class="flex items-center gap-2 text-xs">
                    <span class="bg-green-600/50 px-2 py-1 rounded text-green-50 font-bold"><i class="fas fa-arrow-up mr-1"></i> 5%</span>
                    <span class="text-green-100/80">Increased from last month</span>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group cursor-pointer hover:shadow-md transition-shadow">
                <div class="absolute top-6 right-6 w-8 h-8 border border-gray-200 dark:border-gray-600 rounded-full flex items-center justify-center group-hover:bg-gray-50 dark:group-hover:bg-gray-700 transition-colors">
                    <i class="fas fa-arrow-up text-gray-400 transform rotate-45 text-sm"></i>
                </div>
                <h3 class="text-gray-500 dark:text-gray-400 font-medium mb-2">Ended Projects</h3>
                <p class="text-5xl font-bold mb-4 tracking-tight text-gray-900 dark:text-white">10</p>
                <div class="flex items-center gap-2 text-xs">
                    <span class="bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 px-2 py-1 rounded font-bold border border-green-100 dark:border-green-800"><i class="fas fa-arrow-up mr-1"></i> 6%</span>
                    <span class="text-gray-400">Increased from last month</span>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group cursor-pointer hover:shadow-md transition-shadow">
                <div class="absolute top-6 right-6 w-8 h-8 border border-gray-200 dark:border-gray-600 rounded-full flex items-center justify-center group-hover:bg-gray-50 dark:group-hover:bg-gray-700 transition-colors">
                    <i class="fas fa-arrow-up text-gray-400 transform rotate-45 text-sm"></i>
                </div>
                <h3 class="text-gray-500 dark:text-gray-400 font-medium mb-2">Running Projects</h3>
                <p class="text-5xl font-bold mb-4 tracking-tight text-gray-900 dark:text-white">12</p>
                <div class="flex items-center gap-2 text-xs">
                    <span class="bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 px-2 py-1 rounded font-bold border border-green-100 dark:border-green-800"><i class="fas fa-arrow-up mr-1"></i> 2%</span>
                    <span class="text-gray-400">Increased from last month</span>
                </div>
            </div>

            <!-- Card 4 -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group cursor-pointer hover:shadow-md transition-shadow">
                <div class="absolute top-6 right-6 w-8 h-8 border border-gray-200 dark:border-gray-600 rounded-full flex items-center justify-center group-hover:bg-gray-50 dark:group-hover:bg-gray-700 transition-colors">
                    <i class="fas fa-arrow-up text-gray-400 transform rotate-45 text-sm"></i>
                </div>
                <h3 class="text-gray-500 dark:text-gray-400 font-medium mb-2">Pending Project</h3>
                <p class="text-5xl font-bold mb-4 tracking-tight text-gray-900 dark:text-white">2</p>
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-gray-500 dark:text-gray-400 font-medium">On Discuss</span>
                </div>
            </div>
        </div>

        <!-- Middle Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Analytics Chart Mockup -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 lg:col-span-1">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Project Analytics</h3>
                <div class="flex items-end justify-between h-40 gap-2">
                    <div class="w-full bg-gray-100 dark:bg-gray-700 h-1/2 rounded-t-full relative"></div>
                    <div class="w-full bg-green-600 h-3/4 rounded-t-full relative"></div>
                    <div class="w-full bg-green-400 h-full rounded-t-full relative">
                        <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-white dark:bg-gray-800 text-xs font-bold px-2 py-1 rounded shadow border border-gray-100 dark:border-gray-700">78%</div>
                    </div>
                    <div class="w-full bg-green-900 h-[80%] rounded-t-full relative"></div>
                    <div class="w-full bg-gray-100 dark:bg-gray-700 h-[60%] rounded-t-full relative"></div>
                    <div class="w-full bg-gray-100 dark:bg-gray-700 h-[45%] rounded-t-full relative"></div>
                    <div class="w-full bg-gray-100 dark:bg-gray-700 h-[30%] rounded-t-full relative"></div>
                </div>
                <div class="flex justify-between text-xs font-bold text-gray-400 mt-4 px-2">
                    <span>S</span><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span>
                </div>
            </div>

            <!-- Reminders -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 lg:col-span-1 flex flex-col justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Reminders</h3>
                    <h4 class="text-2xl font-bold text-green-900 dark:text-green-50 leading-tight mb-2">Meeting with Arc Company</h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Time : 02.00 pm - 04.00 pm</p>
                </div>
                <button class="w-full bg-green-700 hover:bg-green-800 text-white font-bold py-3 px-4 rounded-xl shadow-md transition-colors mt-6 flex justify-center items-center gap-2">
                    <i class="fas fa-video"></i> Start Meeting
                </button>
            </div>

            <!-- Project List -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 lg:col-span-1">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Project</h3>
                    <button class="text-xs font-bold border border-gray-200 dark:border-gray-600 rounded-full px-3 py-1 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">+ New</button>
                </div>
                <div class="space-y-4">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600"><i class="fas fa-code"></i></div>
                        <div>
                            <h4 class="font-bold text-sm text-gray-900 dark:text-white">Develop API Endpoints</h4>
                            <p class="text-xs text-gray-400">Due date: Nov 26, 2024</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-teal-100 flex items-center justify-center text-teal-600"><i class="fas fa-layer-group"></i></div>
                        <div>
                            <h4 class="font-bold text-sm text-gray-900 dark:text-white">Onboarding Flow</h4>
                            <p class="text-xs text-gray-400">Due date: Nov 28, 2024</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600"><i class="fas fa-desktop"></i></div>
                        <div>
                            <h4 class="font-bold text-sm text-gray-900 dark:text-white">Build Dashboard</h4>
                            <p class="text-xs text-gray-400">Due date: Nov 30, 2024</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600"><i class="fas fa-bolt"></i></div>
                        <div>
                            <h4 class="font-bold text-sm text-gray-900 dark:text-white">Optimize Page Load</h4>
                            <p class="text-xs text-gray-400">Due date: Dec 5, 2024</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </DashboardLayout>
</template>
