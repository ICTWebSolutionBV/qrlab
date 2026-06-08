<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { Link, usePage, router, useForm } from '@inertiajs/vue3'
import { useTheme } from '@/composables/useTheme'

const page = usePage()
const showSidebar = ref(false)
const showFlash = ref(false)
const flashMessage = ref({ type: '', text: '' })

const auth = computed(() => page.props.auth)
const user = computed(() => auth.value?.user)
const isAdmin = computed(() => user.value?.role === 'admin' || user.value?.role === 'super_admin')
const isSuperAdmin = computed(() => user.value?.role === 'super_admin')
const isGuest = computed(() => !user.value)

const flash = computed(() => page.props.flash)

watch(() => [flash.value?.success, flash.value?.error], ([success, error]) => {
    if (success || error) {
        flashMessage.value = { type: success ? 'success' : 'error', text: success || error }
        showFlash.value = true
        setTimeout(() => showFlash.value = false, 5000)
    }
}, { immediate: true })

const isActive = (routeName) => {
    const current = page.url
    if (routeName === 'dashboard') return current === '/dashboard'
    return current.startsWith('/' + routeName.split('.')[0])
}

const logout = () => router.post(route('logout'))

// Feedback modal
const showFeedback = ref(false)
const feedbackFiles = ref([])
const feedbackForm = useForm({
    name: '',
    email: '',
    message: '',
    screenshots: [],
})

const openFeedback = () => {
    feedbackForm.reset()
    feedbackForm.clearErrors()
    feedbackFiles.value = []
    showFeedback.value = true
    showSidebar.value = false
}
const closeFeedback = () => {
    showFeedback.value = false
}
const onFeedbackFiles = (e) => {
    const files = Array.from(e.target.files || []).slice(0, 5)
    feedbackFiles.value = files
    feedbackForm.screenshots = files
}
const removeFeedbackFile = (idx) => {
    feedbackFiles.value.splice(idx, 1)
    feedbackForm.screenshots = [...feedbackFiles.value]
}
const submitFeedback = () => {
    feedbackForm.screenshots = feedbackFiles.value
    feedbackForm.post(route('feedback.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            showFeedback.value = false
            feedbackForm.reset()
            feedbackFiles.value = []
        },
    })
}

// Changelog modal
const appVersion = computed(() => page.props.app_version || '')
const showChangelog = ref(false)
const changelogLoading = ref(false)
const changelogData = ref(null)

const hasUnseenChangelog = computed(() => {
    if (!appVersion.value) return false
    if (typeof window === 'undefined') return false
    return localStorage.getItem('qr_changelog_last_seen') !== appVersion.value
})

const openChangelog = async () => {
    showChangelog.value = true
    showSidebar.value = false
    if (!changelogData.value) {
        changelogLoading.value = true
        try {
            const res = await fetch(route('changelog'), { headers: { 'Accept': 'application/json' } })
            changelogData.value = await res.json()
        } catch (e) {
            console.error('Failed to load changelog', e)
        } finally {
            changelogLoading.value = false
        }
    }
    if (typeof window !== 'undefined' && appVersion.value) {
        localStorage.setItem('qr_changelog_last_seen', appVersion.value)
    }
}
const closeChangelog = () => { showChangelog.value = false }

// Lightbox for changelog images
const lightboxImage = ref(null)
const openLightbox = (img) => { lightboxImage.value = img }
const closeLightbox = () => { lightboxImage.value = null }

