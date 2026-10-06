@extends('layouts.app')
@section('content')
<x-hero />
<section class="section container"><x-section-header eyebrow="Fresh on the shelf" title="New arrivals" :href="route('new-arrivals')" /><x-product-grid :products="$newArrivals" /></section>
<section class="section section-tint"><div class="container"><x-section-header eyebrow="Staff picks" title="Featured records" :href="route('vinyl')" /><x-product-grid :products="$featured" /></div></section>
<section class="section container"><x-section-header eyebrow="Find your frequency" title="Browse by genre" :href="route('genres.index')" /><x-genre-nav :genres="$genres" /></section>
<section class="crate-promo"><div class="container crate-promo-inner"><div><span class="eyebrow light">A different way to browse</span><h2>Go where the<br>music takes you.</h2><p>Flip through records, hear a little of each, and find something unexpected.</p><a class="button button-light" href="{{ route('crate') }}">Start digging ↗</a></div><div class="crate-promo-mark" aria-hidden="true">33<span>½</span></div></div></section>
<section class="section container"><x-section-header eyebrow="On the horizon" title="Pre-orders" :href="route('preorders')" /><x-product-grid :products="$preorders" /></section>
<section class="section section-tint"><div class="container"><x-section-header eyebrow="Beyond the turntable" title="Merchandise" :href="route('merch')" /><x-product-grid :products="$merchandise" /></div></section>
<section class="section container editorial"><div><span class="eyebrow">Artist in focus</span><h2>{{ $featuredArtist?->name ?? 'Stories behind the sound' }}</h2><p>{{ $featuredArtist?->bio ?? 'Artist stories and conversations will live here.' }}</p>@if($featuredArtist)<a class="text-link" href="{{ route('artists.show', $featuredArtist) }}">Meet the artist ↗</a>@endif</div><div class="editorial-art"><span>33½<br>LISTENING<br>NOTES</span></div></section>
<section class="newsletter"><div class="container newsletter-inner"><div><span class="eyebrow">The listening list</span><h2>Good music, occasionally.</h2></div><p>Newsletter signup coming soon. A place for new finds, store notes, and releases worth hearing.</p></div></section>
@endsection
