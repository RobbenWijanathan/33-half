<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function create(Request $request)
    {
        $items = $request->user()->cartItems()->with(['product', 'variant'])->get();
        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('notice', 'Add something to your cart first.');
        }
        if ($items->contains(fn ($item) => ! $item->product || ($item->product_variant_id && ! $item->variant))) {
            return redirect()->route('cart.index')->with('notice', 'An unavailable item was removed. Please check your cart.');
        }
        $subtotal = $items->sum(fn ($item) => (int) ($item->variant?->price ?? $item->product->price) * $item->quantity);
        $token = $request->session()->get('checkout_token', (string) Str::uuid());
        $request->session()->put('checkout_token', $token);

        return view('account.checkout', ['items' => $items, 'subtotal' => $subtotal, 'user' => $request->user(), 'addresses' => $request->user()->addresses()->get(), 'token' => $token]);
    }

    public function store(Request $request)
    {
        $needsAddress = $request->input('fulfillment') === 'delivery' && ! $request->filled('address_id');
        $data = $request->validate([
            'checkout_token' => ['required', 'uuid'], 'fulfillment' => ['required', Rule::in(['pickup', 'delivery'])],
            'credit_amount' => ['required', 'integer', 'min:0', 'max:100000000000'], 'address_id' => ['nullable', 'integer'],
            'recipient' => [Rule::requiredIf($needsAddress), 'nullable', 'string', 'max:120'],
            'phone' => [Rule::requiredIf($needsAddress), 'nullable', 'string', 'max:30'],
            'street' => [Rule::requiredIf($needsAddress), 'nullable', 'string', 'max:500'],
            'city' => [Rule::requiredIf($needsAddress), 'nullable', 'string', 'max:120'],
            'province' => [Rule::requiredIf($needsAddress), 'nullable', 'string', 'max:120'],
            'postal_code' => [Rule::requiredIf($needsAddress), 'nullable', 'string', 'max:12'],
        ]);
        $existing = $request->user()->orders()->where('checkout_token', $data['checkout_token'])->first();
        if ($existing) {
            return redirect()->route('orders.show', $existing);
        }
        abort_unless(hash_equals((string) $request->session()->get('checkout_token', ''), $data['checkout_token']), 419);

        $order = DB::transaction(function () use ($request, $data) {
            $user = User::lockForUpdate()->findOrFail($request->user()->id);
            $existing = $user->orders()->where('checkout_token', $data['checkout_token'])->first();
            if ($existing) {
                return $existing;
            }
            $cart = $user->cartItems()->orderBy('product_id')->orderBy('product_variant_id')->lockForUpdate()->get();
            if ($cart->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
            }
            $lines = [];
            $subtotal = 0;
            foreach ($cart as $item) {
                $product = Product::lockForUpdate()->find($item->product_id);
                $variant = $item->product_variant_id ? $product?->variants()->lockForUpdate()->find($item->product_variant_id) : null;
                if (! $product || ($item->product_variant_id && ! $variant) || (! $variant && $product->variants()->exists())) {
                    throw ValidationException::withMessages(['cart' => 'An item has changed. Remove it from your cart and choose it again.']);
                }
                $stockItem = $variant ?? $product;
                if ($item->quantity > $stockItem->stock) {
                    throw ValidationException::withMessages(['cart' => $product->name.' has only '.$stockItem->stock.' available. Update your cart.']);
                }
                $price = (int) $stockItem->price;
                $lines[] = ['product_id' => $product->id, 'product_variant_id' => $variant?->id, 'name' => $product->name, 'variant_name' => $variant?->name, 'unit_price' => $price, 'quantity' => $item->quantity, 'total' => $price * $item->quantity];
                $subtotal += $price * $item->quantity;
                $stockItem->decrement('stock', $item->quantity);
            }
            $fee = $data['fulfillment'] === 'delivery' ? max(0, config('storefront.delivery_fee')) : 0;
            $credit = (int) $data['credit_amount'];
            if ($credit > min($user->store_credit, $subtotal + $fee)) {
                throw ValidationException::withMessages(['credit_amount' => 'Credit cannot exceed your balance or the order total.']);
            }
            $address = null;
            if ($data['fulfillment'] === 'delivery') {
                $address = collect($data)->only(['recipient', 'phone', 'street', 'city', 'province', 'postal_code'])->all();
                if (! empty($data['address_id'])) {
                    $address = $user->addresses()->findOrFail($data['address_id'])->snapshot();
                }
            }
            $order = $user->orders()->create([
                'number' => '33H-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)), 'checkout_token' => $data['checkout_token'],
                'fulfillment' => $data['fulfillment'], 'subtotal' => $subtotal, 'delivery_fee' => $fee,
                'credit_applied' => $credit, 'total' => $subtotal + $fee - $credit, 'address_snapshot' => $address,
                'pickup_instructions' => $data['fulfillment'] === 'pickup' ? config('storefront.pickup_location')."\n".config('storefront.pickup_instructions') : null,
                'payment_status' => $subtotal + $fee === $credit ? 'paid' : 'pending',
            ]);
            $order->items()->createMany($lines);
            $order->payment()->create(['amount' => $order->total, 'method' => $order->total === 0 ? 'store_credit' : 'manual', 'status' => $order->payment_status, 'verified_at' => $order->total === 0 ? now() : null]);
            $order->histories()->create(['actor_id' => $user->id, 'status' => 'pending', 'note' => 'Order placed. '.($order->total === 0 ? 'Paid using store credit.' : 'Awaiting manual payment verification.')]);
            if ($credit > 0) {
                $user->changeCredit(-$credit, 'redemption', 'Purchase '.$order->number, 'order-'.$order->id, $user->id, $order->id);
            }
            $user->cartItems()->delete();

            return $order;
        }, 3);
        $request->session()->forget('checkout_token');

        return redirect()->route('orders.show', $order)->with('notice', 'Order placed. Check the payment and fulfillment details below.');
    }
}
