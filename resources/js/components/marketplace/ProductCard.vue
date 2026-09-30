<script setup>
import { Link } from '@inertiajs/vue3';
import CategoryPoster from './CategoryPoster.vue';
import MkIcon from './MkIcon.vue';

defineProps({
    product: { type: Object, required: true },
    badge: { type: String, default: '' },
    dark: { type: Boolean, default: false },
});

function formatPrice(value) {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(value || 0));
}

function isAuto(product) {
    return product.delivery_mode === 'automatic' || product.delivery_mode === 'both';
}
</script>

<template>
    <Link
        :href="product.url"
        class="group flex flex-col overflow-hidden rounded-[14px] border transition duration-200"
        :class="dark
            ? 'border-white/10 bg-[#241C17] hover:border-white/25'
            : 'border-[#EBE2D8] bg-white hover:border-[#1A1410] hover:shadow-[4px_4px_0_#1A1410]'"
    >
        <div class="relative aspect-[16/11] overflow-hidden">
            <img
                v-if="product.image"
                :src="product.image"
                :alt="product.name"
                class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-[1.04]"
            />
            <CategoryPoster v-else :category="product.category" compact />
            <span
                v-if="product.category && !product.image"
                class="absolute bottom-2.5 left-3 text-[11px] font-bold uppercase tracking-[0.08em] text-white/90"
            >{{ product.category.name }}</span>
            <span
                v-if="badge"
                class="absolute left-2.5 top-2.5 rounded-[6px] bg-[color:var(--mk-accent)] px-2 py-[3px] text-[10px] font-bold uppercase tracking-[0.06em] text-white"
            >{{ badge }}</span>
        </div>

        <div class="flex flex-1 flex-col p-3.5">
            <div class="mb-1.5 flex items-center gap-2 text-[11px] font-medium" :class="dark ? 'text-white/50' : 'text-[#8A7B6E]'">
                <span v-if="isAuto(product)" class="inline-flex items-center gap-1 text-[color:var(--mk-accent)]">
                    <MkIcon name="bolt" :size="12" :stroke="2" /> Entrega automática
                </span>
                <span v-else class="inline-flex items-center gap-1">
                    <MkIcon name="chat" :size="12" :stroke="2" /> Entrega via chat
                </span>
            </div>
            <h3
                class="line-clamp-2 min-h-[2.6rem] text-[14px] font-semibold leading-[1.3]"
                :class="dark ? 'text-white' : 'text-[#1A1410]'"
            >{{ product.name }}</h3>

            <div class="mt-3 flex items-end justify-between gap-2">
                <div class="font-display text-[19px] font-bold tracking-tight" :class="dark ? 'text-white' : 'text-[#1A1410]'">
                    {{ formatPrice(product.price) }}
                </div>
                <span
                    class="flex h-8 w-8 items-center justify-center rounded-full transition"
                    :class="dark
                        ? 'bg-white/10 text-white group-hover:bg-[color:var(--mk-accent)]'
                        : 'bg-[#F4EDE5] text-[#1A1410] group-hover:bg-[color:var(--mk-accent)] group-hover:text-white'"
                >
                    <MkIcon name="arrow-up-right" :size="15" :stroke="2" />
                </span>
            </div>

            <div
                v-if="product.seller"
                class="mt-3 flex items-center gap-2 border-t pt-2.5 text-[12px]"
                :class="dark ? 'border-white/10 text-white/60' : 'border-[#F1EAE2] text-[#6B5E52]'"
            >
                <span class="relative flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#1A1410] text-[9px] font-bold uppercase text-white">
                    {{ (product.seller.username || '?').slice(0, 1) }}
                    <span
                        class="absolute -bottom-0.5 -right-0.5 h-2 w-2 rounded-full ring-2"
                        :class="[product.seller.is_online ? 'bg-emerald-500' : 'bg-[#C9BDB1]', dark ? 'ring-[#241C17]' : 'ring-white']"
                    />
                </span>
                <span class="truncate font-medium">{{ product.seller.username }}</span>
                <img
                    v-if="product.seller.level?.image"
                    :src="product.seller.level.image"
                    :title="product.seller.level.name"
                    class="h-4 w-4 rounded-full object-cover"
                    alt=""
                />
                <span class="ml-auto shrink-0 tabular-nums" :class="dark ? 'text-white/40' : 'text-[#A3958A]'">
                    {{ product.seller.sales_count }} vendas
                </span>
            </div>
        </div>
    </Link>
</template>
