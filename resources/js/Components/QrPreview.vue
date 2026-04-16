<script setup>
import { ref, watch, onMounted } from 'vue'
import QRCodeStyling from 'qr-code-styling'

const props = defineProps({
    data: { type: String, default: 'WIFI:T:WPA;S:MyNetwork;P:password;;' },
    width: { type: Number, default: 300 },
    height: { type: Number, default: 300 },
    margin: { type: Number, default: 10 },
    dotsColor: { type: String, default: '#000000' },
    backgroundColor: { type: String, default: '#FFFFFF' },
    dotStyle: { type: String, default: 'square' },
    cornerSquareStyle: { type: String, default: 'square' },
    cornerDotStyle: { type: String, default: 'square' },
    errorCorrectionLevel: { type: String, default: 'M' },
    logoUrl: { type: String, default: null },
    logoSize: { type: Number, default: 0.3 },
})

const emit = defineEmits(['rendered'])
const container = ref(null)
let qrCode = null

const buildOptions = () => ({
    width: props.width,
    height: props.height,
    margin: props.margin,
    data: props.data,
    dotsOptions: {
        color: props.dotsColor,
        type: props.dotStyle,
    },
    backgroundOptions: {
        color: props.backgroundColor,
    },
    cornersSquareOptions: {
        type: props.cornerSquareStyle === 'square' ? undefined : props.cornerSquareStyle,
        color: props.dotsColor,
    },
    cornersDotOptions: {
        type: props.cornerDotStyle === 'square' ? undefined : props.cornerDotStyle,
        color: props.dotsColor,
    },
    qrOptions: {
        errorCorrectionLevel: props.errorCorrectionLevel,
    },
    imageOptions: {
        crossOrigin: 'anonymous',
        margin: 4,
        imageSize: props.logoSize,
    },
    ...(props.logoUrl ? { image: props.logoUrl } : {}),
})

onMounted(() => {
    qrCode = new QRCodeStyling(buildOptions())
    qrCode.append(container.value)
    emit('rendered', qrCode)
})

watch(() => [
    props.data, props.width, props.height, props.margin,
    props.dotsColor, props.backgroundColor, props.dotStyle,
    props.cornerSquareStyle, props.cornerDotStyle,
    props.errorCorrectionLevel, props.logoUrl, props.logoSize,
], () => {
    if (qrCode) {
        qrCode.update(buildOptions())
    }
}, { deep: true })

const downloadRounded = async (name, extension) => {
    if (!qrCode) return

    if (extension === 'svg') {
        // SVG: download as-is (rounded corners not applicable to SVG the same way)
        qrCode.download({ name, extension })
        return
    }

    const blob = await qrCode.getRawData('png')
    const img = new Image()
    img.src = URL.createObjectURL(blob)
    await new Promise(r => img.onload = r)

    const canvas = document.createElement('canvas')
    canvas.width = img.width
    canvas.height = img.height
    const ctx = canvas.getContext('2d')
    const radius = Math.min(img.width, img.height) * 0.04

    ctx.beginPath()
    ctx.moveTo(radius, 0)
    ctx.lineTo(img.width - radius, 0)
    ctx.quadraticCurveTo(img.width, 0, img.width, radius)
    ctx.lineTo(img.width, img.height - radius)
    ctx.quadraticCurveTo(img.width, img.height, img.width - radius, img.height)
    ctx.lineTo(radius, img.height)
    ctx.quadraticCurveTo(0, img.height, 0, img.height - radius)
    ctx.lineTo(0, radius)
    ctx.quadraticCurveTo(0, 0, radius, 0)
    ctx.closePath()
    ctx.clip()

    ctx.drawImage(img, 0, 0)
    URL.revokeObjectURL(img.src)

    const mimeType = extension === 'jpeg' ? 'image/jpeg' : 'image/png'
    canvas.toBlob((b) => {
        const a = document.createElement('a')
        a.href = URL.createObjectURL(b)
        a.download = `${name}.${extension}`
        a.click()
        URL.revokeObjectURL(a.href)
    }, mimeType, 0.95)
}

defineExpose({
    download: (name, extension, rounded = true) => {
        if (rounded) {
            downloadRounded(name, extension)
        } else {
            qrCode?.download({ name, extension })
        }
    },
    getRawData: (extension) => qrCode?.getRawData(extension),
})
</script>

<template>
    <div ref="container" class="qr-preview inline-flex"></div>
</template>

<style scoped>
.qr-preview :deep(canvas) {
    border-radius: 0.75rem;
}
</style>
