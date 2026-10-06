<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Genre;
use App\Models\Label;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function shop(Request $request): View
    {
        return $this->listing($request, Product::query(), 'All products', 'shop');
    }

    public function vinyl(Request $request): View
    {
        return $this->listing($request, Product::vinyl(), 'Vinyl records', 'vinyl');
    }

    public function merchandise(Request $request): View
    {
        return $this->listing($request, Product::merchandise(), 'Merchandise', 'merch');
    }

    public function newArrivals(Request $request): View
    {
        return $this->listing($request, Product::query()->where('is_preorder', false), 'New arrivals', 'new-arrivals');
    }

    public function preorders(Request $request): View
    {
        return $this->listing($request, Product::query()->where('is_preorder', true), 'Pre-orders', 'pre-orders');
    }

    public function show(Product $product): View
    {
        abort_if(request()->routeIs('merch.show') && $product->product_type !== 'merchandise', 404);
        abort_if(request()->routeIs('products.show') && $product->product_type !== 'vinyl', 404);
        $product->load(['artists', 'genres', 'label']);

        return view('products.show', [
            'product' => $product,
            'related' => Product::with('artists')->where('product_type', $product->product_type)
                ->whereKeyNot($product->id)->take(4)->get(),
        ]);
    }

    public function artists(): View
    {
        return view('pages.artists', ['artists' => Artist::withCount('products')->orderBy('name')->get()]);
    }

    public function artist(Artist $artist): View
    {
        return view('pages.collection', [
            'title' => $artist->name,
            'eyebrow' => 'Artist',
            'description' => $artist->bio,
            'products' => $artist->products()->with('artists')->paginate(12),
        ]);
    }

    public function genres(): View
    {
        return view('pages.genres', ['genres' => Genre::withCount('products')->orderBy('name')->get()]);
    }

    public function genre(Genre $genre): View
    {
        return view('pages.collection', [
            'title' => $genre->name,
            'eyebrow' => 'Genre',
            'description' => 'A small selection from the '.$genre->name.' shelf.',
            'products' => $genre->products()->with('artists')->paginate(12),
        ]);
    }

    public function crate(): View
    {
        return view('pages.crate', [
            'products' => Product::vinyl()->with(['artists', 'genres'])->orderBy('id')->get(),
        ]);
    }

    private function listing(Request $request, Builder $query, string $title, string $section): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'genre' => ['nullable', 'string', 'max:100'],
            'artist' => ['nullable', 'string', 'max:100'],
            'label' => ['nullable', 'string', 'max:100'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'availability' => ['nullable', 'in:in-stock,preorder'],
            'sort' => ['nullable', 'in:newest,price-low,price-high,name'],
        ]);

        if ($term = $filters['q'] ?? null) {
            $query->where(function (Builder $search) use ($term) {
                $search->where('name', 'like', '%'.$term.'%')
                    ->orWhereHas('artists', fn (Builder $artists) => $artists->where('name', 'like', '%'.$term.'%'));
            });
        }

        foreach (['genre' => 'genres', 'artist' => 'artists'] as $field => $relation) {
            if ($slug = $filters[$field] ?? null) {
                $query->whereHas($relation, fn (Builder $related) => $related->where('slug', $slug));
            }
        }

        if ($slug = $filters['label'] ?? null) {
            $query->whereHas('label', fn (Builder $label) => $label->where('slug', $slug));
        }
        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }
        if (($filters['availability'] ?? null) === 'in-stock') {
            $query->where('stock', '>', 0)->where('is_preorder', false);
        }
        if (($filters['availability'] ?? null) === 'preorder') {
            $query->where('is_preorder', true);
        }

        match ($filters['sort'] ?? 'newest') {
            'price-low' => $query->orderBy('price'),
            'price-high' => $query->orderByDesc('price'),
            'name' => $query->orderBy('name'),
            default => $query->latest('created_at'),
        };

        return view('pages.catalog', [
            'title' => $title,
            'section' => $section,
            'products' => $query->with('artists')->paginate(12)->withQueryString(),
            'genres' => Genre::orderBy('name')->get(),
            'artists' => Artist::orderBy('name')->get(),
            'labels' => Label::orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }
}
