<script setup>
import { Head } from '@inertiajs/vue3';
import MarketplaceLayout from '@/Layouts/MarketplaceLayout.vue';
import ProductCard from '@/components/marketplace/ProductCard.vue';

defineProps({
    q: { type: String, default: '' },
    products: { type: Object, required: true },
});
</script>

<template>
    <Head title="Buscar" />
    <MarketplaceLayout>
        <div class="mx-auto max-w-7xl px-4 py-8">
            <h1 class="mb-2 text-2xl font-extrabold text-zinc-900">Busca</h1>
            <p class="mb-6 text-sm text-zinc-500">Resultados para “{{ q || 'todos' }}”</p>
            <form action="/buscar" method="get" class="mb-6">
                <input
                    type="search"
                    name="q"
                    :value="q"
                    class="w-full max-w-xl rounded-full border border-orange-200 bg-white px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-orange-300"
                    placeholder="Buscar..."
                />
            </form>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <ProductCard v-for="p in products.data" :key="p.id" :product="p" />
            </div>
            <p v-if="!products.data?.length" class="mt-8 text-center text-zinc-500">Nenhum resultado.</p>
        </div>
    </MarketplaceLayout>
</template>
