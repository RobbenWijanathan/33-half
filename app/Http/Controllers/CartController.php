<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $quantities = $request->session()->get('cart', []);
        $products = Product::whereIn('id', array_keys($quantities))->get();
        $items = $products->map(fn (Product $product) => [
            'product' => $product,
            'quantity' => $quantities[$product->id],
            'total' => $product->price * $quantities[$product->id],
        ]);

        return view('pages.cart', [
            'items' => $items,
            'subtotal' => $products->first()?->formatMoney($items->sum('total')) ?? '$0.00',
        ]);
    }

    public function add(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:99']]);
        if ($product->stock < 1 && ! $product->is_preorder) {
            return back()->with('notice', 'This item is currently out of stock.');
        }

        $cart = $request->session()->get('cart', []);
        $currency = Product::whereIn('id', array_keys($cart))->value('currency');
        if ($currency && $currency !== $product->currency) {
            return back()->with('notice', 'Please finish or empty your current cart before adding items priced in a different currency.');
        }

        $cart[$product->id] = min(($cart[$product->id] ?? 0) + $data['quantity'], $product->is_preorder ? 99 : $product->stock);
        $request->session()->put('cart', $cart);

        return back()->with('notice', 'Added to your cart.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:99']]);
        $cart = $request->session()->get('cart', []);
        if (isset($cart[$product->id])) {
            $cart[$product->id] = min($data['quantity'], $product->is_preorder ? 99 : $product->stock);
            if ($cart[$product->id] < 1) {
                unset($cart[$product->id]);
            }
            $request->session()->put('cart', $cart);
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
}
