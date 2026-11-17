<footer class="site-footer">
    <div class="container">
        <div class="row">
            <div class="col-12 col-md-5 mb-4">
                <x-brand />
                <p class="mt-3">Keep records playing.</p>
            </div>
            <div class="col-6 col-md-3">
                <h2>Explore</h2>
                <a href="{{ route('vinyl') }}">Shop Vinyl</a>
                <a href="{{ route('crates') }}">Explore the Crates</a>
                <a href="{{ route('trade.create') }}">Trade In</a>
            </div>
            <div class="col-6 col-md-4">
                <h2>33½ Records</h2>
                <a href="{{ route('about') }}">About Us</a>
                <a href="{{ route('contact') }}">Contact Us</a>
                <a href="{{ route('quotes.index') }}">Your Quotes</a>
            </div>
        </div>
        <small>© {{ date('Y') }} 33½ Records</small>
    </div>
</footer>
