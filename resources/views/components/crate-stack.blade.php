@props(['products'])
<div class="crate-stack" aria-hidden="true">
    @forelse($products->take(3) as $product)
        <img src="{{ $product->cover_url }}" alt="" loading="lazy">
    @empty
        <span class="crate-stack-empty">33½</span>
    @endforelse
</div>
