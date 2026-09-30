<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import MarketplaceLayout from '@/Layouts/MarketplaceLayout.vue';
import ProductCard from '@/components/marketplace/ProductCard.vue';
import CategoryPoster from '@/components/marketplace/CategoryPoster.vue';
import MkIcon from '@/components/marketplace/MkIcon.vue';
import SellerLevelBadge from '@/components/marketplace/SellerLevelBadge.vue';

const props = defineProps({
    product: { type: Object, required: true },
    questions: { type: Array, default: () => [] },
    related: { type: Array, default: () => [] },
    canAsk: { type: Boolean, default: false },
});

const form = useForm({ body: '' });
function submitQuestion() {
    form.post(`/anuncio/${props.product.slug}/perguntas`, {
        preserveScroll: true,
        onSuccess: () => form.reset('body'),
    });
}

const formatPrice = (v) =>
    new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(v || 0));

const seller = computed(() => props.product.seller);
const isAuto = computed(() => ['automatic', 'both'].includes(props.product.delivery_mode));
const outOfStock = computed(() => props.product.stock_available !== null && props.product.stock_available <= 0);

const deliveryLabel = computed(() => {
    if (props.product.delivery_mode === 'both') return 'Automática ou via chat';
    return isAuto.value ? 'Automática' : 'Via chat com o vendedor';
});

const characteristics = computed(() => {
    const rows = [
        ['Tipo do anúncio', props.product.category?.name || 'Outros'],
        ['Entrega', deliveryLabel.value],
    ];
    if (props.product.warranty_text) rows.push(['Garantia', props.product.warranty_text]);
    if (props.product.region_text) rows.push(['Região', props.product.region_text]);
    const meta = props.product.marketplace_meta;
    if (meta && typeof meta === 'object' && !Array.isArray(meta)) {
        Object.entries(meta).forEach(([k, v]) => {
            if (v !== null && v !== '' && typeof v !== 'object') rows.push([k, String(v)]);
        });
    }
    return rows;
});

function relativeTime(iso) {
    if (!iso) return '—';
    const diff = Math.max(0, (Date.now() - new Date(iso).getTime()) / 1000);
    if (diff < 300) return 'agora';
    if (diff < 3600) return `há ${Math.floor(diff / 60)} minutos`;
    if (diff < 86400) {
        const h = Math.floor(diff / 3600);
        return `há ${h} ${h === 1 ? 'hora' : 'horas'}`;
    }
    const d = Math.floor(diff / 86400);
    return `há ${d} ${d === 1 ? 'dia' : 'dias'}`;
}

const pageUrl = computed(() => (typeof window !== 'undefined' ? window.location.href : ''));
const shareLinks = computed(() => {
    const u = encodeURIComponent(pageUrl.value);
    const t = encodeURIComponent(props.product.name);
    return [
        { label: 'WhatsApp', href: `https://wa.me/?text=${t}%20${u}` },
        { label: 'Telegram', href: `https://t.me/share/url?url=${u}&text=${t}` },
        { label: 'X', href: `https://twitter.com/intent/tweet?url=${u}&text=${t}` },
        { label: 'Facebook', href: `https://www.facebook.com/sharer/sharer.php?u=${u}` },
    ];
});
const copied = ref(false);
async function copyLink() {
    try {
        await navigator.clipboard.writeText(pageUrl.value);
        copied.value = true;
        setTimeout(() => (copied.value = false), 1800);
    } catch {
        window.prompt('Copie o link:', pageUrl.value);
    }
}

const lightbox = ref(false);

const track = ref(null);
function scrollRelated(dir) {
    const el = track.value;
    if (!el) return;
    el.scrollBy({ left: dir * el.clientWidth * 0.8, behavior: 'smooth' });
}
</script>

