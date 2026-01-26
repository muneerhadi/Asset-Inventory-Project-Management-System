<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { resolveImage } from '@/utils/imageResolver';

const props = defineProps({
    item: Object,
    categories: Array,
    statuses: Array,
    currencies: Array, 
    projects: Array,
});

const imagePreviews = ref([]);
const maxImages = 4;
const maxImageSize = 20 * 1024 * 1024; // 20MB
const imagesToKeep = ref([]); // Track which existing images to keep

// Get existing images from item
const existingImages = computed(() => {
    if (props.item.images && Array.isArray(props.item.images) && props.item.images.length > 0) {
        return props.item.images;
    }
    if (props.item.image_path) {
        return [props.item.image_path];
    }
    return [];
});

// Initialize image previews with existing images
existingImages.value.forEach(img => {
    const imageObj = {
        type: 'existing',
        url: resolveImage(img),
        path: img
    };
    imagePreviews.value.push(imageObj);
    imagesToKeep.value.push(img); // Track existing images to keep
});

const form = useForm({
    tag_number: props.item.tag_number ?? '',
    name: props.item.name ?? '',
    description: props.item.description ?? '',
    item_category_id: props.item.item_category_id ?? null,
    item_status_id: props.item.item_status_id ?? null,
    price: props.item.price ?? '',
    currency_id: props.item.currency_id ?? null,
    depreciation_rate: props.item.depreciation_rate ?? '',
    purchase_date: props.item.purchase_date ?? '',
    voucher_number: props.item.voucher_number ?? '',
    location: props.item.location ?? '',
    sublocation: props.item.sublocation ?? '',
    model: props.item.model ?? '',
    project_id: props.item.project_id ?? null,
    remarks: props.item.remarks ?? '',
    images: [],
    images_to_keep: [], // Track which existing images to keep
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
            imagePreviews.value.push({
                type: 'new',
                url: e.target.result,
                file: file
            });
        };
        reader.readAsDataURL(file);
    });
    
    // Reset input
    event.target.value = '';
};

const removeImage = (index) => {
    const preview = imagePreviews.value[index];
    
    if (preview.type === 'new') {
        // Remove from form.images array
        const fileIndex = form.images.findIndex(f => f === preview.file);
        if (fileIndex !== -1) {
            form.images.splice(fileIndex, 1);
        }
    } else if (preview.type === 'existing') {
        // Remove from images_to_keep array
        const keepIndex = imagesToKeep.value.findIndex(path => path === preview.path);
        if (keepIndex !== -1) {
            imagesToKeep.value.splice(keepIndex, 1);
        }
    }
    // Remove from previews
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
    // Include images_to_keep in the form data
    form.images_to_keep = imagesToKeep.value;
    
    form.transform((data) => ({
        ...data,
        _method: 'put',
    })).post(route('items.update', props.item.id), {
        forceFormData: true,
        onFinish: () => form.transform((data) => data),
    });
};
</script>

