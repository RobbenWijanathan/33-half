@props(['product'])

<div data-record-gallery>
    <x-product-art :product="$product" :eager="true" />
    <div class="record-thumbnails" role="group" aria-label="Record artwork">
        @foreach($product->gallery_views as $view)
            <button type="button" data-gallery-view data-image="{{ $view['url'] }}"
                aria-label="{{ $view['label'] }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}">
                <img src="{{ $view['url'] }}" alt="" width="72" height="72" loading="lazy">
            </button>
        @endforeach
    </div>
</div>
