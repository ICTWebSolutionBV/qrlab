<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({
    qrCodes: Object,
})

const deleteTarget = ref(null)

const confirmDelete = (qr) => {
    deleteTarget.value = qr
}

const cancelDelete = () => {
    deleteTarget.value = null
}

const doDelete = () => {
    if (!deleteTarget.value) return
    router.delete(route('qr.destroy', deleteTarget.value.id))
    deleteTarget.value = null
}
</script>

<template>
    <Head title="Dashboard" />
    <AppLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My QR Codes</h1>
            <Link :href="route('qr.create')"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New QR Code
            </Link>
        </div>

        <!-- Empty state -->
        <div v-if="!qrCodes.data?.length" class="text-center py-16">
            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">No QR codes yet</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-4">Create your first WiFi QR code to get started.</p>
            <Link :href="route('qr.create')"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create QR Code
            </Link>
        </div>

        <!-- QR Code grid -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="qr in qrCodes.data" :key="qr.id"
                class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 hover:border-primary-300 dark:hover:border-primary-700 transition-colors group">
                <div class="flex items-start justify-between mb-3">
                    <div class="min-w-0">
                        <h3 class="font-semibold text-gray-900 dark:text-white truncate">{{ qr.name }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ qr.type === 'wifi' ? qr.ssid : qr.type === 'vcard' ? [qr.vcard_data?.first_name, qr.vcard_data?.last_name].filter(Boolean).join(' ') : qr.type === 'email' ? qr.email_data?.to : qr.url }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-1 shrink-0 ml-2">
                        <span class="text-xs px-2 py-1 rounded-full bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400">
                            {{ qr.type === 'url' ? 'URL' : qr.type === 'phone' ? 'Phone' : qr.type === 'vcard' ? 'vCard' : qr.type === 'email' ? 'Email' : qr.encryption }}
                        </span>
                        <span v-if="qr.tracking_enabled" class="text-xs text-gray-400 dark:text-gray-500">
                            {{ qr.scans_count }} scan{{ qr.scans_count === 1 ? '' : 's' }}
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-2 mt-3">
                    <Link :href="route('qr.edit', qr.id)"
                        class="flex-1 text-center px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                        Edit
                    </Link>
                    <button @click="confirmDelete(qr)"
                        class="px-3 py-1.5 text-xs font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors">
                        Delete
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete confirmation modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-150 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="cancelDelete">
                    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="cancelDelete" />
                    <Transition
                        enter-active-class="transition duration-150 ease-out"
                        enter-from-class="opacity-0 scale-95"
                        enter-to-class="opacity-100 scale-100"
                        leave-active-class="transition duration-100 ease-in"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95"
                    >
                        <div v-if="deleteTarget" class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-800 p-6 w-full max-w-sm">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-red-50 dark:bg-red-900/20 rounded-xl flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">Delete QR Code</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">This action cannot be undone.</p>
                                </div>
                            </div>
                            <p class="text-sm text-gray-700 dark:text-gray-300 mb-5">
                                Are you sure you want to delete <span class="font-medium text-gray-900 dark:text-white">{{ deleteTarget.name }}</span>?
                            </p>
                            <div class="flex gap-3">
                                <button @click="cancelDelete"
                                    class="flex-1 px-4 py-2.5 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-xl text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                    Cancel
                                </button>
                                <button @click="doDelete"
                                    class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-medium transition-colors">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
