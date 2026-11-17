@extends('layouts.app')
@section('title', $title.' — 33½')
@section('content')
<div class="container collection-page">
    <div class="breadcrumbs">
        <a href="{{ route('home') }}">Home</a> /
        <a href="{{ route('crates') }}">Crates</a> / {{ $title }}
    </div>
    <h1>{{ $title }}</h1>
    <div class="crate-toolbar">
        <form method="GET" class="crate-sort">
            <label for="record-sort">Sort</label>
            <select id="record-sort" name="sort" class="form-control" data-auto-submit>
                <option value="artist" @selected($sort === 'artist')>Artist</option>
                <option value="title" @selected($sort === 'title')>Album Title</option>
                <option value="newest" @selected($sort === 'newest')>Recently Added</option>
            </select>
            <button class="button button-border sort-submit" type="submit">Apply</button>
        </form>
        <a class="text-link" href="{{ route('crates') }}">View All Crates</a>
    </div>
    <x-record-carousel :products="$products" />
</div>
@endsection
