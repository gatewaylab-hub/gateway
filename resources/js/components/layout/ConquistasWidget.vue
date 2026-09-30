<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { formatCompactCurrency } from '@/lib/utils';
import { navPrefetch } from '@/composables/useAppSidebarNav';
import { useSellerDashboardTemplate } from '@/composables/useSellerDashboardTemplate';
import { useSidebar } from '@/composables/useSidebar';

const props = defineProps({
    variant: { type: String, default: 'header' }, // 'header' | 'sidebar' | 'dashboard'
});

const { isAurora, isKawaii, isThemedShell } = useSellerDashboardTemplate();
const { closeMobileSidebarIfOpen, isMobile } = useSidebar();

const page = usePage();
const progress = computed(() => page.props.achievementsProgress ?? null);

const iconUrl = computed(() => {
    if (!progress.value) return null;
    const curr = progress.value.current_achievement;
    const achievements = progress.value.achievements ?? [];
    const first = achievements[0];
    if (curr?.image) return curr.image;
    if (first?.image) return first.image;
    return null;
});

const isLocked = computed(() => {
    if (!progress.value) return true;
    return progress.value.current_achievement === null;
});

const progressPercent = computed(() => {
    return progress.value?.progress_percent ?? 0;
});

const nextLabel = computed(() => {
    const next = progress.value?.next_achievement;
    if (!next) return null;
    return formatCompactCurrency(next.threshold);
});

const remainingLabel = computed(() => {
    const remaining = progress.value?.remaining;
    if (remaining == null || remaining <= 0) return null;
    if (!progress.value?.next_achievement) return null;
    return formatCompactCurrency(remaining);
});

const totalLabel = computed(() => {
    const total = progress.value?.total_valid_sales ?? 0;
    return formatCompactCurrency(total);
});

const isSidebar = computed(() => props.variant === 'sidebar');
const showLabel = computed(
    () => props.variant === 'header'
        || props.variant === 'dashboard'
        || props.variant === 'sidebar',
);
</script>

<template>
    <Link
        v-if="progress"
        href="/conquistas"
        :prefetch="navPrefetch(isMobile)"
        class="group flex shrink-0 cursor-pointer touch-manipulation items-center gap-3 transition-colors"
        :class="{
            'flex-col items-stretch gap-2 rounded-[14px] border border-[#EBE2D8] bg-white px-3 py-3 hover:border-[#E2D7CB]': isSidebar && !isThemedShell,
            'flex-col items-stretch gap-2 rounded-lg px-3 py-2 hover:bg-zinc-100': isSidebar && isThemedShell,
            'w-full rounded-xl border border-[#EBE2D8] bg-[#F1EAE2]/50 px-4 py-3 hover:bg-[#F1EAE2]': props.variant === 'dashboard',
            'rounded-lg px-3 py-2 hover:bg-[#F1EAE2]': props.variant === 'header',
            '!border-0 !bg-transparent !p-0 hover:!bg-transparent': isAurora && isSidebar,
        }"
        title="Conquistas"
        @click="closeMobileSidebarIfOpen"
    >
        <div class="flex items-center gap-3" :class="isSidebar ? 'w-full' : ''">
            <div
                class="flex shrink-0 items-center justify-center overflow-hidden rounded-xl bg-[#F1EAE2]"
                :class="[
                    isSidebar ? 'h-11 w-11' : 'h-10 w-10',
                    { 'opacity-60 grayscale': isLocked },
                ]"
            >
                <img
                    v-if="iconUrl"
                    :src="iconUrl"
                    alt=""
                    :class="[
                        isSidebar ? 'h-7 w-7' : 'h-7 w-7',
                        'object-contain',
                    ]"
                />
            </div>
            <div
                class="min-w-0 flex-1"
                :class="[
                    isSidebar || props.variant === 'dashboard' ? 'w-full' : '',
                    props.variant === 'header' ? 'hidden w-[130px] sm:block' : '',
                ]"
            >
                <p
                    v-if="showLabel"
                    class="mb-1 text-[10px] font-semibold uppercase tracking-wide text-[#8A7B6E]"
                    :class="isAurora && isSidebar ? 'aurora-fg-muted' : isKawaii && isSidebar ? 'kawaii-fg-muted' : ''"
                >
                    Faturamento
                </p>
                <div
                    class="w-full overflow-hidden rounded-full bg-[#EBE2D8]"
                    :class="[
                        isSidebar || props.variant === 'dashboard' ? 'h-2' : 'h-2',
                    ]"
                >
                    <div
                        class="h-full rounded-full bg-[var(--color-primary)] transition-all duration-500"
                        :style="{ width: `${progressPercent}%` }"
                    />
                </div>
                <p
                    class="mt-1 truncate text-[#6B5E54]"
                    :class="[
                        isSidebar || props.variant === 'dashboard' ? 'text-xs' : 'text-[11px]',
                    ]"
                >
                    {{ totalLabel }}
                    <span v-if="nextLabel"> → {{ nextLabel }}</span>
                </p>
                <p
                    v-if="remainingLabel"
                    class="truncate text-[10px] text-[#8A7B6E]"
                >
                    Faltam {{ remainingLabel }}
                </p>
            </div>
        </div>
    </Link>
</template>
