<script setup>
import { Head } from '@inertiajs/vue3';
import MarketplaceLayout from '@/Layouts/MarketplaceLayout.vue';
import ProductCard from '@/components/marketplace/ProductCard.vue';
import SellerLevelBadge from '@/components/marketplace/SellerLevelBadge.vue';

defineProps({
    seller: { type: Object, required: true },
    products: { type: Object, required: true },
});

function relativeTime(iso) {
    if (!iso) return '—';
    try {
        const d = new Date(iso);
        const diff = Math.max(0, Date.now() - d.getTime());
        const m = Math.floor(diff / 60000);
        if (m < 1) return 'agora';
        if (m < 60) return `há ${m} min`;
        const h = Math.floor(m / 60);
        if (h < 24) return `há ${h} h`;
        const days = Math.floor(h / 24);
        return `há ${days} d`;
    } catch {
        return '—';
    }
}
</script>

<template>
    <Head :title="`Reputação · ${seller.username}`" />
    <MarketplaceLayout>
        <!-- Hero reputação -->
        <div class="border-b border-[#EBE2D8] bg-[#1A1410] text-white">
            <div class="mx-auto flex max-w-[1240px] flex-col gap-6 px-5 py-8 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex min-w-0 items-center gap-4">
                    <div class="relative shrink-0">
                        <img
                            v-if="seller.avatar"
                            :src="seller.avatar"
                            :alt="seller.name"
                            class="h-16 w-16 rounded-full object-cover ring-2 ring-white/20"
                        />
                        <div
                            v-else
                            class="flex h-16 w-16 items-center justify-center rounded-full bg-[color:var(--mk-accent)] font-display text-[22px] font-bold uppercase"
                        >
                            {{ (seller.name || '?').slice(0, 1) }}
                        </div>
                        <span
                            class="absolute bottom-0.5 right-0.5 h-3.5 w-3.5 rounded-full border-2 border-[#1A1410]"
                            :class="seller.is_online ? 'bg-emerald-500' : 'bg-[#8A7B6E]'"
                        />
                    </div>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="font-display truncate text-[26px] font-extrabold tracking-[-0.03em]">
                                {{ seller.username }}
                            </h1>
                            <SellerLevelBadge :seller="seller" size="md" :show-count="false" />
                            <span
                                class="rounded-full px-2 py-0.5 text-[10px] font-bold"
                                :class="seller.is_online ? 'bg-emerald-500/20 text-emerald-300' : 'bg-white/10 text-white/50'"
                            >{{ seller.is_online ? 'ON' : 'OFF' }}</span>
                        </div>
                        <p class="mt-1 text-sm text-white/55">
                            <span v-if="seller.level">{{ seller.level.name }} · </span>
                            {{ seller.sales_count }} vendas · membro desde {{ seller.member_since || '—' }}
                        </p>
                    </div>
                </div>

                <div class="min-w-0 flex-1 lg:max-w-md">
                    <div class="mb-2 text-[12px] font-semibold uppercase tracking-[0.12em] text-white/45">Reputação do usuário</div>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="rounded-[12px] bg-emerald-500/15 px-3 py-3 text-center">
                            <div class="text-[18px] font-bold text-emerald-300">{{ seller.reputation?.positive ?? 0 }}</div>
                            <div class="text-[11px] text-emerald-200/80">Positivas</div>
                        </div>
                        <div class="rounded-[12px] bg-white/10 px-3 py-3 text-center">
                            <div class="text-[18px] font-bold text-white/80">{{ seller.reputation?.neutral ?? 0 }}</div>
                            <div class="text-[11px] text-white/45">Neutras</div>
                        </div>
                        <div class="rounded-[12px] bg-red-500/15 px-3 py-3 text-center">
                            <div class="text-[18px] font-bold text-red-300">{{ seller.reputation?.negative ?? 0 }}</div>
                            <div class="text-[11px] text-red-200/80">Negativas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-auto grid max-w-[1240px] gap-8 px-5 py-8 lg:grid-cols-[280px_1fr]">
            <aside class="space-y-4">
                <div class="rounded-[16px] border border-[#EBE2D8] bg-white p-4">
                    <h2 class="text-[14px] font-bold text-[#1A1410]">Detalhes</h2>
                    <dl class="mt-3 space-y-2 text-[13px]">
                        <div class="flex justify-between gap-2"><dt class="text-[#8A7B6E]">Membro desde</dt><dd class="font-semibold">{{ seller.member_since || '—' }}</dd></div>
                        <div class="flex justify-between gap-2"><dt class="text-[#8A7B6E]">Avaliações positivas</dt><dd class="font-semibold">{{ Math.round(seller.positive_rating_percent) }}%</dd></div>
                        <div class="flex justify-between gap-2"><dt class="text-[#8A7B6E]">Nº de avaliações</dt><dd class="font-semibold">{{ seller.ratings_count }}</dd></div>
                        <div class="flex justify-between gap-2"><dt class="text-[#8A7B6E]">Último acesso</dt><dd class="font-semibold">{{ seller.is_online ? 'online agora' : relativeTime(seller.last_seen_at) }}</dd></div>
                    </dl>
                </div>

                <div class="rounded-[16px] border border-[#EBE2D8] bg-white p-4">
                    <h2 class="text-[14px] font-bold text-[#1A1410]">Verificações</h2>
                    <ul class="mt-3 space-y-2 text-[13px]">
                        <li class="flex justify-between gap-2">
                            <span class="text-[#5C4F44]">E-mail</span>
                            <span :class="seller.kyc_email ? 'font-semibold text-emerald-600' : 'text-[#A3958A]'">{{ seller.kyc_email ? 'Verificado' : 'Pendente' }}</span>
                        </li>
                        <li class="flex justify-between gap-2">
                            <span class="text-[#5C4F44]">Telefone</span>
                            <span :class="seller.kyc_phone ? 'font-semibold text-emerald-600' : 'text-[#A3958A]'">{{ seller.kyc_phone ? 'Verificado' : 'Pendente' }}</span>
                        </li>
                        <li class="flex justify-between gap-2">
                            <span class="text-[#5C4F44]">Documentos</span>
                            <span :class="seller.kyc_documents ? 'font-semibold text-emerald-600' : 'text-[#A3958A]'">{{ seller.kyc_documents ? 'Verificado' : 'Pendente' }}</span>
                        </li>
                    </ul>
                </div>

                <!-- Conquistas / níveis (admin: Plataforma → Conquistas, métrica vendas) -->
                <div class="rounded-[16px] border border-[#EBE2D8] bg-white p-4">
                    <h2 class="text-[14px] font-bold text-[#1A1410]">Conquistas</h2>
                    <p class="mt-1 text-[12px] text-[#8A7B6E]">Emblemas definidos pelo admin (níveis por vendas).</p>
                    <div v-if="seller.unlocked_levels?.length" class="mt-3 grid grid-cols-4 gap-2">
                        <div
                            v-for="lv in seller.unlocked_levels"
                            :key="lv.id || lv.slug"
                            class="flex flex-col items-center gap-1 rounded-[10px] border border-[#EBE2D8] bg-[#FBF7F2] p-2 text-center"
                            :title="lv.name"
                        >
                            <img
                                v-if="lv.image"
                                :src="lv.image"
                                :alt="lv.name"
                                class="h-8 w-8 rounded-full object-cover"
                            />
                            <span
                                v-else
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-[#1A1410] text-[11px] font-bold text-amber-300"
                            >★</span>
                            <span class="line-clamp-2 text-[9px] font-medium leading-tight text-[#3D332B]">{{ lv.name }}</span>
                        </div>
                    </div>
                    <p v-else class="mt-3 text-[13px] text-[#8A7B6E]">Nenhuma conquista desbloqueada ainda.</p>

                    <div v-if="seller.next_level" class="mt-3 rounded-[10px] border border-dashed border-[#E2D7CB] px-3 py-2 text-[12px] text-[#6B5E54]">
                        Próximo: <strong class="text-[#1A1410]">{{ seller.next_level.name }}</strong>
                        <span v-if="seller.next_level.threshold"> · {{ seller.next_level.threshold }} vendas</span>
                        <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-[#EBE2D8]">
                            <div
                                class="h-full rounded-full bg-[color:var(--mk-accent)]"
                                :style="{ width: `${Math.min(100, seller.progress_percent || 0)}%` }"
                            />
                        </div>
                    </div>
                </div>
            </aside>

            <div class="min-w-0 space-y-8">
                <section>
                    <h2 class="font-display text-[22px] font-bold tracking-[-0.03em] text-[#1A1410]">
                        Anúncios ativos de {{ seller.username }}
                    </h2>
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <ProductCard v-for="p in products.data" :key="p.id" :product="p" />
                    </div>
                    <p v-if="!products.data?.length" class="mt-6 rounded-[16px] border border-dashed border-[#E2D7CB] px-4 py-10 text-center text-sm text-[#8A7B6E]">
                        Este vendedor ainda não tem anúncios ativos.
                    </p>
                </section>
            </div>
        </div>
    </MarketplaceLayout>
</template>
