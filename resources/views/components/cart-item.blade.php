@props(['item'])
@php
    $product = $item['product'];
    $url = $product->product_type === 'merchandise' ? route('merch.show', $product) : route('products.show', $product);
@endphp
<article class="cart-item" data-cart-item>
    <a href="{{ $url }}"><x-product-art :product="$product" /></a>
    <div class="cart-item-info">
        <h2><a href="{{ $url }}">{{ $product->name }}</a></h2>
        <p>{{ \App\Support\Money::format($item['price']) }} each ·
            {{ $product->condition === 'used' ? 'Pre-owned' : 'New' }} @if ($item['variant'])
                · {{ $item['variant']->name }}
            @endif
        </p>
        <div class="cart-item-actions">
            <form method="POST" action="{{ route('cart.update', $product) }}" data-quantity-form>
                @csrf
                @method('PATCH')
                <input type="hidden" name="variant_id" value="{{ $item['variant']?->id }}">
                <x-quantity-selector :value="$item['quantity']" :max="max(1, $item['variant']?->stock ?? $product->stock)" />
            </form>
            <form method="POST" action="{{ route('cart.remove', $product) }}">
                @csrf
                @method('DELETE')
                <input type="hidden" name="variant_id" value="{{ $item['variant']?->id }}">
                <button type="submit">Remove</button>
            </form>
        </div>
    </div>
    <strong data-line-total>{{ \App\Support\Money::format($item['total']) }}</strong>
</article>
