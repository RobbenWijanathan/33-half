<header class="site-header">
    <div class="container">
        <div class="header-main">
            <x-brand />
            <form class="header-search" method="GET" action="{{ route('shop') }}" role="search">
                <label class="sr-only" for="site-search">Search records, artists, albums</label>
                <input id="site-search" name="q" type="search" value="{{ request('q') }}" placeholder="Search records, artists, albums…">
                <button type="submit" aria-label="Search">Search</button>
            </form>
            <a class="cart-link" href="{{ route('cart.index') }}">Cart <span>{{ array_sum(session('cart', [])) }}</span></a>
            <button class="menu-toggle" type="button" aria-controls="main-navigation" aria-expanded="false">Menu</button>
        </div>
        <nav id="main-navigation" class="main-nav" aria-label="Main navigation">
            @foreach(['home' => 'Home', 'vinyl' => 'Shop Vinyl', 'crates' => 'Crate-Digging', 'trade.create' => 'Trade In', 'merch' => 'Merchandise', 'quotes.index' => 'Your Quotes'] as $name => $label)
                <a href="{{ route($name) }}" @if(request()->routeIs($name) || ($name === 'crates' && request()->routeIs('crate', 'crates.*', 'genres.show', 'artists.show'))) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>
    </div>
</header>
