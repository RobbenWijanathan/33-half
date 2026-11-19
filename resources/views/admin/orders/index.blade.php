@extends('layouts.admin')
@section('title', 'Transactions')
@section('admin-content')
    <h1>
        Transactions</h1>
    <x-admin-filters :sorts="['newest' => 'Newest', 'oldest' => 'Oldest', 'total' => 'Total ↑', 'status' => 'Status']">
        <div class="col-sm-auto"><select class="form-select" name="status" aria-label="Order status" data-auto-submit>
                <option value="">All statuses</option>
                @foreach (['pending', 'confirmed', 'processing', 'ready_for_pickup', 'picked_up', 'shipped', 'out_for_delivery', 'delivered', 'cancelled', 'refunded'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ Str::headline($status) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-auto"><select class="form-select" name="payment" aria-label="Payment status" data-auto-submit>
                <option value="">All payments</option>
                @foreach (['pending', 'paid', 'cancelled', 'refunded'] as $status)
                    <option value="{{ $status }}" @selected(request('payment') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-auto"><select class="form-select" name="fulfillment" aria-label="Fulfillment" data-auto-submit>
                <option value="">Both methods</option>
                <option value="pickup" @selected(request('fulfillment') === 'pickup')>Pickup</option>
                <option value="delivery" @selected(request('fulfillment') === 'delivery')>Delivery</option>
            </select></div>
    </x-admin-filters>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Order / customer</th>
                    <th>Payable</th>
                    <th>Payment</th>
                    <th>Fulfillment</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td><a href="{{ route('admin.orders.show', $order) }}">{{ $order->number }}</a><small
                                class="d-block text-muted">{{ $order->user->name }} ·
                                {{ $order->created_at->format('d M Y') }}</small></td>
                        <td class="text-nowrap">{{ \App\Support\Money::format($order->total) }}</td>
                        <td>{{ ucfirst($order->payment_status) }}</td>
                        <td><x-status-badge :status="$order->status" /><small
                                class="d-block mt-1">{{ ucfirst($order->fulfillment) }}</small></td>
                </tr>@empty<tr>
                        <td colspan="4">No orders match your search.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>{{ $orders->links() }}
@endsection
