<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import LayoutPlatform from '@/Layouts/LayoutPlatform.vue';

const props = defineProps({
    categories: { type: Array, default: () => [] },
});

const createForm = useForm({
    name: '',
    slug: '',
    sort_order: 0,
    is_active: true,
});

function create() {
    createForm.post('/plataforma/categorias-marketplace', {
        onSuccess: () => createForm.reset(),
    });
}

function save(cat) {
    router.put(`/plataforma/categorias-marketplace/${cat.id}`, {
        name: cat.name,
        slug: cat.slug,
        sort_order: cat.sort_order,
        is_active: cat.is_active,
    }, { preserveScroll: true });
}

function remove(cat) {
    if (!confirm(`Remover categoria ${cat.name}?`)) return;
    router.delete(`/plataforma/categorias-marketplace/${cat.id}`, { preserveScroll: true });
}

const editable = ref(props.categories.map((c) => ({ ...c })));
</script>

<template>
    <Head title="Categorias Marketplace" />
    <LayoutPlatform>
        <div class="mx-auto max-w-4xl space-y-6">
            <h1 class="text-xl font-bold text-zinc-900 dark:text-white">Categorias do marketplace</h1>

            <div class="rounded-2xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-3 font-semibold">Nova categoria</h2>
                <div class="grid gap-3 sm:grid-cols-3">
                    <input v-model="createForm.name" placeholder="Nome" class="rounded-xl border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-950" />
                    <input v-model="createForm.slug" placeholder="slug (opcional)" class="rounded-xl border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-950" />
                    <input v-model.number="createForm.sort_order" type="number" placeholder="Ordem" class="rounded-xl border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-950" />
                </div>
                <button type="button" class="mt-3 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white" @click="create">Criar</button>
            </div>

            <div class="space-y-3">
                <div
                    v-for="cat in editable"
                    :key="cat.id"
                    class="flex flex-wrap items-center gap-2 rounded-2xl border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900"
                >
                    <input v-model="cat.name" class="flex-1 rounded-lg border border-zinc-300 px-2 py-1 text-sm dark:border-zinc-600 dark:bg-zinc-950" />
                    <input v-model="cat.slug" class="w-40 rounded-lg border border-zinc-300 px-2 py-1 text-sm dark:border-zinc-600 dark:bg-zinc-950" />
                    <input v-model.number="cat.sort_order" type="number" class="w-20 rounded-lg border border-zinc-300 px-2 py-1 text-sm dark:border-zinc-600 dark:bg-zinc-950" />
                    <label class="flex items-center gap-1 text-xs"><input v-model="cat.is_active" type="checkbox" /> Ativa</label>
                    <button type="button" class="rounded-lg bg-zinc-900 px-3 py-1 text-xs font-semibold text-white dark:bg-white dark:text-zinc-900" @click="save(cat)">Salvar</button>
                    <button type="button" class="rounded-lg bg-red-600 px-3 py-1 text-xs font-semibold text-white" @click="remove(cat)">Excluir</button>
                </div>
            </div>
        </div>
    </LayoutPlatform>
</template>
