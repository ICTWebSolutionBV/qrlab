<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'
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

defineExpose({
    download: (name, extension) => qrCode?.download({ name, extension }),
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
