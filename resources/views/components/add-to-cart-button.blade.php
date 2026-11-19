@props(['product', 'label' => 'Add to cart'])
<form method="POST" action="{{ route('cart.add', $product) }}" class="add-to-cart-form">
    @csrf
    @php($variants = $product->variants)
    @if ($variants->isNotEmpty())
        <div class="variant-choice"><label class="form-label" for="variant-{{ $product->id }}">Size /
                variant</label><select class="form-select" name="variant_id" id="variant-{{ $product->id }}"
                data-product-variant required>
                @foreach ($variants as $variant)
                    <option value="{{ $variant->id }}" data-stock="{{ $variant->stock }}"
                        data-price="{{ \App\Support\Money::format($variant->price) }}" @disabled($variant->stock < 1)>
                        {{ $variant->name }} · {{ \App\Support\Money::format($variant->price) }} · {{ $variant->stock }}
                        left</option>
                @endforeach
            </select></div>
    @endif
    <x-quantity-selector :max="max(1, $variants->isNotEmpty() ? $variants->max('stock') : $product->stock)" />
    <button class="button button-dark" type="submit" @disabled(($variants->isNotEmpty() ? $variants->sum('stock') : $product->stock) < 1)>
        {{ ($variants->isNotEmpty() ? $variants->sum('stock') : $product->stock) < 1 ? 'Sold out' : $label }}
        <span aria-hidden="true">↗</span>
    </button>
</form>
