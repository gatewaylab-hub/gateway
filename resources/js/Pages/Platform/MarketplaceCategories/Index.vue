<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import LayoutPlatform from '@/Layouts/LayoutPlatform.vue';

const props = defineProps({
    categories: { type: Array, default: () => [] },
});

const createForm = useForm({
    name: '',
    slug: '',
    sort_order: 0,
    is_active: true,
    image: null,
});

const createPreview = ref(null);

function onCreateImage(e) {
    const file = e.target.files?.[0] || null;
    createForm.image = file;
    createPreview.value = file ? URL.createObjectURL(file) : null;
}

function create() {
    createForm.post('/plataforma/categorias-marketplace', {
        forceFormData: true,
        onSuccess: () => {
            createForm.reset();
            createPreview.value = null;
        },
    });
}

const editable = ref(props.categories.map((c) => ({ ...c, imageFile: null, remove_image: false })));

watch(
    () => props.categories,
    (cats) => {
        editable.value = cats.map((c) => ({ ...c, imageFile: null, remove_image: false }));
    },
    { deep: true }
);

function onEditImage(cat, e) {
    const file = e.target.files?.[0] || null;
    cat.imageFile = file;
    cat.remove_image = false;
    if (file) {
        cat.image = URL.createObjectURL(file);
    }
}

function clearImage(cat) {
    cat.imageFile = null;
    cat.image = null;
    cat.remove_image = true;
}

function save(cat) {
    const fd = new FormData();
    fd.append('_method', 'PUT');
    fd.append('name', cat.name);
    fd.append('slug', cat.slug || '');
    fd.append('sort_order', String(cat.sort_order ?? 0));
    fd.append('is_active', cat.is_active ? '1' : '0');
    if (cat.remove_image) {
        fd.append('remove_image', '1');
    }
    if (cat.imageFile) {
        fd.append('image', cat.imageFile);
    }
    router.post(`/plataforma/categorias-marketplace/${cat.id}`, fd, {
        forceFormData: true,
        preserveScroll: true,
    });
}

function remove(cat) {
    if (!confirm(`Remover categoria ${cat.name}?`)) return;
    router.delete(`/plataforma/categorias-marketplace/${cat.id}`, { preserveScroll: true });
}
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
                <div class="mt-3 flex flex-wrap items-center gap-3">
                    <input type="file" accept="image/*" class="text-sm" @change="onCreateImage" />
                    <img v-if="createPreview" :src="createPreview" alt="" class="h-14 w-14 rounded-xl object-cover" />
                </div>
                <p v-if="createForm.errors.image" class="mt-1 text-sm text-red-600">{{ createForm.errors.image }}</p>
                <button type="button" class="mt-3 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white" @click="create">Criar</button>
            </div>

            <div class="space-y-3">
                <div
                    v-for="cat in editable"
                    :key="cat.id"
                    class="flex flex-wrap items-center gap-2 rounded-2xl border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900"
                >
                    <div class="relative h-14 w-14 shrink-0 overflow-hidden rounded-xl bg-zinc-100 dark:bg-zinc-800">
                        <img v-if="cat.image" :src="cat.image" alt="" class="h-full w-full object-cover" />
                    </div>
                    <input v-model="cat.name" class="min-w-[140px] flex-1 rounded-lg border border-zinc-300 px-2 py-1 text-sm dark:border-zinc-600 dark:bg-zinc-950" />
                    <input v-model="cat.slug" class="w-40 rounded-lg border border-zinc-300 px-2 py-1 text-sm dark:border-zinc-600 dark:bg-zinc-950" />
                    <input v-model.number="cat.sort_order" type="number" class="w-20 rounded-lg border border-zinc-300 px-2 py-1 text-sm dark:border-zinc-600 dark:bg-zinc-950" />
                    <label class="flex items-center gap-1 text-xs"><input v-model="cat.is_active" type="checkbox" /> Ativa</label>
                    <input type="file" accept="image/*" class="max-w-[180px] text-xs" @change="onEditImage(cat, $event)" />
                    <button
                        v-if="cat.image || cat.image_path"
                        type="button"
                        class="rounded-lg border border-zinc-300 px-2 py-1 text-xs dark:border-zinc-600"
                        @click="clearImage(cat)"
                    >
                        Remover imagem
                    </button>
                    <button type="button" class="rounded-lg bg-zinc-900 px-3 py-1 text-xs font-semibold text-white dark:bg-white dark:text-zinc-900" @click="save(cat)">Salvar</button>
                    <button type="button" class="rounded-lg bg-red-600 px-3 py-1 text-xs font-semibold text-white" @click="remove(cat)">Excluir</button>
                </div>
            </div>
        </div>
    </LayoutPlatform>
</template>
