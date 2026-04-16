<script setup>
import { ref, computed } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'

const props = defineProps({
    step: { type: Number, default: 1 },
    token: String,
    email: String,
    first_name: { type: String, default: '' },
    last_name: { type: String, default: '' },
    expires_at: String,
    user: { type: Object, default: null },
})

// ----- Step 1: account details -----
const step1Form = useForm({
    first_name: props.first_name || '',
    last_name: props.last_name || '',
    password: '',
    password_confirmation: '',
})

const submitStep1 = () => {
    step1Form.post(route('invite.accept', props.token))
}

// ----- Step 2: pick a 2FA method -----
const method = ref('totp') // 'totp' | 'email' | 'passkey'
const supportsPasskeys = typeof window !== 'undefined' && window.browserSupportsWebAuthn?.()
const setMethod = (m) => { method.value = m }

// TOTP
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

// Email OTP
const emailEnableForm = useForm({})
const enableEmail = () => {
    emailEnableForm.post(route('two-factor.setup.email.enable'), {
        preserveScroll: true,
        onSuccess: () => router.visit(route('dashboard')),
    })
}

// Passkey
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
            onSuccess: () => router.visit(route('dashboard')),
            onError: () => { passkeyError.value = 'Failed to register passkey.' },
        })
    } catch (e) {
        passkeyError.value = e.message || 'Passkey registration failed.'
    } finally {
        passkeyLoading.value = false
    }
}

const finishAfterRecovery = () => router.visit(route('dashboard'))

const currentStep = computed(() => props.step)
</script>

<template>
    <Head :title="currentStep === 1 ? 'Join QR Lab — Step 1' : 'Join QR Lab — Step 2'" />
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-900 via-gray-800 to-primary-900 px-4 py-8">
        <div class="w-full" :class="currentStep === 1 ? 'max-w-sm' : 'max-w-lg'">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-primary-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-primary-600/30">
                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                </div>
                <h1 class="text-2xl font-bold text-white">Join QR Lab</h1>
                <p class="text-gray-400 mt-1 text-sm">
                    <template v-if="currentStep === 1">Create your account for {{ email }}</template>
                    <template v-else>Welcome {{ user?.name }} — let's secure your account</template>
                </p>
            </div>

            <!-- Step indicator -->
            <div class="flex items-center gap-3 mb-5">
                <div class="flex items-center gap-2 flex-1">
                    <div :class="currentStep >= 1 ? 'bg-primary-600 text-white' : 'bg-gray-700 text-gray-400'"
                        class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold shrink-0">
                        <svg v-if="currentStep > 1" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        <span v-else>1</span>
                    </div>
                    <span :class="currentStep === 1 ? 'text-white' : 'text-gray-400'" class="text-xs font-medium">Your info</span>
                </div>
                <div class="h-px flex-1" :class="currentStep > 1 ? 'bg-primary-600' : 'bg-gray-600'"></div>
                <div class="flex items-center gap-2 flex-1 justify-end">
                    <span :class="currentStep === 2 ? 'text-white' : 'text-gray-500'" class="text-xs font-medium">Secure account</span>
                    <div :class="currentStep === 2 ? 'bg-primary-600 text-white' : 'bg-gray-700 text-gray-400'"
                        class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold shrink-0">2</div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl p-6 border border-gray-200 dark:border-gray-800">
                <!-- STEP 1: Account details -->
                <form v-if="currentStep === 1" @submit.prevent="submitStep1" class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">First Name</label>
                            <input v-model="step1Form.first_name" type="text" required autofocus
                                class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                            <p v-if="step1Form.errors.first_name" class="text-red-500 text-xs mt-1">{{ step1Form.errors.first_name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Last Name <span class="text-gray-400 font-normal">(optional)</span></label>
                            <input v-model="step1Form.last_name" type="text"
                                class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                        <input v-model="step1Form.password" type="password" required autocomplete="new-password"
                            class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                        <p v-if="step1Form.errors.password" class="text-red-500 text-xs mt-1">{{ step1Form.errors.password }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confirm Password</label>
                        <input v-model="step1Form.password_confirmation" type="password" required autocomplete="new-password"
                            class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                    </div>

                    <button type="submit" :disabled="step1Form.processing"
                        class="w-full px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-colors disabled:opacity-50 text-sm flex items-center justify-center gap-2">
                        {{ step1Form.processing ? 'Creating account...' : 'Continue to step 2' }}
                        <svg v-if="!step1Form.processing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </form>

                <!-- STEP 2: 2FA setup -->
                <div v-else-if="currentStep === 2" class="space-y-5">
                    <!-- Recovery codes after TOTP confirm -->
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
                            I've saved my codes — finish setup
                        </button>
                    </div>

                    <template v-else>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Pick at least one two-factor method. You'll use this every time you sign in.
                        </p>

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
                                        {{ totpConfirmForm.processing ? 'Verifying...' : 'Confirm & finish setup' }}
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Email -->
                        <div v-else-if="method === 'email'" class="space-y-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                We'll email a 6-digit code to <span class="font-medium text-gray-900 dark:text-white">{{ user?.email }}</span> every time you sign in.
                            </p>
                            <button @click="enableEmail" :disabled="emailEnableForm.processing"
                                class="w-full px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-colors disabled:opacity-50 text-sm">
                                {{ emailEnableForm.processing ? 'Enabling...' : 'Enable email verification & finish' }}
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
                                {{ passkeyLoading ? 'Registering...' : 'Register passkey & finish' }}
                            </button>
                            <p v-if="passkeyError" class="text-red-400 text-xs text-center">{{ passkeyError }}</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>
