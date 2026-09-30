<script setup>
import { Link } from '@inertiajs/vue3';
import SellerLevelBadge from '@/components/marketplace/SellerLevelBadge.vue';

defineProps({
    seller: { type: Object, required: true },
    compact: { type: Boolean, default: false },
});
</script>

<template>
    <div class="rounded-[16px] border border-[#EBE2D8] bg-white p-4 shadow-sm" :class="compact ? '' : 'space-y-3'">
        <div class="flex items-center gap-3">
            <Link :href="seller.profile_url || `/perfil/${seller.username}`" class="relative shrink-0">
                <img
                    v-if="seller.avatar"
                    :src="seller.avatar"
                    class="h-12 w-12 rounded-full object-cover"
                    alt=""
                />
                <div v-else class="flex h-12 w-12 items-center justify-center rounded-full bg-[#1A1410] text-sm font-bold text-white">
                    {{ (seller.name || '?').slice(0, 1).toUpperCase() }}
                </div>
                <span
                    class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white"
                    :class="seller.is_online ? 'bg-emerald-500' : 'bg-zinc-300'"
                />
            </Link>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-1.5">
                    <Link
                        :href="seller.profile_url || `/perfil/${seller.username}`"
                        class="truncate font-semibold text-[color:var(--mk-accent)] hover:underline"
                    >
                        {{ seller.name }}
                    </Link>
                    <SellerLevelBadge :seller="seller" size="sm" />
                </div>
                <div class="mt-0.5 flex flex-wrap items-center gap-2 text-xs text-[#8A7B6E]">
                    <span
                        class="inline-flex items-center rounded-full px-2 py-0.5 font-medium"
                        :class="seller.is_online ? 'bg-emerald-50 text-emerald-700' : 'bg-[#F1EAE2] text-[#8A7B6E]'"
                    >{{ seller.is_online ? 'ON' : 'OFF' }}</span>
                    <span v-if="seller.level">{{ seller.level.name }}</span>
                </div>
            </div>
        </div>

        <ul v-if="!compact" class="space-y-1.5 border-t border-[#EBE2D8] pt-3 text-sm text-[#6B5E54]">
            <li class="flex justify-between gap-2"><span>Membro desde</span><span class="font-medium text-[#1A1410]">{{ seller.member_since || '—' }}</span></li>
            <li class="flex justify-between gap-2"><span>Avaliações positivas</span><span class="font-medium text-[#1A1410]">{{ seller.positive_rating_percent }}%</span></li>
            <li class="flex justify-between gap-2"><span>Número de avaliações</span><span class="font-medium text-[#1A1410]">{{ seller.ratings_count }}</span></li>
        </ul>

        <div v-if="!compact" class="rounded-[12px] border border-[#EBE2D8] bg-[#FBF7F2] p-3 text-xs text-[#6B5E54]">
            <div class="mb-1 font-semibold text-[#1A1410]">Verificações</div>
            <div class="space-y-1">
                <div :class="seller.kyc_email ? 'text-emerald-700' : 'text-[#A3958A]'">{{ seller.kyc_email ? '✓' : '○' }} E-mail</div>
                <div :class="seller.kyc_phone ? 'text-emerald-700' : 'text-[#A3958A]'">{{ seller.kyc_phone ? '✓' : '○' }} Telefone</div>
                <div :class="seller.kyc_documents ? 'text-emerald-700' : 'text-[#A3958A]'">{{ seller.kyc_documents ? '✓' : '○' }} Documentos</div>
            </div>
        </div>
    </div>
</template>
