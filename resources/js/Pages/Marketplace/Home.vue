<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue';
import MarketplaceLayout from '@/Layouts/MarketplaceLayout.vue';
import ProductCard from '@/components/marketplace/ProductCard.vue';
import CategoryPoster from '@/components/marketplace/CategoryPoster.vue';
import MkIcon from '@/components/marketplace/MkIcon.vue';
import { categoryOrder, categoryTheme } from '@/components/marketplace/categoryTheme';

const props = defineProps({
    categories: { type: Array, default: () => [] },
    featured: { type: Array, default: () => [] },
    popular: { type: Array, default: () => [] },
    sellers: { type: Array, default: () => [] },
    recentQuestions: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({ listings: 0, sellers: 0, categories: 0 }) },
    content: { type: Object, required: true },
    preview: { type: Boolean, default: false },
    appName: { type: String, default: 'Gamkon' },
});

const liveContent = ref(null);
const c = computed(() => liveContent.value || props.content);
const sections = computed(() => (c.value.sections || []).filter((s) => s.enabled));

const sortedCategories = computed(() =>
    [...props.categories]
        .sort((a, b) => {
            const ia = categoryOrder.indexOf(a.slug);
            const ib = categoryOrder.indexOf(b.slug);
            return (ia === -1 ? 99 : ia) - (ib === -1 ? 99 : ib);
        })
        .map((cat) => ({ ...cat, theme: categoryTheme(cat) }))
);

const heroPicks = computed(() => (props.featured || []).slice(0, 3));
const quickTags = computed(() => sortedCategories.value.slice(0, 5));
const dealProducts = computed(() => {
    const src = c.value.deals.source === 'featured' ? props.featured : props.popular;
    return (src || []).slice(0, c.value.deals.limit);
});

const gridCols = { 3: 'lg:grid-cols-3', 4: 'lg:grid-cols-4', 5: 'lg:grid-cols-5', 6: 'lg:grid-cols-6' };

const remaining = ref({ h: 0, m: 0, s: 0 });
function tick() {
    const now = new Date();
    const end = new Date(now);
    end.setHours(24, 0, 0, 0);
    const diff = Math.max(0, Math.floor((end - now) / 1000));
    remaining.value = { h: Math.floor(diff / 3600), m: Math.floor((diff % 3600) / 60), s: diff % 60 };
}

const pad = (n) => String(n).padStart(2, '0');
const formatPrice = (v) =>
    new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(v || 0));
const formatCount = (n) => new Intl.NumberFormat('pt-BR').format(Number(n || 0));

/* ---------- Modo prévia (iframe do editor) ---------- */
const hoverId = ref(null);
const selectedId = ref(null);

