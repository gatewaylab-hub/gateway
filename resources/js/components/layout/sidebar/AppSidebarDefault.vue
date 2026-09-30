<script setup>
import { computed, inject, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Bell, PanelRightOpen, X } from 'lucide-vue-next';
import { useSidebar } from '@/composables/useSidebar';
import ConquistasWidget from '@/components/layout/ConquistasWidget.vue';
import AppSidebarNavList from '@/components/layout/sidebar/AppSidebarNavList.vue';
import UserMenu from '@/components/layout/UserMenu.vue';
import LocaleThemeControls from '@/components/layout/LocaleThemeControls.vue';
import AccountManagerCard from '@/components/dashboard/AccountManagerCard.vue';
import { useAppSidebarNav, navPrefetch } from '@/composables/useAppSidebarNav';
import { useI18n } from '@/composables/useI18n';

const { isExpanded, isMobileOpen, toggleSidebar, isMobile, closeMobileSidebarIfOpen } = useSidebar();
const {
    page,
    homeHref,
    appSettings,
    appName,
    hasLogoFull,
    hasLogoIcon,
    navItems,
    isActive,
} = useAppSidebarNav();

const { t } = useI18n();
const openNotificationsPanel = inject('openNotificationsPanel', () => {});
const notificationsUnreadCount = inject('notificationsUnreadCount', { value: 0 });
const unreadBadge = computed(() => Math.max(0, notificationsUnreadCount?.value ?? 0));

const showText = () => isExpanded.value || isMobileOpen.value;
const isSeller = computed(() => !page.props.customer_panel);

const hoverLabel = ref('');
const hoverX = ref(0);
const hoverY = ref(0);

function onItemMouseEnter(event, label) {
    if (showText() || isMobile.value) return;
    hoverLabel.value = label;
    hoverX.value = event.clientX + 14;
    hoverY.value = event.clientY;
}

function onItemMouseMove(event) {
    if (!hoverLabel.value) return;
    hoverX.value = event.clientX + 14;
    hoverY.value = event.clientY;
}

function onItemMouseLeave() {
    hoverLabel.value = '';
}

const accent = () => page.props.marketplaceTheme?.accent || '#FF5A1F';
</script>

