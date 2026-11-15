@props(['genres', 'artists', 'labels', 'filters', 'activeCount' => 0])
{{-- Collapsed behind its summary on small screens; resources/js/catalog.js keeps it open on wide ones. --}}
<aside class="filter-sidebar">
    <details class="filter-panel" data-filter-panel>
        <summary><h2>Filters</h2>@if($activeCount)<span class="filter-count">{{ $activeCount }} <span class="sr-only">active</span></span>@endif</summary>
        <form method="GET" action="{{ url()->current() }}">
            @if(! empty($filters['sort']))<input type="hidden" name="sort" value="{{ $filters['sort'] }}">@endif
            <x-search-bar :value="$filters['q'] ?? ''" />
            <label>Genre<select name="genre"><option value="">All genres</option>@foreach($genres as $genre)<option value="{{ $genre->slug }}" @selected(($filters['genre'] ?? '') === $genre->slug)>{{ $genre->name }}</option>@endforeach</select></label>
            <label>Artist<select name="artist"><option value="">All artists</option>@foreach($artists as $artist)<option value="{{ $artist->slug }}" @selected(($filters['artist'] ?? '') === $artist->slug)>{{ $artist->name }}</option>@endforeach</select></label>
            <label>Label<select name="label"><option value="">All labels</option>@foreach($labels as $label)<option value="{{ $label->slug }}" @selected(($filters['label'] ?? '') === $label->slug)>{{ $label->name }}</option>@endforeach</select></label>
            <div class="filter-price"><label>Min price<input type="number" name="min_price" min="0" step="0.01" inputmode="decimal" value="{{ $filters['min_price'] ?? '' }}" placeholder="$0"></label><label>Max price<input type="number" name="max_price" min="0" step="0.01" inputmode="decimal" value="{{ $filters['max_price'] ?? '' }}" placeholder="Any"></label></div>
            <label>Availability<select name="availability"><option value="">Any</option><option value="in-stock" @selected(($filters['availability'] ?? '') === 'in-stock')>In stock</option><option value="preorder" @selected(($filters['availability'] ?? '') === 'preorder')>Pre-order</option></select></label>
            <button class="button button-dark" type="submit">Apply filters</button>
        </form>
    </details>
</aside>
