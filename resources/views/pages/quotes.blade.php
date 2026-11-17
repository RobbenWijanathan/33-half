@extends('layouts.app')
@section('title', 'Your Quotes')
@section('content')
<div class="container">
    <div class="breadcrumbs"><a href="{{ route('home') }}">Home</a> / Your Quotes</div>
    <div class="section-header">
        <h1>Your Quotes</h1>
        <a class="button button-dark" href="{{ route('trade.create') }}">Request a Quote</a>
    </div>
    <p class="demo-note">These are demo requests saved in your current browser session. They have not been sent to the store, and no cash or credit offer has been made.</p>
    @forelse($quotes as $quote)
        <article class="quote-card">
            <div class="d-flex justify-content-between flex-wrap">
                <span class="eyebrow">{{ $quote['id'] }}</span>
                <x-status-badge :status="$quote['status'] ?? 'pending_review'" />
            </div>
            <h2>{{ $quote['album'] }}</h2>
            <p>{{ $quote['artist'] }}</p>
            <p class="quote-meta">Record: {{ $quote['record_grade'] }} · Sleeve: {{ $quote['sleeve_grade'] }} · {{ $quote['preference'] === 'cash' ? 'Cash' : 'Store credit' }}</p>
            <small class="text-muted">Submitted {{ $quote['submitted_at'] }}</small>
            <details class="mt-3">
                <summary>View Details</summary>
                <div class="quote-details">
                    <p><strong>Contact:</strong> {{ $quote['name'] }} · {{ $quote['email'] }}</p>
                    <p><strong>Notes:</strong> {{ $quote['notes'] ?: 'No additional notes.' }}</p>
                    <p><strong>Photo filenames:</strong> {{ count($quote['photos']) ? implode(', ', $quote['photos']) : 'No photos selected.' }}</p>
                </div>
            </details>
        </article>
    @empty
        <div class="empty-state">
            <h2>No quotes yet.</h2>
            <p>Ready to make room on your shelf? Tell us about your records.</p>
            <a class="button button-dark" href="{{ route('trade.create') }}">Start a Trade In</a>
        </div>
    @endforelse
</div>
@endsection
