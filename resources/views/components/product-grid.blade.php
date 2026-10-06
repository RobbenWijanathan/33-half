@props(['products'])
@if($products->isEmpty())<x-empty-state title="Nothing in this section yet" message="Try another shelf or adjust your filters." />@else<div class="product-grid">@foreach($products as $product)<x-product-card :product="$product" />@endforeach</div>@endif
