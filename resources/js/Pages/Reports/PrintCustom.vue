<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { formatDate } from '@/utils/dateFormat';

const props = defineProps({
    items: Array,
    filters: Object,
    projects: Array,
    statuses: Array,
});

const printPage = () => {
    window.print();
};

const formatDatePrint = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const getSelectedProjects = () => {
    if (!props.filters.project_ids || props.filters.project_ids.length === 0) {
        return 'All Projects';
    }
    const selected = props.projects.filter(p => props.filters.project_ids.includes(p.id));
    return selected.map(p => p.name).join(', ') || 'None';
};

const getSelectedStatuses = () => {
    if (!props.filters.status_ids || props.filters.status_ids.length === 0) {
        return 'All Statuses';
    }
    const selected = props.statuses.filter(s => props.filters.status_ids.includes(s.id));
    return selected.map(s => s.name).join(', ') || 'None';
};
</script>

<template>
    <Head title="Custom Report - Print" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between print:hidden">
                <div>
                    <h2 class="text-3xl font-bold text-slate-900 dark:text-slate-50">
                        <i class="fa-solid fa-filter mr-3 text-purple-600 dark:text-purple-400"></i>
                        Custom Report
                    </h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                        Print View
                    </p>
                </div>
                <div class="flex gap-2">
                    <button
                        type="button"
                        class="rounded-lg bg-gradient-to-r from-blue-600 to-sky-600 px-4 py-2 text-sm font-medium text-white shadow-md transition hover:shadow-lg dark:from-blue-700 dark:to-sky-700"
                        @click="printPage"
                    >
                        <i class="fa-solid fa-print mr-2"></i>
                        Print
                    </button>
                    <Link
                        :href="route('reports.custom')"
                        class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-medium text-slate-700 shadow-md transition hover:shadow-lg dark:bg-slate-700 dark:text-slate-200"
                    >
                        <i class="fa-solid fa-arrow-left mr-2"></i>
                        Back
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6 print:py-0 print:m-0 print:p-0">
            <div class="mx-auto max-w-6xl space-y-6 print:max-w-none print:m-0 print:p-8">
                <!-- Print View Content -->
                <div class="print:block">
                    <!-- Header Section -->
                    <div class="mb-6 relative">
                        <!-- Top Row: Union Aid Logo (Center), Date (Right) -->
                        <div class="flex justify-between items-start mb-4">
                            <!-- Empty space on left for symmetry -->
                            <div class="w-32 flex-shrink-0"></div>
                            
                            <!-- Union Aid Logo - Center -->
                            <div class="flex-1 flex justify-center items-start">
                                <div class="h-20 w-20">
                                    <img src="/storage/logo/union-aid-logo.png" alt="Union Aid Logo" class="h-full w-full object-contain" />
                                </div>
                            </div>
                            
                            <!-- Date - Right -->
                            <div class="text-right flex-shrink-0">
                                <p class="text-sm font-semibold text-slate-900">
                                    Date: {{ formatDatePrint(new Date()) }}
                                </p>
                            </div>
                        </div>

                        <!-- Title - Below Logo -->
                        <div class="text-center mb-6">
                            <!-- Custom Report Text -->
                            <h1 class="text-2xl font-bold text-slate-900 mb-2">
                                Custom Inventory Report
                            </h1>
                        </div>
                    </div>

                    <!-- Filter Criteria Section -->
                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-slate-900 mb-3 border-b-2 border-slate-900 pb-1">
                            Filter Criteria
                        </h3>
                        <table class="w-full border-collapse border-2 border-slate-900 text-xs">
                            <thead>
                                <tr class="bg-slate-100">
                                    <th class="border-2 border-slate-900 px-3 py-2 text-left font-bold">Filter Type</th>
                                    <th class="border-2 border-slate-900 px-3 py-2 text-left font-bold">Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="border-2 border-slate-900 px-3 py-2 font-semibold bg-slate-50">Projects</td>
                                    <td class="border-2 border-slate-900 px-3 py-2">{{ getSelectedProjects() }}</td>
                                </tr>
                                <tr>
                                    <td class="border-2 border-slate-900 px-3 py-2 font-semibold bg-slate-50">Statuses</td>
                                    <td class="border-2 border-slate-900 px-3 py-2">{{ getSelectedStatuses() }}</td>
                                </tr>
                                <tr>
                                    <td class="border-2 border-slate-900 px-3 py-2 font-semibold bg-slate-50">Assignment Status</td>
                                    <td class="border-2 border-slate-900 px-3 py-2">{{ filters.assignment_status || 'All' }}</td>
                                </tr>
                                <tr>
                                    <td class="border-2 border-slate-900 px-3 py-2 font-semibold bg-slate-50">From Date</td>
                                    <td class="border-2 border-slate-900 px-3 py-2">{{ filters.from ? formatDatePrint(filters.from) : 'Any' }}</td>
                                </tr>
                                <tr>
                                    <td class="border-2 border-slate-900 px-3 py-2 font-semibold bg-slate-50">To Date</td>
                                    <td class="border-2 border-slate-900 px-3 py-2">{{ filters.to ? formatDatePrint(filters.to) : 'Any' }}</td>
                                </tr>
                                <tr>
                                    <td class="border-2 border-slate-900 px-3 py-2 font-semibold bg-slate-50">Min Price</td>
                                    <td class="border-2 border-slate-900 px-3 py-2">{{ filters.price_min ? parseFloat(filters.price_min).toFixed(2) : 'Any' }}</td>
                                </tr>
                                <tr>
                                    <td class="border-2 border-slate-900 px-3 py-2 font-semibold bg-slate-50">Max Price</td>
                                    <td class="border-2 border-slate-900 px-3 py-2">{{ filters.price_max ? parseFloat(filters.price_max).toFixed(2) : 'Any' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Items Table -->
                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-slate-900 mb-3 border-b-2 border-slate-900 pb-1">
                            Report Items
                        </h3>
                        <table class="w-full border-collapse border-2 border-slate-900 text-xs">
                            <thead>
                                <tr class="bg-slate-100">
                                    <th class="border-2 border-slate-900 px-3 py-2 text-left font-bold">#</th>
                                    <th class="border-2 border-slate-900 px-3 py-2 text-left font-bold">Project</th>
                                    <th class="border-2 border-slate-900 px-3 py-2 text-left font-bold">Tag Number</th>
                                    <th class="border-2 border-slate-900 px-3 py-2 text-left font-bold">Item Name</th>
                                    <th class="border-2 border-slate-900 px-3 py-2 text-left font-bold">Assignment Status</th>
                                    <th class="border-2 border-slate-900 px-3 py-2 text-left font-bold">Situation</th>
                                    <th class="border-2 border-slate-900 px-3 py-2 text-left font-bold">Purchase Date</th>
                                    <th class="border-2 border-slate-900 px-3 py-2 text-left font-bold">Location</th>
                                    <th class="border-2 border-slate-900 px-3 py-2 text-left font-bold">Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr 
                                    v-for="(item, index) in items" 
                                    :key="item.id"
                                >
                                    <td class="border-2 border-slate-900 px-3 py-2">{{ index + 1 }}</td>
                                    <td class="border-2 border-slate-900 px-3 py-2">{{ item.project?.name || '-' }}</td>
                                    <td class="border-2 border-slate-900 px-3 py-2">{{ item.tag_number || item.item_code || '-' }}</td>
                                    <td class="border-2 border-slate-900 px-3 py-2">{{ item.name || '-' }}</td>
                                    <td class="border-2 border-slate-900 px-3 py-2">{{ item.assignment_status || '-' }}</td>
                                    <td class="border-2 border-slate-900 px-3 py-2">{{ item.status?.name || '-' }}</td>
                                    <td class="border-2 border-slate-900 px-3 py-2">{{ item.purchase_date ? formatDatePrint(item.purchase_date) : '-' }}</td>
                                    <td class="border-2 border-slate-900 px-3 py-2">
                                        {{ item.location || '-' }}<span v-if="item.sublocation"> / {{ item.sublocation }}</span>
                                    </td>
                                    <td class="border-2 border-slate-900 px-3 py-2">
                                        {{ item.price ? parseFloat(item.price).toFixed(2) + ' ' + (item.currency?.code || '') : '-' }}
                                    </td>
                                </tr>
                                <tr v-if="!items || items.length === 0">
                                    <td colspan="9" class="border-2 border-slate-900 px-4 py-4 text-center font-medium">
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
@page {
    margin: 0.5in;
    size: A4;
}

@media print {
    .print\:hidden {
        display: none !important;
    }
    
    body {
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
    }
    
    /* Ensure borders print correctly */
    table, td, th {
        border-collapse: collapse !important;
    }
}
</style>
