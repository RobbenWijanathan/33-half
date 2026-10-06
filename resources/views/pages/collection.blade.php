@extends('layouts.app')
@section('title', $title.' — 33½')
@section('content')
<div class="container page-intro"><span class="eyebrow">{{ $eyebrow }}</span><h1>{{ $title }}</h1><p>{{ $description }}</p></div><section class="section container"><x-product-grid :products="$products" /><x-pagination :paginator="$products" /></section>
@endsection
