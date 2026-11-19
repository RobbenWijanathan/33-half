@extends('layouts.admin')
@section('title', $order->number)
@section('admin-content')
    <a href="{{ route('admin.orders.index') }}">
        ← All transactions</a>
    <h1 class="h2 mt-3">{{ $order->number }}</h1>
    <p><a href="{{ route('admin.users.edit', $order->user) }}">{{ $order->user->name }}</a> ·
        {{ $order->created_at->format('d M Y H:i') }} · {{ ucfirst($order->fulfillment) }}</p>
    <p><x-status-badge :status="$order->status" /> Payment: <strong>{{ ucfirst($order->payment_status) }}</strong></p>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Purchased item</th>
                    <th>Qty</th>
                    <th>Unit price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $item->name }}<small class="d-block">{{ $item->variant_name }}</small></td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ \App\Support\Money::format($item->unit_price) }}</td>
                        <td>{{ \App\Support\Money::format($item->total) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="account-panel mb-4">
        @foreach (['subtotal' => 'Subtotal', 'delivery_fee' => 'Delivery fee', 'credit_applied' => 'Credit applied', 'total' => 'Manual payment amount'] as $field => $label)
            <p class="d-flex justify-content-between">
                <span>{{ $label }}</span><strong>{{ \App\Support\Money::format($order->$field) }}</strong></p>
        @endforeach
        @if ($order->fulfillment === 'delivery')
            <address class="mb-0">{{ $order->address_snapshot['recipient'] }} ·
                {{ $order->address_snapshot['phone'] }}<br>{{ $order->address_snapshot['street'] }}<br>{{ $order->address_snapshot['city'] }},
            {{ $order->address_snapshot['province'] }} {{ $order->address_snapshot['postal_code'] }}</address>@else<p
                class="preserve-lines mb-0">{{ $order->pickup_instructions }}</p>
        @endif
    </div>
    @if ($order->payment_status === 'pending')
        <form class="account-panel mb-4" method="POST" action="{{ route('admin.orders.payment', $order) }}"
            data-confirm="Have you checked that the manual payment was received?">@csrf<h2 class="h4">Verify manual
                payment</h2>
            <p class="small text-muted">Check the actual payment before approving. This local workflow does not collect or
                transfer money.</p><x-field name="note" id="payment-note" label="Payment reference / verification note"
                required maxlength="2000" /><button class="button button-dark">Confirm payment received</button>
        </form>
    @endif
    @if (count($order->nextStatuses()))
        <form class="account-panel mb-4" method="POST" action="{{ route('admin.orders.update', $order) }}"
            data-confirm="Save this order status? For refunds, confirm the manual payment has been returned. Stock and applied credits will be restored once.">
            @csrf @method('PUT')<h2 class="h4">Fulfillment update</h2><x-field name="status"
                label="Next status"><select class="form-select" name="status" id="status">
                    @foreach ($order->nextStatuses() as $status)
                        <option value="{{ $status }}">{{ Str::headline($status) }}</option>
                    @endforeach
                </select></x-field><x-field name="note" id="status-note" label="Update note / refund reference" required
                maxlength="2000" />
            <p class="small text-muted">Cancellation and refunds are supported before dispatch or pickup. Refunds require
                you to return the manual payment first.</p><button class="button button-dark">Save status</button>
        </form>
    @endif
    @if ($order->fulfillment === 'delivery' && !in_array($order->status, ['cancelled', 'refunded']))
        <form class="account-panel mb-4" method="POST" action="{{ route('admin.orders.tracking', $order) }}">@csrf
            @method('PATCH')<h2 class="h4">Delivery tracking</h2><x-field name="carrier" label="Carrier"
                :value="$order->carrier" maxlength="120" required /><x-field name="tracking_number" label="Tracking number"
                :value="$order->tracking_number" maxlength="120" required /><x-field name="tracking_url"
                label="Tracking URL (optional, HTTPS or HTTP)" type="url" :value="$order->tracking_url" maxlength="1000" /><button
                class="button button-border">Save tracking</button></form>
    @endif
    <h2 class="h4">Status & payment history</h2><x-status-history :histories="$order->histories" />
@endsection
