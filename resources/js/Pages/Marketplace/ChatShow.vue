<script setup>
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch, nextTick } from 'vue';
import MarketplaceAccountLayout from '@/Layouts/MarketplaceAccountLayout.vue';
import LayoutInfoprodutor from '@/Layouts/LayoutInfoprodutor.vue';
import MkIcon from '@/components/marketplace/MkIcon.vue';

const props = defineProps({
    conversation: { type: Object, required: true },
    pollUrl: { type: String, required: true },
    use_account_shell: { type: Boolean, default: false },
});

const page = usePage();
const shell = computed(() => props.use_account_shell || page.props.customer_panel);

const form = useForm({ body: '' });
const messages = ref([...props.conversation.messages]);
const threadEl = ref(null);

watch(() => props.conversation.messages, (v) => {
    messages.value = [...v];
    nextTick(scrollToBottom);
});

function scrollToBottom() {
    const el = threadEl.value;
    if (el) el.scrollTop = el.scrollHeight;
}

function send() {
    form.post(`/chat/${props.conversation.order_id}`, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('body');
            nextTick(scrollToBottom);
        },
    });
}

let timer = null;
onMounted(() => {
    nextTick(scrollToBottom);
    timer = setInterval(() => {
        router.reload({
            only: ['conversation'],
            preserveScroll: true,
            preserveState: true,
        });
    }, 5000);
});
onUnmounted(() => {
    if (timer) clearInterval(timer);
});
</script>

<template>
    <Head title="Chat do pedido" />
    <component :is="shell ? MarketplaceAccountLayout : LayoutInfoprodutor" :title="shell ? 'Chat' : undefined">
        <div :class="shell ? 'flex flex-col gap-4' : 'mx-auto flex max-w-3xl flex-col gap-4'">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <h1
                        class="truncate font-bold"
                        :class="shell ? 'font-display text-[22px] tracking-[-0.03em] text-[#1A1410]' : 'text-xl text-zinc-900 dark:text-white'"
                    >
                        {{ conversation.product_name || 'Chat do pedido' }}
                    </h1>
                    <p class="text-sm" :class="shell ? 'text-[#8A7B6E]' : 'text-zinc-500'">
                        Pedido #{{ conversation.order_id }} · {{ conversation.buyer_name }} ↔ {{ conversation.seller_name }}
                    </p>
                </div>
                <Link
                    href="/chat"
                    class="inline-flex shrink-0 items-center gap-1 text-sm font-semibold"
                    :class="shell ? 'text-[#3D332B] hover:text-[#1A1410]' : 'text-blue-600'"
                >
                    <MkIcon v-if="shell" name="chevron-left" :size="14" />
                    Voltar
                </Link>
            </div>

            <div
                ref="threadEl"
                class="flex max-h-[60vh] flex-col gap-3 overflow-y-auto p-4"
                :class="shell
                    ? 'rounded-[16px] border border-[#EBE2D8] bg-white'
                    : 'rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900'"
            >
                <div
                    v-for="m in messages"
                    :key="m.id"
                    class="max-w-[85%] rounded-[14px] px-3 py-2 text-sm"
                    :class="m.mine
                        ? (shell ? 'ml-auto bg-[color:var(--mk-accent)] text-white' : 'ml-auto bg-blue-600 text-white')
                        : m.type !== 'user'
                            ? 'bg-amber-50 text-amber-900'
                            : (shell ? 'bg-[#F1EAE2] text-[#1A1410]' : 'bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-100')"
                >
                    <div v-if="!m.mine && m.type === 'user'" class="mb-0.5 text-[10px] font-semibold opacity-70">{{ m.user_name }}</div>
                    <div v-if="m.type === 'delivery'" class="mb-0.5 text-[10px] font-bold uppercase">Entrega automática</div>
                    <div class="whitespace-pre-wrap">{{ m.body }}</div>
                    <div class="mt-1 text-[10px] opacity-60">{{ m.created_human }}</div>
                </div>
                <p v-if="!messages.length" class="py-8 text-center text-sm text-[#8A7B6E]">Nenhuma mensagem ainda. Diga olá.</p>
            </div>

            <div
                class="p-4"
                :class="shell
                    ? 'rounded-[16px] border border-[#EBE2D8] bg-white'
                    : 'rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900'"
            >
                <textarea
                    v-model="form.body"
                    rows="3"
                    class="w-full rounded-[12px] border px-3 py-2 text-sm outline-none"
                    :class="shell
                        ? 'border-[#E2D7CB] bg-white text-[#1A1410] focus:border-[#1A1410]'
                        : 'border-zinc-300 dark:border-zinc-600 dark:bg-zinc-950'"
                    placeholder="Escreva sua mensagem..."
                />
                <p class="mt-2 text-xs text-orange-700">Não envie WhatsApp, Discord, telefone, e-mail ou links externos.</p>
                <p v-if="form.errors.body" class="mt-1 text-xs text-red-600">{{ form.errors.body }}</p>
                <button
                    type="button"
                    class="mt-3 rounded-[12px] px-4 py-2.5 text-sm font-semibold text-white disabled:opacity-50"
                    :class="shell ? 'bg-[color:var(--mk-accent)] hover:brightness-95' : 'bg-blue-600'"
                    :disabled="form.processing || !form.body.trim()"
                    @click="send"
                >
                    Enviar
                </button>
            </div>
        </div>
    </component>
</template>
