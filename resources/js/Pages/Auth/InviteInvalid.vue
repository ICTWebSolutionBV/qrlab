<script setup>
import { Head, Link } from '@inertiajs/vue3'

defineProps({
    reason: String,
})

const messages = {
    not_found: 'This invite link is invalid or does not exist.',
    used: 'This invite has already been used.',
    expired: 'This invite has expired.',
}
</script>

<template>
    <Head title="Invalid Invite" />
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-900 via-gray-800 to-primary-900 px-4">
        <div class="w-full max-w-sm text-center">
            <div class="w-16 h-16 bg-red-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-white mb-2">Invalid Invite</h1>
            <p class="text-gray-400 mb-6">{{ messages[reason] || 'This invite is no longer valid.' }}</p>
            <Link :href="route('login')" class="inline-flex px-4 py-2.5 bg-gray-700 hover:bg-gray-600 text-white font-medium rounded-xl transition-colors text-sm">
                Back to Login
            </Link>
        </div>
    </div>
</template>
