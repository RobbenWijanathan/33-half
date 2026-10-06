@extends('layouts.app')
@section('title', 'Crate Digging — 33½')
@section('content')
<div class="crate-page"><div class="container crate-head"><div><span class="eyebrow light">A different way to browse</span><h1>Crate digging.</h1><p>Flip through the stack. Stay for a sound.</p></div><div class="crate-counter"><span data-crate-counter>01 / {{ str_pad($products->count(), 2, '0', STR_PAD_LEFT) }}</span><button type="button" data-crate-random>Dig randomly ↗</button></div></div><div class="container crate-stage" data-crate-stage>@forelse($products as $product)<x-crate-card :product="$product" />@empty<x-empty-state title="The crate is empty" message="Add vinyl records to begin digging." />@endforelse</div><div class="container crate-controls"><button type="button" data-crate-prev>← Previous record</button><span>Swipe, scroll, or use the buttons</span><button type="button" data-crate-next>Next record →</button></div></div>
@endsection
