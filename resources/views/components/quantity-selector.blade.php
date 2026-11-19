@props(['name' => 'quantity', 'value' => 1, 'max' => 99])
<label class="quantity-selector"><span class="visually-hidden">Quantity</span><input type="number"
        name="{{ $name }}" min="1" max="{{ $max }}" value="{{ $value }}" required></label>
