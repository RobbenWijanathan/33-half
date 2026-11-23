@props(['title' => 'Nothing here yet', 'message' => 'More is on the way.', 'actionLabel' => 'Browse the shop', 'actionUrl' => null])
<div class="empty-state"><span aria-hidden="true">◎</span><h2>{{ $title }}</h2><p>{{ $message }}</p><a class="button button-dark" href="{{ $actionUrl ?? route('shop') }}" @if ($actionUrl) data-filter-link="empty-action" @endif>{{ $actionLabel }}</a></div>
