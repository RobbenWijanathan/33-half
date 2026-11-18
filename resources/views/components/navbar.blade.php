<header class="site-header">
    <div class="container">
        <div class="header-main">
            <x-brand />
            <form class="header-search" method="GET" action="{{ route('shop') }}" role="search">
                <label class="sr-only" for="site-search">Search records, artists, albums</label>
                <input id="site-search" name="q" type="search" value="{{ request('q') }}" placeholder="Search records, artists, albums…">
                <button type="submit" aria-label="Search">Search</button>
            </form>
            <form class="currency-form" method="POST" action="{{ route('currency.update') }}">
                @csrf
                <label for="display-currency" class="sr-only">Display currency</label>
                <select id="display-currency" name="currency" data-auto-submit>
                    @foreach(['IDR' => 'IDR · Rp', 'USD' => 'USD · $'] as $value => $label)
                        <option value="{{ $value }}" @selected(session('currency', 'IDR') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <noscript><button class="button button-border" type="submit">Update currency</button></noscript>
            </form>
            <a class="cart-link" href="{{ route('cart.index') }}">Cart <span data-cart-count>{{ array_sum(session('cart', [])) }}</span></a>
            <x-theme-toggle />
            <button class="menu-toggle" type="button" aria-controls="main-navigation" aria-expanded="false">Menu</button>
        </div>
        <nav id="main-navigation" class="main-nav" aria-label="Main navigation">
            @foreach(['home' => 'Home', 'vinyl' => 'Shop Vinyl', 'crates' => 'Crates', 'trade.create' => 'Trade In', 'merch' => 'Merchandise', 'quotes.index' => 'Your Quotes'] as $name => $label)
                <a href="{{ route($name) }}" @if(request()->routeIs($name) || ($name === 'crates' && request()->routeIs('crate', 'crates.*', 'genres.show', 'artists.show'))) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>
    </div>
</header>
