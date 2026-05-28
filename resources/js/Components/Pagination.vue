<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: Array,
});
</script>

<template>
    <div v-if="links && links.length > 3">
        <div class="flex flex-wrap -mb-1">
            <template v-for="(link, p) in links" :key="p">
                <div v-if="link.url === null" class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border border-gray-200 dark:border-gray-700 rounded-xl" v-html="link.label" />
                <Link v-else class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded-xl focus:border-primary-500 focus:text-primary-500 transition-colors" :class="{ 'bg-primary-600 text-white border-primary-600': link.active, 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700': !link.active }" :href="link.url" v-html="link.label" preserve-scroll preserve-state />
            </template>
        </div>
    </div>
</template>
