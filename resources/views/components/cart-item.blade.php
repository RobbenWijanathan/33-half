@props(['item'])
@php
    $product = $item['product'];
    $url = $product->product_type === 'merchandise' ? route('merch.show', $product) : route('products.show', $product);
@endphp
<article class="cart-item">
    <a href="{{ $url }}"><img src="{{ asset($product->cover_image) }}" alt="{{ $product->name }} artwork"></a>
    <div class="cart-item-info">
        <h2><a href="{{ $url }}">{{ $product->name }}</a></h2>
        <p>{{ $product->formatted_price }} each · {{ $product->condition === 'used' ? 'Pre-owned' : 'New' }}</p>
        <div class="cart-item-actions">
            <form method="POST" action="{{ route('cart.update', $product) }}">
                @csrf
                @method('PATCH')
                <x-quantity-selector :value="$item['quantity']" :max="$product->is_preorder ? 99 : max(1, $product->stock)" />
                <button type="submit">Update</button>
            </form>
            <form method="POST" action="{{ route('cart.remove', $product) }}">
                @csrf
                @method('DELETE')
                <button type="submit">Remove</button>
            </form>
        </div>
    </div>
    <strong>{{ $product->formatMoney($item['total']) }}</strong>
</article>
