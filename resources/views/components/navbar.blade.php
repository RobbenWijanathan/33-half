<header class="site-header">
    <div class="container nav-row">
        <a class="brand" href="{{ route('home') }}" aria-label="33 and a half home">33<span>½</span><i aria-hidden="true">✳</i></a>
        <nav class="main-nav" aria-label="Main navigation">
            <a href="{{ route('shop') }}" @class(['active' => request()->routeIs('shop')])>Shop</a>
            <a href="{{ route('vinyl') }}" @class(['active' => request()->routeIs('vinyl')])>Vinyl</a>
            <a href="{{ route('new-arrivals') }}" @class(['active' => request()->routeIs('new-arrivals')])>New arrivals</a>
            <a href="{{ route('preorders') }}" @class(['active' => request()->routeIs('preorders')])>Pre-orders</a>
            <a href="{{ route('merch') }}" @class(['active' => request()->routeIs('merch*')])>Merch</a>
            <a href="{{ route('crate') }}" @class(['active' => request()->routeIs('crate')])>Crate digging</a>
        </nav>
        <div class="nav-actions">
            <a class="nav-search" href="{{ route('shop') }}#catalog-search" aria-label="Search the shop">⌕</a>
            <a class="cart-link" href="{{ route('cart.index') }}">Cart <span>{{ array_sum(session('cart', [])) }}</span></a>
        </div>
    </div>
    <div class="container secondary-nav"><span>Music to keep.</span><a href="{{ route('artists.index') }}">Artists</a><a href="{{ route('genres.index') }}">Genres</a><a href="{{ route('about') }}">About</a><a href="{{ route('contact') }}">Contact</a></div>
</header>
