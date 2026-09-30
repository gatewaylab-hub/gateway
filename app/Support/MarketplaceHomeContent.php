<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Str;

class MarketplaceHomeContent
{
    public const KEY_PUBLISHED = 'marketplace_home.published';

    public const KEY_DRAFT = 'marketplace_home.draft';

    public const KEY_PUBLISHED_AT = 'marketplace_home.published_at';

    public const SINGLETON_TYPES = ['hero', 'trust', 'categories', 'deals', 'popular', 'steps', 'recent', 'sellers', 'questions', 'cta'];

    public const ICONS = ['shield', 'bolt', 'chat', 'trophy', 'clock', 'wallet', 'check', 'star', 'user', 'store', 'box'];

    public const MAX_BANNERS = 6;

    public static function defaults(): array
    {
        return [
            'theme' => [
                'accent' => '#FF5A1F',
                'topbar_enabled' => true,
                'topbar_items' => [
                    ['icon' => 'shield', 'text' => 'Pagamento protegido até a entrega'],
                    ['icon' => 'bolt', 'text' => 'Códigos liberados na hora'],
                ],
            ],
            'sections' => array_map(
                fn (string $type) => ['id' => $type, 'type' => $type, 'enabled' => true],
                self::SINGLETON_TYPES
            ),
            'hero' => [
                'badge_mode' => 'live',
                'badge_text' => 'Novidades toda semana',
                'title' => 'Encontre a conta que você procura.',
                'highlight' => 'Venda a que não usa.',
                'subtitle' => 'Contas, créditos, gift cards e assinaturas de vendedores verificados. O pagamento fica retido até você confirmar que recebeu.',
                'search_placeholder' => 'Ex.: conta Valorant, Robux, Netflix',
                'search_button' => 'Buscar',
                'show_quick_tags' => true,
                'quick_tags_label' => 'Populares:',
                'visual' => 'listings',
                'image' => '',
                'trust_card_enabled' => true,
                'trust_card_title' => 'Pagamento retido',
                'trust_card_text' => 'liberado só após a entrega',
            ],
            'trust' => [
                'items' => [
                    ['icon' => 'shield', 'title' => 'Compra garantida', 'text' => 'Reembolso se não receber'],
                    ['icon' => 'bolt', 'title' => 'Entrega automática', 'text' => 'Código liberado na hora'],
                    ['icon' => 'chat', 'title' => 'Chat moderado', 'text' => 'Tudo registrado na plataforma'],
                    ['icon' => 'trophy', 'title' => 'Vendedores com nível', 'text' => 'Reputação por vendas reais'],
                ],
            ],
            'categories' => [
                'title' => 'Categorias populares',
                'link_label' => 'Ver todas',
                'limit' => 8,
            ],
            'deals' => [
                'eyebrow' => 'Termina hoje',
                'title' => 'Ofertas do dia',
                'source' => 'popular',
                'limit' => 4,
                'show_countdown' => true,
            ],
            'popular' => [
                'title' => 'Mais vendidos',
                'subtitle' => 'O que mais saiu nos últimos dias.',
                'limit' => 10,
                'columns' => 5,
            ],
            'steps' => [
                'title' => 'Como a compra funciona',
                'subtitle' => 'Três etapas, com o dinheiro protegido do começo ao fim.',
                'items' => [
                    ['title' => 'Escolha o anúncio', 'text' => 'Compare preço, nível do vendedor e forma de entrega antes de comprar.'],
                    ['title' => 'Pague com proteção', 'text' => 'PIX ou cartão. O valor fica retido pela plataforma, não vai direto ao vendedor.'],
                    ['title' => 'Receba e confirme', 'text' => 'Código na hora ou entrega pelo chat. O vendedor só recebe depois da sua confirmação.'],
                ],
            ],
            'recent' => [
                'title' => 'Recém-chegados',
                'subtitle' => 'Anúncios publicados agora há pouco.',
                'limit' => 8,
                'columns' => 4,
                'badge' => 'Novo',
            ],
            'sellers' => [
                'title' => 'Vendedores em destaque',
                'subtitle' => 'Reputação construída com vendas concluídas.',
                'limit' => 8,
            ],
            'questions' => [
                'title' => 'Perguntas respondidas',
                'limit' => 6,
            ],
            'cta' => [
                'style' => 'accent',
                'title' => "Tem conta parada?\nTransforme em saldo.",
                'bullets' => [
                    'Sem mensalidade, taxa só quando vende',
                    'Saque do saldo via PIX',
                    'Suba de nível a cada venda concluída',
                ],
                'note' => 'Leva menos de 2 minutos',
                'primary_label' => 'Criar conta de vendedor',
                'primary_url' => '/cadastro',
                'secondary_label' => 'Já tenho conta',
                'secondary_url' => '/login',
            ],
        ];
    }

