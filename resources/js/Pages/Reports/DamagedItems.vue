<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { formatDate } from '@/utils/dateFormat';

const props = defineProps({
    items: Array,
});

const currency = ref('USD');

const summary = computed(() => {
    const toCode = (i) => (i?.currency?.code || '').toUpperCase();
    const usdItems = props.items.filter(i => toCode(i) === 'USD');
    const afgItems = props.items.filter(i => toCode(i) === 'AFG');
    const sum = (arr) => arr.reduce((s, i) => s + (parseFloat(i?.price) || 0), 0);
    return {
        total_damaged: props.items.length,
        usd: { count: usdItems.length, total: sum(usdItems) },
        afg: { count: afgItems.length, total: sum(afgItems) },
    };
});

const filteredItems = computed(() => {
    const code = currency.value.toUpperCase();
    return props.items.filter(i => (i?.currency?.code || '').toUpperCase() === code);
});

const formatMoney = (value, code) => {
    const num = parseFloat(value);
    if (isNaN(num)) return '-';
    return `${num.toFixed(2)}${code ? ` ${code}` : ''}`;
};

const printPage = () => {
    window.print();
};
</script>

<template>
    <Head title="Damaged Items Report" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-slate-900 dark:text-slate-50">
                        <i class="fa-solid fa-triangle-exclamation mr-3 text-rose-600 dark:text-rose-400"></i>
                        Damaged Items Report
                    </h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                        Overview of all damaged items in the system
                    </p>
                </div>
                <div class="flex gap-2 print:hidden">
                    <button
                        type="button"
                        class="rounded-lg bg-gradient-to-r from-blue-600 to-sky-600 px-4 py-2 text-sm font-medium text-white shadow-md transition hover:shadow-lg dark:from-blue-700 dark:to-sky-700"
                        @click="printPage"
                    >
                        <i class="fa-solid fa-print mr-2"></i>
                        Print
                    </button>
                    <Link
                        :href="route('reports.index')"
                        class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-medium text-slate-700 shadow-md transition hover:shadow-lg dark:bg-slate-700 dark:text-slate-200"
                    >
                        <i class="fa-solid fa-arrow-left mr-2"></i>
                        Back
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6 print:py-0 print:m-0 print:p-0">
            <div class="mx-auto max-w-6xl space-y-6 print:max-w-none print:m-0 print:p-4">
                <!-- Screen View Content -->
                <div class="print:hidden">
                    <!-- Summary Cards -->
                    <div class="grid gap-4 md:grid-cols-1">
                        <div class="overflow-hidden rounded-xl border border-rose-200/50 bg-gradient-to-br from-rose-100/60 via-pink-50/40 to-red-100/50 p-6 shadow-md dark:border-rose-900/30 dark:bg-gradient-to-br dark:from-rose-950/40 dark:via-slate-900/60 dark:to-red-950/40">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">
                                        Total Damaged Items
                                    </p>
                                    <p class="mt-3 text-4xl font-bold text-slate-900 dark:text-slate-50">
                                        {{ summary.total_damaged }}
                                    </p>
                                </div>
                                <span class="text-rose-600 dark:text-rose-400">
                                    <i class="fa-solid fa-triangle-exclamation text-4xl"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="overflow-hidden rounded-xl border border-slate-200/50 bg-white/70 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-slate-100 dark:border-slate-700 dark:from-slate-900 dark:to-slate-800">
                                        <th class="px-6 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">
                                            <i class="fa-solid fa-cube mr-2 text-slate-600 dark:text-slate-400"></i>
                                            Item Tag
                                        </th>
                                        <th class="px-6 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">
                                            <i class="fa-solid fa-box mr-2 text-slate-600 dark:text-slate-400"></i>
                                            Name
                                        </th>
                                        <th class="px-6 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">
                                            <i class="fa-solid fa-tasks mr-2 text-slate-600 dark:text-slate-400"></i>
                                            Situation
                                        </th>
                                        <th class="px-6 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">
                                            <i class="fa-solid fa-tag mr-2 text-slate-600 dark:text-slate-400"></i>
                                            Project
                                        </th>
                                        <th class="px-6 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">
                                            <i class="fa-solid fa-map-marker mr-2 text-slate-600 dark:text-slate-400"></i>
                                            Location
                                        </th>
                                        <th class="px-6 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">
                                            <i class="fa-solid fa-dollar-sign mr-2 text-slate-600 dark:text-slate-400"></i>
                                            Price
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                    <tr
                                        v-for="(item, index) in items"
                                        :key="item.id"
                                        :class="[
                                            'transition',
                                            index % 2 === 0
                                                ? 'bg-white dark:bg-slate-900/50'
                                                : 'bg-slate-50/50 dark:bg-slate-800/30'
                                        ]"
                                    >
                                        <td class="px-6 py-4 font-medium text-slate-900 dark:text-slate-50">
                                            {{ item.tag_number }}
                                        </td>
                                        <td class="px-6 py-4 text-slate-700 dark:text-slate-300">
                                            {{ item.name }}
                                        </td>
                                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                                            {{ item.project?.code || '-' }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span v-if="item.status?.name" class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-medium text-rose-800 dark:bg-rose-900/30 dark:text-rose-200">
                                                {{ item.status.name }}
                                            </span>
                                            <span v-else class="text-slate-500 dark:text-slate-400">-</span>
                                        </td>
                                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                                            {{ item.location || '-' }}<span v-if="item.sublocation" class="text-slate-500"> / {{ item.sublocation }}</span>
                                        </td>
                                        <td class="px-6 py-4 font-medium text-slate-900 dark:text-slate-50">
                                            {{ item.price ? parseFloat(item.price).toFixed(2) + ' ' + (item.currency?.code || '') : '-' }}
                                        </td>
                                    </tr>
                                    <tr v-if="!items.length">
                                        <td
                                            colspan="6"
                                            class="px-6 py-8 text-center text-slate-500 dark:text-slate-400"
                                        >
                                            <i class="fa-solid fa-inbox text-4xl mb-2 block opacity-50"></i>
                                            No damaged items found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Print View Content -->
                <div class="hidden print:block">
                    <!-- Print Header -->
                    <div class="border-b-4 border-slate-900 pb-8 text-center mb-8">
                        <!-- Logo -->
                        <div class="mb-4 flex justify-center">
                            <div class="h-24 w-24">
                                <img src="/storage/logo/union-aid-logo.png" alt="Logo" class="h-full w-full object-contain">
                            </div>
                        </div>
                        
                        <!-- UNION AID Title -->
                        <h1 class="text-4xl font-bold text-slate-900 tracking-wide">
                            UNION AID
                        </h1>
                        
                        <!-- Server Time -->
                        <p class="mt-3 text-sm font-medium text-slate-700">
                            {{ formatDate(new Date()) }}
                        </p>
                    </div>

                    <!-- Items Table for Print -->
                    <div class="mt-8">
                        <table class="w-full text-xs border-collapse">
                            <thead>
                                <tr class="border-b-2 border-slate-900 bg-slate-100">
                                    <th class="border border-slate-900 px-3 py-2 text-left font-bold">Item Tag</th>
                                    <th class="border border-slate-900 px-3 py-2 text-left font-bold">Name</th>
                                    <th class="border border-slate-900 px-3 py-2 text-left font-bold">Project</th>
                                    <th class="border border-slate-900 px-3 py-2 text-left font-bold">Situation</th>
                                    <th class="border border-slate-900 px-3 py-2 text-left font-bold">Location</th>
                                    <th class="border border-slate-900 px-3 py-2 text-left font-bold">Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in items" :key="item.id" class="border border-slate-900">
                                    <td class="border border-slate-900 px-3 py-2">{{ item.tag_number }}</td>
                                    <td class="border border-slate-900 px-3 py-2">{{ item.name }}</td>
                                    <td class="border border-slate-900 px-3 py-2">{{ item.project?.code || '-' }}</td>
                                    <td class="border border-slate-900 px-3 py-2">{{ item.status?.name || '-' }}</td>
                                    <td class="border border-slate-900 px-3 py-2">
                                        {{ item.location || '-' }}<span v-if="item.sublocation"> / {{ item.sublocation }}</span>
                                    </td>
                                    <td class="border border-slate-900 px-3 py-2">{{ item.price ? parseFloat(item.price).toFixed(2) + ' ' + (item.currency?.code || '') : '-' }}</td>
                                </tr>
                                <tr v-if="!items.length">
                                    <td colspan="6" class="border border-slate-900 px-3 py-4 text-center font-medium">
                                        No damaged items found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@page {
    margin: 0;
}

html, body {
    margin: 0 !important;
    padding: 0 !important;
}
</style>
