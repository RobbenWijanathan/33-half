@extends('layouts.account')
@section('title', 'Account Settings')
@section('account-content')
    <h1>
        Account settings</h1>
    <div class="row g-4">
        <div class="col-md-6">
            <form class="account-panel" method="POST" action="{{ route('account.update') }}">@csrf @method('PATCH')
                <h2 class="h4">Your details</h2>
                <x-field name="name" label="Name" :value="$user->name" autocomplete="name" maxlength="120" required />
                <x-field name="email" label="Email" type="email" :value="$user->email" autocomplete="email" required />
                <x-field name="phone" label="Phone (optional)" :value="$user->phone" maxlength="30" autocomplete="tel" />
                <x-field name="current_password" label="Current password to save changes" type="password"
                    autocomplete="current-password" required />
                <button class="button button-dark">Save details</button>
            </form>
        </div>
        <div class="col-md-6">
            <form class="account-panel" method="POST" action="{{ route('account.password') }}">@csrf @method('PUT')
                <h2 class="h4">Change password</h2>
                <x-field name="current_password" id="password-current" label="Current password" type="password"
                    autocomplete="current-password" required />
                <x-field name="password" label="New password · 8+ characters, letters and numbers" type="password"
                    autocomplete="new-password" minlength="8" required />
                <x-field name="password_confirmation" label="Confirm new password" type="password"
                    autocomplete="new-password" required />
                <button class="button button-border">Update password</button>
            </form>
        </div>
    </div>
    <h2 class="h3 mt-5">Delivery addresses</h2>
    <div class="row g-3">
        @foreach ($addresses as $address)
            <div class="col-md-6">
                <div class="account-panel">
                    <strong>{{ $address->label }}</strong>
                    <p>{{ $address->recipient }} · {{ $address->phone }}<br>{{ $address->street }}<br>{{ $address->city }},
                        {{ $address->province }} {{ $address->postal_code }}</p>
                    <details>
                        <summary>Edit address</summary>
                        <form class="mt-3" method="POST" action="{{ route('addresses.update', $address) }}">@csrf
                            @method('PATCH')
                            @foreach (['label' => 'Label', 'recipient' => 'Recipient', 'phone' => 'Phone', 'street' => 'Street', 'city' => 'City', 'province' => 'Province', 'postal_code' => 'Postal code'] as $field => $label)
                                <x-field :name="$field" :id="'address-' . $address->id . '-' . $field" :label="$label" :value="$address->$field" required />
                            @endforeach
                            <button class="button button-dark">Save address</button>
                        </form>
                    </details>
                    <form class="mt-3" method="POST" action="{{ route('addresses.destroy', $address) }}"
                        data-confirm="Remove this saved address? Previous orders keep their address.">@csrf
                        @method('DELETE')<button class="btn btn-outline-danger btn-sm">Remove address</button></form>
                </div>
            </div>
        @endforeach
    </div>
    <details class="account-panel mt-3" @if ($addresses->isEmpty()) open @endif>
        <summary>Add a delivery address</summary>
        <form class="mt-3" method="POST" action="{{ route('addresses.store') }}">@csrf
            <div class="row">
                @foreach (['label' => 'Label (e.g. Home)', 'recipient' => 'Recipient', 'phone' => 'Phone', 'street' => 'Street', 'city' => 'City', 'province' => 'Province', 'postal_code' => 'Postal code'] as $field => $label)
                    <div class="col-md-6"><x-field :name="$field" :id="'new-address-' . $field" :label="$label" required /></div>
                @endforeach
            </div>
            <button class="button button-dark">Save address</button>
        </form>
    </details>
@endsection
