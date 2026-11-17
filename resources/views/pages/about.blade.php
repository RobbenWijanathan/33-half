@extends('layouts.app')
@section('title', 'About Us — 33½')
@section('content')
<div class="container">
    <div class="breadcrumbs"><a href="{{ route('home') }}">Home</a> / About Us</div>
    <h1>About Us</h1>
    <div class="row align-items-center mb-5">
        <div class="col-md-6 mb-4"><img class="about-photo" src="{{ asset('images/design/record-shopping.webp') }}" alt="A collector browsing records"></div>
        <div class="col-md-6">
            <h2>A place for your next record.</h2>
            <p>33½ brings together new releases and graded used vinyl. Browse the shelves, dig through a genre, or follow an artist you love.</p>
            <p>We check every used record and its sleeve before listing it. Clear grades help you know what you’re taking home.</p>
            <h2>Keep records playing.</h2>
            <p>Trade in your records for cash or store credit. Your collection makes room for something new, and someone else finds their next favourite.</p>
            <a class="button button-dark" href="{{ route('trade.create') }}">Trade In</a>
        </div>
    </div>
    <h2>A fresh selection.</h2>
    <p>Trade-ins keep our crates changing. We buy new records in small batches and restock what people enjoy, alongside a small range of merch and accessories.</p>
    <a class="text-link" href="{{ route('crates') }}">Explore the Crates</a>
</div>
@endsection
