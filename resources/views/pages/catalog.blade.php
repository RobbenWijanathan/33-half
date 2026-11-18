@extends('layouts.app')
@section('title', $title)
@section('content')
<div class="container">
    <div class="breadcrumbs"><a href="{{ route('home') }}">Home</a> / {{ $title }}</div>
    <h1>{{ $title }}</h1>
    <div data-live-filters>
        <form method="GET" class="catalog-toolbar" data-filter-form>
            @if($section !== 'merch')
            <fieldset class="condition-filter">
                <legend class="sr-only">Record condition</legend>
                @foreach(['' => 'All', 'new' => 'New', 'used' => 'Used'] as $value => $label)
                    <label class="condition-option">
                        <input type="radio" name="condition" value="{{ $value }}" @checked(($filters['condition'] ?? '') === $value)>
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </fieldset>
            <label>Genre
                <select name="genre" class="form-control">
                    <option value="">All Genres</option>
                    @foreach($genres as $genre)
                        <option value="{{ $genre->slug }}" @selected(($filters['genre'] ?? '') === $genre->slug)>{{ $genre->name }}</option>
                    @endforeach
                </select>
            </label>
            @endif
            <label>Sort
                <select name="sort" class="form-control">
                    @foreach($sortOptions as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['sort'] ?? array_key_first($sortOptions)) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            @foreach(['q', 'artist', 'label', 'min_price', 'max_price', 'availability'] as $filter)
                @if(isset($filters[$filter]))
                    <input type="hidden" name="{{ $filter }}" value="{{ $filters[$filter] }}">
                @endif
            @endforeach
            <noscript><button class="button button-dark" type="submit">Update results</button></noscript>
            <a class="text-link" data-filter-link="reset" href="{{ url()->current() }}">Reset</a>
        </form>
        <p class="results-count">{{ $products->total() }} {{ Str::plural('item', $products->total()) }}</p>
        <x-product-grid :products="$products" />
        <x-pagination :paginator="$products" />
    </div>
    <p class="sr-only" role="status" data-filter-announcement></p>
</div>
@endsection
