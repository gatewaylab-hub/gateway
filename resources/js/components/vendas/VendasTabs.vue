<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { AlertTriangle, CircleDollarSign, RotateCcw } from 'lucide-vue-next';
import { useI18n } from '@/composables/useI18n';

const page = usePage();
const { t } = useI18n();

const medOpenCount = computed(() => Number(page.props.med_open_count ?? 0));

const isVendas = computed(() => {
    const url = page.url.split('?')[0];
    return url === '/vendas' || (
        url.startsWith('/vendas')
        && !url.startsWith('/vendas/assinaturas')
        && !url.startsWith('/vendas/disputas')
        && !url.startsWith('/vendas/reembolsos')
    );
});

const isDisputas = computed(() => page.url.split('?')[0].startsWith('/vendas/disputas'));
const isReembolsos = computed(() => page.url.split('?')[0].startsWith('/vendas/reembolsos'));

const navClass = 'mk-seller-subnav inline-flex flex-wrap gap-1 rounded-[12px] p-1';

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
        :aria-label="t('sidebar.sales', 'Vendas')"
    >
        <Link href="/vendas" :class="linkClass(isVendas)">
            <CircleDollarSign class="h-4 w-4 shrink-0" aria-hidden="true" />
            {{ t('sales.tab_sales', 'Vendas') }}
        </Link>
        <Link href="/vendas/disputas" :class="linkClass(isDisputas)">
            <AlertTriangle class="h-4 w-4 shrink-0" aria-hidden="true" />
            Disputas MED
            <span
                v-if="medOpenCount > 0"
                class="inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-[color:var(--mk-accent,#FF5A1F)] px-1.5 py-0.5 text-[10px] font-semibold leading-none text-white"
            >
                {{ medOpenCount > 99 ? '99+' : medOpenCount }}
            </span>
        </Link>
        <Link href="/vendas/reembolsos" :class="linkClass(isReembolsos)">
            <RotateCcw class="h-4 w-4 shrink-0" aria-hidden="true" />
            Reembolsos
        </Link>
    </nav>
</template>
