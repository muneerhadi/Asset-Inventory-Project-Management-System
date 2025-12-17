<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { resolveImage } from '@/utils/imageResolver';

const props = defineProps({
    employee: Object,
    availableItems: Array,
});

const selectedItemId = ref(null);
const showAssignModal = ref(false);

const deleteEmployee = () => {
    if (confirm('Are you sure you want to delete this employee?')) {
        router.delete(route('employees.destroy', props.employee.id), { preserveScroll: true });
    }
};

const assignItem = () => {
    if (!selectedItemId.value) return;
    router.post(
        route('employees.assign-item', props.employee.id),
        { item_id: selectedItemId.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                selectedItemId.value = null;
                showAssignModal.value = false;
            },
        },
    );
};

const unassignItem = (assignmentId) => {
    if (!confirm('Are you sure you want to unassign this item?')) return;
    router.delete(route('employees.unassign-item', [props.employee.id, assignmentId]), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="employee.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Human Resources
                    </p>
                    <h2 class="mt-1 text-3xl font-bold text-slate-900 dark:text-slate-50">
                        <i class="fa-solid fa-user-circle mr-3 text-sky-600 dark:text-sky-400"></i>
                        {{ employee.name }}
                    </h2>
                </div>
                <div class="flex gap-2">
                    <Link
                        :href="route('employees.edit', employee.id)"
                        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-amber-600 to-orange-600 px-4 py-2.5 text-sm font-medium text-white shadow-md transition hover:shadow-lg dark:from-amber-700 dark:to-orange-700"
                    >
                        <i class="fa-solid fa-pen"></i>
                        Edit
                    </Link>
                    <button
                        type="button"
                        @click="deleteEmployee"
                        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-rose-600 to-red-600 px-4 py-2.5 text-sm font-medium text-white shadow-md transition hover:shadow-lg dark:from-rose-700 dark:to-red-800"
                    >
                        <i class="fa-solid fa-trash"></i>
                        Delete
                    </button>
                    <Link
                        :href="route('employees.index')"
                        class="inline-flex items-center gap-2 rounded-lg bg-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 shadow-md transition hover:shadow-lg dark:bg-slate-700 dark:text-slate-200"
                    >
                        <i class="fa-solid fa-arrow-left"></i>
                        Back
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="grid gap-6 md:grid-cols-3">
                    <!-- Main Content -->
                    <div class="md:col-span-2 space-y-6">
                        <!-- Profile Card -->
                        <div class="overflow-hidden rounded-xl border border-slate-200/50 bg-gradient-to-br from-white to-slate-50/50 p-6 shadow-md dark:border-slate-700/50 dark:from-slate-900 dark:to-slate-800/50">
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-50">
                                <i class="fa-solid fa-id-card mr-2 text-sky-600 dark:text-sky-400"></i>
                                Profile Information
                            </h3>
                            <dl class="mt-6 grid grid-cols-1 gap-x-4 gap-y-4 text-sm md:grid-cols-2">
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">Full Name</dt>
                                    <dd class="mt-2 font-semibold text-slate-900 dark:text-slate-50">{{ employee.name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">Position</dt>
                                    <dd class="mt-2 text-slate-700 dark:text-slate-300">{{ employee.position || '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">Location</dt>
                                    <dd class="mt-2 text-slate-700 dark:text-slate-300">{{ employee.location || '-' }}</dd>
                                </div>
                                
                            </dl>

                            <div class="mt-6 border-t border-slate-200 pt-6 dark:border-slate-700">
                                <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-50">
                                    <i class="fa-solid fa-envelope mr-2 text-blue-600 dark:text-blue-400"></i>
                                    Email
                                </h4>
                                <div class="mt-3 flex items-center gap-3">
                                    <a
                                        v-if="employee.email"
                                        :href="`mailto:${employee.email}`"
                                        class="inline-flex items-center gap-2 rounded-lg bg-blue-100 px-4 py-2 text-blue-700 transition hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-900/50"
                                    >
                                        <i class="fa-solid fa-paper-plane text-sm"></i>
                                        {{ employee.email }}
                                    </a>
                                    <span v-else class="text-slate-500 dark:text-slate-400">-</span>
                                </div>
                            </div>

                            <div class="mt-6 border-t border-slate-200 pt-6 dark:border-slate-700">
                                <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-50">
                                    <i class="fa-solid fa-phone mr-2 text-emerald-600 dark:text-emerald-400"></i>
                                    Phone
                                </h4>
                                <div class="mt-3 flex items-center gap-3">
                                    <a
                                        v-if="employee.phone"
                                        :href="`tel:${employee.phone}`"
                                        class="inline-flex items-center gap-2 rounded-lg bg-emerald-100 px-4 py-2 text-emerald-700 transition hover:bg-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:hover:bg-emerald-900/50"
                                    >
                                        <i class="fa-solid fa-phone-volume text-sm"></i>
                                        {{ employee.phone }}
                                    </a>
                                    <span v-else class="text-slate-500 dark:text-slate-400">-</span>
                                </div>
                            </div>

                            <div class="mt-6 border-t border-slate-200 pt-6 dark:border-slate-700">
                                <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-50">
                                    <i class="fa-solid fa-home mr-2 text-slate-600 dark:text-slate-400"></i>
                                    Address
                                </h4>
                                <p class="mt-3 whitespace-pre-line text-sm text-slate-700 dark:text-slate-300">
                                    {{ employee.address || 'No address provided.' }}
                                </p>
                            </div>
                        </div>


                        <!-- Assigned Items Card -->
                        <div class="overflow-hidden rounded-xl border border-slate-200/50 bg-gradient-to-br from-white to-slate-50/50 p-6 shadow-md dark:border-slate-700/50 dark:from-slate-900 dark:to-slate-800/50">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-50">
                                    <i class="fa-solid fa-link mr-2 text-sky-600 dark:text-sky-400"></i>
                                    Items Assigned
                                </h3>
                                <button
                                    type="button"
                                    @click="showAssignModal = true"
                                    class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-sky-600 to-blue-600 px-3 py-1.5 text-xs font-medium text-white shadow-md transition hover:shadow-lg dark:from-sky-700 dark:to-blue-800"
                                >
                                    <i class="fa-solid fa-plus"></i>
                                    <span>Assign Item</span>
                                </button>
                            </div>
                            <div v-if="employee.item_employee_assignments && employee.item_employee_assignments.length" class="mt-4 space-y-2">
                                <div
                                    v-for="assignment in employee.item_employee_assignments"
                                    :key="assignment.id"
                                    class="flex items-center gap-3 rounded-lg border-2 border-sky-200 bg-sky-50 px-4 py-3 transition hover:border-sky-300 hover:bg-sky-100 dark:border-sky-900/50 dark:bg-sky-950/30 dark:hover:border-sky-700 dark:hover:bg-sky-900/50"
                                >
                                    <Link
                                        :href="route('items.show', assignment.item.id)"
                                        class="flex flex-1 items-center gap-3"
                                    >
                                        <i class="fa-solid fa-box text-sky-600 dark:text-sky-400"></i>
                                        <div class="flex-1">
                                            <p class="font-medium text-slate-900 dark:text-slate-50">{{ assignment.item.tag_number || assignment.item.item_code }} - {{ assignment.item.name }}</p>
                                            <p class="text-xs text-slate-600 dark:text-slate-400">{{ assignment.item.category?.name || 'No category' }} | {{ assignment.item.status?.name || 'No status' }}</p>
                                        </div>
                                        <i class="fa-solid fa-arrow-right text-sky-600 dark:text-sky-400"></i>
                                    </Link>
                                    <button
                                        type="button"
                                        @click="unassignItem(assignment.id)"
                                        class="ml-2 rounded-lg bg-rose-100 px-2 py-1 text-xs font-medium text-rose-700 transition hover:bg-rose-200 dark:bg-rose-900/30 dark:text-rose-300 dark:hover:bg-rose-900/50"
                                    >
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div v-else class="mt-4 rounded-lg bg-slate-50 p-4 text-center text-slate-500 dark:bg-slate-800/50 dark:text-slate-400">
                                <i class="fa-solid fa-inbox text-2xl mb-2 block opacity-50"></i>
                                No items assigned.
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar Photo -->
                    <div class="space-y-6">
                        <div class="overflow-hidden rounded-xl border-2 border-sky-200/70 bg-gradient-to-br from-sky-50 to-cyan-50/50 p-6 shadow-md dark:border-sky-900/50 dark:from-sky-950/30 dark:to-cyan-900/20">
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-50">
                                <i class="fa-solid fa-image mr-2 text-sky-600 dark:text-sky-400"></i>
                                Employee Photo
                            </h3>
                            <div class="mt-4">
                                <div v-if="employee.image_path" class="flex items-center justify-center rounded-lg bg-white p-4 ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-slate-700">
                                    <img :src="resolveImage(employee.image_path)" alt="Employee photo" class="max-h-64 w-auto object-contain" />
                                </div>
                                <div v-else class="flex flex-col items-center justify-center rounded-lg bg-white p-8 text-center ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-slate-700">
                                    <i class="fa-solid fa-user text-4xl text-slate-300 dark:text-slate-600"></i>
                                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">No photo available</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assign Item Modal -->
        <div v-if="showAssignModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 dark:bg-black/70">
            <div class="w-full max-w-md space-y-4 rounded-xl bg-white shadow-xl dark:bg-slate-900">
                <div class="border-b border-slate-200 bg-gradient-to-r from-sky-50 to-blue-50 px-6 py-4 dark:border-slate-700 dark:from-slate-800 dark:to-slate-900">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-50">
                        <i class="fa-solid fa-box mr-2 text-sky-600 dark:text-sky-400"></i>
                        Assign Item to Employee
                    </h3>
                </div>
                <div class="space-y-4 px-6 py-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-900 dark:text-slate-50 mb-2">
                            Select Item *
                        </label>
                        <select
                            v-model="selectedItemId"
                            class="relative w-full appearance-none rounded-lg border-2 border-slate-300 bg-white py-2.5 pl-3 pr-10 text-sm font-medium text-slate-900 shadow-sm transition-all duration-200 focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-500/20 dark:border-slate-500 dark:bg-slate-800 dark:text-slate-50 dark:focus:border-sky-400"
                        >
                            <option :value="null" class="text-slate-500 dark:bg-slate-800 dark:text-slate-400">Select item...</option>
                            <option v-for="item in availableItems" :key="item.id" :value="String(item.id)" class="text-slate-900 dark:bg-slate-800 dark:text-slate-50">
                                {{ item.tag_number || item.id }} - {{ item.name }} ({{ item.category?.name || 'No category' }})
                            </option>
                        </select>
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                            <i class="fa-solid fa-info-circle mr-1"></i>
                            One item can only be assigned to one employee at a time.
                        </p>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 dark:border-slate-700 dark:bg-slate-800/50">
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600"
                        @click="showAssignModal = false; selectedItemId = null"
                    >
                        <i class="fa-solid fa-xmark"></i>
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-sky-600 to-blue-600 px-6 py-2.5 text-sm font-medium text-white shadow-md transition hover:shadow-lg dark:from-sky-700 dark:to-blue-800"
                        @click="assignItem"
                        :disabled="!selectedItemId"
                    >
                        <i class="fa-solid fa-check"></i>
                        Assign
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
