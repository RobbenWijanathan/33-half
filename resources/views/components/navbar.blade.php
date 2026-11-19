<header class="site-header">
    <div class="container">
        <div class="header-main">
            <x-brand />
            <form class="header-search" method="GET" action="{{ route('shop') }}" role="search">
                <label class="visually-hidden" for="site-search">Search records, artists, albums</label>
                <input id="site-search" name="q" type="search" value="{{ request('q') }}"
                    placeholder="Search records, artists, albums…">
                <button type="submit" aria-label="Search">Search</button>
            </form>
            <form class="currency-form" method="POST" action="{{ route('currency.update') }}">
                @csrf
                <label for="display-currency" class="visually-hidden">Display currency</label>
                <select id="display-currency" name="currency" data-auto-submit>
                    @foreach (['IDR' => 'IDR · Rp', 'USD' => 'USD · $'] as $value => $label)
                        <option value="{{ $value }}" @selected(session('currency', 'IDR') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <noscript><button class="button button-border" type="submit">Update currency</button></noscript>
            </form>
            <a class="cart-link" href="{{ route('cart.index') }}">Cart <span
                    data-cart-count>{{ auth()->check() ? auth()->user()->cartItems()->sum('quantity') : 0 }}</span></a>
            <div class="dropdown profile-menu">
                <button class="profile-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"
                    aria-label="Account menu">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor"
                        stroke-width="1.7" aria-hidden="true">
                        <circle cx="12" cy="8" r="4" />
                        <path d="M4 21v-2a8 8 0 0 1 16 0v2" />
                    </svg>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @auth
                        <li><span class="dropdown-header">{{ auth()->user()->name }}</span></li>
                        @foreach (['account.index' => 'My Profile', 'orders.index' => 'Purchase History', 'quotes.index' => 'Your Quotes / Trade-Ins', 'account.credits' => 'Store Credits', 'orders.tracking' => 'Delivery Tracking', 'account.edit' => 'Account Settings'] as $route => $label)
                            <li><a class="dropdown-item" href="{{ route($route) }}">{{ $label }}</a></li>
                        @endforeach
                        @can('admin')
                            <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">Admin Dashboard</a></li>
                        @endcan
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">@csrf<button class="dropdown-item"
                                    type="submit">Logout</button></form>
                        </li>
                    @else
                        <li><a class="dropdown-item" href="{{ route('login') }}">Login</a></li>
                        <li><a class="dropdown-item" href="{{ route('register') }}">Register</a></li>
                    @endauth
                </ul>
            </div>
            <x-theme-toggle />
            <button class="menu-toggle" type="button" aria-controls="main-navigation"
                aria-expanded="false">Menu</button>
        </div>
        <nav id="main-navigation" class="main-nav" aria-label="Main navigation">
            @foreach (['home' => 'Home', 'vinyl' => 'Shop Vinyl', 'crates' => 'Crates', 'trade.create' => 'Trade In', 'merch' => 'Merchandise', 'quotes.index' => 'Your Quotes'] as $name => $label)
                <a href="{{ route($name) }}"
                    @if (request()->routeIs($name) ||
                            ($name === 'crates' && request()->routeIs('crate', 'crates.*', 'genres.show', 'artists.show'))) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>
    </div>
</header>
