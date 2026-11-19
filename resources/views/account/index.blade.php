@extends('layouts.account')
@section('title', 'My Profile')
@section('account-content')
    <p class="eyebrow">
        Your collection starts here</p>
    <h1>Hello, {{ $user->name }}.</h1>
    <p>{{ $user->email }} @if ($user->phone)
            · {{ $user->phone }}
        @endif
    </p>
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="account-panel"><span class="eyebrow">Store credit</span>
                <p class="display-6 mb-2">{{ \App\Support\Money::format($user->store_credit) }}</p><a
                    href="{{ route('account.credits') }}">View credit activity →</a>
            </div>
        </div>
        <div class="col-md-6">
            <div class="account-panel"><span class="eyebrow">Make it yours</span>
                <h2 class="h4">Keep your details current.</h2>
                <p>Manage your password and delivery addresses.</p><a href="{{ route('account.edit') }}">Account settings
                    →</a>
            </div>
        </div>
    </div>
    <div class="section-heading">
        <h2 class="h4">Recent orders</h2><a href="{{ route('orders.index') }}">View all</a>
    </div>
    @forelse($orders as $order)
        <a class="account-row" href="{{ route('orders.show', $order) }}"><span>{{ $order->number }}<small
                    class="d-block text-muted">{{ $order->created_at->format('d M Y') }}</small></span><span>{{ \App\Support\Money::format($order->total) }}
            <x-status-badge :status="$order->status" /></span></a>@empty<p class="text-muted">No purchases yet. Find your next
            favourite in the shop.</p>
    @endforelse
    <div class="section-heading mt-4">
        <h2 class="h4">Recent trade-ins</h2><a href="{{ route('quotes.index') }}">View all</a>
    </div>
    @forelse($quotes as $quote)
        <a class="account-row" href="{{ route('quotes.show', $quote) }}"><span>{{ $quote->album }}<small
                    class="d-block text-muted">{{ $quote->artist }}</small></span><x-status-badge
            :status="$quote->status" /></a>@empty<p class="text-muted">Nothing to trade yet. Give an old record a second spin.
        </p>
    @endforelse
    <h2 class="h4 mt-4">Saved addresses</h2>
    @forelse($addresses as $address)
    <p><strong>{{ $address->label }}</strong> · {{ $address->street }}, {{ $address->city }}</p>@empty<p
            class="text-muted">No saved addresses.</p>
    @endforelse
@endsection
