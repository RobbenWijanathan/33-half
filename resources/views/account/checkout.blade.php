@extends('layouts.account')
@section('title', 'Checkout')
@section('account-content')
    <h1>
        Checkout</h1>
    <p class="text-muted">Choose how your next find gets home.</p>
    <form method="POST" action="{{ route('checkout.store') }}" data-checkout data-subtotal="{{ $subtotal }}"
        data-fee="{{ config('storefront.delivery_fee') }}" data-balance="{{ $user->store_credit }}"
        data-currency="{{ session('currency', 'IDR') }}" data-rate="{{ config('storefront.idr_per_usd') }}">@csrf
        <input type="hidden" name="checkout_token" value="{{ $token }}">
        <div class="row g-4">
            <div class="col-lg-7">
                <fieldset class="account-panel mb-3">
                    <legend class="h4 float-none w-auto">Pickup or delivery</legend>
                    <label class="form-check mb-2"><input class="form-check-input" type="radio" name="fulfillment"
                            value="pickup" @checked(old('fulfillment', 'pickup') === 'pickup')> <span class="form-check-label">Store pickup ·
                            free</span></label>
                    <label class="form-check"><input class="form-check-input" type="radio" name="fulfillment"
                            value="delivery" @checked(old('fulfillment') === 'delivery')> <span class="form-check-label">Delivery ·
                            {{ \App\Support\Money::format(config('storefront.delivery_fee')) }}</span></label>
                    <p class="small text-muted mt-3 mb-0">
                        {{ config('storefront.pickup_location') }}<br>{{ config('storefront.pickup_instructions') }}</p>
                </fieldset>
                <div class="account-panel" data-delivery-fields>
                    <h2 class="h4">Delivery address</h2>
                    <label class="form-label" for="address_id">Saved address</label><select class="form-select mb-3"
                        id="address_id" name="address_id" data-saved-address>
                        <option value="">Enter an address below</option>
                        @foreach ($addresses as $address)
                            <option value="{{ $address->id }}" data-address="{{ json_encode($address->snapshot()) }}"
                                @selected((string) old('address_id') === (string) $address->id)>{{ $address->label }} · {{ $address->city }}</option>
                        @endforeach
                    </select>
                    @foreach (['recipient' => 'Recipient', 'phone' => 'Phone', 'street' => 'Street', 'city' => 'City', 'province' => 'Province', 'postal_code' => 'Postal code'] as $field => $label)
                        <x-field :name="$field" :label="$label" :value="$field === 'recipient' ? $user->name : ($field === 'phone' ? $user->phone : '')" />
                    @endforeach
                </div>
            </div>
            <div class="col-lg-5">
                <div class="account-panel">
                    <h2 class="h4">Your order</h2>
                    @foreach ($items as $item)
                        <p class="small">{{ $item->quantity }} × {{ $item->product->name }} {{ $item->variant?->name }}
                        </p>
                    @endforeach
                    <hr>
                    <p class="d-flex justify-content-between">
                        <span>Subtotal</span><strong>{{ \App\Support\Money::format($subtotal) }}</strong></p>
                    <p class="d-flex justify-content-between"><span>Delivery</span><strong
                            data-delivery-total>{{ \App\Support\Money::format(0) }}</strong></p>
                    <x-field name="credit_amount" label="Apply credit (whole IDR)" type="number" :value="0"
                        min="0" :max="$user->store_credit" step="1" required />
                    <p class="small text-muted">Available:
                        {{ \App\Support\Money::format($user->store_credit) }}<br>Remaining: <span
                            data-credit-remaining>{{ \App\Support\Money::format($user->store_credit) }}</span></p>
                    <p class="d-flex justify-content-between"><span>Credit applied</span><strong
                            data-credit-total>{{ \App\Support\Money::format(0) }}</strong></p>
                    <p class="d-flex justify-content-between h5"><span>Payable</span><strong
                            data-checkout-total>{{ \App\Support\Money::format($subtotal) }}</strong></p>
                    <p class="small text-muted">{{ config('storefront.payment_instructions') }}</p>
                    <button class="button button-dark w-100" data-loading-text="Placing order…">Place order</button>
                    <div class="cart-continue"><a href="{{ route('cart.index') }}">Back to cart</a></div>
                </div>
            </div>
        </div>
    </form>
@endsection
