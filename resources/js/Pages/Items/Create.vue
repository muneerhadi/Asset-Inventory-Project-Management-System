<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    categories: Array,
    statuses: Array, 
    currencies: Array,
    projects: Array,
    nextSequentialNumber: Number,
});

const defaultCategoryId = props.categories[0]?.id ?? null;
const defaultStatusId = props.statuses[0]?.id ?? null;
const defaultCurrencyId = (props.currencies.find((c) => c.is_default) ?? props.currencies[0])?.id ?? null;
const defaultProjectId = props.projects[0]?.id ?? null;

const imagePreviews = ref([]);
const maxImages = 4;
const maxImageSize = 20 * 1024 * 1024; // 20MB

const form = useForm({
    tag_number: '',
    name: '',
    description: '',
    item_category_id: defaultCategoryId,
    item_status_id: defaultStatusId,
    price: '',
    currency_id: defaultCurrencyId,
    depreciation_rate: '',
    purchase_date: '',
    voucher_number: '',
    location: '',
    sublocation: '',
    model: '',
    serial_number: '',
    project_id: defaultProjectId,
    remarks: 'No Remarks',
    images: [],
    pdf_file: null,
});

const handleImageUpload = (event) => {
    const files = Array.from(event.target.files);
    const remainingSlots = maxImages - imagePreviews.value.length;
    const filesToAdd = files.slice(0, remainingSlots);
    
    filesToAdd.forEach(file => {
        if (file.size > maxImageSize) {
            alert(`File ${file.name} exceeds 20MB limit`);
            return;
        }
        form.images.push(file);
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreviews.value.push(e.target.result);
        };
        reader.readAsDataURL(file);
    });
};

const removeImage = (index) => {
    form.images.splice(index, 1);
    imagePreviews.value.splice(index, 1);
};

const handlePdfUpload = (event) => {
    const file = event.target.files[0];
    if (file && file.type === 'application/pdf') {
        form.pdf_file = file;
    }
};

const removePdf = () => {
    form.pdf_file = null;
};

