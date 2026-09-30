<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductDeliveryCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductDeliveryCodeController extends Controller
{
    public function index(Request $request, Product $produto): Response
    {
        $this->authorizeProduct($request, $produto);

        $codes = ProductDeliveryCode::query()
            ->where('product_id', $produto->id)
            ->orderByDesc('id')
            ->paginate(50);

        return Inertia::render('Marketplace/ProductStock', [
            'product' => [
                'id' => $produto->id,
                'name' => $produto->name,
                'delivery_mode' => $produto->delivery_mode,
            ],
            'codes' => $codes->through(fn (ProductDeliveryCode $c) => [
                'id' => $c->id,
                'status' => $c->status,
                'order_id' => $c->order_id,
                'sold_at' => $c->sold_at?->toIso8601String(),
                'created_at' => $c->created_at?->toIso8601String(),
                'preview' => $c->status === ProductDeliveryCode::STATUS_AVAILABLE
                    ? $this->mask($c->getPayload())
                    : '••••',
            ]),
            'available_count' => ProductDeliveryCode::query()
                ->where('product_id', $produto->id)
                ->where('status', ProductDeliveryCode::STATUS_AVAILABLE)
                ->count(),
        ]);
    }

    public function store(Request $request, Product $produto): RedirectResponse
    {
        $this->authorizeProduct($request, $produto);

        $data = $request->validate([
            'codes' => ['required', 'string', 'max:100000'],
            'delivery_mode' => ['nullable', 'in:chat,automatic,both'],
        ]);

        if (! empty($data['delivery_mode'])) {
            $produto->forceFill(['delivery_mode' => $data['delivery_mode']])->save();
        }

        $lines = preg_split('/\r\n|\r|\n/', $data['codes']) ?: [];
        $tenantId = (int) $produto->tenant_id;
        $added = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $code = new ProductDeliveryCode([
                'product_id' => $produto->id,
                'tenant_id' => $tenantId,
                'status' => ProductDeliveryCode::STATUS_AVAILABLE,
            ]);
            $code->setPayload($line);
            $code->save();
            $added++;
        }

        return back()->with('success', "{$added} código(s) adicionados.");
    }

    protected function authorizeProduct(Request $request, Product $produto): void
    {
        $tenantId = (int) ($request->user()->tenant_id ?: $request->user()->id);
        abort_unless((int) $produto->tenant_id === $tenantId, 403);
    }

    protected function mask(string $value): string
    {
        $len = mb_strlen($value);
        if ($len <= 4) {
            return str_repeat('•', $len);
        }

        return mb_substr($value, 0, 2).str_repeat('•', max(2, $len - 4)).mb_substr($value, -2);
    }
}
