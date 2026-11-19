<nav class="account-nav nav flex-lg-column gap-1" aria-label="My account">
    @foreach (['account.index' => 'My Profile', 'orders.index' => 'Purchase History', 'quotes.index' => 'Your Quotes', 'account.credits' => 'Store Credits', 'orders.tracking' => 'Delivery Tracking', 'account.edit' => 'Account Settings'] as $route => $label)
        <a class="nav-link @if (request()->routeIs($route)) active @endif"
            href="{{ route($route) }}">{{ $label }}</a>
    @endforeach
    @can('admin')
        <a class="nav-link" href="{{ route('admin.dashboard') }}">Admin Dashboard ↗</a>
    @endcan
</nav>
