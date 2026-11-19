@extends('layouts.app')
@section('title', 'Sign in')
@section('content')
    <div class="container account-auth">
        <p class="eyebrow">Back for another spin</p>
        <h1>Sign in</h1>
        <p class="text-muted">Your records, orders, and trade-ins in one place.</p>
        <form method="POST" action="{{ route('login.store') }}">@csrf
            <x-field name="email" label="Email" type="email" autocomplete="username" required autofocus />
            <x-field name="password" label="Password" type="password" autocomplete="current-password" required />
            <label class="form-check mb-3"><input class="form-check-input" type="checkbox" name="remember" value="1">
                <span class="form-check-label">Keep me signed in</span></label>
            <button class="button button-dark w-100">Sign in</button>
        </form>
        <div class="d-flex justify-content-between flex-wrap gap-3 mt-4"><a href="{{ route('password.request') }}">Forgot
                password?</a><a href="{{ route('register') }}">Create an account</a></div>
    </div>
@endsection
