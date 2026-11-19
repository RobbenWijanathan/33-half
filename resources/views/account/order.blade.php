@extends('layouts.account')
@section('title', $order->number)
@section('account-content')
    <p class="eyebrow">
        {{ $order->created_at->format('d M Y, H:i') }}</p>
    <h1 class="h2">{{ $order->number }}</h1>
    <div class="d-flex gap-2 mb-4 flex-wrap"><x-status-badge :status="$order->status" /><span class="status-badge">Payment:
            {{ ucfirst($order->payment_status) }}</span></div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Unit price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $item->name }}<small class="d-block text-muted">{{ $item->variant_name }}</small></td>
                        <td>{{ $item->quantity }}</td>
                        <td class="text-nowrap">{{ \App\Support\Money::format($item->unit_price) }}</td>
                        <td class="text-nowrap">{{ \App\Support\Money::format($item->total) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="row g-4 mt-1">
        <div class="col-md-6">
            <div class="account-panel h-100">
                <h2 class="h4">{{ ucfirst($order->fulfillment) }}</h2>
                @if ($order->fulfillment === 'pickup')
                    <p class="preserve-lines">{{ $order->pickup_instructions }}</p>
                @else
                    <address>{{ $order->address_snapshot['recipient'] }} ·
                        {{ $order->address_snapshot['phone'] }}<br>{{ $order->address_snapshot['street'] }}<br>{{ $order->address_snapshot['city'] }},
                        {{ $order->address_snapshot['province'] }} {{ $order->address_snapshot['postal_code'] }}</address>
                    @if ($order->carrier)
                        <p>{{ $order->carrier }} · {{ $order->tracking_number }}</p>
                    @endif
                    @if ($order->tracking_url)
                        <a href="{{ $order->tracking_url }}" target="_blank" rel="noopener noreferrer">Track with carrier
                            ↗</a>
                    @endif
                @endif
            </div>
        </div>
        <div class="col-md-6">
            <div class="account-panel h-100">
                @foreach (['subtotal' => 'Subtotal', 'delivery_fee' => 'Delivery fee', 'credit_applied' => 'Credit applied', 'total' => 'Amount payable'] as $field => $label)
                    <p class="d-flex justify-content-between gap-3">
                        <span>{{ $label }}</span><strong>{{ $field === 'credit_applied' ? '−' : '' }}{{ \App\Support\Money::format($order->$field) }}</strong>
                    </p>
                @endforeach
                @if ($order->payment_status === 'pending')
                    <h3 class="h6">Manual payment</h3>
                    <p class="small mb-0">{{ config('storefront.payment_instructions') }}</p>
                @endif
            </div>
        </div>
    </div>
    <h2 class="h4 mt-4">Order & tracking history</h2><x-status-history :histories="$order->histories" />
    <a class="button button-border" href="{{ route('orders.index') }}">All orders</a>
@endsection
