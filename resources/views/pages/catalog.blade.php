@extends('layouts.app')
@section('title', $title.' — 33½')
@section('content')
<div class="container">
    <div class="breadcrumbs"><a href="{{ route('home') }}">Home</a> / {{ $title }}</div>
    <h1>{{ $title }}</h1>
    <form method="GET" class="catalog-toolbar">
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
        <label>Sort
            <select name="sort" class="form-control">
                @foreach(['newest' => 'Recently Added', 'name' => 'Title A–Z', 'price-low' => 'Price Low to High', 'price-high' => 'Price High to Low'] as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['sort'] ?? 'newest') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        @foreach(['q', 'artist', 'label', 'min_price', 'max_price', 'availability'] as $filter)
            @if(isset($filters[$filter]))
                <input type="hidden" name="{{ $filter }}" value="{{ $filters[$filter] }}">
            @endif
        @endforeach
        <button class="button button-dark" type="submit">Apply</button>
        <a class="text-link" href="{{ url()->current() }}">Reset</a>
    </form>
    <p class="results-count">{{ $products->total() }} {{ Str::plural('item', $products->total()) }}</p>
    <x-product-grid :products="$products" />
    <x-pagination :paginator="$products" />
</div>
@endsection
