<script setup>
import { computed, ref } from 'vue'
import { sansSerif, getFontStack } from '@/config/fonts.js'

const presetSizes = ['12', '14', '16', '18', '20', '24', '28', '32']

const props = defineProps({
    modelValue: {
        type: Object,
        default: () => ({
            text: '',
            fontFamily: 'Inter',
            fontSize: '16',
            color: '#000000',
            bold: false,
            italic: false,
            underline: false,
            alignment: 'center',
            margin: 8,
            marginCustom: false,
            marginTop: 8,
            marginRight: 0,
            marginBottom: 8,
            marginLeft: 0,
        }),
    },
    label: { type: String, default: 'Text' },
    colorLabel: { type: String, default: 'Color' },
})

const emit = defineEmits(['update:modelValue'])

function update(key, value) {
    emit('update:modelValue', { ...props.modelValue, [key]: value })
}

function toggleCustomMargin() {
    const next = !props.modelValue.marginCustom
    const m = props.modelValue.margin ?? 8
    emit('update:modelValue', {
        ...props.modelValue,
        marginCustom: next,
        ...(next ? { marginTop: m, marginRight: 0, marginBottom: m, marginLeft: 0 } : {}),
    })
}

const hasText = computed(() => !!props.modelValue.text?.trim())
const customSize = ref(false)
const isCustomSize = computed(() => customSize.value || !presetSizes.includes(String(props.modelValue.fontSize)))

function onSizeChange(val) {
    if (val === 'custom') {
        customSize.value = true
    } else {
        customSize.value = false
        update('fontSize', val)
    }
}

const previewStyle = computed(() => ({
    fontFamily: getFontStack(props.modelValue.fontFamily),
    fontSize: props.modelValue.fontSize + 'px',
    color: props.modelValue.color,
    fontWeight: props.modelValue.bold ? '700' : '400',
    fontStyle: props.modelValue.italic ? 'italic' : 'normal',
    textDecoration: props.modelValue.underline ? 'underline' : 'none',
    textAlign: props.modelValue.alignment,
}))
</script>

