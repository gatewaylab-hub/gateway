<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceCategory;
use App\Models\Product;
use App\Models\User;
use App\Services\MarketplacePresenter;
use App\Support\PublicAppUrl;
use Illuminate\Http\Response;

class MarketplaceSitemapController extends Controller
{
    public function __construct(
        protected MarketplacePresenter $presenter,
    ) {}

    public function __invoke(): Response
    {
        $base = rtrim(PublicAppUrl::base(), '/');
        $urls = [];

        $urls[] = [
            'loc' => $base.'/',
            'changefreq' => 'daily',
            'priority' => '1.0',
        ];

        $urls[] = [
            'loc' => $base.'/buscar',
            'changefreq' => 'daily',
            'priority' => '0.6',
        ];

        MarketplaceCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['slug', 'updated_at'])
            ->each(function (MarketplaceCategory $c) use (&$urls, $base) {
                $urls[] = [
                    'loc' => $base.'/categorias/'.$c->slug,
                    'lastmod' => optional($c->updated_at)->toAtomString(),
                    'changefreq' => 'daily',
                    'priority' => '0.8',
                ];
            });

        $this->presenter->activeListingsQuery()
            ->orderByDesc('updated_at')
            ->limit(5000)
            ->get(['id', 'slug', 'checkout_slug', 'updated_at'])
            ->each(function (Product $p) use (&$urls, $base) {
                $slug = (string) ($p->slug ?: $p->checkout_slug);
                if ($slug === '') {
                    return;
                }
                $urls[] = [
                    'loc' => $base.'/anuncio/'.$slug,
                    'lastmod' => optional($p->updated_at)->toAtomString(),
                    'changefreq' => 'daily',
                    'priority' => '0.9',
                ];
            });

        User::query()
            ->where('role', User::ROLE_INFOPRODUTOR)
            ->where('account_status', 'approved')
            ->orderBy('id')
            ->limit(2000)
            ->get(['id', 'username', 'name', 'updated_at'])
            ->each(function (User $u) use (&$urls, $base) {
                $username = $u->publicUsername();
                $urls[] = [
                    'loc' => $base.'/perfil/'.$u->id.'/'.$username,
                    'lastmod' => optional($u->updated_at)->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.5',
                ];
            });

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.e($url['loc'])."</loc>\n";
            if (! empty($url['lastmod'])) {
                $xml .= '    <lastmod>'.e($url['lastmod'])."</lastmod>\n";
            }
            if (! empty($url['changefreq'])) {
                $xml .= '    <changefreq>'.e($url['changefreq'])."</changefreq>\n";
            }
            if (! empty($url['priority'])) {
                $xml .= '    <priority>'.e($url['priority'])."</priority>\n";
            }
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
