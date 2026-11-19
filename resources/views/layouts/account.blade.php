@extends('layouts.app')
@section('content')
    <div class="container account-page">
        <div class="breadcrumbs"><a href="{{ route('home') }}">Home</a> / My account</div>
        <div class="row g-4">
            <aside class="col-lg-3"><x-account-nav /></aside>
            <div class="col-lg-9">@yield('account-content')</div>
        </div>
    </div>
@endsection
