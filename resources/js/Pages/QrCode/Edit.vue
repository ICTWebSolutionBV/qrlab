<script setup>
import { ref, computed } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import QrPreview from '@/Components/QrPreview.vue'

const props = defineProps({
    qrCode: Object,
})

const qrPreviewRef = ref(null)
const logoPreview = ref(null)

const form = useForm({
    name: props.qrCode.name,
    ssid: props.qrCode.ssid,
    password: props.qrCode.password || '',
    encryption: props.qrCode.encryption,
    hidden_network: props.qrCode.hidden_network,
    foreground_color: props.qrCode.foreground_color,
    background_color: props.qrCode.background_color,
    dot_style: props.qrCode.dot_style,
    corner_square_style: props.qrCode.corner_square_style,
    corner_dot_style: props.qrCode.corner_dot_style,
    error_correction: props.qrCode.error_correction,
    size: props.qrCode.size,
    margin: props.qrCode.margin,
    header_text: props.qrCode.header_text || '',
    header_font_size: props.qrCode.header_font_size || '16',
    header_color: props.qrCode.header_color || '#000000',
    header_alignment: props.qrCode.header_alignment || 'center',
    header_margin: props.qrCode.header_margin ?? 8,
    footer_text: props.qrCode.footer_text || '',
    footer_font_size: props.qrCode.footer_font_size || '14',
    footer_color: props.qrCode.footer_color || '#000000',
    footer_alignment: props.qrCode.footer_alignment || 'center',
    footer_margin: props.qrCode.footer_margin ?? 8,
    logo: null,
    logo_size: props.qrCode.logo_size || 30,
    tracking_enabled: props.qrCode.tracking_enabled,
})

