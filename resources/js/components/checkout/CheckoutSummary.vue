<script setup>
import { ref, computed } from 'vue';
import { ChevronDown, ChevronUp, Tag, Globe, Banknote, Check } from 'lucide-vue-next';
import CheckoutDropdown from './CheckoutDropdown.vue';

const INTERVAL_LABELS = {
    weekly: 'Semanal',
    monthly: 'Mensal',
    quarterly: 'Trimestral',
    semi_annual: 'Semestral',
    annual: 'Anual',
    lifetime: 'Vitalício',
};
function intervalLabel(interval) {
    return INTERVAL_LABELS[interval] || interval || '';
}

const props = defineProps({
    product: { type: Object, required: true },
    subscriptionPlan: { type: Object, default: null },
    config: { type: Object, default: () => ({}) },
    primaryColor: { type: String, default: '#FF5A1F' },
    /** Desconto aplicado pelo cupom: { discount_amount, final_price } */
    appliedCoupon: { type: Object, default: null },
    t: { type: Function, default: (k) => k },
    displayCurrency: { type: String, default: 'BRL' },
    priceInCurrency: { type: Function, default: (v) => v },
    formatPrice: { type: Function, default: (v, c) => String(v) },
    locale: { type: String, default: 'pt_BR' },
    supportedLocales: { type: Array, default: () => ['pt_BR', 'en', 'es'] },
    currencyList: { type: Array, default: () => [] },
    localeLabels: { type: Object, default: () => ({ pt_BR: 'PT', en: 'EN', es: 'ES' }) },
});

const emit = defineEmits(['set-locale', 'set-currency']);

const summary = computed(() => props.config?.summary ?? {});
const showDescription = computed(() => summary.value.show_description !== false);
const previousPrice = computed(() => {
    const v = summary.value.previous_price;
    return v != null && v !== '' ? Number(v) : null;
});
const discountText = computed(() => summary.value.discount_text || '');

const priceToShowBrl = computed(() => {
    const applied = props.appliedCoupon;
    if (applied != null && applied.final_price != null) return Number(applied.final_price);
    const p = props.product?.price_brl ?? props.product?.price ?? 0;
    return Number(p);
});
const priceToShow = computed(() => props.priceInCurrency(priceToShowBrl.value));
const showOriginalPriceStrikethrough = computed(() => {
    if (props.appliedCoupon != null && props.product?.price != null) return true;
    return previousPrice.value != null;
});
const originalPriceForDisplayBrl = computed(() => {
    if (props.appliedCoupon != null && props.product?.price != null) return Number(props.product.price);
    return previousPrice.value;
});
const originalPriceForDisplay = computed(() =>
    originalPriceForDisplayBrl.value != null ? props.priceInCurrency(originalPriceForDisplayBrl.value) : null
);
const couponDiscountAmountBrl = computed(() =>
    props.appliedCoupon?.discount_amount != null ? Number(props.appliedCoupon.discount_amount) : 0
);
const couponDiscountAmount = computed(() => props.priceInCurrency(couponDiscountAmountBrl.value));
const description = computed(() => props.product?.description ?? '');
const shortDesc = computed(() => {
    const d = description.value.replace(/<[^>]+>/g, '').trim();
    return d.length > 120 ? d.slice(0, 120) + '…' : d;
});
const fullDesc = computed(() => description.value.replace(/<[^>]+>/g, '').trim());
const showVerMais = computed(() => fullDesc.value.length > 120);

const expanded = ref(false);
const displayDesc = computed(() => (expanded.value ? fullDesc.value : shortDesc.value));

const localeOpen = ref(false);
const currencyOpen = ref(false);

function selectLocale(loc) {
    emit('set-locale', loc);
    localeOpen.value = false;
}
function selectCurrency(code) {
    emit('set-currency', code);
    currencyOpen.value = false;
}
</script>

