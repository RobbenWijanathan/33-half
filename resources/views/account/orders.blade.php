@extends('layouts.account')
@section('title', $tracking ? 'Delivery Tracking' : 'Purchase History')
@section('account-content')
    <h1>{{ $tracking ? 'Delivery tracking' : 'Purchase history' }}</h1>
    @forelse($orders as $order)
        <article class="account-panel mb-3">
            <div class="d-flex justify-content-between gap-3 flex-wrap">
                <div>
                    <h2 class="h5"><a href="{{ route('orders.show', $order) }}">{{ $order->number }}</a></h2>
                    <p class="small text-muted">{{ $order->created_at->format('d M Y, H:i') }} ·
                        {{ ucfirst($order->fulfillment) }}</p>
                </div>
                <div><x-status-badge :status="$order->status" /> <strong
                        class="d-block mt-2">{{ \App\Support\Money::format($order->total) }}</strong></div>
            </div>
            <p>{{ $order->items->map(fn($item) => $item->quantity . ' × ' . $item->name . ($item->variant_name ? ' · ' . $item->variant_name : ''))->join(', ') }}
            </p>
            <a href="{{ route('orders.show', $order) }}">Order details & tracking →</a>
    </article>@empty<x-empty-state title="No orders yet" message="Your next favourite record is waiting in the shop." />
    @endforelse
    {{ $orders->links() }}
@endsection
