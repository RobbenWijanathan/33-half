@props(['title' => 'Nothing here yet', 'message' => 'More is on the way.'])
<div class="empty-state"><span aria-hidden="true">◎</span><h2>{{ $title }}</h2><p>{{ $message }}</p><a class="button button-dark" href="{{ route('shop') }}">Browse the shop</a></div>
