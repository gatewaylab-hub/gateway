<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    seller: { type: Object, required: true },
    /** sm | md */
    size: { type: String, default: 'sm' },
    showCount: { type: Boolean, default: true },
    showFallbackStar: { type: Boolean, default: true },
    showLabel: { type: Boolean, default: false },
});

const href = computed(() => props.seller.profile_url || `/perfil/${props.seller.username}`);
const sizeClass = computed(() => (props.size === 'md' ? 'h-6 w-6' : 'h-4 w-4'));
const levelNumber = computed(() => {
    const unlocked = Array.isArray(props.seller.unlocked_levels) ? props.seller.unlocked_levels.length : 0;
    if (unlocked > 0) return unlocked;
    return props.seller.level ? 1 : 0;
});
const levelTitle = computed(() => {
    const name = props.seller.level?.name;
    if (!name) return null;
    return levelNumber.value > 0 ? `Nível ${levelNumber.value} — ${name}` : name;
});
const tooltipText = computed(() => {
    const parts = [];
    if (levelTitle.value) parts.push(levelTitle.value);
    if (props.seller.level?.description) parts.push(props.seller.level.description);
    if (props.seller.next_level?.name) {
        parts.push(`Próximo: ${props.seller.next_level.name}`);
    }
    return parts.join(' · ') || 'Ver reputação do vendedor';
});

const showTip = ref(false);
</script>

<template>
    <span
        class="relative inline-flex items-center gap-1"
        @mouseenter="showTip = true"
        @mouseleave="showTip = false"
        @focusin="showTip = true"
        @focusout="showTip = false"
    >
        <Link
            v-if="seller.level?.image"
            :href="href"
            class="inline-flex shrink-0 transition hover:opacity-90"
            :title="tooltipText"
        >
            <img
                :src="seller.level.image"
                :alt="seller.level.name || 'Conquista'"
                class="rounded-full object-cover ring-1 ring-[#E2D7CB]"
                :class="sizeClass"
            />
        </Link>
        <Link
            v-else-if="seller.level && showFallbackStar"
            :href="href"
            class="inline-flex shrink-0 items-center justify-center rounded-full bg-[#1A1410] text-[9px] font-bold text-amber-300 ring-1 ring-[#E2D7CB] transition hover:opacity-90"
            :class="sizeClass"
            :title="tooltipText"
        >★</Link>
        <Link
            v-if="showLabel && levelTitle"
            :href="href"
            class="hidden text-[11px] font-semibold text-[#3D332B] hover:underline sm:inline"
            :title="tooltipText"
        >{{ levelTitle }}</Link>
        <Link
            v-if="showCount"
            :href="href"
            class="text-[13px] text-[#8A7B6E] hover:text-[#1A1410] hover:underline"
            :title="tooltipText"
        >({{ seller.sales_count ?? 0 }})</Link>
        <span
            v-if="showTip && levelTitle"
            class="pointer-events-none absolute bottom-full left-1/2 z-20 mb-2 w-max max-w-[220px] -translate-x-1/2 rounded-lg bg-[#1A1410] px-2.5 py-1.5 text-[11px] font-medium leading-snug text-white shadow-lg"
            role="tooltip"
        >
            {{ tooltipText }}
        </span>
    </span>
</template>
