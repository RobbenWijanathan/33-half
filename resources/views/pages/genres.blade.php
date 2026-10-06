@extends('layouts.app')
@section('title', 'Genres — 33½')
@section('content')
<div class="container page-intro"><span class="eyebrow">Follow a sound</span><h1>Genres</h1><p>There is always another shelf to explore.</p></div><section class="container directory-grid">@forelse($genres as $genre)<a class="directory-card" href="{{ route('genres.show', $genre) }}"><span>GENRE / {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><h2>{{ $genre->name }}</h2><span>{{ $genre->products_count }} {{ Str::plural('record', $genre->products_count) }} ↗</span></a>@empty<x-empty-state />@endforelse</section>
@endsection
