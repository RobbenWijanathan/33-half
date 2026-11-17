@extends('layouts.app')
@section('title', 'Contact — 33½')
@section('content')
<div class="container">
    <div class="breadcrumbs"><a href="{{ route('home') }}">Home</a> / Contact Us</div>
    <h1>Let’s talk records.</h1>
    <p class="page-lead">33½ Records · Jakarta, Indonesia</p>
    <div class="row">
        <div class="col-md-6 mb-4">
            <h2>Have records to trade?</h2>
            <p>Start with the artist, album, and condition. You can try the trade-in quote form and view your saved demo request.</p>
            <a class="button button-dark" href="{{ route('trade.create') }}">Trade In</a>
        </div>
        <div class="col-md-6">
            <h2>Visit the store</h2>
            <p>Our store address, opening hours, and contact details will be announced here.</p>
            <a class="text-link" href="{{ route('about') }}">More about 33½</a>
        </div>
    </div>
</div>
@endsection
