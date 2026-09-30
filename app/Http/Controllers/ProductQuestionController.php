<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductAnswer;
use App\Models\ProductQuestion;
use App\Services\ContactLeakFilter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductQuestionController extends Controller
{
    public function __construct(
        protected ContactLeakFilter $filter,
    ) {}

    public function store(Request $request, string $slug): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 401);

        $product = Product::query()
            ->where(function ($q) use ($slug) {
                $q->where('checkout_slug', $slug)->orWhere('slug', $slug);
            })
            ->where('is_active', true)
            ->firstOrFail();

        $sellerOwnerId = (int) ($product->tenant_id ?: 0);
        $askTenant = (int) ($user->tenant_id ?: $user->id);
        if ($sellerOwnerId > 0 && $askTenant === $sellerOwnerId && $user->canAccessSellerPanel()) {
            return back()->withErrors(['body' => 'Você não pode perguntar no próprio anúncio.']);
        }

        $data = $request->validate([
            'body' => ['required', 'string', 'min:3', 'max:2000'],
        ]);

        $this->filter->assertAllowed($data['body']);

        ProductQuestion::query()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'tenant_id' => $product->tenant_id,
            'body' => trim($data['body']),
        ]);

        return back()->with('success', 'Pergunta enviada.');
    }

    public function sellerInbox(Request $request): Response
    {
        $tenantId = (int) ($request->user()->tenant_id ?: $request->user()->id);

        $questions = ProductQuestion::query()
            ->with(['user:id,name', 'product:id,name,checkout_slug,slug', 'answer'])
            ->where('tenant_id', $tenantId)
            ->orderByRaw('answered_at IS NULL DESC')
            ->orderByDesc('created_at')
            ->paginate(30);

        return Inertia::render('Marketplace/SellerQuestions', [
            'questions' => $questions,
        ]);
    }

    public function answer(Request $request, ProductQuestion $question): RedirectResponse
    {
        $user = $request->user();
        $tenantId = (int) ($user->tenant_id ?: $user->id);
        abort_unless((int) $question->tenant_id === $tenantId, 403);

        $data = $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:2000'],
        ]);

        $this->filter->assertAllowed($data['body']);

        ProductAnswer::query()->updateOrCreate(
            ['product_question_id' => $question->id],
            ['user_id' => $user->id, 'body' => trim($data['body'])]
        );

        $question->forceFill(['answered_at' => now()])->save();

        return back()->with('success', 'Resposta publicada.');
    }
}
