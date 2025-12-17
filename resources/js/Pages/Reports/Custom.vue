<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { formatDate } from '@/utils/dateFormat';

const props = defineProps({
    items: Array,
    filters: Object,
    projects: Array,
    statuses: Array,
});

const localFilters = ref({
    project_id: props.filters.project_id ?? null,
    status_id: props.filters.status_id ?? null,
    from: props.filters.from ?? null,
    to: props.filters.to ?? null,
});

const applyFilters = () => {
    router.get(route('reports.custom'), localFilters.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    localFilters.value = {
        project_id: null,
        status_id: null,
        from: null,
        to: null,
    };
    applyFilters();
};

const printPage = () => {
    window.print();
};

watch(
    () => props.filters,
    (newFilters) => {
        localFilters.value = {
            project_id: newFilters.project_id ?? null,
            status_id: newFilters.status_id ?? null,
            from: newFilters.from ?? null,
            to: newFilters.to ?? null,
        };
    },
    { deep: true },
);
</script>

<template>
    <Head title="Custom Report" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-slate-900 dark:text-slate-50">
                        <i class="fa-solid fa-filter mr-3 text-purple-600 dark:text-purple-400"></i>
                        Custom Report
                    </h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                        Build your own inventory report with custom filters
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
                    <!-- Filter Section -->
                    <div class="overflow-hidden rounded-xl border border-slate-200/50 bg-white/70 p-6 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70">
                        <h3 class="mb-4 text-lg font-semibold text-slate-900 dark:text-slate-50">
                            <i class="fa-solid fa-sliders mr-2 text-slate-600 dark:text-slate-400"></i>
                            Filter Criteria
                        </h3>
                        <form
                            class="grid gap-4 md:grid-cols-3"
                            @submit.prevent="applyFilters"
                        >
                            <div class="flex flex-col">
                                <label class="mb-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                                    <i class="fa-solid fa-folder mr-1 text-slate-600 dark:text-slate-400"></i>
                                    Project
                                </label>
                                <select
                                    v-model="localFilters.project_id"
                                    class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-50"
                                >
                                    <option :value="null">All projects</option>
                                    <option
                                        v-for="project in projects"
                                        :key="project.id"
                                        :value="project.id"
                                    >
                                        {{ project.code }} - {{ project.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="flex flex-col">
                                <label class="mb-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                                    <i class="fa-solid fa-circle-check mr-1 text-slate-600 dark:text-slate-400"></i>
                                    Status
                                </label>
                                <select
                                    v-model="localFilters.status_id"
                                    class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-50"
                                >
                                    <option :value="null">All statuses</option>
                                    <option
                                        v-for="status in statuses"
                                        :key="status.id"
                                        :value="status.id"
                                    >
                                        {{ status.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="flex flex-col">
                                <label class="mb-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                                    <i class="fa-solid fa-barcode mr-1 text-slate-600 dark:text-slate-400"></i>
                                    Item Code
                                </label>
                                <input
                                    v-model="localFilters.item_code"
                                    type="text"
                                    placeholder="e.g. ITM-001"
                                    class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 transition placeholder-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-50 dark:placeholder-slate-500"
                                />
                            </div>

                            <div class="flex flex-col">
                                <label class="mb-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                                    <i class="fa-solid fa-calendar mr-1 text-slate-600 dark:text-slate-400"></i>
                                    From Date
                                </label>
                                <input
                                    v-model="localFilters.from"
                                    type="date"
                                    class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-50"
                                />
                            </div>

                            <div class="flex flex-col">
                                <label class="mb-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                                    <i class="fa-solid fa-calendar mr-1 text-slate-600 dark:text-slate-400"></i>
                                    To Date
                                </label>
                                <input
                                    v-model="localFilters.to"
                                    type="date"
                                    class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-50"
                                />
                            </div>

                            <div class="flex items-end gap-2">
                                <button
                                    type="submit"
                                    class="inline-flex items-center rounded-lg bg-gradient-to-r from-blue-600 to-sky-600 px-4 py-2 text-sm font-medium text-white shadow-md transition hover:shadow-lg dark:from-blue-700 dark:to-sky-700"
                                >
                                    <i class="fa-solid fa-magnifying-glass mr-2"></i>
                                    Apply Filters
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-lg bg-slate-200 px-4 py-2 text-sm font-medium text-slate-700 shadow-md transition hover:shadow-lg dark:bg-slate-700 dark:text-slate-200"
                                    @click="clearFilters"
                                >
                                    <i class="fa-solid fa-times mr-2"></i>
                                    Clear
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Items Table -->
                    <div class="overflow-hidden rounded-xl border border-slate-200/50 bg-white/70 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-slate-100 dark:border-slate-700 dark:from-slate-900 dark:to-slate-800">
                                        <th class="px-6 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">
                                            <i class="fa-solid fa-folder mr-2 text-slate-600 dark:text-slate-400"></i>
                                            Project
                                        </th>
                                        <th class="px-6 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">
                                            <i class="fa-solid fa-barcode mr-2 text-slate-600 dark:text-slate-400"></i>
                                            Code
                                        </th>
                                        <th class="px-6 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">
                                            <i class="fa-solid fa-box mr-2 text-slate-600 dark:text-slate-400"></i>
                                            Name
                                        </th>
                                        <th class="px-6 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">
                                            <i class="fa-solid fa-check-circle mr-2 text-slate-600 dark:text-slate-400"></i>
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">
                                            <i class="fa-solid fa-calendar mr-2 text-slate-600 dark:text-slate-400"></i>
                                            Purchase Date
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
                                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                                            {{ item.project?.code || '-' }}
                                        </td>
                                        <td class="px-6 py-4 font-medium text-slate-900 dark:text-slate-50">
                                            {{ item.item_code }}
                                        </td>
                                        <td class="px-6 py-4 text-slate-700 dark:text-slate-300">
                                            {{ item.name }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span v-if="item.status?.name" class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-900 dark:bg-blue-900 dark:text-blue-100">
                                                {{ item.status.name }}
                                            </span>
                                            <span v-else class="text-slate-500 dark:text-slate-400">-</span>
                                        </td>
                                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                                            {{ formatDate(item.purchase_date) }}
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
                                            colspan="7"
                                            class="px-6 py-8 text-center text-slate-500 dark:text-slate-400"
                                        >
                                            <i class="fa-solid fa-inbox text-4xl mb-2 block opacity-50"></i>
                                            No items match the selected filters.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Print View -->
                <div class="hidden print:block">
                    <!-- Print Header -->
                    <div class="border-b-4 border-slate-900 pb-8 text-center mb-8">
                        <div class="mb-4 flex justify-center">
                            <div class="h-24 w-24">
                                <img src="/storage/logo/union-aid-logo.png" alt="Logo" class="h-full w-full object-contain">
                            </div>
                        </div>
                        <h1 class="text-4xl font-bold text-slate-900 tracking-wide">
                            UNION AID
                        </h1>
                        <p class="mt-3 text-sm font-medium text-slate-700">
                            {{ formatDate(new Date()) }}
                        </p>
                    </div>

                    <!-- Print Table -->
                    <div class="mt-8">
                        <table class="w-full text-xs border-collapse">
                            <thead>
                                <tr class="border-b-2 border-slate-900 bg-slate-100">
                                    <th class="border border-slate-900 px-3 py-2 text-left font-bold">Project</th>
                                    <th class="border border-slate-900 px-3 py-2 text-left font-bold">Code</th>
                                    <th class="border border-slate-900 px-3 py-2 text-left font-bold">Name</th>
                                    <th class="border border-slate-900 px-3 py-2 text-left font-bold">Status</th>
                                    <th class="border border-slate-900 px-3 py-2 text-left font-bold">Purchase Date</th>
                                    <th class="border border-slate-900 px-3 py-2 text-left font-bold">Location</th>
                                    <th class="border border-slate-900 px-3 py-2 text-left font-bold">Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in items" :key="item.id" class="border border-slate-900">
                                    <td class="border border-slate-900 px-3 py-2 text-black">{{ item.project?.code || '-' }}</td>
                                    <td class="border border-slate-900 px-3 py-2 text-black">{{ item.item_code }}</td>
                                    <td class="border border-slate-900 px-3 py-2 text-black">{{ item.name }}</td>
                                    <td class="border border-slate-900 px-3 py-2 text-black">{{ item.status?.name || '-' }}</td>
                                    <td class="border border-slate-900 px-3 py-2 text-black">{{ formatDate(item.purchase_date) }}</td>
                                    <td class="border border-slate-900 px-3 py-2 text-black">
                                        {{ item.location || '-' }}<span v-if="item.sublocation"> / {{ item.sublocation }}</span>
                                    </td>
                                    <td class="border border-slate-900 px-3 py-2 text-black">{{ item.price ? parseFloat(item.price).toFixed(2) + ' ' + (item.currency?.code || '') : '-' }}</td>
                                </tr>
                                <tr v-if="!items.length">
                                    <td colspan="7" class="border border-slate-900 px-3 py-4 text-center font-medium text-black">
                                        No items match the selected filters.
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
@page { margin: 0; }
html, body { margin: 0 !important; padding: 0 !important; }
</style>
