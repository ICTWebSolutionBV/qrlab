<script setup>
import { ref, computed } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import QrPreview from '@/Components/QrPreview.vue'
import QrFrame from '@/Components/QrFrame.vue'
import TextLabelEditor from '@/Components/TextLabelEditor.vue'
import { getFontStack, monospace, sansSerif } from '@/config/fonts.js'

const props = defineProps({
    appUrl: String,
})

const page = usePage()
const user = computed(() => page.props.auth?.user)

const qrPreviewRef = ref(null)
const logoPreview = ref(null)
const showTextLabels = ref(false)
const showDesign = ref(false)
const showLogo = ref(false)

function toggleLogo() {
    showLogo.value = !showLogo.value
    if (!showLogo.value) removeLogo()
}
const wifiCustomSize = ref(false)
const wifiIsCustomSize = computed(() => wifiCustomSize.value || !['12','14','16','18','20','24'].includes(String(form.wifi_details_font_size)))

function onWifiSizeChange(val) {
    if (val === 'custom') {
        wifiCustomSize.value = true
    } else {
        wifiCustomSize.value = false
        form.wifi_details_font_size = val
    }
}

const wifiPasswordCustomSize = ref(false)
const wifiPasswordIsCustomSize = computed(() => wifiPasswordCustomSize.value || !['12','14','16','18','20','24'].includes(String(form.wifi_details_password_font_size)))

function onWifiPasswordSizeChange(val) {
    if (val === 'custom') {
        wifiPasswordCustomSize.value = true
    } else {
        wifiPasswordCustomSize.value = false
        form.wifi_details_password_font_size = val
    }
}

function toggleWifiCustomMargin() {
    const next = !form.wifi_details_margin_custom
    form.wifi_details_margin_custom = next
    if (next) {
        form.wifi_details_margin_top = form.wifi_details_margin
        form.wifi_details_margin_right = 0
        form.wifi_details_margin_bottom = form.wifi_details_margin
        form.wifi_details_margin_left = 0
    }
}

const form = useForm({
    type: 'wifi',
    name: '',
    url: '',
    email_data: {
        to: '',
        subject: '',
        body: '',
    },
    vcard_data: {
        first_name: '',
        last_name: '',
        mobile: '',
        phone: '',
        fax: '',
        email: '',
        company: '',
        job_title: '',
        street: '',
        city: '',
        postal_code: '',
        province: '',
        country: '',
        website: '',
    },
    ssid: '',
    password: '',
    encryption: 'WPA2',
    hidden_network: false,
    foreground_color: '#000000',
    background_color: '#FFFFFF',
    dot_style: 'square',
    corner_square_style: 'square',
    corner_dot_style: 'square',
    error_correction: 'M',
    size: 300,
    margin: 10,
    header_text: '',
    header_font_size: '16',
    header_color: '#000000',
    header_alignment: 'center',
    header_margin: 8,
    header_font_family: 'Inter',
    header_bold: false,
    header_italic: false,
    header_underline: false,
    header_margin_custom: false,
    header_margin_top: 8,
    header_margin_right: 0,
    header_margin_bottom: 8,
    header_margin_left: 0,
    footer_text: '',
    footer_font_size: '14',
    footer_color: '#000000',
    footer_alignment: 'center',
    footer_margin: 8,
    footer_font_family: 'Inter',
    footer_bold: false,
    footer_italic: false,
    footer_underline: false,
    footer_margin_custom: false,
    footer_margin_top: 8,
    footer_margin_right: 0,
    footer_margin_bottom: 8,
    footer_margin_left: 0,
    show_wifi_details: false,
    wifi_details_font_family: 'Inter',
    wifi_details_font_size: '14',
    wifi_details_color: '#000000',
    wifi_details_alignment: 'center',
    wifi_details_bold: false,
    wifi_details_italic: false,
    wifi_details_underline: false,
    wifi_details_password_font: 'Consolas',
    wifi_details_show_password: true,
    wifi_details_margin: 8,
    wifi_details_margin_custom: false,
    wifi_details_margin_top: 8,
    wifi_details_margin_right: 0,
    wifi_details_margin_bottom: 8,
    wifi_details_margin_left: 0,
    wifi_details_password_separate_style: false,
    wifi_details_password_font_size: '14',
    wifi_details_password_bold: false,
    wifi_details_password_italic: false,
    wifi_details_password_underline: false,
    wifi_details_password_color: '#000000',
    logo: null,
    logo_size: 30,
    tracking_enabled: false,
    frame_style: 'none',
    frame_color: '#111827',
    frame_label_color: '#111827',
    frame_text: 'SCAN ME',
    frame_font_size: 14,
    frame_font_family: 'system-ui',
    frame_label_gap: 10,
    frame_icon: 'none',
})

const frameStyles = [
    { value: 'none',            label: 'None' },
    { value: 'scan-me-top',     label: 'Scan me — top' },
    { value: 'scan-me-bottom',  label: 'Scan me — bottom' },
    { value: 'rounded',         label: 'Rounded border' },
    { value: 'banner-bottom',   label: 'Banner' },
    { value: 'tag-notch',       label: 'Tag with notch' },
    { value: 'pill-icon',       label: 'Pill with icon' },
    { value: 'arrow-br',        label: 'Cursive arrow — BR' },
    { value: 'arrow-bl',        label: 'Cursive arrow — BL' },
    { value: 'arrow-tr',        label: 'Cursive arrow — TR' },
    { value: 'arrow-tl',        label: 'Cursive arrow — TL' },
]

