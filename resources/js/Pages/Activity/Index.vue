<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { formatDate } from '@/utils/dateFormat';

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
                                    class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500"
                                >
                                    Details
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
                                class="hover:bg-slate-50 dark:hover:bg-slate-900 cursor-pointer transition"
                                @click="$inertia.get(route('activities.show', activity.id))"
                            >
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div :class="[
                                            'flex h-6 w-6 items-center justify-center rounded-full text-xs',
                                            activity.action.includes('delete') || activity.action.includes('removed')
                                                ? 'bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400'
                                                : activity.action.includes('create')
                                                ? 'bg-green-100 text-green-600 dark:bg-green-900/40 dark:text-green-400'
                                                : activity.action.includes('update')
                                                ? 'bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400'
                                                : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
                                        ]">
                                            <i :class="[
                                                activity.action.includes('delete') || activity.action.includes('removed')
                                                    ? 'fa-solid fa-trash'
                                                    : activity.action.includes('create')
                                                    ? 'fa-solid fa-plus'
                                                    : activity.action.includes('update')
                                                    ? 'fa-solid fa-edit'
                                                    : 'fa-solid fa-circle-dot'
                                            ]"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs text-slate-500">{{ formatDate(activity.created_at) }}</p>
                                            <p class="text-xs font-mono text-slate-400">{{ activity.created_at }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div>
                                        <p :class="[
                                            'text-sm font-medium',
                                            activity.action.includes('delete') || activity.action.includes('removed')
                                                ? 'text-red-700 dark:text-red-300'
                                                : 'text-slate-800 dark:text-slate-100'
                                        ]">{{ activity.action }}</p>
                                        <p v-if="activity.description" class="text-xs text-slate-500 mt-1 truncate max-w-md">
                                            {{ activity.description }}
                                        </p>
                                        <div v-if="activity.subject_type" class="mt-1">
                                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-800 dark:bg-slate-800 dark:text-slate-200">
                                                {{ activity.subject_type.split('\\').pop() }} #{{ activity.subject_id }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div v-if="activity.user" class="flex items-center gap-2">
                                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-br from-blue-200 to-blue-300 text-xs font-medium text-blue-700 dark:from-blue-900/40 dark:to-blue-800/40 dark:text-blue-300">
                                            {{ activity.user.name.charAt(0) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-slate-900 dark:text-slate-50">{{ activity.user.name }}</p>
                                            <p class="text-xs text-slate-500 capitalize">{{ activity.user.role.replace('_', ' ') }}</p>
                                        </div>
                                    </div>
                                    <div v-else class="flex items-center gap-2 text-slate-500">
                                        <i class="fa-solid fa-robot text-xs"></i>
                                        <span class="text-sm italic">System</span>
                                    </div>
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
