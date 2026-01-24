<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { formatDate } from '@/utils/dateFormat';

const props = defineProps({
    activities: Object,
});

const isLoading = ref(true);
const isMounted = ref(true);
const hasEntered = ref(false);
let loaderShownAt = 0;
const MIN_LOADER_MS = 400;
const INITIAL_LOADER_MS = 350;

function showLoader() {
    if (!isMounted.value) return;
    isLoading.value = true;
    loaderShownAt = Date.now();
}

function hideLoader() {
    if (!isMounted.value) return;
    const elapsed = Date.now() - loaderShownAt;
    const wait = Math.max(0, MIN_LOADER_MS - elapsed);
    setTimeout(() => {
        if (isMounted.value) isLoading.value = false;
    }, wait);
}

function goToPage(url) {
    if (!url) return;
    router.get(url, {}, { preserveState: true });
}

function goToActivity(id) {
    router.get(route('activities.show', id));
}

onMounted(() => {
    loaderShownAt = Date.now();
    router.on('start', showLoader);
    router.on('finish', hideLoader);
    router.on('cancel', hideLoader);
    router.on('error', hideLoader);
    setTimeout(() => {
        if (!isMounted.value) return;
        isLoading.value = false;
        requestAnimationFrame(() => {
            hasEntered.value = true;
        });
    }, INITIAL_LOADER_MS);
});

onBeforeUnmount(() => {
    isMounted.value = false;
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
                :class="[
                    'activity-section relative overflow-hidden rounded-xl border border-slate-200 bg-gradient-to-b from-white via-slate-50 to-white p-4 shadow-sm dark:border-slate-800 dark:bg-gradient-to-b dark:from-slate-900 dark:via-slate-900/90 dark:to-slate-950',
                    hasEntered && 'activity-section-visible'
                ]"
            >
                <!-- Loading overlay with loader -->
                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div
                        v-show="isLoading"
                        class="activity-loader-overlay absolute inset-0 z-10 flex flex-col items-center justify-center gap-4 rounded-xl bg-slate-900/60 backdrop-blur-sm dark:bg-slate-950/70"
                        aria-hidden="true"
                    >
                        <div class="activity-loader" role="status" aria-label="Loading activity log">
                            <div class="activity-loader-ring activity-loader-ring-1"></div>
                            <div class="activity-loader-ring activity-loader-ring-2"></div>
                            <div class="activity-loader-ring activity-loader-ring-3"></div>
                            <div class="activity-loader-core">
                                <i class="fa-solid fa-clock-rotate-left text-sky-400 text-xl"></i>
                            </div>
                        </div>
                        <p class="text-sm font-medium text-white/90 tracking-wide">Loading activity log…</p>
                    </div>
                </Transition>

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
                                v-for="(activity, idx) in activities.data"
                                :key="activity.id"
                                :class="[
                                    'activity-row hover:bg-slate-50 dark:hover:bg-slate-900 cursor-pointer transition',
                                    hasEntered && 'activity-row-visible'
                                ]"
                                :style="{ '--ar-i': idx }"
                                @click="goToActivity(activity.id)"
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
                            <tr
                                v-if="!activities.data || activities.data.length === 0"
                                :class="['activity-row', hasEntered && 'activity-row-visible']"
                            >
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
                            @click="goToPage(activities.prev_page_url)"
                        >
                            Previous
                        </button>
                        <button
                            type="button"
                            class="rounded-md border border-slate-300 px-2 py-1 text-xs font-medium text-slate-700 disabled:opacity-50 dark:border-slate-700 dark:text-slate-200"
                            :disabled="!activities.next_page_url"
                            @click="goToPage(activities.next_page_url)"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.activity-section {
    opacity: 0;
    transform: translateY(16px);
    transition: opacity 0.4s ease-out, transform 0.4s cubic-bezier(0.22, 1, 0.36, 1);
}
.activity-section-visible {
    opacity: 1;
    transform: translateY(0);
}

.activity-row {
    opacity: 0;
    transform: translateY(8px);
    transition: opacity 0.35s ease-out, transform 0.35s cubic-bezier(0.22, 1, 0.36, 1);
    transition-delay: calc(40ms + (var(--ar-i, 0) * 25ms));
}
.activity-row-visible {
    opacity: 1;
    transform: translateY(0);
}

.activity-loader {
    position: relative;
    width: 88px;
    height: 88px;
}

.activity-loader-ring {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    border: 3px solid transparent;
    border-top-color: rgba(14, 165, 233, 0.95);
    border-right-color: rgba(99, 102, 241, 0.5);
    animation: activity-loader-spin 0.9s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite;
    will-change: transform;
}

.activity-loader-ring-2 {
    inset: 10px;
    border-top-color: rgba(168, 85, 247, 0.9);
    border-right-color: rgba(14, 165, 233, 0.45);
    animation-duration: 1.2s;
    animation-direction: reverse;
}

.activity-loader-ring-3 {
    inset: 20px;
    border-top-color: rgba(99, 102, 241, 0.95);
    border-right-color: rgba(168, 85, 247, 0.45);
    animation-duration: 1.5s;
}

.activity-loader-core {
    position: absolute;
    inset: 50%;
    transform: translate(-50%, -50%);
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    animation: activity-loader-pulse 1s ease-in-out infinite;
    will-change: transform, opacity;
}
</style>

<style>
@keyframes activity-loader-spin {
    to {
        transform: rotate(360deg);
    }
}

@keyframes activity-loader-pulse {
    0%, 100% {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
    }
    50% {
        opacity: 0.9;
        transform: translate(-50%, -50%) scale(1.1);
    }
}
</style>
