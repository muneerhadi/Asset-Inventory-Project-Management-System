<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";

const props = defineProps({
    employees: Array,
    q: String,
});

const search = ref(props.q || "");

// Live search functionality
watch(search, (newValue) => {
    router.get(
        route("search.employees"),
        { q: newValue || undefined },
        { preserveState: true, replace: true },
    );
}, { debounce: 300 });

const clearSearch = () => {
    search.value = '';
    router.get(route('search.employees'), {}, { preserveState: true, replace: true });
};
</script>

<template>
    <Head title="Search employees" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <p
                    class="text-xs font-medium uppercase tracking-wide text-slate-500"
                >
                    Search
                </p>
                <h1
                    class="text-2xl font-semibold leading-tight text-slate-900 dark:text-slate-50"
                >
                    Search employees
                </h1>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-6xl space-y-4 sm:px-6 lg:px-8">
                <div
                    class="flex gap-2 rounded-lg border border-slate-200/50 bg-white/70 p-3 shadow-sm dark:border-slate-700/50 dark:bg-slate-900/70"
                >
                    <Link
                        :href="route('search.items')"
                        :class="[
                            'rounded-full px-3 py-1 text-xs font-medium transition',
                            route().current('search.items')
                                ? 'bg-gradient-to-r from-blue-600 to-sky-600 text-white dark:from-blue-700 dark:to-sky-700'
                                : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700',
                        ]"
                    >
                        <i class="fa-solid fa-box mr-1"></i>
                        Items
                    </Link>
                    <Link
                        :href="route('search.employees')"
                        :class="[
                            'rounded-full px-3 py-1 text-xs font-medium transition',
                            route().current('search.employees')
                                ? 'bg-gradient-to-r from-blue-600 to-sky-600 text-white dark:from-blue-700 dark:to-sky-700'
                                : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700',
                        ]"
                    >
                        <i class="fa-solid fa-users mr-1"></i>
                        Employees
                    </Link>
                </div>

                <form
                    class="flex gap-2 rounded-xl border border-slate-200/50 bg-white/70 p-4 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70"
                >
                    <div class="flex-1 relative">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search by name, location, or position"
                            class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 pr-10 text-sm text-slate-900 placeholder-slate-500 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-50 dark:placeholder-slate-400 dark:focus:border-sky-400"
                        />
                        <button
                            v-if="search"
                            type="button"
                            @click="clearSearch"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300"
                        >
                            <i class="fa-solid fa-times"></i>
                        </button>
                    </div>
                </form>

                <div
                    class="overflow-hidden rounded-xl border border-slate-200/50 bg-gradient-to-br from-white to-slate-50/50 shadow-md dark:border-slate-700/50 dark:from-slate-900 dark:to-slate-800/50"
                >
                    <table
                        class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700"
                    >
                        <thead
                            class="bg-gradient-to-r from-slate-50 to-blue-50/50 dark:from-slate-800 dark:to-slate-900"
                        >
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400"
                                >
                                    Name
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400"
                                >
                                    Location
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400"
                                >
                                    Position
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-slate-200 bg-white/50 dark:divide-slate-700 dark:bg-transparent"
                        >
                            <tr
                                v-for="emp in employees"
                                :key="emp.id"
                                class="transition hover:bg-slate-50 dark:hover:bg-slate-800/30"
                            >
                                <td
                                    class="px-4 py-3 text-xs font-medium text-slate-900 dark:text-slate-50"
                                >
                                    <Link
                                        :href="route('employees.show', emp.id)"
                                        class="font-medium text-sky-600 hover:text-sky-700 dark:text-sky-400 dark:hover:text-sky-300"
                                    >
                                        {{ emp.name }}
                                    </Link>
                                </td>
                                <td
                                    class="px-4 py-3 text-xs text-slate-700 dark:text-slate-300"
                                >
                                    <span
                                        v-if="emp.location"
                                        class="rounded-md bg-amber-100 px-2 py-1 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300"
                                    >
                                        {{ emp.location }}
                                    </span>
                                    <span v-else class="text-slate-500">-</span>
                                </td>
                                <td
                                    class="px-4 py-3 text-xs text-slate-700 dark:text-slate-300"
                                >
                                    <span
                                        v-if="emp.position"
                                        class="rounded-md bg-green-100 px-2 py-1 text-green-700 dark:bg-green-900/40 dark:text-green-300"
                                    >
                                        {{ emp.position }}
                                    </span>
                                    <span v-else class="text-slate-500">-</span>
                                </td>
                            </tr>
                            <tr v-if="!employees.length">
                                <td
                                    colspan="4"
                                    class="px-4 py-8 text-center text-xs text-slate-500"
                                >
                                    <i
                                        class="fa-solid fa-search text-2xl text-slate-300 dark:text-slate-600"
                                    ></i>
                                    <p class="mt-2 font-medium">
                                        No employees found. Try a different
                                        search.
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
