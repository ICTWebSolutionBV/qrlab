<script setup>
import { computed } from 'vue'
import QrPreview from './QrPreview.vue'
import * as LucideIcons from 'lucide-vue-next'

const props = defineProps({
    // QR options — forwarded to QrPreview
    data: { type: String, required: true },
    size: { type: Number, default: 220 },
    dotsColor: { type: String, default: '#000000' },
    backgroundColor: { type: String, default: '#FFFFFF' },
    dotStyle: { type: String, default: 'square' },
    cornerSquareStyle: { type: String, default: 'square' },
    cornerDotStyle: { type: String, default: 'square' },
    errorCorrectionLevel: { type: String, default: 'M' },
    logoUrl: { type: String, default: null },
    logoSize: { type: Number, default: 0.3 },
    margin: { type: Number, default: 4 },

    // Frame options
    // none | scan-me-top | scan-me-bottom | rounded | banner-bottom
    // tag-notch | pill-icon
    // arrow-br | arrow-bl | arrow-tr | arrow-tl
    frameStyle: { type: String, default: 'none' },
    frameColor: { type: String, default: '#111827' },       // border color
    frameLabelColor: { type: String, default: null },       // label bg color (falls back to frameColor)
    frameText: { type: String, default: 'SCAN ME' },
    frameFontSize: { type: Number, default: 14 },
    frameFontFamily: { type: String, default: 'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif' },
    frameLabelGap: { type: Number, default: 10 },
    frameIcon: { type: String, default: 'none' },
})

const effectiveLabelColor = computed(() => props.frameLabelColor || props.frameColor)

// Text color is white when label bg is dark, dark otherwise.
const labelTextColor = computed(() => {
    const h = effectiveLabelColor.value.replace('#', '')
    const r = parseInt(h.slice(0, 2), 16) || 0
    const g = parseInt(h.slice(2, 4), 16) || 0
    const b = parseInt(h.slice(4, 6), 16) || 0
    const yiq = (r * 299 + g * 587 + b * 114) / 1000
    return yiq < 160 ? '#ffffff' : '#111111'
})

const innerSize = computed(() => props.size - 24)

const cursiveText = computed(() => {
    const t = (props.frameText || '').trim()
    if (!t) return 'Scan me'
    const lower = t.toLowerCase()
    return lower.charAt(0).toUpperCase() + lower.slice(1)
})

const isArrow = computed(() => props.frameStyle.startsWith('arrow-'))

const IconComponent = computed(() => {
    if (!props.frameIcon || props.frameIcon === 'none') return null
    return LucideIcons[props.frameIcon] || null
})
</script>

