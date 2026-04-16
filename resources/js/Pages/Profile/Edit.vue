<script setup>
import { ref } from 'vue'
import { Head, useForm, usePage, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

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

const passkeyName = ref('')
const passkeyLoading = ref(false)
const passkeyError = ref('')

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
