<script setup>
import { ref, computed, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { formatDate } from '@/utils/datetime.js'

const props = defineProps({
    links: Object,
    appUrl: { type: String, default: '' },
    filters: {
        type: Object,
        default: () => ({ search: '', sort: 'latest', status: '' }),
    },
})

const search = ref(props.filters.search || '')
const sort = ref(props.filters.sort || 'latest')
const status = ref(props.filters.status || '')

let searchTimer = null
watch(search, () => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(() => reload(), 300)
})
watch([sort, status], () => reload())

function reload() {
    router.get(route('links.index'), {
        search: search.value || undefined,
        sort: sort.value !== 'latest' ? sort.value : undefined,
        status: status.value || undefined,
    }, { preserveState: true, preserveScroll: true, replace: true })
}

const selectedIds = ref(new Set())
const bulkConfirmOpen = ref(false)
const deleteTarget = ref(null)

const isSelected = (id) => selectedIds.value.has(id)
const toggleSelect = (id) => {
    const next = new Set(selectedIds.value)
    next.has(id) ? next.delete(id) : next.add(id)
    selectedIds.value = next
}
const allVisibleSelected = computed(() => {
    const rows = props.links.data || []
    return rows.length > 0 && rows.every((r) => selectedIds.value.has(r.id))
})
const toggleSelectAll = () => {
    const rows = props.links.data || []
    if (allVisibleSelected.value) {
        const next = new Set(selectedIds.value)
        rows.forEach((r) => next.delete(r.id))
        selectedIds.value = next
    } else {
        const next = new Set(selectedIds.value)
        rows.forEach((r) => next.add(r.id))
        selectedIds.value = next
    }
}

function confirmDelete(link) {
    deleteTarget.value = link
}
function doDelete() {
    router.delete(route('links.destroy', deleteTarget.value.id), {
        onSuccess: () => { deleteTarget.value = null },
    })
}

function doBulkDelete() {
    router.post(route('links.bulk-destroy'), { ids: [...selectedIds.value] }, {
        onSuccess: () => {
            selectedIds.value = new Set()
            bulkConfirmOpen.value = false
        },
    })
}

function copyLink(alias) {
    navigator.clipboard.writeText(`${props.appUrl}/${alias}`)
}

const shortUrl = (alias) => `${props.appUrl}/${alias}`

const truncate = (str, len = 50) => str && str.length > len ? str.slice(0, len) + '…' : str
</script>

