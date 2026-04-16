import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

export function applyTheme(preference) {
    if (preference === 'dark') {
        document.documentElement.classList.add('dark')
    } else if (preference === 'light') {
        document.documentElement.classList.remove('dark')
    } else {
        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    }
}

export function useTheme(user) {
    const themePreference = ref('auto')

    function init() {
        if (user?.value) {
            themePreference.value = user.value.theme_preference || 'auto'
        } else {
            themePreference.value = localStorage.getItem('theme') || 'auto'
        }
    }

    function setTheme(pref) {
        themePreference.value = pref
        applyTheme(pref)
        if (user?.value) {
            router.put(route('profile.theme'), { theme_preference: pref }, { preserveScroll: true, preserveState: true })
        } else {
            localStorage.setItem('theme', pref)
        }
    }

    function cycleTheme() {
        const order = ['light', 'dark', 'auto']
        const idx = order.indexOf(themePreference.value)
        setTheme(order[(idx + 1) % order.length])
    }

    return { themePreference, init, setTheme, cycleTheme }
}
