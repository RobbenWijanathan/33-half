@extends('layouts.app')
@section('title', $title.' — 33½')
@section('content')
<div class="container page-intro"><span class="eyebrow">The collection / {{ $section }}</span><h1>{{ $title }}</h1><p>Browse the shelf. Follow a sound. Find your next favorite.</p></div>
<div class="container catalog-layout"><x-filter-sidebar :genres="$genres" :artists="$artists" :labels="$labels" :filters="$filters" /><div class="catalog-results"><div class="results-bar"><span>{{ $products->total() }} {{ Str::plural('item', $products->total()) }}</span><span>Use the filters to narrow the shelf</span></div><x-product-grid :products="$products" /><x-pagination :paginator="$products" /></div></div>
@endsection
