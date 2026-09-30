<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted } from 'vue';
import MkIcon from '@/components/marketplace/MkIcon.vue';
import MarketplaceBrand from '@/components/marketplace/MarketplaceBrand.vue';
import { useMarketplaceBranding } from '@/composables/useMarketplaceBranding';

const props = defineProps({
    title: { type: String, default: '' },
    theme: { type: Object, default: null },
});

const page = usePage();
const { appName, accent: brandingAccent } = useMarketplaceBranding();
const theme = computed(() => props.theme || page.props.marketplaceTheme || {});
const accent = computed(() => theme.value.accent || brandingAccent.value || '#FF5A1F');
const topbarItems = computed(() => {
    if (theme.value.topbar_enabled === false) return [];
    return (theme.value.topbar_items || [
        { icon: 'shield', text: 'Pagamento protegido até a entrega' },
        { icon: 'bolt', text: 'Códigos liberados na hora' },
    ]).filter((i) => i.text);
});
const showTopbar = computed(() => theme.value.topbar_enabled !== false);
const user = () => page.props.auth?.user;
const currentQuery = () => {
    try {
        return new URL(page.url, 'http://x').searchParams.get('q') || '';
    } catch {
        return '';
    }
};

let heartbeatTimer = null;
onMounted(() => {
    if (!user()) return;
    const beat = () => {
        fetch('/presence/heartbeat', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                Accept: 'application/json',
            },
            credentials: 'same-origin',
        }).catch(() => {});
    };
    beat();
    heartbeatTimer = setInterval(beat, 60000);
});
onUnmounted(() => {
    if (heartbeatTimer) clearInterval(heartbeatTimer);
});
</script>