<template>
    <div class="qf-wrap" :data-style="frameStyle" :style="{
        '--frame-color': frameColor,
        '--frame-label-color': effectiveLabelColor,
        '--frame-label-text': labelTextColor,
        '--frame-font-size': frameFontSize + 'px',
        '--frame-font-family': frameFontFamily,
        '--frame-label-gap': frameLabelGap + 'px',
        '--frame-size': size + 'px',
    }">
        <!-- none -->
        <template v-if="frameStyle === 'none'">
            <QrPreview v-bind="{ data, width: size, height: size, dotsColor, backgroundColor, dotStyle, cornerSquareStyle, cornerDotStyle, errorCorrectionLevel, logoUrl, logoSize, margin }" />
        </template>

        <!-- scan-me-top -->
        <template v-else-if="frameStyle === 'scan-me-top'">
            <div class="qf-frame-bordered">
                <div class="qf-chip">{{ frameText }}</div>
                <QrPreview v-bind="{ data, width: innerSize, height: innerSize, dotsColor, backgroundColor, dotStyle, cornerSquareStyle, cornerDotStyle, errorCorrectionLevel, logoUrl, logoSize, margin }" />
            </div>
        </template>

        <!-- scan-me-bottom -->
        <template v-else-if="frameStyle === 'scan-me-bottom'">
            <div class="qf-frame-bordered">
                <QrPreview v-bind="{ data, width: innerSize, height: innerSize, dotsColor, backgroundColor, dotStyle, cornerSquareStyle, cornerDotStyle, errorCorrectionLevel, logoUrl, logoSize, margin }" />
                <div class="qf-chip">{{ frameText }}</div>
            </div>
        </template>

        <!-- rounded -->
        <template v-else-if="frameStyle === 'rounded'">
            <div class="qf-frame-rounded">
                <QrPreview v-bind="{ data, width: innerSize, height: innerSize, dotsColor, backgroundColor, dotStyle, cornerSquareStyle, cornerDotStyle, errorCorrectionLevel, logoUrl, logoSize, margin }" />
            </div>
        </template>

        <!-- banner-bottom -->
        <template v-else-if="frameStyle === 'banner-bottom'">
            <div class="qf-frame-rounded qf-rounded-compact">
                <QrPreview v-bind="{ data, width: innerSize, height: innerSize, dotsColor, backgroundColor, dotStyle, cornerSquareStyle, cornerDotStyle, errorCorrectionLevel, logoUrl, logoSize, margin }" />
                <div class="qf-banner">
                    <span class="qf-banner-arrow"></span>
                    <span>{{ frameText }}</span>
                </div>
            </div>
        </template>

        <!-- tag-notch: rounded card + detached rectangular label with upward notch -->
        <template v-else-if="frameStyle === 'tag-notch'">
            <div class="qf-tag-stage">
                <div class="qf-frame-rounded qf-card">
                    <QrPreview v-bind="{ data, width: innerSize, height: innerSize, dotsColor, backgroundColor, dotStyle, cornerSquareStyle, cornerDotStyle, errorCorrectionLevel, logoUrl, logoSize, margin }" />
                </div>
                <div class="qf-tag">
                    <span class="qf-notch-arrow" aria-hidden="true"></span>
                    <span class="qf-tag-label">{{ frameText }}</span>
                </div>
            </div>
        </template>

        <!-- pill-icon: rounded card + detached pill label with optional icon -->
        <template v-else-if="frameStyle === 'pill-icon'">
            <div class="qf-tag-stage">
                <div class="qf-frame-rounded qf-card">
                    <QrPreview v-bind="{ data, width: innerSize, height: innerSize, dotsColor, backgroundColor, dotStyle, cornerSquareStyle, cornerDotStyle, errorCorrectionLevel, logoUrl, logoSize, margin }" />
                </div>
                <div class="qf-pill">
                    <component v-if="IconComponent" :is="IconComponent" class="qf-pill-icon" />
                    <span>{{ frameText }}</span>
                </div>
            </div>
        </template>

        <!-- arrow-* -->
        <template v-else-if="isArrow">
            <div class="qf-arrow-stage">
                <div class="qf-frame-rounded qf-card">
                    <QrPreview v-bind="{ data, width: innerSize, height: innerSize, dotsColor, backgroundColor, dotStyle, cornerSquareStyle, cornerDotStyle, errorCorrectionLevel, logoUrl, logoSize, margin }" />
                </div>

                <template v-if="frameStyle === 'arrow-br'">
                    <div class="qf-cursive qf-cursive-br">{{ cursiveText }}</div>
                    <svg class="qf-arrow-svg qf-arrow-br" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M80 80 C 60 70, 40 60, 22 28" fill="none" :stroke="frameColor" stroke-width="3" stroke-linecap="round" />
                        <path d="M14 18 L 22 28 L 32 24" fill="none" :stroke="frameColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </template>
                <template v-else-if="frameStyle === 'arrow-bl'">
                    <div class="qf-cursive qf-cursive-bl">{{ cursiveText }}</div>
                    <svg class="qf-arrow-svg qf-arrow-bl" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M20 80 C 40 70, 60 60, 78 28" fill="none" :stroke="frameColor" stroke-width="3" stroke-linecap="round" />
                        <path d="M86 18 L 78 28 L 68 24" fill="none" :stroke="frameColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </template>
                <template v-else-if="frameStyle === 'arrow-tr'">
                    <div class="qf-cursive qf-cursive-tr">{{ cursiveText }}</div>
                    <svg class="qf-arrow-svg qf-arrow-tr" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M80 20 C 60 30, 40 40, 22 72" fill="none" :stroke="frameColor" stroke-width="3" stroke-linecap="round" />
                        <path d="M14 82 L 22 72 L 32 76" fill="none" :stroke="frameColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </template>
                <template v-else-if="frameStyle === 'arrow-tl'">
                    <div class="qf-cursive qf-cursive-tl">{{ cursiveText }}</div>
                    <svg class="qf-arrow-svg qf-arrow-tl" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M20 20 C 40 30, 60 40, 78 72" fill="none" :stroke="frameColor" stroke-width="3" stroke-linecap="round" />
                        <path d="M86 82 L 78 72 L 68 76" fill="none" :stroke="frameColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </template>
            </div>
        </template>
    </div>
