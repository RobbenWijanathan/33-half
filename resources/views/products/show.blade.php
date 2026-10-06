@extends('layouts.app')
@section('title', $product->name.' — 33½')
@section('content')
<div class="container breadcrumbs">
    <a href="{{ route('shop') }}">Shop</a><span>/</span>
    <a href="{{ $product->product_type === 'merchandise' ? route('merch') : route('vinyl') }}">{{ $product->product_type === 'merchandise' ? 'Merch' : 'Vinyl' }}</a>
    <span>/</span><span>{{ $product->name }}</span>
</div>

<section class="container product-detail">
    <div class="detail-art">
        <img src="{{ $product->cover_image ? asset($product->cover_image) : asset('images/demo/placeholder.svg') }}" alt="{{ $product->name }} artwork">
    </div>
    <div class="detail-info">
        <span class="eyebrow">{{ $product->is_preorder ? 'Pre-order' : ($product->product_type === 'merchandise' ? 'Merchandise' : 'Vinyl record') }}</span>
        <h1>{{ $product->name }}</h1>
        <p class="detail-artist">{{ $product->product_type === 'vinyl' ? $product->artists->pluck('name')->join(', ') : $product->merch_category }}</p>
        <p class="detail-price">${{ number_format($product->price, 2) }}</p>
        <p class="detail-description">{{ $product->description }}</p>
        @if($product->audio_preview_url)
            <div class="detail-preview"><x-audio-preview-control :product="$product" /><span>Listen to a short preview</span></div>
        @endif
        <x-add-to-cart-button :product="$product" />
        <p class="detail-note">{{ $product->is_preorder ? 'Pre-order item · fulfillment details to be added.' : ($product->stock > 0 ? $product->stock.' in stock' : 'Currently unavailable') }}</p>
        <div class="detail-facts">
            <h2>Details</h2>
            @if($product->format)
                <p><span>Format</span>{{ $product->format }}</p>
            @endif
            @if($product->release_date)
                <p><span>Release</span>{{ $product->release_date->format('F Y') }}</p>
            @endif
            @if($product->label)
                <p><span>Label</span>{{ $product->label->name }}</p>
            @endif
            @if($product->genres->isNotEmpty())
                <p><span>Genre</span>{{ $product->genres->pluck('name')->join(', ') }}</p>
            @endif
        </div>
    </div>
</section>

<section class="section container">
    <x-section-header eyebrow="Keep exploring" title="More from the shelf" :href="route('shop')" />
    <x-product-grid :products="$related" />
</section>
@endsection

