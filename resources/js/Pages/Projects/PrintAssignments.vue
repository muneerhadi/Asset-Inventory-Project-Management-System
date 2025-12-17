<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    project: Object,
});

const uniqueItems = computed(() => {
    const items = new Set();
    props.project.item_employee_assignments?.forEach(assignment => {
        items.add(assignment.item.id);
    });
    return items.size;
});

const uniqueEmployees = computed(() => {
    const employees = new Set();
    props.project.item_employee_assignments?.forEach(assignment => {
        employees.add(assignment.employee.id);
    });
    return employees.size;
});

const printPage = () => {
    window.print();
};
</script>

<template>
    <Head :title="`Assignments report: ${project.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-slate-900 dark:text-slate-50">
                        <i class="fa-solid fa-handshake mr-3 text-sky-600 dark:text-sky-400"></i>
                        Item-Employee Assignments
                    </h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                        {{ project.code }} · {{ project.name }}
                    </p>
                </div>
                <div class="flex gap-2 print:hidden">
                    <button
                        type="button"
                        class="rounded-lg bg-gradient-to-r from-blue-600 to-sky-600 px-4 py-2 text-sm font-medium text-white shadow-md transition hover:shadow-lg dark:from-blue-700 dark:to-sky-700"
                        @click="printPage"
                    >
                        <i class="fa-solid fa-print mr-2"></i>
                        Print
                    </button>
                    <Link
                        :href="route('projects.show', project.id)"
                        class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-medium text-slate-700 shadow-md transition hover:shadow-lg dark:bg-slate-700 dark:text-slate-200"
                    >
                        <i class="fa-solid fa-arrow-left mr-2"></i>
                        Back
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6 print:py-0 print:m-0 print:p-0">
            <div class="mx-auto max-w-6xl space-y-6 print:max-w-none print:m-0 print:p-4">
                <!-- Screen View Content -->
                <div class="print:hidden">
                    <!-- Header / Summary -->
                    <div class="mb-8 text-center border-b-2 border-slate-300 pb-6 bg-white/80 rounded-xl shadow-sm">
                        <h1 class="text-3xl font-bold text-slate-900 mb-2">Item-Employee Assignments Report</h1>
                        <p class="text-sm text-slate-600 mb-1">{{ project.name }} ({{ project.code }})</p>
                        <p class="text-xs text-slate-500">Generated on {{ new Date().toLocaleDateString() }} at {{ new Date().toLocaleTimeString() }}</p>
                    </div>

                    <!-- Project Summary -->
                    <div class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="border border-slate-200 rounded-lg p-4 bg-slate-50 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-600 mb-1">Total Assignments</p>
                            <p class="text-3xl font-bold text-blue-600">{{ project.item_employee_assignments?.length || 0 }}</p>
                        </div>
                        <div class="border border-slate-200 rounded-lg p-4 bg-slate-50 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-600 mb-1">Unique Items</p>
                            <p class="text-3xl font-bold text-amber-600">{{ uniqueItems }}</p>
                        </div>
                        <div class="border border-slate-200 rounded-lg p-4 bg-slate-50 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-600 mb-1">Unique Employees</p>
                            <p class="text-3xl font-bold text-emerald-600">{{ uniqueEmployees }}</p>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="mb-8 overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
                        <table class="w-full border-collapse text-sm">
                            <thead>
                                <tr class="bg-slate-900 text-white">
                                    <th class="border border-slate-300 px-4 py-3 text-left font-semibold">Item Code</th>
                                    <th class="border border-slate-300 px-4 py-3 text-left font-semibold">Item Name</th>
                                    <th class="border border-slate-300 px-4 py-3 text-left font-semibold">Category</th>
                                    <th class="border border-slate-300 px-4 py-3 text-left font-semibold">Employee Code</th>
                                    <th class="border border-slate-300 px-4 py-3 text-left font-semibold">Employee Name</th>
                                    <th class="border border-slate-300 px-4 py-3 text-left font-semibold">Position</th>
                                    <th class="border border-slate-300 px-4 py-3 text-left font-semibold">Assigned Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(assignment, index) in project.item_employee_assignments"
                                    :key="assignment.id"
                                    :class="index % 2 === 0 ? 'bg-white' : 'bg-slate-50'"
                                >
                                    <td class="border border-slate-200 px-4 py-3 font-medium text-slate-900">{{ assignment.item.item_code }}</td>
                                    <td class="border border-slate-200 px-4 py-3 text-slate-800">{{ assignment.item.name }}</td>
                                    <td class="border border-slate-200 px-4 py-3 text-slate-700">{{ assignment.item.category?.name || '-' }}</td>
                                    <td class="border border-slate-200 px-4 py-3 font-medium text-slate-900">{{ assignment.employee.employee_code }}</td>
                                    <td class="border border-slate-200 px-4 py-3 text-slate-800">{{ assignment.employee.name }}</td>
                                    <td class="border border-slate-200 px-4 py-3 text-slate-700">{{ assignment.employee.position || '-' }}</td>
                                    <td class="border border-slate-200 px-4 py-3 text-slate-700">{{ new Date(assignment.created_at).toLocaleDateString() }}</td>
                                </tr>
                                <tr v-if="!project.item_employee_assignments || project.item_employee_assignments.length === 0">
                                    <td colspan="7" class="border border-slate-200 px-4 py-8 text-center text-slate-500">
                                        <i class="fa-solid fa-inbox text-2xl mb-2 block opacity-50"></i>
                                        No assignments found
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer -->
                    <div class="border-t-2 border-slate-300 pt-6 mt-8">
                        <p class="text-xs text-slate-500 text-center">
                            This is an automatically generated report. Please do not edit this document.
                        </p>
                    </div>
                </div>

                <!-- Print View Content -->
                <div class="hidden print:block">
                    <!-- Print Header -->
                    <div class="border-b-4 border-slate-900 pb-6 text-center mb-6">
                        <!-- Logo -->
                        <div class="mb-4 flex justify-center">
                            <div class="h-20 w-20">
                                <img src="/storage/logo/union-aid-logo.png" alt="Logo" class="h-full w-full object-contain" />
                            </div>
                        </div>

                        <!-- Title -->
                        <h1 class="text-3xl font-bold text-slate-900 tracking-wide">
                            UNION AID
                        </h1>
                        <p class="mt-1 text-sm font-medium text-slate-800">
                            Item-Employee Assignments Report
                        </p>
                        <p class="mt-1 text-xs text-slate-700">
                            {{ project.name }} ({{ project.code }})
                        </p>

                        <!-- Time -->
                        <p class="mt-2 text-xs font-medium text-slate-700">
                            {{ new Date().toLocaleString() }}
                        </p>
                    </div>

                    <!-- Assignments Table for Print -->
                    <div class="mt-4">
                        <table class="w-full text-[11px] border-collapse">
                            <thead>
                                <tr class="border-b-2 border-slate-900 bg-slate-100">
                                    <th class="border border-slate-900 px-2 py-1 text-left font-bold">Item Code</th>
                                    <th class="border border-slate-900 px-2 py-1 text-left font-bold">Item Name</th>
                                    <th class="border border-slate-900 px-2 py-1 text-left font-bold">Category</th>
                                    <th class="border border-slate-900 px-2 py-1 text-left font-bold">Employee Code</th>
                                    <th class="border border-slate-900 px-2 py-1 text-left font-bold">Employee Name</th>
                                    <th class="border border-slate-900 px-2 py-1 text-left font-bold">Position</th>
                                    <th class="border border-slate-900 px-2 py-1 text-left font-bold">Assigned Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="assignment in project.item_employee_assignments" :key="assignment.id" class="border border-slate-900">
                                    <td class="border border-slate-900 px-2 py-1 text-black">{{ assignment.item.item_code }}</td>
                                    <td class="border border-slate-900 px-2 py-1 text-black">{{ assignment.item.name }}</td>
                                    <td class="border border-slate-900 px-2 py-1 text-black">{{ assignment.item.category?.name || '-' }}</td>
                                    <td class="border border-slate-900 px-2 py-1 text-black">{{ assignment.employee.employee_code }}</td>
                                    <td class="border border-slate-900 px-2 py-1 text-black">{{ assignment.employee.name }}</td>
                                    <td class="border border-slate-900 px-2 py-1 text-black">{{ assignment.employee.position || '-' }}</td>
                                    <td class="border border-slate-900 px-2 py-1 text-black">{{ new Date(assignment.created_at).toLocaleDateString() }}</td>
                                </tr>
                                <tr v-if="!project.item_employee_assignments || project.item_employee_assignments.length === 0">
                                    <td colspan="7" class="border border-slate-900 px-3 py-4 text-center font-medium">
                                        No assignments found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@page { margin: 0; }
html, body { margin: 0 !important; padding: 0 !important; }

@media print {
    .print:hidden {
        display: none !important;
    }
}
</style>
