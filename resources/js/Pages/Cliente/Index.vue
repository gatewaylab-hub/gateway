<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import MarketplaceAccountLayout from '@/Layouts/MarketplaceAccountLayout.vue';
import MkIcon from '@/components/marketplace/MkIcon.vue';
import { copyTextToClipboard } from '@/lib/copyText';

const props = defineProps({
    purchases: { type: Array, default: () => [] },
});

const page = usePage();
const searchQuery = ref('');
const filter = ref('all');
const detailsOpen = ref(false);
const detailsRow = ref(null);
const copiedKey = ref('');
const refundOpen = ref(false);
const refundOrderPublicRef = ref('');

const refundForm = useForm({
    order_id: null,
    reason: '',
});

const firstName = computed(() => {
    const name = String(page.props.auth?.user?.name || '').trim();
    if (!name) return '';
    return name.split(/\s+/)[0];
});

const purchaseCount = computed(() => props.purchases?.length || 0);

const filteredPurchases = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    return (props.purchases || []).filter((row) => {
        if (filter.value === 'access' && !row.access_url) return false;
        if (filter.value === 'granted' && !row.is_manual_grant) return false;
        if (!q) return true;
        const haystack = [
            row.product_name,
            row.public_reference,
            row.seller_name,
            row.product_type_label,
        ]
            .filter(Boolean)
            .join(' ')
            .toLowerCase();
        return haystack.includes(q);
    });
});

const hasGranted = computed(() => (props.purchases || []).some((row) => row.is_manual_grant));
const hasAccess = computed(() => (props.purchases || []).some((row) => row.access_url));

function formatBRL(n) {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(n) || 0);
}

function refundStatusLabel(status) {
    if (status === 'pending') return 'Reembolso em análise';
    if (status === 'approved') return 'Reembolso aprovado';
    if (status === 'rejected') return 'Reembolso recusado';
    return '';
}

function openRefund(row) {
    refundForm.order_id = row.order_id;
    refundOrderPublicRef.value = row.public_reference || String(row.order_id);
    refundForm.reason = '';
    refundForm.clearErrors();
    refundOpen.value = true;
}

function closeRefund() {
    refundOpen.value = false;
    refundOrderPublicRef.value = '';
}

function submitRefund() {
    refundForm.post('/painel-cliente/reembolso', {
        preserveScroll: true,
        onSuccess: () => closeRefund(),
    });
}

function openDetails(row) {
    detailsRow.value = row;
    copiedKey.value = '';
    detailsOpen.value = true;
}

function closeDetails() {
    detailsOpen.value = false;
    detailsRow.value = null;
    copiedKey.value = '';
}

async function copyValue(value, key) {
    const ok = await copyTextToClipboard(value);
    if (ok) {
        copiedKey.value = key;
        window.setTimeout(() => {
            if (copiedKey.value === key) copiedKey.value = '';
        }, 1800);
    }
}

const chipClass = (active) =>
    active
        ? 'bg-[#1A1410] text-white'
        : 'border border-[#E2D7CB] bg-white text-[#3D332B] hover:border-[#1A1410]';
</script>

