@props(['status'])

@php
    $label = match ($status) {
        'waiting_for_delivery' => 'Waiting for Delivery',
        'delivered' => 'Delivered',
        'accepted' => 'Accepted',
        default => 'Pending Review',
    };
    $tone = match ($status) {
        'waiting_for_delivery' => 'waiting',
        'delivered' => 'delivered',
        'accepted' => 'accepted',
        default => 'review',
    };
@endphp
<span class="status-badge status-badge--{{ $tone }}">{{ $label }}</span>
