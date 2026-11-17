<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Genre;
use App\Models\Label;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CatalogController extends Controller
{
    public function shop(Request $request): View
    {
        return $this->listing($request, Product::query(), 'All products', 'shop');
    }

    public function vinyl(Request $request): View
    {
        return $this->listing($request, Product::vinyl(), 'Shop Vinyl', 'vinyl');
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
            'related' => Product::with(['artists', 'genres'])->where('product_type', $product->product_type)
                ->whereKeyNot($product->id)->take(4)->get(),
        ]);
    }

    public function artists(): View
    {
        return view('pages.artists', ['artists' => Artist::withCount('products')->orderBy('name')->get()]);
    }

    public function artist(Request $request, Artist $artist): View
    {
        $query = Product::vinyl()->whereHas('artists', fn (Builder $artists) => $artists->whereKey($artist->id));

        return $this->collection($request, $query, $artist->name);
    }

    public function genres(): View
    {
        return view('pages.genres', ['genres' => Genre::withCount('products')->orderBy('name')->get()]);
    }

    public function genre(Request $request, Genre $genre): View
    {
        $query = Product::vinyl()->whereHas('genres', fn (Builder $genres) => $genres->whereKey($genre->id));

        return $this->collection($request, $query, $genre->name);
    }

    public function crates(Request $request): View
    {
        $filters = $request->validate([
            'type' => ['nullable', 'in:all,genres,artists'],
            'sort' => ['nullable', 'in:alphabetical,count'],
        ]);
        $sort = $filters['sort'] ?? 'alphabetical';
        $genres = Genre::with(['products' => fn ($products) => $products->vinyl()->latest('id')])
            ->withCount(['products' => fn ($products) => $products->vinyl()])
            ->orderBy('name')->get();
        $artists = Artist::with(['products' => fn ($products) => $products->vinyl()->latest('id')])
            ->orderBy('name')->get();

        // A letter crate includes every record by artists whose name starts with it.
        $letters = $artists->groupBy(fn (Artist $artist) => Str::upper(Str::substr($artist->name, 0, 1)))
            ->map(fn ($group) => $group->flatMap->products->unique('id')->values())
            ->filter(fn ($records, $letter) => preg_match('/^[A-Z]$/', $letter) && $records->isNotEmpty());

        if ($sort === 'count') {
            $genres = $genres->sortByDesc('products_count')->values();
            $letters = $letters->sortByDesc(fn ($records) => $records->count());
        }

        return view('pages.crates', [
            'genres' => $genres,
            'letters' => $letters,
            'type' => $filters['type'] ?? 'all',
            'sort' => $sort,
        ]);
    }

    public function artistLetter(Request $request, string $letter): View
    {
        $query = Product::vinyl()->whereHas('artists', fn (Builder $artists) => $artists->where('name', 'like', $letter.'%'));

        return $this->collection($request, $query, 'Artists · '.Str::upper($letter));
    }

    public function crate(Request $request): View
    {
        return $this->collection($request, Product::vinyl(), 'All Records');
    }

    private function collection(Request $request, Builder $query, string $title): View
    {
        $filters = $request->validate(['sort' => ['nullable', 'in:artist,title,newest']]);
        $sort = $filters['sort'] ?? 'artist';
        $products = $query->with(['artists', 'genres'])->get();

        $products = match ($sort) {
            'title' => $products->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE),
            'newest' => $products->sortByDesc('id'),
            default => $products->sortBy(fn (Product $product) => $product->artists->first()?->name.' '.$product->name, SORT_NATURAL | SORT_FLAG_CASE),
        };

        return view('pages.collection', [
            'title' => $title,
            'products' => $products->values(),
            'sort' => $sort,
        ]);
    }

    private function listing(Request $request, Builder $query, string $title, string $section): View
    {
        $filters = $request->validate([
            'condition' => ['nullable', 'in:new,used'],
            'q' => ['nullable', 'string', 'max:100'],
            'genre' => ['nullable', 'string', 'max:100'],
            'artist' => ['nullable', 'string', 'max:100'],
            'label' => ['nullable', 'string', 'max:100'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'availability' => ['nullable', 'in:in-stock,preorder'],
            'sort' => ['nullable', 'in:newest,price-low,price-high,name'],
        ]);

        if ($condition = $filters['condition'] ?? null) {
            $query->where('condition', $condition);
        }

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
            default => $query->latest('id'),
        };

        return view('pages.catalog', [
            'title' => $title,
            'section' => $section,
            'products' => $query->with(['artists', 'genres'])->paginate(12)->withQueryString(),
            'genres' => Genre::orderBy('name')->get(),
            'artists' => Artist::orderBy('name')->get(),
            'labels' => Label::orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }
}