<template>
    <Head :title="`Edit ${item.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Inventory
                    </p>
                    <h1 class="text-2xl font-semibold leading-tight text-slate-900 dark:text-slate-50">
                        Edit Item
                    </h1>
                </div>
                <Link
                    :href="route('items.show', item.id)"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-200/50 bg-white/50 px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-white/80 dark:border-slate-700/50 dark:bg-slate-800/50 dark:text-slate-300 dark:hover:bg-slate-800/80"
                >
                    <i class="fa-solid fa-chevron-left"></i>
                    <span>Back to item</span>
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
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="block text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">
                                        Tag number
                                    </label>
                                    <input
                                        v-model="form.tag_number"
                                        type="text"
                                        class="mt-2 block w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder-slate-500 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-50 dark:placeholder-slate-400 dark:focus:border-sky-400"
                                    />
                                    <p v-if="form.errors.tag_number" class="mt-1 text-xs text-rose-600 dark:text-rose-400">
                                        {{ form.errors.tag_number }}
                                    </p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">
                                    Name
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

                            <div>
                                <label class="block text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">
                                    Description
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
                                    Category
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
                                    Project
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
                                    Price
                                </label>
                                <input
                                    v-model="form.price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="mt-2 block w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder-slate-500 transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-50 dark:placeholder-slate-400 dark:focus:border-emerald-400"
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
                        <div class="mt-4">
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

                    <!-- Media & Remarks -->
                    <div class="rounded-xl border border-slate-200/50 bg-white/70 p-6 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70">
                        <div class="mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-image text-amber-600 dark:text-amber-400"></i>
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-50">Media & Notes</h2>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-900 dark:text-slate-50 mb-3">
                                    <i class="fa-solid fa-images mr-2 text-amber-600 dark:text-amber-400"></i>
                                    Item Images (up to {{ maxImages }} images, max 20MB each)
                                </label>
                                
                                <!-- Image Preview Grid -->
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                    <div
                                        v-for="(preview, index) in imagePreviews"
                                        :key="index"
                                        class="group relative aspect-square overflow-hidden rounded-xl border-2 border-slate-200 bg-slate-50 shadow-md transition hover:shadow-lg dark:border-slate-700 dark:bg-slate-800"
                                    >
                                        <img
                                            :src="preview.url"
                                            :alt="`Image ${index + 1}`"
                                            class="h-full w-full object-cover"
                                        />
                                        <button
                                            type="button"
                                            @click="removeImage(index)"
                                            class="absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-full bg-rose-500 text-white shadow-lg transition hover:bg-rose-600 hover:scale-110"
                                            title="Remove image"
                                        >
                                            <i class="fa-solid fa-times text-xs"></i>
                                        </button>
                                        <div
                                            v-if="preview.type === 'existing'"
                                            class="absolute left-2 top-2 rounded-full bg-emerald-500 px-2 py-1 text-xs font-semibold text-white shadow-lg"
                                        >
                                            Current
                                        </div>
                                    </div>
                                    
                                    <!-- Upload Slot -->
                                    <label
                                        v-if="imagePreviews.length < maxImages"
                                        class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-gradient-to-br from-slate-50 to-slate-100 px-4 py-8 transition hover:border-amber-400 hover:bg-amber-50 dark:border-slate-600 dark:from-slate-800 dark:to-slate-900 dark:hover:border-amber-500 dark:hover:bg-slate-800"
                                    >
                                        <i class="fa-solid fa-cloud-arrow-up text-3xl text-slate-400 dark:text-slate-500 mb-2"></i>
                                        <span class="text-xs font-medium text-slate-600 dark:text-slate-400 text-center">
                                            Add Image
                                        </span>
                                        <span class="mt-1 text-xs text-slate-500 dark:text-slate-500">
                                            {{ maxImages - imagePreviews.length }} slot{{ maxImages - imagePreviews.length > 1 ? 's' : '' }} left
                                        </span>
                                        <input
                                            type="file"
                                            class="hidden"
                                            accept="image/*"
                                            multiple
                                            @change="handleImageUpload"
                                        />
                                    </label>
                                </div>
                                
                                <p v-if="form.errors.images" class="mt-2 text-xs text-rose-600 dark:text-rose-400">
                                    <i class="fa-solid fa-circle-exclamation mr-1"></i>
                                    {{ form.errors.images }}
                                </p>
                                <p v-if="form.errors.image" class="mt-2 text-xs text-rose-600 dark:text-rose-400">
                                    <i class="fa-solid fa-circle-exclamation mr-1"></i>
                                    {{ form.errors.image }}
                                </p>
                                <p
                                    v-if="imagePreviews.length === 0"
                                    class="mt-2 text-xs text-slate-500 dark:text-slate-400 italic"
                                >
                                    <i class="fa-solid fa-info-circle mr-1"></i>
                                    No images uploaded. You can add up to {{ maxImages }} images.
                                </p>
                            </div>

                            <div>
                                <label class="block text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400 mb-2">
                                    PDF Document
                                </label>
                                <div v-if="item.pdf_path && !form.pdf_file" class="mb-2 flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800">
                                    <i class="fa-solid fa-file-pdf text-red-500"></i>
                                    <span class="text-sm text-slate-700 dark:text-slate-300">Current PDF Document</span>
                                    <a :href="item.pdf_path" target="_blank" class="ml-auto text-blue-500 hover:text-blue-700">
                                        <i class="fa-solid fa-external-link"></i>
                                    </a>
                                </div>
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
                                    v-if="!form.pdf_file"
                                    class="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-slate-300 bg-gradient-to-br from-slate-50 to-slate-100 px-4 py-6 text-center transition hover:border-red-500 hover:bg-red-50/30 dark:border-slate-600 dark:from-slate-800/50 dark:to-slate-900/50 dark:hover:border-red-400 dark:hover:bg-red-900/10"
                                >
                                    <i class="fa-solid fa-file-pdf text-2xl text-red-400 dark:text-red-500"></i>
                                    <p class="mt-2 text-xs font-medium text-slate-700 dark:text-slate-300">
                                        {{ item.pdf_path ? 'Replace PDF Document' : 'Upload PDF Document' }}
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
                            :href="route('items.show', item.id)"
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
                            <span>{{ form.processing ? 'Updating...' : 'Update Item' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
