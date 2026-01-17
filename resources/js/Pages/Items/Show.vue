<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import { formatDate } from '@/utils/dateFormat';
import { resolveImage } from '@/utils/imageResolver';

const props = defineProps({
    item: Object,
});

const showImageModal = ref(false);
const selectedImageIndex = ref(0);
const selectedImage = ref(null);
const showDeleteModal = ref(false);

const getAllImages = () => {
    // Get all images from images array or fallback to image_path
    let images = [];
    
    // First, try to get from images array
    if (props.item.images) {
        if (Array.isArray(props.item.images)) {
            images = props.item.images.filter(img => img && img.trim() !== ''); // Filter out empty values
        } else if (typeof props.item.images === 'string') {
            // Handle case where images might be a JSON string
            try {
                const parsed = JSON.parse(props.item.images);
                images = Array.isArray(parsed) ? parsed.filter(img => img && img.trim() !== '') : [];
            } catch (e) {
                images = [];
            }
        }
    }
    
    // If we have images array, use it
    if (images.length > 0) {
        // Debug log (remove in production)
        console.log('Item images:', images);
        return images;
    }
    
    // Fallback to image_path if no images array
    if (props.item.image_path) {
        return [props.item.image_path];
    }
    
    return [];
};

const openImageModal = (image, index) => {
    selectedImage.value = resolveImage(image);
    selectedImageIndex.value = index;
    showImageModal.value = true;
};

const closeImageModal = () => {
    showImageModal.value = false;
    selectedImage.value = null;
};

const nextImage = () => {
    const images = getAllImages();
    if (images.length > 0) {
        selectedImageIndex.value = (selectedImageIndex.value + 1) % images.length;
        selectedImage.value = resolveImage(images[selectedImageIndex.value]);
    }
};

const prevImage = () => {
    const images = getAllImages();
    if (images.length > 0) {
        selectedImageIndex.value = (selectedImageIndex.value - 1 + images.length) % images.length;
        selectedImage.value = resolveImage(images[selectedImageIndex.value]);
    }
};

const handleImageError = (event) => {
    event.target.style.display = 'none';
};

const deleteItem = () => {
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    router.delete(route('items.destroy', props.item.id), { 
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
        }
    });
};

const cancelDelete = () => {
    showDeleteModal.value = false;
};