<template>
    <aside
        :class="[
            'mk-seller-sidebar fixed left-0 top-0 z-[99999] flex h-screen flex-col transition-all duration-300 ease-in-out',
            {
                'w-[260px] translate-x-0': isMobileOpen,
                '-translate-x-full': !isMobileOpen,
                'pointer-events-none': isMobile && !isMobileOpen,
                'lg:translate-x-0': true,
                'lg:w-[260px]': isExpanded || isMobileOpen,
                'lg:w-[72px]': !isExpanded && !isMobileOpen,
            },
        ]"
    >
        <div
            :class="[
                'flex items-center border-b border-[#EBE2D8] px-4 py-4',
                showText() ? 'justify-between gap-2' : 'lg:justify-center',
            ]"
        >
            <template v-if="showText()">
                <Link
                    :href="homeHref"
                    :prefetch="navPrefetch(isMobile)"
                    class="flex min-w-0 flex-1 cursor-pointer touch-manipulation items-center gap-2.5 overflow-hidden text-[#1A1410]"
                    @click="closeMobileSidebarIfOpen"
                >
                    <template v-if="hasLogoFull()">
                        <img v-if="appSettings().app_logo" :src="appSettings().app_logo" :alt="appName()" class="h-9 max-w-[180px] object-contain object-left" />
                    </template>
                    <template v-else>
                        <svg width="28" height="28" viewBox="0 0 32 32" aria-hidden="true" class="shrink-0">
                            <rect width="32" height="32" rx="9" :fill="accent()" />
                            <path d="M22 11.5A7 7 0 1 0 23 17h-6.5" stroke="#fff" stroke-width="3.2" stroke-linecap="round" fill="none" />
                        </svg>
                        <span class="font-display truncate text-[18px] font-extrabold tracking-[-0.03em]">{{ appName() }}</span>
                    </template>
                </Link>
                <button
                    type="button"
                    class="flex h-8 w-8 shrink-0 touch-manipulation cursor-pointer select-none items-center justify-center rounded-[10px] text-[#8A7B6E] transition-colors hover:bg-[#F1EAE2] hover:text-[#1A1410]"
                    :aria-label="isMobile ? 'Fechar menu' : 'Recolher menu'"
                    @click="toggleSidebar"
                >
                    <X v-if="isMobile" class="h-5 w-5" aria-hidden="true" />
                    <PanelRightOpen v-else class="h-5 w-5" aria-hidden="true" />
                </button>
            </template>
            <button
                v-else
                type="button"
                class="flex h-12 w-12 items-center justify-center rounded-[12px] text-[#3D332B] transition-colors hover:bg-[#F1EAE2]"
                aria-label="Expandir menu"
                @click="toggleSidebar"
            >
                <template v-if="hasLogoIcon()">
                    <img v-if="appSettings().app_logo_icon" :src="appSettings().app_logo_icon" :alt="appName()" class="h-10 w-10 object-contain" />
                </template>
                <svg v-else width="32" height="32" viewBox="0 0 32 32" aria-hidden="true">
                    <rect width="32" height="32" rx="9" :fill="accent()" />
                    <path d="M22 11.5A7 7 0 1 0 23 17h-6.5" stroke="#fff" stroke-width="3.2" stroke-linecap="round" fill="none" />
                </svg>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto overflow-x-visible no-scrollbar px-3 py-4">
            <AppSidebarNavList
                :items="navItems"
                :show-text="showText()"
                :is-active="isActive"
                :is-mobile="isMobile"
                variant="default"
                @item-mouseenter="onItemMouseEnter"
                @item-mousemove="onItemMouseMove"
                @item-mouseleave="onItemMouseLeave"
            />
        </nav>

        <div
            v-if="isSeller"
            class="shrink-0 border-t border-[#EBE2D8] bg-[#FBF7F2]/70"
            :class="showText() ? 'px-3 py-3' : 'px-2 py-3'"
        >
            <div v-if="showText()" class="mb-3">
                <ConquistasWidget variant="sidebar" />
            </div>

            <div
                class="mb-2 flex items-center gap-1"
                :class="showText() ? 'justify-between' : 'flex-col gap-2'"
            >
                <div class="flex items-center gap-0.5" :class="showText() ? '' : 'flex-col'">
                    <AccountManagerCard variant="default" />
                    <LocaleThemeControls
                        language-only
                        variant="default"
                        size="md"
                        drop-up
                        class="[&_button]:text-[#8A7B6E] [&_button]:hover:bg-[#F1EAE2] [&_button]:hover:text-[#1A1410]"
                    />
                    <button
                        type="button"
                        class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-[10px] text-[#8A7B6E] transition-colors hover:bg-[#F1EAE2] hover:text-[#1A1410]"
                        :aria-label="t('header.notifications', 'Notificações')"
                        @click="openNotificationsPanel()"
                    >
                        <Bell class="h-5 w-5" aria-hidden="true" />
                        <span
                            v-if="unreadBadge > 0"
                            class="absolute -right-0.5 -top-0.5 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-[var(--color-primary)] px-1 text-[10px] font-semibold text-white"
                        >
                            {{ unreadBadge > 99 ? '99+' : unreadBadge }}
                        </span>
                    </button>
                </div>
            </div>

            <UserMenu placement="sidebar" :compact="!showText()" drop-up />
        </div>
    </aside>
    <div
        v-if="hoverLabel"
        class="pointer-events-none fixed z-[100001] hidden -translate-y-1/2 whitespace-nowrap rounded-[8px] bg-[var(--color-primary,#FF5A1F)] px-2.5 py-1 text-xs font-medium text-white shadow-lg lg:block"
        :style="{ left: `${hoverX}px`, top: `${hoverY}px` }"
    >
        {{ hoverLabel }}
    </div>
</template>
