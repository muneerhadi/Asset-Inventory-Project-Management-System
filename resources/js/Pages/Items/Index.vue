<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { formatDate } from '@/utils/dateFormat';

const props = defineProps({
    items: Object,
    stats: Object,
    filters: Object,
});

const search = ref(props.filters?.search ?? '');
const activeStatus = ref(props.filters?.status ?? '');
const showDeleteModal = ref(false);
const itemToDelete = ref(null);
const selectedItems = ref([]);
const showBulkDeleteModal = ref(false);
const showImportModal = ref(false);
const importFile = ref(null);
const isImporting = ref(false);

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success ?? null);
const flashError = computed(() => page.props.flash?.error ?? null);
const importSummary = computed(() => page.props.flash?.import_summary ?? null);

// Live search functionality
watch(search, (newValue) => {
    router.get(
        route('items.index'),
        { search: newValue || undefined, status: activeStatus.value || undefined },
        { preserveState: true, replace: true },
    );
}, { debounce: 300 });

const clearSearch = () => {
    search.value = '';
    router.get(
        route('items.index'),
        { status: activeStatus.value || undefined },
        { preserveState: true, replace: true },
    );
};

const setStatusFilter = (status) => {
    activeStatus.value = status;
    router.get(
        route('items.index'),
        { search: search.value, status: status || undefined },
        { preserveState: true, replace: true },
    );
};

const deleteItem = (item) => {
    itemToDelete.value = item;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (itemToDelete.value) {
        router.delete(route('items.destroy', itemToDelete.value.id), { 
            preserveScroll: true,
            onSuccess: () => {
                showDeleteModal.value = false;
                itemToDelete.value = null;
            }
        });
    }
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    itemToDelete.value = null;
};

const toggleItemSelection = (itemId) => {
    const index = selectedItems.value.indexOf(itemId);
    if (index > -1) {
        selectedItems.value.splice(index, 1);
    } else {
        selectedItems.value.push(itemId);
    }
};

const selectAllItems = () => {
    if (selectedItems.value.length === items.data.length) {
        selectedItems.value = [];
    } else {
        selectedItems.value = items.data.map(item => item.id);
    }
};

const bulkDelete = () => {
    if (selectedItems.value.length > 0) {
        showBulkDeleteModal.value = true;
    }
};

const confirmBulkDelete = () => {
    router.post(route('items.bulk-delete'), {
        item_ids: selectedItems.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showBulkDeleteModal.value = false;
            selectedItems.value = [];
        }
    });
};

