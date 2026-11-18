@extends('layouts.app')
@section('title', 'Crates')
@section('content')
<div class="container crates-directory">
    <div class="breadcrumbs"><a href="{{ route('home') }}">Home</a> / Crates</div>
    <div class="section-header">
        <h1>Crates</h1>
        <a class="text-link" href="{{ route('crate') }}">Dig through all records →</a>
    </div>
    <div data-live-filters>
        <div class="crate-toolbar">
            <nav class="crate-tabs" aria-label="Crate categories">
                @foreach(['all' => 'All', 'genres' => 'Genres', 'artists' => 'A–Z'] as $value => $label)
                    <a data-filter-link="{{ $value }}" href="{{ route('crates', ['type' => $value, 'sort' => $sort]) }}" @if($type === $value) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
            </nav>
            <form method="GET" class="crate-sort" data-filter-form>
                <input type="hidden" name="type" value="{{ $type }}">
                <label for="crate-sort">Sort</label>
                <select id="crate-sort" name="sort" class="form-control">
                    <option value="alphabetical" @selected($sort === 'alphabetical')>Alphabetical</option>
                    <option value="count" @selected($sort === 'count')>Most Records</option>
                </select>
                <noscript><button class="button button-border" type="submit">Update results</button></noscript>
            </form>
        </div>

        @if($type !== 'artists')
            <section class="crate-category-section" aria-labelledby="genre-heading">
                <h2 id="genre-heading">Genres</h2>
                <div class="row">
                    @forelse($genres as $genre)
                        <div class="col-12 col-sm-6 col-lg-3 mb-3">
                            <a class="crate-tile crate-genre-{{ $genre->slug }}" href="{{ route('genres.show', $genre) }}">
                                <x-crate-stack :products="$genre->products" />
                                <span class="crate-tile-label">
                                    <strong>{{ $genre->name }}</strong>
                                    <small>{{ $genre->products_count }} {{ Str::plural('record', $genre->products_count) }}</small>
                                </span>
                            </a>
                        </div>
                    @empty
                        <div class="col-12"><x-empty-state title="No genre crates yet" /></div>
                    @endforelse
                </div>
            </section>
        @endif

        @if($type !== 'genres')
            <section class="crate-category-section" aria-labelledby="artist-heading">
                <h2 id="artist-heading">Artist A–Z</h2>
                <div class="row">
                    @forelse($letters as $letter => $records)
                        <div class="col-12 col-sm-6 col-lg-3 mb-3">
                            <a class="crate-tile crate-tile-letter" style="--letter-hue: {{ (ord($letter) - 65) * 137 % 360 }}" href="{{ route('crates.letter', $letter) }}">
                                <x-crate-stack :products="$records" />
                                <span class="crate-tile-label">
                                    <strong>{{ $letter }}</strong>
                                    <small>{{ $records->count() }} {{ Str::plural('record', $records->count()) }}</small>
                                </span>
                            </a>
                        </div>
                    @empty
                        <div class="col-12"><x-empty-state title="No artist crates yet" /></div>
                    @endforelse
                </div>
            </section>
        @endif
    </div>
    <p class="sr-only" role="status" data-filter-announcement></p>
</div>
@endsection
