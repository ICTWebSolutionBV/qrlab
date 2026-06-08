<script setup>
import { computed, ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Line } from 'vue-chartjs'
import {
    Chart as ChartJS, Title, Tooltip, Legend, LineElement,
    CategoryScale, LinearScale, PointElement, Filler,
} from 'chart.js'
import { formatDate } from '@/utils/datetime.js'

ChartJS.register(Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement, Filler)

const props = defineProps({
    totals: Object,
    daily: Array,
    links: Object,
    filters: Object,
})

const search = ref(props.filters?.search || '')
const sort = ref(props.filters?.sort || 'clicks')
const dir = ref(props.filters?.direction || 'desc')

let timer = null
const reload = (immediate = false) => {
    clearTimeout(timer)
    const go = () => {
        router.get(route('admin.link-stats'), {
            search: search.value || undefined,
            sort: sort.value !== 'clicks' ? sort.value : undefined,
            dir: dir.value !== 'desc' ? dir.value : undefined,
        }, { preserveState: true, preserveScroll: true, replace: true })
    }
    if (immediate) go()
    else timer = setTimeout(go, 300)
}
watch(search, () => reload(false))
watch([sort, dir], () => reload(true))

const toggleSort = (column) => {
    if (sort.value === column) {
        dir.value = dir.value === 'asc' ? 'desc' : 'asc'
    } else {
        sort.value = column
        dir.value = 'desc'
    }
}
const sortIcon = (column) => {
    if (sort.value !== column) return '↕'
    return dir.value === 'asc' ? '↑' : '↓'
}

const fmt = (n) => (typeof n === 'number' ? n.toLocaleString('en-US') : '0')

const chartData = computed(() => ({
    labels: props.daily.map(d => d.day.slice(5)),
    datasets: [{
        label: 'Clicks',
        data: props.daily.map(d => d.count),
        borderColor: 'rgb(16, 185, 129)',
        backgroundColor: 'rgba(16, 185, 129, 0.1)',
        fill: true,
        tension: 0.3,
    }],
}))
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
}

const truncate = (str, len = 50) => str && str.length > len ? str.slice(0, len) + '…' : str
</script>

<template>
    <AppLayout>
        <Head title="Link Stats — Admin" />

        <div class="max-w-7xl mx-auto space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Short Link Stats</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Platform-wide URL shortener overview</p>
            </div>

            <!-- Stat cards -->
            <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ fmt(totals.total_links) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Total links</p>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ fmt(totals.active_links) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Active links</p>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ fmt(totals.clicks_all_time) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">All-time clicks</p>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ fmt(totals.clicks_today) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Clicks today</p>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
                    <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ fmt(totals.clicks_7d) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Clicks 7d</p>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
                    <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ fmt(totals.clicks_30d) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Clicks 30d</p>
                </div>
            </div>

            <!-- Chart -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Clicks — last 30 days</h3>
                <div class="h-56">
                    <Line :data="chartData" :options="chartOptions" />
                </div>
            </div>

            <!-- Links table -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-800 flex flex-wrap gap-3 items-center">
                    <div class="relative flex-1 min-w-[200px]">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                        <input v-model="search" type="text" placeholder="Search links or users…"
                            class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-transparent text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    </div>
                </div>

                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800">
                        <tr>
                            <th class="px-4 py-3 text-left">
                                <button @click="toggleSort('alias')" class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-200">
                                    Alias {{ sortIcon('alias') }}
                                </button>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">Destination</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">Owner</th>
                            <th class="px-4 py-3 text-center">
                                <button @click="toggleSort('clicks')" class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-200">
                                    Clicks {{ sortIcon('clicks') }}
                                </button>
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden xl:table-cell">
                                <button @click="toggleSort('created')">Created {{ sortIcon('created') }}</button>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="link in links.data" :key="link.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-4 py-3">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white font-mono">/{{ link.alias }}</p>
                                <p v-if="link.title" class="text-xs text-gray-400">{{ link.title }}</p>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <a :href="link.original_url" target="_blank"
                                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">
                                    {{ truncate(link.original_url, 45) }}
                                </a>
                            </td>
                            <td class="px-4 py-3 hidden lg:table-cell">
                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ link.owner }}</p>
                                <p class="text-xs text-gray-400">{{ link.owner_email }}</p>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ fmt(link.clicks_count) }}</span>
                            </td>
                            <td class="px-4 py-3 text-center hidden sm:table-cell">
                                <span :class="link.is_active ? 'bg-emerald-100 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400' : 'bg-gray-100 dark:bg-gray-800 text-gray-500'"
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium">
                                    {{ link.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-400 hidden xl:table-cell whitespace-nowrap">
                                {{ formatDate(link.created_at) }}
                            </td>
                        </tr>
                        <tr v-if="links.data.length === 0">
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-400">No links found</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="links.last_page > 1" class="px-4 py-3 border-t border-gray-200 dark:border-gray-800 flex items-center justify-between">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Showing {{ links.from }}–{{ links.to }} of {{ links.total }}
                    </p>
                    <div class="flex gap-1">
                        <Link v-if="links.prev_page_url" :href="links.prev_page_url"
                            class="px-3 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800">
                            Previous
                        </Link>
                        <Link v-if="links.next_page_url" :href="links.next_page_url"
                            class="px-3 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800">
                            Next
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
