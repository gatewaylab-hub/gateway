<script setup>
import MkIcon from '@/components/marketplace/MkIcon.vue';

defineProps({
    modelValue: { type: String, default: 'check' },
    icons: { type: Array, default: () => [] },
});
const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <div class="flex flex-wrap gap-1">
        <button
            v-for="icon in icons"
            :key="icon"
            type="button"
            class="flex h-7 w-7 items-center justify-center rounded-md border transition"
            :class="modelValue === icon
                ? 'border-zinc-900 bg-zinc-900 text-white dark:border-white dark:bg-white dark:text-zinc-900'
                : 'border-zinc-200 text-zinc-500 hover:border-zinc-400 hover:text-zinc-900 dark:border-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-100'"
            :title="icon"
            @click="emit('update:modelValue', icon)"
        >
            <MkIcon :name="icon" :size="14" />
        </button>
    </div>
</template>
