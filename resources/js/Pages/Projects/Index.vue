<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { formatDate } from '@/utils/dateFormat';
import { resolveImage } from '@/utils/imageResolver';

const props = defineProps({
    projects: Object,
    stats: Object,
    filters: Object,
});

const search = ref(props.filters?.search ?? '');

const submitSearch = () => {
    router.get(route('projects.index'), { search: search.value }, { preserveState: true, replace: true });
};
</script>

<template>
    <Head title="Owners" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                        Management
                    </p>
                    <h1 class="text-2xl font-semibold leading-tight text-slate-900 dark:text-slate-50">
                        Owners
                    </h1>
                </div>
                <Link
                    :href="route('projects.create')"
                    class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-sky-600 to-blue-600 px-4 py-2 text-sm font-medium text-white shadow-md transition hover:shadow-lg dark:from-sky-700 dark:to-blue-800"
                >
                    <i class="fa-solid fa-folder-plus"></i>
                    <span>New project</span>
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">

                <div class="rounded-xl border border-slate-200/50 bg-white/70 p-4 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70 dark:backdrop-blur">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex-1">
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search by project code or name..."
                                class="block w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm text-slate-900 placeholder-slate-500 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-50 dark:placeholder-slate-400 dark:focus:border-sky-400"
                                @keyup.enter="submitSearch"
                            />
                        </div>
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-blue-600 to-sky-600 px-4 py-2 text-sm font-medium text-white shadow-md transition hover:shadow-lg dark:from-blue-700 dark:to-sky-700"
                            @click="submitSearch"
                        >
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <span>Search</span>
                        </button>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-slate-200/50 bg-white/70 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-slate-100 dark:border-slate-700 dark:from-slate-900 dark:to-slate-800">
                                    <th class="px-4 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">#</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">Image</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">Name</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">Start Date</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">End Date</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-900 dark:text-slate-50">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                <tr
                                    v-for="(project, index) in projects.data"
                                    :key="project.id"
                                    class="bg-white transition hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50"
                                >
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ (projects.current_page - 1) * projects.per_page + index + 1 }}</td>
                                    <td class="px-4 py-3">
                                        <div class="h-12 w-12 overflow-hidden rounded-full">
                                            <img
                                                v-if="project.logo_path"
                                                :src="resolveImage(project.logo_path)"
                                                :alt="project.name"
                                                class="h-full w-full object-cover"
                                            />
                                            <div v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900">
                                                <i class="fa-solid fa-diagram-project text-blue-300 dark:text-blue-600"></i>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-50">{{ project.name }}</td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ formatDate(project.start_date) }}</td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ formatDate(project.end_date) }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <Link
                                                :href="route('projects.show', project.id)"
                                                class="inline-flex items-center gap-1 rounded-md bg-sky-100 px-2 py-1 text-xs font-medium text-sky-700 transition hover:bg-sky-200 dark:bg-sky-900/40 dark:text-sky-300 dark:hover:bg-sky-900/60"
                                            >
                                                <i class="fa-solid fa-eye text-xs"></i>
                                                <span>View</span>
                                            </Link>
                                            <Link
                                                :href="route('projects.edit', project.id)"
                                                class="inline-flex items-center gap-1 rounded-md bg-slate-200 px-2 py-1 text-xs font-medium text-slate-700 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600"
                                            >
                                                <i class="fa-solid fa-pen text-xs"></i>
                                                <span>Edit</span>
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!projects.data.length">
                                    <td colspan="6" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                        <i class="fa-solid fa-inbox text-2xl mb-2 block opacity-50"></i>
                                        No projects found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex items-center justify-between rounded-xl border border-slate-200/50 bg-white/70 p-4 text-xs text-slate-600 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70 dark:text-slate-400">
                    <div>
                        Showing
                        <span class="font-semibold text-slate-900 dark:text-slate-50">{{ projects.from ?? 0 }}</span>
                        to
                        <span class="font-semibold text-slate-900 dark:text-slate-50">{{ projects.to ?? 0 }}</span>
                        of
                        <span class="font-semibold text-slate-900 dark:text-slate-50">{{ projects.total }}</span>
                        results
                    </div>
                    <div class="flex gap-2">
                        <button
                            type="button"
                            class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50 disabled:opacity-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                            :disabled="!projects.prev_page_url"
                            @click="router.get(projects.prev_page_url)"
                        >
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <button
                            type="button"
                            class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50 disabled:opacity-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                            :disabled="!projects.next_page_url"
                            @click="router.get(projects.next_page_url)"
                        >
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>