// Keyboard navigation for image modal
const handleKeydown = (event) => {
    if (!showImageModal.value) return;
    
    if (event.key === 'Escape') {
        closeImageModal();
    } else if (event.key === 'ArrowLeft') {
        prevImage();
    } else if (event.key === 'ArrowRight') {
        nextImage();
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <Head :title="item.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Inventory
                    </p>
                    <h1 class="text-2xl font-semibold leading-tight text-slate-900 dark:text-slate-50">
                        {{ item.name }}
                    </h1>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400" v-if="item.tag_number">
                        Tag: {{ item.tag_number }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link
                        :href="route('items.edit', item.id)"
                        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-sky-600 to-blue-600 px-4 py-2 text-sm font-medium text-white shadow-md transition hover:shadow-lg dark:from-sky-700 dark:to-blue-800"
                    >
                        <i class="fa-solid fa-pen"></i>
                        <span>Edit</span>
                    </Link>
                    <button
                        type="button"
                        @click="deleteItem"
                        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-rose-600 to-red-600 px-4 py-2 text-sm font-medium text-white shadow-md transition hover:shadow-lg dark:from-rose-700 dark:to-red-800"
                    >
                        <i class="fa-solid fa-trash"></i>
                        <span>Delete</span>
                    </button>
                    <Link
                        :href="route('items.index')"
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-200/50 bg-white/50 px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-white/80 dark:border-slate-700/50 dark:bg-slate-800/50 dark:text-slate-300 dark:hover:bg-slate-800/80"
                    >
                        <i class="fa-solid fa-chevron-left"></i>
                        <span>Back</span>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-6xl space-y-6 sm:px-6 lg:px-8">
                <div class="grid gap-6 md:grid-cols-4">
                    <!-- Main Content -->
                    <div class="md:col-span-3 space-y-6">
                        <!-- General Information -->
                        <div class="rounded-xl border border-slate-200/50 bg-white/70 p-6 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70">
                            <div class="mb-4 flex items-center gap-2">
                                <i class="fa-solid fa-circle-info text-blue-600 dark:text-blue-400"></i>
                                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-50">General Information</h2>
                            </div>
                            <dl class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">Name</dt>
                                    <dd class="mt-1.5 text-sm font-medium text-slate-900 dark:text-slate-50">{{ item.name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">Category</dt>
                                    <dd class="mt-1.5 text-sm text-slate-700 dark:text-slate-300">{{ item.category?.name ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">Situation</dt>
                                    <dd class="mt-1.5">
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
                                            :class="{
                                                'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300': item.status?.slug === 'active',
                                                'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300': item.status?.slug === 'daghma',
                                                'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300': item.status?.is_damaged,
                                                'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300': !item.status,
                                            }"
                                        >
                                            {{ item.status?.name ?? 'Unknown' }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">Status</dt>
                                    <dd class="mt-1.5">
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
                                            :class="{
                                                'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300': item.item_employee_assignments && item.item_employee_assignments.length > 0,
                                                'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300': !item.item_employee_assignments || item.item_employee_assignments.length === 0,
                                            }"
                                        >
                                            {{ (item.item_employee_assignments && item.item_employee_assignments.length > 0) ? 'In Use' : 'In Stock' }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">Owner</dt>
                                    <dd class="mt-1.5">
                                        <Link
                                            v-if="item.project"
                                            :href="route('projects.show', item.project.id)"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-amber-100/80 to-orange-100/80 px-3 py-1.5 text-sm font-medium text-amber-700 shadow-sm transition hover:shadow-md dark:from-amber-900/40 dark:to-orange-900/40 dark:text-amber-300"
                                        >
                                            <i class="fa-solid fa-folder text-amber-600 dark:text-amber-400"></i>
                                            <span>{{ item.project.name }}</span>
                                        </Link>
                                        <span v-else class="text-sm text-slate-600 dark:text-slate-400">Unassigned</span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">Location</dt>
                                    <dd class="mt-1.5 text-sm text-slate-700 dark:text-slate-300">
                                        {{ item.location ?? '-' }}
                                        <span v-if="item.sublocation" class="text-slate-600 dark:text-slate-400"> / {{ item.sublocation }}</span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">Model</dt>
                                    <dd class="mt-1.5 text-sm text-slate-700 dark:text-slate-300">{{ item.model ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">Serial Number</dt>
                                    <dd class="mt-1.5 text-sm text-slate-700 dark:text-slate-300">{{ item.serial_number ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">Description</dt>
                                    <dd class="mt-1.5 text-sm text-slate-700 dark:text-slate-300">{{ item.description ?? '-' }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Financial Information -->
                        <div class="rounded-xl border border-slate-200/50 bg-white/70 p-6 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70">
                            <div class="mb-4 flex items-center gap-2">
                                <i class="fa-solid fa-dollar-sign text-emerald-600 dark:text-emerald-400"></i>
                                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-50">Financial</h2>
                            </div>
                            <dl class="grid gap-4 md:grid-cols-3">
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">Price</dt>
                                    <dd class="mt-1.5 text-sm text-slate-700 dark:text-slate-300">
                                        <span v-if="item.price" class="font-medium text-slate-900 dark:text-slate-50">
                                            {{ item.price }} {{ item.currency?.code ?? '' }}
                                        </span>
                                        <span v-else>-</span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">Currency</dt>
                                    <dd class="mt-1.5 text-sm text-slate-700 dark:text-slate-300">{{ item.currency?.name ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">Depreciation rate</dt>
                                    <dd class="mt-1.5 text-sm text-slate-700 dark:text-slate-300">
                                        <span v-if="item.depreciation_rate !== null" class="font-medium text-slate-900 dark:text-slate-50">
                                            {{ item.depreciation_rate }}%
                                        </span>
                                        <span v-else>-</span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">Purchase date</dt>
                                    <dd class="mt-1.5 text-sm text-slate-700 dark:text-slate-300">{{ formatDate(item.purchase_date) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-600 dark:text-slate-400">Voucher number</dt>
                                    <dd class="mt-1.5 text-sm text-slate-700 dark:text-slate-300">{{ item.voucher_number ?? '-' }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Remarks -->
                        <div class="rounded-xl border border-slate-200/50 bg-white/70 p-6 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70">
                            <div class="mb-4 flex items-center gap-2">
                                <i class="fa-solid fa-sticky-note text-blue-600 dark:text-blue-400"></i>
                                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-50">Remarks</h2>
                            </div>
                            <p class="whitespace-pre-line text-sm text-slate-700 dark:text-slate-300">
                                {{ item.remarks || 'No remarks.' }}
                            </p>
                        </div>

                        <!-- Assigned Employees -->
                        <div class="rounded-xl border border-slate-200/50 bg-white/70 p-6 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70">
                            <div class="mb-4 flex items-center gap-2">
                                <i class="fa-solid fa-users text-emerald-600 dark:text-emerald-400"></i>
                                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-50">Assigned Employees</h2>
                            </div>
                            <div v-if="item.item_employee_assignments && item.item_employee_assignments.length" class="space-y-2">
                                <Link
                                    v-for="assignment in item.item_employee_assignments"
                                    :key="assignment.id"
                                    :href="route('employees.show', assignment.employee.id)"
                                    class="flex items-center gap-3 rounded-lg border-2 border-emerald-200 bg-emerald-50 px-4 py-3 transition hover:border-emerald-300 hover:bg-emerald-100 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:hover:border-emerald-700 dark:hover:bg-emerald-900/50"
                                >
                                    <i class="fa-solid fa-user text-emerald-600 dark:text-emerald-400"></i>
                                    <div class="flex-1">
                                        <p class="font-medium text-slate-900 dark:text-slate-50">{{ assignment.employee.employee_code }} - {{ assignment.employee.name }}</p>
                                        <p class="text-xs text-slate-600 dark:text-slate-400">{{ assignment.employee.position }}</p>
                                    </div>
                                    <i class="fa-solid fa-arrow-right text-emerald-600 dark:text-emerald-400"></i>
                                </Link>
                            </div>
                            <div v-else class="rounded-lg bg-slate-50 p-4 text-center text-slate-500 dark:bg-slate-800/50 dark:text-slate-400">
                                <i class="fa-solid fa-inbox text-2xl mb-2 block opacity-50"></i>
                                No employees assigned.
                            </div>
                        </div>

                        <!-- PDF Document -->
                        <div v-if="item.pdf_path" class="rounded-xl border border-slate-200/50 bg-white/70 p-6 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70">
                            <div class="mb-4 flex items-center gap-2">
                                <i class="fa-solid fa-file-pdf text-red-600 dark:text-red-400"></i>
                                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-50">PDF Document</h2>
                            </div>
                            <div class="flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800">
                                <i class="fa-solid fa-file-pdf text-2xl text-red-500"></i>
                                <div class="flex-1">
                                    <p class="font-medium text-slate-900 dark:text-slate-50">Item Document</p>
                                    <p class="text-sm text-slate-600 dark:text-slate-400">PDF file attached to this item</p>
                                </div>
                                <a
                                    :href="item.pdf_path"
                                    target="_blank"
                                    class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-red-600 to-rose-600 px-4 py-2 text-sm font-medium text-white shadow-md transition hover:shadow-lg"
                                >
                                    <i class="fa-solid fa-external-link"></i>
                                    <span>View PDF</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-4">
                        <!-- Images Gallery -->
                        <div class="overflow-hidden rounded-xl border border-slate-200/50 bg-white/70 shadow-lg dark:border-slate-700/50 dark:bg-slate-900/70">
                            <div class="bg-gradient-to-r from-amber-100/60 via-orange-100/50 to-yellow-100/60 px-5 py-4 dark:from-amber-950/50 dark:via-orange-950/40 dark:to-yellow-950/50">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-bold uppercase tracking-wide text-amber-900 dark:text-amber-200">
                                        <i class="fa-solid fa-images mr-2 text-amber-600 dark:text-amber-400"></i>Item Images
                                    </p>
                                    <span
                                        v-if="getAllImages().length > 0"
                                        class="rounded-full bg-amber-200/80 px-3 py-1 text-xs font-semibold text-amber-900 dark:bg-amber-900/60 dark:text-amber-200"
                                    >
                                        {{ getAllImages().length }} {{ getAllImages().length === 1 ? 'Image' : 'Images' }}
                                    </span>
                                </div>
                            </div>
                            <div class="p-5">
                                <div
                                    v-if="getAllImages().length > 0"
                                    class="grid grid-cols-2 gap-4 sm:grid-cols-3"
                                >
                                    <div
                                        v-for="(img, idx) in getAllImages()"
                                        :key="idx"
                                        class="group relative aspect-square overflow-hidden rounded-xl border-2 border-slate-200 bg-gradient-to-br from-slate-50 to-slate-100 shadow-md transition-all duration-300 hover:border-amber-400 hover:shadow-xl hover:shadow-amber-200/50 dark:border-slate-700 dark:from-slate-800 dark:to-slate-900 dark:hover:border-amber-500 dark:hover:shadow-amber-900/50 cursor-pointer"
                                        @click="openImageModal(img, idx)"
                                    >
                                        <img
                                            :src="resolveImage(img)"
                                            :alt="`${item.name} - Image ${idx + 1}`"
                                            class="h-full w-full object-cover transition-all duration-300 group-hover:scale-110 pointer-events-none"
                                            @error="handleImageError"
                                        />
                                        <!-- Overlay on hover -->
                                        <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-t from-black/60 via-black/0 to-black/0 opacity-0 transition-all duration-300 group-hover:opacity-100 pointer-events-none">
                                            <div class="flex flex-col items-center gap-2">
                                                <div class="rounded-full bg-white/90 p-3 shadow-lg backdrop-blur-sm">
                                                    <i class="fa-solid fa-expand text-lg text-slate-900"></i>
                                                </div>
                                                <span class="text-xs font-semibold text-white drop-shadow-lg">Click to View Full Size</span>
                                            </div>
                                        </div>
                                        <!-- Image number badge -->
                                        <div class="absolute left-2 top-2 rounded-full bg-black/60 px-2 py-1 text-xs font-semibold text-white backdrop-blur-sm pointer-events-none">
                                            {{ idx + 1 }}
                                        </div>
                                    </div>
                                </div>
                                <div
                                    v-else
                                    class="flex min-h-64 flex-col items-center justify-center rounded-xl bg-gradient-to-br from-slate-50 via-slate-100 to-slate-200 p-12 text-center dark:from-slate-800 dark:via-slate-900 dark:to-slate-800"
                                >
                                    <div class="rounded-full bg-slate-200/80 p-6 dark:bg-slate-700/80">
                                        <i class="fa-solid fa-image text-5xl text-slate-400 dark:text-slate-500"></i>
                                    </div>
                                    <p class="mt-4 text-base font-semibold text-slate-600 dark:text-slate-400">No Images Available</p>
                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-500">Upload images when editing this item</p>
                                </div>
                            </div>
                        </div>

                        <!-- Identifiers -->
                        <div class="overflow-hidden rounded-xl border border-slate-200/50 bg-white/70 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70">
                            <div class="bg-gradient-to-r from-blue-100/40 to-sky-100/40 px-4 py-3 dark:from-blue-950/40 dark:to-sky-950/40">
                                <p class="text-xs font-semibold uppercase tracking-wide text-blue-900 dark:text-blue-200">
                                    <i class="fa-solid fa-barcode mr-2"></i>Identifiers
                                </p>
                            </div>
                            <div class="space-y-3 p-4 text-sm">
                                <div>
                                    <p class="text-xs font-medium text-slate-600 dark:text-slate-400">Tag Number</p>
                                    <p class="mt-1 font-mono text-slate-900 dark:text-slate-50">{{ item.tag_number || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-slate-600 dark:text-slate-400">Serial Number</p>
                                    <p class="mt-1 font-mono text-slate-900 dark:text-slate-50">{{ item.serial_number || '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Image Modal / Lightbox -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showImageModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/95 backdrop-blur-sm p-4"
                @click="closeImageModal"
            >
                <div class="relative max-h-full max-w-full" @click.stop>
                    <!-- Close Button -->
                    <button
                        @click="closeImageModal"
                        class="absolute -right-2 -top-2 z-20 flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-2xl transition-all hover:scale-110 hover:bg-rose-500 hover:text-white dark:bg-slate-800 dark:text-white dark:hover:bg-rose-600"
                        aria-label="Close"
                    >
                        <i class="fa-solid fa-times text-sm"></i>
                    </button>

                    <!-- Previous Button -->
                    <button
                        v-if="getAllImages().length > 1"
                        @click.stop="prevImage"
                        class="absolute left-2 top-1/2 z-20 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full bg-white/95 shadow-2xl backdrop-blur-sm transition-all hover:scale-110 hover:bg-amber-500 hover:text-white dark:bg-slate-800/95 dark:text-white dark:hover:bg-amber-600"
                        aria-label="Previous image"
                    >
                        <i class="fa-solid fa-chevron-left text-xs"></i>
                    </button>

                    <!-- Next Button -->
                    <button
                        v-if="getAllImages().length > 1"
                        @click.stop="nextImage"
                        class="absolute right-2 top-1/2 z-20 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full bg-white/95 shadow-2xl backdrop-blur-sm transition-all hover:scale-110 hover:bg-amber-500 hover:text-white dark:bg-slate-800/95 dark:text-white dark:hover:bg-amber-600"
                        aria-label="Next image"
                    >
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                    </button>

                    <!-- Main Image Container -->
                    <div class="relative overflow-hidden rounded-2xl bg-slate-900 shadow-2xl">
                        <img
                            v-if="selectedImage"
                            :src="selectedImage"
                            :alt="`${item.name} - Image ${selectedImageIndex + 1}`"
                            class="h-screen w-screen object-contain"
                            @error="handleImageError"
                        />
                        <div v-else class="flex h-96 items-center justify-center">
                            <div class="text-center text-white">
                                <i class="fa-solid fa-image text-4xl mb-2 opacity-50"></i>
                                <p class="text-sm opacity-75">Loading image...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Image Counter & Info -->
                    <div
                        v-if="getAllImages().length > 1"
                        class="absolute bottom-4 left-1/2 z-20 -translate-x-1/2 transform"
                    >
                        <div class="flex items-center gap-1 rounded-full bg-black/70 px-2 py-1 backdrop-blur-md shadow-xl">
                            <i class="fa-solid fa-images text-amber-400 text-[10px]"></i>
                            <span class="text-[10px] font-medium text-white">
                                {{ selectedImageIndex + 1 }}/{{ getAllImages().length }}
                            </span>
                        </div>
                    </div>

                    <!-- Thumbnail Strip (if multiple images) -->
                    <div
                        v-if="getAllImages().length > 1"
                        class="absolute bottom-0 left-0 right-0 z-10 flex justify-center gap-1 bg-gradient-to-t from-black/80 to-transparent p-3 backdrop-blur-sm"
                    >
                        <div
                            v-for="(img, idx) in getAllImages()"
                            :key="idx"
                            class="h-12 w-12 cursor-pointer overflow-hidden rounded-lg border-2 transition-all"
                            :class="idx === selectedImageIndex ? 'border-amber-400 shadow-lg shadow-amber-400/50 scale-110' : 'border-white/30 hover:border-white/60'"
                            @click.stop="openImageModal(img, idx)"
                        >
                            <img
                                :src="resolveImage(img)"
                                :alt="`Thumbnail ${idx + 1}`"
                                class="h-full w-full object-cover"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Delete Item Confirmation Modal -->
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
                    <div class="bg-slate-50 dark:bg-slate-800 rounded-lg p-3">
                        <p class="font-medium text-slate-900 dark:text-slate-50">{{ item.name }}</p>
                        <p class="text-sm text-slate-600 dark:text-slate-400">Tag: {{ item.tag_number || 'N/A' }}</p>
                        <p class="text-sm text-slate-600 dark:text-slate-400">Category: {{ item.category?.name || 'N/A' }}</p>
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
    </AuthenticatedLayout>
</template>
