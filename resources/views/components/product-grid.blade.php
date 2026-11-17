@props(['products', 'wide' => false])
@if($products->isEmpty())
    <x-empty-state title="No records found" message="Try another crate or adjust your filters." />
@else
    <div class="row product-grid">
        @foreach($products as $product)
            <div class="{{ $wide ? 'col-6' : 'col-6 col-lg-3' }} mb-4">
                <x-product-card :product="$product" />
            </div>
        @endforeach
    </div>
@endif
