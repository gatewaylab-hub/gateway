import { computed, watch, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Tema das telas de autenticação — sempre claro.
 */
export function useAuthTheme() {
    const page = usePage();
    const scheme = computed(() => page.props.public_branding?.panel_color_scheme ?? { mode: 'light', locked: true });
    const theme = computed(() => 'light');

    function applyAuthTheme() {
        if (typeof document === 'undefined') {
            return;
        }
        document.documentElement.classList.remove('dark');
    }

    watch(scheme, () => applyAuthTheme(), { deep: true, immediate: true });
    watch(() => page.url, () => applyAuthTheme());

    onMounted(() => {
        applyAuthTheme();
    });

    return {
        scheme,
        theme,
        isDark: computed(() => false),
        isLight: computed(() => true),
    };
}
