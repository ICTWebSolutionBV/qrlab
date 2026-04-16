<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, useForm, Link } from '@inertiajs/vue3'

const props = defineProps({
    methods: Object,
    emailMasked: String,
})

const defaultMethod = computed(() => {
    if (props.methods.totp) return 'totp'
    if (props.methods.email) return 'email'
    if (props.methods.passkey) return 'passkey'
    return 'totp'
})

const method = ref(defaultMethod.value)

const form = useForm({
    method: method.value,
    code: '',
})

const emailForm = useForm({})
const emailSent = ref(false)
const emailSending = ref(false)

const passkeyLoading = ref(false)
const passkeyError = ref('')
const supportsPasskeys = typeof window !== 'undefined' && window.browserSupportsWebAuthn?.()

const setMethod = (m) => {
    method.value = m
    form.method = m
    form.clearErrors()
}

const submit = () => {
    form.method = method.value
    form.post(route('two-factor.verify'))
}

const sendEmailCode = () => {
    emailSending.value = true
    emailForm.post(route('two-factor.email.send'), {
        preserveScroll: true,
        onSuccess: () => { emailSent.value = true },
        onFinish: () => { emailSending.value = false },
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
    if (method.value === 'email' && props.methods.email) {
        sendEmailCode()
    }
})
</script>

<template>
    <Head title="Two-Factor Verification" />
    <div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-gray-900 via-gray-800 to-primary-900 px-4">
        <div class="w-full max-w-sm flex-1 flex flex-col justify-center">
            <div class="mb-8 text-center">
                <div class="w-12 h-12 bg-primary-600 rounded-2xl flex items-center justify-center shadow-lg shadow-primary-600/30 mx-auto mb-3">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <h1 class="text-2xl font-bold text-white">Verify your identity</h1>
                <p class="text-gray-400 text-sm mt-1">Choose a verification method</p>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl p-6 space-y-5 border border-gray-200 dark:border-gray-800">
                <!-- Method selector -->
                <div class="flex gap-2">
                    <button v-if="methods.totp" type="button" @click="setMethod('totp')"
                        :class="method === 'totp' ? 'bg-primary-50 dark:bg-primary-900/20 border-primary-300 dark:border-primary-700 text-primary-700 dark:text-primary-400' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400'"
                        class="flex-1 px-3 py-2 border rounded-xl text-xs font-medium transition-colors">
                        Authenticator
                    </button>
                    <button v-if="methods.email" type="button" @click="setMethod('email')"
                        :class="method === 'email' ? 'bg-primary-50 dark:bg-primary-900/20 border-primary-300 dark:border-primary-700 text-primary-700 dark:text-primary-400' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400'"
                        class="flex-1 px-3 py-2 border rounded-xl text-xs font-medium transition-colors">
                        Email
                    </button>
                    <button v-if="methods.passkey && supportsPasskeys" type="button" @click="setMethod('passkey')"
                        :class="method === 'passkey' ? 'bg-primary-50 dark:bg-primary-900/20 border-primary-300 dark:border-primary-700 text-primary-700 dark:text-primary-400' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400'"
                        class="flex-1 px-3 py-2 border rounded-xl text-xs font-medium transition-colors">
                        Passkey
                    </button>
                </div>

                <!-- TOTP -->
                <form v-if="method === 'totp'" @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Authenticator code</label>
                        <input v-model="form.code" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="6" autofocus required
                            placeholder="123456"
                            class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-center tracking-widest text-lg focus:ring-2 focus:ring-primary-500 outline-none transition" />
                        <p v-if="form.errors.code" class="text-red-500 text-xs mt-1">{{ form.errors.code }}</p>
                    </div>
                    <button type="submit" :disabled="form.processing"
                        class="w-full px-4 py-2.5 bg-gray-900 dark:bg-gray-100 hover:bg-gray-800 dark:hover:bg-white text-white dark:text-gray-900 font-semibold rounded-xl transition-colors disabled:opacity-50 text-sm">
                        {{ form.processing ? 'Verifying...' : 'Verify' }}
                    </button>
                    <button type="button" @click="setMethod('recovery')" class="text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 w-full text-center">
                        Use a recovery code
                    </button>
                </form>

                <!-- Email -->
                <form v-else-if="method === 'email'" @submit.prevent="submit" class="space-y-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        We sent a code to <span class="font-medium text-gray-900 dark:text-white">{{ emailMasked }}</span>.
                    </p>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Verification code</label>
                        <input v-model="form.code" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="6" autofocus required
                            placeholder="123456"
                            class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-center tracking-widest text-lg focus:ring-2 focus:ring-primary-500 outline-none transition" />
                        <p v-if="form.errors.code" class="text-red-500 text-xs mt-1">{{ form.errors.code }}</p>
                    </div>
                    <button type="submit" :disabled="form.processing"
                        class="w-full px-4 py-2.5 bg-gray-900 dark:bg-gray-100 hover:bg-gray-800 dark:hover:bg-white text-white dark:text-gray-900 font-semibold rounded-xl transition-colors disabled:opacity-50 text-sm">
                        {{ form.processing ? 'Verifying...' : 'Verify' }}
                    </button>
                    <button type="button" @click="sendEmailCode" :disabled="emailSending"
                        class="text-xs text-primary-600 dark:text-primary-400 hover:underline w-full text-center">
                        {{ emailSending ? 'Sending...' : 'Resend code' }}
                    </button>
                </form>

                <!-- Passkey -->
                <div v-else-if="method === 'passkey'" class="space-y-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Use your device's biometrics or security key to sign in.
                    </p>
                    <button @click="loginWithPasskey" :disabled="passkeyLoading"
                        class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-colors disabled:opacity-50 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"/></svg>
                        {{ passkeyLoading ? 'Authenticating...' : 'Authenticate with Passkey' }}
                    </button>
                    <p v-if="passkeyError" class="text-red-400 text-xs text-center">{{ passkeyError }}</p>
                </div>

                <!-- Recovery -->
                <form v-else-if="method === 'recovery'" @submit.prevent="submit" class="space-y-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Enter one of your saved recovery codes. Each code can only be used once.
                    </p>
                    <input type="hidden" name="method" value="recovery" />
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Recovery code</label>
                        <input v-model="form.code" type="text" autofocus required
                            placeholder="xxxxx-xxxxx"
                            class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 outline-none transition" />
                        <p v-if="form.errors.code" class="text-red-500 text-xs mt-1">{{ form.errors.code }}</p>
                    </div>
                    <button type="submit" :disabled="form.processing" @click="form.method = 'recovery'"
                        class="w-full px-4 py-2.5 bg-gray-900 dark:bg-gray-100 hover:bg-gray-800 dark:hover:bg-white text-white dark:text-gray-900 font-semibold rounded-xl transition-colors disabled:opacity-50 text-sm">
                        {{ form.processing ? 'Verifying...' : 'Verify' }}
                    </button>
                    <button type="button" @click="setMethod(defaultMethod)" class="text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 w-full text-center">
                        Back to verification methods
                    </button>
                </form>

                <Link :href="route('two-factor.cancel')" method="post" as="button"
                    class="block w-full text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 text-center mt-2">
                    Cancel and sign in with a different account
                </Link>
            </div>
        </div>
    </div>
</template>
