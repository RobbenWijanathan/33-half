@extends('layouts.app')
@section('title', $title)
@section('content')
    <div class="container">
        <div class="breadcrumbs"><a href="{{ route('home') }}">Home</a> / {{ $title }}</div>
        <h1>{{ $title }}</h1>
        <div data-live-filters>
            <form method="GET" class="catalog-toolbar" data-filter-form>
                @if ($section !== 'merch')
                    <fieldset class="condition-filter">
                        <legend class="visually-hidden">Record condition</legend>
                        @foreach (['' => 'All', 'new' => 'New', 'used' => 'Used'] as $value => $label)
                            <label class="condition-option">
                                <input type="radio" name="condition" value="{{ $value }}"
                                    @checked(($filters['condition'] ?? '') === $value)>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </fieldset>
                    <label>Genre
                        <select name="genre" class="form-control">
                            <option value="">All Genres</option>
                            @foreach ($genres as $genre)
                                <option value="{{ $genre->slug }}" @selected(($filters['genre'] ?? '') === $genre->slug)>{{ $genre->name }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                @endif
                <label>Sort
                    <select name="sort" class="form-control">
                        @foreach ($sortOptions as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['sort'] ?? array_key_first($sortOptions)) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                @foreach (['q', 'artist', 'label', 'min_price', 'max_price', 'availability'] as $filter)
                    @if (isset($filters[$filter]))
                        <input type="hidden" name="{{ $filter }}" value="{{ $filters[$filter] }}">
                    @endif
                @endforeach
                <noscript><button class="button button-dark" type="submit">Update results</button></noscript>
                <a class="text-link" data-filter-link="reset" href="{{ url()->current() }}">Reset</a>
            </form>
            @php($term = $filters['q'] ?? null)
            <div class="results-line">
                <p class="results-count">{{ $products->total() }} {{ Str::plural('item', $products->total()) }}@if ($term) for “{{ $term }}”@endif</p>
                @if ($term)
                    <a class="search-chip" data-filter-link="clear-search" href="{{ request()->fullUrlWithoutQuery(['q', 'page']) }}"><span class="visually-hidden">Clear search </span>Search: “{{ $term }}” <span aria-hidden="true">×</span></a>
                @endif
            </div>
            @if ($products->isEmpty() && $term)
                <x-empty-state :title="'No matches for “'.$term.'”'"
                    message="Check the spelling, or try an artist, genre or label name."
                    action-label="Clear search" :action-url="request()->fullUrlWithoutQuery(['q', 'page'])" />
            @else
                <x-product-grid :products="$products" />
            @endif
            <x-pagination :paginator="$products" />
        </div>
        <p class="visually-hidden" role="status" data-filter-announcement></p>
    </div>
@endsection
