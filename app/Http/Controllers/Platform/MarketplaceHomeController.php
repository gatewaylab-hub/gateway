<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Services\StorageService;
use App\Support\MarketplaceHomeContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MarketplaceHomeController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Platform/MarketplaceHome/Edit', [
            'draft' => MarketplaceHomeContent::draft(),
            'published' => MarketplaceHomeContent::published(),
            'defaults' => MarketplaceHomeContent::defaults(),
            'bannerDefaults' => MarketplaceHomeContent::bannerDefaults(),
            'icons' => MarketplaceHomeContent::ICONS,
            'publishedAt' => MarketplaceHomeContent::publishedAt(),
            'maxBanners' => MarketplaceHomeContent::MAX_BANNERS,
            'layoutFullWidth' => true,
        ]);
    }

    public function saveDraft(Request $request): JsonResponse
    {
        $content = MarketplaceHomeContent::saveDraft($this->payload($request));

        return response()->json(['ok' => true, 'content' => $content]);
    }

    public function publish(Request $request): JsonResponse
    {
        $content = MarketplaceHomeContent::publish($this->payload($request));

        return response()->json([
            'ok' => true,
            'content' => $content,
            'publishedAt' => MarketplaceHomeContent::publishedAt(),
        ]);
    }

    public function discard(): JsonResponse
    {
        return response()->json(['ok' => true, 'content' => MarketplaceHomeContent::discardDraft()]);
    }

    public function upload(Request $request, StorageService $storage): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ]);

        $stored = $storage->storeUploadedPublicFile($request->file('image'), 'marketplace/home');

        return response()->json(['ok' => true, 'url' => $stored['url']]);
    }

    private function payload(Request $request): array
    {
        $content = $request->input('content');

        abort_unless(is_array($content), 422, 'Conteúdo inválido.');

        return $content;
    }
}
