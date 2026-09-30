<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MarketplaceLayout from '@/Layouts/MarketplaceLayout.vue';
import ProductCard from '@/components/marketplace/ProductCard.vue';

defineProps({
    category: { type: Object, required: true },
    products: { type: Object, required: true },
});
</script>

<template>
    <Head :title="category.name" />
    <MarketplaceLayout>
        <div class="mx-auto max-w-7xl px-4 py-8">
            <nav class="mb-4 text-xs text-zinc-500">
                <Link href="/" class="hover:text-[#E85D04]">Início</Link> › {{ category.name }}
            </nav>
            <h1 class="mb-6 text-2xl font-extrabold text-zinc-900">{{ category.name }}</h1>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <ProductCard v-for="p in products.data" :key="p.id" :product="p" />
            </div>
            <p v-if="!products.data?.length" class="mt-8 text-center text-zinc-500">Nenhum anúncio nesta categoria.</p>
        </div>
    </MarketplaceLayout>
</template>
