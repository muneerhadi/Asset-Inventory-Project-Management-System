<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-slate-900 text-start text-base font-medium text-slate-900 bg-slate-100 focus:outline-none focus:text-slate-900 focus:bg-slate-200 focus:border-slate-900 dark:border-slate-100 dark:text-slate-100 dark:bg-slate-900/60 dark:focus:bg-slate-800 dark:focus:border-slate-100 transition duration-150 ease-in-out'
        : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:text-slate-900 focus:bg-slate-50 focus:border-slate-300 dark:text-slate-400 dark:hover:text-slate-50 dark:hover:bg-slate-900/50 dark:hover:border-slate-600 dark:focus:text-slate-50 dark:focus:bg-slate-900/50 dark:focus:border-slate-600 transition duration-150 ease-in-out',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
