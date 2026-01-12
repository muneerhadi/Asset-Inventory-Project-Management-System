<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { formatDate } from '@/utils/dateFormat';

const props = defineProps({
    activity: Object,
});

const getActionIcon = (action) => {
    if (action.includes('create')) return 'fa-plus';
    if (action.includes('update')) return 'fa-edit';
    if (action.includes('delete') || action.includes('removed')) return 'fa-trash';
    if (action.includes('assign')) return 'fa-user-plus';
    return 'fa-circle-dot';
};

const getActionColor = (action) => {
    if (action.includes('create')) return 'text-green-600 bg-green-100 dark:bg-green-900/40 dark:text-green-400';
    if (action.includes('update')) return 'text-blue-600 bg-blue-100 dark:bg-blue-900/40 dark:text-blue-400';
    if (action.includes('delete') || action.includes('removed')) return 'text-red-600 bg-red-100 dark:bg-red-900/40 dark:text-red-400';
    if (action.includes('assign')) return 'text-purple-600 bg-purple-100 dark:bg-purple-900/40 dark:text-purple-400';
    return 'text-slate-600 bg-slate-100 dark:bg-slate-800 dark:text-slate-400';
};
</script>

<template>
    <Head title="Activity Details" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                        Activity Details
                    </p>
                    <h1 class="text-2xl font-semibold leading-tight text-slate-900 dark:text-slate-50">
                        Activity #{{ activity.id }}
                    </h1>
                </div>
                <Link
                    :href="route('activities.index')"
                    class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-medium text-slate-700 shadow-md transition hover:shadow-lg dark:bg-slate-700 dark:text-slate-200"
                >
                    <i class="fa-solid fa-arrow-left mr-2"></i>
                    Back to Activities
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl space-y-6">
                <!-- Main Activity Card -->
                <div class="overflow-hidden rounded-xl border border-slate-200/50 bg-white/70 p-6 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70">
                    <div class="flex items-start gap-4">
                        <div :class="['flex h-12 w-12 items-center justify-center rounded-full', getActionColor(activity.action)]">
                            <i :class="['text-lg', getActionIcon(activity.action)]"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h2 :class="['text-xl font-semibold', activity.action.includes('delete') || activity.action.includes('removed') ? 'text-red-700 dark:text-red-300' : 'text-slate-900 dark:text-slate-50']">
                                        {{ activity.action }}
                                    </h2>
                                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                                        {{ formatDate(activity.created_at) }}
                                    </p>
                                </div>
                            </div>
                            
                            <div v-if="activity.description" class="mt-4">
                                <h3 class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Description</h3>
                                <p class="text-sm text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-800/50 rounded-lg p-3">
                                    {{ activity.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Details Grid -->
                <div class="grid gap-6 md:grid-cols-2">
                    <!-- User Information -->
                    <div class="overflow-hidden rounded-xl border border-slate-200/50 bg-white/70 p-6 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-50 mb-4">
                            <i class="fa-solid fa-user mr-2 text-slate-600 dark:text-slate-400"></i>
                            Performed By
                        </h3>
                        <div v-if="activity.user" class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-blue-200 to-blue-300 text-sm font-medium text-blue-700 dark:from-blue-900/40 dark:to-blue-800/40 dark:text-blue-300">
                                {{ activity.user.name.charAt(0) }}
                            </div>
                            <div>
                                <p class="font-medium text-slate-900 dark:text-slate-50">{{ activity.user.name }}</p>
                                <p class="text-sm text-slate-600 dark:text-slate-400">{{ activity.user.email }}</p>
                                <p class="text-xs text-slate-500 capitalize">{{ activity.user.role.replace('_', ' ') }}</p>
                            </div>
                        </div>
                        <div v-else class="text-slate-500 dark:text-slate-400">
                            <i class="fa-solid fa-robot mr-2"></i>
                            System Action
                        </div>
                    </div>

                    <!-- Subject Information -->
                    <div class="overflow-hidden rounded-xl border border-slate-200/50 bg-white/70 p-6 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-50 mb-4">
                            <i class="fa-solid fa-target mr-2 text-slate-600 dark:text-slate-400"></i>
                            Related To
                        </h3>
                        <div v-if="activity.subject_type && activity.subject_id">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-800 dark:bg-slate-800 dark:text-slate-200">
                                    {{ activity.subject_type.split('\\').pop() }}
                                </span>
                                <span class="text-sm text-slate-600 dark:text-slate-400">ID: {{ activity.subject_id }}</span>
                            </div>
                            <div v-if="activity.subject" class="mt-3 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-lg">
                                <div v-if="activity.subject.name">
                                    <p class="font-medium text-slate-900 dark:text-slate-50">{{ activity.subject.name }}</p>
                                </div>
                                <div v-if="activity.subject.code" class="text-sm text-slate-600 dark:text-slate-400">
                                    Code: {{ activity.subject.code }}
                                </div>
                                <div v-if="activity.subject.tag_number" class="text-sm text-slate-600 dark:text-slate-400">
                                    Tag: {{ activity.subject.tag_number }}
                                </div>
                                <div v-if="activity.subject.employee_code" class="text-sm text-slate-600 dark:text-slate-400">
                                    Employee Code: {{ activity.subject.employee_code }}
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-slate-500 dark:text-slate-400">
                            <i class="fa-solid fa-minus mr-2"></i>
                            No related object
                        </div>
                    </div>
                </div>

                <!-- Technical Details -->
                <div class="overflow-hidden rounded-xl border border-slate-200/50 bg-white/70 p-6 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-50 mb-4">
                        <i class="fa-solid fa-cog mr-2 text-slate-600 dark:text-slate-400"></i>
                        Technical Details
                    </h3>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Activity ID</p>
                            <p class="mt-1 text-sm font-mono text-slate-900 dark:text-slate-50">{{ activity.id }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Created At</p>
                            <p class="mt-1 text-sm font-mono text-slate-900 dark:text-slate-50">{{ activity.created_at }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Updated At</p>
                            <p class="mt-1 text-sm font-mono text-slate-900 dark:text-slate-50">{{ activity.updated_at }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>