import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    CircleDollarSign,
    Wallet,
    Package,
    BarChart3,
    TicketPercent,
    Users,
    Gift,
    MessageCircle,
    HelpCircle,
    Trophy,
    Activity,
} from 'lucide-vue-next';
import { useI18n } from '@/composables/useI18n';

/** Hover only no desktop — no mobile o primeiro toque vira hover e compete com a navegação. */
export const panelNavPrefetch = 'hover';

export function navPrefetch(isMobile) {
    return isMobile ? false : panelNavPrefetch;
}

export function useAppSidebarNav() {
    const page = usePage();
    const { t } = useI18n();

    const homeHref = computed(() => (page.props.customer_panel ? '/painel-cliente' : '/dashboard'));

    const appSettings = () => page.props.appSettings ?? {};
    const appName = () => appSettings().app_name || 'Marketplace';
    const hasLogoFull = () => !!(appSettings().app_logo || appSettings().app_logo_dark);
    const hasLogoIcon = () => !!(appSettings().app_logo_icon || appSettings().app_logo_icon_dark);

    const perms = computed(() => page.props.auth?.permissions ?? {});
    const canView = (key) => {
        const role = page.props.auth?.user?.role;
        if (role === 'admin' || role === 'infoprodutor') return true;
        return !!perms.value?.[key];
    };

    const navItems = computed(() => {
        if (page.props.customer_panel) {
            return [
                { name: 'Minhas compras', href: '/painel-cliente', icon: Package },
                { name: 'Chats', href: '/chat', icon: MessageCircle },
                { name: 'Minha conta', href: '/painel-cliente/conta', icon: Users },
            ];
        }

        const items = [];

        if (canView('dashboard.view')) {
            items.push({ name: t('sidebar.dashboard', 'Dashboard'), href: '/dashboard', icon: LayoutDashboard });
        }

        if (canView('vendas.view')) {
            items.push({
                name: t('sidebar.sales', 'Vendas'),
                href: '/vendas',
                icon: CircleDollarSign,
            });
        }

        if (canView('relatorios.view')) {
            items.push({ name: t('sidebar.reports', 'Relatórios'), href: '/relatorios', icon: BarChart3 });
        }

        if (canView('metrics.view')) {
            items.push({
                name: t('sidebar.metrics', 'Métricas'),
                href: '/metricas',
                icon: Activity,
            });
        }

        items.push({ separator: true });

        if (canView('produtos.view')) {
            items.push({ name: t('sidebar.products', 'Produtos'), href: '/produtos', icon: Package });
            items.push({ name: t('sidebar.coupons', 'Cupons'), href: '/produtos/cupons', icon: TicketPercent });
            items.push({ name: t('sidebar.customers', 'Clientes'), href: '/produtos/clientes', icon: Users });
            items.push({ name: 'Perguntas', href: '/perguntas', icon: HelpCircle });
            items.push({ name: 'Chats', href: '/chat', icon: MessageCircle });
        }

        items.push({ separator: true });

        if (canView('financeiro.view')) {
            items.push({ name: t('sidebar.finance', 'Financeiro'), href: '/financeiro', icon: Wallet });
        }

        items.push({ name: t('sidebar.achievements', 'Conquistas'), href: '/conquistas', icon: Trophy });

        if (page.props.referral_program?.enabled) {
            items.push({
                name: t('sidebar.referral', 'Indique e Ganhe'),
                href: '/indique-e-ganhe',
                icon: Gift,
            });
        }

        if (!page.props.customer_panel) {
            items.push({ separator: true });
            items.push({ pwaInstall: true });
        }

        return items;
    });

    function isActive(href) {
        const url = page.url.split('?')[0];
        if (href === '/dashboard') return url === '/dashboard';
        if (href === '/vendas') {
            return url === '/vendas' || url.startsWith('/vendas/');
        }
        if (href === '/produtos/cupons') {
            return url.startsWith('/produtos/cupons');
        }
        if (href === '/produtos/clientes') {
            return url.startsWith('/produtos/alunos') || url.startsWith('/produtos/clientes');
        }
        if (href === '/perguntas') {
            return url.startsWith('/perguntas');
        }
        if (href === '/chat') {
            return url === '/chat' || url.startsWith('/chat/');
        }
        if (href === '/conquistas') {
            return url.startsWith('/conquistas');
        }
        if (href === '/produtos') {
            if (
                url.startsWith('/produtos/cupons') ||
                url.startsWith('/produtos/alunos') ||
                url.startsWith('/produtos/clientes')
            ) {
                return false;
            }
            return url === '/produtos' || url.startsWith('/produtos/');
        }
        return url === href || url.startsWith(href + '/');
    }

    return {
        page,
        homeHref,
        appSettings,
        appName,
        hasLogoFull,
        hasLogoIcon,
        navItems,
        isActive,
    };
}
