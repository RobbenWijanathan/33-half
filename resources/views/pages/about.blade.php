@extends('layouts.app')
@section('title', 'About Us')
@section('content')
<div class="container about-editorial">
    <div class="breadcrumbs"><a href="{{ route('home') }}">Home</a> / About Us</div>
    <h1>About Us</h1>

    <section class="about-spread about-opening" aria-labelledby="about-opening">
        <figure class="about-store">
            <img src="{{ asset('images/design/store-interior.webp') }}" alt="A record shop lined with shelves and crates">
        </figure>
        <div class="about-intro">
            <p class="eyebrow">33½ Records · Jakarta</p>
            <h2 id="about-opening">A place for your next record.</h2>
            <p>New pressings, graded secondhand records, and a reason to spend a little longer flipping through the bins.</p>
            <img src="{{ asset('images/design/record-sleeves.webp') }}" alt="Printed record sleeves packed into a crate" loading="lazy">
        </div>
    </section>

    <section class="about-digging" aria-labelledby="about-digging">
        <img src="{{ asset('images/design/record-wall.webp') }}" alt="A wall of records arranged sleeve out" loading="lazy">
        <div class="about-editorial-note">
            <h2 id="about-digging">Follow your ears.</h2>
            <p>Start with a genre you know, or take a chance on a cover that catches your eye.</p>
            <a class="text-link" href="{{ route('crates') }}">Explore the crates →</a>
        </div>
    </section>

    <section class="about-spread about-trade" aria-labelledby="about-trade">
        <div class="about-trade-copy">
            <div class="about-vinyl-mark" aria-hidden="true"></div>
            <h2 id="about-trade">Keep records playing.</h2>
            <p>Trade records for cash or store credit. We check the vinyl and sleeve, list both grades, and give good records another spin.</p>
            <a class="text-link" href="{{ route('trade.create') }}">Trade in a record →</a>
        </div>
        <img src="{{ asset('images/design/record-shopping.webp') }}" alt="A listener flipping through a crate of records" loading="lazy">
    </section>
</div>
<img class="about-footer-texture" src="{{ asset('images/design/sleeve-texture.webp') }}" alt="" loading="lazy">
@endsection
