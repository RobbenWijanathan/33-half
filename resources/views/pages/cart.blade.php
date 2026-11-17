@extends('layouts.app')
@section('title', 'Cart — 33½')
@section('content')
<div class="container">
    <div class="breadcrumbs"><a href="{{ route('home') }}">Home</a> / Cart</div>
    <h1>Your Cart</h1>
    <p class="page-lead">{{ $items->count() }} {{ Str::plural('item', $items->count()) }} on your shelf.</p>
    @if($items->isEmpty())
        <x-empty-state title="Your cart is empty" message="Take a look around and find a record worth keeping." />
    @else
        <div class="row">
            <div class="col-lg-8 mb-4">
                @foreach($items as $item)
                    <x-cart-item :item="$item" />
                @endforeach
            </div>
            <aside class="col-lg-4">
                <div class="cart-summary">
                    <h2>Order Summary</h2>
                    <p class="d-flex justify-content-between"><span>Subtotal</span><strong>{{ $subtotal }}</strong></p>
                    <p class="text-muted small">Checkout is not available yet. Your selection is saved in this browser session.</p>
                    <button class="button button-dark w-100" type="button" disabled>Checkout coming soon</button>
                    <a class="text-link mt-3" href="{{ route('shop') }}">Continue shopping →</a>
                </div>
            </aside>
        </div>
    @endif
</div>
@endsection
