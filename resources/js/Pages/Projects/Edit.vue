<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    project: Object,
});

const form = useForm({
    code: props.project.code ?? '',
    name: props.project.name ?? '',
    start_date: props.project.start_date ?? '',
    end_date: props.project.end_date ?? '',
    description: props.project.description ?? '',
    logo: null,
});

const showPasswordModal = ref(false);
const adminPassword = ref('');
const passwordError = ref('');
const isVerifyingPassword = ref(false);
const pendingSubmit = ref(false);

const submit = () => {
    pendingSubmit.value = true;
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
            adminPassword.value = '';
            isVerifyingPassword.value = false;
            actualSubmit();
        },
        onError: (errors) => {
            passwordError.value = errors.password || 'Invalid password';
            isVerifyingPassword.value = false;
        }
    });
};

const actualSubmit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'put',
    })).post(route('projects.update', props.project.id), {
        forceFormData: true,
        onFinish: () => {
            form.transform((data) => data);
            pendingSubmit.value = false;
        },
    });
};

const cancelPasswordModal = () => {
    showPasswordModal.value = false;
    adminPassword.value = '';
    passwordError.value = '';
    isVerifyingPassword.value = false;
    pendingSubmit.value = false;
};
</script>

<template>
    <Head :title="`Edit ${project.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Management
                    </p>
                    <h2 class="mt-1 text-3xl font-bold text-slate-900 dark:text-slate-50">
                        <i class="fa-solid fa-pen-to-square mr-3 text-sky-600 dark:text-sky-400"></i>
                        Edit Project
                    </h2>
                </div>
                <Link
                    :href="route('projects.show', project.id)"
                    class="inline-flex items-center gap-2 rounded-lg bg-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 shadow-md transition hover:shadow-lg dark:bg-slate-700 dark:text-slate-200"
                >
                    <i class="fa-solid fa-arrow-left"></i>
                    Back to project
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-xl border border-slate-200/50 bg-gradient-to-br from-white to-slate-50/50 p-6 shadow-md dark:border-slate-700/50 dark:from-slate-900 dark:to-slate-800/50">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid gap-6 md:grid-cols-1">
                            <div>
                                <label class="block text-sm font-semibold text-slate-900 dark:text-slate-50">
                                    <i class="fa-solid fa-heading mr-2 text-sky-600 dark:text-sky-400"></i>
                                    Name
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
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-semibold text-slate-900 dark:text-slate-50">
                                    <i class="fa-solid fa-calendar-check mr-2 text-emerald-600 dark:text-emerald-400"></i>
                                    Start date
                                </label>
                                <input
                                    v-model="form.start_date"
                                    type="date"
                                    class="mt-2 block w-full rounded-lg border-2 border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 transition-all focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-50 dark:focus:border-sky-400"
                                />
                                <p v-if="form.errors.start_date" class="mt-2 text-xs text-rose-600 dark:text-rose-400">
                                    <i class="fa-solid fa-circle-exclamation mr-1"></i>
                                    {{ form.errors.start_date }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-900 dark:text-slate-50">
                                    <i class="fa-solid fa-calendar-xmark mr-2 text-amber-600 dark:text-amber-400"></i>
                                    End date
                                </label>
                                <input
                                    v-model="form.end_date"
                                    type="date"
                                    class="mt-2 block w-full rounded-lg border-2 border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 transition-all focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-50 dark:focus:border-sky-400"
                                />
                                <p v-if="form.errors.end_date" class="mt-2 text-xs text-rose-600 dark:text-rose-400">
                                    <i class="fa-solid fa-circle-exclamation mr-1"></i>
                                    {{ form.errors.end_date }}
                                </p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-900 dark:text-slate-50">
                                <i class="fa-solid fa-file-lines mr-2 text-slate-600 dark:text-slate-400"></i>
                                Description
                            </label>
                            <textarea
                                v-model="form.description"
                                rows="4"
                                class="mt-2 block w-full rounded-lg border-2 border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 transition-all focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-50 dark:focus:border-sky-400"
                            />
                            <p v-if="form.errors.description" class="mt-2 text-xs text-rose-600 dark:text-rose-400">
                                <i class="fa-solid fa-circle-exclamation mr-1"></i>
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-900 dark:text-slate-50">
                                <i class="fa-solid fa-image mr-2 text-amber-600 dark:text-amber-400"></i>
                                Project Logo
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
                                    @change="form.logo = $event.target.files[0]"
                                />
                            </label>
                            <p v-if="form.errors.logo" class="mt-2 text-xs text-rose-600 dark:text-rose-400">
                                <i class="fa-solid fa-circle-exclamation mr-1"></i>
                                {{ form.errors.logo }}
                            </p>
                            <p
                                v-if="project.logo_path"
                                class="mt-2 text-xs text-slate-500 dark:text-slate-400"
                            >
                                <i class="fa-solid fa-check-circle mr-1 text-emerald-600 dark:text-emerald-400"></i>
                                Current logo: {{ project.logo_path }}
                            </p>
                        </div>

                        <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-6 dark:border-slate-700">
                            <Link
                                :href="route('projects.show', project.id)"
                                class="inline-flex items-center gap-2 rounded-lg bg-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600"
                            >
                                <i class="fa-solid fa-xmark"></i>
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-amber-600 to-orange-600 px-6 py-2.5 text-sm font-medium text-white shadow-md transition hover:shadow-lg disabled:opacity-50"
                                :disabled="form.processing || pendingSubmit"
                            >
                                <i class="fa-solid fa-lock mr-1"></i>
                                <i class="fa-solid fa-floppy-disk"></i>
                                {{ pendingSubmit ? 'Verifying...' : 'Update project' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Admin Password Verification Modal -->
        <div v-if="showPasswordModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 dark:bg-black/70">
            <div class="w-full max-w-md space-y-4 rounded-xl bg-white shadow-xl dark:bg-slate-900">
                <div class="border-b border-slate-200 bg-gradient-to-r from-amber-50 to-orange-50 px-6 py-4 dark:border-slate-700 dark:from-amber-950/50 dark:to-orange-950/50">
                    <h3 class="text-lg font-semibold text-amber-900 dark:text-amber-100">
                        <i class="fa-solid fa-lock mr-2 text-amber-600 dark:text-amber-400"></i>
                        Password Required
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4 dark:bg-amber-950/20 dark:border-amber-800">
                        <p class="text-sm text-amber-800 dark:text-amber-200">
                            <i class="fa-solid fa-info-circle mr-1"></i>
                            Project editing requires password verification for security.
                        </p>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Your Password *
                            </label>
                            <input
                                v-model="adminPassword"
                                type="password"
                                placeholder="Enter your password"
                                class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-50"
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
                        @click="cancelPasswordModal"
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
                        {{ isVerifyingPassword ? 'Verifying...' : 'Verify & Update' }}
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
