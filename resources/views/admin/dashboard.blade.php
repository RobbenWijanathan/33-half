@extends('layouts.admin')
@section('title', 'Admin Overview')
@section('admin-content')
    <p class="eyebrow">33½ Records</p>
    <h1>Store overview</h1>
    <div class="row g-3 mb-4">
        @foreach (['Users' => $users, 'Records' => $records, 'Merchandise' => $merchandise, 'Orders' => $orders, 'Completed orders' => $completed, 'Awaiting review' => $pendingQuotes] as $label => $value)
            <div class="col-6 col-md-4">
                <div class="account-panel h-100"><span class="small text-muted">{{ $label }}</span>
                    <p class="display-6 mb-0">{{ $value }}</p>
                </div>
            </div>
        @endforeach
    </div>
    <div class="account-panel mb-4">
        <h2 class="h5">Verified sales</h2>
        <p class="display-6">{{ \App\Support\Money::format($revenue + $creditSales) }}</p>
        <p class="small text-muted mb-0">Manual payments: {{ \App\Support\Money::format($revenue) }} · Store credit redeemed:
            {{ \App\Support\Money::format($creditSales) }}. Excludes cancelled and refunded orders.</p>
    </div>
    <h2 class="h4">Recent transactions</h2>
    @forelse($recentOrders as $order)
        <a class="account-row" href="{{ route('admin.orders.show', $order) }}"><span>{{ $order->number }}<small
                    class="d-block text-muted">{{ $order->user->name }} ·
                    {{ \App\Support\Money::format($order->total) }}</small></span><x-status-badge
            :status="$order->status" /></a>@empty<p>No transactions yet.</p>
    @endforelse
    <h2 class="h4 mt-4">Recent trade-ins</h2>
    @forelse($recentQuotes as $quote)
        <a class="account-row" href="{{ route('admin.quotes.show', $quote) }}"><span>{{ $quote->album }}<small
                    class="d-block text-muted">{{ $quote->user->name }}</small></span><x-status-badge
            :status="$quote->status" /></a>@empty<p>No quote requests yet.</p>
    @endforelse
    <h2 class="h4 mt-4">Low stock · 3 or fewer</h2>
    @forelse($lowStock as $product)
        <p><a
                href="{{ route($product->product_type === 'vinyl' ? 'admin.records.edit' : 'admin.merchandise.edit', $product) }}">{{ $product->name }}</a>
            ·
            {{ $product->variants->isNotEmpty() ? $product->variants->map(fn($variant) => $variant->name . ': ' . $variant->stock)->join(' / ') : $product->stock . ' left' }}
    </p>@empty<p>Stock levels look healthy.</p>
    @endforelse
@endsection
