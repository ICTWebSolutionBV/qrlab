<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { formatDate, formatDateTime } from '@/utils/datetime.js'
import { Line, Bar } from 'vue-chartjs'
import {
    Chart as ChartJS,
    CategoryScale, LinearScale, PointElement, LineElement, BarElement,
    Title, Tooltip, Legend, Filler,
} from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend, Filler)

const props = defineProps({
    link: Object,
    analytics: Object,
    appUrl: { type: String, default: '' },
})

const shortUrl = computed(() => `${props.appUrl}/${props.link.alias}`)

const fmt = (n) => (typeof n === 'number' ? n.toLocaleString('en-US') : '0')

const cards = computed(() => [
    { label: 'All-time clicks', value: props.analytics.totals.all_time, accent: 'primary' },
    { label: 'Today', value: props.analytics.totals.today, accent: 'emerald' },
    { label: 'Last 7 days', value: props.analytics.totals.last_7_days, accent: 'blue' },
    { label: 'Last 30 days', value: props.analytics.totals.last_30_days, accent: 'purple' },
    { label: 'Unique visitors', value: props.analytics.totals.unique_visitors, accent: 'amber' },
])

const accentClass = (accent) => ({
    primary: 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400',
    emerald: 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400',
    blue: 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400',
    purple: 'bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-400',
    amber: 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400',
}[accent] || '')

const dayLabel = (iso) => new Date(iso + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric' })

const dailyChart = computed(() => ({
    labels: props.analytics.daily.map(d => dayLabel(d.day)),
    datasets: [{
        label: 'Clicks',
        data: props.analytics.daily.map(d => d.count),
        borderColor: 'rgb(16, 185, 129)',
        backgroundColor: 'rgba(16, 185, 129, 0.12)',
        tension: 0.3,
        fill: true,
        pointRadius: 2,
        pointHoverRadius: 4,
        borderWidth: 2,
    }],
}))

const dailyOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(148,163,184,0.15)' } },
        x: { grid: { display: false } },
    },
}

const hourlyChart = computed(() => ({
    labels: props.analytics.hourly.map(h => h.hour),
    datasets: [{
        label: 'Clicks',
        data: props.analytics.hourly.map(h => h.count),
        backgroundColor: 'rgba(99, 102, 241, 0.7)',
        borderRadius: 4,
    }],
}))

const hourlyOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(148,163,184,0.15)' } },
        x: { grid: { display: false } },
    },
}

const maxBreakdown = (items) => Math.max(...(items || []).map(i => i.count), 1)

function copyLink() {
    navigator.clipboard.writeText(shortUrl.value)
}
</script>

