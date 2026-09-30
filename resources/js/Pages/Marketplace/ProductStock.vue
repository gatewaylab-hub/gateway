<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import LayoutInfoprodutor from '@/Layouts/LayoutInfoprodutor.vue';

const props = defineProps({
    product: { type: Object, required: true },
    codes: { type: Object, required: true },
    available_count: { type: Number, default: 0 },
});

const form = useForm({
    codes: '',
    delivery_mode: props.product.delivery_mode || 'chat',
});

function submit() {
    form.post(`/produtos/${props.product.id}/estoque`, { preserveScroll: true, onSuccess: () => form.reset('codes') });
}
</script>

<template>
    <Head title="Estoque de códigos" />
    <LayoutInfoprodutor>
        <div class="mx-auto max-w-3xl space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold">Estoque — {{ product.name }}</h1>
                    <p class="text-sm text-zinc-500">{{ available_count }} códigos disponíveis</p>
                </div>
                <Link :href="`/produtos/${product.id}/edit`" class="text-sm text-blue-600">Voltar ao produto</Link>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <label class="mb-1 block text-sm font-medium">Modo de entrega</label>
                <select v-model="form.delivery_mode" class="mb-3 w-full rounded-xl border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-950">
                    <option value="chat">Somente chat</option>
                    <option value="automatic">Somente automático</option>
                    <option value="both">Chat + automático</option>
                </select>
                <label class="mb-1 block text-sm font-medium">Adicionar códigos (1 por linha)</label>
                <textarea
                    v-model="form.codes"
                    rows="8"
                    class="w-full rounded-xl border border-zinc-300 px-3 py-2 font-mono text-sm dark:border-zinc-600 dark:bg-zinc-950"
                    placeholder="login:senha&#10;CODIGO-ABC-123"
                />
                <button type="button" class="mt-3 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white" @click="submit">
                    Salvar estoque
                </button>
            </div>

            <div class="overflow-hidden rounded-2xl border border-zinc-200 dark:border-zinc-700">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <th class="px-3 py-2">Preview</th>
                            <th class="px-3 py-2">Status</th>
                            <th class="px-3 py-2">Pedido</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="c in codes.data" :key="c.id" class="border-t border-zinc-100 dark:border-zinc-800">
                            <td class="px-3 py-2 font-mono text-xs">{{ c.preview }}</td>
                            <td class="px-3 py-2">{{ c.status }}</td>
                            <td class="px-3 py-2">{{ c.order_id || '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </LayoutInfoprodutor>
</template>
