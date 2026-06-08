<script setup>
import { ref, computed, watch } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    appUrl: { type: String, default: '' },
})

const form = useForm({
    original_url: '',
    alias: '',
    title: '',
    is_active: true,
    expires_at: '',
})

const useCustomAlias = ref(false)
const showExpiry = ref(false)

watch(useCustomAlias, (v) => {
    if (!v) form.alias = ''
})
watch(showExpiry, (v) => {
    if (!v) form.expires_at = ''
})

const previewAlias = computed(() => {
    if (useCustomAlias.value && form.alias) return form.alias
    return 'xxxxxx'
})

const qrDataUrl = ref(null)
const loadingQr = ref(false)

async function previewQr() {
    if (!form.original_url) return
    loadingQr.value = true
    try {
        const url = useCustomAlias.value && form.alias
            ? `${props.appUrl}/${form.alias}`
            : form.original_url
        const resp = await fetch(`https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(url)}`)
        qrDataUrl.value = resp.url
    } finally {
        loadingQr.value = false
    }
}

function submit() {
    form.post(route('links.store'))
}
</script>

<template>
    <AppLayout>
        <Head title="Create Short Link" />

        <div class="max-w-2xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center gap-3">
                <Link :href="route('links.index')"
                    class="p-2 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Short Link</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Shorten a URL and track its performance</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <!-- Destination URL -->
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 space-y-4">
                    <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Destination</h2>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Long URL <span class="text-red-500">*</span></label>
                        <input v-model="form.original_url" type="url" required placeholder="https://example.com/very/long/url/that/needs/shortening"
                            :class="[form.errors.original_url ? 'border-red-400 dark:border-red-500' : 'border-gray-200 dark:border-gray-700']"
                            class="w-full px-4 py-2.5 text-sm border rounded-xl bg-transparent text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500" />
                        <p v-if="form.errors.original_url" class="mt-1.5 text-xs text-red-500">{{ form.errors.original_url }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Title <span class="text-gray-400 font-normal">(optional)</span></label>
                        <input v-model="form.title" type="text" placeholder="My awesome link"
                            class="w-full px-4 py-2.5 text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-transparent text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    </div>
                </div>

                <!-- Short link config -->
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 space-y-4">
                    <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Short Link</h2>

                    <!-- Preview -->
                    <div class="flex items-center gap-0 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden text-sm">
                        <span class="px-3 py-2.5 text-gray-500 dark:text-gray-400 border-r border-gray-200 dark:border-gray-700 whitespace-nowrap shrink-0">{{ appUrl }}/</span>
                        <span class="px-3 py-2.5 text-primary-600 dark:text-primary-400 font-mono font-semibold">{{ previewAlias }}</span>
                    </div>

                    <!-- Custom alias toggle -->
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <div class="relative">
                            <input type="checkbox" v-model="useCustomAlias" class="sr-only" />
                            <div :class="[useCustomAlias ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700']"
                                class="w-10 h-6 rounded-full transition-colors"></div>
                            <div :class="[useCustomAlias ? 'translate-x-4' : 'translate-x-0']"
                                class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform"></div>
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Use custom alias (branded link)</span>
                    </label>

                    <div v-if="useCustomAlias">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Custom alias <span class="text-red-500">*</span></label>
                        <input v-model="form.alias" type="text" placeholder="my-brand (min. 5 characters)" minlength="5"
                            pattern="[a-zA-Z0-9_-]+"
                            :class="[form.errors.alias ? 'border-red-400 dark:border-red-500' : 'border-gray-200 dark:border-gray-700']"
                            class="w-full px-4 py-2.5 text-sm border rounded-xl bg-transparent text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 font-mono" />
                        <p class="mt-1.5 text-xs text-gray-400">Letters, numbers, hyphens and underscores only. Minimum 5 characters.</p>
                        <p v-if="form.errors.alias" class="mt-1 text-xs text-red-500">{{ form.errors.alias }}</p>
                    </div>
                </div>

                <!-- Options -->
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 space-y-4">
                    <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Options</h2>

                    <!-- Active toggle -->
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <div class="relative">
                            <input type="checkbox" v-model="form.is_active" class="sr-only" />
                            <div :class="[form.is_active ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700']"
                                class="w-10 h-6 rounded-full transition-colors"></div>
                            <div :class="[form.is_active ? 'translate-x-4' : 'translate-x-0']"
                                class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform"></div>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Link is active</span>
                            <p class="text-xs text-gray-400">Inactive links return a 404 page</p>
                        </div>
                    </label>

                    <!-- Expiry toggle -->
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <div class="relative">
                            <input type="checkbox" v-model="showExpiry" class="sr-only" />
                            <div :class="[showExpiry ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700']"
                                class="w-10 h-6 rounded-full transition-colors"></div>
                            <div :class="[showExpiry ? 'translate-x-4' : 'translate-x-0']"
                                class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform"></div>
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Set expiry date</span>
                    </label>

                    <div v-if="showExpiry">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Expires at</label>
                        <input v-model="form.expires_at" type="datetime-local"
                            :class="[form.errors.expires_at ? 'border-red-400 dark:border-red-500' : 'border-gray-200 dark:border-gray-700']"
                            class="w-full px-4 py-2.5 text-sm border rounded-xl bg-transparent text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500" />
                        <p v-if="form.errors.expires_at" class="mt-1.5 text-xs text-red-500">{{ form.errors.expires_at }}</p>
                    </div>
                </div>

                <!-- QR Code preview -->
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">QR Code</h2>
                            <p class="text-xs text-gray-400 mt-0.5">Generate a QR code for your short link</p>
                        </div>
                        <button type="button" @click="previewQr"
                            :disabled="!form.original_url"
                            class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                            Preview QR
                        </button>
                    </div>
                    <div v-if="qrDataUrl" class="flex justify-center">
                        <img :src="qrDataUrl" alt="QR preview" class="w-40 h-40 rounded-lg border border-gray-200 dark:border-gray-700" />
                    </div>
                    <div v-else class="flex items-center justify-center h-20 rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-400 text-sm">
                        Click "Preview QR" to generate
                    </div>
                    <p class="text-xs text-gray-400 mt-2 text-center">After saving, download a high-res QR from the link list</p>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pb-4">
                    <Link :href="route('links.index')"
                        class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-xl transition-colors">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition-colors disabled:opacity-50">
                        {{ form.processing ? 'Creating…' : 'Create Short Link' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
