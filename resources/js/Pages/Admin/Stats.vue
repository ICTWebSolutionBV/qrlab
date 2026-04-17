<script setup>
import { computed, ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Line } from 'vue-chartjs'
import {
    Chart as ChartJS,
    Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement, Filler,
} from 'chart.js'
import { formatDate, formatDateTime } from '@/utils/datetime.js'

ChartJS.register(Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement, Filler)

const props = defineProps({
    totals: Object,
    daily: Array,
    users: Object,
    topQrCodes: Array,
    filters: Object,
})

const search = ref(props.filters?.search || '')
const sort = ref(props.filters?.sort || 'scans')
const dir = ref(props.filters?.dir || 'desc')

let timer = null
const reload = (immediate = false) => {
    clearTimeout(timer)
    const go = () => {
        router.get(route('admin.stats'), {
            search: search.value || undefined,
            sort: sort.value !== 'scans' ? sort.value : undefined,
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

const chartData = computed(() => ({
    labels: props.daily.map((d) => d.day.slice(5)),
    datasets: [{
        label: 'Scans',
        data: props.daily.map((d) => d.count),
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

const roleLabel = (role) => {
    if (role === 'super_admin') return 'Super Admin'
    if (role === 'admin') return 'Admin'
    return 'User'
}
</script>

<template>
    <Head title="Stats" />
    <AppLayout>
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Platform stats</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Global usage across all users.</p>
            </div>
        </div>

        <!-- Totals -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Users</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ totals.users }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ totals.admins }} admin{{ totals.admins === 1 ? '' : 's' }}</p>
            </div>
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">QR codes</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ totals.qr_codes }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ totals.qr_codes_tracking }} with tracking</p>
            </div>
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Scans (all time)</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ totals.scans_all_time }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ totals.scans_today }} today</p>
            </div>
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Scans (30 days)</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ totals.scans_30d }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ totals.scans_7d }} in last 7 days</p>
            </div>
        </div>

        <!-- Daily chart -->
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 mb-6">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-3">Scans per day (last 30 days)</h2>
            <div class="h-64">
                <Line :data="chartData" :options="chartOptions" />
            </div>
        </div>

        <!-- Users table -->
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden mb-6">
            <div class="flex items-center justify-between gap-3 px-5 py-3 border-b border-gray-200 dark:border-gray-800">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Users</h2>
                <div class="relative max-w-xs w-full">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z"/></svg>
                    <input v-model="search" type="text" placeholder="Search users..."
                        class="w-full pl-9 pr-3 py-2 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 text-gray-900 dark:text-white" />
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800/50 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="text-left px-5 py-3 font-medium cursor-pointer" @click="toggleSort('name')">Name {{ sortIcon('name') }}</th>
                            <th class="text-left px-5 py-3 font-medium cursor-pointer" @click="toggleSort('email')">Email {{ sortIcon('email') }}</th>
                            <th class="text-left px-5 py-3 font-medium">Role</th>
                            <th class="text-right px-5 py-3 font-medium cursor-pointer" @click="toggleSort('qr_codes')">QR codes {{ sortIcon('qr_codes') }}</th>
                            <th class="text-right px-5 py-3 font-medium cursor-pointer" @click="toggleSort('scans')">Scans {{ sortIcon('scans') }}</th>
                            <th class="text-left px-5 py-3 font-medium cursor-pointer" @click="toggleSort('last_scan')">Last scan {{ sortIcon('last_scan') }}</th>
                            <th class="text-left px-5 py-3 font-medium cursor-pointer" @click="toggleSort('created')">Joined {{ sortIcon('created') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="u in users.data" :key="u.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                            <td class="px-5 py-3 font-medium text-gray-900 dark:text-white whitespace-nowrap">{{ u.name }}</td>
                            <td class="px-5 py-3 text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ u.email }}</td>
                            <td class="px-5 py-3">
                                <span :class="['text-xs px-2 py-0.5 rounded-full font-medium',
                                    u.role === 'super_admin' ? 'bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-400'
                                    : u.role === 'admin' ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400'
                                    : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300']">
                                    {{ roleLabel(u.role) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right tabular-nums text-gray-700 dark:text-gray-300">{{ u.qr_codes_count }}</td>
                            <td class="px-5 py-3 text-right tabular-nums text-gray-700 dark:text-gray-300">{{ u.scans_count }}</td>
                            <td class="px-5 py-3 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                {{ u.last_scan_at ? formatDateTime(u.last_scan_at) : '—' }}
                            </td>
                            <td class="px-5 py-3 text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ formatDate(u.created_at) }}</td>
                        </tr>
                        <tr v-if="!users.data?.length">
                            <td colspan="7" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">No users match.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="users.data?.length && users.last_page > 1" class="flex items-center justify-between px-5 py-3 border-t border-gray-200 dark:border-gray-800">
                <p class="text-sm text-gray-500 dark:text-gray-400">Showing {{ users.from }}–{{ users.to }} of {{ users.total }}</p>
                <div class="flex items-center gap-1">
                    <template v-for="link in users.links" :key="link.label">
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
        </div>

        <!-- Top QR codes -->
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-800">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Top QR codes (by scans)</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800/50 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="text-left px-5 py-3 font-medium">Name</th>
                            <th class="text-left px-5 py-3 font-medium">Type</th>
                            <th class="text-left px-5 py-3 font-medium">Owner</th>
                            <th class="text-right px-5 py-3 font-medium">Scans</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="q in topQrCodes" :key="q.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                            <td class="px-5 py-3 font-medium text-gray-900 dark:text-white whitespace-nowrap max-w-[240px] truncate">{{ q.name }}</td>
                            <td class="px-5 py-3 text-gray-500 dark:text-gray-400 uppercase text-xs">{{ q.type }}</td>
                            <td class="px-5 py-3 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                {{ q.owner }} <span class="text-gray-400">&lt;{{ q.owner_email }}&gt;</span>
                            </td>
                            <td class="px-5 py-3 text-right tabular-nums text-gray-700 dark:text-gray-300">{{ q.scans_count }}</td>
                        </tr>
                        <tr v-if="!topQrCodes.length">
                            <td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">No tracked scans yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