<template>
    <div class="space-y-2">
        <!-- Text input -->
        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ label }}</label>
            <input
                type="text"
                :value="modelValue.text"
                @input="update('text', $event.target.value)"
                class="w-full px-3 py-1.5 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                placeholder="Optional label text"
            />
        </div>

        <template v-if="hasText">
            <!-- Row 1: Font + Size + B/I/U + Color -->
            <div class="flex items-end gap-1.5">
                <div class="flex-1 min-w-0">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Font</label>
                    <select
                        :value="modelValue.fontFamily"
                        @change="update('fontFamily', $event.target.value)"
                        class="w-full px-2 py-1.5 text-xs border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                        <option v-for="font in sansSerif" :key="font.value" :value="font.value" :style="{ fontFamily: font.stack }">
                            {{ font.label }}
                        </option>
                    </select>
                </div>
                <div class="shrink-0" :class="isCustomSize ? 'w-20' : 'w-14'">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Size</label>
                    <select v-if="!isCustomSize"
                        :value="modelValue.fontSize"
                        @change="onSizeChange($event.target.value)"
                        class="w-full px-1.5 py-1.5 text-xs border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                        <option value="12">12</option>
                        <option value="14">14</option>
                        <option value="16">16</option>
                        <option value="18">18</option>
                        <option value="20">20</option>
                        <option value="24">24</option>
                        <option value="28">28</option>
                        <option value="32">32</option>
                        <option value="custom">px…</option>
                    </select>
                    <div v-else class="flex items-center gap-0.5">
                        <input type="number"
                            :value="modelValue.fontSize"
                            @input="update('fontSize', $event.target.value)"
                            min="8" max="120" step="1"
                            class="w-12 px-1.5 py-1.5 text-xs border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" />
                        <span class="text-xs text-gray-400 select-none">px</span>
                    </div>
                </div>
                <div class="flex items-center gap-0.5 shrink-0">
                    <button type="button" @click="update('bold', !modelValue.bold)"
                        :class="['w-7 h-7 rounded text-xs font-bold border transition-colors', modelValue.bold ? 'bg-emerald-600 border-emerald-600 text-white' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:border-emerald-400']"
                        title="Bold">B</button>
                    <button type="button" @click="update('italic', !modelValue.italic)"
                        :class="['w-7 h-7 rounded text-xs italic border transition-colors', modelValue.italic ? 'bg-emerald-600 border-emerald-600 text-white' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:border-emerald-400']"
                        title="Italic">I</button>
                    <button type="button" @click="update('underline', !modelValue.underline)"
                        :class="['w-7 h-7 rounded text-xs underline border transition-colors', modelValue.underline ? 'bg-emerald-600 border-emerald-600 text-white' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:border-emerald-400']"
                        title="Underline">U</button>
                </div>
                <!-- Color -->
                <div class="shrink-0">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ colorLabel }}</label>
                    <div class="flex items-center gap-1">
                        <input type="color" :value="modelValue.color" @input="update('color', $event.target.value)"
                            class="w-7 h-7 rounded cursor-pointer border border-gray-200 dark:border-gray-700 p-0.5 bg-white dark:bg-gray-800 shrink-0" />
                        <input type="text" :value="modelValue.color" @change="update('color', $event.target.value)"
                            maxlength="7"
                            class="w-16 px-1.5 py-1.5 text-xs font-mono border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                    </div>
                </div>
            </div>

            <!-- Row 2: Alignment + Margin -->
            <div class="flex items-end gap-1.5">
                <!-- Alignment -->
                <div class="shrink-0">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Align</label>
                    <div class="flex gap-0.5">
                        <button v-for="align in ['left', 'center', 'right']" :key="align" type="button"
                            @click="update('alignment', align)"
                            :class="['w-7 h-7 rounded text-xs border transition-colors', modelValue.alignment === align ? 'bg-emerald-600 border-emerald-600 text-white' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:border-emerald-400']">
                            <span v-if="align === 'left'">&#9664;</span>
                            <span v-else-if="align === 'center'">&#9644;</span>
                            <span v-else>&#9654;</span>
                        </button>
                    </div>
                </div>

                <!-- Margin -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Margin</label>
                        <button type="button" @click="toggleCustomMargin"
                            :class="['text-xs px-1.5 py-0.5 rounded border transition-colors leading-none', modelValue.marginCustom ? 'bg-emerald-600 border-emerald-600 text-white' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 hover:border-emerald-400']">
                            Custom
                        </button>
                    </div>
                    <!-- Uniform slider -->
                    <div v-if="!modelValue.marginCustom" class="flex items-center gap-1.5">
                        <input type="range" :value="modelValue.margin" @input="update('margin', Number($event.target.value))"
                            min="0" max="40" step="1" class="flex-1 h-1 accent-emerald-600" />
                        <span class="text-xs text-gray-400 w-7 text-right tabular-nums">{{ modelValue.margin }}</span>
                    </div>
                    <!-- Custom 4-box -->
                    <div v-else class="grid grid-cols-4 gap-1">
                        <input v-for="side in [{ key: 'marginTop', label: 'T' }, { key: 'marginRight', label: 'R' }, { key: 'marginBottom', label: 'B' }, { key: 'marginLeft', label: 'L' }]"
                            :key="side.key"
                            type="number" :value="modelValue[side.key] ?? 0"
                            :title="side.label === 'T' ? 'Top' : side.label === 'R' ? 'Right' : side.label === 'B' ? 'Bottom' : 'Left'"
                            :placeholder="side.label"
                            @input="update(side.key, Number($event.target.value))"
                            min="0" max="80" step="1"
                            class="w-full px-0 py-1.5 text-xs text-center border border-gray-200 dark:border-gray-700 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" />
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>
