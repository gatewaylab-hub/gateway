<?php

namespace App\Support;

use App\Models\Setting;
use App\Services\StorageService;

/**
 * SEO institucional, Analytics e redes sociais do marketplace (config global).
 */
final class MarketplaceSeoSettings
{
    public const KEY_HOME_TITLE = 'marketplace_seo_home_title';

    public const KEY_HOME_DESCRIPTION = 'marketplace_seo_home_description';

    public const KEY_DEFAULT_OG_IMAGE = 'marketplace_seo_default_og_image';

    public const KEY_GOOGLE_ANALYTICS_ID = 'marketplace_seo_google_analytics_id';

    public const KEY_GOOGLE_TAG_MANAGER_ID = 'marketplace_seo_google_tag_manager_id';

    public const KEY_SOCIAL_INSTAGRAM = 'marketplace_seo_social_instagram';

    public const KEY_SOCIAL_FACEBOOK = 'marketplace_seo_social_facebook';

    public const KEY_SOCIAL_X = 'marketplace_seo_social_x';

    public const KEY_SOCIAL_YOUTUBE = 'marketplace_seo_social_youtube';

    public const KEY_SOCIAL_TIKTOK = 'marketplace_seo_social_tiktok';

    public const KEY_SOCIAL_DISCORD = 'marketplace_seo_social_discord';

    public static function siteName(): string
    {
        $name = trim((string) config('getfy.app_name', ''));

        return $name !== '' ? $name : 'Gamkon';
    }

    public static function homeTitle(): string
    {
        $stored = trim((string) Setting::get(self::KEY_HOME_TITLE, '', null));
        if ($stored !== '') {
            return $stored;
        }

        return self::siteName().' — Compre e venda com segurança';
    }

    public static function homeDescription(): string
    {
        $stored = HtmlSanitizer::plainTextMultiline(
            (string) Setting::get(self::KEY_HOME_DESCRIPTION, '', null),
            320
        );
        if ($stored !== '') {
            return $stored;
        }

        return 'Compre e venda contas, créditos e assinaturas com pagamento protegido na '.self::siteName().'.';
    }

    public static function defaultOgImagePath(): string
    {
        return trim((string) Setting::get(self::KEY_DEFAULT_OG_IMAGE, '', null));
    }

    public static function defaultOgImageUrl(): ?string
    {
        $path = self::defaultOgImagePath();
        if ($path === '') {
            return null;
        }

        try {
            $url = app(StorageService::class)->resolvePublicUrl($path);

            return $url !== '' ? $url : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public static function googleAnalyticsId(): string
    {
        return trim((string) Setting::get(self::KEY_GOOGLE_ANALYTICS_ID, '', null));
    }

    public static function googleTagManagerId(): string
    {
        return trim((string) Setting::get(self::KEY_GOOGLE_TAG_MANAGER_ID, '', null));
    }

    /**
     * @return array<string, string>
     */
    public static function socialLinks(): array
    {
        $map = [
            'instagram' => self::KEY_SOCIAL_INSTAGRAM,
            'facebook' => self::KEY_SOCIAL_FACEBOOK,
            'x' => self::KEY_SOCIAL_X,
            'youtube' => self::KEY_SOCIAL_YOUTUBE,
            'tiktok' => self::KEY_SOCIAL_TIKTOK,
            'discord' => self::KEY_SOCIAL_DISCORD,
        ];

        $out = [];
        foreach ($map as $name => $key) {
            $url = trim((string) Setting::get($key, '', null));
            if ($url !== '' && filter_var($url, FILTER_VALIDATE_URL)) {
                $out[$name] = $url;
            }
        }

        return $out;
    }

    /**
     * Payload público compartilhado no Inertia / rodapé.
     *
     * @return array<string, mixed>
     */
    public static function publicPayload(): array
    {
        return [
            'site_name' => self::siteName(),
            'home_title' => self::homeTitle(),
            'home_description' => self::homeDescription(),
            'default_og_image' => self::defaultOgImageUrl(),
            'google_analytics_id' => self::googleAnalyticsId(),
            'google_tag_manager_id' => self::googleTagManagerId(),
            'social' => self::socialLinks(),
            'sitemap_url' => rtrim(PublicAppUrl::base(), '/').'/sitemap.xml',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function forSettingsForm(): array
    {
        return [
            'seo_home_title' => trim((string) Setting::get(self::KEY_HOME_TITLE, '', null)),
            'seo_home_description' => trim((string) Setting::get(self::KEY_HOME_DESCRIPTION, '', null)),
            'seo_default_og_image' => self::defaultOgImagePath(),
            'seo_default_og_image_url' => self::defaultOgImageUrl(),
            'seo_google_analytics_id' => self::googleAnalyticsId(),
            'seo_google_tag_manager_id' => self::googleTagManagerId(),
            'seo_social_instagram' => trim((string) Setting::get(self::KEY_SOCIAL_INSTAGRAM, '', null)),
            'seo_social_facebook' => trim((string) Setting::get(self::KEY_SOCIAL_FACEBOOK, '', null)),
            'seo_social_x' => trim((string) Setting::get(self::KEY_SOCIAL_X, '', null)),
            'seo_social_youtube' => trim((string) Setting::get(self::KEY_SOCIAL_YOUTUBE, '', null)),
            'seo_social_tiktok' => trim((string) Setting::get(self::KEY_SOCIAL_TIKTOK, '', null)),
            'seo_social_discord' => trim((string) Setting::get(self::KEY_SOCIAL_DISCORD, '', null)),
            'seo_sitemap_url' => rtrim(PublicAppUrl::base(), '/').'/sitemap.xml',
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function persistFromValidated(array $validated): void
    {
        $stringKeys = [
            'seo_home_title' => self::KEY_HOME_TITLE,
            'seo_google_analytics_id' => self::KEY_GOOGLE_ANALYTICS_ID,
            'seo_google_tag_manager_id' => self::KEY_GOOGLE_TAG_MANAGER_ID,
            'seo_social_instagram' => self::KEY_SOCIAL_INSTAGRAM,
            'seo_social_facebook' => self::KEY_SOCIAL_FACEBOOK,
            'seo_social_x' => self::KEY_SOCIAL_X,
            'seo_social_youtube' => self::KEY_SOCIAL_YOUTUBE,
            'seo_social_tiktok' => self::KEY_SOCIAL_TIKTOK,
            'seo_social_discord' => self::KEY_SOCIAL_DISCORD,
        ];

        foreach ($stringKeys as $formKey => $settingKey) {
            if (! array_key_exists($formKey, $validated)) {
                continue;
            }
            $value = trim((string) ($validated[$formKey] ?? ''));
            if (str_starts_with($formKey, 'seo_google_')) {
                $value = preg_replace('/\s+/', '', $value) ?? '';
            }
            Setting::set($settingKey, $value, null);
        }

        if (array_key_exists('seo_home_description', $validated)) {
            Setting::set(
                self::KEY_HOME_DESCRIPTION,
                HtmlSanitizer::plainTextMultiline((string) ($validated['seo_home_description'] ?? ''), 320),
                null
            );
        }

        if (array_key_exists('seo_default_og_image', $validated)) {
            Setting::set(
                self::KEY_DEFAULT_OG_IMAGE,
                trim((string) ($validated['seo_default_og_image'] ?? '')),
                null
            );
        }
    }
}
