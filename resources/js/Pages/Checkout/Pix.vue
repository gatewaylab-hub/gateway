<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import QrcodeVue from 'qrcode.vue';
import { Clock, Copy, Check, Building2, QrCode, CircleDollarSign } from 'lucide-vue-next';
import confetti from 'canvas-confetti';
import ConversionPixels from '@/components/checkout/ConversionPixels.vue';
import { trackCheckoutPurchase, trackCheckoutPurchaseBeacon } from '@/composables/useCheckoutPurchaseTracking';
import { navigateAfterCheckout } from '@/lib/checkoutRedirect.js';
import MarketplaceCheckoutShell from '@/Layouts/MarketplaceCheckoutShell.vue';

const POLL_INTERVAL_MS = 2500;

defineOptions({ layout: null });

const conversionPixelsRef = ref(null);

const props = defineProps({
    token: { type: String, required: true },
    order_id: { type: Number, required: true },
    checkout_session_token: { type: String, default: '' },
    qrcode: { type: String, default: null },
    copy_paste: { type: String, default: '' },
    amount_formatted: { type: String, default: 'R$ 0,00' },
    product_name: { type: String, default: '' },
    checkout_slug: { type: String, default: '' },
    redirect_after_purchase: { type: String, default: null },
    customer_name: { type: String, default: null },
    customer_email: { type: String, default: null },
    customer_phone: { type: String, default: null },
    created_at: { type: Number, required: true },
    expiry_seconds: { type: Number, default: 900 },
    amount: { type: Number, default: 0 },
    conversion_pixels: { type: Object, default: () => ({}) },
});

/** Woovi/OpenPix envia qrCodeImage como URL; outros gateways podem mandar base64 cru ou data: URL. */
const qrcodeSrc = computed(() => {
    const q = (props.qrcode || '').trim();
    if (!q) return '';
    if (q.startsWith('data:')) return q;
    if (/^https?:\/\//i.test(q)) return q;
    if (q.startsWith('//')) return `https:${q}`;
    return `data:image/png;base64,${q}`;
});

const showQrFromCopyPaste = computed(
    () => !qrcodeSrc.value && (props.copy_paste || '').trim().length > 0
);

const copyButtonText = ref('Copiar');
const status = ref('pending');
const confirmFeedback = ref('');
const confirmChecking = ref(false);
let pollInterval = null;
let timerInterval = null;
let purchaseTracked = false;

const endTime = computed(() => (props.created_at + props.expiry_seconds) * 1000);
const timeLeft = ref(props.expiry_seconds);

function updateTimer() {
    const now = Date.now();
    const left = Math.max(0, Math.floor((endTime.value - now) / 1000));
    timeLeft.value = left;
    if (left <= 0 && pollInterval) {
        clearInterval(pollInterval);
        pollInterval = null;
    }
}

const timerDisplay = computed(() => {
    const left = timeLeft.value;
    const m = Math.floor(left / 60);
    const s = left % 60;
    return `${m < 10 ? '0' : ''}${m}:${s < 10 ? '0' : ''}${s}`;
});

const timerExpired = computed(() => timeLeft.value <= 0);
const hasCustomerInfo = computed(
    () => (props.customer_name || '') !== '' || (props.customer_email || '') !== '' || (props.customer_phone || '') !== ''
);

async function onPaymentCompleted(redirectUrl) {
    if (!purchaseTracked) {
        purchaseTracked = true;
        await trackCheckoutPurchase({
            orderId: props.order_id,
            checkoutSessionToken: props.checkout_session_token || '',
            token: props.token,
            triggerType: 'pix',
            value: props.amount,
            currency: 'BRL',
            pixels: props.conversion_pixels || {},
            conversionPixelsApi: conversionPixelsRef.value,
            settleDelayMs: 500,
        });
    }
    const url = redirectUrl || props.redirect_after_purchase || '/area-membros';
    navigateAfterCheckout(url);
}

function onPageHideBeacon() {
    if (status.value !== 'completed' || purchaseTracked) return;
    purchaseTracked = true;
    trackCheckoutPurchaseBeacon({
        orderId: props.order_id,
        checkoutSessionToken: props.checkout_session_token || '',
        token: props.token,
        triggerType: 'pix',
        value: props.amount,
        currency: 'BRL',
    });
}

async function checkOrderStatus() {
    try {
        const { data } = await axios.get('/checkout/order-status', { params: { token: props.token } });
        if (data.status === 'completed') {
            status.value = 'completed';
            confirmFeedback.value = 'Pagamento aprovado! Redirecionando...';
            if (pollInterval) {
                clearInterval(pollInterval);
                pollInterval = null;
            }
            await onPaymentCompleted(data.redirect_url);
        }
        return data;
    } catch {
        return { status: 'pending' };
    }
}

function onConfirmPayment() {
    confirmChecking.value = true;
    confirmFeedback.value = 'Aguardando...';
    checkOrderStatus().then((data) => {
        confirmChecking.value = false;
        if (data.status !== 'completed') {
            confirmFeedback.value = 'Ainda aguardando. O pagamento é confirmado automaticamente.';
        }
    });
}

async function copyPixCode() {
    const code = props.copy_paste || '';
    if (!code) return;
    try {
        await navigator.clipboard.writeText(code);
        copyButtonText.value = 'Copiado!';
        setTimeout(() => (copyButtonText.value = 'Copiar'), 2000);
    } catch {
        copyButtonText.value = 'Copiar';
    }
}

function backToCheckout() {
    if (props.checkout_slug) {
        router.visit(`/c/${props.checkout_slug}`);
    } else {
        router.visit('/');
    }
}

onMounted(() => {
    confetti({ particleCount: 120, spread: 70, origin: { y: 0.6 } });
    updateTimer();
    timerInterval = setInterval(updateTimer, 1000);
    pollInterval = setInterval(() => {
        if (status.value === 'completed') return;
        checkOrderStatus();
    }, POLL_INTERVAL_MS);
    if (typeof window !== 'undefined') {
        window.addEventListener('pagehide', onPageHideBeacon);
    }
});

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
    if (pollInterval) clearInterval(pollInterval);
    if (typeof window !== 'undefined') {
        window.removeEventListener('pagehide', onPageHideBeacon);
    }
});
</script>

