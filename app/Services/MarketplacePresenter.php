<?php

namespace App\Services;

use App\Models\MarketplaceCategory;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductQuestion;
use App\Models\User;
use App\Services\StorageService;
use Illuminate\Support\Facades\Schema;

class MarketplacePresenter
{
    public function __construct(
        protected SalesAchievementsService $achievements,
        protected StorageService $storage,
    ) {}

    public function sellerCard(User $seller): array
    {
        $tenantId = (int) ($seller->tenant_id ?: $seller->id);
        $level = $this->achievements->getSellerPublicLevel($tenantId);
        $salesCount = $level['sales_count'];
        $positiveRate = $this->positiveRatingPercent($tenantId);
        $username = $seller->publicUsername();
        $current = $level['level'];

        return [
            'id' => $seller->id,
            'name' => $seller->name,
            'username' => $username,
            // ID garante resolução mesmo sem username cadastrado; slug fica amigável.
            'profile_url' => '/perfil/'.$seller->id.'/'.$username,
            'avatar' => $seller->avatar
                ? ($this->storage->resolvePublicUrl((string) $seller->avatar) ?: null)
                : null,
            'member_since' => $seller->created_at?->format('d/m/Y'),
            'last_seen_at' => $seller->last_seen_at?->toIso8601String(),
            'is_online' => $seller->isOnline(),
            'sales_count' => $salesCount,
            'positive_rating_percent' => $positiveRate,
            'ratings_count' => $salesCount,
            'reputation' => [
                'positive' => (int) round($salesCount * ($positiveRate / 100)),
                'neutral' => 0,
                'negative' => max(0, $salesCount - (int) round($salesCount * ($positiveRate / 100))),
            ],
            'kyc_email' => filled($seller->email_verified_at) || filled($seller->email),
            'kyc_phone' => filled($seller->phone),
            'kyc_documents' => ($seller->kyc_status ?? null) === User::KYC_APPROVED,
            'level' => $current ? [
                'name' => $current['name'] ?? null,
                'slug' => $current['slug'] ?? null,
                'image' => $current['image'] ?? null,
                'threshold' => $current['threshold'] ?? null,
                'description' => $current['description'] ?? null,
            ] : null,
            'next_level' => $level['next_level'] ? [
                'name' => $level['next_level']['name'] ?? null,
                'threshold' => $level['next_level']['threshold'] ?? null,
                'image' => $level['next_level']['image'] ?? null,
            ] : null,
            'progress_percent' => $level['progress_percent'] ?? 0,
            'unlocked_levels' => $level['unlocked_levels'] ?? [],
        ];
    }

    public function productCard(Product $product, ?User $seller = null): array
    {
        $seller = $seller ?: User::query()->find($product->tenant_id);
        $image = $product->image
            ? ($this->storage->resolvePublicUrl((string) $product->image) ?: null)
            : null;

        $listingSlug = (string) ($product->slug ?: $product->checkout_slug);
        $checkoutSlug = Product::normalizeCheckoutSlug($product->checkout_slug);

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $listingSlug,
            'description' => $product->description,
            'price' => (float) $product->price,
            'currency' => $product->currency ?: 'BRL',
            'image' => $image,
            'delivery_mode' => $product->delivery_mode ?? Product::DELIVERY_CHAT,
            'warranty_text' => $product->warranty_text,
            'region_text' => $product->region_text,
            'stock_available' => $product->usesAutomaticDelivery()
                ? $product->availableCodesCount()
                : null,
            'category' => $product->marketplaceCategory ? [
                'id' => $product->marketplaceCategory->id,
                'name' => $product->marketplaceCategory->name,
                'slug' => $product->marketplaceCategory->slug,
            ] : null,
            'seller' => $seller ? $this->sellerCard($seller) : null,
            'url' => '/anuncio/'.$listingSlug,
            'checkout_url' => $checkoutSlug
                ? route('checkout.show', ['slug' => $checkoutSlug])
                : null,
        ];
    }

    public function positiveRatingPercent(int $tenantId): float
    {
        // Placeholder até haver avaliações: assume 98% se houver vendas
        $count = $this->achievements->getValidSalesCount($tenantId);

        return $count > 0 ? 98.0 : 100.0;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function recentAnsweredQuestions(int $limit = 8): array
    {
        if (! Schema::hasTable('product_questions')) {
            return [];
        }

        return ProductQuestion::query()
            ->with(['user:id,name,avatar', 'answer.user:id,name', 'product:id,name,checkout_slug,slug'])
            ->whereNotNull('answered_at')
            ->orderByDesc('answered_at')
            ->limit($limit)
            ->get()
            ->map(fn (ProductQuestion $q) => [
                'id' => $q->id,
                'body' => $q->body,
                'answered_at' => $q->answered_at?->diffForHumans(),
                'user_name' => $q->user?->name,
                'answer' => $q->answer?->body,
                'product_name' => $q->product?->name,
                'product_url' => $q->product
                    ? '/anuncio/'.($q->product->slug ?: $q->product->checkout_slug)
                    : null,
            ])
            ->all();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Product>
     */
    public function activeListingsQuery()
    {
        return Product::query()
            ->with(['marketplaceCategory', 'tenantOwner'])
            ->where('is_active', true)
            ->where('admin_blocked', false)
            ->where(function ($q) {
                $q->where('approval_status', Product::APPROVAL_APPROVED)
                    ->orWhereNull('approval_status');
            })
            ->where(function ($q) {
                $q->where('type', Product::TYPE_ANUNCIO)
                    ->orWhereNotNull('marketplace_category_id');
            });
    }
}
