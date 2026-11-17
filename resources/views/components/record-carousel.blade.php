@props(['products'])

@if($products->isEmpty())
    <x-empty-state title="This crate is empty" message="Try another crate to find your next record." />
@else
    <section class="record-carousel" data-record-carousel aria-label="Record browsing">
        <div class="carousel-controls">
            <p id="carousel-help" class="carousel-hint">Swipe or use the arrows to browse.</p>
            <button class="carousel-arrow" data-carousel-prev type="button" aria-label="Previous record" disabled>←</button>
            <span data-carousel-counter>1 / {{ $products->count() }}</span>
            <button class="carousel-arrow" data-carousel-next type="button" aria-label="Next record" @disabled($products->count() < 2)>→</button>
        </div>
        <div class="record-carousel-window" data-carousel-window role="region" aria-label="Record carousel" aria-describedby="carousel-help" tabindex="0">
            @foreach($products as $product)
                <button class="carousel-record {{ $loop->first ? 'is-focused' : '' }}" data-carousel-record type="button"
                    aria-label="Focus {{ $product->name }} by {{ $product->artists->pluck('name')->join(', ') }}"
                    aria-pressed="{{ $loop->first ? 'true' : 'false' }}" tabindex="{{ $loop->first ? '0' : '-1' }}">
                    <x-product-art :product="$product" :eager="$loop->first" />
                </button>
            @endforeach
        </div>
        @foreach($products as $product)
            <article class="carousel-caption-panel" data-carousel-caption @if(!$loop->first) hidden @endif>
                <div>
                    <h2>{{ $product->name }}</h2>
                    <p class="carousel-artist">{{ $product->artists->pluck('name')->join(', ') }} @if($product->release_date) • {{ $product->release_date->year }} @endif</p>
                    <p class="carousel-price">{{ $product->formatted_price }}</p>
                    <div class="carousel-record-actions">
                        <a class="button button-dark" href="{{ route('products.show', $product) }}">View Record</a>
                        <x-audio-preview-control :product="$product" />
                    </div>
                </div>
                <div class="carousel-description">
                    <p>{{ $product->description }}</p>
                    @if($product->genre_line)<p class="carousel-genres">{{ $product->genre_line }}</p>@endif
                </div>
            </article>
        @endforeach
        <p class="sr-only" data-carousel-announcement role="status" aria-live="polite" aria-atomic="true"></p>
    </section>
@endif
