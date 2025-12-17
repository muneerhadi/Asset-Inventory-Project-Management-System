<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { resolveImage } from '@/utils/imageResolver';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
    profile_photo: null,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'patch',
    })).post(route('profile.update'), {
        forceFormData: true,
        onFinish: () => {
            form.transform((data) => data);
            form.reset('profile_photo');
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Profile Information
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Update your account's profile information and email address.
            </p>
        </header>

        <form
            @submit.prevent="submit"
            class="mt-6 space-y-6"
        >
            <div>
                <InputLabel for="name" value="Name" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-gray-800">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div>
                <InputLabel value="Profile photo" />

                <div class="mt-2 flex items-center gap-4">
                    <div>
                        <div
                            v-if="user.profile_photo_path"
                            class="inline-flex h-12 w-12 items-center justify-center overflow-hidden rounded-full bg-gray-200"
                        >
                            <img
                                :src="resolveImage(user.profile_photo_path)"
                                alt="Profile photo"
                                class="h-12 w-12 rounded-full object-cover"
                            />
                        </div>
                        <div
                            v-else
                            class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-gray-200 text-xs font-medium text-gray-700"
                        >
                            {{ user.name.charAt(0) }}
                        </div>
                    </div>

                    <label
                        class="cursor-pointer rounded-md border border-dashed border-gray-300 px-3 py-2 text-xs text-gray-600 hover:border-indigo-400 hover:text-indigo-600"
                    >
                        <span>Click to upload or drag & drop</span>
                        <input
                            type="file"
                            class="hidden"
                            accept="image/*"
                            @change="form.profile_photo = $event.target.files[0]"
                        />
                    </label>
                </div>

                <InputError class="mt-2" :message="form.errors.profile_photo" />
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600"
                    >
                        Saved.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
