@extends('layouts.admin')
@section('title', 'Manage Quotes')
@section('admin-content')
    <h1>
        Trade-in quotes</h1>
    <x-admin-filters :sorts="['newest' => 'Newest', 'oldest' => 'Oldest', 'status' => 'Status', 'user' => 'Customer']">
        <div class="col-sm-auto"><select class="form-select" name="status" aria-label="Quote status" data-auto-submit>
                <option value="">All statuses</option>
                @foreach (['pending_review', 'offer_sent', 'accepted', 'declined', 'waiting_for_delivery', 'delivered', 'inspection', 'completed', 'rejected', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ Str::headline($status) }}</option>
                @endforeach
            </select>
        </div>
    </x-admin-filters>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Record</th>
                    <th>Customer</th>
                    <th>Submitted</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($quotes as $quote)
                    <tr>
                        <td><a href="{{ route('admin.quotes.show', $quote) }}">#{{ $quote->id }} ·
                                {{ $quote->album }}</a><small class="d-block text-muted">{{ $quote->artist }}</small></td>
                        <td>{{ $quote->user->name }}<small class="d-block">{{ $quote->user->email }}</small></td>
                        <td>{{ $quote->created_at->format('d M Y') }}</td>
                        <td><x-status-badge :status="$quote->status" /></td>
                </tr>@empty<tr>
                        <td colspan="4">No quotes match your search.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>{{ $quotes->links() }}
@endsection
