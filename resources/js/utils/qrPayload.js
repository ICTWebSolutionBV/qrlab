// Build the QR payload string for any QR code type.
// Mirrors the logic in Pages/QrCode/Edit.vue so the dashboard modal
// and other views can render a preview without duplicating code.

function buildMailto(e) {
    if (!e) return 'mailto:'
    let uri = `mailto:${e.to || ''}`
    const params = []
    if (e.subject) params.push(`subject=${encodeURIComponent(e.subject)}`)
    if (e.body)    params.push(`body=${encodeURIComponent(e.body)}`)
    if (params.length) uri += '?' + params.join('&')
    return uri
}

function buildVcard(v) {
    if (!v) return 'BEGIN:VCARD\nVERSION:3.0\nFN:Contact\nEND:VCARD'
    const lines = ['BEGIN:VCARD', 'VERSION:3.0']
    const fn = [v.first_name, v.last_name].filter(Boolean).join(' ')
    lines.push(`N:${v.last_name || ''};${v.first_name || ''};;;`)
    lines.push(`FN:${fn || 'Contact'}`)
    if (v.company)    lines.push(`ORG:${v.company}`)
    if (v.job_title)  lines.push(`TITLE:${v.job_title}`)
    if (v.mobile)     lines.push(`TEL;TYPE=CELL:${v.mobile}`)
    if (v.phone)      lines.push(`TEL;TYPE=WORK:${v.phone}`)
    if (v.fax)        lines.push(`TEL;TYPE=FAX:${v.fax}`)
    if (v.email)      lines.push(`EMAIL:${v.email}`)
    if (v.street || v.city || v.postal_code || v.province || v.country)
        lines.push(`ADR;TYPE=WORK:;;${v.street || ''};${v.city || ''};${v.province || ''};${v.postal_code || ''};${v.country || ''}`)
    if (v.website)    lines.push(`URL:${v.website}`)
    lines.push('END:VCARD')
    return lines.join('\n')
}

function buildWifi(qr) {
    const enc = ['WPA', 'WPA2', 'WPA3'].includes(qr.encryption) ? 'WPA'
        : qr.encryption === 'WEP' ? 'WEP' : 'nopass'
    const esc = (v) => (v || '').replace(/([\\;,":])/g, '\\$1')
    const ssid = esc(qr.ssid || 'MyNetwork')
    const pass = esc(qr.password || '')
    const hidden = qr.hidden_network ? 'true' : 'false'
    return `WIFI:T:${enc};S:${ssid};P:${pass};H:${hidden};;`
}

/**
 * Build the data string encoded in the QR.
 * For URL QRs with tracking enabled, this returns the short-link URL
 * so scans get counted; otherwise the raw URL.
 */
export function buildQrPayload(qr, appUrl = '') {
    if (!qr) return ''
    switch (qr.type) {
        case 'url':
            if (qr.tracking_enabled && qr.short_code) {
                return `${appUrl}/r/${qr.short_code}`
            }
            return qr.url || ''
        case 'phone':
            return qr.url ? `tel:${qr.url}` : ''
        case 'vcard':
            return buildVcard(qr.vcard_data)
        case 'email':
            return buildMailto(qr.email_data)
        case 'wifi':
        default:
            return buildWifi(qr)
    }
}
