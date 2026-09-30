<script setup>
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { CheckCircle2, XCircle } from 'lucide-vue-next';
import ConversionPixels from '@/components/checkout/ConversionPixels.vue';
import { trackCheckoutPurchase } from '@/composables/useCheckoutPurchaseTracking';
import MarketplaceCheckoutShell from '@/Layouts/MarketplaceCheckoutShell.vue';

defineOptions({ layout: null });

const conversionPixelsRef = ref(null);
let purchaseFiredForLoad = false;

const props = defineProps({
    redirect_url: { type: String, default: '/' },
    redirect_label: { type: String, default: 'Acessar área de membros' },
    subtitle: { type: String, default: 'Seu pedido foi registrado. Acesse o conteúdo pelo link abaixo.' },
    show_button: { type: Boolean, default: true },
    conversion_pixels: { type: Object, default: () => ({}) },
    order_id: { type: Number, default: null },
    order_amount: { type: Number, default: 0 },
    order_status: { type: String, default: null },
    purchase_confirmed: { type: Boolean, default: false },
    checkout_session_token: { type: String, default: '' },
});

const isRejected = computed(() => String(props.order_status || '').toLowerCase() === 'rejected');
const title = computed(() => {
    if (props.purchase_confirmed) return 'Obrigado pela sua compra';
    if (isRejected.value) return 'Pagamento não aprovado';
    return 'Pagamento em processamento';
});

async function onConversionPixelsMetaReady() {
    if (purchaseFiredForLoad) return;
    if (!props.purchase_confirmed) return;
    if (!props.order_id || !(Number(props.order_amount) > 0)) return;
    purchaseFiredForLoad = true;

    await trackCheckoutPurchase({
        orderId: props.order_id,
        checkoutSessionToken: props.checkout_session_token || '',
        value: props.order_amount,
        currency: 'BRL',
        triggerType: 'approved',
        pixels: props.conversion_pixels || {},
        conversionPixelsApi: conversionPixelsRef.value,
        settleDelayMs: 350,
    });
}
</script>

<template>
    <ConversionPixels
        v-if="purchase_confirmed"
        ref="conversionPixelsRef"
        :pixels="props.conversion_pixels"
        @meta-ready="onConversionPixelsMetaReady"
    />
    <Head>
        <title>{{ title }}</title>
    </Head>
    <MarketplaceCheckoutShell :title="title" back-href="/" back-label="Ir para a loja">
        <div class="flex flex-1 flex-col items-center justify-center px-5 py-16">
            <div class="w-full max-w-md rounded-[16px] border border-[#EBE2D8] bg-white p-8 text-center shadow-[0_1px_0_rgba(26,20,16,0.04)]">
                <div
                    class="mx-auto flex h-14 w-14 items-center justify-center rounded-full"
                    :class="purchase_confirmed ? 'bg-emerald-100 text-emerald-700' : (isRejected ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-700')"
                >
                    <CheckCircle2 v-if="purchase_confirmed" class="h-8 w-8" />
                    <XCircle v-else-if="isRejected" class="h-8 w-8" />
                    <CheckCircle2 v-else class="h-8 w-8 opacity-60" />
                </div>
                <h1 class="mt-4 font-display text-[22px] font-bold tracking-[-0.03em] text-[#1A1410]">
                    {{ title }}
                </h1>
                <p class="mt-2 text-sm text-[#6B5E54]">
                    {{ subtitle }}
                </p>
                <a
                    v-if="show_button && purchase_confirmed"
                    :href="redirect_url"
                    class="mt-6 inline-flex items-center justify-center rounded-[12px] bg-[color:var(--mk-accent)] px-5 py-2.5 text-sm font-semibold text-white hover:brightness-95"
                >
                    {{ redirect_label }}
                </a>
                <a
                    v-else-if="!purchase_confirmed"
                    href="/painel-cliente"
                    class="mt-6 inline-flex items-center justify-center rounded-[12px] border border-[#E2D7CB] bg-white px-5 py-2.5 text-sm font-semibold text-[#1A1410] hover:border-[#1A1410]"
                >
                    Ir para minhas compras
                </a>
            </div>
        </div>
    </MarketplaceCheckoutShell>
</template>
