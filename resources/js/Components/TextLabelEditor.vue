<script setup>
import { computed } from 'vue'
import { sansSerif, getFontStack } from '@/config/fonts.js'

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
        }),
    },
    label: { type: String, default: 'Text' },
    colorLabel: { type: String, default: 'Color' },
})

const emit = defineEmits(['update:modelValue'])

function update(key, value) {
    emit('update:modelValue', { ...props.modelValue, [key]: value })
}

const hasText = computed(() => !!props.modelValue.text?.trim())

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
    <div class="space-y-3">
        <!-- Text input -->
        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ label }}</label>
            <input
                type="text"
                :value="modelValue.text"
                @input="update('text', $event.target.value)"
                class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                placeholder="Optional label text"
            />
        </div>

        <!-- Styling controls — only shown when text is present -->
        <template v-if="hasText">
            <!-- Font + Size row -->
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Font</label>
                    <select
                        :value="modelValue.fontFamily"
                        @change="update('fontFamily', $event.target.value)"
                        class="w-full px-2 py-1.5 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                        <option v-for="font in sansSerif" :key="font.value" :value="font.value" :style="{ fontFamily: font.stack }">
                            {{ font.label }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Size</label>
                    <select
                        :value="modelValue.fontSize"
                        @change="update('fontSize', $event.target.value)"
                        class="w-full px-2 py-1.5 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
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
            </div>

            <!-- Color + Markup row -->
            <div class="flex items-end gap-3">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ colorLabel }}</label>
                    <div class="flex items-center gap-2">
                        <input
                            type="color"
                            :value="modelValue.color"
                            @input="update('color', $event.target.value)"
                            class="w-8 h-8 rounded cursor-pointer border border-gray-200 dark:border-gray-700 p-0.5 bg-white dark:bg-gray-800"
                        />
                        <input
                            type="text"
                            :value="modelValue.color"
                            @change="update('color', $event.target.value)"
                            maxlength="7"
                            class="w-24 px-2 py-1.5 text-sm font-mono border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        />
                    </div>
                </div>

                <!-- Bold / Italic / Underline -->
                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        @click="update('bold', !modelValue.bold)"
                        :class="[
                            'w-8 h-8 rounded text-sm font-bold border transition-colors',
                            modelValue.bold
                                ? 'bg-emerald-600 border-emerald-600 text-white'
                                : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:border-emerald-400'
                        ]"
                        title="Bold"
                    >B</button>
                    <button
                        type="button"
                        @click="update('italic', !modelValue.italic)"
                        :class="[
                            'w-8 h-8 rounded text-sm italic border transition-colors',
                            modelValue.italic
                                ? 'bg-emerald-600 border-emerald-600 text-white'
                                : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:border-emerald-400'
                        ]"
                        title="Italic"
                    >I</button>
                    <button
                        type="button"
                        @click="update('underline', !modelValue.underline)"
                        :class="[
                            'w-8 h-8 rounded text-sm underline border transition-colors',
                            modelValue.underline
                                ? 'bg-emerald-600 border-emerald-600 text-white'
                                : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:border-emerald-400'
                        ]"
                        title="Underline"
                    >U</button>
                </div>
            </div>

            <!-- Alignment + Margin -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Alignment</label>
                    <div class="flex gap-1">
                        <button
                            v-for="align in ['left', 'center', 'right']"
                            :key="align"
                            type="button"
                            @click="update('alignment', align)"
                            :class="[
                                'flex-1 py-1.5 rounded text-xs border capitalize transition-colors',
                                modelValue.alignment === align
                                    ? 'bg-emerald-600 border-emerald-600 text-white'
                                    : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:border-emerald-400'
                            ]"
                        >
                            <span v-if="align === 'left'">&#9664;</span>
                            <span v-else-if="align === 'center'">&#9644;</span>
                            <span v-else>&#9654;</span>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Margin <span class="text-gray-400">{{ modelValue.margin }}px</span></label>
                    <input
                        type="range"
                        :value="modelValue.margin"
                        @input="update('margin', Number($event.target.value))"
                        min="0" max="40" step="1"
                        class="w-full accent-emerald-600"
                    />
                </div>
            </div>

            <!-- Live mini preview -->
            <div class="rounded-lg bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-800 px-3 py-2 text-center overflow-hidden">
                <span :style="previewStyle">{{ modelValue.text }}</span>
            </div>
        </template>
    </div>
</template>
