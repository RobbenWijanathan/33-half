@extends('layouts.app')
@section('title', 'Genres — 33½')
@section('content')
<div class="container">
    <div class="breadcrumbs"><a href="{{ route('home') }}">Home</a> / Genres</div>
    <h1>Genres</h1>
    <div class="directory-grid">
        @forelse($genres as $genre)
            <a class="directory-card" href="{{ route('genres.show', $genre) }}">
                <span>{{ $genre->products_count }} records</span>
                <h2>{{ $genre->name }}</h2>
                <span>Explore →</span>
            </a>
        @empty
            <x-empty-state />
        @endforelse
    </div>
</div>
@endsection