<template>
    <section class="flex flex-row items-start gap-4 sm:gap-5" data-id="summary" data-checkout="summary">
        <div class="relative flex-shrink-0" data-checkout="summary-product-image">
            <img
                :src="product.image_url || 'https://placehold.co/96x96/F1EAE2/8A7B6E?text=Produto'"
                :alt="product.name"
                class="h-24 w-24 rounded-[14px] object-cover ring-1 ring-[#EBE2D8] sm:h-28 sm:w-28"
            />
        </div>
        <div class="min-w-0 flex-1" data-checkout="summary-main">
            <div class="relative flex items-start gap-3">
                <h1
                    class="min-w-0 flex-1 pr-0 font-display text-[20px] font-bold tracking-[-0.03em] text-[#1A1410] line-clamp-2 sm:pr-24 sm:text-[24px]"
                    data-checkout="summary-title"
                >
                    {{ product.name }}
                </h1>
                <div
                    class="absolute right-0 top-[-48px] z-10 flex shrink-0 items-center gap-1.5 rounded-[10px] border border-[#EBE2D8] bg-white p-1 sm:top-0 sm:-translate-y-1/2"
                    data-checkout="summary-locale-currency"
                >
                    <CheckoutDropdown
                        v-model:open="localeOpen"
                        :icon="Globe"
                        aria-label="Idioma"
                        align="right"
                    >
                        <button
                            v-for="loc in supportedLocales"
                            :key="loc"
                            type="button"
                            role="option"
                            class="flex w-full items-center justify-between gap-3 px-4 py-2.5 text-left text-sm transition hover:bg-[#FBF7F2]"
                            :class="locale === loc ? 'bg-[#FBF7F2] font-medium text-[#1A1410]' : 'text-[#3D332B]'"
                            @click="selectLocale(loc)"
                        >
                            <span>{{ localeLabels[loc] || loc }}</span>
                            <Check v-if="locale === loc" class="h-4 w-4 shrink-0 text-[#8A7B6E]" />
                        </button>
                    </CheckoutDropdown>
                    <CheckoutDropdown
                        v-model:open="currencyOpen"
                        :icon="Banknote"
                        aria-label="Moeda"
                        align="right"
                    >
                        <button
                            v-for="c in currencyList"
                            :key="c.code"
                            type="button"
                            role="option"
                            class="flex w-full items-center justify-between gap-3 px-4 py-2.5 text-left text-sm transition hover:bg-[#FBF7F2]"
                            :class="displayCurrency === c.code ? 'bg-[#FBF7F2] font-medium text-[#1A1410]' : 'text-[#3D332B]'"
                            @click="selectCurrency(c.code)"
                        >
                            <span>{{ c.code }} · {{ c.symbol }}</span>
                            <Check v-if="displayCurrency === c.code" class="h-4 w-4 shrink-0 text-[#8A7B6E]" />
                        </button>
                    </CheckoutDropdown>
                </div>
            </div>
            <div class="mt-3 flex flex-wrap items-baseline gap-x-3 gap-y-1" data-checkout="summary-price-row">
                <span class="font-display text-[26px] font-extrabold tracking-[-0.03em] sm:text-[30px]" :style="{ color: primaryColor }">
                    {{ formatPrice(priceToShow, displayCurrency) }}
                    <span v-if="subscriptionPlan?.interval" class="ml-1 align-baseline text-sm font-medium text-[#8A7B6E]">{{ intervalLabel(subscriptionPlan.interval) }}</span>
                </span>
                <span v-if="showOriginalPriceStrikethrough && originalPriceForDisplay != null" class="text-lg font-medium text-[#A3958A] line-through">
                    {{ formatPrice(originalPriceForDisplay, displayCurrency) }}
                </span>
            </div>
            <p
                v-if="couponDiscountAmountBrl > 0"
                class="mt-1.5 text-sm font-medium text-emerald-700"
                data-checkout="summary-coupon-discount"
            >
                {{ t('checkout.discount_coupon') }}: -{{ formatPrice(couponDiscountAmount, displayCurrency) }}
            </p>
            <span
                v-if="discountText"
                class="mt-3 inline-flex items-center gap-1.5 rounded-[10px] border border-[#E2D7CB] bg-[#FBF7F2] px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-[#1A1410]"
                data-checkout="summary-discount-badge"
            >
                <Tag class="h-3.5 w-3.5" />
                {{ discountText }}
            </span>
            <template v-if="showDescription && fullDesc">
                <p
                    class="mt-3 text-sm leading-relaxed text-[#6B5E54]"
                    data-checkout="summary-description"
                    :class="{ 'line-clamp-2': !expanded && showVerMais }"
                >
                    {{ displayDesc }}
                </p>
                <button
                    v-if="showVerMais"
                    type="button"
                    class="mt-2 inline-flex items-center gap-1 rounded-lg text-sm font-semibold transition-colors focus:outline-none"
                    :style="{ color: primaryColor }"
                    @click="expanded = !expanded"
                >
                    {{ expanded ? t('checkout.ver_menos') : t('checkout.ver_mais') }}
                    <ChevronDown v-if="!expanded" class="h-4 w-4" />
                    <ChevronUp v-else class="h-4 w-4" />
                </button>
            </template>
        </div>
    </section>
</template>
