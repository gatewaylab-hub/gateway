<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useMarketplaceBranding } from '@/composables/useMarketplaceBranding';

const props = defineProps({
    href: { type: String, default: '/' },
    /** header | footer | compact */
    variant: { type: String, default: 'header' },
});

const { appName, logoUrl, iconUrl, accent, branding } = useMarketplaceBranding();

/** Logo completa: só a imagem. Só ícone: ícone + nome. Sem nada: marca SVG + nome. */
const fullLogo = computed(() => branding.value.app_logo || '');
const iconOnly = computed(() => !fullLogo.value && !!iconUrl.value);
const markSize = computed(() => (props.variant === 'header' ? 30 : 28));
const nameClass = computed(() => {
    if (props.variant === 'footer') return 'text-[20px] text-white';
    if (props.variant === 'compact') return 'text-[18px] text-[#1A1410]';
    return 'text-[21px] text-[#1A1410]';
});
</script>

<template>
    <Link :href="href" class="flex min-w-0 shrink-0 items-center gap-2.5">
        <img
            v-if="fullLogo"
            :src="fullLogo"
            :alt="appName"
            class="object-contain object-left"
            :class="variant === 'footer' ? 'h-8 max-w-[180px]' : 'h-[30px] max-w-[200px]'"
        />
        <template v-else-if="iconOnly">
            <img
                :src="iconUrl"
                :alt="appName"
                class="h-[30px] w-[30px] shrink-0 object-contain"
                :class="variant !== 'header' ? 'h-7 w-7' : ''"
            />
            <span class="font-display truncate font-extrabold tracking-[-0.03em]" :class="nameClass">{{ appName }}</span>
        </template>
        <template v-else>
            <svg
                :width="markSize"
                :height="markSize"
                viewBox="0 0 32 32"
                aria-hidden="true"
                class="shrink-0"
            >
                <rect width="32" height="32" rx="9" :fill="accent" />
                <path d="M22 11.5A7 7 0 1 0 23 17h-6.5" stroke="#fff" stroke-width="3.2" stroke-linecap="round" fill="none" />
            </svg>
            <span class="font-display truncate font-extrabold tracking-[-0.03em]" :class="nameClass">{{ appName }}</span>
        </template>
    </Link>
</template>
