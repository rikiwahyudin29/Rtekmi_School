<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    disabled: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:modelValue', 'change']);

const isOpen = ref(false);
const containerRef = ref(null);

// parse current value
const currentHour = ref(null);
const currentMinute = ref(null);

const initFromValue = () => {
    if (props.modelValue) {
        const parts = props.modelValue.split(':');
        if (parts.length >= 2) {
            currentHour.value = parseInt(parts[0]);
            currentMinute.value = parseInt(parts[1]);
            return;
        }
    }
    currentHour.value = null;
    currentMinute.value = null;
};

watch(() => props.modelValue, () => {
    initFromValue();
}, { immediate: true });

const displayValue = computed(() => {
    if (currentHour.value === null || currentMinute.value === null) return '';
    const h = currentHour.value.toString().padStart(2, '0');
    const m = currentMinute.value.toString().padStart(2, '0');
    return `${h}:${m}`;
});

const hours = Array.from({ length: 24 }, (_, i) => i);
const minutes = [0, 15, 30, 45];

const selectHour = (h) => {
    currentHour.value = h;
};

const selectMinute = (m) => {
    currentMinute.value = m;
};

const save = () => {
    if (currentHour.value !== null && currentMinute.value !== null) {
        const val = displayValue.value;
        emit('update:modelValue', val);
        emit('change', val);
        isOpen.value = false;
    }
};

const clear = () => {
    currentHour.value = null;
    currentMinute.value = null;
    emit('update:modelValue', '');
    emit('change', '');
};

const close = () => {
    isOpen.value = false;
    initFromValue(); // revert to saved value
};

const handleClickOutside = (event) => {
    if (containerRef.value && !containerRef.value.contains(event.target)) {
        close();
    }
};

onMounted(() => {
    document.addEventListener('mousedown', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('mousedown', handleClickOutside);
});
</script>

<template>
    <div class="relative w-full text-left" ref="containerRef">
        <!-- Input Field -->
        <div 
            @click="!disabled && (isOpen = !isOpen)"
            class="flex items-center justify-between w-full p-2.5 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white cursor-pointer"
            :class="{'opacity-60 cursor-not-allowed': disabled}"
        >
            <span :class="displayValue ? 'text-gray-900 dark:text-white' : 'text-gray-400'">
                {{ displayValue || 'Pilih Jam' }}
            </span>
            <i class="far fa-clock text-gray-400"></i>
        </div>

        <!-- Picker Popup -->
        <div v-if="isOpen" class="absolute z-[100] w-[320px] mt-1 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden transform transition-all origin-top-left -left-4 sm:left-0">
            <div class="p-4">
                <div class="mb-4">
                    <div class="text-sm font-bold text-gray-800 dark:text-gray-200 mb-2">Hours</div>
                    <div class="grid grid-cols-6 gap-1.5">
                        <button 
                            v-for="h in hours" 
                            :key="h" 
                            type="button"
                            @click="selectHour(h)"
                            class="w-full aspect-square flex items-center justify-center text-sm rounded border transition-colors focus:outline-none"
                            :class="currentHour === h ? 'bg-primary-500 border-primary-500 text-white font-bold shadow-sm' : 'bg-white border-gray-200 text-gray-700 hover:border-primary-300 hover:bg-primary-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700'"
                        >
                            {{ h }}
                        </button>
                    </div>
                </div>

                <div>
                    <div class="text-sm font-bold text-gray-800 dark:text-gray-200 mb-2">Minutes</div>
                    <div class="grid grid-cols-4 gap-2 w-2/3">
                        <button 
                            v-for="m in minutes" 
                            :key="m" 
                            type="button"
                            @click="selectMinute(m)"
                            class="py-1.5 text-sm rounded border transition-colors focus:outline-none"
                            :class="currentMinute === m ? 'bg-primary-500 border-primary-500 text-white font-bold shadow-sm' : 'bg-white border-gray-200 text-gray-700 hover:border-primary-300 hover:bg-primary-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700'"
                        >
                            {{ m }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="flex border-t border-gray-200 dark:border-gray-700">
                <button type="button" @click="save" class="flex-1 py-2.5 text-sm font-bold text-white bg-green-600 hover:bg-green-700 transition-colors">
                    Save
                </button>
                <button type="button" @click="clear" class="flex-1 py-2.5 text-sm font-bold text-white bg-amber-500 hover:bg-amber-600 transition-colors">
                    Clear
                </button>
                <button type="button" @click="close" class="flex-1 py-2.5 text-sm font-bold text-white bg-red-600 hover:bg-red-700 transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
</template>