    public static function bannerDefaults(): array
    {
        return [
            'eyebrow' => 'Novidade',
            'title' => 'Seu banner promocional aqui',
            'text' => 'Use este bloco para campanhas, cupons ou lançamentos.',
            'button_label' => 'Ver ofertas',
            'button_url' => '/buscar',
            'image' => '',
            'bg_color' => '#1A1410',
            'tone' => 'light',
            'layout' => 'split',
        ];
    }

    public static function published(): array
    {
        return self::read(self::KEY_PUBLISHED) ?? self::defaults();
    }

    public static function draft(): array
    {
        return self::read(self::KEY_DRAFT) ?? self::published();
    }

    public static function hasPendingDraft(): bool
    {
        $draft = self::read(self::KEY_DRAFT);

        return $draft !== null && $draft != self::published();
    }

    public static function publishedAt(): ?string
    {
        $v = Setting::get(self::KEY_PUBLISHED_AT);

        return is_string($v) && $v !== '' ? $v : null;
    }

    public static function saveDraft(array $input): array
    {
        $clean = self::sanitize($input);
        Setting::set(self::KEY_DRAFT, $clean);

        return $clean;
    }

    public static function publish(?array $input = null): array
    {
        $clean = $input !== null ? self::sanitize($input) : self::draft();
        Setting::set(self::KEY_PUBLISHED, $clean);
        Setting::set(self::KEY_DRAFT, $clean);
        Setting::set(self::KEY_PUBLISHED_AT, now()->toIso8601String());

        return $clean;
    }

    public static function discardDraft(): array
    {
        $published = self::published();
        Setting::set(self::KEY_DRAFT, $published);

        return $published;
    }

    /** Lista pública do tema (cor + top bar), compartilhada com todas as páginas do marketplace. */
    public static function publicTheme(): array
    {
        return self::published()['theme'];
    }

    private static function read(string $key): ?array
    {
        $raw = Setting::get($key);
        if (! is_string($raw) || $raw === '') {
            return null;
        }
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? self::sanitize($decoded) : null;
    }

