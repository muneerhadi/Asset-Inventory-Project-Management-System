<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    location: '',
    position: '',
    email: '',
    phone: '',
    address: '',
    image: null,
});

const submit = () => {
    form.post(route('employees.store'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="New Employee" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Human Resources
                    </p>
                    <h2 class="mt-1 text-3xl font-bold text-slate-900 dark:text-slate-50">
                        <i class="fa-solid fa-user-plus mr-3 text-sky-600 dark:text-sky-400"></i>
                        Create Employee
                    </h2>
                </div>
                <Link
                    :href="route('employees.index')"
                    class="inline-flex items-center gap-2 rounded-lg bg-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 shadow-md transition hover:shadow-lg dark:bg-slate-700 dark:text-slate-200"
                >
                    <i class="fa-solid fa-arrow-left"></i>
                    Back to employees
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-xl border border-slate-200/50 bg-gradient-to-br from-white to-slate-50/50 p-6 shadow-md dark:border-slate-700/50 dark:from-slate-900 dark:to-slate-800/50">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-900 dark:text-slate-50">
                                <i class="fa-solid fa-user mr-2 text-sky-600 dark:text-sky-400"></i>
                                Full Name
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="mt-2 block w-full rounded-lg border-2 border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 transition-all focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-50 dark:focus:border-sky-400"
                                required
                            />
                            <p v-if="form.errors.name" class="mt-2 text-xs text-rose-600 dark:text-rose-400">
                                <i class="fa-solid fa-circle-exclamation mr-1"></i>
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-semibold text-slate-900 dark:text-slate-50">
                                    <i class="fa-solid fa-map-pin mr-2 text-emerald-600 dark:text-emerald-400"></i>
                                    Location
                                </label>
                                <input
                                    v-model="form.location"
                                    type="text"
                                    class="mt-2 block w-full rounded-lg border-2 border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 transition-all focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-50 dark:focus:border-sky-400"
                                />
                                <p v-if="form.errors.location" class="mt-2 text-xs text-rose-600 dark:text-rose-400">
                                    <i class="fa-solid fa-circle-exclamation mr-1"></i>
                                    {{ form.errors.location }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-900 dark:text-slate-50">
                                    <i class="fa-solid fa-briefcase mr-2 text-amber-600 dark:text-amber-400"></i>
                                    Position
                                </label>
                                <input
                                    v-model="form.position"
                                    type="text"
                                    class="mt-2 block w-full rounded-lg border-2 border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 transition-all focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-50 dark:focus:border-sky-400"
                                />
                                <p v-if="form.errors.position" class="mt-2 text-xs text-rose-600 dark:text-rose-400">
                                    <i class="fa-solid fa-circle-exclamation mr-1"></i>
                                    {{ form.errors.position }}
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-semibold text-slate-900 dark:text-slate-50">
                                    <i class="fa-solid fa-envelope mr-2 text-blue-600 dark:text-blue-400"></i>
                                    Email
                                </label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="mt-2 block w-full rounded-lg border-2 border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 transition-all focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-50 dark:focus:border-sky-400"
                                />
                                <p v-if="form.errors.email" class="mt-2 text-xs text-rose-600 dark:text-rose-400">
                                    <i class="fa-solid fa-circle-exclamation mr-1"></i>
                                    {{ form.errors.email }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-900 dark:text-slate-50">
                                    <i class="fa-solid fa-phone mr-2 text-emerald-600 dark:text-emerald-400"></i>
                                    Phone
                                </label>
                                <input
                                    v-model="form.phone"
                                    type="text"
                                    class="mt-2 block w-full rounded-lg border-2 border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 transition-all focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-50 dark:focus:border-sky-400"
                                />
                                <p v-if="form.errors.phone" class="mt-2 text-xs text-rose-600 dark:text-rose-400">
                                    <i class="fa-solid fa-circle-exclamation mr-1"></i>
                                    {{ form.errors.phone }}
                                </p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-900 dark:text-slate-50">
                                <i class="fa-solid fa-home mr-2 text-slate-600 dark:text-slate-400"></i>
                                Address
                            </label>
                            <textarea
                                v-model="form.address"
                                rows="3"
                                class="mt-2 block w-full rounded-lg border-2 border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 transition-all focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-50 dark:focus:border-sky-400"
                            />
                            <p v-if="form.errors.address" class="mt-2 text-xs text-rose-600 dark:text-rose-400">
                                <i class="fa-solid fa-circle-exclamation mr-1"></i>
                                {{ form.errors.address }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-900 dark:text-slate-50">
                                <i class="fa-solid fa-image mr-2 text-amber-600 dark:text-amber-400"></i>
                                Employee Photo
                            </label>
                            <label
                                class="mt-3 flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-8 transition hover:border-sky-400 hover:bg-sky-50 dark:border-slate-600 dark:bg-slate-800/50 dark:hover:border-sky-500 dark:hover:bg-slate-800"
                            >
                                <i class="fa-solid fa-cloud-arrow-up text-2xl text-slate-400 dark:text-slate-500"></i>
                                <span class="mt-2 text-sm font-medium text-slate-700 dark:text-slate-300">Click to upload or drag & drop</span>
                                <span class="text-xs text-slate-500 dark:text-slate-400">PNG, JPG, GIF (Max 2MB)</span>
                                <input
                                    type="file"
                                    class="hidden"
                                    accept="image/*"
                                    @change="form.image = $event.target.files[0]"
                                />
                            </label>
                            <p v-if="form.errors.image" class="mt-2 text-xs text-rose-600 dark:text-rose-400">
                                <i class="fa-solid fa-circle-exclamation mr-1"></i>
                                {{ form.errors.image }}
                            </p>
                        </div>

                        <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-6 dark:border-slate-700">
                            <Link
                                :href="route('employees.index')"
                                class="inline-flex items-center gap-2 rounded-lg bg-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600"
                            >
                                <i class="fa-solid fa-xmark"></i>
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-sky-600 to-blue-600 px-6 py-2.5 text-sm font-medium text-white shadow-md transition hover:shadow-lg disabled:opacity-50 dark:from-sky-700 dark:to-blue-800"
                                :disabled="form.processing"
                            >
                                <i class="fa-solid fa-floppy-disk"></i>
                                Create employee
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
