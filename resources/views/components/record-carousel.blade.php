@props(['products'])
@if($products->isEmpty())
    <x-empty-state title="This crate is empty" message="Try another crate to find your next record." />
@else
    <section class="record-carousel row align-items-center" data-record-carousel aria-label="Record browsing">
        <div class="col-md-6 carousel-art-column">
            <p class="carousel-hint" id="carousel-help">Scroll vertically to dig through the crate.</p>
            <div class="record-carousel-window" data-carousel-window role="region" aria-label="Record carousel" aria-describedby="carousel-help" tabindex="0">
                @foreach($products as $product)
                    <button class="carousel-record {{ $loop->first ? 'is-focused' : '' }}"
                        data-carousel-record type="button"
                        aria-label="Focus {{ $product->name }} by {{ $product->artists->pluck('name')->join(', ') }}"
                        aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
                        tabindex="{{ $loop->first ? '0' : '-1' }}">
                        <img src="{{ asset($product->cover_image ?: 'images/demo/placeholder.svg') }}"
                            alt="{{ $product->name }} artwork" loading="{{ $loop->first ? 'eager' : 'lazy' }}">
                    </button>
                @endforeach
            </div>
            <div class="carousel-controls">
                <button class="carousel-arrow" data-carousel-prev type="button" aria-label="Previous record" disabled>↑</button>
                <span data-carousel-counter>1 / {{ $products->count() }}</span>
                <button class="carousel-arrow" data-carousel-next type="button" aria-label="Next record" @disabled($products->count() < 2)>↓</button>
            </div>
        </div>
        <div class="col-md-6 carousel-caption-column">
            @foreach($products as $product)
                <article class="carousel-caption-panel" data-carousel-caption @if(!$loop->first) hidden @endif>
                    <span class="eyebrow">{{ $product->condition === 'used' ? 'Pre-owned record' : 'New record' }}</span>
                    <h2>{{ $product->name }}</h2>
                    <p class="carousel-artist">{{ $product->artists->pluck('name')->join(', ') }}</p>
                    <p class="carousel-price">{{ $product->formatted_price }}</p>
                    <p class="carousel-description">{{ $product->description }}</p>
                    <p class="carousel-genres">{{ $product->genres->pluck('name')->join(' · ') }}</p>
                    <div class="carousel-record-actions">
                        <a class="button button-dark" href="{{ route('products.show', $product) }}">View Record</a>
                        <x-audio-preview-control :product="$product" />
                    </div>
                </article>
            @endforeach
            <p class="sr-only" data-carousel-announcement role="status" aria-live="polite" aria-atomic="true"></p>
        </div>
    </section>
@endif
