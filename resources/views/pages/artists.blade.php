@extends('layouts.app')
@section('title', 'Artists')
@section('content')
<div class="container">
    <div class="breadcrumbs"><a href="{{ route('home') }}">Home</a> / Artists</div>
    <h1>Artists</h1>
    <div class="directory-grid">
        @forelse($artists as $artist)
            <a class="directory-card" href="{{ route('artists.show', $artist) }}">
                <span>{{ $artist->products_count }} records</span>
                <h2>{{ $artist->name }}</h2>
                <span>Explore →</span>
            </a>
        @empty
            <x-empty-state />
        @endforelse
    </div>
</div>
@endsection