</template>

<style scoped>
.qf-wrap {
    display: inline-block;
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
}
.qf-wrap :deep(canvas) {
    border-radius: 4px;
    display: block;
}

.qf-frame-bordered {
    border: 3px solid var(--frame-color);
    border-radius: 12px;
    padding: 12px;
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    gap: var(--frame-label-gap);
    background: #fff;
}

.qf-frame-rounded {
    border: 3px solid var(--frame-color);
    border-radius: 16px;
    padding: 10px;
    display: inline-flex;
    background: #fff;
    position: relative;
}
.qf-rounded-compact {
    flex-direction: column;
    align-items: stretch;
    padding: 10px 10px 0;
    overflow: hidden;
}

.qf-chip {
    background: var(--frame-label-color);
    color: var(--frame-label-text);
    font-family: var(--frame-font-family);
    font-weight: 700;
    letter-spacing: 0.08em;
    font-size: var(--frame-font-size);
    padding: 4px 14px;
    border-radius: 4px;
    text-transform: uppercase;
}

/* Banner */
.qf-banner {
    background: var(--frame-label-color);
    color: var(--frame-label-text);
    font-family: var(--frame-font-family);
    font-weight: 700;
    letter-spacing: 0.1em;
    font-size: var(--frame-font-size);
    text-align: center;
    padding: 8px 10px;
    text-transform: uppercase;
    margin: var(--frame-label-gap) -10px 0;
    position: relative;
}
.qf-banner-arrow {
    position: absolute;
    top: -7px;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 0;
    border-left: 8px solid transparent;
    border-right: 8px solid transparent;
    border-bottom: 8px solid var(--frame-label-color);
}

/* Tag / Pill stage — QR card with a detached label below */
.qf-tag-stage {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    gap: var(--frame-label-gap);
}
.qf-card {
    border-radius: 18px;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
}

/* Tag-notch: rectangular label with an upward-pointing notch */
.qf-tag {
    position: relative;
    background: var(--frame-label-color);
    color: var(--frame-label-text);
    font-family: var(--frame-font-family);
    padding: 8px 22px;
    border-radius: 6px;
    font-weight: 700;
    letter-spacing: 0.1em;
    font-size: var(--frame-font-size);
    text-transform: uppercase;
    line-height: 1.2;
}
.qf-notch-arrow {
    position: absolute;
    top: -7px;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 0;
    border-left: 8px solid transparent;
    border-right: 8px solid transparent;
    border-bottom: 8px solid var(--frame-label-color);
}

/* Pill with icon */
.qf-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--frame-label-color);
    color: var(--frame-label-text);
    font-family: var(--frame-font-family);
    padding: 8px 18px 8px 10px;
    border-radius: 999px;
    font-weight: 700;
    letter-spacing: 0.08em;
    font-size: var(--frame-font-size);
    text-transform: uppercase;
    line-height: 1.2;
}
.qf-pill-icon {
    width: calc(var(--frame-font-size) + 8px);
    height: calc(var(--frame-font-size) + 8px);
    background: var(--frame-label-text);
    color: var(--frame-label-color);
    border-radius: 999px;
    padding: 3px;
    flex-shrink: 0;
}

/* Arrow / cursive */
.qf-arrow-stage {
    position: relative;
    display: inline-block;
    padding: calc(22px + var(--frame-label-gap)) calc(26px + var(--frame-label-gap));
}
.qf-cursive {
    position: absolute;
    font-family: var(--frame-font-family);
    font-weight: 400;
    font-size: calc(var(--frame-font-size) * 2.2);
    line-height: 1;
    color: var(--frame-color);
    white-space: nowrap;
    transform: rotate(-4deg);
}
.qf-cursive-br { bottom: 8px; right: 8px; }
.qf-cursive-bl { bottom: 8px; left: 8px; transform: rotate(4deg); }
.qf-cursive-tr { top: 8px; right: 8px; transform: rotate(4deg); }
.qf-cursive-tl { top: 8px; left: 8px; transform: rotate(-4deg); }

.qf-arrow-svg {
    position: absolute;
    width: 56px;
    height: 56px;
    pointer-events: none;
}
.qf-arrow-br { bottom: 24px; right: 24px; }
.qf-arrow-bl { bottom: 24px; left: 24px; }
.qf-arrow-tr { top: 24px; right: 24px; }
.qf-arrow-tl { top: 24px; left: 24px; }
</style>
