<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'

const page = usePage()

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const passkeyLoading = ref(false)
const passkeyError = ref('')
const supportsPasskeys = typeof window !== 'undefined' && window.browserSupportsWebAuthn?.()

const login = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    })
}

const loginWithPasskey = async () => {
    passkeyLoading.value = true
    passkeyError.value = ''

    try {
        const optionsRes = await fetch(route('passkeys.authentication_options'))
        const options = await optionsRes.json()

        const assertion = await window.startAuthentication({ optionsJSON: options })

        const passkeyForm = useForm({
            start_authentication_response: JSON.stringify(assertion),
        })

        passkeyForm.post(route('passkeys.login'), {
            onError: () => {
                passkeyError.value = 'Passkey authentication failed. Please try again.'
            },
        })
    } catch (e) {
        if (e.name === 'NotAllowedError') {
            passkeyError.value = 'Passkey authentication was cancelled.'
        } else {
            passkeyError.value = e.message || 'Passkey authentication failed.'
        }
    } finally {
        passkeyLoading.value = false
    }
}

onMounted(() => {
    if (supportsPasskeys) {
        loginWithPasskey()
    }
})
</script>

<template>
    <Head title="Login" />
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-900 via-gray-800 to-primary-900 px-4">
        <div class="w-full max-w-sm">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-primary-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-primary-600/30">
                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                </div>
                <h1 class="text-2xl font-bold text-white">QR Lab</h1>
                <p class="text-gray-400 mt-1 text-sm">Sign in to manage your QR codes</p>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl p-6 space-y-5 border border-gray-200 dark:border-gray-800">
                <!-- Passkey login -->
                <div v-if="supportsPasskeys">
                    <button @click="loginWithPasskey" :disabled="passkeyLoading"
                        class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-colors disabled:opacity-50 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"/></svg>
                        {{ passkeyLoading ? 'Authenticating...' : 'Sign in with Passkey' }}
                    </button>
                    <p v-if="passkeyError" class="text-red-400 text-xs mt-2 text-center">{{ passkeyError }}</p>

                    <div class="relative mt-5">
                        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200 dark:border-gray-700"></div></div>
                        <div class="relative flex justify-center text-xs"><span class="bg-white dark:bg-gray-900 px-3 text-gray-400">or sign in with password</span></div>
                    </div>
                </div>

                <!-- Password login -->
                <form @submit.prevent="login" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                        <input v-model="form.email" type="email" required autofocus autocomplete="username"
                            class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                        <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                        <input v-model="form.password" type="password" required autocomplete="current-password"
                            class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                        <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
                    </div>

                    <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <input v-model="form.remember" type="checkbox" class="rounded border-gray-300 dark:border-gray-700 text-primary-600 focus:ring-primary-500" />
                        Remember me
                    </label>

                    <button type="submit" :disabled="form.processing"
                        class="w-full px-4 py-2.5 bg-gray-900 dark:bg-gray-100 hover:bg-gray-800 dark:hover:bg-white text-white dark:text-gray-900 font-semibold rounded-xl transition-colors disabled:opacity-50 text-sm">
                        {{ form.processing ? 'Signing in...' : 'Sign in' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
