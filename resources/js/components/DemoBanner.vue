<script setup>
import { ref, computed, watch, onBeforeUnmount } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { X } from 'lucide-vue-next';

const page = usePage();
const demoMode = computed(() => !!page.props.demo_mode);
const bannerGradient = computed(() => {
    const from = page.props.public_branding?.theme_primary || '#8A2BE2';
    return `linear-gradient(to right, ${from}, rgb(124 58 237))`;
});
const dismissed = ref(false);
let nagTimer = null;

const visible = computed(() => demoMode.value && !dismissed.value);

function dismiss() {
    dismissed.value = true;
    if (nagTimer) {
        clearTimeout(nagTimer);
    }
    nagTimer = setTimeout(() => {
        dismissed.value = false;
        nagTimer = null;
    }, 60_000);
}

watch(
    visible,
    (v) => {
        if (typeof document === 'undefined') {
            return;
        }
        document.body.style.paddingTop = v ? '48px' : '';
    },
    { immediate: true }
);

onBeforeUnmount(() => {
    if (typeof document !== 'undefined') {
        document.body.style.paddingTop = '';
    }
    if (nagTimer) {
        clearTimeout(nagTimer);
    }
});
</script>

<template>
    <div
        v-if="visible"
        class="fixed inset-x-0 top-0 z-[100] flex h-12 items-center justify-center border-b border-white/20 px-12 text-white shadow-md"
        :style="{ background: bannerGradient }"
        role="region"
        aria-label="Modo demonstração"
    >
        <a
            href="https://gatewaylab.space/"
            target="_blank"
            rel="noopener noreferrer"
            class="text-sm font-semibold underline decoration-white/80 underline-offset-2 transition hover:decoration-white"
        >
            Adquira agora
        </a>
        <button
            type="button"
            class="absolute right-3 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg bg-white/20 transition hover:bg-white/30"
            aria-label="Fechar aviso (reaparece em 1 minuto)"
            @click="dismiss"
        >
            <X class="h-4 w-4" />
        </button>
    </div>
</template>
