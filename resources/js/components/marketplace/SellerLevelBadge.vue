<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    seller: { type: Object, required: true },
    /** sm | md */
    size: { type: String, default: 'sm' },
    showCount: { type: Boolean, default: true },
    showFallbackStar: { type: Boolean, default: true },
});

const href = computed(() => props.seller.profile_url || `/perfil/${props.seller.username}`);
const sizeClass = computed(() => (props.size === 'md' ? 'h-6 w-6' : 'h-4 w-4'));
</script>

<template>
    <span class="inline-flex items-center gap-1">
        <Link
            v-if="seller.level?.image"
            :href="href"
            class="inline-flex shrink-0 transition hover:opacity-90"
            :title="`${seller.level.name || 'Conquista'} — ver reputação`"
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
            :title="`${seller.level.name || 'Conquista'} — ver reputação`"
        >★</Link>
        <Link
            v-if="showCount"
            :href="href"
            class="text-[13px] text-[#8A7B6E] hover:text-[#1A1410] hover:underline"
            title="Ver reputação do vendedor"
        >({{ seller.sales_count ?? 0 }})</Link>
    </span>
</template>
