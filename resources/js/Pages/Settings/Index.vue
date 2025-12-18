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

const openEditManagerProjects = (manager) => {
    editingManagerProjects.value = manager;
    editingProjects.value = manager.projects?.map((p) => p.id) ?? [];
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
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-white text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-slate-100 dark:text-black"
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
                                <label class="block text-xs font-medium text-gray-700">Code</label>
                                <input
                                    v-model="currencyForm.code"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-white text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-slate-100 dark:text-black"
                                    required
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-slate-300">Name</label>
                                <input
                                    v-model="currencyForm.name"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-white text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-slate-100 dark:text-black"
                                    required
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700">Symbol</label>
                                <input
                                    v-model="currencyForm.symbol"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-white text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-slate-100 dark:text-black"
                                />
                            </div>
                            <label class="inline-flex items-center gap-1 text-xs text-gray-700 dark:text-slate-300">
                                <input v-model="currencyForm.is_default" type="checkbox" class="rounded border-gray-300 text-indigo-600" />
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
            </div>
        </div>
    </AuthenticatedLayout>
</template>
