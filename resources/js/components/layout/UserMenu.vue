<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { panelNavPrefetch } from '@/composables/useAppSidebarNav';

const props = defineProps({
    /** header = barra superior; sidebar = rodapé da sidebar */
    placement: { type: String, default: 'header' },
    /** Só avatar (sidebar recolhida) */
    compact: { type: Boolean, default: false },
    /** Menu abre para cima */
    dropUp: { type: Boolean, default: false },
});

const page = usePage();
const dropdownOpen = ref(false);
const dropdownRef = ref(null);

const isSidebar = computed(() => props.placement === 'sidebar');

const user = computed(() => page.props.auth?.user ?? null);
const isPlatformAdmin = computed(() => !!page.props.auth?.is_platform_admin);
const customerPanel = computed(() => !!page.props.customer_panel);
const panelSwitch = computed(() => user.value?.panel_switch ?? {});

const isCustomerAreaPath = computed(() => {
    const path = (page.url || '').replace(/^\//, '').split('?')[0];
    return (
        path === 'area-membros'
        || path.startsWith('area-membros/')
        || path === 'painel-cliente'
        || path.startsWith('painel-cliente/')
    );
});

const isClienteRole = computed(() => {
    const r = user.value?.role;
    return r === 'cliente' || r === 'aluno';
});

/** Compras/aluno: mostrar no painel do vendedor mesmo se panel_switch vier incompleto do backend. */
const showPainelAluno = computed(() => {
    if (!user.value || isPlatformAdmin.value) return false;
    if (customerPanel.value) return false;
    if (panelSwitch.value?.customer) return true;
    const r = user.value.role;
    return r === 'infoprodutor' || r === 'team';
});

/** Virar / voltar ao painel do vendedor em área de comprador. */
const showPainelInfoprodutor = computed(() => {
    if (!user.value || isPlatformAdmin.value) return false;
    if (!(customerPanel.value || isCustomerAreaPath.value)) return false;
    if (panelSwitch.value?.seller) return true;
    const r = user.value.role;
    return r === 'infoprodutor' || r === 'team';
});

const sellerPanelButtonTitle = computed(() =>
    isClienteRole.value ? 'Virar vendedor' : 'Painel do vendedor'
);

const sellerPanelButtonSubtitle = computed(() =>
    isClienteRole.value
        ? 'Cadastro e verificação (KYC)'
        : 'Dashboard, vendas e anúncios'
);

function switchToCustomer() {
    router.post('/painel/trocar', { to: 'customer' });
}

function switchToSeller() {
    router.post('/painel/trocar', { to: 'seller' });
}

const initials = computed(() => {
    if (!user.value?.name) return '?';
    const parts = user.value.name.trim().split(/\s+/);
    if (parts.length >= 2) {
        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }
    return (parts[0][0] || '?').toUpperCase();
});

function toggleDropdown() {
    dropdownOpen.value = !dropdownOpen.value;
}

function closeDropdown() {
    dropdownOpen.value = false;
}

function handleClickOutside(event) {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        closeDropdown();
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

</script>

<template>
    <div v-if="user" ref="dropdownRef" class="relative" :class="isSidebar && !compact ? 'w-full' : ''">
        <button
            type="button"
            class="flex items-center gap-2 text-left text-sm transition-colors"
            :class="[
                isSidebar
                    ? (compact
                        ? 'mx-auto justify-center rounded-[12px] p-1.5 hover:bg-[#F1EAE2]'
                        : 'w-full rounded-[12px] border border-[#EBE2D8] bg-white px-2.5 py-2 hover:bg-[#F1EAE2]')
                    : 'rounded-lg px-2 py-1.5 hover:bg-zinc-200/60',
            ]"
            @click.prevent="toggleDropdown"
        >
            <span
                class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full bg-[var(--color-primary)] text-xs font-medium text-white"
            >
                <img
                    v-if="user.avatar_url"
                    :src="user.avatar_url"
                    :alt="user.name"
                    class="h-full w-full object-cover"
                />
                <span v-else>{{ initials }}</span>
            </span>
            <span
                v-if="!compact"
                class="min-w-0 flex-1 truncate font-medium text-[#1A1410]"
                :class="isSidebar ? 'block' : 'hidden sm:block max-w-[120px]'"
            >
                {{ user.name }}
            </span>
            <svg
                v-if="!compact"
                class="h-4 w-4 shrink-0 text-[#8A7B6E] transition-transform"
                :class="{ 'rotate-180': dropdownOpen }"
                viewBox="0 0 20 20"
                fill="currentColor"
                aria-hidden="true"
            >
                <path
                    fill-rule="evenodd"
                    d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                    clip-rule="evenodd"
                />
            </svg>
        </button>

        <div
            v-if="dropdownOpen"
            class="absolute z-[100002] flex w-[min(100vw-2rem,18rem)] flex-col rounded-xl border border-[#EBE2D8] bg-white p-3 shadow-lg"
            :class="[
                dropUp ? 'bottom-full mb-2' : 'top-full mt-2',
                isSidebar && !compact ? 'left-0 right-0 w-full' : 'right-0',
            ]"
        >
            <div class="border-b border-zinc-200 pb-3 dark:border-zinc-800">
                <p class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">
                    {{ user.name }}
                </p>
                <p class="mt-0.5 truncate text-xs text-zinc-500 dark:text-zinc-400">
                    {{ user.email }}
                </p>
            </div>
            <button
                v-if="showPainelAluno"
                type="button"
                class="mt-2 flex w-full flex-col items-start gap-0.5 rounded-lg px-3 py-2 text-left text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800"
                @click="switchToCustomer(); closeDropdown()"
            >
                <span>Minhas compras</span>
                <span class="text-xs font-normal text-zinc-500 dark:text-zinc-400">Compras e chats</span>
            </button>
            <button
                v-if="showPainelInfoprodutor"
                type="button"
                class="mt-2 flex w-full flex-col items-start gap-0.5 rounded-lg px-3 py-2 text-left text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800"
                @click="switchToSeller(); closeDropdown()"
            >
                <span>{{ sellerPanelButtonTitle }}</span>
                <span class="text-xs font-normal text-zinc-500 dark:text-zinc-400">{{ sellerPanelButtonSubtitle }}</span>
            </button>
            <Link
                v-if="!isPlatformAdmin && (user.role === 'infoprodutor' || user.role === 'admin' || user.role === 'team')"
                href="/meu-perfil"
                :prefetch="panelNavPrefetch"
                class="mt-2 flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800"
                @click="closeDropdown"
            >
                Meu perfil
            </Link>
            <Link
                v-if="isPlatformAdmin"
                href="/plataforma/meu-perfil"
                :prefetch="panelNavPrefetch"
                class="mt-2 flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800"
                @click="closeDropdown"
            >
                Meu perfil
            </Link>
            <Link
                v-if="isPlatformAdmin"
                href="/plataforma/logout"
                method="post"
                as="button"
                class="mt-1 flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800"
                :on-finish="() => { window.location.href = '/plataforma/login'; }"
                @click="closeDropdown"
            >
                Sair
            </Link>
            <Link
                v-else
                href="/logout"
                method="post"
                as="button"
                class="mt-1 flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800"
                :on-finish="() => { window.location.href = '/'; }"
                @click="closeDropdown"
            >
                Sair
            </Link>
        </div>
    </div>
</template>
