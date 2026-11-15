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
        @if($activeFilters)
            <ul class="filter-chips" aria-label="Active filters">
                @foreach($activeFilters as $filter)
                    <li><a class="filter-chip" href="{{ $filter['url'] }}"><span class="sr-only">Remove filter </span>{{ $filter['label'] }}<svg aria-hidden="true" viewBox="0 0 12 12" width="10" height="10"><path d="M2 2l8 8M10 2l-8 8" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg></a></li>
                @endforeach
                @if(count($activeFilters) > 1)<li><a class="filter-chips-clear" href="{{ url()->current() }}">Clear all</a></li>@endif
            </ul>
        @endif
        @if($products->isEmpty() && $activeFilters)
            <x-empty-state
                :title="$term ? 'No matches for “'.$term.'”' : 'Nothing matches these filters'"
                :message="$term ? 'Check the spelling, try an artist, genre or label name, or remove a filter above.' : 'Remove a filter above to widen the shelf.'"
                :action-label="count($activeFilters) > 1 ? 'Clear all filters' : ($term ? 'Clear search' : 'Clear filter')"
                :action-url="url()->current()" />
        @else
            <x-product-grid :products="$products" />
        @endif
        <x-pagination :paginator="$products" />
    </div>
</div>
@endsection
