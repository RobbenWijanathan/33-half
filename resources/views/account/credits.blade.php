@extends('layouts.account')
@section('title', 'Store Credits')
@section('account-content')
    <p class="eyebrow">Keep the music moving</p>
    <h1>Store credits</h1>
    <div class="account-panel mb-4">
        <p class="text-muted mb-1">Available balance</p>
        <p class="display-5">{{ \App\Support\Money::format($user->store_credit) }}</p>
        <p class="mb-0">Use your credit at checkout. Credits stay with your account and cannot be transferred.</p>
    </div>
    <x-credit-history :transactions="$transactions" />
    {{ $transactions->links() }}
@endsection
