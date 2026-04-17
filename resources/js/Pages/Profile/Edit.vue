<script setup>
import { ref } from 'vue'
import { Head, useForm, usePage, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { formatDateTime } from '@/utils/datetime.js'
import { computed } from 'vue'

const props = defineProps({
    user: Object,
})

const page = usePage()
const passkeys = page.props.auth?.user?.passkeys || []

const profileForm = useForm({
    name: props.user.name,
    email: props.user.email,
})

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
})

const themeForm = useForm({
    theme_preference: props.user.theme_preference,
})

// Date & time preferences
const dateTimeForm = useForm({
    timezone: props.user.timezone || 'Europe/Amsterdam',
    date_format: props.user.date_format || 'DD-MM-YYYY',
    time_format: props.user.time_format || 'HH:mm:ss',
})

const commonTimezones = [
    'Europe/Amsterdam',
    'Europe/London',
    'Europe/Berlin',
    'Europe/Paris',
    'Europe/Madrid',
    'Europe/Rome',
    'Europe/Stockholm',
    'Europe/Warsaw',
    'Europe/Moscow',
    'UTC',
    'America/New_York',
    'America/Chicago',
    'America/Denver',
    'America/Los_Angeles',
    'America/Toronto',
    'America/Sao_Paulo',
    'Asia/Dubai',
    'Asia/Kolkata',
    'Asia/Singapore',
    'Asia/Hong_Kong',
    'Asia/Shanghai',
    'Asia/Tokyo',
    'Australia/Sydney',
    'Pacific/Auckland',
]

const dateFormatOptions = [
    { value: 'DD-MM-YYYY', label: 'DD-MM-YYYY (17-04-2026)' },
    { value: 'DD/MM/YYYY', label: 'DD/MM/YYYY (17/04/2026)' },
    { value: 'MM/DD/YYYY', label: 'MM/DD/YYYY (04/17/2026)' },
    { value: 'YYYY-MM-DD', label: 'YYYY-MM-DD (2026-04-17)' },
    { value: 'D MMM YYYY', label: 'D MMM YYYY (17 Apr 2026)' },
    { value: 'MMM D YYYY', label: 'MMM D YYYY (Apr 17 2026)' },
]

const timeFormatOptions = [
    { value: 'HH:mm:ss', label: '24h with seconds (14:30:45)' },
    { value: 'HH:mm', label: '24h (14:30)' },
    { value: 'hh:mm:ss A', label: '12h with seconds (02:30:45 PM)' },
    { value: 'hh:mm A', label: '12h (02:30 PM)' },
]

const dateTimePreview = computed(() => formatDateTime(new Date().toISOString(), {
    timezone: dateTimeForm.timezone,
    dateFormat: dateTimeForm.date_format,
    timeFormat: dateTimeForm.time_format,
}))

const updateDateTime = () => {
    dateTimeForm.put(route('profile.datetime'), {
        preserveScroll: true,
        onSuccess: () => router.reload({ only: ['auth'] }),
    })
}

const passkeyName = ref('')
const passkeyLoading = ref(false)
const passkeyError = ref('')

// --- Two-Factor state ---
const twoFactor = ref({
    totp_enabled: props.user.two_factor?.totp_enabled || false,
    email_enabled: props.user.two_factor?.email_enabled || false,
    passkeys_enabled: props.user.two_factor?.passkeys_enabled || false,
})

// TOTP setup
const totpSetup = ref(null) // { secret, qr_svg }
const totpLoading = ref(false)
const totpConfirmForm = useForm({ code: '' })
const totpDisableForm = useForm({ password: '' })
const showTotpDisable = ref(false)
const recoveryCodes = ref([])

const startTotpSetup = async () => {
    totpLoading.value = true
    try {
        const res = await fetch(route('two-factor.totp.setup'), {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        })
        totpSetup.value = await res.json()
    } catch (e) {
        totpSetup.value = null
    } finally {
        totpLoading.value = false
    }
}

const confirmTotp = () => {
    totpConfirmForm.post(route('two-factor.totp.confirm'), {
        preserveScroll: true,
        onSuccess: (page) => {
            twoFactor.value.totp_enabled = true
            totpSetup.value = null
            totpConfirmForm.reset()
            if (page.props.flash?.recovery_codes) {
                recoveryCodes.value = page.props.flash.recovery_codes
            }
            router.reload({ only: ['user'] })
        },
    })
}

