@extends('layouts.account')
@section('title', 'Trade-in #' . $quote->id)
@section('account-content')
    <p class="eyebrow">
        Trade-in #{{ $quote->id }} · {{ $quote->created_at->format('d M Y') }}</p>
    <h1>{{ $quote->album }}</h1>
    <p class="page-lead">{{ $quote->artist }}</p><x-status-badge :status="$quote->status" />
    <div class="account-panel my-4">
        <p>Record: <strong>{{ $quote->record_grade }}</strong> · Sleeve: <strong>{{ $quote->sleeve_grade }}</strong> ·
            Preference: {{ $quote->preference === 'cash' ? 'Cash' : 'Store credit' }}</p>
        <p class="preserve-lines">{{ $quote->notes }}</p>
        <div class="d-flex flex-wrap gap-2">
            @foreach ($quote->photos ?? [] as $photo)
                <a href="{{ route('quotes.photo', [$quote, $loop->index]) }}" target="_blank" rel="noopener"><img
                        class="upload-preview" src="{{ route('quotes.photo', [$quote, $loop->index]) }}"
                        alt="Trade-in photo {{ $loop->iteration }}"></a>
            @endforeach
        </div>
    </div>
    @if ($quote->cash_offer || $quote->credit_offer)
        <div class="account-panel mb-4">
            <h2 class="h4">Your offer</h2>
            @if ($quote->cash_offer)
                <p>Cash: <strong>{{ \App\Support\Money::format($quote->cash_offer) }}</strong></p>
            @endif
            @if ($quote->credit_offer)
                <p>Store credit: <strong>{{ \App\Support\Money::format($quote->credit_offer) }}</strong></p>
            @endif
            <p class="preserve-lines">{{ $quote->offer_notes }}</p>
            <p class="small text-muted">Final acceptance depends on inspection. Store credit is issued after completion;
                cash is paid manually by the store.</p>
            @if ($quote->accepted_type)
                <p>Selected: <strong>{{ $quote->accepted_type === 'cash' ? 'Cash' : 'Store credit' }} ·
                        {{ \App\Support\Money::format($quote->accepted_amount) }}</strong></p>
            @endif
            @if ($quote->status === 'offer_sent' && auth()->id() === $quote->user_id)
                <form method="POST" action="{{ route('quotes.update', $quote) }}"
                    class="d-flex flex-wrap gap-2 align-items-center">@csrf @method('PATCH')<input type="hidden"
                        name="status" value="accepted"><label class="visually-hidden" for="accepted_type">Choose an
                        offer</label><select id="accepted_type" name="accepted_type" class="form-select w-auto">
                        @if ($quote->cash_offer)
                            <option value="cash">Cash</option>
                            @endif @if ($quote->credit_offer)
                                <option value="store_credit">Store credit</option>
                            @endif
                    </select><button class="button button-dark">Accept offer</button></form>
                <form class="mt-3" method="POST" action="{{ route('quotes.update', $quote) }}"
                    data-confirm="Decline this offer?">@csrf @method('PATCH')<input type="hidden" name="status"
                        value="declined"><button class="button button-border">Decline offer</button></form>
            @endif
        </div>
    @endif
    @if ($quote->status === 'accepted' && auth()->id() === $quote->user_id)
        <form class="account-panel mb-4" method="POST" action="{{ route('quotes.update', $quote) }}">@csrf
            @method('PATCH')<input type="hidden" name="status" value="waiting_for_delivery">
            <h2 class="h4">Get your record to us</h2>
            <p>{{ config('storefront.pickup_location') }}</p><label for="delivery_method"
                class="form-label">Method</label><select class="form-select mb-3" name="delivery_method"
                id="delivery_method">
                <option value="drop_off">Drop off at the store</option>
                <option value="delivery">Arrange delivery to the store</option>
            </select><button class="button button-dark">Confirm method</button>
        </form>
    @endif
    @if ($quote->delivery_method)
        <p>Chosen method: {{ $quote->delivery_method === 'drop_off' ? 'Store drop-off' : 'Delivery to the store' }}.
            Contact the store to arrange receipt.</p>
    @endif
    <h2 class="h4">Status history</h2><x-status-history :histories="$quote->histories" />
    @if (auth()->id() === $quote->user_id && in_array('cancelled', $quote->nextStatuses()))
        <form method="POST" action="{{ route('quotes.update', $quote) }}" data-confirm="Cancel this trade-in request?">
            @csrf @method('PATCH')<input type="hidden" name="status" value="cancelled"><button
                class="button button-border">Cancel request</button></form>
    @endif
@endsection
