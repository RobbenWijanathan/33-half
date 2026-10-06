@extends('layouts.app')
@section('title', 'Cart — 33½')
@section('content')
<div class="container page-intro"><span class="eyebrow">Your selection</span><h1>Cart.</h1><p>{{ $items->count() }} {{ Str::plural('item', $items->count()) }} on your shelf.</p></div><section class="container cart-layout">@if($items->isEmpty())<x-empty-state title="Your cart is empty" message="Take a look around and find a record worth keeping." />@else<div class="cart-items">@foreach($items as $item)<x-cart-item :item="$item" />@endforeach</div><aside class="cart-summary"><h2>Order summary</h2><p><span>Subtotal</span><strong>${{ number_format($subtotal, 2) }}</strong></p><p class="muted">Shipping and taxes will be calculated at checkout.</p><button class="button button-dark" type="button" disabled title="Checkout is not available in this scaffold">Checkout coming soon</button><a class="text-link" href="{{ route('shop') }}">Continue shopping →</a></aside>@endif</section>
@endsection
