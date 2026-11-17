<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Genre;
use App\Models\Label;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
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
        return $this->listing(
            $request,
            Product::newArrivals(),
            'New arrivals',
            'new-arrivals',
            'Everything that landed on the shelf in the last '.Product::NEW_ARRIVAL_DAYS.' days, newest first.',
        );
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
        $products = Product::vinyl()->with(['artists', 'genres'])->orderBy('id')->get();

        return view('pages.crate', compact('products'));
    }

    private function listing(Request $request, Builder $query, string $title, string $section, ?string $description = null): View
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

        // A reversed price range is an easy slip; read it the way it was meant.
        if (isset($filters['min_price'], $filters['max_price']) && $filters['min_price'] > $filters['max_price']) {
            [$filters['min_price'], $filters['max_price']] = [$filters['max_price'], $filters['min_price']];
        }

        // The model scopes do the querying; the controller only connects request, model and view.
        $products = $query->search($filters['q'] ?? null)
            ->filter($filters)
            ->sorted($filters['sort'] ?? 'newest')
            ->with('artists')
            ->paginate(12)
            ->withQueryString();

        $genres = Genre::orderBy('name')->get();
        $artists = Artist::orderBy('name')->get();
        $labels = Label::orderBy('name')->get();
        $activeFilters = $this->activeFilters($request, $filters, $genres, $artists, $labels);
        $description = $description ?? 'Browse the shelf. Follow a sound. Find your next favorite.';

        return view('pages.catalog', compact(
            'title', 'section', 'description', 'products', 'genres', 'artists', 'labels', 'filters', 'activeFilters'
        ));
    }

    /**
     * The filters currently narrowing the listing, each with the URL that removes just that one.
     *
     * @return list<array{label: string, url: string}>
     */
    private function activeFilters(Request $request, array $filters, Collection $genres, Collection $artists, Collection $labels): array
    {
        $active = [];

        foreach (['q', 'genre', 'artist', 'label', 'min_price', 'max_price', 'availability'] as $key) {
            $value = $filters[$key] ?? null;
            if ($value === null || $value === '') {
                continue;
            }

            if ($key === 'q') {
                $label = 'Search: “'.$value.'”';
            } elseif ($key === 'genre') {
                $label = 'Genre: '.($genres->firstWhere('slug', $value)->name ?? $value);
            } elseif ($key === 'artist') {
                $label = 'Artist: '.($artists->firstWhere('slug', $value)->name ?? $value);
            } elseif ($key === 'label') {
                $label = 'Label: '.($labels->firstWhere('slug', $value)->name ?? $value);
            } elseif ($key === 'min_price') {
                $label = 'From $'.str_replace('.00', '', number_format((float) $value, 2));
            } elseif ($key === 'max_price') {
                $label = 'Up to $'.str_replace('.00', '', number_format((float) $value, 2));
            } else {
                $label = $value === 'preorder' ? 'Pre-order' : 'In stock';
            }

            // The chip links to the current URL without this one filter, back on page 1.
            $active[] = ['label' => $label, 'url' => $request->fullUrlWithoutQuery([$key, 'page'])];
        }

        return $active;
    }
}
