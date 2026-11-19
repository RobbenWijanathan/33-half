@extends('layouts.app')
@section('title', 'Choose a password')
@section('content')
    <div class="container account-auth">
        <h1>Choose a password</h1>
        <form method="POST" action="{{ route('password.update') }}">@csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <x-field name="email" label="Email" type="email" :value="$email" autocomplete="username" required />
            <x-field name="password" label="New password · at least 8 characters, letters and numbers" type="password"
                minlength="8" autocomplete="new-password" required />
            <x-field name="password_confirmation" label="Confirm password" type="password" autocomplete="new-password"
                required />
            <button class="button button-dark w-100">Reset password</button>
        </form>
    </div>
@endsection
