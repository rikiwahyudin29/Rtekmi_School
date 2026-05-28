<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: ''
    },
    options: {
        type: Array,
        default: () => []
    },
    placeholder: {
        type: String,
        default: 'Pilih opsi...'
    },
    // The key in the option object to display
    labelKey: {
        type: String,
        default: 'label'
    },
    // The key in the option object to use as value
    valueKey: {
        type: String,
        default: 'value'
    },
    disabled: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:modelValue', 'change']);

const isOpen = ref(false);
const searchQuery = ref('');
const selectRef = ref(null);

const filteredOptions = computed(() => {
    if (!searchQuery.value) return props.options;
    return props.options.filter(opt => {
        const label = String(opt[props.labelKey] || '').toLowerCase();
        return label.includes(searchQuery.value.toLowerCase());
    });
});

const selectedOption = computed(() => {
    return props.options.find(opt => opt[props.valueKey] === props.modelValue);
});

const selectedLabel = computed(() => {
    if (selectedOption.value) {
        return selectedOption.value[props.labelKey];
    }
    return props.placeholder;
});

const toggleDropdown = () => {
    if (props.disabled) return;
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        searchQuery.value = '';
    }
};

const selectOption = (opt) => {
    emit('update:modelValue', opt[props.valueKey]);
    emit('change', opt[props.valueKey]);
    isOpen.value = false;
};

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
    if (selectRef.value && !selectRef.value.contains(event.target)) {
        isOpen.value = false;
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
    <div class="relative w-full text-left" ref="selectRef">
        <!-- Trigger Button -->
        <button 
            type="button" 
            @click="toggleDropdown"
            class="flex items-center justify-between w-full p-2.5 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white"
            :class="{'opacity-60 cursor-not-allowed': disabled}"
            :disabled="disabled"
        >
            <span class="block truncate" :class="{'text-gray-500 dark:text-gray-400': !selectedOption}">
                {{ selectedLabel }}
            </span>
            <i class="fas fa-chevron-down text-gray-400 text-xs ml-2"></i>
        </button>

        <!-- Dropdown Menu -->
        <div 
            v-if="isOpen" 
            class="absolute z-[100] w-full mt-1 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 max-h-60 overflow-hidden flex flex-col"
        >
            <div class="px-3 py-2 border-b border-gray-100 dark:border-gray-700 shrink-0">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input 
                        v-model="searchQuery" 
                        type="text" 
                        class="w-full pl-8 pr-3 py-1.5 text-sm border border-gray-300 rounded-md bg-gray-50 focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white"
                        placeholder="Cari..."
                        autofocus
                        @click.stop
                    >
                </div>
            </div>
            
            <ul class="overflow-y-auto flex-1 custom-scrollbar">
                <li 
                    v-if="filteredOptions.length === 0" 
                    class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 text-center"
                >
                    Tidak ada hasil
                </li>
                <li 
                    v-for="opt in filteredOptions" 
                    :key="opt[valueKey]"
                    @click="selectOption(opt)"
                    class="px-4 py-2 text-sm cursor-pointer hover:bg-primary-50 dark:hover:bg-primary-900/30 text-gray-800 dark:text-gray-200"
                    :class="{'bg-primary-100 dark:bg-primary-900/50 text-primary-700 font-bold': opt[valueKey] === modelValue}"
                >
                    {{ opt[labelKey] }}
                </li>
            </ul>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #475569;
}
</style>