const wifiString = computed(() => {
    const enc = ['WPA', 'WPA2', 'WPA3'].includes(form.encryption) ? 'WPA' :
        form.encryption === 'WEP' ? 'WEP' : 'nopass'
    const ssid = (form.ssid || 'MyNetwork').replace(/([\\;,":])/, '\\$1')
    const pass = (form.password || '').replace(/([\\;,":])/, '\\$1')
    const hidden = form.hidden_network ? 'true' : 'false'
    return `WIFI:T:${enc};S:${ssid};P:${pass};H:${hidden};;`
})

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
    form.put(route('qr.update', props.qrCode.id), {
        forceFormData: true,
    })
}

const roundedExport = ref(true)

const downloadQr = async (ext) => {
    qrPreviewRef.value?.download(form.name || 'qrcode', ext, roundedExport.value)
}
</script>

<template>
    <Head :title="`Edit - ${qrCode.name}`" />
    <AppLayout>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Edit QR Code</h1>

        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Left: Form -->
            <div class="flex-1 space-y-6">
                <!-- WiFi Details -->
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">WiFi Details</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">QR Code Name</label>
                            <input v-model="form.name" type="text"
                                class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Network Name (SSID)</label>
                            <input v-model="form.ssid" type="text"
                                class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                            <input v-model="form.password" type="text"
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
                            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 pt-6">
                                <input v-model="form.hidden_network" type="checkbox" class="rounded border-gray-300 dark:border-gray-700 text-primary-600 focus:ring-primary-500" />
                                Hidden network
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Text Labels -->
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Text Labels (Optional)</h2>
                    <div class="space-y-5">
                        <!-- Header -->
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Header Text</label>
                                <input v-model="form.header_text" type="text" placeholder="Text above QR code"
                                    class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                            </div>
                            <div v-if="form.header_text" class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Size</label>
                                    <select v-model="form.header_font_size"
                                        class="w-full px-2 py-1.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white text-xs">
                                        <option value="12">12px</option>
                                        <option value="14">14px</option>
                                        <option value="16">16px</option>
                                        <option value="18">18px</option>
                                        <option value="20">20px</option>
                                        <option value="24">24px</option>
                                        <option value="28">28px</option>
                                        <option value="32">32px</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Color</label>
                                    <div class="flex items-center gap-1">
                                        <input v-model="form.header_color" type="color" class="w-7 h-7 rounded border border-gray-300 dark:border-gray-700 cursor-pointer" />
                                        <input v-model="form.header_color" type="text" class="flex-1 min-w-0 px-2 py-1.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white text-xs" />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Align</label>
                                    <div class="flex border border-gray-300 dark:border-gray-700 rounded-lg overflow-hidden">
                                        <button v-for="a in ['left', 'center', 'right']" :key="a"
                                            @click="form.header_alignment = a"
                                            :class="[form.header_alignment === a ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400' : 'bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400']"
                                            class="flex-1 py-1.5 text-xs font-medium transition-colors">
                                            {{ a.charAt(0).toUpperCase() }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div v-if="form.header_text">
                                <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Margin ({{ form.header_margin }}px)</label>
                                <input v-model.number="form.header_margin" type="range" min="0" max="40" class="w-full" />
                            </div>
                        </div>

                        <div v-if="form.header_text || form.footer_text" class="border-t border-gray-200 dark:border-gray-700"></div>

                        <!-- Footer -->
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Footer Text</label>
                                <input v-model="form.footer_text" type="text" placeholder="Text below QR code"
                                    class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" />
                            </div>
                            <div v-if="form.footer_text" class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Size</label>
                                    <select v-model="form.footer_font_size"
                                        class="w-full px-2 py-1.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white text-xs">
                                        <option value="12">12px</option>
                                        <option value="14">14px</option>
                                        <option value="16">16px</option>
                                        <option value="18">18px</option>
                                        <option value="20">20px</option>
                                        <option value="24">24px</option>
                                        <option value="28">28px</option>
                                        <option value="32">32px</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Color</label>
                                    <div class="flex items-center gap-1">
                                        <input v-model="form.footer_color" type="color" class="w-7 h-7 rounded border border-gray-300 dark:border-gray-700 cursor-pointer" />
                                        <input v-model="form.footer_color" type="text" class="flex-1 min-w-0 px-2 py-1.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white text-xs" />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Align</label>
                                    <div class="flex border border-gray-300 dark:border-gray-700 rounded-lg overflow-hidden">
                                        <button v-for="a in ['left', 'center', 'right']" :key="a"
                                            @click="form.footer_alignment = a"
                                            :class="[form.footer_alignment === a ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400' : 'bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400']"
                                            class="flex-1 py-1.5 text-xs font-medium transition-colors">
                                            {{ a.charAt(0).toUpperCase() }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div v-if="form.footer_text">
                                <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Margin ({{ form.footer_margin }}px)</label>
                                <input v-model.number="form.footer_margin" type="range" min="0" max="40" class="w-full" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Styling -->
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Design</h2>
                    <div class="space-y-4">
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
                                    {{ level }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Logo -->
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Logo</h2>
                    <div v-if="logoPreview" class="flex items-center gap-4 mb-4">
                        <img :src="logoPreview" class="w-16 h-16 rounded-lg object-contain bg-gray-100 dark:bg-gray-800 p-1" />
                        <button @click="removeLogo" class="text-red-500 text-xs font-medium hover:text-red-700">Remove</button>
                    </div>
                    <label v-else class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-xl cursor-pointer hover:border-primary-400 transition-colors">
                        <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        <span class="text-sm text-gray-500">Upload logo</span>
                        <input type="file" class="hidden" accept=".png,.jpg,.jpeg,.gif,.svg" @change="onLogoChange" />
                    </label>
                    <div v-if="logoPreview" class="mt-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Logo Size ({{ form.logo_size }}%)</label>
                        <input v-model.number="form.logo_size" type="range" min="10" max="40" class="w-full" />
                    </div>
                </div>
            </div>

            <!-- Right: Preview -->
            <div class="lg:w-96">
                <div class="sticky top-24 space-y-4">
                    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 text-center">Preview</h2>
                        <p v-if="form.header_text"
                            :style="{ fontSize: form.header_font_size + 'px', color: form.header_color, textAlign: form.header_alignment, marginBottom: form.header_margin + 'px' }"
                            class="font-medium">{{ form.header_text }}</p>
                        <div class="flex justify-center">
                            <QrPreview ref="qrPreviewRef" :data="wifiString" :width="280" :height="280" :margin="form.margin"
                                :dotsColor="form.foreground_color" :backgroundColor="form.background_color"
                                :dotStyle="form.dot_style" :cornerSquareStyle="form.corner_square_style"
                                :cornerDotStyle="form.corner_dot_style" :errorCorrectionLevel="form.error_correction"
                                :logoUrl="logoPreview" :logoSize="form.logo_size / 100" />
                        </div>
                        <p v-if="form.footer_text"
                            :style="{ fontSize: form.footer_font_size + 'px', color: form.footer_color, textAlign: form.footer_alignment, marginTop: form.footer_margin + 'px' }"
                            class="font-medium">{{ form.footer_text }}</p>
                    </div>

                    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Quick Download</p>
                            <label class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400 cursor-pointer">
                                <input v-model="roundedExport" type="checkbox" class="rounded border-gray-300 dark:border-gray-700 text-primary-600 focus:ring-primary-500 w-3.5 h-3.5" />
                                Rounded
                            </label>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <button @click="downloadQr('png')" class="px-3 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">PNG</button>
                            <button @click="downloadQr('jpeg')" class="px-3 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">JPG</button>
                            <button @click="downloadQr('svg')" class="px-3 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">SVG</button>
                        </div>
                    </div>

                    <button @click="submit" :disabled="form.processing"
                        class="w-full px-4 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-colors disabled:opacity-50 text-sm">
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
