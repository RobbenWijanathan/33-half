<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\AdminAuditLog;
use App\Models\Artist;
use App\Models\Genre;
use App\Models\Label;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:120'], 'sort' => ['nullable', 'in:newest,oldest,name,price,stock'], 'stock' => ['nullable', 'in:low,out'], 'condition' => ['nullable', 'in:new,used']]);
        $type = $this->type($request);
        $products = Product::where('product_type', $type)->with(['artists', 'variants']);
        if (! empty($data['q'])) {
            $products->where(function ($query) use ($data) {
                $query->where('name', 'like', '%'.$data['q'].'%')->orWhereHas('artists', fn ($artists) => $artists->where('name', 'like', '%'.$data['q'].'%'));
            });
        }
        if (! empty($data['condition'])) {
            $products->where('condition', $data['condition']);
        }
        if (! empty($data['stock'])) {
            $limit = $data['stock'] === 'out' ? 0 : 3;
            $products->where(function ($query) use ($limit) {
                $query->where(function ($plain) use ($limit) {
                    $plain->doesntHave('variants')->where('stock', '<=', $limit);
                })
                    ->orWhereHas('variants', fn ($variants) => $variants->where('stock', '<=', $limit));
            });
        }
        $sort = $data['sort'] ?? 'newest';
        $products->orderBy(in_array($sort, ['name', 'price', 'stock']) ? $sort : 'id', $sort === 'newest' ? 'desc' : 'asc');

        return view('admin.products.index', ['products' => $products->paginate(15)->withQueryString(), 'type' => $type, 'base' => $this->base($request)]);
    }

    public function create(Request $request)
    {
        return $this->form($request, new Product(['condition' => 'new', 'stock' => 0]));
    }

    public function store(ProductRequest $request)
    {
        $product = new Product(['product_type' => $this->type($request)]);
        $this->save($request, $product);

        return redirect()->route($this->base($request).'.edit', $product)->with('notice', 'Product created.');
    }

    public function edit(Request $request, Product $product)
    {
        abort_unless($product->product_type === $this->type($request), 404);

        return $this->form($request, $product->load(['artists', 'genres', 'variants']));
    }

    public function update(ProductRequest $request, Product $product)
    {
        abort_unless($product->product_type === $this->type($request), 404);
        $this->save($request, $product);

        return back()->with('notice', 'Product updated.');
    }

    public function destroy(Request $request, Product $product)
    {
        abort_unless($product->product_type === $this->type($request), 404);
        DB::transaction(function () use ($product) {
            $product->delete();
            AdminAuditLog::record('product.archived', $product, 'Archived '.$product->name);
        });

        return redirect()->route($this->base($request).'.index')->with('notice', 'Product archived. Purchase history is preserved.');
    }

    private function form(Request $request, Product $product)
    {
        return view('admin.products.form', ['product' => $product, 'genres' => Genre::orderBy('name')->get(), 'labels' => Label::orderBy('name')->get(), 'type' => $this->type($request), 'base' => $this->base($request)]);
    }

    private function save(ProductRequest $request, Product $product): void
    {
        $data = $request->safe()->except(['cover', 'gallery', 'remove_cover', 'remove_gallery', 'artist_names', 'genre_ids', 'variants']);
        $data['currency'] = 'IDR';
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_preorder'] = $request->boolean('is_preorder');
        $data['preview_start_time'] = $data['preview_start_time'] ?? 0;
        $oldFiles = [];
        $newFiles = [];
        if ($request->hasFile('cover') || $request->boolean('remove_cover')) {
            if (str_starts_with($product->cover_image ?? '', 'storage/catalog/')) {
                $oldFiles[] = substr($product->cover_image, 8);
            }
            $data['cover_image'] = null;
            if ($request->hasFile('cover')) {
                $newFiles[] = $request->file('cover')->store('catalog', 'public');
                $data['cover_image'] = 'storage/'.end($newFiles);
            }
        }
        if ($request->hasFile('gallery') || $request->boolean('remove_gallery')) {
            foreach ($product->gallery_images ?? [] as $image) {
                if (str_starts_with($image['path'], 'storage/catalog/')) {
                    $oldFiles[] = substr($image['path'], 8);
                }
            }
            $data['gallery_images'] = [];
            foreach ($request->file('gallery', []) as $index => $image) {
                $newFiles[] = $image->store('catalog', 'public');
                $data['gallery_images'][] = ['path' => 'storage/'.end($newFiles), 'label' => $index === 0 ? 'Back cover' : 'Record detail'];
            }
        }
        try {
            DB::transaction(function () use ($request, $product, $data) {
                if ($product->exists) {
                    Product::lockForUpdate()->findOrFail($product->id);
                }
                $product->fill($data)->save();
                if ($product->product_type === 'vinyl') {
                    $ids = [];
                    $artistNames = array_filter(array_map('trim', preg_split('/\R/', $request->input('artist_names'))));
                    foreach ($artistNames as $name) {
                        $ids[] = Artist::firstOrCreate(['slug' => Str::limit(Str::slug($name), 240, '')], ['name' => $name])->id;
                    }
                    $product->artists()->sync($ids);
                    $product->syncGenres($request->input('genre_ids', []));
                } else {
                    foreach ($request->input('variants', []) as $row) {
                        $variant = ! empty($row['id']) ? $product->variants()->findOrFail($row['id']) : null;
                        if (! empty($row['delete'])) {
                            $variant?->delete();
                        } elseif (! empty($row['name'])) {
                            $values = collect($row)->only(['name', 'sku', 'price', 'stock'])->all();
                            $variant ? $variant->update($values) : $product->variants()->create($values);
                        }
                    }
                }
                AdminAuditLog::record('product.saved', $product, 'Saved '.$product->name.'; price IDR '.$product->price.'; stock '.$product->stock);
            });
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($newFiles);
            throw $exception;
        }
        Storage::disk('public')->delete($oldFiles);
    }

    private function type(Request $request): string
    {
        return $request->routeIs('admin.merchandise.*') ? 'merchandise' : 'vinyl';
    }

    private function base(Request $request): string
    {
        return $request->routeIs('admin.merchandise.*') ? 'admin.merchandise' : 'admin.records';
    }
}
