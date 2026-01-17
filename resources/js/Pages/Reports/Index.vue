<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    projects: Array,
});

const selectedProjectIds = ref([]);

const openProjectReport = () => {
    if (selectedProjectIds.value.length === 0) return;
    // For now, open the first selected project (single project report)
    // TODO: Update backend to support multiple projects
    router.get(route('reports.project', selectedProjectIds.value[0]));
};

const toggleProject = (projectId) => {
    const index = selectedProjectIds.value.indexOf(projectId);
    if (index > -1) {
        selectedProjectIds.value.splice(index, 1);
    } else {
        selectedProjectIds.value.push(projectId);
    }
};

const toggleAllProjects = () => {
    if (selectedProjectIds.value.length === props.projects.length) {
        selectedProjectIds.value = [];
    } else {
        selectedProjectIds.value = props.projects.map(p => p.id);
    }
};
</script>

<template>
    <Head title="Reports" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                    Analytics
                </p>
                <h1 class="text-2xl font-semibold leading-tight text-slate-900 dark:text-slate-50">
                    Reports
                </h1>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8">
                <div class="grid gap-4 md:grid-cols-2">
                    <section class="overflow-hidden rounded-xl border border-rose-200/50 bg-gradient-to-br from-rose-100/60 via-red-50/40 to-pink-100/50 p-4 shadow-md transition hover:shadow-lg dark:border-rose-900/30 dark:bg-gradient-to-br dark:from-rose-950/40 dark:via-slate-900/60 dark:to-pink-950/40">
                        <div class="flex items-start justify-between">
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-50">Damaged items report</h3>
                            <span class="text-rose-600 dark:text-rose-400">
                                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                            </span>
                        </div>
                        <p class="mt-2 text-xs text-slate-600 dark:text-slate-400">
                            List of all damaged items across inventory, filtered by your project access.
                        </p>
                        <Link
                            :href="route('reports.damaged-items')"
                            class="mt-3 inline-flex items-center gap-2 rounded-lg bg-rose-100 px-3 py-2 text-xs font-medium text-rose-700 transition hover:bg-rose-200 dark:bg-rose-900/40 dark:text-rose-300 dark:hover:bg-rose-900/60"
                        >
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                            <span>Open report</span>
                        </Link>
                    </section>

                    <section class="overflow-hidden rounded-xl border border-purple-200/50 bg-gradient-to-br from-purple-100/60 via-pink-50/40 to-violet-100/50 p-4 shadow-md transition hover:shadow-lg dark:border-purple-900/30 dark:bg-gradient-to-br dark:from-purple-950/40 dark:via-slate-900/60 dark:to-violet-950/40">
                        <div class="flex items-start justify-between">
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-50">Project inventory</h3>
                            <span class="text-purple-600 dark:text-purple-400">
                                <i class="fa-solid fa-folder-open text-lg"></i>
                            </span>
                        </div>
                        <p class="mt-2 text-xs text-slate-600 dark:text-slate-400">
                            Generate printable inventory for a single project, including all items.
                        </p>
                        <div class="mt-3">
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Select Projects
                            </label>
                            <div class="max-h-32 overflow-y-auto border border-slate-200 rounded-lg dark:border-slate-700">
                                <div class="flex items-center p-2 border-b border-slate-200 bg-slate-50 dark:border-slate-600 dark:bg-slate-700">
                                    <input
                                        id="select-all-projects-report"
                                        type="checkbox"
                                        :checked="selectedProjectIds.length === projects.length && projects.length > 0"
                                        @change="toggleAllProjects"
                                        class="h-3 w-3 text-purple-600 border-slate-300 rounded focus:ring-purple-500 dark:border-slate-600 dark:bg-slate-700"
                                    />
                                    <label for="select-all-projects-report" class="ml-2 text-xs font-medium text-slate-900 dark:text-slate-50 cursor-pointer">
                                        Select All
                                    </label>
                                </div>
                                <div v-for="project in projects" :key="project.id" class="flex items-center p-2 hover:bg-slate-50 dark:hover:bg-slate-800">
                                    <input
                                        :id="`project-${project.id}`"
                                        type="checkbox"
                                        :checked="selectedProjectIds.includes(project.id)"
                                        @change="toggleProject(project.id)"
                                        class="h-3 w-3 text-purple-600 border-slate-300 rounded focus:ring-purple-500 dark:border-slate-600 dark:bg-slate-700"
                                    />
                                    <label :for="`project-${project.id}`" class="ml-2 text-xs text-slate-900 dark:text-slate-50 cursor-pointer">
                                        {{ project.name }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="mt-3 inline-flex items-center gap-2 rounded-lg bg-purple-100 px-3 py-2 text-xs font-medium text-purple-700 transition hover:bg-purple-200 disabled:opacity-50 dark:bg-purple-900/40 dark:text-purple-300 dark:hover:bg-purple-900/60"
                            :disabled="selectedProjectIds.length === 0"
                            @click="openProjectReport"
                        >
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                            <span>Open report</span>
                        </button>
                    </section>
                </div>

                <section class="overflow-hidden rounded-xl border border-amber-200/50 bg-gradient-to-br from-amber-100/60 via-orange-50/40 to-yellow-100/50 p-4 shadow-md transition hover:shadow-lg dark:border-amber-900/30 dark:bg-gradient-to-br dark:from-amber-950/40 dark:via-slate-900/60 dark:to-yellow-950/40">
                    <div class="flex items-start justify-between">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-50">Custom report</h3>
                        <span class="text-amber-600 dark:text-amber-400">
                            <i class="fa-solid fa-sliders text-lg"></i>
                        </span>
                    </div>
                    <p class="mt-2 text-xs text-slate-600 dark:text-slate-400">
                        Generate a custom report by date range, project, status, or specific item code.
                    </p>
                    <Link
                        :href="route('reports.custom')"
                        class="mt-3 inline-flex items-center gap-2 rounded-lg bg-amber-100 px-3 py-2 text-xs font-medium text-amber-700 transition hover:bg-amber-200 dark:bg-amber-900/40 dark:text-amber-300 dark:hover:bg-amber-900/60"
                    >
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                        <span>Open report</span>
                    </Link>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
