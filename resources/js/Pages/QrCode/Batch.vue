<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { formatDate } from '@/utils/datetime.js'

const props = defineProps({
    batch: { type: Object, required: true },
})

const typeLabel = (t) => ({ url: 'URL', wifi: 'WiFi', phone: 'Phone', vcard: 'vCard', email: 'Email' }[t] || t)
const subtitle = (qr) => {
    if (qr.type === 'wifi') return qr.ssid
    return qr.url || ''
}

const deleteOpen = ref(false)
const doDelete = () => {
    router.delete(route('qr.bulk.destroy', props.batch.id))
}
</script>

<template>
    <Head :title="`Batch: ${batch.name}`" />
    <AppLayout>
        <div class="flex items-center justify-between mb-6 gap-3">
            <div class="min-w-0">
                <Link :href="route('dashboard')" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">← Dashboard</Link>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white truncate">{{ batch.name }}</h1>
                <div class="flex items-center gap-2 mt-1 text-sm text-gray-500 dark:text-gray-400">
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300">{{ typeLabel(batch.type) }}</span>
                    <span>{{ batch.qr_codes.length }} QR codes</span>
                    <span>· Created {{ formatDate(batch.created_at) }}</span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a :href="route('qr.bulk.export', { batch: batch.id, format: 'png' })"
                    class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg bg-primary-600 hover:bg-primary-700 text-white">
                    Download PNG ZIP
                </a>
                <a :href="route('qr.bulk.export', { batch: batch.id, format: 'svg' })"
                    class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200">
                    SVG ZIP
                </a>
                <a :href="route('qr.bulk.export', { batch: batch.id, format: 'eps' })"
                    class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200">
                    EPS ZIP
                </a>
                <button @click="deleteOpen = true" type="button"
                    class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-700 dark:text-red-300">
                    Delete batch
                </button>
            </div>
        </div>

        <div v-if="!batch.qr_codes.length" class="text-center py-16 text-gray-500 dark:text-gray-400">
            This batch has no QR codes.
        </div>

        <div v-else class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <th class="py-2.5 px-4">Name</th>
                        <th class="py-2.5 px-4">Target</th>
                        <th class="py-2.5 px-4">Tracking</th>
                        <th class="py-2.5 px-4 text-right">Scans</th>
                        <th class="py-2.5 px-4">Created</th>
                        <th class="py-2.5 px-4"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="qr in batch.qr_codes" :key="qr.id" class="border-t border-gray-100 dark:border-gray-800">
                        <td class="py-2 px-4 font-medium text-gray-900 dark:text-white">{{ qr.name }}</td>
                        <td class="py-2 px-4 text-gray-700 dark:text-gray-300">
                            <span class="truncate inline-block max-w-md align-top">{{ subtitle(qr) }}</span>
                        </td>
                        <td class="py-2 px-4">
                            <span v-if="qr.tracking_enabled" class="text-xs font-medium text-emerald-700 dark:text-emerald-400">On</span>
                            <span v-else class="text-xs text-gray-500">Off</span>
                        </td>
                        <td class="py-2 px-4 text-right tabular-nums text-gray-800 dark:text-gray-200">{{ qr.scans_count }}</td>
                        <td class="py-2 px-4 text-gray-500 dark:text-gray-400">{{ formatDate(qr.created_at) }}</td>
                        <td class="py-2 px-4 text-right">
                            <Link :href="route('qr.edit', qr.id)" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">Edit</Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Delete confirm modal -->
        <div v-if="deleteOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="bg-white dark:bg-gray-900 rounded-xl max-w-md w-full p-6 border border-gray-200 dark:border-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Delete batch?</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    This will also delete the {{ batch.qr_codes.length }} QR codes in “{{ batch.name }}”.
                </p>
                <div class="flex items-center justify-end gap-2">
                    <button @click="deleteOpen = false" type="button" class="px-4 py-2 text-sm font-medium rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200">Cancel</button>
                    <button @click="doDelete" type="button" class="px-4 py-2 text-sm font-medium rounded-lg bg-red-600 hover:bg-red-700 text-white">Delete</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
