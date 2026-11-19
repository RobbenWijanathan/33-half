@extends('layouts.admin')
@section('title', 'Review Trade-in #' . $quote->id)
@section('admin-content')
    <a href="{{ route('admin.quotes.index') }}">
        ← All quotes</a>
    <h1 class="mt-3">#{{ $quote->id }} · {{ $quote->album }}</h1>
    <p>{{ $quote->artist }} · <a href="{{ route('admin.users.edit', $quote->user) }}">{{ $quote->user->name }}</a></p>
    <x-status-badge :status="$quote->status" />
    <div class="account-panel my-4">
        <p>Record: {{ $quote->record_grade }} · Sleeve: {{ $quote->sleeve_grade }} · Prefers
            {{ $quote->preference === 'cash' ? 'cash' : 'store credit' }}</p>
        <p class="preserve-lines">{{ $quote->notes }}</p>
        <div class="d-flex flex-wrap gap-2">
            @foreach ($quote->photos ?? [] as $photo)
                <a href="{{ route('quotes.photo', [$quote, $loop->index]) }}" target="_blank" rel="noopener"><img
                        class="upload-preview" src="{{ route('quotes.photo', [$quote, $loop->index]) }}"
                        alt="Quote photo {{ $loop->iteration }}"></a>
            @endforeach
        </div>
        @if ($quote->cash_offer)
            <p class="mt-3">Cash offer: {{ \App\Support\Money::format($quote->cash_offer) }}</p>
        @endif
        @if ($quote->credit_offer)
            <p>Credit offer: {{ \App\Support\Money::format($quote->credit_offer) }}</p>
        @endif
        <p>{{ $quote->offer_notes }}</p>
        @if ($quote->accepted_type)
            <p>Customer chose: {{ Str::headline($quote->accepted_type) }} ·
                {{ \App\Support\Money::format($quote->accepted_amount) }}</p>
        @endif
        @if ($quote->delivery_method)
            <p>Method: {{ Str::headline($quote->delivery_method) }}</p>
        @endif
    </div>
    @php($adminStatuses = array_values(array_diff($quote->nextStatuses(), ['accepted', 'declined', 'waiting_for_delivery'])))
    @if (count($adminStatuses))
        <form class="account-panel mb-4" method="POST" action="{{ route('admin.quotes.update', $quote) }}"
            data-confirm="Save this quote update? Completion issues the selected credit or confirms the manual cash payout.">
            @csrf @method('PATCH')
            <h2 class="h4">Review & update</h2>
            @if ($quote->status === 'pending_review')
                <div class="row">
                    <div class="col-md-6"><x-field name="cash_offer" label="Cash offer (whole IDR, optional)" type="number"
                            min="1" max="1000000000" /></div>
                    <div class="col-md-6"><x-field name="credit_offer" label="Store-credit offer (whole IDR, optional)"
                            type="number" min="1" max="1000000000" /></div>
                </div><x-field name="offer_notes" label="Offer details">
                    <textarea class="form-control" name="offer_notes" id="offer_notes" rows="3">{{ old('offer_notes') }}</textarea>
                </x-field>
            @endif
            <x-field name="status" label="Next status"><select class="form-select" name="status" id="status">
                    @foreach ($adminStatuses as $status)
                        <option value="{{ $status }}">{{ Str::headline($status) }}</option>
                    @endforeach
                </select></x-field>
            @if ($quote->status === 'inspection')
                <p class="small text-muted">Complete only after inspection and agreement. For cash trade-ins, confirm the
                    cash was paid and record a reference in the note. Credit trade-ins issue credit automatically once.</p>
            @endif
            <x-field name="note" label="Update note / payout reference" maxlength="2000" required />
            <button class="button button-dark">Save update</button>
        </form>
    @elseif(in_array($quote->status, ['offer_sent', 'accepted']))
        <p class="text-muted">Waiting for the customer’s decision or delivery choice.</p>
    @endif
    <h2 class="h4">Status history</h2><x-status-history :histories="$quote->histories" />
@endsection