<template>
    <ConversionPixels ref="conversionPixelsRef" :pixels="props.conversion_pixels" />
    <Head>
        <title>Pagamento PIX</title>
    </Head>
    <MarketplaceCheckoutShell title="Pagamento PIX" :back-href="checkout_slug ? `/c/${checkout_slug}` : '/'" back-label="Voltar ao checkout">
        <div class="px-5 py-8 pb-12">
            <div class="mx-auto w-full max-w-md space-y-4">
                <div class="overflow-hidden rounded-[16px] border border-[#EBE2D8] bg-white shadow-[0_1px_0_rgba(26,20,16,0.04)]">
                    <div v-if="qrcodeSrc || showQrFromCopyPaste" class="flex justify-center pb-2 pt-6">
                        <div class="h-40 w-40 rounded-[14px] border border-dashed border-[#E2D7CB] bg-[#FBF7F2] p-2.5 sm:h-44 sm:w-44">
                            <img
                                v-if="qrcodeSrc"
                                :src="qrcodeSrc"
                                alt="QR Code PIX"
                                class="h-full w-full object-contain"
                                style="image-rendering: pixelated"
                            />
                            <QrcodeVue
                                v-else
                                :value="copy_paste"
                                :size="160"
                                level="M"
                                class="h-full w-full"
                            />
                        </div>
                    </div>
                    <div class="px-5 pb-6 sm:px-6">
                        <h1 class="mb-1 text-center font-display text-[20px] font-bold tracking-[-0.03em] text-[#1A1410] sm:text-[22px]">
                            Pague {{ amount_formatted }} via Pix
                        </h1>
                        <p class="mb-5 text-center text-xs text-[#8A7B6E] sm:text-sm">
                            Copie o código ou use a câmera para ler o QR Code e realize o pagamento no app do seu banco.
                        </p>

                        <p class="mb-2 text-xs font-semibold uppercase tracking-[0.08em] text-[#8A7B6E]">Pix Copia e Cola</p>
                        <input
                            type="text"
                            :value="copy_paste"
                            readonly
                            class="mb-3 w-full rounded-[12px] border border-[#E2D7CB] bg-[#FBF7F2] px-3 py-2.5 text-base text-[#1A1410] focus:outline-none"
                        />
                        <button
                            type="button"
                            class="mb-4 flex w-full items-center justify-center gap-1.5 rounded-[12px] bg-[color:var(--mk-accent)] px-4 py-3 text-sm font-semibold text-white transition hover:brightness-95"
                            @click="copyPixCode"
                        >
                            <Copy class="h-4 w-4" />
                            {{ copyButtonText }}
                        </button>

                        <div class="mb-4 space-y-3">
                            <button
                                type="button"
                                class="flex w-full items-center justify-center gap-2 rounded-[12px] border border-[#E2D7CB] bg-white py-3 font-semibold text-[#1A1410] transition hover:border-[#1A1410] disabled:opacity-70"
                                :disabled="confirmChecking || status === 'completed'"
                                @click="onConfirmPayment"
                            >
                                <Check class="h-4 w-4 text-emerald-600" />
                                {{ confirmChecking ? 'Verificando...' : 'Confirmar pagamento' }}
                            </button>
                            <p v-if="confirmFeedback" class="text-center text-xs text-[#8A7B6E]" :class="status === 'completed' ? 'text-emerald-700' : ''">
                                {{ confirmFeedback }}
                            </p>
                        </div>

                        <div class="mb-1 rounded-[12px] border border-dashed border-[#E2D7CB] bg-[#FBF7F2] p-4">
                            <div class="flex items-center justify-center gap-2">
                                <Clock class="h-4 w-4 shrink-0 text-[#6B5E54]" />
                                <span class="text-sm font-medium text-[#3D332B]">Código expira em</span>
                                <span
                                    class="ml-auto font-mono text-lg font-bold tabular-nums text-[#1A1410]"
                                    :class="timerExpired ? 'text-red-600' : ''"
                                >
                                    {{ timerDisplay }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-[16px] border border-[#EBE2D8] bg-white shadow-[0_1px_0_rgba(26,20,16,0.04)]">
                    <div class="space-y-4 p-5 sm:p-6">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#F1EAE2] text-[#3D332B]">
                                <Building2 class="h-5 w-5" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-[#1A1410]">Acesse seu banco</h3>
                                <p class="text-xs text-[#8A7B6E]">Abra o app do seu banco, é rapidinho.</p>
                            </div>
                        </div>
                        <div class="border-t border-dashed border-[#EBE2D8]" />
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#F1EAE2] text-[#3D332B]">
                                <QrCode class="h-5 w-5" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-[#1A1410]">Escolha a opção Pix</h3>
                                <p class="text-xs text-[#8A7B6E]">Selecione "Pix Copia e Cola" ou "Ler QR code".</p>
                            </div>
                        </div>
                        <div class="border-t border-dashed border-[#EBE2D8]" />
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#F1EAE2] text-[#3D332B]">
                                <CircleDollarSign class="h-5 w-5" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-[#1A1410]">Conclua o pagamento</h3>
                                <p class="text-xs text-[#8A7B6E]">Cole o código ou leia o QR code, confirme os dados e pronto!</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-[16px] border border-[#EBE2D8] bg-white shadow-[0_1px_0_rgba(26,20,16,0.04)]">
                    <div class="p-5 sm:p-6">
                        <h3 class="mb-4 font-display text-[15px] font-bold text-[#1A1410]">Resumo da compra</h3>
                        <div class="mb-3 space-y-2">
                            <div v-if="product_name" class="flex items-start justify-between gap-2 text-xs">
                                <span class="shrink-0 text-[#8A7B6E]">Produto</span>
                                <span class="text-right font-medium text-[#1A1410]">{{ product_name }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-[#6B5E54]">Pagamento Pix</span>
                                <span class="font-medium text-[#1A1410]">{{ amount_formatted }}</span>
                            </div>
                        </div>
                        <div class="mt-3 border-t border-dashed border-[#EBE2D8] pt-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-bold text-[#1A1410]">Total</span>
                                <span class="font-display text-[18px] font-extrabold text-[color:var(--mk-accent)]">{{ amount_formatted }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-[16px] border border-[#EBE2D8] bg-white shadow-[0_1px_0_rgba(26,20,16,0.04)]">
                    <div class="p-5 sm:p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#F1EAE2] text-[#3D332B]">
                                    <QrCode class="h-5 w-5" />
                                </div>
                                <span class="text-sm font-medium text-[#1A1410]">Pix</span>
                            </div>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-[10px] border px-3 py-1.5 text-xs font-semibold"
                                :class="status === 'completed'
                                    ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
                                    : 'border-amber-200 bg-amber-50 text-amber-800'"
                            >
                                <Clock v-if="status === 'pending'" class="h-3.5 w-3.5" />
                                <Check v-else class="h-3.5 w-3.5" />
                                {{ status === 'completed' ? 'Aprovado' : 'Pendente' }}
                            </span>
                        </div>
                        <div v-if="hasCustomerInfo" class="mt-5 border-t border-dashed border-[#EBE2D8] pt-5">
                            <h3 class="mb-4 text-sm font-bold text-[#1A1410]">Informações</h3>
                            <div class="space-y-3 text-sm">
                                <div v-if="customer_name" class="flex items-center justify-between">
                                    <span class="text-[#8A7B6E]">Cliente</span>
                                    <span class="ml-2 break-all text-right font-medium text-[#1A1410]">{{ customer_name }}</span>
                                </div>
                                <div v-if="customer_email" class="flex items-center justify-between">
                                    <span class="text-[#8A7B6E]">E-mail</span>
                                    <span class="ml-2 break-all text-right font-medium text-[#1A1410]">{{ customer_email }}</span>
                                </div>
                                <div v-if="customer_phone" class="flex items-center justify-between">
                                    <span class="text-[#8A7B6E]">Telefone</span>
                                    <span class="font-medium text-[#1A1410]">{{ customer_phone }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MarketplaceCheckoutShell>
</template>
