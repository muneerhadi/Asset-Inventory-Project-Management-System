<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { formatDate } from '@/utils/dateFormat';

const props = defineProps({
    items: Object,
});
</script>

<template>
    <Head title="In Stock Items" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Inventory
                    </p>
                    <h1 class="text-2xl font-semibold leading-tight text-slate-900 dark:text-slate-50">
                        In Stock Items
                    </h1>
                </div>
                <Link
                    :href="route('items.index')"
                    class="inline-flex items-center gap-2 rounded-lg bg-slate-200 px-4 py-2 text-sm font-medium text-slate-700 shadow-md transition hover:shadow-lg dark:bg-slate-700 dark:text-slate-200"
                >
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Back to All Items</span>
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-xl border border-slate-200/50 bg-white/70 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-slate-100 dark:border-slate-700 dark:from-slate-900 dark:to-slate-800">
                                    <th class="px-4 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">#</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">Name</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">Tag Number</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">Category</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">Owner</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">Situation</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-900 dark:text-slate-50">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                <tr
                                    v-for="(item, index) in items.data"
                                    :key="item.id"
                                    class="bg-white transition hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50"
                                >
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ (items.current_page - 1) * items.per_page + index + 1 }}</td>
                                    <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-50">{{ item.name }}</td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ item.tag_number || '-' }}</td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ item.category?.name || '-' }}</td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ item.project?.name || 'Unassigned' }}</td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold whitespace-nowrap"
                                            :class="{
                                                'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300': item.status?.slug === 'active',
                                                'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300': item.status?.slug === 'daghma',
                                                'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300': item.status?.is_damaged,
                                                'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300': !item.status,
                                            }"
                                        >
                                            {{ item.status?.name ?? 'Unknown' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <Link
                                                :href="route('items.show', item.id)"
                                                class="inline-flex items-center gap-1 rounded-md bg-sky-100 px-2 py-1 text-xs font-medium text-sky-700 transition hover:bg-sky-200 dark:bg-sky-900/40 dark:text-sky-300 dark:hover:bg-sky-900/60"
                                            >
                                                <i class="fa-solid fa-eye text-xs"></i>
                                                <span>View</span>
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!items.data.length">
                                    <td colspan="7" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                        <i class="fa-solid fa-warehouse text-4xl mb-2 block opacity-50"></i>
                                        No in-stock items found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex items-center justify-between rounded-xl border border-slate-200/50 bg-white/70 p-4 text-xs text-slate-600 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70 dark:text-slate-400">
                    <div>
                        Showing
                        <span class="font-semibold text-slate-900 dark:text-slate-50">{{ items.from ?? 0 }}</span>
                        to
                        <span class="font-semibold text-slate-900 dark:text-slate-50">{{ items.to ?? 0 }}</span>
                        of
                        <span class="font-semibold text-slate-900 dark:text-slate-50">{{ items.total }}</span>
                        results
                    </div>
                    <div class="flex gap-2">
                        <button
                            type="button"
                            class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50 disabled:opacity-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                            :disabled="!items.prev_page_url"
                            @click="router.get(items.prev_page_url)"
                        >
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <button
                            type="button"
                            class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50 disabled:opacity-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                            :disabled="!items.next_page_url"
                            @click="router.get(items.next_page_url)"
                        >
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>