<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceCategory;
use App\Services\StorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class MarketplaceCategoriesController extends Controller
{
    public function index(): Response
    {
        $categories = MarketplaceCategory::query()->orderBy('sort_order')->orderBy('name')->get();
        $storage = app(StorageService::class);

        return Inertia::render('Platform/MarketplaceCategories/Index', [
            'categories' => $categories->map(fn (MarketplaceCategory $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'sort_order' => $c->sort_order,
                'is_active' => $c->is_active,
                'image' => $c->image ? ($storage->resolvePublicUrl((string) $c->image) ?: null) : null,
                'image_path' => $c->image,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:120'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $stored = app(StorageService::class)->storeUploadedPublicFile(
                $request->file('image'),
                'marketplace/categories'
            );
            $imagePath = $stored['path'] ?? null;
        }

        MarketplaceCategory::query()->create([
            'name' => $data['name'],
            'slug' => $data['slug'] ?: Str::slug($data['name']),
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
            'image' => $imagePath,
        ]);

        return back()->with('success', 'Categoria criada.');
    }

    public function update(Request $request, MarketplaceCategory $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:120'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        $category->fill([
            'name' => $data['name'],
            'slug' => $data['slug'] ?: Str::slug($data['name']),
            'sort_order' => $data['sort_order'] ?? $category->sort_order,
            'is_active' => array_key_exists('is_active', $data)
                ? (bool) $data['is_active']
                : $category->is_active,
        ]);

        $storage = app(StorageService::class);

        if ($request->boolean('remove_image') && $category->image) {
            if ($storage->exists($category->image)) {
                $storage->delete($category->image);
            }
            $category->image = null;
        }

        if ($request->hasFile('image')) {
            if ($category->image && $storage->exists($category->image)) {
                $storage->delete($category->image);
            }
            $stored = $storage->storeUploadedPublicFile(
                $request->file('image'),
                'marketplace/categories'
            );
            $category->image = $stored['path'] ?? null;
        }

        $category->save();

        return back()->with('success', 'Categoria atualizada.');
    }

    public function destroy(MarketplaceCategory $category): RedirectResponse
    {
        if ($category->image) {
            $storage = app(StorageService::class);
            if ($storage->exists($category->image)) {
                $storage->delete($category->image);
            }
        }

        $category->delete();

        return back()->with('success', 'Categoria removida.');
    }
}
