@extends('layouts.app')
@section('title', $product->name)
@section('content')
    <div class="container breadcrumbs">
        <a href="{{ route('shop') }}">Shop</a><span>/</span>
        <a
            href="{{ $product->product_type === 'merchandise' ? route('merch') : route('vinyl') }}">{{ $product->product_type === 'merchandise' ? 'Merch' : 'Vinyl' }}</a>
        <span>/</span><span>{{ $product->name }}</span>
    </div>

    <section class="container product-detail">
        <div class="detail-art">
            @if ($product->product_type === 'vinyl')
                <x-record-gallery :product="$product" />
            @else
                <x-product-art :product="$product" :eager="true" />
            @endif
        </div>
        <div class="detail-info">
            <span
                class="eyebrow">{{ $product->is_preorder ? 'Pre-order' : ($product->product_type === 'merchandise' ? 'Merchandise' : 'Vinyl record') }}</span>
            <h1>{{ $product->name }}</h1>
            <p class="detail-artist">
                {{ $product->product_type === 'vinyl' ? $product->artists->pluck('name')->join(', ') : $product->merch_category }}
            </p>
            <p class="detail-price">{{ $product->formatted_price }}</p>
            <p class="detail-description">{{ $product->description }}</p>
            @if ($product->audio_preview_url)
                <div class="detail-preview"><x-audio-preview-control :product="$product" /><span>Listen to a short
                        preview</span></div>
            @endif
            @if ($product->product_type === 'vinyl')
                <x-record-condition :product="$product" />
            @endif
            <x-add-to-cart-button :product="$product" />
            <p class="detail-note">
                {{ $product->variants->isNotEmpty() ? 'Choose a variant to see its price and stock.' : ($product->stock > 0 ? $product->stock . ' in stock' : 'Currently unavailable') }}{{ $product->is_preorder ? ' · Pre-order' : '' }}
            </p>
            <div class="detail-facts">
                <h2>Details</h2>
                @if ($product->format)
                    <p><span>Format</span>{{ $product->format }}</p>
                @endif
                @if ($product->release_date)
                    <p><span>Release</span>{{ $product->release_date->format('F Y') }}</p>
                @endif
                @if ($product->label)
                    <p><span>Label</span>{{ $product->label->name }}</p>
                @endif
                @if ($product->genres->isNotEmpty())
                    <p><span>Genre</span>{{ $product->genre_line }}</p>
                @endif
            </div>
        </div>
    </section>

    <section class="section container">
        <x-section-header eyebrow="Keep exploring" title="More from the shelf" :href="route('shop')" />
        <x-product-grid :products="$related" />
    </section>
@endsection