<template>
    <Head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
        <link
            rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap"
        />
    </Head>

    <div class="mk-root flex min-h-screen flex-col" :style="{ '--mk-accent': accent }">
        <div v-if="showTopbar" data-mk-section="theme" class="bg-[#1A1410] text-[12px] text-[#CDBFB2]">
            <div class="mx-auto flex h-9 max-w-[1240px] items-center justify-between px-5">
                <div class="flex items-center gap-5">
                    <span
                        v-for="(item, i) in topbarItems"
                        :key="i"
                        class="items-center gap-1.5"
                        :class="i === 0 ? 'inline-flex' : 'hidden md:inline-flex'"
                    ><MkIcon :name="item.icon" :size="13" /> {{ item.text }}</span>
                </div>
                <div class="hidden items-center gap-5 sm:flex">
                    <Link href="/cadastro" class="hover:text-white">Seja vendedor</Link>
                    <Link href="/termos-de-uso" class="hover:text-white">Ajuda</Link>
                </div>
            </div>
        </div>

        <header class="sticky top-0 z-50 border-b border-[#EBE2D8] bg-[#FBF7F2]/90 backdrop-blur-lg">
            <div class="mx-auto flex h-[68px] max-w-[1240px] items-center gap-6 px-5">
                <MarketplaceBrand href="/" />

                <form action="/buscar" method="get" class="hidden flex-1 md:block">
                    <label class="group flex h-11 max-w-[560px] items-center gap-2.5 rounded-[12px] border border-[#E2D7CB] bg-white px-4 transition focus-within:border-[#1A1410]">
                        <MkIcon name="search" :size="17" class="text-[#8A7B6E]" />
                        <input
                            type="search"
                            name="q"
                            :value="currentQuery()"
                            placeholder="Buscar contas, gift cards, skins..."
                            class="h-full w-full border-0 bg-transparent p-0 text-[14px] text-[#1A1410] outline-none placeholder:text-[#A3958A] focus:ring-0"
                        />
                        <kbd class="hidden rounded-md border border-[#E2D7CB] px-1.5 py-0.5 text-[10px] font-semibold text-[#A3958A] lg:inline">Enter</kbd>
                    </label>
                </form>

                <nav class="ml-auto flex items-center gap-1.5">
                    <Link href="/buscar" class="rounded-lg px-3 py-2 text-[14px] font-medium text-[#3D332B] hover:bg-[#F1EAE2] md:hidden">
                        <MkIcon name="search" :size="18" />
                    </Link>
                    <template v-if="user()">
                        <Link href="/chat" class="hidden items-center gap-1.5 rounded-lg px-3 py-2 text-[14px] font-medium text-[#3D332B] hover:bg-[#F1EAE2] sm:inline-flex">
                            <MkIcon name="chat" :size="17" /> Mensagens
                        </Link>
                        <Link
                            :href="user().role === 'infoprodutor' || user().role === 'team' ? '/dashboard' : '/painel-cliente'"
                            class="inline-flex items-center gap-2 rounded-[10px] bg-[#1A1410] px-4 py-2.5 text-[14px] font-semibold text-white hover:bg-black"
                        >
                            <MkIcon name="user" :size="16" />
                            {{ user().role === 'infoprodutor' || user().role === 'team' ? 'Painel' : 'Minha conta' }}
                        </Link>
                    </template>
                    <template v-else>
                        <Link href="/login" class="rounded-lg px-3 py-2 text-[14px] font-medium text-[#3D332B] hover:bg-[#F1EAE2]">Entrar</Link>
                        <Link href="/criar-conta" class="hidden rounded-lg px-3 py-2 text-[14px] font-medium text-[#3D332B] hover:bg-[#F1EAE2] sm:inline">Criar conta</Link>
                        <Link
                            href="/cadastro"
                            class="inline-flex items-center gap-1.5 rounded-[10px] bg-[color:var(--mk-accent)] px-4 py-2.5 text-[14px] font-semibold text-white hover:brightness-95"
                        >Anunciar</Link>
                    </template>
                </nav>
            </div>
        </header>

        <main class="flex-1">
            <slot />
        </main>

        <footer class="bg-[#1A1410] text-[#CDBFB2]">
            <div class="mx-auto max-w-[1240px] px-5 pt-16">
                <div class="grid gap-12 pb-14 md:grid-cols-[1.4fr_1fr_1fr_1fr]">
                    <div>
                        <MarketplaceBrand href="/" variant="footer" />
                        <p class="mt-4 max-w-[300px] text-[14px] leading-relaxed text-[#A3958A]">
                            Compre e venda contas, créditos e assinaturas. O dinheiro fica retido até você confirmar o recebimento.
                        </p>
                    </div>
                    <div>
                        <div class="mb-4 text-[12px] font-semibold uppercase tracking-[0.12em] text-white">Comprar</div>
                        <ul class="space-y-2.5 text-[14px]">
                            <li><Link href="/buscar" class="hover:text-white">Todos os anúncios</Link></li>
                            <li><Link href="/categorias/steam" class="hover:text-white">Steam</Link></li>
                            <li><Link href="/categorias/free-fire" class="hover:text-white">Free Fire</Link></li>
                            <li><Link href="/categorias/assinaturas-e-premium" class="hover:text-white">Assinaturas</Link></li>
                        </ul>
                    </div>
                    <div>
                        <div class="mb-4 text-[12px] font-semibold uppercase tracking-[0.12em] text-white">Conta</div>
                        <ul class="space-y-2.5 text-[14px]">
                            <li><Link href="/criar-conta" class="hover:text-white">Criar conta</Link></li>
                            <li><Link href="/login" class="hover:text-white">Entrar</Link></li>
                            <li><Link href="/cadastro" class="hover:text-white">Quero vender</Link></li>
                        </ul>
                    </div>
                    <div>
                        <div class="mb-4 text-[12px] font-semibold uppercase tracking-[0.12em] text-white">Institucional</div>
                        <ul class="space-y-2.5 text-[14px]">
                            <li><Link href="/termos-de-uso" class="hover:text-white">Termos de uso</Link></li>
                            <li><Link href="/politica-privacidade" class="hover:text-white">Privacidade</Link></li>
                        </ul>
                    </div>
                </div>
                <div class="flex flex-col items-start justify-between gap-3 border-t border-white/10 py-6 text-[12px] text-[#7D6F63] sm:flex-row sm:items-center">
                    <span>© {{ new Date().getFullYear() }} {{ appName }}</span>
                    <span>Pagamentos via PIX e cartão · Suporte por chat</span>
                </div>
            </div>
            <div class="pointer-events-none select-none overflow-hidden">
                <div class="font-display -mb-[0.22em] text-center text-[clamp(80px,17vw,240px)] font-extrabold leading-none tracking-[-0.06em] text-white/[0.04]">
                    {{ appName }}
                </div>
            </div>
        </footer>
    </div>
</template>

<style>
.mk-root {
    --mk-accent: #FF5A1F;
    background: #FBF7F2;
    color: #1A1410;
    font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
    font-feature-settings: 'cv11', 'ss01';
}
.mk-root .font-display {
    font-family: 'Plus Jakarta Sans', 'Inter', ui-sans-serif, system-ui, sans-serif;
}
.mk-root input[type='search']::-webkit-search-cancel-button {
    display: none;
}
</style>
