<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { formatDate } from '@/utils/dateFormat';

const props = defineProps({
    summary: Object,
});

const currency = ref('USD');

const printPage = () => { 
    window.print(); 
};
</script>

<style scoped>
@media print { 
    .print\:hidden {
        display: none !important;
    }
    .hidden {
        display: none;
    }
    .hidden.print\:block {
        display: block !important;
    }
    
    /* Hide browser print elements and headers/footers */
    @page {
        margin: 0;
        margin-top: 0; 
        margin-bottom: 0;
    }
    
    * {
        margin: 0;
        padding: 0;
    }
    
    html, body {
        height: auto;
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
    }
    
    /* Firefox */
    @-moz-document url-prefix() {
        @page {
            margin: 0;
        }
        html, body {
            margin: 0 !important;
            padding: 0 !important;
        }
    }
}

/* Disable print margins and headers for all browsers */
@media print {
    html {
        margin: 0 !important;
        padding: 0 !important;
    }
    
    body {
        margin: 0 !important;
        padding: 0 !important;
    }
    
    .no-print {
        display: none !important;
    }
}
</style>

<template>
    <Head title="Economy Report" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                        Reports
                    </p>
                    <h1 class="text-2xl font-semibold leading-tight text-slate-900 dark:text-slate-50">
                        Economy Report
                    </h1>
                </div>
                <div class="flex gap-2">
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-blue-600 to-sky-600 px-4 py-2 text-sm font-medium text-white shadow-md transition hover:shadow-lg dark:from-blue-700 dark:to-sky-700 print:hidden"
                        @click="printPage"
                    >
                        <i class="fa-solid fa-print"></i>
                        <span>Print</span>
                    </button>
                    <Link
                        :href="route('reports.index')"
                        class="inline-flex items-center gap-2 rounded-lg bg-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600 print:hidden"
                    >
                        <i class="fa-solid fa-arrow-left"></i>
                        <span>Back</span>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6 print:py-0 print:m-0 print:p-0">
            <div class="mx-auto max-w-4xl space-y-6 print:max-w-none print:m-0 print:p-4">
                <!-- Currency Selector -->
                <div class="print:hidden">
                    <div class="rounded-xl border border-slate-200/50 bg-white/70 p-4 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70">
                        <div class="flex items-center gap-4">
                            <label class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                <i class="fa-solid fa-coins mr-2 text-blue-600"></i>
                                Select Currency:
                            </label>
                            <div class="flex gap-2">
                                <button
                                    @click="currency = 'USD'"
                                    :class="[
                                        'rounded-lg px-4 py-2 text-sm font-medium transition',
                                        currency === 'USD'
                                            ? 'bg-gradient-to-r from-blue-600 to-sky-600 text-white dark:from-blue-700 dark:to-sky-700'
                                            : 'bg-slate-200 text-slate-700 hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600'
                                    ]"
                                >
                                    USD
                                </button>
                                <button
                                    @click="currency = 'AFG'"
                                    :class="[
                                        'rounded-lg px-4 py-2 text-sm font-medium transition',
                                        currency === 'AFG'
                                            ? 'bg-gradient-to-r from-blue-600 to-sky-600 text-white dark:from-blue-700 dark:to-sky-700'
                                            : 'bg-slate-200 text-slate-700 hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600'
                                    ]"
                                >
                                    AFG
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Print Header -->
                <div class="hidden print:block">
                    <div class="border-b-4 border-slate-900 pb-8 text-center">
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
                </div>

                <!-- Screen View Content -->
                <div class="print:hidden">
                    <div class="overflow-hidden rounded-xl border border-slate-200/50 bg-gradient-to-br from-white to-slate-50/50 shadow-md dark:border-slate-700/50 dark:from-slate-900 dark:to-slate-800/50">
                        <div class="space-y-6 p-6">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="overflow-hidden rounded-xl border border-blue-200/50 bg-gradient-to-br from-blue-100/60 via-sky-50/40 to-cyan-100/50 p-6 shadow-md dark:border-blue-900/30 dark:bg-gradient-to-br dark:from-blue-950/40 dark:via-slate-900/60 dark:to-cyan-950/40">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <p class="text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">
                                                Total Items ({{ currency }})
                                            </p>
                                            <p class="mt-3 text-4xl font-bold text-slate-900 dark:text-slate-50">
                                                {{ currency === 'USD' ? (summary.usd?.count ?? 0) : (summary.afg?.count ?? 0) }}
                                            </p>
                                        </div>
                                        <span class="text-blue-600 dark:text-blue-400">
                                            <i class="fa-solid fa-box text-4xl"></i>
                                        </span>
                                    </div>
                                </div>

                                <div class="overflow-hidden rounded-xl border border-emerald-200/50 bg-gradient-to-br from-emerald-100/60 via-green-50/40 to-teal-100/50 p-6 shadow-md dark:border-emerald-900/30 dark:bg-gradient-to-br dark:from-emerald-950/40 dark:via-slate-900/60 dark:to-teal-950/40">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <p class="text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">
                                                Total Value ({{ currency }})
                                            </p>
                                            <p class="mt-3 text-4xl font-bold text-slate-900 dark:text-slate-50">
                                                {{ currency === 'USD' ? (summary.usd?.total ?? 0) : (summary.afg?.total ?? 0) }}
                                            </p>
                                        </div>
                                        <span class="text-emerald-600 dark:text-emerald-400">
                                            <i class="fa-solid fa-coins text-4xl"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-900/50 dark:text-slate-400">
                                <i class="fa-solid fa-info-circle mr-2 text-blue-600"></i>
                                <span>You can use this summary as an input for the official economy/ministry templates.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Print View Content -->
                <div class="hidden print:block space-y-6">
                    <!-- Overall items first -->
                    <div class="rounded-lg border-2 border-slate-900 bg-slate-50 p-8 text-center">
                        <p class="text-sm font-semibold uppercase tracking-widest text-slate-600">
                            Total Items (All)
                        </p>
                        <p class="mt-4 text-5xl font-bold text-slate-900">
                            {{ summary.total_items ?? 0 }}
                        </p>
                    </div>

                    <!-- USD and AFG details -->
                    <div class="grid gap-8 md:grid-cols-2">
                        <div class="rounded-lg border-2 border-slate-900 bg-slate-50 p-8 text-center">
                            <p class="text-sm font-semibold uppercase tracking-widest text-slate-600">
                                USD — Count & Total
                            </p>
                            <p class="mt-4 text-2xl font-bold text-slate-900">
                                Count: {{ summary.usd?.count ?? 0 }}
                            </p>
                            <p class="mt-2 text-3xl font-bold text-slate-900">
                                {{ summary.usd?.total ?? 0 }} USD
                            </p>
                        </div>
                        <div class="rounded-lg border-2 border-slate-900 bg-slate-50 p-8 text-center">
                            <p class="text-sm font-semibold uppercase tracking-widest text-slate-600">
                                AFG — Count & Total
                            </p>
                            <p class="mt-4 text-2xl font-bold text-slate-900">
                                Count: {{ summary.afg?.count ?? 0 }}
                            </p>
                            <p class="mt-2 text-3xl font-bold text-slate-900">
                                {{ summary.afg?.total ?? 0 }} AFG
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
