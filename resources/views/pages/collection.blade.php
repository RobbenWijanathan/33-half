@extends('layouts.app')
@section('title', $title)
@section('content')
    <div class="container collection-page">
        <div class="breadcrumbs">
            <a href="{{ route('home') }}">Home</a> /
            <a href="{{ route('crates') }}">Crates</a> / {{ $title }}
        </div>
        <h1>{{ $title }}</h1>
        @if ($description !== '')
            <p class="page-lead">{{ $description }}</p>
        @endif
        <div data-live-filters>
            <div class="crate-toolbar">
                <form method="GET" class="crate-sort" data-filter-form>
                    <label for="record-sort">Sort</label>
                    <select id="record-sort" name="sort" class="form-control">
                        <option value="artist" @selected($sort === 'artist')>Artist</option>
                        <option value="title" @selected($sort === 'title')>Album Title</option>
                        <option value="newest" @selected($sort === 'newest')>Recently Added</option>
                    </select>
                    <noscript><button class="button button-border" type="submit">Update results</button></noscript>
                </form>
                <a class="text-link" href="{{ route('crates') }}">View All Crates</a>
            </div>
            <x-record-carousel :products="$products" />
        </div>
        <p class="visually-hidden" role="status" data-filter-announcement></p>
    </div>
@endsection
