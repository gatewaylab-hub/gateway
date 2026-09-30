<script setup>
defineProps({
    modelValue: { type: Boolean, default: false },
    label: { type: String, required: true },
    hint: { type: String, default: '' },
});
const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <button
        type="button"
        class="flex w-full items-center justify-between gap-3 text-left"
        role="switch"
        :aria-checked="modelValue"
        @click="emit('update:modelValue', !modelValue)"
    >
        <span>
            <span class="block text-[12px] font-semibold text-zinc-700 dark:text-zinc-300">{{ label }}</span>
            <span v-if="hint" class="block text-[11px] text-zinc-500">{{ hint }}</span>
        </span>
        <span
            class="relative h-5 w-9 shrink-0 rounded-full transition"
            :class="modelValue ? 'bg-emerald-500' : 'bg-zinc-300 dark:bg-zinc-700'"
        >
            <span
                class="absolute top-0.5 h-4 w-4 rounded-full bg-white shadow transition-all"
                :class="modelValue ? 'left-[18px]' : 'left-0.5'"
            />
        </span>
    </button>
</template>