function sectionFrom(el) {
    return el?.closest?.('[data-mk-section]')?.getAttribute('data-mk-section') || null;
}
function onPreviewClick(e) {
    const a = e.target.closest?.('a, button[type="submit"], form');
    if (a || e.target.closest?.('form')) e.preventDefault();
    const id = sectionFrom(e.target);
    if (id) {
        selectedId.value = id;
        window.parent.postMessage({ type: 'mk-home-select', id }, window.location.origin);
    }
}
function onPreviewSubmit(e) {
    e.preventDefault();
}
function onPreviewOver(e) {
    hoverId.value = sectionFrom(e.target);
}
function onMessage(e) {
    if (e.origin !== window.location.origin || !e.data) return;
    if (e.data.type === 'mk-home-content') liveContent.value = e.data.content;
    if (e.data.type === 'mk-home-focus') {
        selectedId.value = e.data.id;
        nextTick(() => {
            document.querySelector(`[data-mk-section="${e.data.id}"]`)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }
}

let timer = null;
onMounted(() => {
    tick();
    timer = setInterval(tick, 1000);
    if (props.preview && window.parent !== window) {
        window.addEventListener('message', onMessage);
        document.addEventListener('click', onPreviewClick, true);
        document.addEventListener('submit', onPreviewSubmit, true);
        document.addEventListener('mouseover', onPreviewOver);
        window.parent.postMessage({ type: 'mk-home-ready' }, window.location.origin);
    }
});
onUnmounted(() => {
    clearInterval(timer);
    window.removeEventListener('message', onMessage);
    document.removeEventListener('click', onPreviewClick, true);
    document.removeEventListener('submit', onPreviewSubmit, true);
    document.removeEventListener('mouseover', onPreviewOver);
});

const isPreviewFrame = computed(() => props.preview);
function outlineClass(id) {
    if (!isPreviewFrame.value) return '';
    if (selectedId.value === id) return 'mk-outline mk-outline-active';
    if (hoverId.value === id) return 'mk-outline';
    return '';
}

const sectionLabels = {
    hero: 'Destaque principal', trust: 'Garantias', categories: 'Categorias', deals: 'Ofertas do dia',
    popular: 'Mais vendidos', steps: 'Como funciona', recent: 'Recém-chegados', sellers: 'Vendedores',
    questions: 'Perguntas', cta: 'Chamada para vendedores', banner: 'Banner',
};
</script>

<template>
    <Head :title="appName" />
    <MarketplaceLayout :theme="c.theme">
        <template v-for="s in sections" :key="s.id">
            <div :data-mk-section="s.id" class="relative" :class="outlineClass(s.id)">
                <span v-if="isPreviewFrame && (hoverId === s.id || selectedId === s.id)" class="mk-outline-tag">
                    {{ sectionLabels[s.type] }}
                </span>

                <!-- HERO -->
                <section v-if="s.type === 'hero'" class="border-b border-[#EBE2D8]">
                    <div
                        class="mx-auto grid max-w-[1240px] gap-12 px-5 pb-16 pt-14 lg:gap-16 lg:pb-20 lg:pt-20"
                        :class="c.hero.visual === 'none' ? '' : 'lg:grid-cols-[1.05fr_1fr]'"
                    >
                        <div class="flex flex-col justify-center" :class="c.hero.visual === 'none' ? 'mx-auto max-w-[760px] items-center text-center' : ''">
                            <div
                                v-if="c.hero.badge_mode !== 'off'"
                                class="mb-6 inline-flex w-fit items-center gap-2 rounded-full border border-[#E2D7CB] bg-white px-3 py-1.5 text-[12px] font-medium text-[#5C4F44]"
                            >
                                <span class="relative flex h-2 w-2">
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-60" />
                                    <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500" />
                                </span>
                                <template v-if="c.hero.badge_mode === 'live'">{{ formatCount(stats.listings) }} anúncios ativos agora</template>
                                <template v-else>{{ c.hero.badge_text }}</template>
                            </div>

                            <h1 class="font-display text-[40px] font-extrabold leading-[1.05] tracking-[-0.035em] text-[#1A1410] sm:text-[52px] lg:text-[60px]">
                                {{ c.hero.title }}
                                <span v-if="c.hero.highlight" class="text-[color:var(--mk-accent)]">{{ c.hero.highlight }}</span>
                            </h1>

                            <p v-if="c.hero.subtitle" class="mt-7 max-w-[480px] text-[16px] leading-[1.6] text-[#5C4F44]">{{ c.hero.subtitle }}</p>

                            <form action="/buscar" method="get" class="mt-8 flex w-full max-w-[520px] items-center gap-2 rounded-[14px] border border-[#1A1410] bg-white p-1.5 shadow-[4px_4px_0_#1A1410]">
                                <MkIcon name="search" :size="18" class="ml-3 shrink-0 text-[#8A7B6E]" />
                                <input
                                    type="search"
                                    name="q"
                                    :placeholder="c.hero.search_placeholder"
                                    class="h-11 w-full border-0 bg-transparent p-0 text-[15px] outline-none placeholder:text-[#A3958A] focus:ring-0"
                                />
                                <button type="submit" class="h-11 shrink-0 rounded-[10px] bg-[color:var(--mk-accent)] px-5 text-[14px] font-semibold text-white hover:brightness-95">
                                    {{ c.hero.search_button }}
                                </button>
                            </form>

                            <div v-if="c.hero.show_quick_tags && quickTags.length" class="mt-5 flex flex-wrap items-center gap-2 text-[13px]" :class="c.hero.visual === 'none' ? 'justify-center' : ''">
                                <span v-if="c.hero.quick_tags_label" class="text-[#8A7B6E]">{{ c.hero.quick_tags_label }}</span>
                                <Link
                                    v-for="cat in quickTags"
                                    :key="cat.id"
                                    :href="`/categorias/${cat.slug}`"
                                    class="rounded-full border border-[#E2D7CB] px-3 py-1 font-medium text-[#3D332B] transition hover:border-[#1A1410] hover:bg-[#1A1410] hover:text-white"
                                >{{ cat.name }}</Link>
                            </div>
                        </div>

                        <div v-if="c.hero.visual !== 'none'" class="relative hidden min-h-[460px] lg:block">
                            <div v-if="c.hero.visual === 'image' && c.hero.image" class="absolute inset-0 overflow-hidden rounded-[22px] border border-[#1A1410]">
                                <img :src="c.hero.image" alt="" class="h-full w-full object-cover" />
                            </div>
                            <div v-else class="absolute inset-0 grid grid-cols-[1.25fr_1fr] grid-rows-2 gap-3">
                                <Link
                                    v-for="(p, i) in heroPicks"
                                    :key="p.id"
                                    :href="p.url"
                                    class="group relative overflow-hidden rounded-[18px]"
                                    :class="i === 0 ? 'row-span-2' : ''"
                                >
                                    <img v-if="p.image" :src="p.image" :alt="p.name" class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-105" />
                                    <CategoryPoster v-else :category="p.category" :compact="i !== 0" />
                                    <div class="absolute inset-x-0 bottom-0 p-4 text-white">
                                        <div class="text-[11px] font-semibold uppercase tracking-[0.1em] text-white/70">{{ p.category?.name }}</div>
                                        <div class="mt-1 line-clamp-2 font-semibold leading-snug" :class="i === 0 ? 'text-[18px]' : 'text-[14px]'">{{ p.name }}</div>
                                        <div class="mt-2 font-display text-[20px] font-bold">{{ formatPrice(p.price) }}</div>
                                    </div>
                                </Link>
                                <template v-if="heroPicks.length < 3">
                                    <div
                                        v-for="n in 3 - heroPicks.length"
                                        :key="'ph' + n"
                                        class="relative overflow-hidden rounded-[18px]"
                                        :class="heroPicks.length === 0 && n === 1 ? 'row-span-2' : ''"
                                    >
                                        <CategoryPoster :category="sortedCategories[n] || null" compact />
                                    </div>
                                </template>
                            </div>

                            <div
                                v-if="c.hero.trust_card_enabled"
                                class="absolute -bottom-5 -left-6 flex items-center gap-3 rounded-[14px] border border-[#1A1410] bg-white px-4 py-3 shadow-[4px_4px_0_#1A1410]"
                            >
                                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-[#E9F7EF] text-emerald-600">
                                    <MkIcon name="shield" :size="18" :stroke="2" />
                                </span>
                                <div class="text-[12px] leading-tight">
                                    <div class="font-semibold text-[#1A1410]">{{ c.hero.trust_card_title }}</div>
                                    <div v-if="c.hero.trust_card_text" class="text-[#8A7B6E]">{{ c.hero.trust_card_text }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- GARANTIAS -->
                <section v-else-if="s.type === 'trust' && c.trust.items.length" class="border-b border-[#EBE2D8] bg-white">
                    <div
                        class="mx-auto grid max-w-[1240px] grid-cols-2 divide-x divide-[#EBE2D8] px-5"
                        :class="{ 'md:grid-cols-4': c.trust.items.length === 4, 'md:grid-cols-3': c.trust.items.length === 3, 'md:grid-cols-2': c.trust.items.length <= 2 }"
                    >
                        <div v-for="(item, i) in c.trust.items" :key="i" class="flex items-center gap-3 py-5 pr-4" :class="i > 0 ? 'pl-5' : ''">
                            <MkIcon :name="item.icon" :size="22" class="shrink-0 text-[color:var(--mk-accent)]" />
                            <div class="text-[13px] leading-tight">
                                <div class="font-semibold">{{ item.title }}</div>
                                <div class="text-[#8A7B6E]">{{ item.text }}</div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- CATEGORIAS -->
                <section v-else-if="s.type === 'categories'" class="mx-auto max-w-[1240px] px-5 pt-16">
                    <div class="mb-6 flex items-end justify-between gap-4">
                        <h2 class="font-display text-[28px] font-bold tracking-[-0.03em]">{{ c.categories.title }}</h2>
                        <Link v-if="c.categories.link_label" href="/buscar" class="group inline-flex items-center gap-1.5 text-[14px] font-semibold text-[#1A1410]">
                            {{ c.categories.link_label }} <MkIcon name="arrow-right" :size="16" class="transition group-hover:translate-x-0.5" />
                        </Link>
                    </div>
                    <div class="grid grid-cols-4 gap-2.5 md:grid-cols-8 md:gap-3">
                        <Link
                            v-for="cat in sortedCategories.slice(0, c.categories.limit)"
                            :key="cat.id"
                            :href="`/categorias/${cat.slug}`"
                            class="group relative aspect-square overflow-hidden rounded-[12px] transition duration-200 hover:-translate-y-1"
                            :title="cat.name"
                        >
                            <img v-if="cat.image" :src="cat.image" :alt="cat.name" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-110" />
                            <div v-else class="absolute inset-0 transition duration-500 group-hover:scale-110">
                                <CategoryPoster :category="cat" compact />
                            </div>
                            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent px-2 pb-2.5 pt-8 text-center">
                                <div class="text-[10px] font-extrabold uppercase leading-tight tracking-[0.04em] text-white sm:text-[11px]">{{ cat.theme.label }}</div>
                            </div>
                        </Link>
                    </div>
                </section>

                <!-- OFERTAS -->
                <section v-else-if="s.type === 'deals' && (dealProducts.length || isPreviewFrame)" class="mx-auto mt-16 max-w-[1240px] px-5">
                    <div class="overflow-hidden rounded-[22px] bg-[#1A1410] p-6 text-white md:p-8">
                        <div class="mb-7 flex flex-col justify-between gap-5 md:flex-row md:items-end">
                            <div>
                                <div v-if="c.deals.eyebrow" class="text-[12px] font-semibold uppercase tracking-[0.14em] text-[color:var(--mk-accent)]">{{ c.deals.eyebrow }}</div>
                                <h2 class="mt-1 font-display text-[30px] font-bold tracking-[-0.03em]">{{ c.deals.title }}</h2>
                            </div>
                            <div v-if="c.deals.show_countdown" class="flex items-center gap-2">
                                <MkIcon name="clock" :size="16" class="text-white/50" />
                                <div class="flex items-center gap-1 font-display text-[22px] font-bold tabular-nums">
                                    <span class="rounded-[8px] bg-white/10 px-2.5 py-1">{{ pad(remaining.h) }}</span>
                                    <span class="text-white/40">:</span>
                                    <span class="rounded-[8px] bg-white/10 px-2.5 py-1">{{ pad(remaining.m) }}</span>
                                    <span class="text-white/40">:</span>
                                    <span class="rounded-[8px] bg-[color:var(--mk-accent)] px-2.5 py-1">{{ pad(remaining.s) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                            <ProductCard v-for="p in dealProducts" :key="'d' + p.id" :product="p" dark />
                        </div>
                        <p v-if="!dealProducts.length" class="py-8 text-center text-[14px] text-white/50">Sem anúncios para exibir.</p>
                    </div>
                </section>

                <!-- MAIS VENDIDOS -->
                <section v-else-if="s.type === 'popular'" class="mx-auto max-w-[1240px] px-5 pt-16">
                    <div class="mb-6 flex items-end justify-between gap-4">
                        <div>
                            <h2 class="font-display text-[28px] font-bold tracking-[-0.03em]">{{ c.popular.title }}</h2>
                            <p v-if="c.popular.subtitle" class="mt-1 text-[14px] text-[#8A7B6E]">{{ c.popular.subtitle }}</p>
                        </div>
                        <Link href="/buscar" class="group inline-flex items-center gap-1.5 text-[14px] font-semibold">
                            Ver todos <MkIcon name="arrow-right" :size="16" class="transition group-hover:translate-x-0.5" />
                        </Link>
                    </div>
                    <div v-if="popular.length" class="grid grid-cols-2 gap-4 sm:grid-cols-3" :class="gridCols[c.popular.columns]">
                        <ProductCard v-for="p in popular.slice(0, c.popular.limit)" :key="p.id" :product="p" />
                    </div>
                    <div v-else class="rounded-[14px] border border-dashed border-[#D9CCBF] p-12 text-center text-[14px] text-[#8A7B6E]">
                        Nenhum anúncio publicado ainda.
                    </div>
                </section>

                <!-- COMO FUNCIONA -->
                <section v-else-if="s.type === 'steps' && c.steps.items.length" class="mt-20 border-y border-[#EBE2D8] bg-white">
                    <div class="mx-auto grid max-w-[1240px] gap-10 px-5 py-16 lg:grid-cols-[320px_1fr]">
                        <div>
                            <h2 class="font-display text-[32px] font-bold leading-[1.05] tracking-[-0.035em]">{{ c.steps.title }}</h2>
                            <p v-if="c.steps.subtitle" class="mt-3 text-[14px] leading-relaxed text-[#8A7B6E]">{{ c.steps.subtitle }}</p>
                        </div>
                        <ol class="grid gap-8" :class="{ 'sm:grid-cols-2': c.steps.items.length === 2 || c.steps.items.length === 4, 'sm:grid-cols-3': c.steps.items.length === 3 }">
                            <li v-for="(step, i) in c.steps.items" :key="i" class="border-t-2 border-[#1A1410] pt-5">
                                <div class="font-display text-[15px] font-bold text-[color:var(--mk-accent)]">{{ pad(i + 1) }}</div>
                                <div class="mt-3 text-[17px] font-semibold tracking-[-0.01em]">{{ step.title }}</div>
                                <p class="mt-2 text-[14px] leading-relaxed text-[#6B5E52]">{{ step.text }}</p>
                            </li>
                        </ol>
                    </div>
                </section>

                <!-- RECÉM-CHEGADOS -->
                <section v-else-if="s.type === 'recent' && (featured.length || isPreviewFrame)" class="mx-auto max-w-[1240px] px-5 pt-16">
                    <div class="mb-6 flex items-end justify-between gap-4">
                        <div>
                            <h2 class="font-display text-[28px] font-bold tracking-[-0.03em]">{{ c.recent.title }}</h2>
                            <p v-if="c.recent.subtitle" class="mt-1 text-[14px] text-[#8A7B6E]">{{ c.recent.subtitle }}</p>
                        </div>
                        <Link href="/buscar" class="group inline-flex items-center gap-1.5 text-[14px] font-semibold">
                            Ver todos <MkIcon name="arrow-right" :size="16" class="transition group-hover:translate-x-0.5" />
                        </Link>
                    </div>
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3" :class="gridCols[c.recent.columns]">
                        <ProductCard v-for="p in featured.slice(0, c.recent.limit)" :key="'f' + p.id" :product="p" :badge="c.recent.badge" />
                    </div>
                </section>

                <!-- VENDEDORES -->
                <section v-else-if="s.type === 'sellers' && (sellers.length || isPreviewFrame)" class="mx-auto max-w-[1240px] px-5 pt-16">
                    <div class="mb-6">
                        <h2 class="font-display text-[28px] font-bold tracking-[-0.03em]">{{ c.sellers.title }}</h2>
                        <p v-if="c.sellers.subtitle" class="mt-1 text-[14px] text-[#8A7B6E]">{{ c.sellers.subtitle }}</p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <Link
                            v-for="sl in sellers.slice(0, c.sellers.limit)"
                            :key="sl.id"
                            :href="`/v/${sl.username}`"
                            class="group flex items-center gap-3.5 rounded-[14px] border border-[#EBE2D8] bg-white p-4 transition hover:border-[#1A1410]"
                        >
                            <div class="relative shrink-0">
                                <img v-if="sl.avatar" :src="sl.avatar" :alt="sl.name" class="h-12 w-12 rounded-[12px] object-cover" />
                                <div v-else class="flex h-12 w-12 items-center justify-center rounded-[12px] bg-[#1A1410] font-display text-[18px] font-bold uppercase text-white">
                                    {{ (sl.name || '?').slice(0, 1) }}
                                </div>
                                <span class="absolute -bottom-1 -right-1 h-3.5 w-3.5 rounded-full border-[2.5px] border-white" :class="sl.is_online ? 'bg-emerald-500' : 'bg-[#C9BDB1]'" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5">
                                    <span class="truncate text-[15px] font-semibold">{{ sl.username }}</span>
                                    <img v-if="sl.level?.image" :src="sl.level.image" :title="sl.level.name" class="h-4 w-4 rounded-full" alt="" />
                                </div>
                                <div class="mt-0.5 flex items-center gap-2 text-[12px] text-[#8A7B6E]">
                                    <span v-if="sl.level?.name" class="font-medium text-[#3D332B]">{{ sl.level.name }}</span>
                                    <span v-if="sl.level?.name" class="h-1 w-1 rounded-full bg-[#C9BDB1]" />
                                    <span class="tabular-nums">{{ sl.sales_count }} vendas</span>
                                </div>
                            </div>
                            <MkIcon name="arrow-up-right" :size="16" class="shrink-0 text-[#A3958A] transition group-hover:text-[color:var(--mk-accent)]" />
                        </Link>
                    </div>
                    <p v-if="!sellers.length" class="rounded-[14px] border border-dashed border-[#D9CCBF] p-8 text-center text-[14px] text-[#8A7B6E]">Nenhum vendedor para exibir.</p>
                </section>

                <!-- PERGUNTAS -->
                <section v-else-if="s.type === 'questions' && (recentQuestions.length || isPreviewFrame)" class="mx-auto max-w-[1240px] px-5 pt-16">
                    <h2 class="mb-6 font-display text-[28px] font-bold tracking-[-0.03em]">{{ c.questions.title }}</h2>
                    <div class="grid gap-3 md:grid-cols-3">
                        <div v-for="q in recentQuestions.slice(0, c.questions.limit)" :key="q.id" class="rounded-[14px] border border-[#EBE2D8] bg-white p-5">
                            <div class="flex gap-2.5">
                                <MkIcon name="question" :size="18" class="mt-0.5 shrink-0 text-[color:var(--mk-accent)]" />
                                <p class="text-[14px] leading-relaxed text-[#1A1410]">{{ q.body }}</p>
                            </div>
                            <div class="mt-4 flex items-center justify-between gap-3 border-t border-[#F1EAE2] pt-3 text-[12px] text-[#8A7B6E]">
                                <span class="truncate">{{ q.user_name }} · {{ q.answered_at }}</span>
                                <Link v-if="q.product_url" :href="q.product_url" class="shrink-0 font-semibold text-[#1A1410] hover:text-[color:var(--mk-accent)]">Ver anúncio</Link>
                            </div>
                        </div>
                    </div>
                    <p v-if="!recentQuestions.length" class="rounded-[14px] border border-dashed border-[#D9CCBF] p-8 text-center text-[14px] text-[#8A7B6E]">
                        Aparece quando houver perguntas respondidas.
                    </p>
                </section>

                <!-- CTA -->
                <section v-else-if="s.type === 'cta'" class="mx-auto max-w-[1240px] px-5 pt-20">
                    <div
                        class="grid overflow-hidden rounded-[22px] border border-[#1A1410] md:grid-cols-[1.3fr_1fr]"
                        :class="{
                            'bg-[color:var(--mk-accent)] text-white': c.cta.style === 'accent',
                            'bg-[#1A1410] text-white': c.cta.style === 'dark',
                            'bg-white text-[#1A1410]': c.cta.style === 'light',
                        }"
                    >
                        <div class="p-8 md:p-12">
                            <h2 class="whitespace-pre-line font-display text-[34px] font-extrabold leading-[1.02] tracking-[-0.04em] md:text-[44px]">{{ c.cta.title }}</h2>
                            <ul v-if="c.cta.bullets.length" class="mt-6 space-y-2.5 text-[15px]" :class="c.cta.style === 'light' ? 'text-[#5C4F44]' : 'text-white/90'">
                                <li v-for="(b, i) in c.cta.bullets" :key="i" class="flex items-center gap-2.5">
                                    <MkIcon name="check" :size="17" :stroke="2.5" :class="c.cta.style === 'light' ? 'text-[color:var(--mk-accent)]' : ''" /> {{ b }}
                                </li>
                            </ul>
                        </div>
                        <div class="flex flex-col justify-center gap-3 border-t border-[#1A1410] bg-[#FBF7F2] p-8 md:border-l md:border-t-0 md:p-12">
                            <div v-if="c.cta.note" class="text-[13px] font-medium text-[#8A7B6E]">{{ c.cta.note }}</div>
                            <Link :href="c.cta.primary_url" class="inline-flex items-center justify-center gap-2 rounded-[12px] bg-[#1A1410] px-6 py-3.5 text-[15px] font-semibold text-white hover:bg-black">
                                {{ c.cta.primary_label }} <MkIcon name="arrow-right" :size="16" />
                            </Link>
                            <Link
                                v-if="c.cta.secondary_label"
                                :href="c.cta.secondary_url"
                                class="inline-flex items-center justify-center rounded-[12px] border border-[#1A1410] px-6 py-3.5 text-[15px] font-semibold text-[#1A1410] hover:bg-white"
                            >{{ c.cta.secondary_label }}</Link>
                        </div>
                    </div>
                </section>

                <!-- BANNER PERSONALIZADO -->
                <section v-else-if="s.type === 'banner'" class="mx-auto max-w-[1240px] px-5 pt-16">
                    <div
                        class="relative overflow-hidden rounded-[22px]"
                        :class="s.data.tone === 'light' ? 'text-white' : 'text-[#1A1410]'"
                        :style="{ background: s.data.bg_color }"
                    >
                        <template v-if="s.data.layout === 'cover' && s.data.image">
                            <img :src="s.data.image" alt="" class="absolute inset-0 h-full w-full object-cover" />
                            <div class="absolute inset-0" :class="s.data.tone === 'light' ? 'bg-gradient-to-r from-black/75 via-black/40 to-transparent' : 'bg-gradient-to-r from-white/85 via-white/50 to-transparent'" />
                        </template>
                        <div
                            class="relative grid items-center gap-8 p-8 md:p-12"
                            :class="{
                                'md:grid-cols-[1.2fr_1fr]': s.data.layout === 'split' && s.data.image,
                                'text-center': s.data.layout === 'center',
                                'min-h-[300px] max-w-[620px]': s.data.layout === 'cover',
                            }"
                        >
                            <div :class="s.data.layout === 'center' ? 'mx-auto max-w-[680px]' : ''">
                                <div v-if="s.data.eyebrow" class="text-[12px] font-semibold uppercase tracking-[0.14em] opacity-70">{{ s.data.eyebrow }}</div>
                                <h2 class="mt-2 font-display text-[30px] font-extrabold leading-[1.05] tracking-[-0.035em] md:text-[38px]">{{ s.data.title }}</h2>
                                <p v-if="s.data.text" class="mt-4 max-w-[520px] text-[15px] leading-relaxed opacity-80" :class="s.data.layout === 'center' ? 'mx-auto' : ''">{{ s.data.text }}</p>
                                <Link
                                    v-if="s.data.button_label"
                                    :href="s.data.button_url"
                                    class="mt-7 inline-flex items-center gap-2 rounded-[12px] px-6 py-3 text-[14px] font-semibold transition hover:brightness-95"
                                    :class="s.data.tone === 'light' ? 'bg-white text-[#1A1410]' : 'bg-[#1A1410] text-white'"
                                >{{ s.data.button_label }} <MkIcon name="arrow-right" :size="16" /></Link>
                            </div>
                            <div v-if="s.data.layout === 'split' && s.data.image" class="overflow-hidden rounded-[16px]">
                                <img :src="s.data.image" alt="" class="aspect-[4/3] h-full w-full object-cover" />
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </template>
        <div class="h-20" />
    </MarketplaceLayout>
</template>

<style scoped>
.mk-outline {
    outline: 2px dashed color-mix(in srgb, var(--mk-accent) 60%, transparent);
    outline-offset: -2px;
    cursor: pointer;
}
.mk-outline-active {
    outline: 2px solid var(--mk-accent);
}
.mk-outline-tag {
    position: absolute;
    top: 8px;
    left: 8px;
    z-index: 40;
    border-radius: 6px;
    background: var(--mk-accent);
    padding: 2px 8px;
    font-size: 11px;
    font-weight: 700;
    color: #fff;
    pointer-events: none;
}
</style>
