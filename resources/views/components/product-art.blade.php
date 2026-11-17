@props(['product', 'eager' => false])

<span class="artwork artwork--{{ $product->product_type }}">
    <img src="{{ $product->cover_url }}" alt="{{ $product->name }} artwork"
        loading="{{ $eager ? 'eager' : 'lazy' }}"
        @if($product->cover_url !== asset('images/demo/placeholder.svg'))
            data-art-fallback="{{ asset('images/demo/placeholder.svg') }}"
        @endif>
</span>
