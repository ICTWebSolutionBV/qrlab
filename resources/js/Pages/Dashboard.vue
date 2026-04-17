<script setup>
import { computed, ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import QrPreview from '@/Components/QrPreview.vue'
import { buildQrPayload } from '@/utils/qrPayload.js'

const props = defineProps({
    qrCodes: Object,
    appUrl: { type: String, default: '' },
    filters: {
        type: Object,
        default: () => ({ search: '', type: '', tracking: '', sort: 'latest' }),
    },
})

const deleteTarget = ref(null)
const previewTarget = ref(null)

// View mode: grid or list, persisted
const viewMode = ref(localStorage.getItem('qr_dashboard_view') || 'grid')
watch(viewMode, (v) => localStorage.setItem('qr_dashboard_view', v))

// Filter state — synced from server
const search = ref(props.filters.search || '')
const type = ref(props.filters.type || '')
const tracking = ref(props.filters.tracking || '')
const sort = ref(props.filters.sort || 'latest')

let searchTimer = null
const reload = (immediate = false) => {
    clearTimeout(searchTimer)
    const go = () => {
        router.get(route('dashboard'), {
            search: search.value || undefined,
            type: type.value || undefined,
            tracking: tracking.value || undefined,
            sort: sort.value !== 'latest' ? sort.value : undefined,
        }, { preserveState: true, preserveScroll: true, replace: true })
    }
    if (immediate) go()
    else searchTimer = setTimeout(go, 300)
}

watch(search, () => reload(false))
watch([type, tracking, sort], () => reload(true))

const resetFilters = () => {
    search.value = ''
    type.value = ''
    tracking.value = ''
    sort.value = 'latest'
    reload(true)
}

const hasFilters = computed(() =>
    !!(search.value || type.value || tracking.value || (sort.value && sort.value !== 'latest'))
)

const confirmDelete = (qr) => { deleteTarget.value = qr }
const cancelDelete = () => { deleteTarget.value = null }
const doDelete = () => {
    if (!deleteTarget.value) return
    router.delete(route('qr.destroy', deleteTarget.value.id))
    deleteTarget.value = null
}

const showQr = (qr) => { previewTarget.value = qr }
const closeQr = () => { previewTarget.value = null }

const previewPayload = computed(() =>
    previewTarget.value ? buildQrPayload(previewTarget.value, props.appUrl) : ''
)

const typeLabel = (qr) => {
    if (qr.type === 'url') return 'URL'
    if (qr.type === 'phone') return 'Phone'
    if (qr.type === 'vcard') return 'vCard'
    if (qr.type === 'email') return 'Email'
    return qr.encryption || 'WiFi'
}

const subtitle = (qr) => {
    if (qr.type === 'wifi') return qr.ssid
    if (qr.type === 'vcard') return [qr.vcard_data?.first_name, qr.vcard_data?.last_name].filter(Boolean).join(' ')
    if (qr.type === 'email') return qr.email_data?.to
    return qr.url
}

const fmtDate = (iso) => {
    if (!iso) return '—'
    const d = new Date(iso)
    return d.toLocaleDateString()
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

        <!-- Filter bar -->
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-3 mb-4">
            <div class="flex flex-col lg:flex-row lg:items-center gap-3">
                <!-- Search -->
                <div class="relative flex-1 min-w-0">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z"/></svg>
                    <input v-model="search" type="text" placeholder="Search by name, URL or SSID..."
                        class="w-full pl-9 pr-3 py-2 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent text-gray-900 dark:text-white" />
                </div>

                <!-- Type -->
                <select v-model="type"
                    class="px-3 py-2 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 text-gray-900 dark:text-white">
                    <option value="">All types</option>
                    <option value="url">URL</option>
                    <option value="wifi">WiFi</option>
                    <option value="phone">Phone</option>
                    <option value="vcard">vCard</option>
                    <option value="email">Email</option>
                </select>

                <!-- Tracking -->
                <select v-model="tracking"
                    class="px-3 py-2 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 text-gray-900 dark:text-white">
                    <option value="">Tracking: any</option>
                    <option value="on">Tracking on</option>
                    <option value="off">Tracking off</option>
                </select>

                <!-- Sort -->
                <select v-model="sort"
                    class="px-3 py-2 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 text-gray-900 dark:text-white">
                    <option value="latest">Newest first</option>
                    <option value="oldest">Oldest first</option>
                    <option value="name">Name (A–Z)</option>
                    <option value="scans">Most scanned</option>
                </select>

                <button v-if="hasFilters" @click="resetFilters" type="button"
                    class="px-3 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                    Reset
                </button>

                <!-- View toggle -->
                <div class="inline-flex rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-0.5 shrink-0">
                    <button @click="viewMode = 'grid'" type="button" :title="'Card view'"
                        :class="['px-2.5 py-1.5 rounded-md inline-flex items-center justify-center transition-colors',
                            viewMode === 'grid' ? 'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300']">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    </button>
                    <button @click="viewMode = 'list'" type="button" :title="'List view'"
                        :class="['px-2.5 py-1.5 rounded-md inline-flex items-center justify-center transition-colors',
                            viewMode === 'list' ? 'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300']">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty state -->
        <div v-if="!qrCodes.data?.length" class="text-center py-16">
            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
                {{ hasFilters ? 'No QR codes match those filters' : 'No QR codes yet' }}
            </h3>
            <p class="text-gray-500 dark:text-gray-400 mb-4">
                {{ hasFilters ? 'Try adjusting your search or filters.' : 'Create your first QR code to get started.' }}
            </p>
            <button v-if="hasFilters" @click="resetFilters" type="button"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl transition-colors text-sm">
                Clear filters
            </button>
            <Link v-else :href="route('qr.create')"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create QR Code
            </Link>
        </div>

        <!-- Grid / Card view -->
        <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <div v-for="qr in qrCodes.data" :key="qr.id"
                class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg p-3 hover:border-primary-300 dark:hover:border-primary-700 transition-colors group">
                <div class="flex items-start justify-between mb-3 gap-2">
                    <div class="min-w-0">
                        <h3 class="font-semibold text-lg text-gray-900 dark:text-white truncate">{{ qr.name }}</h3>
                        <p class="text-base text-gray-500 dark:text-gray-400 truncate">{{ subtitle(qr) }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-1 shrink-0">
                        <span class="text-sm px-2 py-0.5 rounded-full bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 font-medium">
                            {{ typeLabel(qr) }}
                        </span>
                        <span v-if="qr.tracking_enabled" class="text-sm text-gray-400 dark:text-gray-500">
                            {{ qr.scans_count }} scan{{ qr.scans_count === 1 ? '' : 's' }}
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 mt-3">
                    <button @click="showQr(qr)" title="Show QR"
                        class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2.5 text-base font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-800 rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        Show
                    </button>
                    <Link :href="route('qr.edit', qr.id)" title="Edit"
                        class="p-2.5 text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    </Link>
                    <Link v-if="qr.tracking_enabled" :href="route('qr.analytics', qr.id)" title="View scan analytics"
                        class="p-2.5 text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 rounded-md hover:bg-primary-100 dark:hover:bg-primary-900/40 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18M8 17V9m4 8V5m4 12v-6"/></svg>
                    </Link>
                    <button @click="confirmDelete(qr)" title="Delete QR"
                        class="p-2.5 text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 rounded-md hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- List / Table view -->
        <div v-else class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800/50 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="text-left px-5 py-3 font-medium">Name</th>
                            <th class="text-left px-5 py-3 font-medium">Type</th>
                            <th class="text-left px-5 py-3 font-medium">Target</th>
                            <th class="text-left px-5 py-3 font-medium">Tracking</th>
                            <th class="text-right px-5 py-3 font-medium">Scans</th>
                            <th class="text-left px-5 py-3 font-medium">Created</th>
                            <th class="text-right px-5 py-3 font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="qr in qrCodes.data" :key="qr.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                            <td class="px-5 py-3 font-medium text-gray-900 dark:text-white whitespace-nowrap max-w-[220px] truncate">{{ qr.name }}</td>
                            <td class="px-5 py-3">
                                <span class="text-xs px-2 py-1 rounded-full bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400">
                                    {{ typeLabel(qr) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-500 dark:text-gray-400 max-w-[280px] truncate">{{ subtitle(qr) }}</td>
                            <td class="px-5 py-3">
                                <span v-if="qr.tracking_enabled" class="inline-flex items-center gap-1 text-xs text-emerald-700 dark:text-emerald-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> On
                                </span>
                                <span v-else class="inline-flex items-center gap-1 text-xs text-gray-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-300 dark:bg-gray-600"></span> Off
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right text-gray-700 dark:text-gray-300 tabular-nums">
                                {{ qr.tracking_enabled ? qr.scans_count : '—' }}
                            </td>
                            <td class="px-5 py-3 text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ fmtDate(qr.created_at) }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="showQr(qr)" title="Show QR"
                                        class="p-1.5 rounded-md text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                    </button>
                                    <Link :href="route('qr.edit', qr.id)" title="Edit"
                                        class="p-1.5 rounded-md text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </Link>
                                    <Link v-if="qr.tracking_enabled" :href="route('qr.analytics', qr.id)" title="Analytics"
                                        class="p-1.5 rounded-md text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18M8 17V9m4 8V5m4 12v-6"/></svg>
                                    </Link>
                                    <a :href="route('qr.export', { qrCode: qr.id, format: 'png' })" title="Download PNG"
                                        class="p-1.5 rounded-md text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                                    </a>
                                    <button @click="confirmDelete(qr)" title="Delete"
                                        class="p-1.5 rounded-md text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="qrCodes.data?.length && qrCodes.last_page > 1" class="flex items-center justify-between mt-6">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Showing {{ qrCodes.from }}–{{ qrCodes.to }} of {{ qrCodes.total }}
            </p>
            <div class="flex items-center gap-1">
                <template v-for="link in qrCodes.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url" preserve-scroll preserve-state
                        v-html="link.label"
                        :class="['px-3 py-1.5 text-sm rounded-lg transition-colors',
                            link.active
                                ? 'bg-primary-600 text-white'
                                : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800']" />
                    <span v-else v-html="link.label"
                        class="px-3 py-1.5 text-sm rounded-lg text-gray-300 dark:text-gray-600" />
                </template>
            </div>
        </div>

        <!-- QR preview modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-150 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="previewTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="closeQr">
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeQr" />
                    <Transition
                        enter-active-class="transition duration-150 ease-out"
                        enter-from-class="opacity-0 scale-95"
                        enter-to-class="opacity-100 scale-100"
                        leave-active-class="transition duration-100 ease-in"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95"
                    >
                        <div v-if="previewTarget" class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-800 p-6 w-full max-w-sm">
                            <div class="flex items-center justify-between mb-4">
                                <div class="min-w-0 pr-3">
                                    <h3 class="font-semibold text-gray-900 dark:text-white truncate">{{ previewTarget.name }}</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ previewTarget.type }}</p>
                                </div>
                                <button @click="closeQr"
                                    class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-500 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors inline-flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <div class="flex justify-center bg-white rounded-xl p-4">
                                <QrPreview
                                    :key="previewTarget.id"
                                    :data="previewPayload"
                                    :width="280"
                                    :height="280"
                                    :margin="previewTarget.margin ?? 10"
                                    :dots-color="previewTarget.foreground_color || '#000000'"
                                    :background-color="previewTarget.background_color || '#FFFFFF'"
                                    :dot-style="previewTarget.dot_style || 'square'"
                                    :corner-square-style="previewTarget.corner_square_style || 'square'"
                                    :corner-dot-style="previewTarget.corner_dot_style || 'square'"
                                    :error-correction-level="previewTarget.error_correction || 'M'"
                                />
                            </div>
                            <div class="flex gap-2 mt-4">
                                <Link :href="route('qr.edit', previewTarget.id)"
                                    class="flex-1 text-center px-3 py-2 text-xs font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                                    Edit
                                </Link>
                                <a :href="route('qr.export', { qrCode: previewTarget.id, format: 'png' })"
                                    class="flex-1 text-center px-3 py-2 text-xs font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors">
                                    Download PNG
                                </a>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>

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
