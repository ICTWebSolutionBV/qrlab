<script setup>
import { computed, ref, watch } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    type: { type: String, default: 'url' },
    schemas: { type: Object, required: true },
    preview: { type: Object, default: null },
})

const types = [
    { key: 'url', label: 'URL' },
    { key: 'wifi', label: 'WiFi' },
    { key: 'phone', label: 'Phone' },
    { key: 'vcard', label: 'vCard' },
    { key: 'email', label: 'Email' },
]

const activeType = ref(props.type)
watch(activeType, (t) => {
    if (t !== props.type) {
        router.get(route('qr.bulk.create', { type: t }), {}, { preserveScroll: true })
    }
})

const columns = computed(() => Object.entries(props.schemas[activeType.value] || {}).map(([key, def]) => ({
    key,
    label: def[0],
    required: def[1],
    example: def[2],
})))

const uploadForm = useForm({ file: null })
const fileInput = ref(null)

const chooseFile = () => fileInput.value?.click()
const onFileChange = (e) => {
    const f = e.target.files?.[0]
    if (!f) return
    uploadForm.file = f
    uploadForm.post(route('qr.bulk.preview', activeType.value), {
        forceFormData: true,
        preserveScroll: true,
        onFinish: () => { if (fileInput.value) fileInput.value.value = '' },
    })
}

const discard = () => {
    router.post(route('qr.bulk.discard', activeType.value), {}, { preserveScroll: true })
}

const importForm = useForm({ name: '' })
const doImport = () => {
    if (!props.preview?.valid) return
    importForm.post(route('qr.bulk.store', activeType.value), { preserveScroll: true })
}

const errorEntries = computed(() => {
    const errs = props.preview?.errors || {}
    return Object.entries(errs).map(([line, msgs]) => ({ line, msgs: Array.isArray(msgs) ? msgs : Object.values(msgs) }))
})
</script>

<template>
    <Head title="Bulk import" />
    <AppLayout>
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Bulk import QR codes</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Upload a CSV file to create many QR codes at once.</p>
            </div>
            <Link :href="route('dashboard')" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">← Back to dashboard</Link>
        </div>

        <!-- Tabs -->
        <div class="flex flex-wrap gap-2 mb-4">
            <button v-for="t in types" :key="t.key" @click="activeType = t.key" type="button"
                :class="['px-3 py-1.5 rounded-lg text-sm font-medium border transition-colors',
                    activeType === t.key
                        ? 'bg-primary-600 text-white border-primary-600'
                        : 'bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800']">
                {{ t.label }}
            </button>
        </div>

        <!-- Schema + template -->
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 mb-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Expected columns</h2>
                <a :href="route('qr.bulk.template', activeType)"
                    class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                    Download template
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-800">
                            <th class="py-2 pr-4">Column</th>
                            <th class="py-2 pr-4">Label</th>
                            <th class="py-2 pr-4">Required</th>
                            <th class="py-2">Example</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="c in columns" :key="c.key" class="border-b border-gray-100 dark:border-gray-800 last:border-0">
                            <td class="py-2 pr-4 font-mono text-xs text-gray-800 dark:text-gray-200">{{ c.key }}</td>
                            <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ c.label }}</td>
                            <td class="py-2 pr-4">
                                <span v-if="c.required" class="text-xs font-medium text-red-600 dark:text-red-400">required</span>
                                <span v-else class="text-xs text-gray-500">optional</span>
                            </td>
                            <td class="py-2 text-xs text-gray-500 dark:text-gray-400">{{ c.example }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Upload -->
        <div v-if="!preview" class="bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-700 rounded-xl px-8 py-14 my-8 text-center">
            <input ref="fileInput" type="file" accept=".csv,text/csv" class="hidden" @change="onFileChange" />
            <svg class="w-10 h-10 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.9 5 5 0 019.66-2.12A5.5 5.5 0 0118 17h-1m-6-4v9m0-9l-3 3m3-3l3 3"/></svg>
            <p class="text-sm text-gray-700 dark:text-gray-300 mb-3">Upload a CSV file with your {{ activeType }} rows.</p>
            <button @click="chooseFile" type="button" :disabled="uploadForm.processing"
                class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 disabled:opacity-60 text-white rounded-lg text-sm font-medium">
                {{ uploadForm.processing ? 'Uploading…' : 'Choose CSV file' }}
            </button>
            <div v-if="uploadForm.errors.file" class="mt-3 text-sm text-red-600 dark:text-red-400">{{ uploadForm.errors.file }}</div>
        </div>

        <!-- Preview -->
        <div v-else class="space-y-4">
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Preview</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ preview.file_name }} · {{ preview.total }} rows detected</p>
                    </div>
                    <button @click="discard" type="button" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Discard & upload another</button>
                </div>

                <div v-if="errorEntries.length" class="mb-4 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-900 text-sm">
                    <div class="font-semibold text-red-800 dark:text-red-300 mb-1">Please fix the following before importing:</div>
                    <ul class="list-disc pl-5 text-red-700 dark:text-red-300 space-y-0.5">
                        <li v-for="e in errorEntries" :key="e.line">
                            <span class="font-medium">{{ e.line === '0' ? 'Headers' : `Row ${e.line}` }}:</span> {{ e.msgs.join('; ') }}
                        </li>
                    </ul>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-800">
                                <th class="py-2 pr-4">#</th>
                                <th v-for="c in columns" :key="c.key" class="py-2 pr-4">{{ c.label }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, i) in preview.rows" :key="i" class="border-b border-gray-100 dark:border-gray-800 last:border-0">
                                <td class="py-2 pr-4 text-gray-500">{{ i + 1 }}</td>
                                <td v-for="c in columns" :key="c.key" class="py-2 pr-4 text-gray-800 dark:text-gray-200">
                                    <span class="truncate inline-block max-w-xs align-top">{{ row[c.key] }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Confirm -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5">
                <label class="block text-sm font-medium text-gray-900 dark:text-white mb-1">Batch name</label>
                <input v-model="importForm.name" type="text" placeholder="e.g. Spring campaign"
                    class="w-full px-3 py-2 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 text-gray-900 dark:text-white" />
                <div v-if="importForm.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ importForm.errors.name }}</div>

                <div class="flex items-center justify-end gap-2 mt-4">
                    <button @click="discard" type="button" class="px-4 py-2 text-sm font-medium rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200">Cancel</button>
                    <button @click="doImport" type="button" :disabled="!preview.valid || importForm.processing"
                        class="px-4 py-2 text-sm font-medium rounded-lg bg-primary-600 hover:bg-primary-700 disabled:opacity-60 text-white">
                        {{ importForm.processing ? 'Importing…' : `Import ${preview.total} rows` }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
