<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, useForm, router, Link } from '@inertiajs/vue3'

const props = defineProps({
    user: Object,
})

const supportsPasskeys = typeof window !== 'undefined' && window.browserSupportsWebAuthn?.()

const method = ref('totp') // 'totp' | 'email' | 'passkey'

const setMethod = (m) => {
    method.value = m
}

// ---- TOTP ----
const totpSetup = ref(null)
const totpLoading = ref(false)
const totpConfirmForm = useForm({ code: '' })
const recoveryCodes = ref([])

const startTotp = async () => {
    totpLoading.value = true
    try {
        const res = await fetch(route('two-factor.setup.totp.init'), {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        })
        totpSetup.value = await res.json()
    } finally {
        totpLoading.value = false
    }
}

const confirmTotp = () => {
    totpConfirmForm.post(route('two-factor.setup.totp.confirm'), {
        preserveScroll: true,
        onSuccess: (page) => {
            if (page.props.flash?.recovery_codes) {
                recoveryCodes.value = page.props.flash.recovery_codes
            }
        },
    })
}

// ---- Email ----
const emailEnableForm = useForm({})
const enableEmail = () => {
    emailEnableForm.post(route('two-factor.setup.email.enable'), {
        preserveScroll: true,
        onSuccess: () => {
            router.visit(route('dashboard'))
        },
    })
}

// ---- Passkey ----
const passkeyName = ref('')
const passkeyLoading = ref(false)
const passkeyError = ref('')

const registerPasskey = async () => {
    passkeyLoading.value = true
    passkeyError.value = ''

    try {
        const optionsRes = await fetch(route('passkeys.register-options'))
        const options = await optionsRes.json()

        const credential = await window.startRegistration({ optionsJSON: options })

        const form = useForm({
            passkey: JSON.stringify(credential),
            options: JSON.stringify(options),
            name: passkeyName.value || undefined,
        })

        form.post(route('passkeys.store'), {
            onSuccess: () => {
                router.visit(route('dashboard'))
            },
            onError: () => {
                passkeyError.value = 'Failed to register passkey.'
            },
        })
    } catch (e) {
        passkeyError.value = e.message || 'Passkey registration failed.'
    } finally {
        passkeyLoading.value = false
    }
}

const finishAfterRecovery = () => {
    router.visit(route('dashboard'))
}
</script>

