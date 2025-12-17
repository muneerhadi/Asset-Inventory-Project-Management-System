<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

// Simple logo path from storage - update this path to match your actual logo file
const logoUrl = '/storage/logo/union-aid-logo.png';

const showPassword = ref(false);
const pageLoaded = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

onMounted(() => {
    // Trigger a simple entrance animation when the page is ready
    requestAnimationFrame(() => {
        pageLoaded.value = true;
    });
});
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div class="auth-page" :class="{ 'is-loaded': pageLoaded }">
            <div class="auth-card">
                <div class="auth-header">
                    <img
                        :src="logoUrl"
                        alt="UNION AID logo"
                        class="auth-logo"
                        loading="lazy"
                    />
                    <h1 class="auth-title">UNION AID</h1>
                    <p class="auth-subtitle">Asset Inventory &amp; Project Management System</p>
                </div>

                <div v-if="status" class="auth-status">
                    {{ status }}
                </div>

                <form class="auth-form" @submit.prevent="submit">
                    <div class="auth-field">
                        <label for="email" class="auth-label">Email</label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="auth-input"
                            required
                            autofocus
                            autocomplete="username"
                        />
                        <p v-if="form.errors.email" class="auth-error">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div class="auth-field">
                        <label for="password" class="auth-label">Password</label>
                        <div class="auth-password-wrapper">
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                class="auth-input auth-input-password"
                                required
                                autocomplete="current-password"
                            />
                            <button
                                type="button"
                                class="auth-show-password"
                                @click="showPassword = !showPassword"
                            >
                                {{ showPassword ? 'Hide' : 'Show' }}
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="auth-error">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <div class="auth-remember-row">
                        <label class="auth-remember">
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                name="remember"
                                class="auth-checkbox"
                            />
                            <span>Remember me</span>
                        </label>

                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="auth-link"
                        >
                            Forgot password?
                        </Link>
                    </div>

                    <button
                        type="submit"
                        class="auth-button"
                        :disabled="form.processing"
                    >
                        Log in
                    </button>
                </form>
            </div>
        </div>
    </GuestLayout>
</template>

<style scoped>
.auth-page {
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    background: radial-gradient(circle at top left, #1e3a8a, #0f172a);
    background-attachment: fixed;
    box-sizing: border-box;
    overflow: hidden;
}

.auth-card {
    width: 100%;
    max-width: 420px;
    background: rgba(255, 255, 255, 0.98);
    border-radius: 16px;
    padding: 28px 24px 24px;
    box-shadow: 0 22px 45px rgba(15, 23, 42, 0.35);
    border: 1px solid rgba(148, 163, 184, 0.35);
    box-sizing: border-box;
    opacity: 0;
    transform: translateY(16px) scale(0.98);
    transition: opacity 0.4s ease, transform 0.4s ease;
}

.auth-page.is-loaded .auth-card {
    opacity: 1;
    transform: translateY(0) scale(1);
}

.auth-header {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    margin-bottom: 20px;
}

.auth-logo {
    display: block;
    margin: 0 auto 10px;
    width: 84px;
    height: 84px;
    border-radius: 50%;
    object-fit: cover;
    box-shadow: 0 10px 24px rgba(30, 64, 175, 0.5);
}

.auth-title {
    margin: 0;
    font-size: 22px;
    font-weight: 700;
    letter-spacing: 0.04em;
    color: #0f172a;
}

.auth-subtitle {
    margin: 4px 0 0;
    font-size: 12px;
    color: #64748b;
}

.auth-status {
    margin-bottom: 12px;
    padding: 8px 10px;
    border-radius: 8px;
    background: rgba(22, 163, 74, 0.08);
    border: 1px solid rgba(22, 163, 74, 0.35);
    color: #166534;
    font-size: 12px;
}

.auth-form {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.auth-field {
    display: flex;
    flex-direction: column;
}

.auth-label {
    font-size: 12px;
    font-weight: 500;
    color: #0f172a;
    margin-bottom: 4px;
}

.auth-input {
    width: 100%;
    padding: 9px 10px;
    border-radius: 8px;
    border: 1px solid #cbd5f5;
    font-size: 13px;
    color: #0f172a;
    background-color: #f9fafb;
    outline: none;
    transition: border-color 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
}

.auth-input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 1px rgba(37, 99, 235, 0.3);
    background-color: #ffffff;
}

.auth-input::placeholder {
    color: #9ca3af;
}

.auth-password-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.auth-input-password {
    padding-right: 60px;
}

.auth-show-password {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    border: none;
    background: transparent;
    color: #2563eb;
    font-size: 11px;
    font-weight: 500;
    cursor: pointer;
    padding: 4px 6px;
    border-radius: 999px;
    transition: background-color 0.18s ease, color 0.18s ease;
}

.auth-show-password:hover {
    background-color: rgba(37, 99, 235, 0.05);
    color: #1d4ed8;
}

.auth-error {
    margin-top: 4px;
    font-size: 11px;
    color: #b91c1c;
}

.auth-remember-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-top: 4px;
}

.auth-remember {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #0f172a;
}

.auth-checkbox {
    width: 14px;
    height: 14px;
    border-radius: 3px;
    border: 1px solid #cbd5f5;
}

.auth-link {
    font-size: 12px;
    color: #2563eb;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.18s ease;
}

.auth-link:hover {
    color: #1d4ed8;
}

.auth-button {
    margin-top: 10px;
    width: 100%;
    padding: 9px 12px;
    border-radius: 999px;
    border: none;
    background: linear-gradient(135deg, #1d4ed8, #0ea5e9);
    color: #ffffff;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 0.03em;
    cursor: pointer;
    box-shadow: 0 16px 32px rgba(37, 99, 235, 0.4);
    transition: transform 0.18s ease, box-shadow 0.18s ease, opacity 0.18s ease;
}

.auth-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 20px 40px rgba(37, 99, 235, 0.45);
}

.auth-button:active {
    transform: translateY(0);
    box-shadow: 0 14px 26px rgba(37, 99, 235, 0.35);
}

.auth-button:disabled {
    opacity: 0.6;
    cursor: default;
    box-shadow: none;
}

@media (max-width: 480px) {
    .auth-card {
        padding: 22px 18px 18px;
    }

    .auth-title {
        font-size: 20px;
    }

    .auth-subtitle {
        font-size: 11px;
    }
}
</style>
