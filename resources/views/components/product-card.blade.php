@props(['product'])
@php $url = $product->product_type === 'merchandise' ? route('merch.show', $product) : route('products.show', $product); @endphp
<article class="product-card" @if($product->audio_preview_url) data-preview-card data-audio-url="{{ asset($product->audio_preview_url) }}" data-start-time="{{ $product->preview_start_time }}" @endif>
    <div class="product-media"><a href="{{ $url }}" aria-label="View {{ $product->name }}"><img src="{{ $product->cover_image ? asset($product->cover_image) : asset('images/demo/placeholder.svg') }}" alt="{{ $product->name }} artwork" loading="lazy"></a>@if($product->is_preorder)<span class="product-badge">Pre-order</span>@elseif($product->is_featured)<span class="product-badge">Featured</span>@endif<x-audio-preview-control :product="$product" /></div>
    <div class="product-meta"><span>{{ $product->product_type === 'vinyl' ? ($product->artists->pluck('name')->join(', ') ?: 'Various artists') : ($product->merch_category ?: '33½ merchandise') }}</span><span>{{ $product->product_type === 'vinyl' ? $product->format : 'Merch' }}</span></div>
    <div class="product-title-row"><h3><a href="{{ $url }}">{{ $product->name }}</a></h3><span>${{ number_format($product->price, 2) }}</span></div>
</article>
