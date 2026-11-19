<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $items = $this->items($request);

        return view('pages.cart', ['items' => $items] + $this->totals($items));
    }

    public function add(Request $request, Product $product)
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:99'], 'variant_id' => ['nullable', 'integer']]);
        DB::transaction(function () use ($request, $product, $data) {
            $user = User::lockForUpdate()->findOrFail($request->user()->id);
            $variant = $this->variant($request, $product);
            $limit = min(99, $variant ? $variant->stock : $product->stock);
            if ($limit < 1) {
                throw ValidationException::withMessages(['quantity' => 'This item is out of stock.']);
            }
            $key = $product->id.':'.($variant?->id ?? 0);
            $item = $user->cartItems()->firstOrNew(['line_key' => $key]);
            $item->fill(['product_id' => $product->id, 'product_variant_id' => $variant?->id, 'quantity' => min(($item->quantity ?? 0) + $data['quantity'], $limit)])->save();
        });

        return back()->with('notice', 'Added to your cart.');
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:99'], 'variant_id' => ['nullable', 'integer']]);
        $variant = $this->variant($request, $product);
        $quantity = DB::transaction(function () use ($request, $product, $variant, $data) {
            $user = User::lockForUpdate()->findOrFail($request->user()->id);
            $item = $user->cartItems()->where('line_key', $product->id.':'.($variant?->id ?? 0))->first();
            if (! $item) {
                return 0;
            }
            $quantity = min($data['quantity'], $variant ? $variant->stock : $product->stock, 99);
            $quantity > 0 ? $item->update(['quantity' => $quantity]) : $item->delete();

            return $quantity;
        });
        if ($request->expectsJson()) {
            return response()->json(['quantity' => $quantity, 'lineTotal' => Money::format((int) ($variant?->price ?? $product->price) * $quantity)] + $this->totals($this->items($request)));
        }

        return back()->with('notice', 'Cart updated.');
    }

    public function remove(Request $request, Product $product)
    {
        $request->validate(['variant_id' => ['nullable', 'integer']]);
        DB::transaction(function () use ($request, $product) {
            $user = User::lockForUpdate()->findOrFail($request->user()->id);
            $user->cartItems()->where('line_key', $product->id.':'.$request->integer('variant_id'))->delete();
        });

        return back()->with('notice', 'Item removed.');
    }

    private function variant(Request $request, Product $product)
    {
        if ($product->variants()->exists()) {
            $variant = $product->variants()->find($request->integer('variant_id'));
            if (! $variant) {
                throw ValidationException::withMessages(['variant_id' => 'Choose an available size or variant.']);
            }

            return $variant;
        }
        if ($request->filled('variant_id')) {
            throw ValidationException::withMessages(['variant_id' => 'This variant is no longer available.']);
        }

        return null;
    }

    private function items(Request $request)
    {
        return $request->user()->cartItems()->with(['product', 'variant'])->get()->filter(function ($item) {
            if (! $item->product || ($item->product_variant_id && ! $item->variant)) {
                $item->delete();

                return false;
            }

            return true;
        })->map(fn ($item) => [
            'product' => $item->product, 'variant' => $item->variant, 'quantity' => $item->quantity,
            'price' => (int) ($item->variant?->price ?? $item->product->price),
            'total' => (int) ($item->variant?->price ?? $item->product->price) * $item->quantity,
        ]);
    }

    private function totals($items): array
    {
        $subtotal = Money::format($items->sum('total'));

        return ['subtotal' => $subtotal, 'total' => $subtotal, 'quantityCount' => $items->sum('quantity'), 'itemCount' => $items->count()];
    }
}
