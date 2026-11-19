@extends('layouts.app')
@section('title', 'Create an account')
@section('content')
    <div class="container account-auth">
        <p class="eyebrow">Make room on your shelf</p>
        <h1>Create an account</h1>
        <form method="POST" action="{{ route('register.store') }}">@csrf
            <x-field name="name" label="Name" autocomplete="name" maxlength="120" required />
            <x-field name="email" label="Email" type="email" autocomplete="username" required />
            <x-field name="password" label="Password · at least 8 characters, including letters and numbers" type="password"
                minlength="8" autocomplete="new-password" required />
            <x-field name="password_confirmation" label="Confirm password" type="password" autocomplete="new-password"
                required />
            <button class="button button-dark w-100">Create account</button>
        </form>
        <p class="mt-4">Already collecting with us? <a href="{{ route('login') }}">Sign in</a></p>
    </div>
@endsection