    public static function sanitize(array $input): array
    {
        $d = self::defaults();
        $out = [];

        $theme = self::arr($input['theme'] ?? null);
        $out['theme'] = [
            'accent' => self::color($theme['accent'] ?? null, $d['theme']['accent']),
            'topbar_enabled' => self::bool($theme['topbar_enabled'] ?? null, true),
            'topbar_items' => self::list($theme['topbar_items'] ?? null, $d['theme']['topbar_items'], 3, fn ($i) => [
                'icon' => self::icon($i['icon'] ?? null),
                'text' => self::str($i['text'] ?? null, '', 80),
            ]),
        ];

        $h = self::arr($input['hero'] ?? null);
        $dh = $d['hero'];
        $out['hero'] = [
            'badge_mode' => self::enum($h['badge_mode'] ?? null, ['live', 'custom', 'off'], $dh['badge_mode']),
            'badge_text' => self::str($h['badge_text'] ?? null, $dh['badge_text'], 60),
            'title' => self::str($h['title'] ?? null, $dh['title'], 120),
            'highlight' => self::str($h['highlight'] ?? null, $dh['highlight'], 80, true),
            'subtitle' => self::str($h['subtitle'] ?? null, $dh['subtitle'], 300, true),
            'search_placeholder' => self::str($h['search_placeholder'] ?? null, $dh['search_placeholder'], 80),
            'search_button' => self::str($h['search_button'] ?? null, $dh['search_button'], 24),
            'show_quick_tags' => self::bool($h['show_quick_tags'] ?? null, true),
            'quick_tags_label' => self::str($h['quick_tags_label'] ?? null, $dh['quick_tags_label'], 30, true),
            'visual' => self::enum($h['visual'] ?? null, ['listings', 'image', 'none'], $dh['visual']),
            'image' => self::url($h['image'] ?? null),
            'trust_card_enabled' => self::bool($h['trust_card_enabled'] ?? null, true),
            'trust_card_title' => self::str($h['trust_card_title'] ?? null, $dh['trust_card_title'], 40),
            'trust_card_text' => self::str($h['trust_card_text'] ?? null, $dh['trust_card_text'], 60, true),
        ];

        $out['trust'] = [
            'items' => self::list(self::arr($input['trust'] ?? null)['items'] ?? null, $d['trust']['items'], 4, fn ($i) => [
                'icon' => self::icon($i['icon'] ?? null),
                'title' => self::str($i['title'] ?? null, '', 40),
                'text' => self::str($i['text'] ?? null, '', 60, true),
            ]),
        ];

        $c = self::arr($input['categories'] ?? null);
        $out['categories'] = [
            'title' => self::str($c['title'] ?? null, $d['categories']['title'], 60),
            'link_label' => self::str($c['link_label'] ?? null, $d['categories']['link_label'], 30, true),
            'limit' => self::int($c['limit'] ?? null, 8, 4, 16),
        ];

        $dl = self::arr($input['deals'] ?? null);
        $out['deals'] = [
            'eyebrow' => self::str($dl['eyebrow'] ?? null, $d['deals']['eyebrow'], 40, true),
            'title' => self::str($dl['title'] ?? null, $d['deals']['title'], 60),
            'source' => self::enum($dl['source'] ?? null, ['popular', 'featured'], 'popular'),
            'limit' => self::int($dl['limit'] ?? null, 4, 2, 8),
            'show_countdown' => self::bool($dl['show_countdown'] ?? null, true),
        ];

        foreach (['popular', 'recent'] as $key) {
            $s = self::arr($input[$key] ?? null);
            $out[$key] = [
                'title' => self::str($s['title'] ?? null, $d[$key]['title'], 60),
                'subtitle' => self::str($s['subtitle'] ?? null, $d[$key]['subtitle'], 140, true),
                'limit' => self::int($s['limit'] ?? null, $d[$key]['limit'], 2, 20),
                'columns' => self::int($s['columns'] ?? null, $d[$key]['columns'], 3, 6),
            ];
        }
        $out['recent']['badge'] = self::str(self::arr($input['recent'] ?? null)['badge'] ?? null, $d['recent']['badge'], 16, true);

        $st = self::arr($input['steps'] ?? null);
        $out['steps'] = [
            'title' => self::str($st['title'] ?? null, $d['steps']['title'], 60),
            'subtitle' => self::str($st['subtitle'] ?? null, $d['steps']['subtitle'], 140, true),
            'items' => self::list($st['items'] ?? null, $d['steps']['items'], 4, fn ($i) => [
                'title' => self::str($i['title'] ?? null, '', 50),
                'text' => self::str($i['text'] ?? null, '', 200, true),
            ]),
        ];

        $se = self::arr($input['sellers'] ?? null);
        $out['sellers'] = [
            'title' => self::str($se['title'] ?? null, $d['sellers']['title'], 60),
            'subtitle' => self::str($se['subtitle'] ?? null, $d['sellers']['subtitle'], 140, true),
            'limit' => self::int($se['limit'] ?? null, 8, 2, 12),
        ];

        $q = self::arr($input['questions'] ?? null);
        $out['questions'] = [
            'title' => self::str($q['title'] ?? null, $d['questions']['title'], 60),
            'limit' => self::int($q['limit'] ?? null, 6, 3, 12),
        ];

        $cta = self::arr($input['cta'] ?? null);
        $dc = $d['cta'];
        $out['cta'] = [
            'style' => self::enum($cta['style'] ?? null, ['accent', 'dark', 'light'], 'accent'),
            'title' => self::str($cta['title'] ?? null, $dc['title'], 120),
            'bullets' => array_values(array_filter(array_map(
                fn ($b) => self::str($b, '', 80),
                array_slice(is_array($cta['bullets'] ?? null) ? $cta['bullets'] : $dc['bullets'], 0, 5)
            ), fn ($b) => $b !== '')),
            'note' => self::str($cta['note'] ?? null, $dc['note'], 60, true),
            'primary_label' => self::str($cta['primary_label'] ?? null, $dc['primary_label'], 40),
            'primary_url' => self::link($cta['primary_url'] ?? null, $dc['primary_url']),
            'secondary_label' => self::str($cta['secondary_label'] ?? null, $dc['secondary_label'], 40, true),
            'secondary_url' => self::link($cta['secondary_url'] ?? null, $dc['secondary_url']),
        ];

        $out['sections'] = self::sanitizeSections($input['sections'] ?? null);

        return $out;
    }

