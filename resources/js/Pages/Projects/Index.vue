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
const showDeleteModal = ref(false);
const showPasswordModal = ref(false);
const showFinalConfirmModal = ref(false);
const projectToDelete = ref(null);
const adminPassword = ref('');
const passwordError = ref('');
const isVerifyingPassword = ref(false);

const submitSearch = () => {
    router.get(route('projects.index'), { search: search.value }, { preserveState: true, replace: true });
};

const deleteProject = (project) => {
    projectToDelete.value = project;
    showDeleteModal.value = true;
};

const proceedToPasswordVerification = () => {
    showDeleteModal.value = false;
    showPasswordModal.value = true;
    adminPassword.value = '';
    passwordError.value = '';
};

const verifyPassword = () => {
    if (!adminPassword.value.trim()) {
        passwordError.value = 'Password is required';
        return;
    }
    
    isVerifyingPassword.value = true;
    passwordError.value = '';
    
    router.post(route('projects.verify-password'), {
        password: adminPassword.value
    }, {
        preserveState: true,
        onSuccess: () => {
            showPasswordModal.value = false;
            showFinalConfirmModal.value = true;
            adminPassword.value = '';
            isVerifyingPassword.value = false;
        },
        onError: (errors) => {
            passwordError.value = errors.password || 'Invalid password';
            isVerifyingPassword.value = false;
        }
    });
};

const finalDeleteConfirm = () => {
    if (projectToDelete.value) {
        router.delete(route('projects.destroy', projectToDelete.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                cancelAllModals();
            }
        });
    }
};

