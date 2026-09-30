import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Branding público do marketplace (Personalização global).
 * Nome + logo/ícone usados no header/footer da home e checkout.
 */
export function useMarketplaceBranding() {
    const page = usePage();

    const branding = computed(() => {
        const pub = page.props.public_branding;
        const settings = page.props.appSettings;
        if (pub && typeof pub === 'object') return pub;
        if (settings && typeof settings === 'object') return settings;
        return {};
    });

    const appName = computed(
        () => branding.value.app_name
            || page.props.appName
            || 'Gamkon',
    );

    const logoUrl = computed(
        () => branding.value.app_logo
            || branding.value.app_logo_icon
            || '',
    );

    const iconUrl = computed(
        () => branding.value.app_logo_icon
            || branding.value.app_logo
            || '',
    );

    const accent = computed(
        () => page.props.marketplaceTheme?.accent
            || branding.value.theme_primary
            || '#FF5A1F',
    );

    const hasLogo = computed(() => !!logoUrl.value);

    return {
        branding,
        appName,
        logoUrl,
        iconUrl,
        accent,
        hasLogo,
    };
}
