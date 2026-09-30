<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import MarketplaceLayout from '@/Layouts/MarketplaceLayout.vue';
import MkIcon from '@/components/marketplace/MkIcon.vue';

defineProps({
    title: { type: String, default: '' },
});

const page = usePage();
const path = computed(() => {
    try {
        return new URL(page.url, 'http://x').pathname;
    } catch {
        return page.url || '';
    }
});

const items = computed(() => [
    { href: '/painel-cliente', label: 'Minhas compras', icon: 'box', match: (p) => p === '/painel-cliente' },
    { href: '/chat', label: 'Chats', icon: 'chat', match: (p) => p.startsWith('/chat') },
    { href: '/painel-cliente/conta', label: 'Minha conta', icon: 'user', match: (p) => p.startsWith('/painel-cliente/conta') },
]);

function active(item) {
    return item.match(path.value);
}
</script>

<template>
    <MarketplaceLayout>
        <div class="border-b border-[#EBE2D8] bg-white/70">
            <div class="mx-auto flex max-w-[1240px] flex-col gap-4 px-5 py-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="text-[12px] font-semibold uppercase tracking-[0.14em] text-[color:var(--mk-accent)]">Área do cliente</div>
                    <h1 v-if="title" class="mt-1 font-display text-[28px] font-bold tracking-[-0.03em] text-[#1A1410] sm:text-[32px]">{{ title }}</h1>
                </div>
                <nav class="flex flex-wrap gap-1.5">
                    <Link
                        v-for="item in items"
                        :key="item.href"
                        :href="item.href"
                        class="inline-flex items-center gap-2 rounded-[10px] px-3.5 py-2 text-[13px] font-semibold transition"
                        :class="active(item)
                            ? 'bg-[#1A1410] text-white'
                            : 'border border-[#E2D7CB] bg-white text-[#3D332B] hover:border-[#1A1410]'"
                    >
                        <MkIcon :name="item.icon" :size="15" />
                        {{ item.label }}
                    </Link>
                </nav>
            </div>
        </div>
        <div class="mx-auto max-w-[1240px] px-5 py-8">
            <slot />
        </div>
    </MarketplaceLayout>
</template>
