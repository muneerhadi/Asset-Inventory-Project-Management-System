<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    categories: Array,
    statuses: Array,
    currencies: Array,
    projectManagers: Array,
    projects: Array,
});

const categoryForm = useForm({
    name: '',
    description: '',
    is_active: true,
});

const toggleCategory = (category) => {
    router.put(route('settings.categories.toggle', category.id), {
        is_active: !category.is_active,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const statusForm = useForm({
    name: '',
    description: '',
});

const currencyForm = useForm({
    code: '',
    name: '',
    symbol: '',
    is_default: false,
});

const managerForm = useForm({
    name: '',
    email: '',
    password: '',
    project_ids: [],
});

const editingManagerProjects = ref(null);
const editingProjects = ref([]);
const deletingManager = ref(null);
const deleteStep = ref(1);
const deletePassword = ref('');
const deletePasswordError = ref('');

const openEditManagerProjects = (manager) => {
    editingManagerProjects.value = manager;
    editingProjects.value = manager.projects?.map((p) => p.id) ?? [];
};

const openDeleteManager = (manager) => {
    deletingManager.value = manager;
    deleteStep.value = 1;
    deletePassword.value = '';
    deletePasswordError.value = '';
};

const nextDeleteStep = () => {
    if (deleteStep.value < 3) {
        deleteStep.value++;
    }
};

const prevDeleteStep = () => {
    if (deleteStep.value > 1) {
        deleteStep.value--;
    }
};

const cancelDelete = () => {
    deletingManager.value = null;
    deleteStep.value = 1;
    deletePassword.value = '';
    deletePasswordError.value = '';
};

const confirmDelete = () => {
    if (!deletingManager.value || !deletePassword.value) return;
    
    router.delete(
        route('settings.project-managers.destroy', deletingManager.value.id),
        {
            data: { password: deletePassword.value },
            onError: (errors) => {
                deletePasswordError.value = errors.password || 'An error occurred';
            },
            onSuccess: () => {
                cancelDelete();
            },
        }
    );
};

const submitCategory = () => {
    categoryForm.post(route('settings.categories.store'), {
        onSuccess: () => categoryForm.reset(),
    });
};

// Delete functionality removed for categories

const submitStatus = () => {
    statusForm.post(route('settings.statuses.store'), {
        onSuccess: () => statusForm.reset(),
    });
};

// Delete functionality removed for statuses

const submitCurrency = () => {
    currencyForm.post(route('settings.currencies.store'), {
        onSuccess: () => currencyForm.reset(),
    });
};

// Delete functionality removed for currencies

const submitManager = () => {
    managerForm.post(route('settings.project-managers.store'), {
        onSuccess: () => managerForm.reset(),
    });
};

const submitManagerProjects = () => {
    if (!editingManagerProjects.value) return;
    router.post(
        route('settings.project-managers.projects.update', editingManagerProjects.value.id),
        { project_ids: editingProjects.value },
        {
            onSuccess: () => {
                editingManagerProjects.value = null;
                editingProjects.value = [];
            },
        },
    );
};

const toggleEditingProject = (projectId) => {
    if (editingProjects.value.includes(projectId)) {
        editingProjects.value = editingProjects.value.filter((id) => id !== projectId);
    } else {
        editingProjects.value.push(projectId);
    }
};
</script>

<template>
    <Head title="Settings" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-slate-900 dark:text-slate-50">
                    Settings
                </h2>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="grid gap-6 lg:grid-cols-3">
                    <!-- Categories -->
                    <section class="rounded-xl border border-blue-200/60 bg-gradient-to-br from-blue-50/80 via-white to-sky-50/80 p-5 shadow-md lg:col-span-1 dark:border-blue-900/40 dark:bg-gradient-to-br dark:from-blue-950/40 dark:via-slate-900 dark:to-sky-950/30">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-slate-50">Item Categories</h3>
                        <form class="mt-3 space-y-2" @submit.prevent="submitCategory">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-slate-300">Name</label>
                                <input
                                    v-model="categoryForm.name"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-white text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-50 dark:focus:border-indigo-400"
                                    required
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-slate-300">Description</label>
                                <textarea
                                    v-model="categoryForm.description"
                                    rows="2"
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-white text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-50 dark:focus:border-indigo-400"
                                />
                            </div>
                            <div class="pt-2 flex justify-end">
                                <button
                                    type="submit"
                                    class="inline-flex items-center gap-1 rounded-md bg-sky-600 px-3 py-2 text-xs font-medium text-white shadow-sm hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-1 dark:bg-sky-500 dark:hover:bg-sky-400"
                                    :disabled="categoryForm.processing"
                                >
                                    <i class="fa-solid fa-plus text-[11px]"></i>
                                    <span>Add category</span>
                                </button>
                            </div>
                        </form>

                        <ul class="mt-4 divide-y divide-gray-100 text-sm text-gray-700 dark:divide-slate-700 dark:text-slate-300">
                            <li
                                v-for="cat in categories"
                                :key="cat.id"
                                class="py-1.5 flex items-center justify-between"
                            >
                                <span :class="{'opacity-50 line-through': !cat.is_active}">{{ cat.name }}</span>
                                <button
                                    v-if="cat.is_active !== undefined"
                                    @click="toggleCategory(cat)"
                                    class="text-xs px-2 py-1 rounded bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-slate-200"
                                >
                                    {{ cat.is_active ? 'Disable' : 'Enable' }}
                                </button>
                            </li>
                            <li v-if="!categories.length" class="py-2 text-xs text-gray-500 dark:text-slate-400">
                                No categories yet.
                            </li>
                        </ul>
                    </section>

                    <!-- Statuses -->
                    <section class="rounded-xl border border-purple-200/60 bg-gradient-to-br from-purple-50/80 via-white to-pink-50/80 p-5 shadow-md lg:col-span-1 dark:border-purple-900/40 dark:bg-gradient-to-br dark:from-purple-950/40 dark:via-slate-900 dark:to-pink-950/30">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-slate-50">Item Statuses</h3>
                        <form class="mt-3 space-y-2" @submit.prevent="submitStatus">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-slate-300">Name</label>
                                <input
                                    v-model="statusForm.name"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-white text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-50 dark:focus:border-indigo-400"
                                    required
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-slate-300">Description</label>
                                <textarea
                                    v-model="statusForm.description"
                                    rows="2"
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-white text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-50 dark:focus:border-indigo-400"
                                />
                            </div>
                            <div class="pt-2 flex justify-end">
                                <button
                                    type="submit"
                                    class="inline-flex items-center gap-1 rounded-md bg-sky-600 px-3 py-2 text-xs font-medium text-white shadow-sm hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-1 dark:bg-sky-500 dark:hover:bg-sky-400"
                                    :disabled="statusForm.processing"
                                >
                                    <i class="fa-solid fa-plus text-[11px]"></i>
                                    <span>Add status</span>
                                </button>
                            </div>
                        </form>

                        <ul class="mt-4 divide-y divide-gray-100 text-sm text-gray-700 dark:divide-slate-700 dark:text-slate-300">
                            <li
                                v-for="st in statuses"
                                :key="st.id"
                                class="py-1.5"
                            >
                                <p class="font-medium dark:text-slate-50">{{ st.name }}</p>
                            </li>
                            <li v-if="!statuses.length" class="py-2 text-xs text-gray-500 dark:text-slate-400">
                                No statuses yet.
                            </li>
                        </ul>
                    </section>

                    <!-- Currencies -->
                    <section class="rounded-xl border border-emerald-200/60 bg-gradient-to-br from-emerald-50/80 via-white to-green-50/80 p-5 shadow-md lg:col-span-1 dark:border-emerald-900/40 dark:bg-gradient-to-br dark:from-emerald-950/40 dark:via-slate-900 dark:to-green-950/30">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-slate-50">Currencies</h3>
                        <form class="mt-3 space-y-2" @submit.prevent="submitCurrency">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-slate-300">Code</label>
                                <input
                                    v-model="currencyForm.code"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-white text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-50 dark:focus:border-indigo-400"
                                    required
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-slate-300">Name</label>
                                <input
                                    v-model="currencyForm.name"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-white text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-50 dark:focus:border-indigo-400"
                                    required
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-slate-300">Symbol</label>
                                <input
                                    v-model="currencyForm.symbol"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-white text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-50 dark:focus:border-indigo-400"
                                />
                            </div>
                            <label class="inline-flex items-center gap-1 text-xs text-gray-700 dark:text-slate-300">
                                <input v-model="currencyForm.is_default" type="checkbox" class="rounded border-gray-300 text-indigo-600 dark:border-slate-600 dark:bg-slate-800" />
                                Default currency
                            </label>
                            <div class="pt-2 flex justify-end">
                                <button
                                    type="submit"
                                    class="inline-flex items-center gap-1 rounded-md bg-sky-600 px-3 py-2 text-xs font-medium text-white shadow-sm hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-1 dark:bg-sky-500 dark:hover:bg-sky-400"
                                    :disabled="currencyForm.processing"
                                >
                                    <i class="fa-solid fa-floppy-disk text-[11px]"></i>
                                    <span>Save currency</span>
                                </button>
                            </div>
                        </form>

                        <ul class="mt-4 divide-y divide-gray-100 text-sm text-gray-700 dark:divide-slate-700 dark:text-slate-300">
                            <li
                                v-for="cur in currencies"
                                :key="cur.id"
                                class="py-1.5"
                            >
                                <div>
                                    <p class="font-medium dark:text-slate-50">
                                        {{ cur.code }}
                                        <span class="text-gray-500 dark:text-slate-400">- {{ cur.name }}</span>
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-slate-400">
                                        {{ cur.symbol }}
                                        <span v-if="cur.is_default" class="ml-1 rounded bg-emerald-100 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">default</span>
                                    </p>
                                </div>
                            </li>
                            <li v-if="!currencies.length" class="py-2 text-xs text-gray-500 dark:text-slate-400">
                                No currencies yet.
                            </li>
                        </ul>
                    </section>
                </div>

                <!-- Project Managers Section -->
                <div class="mt-8">
                    <section class="rounded-xl border border-amber-200/60 bg-gradient-to-br from-amber-50/80 via-white to-yellow-50/80 p-6 shadow-md dark:border-amber-900/40 dark:bg-gradient-to-br dark:from-amber-950/40 dark:via-slate-900 dark:to-yellow-950/30">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-slate-50 mb-4">
                            <i class="fa-solid fa-users-gear mr-2 text-amber-600 dark:text-amber-400"></i>
                            Project Managers
                        </h3>
                        
                        <!-- Create New Project Manager Form -->
                        <div class="bg-white/50 dark:bg-slate-800/50 rounded-lg p-4 mb-6">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-slate-50 mb-3">Create New Project Manager</h4>
                            <form class="grid gap-4 md:grid-cols-2" @submit.prevent="submitManager">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-slate-300">Name *</label>
                                    <input
                                        v-model="managerForm.name"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 bg-white text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:ring-amber-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-50"
                                        required
                                    />
                                    <p v-if="managerForm.errors.name" class="mt-1 text-xs text-red-600">{{ managerForm.errors.name }}</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-slate-300">Email *</label>
                                    <input
                                        v-model="managerForm.email"
                                        type="email"
                                        class="mt-1 block w-full rounded-md border-gray-300 bg-white text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:ring-amber-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-50"
                                        required
                                    />
                                    <p v-if="managerForm.errors.email" class="mt-1 text-xs text-red-600">{{ managerForm.errors.email }}</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-slate-300">Password *</label>
                                    <input
                                        v-model="managerForm.password"
                                        type="password"
                                        class="mt-1 block w-full rounded-md border-gray-300 bg-white text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:ring-amber-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-50"
                                        required
                                    />
                                    <p v-if="managerForm.errors.password" class="mt-1 text-xs text-red-600">{{ managerForm.errors.password }}</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-slate-300 mb-2">Assign Projects</label>
                                    <div class="max-h-32 overflow-y-auto border border-gray-200 rounded-md p-2 dark:border-slate-600 dark:bg-slate-800">
                                        <label v-for="project in projects" :key="project.id" class="flex items-center gap-2 text-xs py-1">
                                            <input
                                                v-model="managerForm.project_ids"
                                                :value="project.id"
                                                type="checkbox"
                                                class="rounded border-gray-300 text-amber-600 dark:border-slate-600 dark:bg-slate-800"
                                            />
                                            <span class="text-gray-700 dark:text-slate-300">{{ project.name }}</span>
                                        </label>
                                        <div v-if="!projects.length" class="text-xs text-gray-500 dark:text-slate-400 py-2">
                                            No projects available
                                        </div>
                                    </div>
                                </div>
                                <div class="md:col-span-2 flex justify-end">
                                    <button
                                        type="submit"
                                        class="inline-flex items-center gap-2 rounded-md bg-amber-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 dark:bg-amber-500 dark:hover:bg-amber-400"
                                        :disabled="managerForm.processing"
                                    >
                                        <i class="fa-solid fa-user-plus text-xs"></i>
                                        <span>Create Manager</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Existing Project Managers List -->
                        <div class="space-y-3">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-slate-50">Existing Project Managers</h4>
                            <div v-if="projectManagers.length" class="grid gap-3 md:grid-cols-2">
                                <div
                                    v-for="manager in projectManagers"
                                    :key="manager.id"
                                    class="bg-white/70 dark:bg-slate-800/70 rounded-lg p-4 border border-gray-200 dark:border-slate-700"
                                >
                                    <div class="flex items-start justify-between mb-2">
                                        <div>
                                            <h5 class="font-medium text-gray-900 dark:text-slate-50">{{ manager.name }}</h5>
                                            <p class="text-xs text-gray-600 dark:text-slate-400">{{ manager.email }}</p>
                                        </div>
                                        <div class="flex gap-2">
                                            <button
                                                @click="openEditManagerProjects(manager)"
                                                class="text-xs px-2 py-1 bg-amber-100 text-amber-700 rounded hover:bg-amber-200 dark:bg-amber-900/30 dark:text-amber-300"
                                            >
                                                Edit Projects
                                            </button>
                                            <button
                                                @click="openDeleteManager(manager)"
                                                class="text-xs px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 dark:bg-red-900/30 dark:text-red-300 dark:hover:bg-red-800/40"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                    <div v-if="manager.projects && manager.projects.length" class="mt-2">
                                        <p class="text-xs font-medium text-gray-700 dark:text-slate-300 mb-1">Assigned Projects:</p>
                                        <div class="flex flex-wrap gap-1">
                                            <span
                                                v-for="project in manager.projects"
                                                :key="project.id"
                                                class="inline-block px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded dark:bg-blue-900/30 dark:text-blue-300"
                                            >
                                                {{ project.name }}
                                            </span>
                                        </div>
                                    </div>
                                    <div v-else class="mt-2 text-xs text-gray-500 dark:text-slate-400">
                                        No projects assigned
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-sm text-gray-500 dark:text-slate-400 text-center py-4">
                                No project managers created yet.
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>

        <!-- Delete Manager Modal -->
        <div v-if="deletingManager" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 dark:bg-black/70">
            <div class="w-full max-w-md space-y-4 rounded-xl bg-white shadow-xl dark:bg-slate-900">
                <div class="border-b border-slate-200 bg-gradient-to-r from-red-50 to-rose-50 px-6 py-4 dark:border-slate-700 dark:from-slate-800 dark:to-slate-900">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-50">
                        <i class="fa-solid fa-triangle-exclamation mr-2 text-red-600 dark:text-red-400"></i>
                        Delete Project Manager
                    </h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Step {{ deleteStep }} of 3</p>
                </div>
                
                <!-- Step 1: Warning -->
                <div v-if="deleteStep === 1" class="space-y-4 px-6 py-4">
                    <div class="text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                            <i class="fa-solid fa-exclamation-triangle text-red-600 dark:text-red-400"></i>
                        </div>
                        <h4 class="mt-3 text-lg font-medium text-slate-900 dark:text-slate-50">
                            Are you sure?
                        </h4>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                            You are about to delete <strong>{{ deletingManager.name }}</strong>.
                        </p>
                    </div>
                </div>
                
                <!-- Step 2: Confirmation -->
                <div v-if="deleteStep === 2" class="space-y-4 px-6 py-4">
                    <div class="text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                            <i class="fa-solid fa-user-xmark text-red-600 dark:text-red-400"></i>
                        </div>
                        <h4 class="mt-3 text-lg font-medium text-slate-900 dark:text-slate-50">
                            This action cannot be undone
                        </h4>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                            The project manager account will be permanently deleted.
                        </p>
                    </div>
                </div>
                
                <!-- Step 3: Password Verification -->
                <div v-if="deleteStep === 3" class="space-y-4 px-6 py-4">
                    <div class="text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                            <i class="fa-solid fa-lock text-red-600 dark:text-red-400"></i>
                        </div>
                        <h4 class="mt-3 text-lg font-medium text-slate-900 dark:text-slate-50">
                            Enter your password
                        </h4>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                            Please confirm your admin password to proceed.
                        </p>
                    </div>
                    <div>
                        <input
                            v-model="deletePassword"
                            type="password"
                            placeholder="Your admin password"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:ring-red-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-50"
                            @keyup.enter="confirmDelete"
                        />
                        <p v-if="deletePasswordError" class="mt-1 text-xs text-red-600">{{ deletePasswordError }}</p>
                    </div>
                </div>
                
                <div class="flex items-center justify-between border-t border-slate-200 bg-slate-50 px-6 py-4 dark:border-slate-700 dark:bg-slate-800/50">
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600"
                        @click="deleteStep === 1 ? cancelDelete() : prevDeleteStep()"
                    >
                        <i class="fa-solid fa-arrow-left" v-if="deleteStep > 1"></i>
                        <i class="fa-solid fa-xmark" v-else></i>
                        {{ deleteStep === 1 ? 'Cancel' : 'Back' }}
                    </button>
                    <button
                        v-if="deleteStep < 3"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-6 py-2.5 text-sm font-medium text-white shadow-md transition hover:bg-red-700"
                        @click="nextDeleteStep"
                    >
                        Continue
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                    <button
                        v-else
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-6 py-2.5 text-sm font-medium text-white shadow-md transition hover:bg-red-700 disabled:opacity-50"
                        :disabled="!deletePassword"
                        @click="confirmDelete"
                    >
                        <i class="fa-solid fa-trash"></i>
                        Delete Manager
                    </button>
                </div>
            </div>
        </div>

        <!-- Edit Manager Projects Modal -->
        <div v-if="editingManagerProjects" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 dark:bg-black/70">
            <div class="w-full max-w-md space-y-4 rounded-xl bg-white shadow-xl dark:bg-slate-900">
                <div class="border-b border-slate-200 bg-gradient-to-r from-amber-50 to-yellow-50 px-6 py-4 dark:border-slate-700 dark:from-slate-800 dark:to-slate-900">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-50">
                        <i class="fa-solid fa-diagram-project mr-2 text-amber-600 dark:text-amber-400"></i>
                        Edit Projects for {{ editingManagerProjects.name }}
                    </h3>
                </div>
                <div class="space-y-4 px-6 py-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-900 dark:text-slate-50 mb-2">
                            Assign Projects
                        </label>
                        <div class="max-h-60 overflow-y-auto border border-slate-200 rounded-lg dark:border-slate-700">
                            <div v-for="project in projects" :key="project.id" class="flex items-center p-3 hover:bg-slate-50 dark:hover:bg-slate-800">
                                <input
                                    :id="`project-${project.id}`"
                                    :checked="editingProjects.includes(project.id)"
                                    @change="toggleEditingProject(project.id)"
                                    type="checkbox"
                                    class="h-4 w-4 text-amber-600 border-slate-300 rounded focus:ring-amber-500 dark:border-slate-600 dark:bg-slate-700"
                                />
                                <label :for="`project-${project.id}`" class="ml-3 flex-1 text-sm cursor-pointer">
                                    <div class="font-medium text-slate-900 dark:text-slate-50">
                                        {{ project.name }}
                                    </div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ project.code }}
                                    </div>
                                </label>
                            </div>
                            <div v-if="!projects.length" class="p-4 text-center text-slate-500 dark:text-slate-400">
                                No projects available
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 dark:border-slate-700 dark:bg-slate-800/50">
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600"
                        @click="editingManagerProjects = null; editingProjects = []"
                    >
                        <i class="fa-solid fa-xmark"></i>
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-amber-600 to-yellow-600 px-6 py-2.5 text-sm font-medium text-white shadow-md transition hover:shadow-lg dark:from-amber-700 dark:to-yellow-700"
                        @click="submitManagerProjects"
                    >
                        <i class="fa-solid fa-check"></i>
                        Update Projects
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
