@extends('layouts.app')
@section('title', $title.' — 33½')
@section('content')
@php
    $term = $filters['q'] ?? null;
    $sort = $filters['sort'] ?? 'newest';
    $total = $products->total();
@endphp
<div class="container page-intro"><span class="eyebrow">The collection / {{ $section }}</span><h1>{{ $title }}</h1><p>{{ $description }}</p></div>
<div class="container catalog-layout">
    <x-filter-sidebar :genres="$genres" :artists="$artists" :labels="$labels" :filters="$filters" :active-count="count($activeFilters)" />
    <div class="catalog-results">
        <div class="results-bar">
            <p class="results-count" role="status">{{ $total }} {{ Str::plural('item', $total) }}@if($term) for “{{ $term }}”@endif</p>
            <form class="sort-form" method="GET" action="{{ url()->current() }}" data-auto-submit>
                @foreach(collect($filters)->except('sort')->filter(fn ($value) => $value !== null && $value !== '') as $name => $value)<input type="hidden" name="{{ $name }}" value="{{ $value }}">@endforeach
                <label>Sort by <select name="sort">
                    <option value="newest" @selected($sort === 'newest')>Newest</option>
                    <option value="price-low" @selected($sort === 'price-low')>Price: low to high</option>
                    <option value="price-high" @selected($sort === 'price-high')>Price: high to low</option>
                    <option value="name" @selected($sort === 'name')>Title A–Z</option>
                </select></label>
                <button class="sort-apply" type="submit">Sort</button>
            </form>
        </div>
        @include('partials.filter-chips')
        @if($products->isEmpty() && $activeFilters)
            <x-empty-state
                :title="$term ? 'No matches for “'.$term.'”' : 'Nothing matches these filters'"
                :message="$term ? 'Check the spelling, try an artist, genre or label name, or remove a filter above.' : 'Remove a filter above to widen the shelf.'"
                :action-label="count($activeFilters) > 1 ? 'Clear all filters' : ($term ? 'Clear search' : 'Clear filter')"
                :action-url="url()->current()" />
        @else
            <x-product-grid :products="$products" />
        @endif
        {{ $products->links('partials.pagination') }}
    </div>
</div>
@endsection