// Font families for the frame label. Cursive options are also used by the
// "Scan me" text in the arrow variants.
const frameFontFamilies = [
    { value: 'system-ui',                                                        label: 'Sans-serif (System)' },
    { value: '"Helvetica Neue", Helvetica, Arial, sans-serif',                   label: 'Helvetica' },
    { value: 'Georgia, "Times New Roman", serif',                                label: 'Serif (Georgia)' },
    { value: '"Courier New", Courier, monospace',                                label: 'Monospace' },
    { value: '"Impact", "Haettenschweiler", "Arial Narrow Bold", sans-serif',    label: 'Impact' },
    { value: '"Brush Script MT", "Lucida Handwriting", cursive',                 label: 'Cursive (Brush)' },
    { value: '"Comic Sans MS", "Chalkboard SE", cursive',                        label: 'Comic Sans' },
    { value: '"Trebuchet MS", sans-serif',                                       label: 'Trebuchet' },
    { value: '"Verdana", Geneva, sans-serif',                                    label: 'Verdana' },
]

// Curated icon set from lucide (well-known open icon library).
const frameIcons = [
    { value: 'none',        label: 'No icon' },
    { value: 'Play',        label: 'Play' },
    { value: 'Camera',      label: 'Camera' },
    { value: 'ShoppingBag', label: 'Shopping bag' },
    { value: 'ShoppingCart',label: 'Shopping cart' },
    { value: 'Heart',       label: 'Heart' },
    { value: 'Star',        label: 'Star' },
    { value: 'Globe',       label: 'Globe' },
    { value: 'Wifi',        label: 'WiFi' },
    { value: 'Phone',       label: 'Phone' },
    { value: 'Mail',        label: 'Mail' },
    { value: 'MapPin',      label: 'Map pin' },
    { value: 'Download',    label: 'Download' },
    { value: 'Gift',        label: 'Gift' },
    { value: 'Coffee',      label: 'Coffee' },
    { value: 'Music',       label: 'Music' },
    { value: 'Video',       label: 'Video' },
    { value: 'QrCode',      label: 'QR code' },
    { value: 'Smartphone',  label: 'Smartphone' },
    { value: 'Instagram',   label: 'Instagram' },
    { value: 'Youtube',     label: 'YouTube' },
    { value: 'Facebook',    label: 'Facebook' },
]

function buildMailto(e) {
    let uri = `mailto:${e.to || ''}`
    const params = []
    if (e.subject) params.push(`subject=${encodeURIComponent(e.subject)}`)
    if (e.body)    params.push(`body=${encodeURIComponent(e.body)}`)
    if (params.length) uri += '?' + params.join('&')
    return uri
}