<template>
    <Head title="Minhas compras" />
    <MarketplaceAccountLayout :title="firstName ? `Olá, ${firstName}` : 'Minhas compras'">
        <p class="mb-6 -mt-2 text-[14px] text-[#6B5E54]">
            <template v-if="purchaseCount === 1">Você tem 1 produto nesta conta.</template>
            <template v-else-if="purchaseCount > 1">Você tem {{ purchaseCount }} produtos nesta conta.</template>
            <template v-else>Quando uma compra for concluída, o acesso aparece aqui.</template>
        </p>

        <div
            v-if="page.props.flash?.success"
            class="mb-5 rounded-[12px] border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900"
        >
            {{ page.props.flash.success }}
        </div>
        <div
            v-if="page.props.flash?.error"
            class="mb-5 rounded-[12px] border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900"
        >
            {{ page.props.flash.error }}
        </div>

        <template v-if="purchaseCount">
            <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="relative min-w-0 flex-1">
                    <MkIcon name="search" :size="16" class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-[#A3958A]" />
                    <input
                        v-model="searchQuery"
                        type="search"
                        placeholder="Buscar produto, pedido ou vendedor"
                        class="h-11 w-full rounded-[12px] border border-[#E2D7CB] bg-white py-2.5 pl-10 pr-3 text-[14px] text-[#1A1410] placeholder:text-[#A3958A] outline-none focus:border-[#1A1410]"
                    />
                </div>
                <div class="flex flex-wrap gap-1.5">
                    <button type="button" class="rounded-[10px] px-3.5 py-2 text-[12px] font-semibold transition" :class="chipClass(filter === 'all')" @click="filter = 'all'">Todos</button>
                    <button v-if="hasAccess" type="button" class="rounded-[10px] px-3.5 py-2 text-[12px] font-semibold transition" :class="chipClass(filter === 'access')" @click="filter = 'access'">Com acesso</button>
                    <button v-if="hasGranted" type="button" class="rounded-[10px] px-3.5 py-2 text-[12px] font-semibold transition" :class="chipClass(filter === 'granted')" @click="filter = 'granted'">Liberados</button>
                </div>
            </div>

            <p
                v-if="!filteredPurchases.length"
                class="rounded-[14px] border border-dashed border-[#E2D7CB] px-4 py-10 text-center text-sm text-[#8A7B6E]"
            >
                Nenhum produto encontrado com essa busca.
            </p>

            <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <article
                    v-for="row in filteredPurchases"
                    :key="row.purchase_key || row.order_id"
                    class="flex flex-col overflow-hidden rounded-[16px] border border-[#EBE2D8] bg-white shadow-[0_1px_0_rgba(26,20,16,0.04)] transition hover:border-[#D6C9BB]"
                >
                    <div class="relative h-40 w-full shrink-0 overflow-hidden bg-[#F1EAE2] sm:h-44">
                        <img
                            v-if="row.product_image_url"
                            :src="row.product_image_url"
                            :alt="row.product_name"
                            class="h-full w-full object-cover"
                            loading="lazy"
                        />
                        <div v-else class="flex h-full w-full items-center justify-center text-[#A3958A]">
                            <MkIcon name="box" :size="40" />
                        </div>
                        <div class="absolute left-2 top-2 flex flex-wrap gap-1">
                            <span v-if="row.product_type_label" class="rounded-full bg-black/70 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-white">{{ row.product_type_label }}</span>
                            <span v-if="row.is_order_bump" class="rounded-full bg-[#1A1410]/85 px-2 py-0.5 text-[10px] font-semibold text-white">Extra</span>
                            <span v-if="row.is_manual_grant" class="rounded-full bg-emerald-600/90 px-2 py-0.5 text-[10px] font-semibold text-white">Liberado</span>
                            <span v-if="row.is_renewal" class="rounded-full bg-sky-600/90 px-2 py-0.5 text-[10px] font-semibold text-white">Renovação</span>
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col gap-3 p-4">
                        <div>
                            <h2 class="line-clamp-2 font-display text-[16px] font-bold leading-snug tracking-[-0.02em] text-[#1A1410]">{{ row.product_name }}</h2>
                            <p v-if="row.seller_name" class="mt-0.5 truncate text-[12px] text-[#8A7B6E]">{{ row.seller_name }}</p>
                            <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-[12px] text-[#6B5E54]">
                                <span v-if="row.purchased_at_label" class="inline-flex items-center gap-1">
                                    <MkIcon name="clock" :size="13" /> {{ row.purchased_at_label }}
                                </span>
                                <span v-if="!row.is_manual_grant" class="font-semibold text-[#1A1410]">{{ formatBRL(row.amount) }}</span>
                            </div>
                            <p
                                v-if="row.refund_status"
                                class="mt-2 text-[12px] font-medium"
                                :class="{
                                    'text-amber-700': row.refund_status === 'pending',
                                    'text-emerald-700': row.refund_status === 'approved',
                                    'text-[#8A7B6E]': row.refund_status === 'rejected',
                                }"
                            >
                                {{ refundStatusLabel(row.refund_status) }}
                            </p>
                            <p v-if="!row.access_url && row.access_hint" class="mt-2 text-[12px] leading-relaxed text-[#8A7B6E]">{{ row.access_hint }}</p>
                        </div>
                        <div class="mt-auto flex flex-col gap-2">
                            <a
                                v-if="row.access_url"
                                :href="row.access_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex w-full items-center justify-center gap-1.5 rounded-[12px] bg-[color:var(--mk-accent)] px-3 py-2.5 text-[13px] font-semibold text-white transition hover:brightness-95"
                            >
                                {{ row.access_cta || 'Acessar' }}
                                <MkIcon name="arrow-up-right" :size="14" />
                            </a>
                            <div class="flex gap-2">
                                <button
                                    type="button"
                                    class="flex-1 rounded-[12px] border border-[#E2D7CB] px-3 py-2 text-[12px] font-semibold text-[#3D332B] transition hover:border-[#1A1410]"
                                    @click="openDetails(row)"
                                >
                                    Detalhes
                                </button>
                                <Link
                                    v-if="row.chat_url"
                                    :href="row.chat_url"
                                    class="flex-1 rounded-[12px] border border-[#E2D7CB] px-3 py-2 text-center text-[12px] font-semibold text-[#3D332B] transition hover:border-[#1A1410]"
                                >
                                    Chat
                                </Link>
                                <button
                                    v-if="row.can_request_refund"
                                    type="button"
                                    class="flex-1 rounded-[12px] border border-[#E2D7CB] px-3 py-2 text-[12px] font-semibold text-[#3D332B] transition hover:border-[#1A1410]"
                                    @click="openRefund(row)"
                                >
                                    Reembolso
                                </button>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
        </template>

        <div
            v-else
            class="rounded-[16px] border border-dashed border-[#E2D7CB] bg-white/60 px-6 py-16 text-center"
        >
            <MkIcon name="box" :size="40" class="mx-auto text-[#A3958A]" />
            <p class="mt-4 font-display text-[18px] font-bold text-[#1A1410]">Nenhuma compra por aqui ainda</p>
            <p class="mt-1 text-[14px] text-[#8A7B6E]">Assim que o pagamento for confirmado, o produto e o acesso aparecem nesta página.</p>
            <Link
                href="/buscar"
                class="mt-6 inline-flex items-center gap-2 rounded-[12px] bg-[color:var(--mk-accent)] px-5 py-2.5 text-[14px] font-semibold text-white hover:brightness-95"
            >
                Explorar produtos <MkIcon name="arrow-right" :size="15" />
            </Link>
        </div>

        <Teleport to="body">
            <div
                v-if="detailsOpen && detailsRow"
                class="fixed inset-0 z-[200000] flex items-end justify-center bg-black/50 p-4 sm:items-center"
                role="dialog"
                aria-modal="true"
                @click.self="closeDetails"
            >
                <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-[16px] border border-[#EBE2D8] bg-white p-6 shadow-xl" @click.stop>
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <h2 class="font-display text-[18px] font-bold text-[#1A1410]">{{ detailsRow.product_name }}</h2>
                            <p v-if="detailsRow.product_type_label" class="mt-0.5 text-sm text-[#8A7B6E]">{{ detailsRow.product_type_label }}</p>
                        </div>
                        <button type="button" class="rounded-lg p-1 text-[#8A7B6E] hover:bg-[#F1EAE2]" aria-label="Fechar" @click="closeDetails">✕</button>
                    </div>

                    <dl class="mt-5 space-y-3 text-sm">
                        <div v-if="detailsRow.seller_name" class="flex justify-between gap-4">
                            <dt class="text-[#8A7B6E]">Vendedor</dt>
                            <dd class="text-right font-medium text-[#1A1410]">{{ detailsRow.seller_name }}</dd>
                        </div>
                        <div v-if="detailsRow.purchased_at_label" class="flex justify-between gap-4">
                            <dt class="text-[#8A7B6E]">Data</dt>
                            <dd class="text-right font-medium text-[#1A1410]">{{ detailsRow.purchased_at_label }}</dd>
                        </div>
                        <div v-if="!detailsRow.is_manual_grant" class="flex justify-between gap-4">
                            <dt class="text-[#8A7B6E]">Valor</dt>
                            <dd class="text-right font-medium text-[#1A1410]">{{ formatBRL(detailsRow.amount) }}</dd>
                        </div>
                        <div v-if="detailsRow.payment_method_label" class="flex justify-between gap-4">
                            <dt class="text-[#8A7B6E]">Pagamento</dt>
                            <dd class="text-right font-medium text-[#1A1410]">{{ detailsRow.payment_method_label }}</dd>
                        </div>
                        <div v-if="detailsRow.public_reference" class="flex items-center justify-between gap-4">
                            <dt class="text-[#8A7B6E]">Pedido</dt>
                            <dd class="flex items-center gap-2">
                                <span class="font-medium text-[#1A1410]">#{{ detailsRow.public_reference }}</span>
                                <button type="button" class="rounded-md p-1 text-[#8A7B6E] hover:bg-[#F1EAE2]" @click="copyValue(detailsRow.public_reference, 'ref')">
                                    {{ copiedKey === 'ref' ? '✓' : 'copiar' }}
                                </button>
                            </dd>
                        </div>
                        <div v-if="detailsRow.is_manual_grant" class="flex justify-between gap-4">
                            <dt class="text-[#8A7B6E]">Origem</dt>
                            <dd class="text-right font-medium text-[#1A1410]">Acesso liberado pelo vendedor</dd>
                        </div>
                        <div v-if="detailsRow.refund_status" class="flex justify-between gap-4">
                            <dt class="text-[#8A7B6E]">Reembolso</dt>
                            <dd class="text-right font-medium text-[#1A1410]">{{ refundStatusLabel(detailsRow.refund_status) }}</dd>
                        </div>
                    </dl>

                    <div v-if="detailsRow.shipping" class="mt-5 rounded-[12px] border border-[#EBE2D8] bg-[#FBF7F2] p-4">
                        <p class="text-sm font-semibold text-[#1A1410]">Entrega</p>
                        <p v-for="(line, idx) in detailsRow.shipping.lines" :key="idx" class="mt-1 text-sm text-[#6B5E54]">{{ line }}</p>
                        <p v-if="detailsRow.shipping.delivery_label" class="mt-2 text-xs text-[#8A7B6E]">Prazo estimado: {{ detailsRow.shipping.delivery_label }}</p>
                    </div>

                    <p v-if="detailsRow.access_hint" class="mt-4 text-sm text-[#6B5E54]">{{ detailsRow.access_hint }}</p>

                    <div class="mt-5 flex flex-col gap-2">
                        <a
                            v-if="detailsRow.access_url"
                            :href="detailsRow.access_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex w-full items-center justify-center gap-1.5 rounded-[12px] bg-[color:var(--mk-accent)] px-3 py-2.5 text-sm font-semibold text-white hover:brightness-95"
                        >
                            {{ detailsRow.access_cta || 'Acessar' }}
                        </a>
                        <Link
                            v-if="detailsRow.chat_url"
                            :href="detailsRow.chat_url"
                            class="inline-flex w-full items-center justify-center gap-1.5 rounded-[12px] border border-[#E2D7CB] px-3 py-2.5 text-sm font-semibold text-[#3D332B] hover:border-[#1A1410]"
                        >
                            Abrir chat
                        </Link>
                        <a
                            v-if="detailsRow.support_email"
                            :href="`mailto:${detailsRow.support_email}`"
                            class="inline-flex w-full items-center justify-center gap-1.5 rounded-[12px] border border-[#E2D7CB] px-3 py-2.5 text-sm font-semibold text-[#3D332B] hover:border-[#1A1410]"
                        >
                            Falar com o vendedor
                        </a>
                        <button
                            v-if="detailsRow.can_request_refund"
                            type="button"
                            class="inline-flex w-full items-center justify-center rounded-[12px] border border-[#E2D7CB] px-3 py-2.5 text-sm font-semibold text-[#3D332B] hover:border-[#1A1410]"
                            @click="openRefund(detailsRow); closeDetails()"
                        >
                            Solicitar reembolso
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <Teleport to="body">
            <div
                v-if="refundOpen"
                class="fixed inset-0 z-[200000] flex items-end justify-center bg-black/50 p-4 sm:items-center"
                role="dialog"
                aria-modal="true"
                @click.self="closeRefund"
            >
                <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-[16px] border border-[#EBE2D8] bg-white p-6 shadow-xl" @click.stop>
                    <div class="flex items-start justify-between gap-4">
                        <h2 class="font-display text-[18px] font-bold text-[#1A1410]">Solicitar reembolso</h2>
                        <button type="button" class="rounded-lg p-1 text-[#8A7B6E] hover:bg-[#F1EAE2]" aria-label="Fechar" @click="closeRefund">✕</button>
                    </div>
                    <p class="mt-2 text-sm text-[#6B5E54]">
                        Pedido #{{ refundOrderPublicRef }}. Descreva o motivo; o vendedor será notificado por e-mail.
                    </p>
                    <textarea
                        v-model="refundForm.reason"
                        rows="5"
                        class="mt-4 w-full rounded-[12px] border border-[#E2D7CB] bg-white px-3 py-2 text-sm text-[#1A1410] outline-none focus:border-[#1A1410]"
                        placeholder="Motivo da solicitação"
                    />
                    <p v-if="refundForm.errors.reason" class="mt-1 text-xs text-red-600">{{ refundForm.errors.reason }}</p>
                    <p v-if="refundForm.errors.order_id" class="mt-1 text-xs text-red-600">{{ refundForm.errors.order_id }}</p>
                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" class="rounded-[12px] border border-[#E2D7CB] px-4 py-2 text-sm font-semibold text-[#3D332B]" @click="closeRefund">Cancelar</button>
                        <button
                            type="button"
                            class="rounded-[12px] bg-[color:var(--mk-accent)] px-4 py-2 text-sm font-semibold text-white disabled:opacity-50"
                            :disabled="refundForm.processing"
                            @click="submitRefund"
                        >
                            Enviar solicitação
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </MarketplaceAccountLayout>
</template>