<template>
    <AppLayout>
        <Head :title="`Analytics — /${link.alias}`" />

        <div class="max-w-6xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex flex-wrap items-center gap-3 justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('links.index')"
                        class="p-2 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ link.title || `/${link.alias}` }}</h1>
                        <div class="flex items-center gap-2 mt-0.5">
                            <a :href="shortUrl" target="_blank"
                                class="text-sm font-mono text-primary-600 dark:text-primary-400 hover:underline">{{ shortUrl }}</a>
                            <button @click="copyLink" title="Copy"
                                class="p-0.5 rounded text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a :href="route('links.qr', link.id)"
                        class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        QR Code
                    </a>
                    <Link :href="route('links.edit', link.id)"
                        class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium bg-primary-600 hover:bg-primary-700 text-white rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        Edit
                    </Link>
                </div>
            </div>

            <!-- Destination -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 px-5 py-3 flex items-center gap-3">
                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                <a :href="link.original_url" target="_blank"
                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 truncate">
                    {{ link.original_url }}
                </a>
            </div>

            <!-- Stat cards -->
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                <div v-for="card in cards" :key="card.label"
                    :class="accentClass(card.accent)"
                    class="rounded-xl p-4">
                    <p class="text-2xl font-bold">{{ fmt(card.value) }}</p>
                    <p class="text-sm mt-0.5 opacity-80">{{ card.label }}</p>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Clicks — last 30 days</h3>
                    <div class="h-48">
                        <Line :data="dailyChart" :options="dailyOptions" />
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Clicks by hour (last 24h)</h3>
                    <div class="h-48">
                        <Bar :data="hourlyChart" :options="hourlyOptions" />
                    </div>
                </div>
            </div>

            <!-- Breakdowns -->
            <div class="grid lg:grid-cols-3 gap-6">
                <!-- Browsers -->
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Browsers</h3>
                    <div class="space-y-2.5">
                        <div v-for="item in analytics.browsers" :key="item.label">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-700 dark:text-gray-300">{{ item.label }}</span>
                                <span class="text-gray-500 dark:text-gray-400">{{ fmt(item.count) }}</span>
                            </div>
                            <div class="h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                                <div class="h-full bg-primary-500 rounded-full"
                                    :style="{ width: (item.count / maxBreakdown(analytics.browsers) * 100) + '%' }"></div>
                            </div>
                        </div>
                        <p v-if="!analytics.browsers?.length" class="text-sm text-gray-400 text-center py-2">No data yet</p>
                    </div>
                </div>

                <!-- Devices -->
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Devices</h3>
                    <div class="space-y-2.5">
                        <div v-for="item in analytics.devices" :key="item.label">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-700 dark:text-gray-300">{{ item.label }}</span>
                                <span class="text-gray-500 dark:text-gray-400">{{ fmt(item.count) }}</span>
                            </div>
                            <div class="h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                                <div class="h-full bg-indigo-500 rounded-full"
                                    :style="{ width: (item.count / maxBreakdown(analytics.devices) * 100) + '%' }"></div>
                            </div>
                        </div>
                        <p v-if="!analytics.devices?.length" class="text-sm text-gray-400 text-center py-2">No data yet</p>
                    </div>
                </div>

                <!-- OS -->
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Operating Systems</h3>
                    <div class="space-y-2.5">
                        <div v-for="item in analytics.os" :key="item.label">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-700 dark:text-gray-300">{{ item.label }}</span>
                                <span class="text-gray-500 dark:text-gray-400">{{ fmt(item.count) }}</span>
                            </div>
                            <div class="h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-500 rounded-full"
                                    :style="{ width: (item.count / maxBreakdown(analytics.os) * 100) + '%' }"></div>
                            </div>
                        </div>
                        <p v-if="!analytics.os?.length" class="text-sm text-gray-400 text-center py-2">No data yet</p>
                    </div>
                </div>
            </div>

            <!-- Countries + Referers -->
            <div class="grid lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Top Countries</h3>
                    <div class="space-y-2.5">
                        <div v-for="item in analytics.countries" :key="item.label">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-700 dark:text-gray-300">
                                    <span v-if="item.code" class="mr-1">
                                        {{ String.fromCodePoint(...[...item.code.toUpperCase()].map(c => 0x1F1E6 + c.charCodeAt(0) - 65)) }}
                                    </span>
                                    {{ item.label }}
                                </span>
                                <span class="text-gray-500 dark:text-gray-400">{{ fmt(item.count) }}</span>
                            </div>
                            <div class="h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                                <div class="h-full bg-amber-500 rounded-full"
                                    :style="{ width: (item.count / Math.max(...analytics.countries.map(c => c.count), 1) * 100) + '%' }"></div>
                            </div>
                        </div>
                        <p v-if="!analytics.countries?.length" class="text-sm text-gray-400 text-center py-2">No data yet</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Referrers</h3>
                    <div class="space-y-2.5">
                        <div v-for="item in analytics.referers" :key="item.label">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-700 dark:text-gray-300 truncate max-w-[180px]">{{ item.label }}</span>
                                <span class="text-gray-500 dark:text-gray-400 shrink-0">{{ fmt(item.count) }}</span>
                            </div>
                            <div class="h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                                <div class="h-full bg-pink-500 rounded-full"
                                    :style="{ width: (item.count / maxBreakdown(analytics.referers) * 100) + '%' }"></div>
                            </div>
                        </div>
                        <p v-if="!analytics.referers?.length" class="text-sm text-gray-400 text-center py-2">No referrer data yet</p>
                    </div>
                </div>
            </div>

            <!-- Recent clicks -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-800">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Recent Clicks</h3>
                </div>
                <div v-if="analytics.recent.length === 0" class="py-10 text-center text-sm text-gray-400">No clicks yet</div>
                <table v-else class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Time</th>
                            <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">Browser / Device</th>
                            <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">Location</th>
                            <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="click in analytics.recent" :key="click.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-5 py-3 text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ formatDateTime(click.clicked_at) }}</td>
                            <td class="px-5 py-3 hidden sm:table-cell">
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ click.browser }}</span>
                                <span class="text-xs text-gray-400 ml-1">· {{ click.device }}</span>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-600 dark:text-gray-400 hidden md:table-cell">
                                <span v-if="click.country_code" class="mr-1">
                                    {{ String.fromCodePoint(...[...click.country_code.toUpperCase()].map(c => 0x1F1E6 + c.charCodeAt(0) - 65)) }}
                                </span>
                                {{ click.location }}
                            </td>
                            <td class="px-5 py-3 text-sm font-mono text-gray-400 hidden lg:table-cell">{{ click.ip_masked }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