const cancelBulkDelete = () => {
    showBulkDeleteModal.value = false;
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

const importItems = () => {
    if (!importFile.value) return;
    
    isImporting.value = true;
    const formData = new FormData();
    formData.append('file', importFile.value);
    
    router.post(route('items.import'), formData, {
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
</script>

<template>
    <Head title="Inventory List" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                        Inventory
                    </p>
                    <h1 class="text-2xl font-semibold leading-tight text-slate-900 dark:text-slate-50">
                        Inventory List
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
                        :href="route('items.create')"
                        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-sky-600 to-blue-600 px-4 py-2 text-sm font-medium text-white shadow-md transition hover:shadow-lg dark:from-sky-700 dark:to-blue-800"
                    >
                        <i class="fa-solid fa-plus"></i>
                        <span>New item</span>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">
                <div
                    v-if="flashSuccess"
                    class="rounded-xl border border-emerald-200/50 bg-emerald-50/70 p-4 text-sm text-emerald-800 shadow-md dark:border-emerald-900/30 dark:bg-emerald-950/20 dark:text-emerald-200"
                >
                    {{ flashSuccess }}
                </div>
                <div
                    v-if="flashError"
                    class="rounded-xl border border-rose-200/50 bg-rose-50/70 p-4 text-sm text-rose-800 shadow-md dark:border-rose-900/30 dark:bg-rose-950/20 dark:text-rose-200"
                >
                    {{ flashError }}
                </div>

                <div
                    v-if="importSummary"
                    class="rounded-xl border border-slate-200/50 bg-white/70 p-4 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70"
                >
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-50">
                            Import Summary
                        </h3>
                        <div class="text-xs text-slate-600 dark:text-slate-400">
                            Imported: <span class="font-semibold text-slate-900 dark:text-slate-50">{{ importSummary.imported ?? 0 }}</span>
                            |
                            Rejected: <span class="font-semibold text-slate-900 dark:text-slate-50">{{ importSummary.skipped_errors ?? 0 }}</span>
                            |
                            Duplicates: <span class="font-semibold text-slate-900 dark:text-slate-50">{{ importSummary.skipped_duplicates ?? 0 }}</span>
                        </div>
                    </div>

                    <div v-if="importSummary.errors && importSummary.errors.length" class="mt-3">
                        <div class="text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">
                            Rejected rows
                        </div>
                        <ul class="mt-2 space-y-1 text-sm text-slate-700 dark:text-slate-300">
                            <li v-for="(e, idx) in importSummary.errors" :key="idx" class="rounded-md bg-rose-50 px-3 py-2 dark:bg-rose-950/20">
                                {{ e }}
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-4">
                    <div
                        class="overflow-hidden rounded-xl border border-amber-200/50 bg-gradient-to-br from-amber-100/60 via-orange-50/40 to-yellow-100/50 p-4 text-left shadow-md dark:border-amber-900/30 dark:bg-gradient-to-br dark:from-amber-950/40 dark:via-slate-900/60 dark:to-yellow-950/40"
                        :class="{ 'ring-2 ring-amber-500/70': !activeStatus }"
                    >
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">
                                Total items
                            </p>
                            <span class="text-amber-600 dark:text-amber-400">
                                <i class="fa-solid fa-box text-lg"></i>
                            </span>
                        </div>
                        <p class="mt-3 text-2xl font-semibold text-slate-900 dark:text-slate-50">
                            {{ stats.total ?? 0 }}
                        </p>
                    </div>
                    <button
                        type="button"
                        class="overflow-hidden rounded-xl border border-emerald-200/50 bg-gradient-to-br from-emerald-100/60 via-green-50/40 to-teal-100/50 p-4 text-left shadow-md transition hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-emerald-400/70 dark:border-emerald-900/30 dark:bg-gradient-to-br dark:from-emerald-950/40 dark:via-slate-900/60 dark:to-teal-950/40"
                        :class="{ 'ring-2 ring-emerald-500/70': activeStatus === 'available' }"
                        @click="setStatusFilter('available')"
                    >
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">
                                Available
                            </p>
                            <span class="text-emerald-600 dark:text-emerald-400">
                                <i class="fa-solid fa-check-circle text-lg"></i>
                            </span>
                        </div>
                        <p class="mt-3 text-2xl font-semibold text-slate-900 dark:text-slate-50">
                            {{ stats.available ?? 0 }}
                        </p>
                    </button>
                    <button
                        type="button"
                        class="overflow-hidden rounded-xl border border-rose-200/50 bg-gradient-to-br from-rose-100/60 via-red-50/40 to-pink-100/50 p-4 text-left shadow-md transition hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-rose-400/70 dark:border-rose-900/30 dark:bg-gradient-to-br dark:from-rose-950/40 dark:via-slate-900/60 dark:to-pink-950/40"
                        :class="{ 'ring-2 ring-rose-500/70': activeStatus === 'damaged' }"
                        @click="setStatusFilter('damaged')"
                    >
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">
                                Damaged
                            </p>
                            <span class="text-rose-600 dark:text-rose-400">
                                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                            </span>
                        </div>
                        <p class="mt-3 text-2xl font-semibold text-slate-900 dark:text-slate-50">
                            {{ stats.damaged ?? 0 }}
                        </p>
                    </button>
                    <button
                        type="button"
                        class="overflow-hidden rounded-xl border border-amber-200/50 bg-gradient-to-br from-yellow-100/60 via-amber-50/40 to-orange-100/50 p-4 text-left shadow-md transition hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-yellow-400/70 dark:border-yellow-900/30 dark:bg-gradient-to-br dark:from-yellow-950/40 dark:via-slate-900/60 dark:to-orange-950/40"
                        :class="{ 'ring-2 ring-yellow-500/70': activeStatus === 'daghma' }"
                        @click="setStatusFilter('daghma')"
                    >
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">
                                Daghma
                            </p>
                            <span class="text-yellow-600 dark:text-yellow-400">
                                <i class="fa-solid fa-warning text-lg"></i>
                            </span>
                        </div>
                        <p class="mt-3 text-2xl font-semibold text-slate-900 dark:text-slate-50">
                            {{ stats.daghma ?? 0 }}
                        </p>
                    </button>
                </div>

                <div class="rounded-xl border border-slate-200/50 bg-white/70 p-4 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70 dark:backdrop-blur">
                    <div class="flex items-center gap-2">
                        <button
                            v-if="selectedItems.length > 0"
                            type="button"
                            @click="bulkDelete"
                            class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-red-600 to-rose-600 px-4 py-2 text-sm font-medium text-white shadow-md transition hover:shadow-lg"
                        >
                            <i class="fa-solid fa-trash"></i>
                            <span>Delete Selected ({{ selectedItems.length }})</span>
                        </button>
                        <div class="flex-1 relative">
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search by name, tag, category, model, location..."
                                class="block w-full rounded-lg border border-slate-200 bg-white px-4 py-2 pr-10 text-sm text-slate-900 placeholder-slate-500 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-50 dark:placeholder-slate-400 dark:focus:border-sky-400"
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
                                    <th class="px-4 py-3 text-left">
                                        <input
                                            type="checkbox"
                                            :checked="selectedItems.length === items.data.length && items.data.length > 0"
                                            @change="selectAllItems"
                                            class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700"
                                        />
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">#</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">Name</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">Model</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">Tag Number</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">Type</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">Owner Name</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">Purchase Date</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">Situation</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-900 dark:text-slate-50">Status</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-900 dark:text-slate-50">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                <tr
                                    v-for="(item, index) in items.data"
                                    :key="item.id"
                                    class="bg-white transition hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50"
                                >
                                    <td class="px-4 py-3">
                                        <input
                                            type="checkbox"
                                            :checked="selectedItems.includes(item.id)"
                                            @change="toggleItemSelection(item.id)"
                                            class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700"
                                        />
                                    </td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ (items.current_page - 1) * items.per_page + index + 1 }}</td>
                                    <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-50">{{ item.name }}</td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ item.model || '-' }}</td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ item.tag_number || '-' }}</td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ item.category?.name || '-' }}</td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ item.project?.name || 'Unassigned' }}</td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ item.purchase_date ? formatDate(item.purchase_date) : '-' }}</td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold whitespace-nowrap"
                                            :class="{
                                                'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300': item.status?.slug === 'active',
                                                'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300': item.status?.slug === 'daghma',
                                                'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300': item.status?.is_damaged,
                                                'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300': !item.status,
                                            }"
                                        >
                                            {{ item.status?.name ?? 'Unknown' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold whitespace-nowrap"
                                            :class="{
                                                'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300': item.item_employee_assignments && item.item_employee_assignments.length > 0,
                                                'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300': !item.item_employee_assignments || item.item_employee_assignments.length === 0,
                                            }"
                                        >
                                            {{ (item.item_employee_assignments && item.item_employee_assignments.length > 0) ? 'In Use' : 'In Stock' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <Link
                                                :href="route('items.show', item.id)"
                                                class="inline-flex items-center gap-1 rounded-md bg-sky-100 px-2 py-1 text-xs font-medium text-sky-700 transition hover:bg-sky-200 dark:bg-sky-900/40 dark:text-sky-300 dark:hover:bg-sky-900/60"
                                            >
                                                <i class="fa-solid fa-eye text-xs"></i>
                                                <span>View</span>
                                            </Link>
                                            <Link
                                                :href="route('items.edit', item.id)"
                                                class="inline-flex items-center gap-1 rounded-md bg-slate-200 px-2 py-1 text-xs font-medium text-slate-700 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600"
                                            >
                                                <i class="fa-solid fa-pen text-xs"></i>
                                                <span>Edit</span>
                                            </Link>
                                            <button
                                                type="button"
                                                @click="deleteItem(item)"
                                                class="inline-flex items-center gap-1 rounded-md bg-rose-100 px-2 py-1 text-xs font-medium text-rose-700 transition hover:bg-rose-200 dark:bg-rose-900/40 dark:text-rose-300 dark:hover:bg-rose-900/60"
                                            >
                                                <i class="fa-solid fa-trash text-xs"></i>
                                                <span>Delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!items.data.length">
                                    <td colspan="10" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                        <i class="fa-solid fa-inbox text-2xl mb-2 block opacity-50"></i>
                                        No items found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex items-center justify-between rounded-xl border border-slate-200/50 bg-white/70 p-4 text-xs text-slate-600 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70 dark:text-slate-400">
                    <div>
                        Showing
                        <span class="font-semibold text-slate-900 dark:text-slate-50">{{ items.from ?? 0 }}</span>
                        to
                        <span class="font-semibold text-slate-900 dark:text-slate-50">{{ items.to ?? 0 }}</span>
                        of
                        <span class="font-semibold text-slate-900 dark:text-slate-50">{{ items.total }}</span>
                        results
                    </div>
                    <div class="flex gap-2">
                        <button
                            type="button"
                            class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50 disabled:opacity-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                            :disabled="!items.prev_page_url"
                            @click="router.get(items.prev_page_url)"
                        >
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <button
                            type="button"
                            class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50 disabled:opacity-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                            :disabled="!items.next_page_url"
                            @click="router.get(items.next_page_url)"
                        >
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 dark:bg-black/70">
            <div class="w-full max-w-md space-y-4 rounded-xl bg-white shadow-xl dark:bg-slate-900">
                <div class="border-b border-slate-200 bg-gradient-to-r from-red-50 to-rose-50 px-6 py-4 dark:border-slate-700 dark:from-red-950/50 dark:to-rose-950/50">
                    <h3 class="text-lg font-semibold text-red-900 dark:text-red-100">
                        <i class="fa-solid fa-triangle-exclamation mr-2 text-red-600 dark:text-red-400"></i>
                        Delete Item
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <p class="text-sm text-slate-700 dark:text-slate-300 mb-4">
                        Are you sure you want to delete this item? This action cannot be undone.
                    </p>
                    <div v-if="itemToDelete" class="bg-slate-50 dark:bg-slate-800 rounded-lg p-3">
                        <p class="font-medium text-slate-900 dark:text-slate-50">{{ itemToDelete.name }}</p>
                        <p class="text-sm text-slate-600 dark:text-slate-400">Tag: {{ itemToDelete.tag_number || 'N/A' }}</p>
                        <p class="text-sm text-slate-600 dark:text-slate-400">Category: {{ itemToDelete.category?.name || 'N/A' }}</p>
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
                        Delete Item
                    </button>
                </div>
            </div>
        </div>

        <!-- Import Items Modal -->
        <div v-if="showImportModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 dark:bg-black/70">
            <div class="w-full max-w-lg space-y-4 rounded-xl bg-white shadow-xl dark:bg-slate-900">
                <div class="border-b border-slate-200 bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4 dark:border-slate-700 dark:from-green-950/50 dark:to-emerald-950/50">
                    <h3 class="text-lg font-semibold text-green-900 dark:text-green-100">
                        <i class="fa-solid fa-file-import mr-2 text-green-600 dark:text-green-400"></i>
                        Import Items from Excel
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
                        <div class="text-xs text-blue-600 dark:text-blue-400 font-mono bg-blue-100 dark:bg-blue-900/30 p-2 rounded overflow-x-auto">
                            Asset Tag NO | Category | Poject | Voucher Number | Item Name | description | Model | Serial Number | Price | Date | Location | Sublocation | EMP-Name | Status | Remarks
                        </div>
                        <p class="text-xs text-blue-600 dark:text-blue-400 mt-2">
                            <strong>Required:</strong> Asset Tag NO, Item Name<br>
                            <strong>Note:</strong> Tag numbers must end with unique digits (last 5 digits checked). If EMP-Name is provided, employee must exist in system.
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
                        @click="importItems"
                        :disabled="!importFile || isImporting"
                    >
                        <i v-if="isImporting" class="fa-solid fa-spinner fa-spin"></i>
                        <i v-else class="fa-solid fa-upload"></i>
                        {{ isImporting ? 'Importing...' : 'Import Items' }}
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
                        Delete Multiple Items
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <p class="text-sm text-slate-700 dark:text-slate-300 mb-4">
                        Are you sure you want to delete {{ selectedItems.length }} selected items? This action cannot be undone.
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
                        Delete Items
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>