const disableTotp = () => {
    totpDisableForm.post(route('two-factor.totp.disable'), {
        preserveScroll: true,
        onSuccess: () => {
            twoFactor.value.totp_enabled = false
            showTotpDisable.value = false
            totpDisableForm.reset()
            recoveryCodes.value = []
            router.reload({ only: ['user'] })
        },
    })
}

const regenRecoveryForm = useForm({})
const regenerateRecoveryCodes = () => {
    regenRecoveryForm.post(route('two-factor.recovery-codes.regenerate'), {
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
const emailDisableForm = useForm({ password: '' })
const showEmailDisable = ref(false)

const enableEmailOtp = () => {
    emailEnableForm.post(route('two-factor.email.enable'), {
        preserveScroll: true,
        onSuccess: () => {
            twoFactor.value.email_enabled = true
            router.reload({ only: ['user'] })
        },
    })
}

const disableEmailOtp = () => {
    emailDisableForm.post(route('two-factor.email.disable'), {
        preserveScroll: true,
        onSuccess: () => {
            twoFactor.value.email_enabled = false
            showEmailDisable.value = false
            emailDisableForm.reset()
            router.reload({ only: ['user'] })
        },
    })
}

const updateProfile = () => {
    profileForm.put(route('profile.update'))
}

const updatePassword = () => {
    passwordForm.put(route('profile.password'), {
        onSuccess: () => passwordForm.reset(),
    })
}

const updateTheme = (value) => {
    themeForm.theme_preference = value
    themeForm.put(route('profile.theme'))
}

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
                passkeyName.value = ''
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

const deletePasskey = (id) => {
    router.delete(route('passkeys.destroy', id))
}
</script>

<template>
    <Head title="Profile" />
    <AppLayout>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Profile</h1>

        <div class="space-y-6 max-w-2xl">
            <!-- Profile info -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Profile Information</h2>
                <form @submit.prevent="updateProfile" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                        <input v-model="profileForm.name" type="text" required
                            class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                        <p v-if="profileForm.errors.name" class="text-red-500 text-xs mt-1">{{ profileForm.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                        <input v-model="profileForm.email" type="email" required
                            class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                        <p v-if="profileForm.errors.email" class="text-red-500 text-xs mt-1">{{ profileForm.errors.email }}</p>
                    </div>
                    <button type="submit" :disabled="profileForm.processing"
                        class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors text-sm disabled:opacity-50">
                        Save
                    </button>
                </form>
            </div>

            <!-- Password -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Change Password</h2>
                <form @submit.prevent="updatePassword" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Current Password</label>
                        <input v-model="passwordForm.current_password" type="password" required
                            class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                        <p v-if="passwordForm.errors.current_password" class="text-red-500 text-xs mt-1">{{ passwordForm.errors.current_password }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">New Password</label>
                        <input v-model="passwordForm.password" type="password" required
                            class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                        <p v-if="passwordForm.errors.password" class="text-red-500 text-xs mt-1">{{ passwordForm.errors.password }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confirm New Password</label>
                        <input v-model="passwordForm.password_confirmation" type="password" required
                            class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                    </div>
                    <button type="submit" :disabled="passwordForm.processing"
                        class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors text-sm disabled:opacity-50">
                        Change Password
                    </button>
                </form>
            </div>

            <!-- Theme -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Appearance</h2>
                <div class="flex gap-3">
                    <button v-for="option in ['light', 'dark', 'auto']" :key="option"
                        @click="updateTheme(option)"
                        :class="[themeForm.theme_preference === option ? 'bg-primary-50 dark:bg-primary-900/20 border-primary-300 dark:border-primary-700 text-primary-700 dark:text-primary-400' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800']"
                        class="flex-1 px-4 py-3 border rounded-xl text-sm font-medium capitalize transition-colors">
                        {{ option === 'auto' ? 'System' : option }}
                    </button>
                </div>
            </div>

            <!-- Date & Time -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Date &amp; Time</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Controls how dates and times are displayed across the app.
                </p>
                <form @submit.prevent="updateDateTime" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Timezone</label>
                        <select v-model="dateTimeForm.timezone"
                            class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 outline-none transition">
                            <option v-for="tz in commonTimezones" :key="tz" :value="tz">{{ tz }}</option>
                        </select>
                        <p v-if="dateTimeForm.errors.timezone" class="text-red-500 text-xs mt-1">{{ dateTimeForm.errors.timezone }}</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date format</label>
                            <select v-model="dateTimeForm.date_format"
                                class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 outline-none transition">
                                <option v-for="o in dateFormatOptions" :key="o.value" :value="o.value">{{ o.label }}</option>
                            </select>
                            <p v-if="dateTimeForm.errors.date_format" class="text-red-500 text-xs mt-1">{{ dateTimeForm.errors.date_format }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Time format</label>
                            <select v-model="dateTimeForm.time_format"
                                class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 outline-none transition">
                                <option v-for="o in timeFormatOptions" :key="o.value" :value="o.value">{{ o.label }}</option>
                            </select>
                            <p v-if="dateTimeForm.errors.time_format" class="text-red-500 text-xs mt-1">{{ dateTimeForm.errors.time_format }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-1">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Preview: <span class="font-mono text-gray-900 dark:text-white">{{ dateTimePreview }}</span>
                        </p>
                        <button type="submit" :disabled="dateTimeForm.processing"
                            class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors text-sm disabled:opacity-50">
                            Save
                        </button>
                    </div>
                </form>
            </div>

            <!-- Two-Factor Authentication -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Two-Factor Authentication</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Add an extra layer of security to your account. You can enable any combination of methods.
                </p>

                <!-- Recovery codes display (after TOTP enable or regenerate) -->
                <div v-if="recoveryCodes.length" class="mb-4 p-4 border border-yellow-300 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/20 rounded-xl">
                    <p class="text-sm font-medium text-yellow-900 dark:text-yellow-200 mb-2">
                        Save these recovery codes somewhere safe. They can each be used once if you lose access.
                    </p>
                    <div class="grid grid-cols-2 gap-2 font-mono text-sm text-gray-900 dark:text-white">
                        <span v-for="c in recoveryCodes" :key="c" class="bg-white dark:bg-gray-800 px-2 py-1 rounded">{{ c }}</span>
                    </div>
                    <button @click="recoveryCodes = []" class="mt-3 text-xs text-yellow-700 dark:text-yellow-300 hover:underline">
                        I've saved these codes
                    </button>
                </div>

                <div class="space-y-4">
                    <!-- TOTP / Authenticator -->
                    <div class="border border-gray-200 dark:border-gray-800 rounded-xl p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Authenticator App (TOTP)</h3>
                                    <span v-if="twoFactor.totp_enabled" class="px-2 py-0.5 text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full">Enabled</span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Use apps like Google Authenticator, 1Password, or Authy.
                                </p>
                            </div>
                            <div class="shrink-0">
                                <button v-if="!twoFactor.totp_enabled && !totpSetup" @click="startTotpSetup" :disabled="totpLoading"
                                    class="px-3 py-1.5 text-xs font-medium bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors disabled:opacity-50">
                                    {{ totpLoading ? 'Loading...' : 'Set up' }}
                                </button>
                                <button v-else-if="twoFactor.totp_enabled" @click="showTotpDisable = !showTotpDisable"
                                    class="px-3 py-1.5 text-xs font-medium border border-red-300 dark:border-red-700 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                    Disable
                                </button>
                            </div>
                        </div>

                        <!-- TOTP setup flow -->
                        <div v-if="totpSetup && !twoFactor.totp_enabled" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-800 space-y-3">
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                1. Scan this QR code with your authenticator app:
                            </p>
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
                                <div class="flex gap-2">
                                    <button type="submit" :disabled="totpConfirmForm.processing"
                                        class="flex-1 px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors disabled:opacity-50">
                                        Confirm & Enable
                                    </button>
                                    <button type="button" @click="totpSetup = null"
                                        class="px-3 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- TOTP disable flow -->
                        <div v-if="showTotpDisable && twoFactor.totp_enabled" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-800">
                            <form @submit.prevent="disableTotp" class="space-y-3">
                                <p class="text-sm text-gray-700 dark:text-gray-300">Enter your password to disable TOTP:</p>
                                <input v-model="totpDisableForm.password" type="password" required placeholder="Password"
                                    class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 outline-none" />
                                <p v-if="totpDisableForm.errors.password" class="text-red-500 text-xs">{{ totpDisableForm.errors.password }}</p>
                                <div class="flex gap-2">
                                    <button type="submit" :disabled="totpDisableForm.processing"
                                        class="flex-1 px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-xl transition-colors disabled:opacity-50">
                                        Disable TOTP
                                    </button>
                                    <button type="button" @click="showTotpDisable = false"
                                        class="px-3 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Regenerate recovery codes -->
                        <div v-if="twoFactor.totp_enabled && !showTotpDisable" class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-800">
                            <button @click="regenerateRecoveryCodes" :disabled="regenRecoveryForm.processing"
                                class="text-xs text-primary-600 dark:text-primary-400 hover:underline">
                                Regenerate recovery codes
                            </button>
                        </div>
                    </div>

                    <!-- Email OTP -->
                    <div class="border border-gray-200 dark:border-gray-800 rounded-xl p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Email Verification</h3>
                                    <span v-if="twoFactor.email_enabled" class="px-2 py-0.5 text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full">Enabled</span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Receive a 6-digit code at {{ user.email }} when signing in.
                                </p>
                            </div>
                            <div class="shrink-0">
                                <button v-if="!twoFactor.email_enabled" @click="enableEmailOtp" :disabled="emailEnableForm.processing"
                                    class="px-3 py-1.5 text-xs font-medium bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors disabled:opacity-50">
                                    Enable
                                </button>
                                <button v-else @click="showEmailDisable = !showEmailDisable"
                                    class="px-3 py-1.5 text-xs font-medium border border-red-300 dark:border-red-700 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                    Disable
                                </button>
                            </div>
                        </div>

                        <div v-if="showEmailDisable && twoFactor.email_enabled" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-800">
                            <form @submit.prevent="disableEmailOtp" class="space-y-3">
                                <p class="text-sm text-gray-700 dark:text-gray-300">Enter your password to disable email verification:</p>
                                <input v-model="emailDisableForm.password" type="password" required placeholder="Password"
                                    class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 outline-none" />
                                <p v-if="emailDisableForm.errors.password" class="text-red-500 text-xs">{{ emailDisableForm.errors.password }}</p>
                                <div class="flex gap-2">
                                    <button type="submit" :disabled="emailDisableForm.processing"
                                        class="flex-1 px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-xl transition-colors disabled:opacity-50">
                                        Disable Email 2FA
                                    </button>
                                    <button type="button" @click="showEmailDisable = false"
                                        class="px-3 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Passkey info pointer -->
                    <div class="border border-gray-200 dark:border-gray-800 rounded-xl p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Passkeys</h3>
                                    <span v-if="twoFactor.passkeys_enabled" class="px-2 py-0.5 text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full">Enabled</span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Use device biometrics or a hardware key. Manage passkeys in the section below.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Passkeys -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Passkeys</h2>

                <div v-if="passkeys.length" class="space-y-2 mb-4">
                    <div v-for="pk in passkeys" :key="pk.id"
                        class="flex items-center justify-between px-3 py-2 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <div>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ pk.name }}</span>
                            <span v-if="pk.last_used_at" class="text-xs text-gray-400 ml-2">Last used: {{ new Date(pk.last_used_at).toLocaleDateString() }}</span>
                        </div>
                        <button @click="deletePasskey(pk.id)" class="text-red-500 hover:text-red-700 text-xs font-medium">Remove</button>
                    </div>
                </div>

                <div class="flex gap-2">
                    <input v-model="passkeyName" type="text" placeholder="Passkey name (optional)"
                        class="flex-1 px-3 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                    <button @click="registerPasskey" :disabled="passkeyLoading"
                        class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors text-sm disabled:opacity-50 whitespace-nowrap">
                        {{ passkeyLoading ? 'Registering...' : 'Add Passkey' }}
                    </button>
                </div>
                <p v-if="passkeyError" class="text-red-500 text-xs mt-2">{{ passkeyError }}</p>
            </div>
        </div>
    </AppLayout>
</template>
