// Lightweight date/time formatter driven by user preferences shared via Inertia.
// Uses Intl APIs (no moment/luxon dep); supports a small curated pattern set.

import { usePage } from '@inertiajs/vue3'

export function useDateTimePrefs() {
    const page = usePage()
    const user = page.props?.auth?.user
    return {
        timezone: user?.timezone || 'Europe/Amsterdam',
        dateFormat: user?.date_format || 'DD-MM-YYYY',
        timeFormat: user?.time_format || 'HH:mm:ss',
    }
}

function toParts(iso, timezone) {
    if (!iso) return null
    const d = iso instanceof Date ? iso : new Date(iso)
    if (Number.isNaN(d.getTime())) return null
    const dt = new Intl.DateTimeFormat('en-US', {
        timeZone: timezone,
        year: 'numeric', month: '2-digit', day: '2-digit',
        hour: '2-digit', minute: '2-digit', second: '2-digit',
        hourCycle: 'h23',
    }).formatToParts(d).reduce((acc, p) => {
        if (p.type !== 'literal') acc[p.type] = p.value
        return acc
    }, {})

    const monthShortNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']
    return {
        YYYY: dt.year,
        MM: dt.month,
        DD: dt.day,
        D: String(parseInt(dt.day, 10)),
        MMM: monthShortNames[parseInt(dt.month, 10) - 1] || dt.month,
        HH: dt.hour,
        mm: dt.minute,
        ss: dt.second,
    }
}

function applyDate(parts, fmt) {
    if (!parts) return '—'
    return fmt
        .replace('YYYY', parts.YYYY)
        .replace('MMM', parts.MMM)
        .replace('MM', parts.MM)
        .replace('DD', parts.DD)
        .replace(/\bD\b/, parts.D)
}

function applyTime(parts, fmt) {
    if (!parts) return ''
    if (fmt === 'HH:mm:ss') return `${parts.HH}:${parts.mm}:${parts.ss}`
    if (fmt === 'HH:mm') return `${parts.HH}:${parts.mm}`
    // 12-hour variants
    const h24 = parseInt(parts.HH, 10)
    const ampm = h24 >= 12 ? 'PM' : 'AM'
    const h12 = String(((h24 + 11) % 12) + 1).padStart(2, '0')
    if (fmt === 'hh:mm:ss A') return `${h12}:${parts.mm}:${parts.ss} ${ampm}`
    if (fmt === 'hh:mm A') return `${h12}:${parts.mm} ${ampm}`
    return `${parts.HH}:${parts.mm}:${parts.ss}`
}

export function formatDate(iso, prefs) {
    const p = prefs || useDateTimePrefs()
    return applyDate(toParts(iso, p.timezone), p.dateFormat)
}

export function formatTime(iso, prefs) {
    const p = prefs || useDateTimePrefs()
    return applyTime(toParts(iso, p.timezone), p.timeFormat)
}

export function formatDateTime(iso, prefs) {
    const p = prefs || useDateTimePrefs()
    const parts = toParts(iso, p.timezone)
    return `${applyDate(parts, p.dateFormat)} ${applyTime(parts, p.timeFormat)}`
}
