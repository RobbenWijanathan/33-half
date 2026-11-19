@extends('layouts.admin')
@section('title', 'Manage ' . $user->name)
@section('admin-content')
    <a href="{{ route('admin.users.index') }}">
        ← All profiles</a>
    <h1 class="mt-3">{{ $user->name }}</h1>
    <form class="account-panel mb-4" method="POST" action="{{ route('admin.users.update', $user) }}"
        data-confirm="Save these account details and permissions?">@csrf @method('PUT')
        <x-field name="name" label="Name" :value="$user->name" maxlength="120" required /><x-field name="email"
            label="Email" type="email" :value="$user->email" required /><x-field name="phone" label="Phone"
            :value="$user->phone" maxlength="30" />
        <div class="row">
            <div class="col-md-6"><x-field name="role" label="Role"><select class="form-select" id="role"
                        name="role">
                        @foreach (['user', 'admin'] as $role)
                            <option value="{{ $role }}" @selected(old('role', $user->role) === $role)>{{ ucfirst($role) }}</option>
                        @endforeach
                    </select></x-field></div>
            <div class="col-md-6"><x-field name="is_active" label="Account status"><select class="form-select"
                        id="is_active" name="is_active">
                        <option value="1" @selected(old('is_active', $user->is_active) == 1)>Active</option>
                        <option value="0" @selected(old('is_active', $user->is_active) == 0)>Inactive</option>
                    </select></x-field></div>
        </div>
        <p class="small text-muted">At least one active admin must remain. Deactivation signs the account out; its history
            is preserved.</p><button class="button button-dark">Save account</button>
    </form>
    <div class="account-panel mb-4">
        <h2 class="h4">Store credit · {{ \App\Support\Money::format($user->store_credit) }}</h2>
        <form method="POST" action="{{ route('admin.users.credit', $user) }}"
            data-confirm="Record this store-credit adjustment?">@csrf<x-field name="amount"
                label="Adjustment in whole IDR (negative to deduct)" type="number" step="1" required /><x-field
                name="reason" label="Reason (required)" maxlength="500" required /><button
                class="button button-border">Record adjustment</button></form>
    </div>
    <h2 class="h4">Credit activity</h2><x-credit-history :transactions="$transactions" />{{ $transactions->links() }}
    <h2 class="h4 mt-4">Recent purchases</h2>
    @forelse($orders as $order)
        <a class="account-row" href="{{ route('admin.orders.show', $order) }}"><span>{{ $order->number }} ·
            {{ \App\Support\Money::format($order->total) }}</span><x-status-badge :status="$order->status" /></a>@empty<p>No
            purchases yet.</p>
    @endforelse
    <h2 class="h4 mt-4">Recent quotes</h2>
    @forelse($quotes as $quote)
        <a class="account-row" href="{{ route('admin.quotes.show', $quote) }}"><span>#{{ $quote->id }} ·
            {{ $quote->album }}</span><x-status-badge :status="$quote->status" /></a>@empty<p>No quotes yet.</p>
    @endforelse
@endsection
