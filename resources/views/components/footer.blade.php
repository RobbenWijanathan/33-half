<footer class="site-footer">
    <div class="container footer-top">
        <div><a class="brand footer-brand" href="{{ route('home') }}">33<span>½</span><i aria-hidden="true">✳</i></a><p>For the records you come back to.</p></div>
        <div><h3>Explore</h3><a href="{{ route('vinyl') }}">Vinyl</a><a href="{{ route('merch') }}">Merch</a><a href="{{ route('crate') }}">Crate digging</a></div>
        <div><h3>33½</h3><a href="{{ route('about') }}">About us</a><a href="{{ route('contact') }}">Contact</a><a href="{{ route('artists.index') }}">Artists</a></div>
        <div><h3>Stay in the loop</h3><p>New finds, occasional notes, no noise.</p><p class="muted">Newsletter signup coming soon.</p></div>
    </div>
    <div class="container footer-bottom"><span>© {{ date('Y') }} 33½. Demo storefront.</span><span>Made for listening.</span></div>
</footer>
