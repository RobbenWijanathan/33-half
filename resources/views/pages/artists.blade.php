@extends('layouts.app')
@section('title', 'Artists — 33½')
@section('content')
<div class="container page-intro"><span class="eyebrow">The people behind the music</span><h1>Artists</h1><p>Find the names that make the records.</p></div><section class="container directory-grid">@forelse($artists as $artist)<a class="directory-card" href="{{ route('artists.show', $artist) }}"><span>ARTIST / {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><h2>{{ $artist->name }}</h2><span>{{ $artist->products_count }} {{ Str::plural('release', $artist->products_count) }} ↗</span></a>@empty<x-empty-state />@endforelse</section>
@endsection