<template>
    <AppLayout>
        <Head title="URL Shortener" />

        <div class="max-w-6xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">URL Shortener</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Manage and track your short links</p>
                </div>
                <Link :href="route('links.create')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Short Link
                </Link>
            </div>

            <!-- Feature cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-900 rounded-xl p-4 border border-gray-200 dark:border-gray-800">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg bg-primary-50 dark:bg-primary-900/20 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">Link Editor</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Keep links dynamic and extend their value</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl p-4 border border-gray-200 dark:border-gray-800">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">Branded Links</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Custom aliases for your short links</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl p-4 border border-gray-200 dark:border-gray-800">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">Link Management</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Organise all your links in one place</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl p-4 border border-gray-200 dark:border-gray-800">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">Short URL Tracking</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Measure success with detailed analytics</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 flex flex-wrap gap-3 items-center">
                <div class="relative flex-1 min-w-[200px]">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                    <input v-model="search" type="text" placeholder="Search links…"
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-transparent text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500" />
                </div>
                <select v-model="status"
                    class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">All statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive / Expired</option>
                </select>
                <select v-model="sort"
                    class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="latest">Newest first</option>
                    <option value="oldest">Oldest first</option>
                    <option value="clicks">Most clicks</option>
                </select>

                <button v-if="selectedIds.size > 0"
                    @click="bulkConfirmOpen = true"
                    class="ml-auto inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete {{ selectedIds.size }} selected
                </button>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                <div v-if="links.data.length === 0" class="py-16 text-center">
                    <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-700 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    <p class="text-gray-500 dark:text-gray-400 font-medium">No short links yet</p>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Create your first short link to get started</p>
                    <Link :href="route('links.create')" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Create Short Link
                    </Link>
                </div>

                <table v-else class="w-full">
                    <thead class="border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="w-10 px-4 py-3">
                                <input type="checkbox" :checked="allVisibleSelected" @change="toggleSelectAll"
                                    class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500" />
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Link</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">Destination</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">Clicks</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">Created</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="link in links.data" :key="link.id"
                            :class="[isSelected(link.id) ? 'bg-primary-50/50 dark:bg-primary-900/10' : 'hover:bg-gray-50 dark:hover:bg-gray-800/50']"
                            class="transition-colors">
                            <td class="px-4 py-3">
                                <input type="checkbox" :checked="isSelected(link.id)" @change="toggleSelect(link.id)"
                                    class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500" />
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ link.title || link.alias }}</p>
                                        <div class="flex items-center gap-1 mt-0.5">
                                            <a :href="shortUrl(link.alias)" target="_blank"
                                                class="text-xs text-primary-600 dark:text-primary-400 hover:underline font-mono">
                                                /{{ link.alias }}
                                            </a>
                                            <button @click="copyLink(link.alias)" title="Copy link"
                                                class="p-0.5 rounded text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <a :href="link.original_url" target="_blank"
                                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                    {{ truncate(link.original_url, 45) }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-center hidden sm:table-cell">
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ link.clicks_count.toLocaleString() }}</span>
                            </td>
                            <td class="px-4 py-3 text-center hidden lg:table-cell">
                                <span v-if="link.is_expired"
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 dark:bg-orange-900/20 text-orange-700 dark:text-orange-400">
                                    Expired
                                </span>
                                <span v-else-if="!link.is_active"
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                                    Inactive
                                </span>
                                <span v-else
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400">
                                    Active
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 hidden lg:table-cell whitespace-nowrap">
                                {{ formatDate(link.created_at) }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <Link :href="route('links.analytics', link.id)" title="Analytics"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                    </Link>
                                    <a :href="route('links.qr', link.id)" title="Download QR code"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                    </a>
                                    <Link :href="route('links.edit', link.id)" title="Edit"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </Link>
                                    <button @click="confirmDelete(link)" title="Delete"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="links.last_page > 1" class="px-4 py-3 border-t border-gray-200 dark:border-gray-800 flex items-center justify-between gap-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Showing {{ links.from }}–{{ links.to }} of {{ links.total }}
                    </p>
                    <div class="flex items-center gap-1">
                        <Link v-if="links.prev_page_url" :href="links.prev_page_url"
                            class="px-3 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            Previous
                        </Link>
                        <Link v-if="links.next_page_url" :href="links.next_page_url"
                            class="px-3 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            Next
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete confirm modal -->
        <Transition enter-active-class="transition ease-out duration-150" enter-from-class="opacity-0" enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
                <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl max-w-sm w-full p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Delete short link?</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                        <span class="font-mono text-primary-600 dark:text-primary-400">/{{ deleteTarget.alias }}</span> will be permanently deleted and the redirect will stop working.
                    </p>
                    <div class="flex gap-3 justify-end">
                        <button @click="deleteTarget = null" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">Cancel</button>
                        <button @click="doDelete" class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors">Delete</button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Bulk delete confirm -->
        <Transition enter-active-class="transition ease-out duration-150" enter-from-class="opacity-0" enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="bulkConfirmOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
                <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl max-w-sm w-full p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Delete {{ selectedIds.size }} links?</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">All selected redirects will stop working immediately.</p>
                    <div class="flex gap-3 justify-end">
                        <button @click="bulkConfirmOpen = false" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">Cancel</button>
                        <button @click="doBulkDelete" class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors">Delete all</button>
                    </div>
                </div>
            </div>
        </Transition>
    </AppLayout>
</template>
