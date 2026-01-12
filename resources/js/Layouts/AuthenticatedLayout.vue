<script setup>
import { ref, onMounted, watch } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);

const isDark = ref(false);
const sidebarVisible = ref(false);

const applyTheme = (value) => {
    const root = document.documentElement;
    if (value) {
        root.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        root.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};

onMounted(() => {
    const stored = localStorage.getItem('theme');
    if (stored === 'dark') {
        isDark.value = true;
    } else if (stored === 'light') {
        isDark.value = false;
    } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        isDark.value = true;
    }

    applyTheme(isDark.value);

    // Small entrance animation for the sidebar
    requestAnimationFrame(() => {
        sidebarVisible.value = true;
    });
});

watch(isDark, (value) => {
    applyTheme(value);
});

const toggleTheme = () => {
    isDark.value = !isDark.value;
};
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 text-slate-900 dark:bg-gradient-to-br dark:from-slate-950 dark:via-blue-950/30 dark:to-slate-950 dark:text-slate-100">
        <div class="flex min-h-screen">
            <!-- Sidebar navigation (left) -->
            <aside
                :class="[
                    'hidden md:flex md:flex-col w-64 px-4 py-4 bg-gradient-to-b from-sky-900 via-slate-900 to-slate-950 text-slate-100 shadow-xl transform transition duration-300 ease-out print:hidden sticky top-0 h-screen',
                    sidebarVisible ? 'translate-x-0 opacity-100' : '-translate-x-4 opacity-0',
                ]"
            >
                <div class="flex h-20 items-center justify-center gap-2 mb-6 pt-4">
                    <Link :href="route('dashboard')" class="flex flex-col items-center gap-3">
                        <div class="h-20 w-20">
                            <img src="/storage/logo/union-aid-logo.png" alt="Union Aid Logo" class="h-full w-full object-contain">
                        </div>
                        <span class="text-lg font-bold tracking-wide text-slate-50">
                            UNION AID
                        </span>
                    </Link>
                </div>

                <nav class="mt-6 flex-1 space-y-1 text-sm">
                    <ResponsiveNavLink
                        :href="route('dashboard')"
                        :active="route().current('dashboard')"
                    >
                        Dashboard
                    </ResponsiveNavLink>
                    <ResponsiveNavLink
                        :href="route('items.index')"
                        :active="route().current('items.*')"
                    >
                        Inventory List
                    </ResponsiveNavLink>
                    <ResponsiveNavLink
                        :href="route('employees.index')"
                        :active="route().current('employees.*')"
                    >
                        Employees
                    </ResponsiveNavLink>
                    <ResponsiveNavLink
                        :href="route('projects.index')"
                        :active="route().current('projects.*')"
                    >
                        {{ $page.props.auth.user.role === 'project_manager' ? 'My Projects' : 'Owners' }}
                    </ResponsiveNavLink>
                    <ResponsiveNavLink
                        :href="route('reports.index')"
                        :active="route().current('reports.*')"
                    >
                        Reports
                    </ResponsiveNavLink>
                    <ResponsiveNavLink
                        :href="route('search.items')"
                        :active="route().current('search.*')"
                    >
                        Search
                    </ResponsiveNavLink>
                </nav>
            </aside>

                <!-- Main column -->
            <div class="flex min-h-screen flex-1 flex-col bg-gradient-to-b from-transparent via-blue-50/30 to-indigo-50/20 dark:from-transparent dark:via-slate-900/20 dark:to-slate-900/10">
                <!-- Mobile top bar with menu -->
                <header
                    class="sticky top-0 z-50 flex h-14 items-center justify-between border-b border-slate-200/70 bg-white/80 px-4 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/80 md:px-6 print:hidden"
                >
                    <div class="flex items-center gap-2 md:hidden">
                        <button
                            type="button"
                            @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="inline-flex items-center justify-center rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-slate-50"
                        >
                            <svg
                                class="h-6 w-6"
                                stroke="currentColor"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    :class="{
                                        hidden: showingNavigationDropdown,
                                        'inline-flex': !showingNavigationDropdown,
                                    }"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                                <path
                                    :class="{
                                        hidden: !showingNavigationDropdown,
                                        'inline-flex': showingNavigationDropdown,
                                    }"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                        <Link :href="route('dashboard')" class="flex items-center gap-2">
                            <ApplicationLogo class="h-7 w-auto fill-current text-slate-800 dark:text-slate-100" />
                            <span class="text-sm font-semibold tracking-wide text-slate-800 dark:text-slate-100">
                                UNION AID
                            </span>
                        </Link>
                    </div>

                    <div class="flex flex-1 items-center justify-end gap-4">
                        <!-- Theme toggle -->
                        <button
                            type="button"
                            @click="toggleTheme"
                            class="flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-slate-300 hover:text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-slate-500 dark:hover:text-white"
                            :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
                        >
                            <svg
                                v-if="!isDark"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <circle cx="12" cy="12" r="4" />
                                <path d="M12 2v2m0 16v2m10-10h-2M4 12H2m16.95 6.95-1.41-1.41M6.46 6.46 5.05 5.05m12.9 0-1.41 1.41M6.46 17.54l-1.41 1.41" />
                            </svg>
                            <svg
                                v-else
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"
                                />
                            </svg>
                        </button>

                        <!-- User profile dropdown (top-right) -->
                        <div class="relative">
                            <Dropdown align="right" width="56">
                                <template #trigger>
                                    <span class="inline-flex rounded-full">
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-2 rounded-full border border-transparent bg-white px-2.5 py-1.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700"
                                        >
                                            <span class="inline-flex h-8 w-8 items-center justify-center overflow-hidden rounded-full bg-slate-200 text-xs font-medium text-slate-700 dark:bg-slate-700 dark:text-slate-100">
                                                <img
                                                    v-if="$page.props.auth.user.profile_photo_path"
                                                    :src="$page.props.auth.user.profile_photo_path.startsWith('/') ? $page.props.auth.user.profile_photo_path : '/' + $page.props.auth.user.profile_photo_path"
                                                    alt="Profile photo"
                                                    class="h-8 w-8 rounded-full object-cover"
                                                />
                                                <span v-else>
                                                    {{ $page.props.auth.user.name.charAt(0) }}
                                                </span>
                                            </span>
                                            <span class="hidden text-sm md:inline">
                                                {{ $page.props.auth.user.name }}
                                            </span>
                                            <svg
                                                class="h-4 w-4 text-slate-400"
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </button>
                                    </span>
                                </template>

                                <template #content>
                                    <div class="px-3 py-2 text-xs text-slate-500">
                                        Signed in as
                                        <div class="truncate font-medium text-slate-800 dark:text-slate-100">
                                            {{ $page.props.auth.user.email }}
                                        </div>
                                    </div>
                                    <DropdownLink :href="route('profile.edit')">
                                        Profile
                                    </DropdownLink>
                                    <DropdownLink
                                        v-if="$page.props.auth.user.role === 'super_admin'"
                                        :href="route('settings.index')"
                                    >
                                        Settings
                                    </DropdownLink>
                                    <DropdownLink
                                        :href="route('logout')"
                                        method="post"
                                        as="button"
                                    >
                                        Log Out
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </header>

                <!-- Mobile navigation drawer -->
                <div
                    v-if="showingNavigationDropdown"
                    class="sticky top-14 z-40 border-b border-slate-200 bg-white/95 px-4 py-3 text-sm shadow-sm dark:border-slate-800 dark:bg-slate-900/95 md:hidden"
                >
                    <div class="space-y-1">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            Dashboard
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('items.index')"
                            :active="route().current('items.*')"
                        >
                            Inventory List
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('employees.index')"
                            :active="route().current('employees.*')"
                        >
                            Employees
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('projects.index')"
                            :active="route().current('projects.*')"
                        >
                            {{ $page.props.auth.user.role === 'project_manager' ? 'My Projects' : 'Owners' }}
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('reports.index')"
                            :active="route().current('reports.*')"
                        >
                            Reports
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('search.items')"
                            :active="route().current('search.*')"
                        >
                            Search
                        </ResponsiveNavLink>
                    </div>

                    <div class="mt-3 border-t border-slate-200 pt-3 dark:border-slate-800">
                        <div class="text-xs font-medium text-slate-700 dark:text-slate-200">
                            {{ $page.props.auth.user.name }}
                        </div>
                        <div class="text-xs text-slate-500">
                            {{ $page.props.auth.user.email }}
                        </div>
                        <div class="mt-2 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profile
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>

                <!-- Page content -->
                <main class="flex-1 overflow-y-auto py-6 lg:py-8">
                    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div v-if="$slots.header" class="mb-6">
                            <slot name="header" />
                        </div>
                        <slot />
                    </div>
                </main>
            </div>
        </div>
    </div>
</template>
