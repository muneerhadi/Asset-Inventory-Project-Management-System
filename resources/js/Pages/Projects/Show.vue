<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { formatDate } from '@/utils/dateFormat';
import { resolveImage } from '@/utils/imageResolver';

const props = defineProps({
    project: Object,
});

const showImportItems = ref(false);
const itemImportFile = ref(null);
const itemFileInput = ref(null);

const removeItem = (id) => {
    router.delete(route('projects.detach-item', props.project.id), {
        data: { item_id: id },
        preserveScroll: true,
    });
};

const deleteProject = () => {
    if (!confirm('Are you sure you want to delete this project?')) return;

    router.delete(route('projects.destroy', props.project.id), {
        preserveScroll: true,
        onSuccess: () => {
            router.visit(route('projects.index'));
        },
    });
};

const importItems = () => {
    if (!itemImportFile.value) return;
    const formData = new FormData();
    formData.append('file', itemImportFile.value);
    router.post(
        route('projects.import-items', props.project.id),
        formData,
        {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                itemImportFile.value = null;
                if (itemFileInput.value) itemFileInput.value.value = '';
                showImportItems.value = false;
            },
            onError: () => {
                // keep the panel open so the user can see any validation errors
            },
        }
    );
};
</script>

<template>
    <Head :title="project.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="no-print">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-3xl font-bold text-slate-900 dark:text-slate-50">
                            <i class="fa-solid fa-diagram-project mr-3 text-blue-600 dark:text-blue-400"></i>
                            {{ project.name }}
                        </h2>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                            {{ project.code }}
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                    <a
                        :href="route('projects.export-items', project.id)"
                        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-emerald-600 to-teal-600 px-4 py-2 text-sm font-medium text-white shadow-md transition hover:shadow-lg dark:from-emerald-700 dark:to-teal-700"
                    >
                        <i class="fa-solid fa-download"></i>
                        Export CSV
                    </a>
                    <button
                        type="button"
                        @click="showImportItems = !showImportItems"
                        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-purple-600 to-pink-600 px-4 py-2 text-sm font-medium text-white shadow-md transition hover:shadow-lg dark:from-purple-700 dark:to-pink-700"
                    >
                        <i class="fa-solid fa-file-import"></i>
                        Import Excel
                    </button>
                    <Link
                        :href="route('reports.project', project.id)"
                        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-blue-600 to-sky-600 px-4 py-2 text-sm font-medium text-white shadow-md transition hover:shadow-lg dark:from-blue-700 dark:to-sky-700"
                    >
                        <i class="fa-solid fa-print"></i>
                        Print
                    </Link>
                    <Link
                        :href="route('projects.edit', project.id)"
                        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-amber-600 to-orange-600 px-4 py-2 text-sm font-medium text-white shadow-md transition hover:shadow-lg dark:from-amber-700 dark:to-orange-700"
                    >
                        <i class="fa-solid fa-pen"></i>
                        Edit
                    </Link>
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-rose-600 to-red-600 px-4 py-2 text-sm font-medium text-white shadow-md transition hover:shadow-lg dark:from-rose-700 dark:to-red-800"
                        @click="deleteProject"
                    >
                        <i class="fa-solid fa-trash"></i>
                        Delete
                    </button>
                    <Link
                        :href="route('projects.index')"
                        class="inline-flex items-center gap-2 rounded-lg bg-slate-200 px-4 py-2 text-sm font-medium text-slate-700 shadow-md transition hover:shadow-lg dark:bg-slate-700 dark:text-slate-200"
                    >
                        <i class="fa-solid fa-arrow-left"></i>
                        Back
                    </Link>
                </div>
            </div>
        </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <!-- Project Details Card -->
                <div class="grid gap-6 md:grid-cols-3 no-print">
                    <!-- Main Content -->
                    <div class="md:col-span-2 space-y-6">
                        <!-- Overview Card -->
                        <div class="overflow-hidden rounded-xl border border-slate-200/50 bg-gradient-to-br from-white to-slate-50/50 p-6 shadow-md dark:border-slate-700/50 dark:from-slate-900 dark:to-slate-800/50">
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-50">
                                <i class="fa-solid fa-info-circle mr-2 text-blue-600 dark:text-blue-400"></i>
                                Project Overview
                            </h3>
                            <dl class="mt-4 grid grid-cols-1 gap-x-4 gap-y-4 text-sm md:grid-cols-2">
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">Name</dt>
                                    <dd class="mt-2 font-semibold text-slate-900 dark:text-slate-50">{{ project.name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">Code</dt>
                                    <dd class="mt-2 font-medium text-slate-900 dark:text-slate-50">{{ project.code }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">Start Date</dt>
                                    <dd class="mt-2 text-slate-700 dark:text-slate-300">{{ formatDate(project.start_date) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">End Date</dt>
                                    <dd class="mt-2 text-slate-700 dark:text-slate-300">{{ formatDate(project.end_date) }}</dd>
                                </div>
                            </dl>

                            <div v-if="project.description" class="mt-6 border-t border-slate-200 pt-6 dark:border-slate-700">
                                <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-50">
                                    <i class="fa-solid fa-file-lines mr-2 text-slate-600 dark:text-slate-400"></i>
                                    Description
                                </h4>
                                <p class="mt-3 whitespace-pre-line text-sm text-slate-700 dark:text-slate-300">
                                    {{ project.description }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Logo Card -->
                        <div class="overflow-hidden rounded-xl border-2 border-amber-200/70 bg-gradient-to-br from-amber-50 to-yellow-50/50 p-6 shadow-md dark:border-amber-900/50 dark:from-amber-950/30 dark:to-amber-900/20">
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-50">
                                <i class="fa-solid fa-image mr-2 text-amber-600 dark:text-amber-400"></i>
                                Project Logo
                            </h3>
                            <div class="mt-4">
                                <div v-if="project.logo_path" class="flex items-center justify-center rounded-lg bg-white p-4 ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-slate-700">
                                    <img :src="resolveImage(project.logo_path)" alt="Project logo" class="max-h-56 w-auto object-contain" />
                                </div>
                                <div v-else class="flex flex-col items-center justify-center rounded-lg bg-white p-8 text-center ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-slate-700">
                                    <i class="fa-solid fa-image text-3xl text-slate-300 dark:text-slate-600"></i>
                                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">No logo available</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Import Items Panel -->
                <div v-if="showImportItems" class="mb-6 rounded-xl border border-slate-200/50 bg-white/70 p-4 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70">
                    <div class="space-y-3">
                        <h4 class="font-semibold text-slate-900 dark:text-slate-50">
                            <i class="fa-solid fa-upload mr-2 text-orange-600 dark:text-orange-400"></i>
                            Import Items from Excel
                        </h4>
                        <div class="flex items-end gap-2 flex-wrap">
                            <div class="flex-1 min-w-max">
                                <input
                                    ref="itemFileInput"
                                    type="file"
                                    accept=".xlsx,.xls,.csv"
                                    class="block w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-orange-100 file:text-orange-700 hover:file:bg-orange-200 dark:file:bg-orange-900/30 dark:file:text-orange-300"
                                    @change="itemImportFile = $event.target.files?.[0]"
                                />
                            </div>
                            <div class="flex gap-2">
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-orange-600 to-red-600 px-3 py-2 text-xs font-medium text-white transition hover:shadow-lg disabled:opacity-50"
                                    @click="importItems"
                                    :disabled="!itemImportFile"
                                >
                                    <i class="fa-solid fa-upload"></i>
                                    Import
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-lg bg-slate-300 px-3 py-2 text-xs font-medium text-slate-700 transition hover:bg-slate-400 dark:bg-slate-600 dark:text-slate-200 dark:hover:bg-slate-500"
                                    @click="showImportItems = false"
                                >
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items List -->
                <div class="overflow-hidden rounded-xl border border-slate-200/50 bg-white/70 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70">
                    <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-slate-100 px-6 py-4 dark:border-slate-700 dark:from-slate-900 dark:to-slate-800">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-50">
                            <i class="fa-solid fa-box mr-2 text-amber-600 dark:text-amber-400"></i>
                            Items in Project
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50/50 dark:border-slate-700 dark:bg-slate-800/50">
                                    <th class="px-4 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">#</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">Name</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">Status</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-900 dark:text-slate-50">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                <tr
                                    v-for="(item, index) in project.items || []"
                                    :key="item.id"
                                    class="bg-white transition hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50"
                                >
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ index + 1 }}</td>
                                    <td class="px-4 py-3">
                                        <Link
                                            :href="route('items.show', item.id)"
                                            class="font-medium text-sky-600 hover:text-sky-700 dark:text-sky-400 dark:hover:text-sky-300"
                                        >
                                            {{ item.name }}
                                        </Link>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            v-if="item.status?.name"
                                            class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold whitespace-nowrap"
                                            :class="{
                                                'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300': item.status?.slug === 'active',
                                                'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300': item.status?.slug === 'daghma',
                                                'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300': item.status?.is_damaged,
                                                'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300': !item.status,
                                            }"
                                        >
                                            {{ item.status.name }}
                                        </span>
                                        <span v-else class="text-slate-500 dark:text-slate-400">-</span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-lg bg-rose-100 px-3 py-1 text-xs font-medium text-rose-700 transition hover:bg-rose-200 dark:bg-rose-900/30 dark:text-rose-300 dark:hover:bg-rose-900/50"
                                            @click="removeItem(item.id)"
                                        >
                                            <i class="fa-solid fa-trash text-xs"></i>
                                            <span>Delete</span>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!project.items || !project.items.length">
                                    <td colspan="4" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400">
                                        <i class="fa-solid fa-inbox text-2xl mb-2 block opacity-50"></i>
                                        No items in this project.
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
@media print {
    .no-print {
        display: none !important;
    }

    .only-print {
        display: block !important;
    }
}
</style>
