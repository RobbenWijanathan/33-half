@extends('layouts.app')
@section('title', 'Cart')
@section('content')
<div class="container" data-cart>
    <div class="breadcrumbs"><a href="{{ route('home') }}">Home</a> / Cart</div>
    <h1>Your Cart</h1>
    <p class="page-lead" data-cart-items-count>{{ $items->count() }} {{ Str::plural('item', $items->count()) }} on your shelf.</p>
    <div data-cart-empty @if($items->isNotEmpty()) hidden @endif>
        <x-empty-state title="Your cart is empty" message="Take a look around and find a record worth keeping." />
    </div>
    @if($items->isNotEmpty())
        <div class="row" data-cart-content>
            <div class="col-lg-8 mb-4">
                @foreach($items as $item)
                    <x-cart-item :item="$item" />
                @endforeach
            </div>
            <aside class="col-lg-4">
                <div class="cart-summary">
                    <h2>Order Summary</h2>
                    <p class="d-flex justify-content-between"><span>Subtotal</span><strong data-cart-subtotal>{{ $subtotal }}</strong></p>
                    <p class="cart-grand-total d-flex justify-content-between"><span>Total</span><strong data-cart-total>{{ $total }}</strong></p>
                    <p class="text-muted small">Checkout is not available yet. Your selection is saved in this browser session.</p>
                    <button class="button button-dark w-100" type="button" disabled>Checkout coming soon</button>
                    <div class="cart-continue">
                        <a class="button button-border w-100" href="{{ route('shop') }}">Continue shopping <span aria-hidden="true">→</span></a>
                    </div>
                </div>
            </aside>
        </div>
    @endif
</div>
@endsection
