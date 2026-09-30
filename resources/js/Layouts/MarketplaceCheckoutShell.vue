<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import MkIcon from '@/components/marketplace/MkIcon.vue';
import MarketplaceBrand from '@/components/marketplace/MarketplaceBrand.vue';
import { useMarketplaceBranding } from '@/composables/useMarketplaceBranding';

const props = defineProps({
    title: { type: String, default: 'Finalizar compra' },
    subtitle: { type: String, default: '' },
    backHref: { type: String, default: '/' },
    backLabel: { type: String, default: 'Continuar comprando' },
    /** Quando true, não força fundo cream (usa o do slot/pai). */
    transparent: { type: Boolean, default: false },
});

const page = usePage();
const { appName, accent: brandingAccent } = useMarketplaceBranding();
const theme = computed(() => page.props.marketplaceTheme || {});
const accent = computed(() => theme.value.accent || brandingAccent.value || '#FF5A1F');
const user = () => page.props.auth?.user;
</script>

<template>
    <div class="mk-checkout flex min-h-screen flex-col" :style="{ '--mk-accent': accent }" :class="{ 'mk-checkout--fill': !transparent }">
        <Head>
            <link rel="preconnect" href="https://fonts.googleapis.com" />
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
            <link
                rel="stylesheet"
                href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap"
            />
        </Head>

        <div class="bg-[#1A1410] text-[12px] text-[#CDBFB2]">
            <div class="mx-auto flex h-9 max-w-[1240px] items-center justify-between gap-4 px-5">
                <span class="inline-flex items-center gap-1.5">
                    <MkIcon name="shield" :size="13" /> Pagamento protegido · entrega após a confirmação
                </span>
                <Link href="/" class="hidden hover:text-white sm:inline">Voltar à loja</Link>
            </div>
        </div>

        <header class="sticky top-0 z-50 border-b border-[#EBE2D8] bg-[#FBF7F2]/92 backdrop-blur-lg">
            <div class="mx-auto flex h-[64px] max-w-[1240px] items-center gap-4 px-5">
                <MarketplaceBrand href="/" variant="compact" />

                <div class="hidden min-w-0 flex-1 items-center gap-3 sm:flex">
                    <span class="h-5 w-px bg-[#E2D7CB]" aria-hidden="true" />
                    <div class="min-w-0">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.12em] text-[color:var(--mk-accent)]">Checkout</div>
                        <div class="truncate font-display text-[15px] font-bold tracking-[-0.02em] text-[#1A1410]">{{ title }}</div>
                    </div>
                </div>

                <nav class="ml-auto flex items-center gap-1.5">
                    <Link
                        :href="backHref"
                        class="hidden items-center gap-1.5 rounded-[10px] px-3 py-2 text-[13px] font-medium text-[#3D332B] hover:bg-[#F1EAE2] sm:inline-flex"
                    >
                        <MkIcon name="chevron-left" :size="14" />
                        {{ backLabel }}
                    </Link>
                    <Link
                        v-if="user()"
                        :href="user().role === 'infoprodutor' || user().role === 'team' ? '/dashboard' : '/painel-cliente'"
                        class="inline-flex items-center gap-2 rounded-[10px] border border-[#E2D7CB] bg-white px-3.5 py-2 text-[13px] font-semibold text-[#1A1410] hover:border-[#1A1410]"
                    >
                        <MkIcon name="user" :size="15" />
                        Conta
                    </Link>
                    <Link
                        v-else
                        href="/login"
                        class="inline-flex items-center gap-2 rounded-[10px] border border-[#E2D7CB] bg-white px-3.5 py-2 text-[13px] font-semibold text-[#1A1410] hover:border-[#1A1410]"
                    >
                        Entrar
                    </Link>
                </nav>
            </div>
        </header>

        <p v-if="subtitle" class="mx-auto w-full max-w-[1240px] px-5 pt-4 text-[13px] text-[#6B5E54] sm:hidden">{{ subtitle }}</p>

        <main class="flex-1">
            <slot />
        </main>

        <footer class="border-t border-[#EBE2D8] bg-white/70">
            <div class="mx-auto flex max-w-[1240px] flex-col gap-2 px-5 py-5 text-[12px] text-[#8A7B6E] sm:flex-row sm:items-center sm:justify-between">
                <span>© {{ new Date().getFullYear() }} {{ appName }} · Checkout seguro</span>
                <div class="flex flex-wrap gap-4">
                    <Link href="/termos-de-uso" class="hover:text-[#1A1410]">Termos</Link>
                    <Link href="/politica-privacidade" class="hover:text-[#1A1410]">Privacidade</Link>
                    <span class="inline-flex items-center gap-1"><MkIcon name="shield" :size="12" /> PIX e cartão</span>
                </div>
            </div>
        </footer>
    </div>
</template>

<style>
.mk-checkout {
    --mk-accent: #FF5A1F;
    color: #1A1410;
    font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
    font-feature-settings: 'cv11', 'ss01';
}
.mk-checkout--fill {
    background: #FBF7F2;
}
.mk-checkout .font-display {
    font-family: 'Plus Jakarta Sans', 'Inter', ui-sans-serif, system-ui, sans-serif;
}
</style>