<template>
    <Head :title="product.name" />
    <MarketplaceLayout>
        <div class="mx-auto max-w-[1240px] px-5 pb-6 pt-6">
            <nav class="mb-6 flex flex-wrap items-center gap-1.5 text-[13px] text-[#8A7B6E]">
                <Link href="/" class="hover:text-[#1A1410]">Início</Link>
                <MkIcon name="chevron-right" :size="13" />
                <Link href="/buscar" class="hover:text-[#1A1410]">Anúncios</Link>
                <template v-if="product.category">
                    <MkIcon name="chevron-right" :size="13" />
                    <Link :href="`/categorias/${product.category.slug}`" class="hover:text-[#1A1410]">{{ product.category.name }}</Link>
                </template>
            </nav>

            <div class="grid items-start gap-6 lg:grid-cols-[1fr_320px] lg:gap-8">
                <!-- Coluna principal -->
                <div class="min-w-0 space-y-10">
                    <!-- Topo: imagem + título + compra -->
                    <section class="grid gap-6 sm:grid-cols-[240px_1fr]">
                        <button
                            type="button"
                            class="group relative aspect-square w-full overflow-hidden rounded-[14px] border border-[#EBE2D8] bg-white"
                            @click="product.image && (lightbox = true)"
                        >
                            <img v-if="product.image" :src="product.image" :alt="product.name" class="absolute inset-0 h-full w-full object-cover" />
                            <CategoryPoster v-else :category="product.category" />
                            <span
                                v-if="product.image"
                                class="absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-lg bg-black/50 text-white opacity-0 transition group-hover:opacity-100"
                            ><MkIcon name="expand" :size="15" /></span>
                        </button>

                        <div class="flex min-w-0 flex-col">
                            <h1 class="font-display text-[24px] font-bold uppercase leading-[1.2] tracking-[-0.01em] text-[#1A1410] sm:text-[26px]">
                                {{ product.name }}
                                <span
                                    v-if="seller?.level?.name"
                                    class="ml-1 inline-flex -translate-y-1 items-center rounded-[6px] bg-[#1A1410] px-2 py-0.5 align-middle text-[10px] font-semibold normal-case tracking-normal text-white"
                                >{{ seller.level.name }}</span>
                            </h1>

                            <div class="mt-4 flex flex-wrap items-center gap-2">
                                <span
                                    v-if="product.stock_available !== null"
                                    class="inline-flex items-center gap-1.5 rounded-[8px] border px-2.5 py-1 text-[13px]"
                                    :class="outOfStock ? 'border-red-200 bg-red-50 text-red-700' : 'border-[#E2D7CB] bg-white text-[#3D332B]'"
                                >
                                    <MkIcon name="box" :size="14" />
                                    <strong class="font-semibold">{{ product.stock_available }}</strong>
                                    {{ product.stock_available === 1 ? 'disponível' : 'disponíveis' }}
                                </span>
                                <span v-if="product.category" class="rounded-[8px] bg-[#F1EAE2] px-2.5 py-1 text-[13px] text-[#5C4F44]">
                                    {{ product.category.name }}
                                </span>
                            </div>

                            <div class="mt-auto flex flex-wrap items-center gap-3 pt-6">
                                <div class="font-display text-[30px] font-extrabold tracking-[-0.02em] text-[#1A1410]">
                                    {{ formatPrice(product.price) }}
                                </div>
                                <a
                                    v-if="!outOfStock && product.checkout_url"
                                    :href="product.checkout_url"
                                    class="inline-flex h-11 items-center rounded-[10px] bg-[color:var(--mk-accent)] px-8 text-[14px] font-bold uppercase tracking-[0.04em] text-white hover:brightness-95"
                                >Comprar</a>
                                <span v-else-if="outOfStock" class="inline-flex h-11 items-center rounded-[10px] bg-[#E2D7CB] px-8 text-[14px] font-bold uppercase text-[#8A7B6E]">Esgotado</span>
                                <span v-else class="inline-flex h-11 items-center rounded-[10px] bg-[#E2D7CB] px-8 text-[14px] font-bold uppercase text-[#8A7B6E]">Indisponível</span>
                                <span
                                    class="inline-flex h-9 items-center gap-1.5 rounded-[8px] border px-3 text-[12px] font-semibold"
                                    :class="isAuto ? 'border-[color:var(--mk-accent)] text-[color:var(--mk-accent)]' : 'border-[#E2D7CB] text-[#5C4F44]'"
                                >
                                    <MkIcon :name="isAuto ? 'bolt' : 'chat'" :size="14" :stroke="2" />
                                    {{ isAuto ? 'Entrega automática' : 'Entrega via chat' }}
                                </span>
                            </div>
                        </div>
                    </section>

                    <!-- Características -->
                    <section>
                        <h2 class="mb-3 text-[15px] font-bold uppercase tracking-[0.04em]">Características</h2>
                        <div class="overflow-hidden rounded-[12px] border border-[#EBE2D8] bg-white">
                            <div
                                v-for="([label, value], i) in characteristics"
                                :key="label"
                                class="grid grid-cols-[minmax(140px,40%)_1fr] text-[14px]"
                                :class="i > 0 ? 'border-t border-[#F1EAE2]' : ''"
                            >
                                <div class="bg-[#F7F2EC] px-4 py-2.5 font-medium text-[#3D332B]">{{ label }}</div>
                                <div class="px-4 py-2.5 text-[#5C4F44]">{{ value }}</div>
                            </div>
                        </div>
                    </section>

                    <!-- Descrição -->
                    <section>
                        <h2 class="mb-3 text-[15px] font-bold uppercase tracking-[0.04em]">Descrição do anúncio</h2>
                        <div class="overflow-hidden rounded-[12px] border border-[#EBE2D8] bg-white">
                            <div class="whitespace-pre-wrap break-words px-5 py-5 text-[14px] leading-[1.7] text-[#3D332B]">{{ product.description_html || product.description || 'O vendedor não adicionou uma descrição.' }}</div>
                            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-[#F1EAE2] bg-[#FBF7F2] px-5 py-3 text-[12px] text-[#8A7B6E]">
                                <span class="uppercase tracking-[0.04em]">Criado em {{ product.created_at }}</span>
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="mr-1 uppercase tracking-[0.04em]">Compartilhar</span>
                                    <a
                                        v-for="s in shareLinks"
                                        :key="s.label"
                                        :href="s.href"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="rounded-[6px] border border-[#E2D7CB] bg-white px-2 py-1 font-medium text-[#3D332B] hover:border-[#1A1410]"
                                    >{{ s.label }}</a>
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1 rounded-[6px] border border-[#E2D7CB] bg-white px-2 py-1 font-medium text-[#3D332B] hover:border-[#1A1410]"
                                        @click="copyLink"
                                    >
                                        <MkIcon :name="copied ? 'check' : 'link'" :size="12" :stroke="2" />
                                        {{ copied ? 'Copiado' : 'Copiar' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Perguntas -->
                    <section>
                        <h2 class="mb-3 text-[15px] font-bold uppercase tracking-[0.04em]">
                            Perguntas <span v-if="questions.length" class="text-[#8A7B6E]">({{ questions.length }})</span>
                        </h2>
                        <div class="rounded-[12px] border border-[#EBE2D8] bg-white">
                            <div v-if="questions.length" class="divide-y divide-[#F1EAE2]">
                                <div v-for="q in questions" :key="q.id" class="px-5 py-4">
                                    <div class="flex items-baseline gap-2 text-[13px]">
                                        <span class="font-semibold text-[#1A1410]">{{ q.user?.name }}</span>
                                        <span class="text-[#A3958A]">{{ q.created_at }}</span>
                                    </div>
                                    <p class="mt-1 text-[14px] text-[#3D332B]">{{ q.body }}</p>
                                    <div v-if="q.answer" class="mt-3 border-l-2 border-[color:var(--mk-accent)] pl-3">
                                        <div class="flex items-center gap-2 text-[13px]">
                                            <span class="font-semibold">{{ q.answer.user_name }}</span>
                                            <span class="rounded-[4px] bg-[color:var(--mk-accent)] px-1.5 py-px text-[10px] font-bold uppercase text-white">Vendedor</span>
                                            <span class="text-[#A3958A]">{{ q.answer.created_at }}</span>
                                        </div>
                                        <p class="mt-1 text-[14px] text-[#3D332B]">{{ q.answer.body }}</p>
                                    </div>
                                </div>
                            </div>
                            <p v-else class="px-5 py-4 text-[14px] text-[#8A7B6E]">Nenhuma pergunta até o momento.</p>

                            <div class="border-t border-[#F1EAE2] px-5 py-4">
                                <template v-if="canAsk">
                                    <textarea
                                        v-model="form.body"
                                        rows="3"
                                        class="w-full resize-none rounded-[10px] border border-[#E2D7CB] px-3.5 py-2.5 text-[14px] outline-none focus:border-[#1A1410] focus:ring-0"
                                        placeholder="Escreva sua pergunta para o vendedor"
                                    />
                                    <div class="mt-2 flex flex-wrap items-start justify-between gap-3">
                                        <p class="max-w-[520px] text-[12px] text-[#8A7B6E]">
                                            Não é permitido compartilhar contatos externos (WhatsApp, Discord, Instagram, e-mail etc.).
                                        </p>
                                        <button
                                            type="button"
                                            class="rounded-[10px] bg-[#1A1410] px-5 py-2 text-[13px] font-semibold text-white hover:bg-black disabled:opacity-50"
                                            :disabled="form.processing || !form.body.trim()"
                                            @click="submitQuestion"
                                        >Perguntar</button>
                                    </div>
                                    <p v-if="form.errors.body" class="mt-2 text-[12px] text-red-600">{{ form.errors.body }}</p>
                                </template>
                                <p v-else class="text-[14px]">
                                    <Link href="/login" class="font-semibold text-[color:var(--mk-accent)] hover:underline">Entre na sua conta</Link>
                                    <span class="text-[#8A7B6E]"> para fazer uma pergunta.</span>
                                </p>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Sidebar -->
                <aside class="space-y-4 lg:sticky lg:top-[92px]">
                    <div v-if="seller" class="rounded-[14px] border border-[#EBE2D8] bg-white p-5">
                        <div class="mb-4 text-center text-[15px] font-bold">Vendedor</div>
                        <div class="flex flex-col items-center text-center">
                            <div class="relative">
                                <img v-if="seller.avatar" :src="seller.avatar" :alt="seller.name" class="h-16 w-16 rounded-full object-cover" />
                                <div v-else class="flex h-16 w-16 items-center justify-center rounded-full bg-[#1A1410] font-display text-[22px] font-bold uppercase text-white">
                                    {{ (seller.name || '?').slice(0, 1) }}
                                </div>
                                <span
                                    class="absolute bottom-0.5 right-0.5 h-3.5 w-3.5 rounded-full border-[2.5px] border-white"
                                    :class="seller.is_online ? 'bg-emerald-500' : 'bg-[#C9BDB1]'"
                                />
                            </div>
                            <div class="mt-3 flex flex-wrap items-center justify-center gap-1.5">
                                <Link
                                    :href="seller.profile_url || `/perfil/${seller.username}`"
                                    class="text-[15px] font-semibold text-[color:var(--mk-accent)] hover:underline"
                                >{{ seller.username }}</Link>
                                <SellerLevelBadge :seller="seller" size="sm" />
                                <span
                                    class="rounded-full px-1.5 py-px text-[10px] font-bold"
                                    :class="seller.is_online ? 'bg-emerald-100 text-emerald-700' : 'bg-[#EBE2D8] text-[#8A7B6E]'"
                                >{{ seller.is_online ? 'ON' : 'OFF' }}</span>
                            </div>
                            <p v-if="seller.level?.name" class="mt-1 text-[11px] font-medium text-[#8A7B6E]">
                                {{ seller.level.name }}
                            </p>
                        </div>
                        <dl class="mt-5 space-y-2 text-[13px]">
                            <div class="flex justify-between gap-3"><dt class="text-[#8A7B6E]">Membro desde</dt><dd class="font-semibold">{{ seller.member_since || '—' }}</dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-[#8A7B6E]">Avaliações positivas</dt><dd class="font-semibold">{{ Math.round(seller.positive_rating_percent) }}%</dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-[#8A7B6E]">Número de avaliações</dt><dd class="font-semibold">{{ seller.ratings_count }}</dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-[#8A7B6E]">Último acesso</dt><dd class="font-semibold">{{ seller.is_online ? 'online agora' : relativeTime(seller.last_seen_at) }}</dd></div>
                        </dl>
                    </div>

                    <div v-if="seller" class="rounded-[14px] border border-[#EBE2D8] bg-white p-5">
                        <div class="mb-3 text-center text-[15px] font-bold">Verificações</div>
                        <ul class="space-y-2 text-[13px]">
                            <li v-for="v in [
                                { icon: 'mail', label: 'E-mail', ok: seller.kyc_email },
                                { icon: 'phone', label: 'Telefone', ok: seller.kyc_phone },
                                { icon: 'document', label: 'Documentos', ok: seller.kyc_documents },
                            ]" :key="v.label" class="flex items-center justify-between gap-3">
                                <span class="inline-flex items-center gap-2 text-[#5C4F44]"><MkIcon :name="v.icon" :size="15" /> {{ v.label }}</span>
                                <span class="font-semibold" :class="v.ok ? 'text-emerald-600' : 'text-[#A3958A]'">{{ v.ok ? 'Verificado' : 'Pendente' }}</span>
                            </li>
                        </ul>
                    </div>

                    <div class="rounded-[14px] border border-[#EBE2D8] bg-white p-5 text-center">
                        <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-[#E9F7EF] text-emerald-600">
                            <MkIcon name="shield" :size="20" :stroke="2" />
                        </div>
                        <div class="mt-2.5 text-[15px] font-bold">Entrega garantida</div>
                        <p class="mt-1 text-[13px] text-[#8A7B6E]">Ou o seu dinheiro de volta.</p>
                    </div>
                </aside>
            </div>
        </div>

        <!-- Anúncios parecidos -->
        <section v-if="related.length" class="mx-auto max-w-[1240px] px-5 pb-20 pt-10">
            <div class="mb-5 flex items-end justify-between gap-4">
                <h2 class="font-display text-[26px] font-bold tracking-[-0.03em]">Anúncios parecidos</h2>
                <div class="flex gap-2">
                    <button type="button" class="flex h-9 w-9 items-center justify-center rounded-full border border-[#E2D7CB] bg-white hover:border-[#1A1410]" aria-label="Anterior" @click="scrollRelated(-1)">
                        <MkIcon name="chevron-left" :size="16" :stroke="2" />
                    </button>
                    <button type="button" class="flex h-9 w-9 items-center justify-center rounded-full border border-[#E2D7CB] bg-white hover:border-[#1A1410]" aria-label="Próximo" @click="scrollRelated(1)">
                        <MkIcon name="chevron-right" :size="16" :stroke="2" />
                    </button>
                </div>
            </div>
            <div ref="track" class="related-track -mx-1 flex snap-x snap-mandatory gap-4 overflow-x-auto px-1 pb-2">
                <div v-for="p in related" :key="p.id" class="w-[calc(50%-8px)] shrink-0 snap-start sm:w-[calc(33.333%-11px)] lg:w-[calc(25%-12px)]">
                    <ProductCard :product="p" />
                </div>
            </div>
        </section>

        <!-- Lightbox -->
        <div
            v-if="lightbox && product.image"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 p-6"
            @click.self="lightbox = false"
        >
            <button type="button" class="absolute right-5 top-5 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20" aria-label="Fechar" @click="lightbox = false">
                <MkIcon name="x" :size="20" />
            </button>
            <img :src="product.image" :alt="product.name" class="max-h-[85vh] max-w-full rounded-[12px] object-contain" />
        </div>
    </MarketplaceLayout>
</template>

<style scoped>
.related-track {
    scrollbar-width: none;
}
.related-track::-webkit-scrollbar {
    display: none;
}
</style>
