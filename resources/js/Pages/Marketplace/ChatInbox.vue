<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import MarketplaceAccountLayout from '@/Layouts/MarketplaceAccountLayout.vue';
import LayoutInfoprodutor from '@/Layouts/LayoutInfoprodutor.vue';
import MkIcon from '@/components/marketplace/MkIcon.vue';

defineProps({
    conversations: { type: Object, required: true },
    use_account_shell: { type: Boolean, default: false },
});

const page = usePage();
const shell = computed(() => page.props.use_account_shell || page.props.customer_panel);
</script>

<template>
    <Head title="Chats" />
    <component :is="shell ? MarketplaceAccountLayout : LayoutInfoprodutor" :title="shell ? 'Chats' : undefined">
        <div :class="shell ? 'space-y-4' : 'mx-auto max-w-3xl space-y-4'">
            <h1 v-if="!shell" class="text-xl font-bold text-zinc-900 dark:text-white">Chats de pedidos</h1>
            <p v-if="shell" class="-mt-2 mb-2 text-[14px] text-[#6B5E54]">Converse com o vendedor sobre seus pedidos.</p>

            <div
                class="divide-y overflow-hidden"
                :class="shell
                    ? 'divide-[#EBE2D8] rounded-[16px] border border-[#EBE2D8] bg-white'
                    : 'divide-zinc-200 rounded-2xl border border-zinc-200 bg-white dark:divide-zinc-700 dark:border-zinc-700 dark:bg-zinc-900'"
            >
                <Link
                    v-for="c in conversations.data"
                    :key="c.id"
                    :href="`/chat/${c.order_id}`"
                    class="flex items-center justify-between gap-3 px-4 py-3.5 transition"
                    :class="shell ? 'hover:bg-[#FBF7F2]' : 'hover:bg-zinc-50 dark:hover:bg-zinc-800'"
                >
                    <div class="min-w-0">
                        <div
                            class="truncate font-semibold"
                            :class="shell ? 'font-display text-[15px] text-[#1A1410]' : 'text-zinc-900 dark:text-white'"
                        >
                            {{ c.product?.name || `Pedido #${c.order_id}` }}
                        </div>
                        <div class="mt-0.5 text-xs" :class="shell ? 'text-[#8A7B6E]' : 'text-zinc-500'">
                            {{ c.buyer?.name }} ↔ {{ c.seller?.name }}
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-2 text-xs" :class="shell ? 'text-[#A3958A]' : 'text-zinc-400'">
                        #{{ c.order_id }}
                        <MkIcon v-if="shell" name="chevron-right" :size="14" />
                    </div>
                </Link>
                <p
                    v-if="!conversations.data?.length"
                    class="px-4 py-12 text-center text-sm"
                    :class="shell ? 'text-[#8A7B6E]' : 'text-zinc-500'"
                >
                    Nenhum chat ainda.
                </p>
            </div>
        </div>
    </component>
</template>
