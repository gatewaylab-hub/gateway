<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceCategory;
use App\Models\Product;
use App\Models\ProductQuestion;
use App\Models\User;
use App\Services\MarketplacePresenter;
use App\Support\MarketplaceHomeContent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MarketplaceController extends Controller
{
    public function __construct(
        protected MarketplacePresenter $presenter,
    ) {}

    public function home(Request $request): Response
    {
        $categories = MarketplaceCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'slug', 'image', 'icon']);

        $listingCounts = $this->presenter->activeListingsQuery()
            ->toBase()
            ->selectRaw('marketplace_category_id, count(*) as total')
            ->groupBy('marketplace_category_id')
            ->pluck('total', 'marketplace_category_id');

        $categories = $categories->map(fn (MarketplaceCategory $c) => [
            'id' => $c->id,
            'name' => $c->name,
            'slug' => $c->slug,
            'image' => $c->image,
            'icon' => $c->icon,
            'listings_count' => (int) ($listingCounts[$c->id] ?? 0),
        ]);

        $stats = [
            'listings' => (int) $listingCounts->sum(),
            'sellers' => User::query()
                ->where('role', User::ROLE_INFOPRODUTOR)
                ->where('account_status', 'approved')
                ->count(),
            'categories' => $categories->count(),
        ];

        $preview = $request->boolean('preview') && $request->user()?->canAccessPlatformPanel();
        $content = $preview ? MarketplaceHomeContent::draft() : MarketplaceHomeContent::published();

        $dealsFromPopular = $content['deals']['source'] === 'popular';
        $popularLimit = $preview ? 20 : max($content['popular']['limit'], $dealsFromPopular ? $content['deals']['limit'] : 0);
        $featuredLimit = $preview ? 20 : max($content['recent']['limit'], 3, $dealsFromPopular ? 0 : $content['deals']['limit']);

        $featured = $this->presenter->activeListingsQuery()
            ->latest()
            ->limit($featuredLimit)
            ->get()
            ->map(fn (Product $p) => $this->presenter->productCard($p, $p->tenantOwner));

        $popular = $this->presenter->activeListingsQuery()
            ->orderByDesc('updated_at')
            ->limit($popularLimit)
            ->get()
            ->map(fn (Product $p) => $this->presenter->productCard($p, $p->tenantOwner));

        $sellers = User::query()
            ->where('role', User::ROLE_INFOPRODUTOR)
            ->where('account_status', 'approved')
            ->orderByDesc('last_seen_at')
            ->limit($preview ? 12 : $content['sellers']['limit'])
            ->get()
            ->map(fn (User $u) => $this->presenter->sellerCard($u));

        return Inertia::render('Marketplace/Home', [
            'categories' => $categories,
            'featured' => $featured,
            'popular' => $popular,
            'sellers' => $sellers,
            'recentQuestions' => $this->presenter->recentAnsweredQuestions($preview ? 12 : $content['questions']['limit']),
            'stats' => $stats,
            'content' => $content,
            'preview' => $preview,
            'appName' => config('getfy.app_name', 'Gamkon'),
        ]);
    }

    public function category(string $slug): Response
    {
        $category = MarketplaceCategory::query()->where('slug', $slug)->where('is_active', true)->firstOrFail();

        $products = $this->presenter->activeListingsQuery()
            ->where('marketplace_category_id', $category->id)
            ->latest()
            ->paginate(24)
            ->through(fn (Product $p) => $this->presenter->productCard($p, $p->tenantOwner));

        return Inertia::render('Marketplace/Category', [
            'category' => $category,
            'products' => $products,
        ]);
    }

    public function search(Request $request): Response
    {
        $q = trim((string) $request->query('q', ''));

        $query = $this->presenter->activeListingsQuery();
        if ($q !== '') {
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'ilike', "%{$q}%")
                    ->orWhere('description', 'ilike', "%{$q}%");
            });
        }

        $products = $query->latest()->paginate(24)->withQueryString()
            ->through(fn (Product $p) => $this->presenter->productCard($p, $p->tenantOwner));

        return Inertia::render('Marketplace/Search', [
            'q' => $q,
            'products' => $products,
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $product = Product::query()
            ->with(['marketplaceCategory', 'tenantOwner'])
            ->where(function ($q) use ($slug) {
                $q->where('checkout_slug', $slug)->orWhere('slug', $slug);
            })
            ->where('is_active', true)
            ->where('admin_blocked', false)
            ->firstOrFail();

        $card = $this->presenter->productCard($product, $product->tenantOwner);

        $questions = ProductQuestion::query()
            ->with(['user:id,name,avatar', 'answer.user:id,name'])
            ->where('product_id', $product->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn (ProductQuestion $q) => [
                'id' => $q->id,
                'body' => $q->body,
                'created_at' => $q->created_at?->diffForHumans(),
                'user' => [
                    'name' => $q->user?->name,
                    'avatar' => $q->user?->avatar,
                ],
                'answer' => $q->answer ? [
                    'body' => $q->answer->body,
                    'created_at' => $q->answer->created_at?->diffForHumans(),
                    'user_name' => $q->answer->user?->name,
                ] : null,
            ]);

        $related = $this->presenter->activeListingsQuery()
            ->where('id', '!=', $product->id)
            ->when($product->marketplace_category_id, fn ($q) => $q->where('marketplace_category_id', $product->marketplace_category_id))
            ->limit(12)
            ->get()
            ->map(fn (Product $p) => $this->presenter->productCard($p, $p->tenantOwner));

        return Inertia::render('Marketplace/Show', [
            'product' => array_merge($card, [
                'description_html' => $product->description,
                'created_at' => $product->created_at?->format('d/m/Y'),
                'marketplace_meta' => $product->marketplace_meta ?? [],
            ]),
            'questions' => $questions,
            'related' => $related,
            'canAsk' => (bool) $request->user(),
        ]);
    }

    public function seller(string $username): Response
    {
        $key = trim(rawurldecode($username));
        abort_if($key === '', 404);

        $seller = $this->resolvePublicSeller($key);
        abort_unless($seller, 404);

        return $this->renderSellerProfile($seller);
    }

    public function sellerById(int $id, string $username = ''): Response
    {
        $seller = User::query()->find($id);
        abort_unless($seller, 404);

        return $this->renderSellerProfile($seller);
    }

    private function renderSellerProfile(User $seller): Response
    {
        $products = $this->presenter->activeListingsQuery()
            ->where('tenant_id', $seller->id)
            ->latest()
            ->paginate(24)
            ->through(fn (Product $p) => $this->presenter->productCard($p, $seller));

        return Inertia::render('Marketplace/SellerProfile', [
            'seller' => $this->presenter->sellerCard($seller),
            'products' => $products,
        ]);
    }

    /**
     * Resolve vendedor público por id, username ou slug do nome (publicUsername).
     */
    private function resolvePublicSeller(string $key): ?User
    {
        if (ctype_digit($key)) {
            return User::query()->find((int) $key);
        }

        $lower = mb_strtolower($key);

        $candidates = User::query()
            ->where(function ($q) use ($key, $lower) {
                $q->where('username', $key)
                    ->orWhereRaw('LOWER(username) = ?', [$lower])
                    ->orWhereRaw('LOWER(name) = ?', [$lower])
                    ->orWhereRaw('LOWER(REPLACE(name, " ", "-")) = ?', [$lower]);
            })
            ->limit(50)
            ->get();

        $exact = $candidates->first(
            fn (User $u) => strcasecmp($u->publicUsername(), $key) === 0
        );
        if ($exact) {
            return $exact;
        }

        if ($candidates->isNotEmpty()) {
            return $candidates->first();
        }

        // Fallback: slug(name) quando username está vazio (ex.: "Jamil Abraão" → jamil-abraao)
        return User::query()
            ->where(function ($q) {
                $q->where('role', User::ROLE_INFOPRODUTOR)
                    ->orWhereNotNull('seller_onboarded_at');
            })
            ->orderBy('id')
            ->limit(500)
            ->get()
            ->first(fn (User $u) => strcasecmp($u->publicUsername(), $key) === 0);
    }
}
