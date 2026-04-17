<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { formatDate, formatTime } from '@/utils/datetime.js'
import { Line, Bar } from 'vue-chartjs'
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Title,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend, Filler)

const props = defineProps({
    qrCode: Object,
    analytics: Object,
    appUrl: String,
})

const fmt = (n) => (typeof n === 'number' ? n.toLocaleString('en-US') : '0')

const trackingUrl = computed(() =>
    props.qrCode?.short_code ? `${props.appUrl || ''}/r/${props.qrCode.short_code}` : null
)

const cards = computed(() => [
    { label: 'All-time scans', value: props.analytics.totals.all_time, accent: 'primary' },
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

// Daily line chart
const dayLabel = (isoDate) => {
    const d = new Date(isoDate + 'T00:00:00')
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

const dailyChart = computed(() => ({
    labels: props.analytics.daily.map(d => dayLabel(d.day)),
    datasets: [{
        label: 'Scans',
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
        label: 'Scans',
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

// Breakdown list with percentage bars
const pct = (count, total) => total > 0 ? Math.round((count / total) * 100) : 0

const totalScans = computed(() => props.analytics.totals.all_time || 0)

const fmtDate = (iso) => formatDate(iso)
const fmtTime = (iso) => formatTime(iso)

// Turn ISO country code (e.g. "US") into flag emoji
const flag = (code) => {
    if (!code || code.length !== 2) return '🌐'
    const base = 0x1F1E6
    return String.fromCodePoint(
        base + (code.toUpperCase().charCodeAt(0) - 65),
        base + (code.toUpperCase().charCodeAt(1) - 65)
    )
}
</script>

<template>
    <Head :title="`Analytics — ${qrCode.name}`" />
    <AppLayout>
        <div class="mb-6">
            <Link :href="route('dashboard')" class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600 inline-flex items-center gap-1 mb-2">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to dashboard
            </Link>
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div class="min-w-0">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ qrCode.name }}</h1>
                    <p v-if="trackingUrl" class="text-sm text-gray-500 dark:text-gray-400 mt-0.5 font-mono truncate">{{ trackingUrl }}</p>
                </div>
                <Link :href="route('qr.edit', qrCode.id)"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                    Edit QR
                </Link>
            </div>
        </div>

        <!-- Summary cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
            <div v-for="card in cards" :key="card.label"
                class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4">
                <span :class="accentClass(card.accent)" class="inline-block text-[10px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded-full mb-2">{{ card.label }}</span>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ fmt(card.value) }}</div>
            </div>
        </div>

        <!-- Daily chart -->
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Scans over the last 30 days</h2>
                <span class="text-xs text-gray-500 dark:text-gray-400">Total: {{ fmt(analytics.totals.last_30_days) }}</span>
            </div>
            <div class="h-64">
                <Line :data="dailyChart" :options="dailyOptions" />
            </div>
        </div>

        <!-- Hourly + Breakdowns -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
            <!-- Hourly (last 24h) -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 lg:col-span-2">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Last 24 hours</h2>
                <div class="h-56">
                    <Bar :data="hourlyChart" :options="hourlyOptions" />
                </div>
            </div>

            <!-- Devices -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Devices</h2>
                <div v-if="!analytics.devices.length" class="text-sm text-gray-500 dark:text-gray-400">No data yet.</div>
                <div v-else class="space-y-2.5">
                    <div v-for="row in analytics.devices" :key="row.label">
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="text-gray-700 dark:text-gray-300">{{ row.label }}</span>
                            <span class="text-gray-500 dark:text-gray-400">{{ fmt(row.count) }} · {{ pct(row.count, totalScans) }}%</span>
                        </div>
                        <div class="h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                            <div class="h-full bg-primary-600 rounded-full" :style="{ width: pct(row.count, totalScans) + '%' }"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Countries + Cities -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Top countries</h2>
                <div v-if="!analytics.countries.length" class="text-sm text-gray-500 dark:text-gray-400">No data yet.</div>
                <div v-else class="space-y-2.5">
                    <div v-for="row in analytics.countries" :key="(row.code || 'x') + row.label">
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="text-gray-700 dark:text-gray-300 inline-flex items-center gap-1.5">
                                <span class="text-base leading-none">{{ flag(row.code) }}</span>
                                {{ row.label }}
                            </span>
                            <span class="text-gray-500 dark:text-gray-400">{{ fmt(row.count) }} · {{ pct(row.count, totalScans) }}%</span>
                        </div>
                        <div class="h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 rounded-full" :style="{ width: pct(row.count, totalScans) + '%' }"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Top cities</h2>
                <div v-if="!analytics.cities.length" class="text-sm text-gray-500 dark:text-gray-400">No city data yet.</div>
                <div v-else class="space-y-2.5">
                    <div v-for="row in analytics.cities" :key="row.label">
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="text-gray-700 dark:text-gray-300">{{ row.label }}</span>
                            <span class="text-gray-500 dark:text-gray-400">{{ fmt(row.count) }} · {{ pct(row.count, totalScans) }}%</span>
                        </div>
                        <div class="h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                            <div class="h-full bg-rose-500 rounded-full" :style="{ width: pct(row.count, totalScans) + '%' }"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <!-- Browsers -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Browsers</h2>
                <div v-if="!analytics.browsers.length" class="text-sm text-gray-500 dark:text-gray-400">No data yet.</div>
                <div v-else class="space-y-2.5">
                    <div v-for="row in analytics.browsers" :key="row.label">
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="text-gray-700 dark:text-gray-300">{{ row.label }}</span>
                            <span class="text-gray-500 dark:text-gray-400">{{ fmt(row.count) }} · {{ pct(row.count, totalScans) }}%</span>
                        </div>
                        <div class="h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-500 rounded-full" :style="{ width: pct(row.count, totalScans) + '%' }"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- OS -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Operating systems</h2>
                <div v-if="!analytics.os.length" class="text-sm text-gray-500 dark:text-gray-400">No data yet.</div>
                <div v-else class="space-y-2.5">
                    <div v-for="row in analytics.os" :key="row.label">
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="text-gray-700 dark:text-gray-300">{{ row.label }}</span>
                            <span class="text-gray-500 dark:text-gray-400">{{ fmt(row.count) }} · {{ pct(row.count, totalScans) }}%</span>
                        </div>
                        <div class="h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                            <div class="h-full bg-purple-500 rounded-full" :style="{ width: pct(row.count, totalScans) + '%' }"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent scans -->
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-800">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Recent scans</h2>
            </div>
            <div v-if="!analytics.recent.length" class="p-5 text-sm text-gray-500 dark:text-gray-400">
                No scans recorded yet. Share your tracking URL and they'll show up here.
            </div>
            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="text-left px-5 py-2.5 font-medium">Date</th>
                            <th class="text-left px-5 py-2.5 font-medium">Time</th>
                            <th class="text-left px-5 py-2.5 font-medium">Location</th>
                            <th class="text-left px-5 py-2.5 font-medium">Device</th>
                            <th class="text-left px-5 py-2.5 font-medium">Browser</th>
                            <th class="text-left px-5 py-2.5 font-medium">OS</th>
                            <th class="text-left px-5 py-2.5 font-medium">IP (masked)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        <tr v-for="row in analytics.recent" :key="row.id" class="text-gray-700 dark:text-gray-300">
                            <td class="px-5 py-2.5 whitespace-nowrap">{{ fmtDate(row.scanned_at) }}</td>
                            <td class="px-5 py-2.5 whitespace-nowrap text-gray-500 dark:text-gray-400">{{ fmtTime(row.scanned_at) }}</td>
                            <td class="px-5 py-2.5 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="text-base leading-none">{{ flag(row.country_code) }}</span>
                                    <span>{{ row.location }}</span>
                                </span>
                            </td>
                            <td class="px-5 py-2.5">{{ row.device }}</td>
                            <td class="px-5 py-2.5">{{ row.browser }}</td>
                            <td class="px-5 py-2.5">{{ row.os }}</td>
                            <td class="px-5 py-2.5 font-mono text-xs text-gray-500 dark:text-gray-400">{{ row.ip_masked }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
