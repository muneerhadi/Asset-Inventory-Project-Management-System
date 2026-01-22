<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { resolveImage } from '@/utils/imageResolver';

const props = defineProps({
    employee: Object,
});

const printPage = () => {
    window.print();
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>

<template>
    <Head :title="`Employee Card: ${employee.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between print:hidden">
                <div>
                    <h2 class="text-3xl font-bold text-slate-900 dark:text-slate-50">
                        <i class="fa-solid fa-id-card mr-3 text-sky-600 dark:text-sky-400"></i>
                        Employee Stack Card
                    </h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                        {{ employee.name }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <button
                        type="button"
                        class="rounded-lg bg-gradient-to-r from-blue-600 to-sky-600 px-4 py-2 text-sm font-medium text-white shadow-md transition hover:shadow-lg dark:from-blue-700 dark:to-sky-700"
                        @click="printPage"
                    >
                        <i class="fa-solid fa-print mr-2"></i>
                        Print
                    </button>
                    <Link
                        :href="route('employees.show', employee.id)"
                        class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-medium text-slate-700 shadow-md transition hover:shadow-lg dark:bg-slate-700 dark:text-slate-200"
                    >
                        <i class="fa-solid fa-arrow-left mr-2"></i>
                        Back
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6 print:py-0 print:m-0 print:p-0">
            <div class="mx-auto max-w-6xl space-y-6 print:max-w-none print:m-0 print:p-8">
                <!-- Print View Content -->
                <div class="print:block">
                    <!-- Header Section -->
                    <div class="mb-6 relative">
                        <!-- Top Row: Employee Image (Left), Logo (Center), Date (Right) -->
                        <div class="flex justify-between items-start mb-4">
                            <!-- Employee Image - Left -->
                            <div class="w-32 h-32 border-2 border-slate-900 flex items-center justify-center bg-white flex-shrink-0">
                                <img 
                                    v-if="employee.image_path" 
                                    :src="resolveImage(employee.image_path)" 
                                    alt="Employee photo" 
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center bg-slate-100">
                                    <i class="fa-solid fa-user text-4xl text-slate-400"></i>
                                </div>
                            </div>
                            
                            <!-- Union Aid Logo - Center -->
                            <div class="flex-1 flex justify-center items-start">
                                <div class="h-20 w-20">
                                    <img src="/storage/logo/union-aid-logo.png" alt="Union Aid Logo" class="h-full w-full object-contain" />
                                </div>
                            </div>
                            
                            <!-- Date - Right -->
                            <div class="text-right flex-shrink-0">
                                <p class="text-sm font-semibold text-slate-900">
                                    Date: {{ formatDate(new Date()) }}
                                </p>
                            </div>
                        </div>

                        <!-- Title and Employee Name - Below Logo -->
                        <div class="text-center mb-6">
                            <!-- Employee Stack Card Text -->
                            <h1 class="text-2xl font-bold text-slate-900 mb-2">
                                Employee Stack Card
                            </h1>
                            
                            <!-- Employee Name -->
                            <h2 class="text-xl font-semibold text-slate-800">
                                {{ employee.name }}
                            </h2>
                        </div>
                    </div>

                    <!-- Employee Details Table -->
                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-slate-900 mb-3 border-b-2 border-slate-900 pb-1">
                            Employee Information
                        </h3>
                        <table class="w-full border-collapse border-2 border-slate-900 text-xs">
                            <thead>
                                <tr class="bg-slate-100">
                                    <th class="border-2 border-slate-900 px-3 py-2 text-left font-bold">Name</th>
                                    <th class="border-2 border-slate-900 px-3 py-2 text-left font-bold">Position</th>
                                    <th class="border-2 border-slate-900 px-3 py-2 text-left font-bold">Employee Code</th>
                                    <th class="border-2 border-slate-900 px-3 py-2 text-left font-bold">Location</th>
                                    <th class="border-2 border-slate-900 px-3 py-2 text-left font-bold">Email</th>
                                    <th class="border-2 border-slate-900 px-3 py-2 text-left font-bold">Phone</th>
                                    <th class="border-2 border-slate-900 px-3 py-2 text-left font-bold">Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="border-2 border-slate-900 px-3 py-2">{{ employee.name || '-' }}</td>
                                    <td class="border-2 border-slate-900 px-3 py-2">{{ employee.position || '-' }}</td>
                                    <td class="border-2 border-slate-900 px-3 py-2">{{ employee.employee_code || '-' }}</td>
                                    <td class="border-2 border-slate-900 px-3 py-2">{{ employee.location || '-' }}</td>
                                    <td class="border-2 border-slate-900 px-3 py-2">{{ employee.email || '-' }}</td>
                                    <td class="border-2 border-slate-900 px-3 py-2">{{ employee.phone || '-' }}</td>
                                    <td class="border-2 border-slate-900 px-3 py-2">{{ employee.address || '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Assigned Items Table -->
                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-slate-900 mb-3 border-b-2 border-slate-900 pb-1">
                            Assigned Items
                        </h3>
                        <table class="w-full border-collapse border-2 border-slate-900 text-xs">
                            <thead>
                                <tr class="bg-slate-100">
                                    <th class="border-2 border-slate-900 px-3 py-2 text-left font-bold">Tag Number</th>
                                    <th class="border-2 border-slate-900 px-3 py-2 text-left font-bold">Item Name</th>
                                    <th class="border-2 border-slate-900 px-3 py-2 text-left font-bold">Category</th>
                                    <th class="border-2 border-slate-900 px-3 py-2 text-left font-bold">Status</th>
                                    <th class="border-2 border-slate-900 px-3 py-2 text-left font-bold">Model</th>
                                    <th class="border-2 border-slate-900 px-3 py-2 text-left font-bold">Serial Number</th>
                                    <th class="border-2 border-slate-900 px-3 py-2 text-left font-bold">Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr 
                                    v-for="assignment in employee.item_employee_assignments" 
                                    :key="assignment.id"
                                >
                                    <td class="border-2 border-slate-900 px-3 py-2">{{ assignment.item.tag_number || assignment.item.item_code || '-' }}</td>
                                    <td class="border-2 border-slate-900 px-3 py-2">{{ assignment.item.name || '-' }}</td>
                                    <td class="border-2 border-slate-900 px-3 py-2">{{ assignment.item.category?.name || '-' }}</td>
                                    <td class="border-2 border-slate-900 px-3 py-2">{{ assignment.item.status?.name || '-' }}</td>
                                    <td class="border-2 border-slate-900 px-3 py-2">{{ assignment.item.model || '-' }}</td>
                                    <td class="border-2 border-slate-900 px-3 py-2">{{ assignment.item.serial_number || '-' }}</td>
                                    <td class="border-2 border-slate-900 px-3 py-2">{{ assignment.item.description || '-' }}</td>
                                </tr>
                                <tr v-if="!employee.item_employee_assignments || employee.item_employee_assignments.length === 0">
                                    <td colspan="7" class="border-2 border-slate-900 px-4 py-4 text-center font-medium">
                                        No items assigned.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Signature Section -->
                    <div class="mt-8 flex justify-between">
                        <!-- Employee Signature - Left -->
                        <div class="w-1/2 pr-4">
                            <div class="border-t-2 border-slate-900 pt-2 mt-16">
                                <p class="text-sm font-semibold text-slate-900 text-center">
                                    Employee Signature
                                </p>
                                <p class="text-xs text-slate-700 text-center mt-1">
                                    {{ employee.name }}
                                </p>
                            </div>
                        </div>

                        <!-- Admin Signature - Right -->
                        <div class="w-1/2 pl-4">
                            <div class="border-t-2 border-slate-900 pt-2 mt-16">
                                <p class="text-sm font-semibold text-slate-900 text-center">
                                    Admin Signature
                                </p>
                                <p class="text-xs text-slate-700 text-center mt-1">
                                    _________________________
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@page {
    margin: 0.5in;
    size: A4;
}

@media print {
    .print\:hidden {
        display: none !important;
    }
    
    body {
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
    }
    
    /* Ensure borders print correctly */
    table, td, th {
        border-collapse: collapse !important;
    }
}
</style>
