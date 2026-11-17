<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\Money;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $items = $this->items($request);

        return view('pages.cart', ['items' => $items] + $this->totals($items));
    }

    public function add(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:99']]);
        $limit = $product->is_preorder ? 99 : min(99, $product->stock);

        if ($limit < 1) {
            return back()->with('notice', 'This item is currently out of stock.');
        }

        $cart = $request->session()->get('cart', []);
        $cart[$product->id] = min(($cart[$product->id] ?? 0) + $data['quantity'], $limit);
        $request->session()->put('cart', $cart);

        return back()->with('notice', 'Added to your cart.');
    }

    public function update(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:99']]);
        $cart = $request->session()->get('cart', []);
        $quantity = 0;

        if (isset($cart[$product->id])) {
            $quantity = min($data['quantity'], $product->is_preorder ? 99 : $product->stock);

            if ($quantity > 0) {
                $cart[$product->id] = $quantity;
            } else {
                unset($cart[$product->id]);
            }

            $request->session()->put('cart', $cart);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'quantity' => $quantity,
                'lineTotal' => Money::format($product->price * $quantity),
            ] + $this->totals($this->items($request)));
        }

        return back();
    }

    public function remove(Request $request, Product $product): RedirectResponse
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$product->id]);
        $request->session()->put('cart', $cart);

        return back();
    }

    private function items(Request $request): Collection
    {
        $cart = $request->session()->get('cart', []);

        return Product::whereIn('id', array_keys($cart))->get()->map(fn (Product $product) => [
            'product' => $product,
            'quantity' => $cart[$product->id],
            'total' => $product->price * $cart[$product->id],
        ]);
    }

    private function totals(Collection $items): array
    {
        $subtotal = Money::format($items->sum('total'));

        return [
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'quantityCount' => $items->sum('quantity'),
            'itemCount' => $items->count(),
        ];
    }
}
