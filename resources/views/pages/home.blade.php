@extends('layouts.app')
@section('content')
    <x-hero />
    <section class="section container">
        <x-section-header title="New Arrivals" :href="route('vinyl')" />
        <x-product-grid :products="$newArrivals" />
    </section>
    <section class="section container pt-0">
        <x-section-header title="Merchandise" :href="route('merch')" />
        <x-product-grid :products="$merchandise" :wide="true" />
    </section>
    <section class="about-banner about-banner--home">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <img src="{{ asset('images/design/record-shopping.webp') }}" alt="Browsing the record store shelves" loading="lazy">
                </div>
                <div class="col-md-6 py-4">
                    <h2>About Us</h2>
                    <p>New releases, graded used vinyl, and the joy of finding your next record.</p>
                    <a class="button button-dark" href="{{ route('about') }}">Read More</a>
                </div>
            </div>
        </div>
    </section>
@endsection
