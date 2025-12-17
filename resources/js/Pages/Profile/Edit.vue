<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <!-- Header: match dashboard typography/colors -->
        <template #header>
            <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Account settings
                    </p>
                    <h1 class="text-2xl font-semibold leading-tight text-slate-900 dark:text-slate-50">
                        {{ $page.props.auth.user.name }}
                    </h1>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        {{ $page.props.auth.user.email }}
                    </p>
                </div>
                <div class="flex items-center gap-3 rounded-xl border border-sky-200/60 bg-gradient-to-r from-sky-50 via-blue-50 to-cyan-50 px-4 py-2 text-xs shadow-sm dark:border-sky-900/40 dark:bg-gradient-to-r dark:from-sky-950/60 dark:via-slate-900 dark:to-cyan-950/60">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-sky-600 text-white shadow-md dark:bg-sky-500">
                        <i class="fa-solid fa-user-gear text-sm"></i>
                    </span>
                    <div>
                        <p class="font-semibold text-slate-800 dark:text-slate-50">
                            Manage your profile
                        </p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                            Update personal info, password, and account status
                        </p>
                    </div>
                </div>
            </div>
        </template>

        <!-- Body -->
        <div class="py-8">
            <div class="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-0">
                <!-- Top grid: profile + security -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Profile information card (blue like employees metric) -->
                    <section
                        class="overflow-hidden rounded-2xl border border-blue-200/60 bg-gradient-to-br from-blue-50/80 via-white to-sky-50/80 p-5 shadow-md dark:border-blue-900/40 dark:bg-gradient-to-br dark:from-blue-950/40 dark:via-slate-950 dark:to-sky-950/40"
                    >
                        <div class="mb-4 flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-sky-600 text-white shadow-sm dark:bg-sky-500">
                                <i class="fa-solid fa-id-card text-sm"></i>
                            </span>
                            <div>
                                <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-50">
                                    Profile information
                                </h2>
                                <p class="text-xs text-slate-600 dark:text-slate-400">
                                    Basic details used across the dashboard
                                </p>
                            </div>
                        </div>

                        <div class="mt-3 rounded-xl bg-white p-4 shadow-sm dark:bg-slate-900 dark:border dark:border-slate-700">
                            <!-- Ensure profile form inputs use black text in dark mode -->
                            <UpdateProfileInformationForm
                                :must-verify-email="mustVerifyEmail"
                                :status="status"
                                class="max-w-xl [&_input]:bg-white [&_input]:text-gray-900 [&_textarea]:bg-white [&_textarea]:text-gray-900 dark:[&_input]:bg-slate-100 dark:[&_input]:text-black dark:[&_textarea]:bg-slate-100 dark:[&_textarea]:text-black"
                            />
                        </div>
                    </section>

                    <!-- Password card (purple like projects metric) -->
                    <section
                        class="overflow-hidden rounded-2xl border border-purple-200/60 bg-gradient-to-br from-purple-50/80 via-white to-pink-50/80 p-5 shadow-md dark:border-purple-900/40 dark:bg-gradient-to-br dark:from-purple-950/40 dark:via-slate-950 dark:to-pink-950/40"
                    >
                        <div class="mb-4 flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-purple-600 text-white shadow-sm dark:bg-purple-500">
                                <i class="fa-solid fa-lock text-sm"></i>
                            </span>
                            <div>
                                <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-50">
                                    Security
                                </h2>
                                <p class="text-xs text-slate-600 dark:text-slate-400">
                                    Change your password regularly to keep things safe
                                </p>
                            </div>
                        </div>

                        <div class="mt-3 rounded-xl bg-white p-4 shadow-sm dark:bg-slate-900 dark:border dark:border-slate-700">
                            <!-- Ensure password form inputs use black text in dark mode -->
                            <UpdatePasswordForm
                                class="max-w-xl [&_input]:bg-white [&_input]:text-gray-900 dark:[&_input]:bg-slate-100 dark:[&_input]:text-black"
                            />
                        </div>
                    </section>
                </div>

                <!-- Danger zone card (rose like distributed items metric) -->
                <section
                    class="overflow-hidden rounded-2xl border border-rose-200/70 bg-gradient-to-br from-rose-50/90 via-white to-orange-50/80 p-5 shadow-md dark:border-rose-900/50 dark:bg-gradient-to-br dark:from-rose-950/50 dark:via-slate-950 dark:to-orange-950/50"
                >
                    <div class="mb-4 flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-rose-600 text-white shadow-sm dark:bg-rose-500">
                            <i class="fa-solid fa-triangle-exclamation text-sm"></i>
                        </span>
                        <div>
                            <h2 class="text-sm font-semibold text-rose-900 dark:text-rose-100">
                                Danger zone
                            </h2>
                            <p class="text-xs text-rose-700/80 dark:text-rose-200/80">
                                Deleting your account is permanent and cannot be undone.
                            </p>
                        </div>
                    </div>

                    <div class="mt-3 rounded-xl bg-white p-4 shadow-sm dark:bg-slate-900 dark:border dark:border-rose-900/70">
                        <DeleteUserForm class="max-w-xl" />
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
