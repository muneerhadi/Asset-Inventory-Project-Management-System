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
        ? 'inline-flex items-center px-1 pt-1 border-b-2 border-slate-900 text-sm font-medium leading-5 text-slate-900 dark:border-slate-100 dark:text-slate-100 focus:outline-none focus:border-slate-900 dark:focus:border-slate-100 transition duration-150 ease-in-out'
        : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-slate-500 hover:text-slate-900 hover:border-slate-300 focus:outline-none focus:text-slate-900 focus:border-slate-300 dark:text-slate-400 dark:hover:text-slate-50 dark:hover:border-slate-600 dark:focus:text-slate-50 dark:focus:border-slate-600 transition duration-150 ease-in-out',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