function buildVcard(v) {
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

const qrContent = computed(() => {
    if (form.type === 'url') {
        return form.url || 'https://example.com'
    }
    if (form.type === 'phone') {
        return form.url ? `tel:${form.url}` : 'tel:+31612345678'
    }
    if (form.type === 'vcard') {
        return buildVcard(form.vcard_data)
    }
    if (form.type === 'email') {
        return buildMailto(form.email_data)
    }
    const enc = ['WPA', 'WPA2', 'WPA3'].includes(form.encryption) ? 'WPA' :
        form.encryption === 'WEP' ? 'WEP' : 'nopass'
    const ssid = (form.ssid || 'MyNetwork').replace(/([\\;,":])/, '\\$1')
    const pass = (form.password || '').replace(/([\\;,":])/, '\\$1')
    const hidden = form.hidden_network ? 'true' : 'false'
    return `WIFI:T:${enc};S:${ssid};P:${pass};H:${hidden};;`
})

// TextLabelEditor bindings
const headerLabel = computed(() => ({
    text: form.header_text,
    fontFamily: form.header_font_family,
    fontSize: form.header_font_size,
    color: form.header_color,
    bold: form.header_bold,
    italic: form.header_italic,
    underline: form.header_underline,
    alignment: form.header_alignment,
    margin: form.header_margin,
    marginCustom: form.header_margin_custom,
    marginTop: form.header_margin_top,
    marginRight: form.header_margin_right,
    marginBottom: form.header_margin_bottom,
    marginLeft: form.header_margin_left,
}))

function updateHeader(val) {
    form.header_text = val.text
    form.header_font_family = val.fontFamily
    form.header_font_size = val.fontSize
    form.header_color = val.color
    form.header_bold = val.bold
    form.header_italic = val.italic
    form.header_underline = val.underline
    form.header_alignment = val.alignment
    form.header_margin = val.margin
    form.header_margin_custom = val.marginCustom
    form.header_margin_top = val.marginTop
    form.header_margin_right = val.marginRight
    form.header_margin_bottom = val.marginBottom
    form.header_margin_left = val.marginLeft
}

const footerLabel = computed(() => ({
    text: form.footer_text,
    fontFamily: form.footer_font_family,
    fontSize: form.footer_font_size,
    color: form.footer_color,
    bold: form.footer_bold,
    italic: form.footer_italic,
    underline: form.footer_underline,
    alignment: form.footer_alignment,
    margin: form.footer_margin,
    marginCustom: form.footer_margin_custom,
    marginTop: form.footer_margin_top,
    marginRight: form.footer_margin_right,
    marginBottom: form.footer_margin_bottom,
    marginLeft: form.footer_margin_left,
}))

function updateFooter(val) {
    form.footer_text = val.text
    form.footer_font_family = val.fontFamily
    form.footer_font_size = val.fontSize
    form.footer_color = val.color
    form.footer_bold = val.bold
    form.footer_italic = val.italic
    form.footer_underline = val.underline
    form.footer_alignment = val.alignment
    form.footer_margin = val.margin
    form.footer_margin_custom = val.marginCustom
    form.footer_margin_top = val.marginTop
    form.footer_margin_right = val.marginRight
    form.footer_margin_bottom = val.marginBottom
    form.footer_margin_left = val.marginLeft
}

// Preview style helpers
const headerStyle = computed(() => ({
    fontFamily: getFontStack(form.header_font_family),
    fontSize: form.header_font_size + 'px',
    color: form.header_color,
    fontWeight: form.header_bold ? '700' : '400',
    fontStyle: form.header_italic ? 'italic' : 'normal',
    textDecoration: form.header_underline ? 'underline' : 'none',
    textAlign: form.header_alignment,
    ...(form.header_margin_custom
        ? { margin: `${form.header_margin_top}px ${form.header_margin_right}px ${form.header_margin_bottom}px ${form.header_margin_left}px` }
        : { marginBottom: form.header_margin + 'px' }
    ),
}))

const footerStyle = computed(() => ({
    fontFamily: getFontStack(form.footer_font_family),
    fontSize: form.footer_font_size + 'px',
    color: form.footer_color,
    fontWeight: form.footer_bold ? '700' : '400',
    fontStyle: form.footer_italic ? 'italic' : 'normal',
    textDecoration: form.footer_underline ? 'underline' : 'none',
    textAlign: form.footer_alignment,
    ...(form.footer_margin_custom
        ? { margin: `${form.footer_margin_top}px ${form.footer_margin_right}px ${form.footer_margin_bottom}px ${form.footer_margin_left}px` }
        : { marginTop: form.footer_margin + 'px' }
    ),
}))

const wifiDetailsBaseStyle = computed(() => ({
    fontFamily: getFontStack(form.wifi_details_font_family),
    fontSize: form.wifi_details_font_size + 'px',
    color: form.wifi_details_color,
    textAlign: form.wifi_details_alignment,
    fontWeight: form.wifi_details_bold ? '700' : '400',
    fontStyle: form.wifi_details_italic ? 'italic' : 'normal',
    textDecoration: form.wifi_details_underline ? 'underline' : 'none',
    ...(form.wifi_details_margin_custom
        ? { margin: `${form.wifi_details_margin_top}px ${form.wifi_details_margin_right}px ${form.wifi_details_margin_bottom}px ${form.wifi_details_margin_left}px` }
        : { marginTop: form.wifi_details_margin + 'px' }
    ),
}))

const wifiPasswordStyle = computed(() => ({
    fontFamily: getFontStack(form.wifi_details_password_font),
    ...(form.wifi_details_password_separate_style ? {
        fontSize: form.wifi_details_password_font_size + 'px',
        fontWeight: form.wifi_details_password_bold ? '700' : '400',
        fontStyle: form.wifi_details_password_italic ? 'italic' : 'normal',
        textDecoration: form.wifi_details_password_underline ? 'underline' : 'none',
        color: form.wifi_details_password_color,
    } : {}),
}))

const dotStyles = [
    { value: 'square', label: 'Square' },
    { value: 'dots', label: 'Dots' },
    { value: 'rounded', label: 'Rounded' },
    { value: 'extra-rounded', label: 'Extra Rounded' },
    { value: 'classy', label: 'Classy' },
    { value: 'classy-rounded', label: 'Classy Rounded' },
]

const cornerStyles = [
    { value: 'square', label: 'Square' },
    { value: 'dot', label: 'Dot' },
    { value: 'extra-rounded', label: 'Rounded' },
]

const onLogoChange = (e) => {
    const file = e.target.files[0]
    if (file) {
        form.logo = file
        logoPreview.value = URL.createObjectURL(file)
    }
}

const removeLogo = () => {
    form.logo = null
    logoPreview.value = null
}

const submit = () => {
    form.post(route('qr.store'), {
        forceFormData: true,
    })
}

const roundedExport = ref(true)

const downloadQr = async (ext) => {
    qrPreviewRef.value?.download(form.name || 'qrcode', ext, roundedExport.value)
}
</script>

<template>
    <Head :title="form.type === 'url' ? 'URL QR Code Generator' : 'WiFi QR Code Generator'" />
    <AppLayout>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">QR Code Generator</h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm mb-6">Generate a QR code for a URL or WiFi network.</p>

        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Left: Form -->
            <div class="flex-1 space-y-6">

                <!-- Type switcher -->
                <div class="flex gap-2 p-1 bg-gray-100 dark:bg-gray-800 rounded-xl w-fit">
                    <button type="button" @click="form.type = 'wifi'"
                        :class="['px-4 py-2 text-sm font-medium rounded-lg transition-colors', form.type === 'wifi' ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300']">
                        WiFi
                    </button>
                    <button type="button" @click="form.type = 'url'"
                        :class="['px-4 py-2 text-sm font-medium rounded-lg transition-colors', form.type === 'url' ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300']">
                        URL
                    </button>
                    <button type="button" @click="form.type = 'phone'"
                        :class="['px-4 py-2 text-sm font-medium rounded-lg transition-colors', form.type === 'phone' ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300']">
                        Phone
                    </button>
                    <button type="button" @click="form.type = 'vcard'"
                        :class="['px-4 py-2 text-sm font-medium rounded-lg transition-colors', form.type === 'vcard' ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300']">
                        vCard
                    </button>
                    <button type="button" @click="form.type = 'email'"
                        :class="['px-4 py-2 text-sm font-medium rounded-lg transition-colors', form.type === 'email' ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300']">
                        Email
                    </button>
                </div>

                <!-- WiFi Details -->
                <div v-if="form.type === 'wifi'" class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">WiFi Details</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Network Name (SSID)</label>
                            <input v-model="form.ssid" type="text" placeholder="SSID"
                                class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                            <p v-if="form.errors.ssid" class="text-red-500 text-xs mt-1">{{ form.errors.ssid }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                            <input v-model="form.password" type="text" placeholder="WiFi password"
                                class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                        </div>
                        <div class="flex gap-4">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Encryption</label>
                                <select v-model="form.encryption"
                                    class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                                    <option value="WPA2">WPA2</option>
                                    <option value="WPA">WPA</option>
                                    <option value="WPA3">WPA3</option>
                                    <option value="WEP">WEP</option>
                                    <option value="nopass">None</option>
                                </select>
                            </div>
                            <div class="flex items-center gap-6 pt-6">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Hidden network</span>
                                <button type="button" @click="form.hidden_network = !form.hidden_network"
                                    :class="['relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none', form.hidden_network ? 'bg-emerald-600' : 'bg-gray-200 dark:bg-gray-700']">
                                    <span :class="['pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out', form.hidden_network ? 'translate-x-4' : 'translate-x-0']" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- URL Details -->
                <div v-else-if="form.type === 'url'" class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">URL Details</h2>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">URL</label>
                        <input v-model="form.url" type="url" placeholder="https://example.com"
                            class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                        <p v-if="form.errors.url" class="text-red-500 text-xs mt-1">{{ form.errors.url }}</p>
                    </div>
                </div>

                <!-- Phone Details -->
                <div v-else-if="form.type === 'phone'" class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Phone Details</h2>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone Number</label>
                        <input v-model="form.url" type="tel" placeholder="+31612345678"
                            class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Include country code (e.g. +31). Scanning will prompt a call.</p>
                        <p v-if="form.errors.url" class="text-red-500 text-xs mt-1">{{ form.errors.url }}</p>
                    </div>
                </div>

                <!-- vCard Details -->
                <div v-else-if="form.type === 'vcard'" class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">vCard Details</h2>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">First Name</label>
                                <input v-model="form.vcard_data.first_name" type="text" placeholder="John"
                                    class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Last Name</label>
                                <input v-model="form.vcard_data.last_name" type="text" placeholder="Doe"
                                    class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company</label>
                                <input v-model="form.vcard_data.company" type="text" placeholder="Acme Corp"
                                    class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Job Title</label>
                                <input v-model="form.vcard_data.job_title" type="text" placeholder="Developer"
                                    class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mobile</label>
                            <input v-model="form.vcard_data.mobile" type="tel" placeholder="+31612345678"
                                class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
                                <input v-model="form.vcard_data.phone" type="tel" placeholder="+31201234567"
                                    class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fax</label>
                                <input v-model="form.vcard_data.fax" type="tel" placeholder="+31201234568"
                                    class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                            <input v-model="form.vcard_data.email" type="email" placeholder="john@example.com"
                                class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Street</label>
                            <input v-model="form.vcard_data.street" type="text"
                                class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">City</label>
                                <input v-model="form.vcard_data.city" type="text"
                                    class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Postal Code</label>
                                <input v-model="form.vcard_data.postal_code" type="text"
                                    class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Province / State</label>
                                <input v-model="form.vcard_data.province" type="text"
                                    class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Country</label>
                                <input v-model="form.vcard_data.country" type="text"
                                    class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Website</label>
                            <input v-model="form.vcard_data.website" type="url" placeholder="https://example.com"
                                class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                        </div>
                    </div>
                </div>

                <!-- Email Details -->
                <div v-else class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Email Details</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email Address</label>
                            <input v-model="form.email_data.to" type="email" placeholder="recipient@example.com"
                                class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                            <p v-if="form.errors['email_data.to']" class="text-red-500 text-xs mt-1">{{ form.errors['email_data.to'] }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subject <span class="text-gray-400 font-normal">(optional)</span></label>
                            <input v-model="form.email_data.subject" type="text" placeholder="Subject line"
                                class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Message <span class="text-gray-400 font-normal">(optional)</span></label>
                            <textarea v-model="form.email_data.body" rows="4" placeholder="Message body..."
                                class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition resize-none" />
                        </div>
                    </div>
                </div>

                <!-- Text Labels -->
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6">
                    <div class="flex items-center justify-between" :class="showTextLabels ? 'mb-4' : ''">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Text Labels</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Add header and footer text to your QR code</p>
                        </div>
                        <button
                            type="button"
                            @click="showTextLabels = !showTextLabels"
                            :class="[
                                'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2',
                                showTextLabels ? 'bg-emerald-600' : 'bg-gray-200 dark:bg-gray-700'
                            ]"
                        >
                            <span :class="['pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out', showTextLabels ? 'translate-x-5' : 'translate-x-0']" />
                        </button>
                    </div>
                    <template v-if="showTextLabels">
                        <div class="space-y-6">
                            <div>
                                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3">Header</p>
                                <TextLabelEditor
                                    :model-value="headerLabel"
                                    @update:model-value="updateHeader"
                                    label="Header Text"
                                />
                            </div>
                            <div class="border-t border-gray-100 dark:border-gray-800 pt-4">
                                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3">Footer</p>
                                <TextLabelEditor
                                    :model-value="footerLabel"
                                    @update:model-value="updateFooter"
                                    label="Footer Text"
                                />
                            </div>
                        </div>
                    </template>
                </div>

                <!-- WiFi Info Display -->
                <div v-if="form.type === 'wifi'" class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">WiFi Info Display</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Show network name and password below the QR code</p>
                        </div>
                        <button
                            type="button"
                            @click="form.show_wifi_details = !form.show_wifi_details"
                            :class="[
                                'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2',
                                form.show_wifi_details ? 'bg-emerald-600' : 'bg-gray-200 dark:bg-gray-700'
                            ]"
                        >
                            <span
                                :class="[
                                    'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                                    form.show_wifi_details ? 'translate-x-5' : 'translate-x-0'
                                ]"
                            />
                        </button>
                    </div>

                    <template v-if="form.show_wifi_details">
                        <div class="space-y-2">
                            <!-- Row 1: Font + Size + B/I/U + Color -->
                            <div class="flex items-end gap-1.5">
                                <div class="flex-1 min-w-0">
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Font</label>
                                    <select v-model="form.wifi_details_font_family"
                                        class="w-full px-2 py-1.5 text-xs border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                        <option v-for="font in sansSerif" :key="font.value" :value="font.value" :style="{ fontFamily: font.stack }">{{ font.label }}</option>
                                    </select>
                                </div>
                                <div class="shrink-0" :class="wifiIsCustomSize ? 'w-20' : 'w-14'">
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Size</label>
                                    <select v-if="!wifiIsCustomSize"
                                        :value="form.wifi_details_font_size"
                                        @change="onWifiSizeChange($event.target.value)"
                                        class="w-full px-1.5 py-1.5 text-xs border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                        <option value="12">12</option>
                                        <option value="14">14</option>
                                        <option value="16">16</option>
                                        <option value="18">18</option>
                                        <option value="20">20</option>
                                        <option value="24">24</option>
                                        <option value="custom">px…</option>
                                    </select>
                                    <div v-else class="flex items-center gap-0.5">
                                        <input type="number" v-model="form.wifi_details_font_size"
                                            min="8" max="120" step="1"
                                            class="w-12 px-1.5 py-1.5 text-xs border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" />
                                        <span class="text-xs text-gray-400 select-none">px</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-0.5 shrink-0">
                                    <button type="button" @click="form.wifi_details_bold = !form.wifi_details_bold"
                                        :class="['w-7 h-7 rounded text-xs font-bold border transition-colors', form.wifi_details_bold ? 'bg-emerald-600 border-emerald-600 text-white' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:border-emerald-400']"
                                        title="Bold">B</button>
                                    <button type="button" @click="form.wifi_details_italic = !form.wifi_details_italic"
                                        :class="['w-7 h-7 rounded text-xs italic border transition-colors', form.wifi_details_italic ? 'bg-emerald-600 border-emerald-600 text-white' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:border-emerald-400']"
                                        title="Italic">I</button>
                                    <button type="button" @click="form.wifi_details_underline = !form.wifi_details_underline"
                                        :class="['w-7 h-7 rounded text-xs underline border transition-colors', form.wifi_details_underline ? 'bg-emerald-600 border-emerald-600 text-white' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:border-emerald-400']"
                                        title="Underline">U</button>
                                </div>
                                <div class="shrink-0">
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Color</label>
                                    <div class="flex items-center gap-1">
                                        <input type="color" v-model="form.wifi_details_color"
                                            class="w-7 h-7 rounded cursor-pointer border border-gray-200 dark:border-gray-700 p-0.5 bg-white dark:bg-gray-800 shrink-0" />
                                        <input type="text" v-model="form.wifi_details_color" maxlength="7"
                                            class="w-16 px-1.5 py-1.5 text-xs font-mono border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                                    </div>
                                </div>
                            </div>

                            <!-- Row 2: Align + Margin -->
                            <div class="flex items-end gap-1.5">
                                <div class="shrink-0">
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Align</label>
                                    <div class="flex gap-0.5">
                                        <button v-for="align in ['left', 'center', 'right']" :key="align" type="button"
                                            @click="form.wifi_details_alignment = align"
                                            :class="['w-7 h-7 rounded text-xs border transition-colors', form.wifi_details_alignment === align ? 'bg-emerald-600 border-emerald-600 text-white' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:border-emerald-400']">
                                            <span v-if="align === 'left'">&#9664;</span>
                                            <span v-else-if="align === 'center'">&#9644;</span>
                                            <span v-else>&#9654;</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Margin</label>
                                        <button type="button" @click="toggleWifiCustomMargin"
                                            :class="['text-xs px-1.5 py-0.5 rounded border transition-colors leading-none', form.wifi_details_margin_custom ? 'bg-emerald-600 border-emerald-600 text-white' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 hover:border-emerald-400']">
                                            Custom
                                        </button>
                                    </div>
                                    <div v-if="!form.wifi_details_margin_custom" class="flex items-center gap-1.5">
                                        <input type="range" v-model.number="form.wifi_details_margin" min="0" max="40" step="1" class="flex-1 h-1 accent-emerald-600" />
                                        <span class="text-xs text-gray-400 w-7 text-right tabular-nums">{{ form.wifi_details_margin }}</span>
                                    </div>
                                    <div v-else class="grid grid-cols-4 gap-1">
                                        <input v-for="side in [{ key: 'wifi_details_margin_top', label: 'T' }, { key: 'wifi_details_margin_right', label: 'R' }, { key: 'wifi_details_margin_bottom', label: 'B' }, { key: 'wifi_details_margin_left', label: 'L' }]"
                                            :key="side.key" type="number" v-model.number="form[side.key]"
                                            :title="side.label === 'T' ? 'Top' : side.label === 'R' ? 'Right' : side.label === 'B' ? 'Bottom' : 'Left'"
                                            :placeholder="side.label"
                                            min="0" max="80" step="1"
                                            class="w-full px-0 py-1.5 text-xs text-center border border-gray-200 dark:border-gray-700 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" />
                                    </div>
                                </div>
                            </div>

                            <!-- Row 3: Password section -->
                            <!-- When NOT separate: Font + toggles -->
                            <div v-if="!form.wifi_details_password_separate_style" class="flex items-end gap-1.5 pt-1">
                                <div class="flex-1 min-w-0">
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Password Font</label>
                                    <select v-model="form.wifi_details_password_font"
                                        class="w-full px-2 py-1.5 text-xs border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                        <option v-for="font in monospace" :key="font.value" :value="font.value" :style="{ fontFamily: font.stack }">
                                            {{ font.label }}
                                        </option>
                                    </select>
                                </div>
                                <div class="flex flex-col gap-1.5 shrink-0 pb-0.5">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Show password</span>
                                        <button type="button" @click="form.wifi_details_show_password = !form.wifi_details_show_password"
                                            :class="['relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none', form.wifi_details_show_password ? 'bg-emerald-600' : 'bg-gray-200 dark:bg-gray-700']">
                                            <span :class="['pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out', form.wifi_details_show_password ? 'translate-x-4' : 'translate-x-0']" />
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Style separately</span>
                                        <button type="button" @click="form.wifi_details_password_separate_style = !form.wifi_details_password_separate_style"
                                            :class="['relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none', form.wifi_details_password_separate_style ? 'bg-emerald-600' : 'bg-gray-200 dark:bg-gray-700']">
                                            <span :class="['pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out', form.wifi_details_password_separate_style ? 'translate-x-4' : 'translate-x-0']" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- When separate: Font + Size + B/I/U + Color in one row, toggles below -->
                            <template v-else>
                                <div class="flex items-end gap-1.5 pt-1">
                                    <div class="flex-1 min-w-0">
                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Password Font</label>
                                        <select v-model="form.wifi_details_password_font"
                                            class="w-full px-2 py-1.5 text-xs border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                            <option v-for="font in monospace" :key="font.value" :value="font.value" :style="{ fontFamily: font.stack }">
                                                {{ font.label }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="shrink-0" :class="wifiPasswordIsCustomSize ? 'w-20' : 'w-14'">
                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Size</label>
                                        <select v-if="!wifiPasswordIsCustomSize"
                                            :value="form.wifi_details_password_font_size"
                                            @change="onWifiPasswordSizeChange($event.target.value)"
                                            class="w-full px-1.5 py-1.5 text-xs border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                            <option value="12">12</option>
                                            <option value="14">14</option>
                                            <option value="16">16</option>
                                            <option value="18">18</option>
                                            <option value="20">20</option>
                                            <option value="24">24</option>
                                            <option value="custom">px…</option>
                                        </select>
                                        <div v-else class="flex items-center gap-0.5">
                                            <input type="number" v-model="form.wifi_details_password_font_size"
                                                min="8" max="120" step="1"
                                                class="w-12 px-1.5 py-1.5 text-xs border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" />
                                            <span class="text-xs text-gray-400 select-none">px</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-0.5 shrink-0">
                                        <button type="button" @click="form.wifi_details_password_bold = !form.wifi_details_password_bold"
                                            :class="['w-7 h-7 rounded text-xs font-bold border transition-colors', form.wifi_details_password_bold ? 'bg-emerald-600 border-emerald-600 text-white' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:border-emerald-400']"
                                            title="Bold">B</button>
                                        <button type="button" @click="form.wifi_details_password_italic = !form.wifi_details_password_italic"
                                            :class="['w-7 h-7 rounded text-xs italic border transition-colors', form.wifi_details_password_italic ? 'bg-emerald-600 border-emerald-600 text-white' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:border-emerald-400']"
                                            title="Italic">I</button>
                                        <button type="button" @click="form.wifi_details_password_underline = !form.wifi_details_password_underline"
                                            :class="['w-7 h-7 rounded text-xs underline border transition-colors', form.wifi_details_password_underline ? 'bg-emerald-600 border-emerald-600 text-white' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:border-emerald-400']"
                                            title="Underline">U</button>
                                    </div>
                                    <div class="shrink-0">
                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Color</label>
                                        <div class="flex items-center gap-1">
                                            <input type="color" v-model="form.wifi_details_password_color"
                                                class="w-7 h-7 rounded cursor-pointer border border-gray-200 dark:border-gray-700 p-0.5 bg-white dark:bg-gray-800 shrink-0" />
                                            <input type="text" v-model="form.wifi_details_password_color" maxlength="7"
                                                class="w-16 px-1.5 py-1.5 text-xs font-mono border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                                        </div>
                                    </div>
                                </div>
                                <!-- Toggles row -->
                                <div class="flex items-center justify-end gap-4 pt-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Show password</span>
                                        <button type="button" @click="form.wifi_details_show_password = !form.wifi_details_show_password"
                                            :class="['relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none', form.wifi_details_show_password ? 'bg-emerald-600' : 'bg-gray-200 dark:bg-gray-700']">
                                            <span :class="['pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out', form.wifi_details_show_password ? 'translate-x-4' : 'translate-x-0']" />
                                        </button>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Style separately</span>
                                        <button type="button" @click="form.wifi_details_password_separate_style = !form.wifi_details_password_separate_style"
                                            :class="['relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none', form.wifi_details_password_separate_style ? 'bg-emerald-600' : 'bg-gray-200 dark:bg-gray-700']">
                                            <span :class="['pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out', form.wifi_details_password_separate_style ? 'translate-x-4' : 'translate-x-0']" />
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- Styling -->
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6">
                    <div class="flex items-center justify-between" :class="showDesign ? 'mb-4' : ''">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Design</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Customize colors, dot style and error correction</p>
                        </div>
                        <button
                            type="button"
                            @click="showDesign = !showDesign"
                            :class="[
                                'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2',
                                showDesign ? 'bg-emerald-600' : 'bg-gray-200 dark:bg-gray-700'
                            ]"
                        >
                            <span :class="['pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out', showDesign ? 'translate-x-5' : 'translate-x-0']" />
                        </button>
                    </div>
                    <div v-if="showDesign" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">QR Color</label>
                                <div class="flex items-center gap-2">
                                    <input v-model="form.foreground_color" type="color" class="w-10 h-10 rounded-lg border border-gray-300 dark:border-gray-700 cursor-pointer" />
                                    <input v-model="form.foreground_color" type="text" class="flex-1 px-3 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Background</label>
                                <div class="flex items-center gap-2">
                                    <input v-model="form.background_color" type="color" class="w-10 h-10 rounded-lg border border-gray-300 dark:border-gray-700 cursor-pointer" />
                                    <input v-model="form.background_color" type="text" class="flex-1 px-3 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm" />
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Dot Style</label>
                            <div class="grid grid-cols-3 gap-2">
                                <button v-for="style in dotStyles" :key="style.value"
                                    @click="form.dot_style = style.value"
                                    :class="[form.dot_style === style.value ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800']"
                                    class="px-3 py-2 border rounded-lg text-xs font-medium transition-colors">
                                    {{ style.label }}
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Corner Style</label>
                            <div class="grid grid-cols-3 gap-2">
                                <button v-for="style in cornerStyles" :key="style.value"
                                    @click="form.corner_square_style = style.value; form.corner_dot_style = style.value"
                                    :class="[form.corner_square_style === style.value ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800']"
                                    class="px-3 py-2 border rounded-lg text-xs font-medium transition-colors">
                                    {{ style.label }}
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Error Correction</label>
                            <div class="grid grid-cols-4 gap-2">
                                <button v-for="level in ['L', 'M', 'Q', 'H']" :key="level"
                                    @click="form.error_correction = level"
                                    :class="[form.error_correction === level ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800']"
                                    class="px-3 py-2 border rounded-lg text-xs font-medium transition-colors">
                                    {{ level }} {{ level === 'L' ? '(7%)' : level === 'M' ? '(15%)' : level === 'Q' ? '(25%)' : '(30%)' }}
                                </button>
                            </div>
                        </div>

                        <!-- Frame -->
                        <div class="pt-4 border-t border-gray-200 dark:border-gray-800">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Frame</label>
                            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                                <button v-for="f in frameStyles" :key="f.value" type="button"
                                    @click="form.frame_style = f.value"
                                    :title="f.label"
                                    :class="[form.frame_style === f.value ? 'border-primary-500 ring-2 ring-primary-200 dark:ring-primary-900/40' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300']"
                                    class="aspect-square bg-white dark:bg-gray-950 border rounded-lg flex items-center justify-center transition-colors overflow-hidden">
                                    <div class="qf-thumb">
                                        <QrFrame
                                            :data="'QRLAB'"
                                            :size="60"
                                            :frame-style="f.value"
                                            :frame-color="form.frame_color"
                                            :frame-label-color="form.frame_label_color"
                                            :frame-text="'SCAN'"
                                            :frame-font-size="8"
                                            :frame-font-family="form.frame_font_family"
                                            :frame-icon="f.value === 'pill-icon' ? (form.frame_icon === 'none' ? 'Play' : form.frame_icon) : 'none'" />
                                    </div>
                                </button>
                            </div>
                            <div class="mt-3 grid grid-cols-2 gap-3" v-if="form.frame_style !== 'none'">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Border color</label>
                                    <div class="flex items-center gap-2">
                                        <input v-model="form.frame_color" type="color" class="w-9 h-9 rounded-lg border border-gray-300 dark:border-gray-700 cursor-pointer" />
                                        <input v-model="form.frame_color" type="text" class="flex-1 px-3 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm" />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Label color</label>
                                    <div class="flex items-center gap-2">
                                        <input v-model="form.frame_label_color" type="color" class="w-9 h-9 rounded-lg border border-gray-300 dark:border-gray-700 cursor-pointer" />
                                        <input v-model="form.frame_label_color" type="text" class="flex-1 px-3 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm" />
                                    </div>
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Label text</label>
                                    <input v-model="form.frame_text" type="text" maxlength="24"
                                        class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Font size ({{ form.frame_font_size }}px)</label>
                                    <div class="h-[38px] flex items-center">
                                        <input v-model.number="form.frame_font_size" type="range" min="10" max="28" class="w-full" />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Font family</label>
                                    <select v-model="form.frame_font_family"
                                        :style="{ fontFamily: form.frame_font_family }"
                                        class="w-full h-[38px] px-3 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm">
                                        <option v-for="f in frameFontFamilies" :key="f.value" :value="f.value" :style="{ fontFamily: f.value }">{{ f.label }}</option>
                                    </select>
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Label distance ({{ form.frame_label_gap }}px)</label>
                                    <input v-model.number="form.frame_label_gap" type="range" min="0" max="40" class="w-full" />
                                </div>
                                <div v-if="form.frame_style === 'pill-icon'" class="col-span-2">
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Icon</label>
                                    <select v-model="form.frame_icon"
                                        class="w-full h-[38px] px-3 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm">
                                        <option v-for="i in frameIcons" :key="i.value" :value="i.value">{{ i.label }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Logo -->
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6">
                    <div class="flex items-center justify-between" :class="showLogo ? 'mb-4' : ''">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Logo</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Add a logo or image in the centre of the QR code</p>
                        </div>
                        <button type="button" @click="toggleLogo"
                            :class="['relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2', showLogo ? 'bg-emerald-600' : 'bg-gray-200 dark:bg-gray-700']">
                            <span :class="['pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out', showLogo ? 'translate-x-5' : 'translate-x-0']" />
                        </button>
                    </div>
                    <template v-if="showLogo">
                        <div v-if="logoPreview" class="flex items-center gap-4 mb-4">
                            <img :src="logoPreview" class="w-16 h-16 rounded-lg object-contain bg-gray-100 dark:bg-gray-800 p-1" />
                            <div class="flex-1">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Logo uploaded</p>
                                <button @click="removeLogo" class="text-red-500 text-xs font-medium hover:text-red-700">Remove</button>
                            </div>
                        </div>
                        <label v-else class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-xl cursor-pointer hover:border-primary-400 transition-colors">
                            <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            <span class="text-sm text-gray-500">Upload logo (PNG, JPG, SVG, max 2MB)</span>
                            <input type="file" class="hidden" accept=".png,.jpg,.jpeg,.gif,.svg" @change="onLogoChange" />
                        </label>
                        <div v-if="logoPreview" class="mt-3">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Logo Size ({{ form.logo_size }}%)</label>
                            <input v-model.number="form.logo_size" type="range" min="10" max="40" class="w-full" />
                        </div>
                    </template>
                </div>
            </div>

            <!-- Right: Preview & Actions -->
            <div class="lg:w-96">
                <div class="sticky top-24 space-y-4">
                    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 text-center">Preview</h2>

                        <!-- Header text -->
                        <p v-if="form.header_text" :style="headerStyle">{{ form.header_text }}</p>

                        <div class="qf-preview-stage">
                            <QrFrame v-if="form.frame_style && form.frame_style !== 'none'"
                                :data="qrContent"
                                :size="180"
                                :margin="form.margin"
                                :dotsColor="form.foreground_color"
                                :backgroundColor="form.background_color"
                                :dotStyle="form.dot_style"
                                :cornerSquareStyle="form.corner_square_style"
                                :cornerDotStyle="form.corner_dot_style"
                                :errorCorrectionLevel="form.error_correction"
                                :logoUrl="logoPreview"
                                :logoSize="form.logo_size / 100"
                                :frame-style="form.frame_style"
                                :frame-color="form.frame_color"
                                :frame-label-color="form.frame_label_color"
                                :frame-text="form.frame_text"
                                :frame-font-size="form.frame_font_size"
                                :frame-font-family="form.frame_font_family"
                                :frame-label-gap="form.frame_label_gap"
                                :frame-icon="form.frame_icon"
                            />
                            <QrPreview v-else
                                ref="qrPreviewRef"
                                :data="qrContent"
                                :width="280"
                                :height="280"
                                :margin="form.margin"
                                :dotsColor="form.foreground_color"
                                :backgroundColor="form.background_color"
                                :dotStyle="form.dot_style"
                                :cornerSquareStyle="form.corner_square_style"
                                :cornerDotStyle="form.corner_dot_style"
                                :errorCorrectionLevel="form.error_correction"
                                :logoUrl="logoPreview"
                                :logoSize="form.logo_size / 100"
                            />
                        </div>

                        <!-- Footer text -->
                        <p v-if="form.footer_text" :style="footerStyle">{{ form.footer_text }}</p>

                        <!-- WiFi info block -->
                        <div v-if="form.show_wifi_details" :style="wifiDetailsBaseStyle" class="space-y-1">
                            <p>
                                <span class="font-medium">WiFi Name: </span>
                                <span>{{ form.ssid || 'MyNetwork' }}</span>
                            </p>
                            <p v-if="form.wifi_details_show_password && form.password">
                                <span class="font-medium">Password: </span>
                                <span :style="wifiPasswordStyle">{{ form.password }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Quick download -->
                    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Quick Download</p>
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs text-gray-500 dark:text-gray-400">Rounded</span>
                                <button type="button" @click="roundedExport = !roundedExport"
                                    :class="['relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none', roundedExport ? 'bg-emerald-600' : 'bg-gray-200 dark:bg-gray-700']">
                                    <span :class="['pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out', roundedExport ? 'translate-x-4' : 'translate-x-0']" />
                                </button>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <button @click="downloadQr('png')" class="px-3 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">PNG</button>
                            <button @click="downloadQr('jpeg')" class="px-3 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">JPG</button>
                            <button @click="downloadQr('svg')" class="px-3 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">SVG</button>
                        </div>
                    </div>

                    <!-- Save (authenticated) -->
                    <template v-if="user">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">QR Code Name</label>
                            <input v-model="form.name" type="text" :placeholder="form.type === 'url' ? 'e.g. Company Website' : 'e.g. Office WiFi'"
                                class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                            <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                        </div>
                        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl px-4 py-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">Track scans</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        <template v-if="form.type === 'url'">QR redirects through a short link so scans can be counted</template>
                                        <template v-else>Only available for URL QR codes — other types encode data directly on the device</template>
                                    </p>
                                </div>
                                <button type="button" @click="form.type === 'url' && (form.tracking_enabled = !form.tracking_enabled)"
                                    :disabled="form.type !== 'url'"
                                    :class="['relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none', form.type !== 'url' ? 'bg-gray-200 dark:bg-gray-700 opacity-50 cursor-not-allowed' : form.tracking_enabled ? 'bg-emerald-600' : 'bg-gray-200 dark:bg-gray-700']">
                                    <span :class="['pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out', form.tracking_enabled && form.type === 'url' ? 'translate-x-5' : 'translate-x-0']" />
                                </button>
                            </div>
                        </div>
                        <button @click="submit" :disabled="form.processing || !form.name || (form.type === 'wifi' ? !form.ssid : form.type === 'vcard' ? !form.vcard_data.first_name && !form.vcard_data.last_name : form.type === 'email' ? !form.email_data.to.trim() : !form.url.trim())"
                            class="w-full px-4 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-colors disabled:opacity-50 text-sm">
                            {{ form.processing ? 'Saving...' : 'Save QR Code' }}
                        </button>
                    </template>

                    <!-- Login prompt (guest) -->
                    <div v-else class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4">
                        <div class="text-center">
                            <svg class="w-8 h-8 text-gray-400 dark:text-gray-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <p class="text-sm font-medium text-gray-900 dark:text-white mb-1">Want to save your QR codes?</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Sign in to save, edit, and manage your QR codes. Contact your administrator to request an account.</p>
                            <Link :href="route('login')"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                Sign in
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Preview stage: centre the frame inside the sidebar preview card. */
.qf-preview-stage {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    min-height: 240px;
}
.qf-preview-stage > :deep(*) {
    flex: 0 0 auto;
}

/* Thumbnail: scale the frame so every style (including the wide arrow
   variants) fits the aspect-square button with equal margins. Transform
   doesn't affect flex layout, so flex centering still works. */
.qf-thumb {
    display: flex;
    align-items: center;
    justify-content: center;
    transform: scale(0.75);
    transform-origin: center center;
    line-height: 0;
}
</style>
