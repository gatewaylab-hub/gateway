<script setup>
defineProps({
    modelValue: { type: [String, Number], default: '' },
    options: { type: Array, required: true },
    label: { type: String, default: '' },
});
const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <div>
        <div v-if="label" class="mb-1.5 text-[12px] font-semibold text-zinc-700 dark:text-zinc-300">{{ label }}</div>
        <div class="flex rounded-lg bg-zinc-100 p-0.5 dark:bg-zinc-800">
            <button
                v-for="opt in options"
                :key="opt.value"
                type="button"
                class="flex-1 rounded-md px-2 py-1.5 text-[12px] font-medium transition"
                :class="modelValue === opt.value
                    ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-950 dark:text-white'
                    : 'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100'"
                @click="emit('update:modelValue', opt.value)"
            >{{ opt.label }}</button>
        </div>
    </div>
</template>
