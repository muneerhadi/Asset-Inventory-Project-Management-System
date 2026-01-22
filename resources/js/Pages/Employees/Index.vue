<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    employees: Object, 
    stats: Object,
    filters: Object,
    availableItems: {
        type: Array,
        default: () => []
    },
});

const search = ref(props.filters?.search ?? '');
const selectedEmployeeId = ref(null);
const selectedItemIds = ref([]);
const showAssignModal = ref(false);
const showDeleteModal = ref(false);
const employeeToDelete = ref(null);
const showImportModal = ref(false);
const importFile = ref(null);
const isImporting = ref(false);
const selectedEmployees = ref([]);
const showBulkDeleteModal = ref(false);
const showCheckboxes = ref(false);
const itemSearch = ref('');

// Reset selections when hiding checkboxes
watch(showCheckboxes, (newVal) => {
    if (!newVal) {
        selectedEmployees.value = [];
    }
});

// Live search functionality
watch(search, (newValue) => {
    router.get(
        route('employees.index'),
        { search: newValue || undefined },
        { preserveState: true, replace: true },
    );
}, { debounce: 300 });

const clearSearch = () => {
    search.value = '';
    router.get(route('employees.index'), {}, { preserveState: true, replace: true });
};

const openAssignModal = (employeeId) => {
    selectedEmployeeId.value = employeeId;
    selectedItemIds.value = [];
    itemSearch.value = '';
    showAssignModal.value = true;
};

const closeAssignModal = () => {
    showAssignModal.value = false;
    selectedEmployeeId.value = null;
    selectedItemIds.value = [];
    itemSearch.value = '';
};

const assignItem = () => {
    if (!selectedEmployeeId.value || selectedItemIds.value.length === 0) return;
    router.post(
        route('employees.assign-item', selectedEmployeeId.value),
        { item_ids: selectedItemIds.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                closeAssignModal();
            },
        },
    );
};

const deleteEmployee = (employee) => {
    employeeToDelete.value = employee;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (employeeToDelete.value) {
        router.delete(route('employees.destroy', employeeToDelete.value.id), { 
            preserveScroll: true,
            onSuccess: () => {
                showDeleteModal.value = false;
                employeeToDelete.value = null;
            }
        });
    }
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    employeeToDelete.value = null;
};

const openImportModal = () => {
    showImportModal.value = true;
};

const closeImportModal = () => {
    showImportModal.value = false;
    importFile.value = null;
};

const handleFileChange = (event) => {
    importFile.value = event.target.files[0];
};

const importEmployees = () => {
    if (!importFile.value) return;
    
    isImporting.value = true;
    const formData = new FormData();
    formData.append('file', importFile.value);
    
    router.post(route('employees.import'), formData, {
        preserveScroll: true,
        onSuccess: () => {
            closeImportModal();
            isImporting.value = false;
        },
        onError: () => {
            isImporting.value = false;
        }
    });
};

const toggleEmployeeSelection = (employeeId) => {
    const index = selectedEmployees.value.indexOf(employeeId);
    if (index > -1) {
        selectedEmployees.value.splice(index, 1);
    } else {
        selectedEmployees.value.push(employeeId);
    }
};

const selectAllEmployees = () => {
    if (selectedEmployees.value.length === employees.data.length) {
        selectedEmployees.value = [];
    } else {
        selectedEmployees.value = employees.data.map(emp => emp.id);
    }
};

const bulkDeleteEmployees = () => {
    if (selectedEmployees.value.length > 0) {
        showBulkDeleteModal.value = true;
    }
};

const confirmBulkDelete = () => {
    router.post(route('employees.bulk-delete'), {
        employee_ids: selectedEmployees.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showBulkDeleteModal.value = false;
            selectedEmployees.value = [];
        }
    });
};

const cancelBulkDelete = () => {
    showBulkDeleteModal.value = false;
};

const filteredItems = computed(() => {
    if (!props.availableItems || !Array.isArray(props.availableItems)) return [];
    if (!itemSearch.value) return props.availableItems;
    return props.availableItems.filter(item => 
        item.tag_number?.toLowerCase().includes(itemSearch.value.toLowerCase())
    );
});
</script>

