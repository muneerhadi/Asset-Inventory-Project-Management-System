<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { formatDate } from "@/utils/dateFormat";
import { resolveImage } from "@/utils/imageResolver";

const props = defineProps({
    welcomeMessage: String,
    userRole: String,
    metrics: Object,
    recentActivities: Array,
    recentEmployees: Array,
    recentItems: Array,
    projectDeadlines: Array,
    error: String,
});

const handleImageError = (event) => {
    event.target.style.display = 'none';
    const fallback = event.target.nextElementSibling;
    if (fallback) {
        fallback.style.display = 'flex';
    }
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between"
            >
                <div>
                    <p
                        class="text-xs font-medium uppercase tracking-wide text-slate-500"
                    >
                        Dashboard
                    </p>
                    <h1
                        class="text-2xl font-semibold leading-tight text-slate-900 dark:text-slate-50"
                    >
                        Welcome to Union Aid MIS
                    </h1>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                Error: {{ error }}
            </div>
            <!-- Metrics cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <div
                    class="flex flex-col justify-between overflow-hidden rounded-xl border border-blue-200/60 bg-gradient-to-br from-blue-100/60 via-blue-50/40 to-sky-100/50 p-4 text-left shadow-md transition hover:shadow-lg dark:border-blue-900/40 dark:bg-gradient-to-br dark:from-blue-950/40 dark:via-slate-900/60 dark:to-sky-950/40"
                >
                    <div class="flex items-center justify-between">
                        <p
                            class="text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400"
                        >
                            Registered Employees
                        </p>
                        <span class="text-sky-600 dark:text-sky-400">
                            <i class="fa-solid fa-users text-lg"></i>
                        </span>
                    </div>
                    <p
                        class="mt-3 text-2xl font-semibold text-slate-900 dark:text-slate-50"
                    >
                        {{ metrics.employees ?? 0 }}
                    </p>
                    <Link
                        :href="route('employees.index')"
                        class="mt-2 inline-flex items-center text-xs font-medium text-sky-600 hover:text-sky-700 dark:text-sky-400 dark:hover:text-sky-300"
                    >
                        View employees
                    </Link>
                </div>

                <div
                    class="flex flex-col justify-between overflow-hidden rounded-xl border border-amber-200/60 bg-gradient-to-br from-amber-100/60 via-orange-50/40 to-yellow-100/50 p-4 text-left shadow-md transition hover:shadow-lg dark:border-amber-900/40 dark:bg-gradient-to-br dark:from-amber-950/40 dark:via-slate-900/60 dark:to-yellow-950/40"
                >
                    <div class="flex items-center justify-between">
                        <p
                            class="text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400"
                        >
                            Total Registered Items
                        </p>
                        <span class="text-sky-600 dark:text-sky-400">
                            <i class="fa-solid fa-boxes-stacked text-lg"></i>
                        </span>
                    </div>
                    <p
                        class="mt-3 text-2xl font-semibold text-slate-900 dark:text-slate-50"
                    >
                        {{ metrics.items ?? 0 }}
                    </p>
                    <Link
                        :href="route('items.index')"
                        class="mt-2 inline-flex items-center text-xs font-medium text-sky-600 hover:text-sky-700 dark:text-sky-400 dark:hover:text-sky-300"
                    >
                        View items
                    </Link>
                </div>

                <Link
                    :href="route('items.index')"
                    class="flex flex-col justify-between overflow-hidden rounded-xl border border-green-200/60 bg-gradient-to-br from-green-100/60 via-emerald-50/40 to-teal-100/50 p-4 text-left shadow-md transition hover:shadow-lg dark:border-green-900/40 dark:bg-gradient-to-br dark:from-green-950/40 dark:via-slate-900/60 dark:to-teal-950/40"
                >
                    <div class="flex items-center justify-between">
                        <p
                            class="text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400"
                        >
                            In Stock Items
                        </p>
                        <span class="text-green-600 dark:text-green-400">
                            <i class="fa-solid fa-warehouse text-lg"></i>
                        </span>
                    </div>
                    <p
                        class="mt-3 text-2xl font-semibold text-slate-900 dark:text-slate-50"
                    >
                        {{ metrics.in_stock ?? 0 }}
                    </p>
                    <p class="mt-2 inline-flex items-center text-xs font-medium text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300">
                        View items
                    </p>
                </Link>

                <div
                    class="flex flex-col justify-between overflow-hidden rounded-xl border border-purple-200/60 bg-gradient-to-br from-purple-100/60 via-pink-50/40 to-violet-100/50 p-4 text-left shadow-md transition hover:shadow-lg dark:border-purple-900/40 dark:bg-gradient-to-br dark:from-purple-950/40 dark:via-slate-900/60 dark:to-violet-950/40"
                >
                    <div class="flex items-center justify-between">
                        <p
                            class="text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400"
                        >
                            Total Owners
                        </p>
                        <span class="text-sky-600 dark:text-sky-400">
                            <i class="fa-solid fa-diagram-project text-lg"></i>
                        </span>
                    </div>
                    <p
                        class="mt-3 text-2xl font-semibold text-slate-900 dark:text-slate-50"
                    >
                        {{ metrics.projects ?? 0 }}
                    </p>
                    <Link
                        :href="route('projects.index')"
                        class="mt-2 inline-flex items-center text-xs font-medium text-sky-600 hover:text-sky-700 dark:text-sky-400 dark:hover:text-sky-300"
                    >
                        View projects
                    </Link>
                </div>
            </div>

            <!-- Main dashboard content -->
            <div class="grid gap-6" :class="userRole === 'project_manager' ? 'lg:grid-cols-2' : 'lg:grid-cols-3'">
                <div class="space-y-4" :class="userRole === 'project_manager' ? 'lg:col-span-1' : 'lg:col-span-2'">
                    <!-- Recent activity (big card) - Only for super admin -->
                    <section
                        v-if="userRole === 'super_admin'"
                        class="overflow-hidden rounded-xl border border-sky-200/50 bg-gradient-to-b from-sky-50/80 via-white to-blue-50/60 p-4 shadow-md dark:border-sky-900/30 dark:bg-gradient-to-b dark:from-sky-950/30 dark:via-slate-900 dark:to-blue-950/20"
                    >
                        <div class="mb-3 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span
                                    class="flex h-7 w-7 items-center justify-center rounded-full bg-sky-100 text-sky-600 dark:bg-sky-900/40 dark:text-sky-300"
                                >
                                    <i
                                        class="fa-solid fa-clock-rotate-left text-xs"
                                    ></i>
                                </span>
                                <h2
                                    class="text-sm font-semibold text-slate-900 dark:text-slate-50"
                                >
                                    Recent activity
                                </h2>
                            </div>
                            <Link
                                :href="route('activities.index')"
                                class="text-xs font-medium text-sky-600 hover:text-sky-500 dark:text-sky-400 dark:hover:text-sky-300"
                            >
                                View all
                            </Link>
                        </div>
                        <ul
                            class="divide-y divide-slate-100 text-sm dark:divide-slate-800"
                        >
                            <li
                                v-for="activity in recentActivities"
                                :key="activity.id"
                                class="py-2"
                            >
                                <Link
                                    :href="route('activities.show', activity.id)"
                                    class="flex items-start gap-3 rounded-lg p-2 transition hover:bg-slate-50 dark:hover:bg-slate-800/50"
                                >
                                    <div
                                        :class="[
                                            'mt-1 flex h-7 w-7 items-center justify-center rounded-full text-slate-500 dark:text-slate-300',
                                            activity.action.includes('delete') || activity.action.includes('removed')
                                                ? 'bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400'
                                                : 'bg-slate-100 dark:bg-slate-800'
                                        ]"
                                    >
                                        <i
                                            :class="[
                                                'text-[10px]',
                                                activity.action.includes('delete') || activity.action.includes('removed')
                                                    ? 'fa-solid fa-trash'
                                                    : 'fa-solid fa-circle-dot'
                                            ]"
                                        ></i>
                                    </div>
                                    <div
                                        class="flex flex-1 justify-between gap-3"
                                    >
                                        <div>
                                            <p
                                                class="text-xs font-medium text-slate-500"
                                            >
                                                {{
                                                    formatDate(
                                                        activity.created_at
                                                    )
                                                }}
                                            </p>
                                            <p
                                                :class="[
                                                    'mt-0.5',
                                                    activity.action.includes('delete') || activity.action.includes('removed')
                                                        ? 'text-red-700 dark:text-red-300'
                                                        : 'text-slate-800 dark:text-slate-100'
                                                ]"
                                            >
                                                {{ activity.action }}
                                                <span
                                                    v-if="activity.user"
                                                    class="text-xs text-slate-500"
                                                >
                                                    · by
                                                    {{ activity.user.name }}
                                                </span>
                                            </p>
                                        </div>
                                        <i class="fa-solid fa-chevron-right text-xs text-slate-400 mt-2"></i>
                                    </div>
                                </Link>
                            </li>
                            <li
                                v-if="
                                    !recentActivities ||
                                    recentActivities.length === 0
                                "
                                class="py-4 text-sm text-slate-500"
                            >
                                No recent activity yet.
                            </li>
                        </ul>
                    </section>

                    <div class="grid gap-4" :class="userRole === 'project_manager' ? 'md:grid-cols-1' : 'md:grid-cols-2'">
                        <!-- Project manager-only deadlines card -->
                        <section
                            v-if="userRole === 'project_manager'"
                            class="overflow-hidden rounded-xl border border-indigo-200/50 bg-gradient-to-br from-indigo-50/80 via-white to-purple-50/60 p-4 shadow-md dark:border-indigo-900/30 dark:bg-gradient-to-br dark:from-indigo-950/30 dark:via-slate-900 dark:to-purple-950/20"
                        >
                            <div class="mb-3 flex items-center justify-between">
                                <h2
                                    class="text-sm font-semibold text-slate-900 dark:text-slate-50"
                                >
                                    My projects deadlines
                                </h2>
                            </div>
                            <ul
                                class="space-y-2 text-sm text-slate-700 dark:text-slate-200"
                            >
                                <li
                                    v-for="project in projectDeadlines"
                                    :key="project.id"
                                    class="flex items-start justify-between gap-3"
                                >
                                    <div>
                                        <p
                                            class="font-medium text-slate-900 dark:text-slate-50"
                                        >
                                            {{ project.name }}
                                        </p>
                                        <p class="text-xs text-slate-500">
                                            {{ project.code }}
                                        </p>
                                    </div>
                                    <div class="text-right text-xs">
                                        <p>
                                            End:
                                            <span v-if="project.end_date">
                                                {{
                                                    formatDate(project.end_date)
                                                }}
                                            </span>
                                            <span v-else>-</span>
                                        </p>
                                        <p
                                            v-if="
                                                project.days_to_deadline !==
                                                null
                                            "
                                            :class="{
                                                'text-emerald-600':
                                                    project.days_to_deadline >
                                                    14,
                                                'text-amber-600':
                                                    project.days_to_deadline <=
                                                        14 &&
                                                    project.days_to_deadline >=
                                                        0,
                                                'text-rose-600':
                                                    project.days_to_deadline <
                                                    0,
                                            }"
                                        >
                                            <span
                                                v-if="
                                                    project.days_to_deadline > 0
                                                "
                                            >
                                                {{
                                                    project.days_to_deadline
                                                }}
                                                days left
                                            </span>
                                            <span
                                                v-else-if="
                                                    project.days_to_deadline ===
                                                    0
                                                "
                                            >
                                                Deadline today
                                            </span>
                                            <span v-else>
                                                {{
                                                    Math.abs(
                                                        project.days_to_deadline
                                                    )
                                                }}
                                                days overdue
                                            </span>
                                        </p>
                                        <p v-else class="text-slate-400">
                                            No end date
                                        </p>
                                    </div>
                                </li>
                                <li
                                    v-if="
                                        (!projectDeadlines ||
                                            projectDeadlines.length === 0) &&
                                        userRole === 'project_manager'
                                    "
                                    class="py-2 text-sm text-slate-500"
                                >
                                    No projects assigned yet.
                                </li>
                            </ul>
                        </section>

                        <!-- Recent employees card - Only for super admin -->
                        <section
                            v-if="userRole === 'super_admin'"
                            class="overflow-hidden rounded-xl border border-emerald-200/50 bg-gradient-to-b from-emerald-50/80 via-white to-green-50/60 p-4 shadow-md dark:border-emerald-900/30 dark:bg-gradient-to-b dark:from-emerald-950/30 dark:via-slate-900 dark:to-green-950/20"
                        >
                            <div class="mb-3 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-300"
                                    >
                                        <i
                                            class="fa-solid fa-user-tie text-xs"
                                        ></i>
                                    </span>
                                    <h2
                                        class="text-sm font-semibold text-slate-900 dark:text-slate-50"
                                    >
                                        Recent employees
                                    </h2>
                                </div>
                                <Link
                                    :href="route('employees.index')"
                                    class="text-xs font-medium text-emerald-600 hover:text-emerald-500 dark:text-emerald-400 dark:hover:text-emerald-300"
                                >
                                    View all
                                </Link>
                            </div>
                            <ul
                                class="divide-y divide-emerald-100 text-sm dark:divide-slate-800"
                            >
                                <li
                                    v-for="employee in recentEmployees"
                                    :key="employee.id"
                                    class="flex items-center justify-between gap-3 py-2.5"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full bg-gradient-to-br from-emerald-200 to-emerald-300 text-xs font-medium text-emerald-700 shadow-sm dark:from-emerald-900/40 dark:to-emerald-800/40 dark:text-emerald-300"
                                        >
                                            <img
                                                v-if="
                                                    employee.image_path
                                                "
                                                :src="
                                                    resolveImage(
                                                        employee.image_path
                                                    )
                                                "
                                                alt="Employee photo"
                                                class="h-10 w-10 rounded-full object-cover"
                                                @error="handleImageError"
                                            />
                                            <span
                                                :style="employee.image_path ? 'display: none' : 'display: flex'"
                                                class="h-10 w-10 items-center justify-center"
                                            >
                                                {{ employee.name.charAt(0) }}
                                            </span>
                                        </div>
                                        <div>
                                            <p
                                                class="text-sm font-medium text-slate-900 dark:text-slate-50"
                                            >
                                                {{ employee.name }}
                                            </p>
                                        </div>
                                    </div>
                                    <Link
                                        :href="
                                            route('employees.show', employee.id)
                                        "
                                        class="inline-flex items-center gap-1 rounded-md bg-emerald-500/10 px-2 py-1 text-xs font-medium text-emerald-600 transition hover:bg-emerald-500/20 dark:bg-emerald-900/30 dark:text-emerald-300 dark:hover:bg-emerald-900/50"
                                    >
                                        <i
                                            class="fa-solid fa-arrow-right text-[10px]"
                                        ></i>
                                        <span>Details</span>
                                    </Link>
                                </li>
                                <li
                                    v-if="
                                        !recentEmployees ||
                                        recentEmployees.length === 0
                                    "
                                    class="py-3 text-center text-sm text-slate-500"
                                >
                                    No employees yet.
                                </li>
                            </ul>
                        </section>

                        <!-- Recent items card - Only for super admin -->
                        <section
                            v-if="userRole === 'super_admin'"
                            class="overflow-hidden rounded-xl border border-amber-200/50 bg-gradient-to-b from-amber-50/80 via-white to-orange-50/60 p-4 shadow-md dark:border-amber-900/30 dark:bg-gradient-to-b dark:from-amber-950/30 dark:via-slate-900 dark:to-orange-950/20"
                        >
                            <div class="mb-3 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="flex h-7 w-7 items-center justify-center rounded-full bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-300"
                                    >
                                        <i class="fa-solid fa-box text-xs"></i>
                                    </span>
                                    <h2
                                        class="text-sm font-semibold text-slate-900 dark:text-slate-50"
                                    >
                                        Recent items
                                    </h2>
                                </div>
                                <Link
                                    :href="route('items.index')"
                                    class="text-xs font-medium text-amber-600 hover:text-amber-500 dark:text-amber-400 dark:hover:text-amber-300"
                                >
                                    View all
                                </Link>
                            </div>
                            <ul
                                class="divide-y divide-amber-100 text-sm dark:divide-slate-800"
                            >
                                <li
                                    v-for="item in recentItems"
                                    :key="item.id"
                                    class="flex items-center justify-between gap-3 py-2.5"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-md bg-gradient-to-br from-amber-200 to-amber-300 text-xs font-medium text-amber-700 shadow-sm dark:from-amber-900/40 dark:to-amber-800/40 dark:text-amber-300"
                                        >
                                            <img
                                                v-if="item.image_path"
                                                :src="
                                                    resolveImage(
                                                        item.image_path
                                                    )
                                                "
                                                alt="Item image"
                                                class="h-10 w-10 rounded-md object-cover"
                                                @error="handleImageError"
                                            />
                                            <span
                                                :style="item.image_path ? 'display: none' : 'display: flex'"
                                                class="h-10 w-10 items-center justify-center"
                                            >
                                                {{ item.name.charAt(0) }}
                                            </span>
                                        </div>
                                        <div>
                                            <p
                                                class="text-sm font-medium text-slate-900 dark:text-slate-50"
                                            >
                                                {{ item.name }}
                                            </p>
                                            <p
                                                class="text-xs text-slate-500 dark:text-slate-400"
                                            >
                                                Tag:
                                                {{ item.tag_number || "-" }}
                                            </p>
                                        </div>
                                    </div>
                                    <Link
                                        :href="route('items.show', item.id)"
                                        class="inline-flex items-center gap-1 rounded-md bg-amber-500/10 px-2 py-1 text-xs font-medium text-amber-600 transition hover:bg-amber-500/20 dark:bg-amber-900/30 dark:text-amber-300 dark:hover:bg-amber-900/50"
                                    >
                                        <i
                                            class="fa-solid fa-arrow-right text-[10px]"
                                        ></i>
                                        <span>Details</span>
                                    </Link>
                                </li>
                                <li
                                    v-if="
                                        !recentItems || recentItems.length === 0
                                    "
                                    class="py-3 text-center text-sm text-slate-500"
                                >
                                    No items yet.
                                </li>
                            </ul>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
