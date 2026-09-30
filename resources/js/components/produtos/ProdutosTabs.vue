<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Package } from 'lucide-vue-next';
import { useI18n } from '@/composables/useI18n';

const page = usePage();
const { t } = useI18n();

const path = computed(() => page.url.split('?')[0]);

const isProdutos = computed(() => {
    const p = path.value;
    return p === '/produtos' || /^\/produtos\/[^/]+/.test(p);
});

const navClass = 'mk-seller-subnav inline-flex rounded-[12px] p-1';

function linkClass(active) {
    return [
        'mk-seller-subnav-item flex items-center gap-2 rounded-[10px] px-4 py-2.5 text-sm font-semibold transition-all duration-200',
        active ? 'mk-seller-subnav-item-active' : 'hover:text-[#1A1410]',
    ];
}
</script>

<template>
    <nav
        :class="navClass"
        :aria-label="t('sidebar.products', 'Produtos')"
    >
        <Link href="/produtos" :class="linkClass(isProdutos)">
            <Package class="h-4 w-4 shrink-0" aria-hidden="true" />
            {{ t('products.tab_products', 'Produtos') }}
        </Link>
    </nav>
</template>
