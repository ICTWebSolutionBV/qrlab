<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3'

defineProps({
    status: String,
})

const form = useForm({ email: '' })

const submit = () => {
    form.post(route('password.email'))
}
</script>

<template>
    <Head title="Forgot Password" />
    <div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-gray-900 via-gray-800 to-primary-900 px-4">
        <div class="w-full max-w-sm">
            <div class="mb-8 text-center">
                <h1 class="text-2xl font-bold text-white">Reset your password</h1>
                <p class="text-gray-400 text-sm mt-2">Enter your email and we'll send you a reset link.</p>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl p-6 border border-gray-200 dark:border-gray-800">
                <p v-if="status" class="text-green-600 dark:text-green-400 text-sm mb-4">{{ status }}</p>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                        <input v-model="form.email" type="email" required autofocus
                            class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 outline-none" />
                        <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                    </div>
                    <button type="submit" :disabled="form.processing"
                        class="w-full px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl disabled:opacity-50 text-sm">
                        {{ form.processing ? 'Sending...' : 'Send reset link' }}
                    </button>
                </form>

                <div class="text-center mt-4">
                    <Link :href="route('login')" class="text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                        Back to sign in
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
