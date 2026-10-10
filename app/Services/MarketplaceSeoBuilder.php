<?php

namespace App\Services;

use App\Models\MarketplaceCategory;
use App\Models\Product;
use App\Models\User;
use App\Support\HtmlSanitizer;
use App\Support\MarketplaceSeoSettings;
use App\Support\PublicAppUrl;

class MarketplaceSeoBuilder
{
    public function __construct(
        protected StorageService $storage,
    ) {}

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public function home(array $overrides = []): array
    {
        $site = MarketplaceSeoSettings::siteName();
        $title = MarketplaceSeoSettings::homeTitle();
        $description = MarketplaceSeoSettings::homeDescription();
        $canonical = rtrim(PublicAppUrl::base(), '/').'/';
        $image = MarketplaceSeoSettings::defaultOgImageUrl();

        $seo = $this->base([
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'og_type' => 'website',
            'og_title' => $title,
            'og_description' => $description,
            'og_image' => $image,
            'og_url' => $canonical,
            'robots' => 'index, follow',
            'json_ld' => [
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'Organization',
                    'name' => $site,
                    'url' => $canonical,
                    'logo' => $image,
                ],
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'WebSite',
                    'name' => $site,
                    'url' => $canonical,
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => rtrim(PublicAppUrl::base(), '/').'/buscar?q={search_term_string}',
                        'query-input' => 'required name=search_term_string',
                    ],
                ],
            ],
        ]);

        return array_replace($seo, $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    public function category(MarketplaceCategory $category): array
    {
        $site = MarketplaceSeoSettings::siteName();
        $name = trim((string) $category->name);
        $title = $name.' | Comprar e vender na '.$site;
        $description = $this->safeSummary(
            "Compre e venda {$name} com pagamento protegido na {$site}. Anúncios verificados e compra segura."
        );
        $canonical = rtrim(PublicAppUrl::base(), '/').'/categorias/'.$category->slug;
        $image = $category->image
            ? ($this->storage->resolvePublicUrl((string) $category->image) ?: null)
            : MarketplaceSeoSettings::defaultOgImageUrl();

        return $this->base([
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'og_type' => 'website',
            'og_title' => $title,
            'og_description' => $description,
            'og_image' => $image,
            'og_url' => $canonical,
            'robots' => 'index, follow',
            'json_ld' => [[
                '@context' => 'https://schema.org',
                '@type' => 'CollectionPage',
                'name' => $name,
                'description' => $description,
                'url' => $canonical,
            ]],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function product(Product $product, ?string $imageUrl = null, ?float $price = null): array
    {
        $site = MarketplaceSeoSettings::siteName();
        $name = HtmlSanitizer::plainText((string) $product->name, 180);
        $title = $name.' | '.$site;
        $description = $this->safeSummary(
            $product->description
                ? (string) $product->description
                : "Compre {$name} com segurança na {$site}. Pagamento protegido e compra segura pelo marketplace."
        );
        $slug = (string) ($product->slug ?: $product->checkout_slug);
        $canonical = rtrim(PublicAppUrl::base(), '/').'/anuncio/'.$slug;
        $image = $imageUrl
            ?: ($product->image ? ($this->storage->resolvePublicUrl((string) $product->image) ?: null) : null)
            ?: MarketplaceSeoSettings::defaultOgImageUrl();

        $priceValue = $price;
        if ($priceValue === null && $product->price !== null) {
            $priceValue = round((float) $product->price, 2);
        }

        $availability = ($product->is_active && ! $product->admin_blocked)
            ? 'https://schema.org/InStock'
            : 'https://schema.org/OutOfStock';

        $productSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $name,
            'description' => $description,
            'url' => $canonical,
        ];
        if ($image) {
            $productSchema['image'] = [$image];
        }
        if ($priceValue !== null && $priceValue >= 0) {
            $productSchema['offers'] = [
                '@type' => 'Offer',
                'priceCurrency' => 'BRL',
                'price' => number_format($priceValue, 2, '.', ''),
                'availability' => $availability,
                'url' => $canonical,
            ];
        }

        return $this->base([
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'og_type' => 'product',
            'og_title' => $title,
            'og_description' => $description,
            'og_image' => $image,
            'og_url' => $canonical,
            'robots' => 'index, follow',
            'json_ld' => [$productSchema],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function seller(User $seller, array $sellerCard = []): array
    {
        $site = MarketplaceSeoSettings::siteName();
        $username = (string) ($sellerCard['username'] ?? $seller->publicUsername());
        $name = HtmlSanitizer::plainText((string) ($sellerCard['name'] ?? $seller->name ?? $username), 120);
        $title = $name.' (@'.$username.') | '.$site;
        $sales = (int) ($sellerCard['sales_count'] ?? 0);
        $description = $this->safeSummary(
            "Perfil público de {$name} na {$site}. {$sales} vendas e reputação no marketplace."
        );
        $canonical = (string) ($sellerCard['profile_url'] ?? ('/perfil/'.$seller->id.'/'.$username));
        if (! str_starts_with($canonical, 'http')) {
            $canonical = rtrim(PublicAppUrl::base(), '/').$canonical;
        }
        $image = $sellerCard['avatar']
            ?? ($seller->avatar ? ($this->storage->resolvePublicUrl((string) $seller->avatar) ?: null) : null)
            ?: MarketplaceSeoSettings::defaultOgImageUrl();

        return $this->base([
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'og_type' => 'profile',
            'og_title' => $title,
            'og_description' => $description,
            'og_image' => $image,
            'og_url' => $canonical,
            'robots' => 'index, follow',
            'json_ld' => [[
                '@context' => 'https://schema.org',
                '@type' => 'ProfilePage',
                'mainEntity' => [
                    '@type' => 'Person',
                    'name' => $name,
                    'url' => $canonical,
                    'image' => $image,
                ],
            ]],
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function base(array $data): array
    {
        $site = MarketplaceSeoSettings::siteName();

        return [
            'title' => $data['title'] ?? $site,
            'description' => $data['description'] ?? '',
            'canonical' => $data['canonical'] ?? rtrim(PublicAppUrl::base(), '/'),
            'robots' => $data['robots'] ?? 'index, follow',
            'og_type' => $data['og_type'] ?? 'website',
            'og_site_name' => $site,
            'og_locale' => 'pt_BR',
            'og_title' => $data['og_title'] ?? ($data['title'] ?? $site),
            'og_description' => $data['og_description'] ?? ($data['description'] ?? ''),
            'og_image' => $data['og_image'] ?? MarketplaceSeoSettings::defaultOgImageUrl(),
            'og_url' => $data['og_url'] ?? ($data['canonical'] ?? rtrim(PublicAppUrl::base(), '/')),
            'twitter_card' => 'summary_large_image',
            'twitter_title' => $data['og_title'] ?? ($data['title'] ?? $site),
            'twitter_description' => $data['og_description'] ?? ($data['description'] ?? ''),
            'twitter_image' => $data['og_image'] ?? MarketplaceSeoSettings::defaultOgImageUrl(),
            'json_ld' => $data['json_ld'] ?? [],
        ];
    }

    private function safeSummary(string $text, int $max = 160): string
    {
        $clean = HtmlSanitizer::plainTextMultiline($text, 2000);
        $clean = preg_replace('/\s+/u', ' ', $clean) ?? $clean;
        $clean = trim($clean);

        // Remove contatos externos óbvios dos metadados públicos.
        $clean = preg_replace('/[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}/i', '', $clean) ?? $clean;
        $clean = preg_replace('/(?:\+?55)?\s*\(?\d{2}\)?\s*\d{4,5}[-\s]?\d{4}/', '', $clean) ?? $clean;
        $clean = preg_replace('/\b(whatsapp|wa\.me|telegram|t\.me|instagram|discord)\b/i', '', $clean) ?? $clean;
        $clean = preg_replace('/https?:\/\/\S+/i', '', $clean) ?? $clean;
        $clean = preg_replace('/\s+/u', ' ', $clean) ?? $clean;
        $clean = trim($clean);

        if (mb_strlen($clean) > $max) {
            $clean = rtrim(mb_substr($clean, 0, $max - 1)).'…';
        }

        return $clean;
    }
}
