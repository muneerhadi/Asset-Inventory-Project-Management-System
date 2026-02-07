<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    categories: Array,
});

const form = useForm({
    name: '',
    description: '',
});

const submit = () => { 
    form.post(route('entry.categories.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Item Categories" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Entry</p>
                <h1 class="text-2xl font-semibold leading-tight text-slate-900 dark:text-slate-50">
                    Item Categories
                </h1>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-2xl space-y-6 sm:px-6 lg:px-8">
                <div
                    v-if="$page.props.flash?.success"
                    class="rounded-xl border border-emerald-200/50 bg-emerald-50/70 p-4 text-sm text-emerald-800 dark:border-emerald-900/30 dark:bg-emerald-950/20 dark:text-emerald-200"
                >
                    {{ $page.props.flash.success }}
                </div>
                <section class="rounded-xl border border-teal-200/60 bg-gradient-to-br from-teal-50/80 via-white to-cyan-50/80 p-6 shadow-md dark:border-teal-900/40 dark:from-teal-950/40 dark:via-slate-900 dark:to-cyan-950/30">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-50 mb-4">Add new category</h2>
                    <form class="space-y-4" @submit.prevent="submit">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Name *</label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full rounded-md border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-teal-500 focus:ring-teal-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-50"
                                required
                            />
                            <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Description</label>
                            <textarea
                                v-model="form.description"
                                rows="2"
                                class="mt-1 block w-full rounded-md border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-teal-500 focus:ring-teal-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-50"
                            />
                        </div>
                        <div class="flex justify-end">
                            <button
                                type="submit"
                                class="inline-flex items-center gap-2 rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-teal-500 dark:hover:bg-teal-600"
                                :disabled="form.processing"
                            >
                                <i class="fa-solid fa-plus text-xs"></i>
                                Add category
                            </button>
                        </div>
                    </form>
                </section>
                <section class="rounded-xl border border-slate-200/60 bg-white/80 p-6 shadow-md dark:border-slate-700/50 dark:bg-slate-900/70">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-50 mb-4">Existing categories</h2>
                    <ul class="divide-y divide-slate-200 text-sm dark:divide-slate-700">
                        <li
                            v-for="cat in categories"
                            :key="cat.id"
                            class="py-3 flex items-center justify-between"
                        >
                            <span :class="{ 'opacity-50 line-through': cat.is_active === false }">{{ cat.name }}</span>
                            <span v-if="cat.description" class="text-xs text-slate-500 dark:text-slate-400 truncate max-w-xs">{{ cat.description }}</span>
                        </li>
                        <li v-if="!categories?.length" class="py-4 text-center text-slate-500 dark:text-slate-400">
                            No categories yet.
                        </li>
                    </ul>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
