<script setup>
import { ref, watch } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    link: Object,
    appUrl: { type: String, default: '' },
})

const form = useForm({
    original_url: props.link.original_url,
    alias: props.link.alias,
    title: props.link.title || '',
    is_active: props.link.is_active,
    expires_at: props.link.expires_at
        ? new Date(props.link.expires_at).toISOString().slice(0, 16)
        : '',
})

const showExpiry = ref(!!props.link.expires_at)

watch(showExpiry, (v) => {
    if (!v) form.expires_at = ''
})

function submit() {
    form.put(route('links.update', props.link.id))
}
</script>

<template>
    <AppLayout>
        <Head title="Edit Short Link" />

        <div class="max-w-2xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center gap-3">
                <Link :href="route('links.index')"
                    class="p-2 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Short Link</h1>
                    <p class="text-sm font-mono text-primary-600 dark:text-primary-400 mt-0.5">{{ appUrl }}/{{ link.alias }}</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <!-- Destination -->
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 space-y-4">
                    <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Destination</h2>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Long URL <span class="text-red-500">*</span></label>
                        <input v-model="form.original_url" type="url" required
                            :class="[form.errors.original_url ? 'border-red-400 dark:border-red-500' : 'border-gray-200 dark:border-gray-700']"
                            class="w-full px-4 py-2.5 text-sm border rounded-xl bg-transparent text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500" />
                        <p v-if="form.errors.original_url" class="mt-1.5 text-xs text-red-500">{{ form.errors.original_url }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Title <span class="text-gray-400 font-normal">(optional)</span></label>
                        <input v-model="form.title" type="text"
                            class="w-full px-4 py-2.5 text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-transparent text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    </div>
                </div>

                <!-- Alias -->
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 space-y-4">
                    <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Short Link</h2>

                    <div class="flex items-center gap-0 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden text-sm">
                        <span class="px-3 py-2.5 text-gray-500 dark:text-gray-400 border-r border-gray-200 dark:border-gray-700 whitespace-nowrap shrink-0">{{ appUrl }}/</span>
                        <span class="px-3 py-2.5 text-primary-600 dark:text-primary-400 font-mono font-semibold">{{ form.alias || 'alias' }}</span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Alias <span class="text-red-500">*</span></label>
                        <input v-model="form.alias" type="text" required minlength="5"
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

                <!-- Download QR -->
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">QR Code</h2>
                            <p class="text-xs text-gray-400 mt-0.5">Download a high-res QR code for this short link</p>
                        </div>
                        <a :href="route('links.qr', link.id)"
                            class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Download PNG
                        </a>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between pb-4">
                    <Link :href="route('links.analytics', link.id)"
                        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        View Analytics
                    </Link>
                    <div class="flex items-center gap-3">
                        <Link :href="route('links.index')"
                            class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-xl transition-colors">
                            Cancel
                        </Link>
                        <button type="submit" :disabled="form.processing"
                            class="px-5 py-2.5 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition-colors disabled:opacity-50">
                            {{ form.processing ? 'Saving…' : 'Save Changes' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
