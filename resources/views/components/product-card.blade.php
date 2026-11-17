@props(['product'])
@php
    $url = $product->product_type === 'merchandise' ? route('merch.show', $product) : route('products.show', $product);
@endphp
<article class="product-card" data-preview-card data-audio-url="{{ $product->audio_preview_url ? asset($product->audio_preview_url) : '' }}" data-start-time="{{ $product->preview_start_time }}">
    <div class="product-media">
        <a href="{{ $url }}">
            <img src="{{ asset($product->cover_image ?: 'images/demo/placeholder.svg') }}" alt="{{ $product->name }} artwork" loading="lazy">
        </a>
        @if($product->product_type === 'vinyl')
            <span @class(['product-badge', 'used' => $product->condition === 'used'])>
                {{ $product->is_preorder ? 'Pre-order' : ($product->condition === 'used' ? 'Pre-Owned' : 'New') }}
            </span>
        @endif
        <x-audio-preview-control :product="$product" />
    </div>
    <div class="product-info">
        <h3><a href="{{ $url }}">{{ $product->name }}</a></h3>
        <p>{{ $product->product_type === 'vinyl' ? $product->artists->pluck('name')->join(', ') : $product->merch_category }}</p>
    </div>
    <div class="product-bottom">
        <strong>{{ $product->formatted_price }}</strong>
        <small>{{ $product->product_type === 'vinyl' ? $product->genres->pluck('name')->join(' · ') : '33½ Merchandise' }}</small>
    </div>
</article>
