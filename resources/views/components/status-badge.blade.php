@props(['status'])
@php
    $tone = match ($status) {
        'completed', 'accepted', 'picked_up', 'paid', 'delivered' => 'accepted',
        'rejected', 'declined', 'cancelled', 'refunded' => 'closed',
        'shipped', 'out_for_delivery', 'inspection', 'confirmed', 'ready_for_pickup' => 'delivered',
        default => 'waiting',
    };
@endphp
<span class="status-badge status-badge--{{ $tone }}">{{ Str::headline($status) }}</span>
