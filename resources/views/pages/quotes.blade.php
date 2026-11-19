@extends('layouts.account')
@section('title', 'Your Quotes')
@section('account-content')
    <div class="section-heading">
        <h1>Your quotes</h1><a class="button button-dark" href="{{ route('trade.create') }}">Request a quote</a>
    </div>
    <form class="d-flex gap-2 align-items-center mb-4" method="GET"><label for="quote-sort">Sort</label><select
            class="form-select w-auto" name="sort" id="quote-sort" data-auto-submit>
            @foreach (['newest' => 'Newest first', 'oldest' => 'Oldest first', 'status' => 'Status'] as $value => $label)
                <option value="{{ $value }}" @selected(request('sort', 'newest') === $value)>{{ $label }}</option>
            @endforeach
        </select><noscript><button class="button button-border">Sort</button></noscript></form>
    @forelse($quotes as $quote)
        <article class="account-panel mb-3">
            <div class="d-flex justify-content-between gap-3 flex-wrap">
                <div><span class="eyebrow">#{{ $quote->id }} · {{ $quote->created_at->format('d M Y') }}</span>
                    <h2 class="h4 mt-2"><a href="{{ route('quotes.show', $quote) }}">{{ $quote->album }}</a></h2>
                    <p>{{ $quote->artist }}</p>
                </div>
                <div><x-status-badge :status="$quote->status" /></div>
            </div>
            <p class="small text-muted">Record: {{ $quote->record_grade }} · Sleeve: {{ $quote->sleeve_grade }} ·
                {{ $quote->preference === 'cash' ? 'Cash' : 'Store credit' }}</p>
            <a href="{{ route('quotes.show', $quote) }}">Details, offers & status →</a>
        </article>
    @empty<x-empty-state title="No quotes yet."
            message="Ready to make room on your shelf? Tell us about your records." />
    @endforelse
    {{ $quotes->links() }}
@endsection
