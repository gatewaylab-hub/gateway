<script setup>
import { computed } from 'vue';

const props = defineProps({
    label: { type: String, required: true },
    hint: { type: String, default: '' },
    modelValue: { type: [String, Number], default: undefined },
    max: { type: Number, default: 0 },
    multiline: { type: Boolean, default: false },
    rows: { type: Number, default: 3 },
    placeholder: { type: String, default: '' },
    type: { type: String, default: 'text' },
});
const emit = defineEmits(['update:modelValue']);

const isInput = computed(() => props.modelValue !== undefined);
const count = computed(() => String(props.modelValue ?? '').length);
const inputClass =
    'w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-[13px] text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-zinc-900 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:focus:border-zinc-300';
</script>

<template>
    <label class="block">
        <span class="mb-1.5 flex items-baseline justify-between gap-2">
            <span class="text-[12px] font-semibold text-zinc-700 dark:text-zinc-300">{{ label }}</span>
            <span
                v-if="isInput && max"
                class="text-[11px] tabular-nums"
                :class="count > max * 0.9 ? 'text-amber-600' : 'text-zinc-400'"
            >{{ count }}/{{ max }}</span>
        </span>
        <template v-if="isInput">
            <textarea
                v-if="multiline"
                :value="modelValue"
                :rows="rows"
                :maxlength="max || undefined"
                :placeholder="placeholder"
                :class="[inputClass, 'resize-none leading-relaxed']"
                @input="emit('update:modelValue', $event.target.value)"
            />
            <input
                v-else
                :type="type"
                :value="modelValue"
                :maxlength="max || undefined"
                :placeholder="placeholder"
                :class="inputClass"
                @input="emit('update:modelValue', type === 'number' ? Number($event.target.value) : $event.target.value)"
            />
        </template>
        <slot v-else />
        <span v-if="hint" class="mt-1 block text-[11px] leading-snug text-zinc-500">{{ hint }}</span>
    </label>
</template>
