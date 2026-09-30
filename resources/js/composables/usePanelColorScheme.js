import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { usePage } from '@inertiajs/vue3';

const DEFAULT_SCHEME = { mode: 'light', locked: true };

function readCurrentThemeFromDom() {
    if (typeof document === 'undefined') {
        return 'light';
    }

    return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
}

/**
 * Tema do painel — marketplace sempre claro (sem toggle dark).
 */
export function resolvePanelTheme() {
    return 'light';
}

export function usePanelColorScheme() {
    const page = usePage();
    const scheme = computed(() => page.props.public_branding?.panel_color_scheme ?? DEFAULT_SCHEME);
    const showToggler = computed(() => false);
    const theme = ref('light');

    function applyTheme() {
        theme.value = 'light';
        if (typeof document !== 'undefined') {
            document.documentElement.classList.remove('dark');
        }
    }

    function syncFromPolicy() {
        applyTheme();
    }

    function setTheme() {
        // Dark desativado no marketplace.
        applyTheme();
    }

    if (typeof window !== 'undefined') {
        syncFromPolicy();
    }

    watch(scheme, () => syncFromPolicy(), { deep: true });
    watch(() => page.url, () => syncFromPolicy());

    onMounted(() => {
        syncFromPolicy();
    });

    onBeforeUnmount(() => {});

    return {
        scheme,
        showToggler,
        theme,
        setTheme,
        applyTheme: syncFromPolicy,
    };
}