// Minimal inline markdown: `code`, **bold**, *italic*. HTML-escape first.
const renderInline = (text) => {
    const esc = String(text)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;')
    return esc
        .replace(/`([^`]+)`/g, '<code class="px-1 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-xs">$1</code>')
        .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
        .replace(/(^|\s)\*([^*]+)\*/g, '$1<em>$2</em>')
}

// Theme
const { themePreference, init: initTheme, cycleTheme } = useTheme(user)
onMounted(initTheme)
watch(() => user.value?.theme_preference, (pref) => {
    if (pref) themePreference.value = pref
})
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-950 flex flex-col">
        <!-- Mobile sidebar overlay -->
        <div v-if="showSidebar && !isGuest" class="fixed inset-0 z-40 lg:hidden" @click="showSidebar = false">
            <div class="fixed inset-0 bg-black/50"></div>
        </div>

        <!-- Sidebar (authenticated only) -->
        <aside v-if="!isGuest"
            :class="[showSidebar ? 'translate-x-0' : 'translate-x-full lg:translate-x-0']"
            class="fixed inset-y-0 right-0 lg:right-auto lg:left-0 z-50 w-64 bg-white dark:bg-gray-900 border-l lg:border-l-0 lg:border-r border-gray-200 dark:border-gray-800 transition-transform duration-200 ease-in-out lg:z-0 flex flex-col">
            <div class="flex items-center justify-between h-16 px-6 border-b border-gray-200 dark:border-gray-800">
                <Link :href="route('dashboard')" class="flex items-center gap-2.5 min-w-0" @click="showSidebar = false">
                    <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    </div>
                    <span class="font-bold text-gray-900 dark:text-white text-lg truncate">QR Lab</span>
                </Link>
                <button @click="showSidebar = false" class="lg:hidden p-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <nav class="p-4 space-y-1 flex-1">
                <Link :href="route('dashboard')"
                    :class="[isActive('dashboard') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800']"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
                    @click="showSidebar = false">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </Link>
                <Link :href="route('qr.create')"
                    :class="[page.url.startsWith('/qr') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800']"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
                    @click="showSidebar = false">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    QR Codes
                </Link>
                <Link :href="route('links.index')"
                    :class="[page.url.startsWith('/links') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800']"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
                    @click="showSidebar = false">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    URL Shortener
                </Link>

                <!-- Admin section -->
                <template v-if="isAdmin">
                    <div class="pt-4 mt-4 border-t border-gray-200 dark:border-gray-800">
                        <p class="px-3 mb-2 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Admin</p>
                        <Link :href="route('admin.users.index')"
                            :class="[page.url.startsWith('/admin/users') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800']"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
                            @click="showSidebar = false">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            Users
                        </Link>
                        <Link v-if="isSuperAdmin" :href="route('admin.stats')"
                            :class="[page.url === '/admin/stats' ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800']"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
                            @click="showSidebar = false">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18M8 17V9m4 8V5m4 12v-6"/></svg>
                            QR Stats
                        </Link>
                        <Link v-if="isSuperAdmin" :href="route('admin.link-stats')"
                            :class="[page.url === '/admin/link-stats' ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800']"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
                            @click="showSidebar = false">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                            Link Stats
                        </Link>
                    </div>
                </template>
            </nav>

            <!-- Sidebar footer -->
            <div class="p-4 border-t border-gray-200 dark:border-gray-800 space-y-1">
                <button @click="openFeedback"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors w-full">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    Send feedback
                </button>
                <button @click="openChangelog"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors w-full">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="flex-1 text-left">What's new</span>
                    <span v-if="hasUnseenChangelog" class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-primary-600 text-white">NEW</span>
                    <span v-else-if="appVersion" class="text-[10px] text-gray-400">v{{ appVersion }}</span>
                </button>
                <Link :href="route('profile.edit')"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center text-primary-700 dark:text-primary-400 text-xs font-bold">
                        {{ user?.name?.charAt(0)?.toUpperCase() }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="truncate text-gray-900 dark:text-white">{{ user?.name }}</div>
                        <div class="truncate text-xs text-gray-400">{{ user?.email }}</div>
                    </div>
                </Link>
                <button @click="logout" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors w-full">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Sign out
                </button>
            </div>
        </aside>

        <!-- Guest header -->
        <header v-if="isGuest" class="sticky top-0 z-30 bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm border-b border-gray-200 dark:border-gray-800">
            <div class="max-w-7xl mx-auto flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                <Link href="/" class="flex items-center gap-2.5">
                    <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    </div>
                    <span class="font-bold text-gray-900 dark:text-white text-lg">QR Lab</span>
                </Link>
                <div class="flex items-center gap-2">
                    <button @click="cycleTheme"
                        :title="themePreference === 'light' ? 'Light mode' : themePreference === 'dark' ? 'Dark mode' : 'Auto mode'"
                        class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <!-- Sun: light -->
                        <svg v-if="themePreference === 'light'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/></svg>
                        <!-- Moon: dark -->
                        <svg v-else-if="themePreference === 'dark'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        <!-- Monitor: auto -->
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </button>
                    <Link :href="route('login')"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                        Sign in
                    </Link>
                </div>
            </div>
        </header>

        <!-- Main content -->
        <div :class="[isGuest ? '' : 'lg:pl-64']" class="flex-1 flex flex-col">
            <!-- Auth header (mobile hamburger + theme toggle) -->
            <header v-if="!isGuest" class="sticky top-0 z-30 h-16 bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm border-b border-gray-200 dark:border-gray-800">
                <div class="flex items-center justify-end gap-1 h-full px-4 sm:px-6">
                    <button @click="cycleTheme"
                        :title="themePreference === 'light' ? 'Light mode' : themePreference === 'dark' ? 'Dark mode' : 'Auto mode'"
                        class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <svg v-if="themePreference === 'light'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/></svg>
                        <svg v-else-if="themePreference === 'dark'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </button>
                    <button @click="showSidebar = !showSidebar" class="lg:hidden p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </header>

            <main :class="[isGuest ? 'max-w-7xl mx-auto w-full' : '']" class="p-4 sm:p-6 lg:p-8 pb-20 lg:pb-8 flex-1">
                <slot />
            </main>

            <!-- Footer -->
            <footer class="border-t border-gray-200 dark:border-gray-800 py-4 px-4 sm:px-6 lg:px-8">
                <p class="text-center text-xs text-gray-400 dark:text-gray-500">
                    &copy; {{ new Date().getFullYear() }} ICTWebSolution B.V. All rights reserved.
                </p>
            </footer>
        </div>

        <!-- Flash toast -->
        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="translate-y-2 opacity-0" enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition ease-in duration-200" leave-from-class="translate-y-0 opacity-100" leave-to-class="translate-y-2 opacity-0">
            <div v-if="showFlash" class="fixed bottom-20 sm:bottom-6 right-4 sm:right-6 z-[60] max-w-sm">
                <div :class="[flashMessage.type === 'success' ? 'bg-green-600' : 'bg-red-600']"
                    class="text-white px-4 py-3 rounded-xl shadow-lg text-sm font-medium flex items-center gap-2">
                    <svg v-if="flashMessage.type === 'success'" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <svg v-else class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    {{ flashMessage.text }}
                    <button @click="showFlash = false" class="ml-2 opacity-75 hover:opacity-100">&times;</button>
                </div>
            </div>
        </Transition>

        <!-- Feedback modal -->
        <Transition enter-active-class="transition ease-out duration-150" enter-from-class="opacity-0" enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showFeedback && !isGuest" class="fixed inset-0 z-[70] flex items-center justify-center p-4" @click.self="closeFeedback">
                <div class="fixed inset-0 bg-black/50" @click="closeFeedback"></div>
                <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
                    <div class="flex items-start justify-between px-6 pt-5 pb-2">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Send feedback</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Report a bug, suggest an improvement, or just say hi.</p>
                        </div>
                        <button @click="closeFeedback" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form @submit.prevent="submitFeedback" class="px-6 pb-6 space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name <span class="text-gray-400 font-normal">(optional)</span></label>
                                <input v-model="feedbackForm.name" type="text" maxlength="100"
                                    class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                                <p v-if="feedbackForm.errors.name" class="text-red-500 text-xs mt-1">{{ feedbackForm.errors.name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email <span class="text-gray-400 font-normal">(optional)</span></label>
                                <input v-model="feedbackForm.email" type="email" maxlength="255"
                                    class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                                <p v-if="feedbackForm.errors.email" class="text-red-500 text-xs mt-1">{{ feedbackForm.errors.email }}</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Message</label>
                            <textarea v-model="feedbackForm.message" rows="5" required maxlength="5000"
                                placeholder="What's on your mind?"
                                class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition resize-y"></textarea>
                            <p v-if="feedbackForm.errors.message" class="text-red-500 text-xs mt-1">{{ feedbackForm.errors.message }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Screenshots <span class="text-gray-400 font-normal">(optional, up to 5, 5&nbsp;MB each)</span></label>
                            <input type="file" accept="image/*" multiple @change="onFeedbackFiles"
                                class="block w-full text-sm text-gray-600 dark:text-gray-400 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gray-100 dark:file:bg-gray-800 file:text-gray-700 dark:file:text-gray-300 hover:file:bg-gray-200 dark:hover:file:bg-gray-700" />
                            <ul v-if="feedbackFiles.length" class="mt-2 space-y-1">
                                <li v-for="(f, idx) in feedbackFiles" :key="idx"
                                    class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 rounded-lg px-3 py-1.5">
                                    <span class="truncate">{{ f.name }} <span class="text-gray-400">({{ Math.round(f.size / 1024) }} KB)</span></span>
                                    <button type="button" @click="removeFeedbackFile(idx)" class="ml-2 text-red-500 hover:text-red-600">&times;</button>
                                </li>
                            </ul>
                            <p v-if="feedbackForm.errors.screenshots || feedbackForm.errors['screenshots.0']" class="text-red-500 text-xs mt-1">
                                {{ feedbackForm.errors.screenshots || feedbackForm.errors['screenshots.0'] }}
                            </p>
                            <div v-if="feedbackForm.progress" class="mt-2 h-1 bg-gray-200 dark:bg-gray-800 rounded-full overflow-hidden">
                                <div class="h-full bg-primary-600 transition-all" :style="{ width: feedbackForm.progress.percentage + '%' }"></div>
                            </div>
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button type="submit" :disabled="feedbackForm.processing"
                                class="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors text-sm disabled:opacity-50">
                                {{ feedbackForm.processing ? 'Sending…' : 'Send feedback' }}
                            </button>
                            <button type="button" @click="closeFeedback"
                                class="px-4 py-2.5 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-xl text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <!-- Changelog modal -->
        <Transition enter-active-class="transition ease-out duration-150" enter-from-class="opacity-0" enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showChangelog && !isGuest" class="fixed inset-0 z-[70] flex items-center justify-center p-4" @click.self="closeChangelog">
                <div class="fixed inset-0 bg-black/50" @click="closeChangelog"></div>
                <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col">
                    <div class="flex items-start justify-between px-6 pt-5 pb-3 border-b border-gray-100 dark:border-gray-800">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">What's new</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                Release notes for QR Lab
                                <span v-if="appVersion">· <span class="font-medium text-gray-700 dark:text-gray-300">v{{ appVersion }}</span></span>
                            </p>
                        </div>
                        <button @click="closeChangelog" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="overflow-y-auto px-6 py-5 flex-1">
                        <div v-if="changelogLoading" class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">Loading release notes…</div>

                        <div v-else-if="!changelogData?.versions?.length" class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">No release notes yet.</div>

                        <div v-else class="space-y-8">
                            <section v-for="v in changelogData.versions" :key="v.version">
                                <div class="flex items-baseline gap-2 mb-3">
                                    <h3 class="text-base font-bold text-gray-900 dark:text-white">v{{ v.version }}</h3>
                                    <span v-if="v.version === changelogData.app_version"
                                        class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-primary-600 text-white">
                                        Current
                                    </span>
                                    <span v-if="v.date" class="text-xs text-gray-500 dark:text-gray-400 ml-auto">{{ v.date }}</span>
                                </div>

                                <div v-if="v.images?.length" class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                                    <button v-for="(img, i) in v.images" :key="i" type="button" @click="openLightbox(img)"
                                        class="block rounded-lg overflow-hidden border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800 hover:border-primary-400 transition-colors cursor-zoom-in">
                                        <img :src="img.src" :alt="img.alt" class="w-full h-auto block" loading="lazy" />
                                    </button>
                                </div>

                                <div v-for="s in v.sections" :key="s.title" class="mb-3 last:mb-0">
                                    <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1.5">{{ s.title }}</h4>
                                    <ul class="space-y-1.5 text-sm text-gray-700 dark:text-gray-300">
                                        <li v-for="(item, i) in s.items" :key="i" class="flex gap-2">
                                            <span class="text-primary-500 mt-1">•</span>
                                            <span v-html="renderInline(item)"></span>
                                        </li>
                                    </ul>
                                </div>
                            </section>
                        </div>
                    </div>

                    <div class="px-6 py-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                        <a href="https://github.com/ICTWebSolutionBV/qrlab/blob/main/CHANGELOG.md" target="_blank" rel="noopener"
                            class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                            View full CHANGELOG on GitHub ↗
                        </a>
                        <button type="button" @click="closeChangelog"
                            class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors text-sm">
                            Got it
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Lightbox for changelog images -->
        <Transition enter-active-class="transition ease-out duration-150" enter-from-class="opacity-0" enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="lightboxImage" class="fixed inset-0 z-[80] flex items-center justify-center p-4 bg-black/85" @click.self="closeLightbox" @keydown.esc="closeLightbox" tabindex="0">
                <button type="button" @click="closeLightbox" class="absolute top-4 right-4 p-2 rounded-full bg-white/10 hover:bg-white/20 text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <figure class="max-w-[95vw] max-h-[92vh] flex flex-col items-center gap-3" @click.stop>
                    <img :src="lightboxImage.src" :alt="lightboxImage.alt" class="max-w-full max-h-[85vh] rounded-lg shadow-2xl" />
                    <figcaption v-if="lightboxImage.alt" class="text-sm text-white/80 text-center px-4">{{ lightboxImage.alt }}</figcaption>
                </figure>
            </div>
        </Transition>

        <!-- Mobile bottom nav (authenticated only) -->
        <nav v-if="!isGuest" class="fixed bottom-0 left-0 right-0 z-40 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 lg:hidden">
            <div class="flex justify-around py-2">
                <Link :href="route('dashboard')"
                    :class="[isActive('dashboard') ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400 dark:text-gray-500']"
                    class="flex flex-col items-center gap-0.5 px-3 py-1 text-xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </Link>
                <Link :href="route('qr.create')"
                    :class="[page.url.startsWith('/qr/create') ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400 dark:text-gray-500']"
                    class="flex flex-col items-center gap-0.5 px-3 py-1 text-xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Create
                </Link>
                <Link :href="route('profile.edit')"
                    :class="[page.url.startsWith('/profile') ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400 dark:text-gray-500']"
                    class="flex flex-col items-center gap-0.5 px-3 py-1 text-xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Profile
                </Link>
            </div>
        </nav>
    </div>
</template>
