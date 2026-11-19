@extends('layouts.app')
@section('content')
    <div class="container admin-page">
        <div class="breadcrumbs"><a href="{{ route('home') }}">Storefront</a> / Admin</div>
        <div class="row g-4">
            <aside class="col-lg-3">
                <nav class="account-nav nav flex-lg-column gap-1" aria-label="Administration">
                    <span class="eyebrow px-3 pt-3 pb-2">Behind the counter</span>
                    @foreach (['dashboard' => 'Overview', 'quotes.index' => 'Trade-in Quotes', 'records.index' => 'Records', 'orders.index' => 'Transactions', 'merchandise.index' => 'Merchandise', 'crates.index' => 'Crates', 'users.index' => 'Profiles', 'audit' => 'Audit Log'] as $route => $label)
                        <a class="nav-link @if (request()->routeIs('admin.' . Str::before($route, '.') . '*')) active @endif"
                            href="{{ route('admin.' . $route) }}">{{ $label }}</a>
                    @endforeach
                    <a class="nav-link" href="{{ route('account.index') }}">My account ↗</a>
                </nav>
            </aside>
            <div class="col-lg-9 admin-content">@yield('admin-content')</div>
        </div>
    </div>
@endsection