const submit = () => {
    form.post(route('items.store'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="New Item" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Inventory
                    </p>
                    <h1 class="text-2xl font-semibold leading-tight text-slate-900 dark:text-slate-50">
                        New Item
                    </h1>
                </div>
                <Link
                    :href="route('items.index')"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-200/50 bg-white/50 px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-white/80 dark:border-slate-700/50 dark:bg-slate-800/50 dark:text-slate-300 dark:hover:bg-slate-800/80"
                >
                    <i class="fa-solid fa-chevron-left"></i>
                    <span>Back to items</span>
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Basic Information -->
                    <div class="rounded-xl border border-slate-200/50 bg-white/70 p-6 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70">
                        <div class="mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-info-circle text-blue-600 dark:text-blue-400"></i>
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-50">Basic Information</h2>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">
                                    Item Name
                                </label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    class="mt-2 block w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder-slate-500 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-50 dark:placeholder-slate-400 dark:focus:border-sky-400"
                                    required
                                />
                                <p v-if="form.errors.name" class="mt-1 text-xs text-rose-600 dark:text-rose-400">
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="block text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">
                                        Tag number <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="mt-1 mb-2 text-xs text-blue-600 dark:text-blue-400">
                                        Next available number: {{ nextSequentialNumber.toString().padStart(2, '0') }}
                                    </div>
                                    <input
                                        v-model="form.tag_number"
                                        type="text"
                                        placeholder="e.g., UA-AFV-IT-KBL-QLB-34"
                                        class="mt-2 block w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder-slate-500 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-50 dark:placeholder-slate-400 dark:focus:border-sky-400"
                                        required
                                    />
                                    <p v-if="form.errors.tag_number" class="mt-1 text-xs text-rose-600 dark:text-rose-400">
                                        {{ form.errors.tag_number }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        Tag number must end with a unique number (e.g., 34)
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">
                                        Model
                                    </label>
                                    <input
                                        v-model="form.model"
                                        type="text"
                                        class="mt-2 block w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder-slate-500 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-50 dark:placeholder-slate-400 dark:focus:border-sky-400"
                                    />
                                    <p v-if="form.errors.model" class="mt-1 text-xs text-rose-600 dark:text-rose-400">
                                        {{ form.errors.model }}
                                    </p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">
                                    Serial Number
                                </label>
                                <input
                                    v-model="form.serial_number"
                                    type="text"
                                    class="mt-2 block w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder-slate-500 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-50 dark:placeholder-slate-400 dark:focus:border-sky-400"
                                />
                                <p v-if="form.errors.serial_number" class="mt-1 text-xs text-rose-600 dark:text-rose-400">
                                    {{ form.errors.serial_number }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">
                                    Specification
                                </label>
                                <textarea
                                    v-model="form.description"
                                    rows="3"
                                    class="mt-2 block w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder-slate-500 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-50 dark:placeholder-slate-400 dark:focus:border-sky-400"
                                />
                                <p v-if="form.errors.description" class="mt-1 text-xs text-rose-600 dark:text-rose-400">
                                    {{ form.errors.description }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Classification -->
                    <div class="rounded-xl border border-slate-200/50 bg-white/70 p-6 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70">
                        <div class="mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-tags text-amber-600 dark:text-amber-400"></i>
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-50">Classification</h2>
                        </div>
                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <label class="block text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">
                                    Type
                                </label>
                                <select
                                    v-model="form.item_category_id"
                                    class="mt-2 block w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 transition focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-50 dark:focus:border-amber-400"
                                    required
                                >
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                        {{ cat.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.item_category_id" class="mt-1 text-xs text-rose-600 dark:text-rose-400">
                                    {{ form.errors.item_category_id }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">
                                    Status
                                </label>
                                <select
                                    v-model="form.item_status_id"
                                    class="mt-2 block w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-50 dark:focus:border-emerald-400"
                                    required
                                >
                                    <option v-for="st in statuses" :key="st.id" :value="st.id">
                                        {{ st.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.item_status_id" class="mt-1 text-xs text-rose-600 dark:text-rose-400">
                                    {{ form.errors.item_status_id }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">
                                    Owner
                                </label>
                                <select
                                    v-model="form.project_id"
                                    class="mt-2 block w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-50 dark:focus:border-sky-400"
                                >
                                    <option :value="null">Unassigned</option>
                                    <option v-for="proj in projects" :key="proj.id" :value="proj.id">
                                        {{ proj.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.project_id" class="mt-1 text-xs text-rose-600 dark:text-rose-400">
                                    {{ form.errors.project_id }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Information -->
                    <div class="rounded-xl border border-slate-200/50 bg-white/70 p-6 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70">
                        <div class="mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-dollar-sign text-emerald-600 dark:text-emerald-400"></i>
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-50">Financial</h2>
                        </div>
                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <label class="block text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">
                                    Price <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    v-model="form.price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="mt-2 block w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder-slate-500 transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-50 dark:placeholder-slate-400 dark:focus:border-emerald-400"
                                    required
                                />
                                <p v-if="form.errors.price" class="mt-1 text-xs text-rose-600 dark:text-rose-400">
                                    {{ form.errors.price }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">
                                    Currency
                                </label>
                                <select
                                    v-model="form.currency_id"
                                    class="mt-2 block w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-50 dark:focus:border-emerald-400"
                                >
                                    <option :value="null">None</option>
                                    <option v-for="cur in currencies" :key="cur.id" :value="cur.id">
                                        {{ cur.code }} - {{ cur.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.currency_id" class="mt-1 text-xs text-rose-600 dark:text-rose-400">
                                    {{ form.errors.currency_id }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">
                                    Depreciation rate (%)
                                </label>
                                <input
                                    v-model="form.depreciation_rate"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="mt-2 block w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder-slate-500 transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-50 dark:placeholder-slate-400 dark:focus:border-emerald-400"
                                />
                                <p v-if="form.errors.depreciation_rate" class="mt-1 text-xs text-rose-600 dark:text-rose-400">
                                    {{ form.errors.depreciation_rate }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-4 grid gap-4 md:grid-cols-3">
                            <div>
                                <label class="block text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">
                                    Purchase date
                                </label>
                                <input
                                    v-model="form.purchase_date"
                                    type="date"
                                    class="mt-2 block w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-50 dark:focus:border-emerald-400"
                                />
                                <p v-if="form.errors.purchase_date" class="mt-1 text-xs text-rose-600 dark:text-rose-400">
                                    {{ form.errors.purchase_date }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">
                                    Voucher number
                                </label>
                                <input
                                    v-model="form.voucher_number"
                                    type="text"
                                    class="mt-2 block w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder-slate-500 transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-50 dark:placeholder-slate-400 dark:focus:border-emerald-400"
                                />
                                <p v-if="form.errors.voucher_number" class="mt-1 text-xs text-rose-600 dark:text-rose-400">
                                    {{ form.errors.voucher_number }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Location & Additional Details -->
                    <div class="rounded-xl border border-slate-200/50 bg-white/70 p-6 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70">
                        <div class="mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-map-pin text-blue-600 dark:text-blue-400"></i>
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-50">Location & Details</h2>
                        </div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">
                                    Location
                                </label>
                                <input
                                    v-model="form.location"
                                    type="text"
                                    class="mt-2 block w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder-slate-500 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-50 dark:placeholder-slate-400 dark:focus:border-sky-400"
                                />
                                <p v-if="form.errors.location" class="mt-1 text-xs text-rose-600 dark:text-rose-400">
                                    {{ form.errors.location }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">
                                    Sub-location
                                </label>
                                <input
                                    v-model="form.sublocation"
                                    type="text"
                                    class="mt-2 block w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder-slate-500 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-50 dark:placeholder-slate-400 dark:focus:border-sky-400"
                                />
                                <p v-if="form.errors.sublocation" class="mt-1 text-xs text-rose-600 dark:text-rose-400">
                                    {{ form.errors.sublocation }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Media & Remarks -->
                    <div class="rounded-xl border border-slate-200/50 bg-white/70 p-6 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70">
                        <div class="mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-image text-amber-600 dark:text-amber-400"></i>
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-50">Media & Notes</h2>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400 mb-2">
                                    Images (up to {{ maxImages }} images, max 20MB each)
                                </label>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                    <div
                                        v-for="(preview, index) in imagePreviews"
                                        :key="index"
                                        class="relative"
                                    >
                                        <img :src="preview" alt="Preview" class="w-full h-32 object-cover rounded-lg border border-slate-200 dark:border-slate-700" />
                                        <button
                                            type="button"
                                            @click="removeImage(index)"
                                            class="absolute top-1 right-1 rounded-full bg-rose-500 text-white p-1 hover:bg-rose-600"
                                        >
                                            <i class="fa-solid fa-times text-xs"></i>
                                        </button>
                                    </div>
                                    <label
                                        v-if="imagePreviews.length < maxImages"
                                        class="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-slate-300 bg-gradient-to-br from-slate-50 to-slate-100 px-4 py-8 text-center transition hover:border-blue-500 hover:bg-blue-50/30 dark:border-slate-600 dark:from-slate-800/50 dark:to-slate-900/50 dark:hover:border-blue-400 dark:hover:bg-blue-900/10"
                                    >
                                        <i class="fa-solid fa-cloud-arrow-up text-2xl text-slate-400 dark:text-slate-500"></i>
                                        <p class="mt-2 text-xs font-medium text-slate-700 dark:text-slate-300">
                                            Add Image
                                        </p>
                                        <input
                                            type="file"
                                            class="hidden"
                                            accept="image/*"
                                            multiple
                                            @change="handleImageUpload"
                                        />
                                    </label>
                                </div>
                                <p v-if="form.errors.images" class="mt-1 text-xs text-rose-600 dark:text-rose-400">
                                    {{ form.errors.images }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400 mb-2">
                                    PDF Document
                                </label>
                                <div v-if="form.pdf_file" class="mb-2 flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800">
                                    <i class="fa-solid fa-file-pdf text-red-500"></i>
                                    <span class="text-sm text-slate-700 dark:text-slate-300">{{ form.pdf_file.name }}</span>
                                    <button
                                        type="button"
                                        @click="removePdf"
                                        class="ml-auto text-rose-500 hover:text-rose-700"
                                    >
                                        <i class="fa-solid fa-times"></i>
                                    </button>
                                </div>
                                <label
                                    v-else
                                    class="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-slate-300 bg-gradient-to-br from-slate-50 to-slate-100 px-4 py-6 text-center transition hover:border-red-500 hover:bg-red-50/30 dark:border-slate-600 dark:from-slate-800/50 dark:to-slate-900/50 dark:hover:border-red-400 dark:hover:bg-red-900/10"
                                >
                                    <i class="fa-solid fa-file-pdf text-2xl text-red-400 dark:text-red-500"></i>
                                    <p class="mt-2 text-xs font-medium text-slate-700 dark:text-slate-300">
                                        Upload PDF Document
                                    </p>
                                    <input
                                        type="file"
                                        class="hidden"
                                        accept=".pdf"
                                        @change="handlePdfUpload"
                                    />
                                </label>
                                <p v-if="form.errors.pdf_file" class="mt-1 text-xs text-rose-600 dark:text-rose-400">
                                    {{ form.errors.pdf_file }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">
                                    Remarks
                                </label>
                                <textarea
                                    v-model="form.remarks"
                                    rows="3"
                                    class="mt-2 block w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder-slate-500 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-50 dark:placeholder-slate-400 dark:focus:border-sky-400"
                                />
                                <p v-if="form.errors.remarks" class="mt-1 text-xs text-rose-600 dark:text-rose-400">
                                    {{ form.errors.remarks }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end gap-3">
                        <Link
                            :href="route('items.index')"
                            class="inline-flex items-center gap-2 rounded-lg border border-slate-200/50 bg-white/50 px-6 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-white/80 dark:border-slate-700/50 dark:bg-slate-800/50 dark:text-slate-300 dark:hover:bg-slate-800/80"
                        >
                            <span>Cancel</span>
                        </Link>
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-sky-600 to-blue-600 px-6 py-2.5 text-sm font-medium text-white shadow-md transition hover:shadow-lg disabled:opacity-50 dark:from-sky-700 dark:to-blue-800"
                            :disabled="form.processing"
                        >
                            <i class="fa-solid fa-check"></i>
                            <span>{{ form.processing ? 'Saving...' : 'Save Item' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
