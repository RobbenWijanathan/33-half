@extends('layouts.app')
@section('title', 'Reset password')
@section('content')
    <div class="container account-auth">
        <h1>Forgot password?</h1>
        <p>Enter your account email to request a reset link.</p>
        <form method="POST" action="{{ route('password.email') }}">@csrf
            <x-field name="email" label="Email" type="email" autocomplete="email" required />
            <button class="button button-dark w-100">Send reset link</button>
        </form>
        <a class="d-inline-block mt-4" href="{{ route('login') }}">Back to sign in</a>
    </div>
@endsection
