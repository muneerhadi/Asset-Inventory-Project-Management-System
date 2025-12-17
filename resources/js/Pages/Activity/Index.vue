<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    activities: Object,
});
</script>

<template>
    <Head title="Activity" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                        Activity log
                    </p>
                    <h1 class="text-2xl font-semibold leading-tight text-slate-900 dark:text-slate-50">
                        Recent activity
                    </h1>
                </div>
                <div class="text-xs text-slate-500 md:text-right">
                    <p class="font-medium text-slate-700 dark:text-slate-200">
                        Total entries
                    </p>
                    <p>{{ activities.total ?? 0 }}</p>
                </div>
            </div>
        </template>

        <div class="space-y-4">
            <section
                class="overflow-hidden rounded-xl border border-slate-200 bg-gradient-to-b from-white via-slate-50 to-white p-4 shadow-sm dark:border-slate-800 dark:bg-gradient-to-b dark:from-slate-900 dark:via-slate-900/90 dark:to-slate-950"
            >
                <div class="mb-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-sky-100 text-sky-600 dark:bg-sky-900/40 dark:text-sky-300"
                        >
                            <i class="fa-solid fa-clock-rotate-left text-sm"></i>
                        </span>
                        <div>
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-50">
                                Activity history
                            </h2>
                            <p class="text-xs text-slate-500">
                                All recent changes in the system.
                            </p>
                        </div>
                    </div>
                    <div class="hidden text-xs text-slate-500 sm:block">
                        Page {{ activities.current_page }} of {{ activities.last_page }}
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                        <thead class="bg-slate-50 dark:bg-slate-900/80">
                            <tr>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wide text-slate-500"
                                >
                                    When
                                </th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wide text-slate-500"
                                >
                                    Action
                                </th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wide text-slate-500"
                                >
                                    User
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white/90 dark:divide-slate-800 dark:bg-slate-900/80">
                            <tr
                                v-for="activity in activities.data"
                                :key="activity.id"
                                class="hover:bg-slate-50 dark:hover:bg-slate-900"
                            >
                                <td class="px-4 py-2 text-xs text-slate-500">
                                    {{ activity.created_at }}
                                </td>
                                <td class="px-4 py-2 text-sm text-slate-800 dark:text-slate-100">
                                    {{ activity.action }}
                                </td>
                                <td class="px-4 py-2 text-xs text-slate-500">
                                    <span v-if="activity.user">
                                        {{ activity.user.name }}
                                    </span>
                                    <span v-else class="italic text-slate-400">System</span>
                                </td>
                            </tr>
                            <tr v-if="!activities.data || activities.data.length === 0">
                                <td
                                    colspan="3"
                                    class="px-4 py-4 text-center text-xs text-slate-500"
                                >
                                    No activity entries yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    class="mt-4 flex flex-col items-center justify-between gap-3 text-xs text-slate-500 sm:flex-row"
                >
                    <div>
                        Showing
                        <span class="font-semibold">{{ activities.from ?? 0 }}</span>
                        to
                        <span class="font-semibold">{{ activities.to ?? 0 }}</span>
                        of
                        <span class="font-semibold">{{ activities.total ?? 0 }}</span>
                        results
                    </div>
                    <div class="flex gap-2">
                        <button
                            type="button"
                            class="rounded-md border border-slate-300 px-2 py-1 text-xs font-medium text-slate-700 disabled:opacity-50 dark:border-slate-700 dark:text-slate-200"
                            :disabled="!activities.prev_page_url"
                            @click="$inertia.get(activities.prev_page_url)"
                        >
                            Previous
                        </button>
                        <button
                            type="button"
                            class="rounded-md border border-slate-300 px-2 py-1 text-xs font-medium text-slate-700 disabled:opacity-50 dark:border-slate-700 dark:text-slate-200"
                            :disabled="!activities.next_page_url"
                            @click="$inertia.get(activities.next_page_url)"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
