<?php

namespace Database\Seeders;

use App\Models\MarketplaceCategory;
use App\Models\Product;
use App\Models\ProductDeliveryCode;
use App\Models\User;
use Illuminate\Database\Seeder;

class LocalMarketplaceDemoSeeder extends Seeder
{
    public function run(): void
    {
        $seller = User::query()
            ->where('email', LocalDevAccessSeeder::EMAIL)
            ->where('role', User::ROLE_INFOPRODUTOR)
            ->first();

        if (! $seller) {
            $this->command?->error('Seller local não encontrado. Rode LocalDevAccessSeeder antes.');

            return;
        }

        $tenantId = (int) ($seller->tenant_id ?: $seller->id);
        $cats = MarketplaceCategory::query()->get()->keyBy('slug');

        // checkout_slug max 16 chars
        $samples = [
            [
                'name' => 'Duolingo Super 30 dias — cupom global',
                'slug' => 'duolingo-super',
                'category' => 'assinaturas-e-premium',
                'price' => 14.90,
                'delivery_mode' => Product::DELIVERY_BOTH,
                'warranty_text' => '7 dias',
                'region_text' => 'Global',
                'description' => "Ativação na sua conta.\n\n• Acesso Super por 30 dias\n• Entrega automática ou via chat\n• Ativação global\n\nApós a compra, o código aparece no chat automaticamente.",
                'codes' => ['DUO-DEMO-AAAA-1111', 'DUO-DEMO-BBBB-2222', 'DUO-DEMO-CCCC-3333'],
            ],
            [
                'name' => 'Conta Steam Offline | Entrega rápida',
                'slug' => 'steam-offline',
                'category' => 'steam',
                'price' => 29.90,
                'delivery_mode' => Product::DELIVERY_AUTOMATIC,
                'warranty_text' => '30 dias',
                'region_text' => 'Brasil',
                'description' => "Contas Steam offline para jogar mais pagando menos.\n\n• Entrega automática\n• Suporte via chat\n• Produto vitalício (enquanto a conta existir)",
                'codes' => [
                    "login:steam_demo_01\nsenha:DemoSteam#01",
                    "login:steam_demo_02\nsenha:DemoSteam#02",
                ],
            ],
            [
                'name' => 'Free Fire — Diamantes (recarga assistida)',
                'slug' => 'ff-diamantes',
                'category' => 'free-fire',
                'price' => 19.90,
                'delivery_mode' => Product::DELIVERY_CHAT,
                'warranty_text' => '24h',
                'region_text' => 'BR',
                'description' => "Recarga assistida de diamantes Free Fire.\n\nApós o pagamento, fale com o vendedor no chat e informe seu ID do jogo.",
                'codes' => [],
            ],
            [
                'name' => 'Roblox Robux — pacote inicial',
                'slug' => 'roblox-robux',
                'category' => 'roblox',
                'price' => 24.90,
                'delivery_mode' => Product::DELIVERY_CHAT,
                'warranty_text' => '48h',
                'region_text' => 'Global',
                'description' => "Pacote de Robux com entrega via chat.\n\nInforme seu usuário Roblox após a compra.",
                'codes' => [],
            ],
            [
                'name' => 'Valorant Points — recarga',
                'slug' => 'valorant-vp',
                'category' => 'valorant',
                'price' => 39.90,
                'delivery_mode' => Product::DELIVERY_BOTH,
                'warranty_text' => '7 dias',
                'region_text' => 'BR',
                'description' => "Recarga de VP Valorant.\n\nPode vir por código automático ou entrega assistida no chat.",
                'codes' => ['VP-DEMO-1000-AAAA', 'VP-DEMO-1000-BBBB'],
            ],
            [
                'name' => 'Minecraft Java — conta premium',
                'slug' => 'minecraft-java',
                'category' => 'minecraft',
                'price' => 49.90,
                'delivery_mode' => Product::DELIVERY_AUTOMATIC,
                'warranty_text' => '15 dias',
                'region_text' => 'Global',
                'description' => "Conta Minecraft Java Premium.\n\nLogin e senha enviados automaticamente após o pagamento.",
                'codes' => [
                    "email:mc_demo_01@example.com\nsenha:MineDemo#01",
                    "email:mc_demo_02@example.com\nsenha:MineDemo#02",
                ],
            ],
            [
                'name' => 'League of Legends — Smurf Level 30',
                'slug' => 'lol-smurf-30',
                'category' => 'league-of-legends',
                'price' => 34.90,
                'delivery_mode' => Product::DELIVERY_CHAT,
                'warranty_text' => '7 dias',
                'region_text' => 'BR',
                'description' => "Conta LoL Smurf nível 30.\n\nEntrega via chat com o vendedor após a confirmação do pagamento.",
                'codes' => [],
            ],
            [
                'name' => 'Netflix Premium 1 mês — compartilhamento',
                'slug' => 'netflix-1m',
                'category' => 'assinaturas-e-premium',
                'price' => 12.90,
                'delivery_mode' => Product::DELIVERY_BOTH,
                'warranty_text' => '30 dias',
                'region_text' => 'BR',
                'description' => "Acesso Netflix Premium por 1 mês.\n\nCódigo/login no chat após a compra.",
                'codes' => ['NF-DEMO-SLOT-01', 'NF-DEMO-SLOT-02', 'NF-DEMO-SLOT-03'],
            ],
        ];

        $created = 0;
        foreach ($samples as $sample) {
            $cat = $cats->get($sample['category']);
            $product = Product::withTrashed()->updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'slug' => $sample['slug'],
                ],
                [
                    'name' => $sample['name'],
                    'description' => $sample['description'],
                    'type' => Product::TYPE_ANUNCIO,
                    'billing_type' => Product::BILLING_ONE_TIME,
                    'delivery_mode' => $sample['delivery_mode'],
                    'warranty_text' => $sample['warranty_text'],
                    'region_text' => $sample['region_text'],
                    'marketplace_category_id' => $cat?->id,
                    'price' => $sample['price'],
                    'currency' => 'BRL',
                    'is_active' => true,
                    'admin_blocked' => false,
                    'approval_status' => Product::APPROVAL_APPROVED,
                    'approval_source' => Product::APPROVAL_SOURCE_MANUAL,
                    'reviewed_at' => now(),
                    'deleted_at' => null,
                ]
            );

            // /c/{slug} exige [a-z0-9]{6,16} — não usar slug com hífen
            $product->ensureValidCheckoutSlug();

            if (! empty($sample['codes'])) {
                ProductDeliveryCode::query()
                    ->where('product_id', $product->id)
                    ->where('status', ProductDeliveryCode::STATUS_AVAILABLE)
                    ->delete();

                foreach ($sample['codes'] as $line) {
                    $code = new ProductDeliveryCode([
                        'product_id' => $product->id,
                        'tenant_id' => $tenantId,
                        'status' => ProductDeliveryCode::STATUS_AVAILABLE,
                    ]);
                    $code->setPayload($line);
                    $code->save();
                }
            }

            $created++;
            $this->command?->info("OK {$product->name} -> /anuncio/{$product->slug} | checkout /c/{$product->checkout_slug}");
        }

        $this->command?->info("{$created} anuncios demo prontos para {$seller->email}.");
    }
}