const cancelAllModals = () => {
    showDeleteModal.value = false;
    showPasswordModal.value = false;
    showFinalConfirmModal.value = false;
    projectToDelete.value = null;
    adminPassword.value = '';
    passwordError.value = '';
    isVerifyingPassword.value = false;
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
                        My Projects
                    </h1>
                </div>
                <Link
                    v-if="$page.props.auth.user.role === 'super_admin'"
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
                                                v-if="$page.props.auth.user.role === 'super_admin'"
                                                :href="route('projects.edit', project.id)"
                                                class="inline-flex items-center gap-1 rounded-md bg-slate-200 px-2 py-1 text-xs font-medium text-slate-700 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600"
                                            >
                                                <i class="fa-solid fa-pen text-xs"></i>
                                                <span>Edit</span>
                                            </Link>
                                            <button
                                                v-if="$page.props.auth.user.role === 'super_admin'"
                                                type="button"
                                                @click="deleteProject(project)"
                                                class="inline-flex items-center gap-1 rounded-md bg-rose-100 px-2 py-1 text-xs font-medium text-rose-700 transition hover:bg-rose-200 dark:bg-rose-900/40 dark:text-rose-300 dark:hover:bg-rose-900/60"
                                            >
                                                <i class="fa-solid fa-trash text-xs"></i>
                                                <span>Delete</span>
                                            </button>
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

        <!-- Step 1: Initial Delete Warning Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 dark:bg-black/70">
            <div class="w-full max-w-lg space-y-4 rounded-xl bg-white shadow-xl dark:bg-slate-900">
                <div class="border-b border-slate-200 bg-gradient-to-r from-red-50 to-rose-50 px-6 py-4 dark:border-slate-700 dark:from-red-950/50 dark:to-rose-950/50">
                    <h3 class="text-lg font-semibold text-red-900 dark:text-red-100">
                        <i class="fa-solid fa-triangle-exclamation mr-2 text-red-600 dark:text-red-400"></i>
                        ⚠️ DANGER - Delete Project
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4 dark:bg-red-950/20 dark:border-red-800">
                        <h4 class="font-semibold text-red-800 dark:text-red-200 mb-2">⚠️ CRITICAL WARNING</h4>
                        <p class="text-sm text-red-700 dark:text-red-300 mb-2">
                            You are about to permanently delete a project. This action will:
                        </p>
                        <ul class="text-sm text-red-700 dark:text-red-300 list-disc list-inside space-y-1">
                            <li>Remove all project data permanently</li>
                            <li>Unassign all employees from this project</li>
                            <li>Unassign all items from this project</li>
                            <li>Delete all project-related records</li>
                        </ul>
                        <p class="text-sm font-semibold text-red-800 dark:text-red-200 mt-3">
                            This action CANNOT be undone!
                        </p>
                    </div>
                    <div v-if="projectToDelete" class="bg-slate-50 dark:bg-slate-800 rounded-lg p-3">
                        <p class="font-medium text-slate-900 dark:text-slate-50">{{ projectToDelete.name }}</p>
                        <p class="text-sm text-slate-600 dark:text-slate-400">Code: {{ projectToDelete.code || 'N/A' }}</p>
                        <p class="text-sm text-slate-600 dark:text-slate-400">Start: {{ formatDate(projectToDelete.start_date) }}</p>
                        <p class="text-sm text-slate-600 dark:text-slate-400">End: {{ formatDate(projectToDelete.end_date) }}</p>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 dark:border-slate-700 dark:bg-slate-800/50">
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600"
                        @click="cancelAllModals"
                    >
                        <i class="fa-solid fa-xmark"></i>
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-red-600 to-rose-600 px-6 py-2.5 text-sm font-medium text-white shadow-md transition hover:shadow-lg"
                        @click="proceedToPasswordVerification"
                    >
                        <i class="fa-solid fa-arrow-right"></i>
                        I Understand, Continue
                    </button>
                </div>
            </div>
        </div>

        <!-- Step 2: Admin Password Verification Modal -->
        <div v-if="showPasswordModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 dark:bg-black/70">
            <div class="w-full max-w-md space-y-4 rounded-xl bg-white shadow-xl dark:bg-slate-900">
                <div class="border-b border-slate-200 bg-gradient-to-r from-amber-50 to-orange-50 px-6 py-4 dark:border-slate-700 dark:from-amber-950/50 dark:to-orange-950/50">
                    <h3 class="text-lg font-semibold text-amber-900 dark:text-amber-100">
                        <i class="fa-solid fa-lock mr-2 text-amber-600 dark:text-amber-400"></i>
                        Admin Password Required
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <p class="text-sm text-slate-700 dark:text-slate-300 mb-4">
                        Enter your admin password to continue with project deletion:
                    </p>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Admin Password *
                            </label>
                            <input
                                v-model="adminPassword"
                                type="password"
                                placeholder="Enter your password"
                                class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-50"
                                :class="{ 'border-red-500 ring-2 ring-red-500/20': passwordError }"
                                @keyup.enter="verifyPassword"
                                :disabled="isVerifyingPassword"
                            />
                            <p v-if="passwordError" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ passwordError }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 dark:border-slate-700 dark:bg-slate-800/50">
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600"
                        @click="cancelAllModals"
                        :disabled="isVerifyingPassword"
                    >
                        <i class="fa-solid fa-xmark"></i>
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-amber-600 to-orange-600 px-6 py-2.5 text-sm font-medium text-white shadow-md transition hover:shadow-lg disabled:opacity-50"
                        @click="verifyPassword"
                        :disabled="!adminPassword.trim() || isVerifyingPassword"
                    >
                        <i v-if="isVerifyingPassword" class="fa-solid fa-spinner fa-spin"></i>
                        <i v-else class="fa-solid fa-key"></i>
                        {{ isVerifyingPassword ? 'Verifying...' : 'Verify Password' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Step 3: Final Confirmation Modal -->
        <div v-if="showFinalConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 dark:bg-black/70">
            <div class="w-full max-w-md space-y-4 rounded-xl bg-white shadow-xl dark:bg-slate-900">
                <div class="border-b border-slate-200 bg-gradient-to-r from-red-50 to-rose-50 px-6 py-4 dark:border-slate-700 dark:from-red-950/50 dark:to-rose-950/50">
                    <h3 class="text-lg font-semibold text-red-900 dark:text-red-100">
                        <i class="fa-solid fa-exclamation-triangle mr-2 text-red-600 dark:text-red-400"></i>
                        Final Confirmation
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <p class="text-sm text-slate-700 dark:text-slate-300 mb-4">
                        Password verified. This is your final chance to cancel.
                    </p>
                    <div class="bg-red-50 border border-red-200 rounded-lg p-3 dark:bg-red-950/20 dark:border-red-800">
                        <p class="text-sm font-semibold text-red-800 dark:text-red-200">
                            Are you absolutely sure you want to delete this project?
                        </p>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 dark:border-slate-700 dark:bg-slate-800/50">
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600"
                        @click="cancelAllModals"
                    >
                        <i class="fa-solid fa-xmark"></i>
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-red-600 to-rose-600 px-6 py-2.5 text-sm font-medium text-white shadow-md transition hover:shadow-lg"
                        @click="finalDeleteConfirm"
                    >
                        <i class="fa-solid fa-trash"></i>
                        Delete Project Forever
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>