<template>
    <Head title="Set up two-factor authentication" />
    <div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-gray-900 via-gray-800 to-primary-900 px-4 py-8">
        <div class="w-full max-w-lg flex-1 flex flex-col justify-center">
            <div class="mb-5 text-center">
                <div class="w-12 h-12 bg-primary-600 rounded-2xl flex items-center justify-center shadow-lg shadow-primary-600/30 mx-auto mb-3">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <h1 class="text-2xl font-bold text-white">Secure your account</h1>
                <p class="text-gray-300 text-sm mt-2">
                    Pick at least one method below — you can always add more later from your profile.
                </p>
            </div>

            <!-- Step indicator -->
            <div class="flex items-center gap-3 mb-5 max-w-sm mx-auto w-full">
                <div class="flex items-center gap-2 flex-1">
                    <div class="w-7 h-7 rounded-full bg-primary-600/40 text-white flex items-center justify-center text-xs font-semibold shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="text-xs font-medium text-gray-400">Your info</span>
                </div>
                <div class="h-px flex-1 bg-primary-600"></div>
                <div class="flex items-center gap-2 flex-1 justify-end">
                    <span class="text-xs font-medium text-white">Secure account</span>
                    <div class="w-7 h-7 rounded-full bg-primary-600 text-white flex items-center justify-center text-xs font-semibold shrink-0">2</div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl p-6 space-y-5 border border-gray-200 dark:border-gray-800">
                <!-- Recovery codes shown after TOTP confirm -->
                <div v-if="recoveryCodes.length" class="p-4 border border-yellow-300 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/20 rounded-xl">
                    <h3 class="text-sm font-semibold text-yellow-900 dark:text-yellow-200 mb-2">Save your recovery codes</h3>
                    <p class="text-xs text-yellow-800 dark:text-yellow-300 mb-3">
                        Store these somewhere safe. Each can be used once if you lose access to your authenticator.
                    </p>
                    <div class="grid grid-cols-2 gap-2 font-mono text-sm text-gray-900 dark:text-white mb-3">
                        <span v-for="c in recoveryCodes" :key="c" class="bg-white dark:bg-gray-800 px-2 py-1 rounded">{{ c }}</span>
                    </div>
                    <button @click="finishAfterRecovery"
                        class="w-full px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl text-sm">
                        I've saved my codes — continue
                    </button>
                </div>

                <template v-else>
                    <!-- Method tabs -->
                    <div class="flex gap-2">
                        <button type="button" @click="setMethod('totp')"
                            :class="method === 'totp' ? 'bg-primary-50 dark:bg-primary-900/20 border-primary-300 dark:border-primary-700 text-primary-700 dark:text-primary-400' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400'"
                            class="flex-1 px-3 py-2 border rounded-xl text-xs font-medium transition-colors">
                            Authenticator
                        </button>
                        <button type="button" @click="setMethod('email')"
                            :class="method === 'email' ? 'bg-primary-50 dark:bg-primary-900/20 border-primary-300 dark:border-primary-700 text-primary-700 dark:text-primary-400' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400'"
                            class="flex-1 px-3 py-2 border rounded-xl text-xs font-medium transition-colors">
                            Email
                        </button>
                        <button v-if="supportsPasskeys" type="button" @click="setMethod('passkey')"
                            :class="method === 'passkey' ? 'bg-primary-50 dark:bg-primary-900/20 border-primary-300 dark:border-primary-700 text-primary-700 dark:text-primary-400' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400'"
                            class="flex-1 px-3 py-2 border rounded-xl text-xs font-medium transition-colors">
                            Passkey
                        </button>
                    </div>

                    <!-- TOTP -->
                    <div v-if="method === 'totp'" class="space-y-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Use an app like Google Authenticator, 1Password, or Authy. You'll enter a 6-digit code each time you sign in.
                        </p>
                        <button v-if="!totpSetup" @click="startTotp" :disabled="totpLoading"
                            class="w-full px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-colors disabled:opacity-50 text-sm">
                            {{ totpLoading ? 'Loading...' : 'Start setup' }}
                        </button>

                        <div v-else class="space-y-3">
                            <p class="text-sm text-gray-700 dark:text-gray-300">1. Scan this QR code with your authenticator app:</p>
                            <div class="flex justify-center p-3 bg-white rounded-xl" v-html="totpSetup.qr_svg"></div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Or enter this secret manually:
                                <code class="font-mono text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded ml-1 break-all">{{ totpSetup.secret }}</code>
                            </p>
                            <form @submit.prevent="confirmTotp" class="space-y-3">
                                <p class="text-sm text-gray-700 dark:text-gray-300">2. Enter the 6-digit code from your app:</p>
                                <input v-model="totpConfirmForm.code" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="6" required placeholder="123456"
                                    class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-center tracking-widest font-mono focus:ring-2 focus:ring-primary-500 outline-none" />
                                <p v-if="totpConfirmForm.errors.code" class="text-red-500 text-xs">{{ totpConfirmForm.errors.code }}</p>
                                <button type="submit" :disabled="totpConfirmForm.processing"
                                    class="w-full px-4 py-2.5 bg-gray-900 dark:bg-gray-100 hover:bg-gray-800 dark:hover:bg-white text-white dark:text-gray-900 font-semibold rounded-xl transition-colors disabled:opacity-50 text-sm">
                                    {{ totpConfirmForm.processing ? 'Verifying...' : 'Confirm & Enable' }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Email -->
                    <div v-else-if="method === 'email'" class="space-y-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            We'll email a 6-digit code to <span class="font-medium text-gray-900 dark:text-white">{{ user.email }}</span> every time you sign in.
                        </p>
                        <button @click="enableEmail" :disabled="emailEnableForm.processing"
                            class="w-full px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-colors disabled:opacity-50 text-sm">
                            {{ emailEnableForm.processing ? 'Enabling...' : 'Enable email verification' }}
                        </button>
                    </div>

                    <!-- Passkey -->
                    <div v-else-if="method === 'passkey'" class="space-y-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Register a passkey using your device's biometrics or a hardware security key. Most convenient and most secure.
                        </p>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Passkey name (optional)</label>
                            <input v-model="passkeyName" type="text" placeholder="e.g. My MacBook"
                                class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 outline-none" />
                        </div>
                        <button @click="registerPasskey" :disabled="passkeyLoading"
                            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-colors disabled:opacity-50 shadow-sm text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"/></svg>
                            {{ passkeyLoading ? 'Registering...' : 'Register passkey' }}
                        </button>
                        <p v-if="passkeyError" class="text-red-400 text-xs text-center">{{ passkeyError }}</p>
                    </div>
                </template>
            </div>

            <div class="text-center mt-4">
                <Link :href="route('logout')" method="post" as="button"
                    class="text-xs text-gray-400 hover:text-gray-200">
                    Sign out
                </Link>
            </div>
        </div>
    </div>
</template>