    private static function sanitizeSections(mixed $raw): array
    {
        $defaults = self::defaults()['sections'];
        if (! is_array($raw)) {
            return $defaults;
        }

        $seen = [];
        $banners = 0;
        $sections = [];
        foreach ($raw as $s) {
            if (! is_array($s)) {
                continue;
            }
            $type = $s['type'] ?? null;
            if (in_array($type, self::SINGLETON_TYPES, true)) {
                if (isset($seen[$type])) {
                    continue;
                }
                $seen[$type] = true;
                $sections[] = ['id' => $type, 'type' => $type, 'enabled' => self::bool($s['enabled'] ?? null, true)];
            } elseif ($type === 'banner' && $banners < self::MAX_BANNERS) {
                $banners++;
                $id = is_string($s['id'] ?? null) && preg_match('/^banner-[a-z0-9]{4,16}$/', $s['id'])
                    ? $s['id']
                    : 'banner-'.Str::lower(Str::random(8));
                $data = self::arr($s['data'] ?? null);
                $bd = self::bannerDefaults();
                $sections[] = [
                    'id' => $id,
                    'type' => 'banner',
                    'enabled' => self::bool($s['enabled'] ?? null, true),
                    'data' => [
                        'eyebrow' => self::str($data['eyebrow'] ?? null, '', 40, true),
                        'title' => self::str($data['title'] ?? null, $bd['title'], 100),
                        'text' => self::str($data['text'] ?? null, '', 240, true),
                        'button_label' => self::str($data['button_label'] ?? null, '', 30, true),
                        'button_url' => self::link($data['button_url'] ?? null, '/buscar'),
                        'image' => self::url($data['image'] ?? null),
                        'bg_color' => self::color($data['bg_color'] ?? null, $bd['bg_color']),
                        'tone' => self::enum($data['tone'] ?? null, ['light', 'dark'], 'light'),
                        'layout' => self::enum($data['layout'] ?? null, ['split', 'center', 'cover'], 'split'),
                    ],
                ];
            }
        }

        foreach ($defaults as $def) {
            if (! isset($seen[$def['type']])) {
                $sections[] = array_merge($def, ['enabled' => false]);
            }
        }

        return $sections;
    }

    private static function arr(mixed $v): array
    {
        return is_array($v) ? $v : [];
    }

    private static function str(mixed $v, string $default, int $max, bool $allowEmpty = false): string
    {
        if (! is_string($v)) {
            return $default;
        }
        $v = trim(strip_tags($v));
        if ($v === '' && ! $allowEmpty) {
            return $default;
        }

        return mb_substr($v, 0, $max);
    }

    private static function bool(mixed $v, bool $default): bool
    {
        return is_bool($v) ? $v : (is_numeric($v) ? (bool) $v : $default);
    }

    private static function int(mixed $v, int $default, int $min, int $max): int
    {
        return is_numeric($v) ? max($min, min($max, (int) $v)) : $default;
    }

    private static function enum(mixed $v, array $allowed, string $default): string
    {
        return in_array($v, $allowed, true) ? $v : $default;
    }

    private static function icon(mixed $v): string
    {
        return self::enum($v, self::ICONS, 'check');
    }

    private static function color(mixed $v, string $default): string
    {
        return is_string($v) && preg_match('/^#[0-9A-Fa-f]{6}$/', $v) ? strtoupper($v) : $default;
    }

    private static function url(mixed $v): string
    {
        if (! is_string($v) || ($v = trim($v)) === '') {
            return '';
        }

        return (str_starts_with($v, '/') && ! str_starts_with($v, '//')) || preg_match('#^https?://#i', $v)
            ? mb_substr($v, 0, 500)
            : '';
    }

    private static function link(mixed $v, string $default): string
    {
        $u = self::url($v);

        return $u !== '' ? $u : $default;
    }

    private static function list(mixed $raw, array $default, int $max, callable $map): array
    {
        if (! is_array($raw)) {
            return $default;
        }
        $items = [];
        foreach (array_slice(array_values($raw), 0, $max) as $item) {
            if (is_array($item)) {
                $items[] = $map($item);
            }
        }

        return $items;
    }
}
