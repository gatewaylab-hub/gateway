<script setup>
import { computed } from 'vue';
import { categoryTheme } from './categoryTheme';

const props = defineProps({
    category: { type: Object, default: null },
    compact: { type: Boolean, default: false },
});

const theme = computed(() => categoryTheme(props.category));
</script>

<template>
    <div class="poster absolute inset-0 overflow-hidden" :style="{ background: theme.bg, '--accent': theme.accent }">
        <div class="poster-grain absolute inset-0" />
        <span
            class="poster-mark absolute font-display font-extrabold leading-none tracking-[-0.06em]"
            :class="compact ? 'text-[4.5rem] -right-2 -top-3' : 'text-[7.5rem] -right-3 -top-5'"
        >{{ theme.mark }}</span>
        <div class="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-black/55 to-transparent" />
    </div>
</template>

<style scoped>
.poster-grain {
    background-image:
        repeating-linear-gradient(135deg, rgba(255, 255, 255, 0.06) 0 1px, transparent 1px 10px),
        radial-gradient(120% 80% at 0% 100%, rgba(0, 0, 0, 0.35), transparent 60%);
}
.poster-mark {
    color: transparent;
    -webkit-text-stroke: 2px var(--accent);
    opacity: 0.9;
}
</style>