<template>
    <Head title="Employees" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                        Human Resources
                    </p>
                    <h1 class="text-2xl font-semibold leading-tight text-slate-900 dark:text-slate-50">
                        Employees
                    </h1>
                </div>
                <div class="flex gap-2">
                    <button
                        type="button"
                        @click="openImportModal"
                        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-green-600 to-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-md transition hover:shadow-lg dark:from-green-700 dark:to-emerald-800"
                    >
                        <i class="fa-solid fa-file-import"></i>
                        <span>Import</span>
                    </button>
                    <Link
                        :href="route('employees.create')"
                        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-sky-600 to-blue-600 px-4 py-2 text-sm font-medium text-white shadow-md transition hover:shadow-lg dark:from-sky-700 dark:to-blue-800"
                    >
                        <i class="fa-solid fa-user-plus"></i>
                        <span>New employee</span>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">

                <div class="rounded-xl border border-slate-200/50 bg-white/70 p-4 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70 dark:backdrop-blur">
                    <div class="flex items-center gap-2">
                        <button
                            v-if="$page.props.auth.user.role === 'super_admin'"
                            type="button"
                            @click="showCheckboxes = !showCheckboxes"
                            class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-gray-600 to-slate-600 px-4 py-2 text-sm font-medium text-white shadow-md transition hover:shadow-lg"
                        >
                            <i class="fa-solid fa-check-square"></i>
                            <span>{{ showCheckboxes ? 'Hide Selection' : 'Bulk Delete' }}</span>
                        </button>
                        <button
                            v-if="selectedEmployees.length > 0 && showCheckboxes && $page.props.auth.user.role === 'super_admin'"
                            type="button"
                            @click="bulkDeleteEmployees"
                            class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-red-600 to-rose-600 px-4 py-2 text-sm font-medium text-white shadow-md transition hover:shadow-lg"
                        >
                            <i class="fa-solid fa-trash"></i>
                            <span>Delete Selected ({{ selectedEmployees.length }})</span>
                        </button>
                        <div class="flex-1 relative">
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search by name, position, location, email..."
                                class="block w-full rounded-lg border border-slate-200 bg-white px-4 py-2 pr-10 text-sm text-slate-900 placeholder-slate-500 transition focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-50 dark:placeholder-slate-400 dark:focus:border-pink-400"
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
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-slate-200/50 bg-white/70 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-slate-100 dark:border-slate-700 dark:from-slate-900 dark:to-slate-800">
                                    <th v-if="showCheckboxes && $page.props.auth.user.role === 'super_admin'" class="px-4 py-3 text-left cursor-pointer" @click="selectAllEmployees">
                                        <input
                                            type="checkbox"
                                            :checked="selectedEmployees.length === employees.data.length && employees.data.length > 0"
                                            @change="selectAllEmployees"
                                            class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700"
                                        />
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">#</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">Name</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">Position</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">Location</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-900 dark:text-slate-50">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                <tr
                                    v-for="(employee, index) in employees.data"
                                    :key="employee.id"
                                    class="bg-white transition hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50"
                                >
                                    <td v-if="showCheckboxes && $page.props.auth.user.role === 'super_admin'" class="px-4 py-3">
                                        <input
                                            type="checkbox"
                                            :checked="selectedEmployees.includes(employee.id)"
                                            @change="toggleEmployeeSelection(employee.id)"
                                            class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700"
                                        />
                                    </td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ (employees.current_page - 1) * employees.per_page + index + 1 }}</td>
                                    <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-50">{{ employee.name }}</td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ employee.position || '-' }}</td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ employee.location || '-' }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <Link
                                                :href="route('employees.show', employee.id)"
                                                class="inline-flex items-center gap-1 rounded-md bg-sky-100 px-2 py-1 text-xs font-medium text-sky-700 transition hover:bg-sky-200 dark:bg-sky-900/40 dark:text-sky-300 dark:hover:bg-sky-900/60"
                                            >
                                                <i class="fa-solid fa-eye text-xs"></i>
                                                <span>View</span>
                                            </Link>
                                            <button
                                                type="button"
                                                @click="openAssignModal(employee.id)"
                                                class="inline-flex items-center gap-1 rounded-md bg-emerald-100 px-2 py-1 text-xs font-medium text-emerald-700 transition hover:bg-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-300 dark:hover:bg-emerald-900/60"
                                            >
                                                <i class="fa-solid fa-link text-xs"></i>
                                                <span>Assign Item</span>
                                            </button>
                                            <Link
                                                :href="route('employees.edit', employee.id)"
                                                class="inline-flex items-center gap-1 rounded-md bg-slate-200 px-2 py-1 text-xs font-medium text-slate-700 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600"
                                            >
                                                <i class="fa-solid fa-pen text-xs"></i>
                                                <span>Edit</span>
                                            </Link>
                                            <button
                                                v-if="$page.props.auth.user.role === 'super_admin'"
                                                type="button"
                                                @click="deleteEmployee(employee)"
                                                class="inline-flex items-center gap-1 rounded-md bg-rose-100 px-2 py-1 text-xs font-medium text-rose-700 transition hover:bg-rose-200 dark:bg-rose-900/40 dark:text-rose-300 dark:hover:bg-rose-900/60"
                                            >
                                                <i class="fa-solid fa-trash text-xs"></i>
                                                <span>Delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!employees.data.length">
                                    <td :colspan="showCheckboxes && $page.props.auth.user.role === 'super_admin' ? 6 : 5" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                        <i class="fa-solid fa-inbox text-2xl mb-2 block opacity-50"></i>
                                        No employees found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex items-center justify-between rounded-xl border border-slate-200/50 bg-white/70 p-4 text-xs text-slate-600 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70 dark:text-slate-400">
                    <div>
                        Showing
                        <span class="font-semibold text-slate-900 dark:text-slate-50">{{ employees.from ?? 0 }}</span>
                        to
                        <span class="font-semibold text-slate-900 dark:text-slate-50">{{ employees.to ?? 0 }}</span>
                        of
                        <span class="font-semibold text-slate-900 dark:text-slate-50">{{ employees.total }}</span>
                        results
                    </div>
                    <div class="flex gap-2">
                        <button
                            type="button"
                            class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50 disabled:opacity-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                            :disabled="!employees.prev_page_url"
                            @click="router.get(employees.prev_page_url)"
                        >
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <button
                            type="button"
                            class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50 disabled:opacity-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                            :disabled="!employees.next_page_url"
                            @click="router.get(employees.next_page_url)"
                        >
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
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
                            Search Items by Tag Number
                        </label>
                        <input
                            v-model="itemSearch"
                            type="text"
                            placeholder="Enter tag number to search..."
                            class="block w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm text-slate-900 placeholder-slate-500 transition focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-50 dark:placeholder-slate-400"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-900 dark:text-slate-50 mb-2">
                            Select Items *
                        </label>
                        <div class="max-h-60 overflow-y-auto border border-slate-200 rounded-lg dark:border-slate-700">
                            <div v-for="item in filteredItems" :key="item.id" class="flex items-center p-3 hover:bg-slate-50 dark:hover:bg-slate-800">
                                <input
                                    :id="`item-${item.id}`"
                                    v-model="selectedItemIds"
                                    :value="String(item.id)"
                                    type="checkbox"
                                    class="h-4 w-4 text-sky-600 border-slate-300 rounded focus:ring-sky-500 dark:border-slate-600 dark:bg-slate-700"
                                />
                                <label :for="`item-${item.id}`" class="ml-3 flex-1 text-sm cursor-pointer">
                                    <div class="font-medium text-slate-900 dark:text-slate-50">
                                        {{ item.tag_number || item.id }} - {{ item.name }}
                                    </div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ item.category?.name || 'No category' }} • {{ item.status?.name || 'No status' }}
                                    </div>
                                </label>
                            </div>
                            <div v-if="!filteredItems.length" class="p-4 text-center text-slate-500 dark:text-slate-400">
                                {{ itemSearch ? 'No items found matching your search' : 'No available items to assign' }}
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                            <i class="fa-solid fa-info-circle mr-1"></i>
                            Select multiple items to assign to this employee.
                        </p>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 dark:border-slate-700 dark:bg-slate-800/50">
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600"
                        @click="closeAssignModal"
                    >
                        <i class="fa-solid fa-xmark"></i>
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-sky-600 to-blue-600 px-6 py-2.5 text-sm font-medium text-white shadow-md transition hover:shadow-lg dark:from-sky-700 dark:to-blue-800 disabled:opacity-50"
                        @click="assignItem"
                        :disabled="selectedItemIds.length === 0"
                    >
                        <i class="fa-solid fa-check"></i>
                        Assign
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 dark:bg-black/70">
            <div class="w-full max-w-md space-y-4 rounded-xl bg-white shadow-xl dark:bg-slate-900">
                <div class="border-b border-slate-200 bg-gradient-to-r from-red-50 to-rose-50 px-6 py-4 dark:border-slate-700 dark:from-red-950/50 dark:to-rose-950/50">
                    <h3 class="text-lg font-semibold text-red-900 dark:text-red-100">
                        <i class="fa-solid fa-triangle-exclamation mr-2 text-red-600 dark:text-red-400"></i>
                        Delete Employee
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <p class="text-sm text-slate-700 dark:text-slate-300 mb-4">
                        Are you sure you want to delete this employee? This action cannot be undone.
                    </p>
                    <div v-if="employeeToDelete" class="bg-slate-50 dark:bg-slate-800 rounded-lg p-3">
                        <p class="font-medium text-slate-900 dark:text-slate-50">{{ employeeToDelete.name }}</p>
                        <p class="text-sm text-slate-600 dark:text-slate-400">Position: {{ employeeToDelete.position || 'N/A' }}</p>
                        <p class="text-sm text-slate-600 dark:text-slate-400">Location: {{ employeeToDelete.location || 'N/A' }}</p>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 dark:border-slate-700 dark:bg-slate-800/50">
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600"
                        @click="cancelDelete"
                    >
                        <i class="fa-solid fa-xmark"></i>
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-red-600 to-rose-600 px-6 py-2.5 text-sm font-medium text-white shadow-md transition hover:shadow-lg"
                        @click="confirmDelete"
                    >
                        <i class="fa-solid fa-trash"></i>
                        Delete Employee
                    </button>
                </div>
            </div>
        </div>

        <!-- Import Employees Modal -->
        <div v-if="showImportModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 dark:bg-black/70">
            <div class="w-full max-w-lg space-y-4 rounded-xl bg-white shadow-xl dark:bg-slate-900">
                <div class="border-b border-slate-200 bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4 dark:border-slate-700 dark:from-green-950/50 dark:to-emerald-950/50">
                    <h3 class="text-lg font-semibold text-green-900 dark:text-green-100">
                        <i class="fa-solid fa-file-import mr-2 text-green-600 dark:text-green-400"></i>
                        Import Employees from Excel
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Select Excel File (.xlsx, .csv)
                        </label>
                        <input
                            type="file"
                            accept=".xlsx,.xls,.csv"
                            @change="handleFileChange"
                            class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-green-50 file:text-green-700 hover:file:bg-green-100 dark:file:bg-green-900/20 dark:file:text-green-300"
                        />
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 dark:bg-blue-950/20 dark:border-blue-800">
                        <h4 class="font-medium text-blue-800 dark:text-blue-200 mb-2">
                            <i class="fa-solid fa-info-circle mr-1"></i>
                            Excel File Format
                        </h4>
                        <p class="text-sm text-blue-700 dark:text-blue-300 mb-2">
                            Your Excel file should have the following columns:
                        </p>
                        <div class="text-xs text-blue-600 dark:text-blue-400 font-mono bg-blue-100 dark:bg-blue-900/30 p-2 rounded">
                            name | email | phone | position | location | department | hire_date | salary | project
                        </div>
                        <p class="text-xs text-blue-600 dark:text-blue-400 mt-2">
                            <strong>Required:</strong> name, email<br>
                            <strong>Note:</strong> Duplicate emails will be rejected
                        </p>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 dark:border-slate-700 dark:bg-slate-800/50">
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600"
                        @click="closeImportModal"
                        :disabled="isImporting"
                    >
                        <i class="fa-solid fa-xmark"></i>
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-2.5 text-sm font-medium text-white shadow-md transition hover:shadow-lg disabled:opacity-50"
                        @click="importEmployees"
                        :disabled="!importFile || isImporting"
                    >
                        <i v-if="isImporting" class="fa-solid fa-spinner fa-spin"></i>
                        <i v-else class="fa-solid fa-upload"></i>
                        {{ isImporting ? 'Importing...' : 'Import Employees' }}
                    </button>
                </div>
            </div>
        </div>
        <!-- Bulk Delete Confirmation Modal -->
        <div v-if="showBulkDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 dark:bg-black/70">
            <div class="w-full max-w-md space-y-4 rounded-xl bg-white shadow-xl dark:bg-slate-900">
                <div class="border-b border-slate-200 bg-gradient-to-r from-red-50 to-rose-50 px-6 py-4 dark:border-slate-700 dark:from-red-950/50 dark:to-rose-950/50">
                    <h3 class="text-lg font-semibold text-red-900 dark:text-red-100">
                        <i class="fa-solid fa-triangle-exclamation mr-2 text-red-600 dark:text-red-400"></i>
                        Delete Multiple Employees
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <p class="text-sm text-slate-700 dark:text-slate-300 mb-4">
                        Are you sure you want to delete {{ selectedEmployees.length }} selected employees? This action cannot be undone.
                    </p>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 dark:border-slate-700 dark:bg-slate-800/50">
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600"
                        @click="cancelBulkDelete"
                    >
                        <i class="fa-solid fa-xmark"></i>
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-red-600 to-rose-600 px-6 py-2.5 text-sm font-medium text-white shadow-md transition hover:shadow-lg"
                        @click="confirmBulkDelete"
                    >
                        <i class="fa-solid fa-trash"></i>
                        Delete Employees
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
